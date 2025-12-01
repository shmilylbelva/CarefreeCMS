<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\common\Logger;
use app\model\AiProvider;
use app\model\AiModel;
use app\model\OperationLog;
use think\Request;

/**
 * AI厂商管理控制器
 */
class AiProviderController extends BaseController
{
    /**
     * 厂商列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $status = $request->get('status', '');
        $isCustom = $request->get('is_custom', '');

        $query = AiProvider::order('sort_order', 'asc')
            ->order('id', 'asc');

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($isCustom !== '') {
            $query->where('is_custom', $isCustom);
        }

        // 先统计总数（必须在分页之前）
        $total = $query->count();

        // 再获取分页数据
        $list = $query->page($page, $pageSize)->select();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取所有启用的厂商（下拉选择）
     */
    public function all(Request $request)
    {
        $list = AiProvider::where('status', 1)
            ->order('sort_order', 'asc')
            ->order('id', 'asc')
            ->field('id,code,name,name_en')
            ->select();

        return Response::success($list->toArray());
    }

    /**
     * 厂商详情
     */
    public function read($id)
    {
        $provider = AiProvider::find($id);

        if (!$provider) {
            return Response::notFound('厂商不存在');
        }

        return Response::success($provider->toArray());
    }

    /**
     * 创建厂商
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['code'])) {
            return Response::error('厂商代码不能为空');
        }

        if (empty($data['name'])) {
            return Response::error('厂商名称不能为空');
        }

        // 检查代码是否已存在
        if (AiProvider::codeExists($data['code'])) {
            return Response::error('厂商代码已存在');
        }

        try {
            $provider = AiProvider::create($data);

            Logger::create(OperationLog::MODULE_SYSTEM, 'AI厂商', $provider->id);
            return Response::success(['id' => $provider->id], 'AI厂商创建成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_CREATE, '创建AI厂商失败', false, $e->getMessage());
            return Response::error('AI厂商创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新厂商
     */
    public function update(Request $request, $id)
    {
        $provider = AiProvider::find($id);
        if (!$provider) {
            return Response::notFound('厂商不存在');
        }

        // 内置厂商不允许修改核心字段
        if ($provider->is_builtin) {
            $allowedFields = ['status', 'sort_order', 'description', 'logo_url', 'website', 'api_doc_url'];
            $data = $request->only($allowedFields);
        } else {
            $data = $request->post();

            // 如果修改了代码，检查是否重复
            if (isset($data['code']) && $data['code'] !== $provider->code) {
                if (AiProvider::codeExists($data['code'], $id)) {
                    return Response::error('厂商代码已存在');
                }
            }
        }

        try {
            $provider->save($data);

            Logger::update(OperationLog::MODULE_SYSTEM, 'AI厂商', $id);
            return Response::success([], 'AI厂商更新成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_UPDATE, "更新AI厂商失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('AI厂商更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除厂商
     */
    public function delete($id)
    {
        $provider = AiProvider::find($id);
        if (!$provider) {
            return Response::notFound('厂商不存在');
        }

        // 内置厂商不允许删除
        if ($provider->is_builtin) {
            return Response::error('内置厂商不允许删除');
        }

        // 检查是否有关联的模型
        $modelCount = AiModel::where('provider_id', $id)->count();
        if ($modelCount > 0) {
            return Response::error('该厂商下有关联的模型，无法删除');
        }

        // 检查是否有关联的配置
        $configCount = \app\model\AiConfig::where('provider', $provider->code)->count();
        if ($configCount > 0) {
            return Response::error('该厂商下有关联的AI配置，无法删除');
        }

        try {
            $providerName = $provider->name;
            $provider->delete();
            Logger::delete(OperationLog::MODULE_SYSTEM, "AI厂商[{$providerName}]", $id);
            return Response::success([], 'AI厂商删除成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_DELETE, "删除AI厂商失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('AI厂商删除失败：' . $e->getMessage());
        }
    }

    /**
     * 获取厂商的模型列表
     */
    public function models($id)
    {
        $provider = AiProvider::find($id);
        if (!$provider) {
            return Response::notFound('厂商不存在');
        }

        $models = AiModel::getModelsByProvider($id);

        return Response::success($models->toArray());
    }
}
