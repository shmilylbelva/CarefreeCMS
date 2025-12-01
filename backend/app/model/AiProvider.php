<?php

namespace app\model;

use think\Model;

/**
 * AI厂商模型
 */
class AiProvider extends Model
{
    protected $name = 'ai_providers';

    // 自动时间戳
    protected $autoWriteTimestamp = true;

    // 字段类型定义
    protected $type = [
        'config_fields' => 'json',
    ];

    /**
     * 关联模型列表
     */
    public function models()
    {
        return $this->hasMany(AiModel::class, 'provider_id');
    }

    /**
     * 获取启用的厂商列表
     */
    public static function getActiveProviders()
    {
        return self::where('status', 1)
            ->order('sort_order', 'asc')
            ->order('id', 'asc')
            ->select();
    }

    /**
     * 获取厂商代码映射
     */
    public static function getProviderCodeMap()
    {
        $providers = self::where('status', 1)->select();
        $map = [];
        foreach ($providers as $provider) {
            $map[$provider->code] = $provider->name;
        }
        return $map;
    }

    /**
     * 根据代码获取厂商
     */
    public static function getByCode($code)
    {
        return self::where('code', $code)->find();
    }

    /**
     * 检查代码是否已存在
     */
    public static function codeExists($code, $excludeId = null)
    {
        $query = self::where('code', $code);
        if ($excludeId) {
            $query->where('id', '<>', $excludeId);
        }
        return $query->count() > 0;
    }
}
