<?php
namespace app\service\tag;

use app\model\Article;
use think\facade\Db;
use think\facade\Cache;

/**
 * 个性化内容标签服务类
 * 处理个性化内容标签的数据查询
 */
class PersonalizeTagService
{
    /**
     * 获取个性化内容
     *
     * @param array $params 查询参数
     *   - userid: 用户ID
     *   - scene: 场景（home-首页，detail-详情页，search-搜索结果）
     *   - limit: 数量限制
     * @return array
     */
    public static function getList($params = [])
    {
        $userid = $params['userid'] ?? 0;
        $scene = $params['scene'] ?? 'home';
        $limit = $params['limit'] ?? 10;

        try {
            // 如果没有用户ID，返回通用推荐
            if (empty($userid)) {
                return self::getDefaultContent($limit);
            }

            // 获取用户画像
            $userProfile = self::getUserProfile($userid);

            // 根据场景返回个性化内容
            switch ($scene) {
                case 'home':
                    return self::getHomePersonalizedContent($userProfile, $limit);

                case 'detail':
                    return self::getDetailPersonalizedContent($userProfile, $limit);

                case 'search':
                    return self::getSearchPersonalizedContent($userProfile, $limit);

                default:
                    return self::getHomePersonalizedContent($userProfile, $limit);
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取用户画像
     *
     * @param int $userid 用户ID
     * @return array
     */
    private static function getUserProfile($userid)
    {
        // 缓存用户画像（1小时）
        $cacheKey = "user_profile_{$userid}";
        $profile = Cache::get($cacheKey);

        if (!empty($profile)) {
            return $profile;
        }

        $profile = [
            'user_id' => $userid,
            'interests' => [], // 兴趣分类
            'reading_time' => '', // 阅读时间偏好
            'content_length' => '', // 内容长度偏好
            'active_level' => 0, // 活跃度
            'recent_categories' => [], // 最近浏览的分类
            'favorite_tags' => [], // 喜欢的标签
        ];

        try {
            // 获取用户最近30天的浏览历史
            $viewHistory = Db::table('user_view_history')
                ->alias('uvh')
                ->leftJoin('articles a', 'uvh.article_id = a.id')
                ->where('uvh.user_id', $userid)
                ->whereTime('uvh.create_time', '>=', '-30 days')
                ->field('a.category_id, a.id, uvh.create_time, uvh.duration')
                ->select()
                ->toArray();

            // 分析兴趣分类
            $categoryCount = [];
            $totalViews = count($viewHistory);

            foreach ($viewHistory as $view) {
                if (!empty($view['category_id'])) {
                    $categoryCount[$view['category_id']] = ($categoryCount[$view['category_id']] ?? 0) + 1;
                }
            }

            // 按浏览次数排序
            arsort($categoryCount);
            $profile['interests'] = array_slice(array_keys($categoryCount), 0, 5);
            $profile['recent_categories'] = array_slice(array_keys($categoryCount), 0, 3);

            // 分析阅读时间偏好
            $hourCounts = [];
            foreach ($viewHistory as $view) {
                $hour = date('H', strtotime($view['create_time']));
                $hourCounts[$hour] = ($hourCounts[$hour] ?? 0) + 1;
            }

            if (!empty($hourCounts)) {
                arsort($hourCounts);
                $topHour = array_key_first($hourCounts);
                if ($topHour >= 6 && $topHour < 12) {
                    $profile['reading_time'] = 'morning';
                } elseif ($topHour >= 12 && $topHour < 18) {
                    $profile['reading_time'] = 'afternoon';
                } elseif ($topHour >= 18 && $topHour < 24) {
                    $profile['reading_time'] = 'evening';
                } else {
                    $profile['reading_time'] = 'night';
                }
            }

            // 计算活跃度
            $profile['active_level'] = min(100, $totalViews * 2);

            // 获取喜欢的标签
            $articleIds = array_column($viewHistory, 'id');
            if (!empty($articleIds)) {
                $tagCounts = Db::table('article_tags')
                    ->alias('at')
                    ->leftJoin('tags t', 'at.tag_id = t.id')
                    ->whereIn('at.article_id', $articleIds)
                    ->field('at.tag_id, t.name, COUNT(*) as tag_count')
                    ->group('at.tag_id')
                    ->order('tag_count', 'desc')
                    ->limit(10)
                    ->select()
                    ->toArray();

                $profile['favorite_tags'] = array_column($tagCounts, 'tag_id');
            }

            Cache::set($cacheKey, $profile, 3600);
        } catch (\Exception $e) {
            // 出错时返回基础画像
        }

        return $profile;
    }

    /**
     * 获取首页个性化内容
     *
     * @param array $userProfile 用户画像
     * @param int $limit 数量限制
     * @return array
     */
    private static function getHomePersonalizedContent($userProfile, $limit)
    {
        $query = Article::where('status', 1);

        // 优先推荐用户感兴趣的分类
        if (!empty($userProfile['interests'])) {
            $query->where(function($q) use ($userProfile) {
                $q->whereIn('category_id', $userProfile['interests']);
            });
        }

        // 排除用户最近已浏览的文章
        $recentViews = Db::table('user_view_history')
            ->where('user_id', $userProfile['user_id'])
            ->whereTime('create_time', '>=', '-7 days')
            ->column('article_id');

        if (!empty($recentViews)) {
            $query->whereNotIn('id', $recentViews);
        }

        // 综合排序：时间 + 热度
        $articles = $query->field('*, (view_count * 0.3 + like_count * 2 + UNIX_TIMESTAMP(create_time) / 10000) as score')
            ->order('score', 'desc')
            ->limit($limit * 2)
            ->select()
            ->toArray();

        // 添加个性化分数
        foreach ($articles as &$article) {
            $article['personalize_score'] = self::calculatePersonalizeScore($article, $userProfile);
        }

        // 按个性化分数排序
        usort($articles, function($a, $b) {
            return $b['personalize_score'] <=> $a['personalize_score'];
        });

        return array_slice($articles, 0, $limit);
    }

    /**
     * 获取详情页个性化内容
     *
     * @param array $userProfile 用户画像
     * @param int $limit 数量限制
     * @return array
     */
    private static function getDetailPersonalizedContent($userProfile, $limit)
    {
        // 详情页推荐：结合用户兴趣和当前内容相关性
        return self::getHomePersonalizedContent($userProfile, $limit);
    }

    /**
     * 获取搜索个性化内容
     *
     * @param array $userProfile 用户画像
     * @param int $limit 数量限制
     * @return array
     */
    private static function getSearchPersonalizedContent($userProfile, $limit)
    {
        // 搜索页推荐：基于用户历史搜索和浏览偏好
        return self::getHomePersonalizedContent($userProfile, $limit);
    }

    /**
     * 计算个性化分数
     *
     * @param array $article 文章
     * @param array $userProfile 用户画像
     * @return float
     */
    private static function calculatePersonalizeScore($article, $userProfile)
    {
        $score = 0;

        // 分类匹配度（权重40%）
        if (in_array($article['category_id'], $userProfile['interests'])) {
            $position = array_search($article['category_id'], $userProfile['interests']);
            $score += 40 * (1 - $position * 0.1);
        }

        // 时效性（权重30%）
        $daysSincePublish = (time() - strtotime($article['create_time'])) / 86400;
        if ($daysSincePublish < 1) {
            $score += 30;
        } elseif ($daysSincePublish < 7) {
            $score += 30 * (1 - $daysSincePublish / 7);
        } elseif ($daysSincePublish < 30) {
            $score += 15 * (1 - $daysSincePublish / 30);
        }

        // 热度（权重20%）
        $hotScore = ($article['view_count'] ?? 0) * 0.1 + ($article['like_count'] ?? 0) * 2;
        $score += min(20, $hotScore / 10);

        // 质量（权重10%）
        if (!empty($article['cover_image'])) {
            $score += 5;
        }
        if (mb_strlen($article['title'] ?? '', 'utf-8') >= 10) {
            $score += 5;
        }

        return $score;
    }

    /**
     * 获取默认内容（未登录用户）
     *
     * @param int $limit 数量限制
     * @return array
     */
    private static function getDefaultContent($limit)
    {
        // 返回热门和最新的混合内容
        $hotArticles = Article::where('status', 1)
            ->order('view_count', 'desc')
            ->limit($limit / 2)
            ->select()
            ->toArray();

        $newArticles = Article::where('status', 1)
            ->order('create_time', 'desc')
            ->limit($limit / 2)
            ->select()
            ->toArray();

        return array_merge($hotArticles, $newArticles);
    }

    /**
     * 记录用户行为
     *
     * @param int $userid 用户ID
     * @param string $action 行为类型（view-浏览，like-点赞，share-分享，comment-评论）
     * @param int $articleId 文章ID
     * @param array $extra 额外数据
     * @return bool
     */
    public static function recordBehavior($userid, $action, $articleId, $extra = [])
    {
        if (empty($userid) || empty($action) || empty($articleId)) {
            return false;
        }

        try {
            Db::table('user_behaviors')->insert([
                'user_id' => $userid,
                'action' => $action,
                'article_id' => $articleId,
                'extra_data' => json_encode($extra),
                'create_time' => date('Y-m-d H:i:s')
            ]);

            // 清除用户画像缓存
            Cache::delete("user_profile_{$userid}");

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取用户兴趣标签
     *
     * @param int $userid 用户ID
     * @return array
     */
    public static function getUserInterestTags($userid)
    {
        $profile = self::getUserProfile($userid);

        if (empty($profile['favorite_tags'])) {
            return [];
        }

        return Db::table('tags')
            ->whereIn('id', $profile['favorite_tags'])
            ->select()
            ->toArray();
    }

    /**
     * 更新用户画像
     *
     * @param int $userid 用户ID
     * @return bool
     */
    public static function updateUserProfile($userid)
    {
        // 清除缓存，强制重新生成画像
        Cache::delete("user_profile_{$userid}");

        // 重新生成画像
        self::getUserProfile($userid);

        return true;
    }

    /**
     * 获取用户阅读报告
     *
     * @param int $userid 用户ID
     * @param int $days 天数
     * @return array
     */
    public static function getUserReadingReport($userid, $days = 30)
    {
        try {
            $startDate = date('Y-m-d', strtotime("-{$days} days"));

            // 获取阅读数据
            $viewHistory = Db::table('user_view_history')
                ->where('user_id', $userid)
                ->where('create_time', '>=', $startDate)
                ->select()
                ->toArray();

            $report = [
                'total_reads' => count($viewHistory),
                'total_duration' => array_sum(array_column($viewHistory, 'duration')),
                'avg_duration' => 0,
                'most_active_day' => '',
                'most_active_hour' => '',
                'favorite_categories' => [],
                'reading_trend' => []
            ];

            if ($report['total_reads'] > 0) {
                $report['avg_duration'] = round($report['total_duration'] / $report['total_reads']);

                // 分析最活跃的日期
                $dayCounts = [];
                $hourCounts = [];

                foreach ($viewHistory as $view) {
                    $date = date('Y-m-d', strtotime($view['create_time']));
                    $hour = date('H', strtotime($view['create_time']));

                    $dayCounts[$date] = ($dayCounts[$date] ?? 0) + 1;
                    $hourCounts[$hour] = ($hourCounts[$hour] ?? 0) + 1;
                }

                arsort($dayCounts);
                arsort($hourCounts);

                $report['most_active_day'] = array_key_first($dayCounts) ?? '';
                $report['most_active_hour'] = array_key_first($hourCounts) ?? '';

                // 获取用户画像中的兴趣分类
                $profile = self::getUserProfile($userid);
                $report['favorite_categories'] = $profile['interests'];
            }

            return $report;
        } catch (\Exception $e) {
            return [];
        }
    }
}
