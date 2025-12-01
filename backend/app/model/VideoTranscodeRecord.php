<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 视频转码记录模型
 */
class VideoTranscodeRecord extends Model
{
    protected $table = 'video_transcode_records';

    protected $autoWriteTimestamp = true;

    // 状态常量
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * 关联媒体
     */
    public function media()
    {
        return $this->belongsTo(MediaLibrary::class, 'media_id', 'id');
    }

    /**
     * 关联原始文件
     */
    public function originalFile()
    {
        return $this->belongsTo(MediaFile::class, 'original_file_id', 'id');
    }

    /**
     * 关联结果文件
     */
    public function resultFile()
    {
        return $this->belongsTo(MediaFile::class, 'result_file_id', 'id');
    }

    /**
     * 更新进度
     */
    public function updateProgress(int $progress): bool
    {
        $this->progress = min(100, max(0, $progress));
        return $this->save();
    }

    /**
     * 标记为处理中
     */
    public function markAsProcessing(): bool
    {
        $this->status = self::STATUS_PROCESSING;
        $this->started_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * 标记为完成
     */
    public function markAsCompleted(int $resultFileId): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->result_file_id = $resultFileId;
        $this->progress = 100;
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
        $this->completed_at = date('Y-m-d H:i:s');
        return $this->save();
    }
}
