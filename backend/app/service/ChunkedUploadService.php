<?php
declare (strict_types = 1);

namespace app\service;

use app\model\ChunkedUploadSession;
use app\model\UploadChunk;
use app\model\MediaFile;
use think\file\UploadedFile;

/**
 * 分片上传服务
 * 支持大文件的分片上传和断点续传
 */
class ChunkedUploadService
{
    protected $mediaLibraryService;
    protected $mediaFileService;

    // 默认分片大小 2MB
    const DEFAULT_CHUNK_SIZE = 2097152;

    // 默认过期时间 24小时
    const DEFAULT_EXPIRY_HOURS = 24;

    public function __construct()
    {
        $this->mediaLibraryService = new MediaLibraryService();
        $this->mediaFileService = new MediaFileService();
    }

    /**
     * 初始化分片上传会话
     *
     * @param array $data 会话数据
     * @return ChunkedUploadSession
     */
    public function initSession(array $data): ChunkedUploadSession
    {
        // 生成唯一上传ID
        $uploadId = ChunkedUploadSession::generateUploadId();

        // 计算总分片数
        $fileSize = $data['file_size'];
        $chunkSize = $data['chunk_size'] ?? self::DEFAULT_CHUNK_SIZE;
        $totalChunks = (int)ceil($fileSize / $chunkSize);

        // 创建临时目录
        $tempDir = $this->createTempDir($uploadId);

        // 计算过期时间
        $expiryHours = $data['expiry_hours'] ?? self::DEFAULT_EXPIRY_HOURS;
        $expiresAt = date('Y-m-d H:i:s', time() + ($expiryHours * 3600));

        // 创建会话
        $session = ChunkedUploadSession::create([
            'user_id' => $data['user_id'],
            'site_id' => $data['site_id'] ?? null,
            'upload_id' => $uploadId,
            'file_name' => $data['file_name'],
            'file_size' => $fileSize,
            'mime_type' => $data['mime_type'],
            'chunk_size' => $chunkSize,
            'total_chunks' => $totalChunks,
            'temp_dir' => $tempDir,
            'status' => ChunkedUploadSession::STATUS_UPLOADING,
            'expires_at' => $expiresAt,
        ]);

        return $session;
    }

    /**
     * 上传单个分片
     *
     * @param string $uploadId 上传ID
     * @param int $chunkIndex 分片序号
     * @param UploadedFile $file 分片文件
     * @param string|null $chunkHash 分片哈希（用于验证）
     * @return UploadChunk
     */
    public function uploadChunk(string $uploadId, int $chunkIndex, UploadedFile $file, ?string $chunkHash = null): UploadChunk
    {
        // 查找会话
        $session = ChunkedUploadSession::where('upload_id', $uploadId)->find();

        if (!$session) {
            throw new \Exception('上传会话不存在');
        }

        if ($session->status !== ChunkedUploadSession::STATUS_UPLOADING) {
            throw new \Exception('上传会话状态不正确');
        }

        if ($session->isExpired()) {
            throw new \Exception('上传会话已过期');
        }

        // 检查分片序号是否有效
        if ($chunkIndex < 0 || $chunkIndex >= $session->total_chunks) {
            throw new \Exception('分片序号无效');
        }

        // 检查分片是否已存在
        $existingChunk = UploadChunk::where('session_id', $session->id)
            ->where('chunk_index', $chunkIndex)
            ->find();

        if ($existingChunk) {
            // 分片已存在，返回现有记录
            return $existingChunk;
        }

        // 保存分片文件
        $chunkFilename = sprintf('chunk_%04d', $chunkIndex);
        $chunkPath = $session->temp_dir . DIRECTORY_SEPARATOR . $chunkFilename;

        $file->move(dirname($chunkPath), basename($chunkPath));

        // 验证分片哈希（如果提供）
        if ($chunkHash) {
            $actualHash = hash_file('md5', $chunkPath);
            if ($actualHash !== $chunkHash) {
                unlink($chunkPath);
                throw new \Exception('分片哈希验证失败');
            }
        }

        // 创建分片记录
        $chunk = UploadChunk::create([
            'session_id' => $session->id,
            'chunk_index' => $chunkIndex,
            'chunk_size' => filesize($chunkPath),
            'chunk_hash' => $chunkHash,
            'file_path' => $chunkPath,
            'status' => $chunkHash ? UploadChunk::STATUS_VERIFIED : UploadChunk::STATUS_UPLOADED,
            'uploaded_at' => date('Y-m-d H:i:s'),
        ]);

        // 更新会话的已上传分片计数
        $session->incrementUploadedChunks();

        return $chunk;
    }

