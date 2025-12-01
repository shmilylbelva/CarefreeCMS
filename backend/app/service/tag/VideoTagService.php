<?php
namespace app\service\tag;

use think\facade\Db;

/**
 * 视频标签服务类
 * 处理视频标签的数据查询
 */
class VideoTagService
{
    /**
     * 获取视频列表
     *
     * @param array $params 查询参数
     *   - catid: 分类ID
     *   - limit: 数量限制
     *   - orderby: 排序方式
     *   - featured: 是否精选（1-精选，0-全部）
     * @return array
     */
    public static function getList($params = [])
    {
        $catid = $params['catid'] ?? 0;
        $limit = $params['limit'] ?? 10;
        $orderby = $params['orderby'] ?? 'create_time desc';
        $featured = $params['featured'] ?? 0;

        try {
            // 构建查询
            $query = Db::table('videos')
                ->alias('v')
                ->leftJoin('categories c', 'v.category_id = c.id')
                ->field('v.*, c.name as category_name')
                ->where('v.status', 1);

            // 按分类筛选
            if ($catid > 0) {
                $query->where('v.category_id', $catid);
            }

            // 精选筛选
            if ($featured == 1) {
                $query->where('v.is_featured', 1);
            }

            // 排序
            $orderArr = explode(' ', $orderby);
            $orderField = $orderArr[0] ?? 'create_time';
            $orderType = $orderArr[1] ?? 'desc';
            $query->order($orderField, $orderType);

            // 限制数量
            if ($limit > 0) {
                $query->limit($limit);
            }

            $videos = $query->select()->toArray();

            // 处理视频数据
            foreach ($videos as &$video) {
                // 格式化播放次数
                $video['view_count_formatted'] = self::formatViewCount($video['view_count'] ?? 0);

                // 格式化时长
                $video['duration_formatted'] = self::formatDuration($video['duration'] ?? 0);

                // 确保封面图片路径完整
                if (!empty($video['cover']) && !str_starts_with($video['cover'], 'http')) {
                    $video['cover'] = request()->domain() . $video['cover'];
                }

                // 确保视频路径完整
                if (!empty($video['video_url']) && !str_starts_with($video['video_url'], 'http')) {
                    $video['video_url'] = request()->domain() . $video['video_url'];
                }

                // 格式化发布时间
                if (!empty($video['create_time'])) {
                    $video['create_time_formatted'] = self::formatTime($video['create_time']);
                }
            }

            return $videos;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 格式化播放次数
     *
     * @param int $count 播放次数
     * @return string
     */
    private static function formatViewCount($count)
    {
        if ($count >= 10000) {
            return round($count / 10000, 1) . '万';
        } elseif ($count >= 1000) {
            return round($count / 1000, 1) . 'k';
        }
        return (string)$count;
    }

    /**
     * 格式化视频时长
     *
     * @param int $seconds 秒数
     * @return string
     */
    private static function formatDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        } else {
            return sprintf('%02d:%02d', $minutes, $secs);
        }
    }

    /**
     * 格式化时间
     *
     * @param string $time 时间
     * @return string
     */
    private static function formatTime($time)
    {
        $timestamp = is_numeric($time) ? $time : strtotime($time);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return '刚刚';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff < 2592000) {
            return floor($diff / 86400) . '天前';
        } else {
            return date('Y-m-d', $timestamp);
        }
    }

    /**
     * 获取单个视频信息
     *
     * @param int $id 视频ID
     * @return array|null
     */
    public static function getOne($id)
    {
        try {
            $video = Db::table('videos')
                ->alias('v')
                ->leftJoin('categories c', 'v.category_id = c.id')
                ->field('v.*, c.name as category_name')
                ->where('v.id', $id)
                ->where('v.status', 1)
                ->find();

            if ($video) {
                // 处理路径
                if (!empty($video['cover']) && !str_starts_with($video['cover'], 'http')) {
                    $video['cover'] = request()->domain() . $video['cover'];
                }
                if (!empty($video['video_url']) && !str_starts_with($video['video_url'], 'http')) {
                    $video['video_url'] = request()->domain() . $video['video_url'];
                }

                // 增加播放次数
                Db::table('videos')->where('id', $id)->inc('view_count')->update();
            }

            return $video;
        } catch (\Exception $e) {
            return null;
        }
    }
}
