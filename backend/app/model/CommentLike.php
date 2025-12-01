<?php

namespace app\model;

use think\Model;

/**
 * 评论点赞/点踩模型
 */
class CommentLike extends Model
{
    protected $name = 'comment_likes';
    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'create_time';
    protected $updateTime = false;

    // 类型常量
    const TYPE_LIKE = 1;    // 点赞
    const TYPE_DISLIKE = 2; // 点踩

    protected $type = [
        'comment_id' => 'integer',
        'user_id'    => 'integer',
        'type'       => 'integer',
    ];

    /**
     * 关联评论
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id', 'id');
    }

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(FrontUser::class, 'user_id', 'id');
    }

    /**
     * 用户对评论点赞
     *
     * @param int $commentId 评论ID
     * @param int|null $userId 用户ID（注册用户）
     * @param string|null $userIp IP地址（游客）
     * @return array
     */
    public static function like(int $commentId, ?int $userId = null, ?string $userIp = null): array
    {
        return self::toggleLike($commentId, self::TYPE_LIKE, $userId, $userIp);
    }

    /**
     * 用户对评论点踩
     *
     * @param int $commentId 评论ID
     * @param int|null $userId 用户ID（注册用户）
     * @param string|null $userIp IP地址（游客）
     * @return array
     */
    public static function dislike(int $commentId, ?int $userId = null, ?string $userIp = null): array
    {
        return self::toggleLike($commentId, self::TYPE_DISLIKE, $userId, $userIp);
    }

    /**
     * 切换点赞/点踩状态
     *
     * @param int $commentId 评论ID
     * @param int $type 类型(1-点赞 2-点踩)
     * @param int|null $userId 用户ID
     * @param string|null $userIp 用户IP
     * @return array
     */
    private static function toggleLike(int $commentId, int $type, ?int $userId, ?string $userIp): array
    {
        // 查找现有记录
        $query = self::where('comment_id', $commentId);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('user_ip', $userIp);
        }

        $existing = $query->find();

        // 如果已有相同类型的记录，则取消
        if ($existing && $existing->type == $type) {
            $existing->delete();

            // 更新评论统计
            $comment = Comment::find($commentId);
            if ($comment) {
                if ($type == self::TYPE_LIKE) {
                    $comment->like_count = max(0, $comment->like_count - 1);
                } else {
                    $comment->dislike_count = max(0, $comment->dislike_count - 1);
                }
                $comment->save();
                self::updateHotScore($comment);
            }

            return [
                'action' => 'cancelled',
                'type' => $type == self::TYPE_LIKE ? 'like' : 'dislike',
                'message' => '已取消'
            ];
        }

        // 如果已有不同类型的记录，先删除旧记录
        if ($existing) {
            $oldType = $existing->type;
            $existing->delete();

            // 减少旧类型计数
            $comment = Comment::find($commentId);
            if ($comment) {
                if ($oldType == self::TYPE_LIKE) {
                    $comment->like_count = max(0, $comment->like_count - 1);
                } else {
                    $comment->dislike_count = max(0, $comment->dislike_count - 1);
                }
            }
        }

        // 创建新记录
        self::create([
            'comment_id' => $commentId,
            'user_id' => $userId,
            'user_ip' => $userIp,
            'type' => $type
        ]);

        // 更新评论统计
        $comment = Comment::find($commentId);
        if ($comment) {
            if ($type == self::TYPE_LIKE) {
                $comment->like_count += 1;
            } else {
                $comment->dislike_count += 1;
            }
            $comment->save();
            self::updateHotScore($comment);
        }

        // 发送点赞通知（仅点赞发送，点踩不发送；仅登录用户可发送）
        if ($type == self::TYPE_LIKE && $userId) {
            \app\service\CommentNotificationService::notifyCommentLike($commentId, $userId);
        }

        return [
            'action' => 'added',
            'type' => $type == self::TYPE_LIKE ? 'like' : 'dislike',
            'message' => $type == self::TYPE_LIKE ? '点赞成功' : '点踩成功'
        ];
    }

    /**
     * 更新评论热度分数
     *
     * @param Comment $comment
     * @return void
     */
    private static function updateHotScore(Comment $comment): void
    {
        // 热度算法: (点赞数 * 2 - 点踩数) / (发布天数 + 2)^1.5
        $days = max(1, (time() - strtotime($comment->create_time)) / 86400);
        $score = ($comment->like_count * 2 - $comment->dislike_count) / pow($days + 2, 1.5);

        $comment->hot_score = round($score, 2);
        $comment->is_hot = $score > 5 ? 1 : 0; // 分数超过5标记为热门
        $comment->save();
    }

    /**
     * 获取用户对评论的点赞状态
     *
     * @param int $commentId 评论ID
     * @param int|null $userId 用户ID
     * @param string|null $userIp 用户IP
     * @return int|null 1-已点赞 2-已点踩 null-未操作
     */
    public static function getUserLikeStatus(int $commentId, ?int $userId, ?string $userIp): ?int
    {
        $query = self::where('comment_id', $commentId);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('user_ip', $userIp);
        }

        $like = $query->find();
        return $like ? $like->type : null;
    }

    /**
     * 批量获取用户对多个评论的点赞状态
     *
     * @param array $commentIds 评论ID数组
     * @param int|null $userId 用户ID
     * @param string|null $userIp 用户IP
     * @return array [comment_id => type]
     */
    public static function getUserLikeStatuses(array $commentIds, ?int $userId, ?string $userIp): array
    {
        $query = self::where('comment_id', 'in', $commentIds);

        if ($userId) {
            $query->where('user_id', $userId);
        } else {
            $query->where('user_ip', $userIp);
        }

        $likes = $query->select();
        $result = [];
        foreach ($likes as $like) {
            $result[$like->comment_id] = $like->type;
        }
        return $result;
    }
}
