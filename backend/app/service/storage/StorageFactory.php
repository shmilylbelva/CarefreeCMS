<?php
declare (strict_types = 1);

namespace app\service\storage;

use app\model\StorageConfig;

/**
 * 存储工厂类
 * 负责创建和管理存储适配器实例
 */
class StorageFactory
{
    /**
     * 存储适配器实例缓存
     */
    protected static $instances = [];

    /**
     * 适配器类映射
     */
    protected static $driverMap = [
        'local' => LocalStorage::class,
        'aliyun_oss' => AliyunOssStorage::class,
        'tencent_cos' => TencentCosStorage::class,
        'qiniu' => QiniuStorage::class,
    ];

    /**
     * 创建存储适配器实例
     *
     * @param string $driver 驱动类型 (local/aliyun_oss/tencent_cos/qiniu)
     * @param array $config 配置参数
     * @return StorageInterface
     */
    public static function create(string $driver, array $config = []): StorageInterface
    {
        if (!isset(self::$driverMap[$driver])) {
            throw new \Exception("不支持的存储驱动: {$driver}");
        }

        $class = self::$driverMap[$driver];

        return new $class($config);
    }

    /**
     * 根据配置ID获取存储实例（带缓存）
     *
     * @param int|null $configId 存储配置ID，null表示使用默认配置
     * @return StorageInterface
     */
    public static function getInstance(?int $configId = null): StorageInterface
    {
        // 如果没有指定配置ID，使用默认配置
        if ($configId === null) {
            $config = StorageConfig::getDefault();
            if (!$config) {
                // 如果没有默认配置，返回本地存储
                return self::getLocalInstance();
            }
            $configId = $config->id;
        } else {
            $config = StorageConfig::find($configId);
            if (!$config) {
                throw new \Exception('存储配置不存在');
            }
        }

        // 检查缓存
        if (isset(self::$instances[$configId])) {
            return self::$instances[$configId];
        }

        // 创建新实例
        $instance = self::create($config->driver, $config->config_data);

        // 缓存实例
        self::$instances[$configId] = $instance;

        return $instance;
    }

    /**
     * 获取本地存储实例
     *
     * @return StorageInterface
     */
    public static function getLocalInstance(): StorageInterface
    {
        if (isset(self::$instances['local'])) {
            return self::$instances['local'];
        }

        $config = [
            'root_path' => app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . 'uploads',
            'url_prefix' => '/uploads',
        ];

        $instance = self::create('local', $config);
        self::$instances['local'] = $instance;

        return $instance;
    }

    /**
     * 根据站点ID获取存储实例
     *
     * @param int|null $siteId 站点ID
     * @return StorageInterface
     */
    public static function getInstanceForSite(?int $siteId = null): StorageInterface
    {
        if ($siteId === null) {
            return self::getInstance();
        }

        // 查找该站点的存储配置
        $config = StorageConfig::where('site_id', $siteId)
            ->where('is_enabled', 1)
            ->where('is_default', 1)
            ->find();

        if (!$config) {
            // 如果该站点没有配置，使用全局默认配置
            return self::getInstance();
        }

        return self::getInstance($config->id);
    }

    /**
     * 清除实例缓存
     *
     * @param int|null $configId 配置ID，null表示清除所有
     */
    public static function clearCache(?int $configId = null): void
    {
        if ($configId === null) {
            self::$instances = [];
        } else {
            unset(self::$instances[$configId]);
        }
    }

    /**
     * 注册自定义存储驱动
     *
     * @param string $name 驱动名称
     * @param string $class 驱动类名（必须实现 StorageInterface）
     */
    public static function registerDriver(string $name, string $class): void
    {
        if (!is_subclass_of($class, StorageInterface::class)) {
            throw new \Exception("驱动类必须实现 StorageInterface 接口");
        }

        self::$driverMap[$name] = $class;
    }

    /**
     * 获取所有支持的驱动
     *
     * @return array
     */
    public static function getSupportedDrivers(): array
    {
        return array_keys(self::$driverMap);
    }

    /**
     * 测试存储配置连接
     *
     * @param string $driver 驱动类型
     * @param array $config 配置参数
     * @return array ['success' => bool, 'message' => string]
     */
    public static function testConnection(string $driver, array $config): array
    {
        try {
            $storage = self::create($driver, $config);

            // 尝试列出文件（测试连接）
            $storage->listFiles('', 1);

            return [
                'success' => true,
                'message' => '连接成功',
                'provider' => $storage->getProvider(),
                'bucket' => $storage->getBucket(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
