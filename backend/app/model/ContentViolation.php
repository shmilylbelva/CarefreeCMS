<?php

namespace app\model;

use think\Model;

/**
 * 违规内容记录模型
 */
class ContentViolation extends Model
{
    protected $name = 'content_violations';

    // 内容类型常量
    const TYPE_ARTICLE = 'article';
    const TYPE_COMMENT = 'comment';
    const TYPE_PAGE = 'page';

    // 处理动作常量
    const ACTION_WARN = 'warn';       // 警告
    const ACTION_REPLACE = 'replace'; // 替换
    const ACTION_REJECT = 'reject';   // 拒绝

    // 状态常量
    const STATUS_PENDING = 'pending';   // 待处理
    const STATUS_REVIEWED = 'reviewed'; // 已审核
    const STATUS_IGNORED = 'ignored';   // 已忽略

    /**
     * 字段类型定义
     */
    protected $type = [
        'matched_words' => 'json',
    ];

    /**
     * 记录违规内容
     * @param array $data
     * @return ContentViolation|Model
     */
    public static function record(array $data)
    {
        return self::create([
            'content_type' => $data['content_type'],
            'content_id' => $data['content_id'],
            'user_id' => $data['user_id'],
            'matched_words' => $data['matched_words'],
            'original_content' => $data['original_content'] ?? null,
            'filtered_content' => $data['filtered_content'] ?? null,
            'action' => $data['action'],
            'status' => self::STATUS_PENDING
        ]);
    }

    /**
     * 标记为已审核
     * @param int $id
     * @param int $reviewedBy
     * @return bool
     */
    public static function markAsReviewed(int $id, int $reviewedBy): bool
    {
        return self::where('id', $id)->update([
            'status' => self::STATUS_REVIEWED,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s')
        ]) > 0;
    }

    /**
     * 标记为已忽略
     * @param int $id
     * @param int $reviewedBy
     * @return bool
     */
    public static function markAsIgnored(int $id, int $reviewedBy): bool
    {
        return self::where('id', $id)->update([
            'status' => self::STATUS_IGNORED,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => date('Y-m-d H:i:s')
        ]) > 0;
    }

    /**
     * 获取用户违规次数
     * @param int $userId
     * @param int $days 统计天数
     * @return int
     */
    public static function getUserViolationCount(int $userId, int $days = 30): int
    {
        return self::where('user_id', $userId)
            ->where('created_at', '>=', date('Y-m-d H:i:s', strtotime("-{$days} days")))
            ->count();
    }

    /**
     * 获取内容违规记录
     * @param string $contentType
     * @param int $contentId
     * @return array
     */
    public static function getByContent(string $contentType, int $contentId): array
    {
        return self::where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->order('created_at desc')
            ->select()
            ->toArray();
    }

    /**
     * 获取统计信息
     * @return array
     */
    public static function getStatistics(): array
    {
        $total = self::count();
        $pending = self::where('status', self::STATUS_PENDING)->count();
        $reviewed = self::where('status', self::STATUS_REVIEWED)->count();
        $ignored = self::where('status', self::STATUS_IGNORED)->count();
        $byAction = self::group('action')->column('COUNT(*) as count', 'action');
        $byType = self::group('content_type')->column('COUNT(*) as count', 'content_type');

        return [
            'total_count' => $total,
            'pending_count' => $pending,
            'reviewed_count' => $reviewed,
            'ignored_count' => $ignored,
            'by_action' => $byAction,
            'by_type' => $byType
        ];
    }

    /**
     * 获取用户关联
     */
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id', 'id');
    }

    /**
     * 获取审核人关联
     */
    public function reviewer()
    {
        return $this->belongsTo(AdminUser::class, 'reviewed_by', 'id');
    }
}
