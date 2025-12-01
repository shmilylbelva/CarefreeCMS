<?php

namespace app\model;

use think\model\concern\SoftDelete;
use app\traits\Cacheable;

/**
 * 分类模型
 */
class Category extends SiteModel
{
    use SoftDelete, Cacheable;

    protected $name = 'categories';
    protected $pk = 'id';  // 明确指定主键

    protected $autoWriteTimestamp = true;
    protected $deleteTime = 'deleted_at';
    protected $defaultSoftDelete = null;

    protected $type = [
        'parent_id' => 'integer',
        'sort'      => 'integer',
        'status'    => 'integer',
    ];

    /**
     * 缓存配置
     */
    protected static $cacheTag = 'categories';
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
     * 关联父分类
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id', 'id');
    }

    /**
     * 关联子分类
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * 关联文章
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    /**
     * 搜索器：分类名称
     */
    public function searchNameAttr($query, $value)
    {
        $query->where('name', 'like', '%' . $value . '%');
    }

    /**
     * 搜索器：父分类
     */
    public function searchParentIdAttr($query, $value)
    {
        $query->where('parent_id', $value);
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
