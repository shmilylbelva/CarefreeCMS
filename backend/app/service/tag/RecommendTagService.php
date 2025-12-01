<?php
namespace app\service\tag;

use app\model\Article;
use think\facade\Db;
use think\facade\Cache;

/**
 * AI推荐标签服务类
 * 处理智能推荐标签的数据查询
 */
class RecommendTagService
{
    /**
     * 获取推荐内容
     *
     * @param array $params 查询参数
     *   - type: 推荐类型（similar-相似内容，hot-热门内容，related-相关内容，user-基于用户）
     *   - userid: 用户ID
     *   - aid: 文章ID（用于相似/相关推荐）
     *   - limit: 数量限制
     * @return array
     */
    public static function getList($params = [])
    {
        $type = $params['type'] ?? 'hot';
        $userid = $params['userid'] ?? 0;
        $aid = $params['aid'] ?? 0;
        $limit = $params['limit'] ?? 10;

        try {
            switch ($type) {
                case 'similar':
                    // 相似内容推荐（基于当前文章）
                    return self::getSimilarContent($aid, $limit);

                case 'hot':
                    // 热门内容推荐
                    return self::getHotContent($limit);

                case 'related':
                    // 相关内容推荐（基于标签）
                    return self::getRelatedContent($aid, $limit);

                case 'user':
                    // 基于用户的个性化推荐
                    return self::getUserBasedRecommend($userid, $limit);

                case 'collaborative':
                    // 协同过滤推荐
                    return self::getCollaborativeRecommend($userid, $limit);

                default:
                    return self::getHotContent($limit);
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取相似内容（基于内容相似度）
     *
     * @param int $aid 文章ID
     * @param int $limit 数量限制
     * @return array
     */
    private static function getSimilarContent($aid, $limit)
    {
        if (empty($aid)) {
            return [];
        }

        // 获取当前文章
        $article = Article::where('id', $aid)->find();
        if (!$article) {
            return [];
        }

        // 基于分类和标签的相似度推荐
        $query = Article::where('status', 1)
            ->where('id', '<>', $aid);

        // 同分类优先
        if (!empty($article['category_id'])) {
            $query->where('category_id', $article['category_id']);
        }

        $articles = $query->order('view_count', 'desc')
            ->limit($limit * 2) // 获取更多候选
            ->select()
            ->toArray();

        // 计算相似度分数
        foreach ($articles as &$item) {
            $item['similarity_score'] = self::calculateSimilarity($article, $item);
        }

        // 按相似度排序
        usort($articles, function($a, $b) {
            return $b['similarity_score'] <=> $a['similarity_score'];
        });

        return array_slice($articles, 0, $limit);
    }

    /**
     * 计算文章相似度
     *
     * @param array $article1 文章1
     * @param array $article2 文章2
     * @return float
     */
    private static function calculateSimilarity($article1, $article2)
    {
        $score = 0;

        // 同分类加分
        if ($article1['category_id'] == $article2['category_id']) {
            $score += 50;
        }

        // 标题相似度（简单的关键词匹配）
        $keywords1 = self::extractKeywords($article1['title']);
        $keywords2 = self::extractKeywords($article2['title']);
        $commonKeywords = array_intersect($keywords1, $keywords2);
        $score += count($commonKeywords) * 10;

        // 时间因素（越新的文章分数略高）
        $timeDiff = abs(strtotime($article1['create_time']) - strtotime($article2['create_time']));
        $daysDiff = $timeDiff / 86400;
        if ($daysDiff < 30) {
            $score += 20 * (1 - $daysDiff / 30);
        }

        return $score;
    }

    /**
     * 提取关键词（简单实现）
     *
     * @param string $text 文本
     * @return array
     */
    private static function extractKeywords($text)
    {
        // 简单的分词（实际项目中应使用专业分词工具）
        $text = preg_replace('/[^\x{4e00}-\x{9fa5}a-zA-Z0-9]/u', ' ', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // 过滤停用词
        $stopWords = ['的', '是', '在', '了', '和', '与', '及', '等', '有', '为'];
        $words = array_diff($words, $stopWords);

        return array_values($words);
    }

    /**
     * 获取热门内容
     *
     * @param int $limit 数量限制
     * @return array
     */
    private static function getHotContent($limit)
    {
        // 缓存30分钟
        $cacheKey = "recommend_hot_{$limit}";
        $result = Cache::get($cacheKey);

        if (!empty($result)) {
            return $result;
        }

        // 综合评分：浏览量 * 0.5 + 评论数 * 3 + 点赞数 * 2
        $articles = Article::where('status', 1)
            ->field('*, (view_count * 0.5 + comment_count * 3 + like_count * 2) as hot_score')
            ->order('hot_score', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();

        Cache::set($cacheKey, $articles, 1800);

        return $articles;
    }

    /**
     * 获取相关内容（基于标签）
     *
     * @param int $aid 文章ID
     * @param int $limit 数量限制
     * @return array
     */
    private static function getRelatedContent($aid, $limit)
    {
        if (empty($aid)) {
            return [];
        }

        // 获取当前文章的标签
        $tags = Db::table('article_tags')
            ->where('article_id', $aid)
            ->column('tag_id');

        if (empty($tags)) {
            // 如果没有标签，返回同分类的文章
            $article = Article::where('id', $aid)->find();
            if ($article) {
                return Article::where('status', 1)
                    ->where('id', '<>', $aid)
                    ->where('category_id', $article['category_id'])
                    ->order('create_time', 'desc')
                    ->limit($limit)
                    ->select()
                    ->toArray();
            }
            return [];
        }

        // 查找有相同标签的文章
        $relatedArticles = Db::table('article_tags')
            ->alias('at')
            ->leftJoin('articles a', 'at.article_id = a.id')
            ->where('at.tag_id', 'in', $tags)
            ->where('at.article_id', '<>', $aid)
            ->where('a.status', 1)
            ->field('a.*, COUNT(at.tag_id) as tag_match_count')
            ->group('a.id')
            ->order('tag_match_count', 'desc')
            ->order('a.view_count', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();

        return $relatedArticles;
    }

    /**
     * 基于用户的个性化推荐
     *
     * @param int $userid 用户ID
     * @param int $limit 数量限制
     * @return array
     */
    private static function getUserBasedRecommend($userid, $limit)
    {
        if (empty($userid)) {
            return self::getHotContent($limit);
        }

        // 获取用户浏览历史
        $viewHistory = Db::table('user_view_history')
            ->where('user_id', $userid)
            ->order('create_time', 'desc')
            ->limit(20)
            ->column('article_id');

        if (empty($viewHistory)) {
            return self::getHotContent($limit);
        }

        // 获取用户浏览过的文章的分类和标签
        $articles = Article::whereIn('id', $viewHistory)->select()->toArray();

        $categoryIds = array_column($articles, 'category_id');
        $categoryIds = array_filter(array_unique($categoryIds));

        // 统计用户偏好的分类
        $categoryPreference = [];
        foreach ($categoryIds as $catid) {
            $categoryPreference[$catid] = ($categoryPreference[$catid] ?? 0) + 1;
        }

        // 按偏好排序
        arsort($categoryPreference);
        $preferredCategories = array_keys($categoryPreference);

        // 推荐用户偏好分类中的新文章（排除已浏览的）
        $recommended = Article::where('status', 1)
            ->whereNotIn('id', $viewHistory)
            ->whereIn('category_id', array_slice($preferredCategories, 0, 3))
            ->order('create_time', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();

        return $recommended;
    }

    /**
     * 协同过滤推荐
     *
     * @param int $userid 用户ID
     * @param int $limit 数量限制
     * @return array
     */
    private static function getCollaborativeRecommend($userid, $limit)
    {
        if (empty($userid)) {
            return self::getHotContent($limit);
        }

        // 获取用户浏览历史
        $userViewHistory = Db::table('user_view_history')
            ->where('user_id', $userid)
            ->column('article_id');

        if (empty($userViewHistory)) {
            return self::getHotContent($limit);
        }

        // 查找有相似浏览历史的其他用户
        $similarUsers = Db::table('user_view_history')
            ->alias('uvh')
            ->where('uvh.user_id', '<>', $userid)
            ->whereIn('uvh.article_id', $userViewHistory)
            ->field('uvh.user_id, COUNT(DISTINCT uvh.article_id) as common_views')
            ->group('uvh.user_id')
            ->having('common_views', '>=', 2)
            ->order('common_views', 'desc')
            ->limit(10)
            ->column('user_id');

        if (empty($similarUsers)) {
            return self::getHotContent($limit);
        }

        // 获取相似用户浏览但当前用户未浏览的文章
        $recommendedArticleIds = Db::table('user_view_history')
            ->whereIn('user_id', $similarUsers)
            ->whereNotIn('article_id', $userViewHistory)
            ->field('article_id, COUNT(DISTINCT user_id) as user_count')
            ->group('article_id')
            ->order('user_count', 'desc')
            ->limit($limit)
            ->column('article_id');

        if (empty($recommendedArticleIds)) {
            return self::getHotContent($limit);
        }

        return Article::where('status', 1)
            ->whereIn('id', $recommendedArticleIds)
            ->select()
            ->toArray();
    }

    /**
     * 记录用户浏览行为
     *
     * @param int $userid 用户ID
     * @param int $articleId 文章ID
     * @return bool
     */
    public static function recordView($userid, $articleId)
    {
        if (empty($userid) || empty($articleId)) {
            return false;
        }

        try {
            // 检查是否已记录（24小时内不重复记录）
            $exists = Db::table('user_view_history')
                ->where('user_id', $userid)
                ->where('article_id', $articleId)
                ->whereTime('create_time', '>=', '-24 hours')
                ->find();

            if (!$exists) {
                Db::table('user_view_history')->insert([
                    'user_id' => $userid,
                    'article_id' => $articleId,
                    'create_time' => date('Y-m-d H:i:s')
                ]);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 清除推荐缓存
     *
     * @return bool
     */
    public static function clearCache()
    {
        $limits = [5, 10, 15, 20];

        foreach ($limits as $limit) {
            Cache::delete("recommend_hot_{$limit}");
        }

        return true;
    }
}
