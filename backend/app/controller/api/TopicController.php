<?php
declare (strict_types = 1);

namespace app\controller\api;

use app\model\Topic;
use app\model\Relation;
use app\model\Article;
use think\Request;
use think\facade\Validate;

/**
 * 专题管理控制器
 */
class TopicController extends BaseController
{
    /**
     * 获取专题列表
     */
    public function index(Request $request)
    {
        $page = $request->param('page', 1);
        $pageSize = $request->param('page_size', 10);
        $keyword = $request->param('keyword', '');
        $status = $request->param('status', '');
        $isRecommended = $request->param('is_recommended', '');
        $siteId = $request->param('site_id', '');

        // 禁用自动站点过滤，允许查看所有站点
        $query = Topic::withoutSiteScope()
            ->with(['site'])
            ->order('sort', 'asc')
            ->order('id', 'desc');

        // 关键词搜索
        if (!empty($keyword)) {
            $query->where('name|description', 'like', '%' . $keyword . '%');
        }

        // 状态筛选
        if ($status !== '') {
            $query->where('status', $status);
        }

        // 推荐筛选
        if ($isRecommended !== '') {
            $query->where('is_recommended', $isRecommended);
        }

        // 站点筛选
        if ($siteId !== '') {
            $query->where('topics.site_id', $siteId);
        }

        $list = $query->paginate([
            'list_rows' => $pageSize,
            'page' => $page,
        ]);

        return $this->success([
            'list' => $list->items(),
            'total' => $list->total(),
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * 获取所有专题（不分页）
     */
    public function all(Request $request)
    {
        $siteId = $request->param('site_id', '');
        $siteIds = $request->param('site_ids', '');

        $query = Topic::withoutSiteScope()
            ->where('status', Topic::STATUS_ENABLED)
            ->order('sort', 'asc')
            ->order('id', 'desc');

        // 支持多站点筛选（site_ids 参数优先）
        if ($siteIds !== '') {
            // site_ids 是逗号分隔的字符串，如 "1,2,3"
            $siteIdArray = array_filter(array_map('intval', explode(',', $siteIds)));
            if (!empty($siteIdArray)) {
                $query->whereIn('topics.site_id', $siteIdArray);
            }
        } elseif ($siteId !== '') {
            // 兼容单个 site_id 参数
            $query->where('topics.site_id', $siteId);
        }

        $list = $query->select();

        return $this->success([
            'list' => $list,
        ]);
    }

    /**
     * 获取专题详情
     */
    public function read($id)
    {
        $topic = Topic::withoutSiteScope()->find($id);

        if (!$topic) {
            return $this->error('专题不存在');
        }

        return $this->success($topic);
    }

    /**
     * 创建专题
     */
    public function save(Request $request)
    {
        $allData = $request->post();

        $data = $request->only([
            'name',
            'slug',
            'description',
            'cover_image',
            'template',
            'seo_title',
            'seo_keywords',
            'seo_description',
            'is_recommended',
            'status',
            'sort',
        ]);

        // 多站点支持：获取站点IDs（数组或单个值）
        $siteIds = [];
        if (isset($allData['site_ids']) && is_array($allData['site_ids']) && !empty($allData['site_ids'])) {
            $siteIds = $allData['site_ids'];
        } elseif (isset($allData['site_id'])) {
            $siteIds = [$allData['site_id']];
        } else {
            $siteIds = [1];
        }

        // 数据验证
        $validate = Validate::rule([
            'name' => 'require|max:100',
            'slug' => 'require|max:100|regex:^[a-z0-9\-]+$',
            'template' => 'max:100',
            'seo_title' => 'max:200',
            'seo_keywords' => 'max:255',
            'seo_description' => 'max:500',
        ])->message([
            'name.require' => '专题名称不能为空',
            'name.max' => '专题名称最多100个字符',
            'slug.require' => 'URL别名不能为空',
            'slug.max' => 'URL别名最多100个字符',
            'slug.regex' => 'URL别名只能包含小写字母、数字和连字符',
        ]);

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        // 设置默认值
        if (!isset($data['template'])) {
            $data['template'] = 'topic_default';
        }
        if (!isset($data['is_recommended'])) {
            $data['is_recommended'] = 0;
        }
        if (!isset($data['status'])) {
            $data['status'] = Topic::STATUS_ENABLED;
        }
        if (!isset($data['sort'])) {
            $data['sort'] = 0;
        }

        try {
            $createdTopics = [];
            $sourceId = null;

            // 为每个站点创建专题副本
            foreach ($siteIds as $index => $siteId) {
                $topicData = $data;
                $topicData['site_id'] = $siteId;

                // 检查同 slug 专题
                $exists = Topic::where('slug', $topicData['slug'])
                    ->where('site_id', $siteId)
                    ->find();
                if ($exists) {
                    throw new \Exception("站点ID {$siteId} 下已存在相同URL别名的专题");
                }

                // 第一个是主记录，后续记录设置 source_id
                if ($index > 0 && $sourceId) {
                    $topicData['source_id'] = $sourceId;
                }

                $topic = new Topic();
                $topic->save($topicData);

                // 第一个记录作为源记录
                if ($index === 0) {
                    $sourceId = $topic->id;
                }

                $createdTopics[] = $topic;
            }

            $message = count($createdTopics) > 1
                ? "专题创建成功，已为 " . count($createdTopics) . " 个站点创建副本"
                : '创建成功';

            return $this->success([
                'id' => $createdTopics[0]->id,
                'count' => count($createdTopics),
                'ids' => array_map(fn($t) => $t->id, $createdTopics)
            ], $message);
        } catch (\Exception $e) {
            return $this->error('专题创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新专题
     */
    public function update(Request $request, $id)
    {
        $topic = Topic::withoutSiteScope()->find($id);

        if (!$topic) {
            return $this->error('专题不存在');
        }

        // 关键修复：确保模型实例也禁用站点过滤
        $reflection = new \ReflectionObject($topic);
        if ($reflection->hasProperty('multiSiteEnabled')) {
            $property = $reflection->getProperty('multiSiteEnabled');
            $property->setAccessible(true);
            $property->setValue($topic, false);
        }

        $data = $request->only([
            'name',
            'slug',
            'description',
            'cover_image',
            'template',
            'seo_title',
            'seo_keywords',
            'seo_description',
            'is_recommended',
            'status',
            'sort',
        ]);

        // 数据验证
        $validate = Validate::rule([
            'name' => 'require|max:100',
            'slug' => 'require|max:100|regex:^[a-z0-9\-]+$|unique:topics,slug,' . $id,
            'template' => 'max:100',
            'seo_title' => 'max:200',
            'seo_keywords' => 'max:255',
            'seo_description' => 'max:500',
        ])->message([
            'name.require' => '专题名称不能为空',
            'name.max' => '专题名称最多100个字符',
            'slug.require' => 'URL别名不能为空',
            'slug.max' => 'URL别名最多100个字符',
            'slug.regex' => 'URL别名只能包含小写字母、数字和连字符',
            'slug.unique' => 'URL别名已存在',
        ]);

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        try {
            // 使用Db类直接更新，确保WHERE条件精确，只更新指定ID的记录
            $affected = \think\facade\Db::name('topics')
                ->where('id', '=', $id)
                ->limit(1)
                ->update($data);

            if ($affected === 0) {
                return $this->error('更新失败：未找到该记录或数据未改变');
            }

            return $this->success(['affected' => $affected], '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除专题
     */
    public function delete($id)
    {
        $topic = Topic::withoutSiteScope()->find($id);

        if (!$topic) {
            return $this->error('专题不存在');
        }

        // 删除专题文章关联
        Relation::where('source_type', 'topic')
            ->where('source_id', $id)
            ->where('target_type', 'article')
            ->delete();

        try {
            // 使用Db类直接执行软删除，确保只删除指定ID的记录
            $affected = \think\facade\Db::name('topics')
                ->where('id', '=', $id)
                ->limit(1)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            if ($affected === 0) {
                return $this->error('专题删除失败：未找到该专题');
            }

            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 获取专题的文章列表
     */
    public function articles(Request $request, $id)
    {
        $page = $request->param('page', 1);
        $pageSize = $request->param('page_size', 10);

        $topic = Topic::withoutSiteScope()->find($id);

        if (!$topic) {
            return $this->error('专题不存在');
        }

        // 获取关联的文章ID
        $topicArticles = Relation::where('source_type', 'topic')
            ->where('source_id', $id)
            ->where('target_type', 'article')
            ->order('sort', 'asc')
            ->select();

        $articleIds = $topicArticles->column('target_id');

        if (empty($articleIds)) {
            return $this->success([
                'list' => [],
                'total' => 0,
                'page' => $page,
                'page_size' => $pageSize,
            ]);
        }

        // 查询文章详情
        $query = Article::withoutSiteScope()
            ->whereIn('id', $articleIds)
            ->with(['category', 'user'])
            ->order('id', 'desc');

        $list = $query->paginate([
            'list_rows' => $pageSize,
            'page' => $page,
        ]);

        // 附加排序和精选信息
        foreach ($list->items() as &$article) {
            $relation = $topicArticles->where('target_id', $article->id)->first();
            $article->topic_sort = $relation ? $relation->sort : 0;
            // extra字段已被ThinkPHP自动反序列化为数组
            $extra = $relation && $relation->extra ? $relation->extra : [];
            $article->is_featured = is_array($extra) ? ($extra['is_featured'] ?? 0) : 0;
        }

        return $this->success([
            'list' => $list->items(),
            'total' => $list->total(),
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * 添加文章到专题
     */
    public function addArticle(Request $request, $id)
    {
        $topic = Topic::withoutSiteScope()->find($id);

        if (!$topic) {
            return $this->error('专题不存在');
        }

        $articleId = $request->param('article_id');
        $sort = $request->param('sort', 0);
        $isFeatured = $request->param('is_featured', 0);

        // 检查文章是否存在
        $article = Article::withoutSiteScope()->find($articleId);
        if (!$article) {
            return $this->error('文章不存在');
        }

        // 检查是否已关联
        $exists = Relation::where('source_type', 'topic')
            ->where('source_id', $id)
            ->where('target_type', 'article')
            ->where('target_id', $articleId)
            ->find();

        if ($exists) {
            return $this->error('文章已在该专题中');
        }

        // 添加关联
        Relation::create([
            'source_type' => 'topic',
            'source_id' => $id,
            'target_type' => 'article',
            'target_id' => $articleId,
            'sort' => $sort,
            // ThinkPHP会自动序列化数组为JSON
            'extra' => ['is_featured' => $isFeatured],
            'site_id' => 1, // 默认站点ID，可根据需求调整
        ]);

        return $this->success(null, '添加成功');
    }

    /**
     * 从专题移除文章
     */
    public function removeArticle(Request $request, $id, $article_id = null)
    {
        $topic = Topic::withoutSiteScope()->find($id);

        if (!$topic) {
            return $this->error('专题不存在');
        }

        // 支持两种方式：URL参数或body参数（向后兼容）
        $articleId = $article_id ?? $request->param('article_id');

        if (!$articleId) {
            return $this->error('文章ID不能为空');
        }

        // 移除关联
        Relation::where('source_type', 'topic')
            ->where('source_id', $id)
            ->where('target_type', 'article')
            ->where('target_id', $articleId)
            ->delete();

        return $this->success(null, '移除成功');
    }

    /**
     * 批量设置专题文章
     */
    public function setArticles(Request $request, $id)
    {
        $topic = Topic::withoutSiteScope()->find($id);

        if (!$topic) {
            return $this->error('专题不存在');
        }

        $articleIds = $request->param('article_ids', []);

        if (!is_array($articleIds)) {
            return $this->error('文章ID必须是数组');
        }

        // 删除原有关联
        Relation::where('source_type', 'topic')
            ->where('source_id', $id)
            ->where('target_type', 'article')
            ->delete();

        // 批量添加新关联
        if (!empty($articleIds)) {
            $relations = [];
            foreach ($articleIds as $index => $articleId) {
                $relations[] = [
                    'source_type' => 'topic',
                    'source_id' => $id,
                    'target_type' => 'article',
                    'target_id' => $articleId,
                    'sort' => $index,
                    'site_id' => 1,
                ];
            }
            (new Relation())->saveAll($relations);
        }

        return $this->success(null, '设置成功');
    }

    /**
     * 更新文章在专题中的排序
     */
    public function updateArticleSort(Request $request, $id, $article_id = null)
    {
        $topic = Topic::withoutSiteScope()->find($id);

        if (!$topic) {
            return $this->error('专题不存在');
        }

        // 支持两种方式：URL参数或body参数（向后兼容）
        $articleId = $article_id ?? $request->param('article_id');
        $sort = $request->param('sort', 0);

        if (!$articleId) {
            return $this->error('文章ID不能为空');
        }

        Relation::updateTopicArticleSort($id, $articleId, $sort);

        return $this->success(null, '排序更新成功');
    }

    /**
     * 设置文章为精选
     */
    public function setArticleFeatured(Request $request, $id, $article_id = null)
    {
        $topic = Topic::withoutSiteScope()->find($id);

        if (!$topic) {
            return $this->error('专题不存在');
        }

        // 支持两种方式：URL参数或body参数（向后兼容）
        $articleId = $article_id ?? $request->param('article_id');
        $isFeatured = $request->param('is_featured', 1);

        if (!$articleId) {
            return $this->error('文章ID不能为空');
        }

        // 更新关联的精选状态
        $relation = Relation::where('source_type', 'topic')
            ->where('source_id', $id)
            ->where('target_type', 'article')
            ->where('target_id', $articleId)
            ->find();

        if (!$relation) {
            return $this->error('文章不在该专题中');
        }

        // extra字段已被ThinkPHP自动反序列化为数组，无需json_decode
        $extra = is_array($relation->extra) ? $relation->extra : [];
        $extra['is_featured'] = $isFeatured;
        // ThinkPHP会自动序列化为JSON，无需json_encode
        $relation->extra = $extra;
        $relation->save();

        return $this->success(null, '设置成功');
    }
}
