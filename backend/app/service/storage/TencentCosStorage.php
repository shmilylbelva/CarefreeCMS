<?php
declare (strict_types = 1);

namespace app\service\storage;

use Qcloud\Cos\Client;
use Qcloud\Cos\Exception\ServiceResponseException;

/**
 * 腾讯云COS存储适配器
 * 需要安装: composer require qcloud/cos-sdk-v5
 */
class TencentCosStorage implements StorageInterface
{
    protected $config;
    protected $client;
    protected $bucket;
    protected $region;
    protected $cdnDomain;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->bucket = $config['bucket'];
        $this->region = $config['region'];
        $this->cdnDomain = $config['cdn_domain'] ?? '';

        try {
            $this->client = new Client([
                'region' => $this->region,
                'credentials' => [
                    'secretId' => $config['secret_id'],
                    'secretKey' => $config['secret_key'],
                ],
                'timeout' => $config['timeout'] ?? 60,
                'connect_timeout' => $config['connect_timeout'] ?? 60,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('COS初始化失败: ' . $e->getMessage());
        }
    }

    /**
     * 上传文件
     */
    public function upload(string $localPath, string $remotePath, array $options = []): array
    {
        try {
            $params = [
                'Bucket' => $this->bucket,
                'Key' => $remotePath,
                'Body' => fopen($localPath, 'rb'),
            ];

            if (isset($options['content_type'])) {
                $params['ContentType'] = $options['content_type'];
            }

            if (isset($options['acl'])) {
                $params['ACL'] = $options['acl'];
            }

            $this->client->putObject($params);

            $fileSize = filesize($localPath);

            return [
                'url' => $this->getUrl($remotePath),
                'path' => $remotePath,
                'size' => $fileSize,
            ];
        } catch (ServiceResponseException $e) {
            throw new \Exception('COS上传失败: ' . $e->getMessage());
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

            $result = $this->client->getObject([
                'Bucket' => $this->bucket,
                'Key' => $remotePath,
                'SaveAs' => $localPath,
            ]);

            return file_exists($localPath);
        } catch (ServiceResponseException $e) {
            return false;
        }
    }

    /**
     * 删除文件
     */
    public function delete(string $remotePath): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $remotePath,
            ]);
            return true;
        } catch (ServiceResponseException $e) {
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
            // COS支持批量删除，最多1000个
            $chunks = array_chunk($remotePaths, 1000);

            foreach ($chunks as $chunk) {
                $objects = array_map(function ($path) {
                    return ['Key' => $path];
                }, $chunk);

                try {
                    $deleteResult = $this->client->deleteObjects([
                        'Bucket' => $this->bucket,
                        'Objects' => $objects,
                    ]);

                    // 检查删除结果
                    if (isset($deleteResult['Deleted'])) {
                        foreach ($deleteResult['Deleted'] as $deleted) {
                            $result['success'][] = $deleted['Key'];
                        }
                    }

                    if (isset($deleteResult['Errors'])) {
                        foreach ($deleteResult['Errors'] as $error) {
                            $result['failed'][] = $error['Key'];
                        }
                    }
                } catch (ServiceResponseException $e) {
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
            $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $remotePath,
            ]);
            return true;
        } catch (ServiceResponseException $e) {
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
                $signedUrl = $this->client->getObjectUrl($this->bucket, $remotePath, '+' . $expires . ' seconds');
                return $signedUrl;
            }

            // 公共读取URL
            return sprintf(
                'https://%s.cos.%s.myqcloud.com/%s',
                $this->bucket,
                $this->region,
                ltrim($remotePath, '/')
            );
        } catch (\Exception $e) {
            throw new \Exception('获取URL失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取文件信息
     */
    public function getMetadata(string $remotePath): array
    {
        try {
            $result = $this->client->headObject([
                'Bucket' => $this->bucket,
                'Key' => $remotePath,
            ]);

            return [
                'size' => (int)($result['ContentLength'] ?? 0),
                'mime' => $result['ContentType'] ?? 'application/octet-stream',
                'modified' => isset($result['LastModified']) ? strtotime($result['LastModified']) : 0,
            ];
        } catch (ServiceResponseException $e) {
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

            $result = $this->client->listObjects([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
                'MaxKeys' => $limit,
            ]);

            $files = [];
            if (isset($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    $files[] = [
                        'path' => $object['Key'],
                        'size' => (int)$object['Size'],
                        'modified' => strtotime($object['LastModified']),
                    ];
                }
            }

            return $files;
        } catch (ServiceResponseException $e) {
            return [];
        }
    }

    /**
     * 复制文件
     */
    public function copy(string $sourcePath, string $destPath): bool
    {
        try {
            $this->client->copyObject([
                'Bucket' => $this->bucket,
                'Key' => $destPath,
                'CopySource' => $this->bucket . '.cos.' . $this->region . '.myqcloud.com/' . $sourcePath,
            ]);
            return true;
        } catch (ServiceResponseException $e) {
            return false;
        }
    }

    /**
     * 移动文件
     */
    public function move(string $sourcePath, string $destPath): bool
    {
        try {
            // COS没有直接的移动操作，需要复制后删除
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
        return 'tencent_cos';
    }

    /**
     * 获取存储桶名称
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }
}
