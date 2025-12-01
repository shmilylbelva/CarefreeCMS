<?php
declare (strict_types = 1);

namespace app\service;

use app\model\Site;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Config;
use think\facade\Db;

/**
 * 站点上下文服务
 * 负责管理当前请求的站点上下文，包括站点识别、站点切换、表前缀管理等
 */
class SiteContextService
{
    /**
     * 当前站点ID
     * @var int
     */
    private static $currentSiteId = null;

    /**
     * 当前站点对象
     * @var Site|null
     */
    private static $currentSite = null;

    /**
     * 缓存前缀
     */
    const CACHE_PREFIX = 'site_context:';

    /**
     * 缓存时间（秒）
     */
    const CACHE_TIME = 3600;

    /**
     * 识别当前站点
     * 根据域名、子域名、URL参数等识别当前请求对应的站点
     * @return Site|null
     */
    public static function identifySite()
    {
        // 如果已经识别过，直接返回
        if (self::$currentSite !== null) {
            return self::$currentSite;
        }

        $site = null;

        // 1. 优先从URL参数中获取站点代码（用于后台管理切换站点）
        $siteCode = Request::param('site_code', '');
        if ($siteCode) {
            $site = self::getSiteBySiteCode($siteCode);
        }

        // 2. 如果都没有识别到，使用主站
        if (!$site) {
            $site = self::getMainSite();
        }

        // 设置当前站点
        if ($site) {
            self::setSite($site);
        }

        return $site;
    }

    /**
     * 根据站点代码获取站点（带缓存）
     * @param string $siteCode 站点代码
     * @return Site|null
     */
    private static function getSiteBySiteCode($siteCode)
    {
        // Site模型已内置缓存，直接调用
        return Site::getBySiteCode($siteCode);
    }

    /**
     * 获取主站（带缓存）
     * @return Site|null
     */
    private static function getMainSite()
    {
        // Site模型已内置缓存，直接调用
        return Site::getMainSite();
    }

    /**
     * 设置当前站点
     * @param Site $site 站点对象
     */
    public static function setSite(Site $site)
    {
        self::$currentSite = $site;
        self::$currentSiteId = $site->id;

        // 如果站点有表前缀，设置数据库表前缀
        if ($site->db_prefix) {
            self::setTablePrefix($site->db_prefix);
        }

        // 注意：站点信息已在MultiSite中间件中注入到Request对象，无需再次设置
    }

    /**
     * 获取当前站点
     * @return Site|null
     */
    public static function getSite()
    {
        if (self::$currentSite === null) {
            self::identifySite();
        }

        return self::$currentSite;
    }

    /**
     * 获取当前站点ID
     * @return int|null
     */
    public static function getSiteId()
    {
        if (self::$currentSiteId === null) {
            $site = self::getSite();
            if ($site) {
                self::$currentSiteId = $site->id;
            }
        }

        return self::$currentSiteId;
    }

    /**
     * 切换站点
     * @param int $siteId 站点ID
     * @return bool
     */
    public static function switchSite($siteId)
    {
        $site = Site::find($siteId);

        if (!$site || $site->status != Site::STATUS_ENABLED) {
            return false;
        }

        self::setSite($site);

        return true;
    }

    /**
     * 切换到主站
     * @return bool
     */
    public static function switchToMainSite()
    {
        $mainSite = self::getMainSite();

        if (!$mainSite) {
            return false;
        }

        self::setSite($mainSite);

        return true;
    }

    /**
     * 设置数据库表前缀
     * @param string $prefix 表前缀
     */
    private static function setTablePrefix($prefix)
    {
        // 注意：ThinkPHP 8 动态设置表前缀需要特殊处理
        // 这里提供一个基本实现，具体需要根据实际情况调整

        $config = Config::get('database.connections.mysql');
        $config['prefix'] = $prefix;

        // 重新连接数据库
        Db::connect($config, true);
    }

    /**
     * 重置表前缀为默认值
     */
    public static function resetTablePrefix()
    {
        $defaultPrefix = Config::get('database.connections.mysql.prefix', '');
        if ($defaultPrefix) {
            self::setTablePrefix($defaultPrefix);
        }
    }

