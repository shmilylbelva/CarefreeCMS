<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 媒体水印处理日志模型
 */
class MediaWatermarkLog extends Model
{
    protected $name = 'media_watermark_log';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = false;

    protected $type = [
        'media_id' => 'integer',
        'preset_id' => 'integer',
        'user_id' => 'integer',
        'output_file_id' => 'integer',
        'processing_time' => 'integer',
    ];

    // 状态常量
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    /**
     * 关联媒体
     */
    public function media()
    {
        return $this->belongsTo(MediaLibrary::class, 'media_id', 'id');
    }

    /**
     * 关联水印预设
     */
    public function preset()
    {
        return $this->belongsTo(MediaWatermarkPreset::class, 'preset_id', 'id');
    }

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id', 'id');
    }
}
