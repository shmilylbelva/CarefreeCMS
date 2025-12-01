<?php

namespace app\service;

use app\model\Comment;
use app\model\UserNotification;
use app\model\Article;
use app\model\FrontUser;
use think\facade\Log;

/**
 * 评论通知服务
 */
class CommentNotificationService
{
    /**
     * 发送新评论通知（通知文章作者）
     *
     * @param Comment $comment 评论对象
     * @return bool
     */
    public static function notifyNewComment(Comment $comment): bool
    {
        try {
            $article = Article::find($comment->article_id);
            if (!$article || !$article->author_id) {
                return false;
            }

            // 不给自己发通知
            if ($article->author_id == $comment->user_id) {
                return true;
            }

            // 获取评论者名称
            $commenterName = self::getCommenterName($comment);

            // 创建通知
            UserNotification::createNotification([
                'user_id' => $article->author_id,
                'type' => UserNotification::TYPE_COMMENT_REPLY,
                'title' => '您的文章收到了新评论',
                'content' => "{$commenterName} 评论了您的文章《{$article->title}》",
                'related_type' => 'article',
                'related_id' => $article->id,
                'sender_id' => $comment->user_id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("评论通知发送失败：" . $e->getMessage());
            return false;
        }
    }

    /**
     * 发送回复通知（通知被回复的评论作者）
     *
     * @param Comment $comment 新评论对象
     * @return bool
     */
    public static function notifyCommentReply(Comment $comment): bool
    {
        try {
            // 不是回复则不发送
            if (!$comment->parent_id) {
                return true;
            }

            $parentComment = Comment::find($comment->parent_id);
            if (!$parentComment || !$parentComment->user_id) {
                return false;
            }

            // 不给自己发通知
            if ($parentComment->user_id == $comment->user_id) {
                return true;
            }

            // 获取评论者名称
            $commenterName = self::getCommenterName($comment);

            // 获取文章信息
            $article = Article::find($comment->article_id);
            $articleTitle = $article ? $article->title : '文章';

            // 创建通知
            UserNotification::createNotification([
                'user_id' => $parentComment->user_id,
                'type' => UserNotification::TYPE_COMMENT_REPLY,
                'title' => '您的评论收到了回复',
                'content' => "{$commenterName} 回复了您在《{$articleTitle}》下的评论",
                'related_type' => 'comment',
                'related_id' => $comment->id,
                'sender_id' => $comment->user_id
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("评论回复通知发送失败：" . $e->getMessage());
            return false;
        }
    }

    /**
     * 发送点赞通知（通知评论作者）
     *
     * @param int $commentId 评论ID
     * @param int $likerId 点赞者ID
     * @return bool
     */
    public static function notifyCommentLike(int $commentId, int $likerId): bool
    {
        try {
            $comment = Comment::find($commentId);
            if (!$comment || !$comment->user_id) {
                return false;
            }

            // 不给自己发通知
            if ($comment->user_id == $likerId) {
                return true;
            }

            // 获取点赞者信息
            $liker = FrontUser::find($likerId);
            $likerName = $liker ? ($liker->nickname ?: $liker->username) : '有人';

            // 获取文章信息
            $article = Article::find($comment->article_id);
            $articleTitle = $article ? $article->title : '文章';

            // 创建通知
            UserNotification::createNotification([
                'user_id' => $comment->user_id,
                'type' => UserNotification::TYPE_COMMENT_LIKE,
                'title' => '您的评论收到了点赞',
                'content' => "{$likerName} 赞了您在《{$articleTitle}》下的评论",
                'related_type' => 'comment',
                'related_id' => $commentId,
                'sender_id' => $likerId
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("评论点赞通知发送失败：" . $e->getMessage());
            return false;
        }
    }

    /**
     * 发送评论审核通过通知
     *
     * @param Comment $comment 评论对象
     * @return bool
     */
    public static function notifyCommentApproved(Comment $comment): bool
    {
        try {
            // 游客评论没有user_id，不发送通知
            if (!$comment->user_id) {
                return true;
            }

            // 获取文章信息
            $article = Article::find($comment->article_id);
            $articleTitle = $article ? $article->title : '文章';

            // 创建通知
            UserNotification::createNotification([
                'user_id' => $comment->user_id,
                'type' => 'comment_approved',
                'title' => '您的评论已通过审核',
                'content' => "您在《{$articleTitle}》下的评论已通过审核",
                'related_type' => 'comment',
                'related_id' => $comment->id,
                'sender_id' => null
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("评论审核通过通知发送失败：" . $e->getMessage());
            return false;
        }
    }

    /**
     * 发送评论审核拒绝通知
     *
     * @param Comment $comment 评论对象
     * @param string|null $reason 拒绝原因
     * @return bool
     */
    public static function notifyCommentRejected(Comment $comment, ?string $reason = null): bool
    {
        try {
            // 游客评论没有user_id，不发送通知
            if (!$comment->user_id) {
                return true;
            }

            // 获取文章信息
            $article = Article::find($comment->article_id);
            $articleTitle = $article ? $article->title : '文章';

            $content = "您在《{$articleTitle}》下的评论未通过审核";
            if ($reason) {
                $content .= "，原因：{$reason}";
            }

            // 创建通知
            UserNotification::createNotification([
                'user_id' => $comment->user_id,
                'type' => 'comment_rejected',
                'title' => '您的评论未通过审核',
                'content' => $content,
                'related_type' => 'comment',
                'related_id' => $comment->id,
                'sender_id' => null
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("评论审核拒绝通知发送失败：" . $e->getMessage());
            return false;
        }
    }

    /**
     * 发送评论被举报通知（通知评论作者）
     *
     * @param Comment $comment 评论对象
     * @return bool
     */
    public static function notifyCommentReported(Comment $comment): bool
    {
        try {
            // 游客评论没有user_id，不发送通知
            if (!$comment->user_id) {
                return true;
            }

            // 获取文章信息
            $article = Article::find($comment->article_id);
            $articleTitle = $article ? $article->title : '文章';

            // 创建通知
            UserNotification::createNotification([
                'user_id' => $comment->user_id,
                'type' => 'comment_reported',
                'title' => '您的评论被多次举报',
                'content' => "您在《{$articleTitle}》下的评论因被多次举报已被暂时隐藏，管理员将尽快审核处理",
                'related_type' => 'comment',
                'related_id' => $comment->id,
                'sender_id' => null
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("评论举报通知发送失败：" . $e->getMessage());
            return false;
        }
    }

    /**
     * 发送评论被删除通知（通知评论作者）
     *
     * @param Comment $comment 评论对象
     * @param string|null $reason 删除原因
     * @return bool
     */
    public static function notifyCommentDeleted(Comment $comment, ?string $reason = null): bool
    {
        try {
            // 游客评论没有user_id，不发送通知
            if (!$comment->user_id) {
                return true;
            }

            // 获取文章信息
            $article = Article::find($comment->article_id);
            $articleTitle = $article ? $article->title : '文章';

            $content = "您在《{$articleTitle}》下的评论已被管理员删除";
            if ($reason) {
                $content .= "，原因：{$reason}";
            }

            // 创建通知
            UserNotification::createNotification([
                'user_id' => $comment->user_id,
                'type' => 'comment_deleted',
                'title' => '您的评论已被删除',
                'content' => $content,
                'related_type' => 'comment',
                'related_id' => $comment->id,
                'sender_id' => null
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error("评论删除通知发送失败：" . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取评论者名称
     *
     * @param Comment $comment
     * @return string
     */
    private static function getCommenterName(Comment $comment): string
    {
        if ($comment->user_id && $comment->user) {
            return $comment->user->nickname ?: $comment->user->username;
        }
        return $comment->user_name ?: '游客';
    }
}
