<?php

namespace app\controller\admin;

use app\BaseController;
use app\common\Response;
use app\model\Comment;
use app\model\CommentReport;
use think\Request;

/**
 * 后台评论管理控制器
 */
class CommentController extends BaseController
{
    /**
     * 获取评论列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 20);
        $status = $request->get('status', '');
        $articleId = $request->get('article_id', 0);
        $userId = $request->get('user_id', 0);
        $isGuest = $request->get('is_guest', '');
        $keyword = $request->get('keyword', '');
        $startDate = $request->get('start_date', '');
        $endDate = $request->get('end_date', '');
        $hasReports = $request->get('has_reports', '');
        $orderBy = $request->get('order_by', 'create_time');
        $orderDir = $request->get('order_dir', 'desc');

        $query = Comment::with(['user', 'article', 'parent']);

        // 状态筛选
        if ($status !== '') {
            $query->where('status', $status);
        }

        // 文章ID筛选
        if ($articleId > 0) {
            $query->where('article_id', $articleId);
        }

        // 用户ID筛选
        if ($userId > 0) {
            $query->where('user_id', $userId);
        }

        // 是否游客筛选
        if ($isGuest !== '') {
            $query->where('is_guest', $isGuest);
        }

        // 关键词搜索（搜索评论内容或用户名）
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('content', 'like', '%' . $keyword . '%')
                  ->whereOr('user_name', 'like', '%' . $keyword . '%')
                  ->whereOr('user_email', 'like', '%' . $keyword . '%');
            });
        }

        // 日期范围筛选
        if (!empty($startDate)) {
            $query->where('create_time', '>=', $startDate . ' 00:00:00');
        }
        if (!empty($endDate)) {
            $query->where('create_time', '<=', $endDate . ' 23:59:59');
        }

        // 有举报的评论
        if ($hasReports === '1') {
            $query->where('report_count', '>', 0);
        }

        // 排序
        $allowedOrderFields = ['create_time', 'like_count', 'dislike_count', 'report_count', 'hot_score'];
        if (in_array($orderBy, $allowedOrderFields)) {
            $query->order($orderBy, $orderDir);
        } else {
            $query->order('create_time', 'desc');
        }

        $comments = $query->paginate([
            'list_rows' => $limit,
            'page' => $page
        ]);

        return Response::success($comments);
    }

    /**
     * 获取评论详情
     */
    public function read(Request $request, $id)
    {
        $comment = Comment::with(['user', 'article', 'parent', 'children', 'reports', 'likes'])
            ->find($id);

        if (!$comment) {
            return Response::notFound('评论不存在');
        }

        // 获取举报信息
        $reports = CommentReport::with(['reporter', 'handler'])
            ->where('comment_id', $id)
            ->order('create_time', 'desc')
            ->select();

        $data = $comment->toArray();
        $data['reports_detail'] = $reports;

        return Response::success($data);
    }

    /**
     * 审核评论（通过/拒绝）
     */
    public function audit(Request $request, $id)
    {
        $status = $request->param('status');

        $validStatuses = [
            Comment::STATUS_APPROVED,
            Comment::STATUS_REJECTED
        ];

        if (!in_array($status, $validStatuses)) {
            return Response::error('审核状态不正确');
        }

        if (Comment::audit($id, $status)) {
            $statusText = $status == Comment::STATUS_APPROVED ? '已通过' : '已拒绝';
            return Response::success([], "评论审核{$statusText}");
        }

        return Response::error('评论不存在或审核失败');
    }

    /**
     * 批量审核评论
     */
    public function batchAudit(Request $request)
    {
        $ids = $request->param('ids', []);
        $status = $request->param('status');

        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要审核的评论');
        }

        $validStatuses = [
            Comment::STATUS_APPROVED,
            Comment::STATUS_REJECTED
        ];

        if (!in_array($status, $validStatuses)) {
            return Response::error('审核状态不正确');
        }

        $successCount = 0;
        foreach ($ids as $id) {
            if (Comment::audit($id, $status)) {
                $successCount++;
            }
        }

