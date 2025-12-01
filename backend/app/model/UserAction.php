<?php

namespace app\model;

/**
 * 用户行为模型
 * 替代：user_likes, user_favorites, user_follows
 */
class UserAction extends SiteModel
{
    protected $name = 'user_actions';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = false;

    protected $type = [
        'user_id' => 'integer',
        'target_id' => 'integer',
        'site_id' => 'integer',
    ];

    // 行为类型常量
    const ACTION_LIKE = 'like';         // 点赞
    const ACTION_DISLIKE = 'dislike';   // 踩
    const ACTION_FAVORITE = 'favorite'; // 收藏
    const ACTION_FOLLOW = 'follow';     // 关注

    // 目标类型常量
    const TARGET_ARTICLE = 'article';
    const TARGET_COMMENT = 'comment';
    const TARGET_USER = 'user';

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(FrontUser::class, 'user_id', 'id');
    }

    /**
     * 添加点赞
     */
    public static function addLike($userId, $targetType, $targetId, $siteId = 1)
    {
        return self::create([
            'user_id' => $userId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'action_type' => self::ACTION_LIKE,
            'site_id' => $siteId,
        ]);
    }

    /**
     * 添加踩
     */
    public static function addDislike($userId, $targetType, $targetId, $siteId = 1)
    {
        return self::create([
            'user_id' => $userId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'action_type' => self::ACTION_DISLIKE,
            'site_id' => $siteId,
        ]);
    }

    /**
     * 添加收藏
     */
    public static function addFavorite($userId, $targetType, $targetId, $siteId = 1)
    {
        return self::create([
            'user_id' => $userId,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'action_type' => self::ACTION_FAVORITE,
            'site_id' => $siteId,
        ]);
    }

    /**
     * 添加关注
     */
    public static function addFollow($userId, $followUserId, $siteId = 1)
    {
        return self::create([
            'user_id' => $userId,
            'target_type' => self::TARGET_USER,
            'target_id' => $followUserId,
            'action_type' => self::ACTION_FOLLOW,
            'site_id' => $siteId,
        ]);
    }

    /**
     * 取消点赞
     */
    public static function removeLike($userId, $targetType, $targetId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', self::ACTION_LIKE)
            ->delete();
    }

    /**
     * 取消踩
     */
    public static function removeDislike($userId, $targetType, $targetId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', self::ACTION_DISLIKE)
            ->delete();
    }

    /**
     * 取消收藏
     */
    public static function removeFavorite($userId, $targetType, $targetId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', self::ACTION_FAVORITE)
            ->delete();
    }

    /**
     * 取消关注
     */
    public static function removeFollow($userId, $followUserId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', self::TARGET_USER)
            ->where('target_id', $followUserId)
            ->where('action_type', self::ACTION_FOLLOW)
            ->delete();
    }

    /**
     * 检查是否已点赞
     */
    public static function hasLiked($userId, $targetType, $targetId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', self::ACTION_LIKE)
            ->count() > 0;
    }

    /**
     * 检查是否已踩
     */
    public static function hasDisliked($userId, $targetType, $targetId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', self::ACTION_DISLIKE)
            ->count() > 0;
    }

    /**
     * 检查是否已收藏
     */
    public static function hasFavorited($userId, $targetType, $targetId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', self::ACTION_FAVORITE)
            ->count() > 0;
    }

    /**
     * 检查是否已关注
     */
    public static function hasFollowed($userId, $followUserId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', self::TARGET_USER)
            ->where('target_id', $followUserId)
            ->where('action_type', self::ACTION_FOLLOW)
            ->count() > 0;
    }

    /**
     * 获取点赞数
     */
    public static function getLikeCount($targetType, $targetId)
    {
        return self::where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', self::ACTION_LIKE)
            ->count();
    }

    /**
     * 获取踩数
     */
    public static function getDislikeCount($targetType, $targetId)
    {
        return self::where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', self::ACTION_DISLIKE)
            ->count();
    }

    /**
     * 获取收藏数
     */
    public static function getFavoriteCount($targetType, $targetId)
    {
        return self::where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('action_type', self::ACTION_FAVORITE)
            ->count();
    }

    /**
     * 获取粉丝数
     */
    public static function getFollowerCount($userId)
    {
        return self::where('target_type', self::TARGET_USER)
            ->where('target_id', $userId)
            ->where('action_type', self::ACTION_FOLLOW)
            ->count();
    }

    /**
     * 获取关注数
     */
    public static function getFollowingCount($userId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', self::TARGET_USER)
            ->where('action_type', self::ACTION_FOLLOW)
            ->count();
    }

    /**
     * 获取用户收藏的文章ID列表
     */
    public static function getUserFavoriteArticleIds($userId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', self::TARGET_ARTICLE)
            ->where('action_type', self::ACTION_FAVORITE)
            ->order('created_at', 'desc')
            ->column('target_id');
    }

    /**
     * 获取用户关注的用户ID列表
     */
    public static function getUserFollowingIds($userId)
    {
        return self::where('user_id', $userId)
            ->where('target_type', self::TARGET_USER)
            ->where('action_type', self::ACTION_FOLLOW)
            ->order('created_at', 'desc')
            ->column('target_id');
    }

    /**
     * 获取用户的粉丝ID列表
     */
    public static function getUserFollowerIds($userId)
    {
        return self::where('target_type', self::TARGET_USER)
            ->where('target_id', $userId)
            ->where('action_type', self::ACTION_FOLLOW)
            ->order('created_at', 'desc')
            ->column('user_id');
    }
}
