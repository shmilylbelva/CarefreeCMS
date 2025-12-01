<?php
declare (strict_types = 1);

namespace app\service;

use app\model\MediaFile;
use app\service\storage\StorageFactory;
use think\file\UploadedFile;

/**
 * 媒体文件服务
 * 处理文件上传、去重、存储等操作（支持云存储）
 */
class MediaFileService
{
    protected $storage;
    /**
     * 上传文件并创建MediaFile记录（支持去重和云存储）
     * @param UploadedFile $file 上传的文件对象
     * @param array $options 配置选项
     * @return MediaFile
     * @throws \Exception
     */
    public function uploadFile(UploadedFile $file, array $options = []): MediaFile
    {
        // 获取配置
        $storageConfigId = $options['storage_config_id'] ?? null;
        $siteId = $options['site_id'] ?? null;
        $savePath = $options['save_path'] ?? date('Y/m/d');

        // 获取存储实例
        if ($storageConfigId) {
            $this->storage = StorageFactory::getInstance($storageConfigId);
        } elseif ($siteId) {
            $this->storage = StorageFactory::getInstanceForSite($siteId);
        } else {
            $this->storage = StorageFactory::getInstance();
        }

        // 获取文件基本信息
        $originalName = $file->getOriginalName();
        $ext = strtolower($file->extension());
        $mimeType = $file->getMime();
        $fileSize = $file->getSize();

        // 生成临时文件路径用于计算hash
        $tempPath = $file->getRealPath();

        // 计算文件hash
        $fileHash = MediaFile::calculateHash($tempPath);

        // 检查文件是否已存在（去重）
        $existingFile = MediaFile::findByHash($fileHash);

        if ($existingFile) {
            // 文件已存在，增加引用计数并返回
            $existingFile->incrementRefCount();
            return $existingFile;
        }

        // 文件不存在，需要上传
        $fileName = date('YmdHis') . '_' . uniqid() . '.' . $ext;
        $remotePath = $savePath . '/' . $fileName;

        // 上传到存储
        $uploadResult = $this->storage->upload($tempPath, $remotePath, [
            'content_type' => $mimeType,
        ]);

        // 判断文件类型
        $fileType = MediaFile::getFileTypeByMime($mimeType);

        // 获取图片/视频尺寸
        $width = null;
        $height = null;
        $duration = null;

        if ($fileType === MediaFile::TYPE_IMAGE) {
            $imageInfo = @getimagesize($tempPath);
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        } elseif ($fileType === MediaFile::TYPE_VIDEO) {
            // TODO: 使用FFmpeg获取视频信息
            // $videoInfo = $this->getVideoInfo($tempPath);
            // $width = $videoInfo['width'];
            // $height = $videoInfo['height'];
            // $duration = $videoInfo['duration'];
        }

        // 创建MediaFile记录
        $mediaFile = MediaFile::create([
            'file_hash' => $fileHash,
            'file_path' => $uploadResult['path'],
            'file_url' => $uploadResult['url'],
            'file_name' => $originalName,
            'file_ext' => $ext,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'file_type' => $fileType,
            'storage_type' => $this->storage->getProvider(),
            'storage_config_id' => $storageConfigId,
            'width' => $width,
            'height' => $height,
            'duration' => $duration,
            'ref_count' => 1, // 初始引用计数为1
        ]);

        return $mediaFile;
    }

    /**
     * 从URL下载文件并创建MediaFile记录
     * @param string $url 文件URL
     * @param array $options 配置选项
     * @return MediaFile
     * @throws \Exception
     */
    public function downloadFromUrl(string $url, array $options = []): MediaFile
    {
        // 下载文件到临时目录
        $tempFile = $this->downloadToTemp($url);

        try {
            // 获取文件信息
            $fileInfo = pathinfo($url);
            $ext = $fileInfo['extension'] ?? 'tmp';
            $originalName = $fileInfo['basename'] ?? 'downloaded_file';

            // 创建UploadedFile对象
            $uploadedFile = new UploadedFile(
                $tempFile,
                $originalName,
                mime_content_type($tempFile),
                filesize($tempFile),
                0
            );

            // 使用uploadFile方法处理
            return $this->uploadFile($uploadedFile, $options);

        } finally {
            // 清理临时文件
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    /**
     * 下载文件到临时目录
     * @param string $url 文件URL
     * @return string 临时文件路径
     * @throws \Exception
     */
    protected function downloadToTemp(string $url): string
    {
        $tempDir = sys_get_temp_dir();
        $tempFile = $tempDir . DIRECTORY_SEPARATOR . 'media_' . uniqid() . '.tmp';

        $content = @file_get_contents($url);

        if ($content === false) {
            throw new \Exception('无法下载文件：' . $url);
        }

        if (file_put_contents($tempFile, $content) === false) {
            throw new \Exception('无法保存临时文件');
        }

        return $tempFile;
    }

    /**
     * 删除MediaFile（如果引用计数为0，支持云存储）
     * @param int $fileId 文件ID
     * @param bool $force 是否强制删除
     * @return bool
     */
    public function deleteFile(int $fileId, bool $force = false): bool
    {
        $file = MediaFile::find($fileId);

        if (!$file) {
            throw new \Exception('文件不存在');
        }

        // 减少引用计数
        $file->decrementRefCount();

        // 如果引用计数为0或强制删除，则删除物理文件和记录
        if ($force || $file->canDelete()) {
            // 获取存储实例
            if ($file->storage_config_id) {
                $storage = StorageFactory::getInstance($file->storage_config_id);
            } else {
                $storage = StorageFactory::getLocalInstance();
            }

            // 删除云端文件
            $storage->delete($file->file_path);

            return $file->delete();
        }

        return true;
    }

    /**
     * 获取文件存储统计信息
     * @return array
     */
    public function getStorageStats(): array
    {
        $stats = [
            'total_files' => MediaFile::count(),
            'total_size' => MediaFile::sum('file_size'),
            'by_type' => [],
            'by_storage' => [],
        ];

        // 按文件类型统计
        $typeStats = MediaFile::field('file_type, COUNT(*) as count, SUM(file_size) as size')
            ->group('file_type')
            ->select();

        foreach ($typeStats as $stat) {
            $stats['by_type'][$stat['file_type']] = [
                'count' => $stat['count'],
                'size' => $stat['size'],
            ];
        }

        // 按存储类型统计
        $storageStats = MediaFile::field('storage_type, COUNT(*) as count, SUM(file_size) as size')
            ->group('storage_type')
            ->select();

        foreach ($storageStats as $stat) {
            $stats['by_storage'][$stat['storage_type']] = [
                'count' => $stat['count'],
                'size' => $stat['size'],
            ];
        }

        return $stats;
    }

    /**
     * 清理无引用的文件
     * @return int 清理的文件数量
     */
    public function cleanupUnreferencedFiles(): int
    {
        $files = MediaFile::where('ref_count', 0)->select();
        $count = 0;

        foreach ($files as $file) {
            if ($file->deletePhysicalFile()) {
                $file->delete();
                $count++;
            }
        }

        return $count;
    }
}
