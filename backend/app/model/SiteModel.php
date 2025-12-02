<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use app\service\SiteContextService;
use app\traits\SiteScoped;

/**
 * 支持多站点共享表的基础模型
 * 使用 site_id 字段区分不同站点的数据
 * 所有需要按站点隔离数据的模型都应该继承此类
 *
 * 新版本使用 SiteScoped trait 提供更强大的站点隔离功能
 *
 * @method static \think\db\Query withoutSiteScope() 临时禁用站点过滤
 * @method static \think\db\Query forSite(int|array $siteId) 指定站点查询
 * @method static \think\db\Query forCurrentSite() 当前站点查询
 * @method static \think\db\Query forAllSites() 所有站点查询
 */
abstract class SiteModel extends Model
{
    use SiteScoped;

    /**
     * 是否启用多站点自动过滤（向后兼容）
     * @var bool
     * @deprecated 使用 SiteScoped trait 的方法替代
     */
    protected $multiSiteEnabled = true;

    /**
     * 获取查询对象（自动添加 site_id 条件）
     * @param array|null $scope 查询作用域
     * @return \think\db\BaseQuery
     */
    public function db(?array $scope = []): \think\db\BaseQuery
    {
        $query = parent::db($scope);

        // 如果启用了多站点，自动添加 site_id
        if ($this->multiSiteEnabled) {
            try {
                // 获取当前站点并添加条件
                $site = SiteContextService::getSite();
                $siteId = $site ? $site->id : 1;

                // 使用表名限定列名，避免 JOIN 时的歧义
                $tableName = $this->name;
                $query->where($tableName . '.site_id', $siteId);
            } catch (\Exception $e) {
                // 发生异常，默认查询主站
                $tableName = $this->name;
                $query->where($tableName . '.site_id', 1);
            }
        }

        return $query;
    }

    /**
     * 创建前自动设置 site_id
     * @param \think\Model $model
     */
    public static function onBeforeInsert($model)
    {
        if ($model->multiSiteEnabled && empty($model->site_id)) {
            try {
                $site = SiteContextService::getSite();
                $model->site_id = $site ? $site->id : 1;
            } catch (\Exception $e) {
                $model->site_id = 1;
            }
        }
    }

    /**
     * 查询所有站点的数据
     * @return \think\db\Query
     */
    public static function allSites()
    {
        $model = new static();
        // 临时禁用多站点过滤
        $originalEnabled = $model->multiSiteEnabled;
        $model->multiSiteEnabled = false;

        $query = $model->db();

        // 恢复设置
        $model->multiSiteEnabled = $originalEnabled;

        return $query;
    }

    /**
     * 按站点ID查询
     * @param int $siteId 站点ID
     * @return \think\db\Query
     */
    public static function bySite(int $siteId)
    {
        return (new static())->db()->where('site_id', $siteId);
    }

    /**
     * 无站点限制查询（查询所有站点数据）
     * @return \think\db\Query
     */
    public static function withoutSiteScope()
    {
        $model = new static();
        $model->multiSiteEnabled = false;
        return $model->db();
    }

    /**
     * 无站点限制 + 只查询已删除数据（用于回收站）
     * 直接使用 Db 类查询，绕过 Model 的软删除自动过滤
     * @return \think\db\Query
     */
    public static function withoutSiteScopeTrashed()
    {
        $model = new static();

        // 获取表名（不带前缀的名称，用于 Db::name()）
        $tableName = $model->name;

        // 获取软删除字段名
        $deleteTimeField = 'deleted_at';
        if (property_exists($model, 'deleteTime') && $model->deleteTime) {
            $deleteTimeField = $model->deleteTime;
        }

        // 直接使用 Db 查询，绕过 SoftDelete trait 的自动过滤
        // Db::name() 会自动添加表前缀
        return \think\facade\Db::name($tableName)->whereNotNull($deleteTimeField);
    }

    /**
     * 关联站点
     * @return \think\model\relation\BelongsTo
     */
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'id');
    }
}
