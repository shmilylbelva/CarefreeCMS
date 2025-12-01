<?php

namespace app\model;

use think\model\concern\SoftDelete;
use app\traits\Cacheable;

/**
 * 标签模型
 */
class Tag extends SiteModel
{
    use SoftDelete, Cacheable;

    protected $name = 'tags';
    protected $pk = 'id';  // 明确指定主键

    protected $autoWriteTimestamp = true;
    protected $deleteTime = 'deleted_at';
    protected $defaultSoftDelete = null;

    protected $type = [
        'article_count' => 'integer',
        'sort'          => 'integer',
        'status'        => 'integer',
    ];

    /**
     * 缓存配置
     */
    protected static $cacheTag = 'tags';
    protected static $cacheExpire = 3600; // 1小时

    /**
     * 模型事件：数据插入后
     */
    protected static function onAfterInsert($model)
    {
        static::clearCacheTag();
    }

    /**
     * 模型事件：数据更新后
     */
    protected static function onAfterUpdate($model)
    {
        static::clearCacheTag();
    }

    /**
     * 模型事件：数据删除后
     */
    protected static function onAfterDelete($model)
    {
        static::clearCacheTag();
    }

    /**
     * 关联文章（多对多）
     * 使用统一的 relations 表
     */
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'relations', 'source_id', 'target_id')
            ->where('pivot.source_type', 'article')
            ->where('pivot.target_type', 'tag');
    }

    /**
     * 搜索器：标签名称
     */
    public function searchNameAttr($query, $value)
    {
        $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * 搜索器：状态
     */
    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }

    /**
     * 搜索器：站点ID
     */
    public function searchSiteIdAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('site_id', $value);
        }
    }
}
