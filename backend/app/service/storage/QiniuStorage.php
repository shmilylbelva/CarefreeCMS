<?php
declare (strict_types = 1);

namespace app\service\storage;

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

/**
 * 七牛云存储适配器
 * 需要安装: composer require qiniu/php-sdk
 */
class QiniuStorage implements StorageInterface
{
    protected $config;
    protected $auth;
    protected $bucket;
    protected $domain;
    protected $uploadManager;
    protected $bucketManager;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->bucket = $config['bucket'];
        $this->domain = rtrim($config['domain'], '/');

        try {
            $this->auth = new Auth($config['access_key'], $config['secret_key']);
            $this->uploadManager = new UploadManager();
            $this->bucketManager = new BucketManager($this->auth);
        } catch (\Exception $e) {
            throw new \Exception('七牛云初始化失败: ' . $e->getMessage());
        }
    }

    /**
     * 上传文件
     */
    public function upload(string $localPath, string $remotePath, array $options = []): array
    {
        try {
            // 生成上传凭证
            $token = $this->auth->uploadToken($this->bucket, $remotePath);

            // 上传文件
            list($ret, $err) = $this->uploadManager->putFile($token, $remotePath, $localPath);

            if ($err !== null) {
                throw new \Exception('上传失败: ' . json_encode($err));
            }

            $fileSize = filesize($localPath);

            return [
                'url' => $this->getUrl($remotePath),
                'path' => $remotePath,
                'size' => $fileSize,
            ];
        } catch (\Exception $e) {
            throw new \Exception('七牛云上传失败: ' . $e->getMessage());
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

            // 获取文件URL
            $url = $this->getUrl($remotePath);

            // 下载文件
            $content = file_get_contents($url);

            if ($content === false) {
                return false;
            }

            return file_put_contents($localPath, $content) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 删除文件
     */
    public function delete(string $remotePath): bool
    {
        try {
            $err = $this->bucketManager->delete($this->bucket, $remotePath);

            // null 表示成功
            return $err === null;
        } catch (\Exception $e) {
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
            // 七牛云支持批量删除，最多1000个
            $chunks = array_chunk($remotePaths, 1000);

            foreach ($chunks as $chunk) {
                // 构建批量删除操作
                $ops = $this->bucketManager->buildBatchDelete($this->bucket, $chunk);

                // 执行批量删除
                list($ret, $err) = $this->bucketManager->batch($ops);

                // 处理结果
                if ($err !== null) {
                    $result['failed'] = array_merge($result['failed'], $chunk);
                } else {
                    // 检查每个文件的删除结果
                    foreach ($ret as $index => $item) {
                        if (isset($item['code']) && $item['code'] == 200) {
                            $result['success'][] = $chunk[$index];
                        } else {
                            $result['failed'][] = $chunk[$index];
                        }
                    }
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
            list($ret, $err) = $this->bucketManager->stat($this->bucket, $remotePath);

            return $err === null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取文件访问URL
     */
    public function getUrl(string $remotePath, int $expires = 0): string
    {
        $url = $this->domain . '/' . ltrim($remotePath, '/');

        // 如果需要私有访问
        if ($expires > 0 || ($this->config['private'] ?? false)) {
            $deadline = $expires > 0 ? time() + $expires : time() + 3600;
            return $this->auth->privateDownloadUrl($url, $deadline);
        }

        return $url;
    }

    /**
     * 获取文件信息
     */
    public function getMetadata(string $remotePath): array
    {
        try {
            list($ret, $err) = $this->bucketManager->stat($this->bucket, $remotePath);

            if ($err !== null) {
                throw new \Exception('获取文件信息失败');
            }

            return [
                'size' => (int)($ret['fsize'] ?? 0),
                'mime' => $ret['mimeType'] ?? 'application/octet-stream',
                'modified' => (int)($ret['putTime'] ?? 0) / 10000000, // 七牛返回的是100纳秒
            ];
        } catch (\Exception $e) {
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

            list($items, $marker, $err) = $this->bucketManager->listFiles(
                $this->bucket,
                $prefix,
                null,
                $limit
            );

            if ($err !== null) {
                return [];
            }

            $files = [];
            foreach ($items as $item) {
                $files[] = [
                    'path' => $item['key'],
                    'size' => (int)$item['fsize'],
                    'modified' => (int)($item['putTime'] / 10000000),
                ];
            }

            return $files;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 复制文件
     */
    public function copy(string $sourcePath, string $destPath): bool
    {
        try {
            $err = $this->bucketManager->copy(
                $this->bucket,
                $sourcePath,
                $this->bucket,
                $destPath
            );

            return $err === null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 移动文件
     */
    public function move(string $sourcePath, string $destPath): bool
    {
        try {
            $err = $this->bucketManager->move(
                $this->bucket,
                $sourcePath,
                $this->bucket,
                $destPath
            );

            return $err === null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取存储提供商名称
     */
    public function getProvider(): string
    {
        return 'qiniu';
    }

    /**
     * 获取存储桶名称
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }
}
