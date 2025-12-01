<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\CommentReport;
use app\model\Comment;
use think\Request;

/**
 * 评论举报控制器
 */
class CommentReportController extends BaseController
{
    /**
     * 举报评论（前台用户）
     */
    public function report(Request $request)
    {
        $commentId = $request->param('comment_id', 0);
        $reason = $request->param('reason', CommentReport::REASON_SPAM);
        $reasonDetail = $request->param('reason_detail', '');

        if (!$commentId) {
            return Response::error('评论ID不能为空');
        }

        // 验证举报原因
        $validReasons = [
            CommentReport::REASON_SPAM,
            CommentReport::REASON_ABUSE,
            CommentReport::REASON_PORN,
            CommentReport::REASON_AD,
            CommentReport::REASON_OTHER
        ];

        if (!in_array($reason, $validReasons)) {
            return Response::error('举报原因不正确');
        }

        // 获取用户信息
        $reporterId = null;
        $reporterIp = $request->ip();
        $reporterEmail = $request->param('email', '');

        // 尝试从token获取用户ID
        $token = $request->header('Authorization', '');
        if (!empty($token) && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
            $payload = \app\common\Jwt::verify($token);
            if ($payload !== false) {
                $payload = json_decode(json_encode($payload), true);
                if (isset($payload['id'])) {
                    $reporterId = $payload['id'];
                }
            }
        }

        // 游客举报需要提供邮箱
        if (!$reporterId && empty($reporterEmail)) {
            return Response::error('游客举报需要提供邮箱');
        }

        // 执行举报
        $result = CommentReport::reportComment(
            $commentId,
            $reason,
            $reasonDetail,
            $reporterId,
            $reporterIp,
            $reporterEmail
        );

        if (!$result) {
            return Response::error('举报失败，可能评论不存在或您已举报过该评论');
        }

        return Response::success([], '举报成功，管理员将尽快处理');
    }

    /**
     * 获取举报列表（后台管理员）
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $status = $request->get('status', '');
        $reason = $request->get('reason', '');
        $commentId = $request->get('comment_id', 0);

        $query = CommentReport::with(['comment', 'reporter']);

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($reason) {
            $query->where('reason', $reason);
        }

        if ($commentId > 0) {
            $query->where('comment_id', $commentId);
        }

        $query->order('create_time', 'desc');

        $reports = $query->paginate([
            'list_rows' => $limit,
            'page' => $page
        ]);

        return Response::success($reports);
    }

    /**
     * 获取举报详情（后台管理员）
     */
    public function read(Request $request, $id)
    {
        $report = CommentReport::with(['comment', 'reporter', 'handler'])->find($id);

        if (!$report) {
            return Response::notFound('举报记录不存在');
        }

        return Response::success($report);
    }

    /**
     * 处理举报（后台管理员）
     */
    public function handle(Request $request, $id)
    {
        $result = $request->param('result', CommentReport::RESULT_DELETED);
        $remark = $request->param('remark', '');

        // 验证处理结果
        $validResults = [
            CommentReport::RESULT_DELETED,
            CommentReport::RESULT_APPROVED
        ];

        if (!in_array($result, $validResults)) {
            return Response::error('处理结果不正确');
        }

        $handlerId = $request->user['id'];

        if (CommentReport::handleReport($id, $result, $handlerId, $remark)) {
            return Response::success([], '处理成功');
        }

        return Response::error('处理失败');
    }

    /**
     * 忽略举报（后台管理员）
     */
    public function ignore(Request $request, $id)
    {
        $handlerId = $request->user['id'];

        if (CommentReport::ignoreReport($id, $handlerId)) {
            return Response::success([], '已忽略');
        }

        return Response::error('操作失败');
    }

    /**
     * 批量处理举报（后台管理员）
     */
    public function batchHandle(Request $request)
    {
        $ids = $request->param('ids', []);
        $result = $request->param('result', CommentReport::RESULT_DELETED);

        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要处理的举报');
        }

        $handlerId = $request->user['id'];
        $count = CommentReport::batchHandle($ids, $result, $handlerId);

        return Response::success([
            'total' => count($ids),
            'success' => $count
        ], "成功处理 {$count} 条举报");
    }

    /**
     * 获取举报统计（后台管理员）
     */
    public function statistics(Request $request)
    {
        $stats = CommentReport::getStatistics();
        return Response::success($stats);
    }

    /**
     * 删除举报记录（后台管理员）
     */
    public function delete(Request $request, $id)
    {
        $report = CommentReport::find($id);
        if (!$report) {
            return Response::notFound('举报记录不存在');
        }

        if ($report->delete()) {
            return Response::success([], '删除成功');
        }

        return Response::error('删除失败');
    }
}
