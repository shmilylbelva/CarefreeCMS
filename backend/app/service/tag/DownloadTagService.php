<?php
namespace app\service\tag;

use think\facade\Db;

/**
 * 下载标签服务类
 * 处理文件下载标签的数据查询
 */
class DownloadTagService
{
    /**
     * 获取下载文件列表
     *
     * @param array $params 查询参数
     *   - catid: 分类ID
     *   - limit: 数量限制
     *   - orderby: 排序方式
     *   - type: 文件类型（doc, pdf, zip, image, video, audio, software等）
     * @return array
     */
    public static function getList($params = [])
    {
        $catid = $params['catid'] ?? 0;
        $limit = $params['limit'] ?? 10;
        $orderby = $params['orderby'] ?? 'create_time desc';
        $type = $params['type'] ?? '';

        try {
            // 构建查询
            $query = Db::table('downloads')
                ->alias('d')
                ->leftJoin('categories c', 'd.category_id = c.id')
                ->leftJoin('admin_users u', 'd.user_id = u.id')
                ->field('d.*, c.name as category_name, u.username as uploader_name')
                ->where('d.status', 1);

            // 按分类筛选
            if ($catid > 0) {
                $query->where('d.category_id', $catid);
            }

            // 按文件类型筛选
            if (!empty($type)) {
                $extensions = self::getExtensionsByType($type);
                if (!empty($extensions)) {
                    $query->where(function($query) use ($extensions) {
                        foreach ($extensions as $ext) {
                            $query->whereOr('d.file_extension', $ext);
                        }
                    });
                }
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

            $downloads = $query->select()->toArray();

            // 处理下载数据
            foreach ($downloads as &$download) {
                // 格式化文件大小
                $download['file_size_formatted'] = self::formatFileSize($download['file_size'] ?? 0);

                // 格式化下载次数
                $download['download_count_formatted'] = self::formatDownloadCount($download['download_count'] ?? 0);

                // 确保文件路径完整
                if (!empty($download['file_url']) && !str_starts_with($download['file_url'], 'http')) {
                    $download['file_url'] = request()->domain() . $download['file_url'];
                }

                // 确保图标路径完整
                if (!empty($download['icon']) && !str_starts_with($download['icon'], 'http')) {
                    $download['icon'] = request()->domain() . $download['icon'];
                } else if (empty($download['icon'])) {
                    // 根据文件类型生成默认图标
                    $download['icon'] = self::getDefaultIcon($download['file_extension'] ?? '');
                }

                // 格式化发布时间
                if (!empty($download['create_time'])) {
                    $download['create_time_formatted'] = date('Y-m-d', is_numeric($download['create_time']) ? $download['create_time'] : strtotime($download['create_time']));
                }

                // 添加文件类型标签
                $download['type_label'] = self::getTypeLabel($download['file_extension'] ?? '');
            }

            return $downloads;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 根据类型获取文件扩展名
     *
     * @param string $type 类型
     * @return array
     */
    private static function getExtensionsByType($type)
    {
        $typeMap = [
            'doc' => ['doc', 'docx', 'txt', 'rtf', 'odt'],
            'pdf' => ['pdf'],
            'zip' => ['zip', 'rar', '7z', 'tar', 'gz'],
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'],
            'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'],
            'audio' => ['mp3', 'wav', 'flac', 'aac', 'ogg', 'wma'],
            'software' => ['exe', 'dmg', 'apk', 'deb', 'rpm'],
            'excel' => ['xls', 'xlsx', 'csv'],
            'ppt' => ['ppt', 'pptx'],
            'code' => ['js', 'php', 'py', 'java', 'c', 'cpp', 'html', 'css'],
        ];

        return $typeMap[$type] ?? [];
    }

    /**
     * 格式化文件大小
     *
     * @param int $bytes 字节数
     * @return string
     */
    private static function formatFileSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 格式化下载次数
     *
     * @param int $count 下载次数
     * @return string
     */
    private static function formatDownloadCount($count)
    {
        if ($count >= 10000) {
            return round($count / 10000, 1) . '万';
        } elseif ($count >= 1000) {
            return round($count / 1000, 1) . 'k';
        }
        return (string)$count;
    }

    /**
     * 获取默认图标
     *
     * @param string $extension 文件扩展名
     * @return string
     */
    private static function getDefaultIcon($extension)
    {
        $iconMap = [
            'pdf' => '/static/icons/file-pdf.svg',
            'doc' => '/static/icons/file-word.svg',
            'docx' => '/static/icons/file-word.svg',
            'xls' => '/static/icons/file-excel.svg',
            'xlsx' => '/static/icons/file-excel.svg',
            'ppt' => '/static/icons/file-ppt.svg',
            'pptx' => '/static/icons/file-ppt.svg',
            'zip' => '/static/icons/file-zip.svg',
            'rar' => '/static/icons/file-zip.svg',
            'jpg' => '/static/icons/file-image.svg',
            'png' => '/static/icons/file-image.svg',
            'mp4' => '/static/icons/file-video.svg',
            'mp3' => '/static/icons/file-audio.svg',
        ];

        return $iconMap[$extension] ?? '/static/icons/file-default.svg';
    }

    /**
     * 获取类型标签
     *
     * @param string $extension 文件扩展名
     * @return string
     */
    private static function getTypeLabel($extension)
    {
        $labelMap = [
            'pdf' => 'PDF文档',
            'doc' => 'Word文档',
            'docx' => 'Word文档',
            'xls' => 'Excel表格',
            'xlsx' => 'Excel表格',
            'ppt' => 'PPT演示',
            'pptx' => 'PPT演示',
            'zip' => '压缩文件',
            'rar' => '压缩文件',
            '7z' => '压缩文件',
            'jpg' => '图片',
            'png' => '图片',
            'gif' => '图片',
            'mp4' => '视频',
            'avi' => '视频',
            'mp3' => '音频',
            'wav' => '音频',
            'exe' => '软件',
            'apk' => '安卓应用',
        ];

        return $labelMap[$extension] ?? '文件';
    }

    /**
     * 获取单个下载文件信息
     *
     * @param int $id 文件ID
     * @return array|null
     */
    public static function getOne($id)
    {
        try {
            $download = Db::table('downloads')
                ->alias('d')
                ->leftJoin('categories c', 'd.category_id = c.id')
                ->leftJoin('admin_users u', 'd.user_id = u.id')
                ->field('d.*, c.name as category_name, u.username as uploader_name')
                ->where('d.id', $id)
                ->where('d.status', 1)
                ->find();

            if ($download) {
                // 处理路径
                if (!empty($download['file_url']) && !str_starts_with($download['file_url'], 'http')) {
                    $download['file_url'] = request()->domain() . $download['file_url'];
                }

                // 增加下载次数
                Db::table('downloads')->where('id', $id)->inc('download_count')->update();

                // 格式化数据
                $download['file_size_formatted'] = self::formatFileSize($download['file_size'] ?? 0);
                $download['type_label'] = self::getTypeLabel($download['file_extension'] ?? '');
            }

            return $download;
        } catch (\Exception $e) {
            return null;
        }
    }
}
