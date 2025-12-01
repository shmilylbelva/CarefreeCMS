<?php
declare (strict_types = 1);

namespace app\queue;

use app\service\VideoProcessingService;
use think\queue\Job;

/**
 * 视频转码队列任务
 * 异步执行视频转码，避免阻塞请求
 */
class VideoTranscodeJob
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
            $mediaId = $data['media_id'] ?? 0;
            $format = $data['format'] ?? 'mp4';
            $quality = $data['quality'] ?? 'medium';
            $resolution = $data['resolution'] ?? null;

            if (empty($mediaId)) {
                throw new \Exception('媒体ID不能为空');
            }

            // 执行视频转码
            $service = new VideoProcessingService();
            $result = $service->transcodeVideo($mediaId, [
                'format' => $format,
                'quality' => $quality,
                'resolution' => $resolution,
            ]);

            // 任务完成，删除队列任务
            $job->delete();

            // 记录日志
            trace('视频转码任务完成: Media ID ' . $mediaId, 'info');

        } catch (\Exception $e) {
            trace('视频转码任务失败: ' . $e->getMessage(), 'error');

            // 判断是否需要重试
            if ($job->attempts() < 2) {
                // 视频转码通常耗时较长，只重试2次
                $job->release(120); // 延迟120秒重试
            } else {
                // 超过重试次数，删除任务
                $job->delete();

                // 更新媒体状态为失败
                try {
                    $mediaId = $data['media_id'] ?? 0;
                    if ($mediaId) {
                        $media = \app\model\MediaLibrary::find($mediaId);
                        if ($media) {
                            $media->status = 'failed';
                            $media->save();
                        }
                    }
                } catch (\Exception $ex) {
                    trace('更新媒体失败状态错误: ' . $ex->getMessage(), 'error');
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
        trace('视频转码任务最终失败: ' . json_encode($data), 'error');
    }
}
