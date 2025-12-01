<?php

namespace app\model;

use think\Model;

/**
 * 用户信誉度模型
 */
class UserReputation extends Model
{
    protected $name = 'user_reputation';

    /**
     * 获取或创建用户信誉记录
     * @param int $userId
     * @return UserReputation|Model
     */
    public static function getOrCreate(int $userId)
    {
        $reputation = self::where('user_id', $userId)->find();

        if (!$reputation) {
            $reputation = self::create([
                'user_id' => $userId,
                'score' => 100,
                'violation_count' => 0,
                'approved_count' => 0,
                'rejected_count' => 0,
                'auto_approve' => 0
            ]);
        }

        return $reputation;
    }

    /**
     * 记录违规行为
     * @param int $userId
     * @param int $points 扣除分数
     * @return bool
     */
    public static function recordViolation(int $userId, int $points = 5): bool
    {
        $reputation = self::getOrCreate($userId);

        $reputation->violation_count += 1;
        $reputation->score = max(0, $reputation->score - $points);
        $reputation->last_violation_at = date('Y-m-d H:i:s');

        // 信誉分低于60分，取消自动审核通过
        if ($reputation->score < 60) {
            $reputation->auto_approve = 0;
        }

        return $reputation->save();
    }

    /**
     * 记录审核通过
     * @param int $userId
     * @param int $points 增加分数
     * @return bool
     */
    public static function recordApproved(int $userId, int $points = 1): bool
    {
        $reputation = self::getOrCreate($userId);

        $reputation->approved_count += 1;
        $reputation->score = min(100, $reputation->score + $points);

        // 信誉分高于80且通过审核超过50次，自动审核通过
        if ($reputation->score >= 80 && $reputation->approved_count >= 50) {
            $reputation->auto_approve = 1;
        }

        return $reputation->save();
    }

    /**
     * 记录拒绝
     * @param int $userId
     * @param int $points 扣除分数
     * @return bool
     */
    public static function recordRejected(int $userId, int $points = 2): bool
    {
        $reputation = self::getOrCreate($userId);

        $reputation->rejected_count += 1;
        $reputation->score = max(0, $reputation->score - $points);

        // 信誉分低于70分，取消自动审核通过
        if ($reputation->score < 70) {
            $reputation->auto_approve = 0;
        }

        return $reputation->save();
    }

    /**
     * 检查是否可自动通过审核
     * @param int $userId
     * @return bool
     */
    public static function canAutoApprove(int $userId): bool
    {
        $reputation = self::getOrCreate($userId);
        return $reputation->auto_approve == 1 && $reputation->score >= 80;
    }

    /**
     * 获取信誉等级
     * @param int $score
     * @return string
     */
    public static function getLevel(int $score): string
    {
        if ($score >= 90) {
            return '优秀';
        } elseif ($score >= 80) {
            return '良好';
        } elseif ($score >= 60) {
            return '一般';
        } elseif ($score >= 40) {
            return '较差';
        } else {
            return '极差';
        }
    }

    /**
     * 重置用户信誉
     * @param int $userId
     * @return bool
     */
    public static function resetReputation(int $userId): bool
    {
        $reputation = self::getOrCreate($userId);

        return $reputation->save([
            'score' => 100,
            'violation_count' => 0,
            'approved_count' => 0,
            'rejected_count' => 0,
            'auto_approve' => 0,
            'last_violation_at' => null
        ]);
    }

    /**
     * 获取统计信息
     * @return array
     */
    public static function getStatistics(): array
    {
        $excellent = self::where('score', '>=', 90)->count();
        $good = self::where('score', '>=', 80)->where('score', '<', 90)->count();
        $normal = self::where('score', '>=', 60)->where('score', '<', 80)->count();
        $poor = self::where('score', '<', 60)->count();
        $autoApprove = self::where('auto_approve', 1)->count();

        return [
            'excellent' => $excellent,
            'good' => $good,
            'normal' => $normal,
            'poor' => $poor,
            'auto_approve_users' => $autoApprove,
            'total' => self::count()
        ];
    }

    /**
     * 用户关联
     */
    public function user()
    {
        return $this->belongsTo(FrontUser::class, 'user_id', 'id');
    }
}
