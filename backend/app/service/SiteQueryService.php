<?php
declare(strict_types=1);

namespace app\service;

use app\model\Site;

/**
 * 站点查询服务
 * 提供便捷的站点查询和管理方法
 */
class SiteQueryService
{
    /**
     * 缓存的站点数据
     */
    protected static $sitesCache = null;
    protected static $sitesByCode = null;

    /**
     * 获取当前站点ID
     */
    public function current(): ?int
    {
        return SiteContextService::getSiteId();
    }

    /**
     * 设置当前站点ID
     */
    public function setCurrent(int $siteId): bool
    {
        return SiteContextService::switchSite($siteId);
    }

    /**
     * 检查是否为当前站点
     */
    public function isCurrent(int $siteId): bool
    {
        return $this->current() === $siteId;
    }

    /**
     * 获取所有站点ID列表
     *
     * @param bool $enabledOnly 是否只返回启用的站点
     * @return array
     */
    public function ids(bool $enabledOnly = true): array
    {
        $sites = $this->all($enabledOnly);
        return array_column($sites, 'id');
    }

    /**
     * 获取所有站点
     *
     * @param bool $enabledOnly
     * @return array
     */
    public function all(bool $enabledOnly = true): array
    {
        if (self::$sitesCache === null) {
            self::$sitesCache = Site::forAllSites()
                ->field('id,site_code,site_name,site_type,status')
                ->select()
                ->toArray();
        }

        if ($enabledOnly) {
            return array_filter(self::$sitesCache, function ($site) {
                return $site['status'] == Site::STATUS_ENABLED;
            });
        }

        return self::$sitesCache;
    }

    /**
     * 获取指定站点
     */
    public function get(int $siteId): ?Site
    {
        return Site::forSite($siteId)->find($siteId);
    }

    /**
     * 根据站点代码获取站点
     */
    public function getByCode(string $siteCode): ?Site
    {
        if (self::$sitesByCode === null) {
            $sites = $this->all(false);
            self::$sitesByCode = array_column($sites, null, 'site_code');
        }

        $siteData = self::$sitesByCode[$siteCode] ?? null;
        if ($siteData) {
            return Site::forSite($siteData['id'])->find($siteData['id']);
        }

        return null;
    }

    /**
     * 获取主站
     */
    public function main(): ?Site
    {
        return Site::getMainSite();
    }

    /**
     * 获取所有启用的站点
     */
    public function enabled(): array
    {
        return Site::getEnabledSites()->toArray();
    }

    /**
     * 检查站点是否存在
     */
    public function exists(int $siteId): bool
    {
        $sites = $this->all(false);
        foreach ($sites as $site) {
            if ($site['id'] === $siteId) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检查站点是否启用
     */
    public function isEnabled(int $siteId): bool
    {
        $sites = $this->all(true);
        foreach ($sites as $site) {
            if ($site['id'] === $siteId) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取站点名称
     */
    public function name(int $siteId): string
    {
        $sites = $this->all(false);
        foreach ($sites as $site) {
            if ($site['id'] === $siteId) {
                return $site['site_name'];
            }
        }
        return '';
    }

    /**
     * 清除缓存
     */
    public function clearCache()
    {
        self::$sitesCache = null;
        self::$sitesByCode = null;
    }

    /**
     * 过滤查询结果（只保留当前站点的数据）
     *
     * @param array|\think\Collection $data
     * @param string $siteField
     * @return array
     */
    public function filterCurrent($data, string $siteField = 'site_id'): array
    {
        $currentSiteId = $this->current();

        if ($currentSiteId === null) {
            return is_array($data) ? $data : $data->toArray();
        }

        $filtered = [];
        foreach ($data as $item) {
            $itemArray = is_array($item) ? $item : $item->toArray();
            if (isset($itemArray[$siteField]) && $itemArray[$siteField] == $currentSiteId) {
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    /**
     * 批量检查数据是否属于当前站点
     *
     * @param array $ids
     * @param string $modelClass
     * @param string $siteField
     * @return bool
     */
    public function belongsToCurrent(array $ids, string $modelClass, string $siteField = 'site_id'): bool
    {
        $currentSiteId = $this->current();

        if ($currentSiteId === null) {
            return true; // 如果没有当前站点，认为都属于
        }

        $count = $modelClass::forAllSites()
            ->whereIn('id', $ids)
            ->where($siteField, $currentSiteId)
            ->count();

        return $count === count($ids);
    }

    /**
     * 确保数据属于当前站点（不属于则抛出异常）
     *
     * @param int $id
     * @param string $modelClass
     * @param string $siteField
     * @throws \RuntimeException
     */
    public function ensureBelongsToCurrent(int $id, string $modelClass, string $siteField = 'site_id')
    {
        if (!$this->belongsToCurrent([$id], $modelClass, $siteField)) {
            throw new \RuntimeException('数据不属于当前站点');
        }
    }
}
