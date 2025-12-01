<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\VideoTranscodeRecord;
use app\service\VideoProcessingService;
use think\App;
use think\Request;

/**
 * 视频处理控制器
 */
class VideoProcessing extends BaseController
{
    protected $videoService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->videoService = new VideoProcessingService();
    }

    /**
     * 获取视频信息
     */
    public function info(Request $request)
    {
        try {
            $mediaId = $request->get('media_id');

            if (empty($mediaId)) {
                return Response::error('媒体ID不能为空');
            }

            $media = \app\model\MediaLibrary::with('file')->find($mediaId);

            if (!$media || !$media->file) {
                return Response::notFound('媒体不存在');
            }

            // 获取本地文件路径
            $filePath = $this->videoService->getLocalFilePath($media->file);

            // 获取视频信息
            $info = $this->videoService->getVideoInfo($filePath);

            return Response::success($info);

        } catch (\Exception $e) {
            return Response::error('获取失败：' . $e->getMessage());
        }
    }

    /**
     * 视频转码
     */
    public function transcode(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $format = $request->post('format', 'mp4');
            $quality = $request->post('quality', 'medium');
            $resolution = $request->post('resolution');
            $async = $request->post('async', true); // 默认异步

            if (empty($mediaId)) {
                return Response::error('媒体ID不能为空');
            }

            // 如果异步执行，推送到队列
            if ($async) {
                $jobId = \think\facade\Queue::push('app\queue\VideoTranscodeJob', [
                    'media_id' => $mediaId,
                    'format' => $format,
                    'quality' => $quality,
                    'resolution' => $resolution,
                ], 'video');

                return Response::success([
                    'job_id' => $jobId,
                ], '转码任务已加入队列');
            }

            // 同步执行
            $result = $this->videoService->transcodeVideo($mediaId, [
                'format' => $format,
                'quality' => $quality,
                'resolution' => $resolution,
            ]);

            return Response::success($result, '转码成功');

        } catch (\Exception $e) {
            return Response::error('转码失败：' . $e->getMessage());
        }
    }

    /**
     * 生成视频封面
     */
    public function generatePoster(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $timeInSeconds = $request->post('time', 1);

            if (empty($mediaId)) {
                return Response::error('媒体ID不能为空');
            }

            $posterFile = $this->videoService->generatePoster($mediaId, (int)$timeInSeconds);

            return Response::success($posterFile, '封面生成成功');

        } catch (\Exception $e) {
            return Response::error('生成失败：' . $e->getMessage());
        }
    }

    /**
     * 生成多帧预览图
     */
    public function generateThumbnails(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $frameCount = $request->post('frame_count', 9);

            if (empty($mediaId)) {
                return Response::error('媒体ID不能为空');
            }

            $thumbnails = $this->videoService->generateThumbnails($mediaId, (int)$frameCount);

            return Response::success([
                'count' => count($thumbnails),
                'thumbnails' => $thumbnails,
            ], '预览图生成成功');

        } catch (\Exception $e) {
            return Response::error('生成失败：' . $e->getMessage());
        }
    }

    /**
     * 转码记录列表
     */
    public function transcodeRecords(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);
        $mediaId = $request->get('media_id', '');
        $status = $request->get('status', '');

        $query = VideoTranscodeRecord::with(['media', 'originalFile', 'resultFile'])
            ->order('created_at', 'desc');

        if (!empty($mediaId)) {
            $query->where('media_id', $mediaId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $list = $query->page($page, $pageSize)->select();
        $total = VideoTranscodeRecord::when(!empty($mediaId), function ($q) use ($mediaId) {
            $q->where('media_id', $mediaId);
        })
        ->when(!empty($status), function ($q) use ($status) {
            $q->where('status', $status);
        })
        ->count();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 转码记录详情
     */
    public function transcodeRecordDetail($id)
    {
        $record = VideoTranscodeRecord::with(['media', 'originalFile', 'resultFile'])->find($id);

        if (!$record) {
            return Response::notFound('记录不存在');
        }

        return Response::success($record);
    }

    /**
     * 转码统计
     */
    public function transcodeStats()
    {
        try {
            $stats = [
                'total' => VideoTranscodeRecord::count(),
                'pending' => VideoTranscodeRecord::where('status', VideoTranscodeRecord::STATUS_PENDING)->count(),
                'processing' => VideoTranscodeRecord::where('status', VideoTranscodeRecord::STATUS_PROCESSING)->count(),
                'completed' => VideoTranscodeRecord::where('status', VideoTranscodeRecord::STATUS_COMPLETED)->count(),
                'failed' => VideoTranscodeRecord::where('status', VideoTranscodeRecord::STATUS_FAILED)->count(),
            ];

            return Response::success($stats);

        } catch (\Exception $e) {
            return Response::error('获取统计失败：' . $e->getMessage());
        }
    }
}
