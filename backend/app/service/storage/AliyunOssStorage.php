<?php
declare (strict_types = 1);

namespace app\service\storage;

use OSS\OssClient;
use OSS\Core\OssException;

/**
 * 阿里云OSS存储适配器
 * 需要安装: composer require aliyuncs/oss-sdk-php
 */
class AliyunOssStorage implements StorageInterface
{
    protected $config;
    protected $client;
    protected $bucket;
    protected $endpoint;
    protected $cdnDomain;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->bucket = $config['bucket'];
        $this->endpoint = $config['endpoint'];
        $this->cdnDomain = $config['cdn_domain'] ?? '';

        try {
            $this->client = new OssClient(
                $config['access_key_id'],
                $config['access_key_secret'],
                $this->endpoint,
                $config['is_cname'] ?? false
            );
        } catch (OssException $e) {
            throw new \Exception('OSS初始化失败: ' . $e->getMessage());
        }
    }

    /**
     * 上传文件
     */
    public function upload(string $localPath, string $remotePath, array $options = []): array
    {
        try {
            // 设置选项
            $ossOptions = [];

            if (isset($options['content_type'])) {
                $ossOptions[OssClient::OSS_CONTENT_TYPE] = $options['content_type'];
            }

            if (isset($options['acl'])) {
                $ossOptions[OssClient::OSS_OBJECT_ACL] = $options['acl'];
            } else {
                $ossOptions[OssClient::OSS_OBJECT_ACL] = OssClient::OSS_ACL_TYPE_PUBLIC_READ;
            }

            // 上传文件
            $this->client->uploadFile($this->bucket, $remotePath, $localPath, $ossOptions);

            $fileSize = filesize($localPath);

            return [
                'url' => $this->getUrl($remotePath),
                'path' => $remotePath,
                'size' => $fileSize,
            ];
        } catch (OssException $e) {
            throw new \Exception('OSS上传失败: ' . $e->getMessage());
        }
    }

    /**
     * 下载文件
     */
    public function download(string $remotePath, string $localPath): bool
    {
        try {
            // 创建目标目录
            $dir = dirname($localPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $options = [
                OssClient::OSS_FILE_DOWNLOAD => $localPath,
            ];

            $this->client->getObject($this->bucket, $remotePath, $options);

            return file_exists($localPath);
        } catch (OssException $e) {
            return false;
        }
    }

    /**
     * 删除文件
     */
    public function delete(string $remotePath): bool
    {
        try {
            $this->client->deleteObject($this->bucket, $remotePath);
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    /**
     * 批量删除文件
     */
    public function batchDelete(array $remotePaths): array
    {
        $result = [
            'success' => [],
            'failed' => [],
        ];

        try {
            // OSS支持批量删除，最多1000个
            $chunks = array_chunk($remotePaths, 1000);

            foreach ($chunks as $chunk) {
                try {
                    $this->client->deleteObjects($this->bucket, $chunk);
                    $result['success'] = array_merge($result['success'], $chunk);
                } catch (OssException $e) {
                    $result['failed'] = array_merge($result['failed'], $chunk);
                }
            }
        } catch (\Exception $e) {
            $result['failed'] = $remotePaths;
        }

        return $result;
    }

    /**
     * 检查文件是否存在
     */
    public function exists(string $remotePath): bool
    {
        try {
            return $this->client->doesObjectExist($this->bucket, $remotePath);
        } catch (OssException $e) {
            return false;
        }
    }

    /**
     * 获取文件访问URL
     */
    public function getUrl(string $remotePath, int $expires = 0): string
    {
        try {
            // 如果配置了CDN域名，优先使用CDN
            if ($this->cdnDomain) {
                return rtrim($this->cdnDomain, '/') . '/' . ltrim($remotePath, '/');
            }

            // 如果需要临时URL
            if ($expires > 0) {
                return $this->client->signUrl($this->bucket, $remotePath, $expires);
            }

            // 公共读取URL
            return sprintf(
                'https://%s.%s/%s',
                $this->bucket,
                $this->endpoint,
                ltrim($remotePath, '/')
            );
        } catch (OssException $e) {
            throw new \Exception('获取URL失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取文件信息
     */
    public function getMetadata(string $remotePath): array
    {
        try {
            $meta = $this->client->getObjectMeta($this->bucket, $remotePath);

            return [
                'size' => (int)($meta['content-length'] ?? 0),
                'mime' => $meta['content-type'] ?? 'application/octet-stream',
                'modified' => isset($meta['last-modified']) ? strtotime($meta['last-modified']) : 0,
            ];
        } catch (OssException $e) {
            throw new \Exception('获取文件信息失败: ' . $e->getMessage());
        }
    }

    /**
     * 列出目录下的文件
     */
    public function listFiles(string $directory = '', int $limit = 1000): array
    {
        try {
            $prefix = $directory ? rtrim($directory, '/') . '/' : '';

            $options = [
                'prefix' => $prefix,
                'max-keys' => $limit,
            ];

            $listInfo = $this->client->listObjects($this->bucket, $options);

            $files = [];
            foreach ($listInfo->getObjectList() as $object) {
                $files[] = [
                    'path' => $object->getKey(),
                    'size' => $object->getSize(),
                    'modified' => strtotime($object->getLastModified()),
                ];
            }

            return $files;
        } catch (OssException $e) {
            return [];
        }
    }

    /**
     * 复制文件
     */
    public function copy(string $sourcePath, string $destPath): bool
    {
        try {
            $this->client->copyObject($this->bucket, $sourcePath, $this->bucket, $destPath);
            return true;
        } catch (OssException $e) {
            return false;
        }
    }

    /**
     * 移动文件
     */
    public function move(string $sourcePath, string $destPath): bool
    {
        try {
            // OSS没有直接的移动操作，需要复制后删除
            if ($this->copy($sourcePath, $destPath)) {
                return $this->delete($sourcePath);
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取存储提供商名称
     */
    public function getProvider(): string
    {
        return 'aliyun_oss';
    }

    /**
     * 获取存储桶名称
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }
}
