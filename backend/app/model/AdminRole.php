<?php

namespace app\model;

use think\Model;
use app\traits\Cacheable;
use app\model\AdminUser;

/**
 * 角色模型
 */
class AdminRole extends Model
{
    use Cacheable;

    protected $name = 'admin_roles';

    protected $autoWriteTimestamp = true;

    protected $type = [
        'permissions' => 'json',
        'sort'        => 'integer',
        'status'      => 'integer',
    ];

    /**
     * 缓存配置
     */
    protected static $cacheTag = 'admin_roles';
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
     * 关联用户
     */
    public function users()
    {
        return $this->hasMany(AdminUser::class, 'role_id');
    }
}
