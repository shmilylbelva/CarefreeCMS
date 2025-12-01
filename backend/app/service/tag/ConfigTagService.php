<?php
namespace app\service\tag;

use app\model\Site;
use app\model\SiteTemplateConfig;
use app\service\SiteContextService;
use think\facade\Cache;

/**
 * 配置标签服务类
 * 处理网站配置标签的数据查询
 *
 * 支持多站点，按优先级获取配置：
 * 1. 全局模板变量（静态生成时传递）
 * 2. 站点自定义配置（SiteTemplateConfig.custom_config）
 * 3. 模板包默认配置（TemplatePackage.default_config）
 * 4. 站点基础字段（Site模型字段，如 logo, seo_config等）
 */
class ConfigTagService
{
    /**
     * 获取配置值
     *
     * @param string $name 配置名称，支持点号分隔的嵌套访问（如 seo.keywords）
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($name, $default = '')
    {
        // 1. 优先从全局变量获取（静态生成场景）
        // Build控制器在渲染模板前会设置此变量
        global $__template_site_config__;
        if (isset($__template_site_config__) && is_array($__template_site_config__)) {
            $value = self::getNestedValue($__template_site_config__, $name);
            if ($value !== null) {
                return $value;
            }
        }

        // 2. 从站点上下文获取（动态渲染场景）
        $site = SiteContextService::getSite();
        if (!$site) {
            return $default;
        }

        // 缓存键包含站点ID以隔离不同站点的配置
        $cacheKey = 'site_template_config_' . $site->id . '_' . $name;
        $cachedValue = Cache::get($cacheKey);

        if ($cachedValue !== false && $cachedValue !== null) {
            return $cachedValue;
        }

        // 3. 查询站点模板配置
        $siteConfig = SiteTemplateConfig::where('site_id', $site->id)
            ->with('package')
            ->find();

        $value = null;

        if ($siteConfig) {
            // 3.1 优先从站点自定义配置获取
            $customConfig = $siteConfig->custom_config ?? [];
            $value = self::getNestedValue($customConfig, $name);

            // 3.2 如果没有，从模板包默认配置获取
            if ($value === null && $siteConfig->package) {
                $defaultConfig = $siteConfig->package->default_config ?? [];
                $value = self::getNestedValue($defaultConfig, $name);
            }
        }

        // 4. 如果都没有，尝试从站点基础字段获取
        if ($value === null) {
            $value = self::getSiteFieldValue($site, $name);
        }

        // 5. 仍然没有则使用默认值
        if ($value === null) {
            $value = $default;
        }

        // 缓存配置值（缓存1小时）
        if ($value !== $default) {
            Cache::set($cacheKey, $value, 3600);
        }

        return $value;
    }

    /**
     * 获取所有配置
     * 返回合并后的完整配置数组
     *
     * @return array
     */
    public static function getAll()
    {
        // 1. 优先从全局变量获取（静态生成场景）
        global $__template_site_config__;
        if (isset($__template_site_config__) && is_array($__template_site_config__)) {
            return $__template_site_config__;
        }

        // 2. 从站点上下文获取（动态渲染场景）
        $site = SiteContextService::getSite();
        if (!$site) {
            return [];
        }

        $cacheKey = 'site_template_config_all_' . $site->id;
        $cachedConfigs = Cache::get($cacheKey);

        if ($cachedConfigs !== false && $cachedConfigs !== null) {
            return $cachedConfigs;
        }

        // 查询站点模板配置
        $siteConfig = SiteTemplateConfig::where('site_id', $site->id)
            ->with('package')
            ->find();

        $configs = [];

        if ($siteConfig) {
            // 获取模板包默认配置
            $defaultConfig = $siteConfig->package ? ($siteConfig->package->default_config ?? []) : [];

            // 获取站点自定义配置
            $customConfig = $siteConfig->custom_config ?? [];

            // 合并配置（自定义覆盖默认）
            $configs = array_merge($defaultConfig, $customConfig);
        }

        // 添加站点基础字段
        $configs = array_merge($configs, self::getSiteFieldsArray($site));

        // 缓存配置（缓存1小时）
        Cache::set($cacheKey, $configs, 3600);

        return $configs;
    }

    /**
     * 获取嵌套数组的值（支持点号分隔）
     * 例如：seo.keywords 会从数组中获取 $array['seo']['keywords']
     *
     * @param array $array 数组
     * @param string $key 键名，支持点号分隔
     * @return mixed|null
     */
    protected static function getNestedValue($array, $key)
    {
        if (!is_array($array)) {
            return null;
        }

        // 如果直接存在该键
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        // 支持点号分隔的嵌套访问
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $array;

            foreach ($keys as $k) {
                if (!is_array($value) || !array_key_exists($k, $value)) {
                    return null;
                }
                $value = $value[$k];
            }

            return $value;
        }

        return null;
    }

    /**
     * 从Site模型获取字段值
     * 支持的字段：logo, site_name, site_code, seo_config, analytics_config等
     *
     * @param Site $site 站点对象
     * @param string $name 字段名
     * @return mixed|null
     */
    protected static function getSiteFieldValue($site, $name)
    {
        // 直接字段访问
        $directFields = ['logo', 'site_name', 'site_code', 'domain',
                        'site_title', 'site_keywords', 'site_description'];

        if (in_array($name, $directFields) && isset($site->$name)) {
            return $site->$name;
        }

        // JSON字段访问（支持嵌套）
        $jsonFields = ['seo_config', 'analytics_config', 'config', 'storage_config'];

        foreach ($jsonFields as $field) {
            if (isset($site->$field) && is_array($site->$field)) {
                $value = self::getNestedValue($site->$field, $name);
                if ($value !== null) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * 获取站点字段数组
     * 返回所有可用的站点配置字段
     *
     * @param Site $site 站点对象
     * @return array
     */
    protected static function getSiteFieldsArray($site)
    {
        $fields = [
            'site_name' => $site->site_name ?? '',
            'site_code' => $site->site_code ?? '',
            'logo' => $site->logo ?? '',
            'site_url' => $site->site_url ?? '',
        ];

        // 合并JSON字段
        if (isset($site->seo_config) && is_array($site->seo_config)) {
            $fields['seo'] = $site->seo_config;
            // 同时支持扁平访问
            foreach ($site->seo_config as $key => $value) {
                $fields['seo_' . $key] = $value;
            }
        }

        if (isset($site->analytics_config) && is_array($site->analytics_config)) {
            $fields['analytics'] = $site->analytics_config;
        }

        if (isset($site->config) && is_array($site->config)) {
            $fields = array_merge($fields, $site->config);
        }

        return $fields;
    }

    /**
     * 清除配置缓存
     *
     * @param int|null $siteId 站点ID，为空则清除当前站点
     * @param string|null $name 配置名称，为空则清除所有
     * @return void
     */
    public static function clearCache($siteId = null, $name = null)
    {
        if ($siteId === null) {
            $site = SiteContextService::getSite();
            $siteId = $site ? $site->id : 0;
        }

        if ($siteId === 0) {
            return;
        }

        if ($name) {
            Cache::delete('site_template_config_' . $siteId . '_' . $name);
        } else {
            // 清除该站点的所有配置缓存
            Cache::delete('site_template_config_all_' . $siteId);
            // 使用通配符删除所有单项缓存（需要缓存驱动支持）
            $prefix = 'site_template_config_' . $siteId . '_';
            // 注意：这里简化处理，实际可能需要遍历删除
            Cache::tag('site_template_config_' . $siteId)->clear();
        }
    }
}
