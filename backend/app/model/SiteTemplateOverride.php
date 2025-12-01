<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 站点模板覆盖模型
 */
class SiteTemplateOverride extends Model
{
    protected $name = 'site_template_overrides';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'id' => 'integer',
        'site_id' => 'integer',
        'template_id' => 'integer',
        'priority' => 'integer',
    ];

    // 模板类型常量
    const TYPE_INDEX = 'index';       // 首页
    const TYPE_CATEGORY = 'category'; // 分类页
    const TYPE_ARTICLE = 'article';   // 文章详情页
    const TYPE_ARTICLES = 'articles'; // 文章列表页
    const TYPE_PAGE = 'page';         // 单页
    const TYPE_TAG = 'tag';           // 标签页
    const TYPE_SEARCH = 'search';     // 搜索页
    const TYPE_404 = '404';           // 404页面

    /**
     * 关联站点
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }

    /**
     * 关联模板
     */
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id', 'id');
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
     * 获取站点的所有模板覆盖
     */
    public static function getBySite($siteId)
    {
        return self::where('site_id', $siteId)
            ->with('template')
            ->order('priority', 'desc')
            ->select();
    }

    /**
     * 获取站点指定类型的模板覆盖
     */
    public static function getBySiteAndType($siteId, $templateType)
    {
        return self::where('site_id', $siteId)
            ->where('template_type', $templateType)
            ->with('template')
            ->find();
    }

    /**
     * 设置站点模板覆盖
     */
    public static function setOverride($siteId, $templateType, $templateId, $priority = 0)
    {
        // 查找是否已存在
        $override = self::where('site_id', $siteId)
            ->where('template_type', $templateType)
            ->find();

        if ($override) {
            // 更新
            $override->template_id = $templateId;
            $override->priority = $priority;
            return $override->save();
        } else {
            // 创建
            return self::create([
                'site_id' => $siteId,
                'template_type' => $templateType,
                'template_id' => $templateId,
                'priority' => $priority,
            ]);
        }
    }

    /**
     * 移除站点模板覆盖
     */
    public static function removeOverride($siteId, $templateType)
    {
        return self::where('site_id', $siteId)
            ->where('template_type', $templateType)
            ->delete();
    }

    /**
     * 搜索器：站点ID
     */
    public function searchSiteIdAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('site_id', $value);
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
}
