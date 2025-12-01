<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\CommentLike;
use app\model\Comment;
use think\Request;

/**
 * 评论点赞/点踩控制器
 */
class CommentLikeController extends BaseController
{
    /**
     * 点赞评论
     */
    public function like(Request $request)
    {
        $commentId = $request->param('comment_id', 0);

        if (!$commentId) {
            return Response::error('评论ID不能为空');
        }

        // 检查评论是否存在
        $comment = Comment::find($commentId);
        if (!$comment) {
            return Response::notFound('评论不存在');
        }

        // 获取用户信息
        $userId = null;
        $userIp = $request->ip();

        // 尝试从token获取用户ID
        $token = $request->header('Authorization', '');
        if (!empty($token) && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
            $payload = \app\common\Jwt::verify($token);
            if ($payload !== false) {
                $payload = json_decode(json_encode($payload), true);
                if (isset($payload['id'])) {
                    $userId = $payload['id'];
                }
            }
        }

        // 执行点赞
        $result = CommentLike::like($commentId, $userId, $userIp);

        // 重新加载评论数据
        $comment = Comment::find($commentId);

        return Response::success([
            'action' => $result['action'],
            'type' => $result['type'],
            'like_count' => $comment->like_count,
            'dislike_count' => $comment->dislike_count,
        ], $result['message']);
    }

    /**
     * 点踩评论
     */
    public function dislike(Request $request)
    {
        $commentId = $request->param('comment_id', 0);

        if (!$commentId) {
            return Response::error('评论ID不能为空');
        }

        // 检查评论是否存在
        $comment = Comment::find($commentId);
        if (!$comment) {
            return Response::notFound('评论不存在');
        }

        // 获取用户信息
        $userId = null;
        $userIp = $request->ip();

        // 尝试从token获取用户ID
        $token = $request->header('Authorization', '');
        if (!empty($token) && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
            $payload = \app\common\Jwt::verify($token);
            if ($payload !== false) {
                $payload = json_decode(json_encode($payload), true);
                if (isset($payload['id'])) {
                    $userId = $payload['id'];
                }
            }
        }

        // 执行点踩
        $result = CommentLike::dislike($commentId, $userId, $userIp);

        // 重新加载评论数据
        $comment = Comment::find($commentId);

        return Response::success([
            'action' => $result['action'],
            'type' => $result['type'],
            'like_count' => $comment->like_count,
            'dislike_count' => $comment->dislike_count,
        ], $result['message']);
    }

    /**
     * 获取用户点赞状态
     */
    public function getStatus(Request $request)
    {
        $commentId = $request->param('comment_id', 0);

        if (!$commentId) {
            return Response::error('评论ID不能为空');
        }

        // 获取用户信息
        $userId = null;
        $userIp = $request->ip();

        // 尝试从token获取用户ID
        $token = $request->header('Authorization', '');
        if (!empty($token) && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
            $payload = \app\common\Jwt::verify($token);
            if ($payload !== false) {
                $payload = json_decode(json_encode($payload), true);
                if (isset($payload['id'])) {
                    $userId = $payload['id'];
                }
            }
        }

        $status = CommentLike::getUserLikeStatus($commentId, $userId, $userIp);

        return Response::success([
            'comment_id' => $commentId,
            'status' => $status, // 1-已点赞 2-已点踩 null-未操作
            'status_text' => $status == 1 ? '已点赞' : ($status == 2 ? '已点踩' : '未操作')
        ]);
    }

    /**
     * 批量获取评论点赞状态
     */
    public function getBatchStatus(Request $request)
    {
        $commentIds = $request->param('comment_ids', []);

        if (empty($commentIds) || !is_array($commentIds)) {
            return Response::error('评论ID列表不能为空');
        }

        // 获取用户信息
        $userId = null;
        $userIp = $request->ip();

        // 尝试从token获取用户ID
        $token = $request->header('Authorization', '');
        if (!empty($token) && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
            $payload = \app\common\Jwt::verify($token);
            if ($payload !== false) {
                $payload = json_decode(json_encode($payload), true);
                if (isset($payload['id'])) {
                    $userId = $payload['id'];
                }
            }
        }

        $statuses = CommentLike::getUserLikeStatuses($commentIds, $userId, $userIp);

        return Response::success($statuses);
    }
}
