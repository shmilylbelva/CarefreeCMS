<?php

namespace app\model;

use think\Model;

/**
 * 评论举报模型
 */
class CommentReport extends Model
{
    protected $name = 'comment_reports';
    protected $autoWriteTimestamp = true;

    // 举报原因常量
    const REASON_SPAM = 'spam';       // 垃圾信息
    const REASON_ABUSE = 'abuse';     // 辱骂攻击
    const REASON_PORN = 'porn';       // 色情低俗
    const REASON_AD = 'ad';           // 广告信息
    const REASON_OTHER = 'other';     // 其他原因

    // 处理状态常量
    const STATUS_PENDING = 0;     // 待处理
    const STATUS_HANDLED = 1;     // 已处理
    const STATUS_IGNORED = 2;     // 已忽略

    // 处理结果常量
    const RESULT_DELETED = 'deleted';   // 已删除评论
    const RESULT_APPROVED = 'approved'; // 误报，评论正常

    protected $type = [
        'comment_id'  => 'integer',
        'reporter_id' => 'integer',
        'status'      => 'integer',
        'handler_id'  => 'integer',
    ];

    /**
     * 关联评论
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id', 'id');
    }

    /**
     * 关联举报人
     */
    public function reporter()
    {
        return $this->belongsTo(FrontUser::class, 'reporter_id', 'id');
    }

    /**
     * 关联处理人
     */
    public function handler()
    {
        return $this->belongsTo(\app\model\User::class, 'handler_id', 'id');
    }

    /**
     * 举报评论
     *
     * @param int $commentId 评论ID
     * @param string $reason 举报原因
     * @param string|null $reasonDetail 详细说明
     * @param int|null $reporterId 举报人ID（注册用户）
     * @param string|null $reporterIp 举报人IP（游客）
     * @param string|null $reporterEmail 举报人邮箱（游客）
     * @return bool|Model
     */
    public static function reportComment(
        int $commentId,
        string $reason = self::REASON_SPAM,
        ?string $reasonDetail = null,
        ?int $reporterId = null,
        ?string $reporterIp = null,
        ?string $reporterEmail = null
    ) {
        // 检查评论是否存在
        $comment = Comment::find($commentId);
        if (!$comment) {
            return false;
        }

        // 检查是否重复举报（24小时内）
        $query = self::where('comment_id', $commentId)
            ->where('create_time', '>=', date('Y-m-d H:i:s', time() - 86400));

        if ($reporterId) {
            $query->where('reporter_id', $reporterId);
        } else {
            $query->where('reporter_ip', $reporterIp);
        }

        if ($query->find()) {
            return false; // 已举报过
        }

        // 创建举报记录
        $report = self::create([
            'comment_id' => $commentId,
            'reporter_id' => $reporterId,
            'reporter_ip' => $reporterIp,
            'reporter_email' => $reporterEmail,
            'reason' => $reason,
            'reason_detail' => $reasonDetail,
            'status' => self::STATUS_PENDING
        ]);

        // 更新评论被举报次数
        $comment->report_count += 1;
        $comment->save();

        // 如果举报次数达到阈值，自动隐藏评论
        if ($comment->report_count >= 5) {
            $comment->status = Comment::STATUS_PENDING;
            $comment->save();

            // 发送被举报通知
            \app\service\CommentNotificationService::notifyCommentReported($comment);
        }

        return $report;
    }

    /**
     * 处理举报
     *
     * @param int $reportId 举报ID
     * @param string $result 处理结果
     * @param int $handlerId 处理人ID
     * @param string|null $remark 处理备注
     * @return bool
     */
    public static function handleReport(int $reportId, string $result, int $handlerId, ?string $remark = null): bool
    {
        $report = self::find($reportId);
        if (!$report || $report->status != self::STATUS_PENDING) {
            return false;
        }

        $report->status = self::STATUS_HANDLED;
        $report->handle_result = $result;
        $report->handler_id = $handlerId;
        $report->handle_time = date('Y-m-d H:i:s');
        $report->handle_remark = $remark;
        $report->save();

        // 如果处理结果是删除评论
        if ($result == self::RESULT_DELETED) {
            $comment = Comment::find($report->comment_id);
            if ($comment) {
                $comment->status = Comment::STATUS_REJECTED;
                $comment->save();
            }
        }

        return true;
    }

    /**
     * 忽略举报
     *
     * @param int $reportId 举报ID
     * @param int $handlerId 处理人ID
     * @return bool
     */
    public static function ignoreReport(int $reportId, int $handlerId): bool
    {
        $report = self::find($reportId);
        if (!$report || $report->status != self::STATUS_PENDING) {
            return false;
        }

        $report->status = self::STATUS_IGNORED;
        $report->handler_id = $handlerId;
        $report->handle_time = date('Y-m-d H:i:s');
        $report->save();

        return true;
    }

    /**
     * 批量处理举报
     *
     * @param array $reportIds 举报ID数组
     * @param string $result 处理结果
     * @param int $handlerId 处理人ID
     * @return int 处理成功数量
     */
    public static function batchHandle(array $reportIds, string $result, int $handlerId): int
    {
        $count = 0;
        foreach ($reportIds as $reportId) {
            if (self::handleReport($reportId, $result, $handlerId)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 获取举报统计
     *
     * @return array
     */
    public static function getStatistics(): array
    {
        return [
            'total' => self::count(),
            'pending' => self::where('status', self::STATUS_PENDING)->count(),
            'handled' => self::where('status', self::STATUS_HANDLED)->count(),
            'ignored' => self::where('status', self::STATUS_IGNORED)->count(),
            'today' => self::whereTime('create_time', 'today')->count(),
            'week' => self::whereTime('create_time', 'week')->count(),
            'month' => self::whereTime('create_time', 'month')->count(),
        ];
    }

    /**
     * 获取器：举报原因文本
     */
    public function getReasonTextAttr($value, $data)
    {
        $reasons = [
            self::REASON_SPAM => '垃圾信息',
            self::REASON_ABUSE => '辱骂攻击',
            self::REASON_PORN => '色情低俗',
            self::REASON_AD => '广告信息',
            self::REASON_OTHER => '其他原因',
        ];
        return $reasons[$data['reason']] ?? '未知';
    }

    /**
     * 获取器：处理状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $statuses = [
            self::STATUS_PENDING => '待处理',
            self::STATUS_HANDLED => '已处理',
            self::STATUS_IGNORED => '已忽略',
        ];
        return $statuses[$data['status']] ?? '未知';
    }

    /**
     * 获取器：处理结果文本
     */
    public function getHandleResultTextAttr($value, $data)
    {
        if (empty($data['handle_result'])) {
            return '';
        }

        $results = [
            self::RESULT_DELETED => '评论已删除',
            self::RESULT_APPROVED => '误报，评论正常',
        ];
        return $results[$data['handle_result']] ?? $data['handle_result'];
    }
}
