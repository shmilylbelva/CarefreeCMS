<?php

namespace app\model;

use think\Model;

/**
 * 站点管理员关联模型
 */
class SiteAdmin extends Model
{
    protected $name = 'site_admins';

    protected $autoWriteTimestamp = true;

    protected $type = [
        'id'            => 'integer',
        'site_id'       => 'integer',
        'admin_user_id' => 'integer',
        'role_type'     => 'integer',
        'permissions'   => 'json',
        'status'        => 'integer',
    ];

    // 角色类型常量
    const ROLE_ADMIN = 1;    // 站点管理员
    const ROLE_EDITOR = 2;   // 站点编辑
    const ROLE_AUDITOR = 3;  // 站点审核员

    // 状态常量
    const STATUS_DISABLED = 0; // 禁用
    const STATUS_ENABLED = 1;  // 启用

    /**
     * 关联站点
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }

    /**
     * 关联管理员用户
     */
    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id', 'id');
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

    /**
     * 搜索器：管理员用户ID
     */
    public function searchAdminUserIdAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('admin_user_id', $value);
        }
    }

    /**
     * 搜索器：角色类型
     */
    public function searchRoleTypeAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('role_type', $value);
        }
    }

    /**
     * 搜索器：状态
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('status', $value);
        }
    }

    /**
     * 获取器：角色类型文本
     */
    public function getRoleTypeTextAttr($value, $data)
    {
        $roles = [
            self::ROLE_ADMIN   => '站点管理员',
            self::ROLE_EDITOR  => '站点编辑',
            self::ROLE_AUDITOR => '站点审核员',
        ];
        return $roles[$data['role_type']] ?? '未知';
    }

    /**
     * 获取器：状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $statuses = [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED  => '启用',
        ];
        return $statuses[$data['status']] ?? '未知';
    }

    /**
     * 检查管理员是否有站点权限
     */
    public static function hasAccess($adminUserId, $siteId)
    {
        return self::where('admin_user_id', $adminUserId)
            ->where('site_id', $siteId)
            ->where('status', self::STATUS_ENABLED)
            ->count() > 0;
    }

    /**
     * 获取管理员管理的所有站点
     */
    public static function getAdminSites($adminUserId)
    {
        return self::where('admin_user_id', $adminUserId)
            ->where('status', self::STATUS_ENABLED)
            ->with('site')
            ->select();
    }

    /**
     * 获取站点的所有管理员
     */
    public static function getSiteAdmins($siteId)
    {
        return self::where('site_id', $siteId)
            ->where('status', self::STATUS_ENABLED)
            ->with('adminUser')
            ->select();
    }
}
