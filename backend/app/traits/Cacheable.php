<?php
declare(strict_types=1);

namespace app\traits;

use think\facade\Cache;

/**
 * 缓存辅助Trait
 * 简化模型和控制器中的缓存使用
 *
 * 使用此trait的类需要定义以下属性：
 * - protected static $cacheTag = 'tag_name';
 * - protected static $cacheExpire = 3600;
 */
trait Cacheable
{

    /**
     * 记住查询结果（带缓存）
     *
     * @param string $key 缓存键
     * @param \Closure $callback 回调函数
     * @param int|null $expire 过期时间（秒），null使用默认值
     * @param bool $useTag 是否使用标签
     * @return mixed
     */
    protected static function cacheRemember(string $key, \Closure $callback, ?int $expire = null, bool $useTag = true)
    {
        $expire = $expire ?? (static::$cacheExpire ?? 3600);
        $fullKey = static::getCacheKey($key);

        try {
            $cacheTag = static::$cacheTag ?? null;
            if ($useTag && $cacheTag) {
                return Cache::tag($cacheTag)->remember($fullKey, $callback, $expire);
            }

            return Cache::remember($fullKey, $callback, $expire);
        } catch (\Exception $e) {
            // 缓存失败时直接执行回调
            error_log("缓存失败: " . $e->getMessage());
            return $callback();
        }
    }

    /**
     * 获取缓存
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected static function cacheGet(string $key, $default = null)
    {
        $fullKey = static::getCacheKey($key);

        try {
            return Cache::get($fullKey, $default);
        } catch (\Exception $e) {
            error_log("获取缓存失败: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * 设置缓存
     *
     * @param string $key
     * @param mixed $value
     * @param int|null $expire
     * @param bool $useTag
     * @return bool
     */
    protected static function cacheSet(string $key, $value, ?int $expire = null, bool $useTag = true): bool
    {
        $expire = $expire ?? (static::$cacheExpire ?? 3600);
        $fullKey = static::getCacheKey($key);

        try {
            $cacheTag = static::$cacheTag ?? null;
            if ($useTag && $cacheTag) {
                return Cache::tag($cacheTag)->set($fullKey, $value, $expire);
            }

            return Cache::set($fullKey, $value, $expire);
        } catch (\Exception $e) {
            error_log("设置缓存失败: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 删除缓存
     *
     * @param string $key
     * @return bool
     */
    protected static function cacheDelete(string $key): bool
    {
        $fullKey = static::getCacheKey($key);

        try {
            return Cache::delete($fullKey);
        } catch (\Exception $e) {
            error_log("删除缓存失败: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 清除标签下的所有缓存
     *
     * @param string|null $tag 标签名，null使用默认标签
     * @return bool
     */
    public static function clearCacheTag(?string $tag = null): bool
    {
        $tag = $tag ?? (static::$cacheTag ?? null);

        if (!$tag) {
            return false;
        }

        try {
            return Cache::tag($tag)->clear();
        } catch (\Exception $e) {
            error_log("清除标签缓存失败: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取完整的缓存键
     *
     * @param string $key
     * @return string
     */
    protected static function getCacheKey(string $key): string
    {
        // 使用类名作为命名空间，避免键冲突
        $className = basename(str_replace('\\', '/', static::class));
        return strtolower($className) . ':' . $key;
    }

    /**
     * 模型事件：在数据创建后清除缓存
     * 在模型中调用: static::afterInsert(function($model) { self::clearCacheTag(); });
     */
    protected static function onAfterWrite()
    {
        static::clearCacheTag();
    }

    /**
     * 模型事件：在数据删除后清除缓存
     */
    protected static function onAfterDelete()
    {
        static::clearCacheTag();
    }

    /**
     * 获取缓存的列表数据
     *
     * @param string $key 缓存键
     * @param \Closure $query 查询构造器回调
     * @param array $params 查询参数（用于构建缓存键）
     * @param int|null $expire 过期时间
     * @return array
     *
     * @example
     * $categories = Category::getCachedList('list', function() {
     *     return Category::where('status', 1)->select();
     * }, ['status' => 1], 3600);
     */
    protected static function getCachedList(string $key, \Closure $query, array $params = [], ?int $expire = null): array
    {
        // 根据参数生成唯一的缓存键
        $cacheKey = $key;
        if (!empty($params)) {
            ksort($params);
            $cacheKey .= ':' . md5(json_encode($params));
        }

        return static::cacheRemember($cacheKey, function () use ($query) {
            $result = $query();

            // 如果已经是数组，直接返回
            if (is_array($result)) {
                return $result;
            }

            // 如果是Collection或模型结果集，转换为数组
            if (method_exists($result, 'toArray')) {
                return $result->toArray();
            }

            // 其他情况返回空数组
            return $result ? [] : [];
        }, $expire);
    }
}
