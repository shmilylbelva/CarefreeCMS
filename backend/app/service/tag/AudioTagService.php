<?php
namespace app\service\tag;

use think\facade\Db;

/**
 * 音频标签服务类
 * 处理音频标签的数据查询
 */
class AudioTagService
{
    /**
     * 获取音频列表
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
            $query = Db::table('audios')
                ->alias('a')
                ->leftJoin('categories c', 'a.category_id = c.id')
                ->leftJoin('admin_users u', 'a.user_id = u.id')
                ->field('a.*, c.name as category_name, u.username as author_name')
                ->where('a.status', 1);

            // 按分类筛选
            if ($catid > 0) {
                $query->where('a.category_id', $catid);
            }

            // 精选筛选
            if ($featured == 1) {
                $query->where('a.is_featured', 1);
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

            $audios = $query->select()->toArray();

            // 处理音频数据
            foreach ($audios as &$audio) {
                // 格式化播放次数
                $audio['play_count_formatted'] = self::formatPlayCount($audio['play_count'] ?? 0);

                // 格式化时长
                $audio['duration_formatted'] = self::formatDuration($audio['duration'] ?? 0);

                // 确保封面图片路径完整
                if (!empty($audio['cover']) && !str_starts_with($audio['cover'], 'http')) {
                    $audio['cover'] = request()->domain() . $audio['cover'];
                }

                // 确保音频路径完整
                if (!empty($audio['audio_url']) && !str_starts_with($audio['audio_url'], 'http')) {
                    $audio['audio_url'] = request()->domain() . $audio['audio_url'];
                }

                // 格式化文件大小
                if (!empty($audio['file_size'])) {
                    $audio['file_size_formatted'] = self::formatFileSize($audio['file_size']);
                }

                // 格式化发布时间
                if (!empty($audio['create_time'])) {
                    $audio['create_time_formatted'] = date('Y-m-d', is_numeric($audio['create_time']) ? $audio['create_time'] : strtotime($audio['create_time']));
                }
            }

            return $audios;
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
    private static function formatPlayCount($count)
    {
        if ($count >= 10000) {
            return round($count / 10000, 1) . '万';
        } elseif ($count >= 1000) {
            return round($count / 1000, 1) . 'k';
        }
        return (string)$count;
    }

    /**
     * 格式化时长
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
     * 格式化文件大小
     *
     * @param int $bytes 字节数
     * @return string
     */
    private static function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 获取单个音频信息
     *
     * @param int $id 音频ID
     * @return array|null
     */
    public static function getOne($id)
    {
        try {
            $audio = Db::table('audios')
                ->alias('a')
                ->leftJoin('categories c', 'a.category_id = c.id')
                ->leftJoin('admin_users u', 'a.user_id = u.id')
                ->field('a.*, c.name as category_name, u.username as author_name')
                ->where('a.id', $id)
                ->where('a.status', 1)
                ->find();

            if ($audio) {
                // 处理路径
                if (!empty($audio['cover']) && !str_starts_with($audio['cover'], 'http')) {
                    $audio['cover'] = request()->domain() . $audio['cover'];
                }
                if (!empty($audio['audio_url']) && !str_starts_with($audio['audio_url'], 'http')) {
                    $audio['audio_url'] = request()->domain() . $audio['audio_url'];
                }

                // 增加播放次数
                Db::table('audios')->where('id', $id)->inc('play_count')->update();
            }

            return $audio;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取音频专辑列表
     *
     * @param int $albumId 专辑ID
     * @param int $limit 数量限制
     * @return array
     */
    public static function getAlbumList($albumId, $limit = 20)
    {
        try {
            return Db::table('audios')
                ->where('album_id', $albumId)
                ->where('status', 1)
                ->order('sort', 'asc')
                ->limit($limit)
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
