<?php
declare (strict_types = 1);

namespace app\queue;

use app\service\MediaThumbnailService;
use think\queue\Job;

/**
 * 批量缩略图生成队列任务
 * 异步批量生成缩略图，避免阻塞请求
 */
class BatchThumbnailJob
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
            $mediaIds = $data['media_ids'] ?? [];
            $presetName = $data['preset_name'] ?? null;

            if (empty($mediaIds)) {
                throw new \Exception('媒体ID列表不能为空');
            }

            $service = new MediaThumbnailService();
            $results = [
                'success' => [],
                'failed' => [],
            ];

            foreach ($mediaIds as $mediaId) {
                try {
                    if ($presetName) {
                        // 使用指定预设生成
                        $service->generateThumbnail($mediaId, $presetName);
                    } else {
                        // 生成所有自动预设
                        $service->generateAutoThumbnails($mediaId);
                    }
                    $results['success'][] = $mediaId;
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'media_id' => $mediaId,
                        'error' => $e->getMessage(),
                    ];
                    trace('生成缩略图失败 [Media ID: ' . $mediaId . ']: ' . $e->getMessage(), 'error');
                }
            }

            // 任务完成，删除队列任务
            $job->delete();

            // 记录日志
            trace('批量缩略图生成任务完成: ' . count($results['success']) . ' 成功, ' . count($results['failed']) . ' 失败', 'info');

        } catch (\Exception $e) {
            trace('批量缩略图生成任务失败: ' . $e->getMessage(), 'error');

            if ($job->attempts() < 3) {
                $job->release(60);
            } else {
                $job->delete();
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
        trace('批量缩略图生成任务最终失败: ' . json_encode($data), 'error');
    }
}
