<?php
namespace app\service\tag;

use app\model\Config;
use think\facade\Cache;

/**
 * 配置标签服务类
 * 处理网站配置标签的数据查询
 */
class ConfigTagService
{
    /**
     * 获取配置值
     *
     * @param string $name 配置名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function get($name, $default = '')
    {
        // 尝试从缓存获取
        $cacheKey = 'site_config_' . $name;
        $value = Cache::get($cacheKey);

        if ($value !== false && $value !== null) {
            return $value;
        }

        // 从数据库查询
        $setting = Config::where('config_key', $name)->find();

        if ($setting) {
            $value = $setting->config_value;
            // 缓存1小时
            Cache::set($cacheKey, $value, 3600);
            return $value;
        }

        return $default;
    }

    /**
     * 获取所有配置
     *
     * @return array
     */
    public static function getAll()
    {
        $cacheKey = 'site_config_all';
        $configs = Cache::get($cacheKey);

        if ($configs !== false && $configs !== null) {
            return $configs;
        }

        $configs = Config::getAllConfigs();

        // 缓存1小时
        Cache::set($cacheKey, $configs, 3600);

        return $configs;
    }

    /**
     * 清除配置缓存
     *
     * @param string|null $name 配置名称，为空则清除所有
     * @return void
     */
    public static function clearCache($name = null)
    {
        if ($name) {
            Cache::delete('site_config_' . $name);
        } else {
            Cache::tag('site_config')->clear();
        }
    }
}
