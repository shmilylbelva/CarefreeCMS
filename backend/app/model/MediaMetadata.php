<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 媒体元数据模型
 * 存储EXIF、IPTC等扩展信息
 */
class MediaMetadata extends Model
{
    protected $name = 'media_metadata';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = false;

    protected $type = [
        'media_id' => 'integer',
    ];

    /**
     * 关联媒体
     */
    public function media()
    {
        return $this->belongsTo(MediaLibrary::class, 'media_id', 'id');
    }

    /**
     * 获取JSON解析后的值
     */
    public function getValueAttr($value, $data)
    {
        if (isset($data['meta_value'])) {
            return json_decode($data['meta_value'], true);
        }
        return null;
    }

    /**
     * 批量设置元数据
     */
    public static function setMany(int $mediaId, array $metadata)
    {
        foreach ($metadata as $key => $value) {
            $jsonValue = is_string($value) ? $value : json_encode($value);

            self::create([
                'media_id' => $mediaId,
                'meta_key' => $key,
                'meta_value' => $jsonValue,
            ]);
        }

        return true;
    }

    /**
     * 获取媒体的所有元数据
     */
    public static function getByMedia(int $mediaId): array
    {
        $metadata = self::where('media_id', $mediaId)->select();

        $result = [];
        foreach ($metadata as $meta) {
            $result[$meta->meta_key] = json_decode($meta->meta_value, true);
        }

        return $result;
    }
}
