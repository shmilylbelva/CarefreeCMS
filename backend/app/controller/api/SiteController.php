<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\service\SiteService;
use app\service\SiteContextService;
use think\Request;
use think\Exception;

/**
 * 站点管理控制器
 */
class SiteController extends BaseController
{
    /**
     * 站点服务
     * @var SiteService
     */
    protected $siteService;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->siteService = new SiteService();
    }

    /**
     * 站点列表
     */
    public function index(Request $request)
    {
        $params = $request->get();
        $params['with_parent'] = true;
        $params['with_template'] = true;

        try {
            $result = $this->siteService->getList($params);
            return Response::paginate(
                $result['list']->toArray(),
                $result['total'],
                $result['page'],
                $result['limit']
            );
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 站点详情
     */
    public function read($id)
    {
        try {
            $site = $this->siteService->getDetail($id);
            return Response::success($site->toArray());
        } catch (\Exception $e) {
            return Response::notFound($e->getMessage());
        }
    }

    /**
     * 创建站点
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['site_code'])) {
            return Response::error('站点代码不能为空');
        }
        if (empty($data['site_name'])) {
            return Response::error('站点名称不能为空');
        }

        try {
            $site = $this->siteService->create($data);
            return Response::success($site->toArray(), '站点创建成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 更新站点
     */
    public function update(Request $request, $id)
    {
        $data = $request->post();

        try {
            $this->siteService->update($id, $data);
            return Response::success(null, '站点更新成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 删除站点
     */
    public function delete($id)
    {
        try {
            $this->siteService->delete($id);
            return Response::success(null, '站点删除成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 批量删除站点
     */
    public function batchDelete(Request $request)
    {
        $ids = $request->post('ids', []);

        if (empty($ids)) {
            return Response::error('请选择要删除的站点');
        }

        $count = $this->siteService->batchDelete($ids);

        return Response::success(['deleted_count' => $count], "成功删除 {$count} 个站点");
    }

    /**
     * 更新站点状态
     */
    public function updateStatus(Request $request, $id)
    {
        $status = $request->post('status');

        if ($status === null) {
            return Response::error('状态参数缺失');
        }

        try {
            $this->siteService->updateStatus($id, $status);
            return Response::success(null, '状态更新成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 获取所有启用的站点（用于下拉选择）
     */
    public function options()
    {
        try {
            $options = SiteContextService::getSiteOptions(true);
            return Response::success($options);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 获取当前站点信息
     */
    public function current()
    {
        try {
            $site = SiteContextService::getSite();

            if (!$site) {
                return Response::error('未识别到当前站点');
            }

            return Response::success($site->toArray());
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 切换站点
     */
    public function switch(Request $request)
    {
        $siteId = $request->post('site_id');

        if (!$siteId) {
            return Response::error('站点ID不能为空');
        }

        try {
            $result = SiteContextService::switchSite($siteId);

            if (!$result) {
                return Response::error('站点切换失败或站点不可用');
            }

            $site = SiteContextService::getSite();

            return Response::success($site->toArray(), '站点切换成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 为站点分配管理员
     */
    public function assignAdmins(Request $request, $id)
    {
        $adminUserIds = $request->post('admin_user_ids', []);

        if (!is_array($adminUserIds)) {
            return Response::error('管理员ID格式错误');
        }

        try {
            $this->siteService->assignAdmins($id, $adminUserIds);
            return Response::success(null, '管理员分配成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 获取站点的管理员列表
     */
    public function admins($id)
    {
        try {
            $admins = $this->siteService->getSiteAdmins($id);
            return Response::success($admins);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 更新站点统计数据
     */
    public function updateStats($id)
    {
        try {
            $this->siteService->updateStats($id);
            return Response::success(null, '统计数据更新成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 复制站点配置
     */
    public function copyConfig(Request $request)
    {
        $fromSiteId = $request->post('from_site_id');
        $toSiteId = $request->post('to_site_id');

        if (!$fromSiteId || !$toSiteId) {
            return Response::error('源站点和目标站点ID不能为空');
        }

        try {
            $this->siteService->copyConfig($fromSiteId, $toSiteId);
            return Response::success(null, '配置复制成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 清除站点缓存
     */
    public function clearCache(Request $request)
    {
        $siteId = $request->post('site_id', null);

        try {
            SiteContextService::clearCache($siteId);

            $message = $siteId ? '站点缓存已清除' : '所有站点缓存已清除';
            return Response::success(null, $message);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 创建站点表
     */
    public function createTables($id)
    {
        try {
            $site = $this->siteService->getDetail($id);

            if (empty($site->db_prefix)) {
                return Response::error('该站点未配置表前缀');
            }

            $result = \app\service\SiteTableService::createSiteTables($site->site_code, $site->db_prefix);

            return Response::success([
                'success' => $result['success'],
                'failed' => $result['failed'],
                'skipped' => $result['skipped'],
                'success_count' => count($result['success']),
                'failed_count' => count($result['failed']),
                'skipped_count' => count($result['skipped'])
            ], '站点表创建完成');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 检查站点表状态
     */
    public function checkTables($id)
    {
        try {
            $site = $this->siteService->getDetail($id);

            if (empty($site->db_prefix)) {
                return Response::error('该站点未配置表前缀');
            }

            $result = \app\service\SiteTableService::checkSiteTables($site->db_prefix);

            return Response::success($result);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 迁移数据到站点表
     */
    public function migrateData(Request $request, $id)
    {
        try {
            $site = $this->siteService->getDetail($id);

            if (empty($site->db_prefix)) {
                return Response::error('该站点未配置表前缀');
            }

            $tables = $request->post('tables', []); // 可选，指定要迁移的表

            $result = \app\service\SiteTableService::migrateData($site->id, $site->db_prefix, $tables);

            return Response::success([
                'success' => $result['success'],
                'failed' => $result['failed'],
                'counts' => $result['counts'],
                'total_migrated' => array_sum($result['counts'])
            ], '数据迁移完成');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 清空站点表数据
     */
    public function truncateTables($id)
    {
        try {
            $site = $this->siteService->getDetail($id);

            if (empty($site->db_prefix)) {
                return Response::error('该站点未配置表前缀');
            }

            $count = \app\service\SiteTableService::truncateSiteTables($site->db_prefix);

            return Response::success(['count' => $count], "已清空 {$count} 张表的数据");
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    // ==================== 模板配置相关 ====================

    /**
     * 获取站点的模板配置
     */
    public function getTemplateConfig($id)
    {
        try {
            $config = \app\model\SiteTemplateConfig::getActiveBySite($id);

            if (!$config) {
                return Response::success([
                    'has_config' => false,
                    'package' => null,
                    'custom_config' => null,
                ]);
            }

            return Response::success([
                'has_config' => true,
                'config_id' => $config->id,
                'package_id' => $config->package_id,
                'package' => $config->package,
                'custom_config' => $config->custom_config,
                'merged_config' => $config->getMergedConfig(),
            ]);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 设置站点的模板包
     */
    public function setTemplatePackage(Request $request, $id)
    {
        $packageId = $request->post('package_id');

        if (empty($packageId)) {
            return Response::error('请选择模板包');
        }

        try {
            // 检查模板包是否存在
            $package = \app\model\TemplatePackage::find($packageId);
            if (!$package) {
                return Response::notFound('模板包不存在');
            }

            // 检查权限
            if (!$package->canUseBySite($id)) {
                return Response::error('该站点无权使用此模板包');
            }

            // 查找或创建配置
            $config = \app\model\SiteTemplateConfig::where('site_id', $id)
                ->where('package_id', $packageId)
                ->find();

            if (!$config) {
                $config = \app\model\SiteTemplateConfig::create([
                    'site_id' => $id,
                    'package_id' => $packageId,
                    'is_active' => 0,
                ]);
            }

            // 激活该配置
            $config->activate();

            return Response::success($config, '模板包设置成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 更新站点的模板自定义配置
     */
    public function updateTemplateConfig(Request $request, $id)
    {
        $customConfig = $request->post('custom_config', []);

        try {
            $config = \app\model\SiteTemplateConfig::getActiveBySite($id);

            if (!$config) {
                return Response::error('该站点尚未选择模板包');
            }

            $config->custom_config = $customConfig;
            $config->save();

            return Response::success($config, '模板配置更新成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 获取站点的模板覆盖列表
     */
    public function getTemplateOverrides($id)
    {
        try {
            $overrides = \app\model\SiteTemplateOverride::getBySite($id);

            return Response::success($overrides);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 设置站点的模板覆盖
     */
    public function setTemplateOverride(Request $request, $id)
    {
        $templateType = $request->post('template_type');
        $templateId = $request->post('template_id');
        $priority = $request->post('priority', 0);

        if (empty($templateType)) {
            return Response::error('请指定模板类型');
        }

        if (empty($templateId)) {
            return Response::error('请选择模板');
        }

        try {
            // 检查模板是否存在（模板是全局共享的，需要禁用站点过滤）
            $template = \app\model\Template::withoutSiteScope()->find($templateId);
            if (!$template) {
                return Response::notFound('模板不存在');
            }

            // 设置覆盖
            \app\model\SiteTemplateOverride::setOverride($id, $templateType, $templateId, $priority);

            return Response::success(null, '模板覆盖设置成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 移除站点的模板覆盖
     */
    public function removeTemplateOverride(Request $request, $id)
    {
        $templateType = $request->post('template_type');

        if (empty($templateType)) {
            return Response::error('请指定模板类型');
        }

        try {
            \app\model\SiteTemplateOverride::removeOverride($id, $templateType);

            return Response::success(null, '模板覆盖已移除');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }
}
