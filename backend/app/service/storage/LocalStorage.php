<?php
declare (strict_types = 1);

namespace app\service\storage;

use think\facade\Filesystem;

/**
 * 本地存储适配器
 * 默认存储方式，文件存储在服务器本地
 */
class LocalStorage implements StorageInterface
{
    protected $config;
    protected $rootPath;
    protected $urlPrefix;

    public function __construct(array $config = [])
    {
        $this->config = $config;

        // 处理根路径，支持相对路径和绝对路径
        if (isset($config['root_path'])) {
            $rootPath = $config['root_path'];
            // 如果是相对路径，添加应用根目录
            if (!preg_match('/^([a-zA-Z]:|\/)/', $rootPath)) {
                $rootPath = app()->getRootPath() . $rootPath;
            }
            $this->rootPath = rtrim(str_replace('\\', '/', $rootPath), '/');
        } else {
            $this->rootPath = app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . 'uploads';
        }

        $this->urlPrefix = $config['url_prefix'] ?? '/uploads';

        // 确保根目录存在
        if (!is_dir($this->rootPath)) {
            mkdir($this->rootPath, 0755, true);
        }
    }

    /**
     * 上传文件
     */
    public function upload(string $localPath, string $remotePath, array $options = []): array
    {
        $fullPath = $this->getFullPath($remotePath);

        // 创建目录
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // 复制文件
        if (!copy($localPath, $fullPath)) {
            throw new \Exception('文件上传失败');
        }

        // 设置权限
        chmod($fullPath, 0644);

        return [
            'url' => $this->getUrl($remotePath),
            'path' => $remotePath,
            'size' => filesize($fullPath),
        ];
    }

    /**
     * 下载文件
     */
    public function download(string $remotePath, string $localPath): bool
    {
        $fullPath = $this->getFullPath($remotePath);

        if (!file_exists($fullPath)) {
            return false;
        }

        // 创建目标目录
        $dir = dirname($localPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return copy($fullPath, $localPath);
    }

    /**
     * 删除文件
     */
    public function delete(string $remotePath): bool
    {
        $fullPath = $this->getFullPath($remotePath);

        if (!file_exists($fullPath)) {
            return true; // 文件不存在视为删除成功
        }

        return unlink($fullPath);
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

        foreach ($remotePaths as $path) {
            if ($this->delete($path)) {
                $result['success'][] = $path;
            } else {
                $result['failed'][] = $path;
            }
        }

        return $result;
    }

    /**
     * 检查文件是否存在
     */
    public function exists(string $remotePath): bool
    {
        return file_exists($this->getFullPath($remotePath));
    }

    /**
     * 获取文件访问URL
     */
    public function getUrl(string $remotePath, int $expires = 0): string
    {
        // 本地存储不支持临时URL
        return $this->urlPrefix . '/' . ltrim($remotePath, '/');
    }

    /**
     * 获取文件信息
     */
    public function getMetadata(string $remotePath): array
    {
        $fullPath = $this->getFullPath($remotePath);

        if (!file_exists($fullPath)) {
            throw new \Exception('文件不存在');
        }

        return [
            'size' => filesize($fullPath),
            'mime' => mime_content_type($fullPath),
            'modified' => filemtime($fullPath),
        ];
    }

    /**
     * 列出目录下的文件
     */
    public function listFiles(string $directory = '', int $limit = 1000): array
    {
        $fullPath = $this->getFullPath($directory);

        if (!is_dir($fullPath)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($fullPath)
        );

        $count = 0;
        foreach ($iterator as $file) {
            if ($file->isFile() && $count < $limit) {
                $relativePath = str_replace($this->rootPath, '', $file->getPathname());
                $files[] = [
                    'path' => ltrim($relativePath, '/\\'),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                ];
                $count++;
            }
        }

        return $files;
    }

    /**
     * 复制文件
     */
    public function copy(string $sourcePath, string $destPath): bool
    {
        $sourceFullPath = $this->getFullPath($sourcePath);
        $destFullPath = $this->getFullPath($destPath);

        if (!file_exists($sourceFullPath)) {
            return false;
        }

        // 创建目标目录
        $dir = dirname($destFullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return copy($sourceFullPath, $destFullPath);
    }

    /**
     * 移动文件
     */
    public function move(string $sourcePath, string $destPath): bool
    {
        $sourceFullPath = $this->getFullPath($sourcePath);
        $destFullPath = $this->getFullPath($destPath);

        if (!file_exists($sourceFullPath)) {
            return false;
        }

        // 创建目标目录
        $dir = dirname($destFullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return rename($sourceFullPath, $destFullPath);
    }

    /**
     * 获取存储提供商名称
     */
    public function getProvider(): string
    {
        return 'local';
    }

    /**
     * 获取存储桶名称
     */
    public function getBucket(): string
    {
        return 'local';
    }

    /**
     * 获取完整路径
     */
    protected function getFullPath(string $relativePath): string
    {
        return $this->rootPath . '/' . ltrim($relativePath, '/');
    }
}
