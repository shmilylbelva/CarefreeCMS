<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 媒体视频转码任务模型
 */
class MediaVideoTranscode extends Model
{
    protected $name = 'media_video_transcodes';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'media_id' => 'integer',
        'original_file_id' => 'integer',
        'output_file_id' => 'integer',
        'poster_file_id' => 'integer',
        'progress' => 'integer',
        'processing_time' => 'integer',
    ];

    // 状态常量
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    // 预设常量
    const PRESET_480P = '480p';
    const PRESET_720P = '720p';
    const PRESET_1080P = '1080p';
    const PRESET_4K = '4k';

    // 格式常量
    const FORMAT_MP4 = 'mp4';
    const FORMAT_WEBM = 'webm';
    const FORMAT_HLS = 'hls';

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
     * 关联输出文件
     */
    public function outputFile()
    {
        return $this->belongsTo(MediaFile::class, 'output_file_id', 'id');
    }

    /**
     * 关联海报文件
     */
    public function posterFile()
    {
        return $this->belongsTo(MediaFile::class, 'poster_file_id', 'id');
    }

    /**
     * 更新进度
     */
    public function updateProgress(int $progress)
    {
        $this->progress = $progress;
        return $this->save();
    }

    /**
     * 标记为处理中
     */
    public function markAsProcessing()
    {
        $this->status = self::STATUS_PROCESSING;
        $this->started_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * 标记为完成
     */
    public function markAsCompleted(int $outputFileId = null, int $posterFileId = null)
    {
        $this->status = self::STATUS_COMPLETED;
        $this->progress = 100;
        $this->completed_at = date('Y-m-d H:i:s');

        if ($outputFileId) {
            $this->output_file_id = $outputFileId;
        }

        if ($posterFileId) {
            $this->poster_file_id = $posterFileId;
        }

        // 计算处理时长
        if ($this->started_at) {
            $start = strtotime($this->started_at);
            $end = strtotime($this->completed_at);
            $this->processing_time = $end - $start;
        }

        return $this->save();
    }

    /**
     * 标记为失败
     */
    public function markAsFailed(string $errorMessage)
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->completed_at = date('Y-m-d H:i:s');

        return $this->save();
    }
}
