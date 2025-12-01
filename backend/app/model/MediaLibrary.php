<?php
declare (strict_types = 1);

namespace app\model;

/**
 * 媒体库业务管理模型
 * 存储用户上传的媒体业务信息，支持多站点
 * 继承SiteModel实现多站点数据隔离
 */
class MediaLibrary extends SiteModel
{
    protected $name = 'media_library';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $deleteTime = 'deleted_at'; // 软删除

    protected $type = [
        'file_id' => 'integer',
        'site_id' => 'integer',
        'user_id' => 'integer',
        'source_id' => 'integer',
        'is_public' => 'integer',
        'view_count' => 'integer',
        'download_count' => 'integer',
    ];

    // 来源类型常量
    const SOURCE_UPLOAD = 'upload';         // 手动上传
    const SOURCE_AI_GENERATE = 'ai_generate'; // AI生成
    const SOURCE_EXTERNAL = 'external';     // 外部链接

    // 状态常量
    const STATUS_ACTIVE = 'active';         // 正常
    const STATUS_PROCESSING = 'processing'; // 处理中
    const STATUS_FAILED = 'failed';         // 失败
    const STATUS_DELETED = 'deleted';       // 已删除

    /**
     * 关联物理文件
     */
    public function file()
    {
        return $this->belongsTo(MediaFile::class, 'file_id', 'id');
    }

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id', 'id');
    }

    /**
     * 关联元数据
     */
    public function metadata()
    {
        return $this->hasMany(MediaMetadata::class, 'media_id');
    }

    /**
     * 关联缩略图
     */
    public function thumbnails()
    {
        return $this->hasMany(MediaThumbnail::class, 'media_id');
    }

    /**
     * 关联水印日志
     */
    public function watermarkLogs()
    {
        return $this->hasMany(MediaWatermarkLog::class, 'media_id');
    }

    /**
     * 关联编辑历史
     */
    public function editHistory()
    {
        return $this->hasMany(MediaEditHistory::class, 'media_id');
    }

    /**
     * 关联视频转码记录
     */
    public function videoTranscodes()
    {
        return $this->hasMany(MediaVideoTranscode::class, 'media_id');
    }

    /**
     * 获取文件URL（通过关联的file获取）
     */
    public function getFileUrlAttr($value, $data)
    {
        if (!isset($data['file_id'])) {
            return '';
        }

        return $this->file ? $this->file->file_url : '';
    }

    /**
     * 获取文件类型
     */
    public function getFileTypeAttr($value, $data)
    {
        return $this->file ? $this->file->file_type : '';
    }

    /**
     * 获取文件大小
     */
    public function getFileSizeAttr($value, $data)
    {
        return $this->file ? $this->file->file_size : 0;
    }

    /**
     * 获取文件扩展名
     */
    public function getFileExtAttr($value, $data)
    {
        return $this->file ? $this->file->file_ext : '';
    }

    /**
     * 获取特定规格的缩略图URL
     */
    public function getThumbnailUrl(string $preset = 'medium'): ?string
    {
        $thumbnail = $this->thumbnails()
            ->where('preset_name', $preset)
            ->find();

        if ($thumbnail) {
            $request = request();
            $domain = $request->domain();
            return $domain . '/' . ltrim($thumbnail->file_path, '/');
        }

        return null;
    }

    /**
     * 获取所有缩略图URLs
     */
    public function getAllThumbnails(): array
    {
        $result = [];
        $thumbnails = $this->thumbnails;

        foreach ($thumbnails as $thumb) {
            $request = request();
            $domain = $request->domain();
            $result[$thumb->preset_name] = $domain . '/' . ltrim($thumb->file_path, '/');
        }

        return $result;
    }

    /**
     * 增加查看次数
     */
    public function incrementViewCount()
    {
        return $this->inc('view_count')->save();
    }

    /**
     * 增加下载次数
     */
    public function incrementDownloadCount()
    {
        return $this->inc('download_count')->save();
    }

    /**
     * 获取元数据值
     */
    public function getMetaValue(string $key, $default = null)
    {
        $meta = $this->metadata()
            ->where('meta_key', $key)
            ->find();

        if ($meta) {
            return json_decode($meta->meta_value, true);
        }

        return $default;
    }

    /**
     * 设置元数据值
     */
    public function setMetaValue(string $key, $value)
    {
        $meta = $this->metadata()
            ->where('meta_key', $key)
            ->find();

        $jsonValue = is_string($value) ? $value : json_encode($value);

        if ($meta) {
            $meta->meta_value = $jsonValue;
            $meta->save();
        } else {
            MediaMetadata::create([
                'media_id' => $this->id,
                'meta_key' => $key,
                'meta_value' => $jsonValue,
            ]);
        }

        return true;
    }

    /**
     * 模型查询前事件 - 自动预加载file关联
     */
    public static function onAfterRead($model)
    {
        // 自动加载file关联，便于使用
        // ThinkPHP 会自动延迟加载关联，无需手动检查
    }

    /**
     * 删除前事件 - 减少文件引用计数
     * 注意：ThinkPHP 8 中已移除 isForceDelete() 方法
     * 文件引用计数的处理已移至 MediaLibraryService 中
     */
    public static function onBeforeDelete($model)
    {
        // 在 ThinkPHP 8 中，删除逻辑已移至 Service 层处理
        // 此事件保留为空，以便未来扩展
    }
}
