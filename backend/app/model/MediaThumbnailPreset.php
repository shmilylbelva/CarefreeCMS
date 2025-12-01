<?php
declare (strict_types = 1);

namespace app\model;

/**
 * 缩略图预设配置模型
 */
class MediaThumbnailPreset extends SiteModel
{
    protected $name = 'media_thumbnail_presets';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'site_id' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'quality' => 'integer',
        'is_builtin' => 'integer',
        'is_auto_generate' => 'integer',
    ];

    // 缩放模式常量
    const MODE_FIT = 'fit';       // 等比例缩放，适应尺寸
    const MODE_FILL = 'fill';     // 等比例缩放，填充尺寸
    const MODE_CROP = 'crop';     // 裁剪到指定尺寸
    const MODE_EXACT = 'exact';   // 强制缩放到指定尺寸

    /**
     * 获取所有自动生成的预设
     */
    public static function getAutoGeneratePresets(int $siteId = null)
    {
        $query = self::where('is_auto_generate', 1);

        if ($siteId) {
            $query->where('site_id', $siteId);
        }

        return $query->select();
    }

    /**
     * 获取默认预设
     */
    public static function getDefault()
    {
        return self::where('name', 'medium')->find();
    }

    /**
     * 根据名称获取预设
     */
    public static function getByName(string $name, int $siteId = null)
    {
        $query = self::where('name', $name);

        if ($siteId) {
            $query->where('site_id', $siteId);
        }

        return $query->find();
    }
}
