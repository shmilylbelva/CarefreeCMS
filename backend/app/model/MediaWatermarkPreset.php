<?php
declare (strict_types = 1);

namespace app\model;

/**
 * 媒体水印预设模型
 */
class MediaWatermarkPreset extends SiteModel
{
    protected $name = 'media_watermark_presets';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'site_id' => 'integer',
        'text_size' => 'integer',
        'offset_x' => 'integer',
        'offset_y' => 'integer',
        'opacity' => 'integer',
        'scale' => 'integer',
        'tile_spacing' => 'integer',
        'is_default' => 'integer',
        'is_active' => 'integer',
    ];

    // 水印类型常量
    const TYPE_TEXT = 'text';     // 文字水印
    const TYPE_IMAGE = 'image';   // 图片水印
    const TYPE_TILED = 'tiled';   // 平铺水印

    // 位置常量
    const POS_TOP_LEFT = 'top-left';
    const POS_TOP_RIGHT = 'top-right';
    const POS_BOTTOM_LEFT = 'bottom-left';
    const POS_BOTTOM_RIGHT = 'bottom-right';
    const POS_CENTER = 'center';

    /**
     * 获取默认水印预设
     */
    public static function getDefault(int $siteId = null)
    {
        $query = self::where('is_default', 1)
            ->where('is_active', 1);

        if ($siteId) {
            $query->where('site_id', $siteId);
        }

        return $query->find();
    }

    /**
     * 获取所有激活的预设
     */
    public static function getActive(int $siteId = null)
    {
        $query = self::where('is_active', 1);

        if ($siteId) {
            $query->where('site_id', $siteId);
        }

        return $query->select();
    }
}