    /**
     * 检查当前是否为主站
     * @return bool
     */
    public static function isMainSite()
    {
        $site = self::getSite();

        return $site && $site->site_type == Site::TYPE_MAIN;
    }

    /**
     * 获取站点配置
     * @param string|null $key 配置键，为空则返回所有配置
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getSiteConfig($key = null, $default = null)
    {
        $site = self::getSite();

        if (!$site || !$site->config) {
            return $default;
        }

        if ($key === null) {
            return $site->config;
        }

        return $site->config[$key] ?? $default;
    }

    /**
     * 设置站点配置
     * @param string|array $key 配置键或配置数组
     * @param mixed $value 配置值
     * @return bool
     */
    public static function setSiteConfig($key, $value = null)
    {
        $site = self::getSite();

        if (!$site) {
            return false;
        }

        $config = $site->config ?: [];

        if (is_array($key)) {
            // 批量设置
            $config = array_merge($config, $key);
        } else {
            // 单个设置
            $config[$key] = $value;
        }

        return $site->save(['config' => $config]);
    }

    /**
     * 获取站点SEO配置
     * @param string|null $key 配置键
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function getSeoConfig($key = null, $default = null)
    {
        $site = self::getSite();

        if (!$site || !$site->seo_config) {
            return $default;
        }

        if ($key === null) {
            return $site->seo_config;
        }

        return $site->seo_config[$key] ?? $default;
    }

    /**
     * 获取站点URL
     * @param string $path 路径
     * @return string
     */
    public static function getSiteUrl($path = '')
    {
        $site = self::getSite();

        if (!$site) {
            return $path;
        }

        $domain = $site->full_domain;

        if (!$domain) {
            return $path;
        }

        $protocol = Request::isSsl() ? 'https://' : 'http://';
        $url = $protocol . $domain;

        if ($path) {
            $url .= '/' . ltrim($path, '/');
        }

        return $url;
    }

    /**
     * 清除站点缓存
     * @param int|null $siteId 站点ID，为空则清除所有站点缓存
     */
    public static function clearCache($siteId = null)
    {
        if ($siteId) {
            $site = Site::find($siteId);
            if ($site) {
                // 清除指定站点的缓存（现在Site模型已有内置缓存）
                // 清除站点选项缓存
                Cache::delete(self::CACHE_PREFIX . 'options:enabled');
                Cache::delete(self::CACHE_PREFIX . 'options:all');
            }
        } else {
            // 清除所有站点缓存
            Cache::delete(self::CACHE_PREFIX . 'options:enabled');
            Cache::delete(self::CACHE_PREFIX . 'options:all');
        }

        // 清除Site模型的缓存
        Site::clearCacheTag();

        // 如果清除的是当前站点，重置当前站点
        if ($siteId === null || $siteId == self::$currentSiteId) {
            self::$currentSite = null;
            self::$currentSiteId = null;
        }
    }

    /**
     * 检查站点是否可访问
     * @param int|null $siteId 站点ID，为空则检查当前站点
     * @return bool
     */
    public static function isAccessible($siteId = null)
    {
        if ($siteId === null) {
            $site = self::getSite();
        } else {
            $site = Site::find($siteId);
        }

        if (!$site) {
            return false;
        }

        return $site->status == Site::STATUS_ENABLED;
    }

    /**
     * 获取站点列表选项（用于下拉选择，带缓存）
     * @param bool $onlyEnabled 是否只返回启用的站点
     * @return array
     */
    public static function getSiteOptions($onlyEnabled = true)
    {
        $cacheKey = self::CACHE_PREFIX . 'options:' . ($onlyEnabled ? 'enabled' : 'all');

        return Cache::remember($cacheKey, function () use ($onlyEnabled) {
            $query = Site::field('id,site_code,site_name,status')
                ->order('sort', 'asc')
                ->order('id', 'asc');

            if ($onlyEnabled) {
                $query->where('status', Site::STATUS_ENABLED);
            }

            $sites = $query->select();

            $options = [];
            foreach ($sites as $site) {
                $options[] = [
                    'id' => $site->id,
                    'name' => $site->site_name,
                    'site_code' => $site->site_code,
                    'display_name' => $site->site_name . ' (' . $site->site_code . ')',
                ];
            }

            return $options;
        }, self::CACHE_TIME);
    }
}
