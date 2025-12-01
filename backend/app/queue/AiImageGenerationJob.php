<?php
declare (strict_types = 1);

namespace app\queue;

use app\service\AiImageGenerationService;
use think\queue\Job;

/**
 * AI图片生成队列任务
 * 异步执行AI图片生成，避免阻塞请求
 */
class AiImageGenerationJob
{
    /**
     * 执行任务
     *
     * @param Job $job 任务对象
     * @param array $data 任务数据
     * @return void
     */
    public function fire(Job $job, array $data): void
    {
        try {
            // 获取任务ID
            $taskId = $data['task_id'] ?? 0;

            if (empty($taskId)) {
                throw new \Exception('任务ID不能为空');
            }

            // 执行AI图片生成
            $service = new AiImageGenerationService();
            $service->executeTask($taskId);

            // 任务完成，删除队列任务
            $job->delete();

            // 记录日志
            trace('AI图片生成任务完成: ' . $taskId, 'info');

        } catch (\Exception $e) {
            // 记录错误
            trace('AI图片生成任务失败: ' . $e->getMessage(), 'error');

            // 判断是否需要重试
            if ($job->attempts() < 3) {
                // 重试次数小于3次，重新发布任务（延迟60秒）
                $job->release(60);
            } else {
                // 超过重试次数，删除任务并记录失败
                $job->delete();

                // 更新任务状态为失败
                try {
                    $taskId = $data['task_id'] ?? 0;
                    if ($taskId) {
                        $task = \app\model\AiImageTask::find($taskId);
                        if ($task) {
                            $task->markAsFailed('队列任务执行失败: ' . $e->getMessage());
                        }
                    }
                } catch (\Exception $ex) {
                    trace('更新任务失败状态错误: ' . $ex->getMessage(), 'error');
                }
            }
        }
    }

    /**
     * 任务失败处理
     *
     * @param array $data 任务数据
     * @return void
     */
    public function failed(array $data): void
    {
        trace('AI图片生成任务最终失败: ' . json_encode($data), 'error');
    }
}
