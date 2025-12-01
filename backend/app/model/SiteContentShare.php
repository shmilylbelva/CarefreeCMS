<?php

namespace app\model;

use think\Model;

/**
 * 站点内容共享模型
 */
class SiteContentShare extends Model
{
    protected $name = 'site_content_share';

    protected $autoWriteTimestamp = true;

    protected $type = [
        'id'                => 'integer',
        'source_site_id'    => 'integer',
        'target_site_id'    => 'integer',
        'content_id'        => 'integer',
        'share_mode'        => 'integer',
        'sync_update'       => 'integer',
        'target_content_id' => 'integer',
        'share_status'      => 'integer',
    ];

    // 内容类型常量
    const TYPE_ARTICLE = 'article';
    const TYPE_CATEGORY = 'category';
    const TYPE_TAG = 'tag';

    // 共享模式常量
    const MODE_REFERENCE = 1; // 引用模式（不复制）
    const MODE_COPY = 2;      // 复制模式（独立副本）

    // 共享状态常量
    const STATUS_CANCELLED = 0; // 已取消
    const STATUS_SHARING = 1;   // 共享中

    /**
     * 关联源站点
     */
    public function sourceSite()
    {
        return $this->belongsTo(Site::class, 'source_site_id', 'id');
    }

    /**
     * 关联目标站点
     */
    public function targetSite()
    {
        return $this->belongsTo(Site::class, 'target_site_id', 'id');
    }

    /**
     * 搜索器：源站点ID
     */
    public function searchSourceSiteIdAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('source_site_id', $value);
        }
    }

    /**
     * 搜索器：目标站点ID
     */
    public function searchTargetSiteIdAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('target_site_id', $value);
        }
    }

    /**
     * 搜索器：内容类型
     */
    public function searchContentTypeAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('content_type', $value);
        }
    }

    /**
     * 搜索器：共享模式
     */
    public function searchShareModeAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('share_mode', $value);
        }
    }

    /**
     * 搜索器：共享状态
     */
    public function searchShareStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('share_status', $value);
        }
    }

    /**
     * 获取器：内容类型文本
     */
    public function getContentTypeTextAttr($value, $data)
    {
        $types = [
            self::TYPE_ARTICLE  => '文章',
            self::TYPE_CATEGORY => '分类',
            self::TYPE_TAG      => '标签',
        ];
        return $types[$data['content_type']] ?? '未知';
    }

    /**
     * 获取器：共享模式文本
     */
    public function getShareModeTextAttr($value, $data)
    {
        $modes = [
            self::MODE_REFERENCE => '引用模式',
            self::MODE_COPY      => '复制模式',
        ];
        return $modes[$data['share_mode']] ?? '未知';
    }

    /**
     * 获取器：共享状态文本
     */
    public function getShareStatusTextAttr($value, $data)
    {
        $statuses = [
            self::STATUS_CANCELLED => '已取消',
            self::STATUS_SHARING   => '共享中',
        ];
        return $statuses[$data['share_status']] ?? '未知';
    }

    /**
     * 检查内容是否已共享到目标站点
     */
    public static function isShared($sourceSiteId, $targetSiteId, $contentType, $contentId)
    {
        return self::where('source_site_id', $sourceSiteId)
            ->where('target_site_id', $targetSiteId)
            ->where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where('share_status', self::STATUS_SHARING)
            ->count() > 0;
    }

    /**
     * 获取内容的所有共享记录
     */
    public static function getContentShares($sourceSiteId, $contentType, $contentId)
    {
        return self::where('source_site_id', $sourceSiteId)
            ->where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->where('share_status', self::STATUS_SHARING)
            ->with(['targetSite'])
            ->select();
    }

    /**
     * 获取站点共享到其他站点的内容列表
     */
    public static function getSharedToOthers($sourceSiteId, $contentType = null)
    {
        $query = self::where('source_site_id', $sourceSiteId)
            ->where('share_status', self::STATUS_SHARING);

        if ($contentType !== null) {
            $query->where('content_type', $contentType);
        }

        return $query->with(['targetSite'])->select();
    }

    /**
     * 获取站点从其他站点共享的内容列表
     */
    public static function getSharedFromOthers($targetSiteId, $contentType = null)
    {
        $query = self::where('target_site_id', $targetSiteId)
            ->where('share_status', self::STATUS_SHARING);

        if ($contentType !== null) {
            $query->where('content_type', $contentType);
        }

        return $query->with(['sourceSite'])->select();
    }

    /**
     * 取消共享
     */
    public function cancelShare()
    {
        return $this->save(['share_status' => self::STATUS_CANCELLED]);
    }
}
