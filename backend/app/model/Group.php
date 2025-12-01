<?php

namespace app\model;

/**
 * 通用分组模型
 * 替代：link_groups, slider_groups, point_shop_categories, ad_positions
 */
class Group extends SiteModel
{
    protected $name = 'groups';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'parent_id' => 'integer',
        'sort' => 'integer',
        'status' => 'integer',
        'site_id' => 'integer',
        'config' => 'json',
    ];

    // 分组类型常量
    const TYPE_LINK = 'link';              // 友情链接分组
    const TYPE_SLIDER = 'slider';          // 幻灯片分组
    const TYPE_POINT_SHOP = 'point_shop';  // 积分商品分类
    const TYPE_AD = 'ad';                  // 广告位

    // 状态常量
    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用

    /**
     * 关联友情链接
     */
    public function links()
    {
        return $this->hasMany(Link::class, 'group_id', 'id')
            ->order('sort', 'asc');
    }

    /**
     * 关联幻灯片
     */
    public function sliders()
    {
        return $this->hasMany(Slider::class, 'group_id', 'id')
            ->order('sort', 'asc');
    }

    /**
     * 获取启用的幻灯片
     */
    public function activeSliders()
    {
        return $this->hasMany(Slider::class, 'group_id', 'id')
            ->where('status', Slider::STATUS_ENABLED)
            ->where(function($query) {
                $now = date('Y-m-d H:i:s');
                $query->where(function($q) use ($now) {
                    $q->whereNull('start_time')
                      ->whereOr('start_time', '<=', $now);
                })->where(function($q) use ($now) {
                    $q->whereNull('end_time')
                      ->whereOr('end_time', '>=', $now);
                });
            })
            ->order('sort', 'asc');
    }

    /**
     * 关联广告
     */
    public function ads()
    {
        return $this->hasMany(Ad::class, 'position_id', 'id')
            ->order('sort', 'asc');
    }

    /**
     * 获取所有类型
     */
    public static function getTypeList()
    {
        return [
            self::TYPE_LINK => '友情链接分组',
            self::TYPE_SLIDER => '幻灯片分组',
            self::TYPE_POINT_SHOP => '积分商品分类',
            self::TYPE_AD => '广告位',
        ];
    }

    /**
     * 获取所有状态
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_DISABLED => '禁用',
            self::STATUS_ENABLED => '启用',
        ];
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = $data['status'] ?? 0;
        $list = self::getStatusList();
        return $list[$status] ?? '未知';
    }

    /**
     * 获取类型文本
     */
    public function getTypeTextAttr($value, $data)
    {
        $type = $data['type'] ?? '';
        $list = self::getTypeList();
        return $list[$type] ?? '未知';
    }

    /**
     * 根据类型获取分组列表
     */
    public static function getByType($type, $status = null)
    {
        $query = self::where('type', $type);

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->order('sort', 'asc')->select();
    }

    /**
     * 根据slug获取分组
     */
    public static function getBySlug($slug, $type = null)
    {
        $query = self::where('slug', $slug);

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->find();
    }

    /**
     * 获取友情链接分组列表
     */
    public static function getLinkGroups($status = self::STATUS_ENABLED)
    {
        return self::getByType(self::TYPE_LINK, $status);
    }

    /**
     * 获取幻灯片分组列表
     */
    public static function getSliderGroups($status = self::STATUS_ENABLED)
    {
        return self::getByType(self::TYPE_SLIDER, $status);
    }

    /**
     * 获取积分商品分类列表
     */
    public static function getPointShopCategories($status = self::STATUS_ENABLED)
    {
        return self::getByType(self::TYPE_POINT_SHOP, $status);
    }

    /**
     * 获取广告位列表
     */
    public static function getAdPositions($status = self::STATUS_ENABLED)
    {
        return self::getByType(self::TYPE_AD, $status);
    }

    /**
     * 创建友情链接分组
     */
    public static function createLinkGroup($data)
    {
        $data['type'] = self::TYPE_LINK;
        return self::create($data);
    }

    /**
     * 创建幻灯片分组
     */
    public static function createSliderGroup($data)
    {
        $data['type'] = self::TYPE_SLIDER;
        return self::create($data);
    }

    /**
     * 创建积分商品分类
     */
    public static function createPointShopCategory($data)
    {
        $data['type'] = self::TYPE_POINT_SHOP;
        return self::create($data);
    }

    /**
     * 创建广告位
     */
    public static function createAdPosition($data)
    {
        $data['type'] = self::TYPE_AD;
        return self::create($data);
    }
}
