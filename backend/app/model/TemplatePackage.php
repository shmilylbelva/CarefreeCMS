<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 模板包模型
 */
class TemplatePackage extends Model
{
    protected $name = 'template_packages';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id' => 'integer',
        'is_system' => 'integer',
        'is_global' => 'integer',
        'status' => 'integer',
        'sort' => 'integer',
        'config_schema' => 'json',
        'default_config' => 'json',
        'allowed_sites' => 'json',
    ];

    // 状态常量
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * 关联模板文件
     */
    public function templates()
    {
        return $this->hasMany(Template::class, 'package_id', 'id');
    }

    /**
     * 关联站点配置
     */
    public function siteConfigs()
    {
        return $this->hasMany(SiteTemplateConfig::class, 'package_id', 'id');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED  => '启用',
        ];
        return $status[$data['status']] ?? '未知';
    }

    /**
     * 获取是否系统内置文本
     */
    public function getIsSystemTextAttr($value, $data)
    {
        return $data['is_system'] ? '是' : '否';
    }

    /**
     * 获取是否全局可用文本
     */
    public function getIsGlobalTextAttr($value, $data)
    {
        return $data['is_global'] ? '是' : '否';
    }

    /**
     * 搜索器：名称
     */
    public function searchNameAttr($query, $value)
    {
        if ($value) {
            $query->where('name', 'like', '%' . $value . '%');
        }
    }

    /**
     * 搜索器：代码
     */
    public function searchCodeAttr($query, $value)
    {
        if ($value) {
            $query->where('code', 'like', '%' . $value . '%');
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
     * 搜索器：是否系统内置
     */
    public function searchIsSystemAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('is_system', $value);
        }
    }

    /**
     * 获取可用的模板包列表
     */
    public static function getAvailablePackages($siteId = null)
    {
        $query = self::where('status', self::STATUS_ENABLED);

        // 如果指定了站点ID，需要检查权限
        if ($siteId) {
            $query->where(function($q) use ($siteId) {
                $q->where('is_global', 1)
                  ->whereOr('allowed_sites', 'like', '%"' . $siteId . '"%');
            });
        } else {
            // 未指定站点，只返回全局可用的
            $query->where('is_global', 1);
        }

        return $query->order('sort', 'asc')->select();
    }

    /**
     * 检查站点是否有权限使用该模板包
     */
    public function canUseBySite($siteId)
    {
        // 全局可用
        if ($this->is_global) {
            return true;
        }

        // 检查是否在允许列表中
        $allowedSites = $this->allowed_sites ?? [];
        return in_array($siteId, $allowedSites);
    }
}
