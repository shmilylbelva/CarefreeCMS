<?php
declare (strict_types = 1);

namespace app\service\storage;

/**
 * 存储适配器接口
 * 定义所有存储提供商必须实现的方法
 */
interface StorageInterface
{
    /**
     * 上传文件
     *
     * @param string $localPath 本地文件路径
     * @param string $remotePath 远程存储路径
     * @param array $options 额外选项（如 ACL、Content-Type 等）
     * @return array ['url' => '访问URL', 'path' => '存储路径', 'size' => 文件大小]
     */
    public function upload(string $localPath, string $remotePath, array $options = []): array;

    /**
     * 下载文件
     *
     * @param string $remotePath 远程存储路径
     * @param string $localPath 本地保存路径
     * @return bool
     */
    public function download(string $remotePath, string $localPath): bool;

    /**
     * 删除文件
     *
     * @param string $remotePath 远程存储路径
     * @return bool
     */
    public function delete(string $remotePath): bool;

    /**
     * 批量删除文件
     *
     * @param array $remotePaths 远程存储路径数组
     * @return array ['success' => [], 'failed' => []]
     */
    public function batchDelete(array $remotePaths): array;

    /**
     * 检查文件是否存在
     *
     * @param string $remotePath 远程存储路径
     * @return bool
     */
    public function exists(string $remotePath): bool;

    /**
     * 获取文件访问URL
     *
     * @param string $remotePath 远程存储路径
     * @param int $expires 有效期（秒），0表示永久
     * @return string
     */
    public function getUrl(string $remotePath, int $expires = 0): string;

    /**
     * 获取文件信息
     *
     * @param string $remotePath 远程存储路径
     * @return array ['size' => 大小, 'mime' => MIME类型, 'modified' => 修改时间]
     */
    public function getMetadata(string $remotePath): array;

    /**
     * 列出目录下的文件
     *
     * @param string $directory 目录路径
     * @param int $limit 限制数量
     * @return array
     */
    public function listFiles(string $directory = '', int $limit = 1000): array;

    /**
     * 复制文件
     *
     * @param string $sourcePath 源路径
     * @param string $destPath 目标路径
     * @return bool
     */
    public function copy(string $sourcePath, string $destPath): bool;

    /**
     * 移动文件
     *
     * @param string $sourcePath 源路径
     * @param string $destPath 目标路径
     * @return bool
     */
    public function move(string $sourcePath, string $destPath): bool;

    /**
     * 获取存储提供商名称
     *
     * @return string
     */
    public function getProvider(): string;

    /**
     * 获取存储桶/容器名称
     *
     * @return string
     */
    public function getBucket(): string;
}
