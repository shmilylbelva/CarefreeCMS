<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 定时任务模型
 */
class CronJob extends Model
{
    protected $name = 'cron_jobs';

    // 设置字段信息
    protected $schema = [
        'id'                => 'int',
        'name'              => 'string',
        'title'             => 'string',
        'cron_expression'   => 'string',
        'command'           => 'string',
        'params'            => 'json',
        'is_enabled'        => 'int',
        'is_system'         => 'int',
        'run_count'         => 'int',
        'last_run_time'     => 'datetime',
        'last_run_status'   => 'string',
        'last_run_duration' => 'int',
        'next_run_time'     => 'datetime',
        'description'       => 'string',
        'create_time'       => 'datetime',
        'update_time'       => 'datetime',
    ];

    // 自动时间戳
    protected $autoWriteTimestamp = true;

    // 字段类型定义
    protected $type = [
        'params' => 'json',
    ];

    // 状态常量
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    // 运行状态常量
    const RUN_STATUS_SUCCESS = 'success';
    const RUN_STATUS_FAILED = 'failed';
    const RUN_STATUS_RUNNING = 'running';

    /**
     * 关联执行日志
     */
    public function logs()
    {
        return $this->hasMany(CronJobLog::class, 'job_id', 'id');
    }

    /**
     * 获取最近的日志
     */
    public function getRecentLogs($limit = 10)
    {
        return $this->logs()
            ->order('start_time', 'desc')
            ->limit($limit)
            ->select();
    }

    /**
     * 搜索器：任务名称
     */
    public function searchNameAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('name', 'like', '%' . $value . '%');
        }
    }

    /**
     * 搜索器：任务标题
     */
    public function searchTitleAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('title', 'like', '%' . $value . '%');
        }
    }

    /**
     * 搜索器：启用状态
     */
    public function searchIsEnabledAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('is_enabled', $value);
        }
    }

    /**
     * 搜索器：是否系统任务
     */
    public function searchIsSystemAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('is_system', $value);
        }
    }

    /**
     * 搜索器：最后运行状态
     */
    public function searchLastRunStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('last_run_status', $value);
        }
    }

    /**
     * 获取状态文本
     */
    public function getIsEnabledTextAttr($value, $data)
    {
        return $data['is_enabled'] == self::STATUS_ENABLED ? '启用' : '禁用';
    }

    /**
     * 获取类型文本
     */
    public function getIsSystemTextAttr($value, $data)
    {
        return $data['is_system'] ? '系统任务' : '自定义任务';
    }

    /**
     * 获取状态文本
     */
    public function getLastRunStatusTextAttr($value, $data)
    {
        $status = [
            self::RUN_STATUS_SUCCESS => '成功',
            self::RUN_STATUS_FAILED  => '失败',
            self::RUN_STATUS_RUNNING => '运行中',
        ];
        return isset($data['last_run_status']) ? ($status[$data['last_run_status']] ?? '未运行') : '未运行';
    }

    /**
     * 计算下次运行时间
     * @param string $cronExpression Cron表达式
     * @param string $baseTime 基准时间（默认当前时间）
     * @return string|null 下次运行时间
     */
    public static function calculateNextRunTime($cronExpression, $baseTime = null)
    {
        try {
            $cron = new \Cron\CronExpression($cronExpression);
            $nextRun = $cron->getNextRunDate($baseTime);
            return $nextRun->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 验证Cron表达式
     * @param string $cronExpression
     * @return bool
     */
    public static function validateCronExpression($cronExpression)
    {
        try {
            new \Cron\CronExpression($cronExpression);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 更新运行信息
     * @param string $status 状态
     * @param int $duration 运行时长（秒）
     */
    public function updateRunInfo($status, $duration = 0)
    {
        $this->run_count = $this->run_count + 1;
        $this->last_run_time = date('Y-m-d H:i:s');
        $this->last_run_status = $status;
        $this->last_run_duration = $duration;
        $this->next_run_time = self::calculateNextRunTime($this->cron_expression);
        $this->save();
    }

    /**
     * 获取待执行的任务列表
     * @return \think\Collection
     */
    public static function getPendingJobs()
    {
        $now = date('Y-m-d H:i:s');
        return self::where('is_enabled', self::STATUS_ENABLED)
            ->where(function ($query) use ($now) {
                $query->whereNull('next_run_time')
                    ->whereOr('next_run_time', '<=', $now);
            })
            ->select();
    }
}
