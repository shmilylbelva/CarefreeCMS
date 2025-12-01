<?php

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;
use app\traits\Cacheable;

/**
 * 站点模型
 */
class Site extends Model
{
    use SoftDelete, Cacheable;

    protected $name = 'sites';

    protected $autoWriteTimestamp = true;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = null;

    protected $type = [
        'id'                 => 'integer',
        'site_type'          => 'integer',
        'parent_site_id'     => 'integer',
        'domain_bind_type'   => 'integer',
        'template_id'        => 'integer',
        'config'             => 'json',
        'seo_config'         => 'json',
        'analytics_config'   => 'json',
        'static_enable'      => 'integer',
        'storage_type'       => 'integer',
        'storage_config'     => 'json',
        'status'             => 'integer',
        'sort'               => 'integer',
        'visit_count'        => 'integer',
        'article_count'      => 'integer',
        'user_count'         => 'integer',
    ];

    // 追加到模型数组的访问器
    protected $append = ['name'];

    // 站点类型常量
    const TYPE_MAIN = 1;        // 主站
    const TYPE_SUB = 2;         // 子站
    const TYPE_INDEPENDENT = 3; // 独立站

    // 域名绑定类型常量
    const DOMAIN_INDEPENDENT = 1; // 独立域名
    const DOMAIN_SUB = 2;         // 子域名
    const DOMAIN_DIRECTORY = 3;   // 目录

    // 状态常量
    const STATUS_DISABLED = 0;    // 禁用
    const STATUS_ENABLED = 1;     // 启用
    const STATUS_MAINTENANCE = 2; // 维护中

    // 存储类型常量
    const STORAGE_LOCAL = 1; // 本地
    const STORAGE_OSS = 2;   // OSS
    const STORAGE_COS = 3;   // COS

    /**
     * 缓存配置
     */
    protected static $cacheTag = 'sites';
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
     * 关联父站点
     */
    public function parent()
    {
        return $this->belongsTo(Site::class, 'parent_site_id', 'id');
    }

    /**
     * 关联子站点
     */
    public function children()
    {
        return $this->hasMany(Site::class, 'parent_site_id', 'id');
    }