        return Response::success([
            'total' => count($ids),
            'success' => $successCount
        ], "成功审核 {$successCount} 条评论");
    }

    /**
     * 删除评论（包含子评论）
     */
    public function delete(Request $request, $id)
    {
        if (Comment::deleteWithChildren($id)) {
            return Response::success([], '删除成功');
        }

        return Response::error('评论不存在或删除失败');
    }

    /**
     * 批量删除评论
     */
    public function batchDelete(Request $request)
    {
        $ids = $request->param('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要删除的评论');
        }

        $successCount = 0;
        foreach ($ids as $id) {
            if (Comment::deleteWithChildren($id)) {
                $successCount++;
            }
        }

        return Response::success([
            'total' => count($ids),
            'success' => $successCount
        ], "成功删除 {$successCount} 条评论");
    }

    /**
     * 更新评论内容（管理员编辑）
     */
    public function update(Request $request, $id)
    {
        $content = $request->param('content', '');

        if (empty($content)) {
            return Response::error('评论内容不能为空');
        }

        $comment = Comment::find($id);
        if (!$comment) {
            return Response::notFound('评论不存在');
        }

        $comment->content = $content;
        if ($comment->save()) {
            return Response::success($comment, '更新成功');
        }

        return Response::error('更新失败');
    }

    /**
     * 获取评论统计数据
     */
    public function statistics(Request $request)
    {
        $stats = Comment::getStatistics();
        return Response::success($stats);
    }

    /**
     * 获取评论趋势数据（最近30天）
     */
    public function trend(Request $request)
    {
        $data = Comment::getTrendData();
        return Response::success($data);
    }

    /**
     * 获取活跃用户（评论最多的用户）
     */
    public function activeUsers(Request $request)
    {
        $limit = $request->get('limit', 10);
        $days = $request->get('days', 30);

        $users = Comment::getActiveUsers($limit, $days);
        return Response::success($users);
    }

    /**
     * 获取热门评论
     */
    public function hotComments(Request $request)
    {
        $articleId = $request->get('article_id', 0);
        $limit = $request->get('limit', 10);

        if ($articleId > 0) {
            $comments = Comment::getHotComments($articleId, $limit);
        } else {
            // 全站热门评论
            $comments = Comment::with(['user', 'article'])
                ->where('status', Comment::STATUS_APPROVED)
                ->where('is_hot', 1)
                ->order('hot_score', 'desc')
                ->limit($limit)
                ->select()
                ->toArray();
        }

        return Response::success($comments);
    }

    /**
     * 标记/取消热门评论
     */
    public function toggleHot(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return Response::notFound('评论不存在');
        }

        $comment->is_hot = $comment->is_hot ? 0 : 1;
        if ($comment->save()) {
            $text = $comment->is_hot ? '已标记为热门' : '已取消热门';
            return Response::success(['is_hot' => $comment->is_hot], $text);
        }

        return Response::error('操作失败');
    }

    /**
     * 获取待审核评论数量
     */
    public function pendingCount(Request $request)
    {
        $count = Comment::where('status', Comment::STATUS_PENDING)->count();
        return Response::success(['count' => $count]);
    }

    /**
     * 批量操作评论状态
     */
    public function batchUpdateStatus(Request $request)
    {
        $ids = $request->param('ids', []);
        $status = $request->param('status');

        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要操作的评论');
        }

        $validStatuses = [
            Comment::STATUS_PENDING,
            Comment::STATUS_APPROVED,
            Comment::STATUS_REJECTED
        ];

        if (!in_array($status, $validStatuses)) {
            return Response::error('状态值不正确');
        }

        $successCount = 0;
        foreach ($ids as $id) {
            if (Comment::audit($id, $status)) {
                $successCount++;
            }
        }

        return Response::success([
            'total' => count($ids),
            'success' => $successCount
        ], "成功更新 {$successCount} 条评论");
    }

    /**
     * 回复评论（管理员身份）
     */
    public function reply(Request $request, $id)
    {
        $content = $request->param('content', '');
        $articleId = $request->param('article_id', 0);

        if (empty($content)) {
            return Response::error('回复内容不能为空');
        }

        $parentComment = Comment::find($id);
        if (!$parentComment) {
            return Response::notFound('父评论不存在');
        }

        // 创建管理员回复
        $comment = Comment::create([
            'article_id' => $articleId ?: $parentComment->article_id,
            'parent_id' => $id,
            'content' => $content,
            'user_id' => $request->user['id'],
            'is_guest' => 0,
            'is_admin' => 1,
            'status' => Comment::STATUS_APPROVED,
            'user_ip' => $request->ip(),
        ]);

        if ($comment) {
            return Response::success($comment, '回复成功');
        }

        return Response::error('回复失败');
    }
}
