<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 专题模型
 */
class Topic extends SiteModel
{
    use SoftDelete;

    protected $name = 'topics';
    protected $deleteTime = 'deleted_at';
    protected $defaultSoftDelete = null;

    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 状态常量
    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用

    /**
     * 关联文章（多对多）
     * 使用统一的 relations 表
     */
    public function articles()
    {
        return $this->belongsToMany(
            Article::class,
            'relations',
            'target_id',
            'source_id'
        )->where('pivot.source_type', 'topic')
         ->where('pivot.target_type', 'article')
         ->withField(['id', 'title', 'summary', 'cover_image', 'view_count', 'create_time'])
         ->order('pivot.sort', 'asc');
    }

    /**
     * 获取专题的文章列表（带分页）
     */
    public function getArticlesWithPagination($page = 1, $pageSize = 10)
    {
        return $this->articles()
            ->where('articles.status', Article::STATUS_PUBLISHED)
            ->where('articles.deleted_at', null)
            ->page($page, $pageSize)
            ->select();
    }

    /**
     * 添加文章到专题
     * 使用统一的 Relation 模型
     */
    public function addArticle($articleId, $sort = 0, $isFeatured = 0)
    {
        $relation = new Relation();
        $relation->source_type = 'topic';
        $relation->source_id = $this->id;
        $relation->target_type = 'article';
        $relation->target_id = $articleId;
        $relation->sort = $sort;
        $relation->extra = json_encode(['is_featured' => $isFeatured]);
        $relation->site_id = $this->site_id ?? 1;
        $relation->save();

        // 更新专题文章数量
        $this->updateArticleCount();
    }

    /**
     * 从专题移除文章
     * 使用统一的 Relation 模型
     */
    public function removeArticle($articleId)
    {
        Relation::where('source_type', 'topic')
            ->where('source_id', $this->id)
            ->where('target_type', 'article')
            ->where('target_id', $articleId)
            ->delete();

        // 更新专题文章数量
        $this->updateArticleCount();
    }

    /**
     * 批量设置专题文章
     * 使用统一的 Relation 模型
     */
    public function setArticles($articleIds)
    {
        // 删除现有关联
        Relation::where('source_type', 'topic')
            ->where('source_id', $this->id)
            ->where('target_type', 'article')
            ->delete();

        // 添加新关联
        foreach ($articleIds as $index => $articleId) {
            $this->addArticle($articleId, $index);
        }
    }

    /**
     * 更新文章数量
     * 使用统一的 Relation 模型
     */
    public function updateArticleCount()
    {
        $count = Relation::where('source_type', 'topic')
            ->where('source_id', $this->id)
            ->where('target_type', 'article')
            ->count();
        $this->article_count = $count;
        $this->save();
    }

    /**
     * 增加浏览次数
     */
    public function incrementViewCount()
    {
        $this->view_count++;
        $this->save();
    }

    /**
     * 获取所有状态
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用',
        ];
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = $data['status'] ?? 0;
        $list = self::getStatusList();
        return $list[$status] ?? '未知';
    }

    /**
     * URL别名修改器 - 自动转小写，替换空格为连字符
     */
    public function setSlugAttr($value)
    {
        return strtolower(str_replace(' ', '-', trim($value)));
    }
}
