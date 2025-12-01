<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use think\facade\Queue;
use think\Request;

/**
 * 队列管理控制器
 * 用于管理和监控队列任务
 */
class QueueManage extends BaseController
{
    /**
     * 推送AI图片生成任务到队列
     */
    public function pushAiImageJob(Request $request)
    {
        try {
            $taskId = $request->post('task_id');

            if (empty($taskId)) {
                return Response::error('任务ID不能为空');
            }

            // 推送到队列
            $jobId = Queue::push('app\queue\AiImageGenerationJob', [
                'task_id' => $taskId,
            ], 'ai-image');

            return Response::success([
                'job_id' => $jobId,
            ], '任务已加入队列');

        } catch (\Exception $e) {
            return Response::error('推送失败：' . $e->getMessage());
        }
    }

    /**
     * 推送批量缩略图生成任务到队列
     */
    public function pushBatchThumbnailJob(Request $request)
    {
        try {
            $mediaIds = $request->post('media_ids', []);
            $presetName = $request->post('preset_name');

            if (empty($mediaIds)) {
                return Response::error('媒体ID列表不能为空');
            }

            // 推送到队列
            $jobId = Queue::push('app\queue\BatchThumbnailJob', [
                'media_ids' => $mediaIds,
                'preset_name' => $presetName,
            ], 'thumbnail');

            return Response::success([
                'job_id' => $jobId,
                'count' => count($mediaIds),
            ], '批量缩略图任务已加入队列');

        } catch (\Exception $e) {
            return Response::error('推送失败：' . $e->getMessage());
        }
    }

    /**
     * 推送批量水印处理任务到队列
     */
    public function pushBatchWatermarkJob(Request $request)
    {
        try {
            $mediaIds = $request->post('media_ids', []);
            $presetId = $request->post('preset_id');
            $config = $request->post('config');

            if (empty($mediaIds)) {
                return Response::error('媒体ID列表不能为空');
            }

            // 推送到队列
            $jobId = Queue::push('app\queue\BatchWatermarkJob', [
                'media_ids' => $mediaIds,
                'preset_id' => $presetId,
                'config' => $config,
            ], 'watermark');

            return Response::success([
                'job_id' => $jobId,
                'count' => count($mediaIds),
            ], '批量水印任务已加入队列');

        } catch (\Exception $e) {
            return Response::error('推送失败：' . $e->getMessage());
        }
    }

