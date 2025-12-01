<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 定时任务日志模型
 */
class CronJobLog extends Model
{
    protected $name = 'cron_job_logs';

    // 设置字段信息
    protected $schema = [
        'id'            => 'int',
        'job_id'        => 'int',
        'job_name'      => 'string',
        'status'        => 'string',
        'start_time'    => 'datetime',
        'end_time'      => 'datetime',
        'duration'      => 'int',
        'output'        => 'string',
        'error_message' => 'string',
        'create_time'   => 'datetime',
    ];

    // 自动时间戳
    protected $autoWriteTimestamp = 'create_time';
    protected $updateTime = false;

    // 状态常量
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_RUNNING = 'running';

    /**
     * 关联任务
     */
    public function job()
    {
        return $this->belongsTo(CronJob::class, 'job_id', 'id');
    }

    /**
     * 搜索器：任务ID
     */
    public function searchJobIdAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('job_id', $value);
        }
    }

    /**
     * 搜索器：任务名称
     */
    public function searchJobNameAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('job_name', 'like', '%' . $value . '%');
        }
    }

    /**
     * 搜索器：状态
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('status', $value);
        }
    }

    /**
     * 搜索器：开始时间范围
     */
    public function searchStartTimeRangeAttr($query, $value)
    {
        if (is_array($value) && count($value) == 2) {
            $query->whereBetweenTime('start_time', $value[0], $value[1]);
        }
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_SUCCESS => '成功',
            self::STATUS_FAILED  => '失败',
            self::STATUS_RUNNING => '运行中',
        ];
        return $status[$data['status']] ?? '未知';
    }

    /**
     * 获取运行时长文本
     */
    public function getDurationTextAttr($value, $data)
    {
        if (!isset($data['duration']) || $data['duration'] === null) {
            return '-';
        }

        $seconds = $data['duration'];
        if ($seconds < 60) {
            return $seconds . '秒';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $seconds = $seconds % 60;
            return $minutes . '分' . $seconds . '秒';
        } else {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . '小时' . $minutes . '分';
        }
    }

    /**
     * 创建日志
     * @param int $jobId 任务ID
     * @param string $jobName 任务名称
     * @return static
     */
    public static function createLog($jobId, $jobName)
    {
        return self::create([
            'job_id'     => $jobId,
            'job_name'   => $jobName,
            'status'     => self::STATUS_RUNNING,
            'start_time' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 更新日志为成功
     * @param string $output 输出信息
     */
    public function markAsSuccess($output = '')
    {
        $endTime = date('Y-m-d H:i:s');
        $duration = strtotime($endTime) - strtotime($this->start_time);

        $this->status = self::STATUS_SUCCESS;
        $this->end_time = $endTime;
        $this->duration = $duration;
        $this->output = $output;
        $this->save();
    }

    /**
     * 更新日志为失败
     * @param string $errorMessage 错误信息
     */
    public function markAsFailed($errorMessage = '')
    {
        $endTime = date('Y-m-d H:i:s');
        $duration = strtotime($endTime) - strtotime($this->start_time);

        $this->status = self::STATUS_FAILED;
        $this->end_time = $endTime;
        $this->duration = $duration;
        $this->error_message = $errorMessage;
        $this->save();
    }

    /**
     * 清理旧日志
     * @param int $days 保留天数
     * @return int 删除数量
     */
    public static function cleanOldLogs($days = 30)
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return self::where('create_time', '<', $date)->delete();
    }
}
