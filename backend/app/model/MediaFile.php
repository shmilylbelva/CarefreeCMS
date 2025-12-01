<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 媒体文件物理存储模型
 * 存储实际文件的物理信息，支持文件去重
 * 注意：此表不继承SiteModel，因为物理文件是跨站点共享的
 */
class MediaFile extends Model
{
    protected $name = 'media_files';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
        'ref_count' => 'integer',
    ];

    // 文件类型常量
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_DOCUMENT = 'document';
    const TYPE_OTHER = 'other';

    // 存储类型常量
    const STORAGE_LOCAL = 'local';
    const STORAGE_OSS = 'oss';
    const STORAGE_COS = 'cos';
    const STORAGE_QINIU = 'qiniu';

    /**
     * 关联媒体库记录（一个文件可能被多个站点引用）
     */
    public function mediaLibrary()
    {
        return $this->hasMany(MediaLibrary::class, 'file_id');
    }

    /**
     * 增加引用计数
     */
    public function incrementRefCount()
    {
        return $this->inc('ref_count')->save();
    }

    /**
     * 减少引用计数
     */
    public function decrementRefCount()
    {
        if ($this->ref_count > 0) {
            return $this->dec('ref_count')->save();
        }
        return false;
    }

    /**
     * 获取文件完整URL（获取器）
     */
    public function getFileUrlAttr($value, $data)
    {
        // 如果数据库中已经存储了完整URL，直接使用
        if (!empty($value)) {
            // 如果是相对路径，加上域名
            if (strpos($value, 'http') !== 0) {
                $request = request();
                $domain = $request->domain();
                return $domain . $value;
            }
            return $value;
        }

        if (empty($data['file_path'])) {
            return '';
        }

        // 如果是云存储，返回云存储完整路径
        if (!empty($data['storage_path']) && $data['storage_type'] !== self::STORAGE_LOCAL) {
            return $data['storage_path'];
        }

        // 本地存储，生成URL（添加uploads前缀）
        $request = request();
        $domain = $request->domain();
        return $domain . '/uploads/' . ltrim($data['file_path'], '/');
    }

    /**
     * 获取文件完整路径（用于本地文件操作）
     */
    public function getFullPath()
    {
        if ($this->storage_type === self::STORAGE_LOCAL) {
            return app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $this->file_path;
        }
        return null;
    }

    /**
     * 根据文件hash查找文件
     */
    public static function findByHash(string $hash)
    {
        return self::where('file_hash', $hash)->find();
    }

    /**
     * 计算文件hash值
     * @param string $filePath 文件绝对路径
     * @return string SHA256哈希值
     */
    public static function calculateHash(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \Exception('文件不存在');
        }
        return hash_file('sha256', $filePath);
    }

    /**
     * 判断文件是否可以删除（引用计数为0）
     */
    public function canDelete(): bool
    {
        return $this->ref_count <= 0;
    }

    /**
     * 删除物理文件
     */
    public function deletePhysicalFile(): bool
    {
        if ($this->storage_type === self::STORAGE_LOCAL) {
            $fullPath = $this->getFullPath();
            if ($fullPath && file_exists($fullPath)) {
                return @unlink($fullPath);
            }
        }
        // TODO: 添加云存储删除逻辑
        return true;
    }

    /**
     * 根据MIME类型判断文件类型
     */
    public static function getFileTypeByMime(string $mimeType): string
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/x-icon'];
        $videoTypes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-ms-wmv', 'video/x-flv', 'video/x-matroska'];
        $audioTypes = ['audio/mpeg', 'audio/wav', 'audio/x-ms-wma', 'audio/ogg', 'audio/flac'];

        if (in_array($mimeType, $imageTypes) || strpos($mimeType, 'image/') === 0) {
            return self::TYPE_IMAGE;
        } elseif (in_array($mimeType, $videoTypes) || strpos($mimeType, 'video/') === 0) {
            return self::TYPE_VIDEO;
        } elseif (in_array($mimeType, $audioTypes) || strpos($mimeType, 'audio/') === 0) {
            return self::TYPE_AUDIO;
        } elseif (strpos($mimeType, 'application/') === 0 || strpos($mimeType, 'text/') === 0) {
            return self::TYPE_DOCUMENT;
        }

        return self::TYPE_OTHER;
    }
}
