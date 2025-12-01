<?php
declare(strict_types=1);

namespace app\traits;

use think\db\Query;
use think\Model;

/**
 * 站点作用域 Trait
 * 自动为模型查询添加站点过滤
 *
 * 使用方法:
 * 1. 在模型中 use SiteScoped
 * 2. 确保表中有 site_id 字段
 * 3. 自动应用站点过滤
 *
 * @method static Query withoutSiteScope() 临时禁用站点过滤
 * @method static Query forSite(int|array $siteId) 指定站点查询
 * @method static Query forCurrentSite() 当前站点查询
 * @method static Query forAllSites() 所有站点查询（明确意图）
 */
trait SiteScoped
{
    /**
     * 是否启用站点作用域
     * @var bool
     */
    protected static $siteScope = true;

    /**
     * 站点字段名
     * @var string
     */
    protected $siteField = 'site_id';

    /**
     * 全局查询作用域 - ThinkPHP 8 方式
     * 在模型查询时自动调用
     */
    protected function base(Query $query): void
    {
        // 应用站点作用域
        self::applySiteScope($query);
    }

    /**
     * 应用站点作用域
     */
    protected static function applySiteScope(Query $query)
    {
        // 如果已经禁用站点作用域，跳过
        if (!static::shouldApplySiteScope($query)) {
            return;
        }

        // 获取当前站点ID
        $siteId = static::getCurrentSiteId();

        if ($siteId !== null) {
            $instance = new static();
            $table = $instance->getTable();
            $siteField = $instance->siteField;

            // 检查是否已经有 site_id 条件（避免重复添加）
            $where = $query->getOptions('where');
            if (!static::hasSiteCondition($where, $table, $siteField)) {
                $query->where("{$table}.{$siteField}", $siteId);
            }
        }
    }

    /**
     * 检查是否已有站点条件
     */
    protected static function hasSiteCondition($where, string $table, string $siteField): bool
    {
        if (empty($where)) {
            return false;
        }

        foreach ($where as $condition) {
            if (isset($condition[0])) {
                $field = $condition[0];
                // 检查是否是 site_id 字段
                if ($field === $siteField || $field === "{$table}.{$siteField}") {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * 是否应该应用站点作用域
     */
    protected static function shouldApplySiteScope(Query $query): bool
    {
        // 检查是否已禁用站点作用域
        $options = $query->getOptions();

        // 如果设置了 'without_site_scope' 选项，跳过
        if (isset($options['without_site_scope']) && $options['without_site_scope']) {
            return false;
        }

        // 如果全局禁用，跳过
        if (!static::$siteScope) {
            return false;
        }

        return true;
    }

    /**
     * 获取当前站点ID
     */
    protected static function getCurrentSiteId(): ?int
    {
        // 从请求上下文获取站点ID
        if (app()->has('current_site_id')) {
            return app()->get('current_site_id');
        }

        // 从SiteContextService获取
        if (class_exists(\app\service\SiteContextService::class)) {
            $siteId = \app\service\SiteContextService::getSiteId();
            if ($siteId !== null) {
                // 缓存到应用容器
                app()->bind('current_site_id', $siteId);
                return $siteId;
            }
        }

        return null;
    }

    /**
     * 临时禁用站点作用域
     *
     * 使用示例:
     * Model::withoutSiteScope()->select()
     *
     * @return Query
     */
    public static function withoutSiteScope(): Query
    {
        return static::db()->setOption('without_site_scope', true);
    }

    /**
     * 指定站点查询
     *
     * 使用示例:
     * Model::forSite(1)->select()
     * Model::forSite([1, 2, 3])->select()
     *
     * @param int|array $siteId
     * @return Query
     */
    public static function forSite($siteId): Query
    {
        $instance = new static();
        $siteField = $instance->siteField;

        if (is_array($siteId)) {
            return static::withoutSiteScope()->whereIn($siteField, $siteId);
        }

        return static::withoutSiteScope()->where($siteField, $siteId);
    }

    /**
     * 当前站点查询（明确意图）
     *
     * 使用示例:
     * Model::forCurrentSite()->select()
     *
     * @return Query
     */
    public static function forCurrentSite(): Query
    {
        $siteId = static::getCurrentSiteId();

        if ($siteId === null) {
            throw new \RuntimeException('当前站点ID未设置');
        }

        return static::forSite($siteId);
    }

    /**
     * 所有站点查询（明确意图，比withoutSiteScope更语义化）
     *
     * 使用示例:
     * Model::forAllSites()->select()
     *
     * @return Query
     */
    public static function forAllSites(): Query
    {
        return static::withoutSiteScope();
    }

    /**
     * 全局禁用站点作用域（用于特殊场景）
     */
    public static function disableSiteScope()
    {
        static::$siteScope = false;
    }

    /**
     * 全局启用站点作用域
     */
    public static function enableSiteScope()
    {
        static::$siteScope = true;
    }

    /**
     * 获取站点字段名
     */
    public function getSiteField(): string
    {
        return $this->siteField;
    }

    /**
     * 设置站点字段名（用于非标准字段名）
     */
    public function setSiteField(string $field): self
    {
        $this->siteField = $field;
        return $this;
    }
}
