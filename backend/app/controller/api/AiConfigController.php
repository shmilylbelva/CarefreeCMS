<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\common\Logger;
use app\model\AiConfig;
use app\model\OperationLog;
use app\service\AiService;
use think\Request;

/**
 * AI配置管理控制器
 */
class AiConfigController extends BaseController
{
    /**
     * 配置列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $provider = $request->get('provider', '');
        $status = $request->get('status', '');

        $query = AiConfig::order('is_default', 'desc')
            ->order('id', 'desc');

        if ($provider) {
            $query->where('provider', $provider);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $list = $query->page($page, $pageSize)->select();
        $total = $query->count();

        // 隐藏敏感信息
        $list = $list->map(function ($item) {
            $item->api_key = substr($item->api_key, 0, 10) . '***';
            return $item;
        });

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取所有配置（下拉选择）
     * 注意：批量文章生成只显示支持文本生成的AI配置
     */
    public function all(Request $request)
    {
        // 是否只显示支持文本生成的配置（用于批量文章生成）
        $textGenerationOnly = $request->get('text_generation_only', false);

        // 基础查询
        $query = AiConfig::alias('ac')
            ->where('ac.status', 1)
            ->order('ac.is_default', 'desc')
            ->order('ac.id', 'desc')
            ->field('ac.id,ac.name,ac.provider,ac.model,ac.is_default');

        // 如果需要筛选支持文本生成的配置
        if ($textGenerationOnly) {
            $query->join('ai_providers ap', 'ac.provider = ap.code')
                  ->join('ai_models am', 'ap.id = am.provider_id AND ac.model = am.model_code')
                  ->where('am.supports_text_generation', 1)
                  ->where('am.status', 1);
        }

        $list = $query->select();

        return Response::success($list->toArray());
    }

    /**
     * 配置详情
     */
    public function read($id)
    {
        $config = AiConfig::find($id);

        if (!$config) {
            return Response::notFound('配置不存在');
        }

        // 隐藏API密钥的部分内容
        $data = $config->toArray();
        $data['api_key'] = substr($config->api_key, 0, 10) . '***';

        return Response::success($data);
    }

    /**
     * 创建配置
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['name'])) {
            return Response::error('配置名称不能为空');
        }

        if (empty($data['provider'])) {
            return Response::error('AI提供商不能为空');
        }

        if (empty($data['api_key'])) {
            return Response::error('API密钥不能为空');
        }

        try {
            $config = AiConfig::create($data);

            // 如果设置为默认，取消其他默认配置
            if (!empty($data['is_default'])) {
                $config->setAsDefault();
            }

            Logger::create(OperationLog::MODULE_SYSTEM, 'AI配置', $config->id);
            return Response::success(['id' => $config->id], 'AI配置创建成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_CREATE, '创建AI配置失败', false, $e->getMessage());
            return Response::error('AI配置创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新配置
     */
    public function update(Request $request, $id)
    {
        $config = AiConfig::find($id);
        if (!$config) {
            return Response::notFound('配置不存在');
        }

        $data = $request->post();

        // 如果API密钥是隐藏格式，不更新
        if (isset($data['api_key']) && strpos($data['api_key'], '***') !== false) {
            unset($data['api_key']);
        }

        try {
            $affected = \think\facade\Db::name('ai_configs')
                ->where('id', '=', $id)
                ->limit(1)
                ->update($data);

            if ($affected === 0) {
                return Response::error('AI配置更新失败：未找到该配置或数据未改变');
            }

            // 如果设置为默认，取消其他默认配置
            if (!empty($data['is_default'])) {
                $config = AiConfig::find($id);
                $config->setAsDefault();
            }

            Logger::update(OperationLog::MODULE_SYSTEM, 'AI配置', $id);
            return Response::success([], 'AI配置更新成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_UPDATE, "更新AI配置失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('AI配置更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除配置
     */
    public function delete($id)
    {
        $config = AiConfig::find($id);
        if (!$config) {
            return Response::notFound('配置不存在');
        }

        // 检查是否有相关的任务
        $taskCount = \app\model\AiArticleTask::where('ai_config_id', $id)->count();
        if ($taskCount > 0) {
            return Response::error('该配置下有关联的生成任务，无法删除');
        }

        try {
            $configId = $config->id;
            $configName = $config->name;

            // 使用Db类直接删除，确保WHERE条件精确
            $affected = \think\facade\Db::name('ai_configs')
                ->where('id', '=', $configId)
                ->limit(1)
                ->delete();

            if ($affected === 0) {
                throw new \Exception('AI配置删除失败：未找到该配置');
            }

            Logger::delete(OperationLog::MODULE_SYSTEM, "AI配置[{$configName}]", $configId);
            return Response::success([], 'AI配置删除成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_DELETE, "删除AI配置失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('AI配置删除失败：' . $e->getMessage());
        }
    }

    /**
     * 测试AI连接
     */
    public function test($id)
    {
        $config = AiConfig::find($id);
        if (!$config) {
            return Response::notFound('配置不存在');
        }

        try {
            $service = AiService::createFromConfig($config);
            $result = $service->testConnection();

            return Response::success($result, 'AI连接测试成功');
        } catch (\Exception $e) {
            return Response::error('AI连接测试失败：' . $e->getMessage());
        }
    }

    /**
     * 设置为默认配置
     */
    public function setDefault($id)
    {
        $config = AiConfig::find($id);
        if (!$config) {
            return Response::notFound('配置不存在');
        }

        try {
            $config->setAsDefault();
            return Response::success([], '默认配置设置成功');
        } catch (\Exception $e) {
            return Response::error('设置失败：' . $e->getMessage());
        }
    }

    /**
     * 获取AI提供商列表
     */
    public function providers()
    {
        return Response::success(AiConfig::getProviders());
    }

    /**
     * 获取提供商支持的模型列表
     */
    public function providerModels(Request $request)
    {
        $provider = $request->get('provider');

        if (!$provider) {
            return Response::success(AiConfig::getProviderModels());
        }

        $models = AiConfig::getProviderModels();

        if (!isset($models[$provider])) {
            return Response::error('不支持的AI提供商');
        }

        return Response::success($models[$provider]);
    }

    /**
     * 获取提供商配置说明
     */
    public function providerConfigGuide(Request $request)
    {
        $provider = $request->get('provider');

        if (!$provider) {
            return Response::success(AiConfig::getProviderConfigGuide());
        }

        $guides = AiConfig::getProviderConfigGuide();

        // 如果是自定义厂商（不在预设列表中），返回通用配置说明
        if (!isset($guides[$provider])) {
            $defaultGuide = [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => '输入兼容OpenAI格式的API端点 (必填)', 'required' => true],
                'extra_fields' => [],
            ];
            return Response::success($defaultGuide);
        }

        return Response::success($guides[$provider]);
    }
}
