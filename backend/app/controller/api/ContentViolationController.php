<?php

namespace app\controller\api;

use app\model\ContentViolation;
use think\Request;
use think\Response;

/**
 * 违规内容管理控制器
 */
class ContentViolationController extends BaseController
{
    /**
     * 获取违规记录列表
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $page = $request->param('page', 1);
        $pageSize = $request->param('page_size', 20);
        $contentType = $request->param('content_type', '');
        $action = $request->param('action', '');
        $status = $request->param('status', '');
        $userId = $request->param('user_id', '');

        $query = ContentViolation::order('created_at desc');

        // 筛选条件
        if ($contentType) {
            $query->where('content_type', $contentType);
        }
        if ($action) {
            $query->where('action', $action);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $total = $query->count();
        $list = $query->page($page, $pageSize)
            ->with(['user', 'reviewer'])
            ->select()
            ->toArray();

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'total' => $total,
                'list' => $list,
                'page' => $page,
                'page_size' => $pageSize
            ]
        ]);
    }

    /**
     * 获取违规记录详情
     * @param int $id
     * @return Response
     */
    public function read(int $id): Response
    {
        $violation = ContentViolation::with(['user', 'reviewer'])->find($id);

        if (!$violation) {
            return json(['code' => 404, 'message' => '记录不存在']);
        }

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => $violation
        ]);
    }

    /**
     * 标记为已审核
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function markAsReviewed(Request $request, int $id): Response
    {
        $userId = $request->userId ?? 0;

        $result = ContentViolation::markAsReviewed($id, $userId);

        if (!$result) {
            return json(['code' => 400, 'message' => '操作失败']);
        }

        return json([
            'code' => 0,
            'message' => '已标记为已审核'
        ]);
    }

    /**
     * 标记为已忽略
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function markAsIgnored(Request $request, int $id): Response
    {
        $userId = $request->userId ?? 0;

        $result = ContentViolation::markAsIgnored($id, $userId);

        if (!$result) {
            return json(['code' => 400, 'message' => '操作失败']);
        }

        return json([
            'code' => 0,
            'message' => '已标记为已忽略'
        ]);
    }

    /**
     * 批量审核
     * @param Request $request
     * @return Response
     */
    public function batchReview(Request $request): Response
    {
        $ids = $request->param('ids', []);
        $status = $request->param('status', ContentViolation::STATUS_REVIEWED);
        $userId = $request->userId ?? 0;

        if (empty($ids)) {
            return json(['code' => 400, 'message' => 'IDs不能为空']);
        }

        foreach ($ids as $id) {
            if ($status === ContentViolation::STATUS_REVIEWED) {
                ContentViolation::markAsReviewed($id, $userId);
            } else {
                ContentViolation::markAsIgnored($id, $userId);
            }
        }

        return json([
            'code' => 0,
            'message' => '批量处理成功'
        ]);
    }

    /**
     * 删除违规记录
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response
    {
        $violation = ContentViolation::find($id);
        if (!$violation) {
            return json(['code' => 404, 'message' => '记录不存在']);
        }

        $violation->delete();

        return json([
            'code' => 0,
            'message' => '删除成功'
        ]);
    }

    /**
     * 获取统计信息
     * @return Response
     */
    public function statistics(): Response
    {
        return json([
            'code' => 0,
            'message' => 'success',
            'data' => ContentViolation::getStatistics()
        ]);
    }
}
