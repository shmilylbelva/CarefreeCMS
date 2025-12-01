<?php

namespace app\model;

use think\Model;

/**
 * AI模型模型
 */
class AiModel extends Model
{
    protected $name = 'ai_models';

    // 自动时间戳
    protected $autoWriteTimestamp = true;

    // 字段类型定义
    protected $type = [
        'pricing_info' => 'json',
        'extra_config' => 'json',
    ];

    /**
     * 关联厂商
     */
    public function provider()
    {
        return $this->belongsTo(AiProvider::class, 'provider_id', 'id');
    }

    /**
     * 获取指定厂商的模型列表
     */
    public static function getModelsByProvider($providerId)
    {
        return self::where('provider_id', $providerId)
            ->where('status', 1)
            ->order('sort_order', 'asc')
            ->order('id', 'asc')
            ->select();
    }

    /**
     * 获取指定厂商的模型列表（带厂商信息）
     */
    public static function getModelsWithProvider($providerId = null)
    {
        $query = self::with('provider');

        if ($providerId) {
            $query->where('provider_id', $providerId);
        }

        return $query->where('ai_models.status', 1)
            ->order('provider_id', 'asc')
            ->order('sort_order', 'asc')
            ->select();
    }

    /**
     * 获取所有启用的模型（按厂商分组）
     */
    public static function getAllModelsGrouped()
    {
        $models = self::alias('m')
            ->join('ai_providers p', 'm.provider_id = p.id')
            ->where('m.status', 1)
            ->where('p.status', 1)
            ->field('m.*, p.code as provider_code, p.name as provider_name')
            ->order('p.sort_order', 'asc')
            ->order('m.sort_order', 'asc')
            ->select()
            ->toArray();

        // 按厂商分组
        $grouped = [];
        foreach ($models as $model) {
            $providerCode = $model['provider_code'];
            if (!isset($grouped[$providerCode])) {
                $grouped[$providerCode] = [];
            }
            $grouped[$providerCode][] = [
                'value' => $model['model_code'],
                'label' => $model['model_name'],
                'description' => $model['description'] ?? ''
            ];
        }

        return $grouped;
    }

    /**
     * 根据代码获取模型
     */
    public static function getByCode($modelCode, $providerId = null)
    {
        $query = self::where('model_code', $modelCode);
        if ($providerId) {
            $query->where('provider_id', $providerId);
        }
        return $query->find();
    }
}
