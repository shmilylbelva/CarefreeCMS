<?php
declare(strict_types=1);

namespace app\facade;

use app\service\SiteContextService;
use think\Facade;

/**
 * 站点查询门面类
 *
 * 提供便捷的站点相关查询方法
 *
 * @method static int|null current() 获取当前站点ID
 * @method static bool setCurrent(int $siteId) 设置当前站点ID
 * @method static bool isCurrent(int $siteId) 检查是否为当前站点
 * @method static array ids(bool $enabledOnly = true) 获取所有站点ID列表
 * @method static \app\model\Site get(int $siteId) 获取指定站点
 * @method static \app\model\Site getByCode(string $siteCode) 根据站点代码获取
 * @method static \app\model\Site main() 获取主站
 * @method static array enabled() 获取所有启用的站点
 * @method static bool exists(int $siteId) 检查站点是否存在
 * @method static bool isEnabled(int $siteId) 检查站点是否启用
 * @method static string name(int $siteId) 获取站点名称
 *
 * @see \app\service\SiteQueryService
 */
class SiteQuery extends Facade
{
    /**
     * 获取当前Facade对应类名
     */
    protected static function getFacadeClass()
    {
        return \app\service\SiteQueryService::class;
    }
}
