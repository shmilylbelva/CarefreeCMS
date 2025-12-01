<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 媒体编辑历史模型
 */
class MediaEditHistory extends Model
{
    protected $name = 'media_edit_history';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = false;

    protected $type = [
        'media_id' => 'integer',
        'user_id' => 'integer',
        'original_file_id' => 'integer',
        'result_file_id' => 'integer',
        'processing_time' => 'integer',
    ];

    // 操作类型常量
    const OP_RESIZE = 'resize';           // 调整大小
    const OP_CROP = 'crop';               // 裁剪
    const OP_ROTATE = 'rotate';           // 旋转
    const OP_FLIP = 'flip';               // 翻转
    const OP_FILTER = 'filter';           // 滤镜
    const OP_BRIGHTNESS = 'brightness';   // 亮度
    const OP_CONTRAST = 'contrast';       // 对比度
    const OP_SATURATION = 'saturation';   // 饱和度
    const OP_BLUR = 'blur';               // 模糊
    const OP_SHARPEN = 'sharpen';         // 锐化
    const OP_WATERMARK = 'watermark';     // 水印

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
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id', 'id');
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
     * 获取操作参数（JSON解析）
     */
    public function getParamsAttr($value, $data)
    {
        if (isset($data['operation_params'])) {
            return json_decode($data['operation_params'], true);
        }
        return [];
    }
}
