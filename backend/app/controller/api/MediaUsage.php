<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\service\MediaUsageService;
use think\App;
use think\Request;

/**
 * 媒体使用追踪控制器
 */
class MediaUsage extends BaseController
{
    protected $usageService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->usageService = new MediaUsageService();
    }

    /**
     * 获取媒体的使用情况
     */
    public function getMediaUsage(Request $request, $mediaId)
    {
        try {
            $info = $this->usageService->getMediaUsageInfo($mediaId);
            return Response::success($info);
        } catch (\Exception $e) {
            return Response::error('获取使用信息失败：' . $e->getMessage());
        }
    }

    /**
     * 获取对象使用的媒体列表
     */
    public function getUsedMedia(Request $request)
    {
        try {
            $usableType = $request->get('usable_type');
            $usableId = $request->get('usable_id');

            if (empty($usableType) || empty($usableId)) {
                return Response::error('参数错误');
            }

            $media = $this->usageService->getUsedMedia($usableType, (int)$usableId);

            return Response::success($media);
        } catch (\Exception $e) {
            return Response::error('获取媒体列表失败：' . $e->getMessage());
        }
    }

    /**
     * 检查媒体是否可以安全删除
     */
    public function checkSafeDelete($mediaId)
    {
        try {
            $result = $this->usageService->canSafelyDelete($mediaId);
            return Response::success($result);
        } catch (\Exception $e) {
            return Response::error('检查失败：' . $e->getMessage());
        }
    }

    /**
     * 获取未使用的媒体列表
     */
    public function getUnusedMedia(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 20);

            $filters = [];
            if ($request->has('file_type')) {
                $filters['file_type'] = $request->get('file_type');
            }
            if ($request->has('days_ago')) {
                $filters['days_ago'] = (int)$request->get('days_ago');
            }

            $result = $this->usageService->getUnusedMedia((int)$page, (int)$pageSize, $filters);

            return Response::paginate(
                $result['list'],
                $result['total'],
                $result['page'],
                $result['pageSize']
            );
        } catch (\Exception $e) {
            return Response::error('获取未使用媒体失败：' . $e->getMessage());
        }
    }

    /**
     * 清理未使用的媒体
     */
    public function cleanUnused(Request $request)
    {
        try {
            $filters = [];
            if ($request->has('file_type')) {
                $filters['file_type'] = $request->post('file_type');
            }
            if ($request->has('days_ago')) {
                $filters['days_ago'] = (int)$request->post('days_ago');
            }

            $result = $this->usageService->cleanUnusedMedia($filters);

            return Response::success($result, "清理完成：已删除 {$result['deleted']} 个媒体");
        } catch (\Exception $e) {
            return Response::error('清理失败：' . $e->getMessage());
        }
    }

    /**
     * 手动记录媒体使用
     */
    public function recordUsage(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $usableType = $request->post('usable_type');
            $usableId = $request->post('usable_id');
            $fieldName = $request->post('field_name', null);

            if (empty($mediaId) || empty($usableType) || empty($usableId)) {
                return Response::error('参数错误');
            }

            $usage = $this->usageService->recordUsage(
                (int)$mediaId,
                $usableType,
                (int)$usableId,
                $fieldName
            );

            return Response::success($usage, '记录成功');
        } catch (\Exception $e) {
            return Response::error('记录失败：' . $e->getMessage());
        }
    }

    /**
     * 删除使用记录
     */
    public function removeUsage(Request $request)
    {
        try {
            $usableType = $request->post('usable_type');
            $usableId = $request->post('usable_id');
            $fieldName = $request->post('field_name', null);

            if (empty($usableType) || empty($usableId)) {
                return Response::error('参数错误');
            }

            $count = $this->usageService->removeUsage($usableType, (int)$usableId, $fieldName);

            return Response::success(['count' => $count], "已删除 {$count} 条记录");
        } catch (\Exception $e) {
            return Response::error('删除失败：' . $e->getMessage());
        }
    }
}
