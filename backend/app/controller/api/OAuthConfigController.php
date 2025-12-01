<?php
declare (strict_types = 1);

namespace app\controller\api;

use app\BaseController;
use app\model\OAuthConfig;
use think\Request;
use think\Response;

/**
 * OAuth配置管理控制器（后台管理）
 */
class OAuthConfigController extends BaseController
{
    /**
     * 获取OAuth配置列表
     */
    public function index(Request $request): Response
    {
        $page = $request->param('page', 1);
        $limit = $request->param('limit', 15);

        $query = OAuthConfig::order('sort_order', 'asc');

        // 搜索条件
        $query->withSearch(['platform', 'platform_name', 'is_enabled'], $request->param());

        $list = $query->paginate([
            'list_rows' => $limit,
            'page' => $page,
        ]);

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'list' => $list->items(),
                'total' => $list->total(),
                'page' => $list->currentPage(),
                'limit' => $limit,
            ]
        ]);
    }

    /**
     * 获取OAuth配置详情
     */
    public function read($id): Response
    {
        $config = OAuthConfig::find($id);
        if (!$config) {
            return json(['code' => 404, 'message' => 'OAuth配置不存在']);
        }

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => $config
        ]);
    }

    /**
     * 更新OAuth配置
     */
    public function update(Request $request, $id): Response
    {
        $config = OAuthConfig::find($id);
        if (!$config) {
            return json(['code' => 404, 'message' => 'OAuth配置不存在']);
        }

        $data = $request->only([
            'platform_name', 'app_id', 'app_secret', 'redirect_uri',
            'scope', 'is_enabled', 'sort_order', 'extra_config', 'remark'
        ]);

        // 如果app_secret包含*号，说明是隐藏的，不更新
        if (isset($data['app_secret']) && strpos($data['app_secret'], '*') !== false) {
            unset($data['app_secret']);
        }

        $config->save($data);

        return json([
            'code' => 0,
            'message' => 'OAuth配置更新成功',
            'data' => $config
        ]);
    }

    /**
     * 批量更新状态
     */
    public function batchUpdateStatus(Request $request): Response
    {
        $ids = $request->param('ids', []);
        $is_enabled = $request->param('is_enabled', 0);

        if (empty($ids)) {
            return json(['code' => 400, 'message' => '请选择要操作的配置']);
        }

        OAuthConfig::whereIn('id', $ids)->update(['is_enabled' => $is_enabled]);

        return json([
            'code' => 0,
            'message' => '批量更新成功'
        ]);
    }

    /**
     * 获取平台选项（用于下拉选择）
     */
    public function getPlatformOptions(): Response
    {
        $options = OAuthConfig::getPlatformOptions();

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => $options
        ]);
    }

    /**
     * 获取启用的平台列表（前台使用）
     */
    public function getEnabledPlatforms(): Response
    {
        $platforms = OAuthConfig::getEnabledPlatforms();

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => $platforms
        ]);
    }

    /**
     * 测试OAuth配置
     */
    public function testConfig($id): Response
    {
        $config = OAuthConfig::find($id);
        if (!$config) {
            return json(['code' => 404, 'message' => 'OAuth配置不存在']);
        }

        // 检查配置完整性
        if (!$config->isConfigComplete()) {
            return json([
                'code' => 400,
                'message' => '配置不完整',
                'data' => [
                    'missing_fields' => array_filter([
                        'app_id' => empty($config->app_id),
                        'app_secret' => empty($config->app_secret),
                        'redirect_uri' => empty($config->redirect_uri),
                    ])
                ]
            ]);
        }

        return json([
            'code' => 0,
            'message' => '配置检测通过',
            'data' => [
                'is_complete' => true
            ]
        ]);
    }
}