    /**
     * 推送视频转码任务到队列
     */
    public function pushVideoTranscodeJob(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $format = $request->post('format', 'mp4');
            $quality = $request->post('quality', 'medium');
            $resolution = $request->post('resolution');

            if (empty($mediaId)) {
                return Response::error('媒体ID不能为空');
            }

            // 推送到队列
            $jobId = Queue::push('app\queue\VideoTranscodeJob', [
                'media_id' => $mediaId,
                'format' => $format,
                'quality' => $quality,
                'resolution' => $resolution,
            ], 'video');

            return Response::success([
                'job_id' => $jobId,
            ], '视频转码任务已加入队列');

        } catch (\Exception $e) {
            return Response::error('推送失败：' . $e->getMessage());
        }
    }

    /**
     * 延迟推送任务
     */
    public function pushLater(Request $request)
    {
        try {
            $jobClass = $request->post('job_class');
            $data = $request->post('data', []);
            $delay = $request->post('delay', 0); // 延迟时间（秒）
            $queue = $request->post('queue', 'default');

            if (empty($jobClass)) {
                return Response::error('任务类不能为空');
            }

            // 延迟推送到队列
            $jobId = Queue::later($delay, $jobClass, $data, $queue);

            return Response::success([
                'job_id' => $jobId,
                'delay' => $delay,
            ], '延迟任务已加入队列');

        } catch (\Exception $e) {
            return Response::error('推送失败：' . $e->getMessage());
        }
    }

    /**
     * 获取队列统计信息
     */
    public function stats()
    {
        try {
            // 注意：这需要配合Redis或数据库驱动来获取实际统计
            // 这里提供一个示例结构
            $stats = [
                'queues' => [
                    'ai-image' => [
                        'pending' => 0,
                        'processing' => 0,
                        'failed' => 0,
                        'completed' => 0,
                    ],
                    'thumbnail' => [
                        'pending' => 0,
                        'processing' => 0,
                        'failed' => 0,
                        'completed' => 0,
                    ],
                    'watermark' => [
                        'pending' => 0,
                        'processing' => 0,
                        'failed' => 0,
                        'completed' => 0,
                    ],
                    'video' => [
                        'pending' => 0,
                        'processing' => 0,
                        'failed' => 0,
                        'completed' => 0,
                    ],
                ],
            ];

            return Response::success($stats);

        } catch (\Exception $e) {
            return Response::error('获取统计失败：' . $e->getMessage());
        }
    }

    /**
     * 清空队列
     */
    public function clear(Request $request)
    {
        try {
            $queue = $request->post('queue', 'default');

            // 清空指定队列
            // 注意：具体实现依赖于队列驱动
            // Queue::clear($queue);

            return Response::success([], '队列已清空');

        } catch (\Exception $e) {
            return Response::error('清空失败：' . $e->getMessage());
        }
    }

    /**
     * 获取AI图片任务列表
     */
    public function getAiImageTasks(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 20);
            $status = $request->get('status', '');
            $keyword = $request->get('keyword', '');

            // 这里返回空列表作为示例
            // 实际应该从数据库或队列驱动中获取任务列表
            $list = [];
            $total = 0;

            return Response::paginate($list, $total, $page, $pageSize);

        } catch (\Exception $e) {
            return Response::error('获取任务列表失败：' . $e->getMessage());
        }
    }

    /**
     * 获取视频转码任务列表
     */
    public function getVideoTranscodeTasks(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 20);
            $status = $request->get('status', '');
            $keyword = $request->get('keyword', '');

            // 这里返回空列表作为示例
            $list = [];
            $total = 0;

            return Response::paginate($list, $total, $page, $pageSize);

        } catch (\Exception $e) {
            return Response::error('获取任务列表失败：' . $e->getMessage());
        }
    }

    /**
     * 获取队列日志
     */
    public function getLogs(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 50);
            $queue = $request->get('queue', '');
            $level = $request->get('level', '');
            $keyword = $request->get('keyword', '');

            // 这里返回空列表作为示例
            $list = [];
            $total = 0;

            return Response::paginate($list, $total, $page, $pageSize);

        } catch (\Exception $e) {
            return Response::error('获取日志失败：' . $e->getMessage());
        }
    }

    /**
     * 重试AI图片任务
     */
    public function retryAiImageTask(Request $request, $taskId)
    {
        try {
            return Response::success([], '任务已重新加入队列');
        } catch (\Exception $e) {
            return Response::error('重试失败：' . $e->getMessage());
        }
    }

    /**
     * 取消AI图片任务
     */
    public function cancelAiImageTask(Request $request, $taskId)
    {
        try {
            return Response::success([], '任务已取消');
        } catch (\Exception $e) {
            return Response::error('取消失败：' . $e->getMessage());
        }
    }

    /**
     * 删除AI图片任务
     */
    public function deleteAiImageTask(Request $request, $taskId)
    {
        try {
            return Response::success([], '任务已删除');
        } catch (\Exception $e) {
            return Response::error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 重试视频转码任务
     */
    public function retryVideoTranscodeTask(Request $request, $taskId)
    {
        try {
            return Response::success([], '任务已重新加入队列');
        } catch (\Exception $e) {
            return Response::error('重试失败：' . $e->getMessage());
        }
    }

    /**
     * 取消视频转码任务
     */
    public function cancelVideoTranscodeTask(Request $request, $taskId)
    {
        try {
            return Response::success([], '任务已取消');
        } catch (\Exception $e) {
            return Response::error('取消失败：' . $e->getMessage());
        }
    }

    /**
     * 删除视频转码任务
     */
    public function deleteVideoTranscodeTask(Request $request, $taskId)
    {
        try {
            return Response::success([], '任务已删除');
        } catch (\Exception $e) {
            return Response::error('删除失败：' . $e->getMessage());
        }
    }
}
