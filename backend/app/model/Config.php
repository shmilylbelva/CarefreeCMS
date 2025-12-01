<?php

namespace app\model;

use think\Model;
use app\traits\Cacheable;

/**
 * 系统配置模型
 */
class Config extends Model
{
    use Cacheable;

    protected $name = 'site_config';

    protected $autoWriteTimestamp = true;

    /**
     * 缓存配置
     */
    protected static $cacheTag = 'system_config';
    protected static $cacheExpire = 7200; // 2小时（配置变化很少）

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
     * 获取配置值（带缓存）
     */
    public static function getConfig($key, $default = '')
    {
        // 使用缓存获取所有配置
        $allConfigs = static::getAllConfigsCached();
        return $allConfigs[$key] ?? $default;
    }

    /**
     * 设置配置值
     */
    public static function setConfig($key, $value)
    {
        $config = self::where('config_key', $key)->find();
        if ($config) {
            $config->config_value = $value;
            $config->save();
        } else {
            self::create([
                'config_key' => $key,
                'config_value' => $value
            ]);
        }
        return true;
    }

    /**
     * 批量设置配置
     */
    public static function setConfigs($configs)
    {
        foreach ($configs as $key => $value) {
            self::setConfig($key, $value);
        }
        return true;
    }

    /**
     * 获取所有配置（带缓存）
     */
    public static function getAllConfigsCached()
    {
        return static::cacheRemember('all_configs', function () {
            return static::getAllConfigs();
        }, static::$cacheExpire);
    }

    /**
     * 获取所有配置（不使用缓存）
     */
    public static function getAllConfigs()
    {
        $configs = self::select();
        $result = [];
        foreach ($configs as $config) {
            $result[$config->config_key] = $config->config_value;
        }
        return $result;
    }

    /**
     * 批量获取配置（带缓存）
     *
     * @param array $keys 配置键数组
     * @return array 配置键值对
     */
    public static function getBatchConfigs(array $keys)
    {
        $allConfigs = static::getAllConfigsCached();
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $allConfigs[$key] ?? '';
        }
        return $result;
    }
}
