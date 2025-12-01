<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 模板模型
 * 支持多站点数据隔离
 */
class Template extends SiteModel
{
    protected $name = 'templates';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id' => 'integer',
        'site_id' => 'integer',
        'package_id' => 'integer',
        'is_default' => 'integer',
        'is_package_default' => 'integer',
        'parent_template_id' => 'integer',
        'status' => 'integer',
        'variables' => 'json',
        'config_schema' => 'json',
    ];

    // 状态常量
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    // 模板类型常量
    const TYPE_INDEX = 'index';
    const TYPE_CATEGORY = 'category';
    const TYPE_ARTICLE = 'article';
    const TYPE_ARTICLES = 'articles';
    const TYPE_PAGE = 'page';
    const TYPE_TAG = 'tag';
    const TYPE_SEARCH = 'search';
    const TYPE_404 = '404';

    /**
     * 关联使用该模板的站点（废弃）
     */
    public function sites()
    {
        return $this->hasMany(Site::class, 'template_id', 'id');
    }

    /**
     * 关联模板包
     */
    public function package()
    {
        return $this->belongsTo(TemplatePackage::class, 'package_id', 'id');
    }

    /**
     * 关联父模板（继承）
     */
    public function parent()
    {
        return $this->belongsTo(Template::class, 'parent_template_id', 'id');
    }

    /**
     * 关联子模板（被继承）
     */
    public function children()
    {
        return $this->hasMany(Template::class, 'parent_template_id', 'id');
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED  => '启用',
        ];
        return $status[$data['status']] ?? '未知';
    }

    /**
     * 获取模板类型文本
     */
    public function getTemplateTypeTextAttr($value, $data)
    {
        $types = self::getTemplateTypes();
        return $types[$data['template_type']] ?? $data['template_type'];
    }

    /**
     * 获取所有模板类型
     */
    public static function getTemplateTypes()
    {
        return [
            self::TYPE_INDEX => '首页',
            self::TYPE_CATEGORY => '分类页',
            self::TYPE_ARTICLE => '文章详情页',
            self::TYPE_ARTICLES => '文章列表页',
            self::TYPE_PAGE => '单页',
            self::TYPE_TAG => '标签页',
            self::TYPE_SEARCH => '搜索页',
            self::TYPE_404 => '404页面',
        ];
    }

    /**
     * 根据模板包获取模板列表
     * 注意：模板包的模板通常是全局共享的（site_id=0），所以需要禁用站点过滤
     */
    public static function getByPackage($packageId, $templateType = null)
    {
        $query = self::withoutSiteScope()
            ->where('package_id', $packageId)
            ->where('status', self::STATUS_ENABLED);

        if ($templateType) {
            $query->where('template_type', $templateType);
        }

        return $query->select();
    }

    /**
     * 获取模板包内指定类型的默认模板
     */
    public static function getPackageDefault($packageId, $templateType)
    {
        return self::withoutSiteScope()
            ->where('package_id', $packageId)
            ->where('template_type', $templateType)
            ->where('is_package_default', 1)
            ->where('status', self::STATUS_ENABLED)
            ->find();
    }

    /**
     * 搜索器：模板包ID
     */
    public function searchPackageIdAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('package_id', $value);
        }
    }

    /**
     * 搜索器：模板类型
     */
    public function searchTemplateTypeAttr($query, $value)
    {
        if ($value) {
            $query->where('template_type', $value);
        }
    }

    /**
     * 搜索器：状态
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('status', $value);
        }
    }
}
