<?php
declare (strict_types = 1);

namespace app\service;

use app\model\AiImageTask;
use app\model\AiImagePromptTemplate;
use app\model\AiModel;
use app\model\MediaLibrary;

/**
 * AI图片生成服务
 * 集成现有AI系统生成图片
 */
class AiImageGenerationService
{
    protected $mediaService;
    protected $fileService;

    public function __construct()
    {
        $this->mediaService = new MediaLibraryService();
        $this->fileService = new MediaFileService();
    }

    /**
     * 创建AI图片生成任务
     */
    public function createTask(array $data): AiImageTask
    {
        // 验证AI模型
        $aiModel = AiModel::find($data['ai_model_id']);

        if (!$aiModel) {
            throw new \Exception('AI模型不存在');
        }

        if (!$aiModel->supports_image_generation) {
            throw new \Exception('该AI模型不支持图片生成');
        }

        // 如果使用模板，渲染提示词
        $prompt = $data['prompt'] ?? '';

        if (!empty($data['template_id'])) {
            $template = AiImagePromptTemplate::find($data['template_id']);

            if (!$template) {
                throw new \Exception('提示词模板不存在');
            }

            $variables = $data['template_variables'] ?? [];
            $prompt = $template->renderPrompt($variables);

            // 增加模板使用次数
            $template->incrementUsageCount();
        }

        // 创建任务
        $task = AiImageTask::create([
            'user_id' => $data['user_id'] ?? request()->user['id'] ?? 1,
            'ai_model_id' => $data['ai_model_id'],
            'prompt' => $prompt,
            'negative_prompt' => $data['negative_prompt'] ?? null,
            'template_id' => $data['template_id'] ?? null,
            'template_variables' => isset($data['template_variables']) ? json_encode($data['template_variables']) : null,
            'image_count' => $data['image_count'] ?? 1,
            'width' => $data['width'] ?? 1024,
            'height' => $data['height'] ?? 1024,
            'style' => $data['style'] ?? null,
            'quality' => $data['quality'] ?? 'standard',
            'extra_params' => isset($data['extra_params']) ? json_encode($data['extra_params']) : null,
            'status' => AiImageTask::STATUS_PENDING,
        ]);

        return $task;
    }

    /**
     * 执行AI图片生成任务
     * 注意：这是一个示例方法，实际需要根据具体的AI服务商实现
     */
    public function executeTask(int $taskId): array
    {
        $task = AiImageTask::with(['aiModel'])->find($taskId);

        if (!$task) {
            throw new \Exception('任务不存在');
        }

        if ($task->status !== AiImageTask::STATUS_PENDING) {
            throw new \Exception('任务状态不正确');
        }

        try {
            // 标记为处理中
            $task->markAsProcessing();

            // 根据AI提供商选择相应的服务
            $aiService = $this->getAiService($task->aiModel);

            $generatedImages = [];
            $imageCount = $task->image_count;

            for ($i = 0; $i < $imageCount; $i++) {
                // 更新进度
                $progress = (int)(($i / $imageCount) * 100);
                $task->updateProgress($progress, $i);

                // 调用AI服务生成图片
                $imageUrl = $this->generateImageWithAI($task, $aiService);

                // 下载图片并保存到媒体库
                $mediaFile = $this->fileService->downloadFromUrl($imageUrl);

                // 创建媒体库记录
                $media = MediaLibrary::create([
                    'file_id' => $mediaFile->id,
                    'user_id' => $task->user_id,
                    'site_id' => $task->site_id,
                    'title' => "AI生成图片 - {$task->prompt}",
                    'description' => "通过{$task->aiModel->model_name}生成",
                    'source' => MediaLibrary::SOURCE_AI_GENERATE,
                    'source_id' => $task->id,
                    'status' => MediaLibrary::STATUS_ACTIVE,
                ]);

                // 生成缩略图
                $thumbnailService = new MediaThumbnailService();
                $thumbnailService->generateAutoThumbnails($media->id);

                $generatedImages[] = $media;
            }

            // 标记为完成
            $task->markAsCompleted();

            return $generatedImages;

        } catch (\Exception $e) {
            // 标记为失败
            $task->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * 根据AI模型获取对应的服务实例
     */
    protected function getAiService(AiModel $model)
    {
        // 根据模型提供商code获取服务
        $providerCode = $model->provider->code ?? '';

        switch ($providerCode) {
            case 'openai':
                return new \app\service\OpenAiService();
            case 'claude':
                return new \app\service\ClaudeService();
            // 其他AI服务商...
            default:
                throw new \Exception('不支持的AI提供商');
        }
    }

    /**
     * 使用AI服务生成图片
     */
    protected function generateImageWithAI(AiImageTask $task, $aiService): string
    {
        // 构建尺寸字符串
        $size = "{$task->width}x{$task->height}";

        // 调用AI服务生成图片
        $response = $aiService->generateImage([
            'prompt' => $task->prompt,
            'n' => 1,
            'size' => $size,
            'quality' => $task->quality,
            'style' => $task->style,
        ]);

        if (!$response['success'] || empty($response['images'])) {
            throw new \Exception('AI图片生成失败');
        }

        return $response['images'][0]['url'];
    }

    /**
     * 批量创建任务
     */
    public function createBatchTasks(array $prompts, array $commonData): array
    {
        $tasks = [];

        foreach ($prompts as $prompt) {
            $taskData = array_merge($commonData, ['prompt' => $prompt]);
            $tasks[] = $this->createTask($taskData);
        }

        return $tasks;
    }

    /**
     * 取消任务
     */
    public function cancelTask(int $taskId): bool
    {
        $task = AiImageTask::find($taskId);

        if (!$task) {
            throw new \Exception('任务不存在');
        }

        if ($task->status !== AiImageTask::STATUS_PENDING) {
            throw new \Exception('只能取消待处理的任务');
        }

        $task->markAsFailed('用户取消');

        return true;
    }

    /**
     * 重试失败的任务
     */
    public function retryTask(int $taskId): AiImageTask
    {
        $task = AiImageTask::find($taskId);

        if (!$task) {
            throw new \Exception('任务不存在');
        }

        if ($task->status !== AiImageTask::STATUS_FAILED) {
            throw new \Exception('只能重试失败的任务');
        }

        // 重置任务状态
        $task->status = AiImageTask::STATUS_PENDING;
        $task->progress = 0;
        $task->generated_count = 0;
        $task->error_message = null;
        $task->started_at = null;
        $task->completed_at = null;
        $task->save();

        return $task;
    }

    /**
     * 获取任务统计
     */
    public function getTaskStats(int $userId = null): array
    {
        $query = AiImageTask::where('id', '>', 0);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $total = $query->count();
        $pending = (clone $query)->where('status', AiImageTask::STATUS_PENDING)->count();
        $processing = (clone $query)->where('status', AiImageTask::STATUS_PROCESSING)->count();
        $completed = (clone $query)->where('status', AiImageTask::STATUS_COMPLETED)->count();
        $failed = (clone $query)->where('status', AiImageTask::STATUS_FAILED)->count();

        $totalCost = $query->sum('cost_amount');
        $totalImages = $query->sum('generated_count');

        return [
            'total' => $total,
            'pending' => $pending,
            'processing' => $processing,
            'completed' => $completed,
            'failed' => $failed,
            'total_cost' => $totalCost,
            'total_images' => $totalImages,
        ];
    }
}
