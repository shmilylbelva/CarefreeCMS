<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 媒体缩略图模型
 * 支持多规格缩略图
 */
class MediaThumbnail extends Model
{
    protected $name = 'media_thumbnails';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = false;

    protected $type = [
        'media_id' => 'integer',
        'preset_id' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * 关联媒体
     */
    public function media()
    {
        return $this->belongsTo(MediaLibrary::class, 'media_id', 'id');
    }

    /**
     * 关联预设
     */
    public function preset()
    {
        return $this->belongsTo(MediaThumbnailPreset::class, 'preset_id', 'id');
    }

    /**
     * 获取缩略图URL
     */
    public function getThumbnailUrlAttr($value, $data)
    {
        if (empty($data['file_path'])) {
            return '';
        }

        $request = request();
        $domain = $request->domain();
        return $domain . '/' . ltrim($data['file_path'], '/');
    }

    /**
     * 获取缩略图完整路径
     */
    public function getFullPath()
    {
        if ($this->storage_type === 'local') {
            return app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $this->file_path;
        }
        return null;
    }

    /**
     * 删除缩略图物理文件
     */
    public function deletePhysicalFile(): bool
    {
        if ($this->storage_type === 'local') {
            $fullPath = $this->getFullPath();
            if ($fullPath && file_exists($fullPath)) {
                return @unlink($fullPath);
            }
        }
        return true;
    }
}
