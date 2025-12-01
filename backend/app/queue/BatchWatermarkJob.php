<?php
declare (strict_types = 1);

namespace app\queue;

use app\service\MediaWatermarkService;
use think\queue\Job;

/**
 * 批量水印处理队列任务
 * 异步批量添加水印，避免阻塞请求
 */
class BatchWatermarkJob
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
            $presetId = $data['preset_id'] ?? null;
            $config = $data['config'] ?? null;

            if (empty($mediaIds)) {
                throw new \Exception('媒体ID列表不能为空');
            }

            if (empty($presetId) && empty($config)) {
                throw new \Exception('必须指定预设ID或配置');
            }

            $service = new MediaWatermarkService();
            $results = [
                'success' => [],
                'failed' => [],
            ];

            foreach ($mediaIds as $mediaId) {
                try {
                    if ($presetId) {
                        $service->addWatermarkByPreset($mediaId, $presetId);
                    } else {
                        $service->addWatermark($mediaId, $config);
                    }
                    $results['success'][] = $mediaId;
                } catch (\Exception $e) {
                    $results['failed'][] = [
                        'media_id' => $mediaId,
                        'error' => $e->getMessage(),
                    ];
                    trace('添加水印失败 [Media ID: ' . $mediaId . ']: ' . $e->getMessage(), 'error');
                }
            }

            // 任务完成，删除队列任务
            $job->delete();

            // 记录日志
            trace('批量水印处理任务完成: ' . count($results['success']) . ' 成功, ' . count($results['failed']) . ' 失败', 'info');

        } catch (\Exception $e) {
            trace('批量水印处理任务失败: ' . $e->getMessage(), 'error');

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
        trace('批量水印处理任务最终失败: ' . json_encode($data), 'error');
    }
}
