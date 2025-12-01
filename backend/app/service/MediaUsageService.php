<?php
declare (strict_types = 1);

namespace app\service;

use app\model\MediaUsage;
use app\model\MediaLibrary;
use think\facade\Db;

/**
 * 媒体使用追踪服务
 */
class MediaUsageService
{
    /**
     * 从内容中提取媒体ID
     * 支持提取img标签的src和data-src属性中的媒体ID
     */
    public function extractMediaIds(string $content): array
    {
        $mediaIds = [];

        // 匹配 <img> 标签中的媒体URL
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $url) {
                $id = $this->getMediaIdFromUrl($url);
                if ($id) {
                    $mediaIds[] = $id;
                }
            }
        }

        // 匹配 data-src（懒加载）
        preg_match_all('/<img[^>]+data-src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $url) {
                $id = $this->getMediaIdFromUrl($url);
                if ($id) {
                    $mediaIds[] = $id;
                }
            }
        }

        // 匹配Markdown格式的图片 ![alt](url)
        preg_match_all('/!\[([^\]]*)\]\(([^\)]+)\)/i', $content, $matches);
        if (!empty($matches[2])) {
            foreach ($matches[2] as $url) {
                $id = $this->getMediaIdFromUrl($url);
                if ($id) {
                    $mediaIds[] = $id;
                }
            }
        }

        return array_unique(array_filter($mediaIds));
    }

    /**
     * 从URL中提取媒体ID
     */
    protected function getMediaIdFromUrl(string $url): ?int
    {
        // 如果是完整URL，提取路径部分
        $path = $url;
        if (preg_match('#^https?://[^/]+(/.+)$#', $url, $matches)) {
            $path = $matches[1];
        }

        // 移除 /uploads/ 前缀，因为数据库中的file_path不包含这个前缀
        $filePath = preg_replace('#^/uploads/#', '', $path);

        // 通过file_path查找对应的MediaFile
        $file = \app\model\MediaFile::where('file_path', $filePath)->find();
        if (!$file) {
            return null;
        }

        // 通过file_id查找MediaLibrary记录（禁用站点过滤）
        $media = MediaLibrary::withoutSiteScope()
            ->where('file_id', $file->id)
            ->find();

        return $media ? $media->id : null;
    }

    /**
     * 记录媒体使用
     */
    public function recordUsage(
        int $mediaId,
        string $usableType,
        int $usableId,
        string $fieldName = null,
        int $count = 1
    ): MediaUsage {
        $usage = MediaUsage::where('media_id', $mediaId)
            ->where('usable_type', $usableType)
            ->where('usable_id', $usableId)
            ->find();

        if ($usage) {
            // 更新引用次数
            $usage->usage_count = $count;
            $usage->field_name = $fieldName;
            $usage->save();
        } else {
            // 创建新记录
            $usage = MediaUsage::create([
                'media_id' => $mediaId,
                'usable_type' => $usableType,
                'usable_id' => $usableId,
                'field_name' => $fieldName,
                'usage_count' => $count,
            ]);
        }

        return $usage;
    }

    /**
     * 批量记录媒体使用（通过内容自动提取）
     */
    public function recordUsageFromContent(
        string $content,
        string $usableType,
        int $usableId,
        string $fieldName = 'content'
    ): int {
        // 提取所有媒体ID
        $mediaIds = $this->extractMediaIds($content);

        // 删除旧的引用记录
        MediaUsage::where('usable_type', $usableType)
            ->where('usable_id', $usableId)
            ->where('field_name', $fieldName)
            ->delete();

        // 统计每个媒体的引用次数
        $mediaCounts = array_count_values($mediaIds);

        // 批量创建新记录
        $count = 0;
        foreach ($mediaCounts as $mediaId => $usageCount) {
            $this->recordUsage($mediaId, $usableType, $usableId, $fieldName, $usageCount);
            $count++;
        }

        return $count;
    }

    /**
     * 批量记录媒体使用（直接指定媒体ID数组）
     */
    public function recordUsageFromMediaIds(
        array $mediaIds,
        string $usableType,
        int $usableId,
        string $fieldName = null
    ): int {
        // 删除旧的引用记录
        $query = MediaUsage::where('usable_type', $usableType)
            ->where('usable_id', $usableId);

        if ($fieldName) {
            $query->where('field_name', $fieldName);
        }

        $query->delete();

        // 统计每个媒体的引用次数
        $mediaCounts = array_count_values($mediaIds);

        // 批量创建新记录
        $count = 0;
        foreach ($mediaCounts as $mediaId => $usageCount) {
            $this->recordUsage($mediaId, $usableType, $usableId, $fieldName, $usageCount);
            $count++;
        }

        return $count;
    }

    /**
     * 删除使用记录
     */
    public function removeUsage(string $usableType, int $usableId, string $fieldName = null): int
    {
        $query = MediaUsage::where('usable_type', $usableType)
            ->where('usable_id', $usableId);

        if ($fieldName) {
            $query->where('field_name', $fieldName);
        }

        return $query->delete();
    }

    /**
     * 获取对象使用的媒体列表
     */
    public function getUsedMedia(string $usableType, int $usableId): array
    {
        $usages = MediaUsage::where('usable_type', $usableType)
            ->where('usable_id', $usableId)
            ->with(['media' => function($query) {
                $query->with('file');
            }])
            ->select();

        $result = [];
        foreach ($usages as $usage) {
            if ($usage->media) {
                $result[] = [
                    'id' => $usage->media->id,
                    'file_name' => $usage->media->file_name,
                    'file_url' => $usage->media->file_url,
                    'file_type' => $usage->media->file_type,
                    'field_name' => $usage->field_name,
                    'usage_count' => $usage->usage_count,
                ];
            }
        }

        return $result;
    }

    /**
     * 获取媒体的使用情况
     */
    public function getMediaUsageInfo(int $mediaId): array
    {
        $usages = MediaUsage::where('media_id', $mediaId)
            ->select()
            ->toArray();

        $result = [
            'total_usage' => count($usages),
            'usage_details' => [],
        ];

        foreach ($usages as $usage) {
            $type = $usage['usable_type'];
            if (!isset($result['usage_details'][$type])) {
                $result['usage_details'][$type] = [];
            }

            $result['usage_details'][$type][] = [
                'id' => $usage['usable_id'],
                'field' => $usage['field_name'],
                'count' => $usage['usage_count'],
            ];
        }

        return $result;
    }

    /**
     * 检查媒体是否可以安全删除
     */
    public function canSafelyDelete(int $mediaId): array
    {
        $usages = MediaUsage::where('media_id', $mediaId)->select();

        return [
            'can_delete' => $usages->isEmpty(),
            'usage_count' => $usages->count(),
            'usages' => $usages->toArray(),
        ];
    }

    /**
     * 获取未使用的媒体列表
     */
    public function getUnusedMedia(int $page = 1, int $pageSize = 20, array $filters = []): array
    {
        // 获取所有已使用的媒体ID
        $usedMediaIds = MediaUsage::column('media_id');

        // 查询未使用的媒体
        $query = MediaLibrary::whereNotIn('id', $usedMediaIds);

        // 应用筛选条件
        if (isset($filters['file_type']) && $filters['file_type']) {
            $query->where('file_type', $filters['file_type']);
        }

        if (isset($filters['days_ago']) && $filters['days_ago'] > 0) {
            $date = date('Y-m-d H:i:s', strtotime("-{$filters['days_ago']} days"));
            $query->where('created_at', '<', $date);
        }

        $total = $query->count();
        $list = $query->with('file')
            ->page($page, $pageSize)
            ->order('created_at', 'desc')
            ->select()
            ->toArray();

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
        ];
    }

    /**
     * 批量删除未使用的媒体
     */
    public function cleanUnusedMedia(array $filters = []): array
    {
        $result = $this->getUnusedMedia(1, 10000, $filters);
        $mediaIds = array_column($result['list'], 'id');

        $deletedCount = 0;
        $failedCount = 0;

        foreach ($mediaIds as $mediaId) {
            try {
                $media = MediaLibrary::find($mediaId);
                if ($media) {
                    $media->delete();
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;
            }
        }

        return [
            'total' => count($mediaIds),
            'deleted' => $deletedCount,
            'failed' => $failedCount,
        ];
    }

    /**
     * 重建文章的媒体使用记录
     */
    public function syncArticleMediaUsage(int $articleId): array
    {
        $article = \app\model\Article::withoutSiteScope()->find($articleId);
        if (!$article) {
            throw new \Exception('文章不存在');
        }

        $synced = [];

        // 从内容中提取并记录媒体使用
        if (!empty($article->content)) {
            $count = $this->recordUsageFromContent(
                $article->content,
                'article',
                $article->id,
                'content'
            );
            $synced['content'] = $count;
        }

        // 记录缩略图的使用（单个URL字段）
        if (!empty($article->thumb)) {
            $thumbMediaId = $this->getMediaIdFromUrl($article->thumb);
            if ($thumbMediaId) {
                $this->recordUsage($thumbMediaId, 'article', $article->id, 'thumb', 1);
                $synced['thumb'] = 1;
            }
        }

        // 记录封面图片的使用（单个URL字段）
        if (!empty($article->cover_image)) {
            $coverMediaId = $this->getMediaIdFromUrl($article->cover_image);
            if ($coverMediaId) {
                $this->recordUsage($coverMediaId, 'article', $article->id, 'cover_image', 1);
                $synced['cover_image'] = 1;
            }
        }

        // 记录图片集合的使用
        if (!empty($article->images)) {
            $imagesContent = is_array($article->images) ? json_encode($article->images) : $article->images;
            $imageMediaIds = $this->extractMediaIds($imagesContent);
            if (!empty($imageMediaIds)) {
                $count = $this->recordUsageFromMediaIds(
                    $imageMediaIds,
                    'article',
                    $article->id,
                    'images'
                );
                $synced['images'] = $count;
            }
        }

        // 记录OG图片的使用（单个URL字段）
        if (!empty($article->og_image)) {
            $ogMediaId = $this->getMediaIdFromUrl($article->og_image);
            if ($ogMediaId) {
                $this->recordUsage($ogMediaId, 'article', $article->id, 'og_image', 1);
                $synced['og_image'] = 1;
            }
        }

        return $synced;
    }
}
