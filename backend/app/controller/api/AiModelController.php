<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\common\Logger;
use app\model\AiModel;
use app\model\AiProvider;
use app\model\OperationLog;
use think\Request;

/**
 * AI模型管理控制器
 */
class AiModelController extends BaseController
{
    /**
     * 模型列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $providerId = $request->get('provider_id', '');
        $status = $request->get('status', '');
        $keyword = $request->get('keyword', '');

        $query = AiModel::with('provider')
            ->order('provider_id', 'asc')
            ->order('sort_order', 'asc')
            ->order('id', 'asc');

        if ($providerId !== '') {
            $query->where('provider_id', $providerId);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('model_code', "%{$keyword}%")
                  ->whereOr('model_name', 'like', "%{$keyword}%");
            });
        }

        // 先统计总数（必须在分页之前）
        $total = $query->count();

        // 再获取分页数据
        $list = $query->page($page, $pageSize)->select();

        // 补充厂商信息
        $list = $list->toArray();
        foreach ($list as &$item) {
            if (isset($item['provider'])) {
                $item['provider_name'] = $item['provider']['name'];
                $item['provider_code'] = $item['provider']['code'];
            }
        }

        return Response::paginate($list, $total, $page, $pageSize);
    }

    /**
     * 获取所有模型（按厂商分组）
     */
    public function all(Request $request)
    {
        $grouped = AiModel::getAllModelsGrouped();
        return Response::success($grouped);
    }

    /**
     * 模型详情
     */
    public function read($id)
    {
        $model = AiModel::with('provider')->find($id);

        if (!$model) {
            return Response::notFound('模型不存在');
        }

        $data = $model->toArray();
        if (isset($data['provider'])) {
            $data['provider_name'] = $data['provider']['name'];
            $data['provider_code'] = $data['provider']['code'];
        }

        return Response::success($data);
    }

    /**
     * 创建模型
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['provider_id'])) {
            return Response::error('所属厂商不能为空');
        }

        if (empty($data['model_code'])) {
            return Response::error('模型代码不能为空');
        }

        if (empty($data['model_name'])) {
            return Response::error('模型名称不能为空');
        }

        // 检查厂商是否存在
        $provider = AiProvider::find($data['provider_id']);
        if (!$provider) {
            return Response::error('所属厂商不存在');
        }

        try {
            $model = AiModel::create($data);

            Logger::create(OperationLog::MODULE_SYSTEM, 'AI模型', $model->id);
            return Response::success(['id' => $model->id], 'AI模型创建成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_CREATE, '创建AI模型失败', false, $e->getMessage());
            return Response::error('AI模型创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新模型
     */
    public function update(Request $request, $id)
    {
        $model = AiModel::find($id);
        if (!$model) {
            return Response::notFound('模型不存在');
        }

        $data = $request->post();

        // 如果修改了厂商，检查厂商是否存在
        if (isset($data['provider_id']) && $data['provider_id'] != $model->provider_id) {
            $provider = AiProvider::find($data['provider_id']);
            if (!$provider) {
                return Response::error('所属厂商不存在');
            }
        }

        // 内置模型不允许修改核心字段（provider_id, model_code, model_name等）
        // 但允许修改能力字段、描述、状态等
        if ($model->is_builtin) {
            $allowedFields = [
                'status', 'sort_order', 'description', 'context_window', 'max_output_tokens',
                // 基础能力
                'supports_text_generation', 'supports_functions', 'supports_streaming', 'supports_embeddings',
                // 多模态能力 - 图像
                'supports_image_input', 'supports_image_generation',
                // 多模态能力 - 音频
                'supports_audio_input', 'supports_audio_output', 'supports_audio_generation', 'supports_realtime_voice',
                // 多模态能力 - 视频
                'supports_video_input', 'supports_video_generation',
                // 专项能力
                'supports_code_generation', 'supports_code_interpreter', 'supports_document_parsing', 'supports_web_search',
                // 其他
                'pricing_info', 'extra_config', 'multimodal_capabilities'
            ];
            $data = array_intersect_key($data, array_flip($allowedFields));
        }

        try {
            $model->save($data);

            Logger::update(OperationLog::MODULE_SYSTEM, 'AI模型', $id);
            return Response::success([], 'AI模型更新成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_UPDATE, "更新AI模型失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('AI模型更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除模型
     */
    public function delete($id)
    {
        $model = AiModel::find($id);
        if (!$model) {
            return Response::notFound('模型不存在');
        }

        // 内置模型不允许删除
        if ($model->is_builtin) {
            return Response::error('内置模型不允许删除');
        }

        // 检查是否有关联的配置在使用该模型
        $configCount = \app\model\AiConfig::where('model', $model->model_code)->count();
        if ($configCount > 0) {
            return Response::error('该模型正在被AI配置使用，无法删除');
        }

        try {
            $modelName = $model->model_name;
            $model->delete();
            Logger::delete(OperationLog::MODULE_SYSTEM, "AI模型[{$modelName}]", $id);
            return Response::success([], 'AI模型删除成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_DELETE, "删除AI模型失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('AI模型删除失败：' . $e->getMessage());
        }
    }

    /**
     * 批量导入模型
     */
    public function batchImport(Request $request)
    {
        $providerId = $request->post('provider_id');
        $models = $request->post('models', []);

        if (empty($providerId)) {
            return Response::error('所属厂商不能为空');
        }

        if (empty($models) || !is_array($models)) {
            return Response::error('模型数据不能为空');
        }

        // 检查厂商是否存在
        $provider = AiProvider::find($providerId);
        if (!$provider) {
            return Response::error('所属厂商不存在');
        }

        try {
            $successCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($models as $modelData) {
                if (empty($modelData['model_code']) || empty($modelData['model_name'])) {
                    $failedCount++;
                    $errors[] = '模型代码和名称不能为空';
                    continue;
                }

                $modelData['provider_id'] = $providerId;

                try {
                    AiModel::create($modelData);
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "导入 {$modelData['model_code']} 失败：" . $e->getMessage();
                }
            }

            $message = "成功导入 {$successCount} 个模型";
            if ($failedCount > 0) {
                $message .= "，失败 {$failedCount} 个";
            }

            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_CREATE, $message, true);

            return Response::success([
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors
            ], $message);
        } catch (\Exception $e) {
            return Response::error('批量导入失败：' . $e->getMessage());
        }
    }
}
