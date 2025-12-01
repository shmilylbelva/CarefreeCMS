<?php

namespace app\model;

use think\Model;
use app\traits\Cacheable;
use app\model\AdminRole;

/**
 * 管理员用户模型
 */
class AdminUser extends Model
{
    use Cacheable;

    // 设置表名
    protected $name = 'admin_users';

    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'username'        => 'string',
        'password'        => 'string',
        'real_name'       => 'string',
        'email'           => 'string',
        'phone'           => 'string',
        'avatar'          => 'string',
        'role_id'         => 'int',
        'status'          => 'int',
        'last_login_time' => 'datetime',
        'last_login_ip'   => 'string',
        'create_time'     => 'datetime',
        'update_time'     => 'datetime',
    ];

    // 自动时间戳
    protected $autoWriteTimestamp = true;

    // 隐藏字段（不在JSON中显示）
    protected $hidden = ['password'];

    // 只读字段
    protected $readonly = ['username'];

    // 类型转换
    protected $type = [
        'role_id' => 'integer',
        'status'  => 'integer',
    ];

    /**
     * 缓存配置
     */
    protected static $cacheTag = 'admin_users';
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
     * 关联角色
     */
    public function role()
    {
        return $this->belongsTo(AdminRole::class, 'role_id', 'id');
    }

    /**
     * 搜索器：用户名
     */
    public function searchUsernameAttr($query, $value)
    {
        $query->where('username', 'like', '%' . $value . '%');
    }

    /**
     * 搜索器：状态
     */
    public function searchStatusAttr($query, $value)
    {
        $query->where('status', $value);
    }

    /**
     * 搜索器：角色
     */
    public function searchRoleIdAttr($query, $value)
    {
        $query->where('role_id', $value);
    }

    /**
     * 修改器：密码加密
     */
    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * 验证密码
     * @param string $password 输入的密码
     * @return bool
     */
    public function checkPassword(string $password): bool
    {
        return password_verify($password, $this->getData('password'));
    }

    /**
     * 获取器：状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [0 => '禁用', 1 => '启用'];
        return $status[$data['status']] ?? '未知';
    }

    /**
     * 获取用户权限（带缓存）
     *
     * @param int $userId
     * @return array
     */
    public static function getUserPermissions(int $userId): array
    {
        return static::cacheRemember("user_permissions:{$userId}", function () use ($userId) {
            $user = static::with('role')->find($userId);

            if (!$user || !$user->role) {
                return [];
            }

            // 获取权限（AdminRole模型已自动将JSON转为数组）
            $permissions = $user->role->permissions ?? [];
            return is_array($permissions) ? $permissions : [];
        }, 3600);
    }

    /**
     * 检查用户是否有指定权限（带缓存）
     *
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    public static function hasPermission(int $userId, string $permission): bool
    {
        $permissions = static::getUserPermissions($userId);
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }

    /**
     * 清除用户权限缓存
     *
     * @param int $userId
     * @return bool
     */
    public static function clearUserPermissionsCache(int $userId): bool
    {
        return static::cacheDelete("user_permissions:{$userId}");
    }
}
