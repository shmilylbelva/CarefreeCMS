<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 媒体使用追踪模型
 */
class MediaUsage extends Model
{
    protected $name = 'media_usage';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'media_id' => 'integer',
        'usable_id' => 'integer',
        'usage_count' => 'integer',
    ];

    // 使用类型常量
    const TYPE_ARTICLE = 'article';
    const TYPE_PAGE = 'page';
    const TYPE_COMMENT = 'comment';
    const TYPE_TOPIC = 'topic';
    const TYPE_CUSTOM_FIELD = 'custom_field';

    /**
     * 关联媒体
     */
    public function media()
    {
        return $this->belongsTo(MediaLibrary::class, 'media_id', 'id');
    }

    /**
     * 获取媒体的所有使用记录
     */
    public static function getMediaUsage(int $mediaId): array
    {
        return self::where('media_id', $mediaId)
            ->select()
            ->toArray();
    }

    /**
     * 获取指定对象使用的所有媒体
     */
    public static function getUsableMedia(string $usableType, int $usableId): array
    {
        return self::where('usable_type', $usableType)
            ->where('usable_id', $usableId)
            ->with('media')
            ->select()
            ->toArray();
    }

    /**
     * 检查媒体是否被使用
     */
    public static function isMediaUsed(int $mediaId): bool
    {
        return self::where('media_id', $mediaId)->count() > 0;
    }

    /**
     * 获取未使用的媒体ID列表
     */
    public static function getUnusedMediaIds(array $mediaIds): array
    {
        $usedIds = self::whereIn('media_id', $mediaIds)
            ->column('media_id');

        return array_diff($mediaIds, $usedIds);
    }
}