    /**
     * 合并所有分片
     *
     * @param string $uploadId 上传ID
     * @param array $options 选项
     * @return array
     */
    public function mergeChunks(string $uploadId, array $options = []): array
    {
        // 查找会话
        $session = ChunkedUploadSession::where('upload_id', $uploadId)->find();

        if (!$session) {
            throw new \Exception('上传会话不存在');
        }

        if (!$session->isAllChunksUploaded()) {
            throw new \Exception('还有分片未上传');
        }

        // 标记为合并中
        $session->markAsMerging();

        try {
            // 获取所有分片（按序号排序）
            $chunks = UploadChunk::where('session_id', $session->id)
                ->order('chunk_index', 'asc')
                ->select();

            // 创建合并后的文件
            $mergedFilePath = $session->temp_dir . DIRECTORY_SEPARATOR . 'merged_' . $session->file_name;
            $mergedFile = fopen($mergedFilePath, 'wb');

            if (!$mergedFile) {
                throw new \Exception('无法创建合并文件');
            }

            // 合并所有分片
            foreach ($chunks as $chunk) {
                if (!file_exists($chunk->file_path)) {
                    fclose($mergedFile);
                    throw new \Exception('分片文件缺失: ' . $chunk->chunk_index);
                }

                $chunkData = file_get_contents($chunk->file_path);
                fwrite($mergedFile, $chunkData);
            }

            fclose($mergedFile);

            // 计算完整文件哈希
            $fileHash = hash_file('sha256', $mergedFilePath);

            // 检查文件大小
            $actualSize = filesize($mergedFilePath);
            if ($actualSize !== $session->file_size) {
                throw new \Exception('合并后文件大小不匹配');
            }

            // 创建UploadedFile对象
            $uploadedFile = new UploadedFile(
                $mergedFilePath,
                $session->file_name,
                $session->mime_type,
                $actualSize,
                0
            );

            // 上传到存储
            $mediaFile = $this->mediaFileService->uploadFile($uploadedFile, [
                'site_id' => $session->site_id,
                'storage_config_id' => $options['storage_config_id'] ?? null,
            ]);

            // 创建媒体库记录
            $media = $this->mediaLibraryService->create([
                'file_id' => $mediaFile->id,
                'site_id' => $session->site_id,
                'user_id' => $session->user_id,
                'title' => $options['title'] ?? pathinfo($session->file_name, PATHINFO_FILENAME),
                'description' => $options['description'] ?? null,
                'category_ids' => $options['category_ids'] ?? [],
                'tags' => $options['tags'] ?? [],
                'is_public' => $options['is_public'] ?? 1,
            ]);

            // 标记会话为完成
            $session->markAsCompleted($media->id, $fileHash);

            // 清理临时文件
            $session->cleanupTempFiles();

            return [
                'media' => $media,
                'file' => $mediaFile,
                'upload_id' => $uploadId,
            ];

        } catch (\Exception $e) {
            $session->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * 获取上传进度
     *
     * @param string $uploadId 上传ID
     * @return array
     */
    public function getProgress(string $uploadId): array
    {
        $session = ChunkedUploadSession::where('upload_id', $uploadId)->find();

        if (!$session) {
            throw new \Exception('上传会话不存在');
        }

        return [
            'upload_id' => $uploadId,
            'status' => $session->status,
            'total_chunks' => $session->total_chunks,
            'uploaded_chunks' => $session->uploaded_chunks,
            'progress' => $session->progress,
            'is_completed' => $session->status === ChunkedUploadSession::STATUS_COMPLETED,
            'media_id' => $session->media_id,
        ];
    }

    /**
     * 取消上传
     *
     * @param string $uploadId 上传ID
     * @return bool
     */
    public function cancelUpload(string $uploadId): bool
    {
        $session = ChunkedUploadSession::where('upload_id', $uploadId)->find();

        if (!$session) {
            throw new \Exception('上传会话不存在');
        }

        // 清理临时文件
        $session->cleanupTempFiles();

        // 删除分片记录
        UploadChunk::where('session_id', $session->id)->delete();

        // 删除会话
        return $session->delete();
    }

    /**
     * 清理过期的上传会话
     *
     * @return int 清理的会话数量
     */
    public function cleanupExpiredSessions(): int
    {
        $expiredSessions = ChunkedUploadSession::where('expires_at', '<', date('Y-m-d H:i:s'))
            ->where('status', '<>', ChunkedUploadSession::STATUS_COMPLETED)
            ->select();

        $count = 0;

        foreach ($expiredSessions as $session) {
            try {
                $this->cancelUpload($session->upload_id);
                $count++;
            } catch (\Exception $e) {
                trace('清理过期会话失败: ' . $e->getMessage(), 'error');
            }
        }

        return $count;
    }

    /**
     * 创建临时目录
     *
     * @param string $uploadId 上传ID
     * @return string
     */
    protected function createTempDir(string $uploadId): string
    {
        $baseDir = app()->getRuntimePath() . 'chunked_uploads';

        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        $tempDir = $baseDir . DIRECTORY_SEPARATOR . $uploadId;

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        return $tempDir;
    }
}
