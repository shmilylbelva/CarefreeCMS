<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 存储配置模型
 */
class StorageConfig extends Model
{
    protected $table = 'storage_configs';

    protected $autoWriteTimestamp = true;

    // 字段类型定义
    protected $type = [
        'config_data' => 'json',
    ];

    /**
     * 获取器 - 配置数据
     */
    public function getConfigDataAttr($value)
    {
        // 处理 ThinkPHP 8 的 Json 对象
        if ($value instanceof \think\model\type\Json) {
            return $value->value();
        }
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        if (is_array($value)) {
            return $value;
        }
        return [];
    }

    /**
     * 修改器 - 配置数据
     */
    public function setConfigDataAttr($value)
    {
        if (is_array($value)) {
            return json_encode($value);
        }
        return $value;
    }

    /**
     * 获取默认存储配置
     */
    public static function getDefault(?int $siteId = null)
    {
        $query = self::where('is_enabled', 1)
            ->where('is_default', 1)
            ->order('sort_order', 'asc');

        if ($siteId !== null) {
            // 优先查找站点专用配置
            $config = (clone $query)->where('site_id', $siteId)->find();
            if ($config) {
                return $config;
            }
        }

        // 查找全局配置
        return $query->where('site_id', null)->find();
    }

    /**
     * 设置为默认存储
     */
    public function setAsDefault(): bool
    {
        // 先取消同站点其他默认配置
        self::where('is_default', 1)
            ->where('site_id', $this->site_id ?? 0)
            ->update(['is_default' => 0]);

        // 设置当前为默认
        $this->is_default = 1;
        return $this->save();
    }

    /**
     * 验证配置连接
     */
    public function testConnection(): array
    {
        try {
            $factory = new \app\service\storage\StorageFactory();
            return $factory::testConnection($this->driver, $this->config_data);
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * 获取驱动显示名称
     */
    public function getDriverNameAttr()
    {
        $names = [
            'local' => '本地存储',
            'aliyun_oss' => '阿里云OSS',
            'tencent_cos' => '腾讯云COS',
            'qiniu' => '七牛云',
        ];

        return $names[$this->driver] ?? $this->driver;
    }

    /**
     * 根据驱动类型获取配置项模板
     */
    public static function getConfigTemplate(string $driver): array
    {
        $templates = [
            'local' => [
                'root_path' => [
                    'label' => '根目录',
                    'type' => 'text',
                    'required' => true,
                    'default' => 'uploads',
                    'description' => '相对于public目录的路径',
                ],
                'url_prefix' => [
                    'label' => 'URL前缀',
                    'type' => 'text',
                    'required' => true,
                    'default' => '/uploads',
                    'description' => '访问URL的前缀',
                ],
            ],
            'aliyun_oss' => [
                'access_key_id' => [
                    'label' => 'AccessKey ID',
                    'type' => 'text',
                    'required' => true,
                ],
                'access_key_secret' => [
                    'label' => 'AccessKey Secret',
                    'type' => 'password',
                    'required' => true,
                ],
                'bucket' => [
                    'label' => 'Bucket名称',
                    'type' => 'text',
                    'required' => true,
                ],
                'endpoint' => [
                    'label' => '访问域名',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'oss-cn-hangzhou.aliyuncs.com',
                ],
                'cdn_domain' => [
                    'label' => 'CDN加速域名',
                    'type' => 'text',
                    'required' => false,
                    'description' => '如果配置了CDN，填写CDN域名',
                ],
                'is_cname' => [
                    'label' => '是否使用CNAME',
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
            'tencent_cos' => [
                'secret_id' => [
                    'label' => 'SecretId',
                    'type' => 'text',
                    'required' => true,
                ],
                'secret_key' => [
                    'label' => 'SecretKey',
                    'type' => 'password',
                    'required' => true,
                ],
                'bucket' => [
                    'label' => 'Bucket名称',
                    'type' => 'text',
                    'required' => true,
                ],
                'region' => [
                    'label' => '地域',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'ap-guangzhou',
                ],
                'cdn_domain' => [
                    'label' => 'CDN加速域名',
                    'type' => 'text',
                    'required' => false,
                ],
            ],
            'qiniu' => [
                'access_key' => [
                    'label' => 'AccessKey',
                    'type' => 'text',
                    'required' => true,
                ],
                'secret_key' => [
                    'label' => 'SecretKey',
                    'type' => 'password',
                    'required' => true,
                ],
                'bucket' => [
                    'label' => 'Bucket名称',
                    'type' => 'text',
                    'required' => true,
                ],
                'domain' => [
                    'label' => '访问域名',
                    'type' => 'text',
                    'required' => true,
                    'description' => '空间绑定的域名',
                ],
                'private' => [
                    'label' => '私有空间',
                    'type' => 'boolean',
                    'default' => false,
                ],
            ],
        ];

        return $templates[$driver] ?? [];
    }
}