    /**
     * 关联模板
     */
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'id');
    }

    /**
     * 关联站点管理员
     */
    public function admins()
    {
        return $this->belongsToMany(
            AdminUser::class,
            SiteAdmin::class,
            'admin_user_id',
            'site_id'
        )->withField(['role_type', 'permissions', 'status']);
    }

    /**
     * 关联文章
     */
    public function articles()
    {
        return $this->hasMany(Article::class, 'site_id', 'id');
    }

    /**
     * 关联分类
     */
    public function categories()
    {
        return $this->hasMany(Category::class, 'site_id', 'id');
    }

    /**
     * 搜索器：站点代码
     */
    public function searchSiteCodeAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('site_code', 'like', '%' . $value . '%');
        }
    }

    /**
     * 搜索器：站点名称
     */
    public function searchSiteNameAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('site_name', 'like', '%' . $value . '%');
        }
    }

    /**
     * 搜索器：站点类型
     */
    public function searchSiteTypeAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('site_type', $value);
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
     * 搜索器：地域代码
     */
    public function searchRegionCodeAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('region_code', $value);
        }
    }

    /**
     * 搜索器：城市
     */
    public function searchCityAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('city', 'like', '%' . $value . '%');
        }
    }


    /**
     * 获取器：站点名称（将 site_name 映射为 name）
     */
    public function getNameAttr($value, $data)
    {
        return $data['site_name'] ?? '';
    }

    /**
     * 获取器：站点类型文本
     */
    public function getSiteTypeTextAttr($value, $data)
    {
        $types = [
            self::TYPE_MAIN        => '主站',
            self::TYPE_SUB         => '子站',
            self::TYPE_INDEPENDENT => '独立站',
        ];
        return $types[$data['site_type']] ?? '未知';
    }

    /**
     * 获取器：状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $statuses = [
            self::STATUS_DISABLED    => '禁用',
            self::STATUS_ENABLED     => '启用',
            self::STATUS_MAINTENANCE => '维护中',
        ];
        return $statuses[$data['status']] ?? '未知';
    }

    /**
     * 获取器：域名绑定类型文本
     */
    public function getDomainBindTypeTextAttr($value, $data)
    {
        $types = [
            self::DOMAIN_INDEPENDENT => '独立域名',
            self::DOMAIN_SUB         => '子域名',
            self::DOMAIN_DIRECTORY   => '目录',
        ];
        return $types[$data['domain_bind_type']] ?? '未知';
    }

    /**
     * 获取器：完整域名
     */
    public function getFullDomainAttr($value, $data)
    {
        // 如果有网站网址，直接返回
        if (!empty($data['site_url'])) {
            return $data['site_url'];
        }

        // 如果是子域名模式，返回子域名
        if ($data['domain_bind_type'] == self::DOMAIN_SUB && !empty($data['sub_domain'])) {
            // 需要从配置中获取主域名
            $mainDomain = config('app.main_domain', '');
            return $data['sub_domain'] . '.' . $mainDomain;
        }

        return '';
    }

    /**
     * 修改器：站点代码（转换为小写）
     */
    public function setSiteCodeAttr($value)
    {
        return strtolower(trim($value));
    }

    /**
     * 修改器：子域名（转换为小写）
     */
    public function setSubDomainAttr($value)
    {
        return $value ? strtolower(trim($value)) : null;
    }

    /**
     * 获取所有启用的站点（带缓存）
     */
    public static function getEnabledSites()
    {
        return static::getCachedList('enabled_sites', function () {
            return self::where('status', self::STATUS_ENABLED)
                ->order('sort', 'asc')
                ->order('id', 'asc')
                ->select();
        }, ['status' => self::STATUS_ENABLED]);
    }

    /**
     * 根据站点代码获取站点（带缓存）
     */
    public static function getBySiteCode($siteCode)
    {
        return static::cacheRemember("site_code:{$siteCode}", function () use ($siteCode) {
            return self::where('site_code', $siteCode)
                ->where('status', self::STATUS_ENABLED)
                ->find();
        });
    }

    /**
     * 根据地域代码获取站点（带缓存）
     */
    public static function getByRegionCode($regionCode)
    {
        return static::cacheRemember("region_code:{$regionCode}", function () use ($regionCode) {
            return self::where('region_code', $regionCode)
                ->where('status', self::STATUS_ENABLED)
                ->find();
        });
    }

    /**
     * 获取主站（带缓存）
     */
    public static function getMainSite()
    {
        return static::cacheRemember('main_site', function () {
            return self::where('site_type', self::TYPE_MAIN)
                ->where('status', self::STATUS_ENABLED)
                ->find();
        });
    }

    /**
     * 增加访问量
     */
    public function incrementVisitCount($count = 1)
    {
        return $this->inc('visit_count', $count)->update();
    }

    /**
     * 更新文章数
     */
    public function updateArticleCount()
    {
        $count = Article::where('site_id', $this->id)->count();
        return $this->save(['article_count' => $count]);
    }

    /**
     * 更新用户数
     */
    public function updateUserCount()
    {
        $count = FrontUser::where('site_id', $this->id)->count();
        return $this->save(['user_count' => $count]);
    }

    /**
     * 获取器：SEO标题
     */
    public function getSeoTitleAttr($value, $data)
    {
        $seoConfig = $data['seo_config'] ?? [];
        return is_array($seoConfig) ? ($seoConfig['seo_title'] ?? '') : '';
    }

    /**
     * 获取器：SEO关键词
     */
    public function getSeoKeywordsAttr($value, $data)
    {
        $seoConfig = $data['seo_config'] ?? [];
        return is_array($seoConfig) ? ($seoConfig['seo_keywords'] ?? '') : '';
    }

    /**
     * 获取器：SEO描述
     */
    public function getSeoDescriptionAttr($value, $data)
    {
        $seoConfig = $data['seo_config'] ?? [];
        return is_array($seoConfig) ? ($seoConfig['seo_description'] ?? '') : '';
    }

    /**
     * 修改器：SEO标题（写入 seo_config JSON）
     */
    public function setSeoTitleAttr($value)
    {
        $seoConfig = $this->getData('seo_config') ?: [];
        if (!is_array($seoConfig)) {
            $seoConfig = [];
        }
        $seoConfig['seo_title'] = $value;
        $this->set('seo_config', $seoConfig);
    }

    /**
     * 修改器：SEO关键词（写入 seo_config JSON）
     */
    public function setSeoKeywordsAttr($value)
    {
        $seoConfig = $this->getData('seo_config') ?: [];
        if (!is_array($seoConfig)) {
            $seoConfig = [];
        }
        $seoConfig['seo_keywords'] = $value;
        $this->set('seo_config', $seoConfig);
    }

    /**
     * 修改器：SEO描述（写入 seo_config JSON）
     */
    public function setSeoDescriptionAttr($value)
    {
        $seoConfig = $this->getData('seo_config') ?: [];
        if (!is_array($seoConfig)) {
            $seoConfig = [];
        }
        $seoConfig['seo_description'] = $value;
        $this->set('seo_config', $seoConfig);
    }

    /**
     * 获取器：首页模板
     */
    public function getIndexTemplateAttr($value, $data)
    {
        $config = $data['config'] ?? [];
        return is_array($config) ? ($config['index_template'] ?? 'index') : 'index';
    }

    /**
     * 获取器：回收站开关
     */
    public function getRecycleBinEnableAttr($value, $data)
    {
        $config = $data['config'] ?? [];
        return is_array($config) ? ($config['recycle_bin_enable'] ?? 'open') : 'open';
    }

    /**
     * 获取器：文档副栏目开关
     */
    public function getArticleSubCategoryAttr($value, $data)
    {
        $config = $data['config'] ?? [];
        return is_array($config) ? ($config['article_sub_category'] ?? 'close') : 'close';
    }
}
