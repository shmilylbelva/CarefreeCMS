<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 站点模板配置模型
 */
class SiteTemplateConfig extends Model
{
    protected $name = 'site_template_config';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id' => 'integer',
        'site_id' => 'integer',
        'package_id' => 'integer',
        'is_active' => 'integer',
        'custom_config' => 'json',
    ];

    /**
     * 关联站点
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }

    /**
     * 关联模板包
     */
    public function package()
    {
        return $this->belongsTo(TemplatePackage::class, 'package_id', 'id');
    }

    /**
     * 获取站点当前激活的模板配置
     */
    public static function getActiveBySite($siteId)
    {
        return self::where('site_id', $siteId)
            ->where('is_active', 1)
            ->with('package')
            ->find();
    }

    /**
     * 激活该配置（同时停用该站点的其他配置）
     */
    public function activate()
    {
        // 停用该站点的所有其他配置
        self::where('site_id', $this->site_id)
            ->where('id', '<>', $this->id)
            ->update(['is_active' => 0]);

        // 激活当前配置
        $this->is_active = 1;
        return $this->save();
    }

    /**
     * 获取合并后的配置（默认配置 + 自定义配置）
     */
    public function getMergedConfig()
    {
        if (!$this->package) {
            return [];
        }

        $defaultConfig = $this->package->default_config ?? [];
        $customConfig = $this->custom_config ?? [];

        // 合并配置（自定义配置覆盖默认配置）
        return array_merge($defaultConfig, $customConfig);
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
     * 搜索器：模板包ID
     */
    public function searchPackageIdAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('package_id', $value);
        }
    }

    /**
     * 搜索器：是否激活
     */
    public function searchIsActiveAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('is_active', $value);
        }
    }
}
