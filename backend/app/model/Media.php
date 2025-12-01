<?php

namespace app\model;

use think\model\concern\SoftDelete;
use app\model\Config;

/**
 * 媒体库模型（旧版本，已废弃）
 *
 * 注意：这是旧版媒体库模型，已废弃。
 * 新代码请使用 MediaLibrary 模型。
 * 此模型保留用于向后兼容。
 */
class Media extends SiteModel
{
    use SoftDelete;

    protected $name = 'media_legacy';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = false;
    protected $deleteTime = 'deleted_at';
    protected $defaultSoftDelete = null;

    protected $type = [
        'user_id'   => 'integer',
        'file_size' => 'integer',
        'width'     => 'integer',
        'height'    => 'integer',
    ];

    /**
     * 关联上传者
     */
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id', 'id');
    }

    /**
     * 搜索器：文件类型
     */
    public function searchFileTypeAttr($query, $value)
    {
        $query->where('file_type', $value);
    }

    /**
     * 获取器：文件URL（动态生成）
     */
    public function getFileUrlAttr($value, $data)
    {
        // 如果file_path为空，返回空字符串
        if (empty($data['file_path'])) {
            return '';
        }

        // 使用当前站点的site_url字段
        $site = \app\service\SiteContextService::getSite();
        $siteUrl = $site ? $site->site_url : '';

        if (!empty($siteUrl)) {
            // 如果站点配置了site_url，使用站点URL
            return rtrim($siteUrl, '/') . '/' . $data['file_path'];
        } else {
            // 如果没有配置，使用API域名 + /html
            return request()->domain() . '/html/' . $data['file_path'];
        }
    }

    /**
     * 获取器：文件大小格式化
     */
    public function getFileSizeTextAttr($value, $data)
    {
        $size = $data['file_size'];
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}
