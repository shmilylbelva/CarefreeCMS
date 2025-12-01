<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 分片上传会话模型
 */
class ChunkedUploadSession extends Model
{
    protected $table = 'chunked_upload_sessions';

    protected $autoWriteTimestamp = true;

    // 状态常量
    const STATUS_UPLOADING = 'uploading';
    const STATUS_MERGING = 'merging';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * 关联分片记录
     */
    public function chunks()
    {
        return $this->hasMany(UploadChunk::class, 'session_id');
    }

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 关联媒体
     */
    public function media()
    {
        return $this->belongsTo(MediaLibrary::class, 'media_id', 'id');
    }

    /**
     * 生成唯一的上传ID
     */
    public static function generateUploadId(): string
    {
        return md5(uniqid((string)mt_rand(), true));
    }

    /**
     * 计算进度百分比
     */
    public function getProgressAttr(): float
    {
        if ($this->total_chunks == 0) {
            return 0;
        }

        return round(($this->uploaded_chunks / $this->total_chunks) * 100, 2);
    }

    /**
     * 检查是否所有分片都已上传
     */
    public function isAllChunksUploaded(): bool
    {
        return $this->uploaded_chunks >= $this->total_chunks;
    }

    /**
     * 增加已上传分片计数
     */
    public function incrementUploadedChunks(): bool
    {
        return $this->inc('uploaded_chunks')->update();
    }

    /**
     * 标记为合并中
     */
    public function markAsMerging(): bool
    {
        $this->status = self::STATUS_MERGING;
        return $this->save();
    }

    /**
     * 标记为完成
     */
    public function markAsCompleted(int $mediaId, string $fileHash): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->media_id = $mediaId;
        $this->file_hash = $fileHash;
        $this->completed_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * 标记为失败
     */
    public function markAsFailed(string $errorMessage): bool
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        return $this->save();
    }

    /**
     * 检查是否已过期
     */
    public function isExpired(): bool
    {
        return strtotime($this->expires_at) < time();
    }

    /**
     * 清理临时文件
     */
    public function cleanupTempFiles(): bool
    {
        if (empty($this->temp_dir) || !is_dir($this->temp_dir)) {
            return true;
        }

        // 删除临时目录及其所有文件
        return $this->deleteDirectory($this->temp_dir);
    }

    /**
     * 递归删除目录
     */
    protected function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }
}
