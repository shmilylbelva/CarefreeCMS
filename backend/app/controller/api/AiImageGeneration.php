<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\AiImageTask;
use app\model\AiImagePromptTemplate;
use app\model\AiModel;
use app\service\AiImageGenerationService;
use think\App;
use think\Request;

/**
 * AI图片生成控制器
 */
class AiImageGeneration extends BaseController
{
    protected $aiImageService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->aiImageService = new AiImageGenerationService();
    }

    /**
     * 获取支持图片生成的AI模型列表
     */
    public function models()
    {
        $models = AiModel::where('supports_image_generation', 1)
            ->where('status', 1)
            ->order('sort_order', 'asc')
            ->select();

        return Response::success($models);
    }

    /**
     * 任务列表
     */
    public function tasks(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);
        $status = $request->get('status', '');
        $userId = $request->get('user_id', '');

        $query = AiImageTask::with(['aiModel', 'user', 'template'])
            ->order('created_at', 'desc');

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($userId)) {
            $query->where('user_id', $userId);
        }

        $list = $query->page($page, $pageSize)->select();
        $total = AiImageTask::when(!empty($status), function ($q) use ($status) {
            $q->where('status', $status);
        })
        ->when(!empty($userId), function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->count();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取任务详情
     */
    public function taskDetail($id)
    {
        $task = AiImageTask::with(['aiModel', 'user', 'template'])->find($id);

        if (!$task) {
            return Response::notFound('任务不存在');
        }

        // 获取生成的媒体
        $generatedMedia = $task->generatedMedia();

        return Response::success([
            'task' => $task,
            'generated_media' => $generatedMedia,
        ]);
    }

    /**
     * 创建生成任务
     */
    public function create(Request $request)
    {
        try {
            $data = [
                'ai_model_id' => $request->post('ai_model_id'),
                'prompt' => $request->post('prompt'),
                'negative_prompt' => $request->post('negative_prompt'),
                'template_id' => $request->post('template_id'),
                'template_variables' => $request->post('template_variables', []),
                'image_count' => $request->post('image_count', 1),
                'width' => $request->post('width', 1024),
                'height' => $request->post('height', 1024),
                'style' => $request->post('style'),
                'quality' => $request->post('quality', 'standard'),
                'extra_params' => $request->post('extra_params', []),
            ];

            if (empty($data['ai_model_id'])) {
                return Response::error('请选择AI模型');
            }

            if (empty($data['prompt']) && empty($data['template_id'])) {
                return Response::error('请输入提示词或选择模板');
            }

            $task = $this->aiImageService->createTask($data);

            return Response::success($task, '任务创建成功');

        } catch (\Exception $e) {
            return Response::error('创建失败：' . $e->getMessage());
        }
    }

    /**
     * 执行任务（同步）
     */
    public function execute(Request $request)
    {
        try {
            $taskId = $request->post('task_id');
            $async = $request->post('async', false); // 是否异步执行

            if (empty($taskId)) {
                return Response::error('任务ID不能为空');
            }

            // 如果异步执行，推送到队列
            if ($async) {
                $jobId = \think\facade\Queue::push('app\queue\AiImageGenerationJob', [
                    'task_id' => $taskId,
                ], 'ai-image');

                return Response::success([
                    'job_id' => $jobId,
                    'task_id' => $taskId,
                ], '任务已加入队列，将异步执行');
            }

            // 同步执行
            $generatedMedia = $this->aiImageService->executeTask($taskId);

            return Response::success([
                'count' => count($generatedMedia),
                'media' => $generatedMedia,
            ], '图片生成成功');

        } catch (\Exception $e) {
            return Response::error('生成失败：' . $e->getMessage());
        }
    }

    /**
     * 取消任务
     */
    public function cancel(Request $request)
    {
        try {
            $taskId = $request->post('task_id');

            if (empty($taskId)) {
                return Response::error('任务ID不能为空');
            }

            $this->aiImageService->cancelTask($taskId);

            return Response::success([], '任务已取消');

        } catch (\Exception $e) {
            return Response::error('取消失败：' . $e->getMessage());
        }
    }

    /**
     * 重试任务
     */
    public function retry(Request $request)
    {
        try {
            $taskId = $request->post('task_id');

            if (empty($taskId)) {
                return Response::error('任务ID不能为空');
            }

            $task = $this->aiImageService->retryTask($taskId);

            return Response::success($task, '任务已重置，可以重新执行');

        } catch (\Exception $e) {
            return Response::error('重试失败：' . $e->getMessage());
        }
    }

    /**
     * 任务统计
     */
    public function stats(Request $request)
    {
        try {
            $userId = $request->get('user_id');

            $stats = $this->aiImageService->getTaskStats($userId);

            return Response::success($stats);

        } catch (\Exception $e) {
            return Response::error('获取统计失败：' . $e->getMessage());
        }
    }

    /**
     * 提示词模板列表
     */
    public function templates(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);
        $category = $request->get('category', '');

        $query = AiImagePromptTemplate::where('is_public', 1)
            ->order('sort_order', 'asc')
            ->order('usage_count', 'desc');

        if (!empty($category)) {
            $query->where('category', $category);
        }

        $list = $query->page($page, $pageSize)->select();
        $total = AiImagePromptTemplate::where('is_public', 1)
            ->when(!empty($category), function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->count();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取热门模板
     */
    public function popularTemplates()
    {
        $templates = AiImagePromptTemplate::getPopular(20);

        return Response::success($templates);
    }

    /**
     * 创建提示词模板
     */
    public function createTemplate(Request $request)
    {
        try {
            $data = [
                'name' => $request->post('name'),
                'category' => $request->post('category'),
                'prompt_template' => $request->post('prompt_template'),
                'negative_prompt' => $request->post('negative_prompt'),
                'variables' => $request->post('variables', []),
                'default_width' => $request->post('default_width'),
                'default_height' => $request->post('default_height'),
                'default_style' => $request->post('default_style'),
                'thumbnail' => $request->post('thumbnail'),
                'description' => $request->post('description'),
                'is_public' => $request->post('is_public', 1),
                'is_builtin' => 0,
                'sort_order' => $request->post('sort_order', 0),
            ];

            if (empty($data['name']) || empty($data['prompt_template'])) {
                return Response::error('模板名称和提示词不能为空');
            }

            if (!empty($data['variables'])) {
                $data['variables'] = json_encode($data['variables']);
            }

            $template = AiImagePromptTemplate::create($data);

            return Response::success($template, '模板创建成功');

        } catch (\Exception $e) {
            return Response::error('创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新提示词模板
     */
    public function updateTemplate(Request $request, $id)
    {
        try {
            $template = AiImagePromptTemplate::find($id);

            if (!$template) {
                return Response::notFound('模板不存在');
            }

            if ($template->is_builtin) {
                return Response::error('内置模板不允许修改');
            }

            $data = [];

            if ($request->has('name')) {
                $data['name'] = $request->post('name');
            }

            if ($request->has('category')) {
                $data['category'] = $request->post('category');
            }

            if ($request->has('prompt_template')) {
                $data['prompt_template'] = $request->post('prompt_template');
            }

            if ($request->has('negative_prompt')) {
                $data['negative_prompt'] = $request->post('negative_prompt');
            }

            if ($request->has('variables')) {
                $data['variables'] = json_encode($request->post('variables', []));
            }

            if ($request->has('default_width')) {
                $data['default_width'] = $request->post('default_width');
            }

            if ($request->has('default_height')) {
                $data['default_height'] = $request->post('default_height');
            }

            if ($request->has('default_style')) {
                $data['default_style'] = $request->post('default_style');
            }

            if ($request->has('thumbnail')) {
                $data['thumbnail'] = $request->post('thumbnail');
            }

            if ($request->has('description')) {
                $data['description'] = $request->post('description');
            }

            if ($request->has('is_public')) {
                $data['is_public'] = $request->post('is_public');
            }

            if ($request->has('sort_order')) {
                $data['sort_order'] = $request->post('sort_order');
            }

            $template->save($data);

            return Response::success($template, '模板更新成功');

        } catch (\Exception $e) {
            return Response::error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除提示词模板
     */
    public function deleteTemplate($id)
    {
        try {
            $template = AiImagePromptTemplate::find($id);

            if (!$template) {
                return Response::notFound('模板不存在');
            }

            if ($template->is_builtin) {
                return Response::error('内置模板不允许删除');
            }

            $templateId = $template->id;

            // 使用Db类直接删除，确保WHERE条件精确
            $affected = \think\facade\Db::name('ai_image_prompt_templates')
                ->where('id', '=', $templateId)
                ->limit(1)
                ->delete();

            if ($affected === 0) {
                throw new \Exception('模板删除失败：未找到该模板');
            }

            return Response::success([], '模板删除成功');

        } catch (\Exception $e) {
            return Response::error('删除失败：' . $e->getMessage());
        }
    }
}
