<?php
declare (strict_types = 1);

namespace app\service;

use app\model\CronJob;
use app\model\CronJobLog;
use think\Exception;
use think\facade\Log;

/**
 * 定时任务服务
 */
class CronJobService
{
    /**
     * 获取任务列表
     * @param array $params 查询参数
     * @return array
     */
    public function getList($params = [])
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;

        // 过滤掉非搜索参数
        $searchParams = $params;
        unset($searchParams['page'], $searchParams['limit']);

        $query = CronJob::withSearch(array_keys($searchParams), $searchParams);

        $total = $query->count();
        $list = $query->page($page, $limit)
            ->order('id', 'desc')
            ->select();

        return [
            'total' => $total,
            'list'  => $list,
            'page'  => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取任务详情
     * @param int $id 任务ID
     * @return CronJob
     * @throws Exception
     */
    public function getDetail($id)
    {
        $job = CronJob::find($id);

        if (!$job) {
            throw new Exception('任务不存在');
        }

        return $job;
    }

    /**
     * 创建任务
     * @param array $data 任务数据
     * @return CronJob
     * @throws Exception
     */
    public function create($data)
    {
        // 验证任务名称唯一性
        if (CronJob::where('name', $data['name'])->count() > 0) {
            throw new Exception('任务名称已存在');
        }

        // 验证Cron表达式
        if (!CronJob::validateCronExpression($data['cron_expression'])) {
            throw new Exception('Cron表达式格式不正确');
        }

        // 计算下次运行时间
        $data['next_run_time'] = CronJob::calculateNextRunTime($data['cron_expression']);

        // 处理params字段
        if (isset($data['params']) && is_string($data['params'])) {
            $data['params'] = json_decode($data['params'], true);
        }

        $job = CronJob::create($data);

        return $job;
    }

    /**
     * 更新任务
     * @param int $id 任务ID
     * @param array $data 任务数据
     * @return bool
     * @throws Exception
     */
    public function update($id, $data)
    {
        $job = CronJob::find($id);
        if (!$job) {
            throw new Exception('任务不存在');
        }

        // 系统任务不允许修改某些字段
        if ($job->is_system) {
            unset($data['name'], $data['command'], $data['is_system']);
        }

        // 如果修改了任务名称，验证唯一性
        if (isset($data['name']) && $data['name'] != $job->name) {
            if (CronJob::where('name', $data['name'])->where('id', '<>', $id)->count() > 0) {
                throw new Exception('任务名称已存在');
            }
        }

        // 如果修改了Cron表达式，验证格式并重新计算下次运行时间
        if (isset($data['cron_expression']) && $data['cron_expression'] != $job->cron_expression) {
            if (!CronJob::validateCronExpression($data['cron_expression'])) {
                throw new Exception('Cron表达式格式不正确');
            }
            $data['next_run_time'] = CronJob::calculateNextRunTime($data['cron_expression']);
        }

        // 处理params字段
        if (isset($data['params']) && is_string($data['params'])) {
            $data['params'] = json_decode($data['params'], true);
        }

        return $job->save($data);
    }

    /**
     * 删除任务
     * @param int $id 任务ID
     * @return bool
     * @throws Exception
     */
    public function delete($id)
    {
        $job = CronJob::find($id);
        if (!$job) {
            throw new Exception('任务不存在');
        }

        // 不允许删除系统任务
        if ($job->is_system) {
            throw new Exception('不允许删除系统任务');
        }

        // 删除任务日志
        CronJobLog::where('job_id', $id)->delete();

        return $job->delete();
    }

    /**
     * 批量删除任务
     * @param array $ids 任务ID数组
     * @return int 删除数量
     */
    public function batchDelete($ids)
    {
        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->delete($id);
                $count++;
            } catch (\Exception $e) {
                // 记录错误，继续删除其他任务
                Log::error('删除定时任务失败: ' . $e->getMessage());
                continue;
            }
        }
        return $count;
    }

    /**
     * 更新任务状态
     * @param int $id 任务ID
     * @param int $isEnabled 启用状态
     * @return bool
     * @throws Exception
     */
    public function updateStatus($id, $isEnabled)
    {
        $job = CronJob::find($id);
        if (!$job) {
            throw new Exception('任务不存在');
        }

        // 如果启用任务，重新计算下次运行时间
        if ($isEnabled == CronJob::STATUS_ENABLED) {
            $nextRunTime = CronJob::calculateNextRunTime($job->cron_expression);
            return $job->save([
                'is_enabled'    => $isEnabled,
                'next_run_time' => $nextRunTime,
            ]);
        }

        return $job->save(['is_enabled' => $isEnabled]);
    }

    /**
     * 手动执行任务
     * @param int $id 任务ID
     * @return array 执行结果
     * @throws Exception
     */
    public function runNow($id)
    {
        $job = CronJob::find($id);
        if (!$job) {
            throw new Exception('任务不存在');
        }

        // 创建日志
        $log = CronJobLog::createLog($job->id, $job->name);

        try {
            // 执行任务
            $result = $this->executeJob($job);

            // 更新日志为成功
            $log->markAsSuccess($result['output'] ?? '');

            // 更新任务运行信息
            $job->updateRunInfo(CronJob::RUN_STATUS_SUCCESS, $log->duration);

            return [
                'success' => true,
                'message' => '任务执行成功',
                'output'  => $result['output'] ?? '',
                'log_id'  => $log->id,
            ];
        } catch (\Exception $e) {
            // 更新日志为失败
            $log->markAsFailed($e->getMessage());

            // 更新任务运行信息
            $job->updateRunInfo(CronJob::RUN_STATUS_FAILED, $log->duration);

            throw new Exception('任务执行失败: ' . $e->getMessage());
        }
    }

    /**
     * 执行任务
     * @param CronJob $job 任务对象
     * @return array 执行结果
     * @throws Exception
     */
    protected function executeJob($job)
    {
        $command = $job->command;
        $params = $job->params ?? [];

        // 根据命令类型执行不同的逻辑
        switch ($command) {
            case 'database:backup':
                return $this->executeDatabaseBackup($params);
            case 'cache:clear':
                return $this->executeCacheClear($params);
            case 'log:clean':
                return $this->executeLogClean($params);
            case 'article:publish':
                return $this->executeArticlePublish($params);
            default:
                // 如果是自定义命令，可以在这里扩展
                throw new Exception('不支持的命令类型: ' . $command);
        }
    }

    /**
     * 执行数据库备份
     * @param array $params 参数
     * @return array
     */
    protected function executeDatabaseBackup($params)
    {
        try {
            $backupService = new \app\service\DatabaseService();
            $tables = $params['tables'] ?? [];
            $result = $backupService->backup($tables);

            return [
                'success' => true,
                'output'  => '数据库备份完成，文件: ' . ($result['file'] ?? ''),
            ];
        } catch (\Exception $e) {
            throw new Exception('数据库备份失败: ' . $e->getMessage());
        }
    }

    /**
     * 执行缓存清理
     * @param array $params 参数
     * @return array
     */
    protected function executeCacheClear($params)
    {
        try {
            $cacheService = new \app\service\CacheService();
            $type = $params['type'] ?? 'all';

            switch ($type) {
                case 'page':
                    $cacheService->clearPageCache();
                    $message = '页面缓存清理完成';
                    break;
                case 'data':
                    $cacheService->clearDataCache();
                    $message = '数据缓存清理完成';
                    break;
                case 'template':
                    $cacheService->clearTemplateCache();
                    $message = '模板缓存清理完成';
                    break;
                default:
                    $cacheService->clearAll();
                    $message = '所有缓存清理完成';
            }

            return [
                'success' => true,
                'output'  => $message,
            ];
        } catch (\Exception $e) {
            throw new Exception('缓存清理失败: ' . $e->getMessage());
        }
    }

    /**
     * 执行日志清理
     * @param array $params 参数
     * @return array
     */
    protected function executeLogClean($params)
    {
        try {
            $days = $params['days'] ?? 30;
            $count = CronJobLog::cleanOldLogs($days);

            return [
                'success' => true,
                'output'  => "清理了 {$count} 条 {$days} 天前的日志",
            ];
        } catch (\Exception $e) {
            throw new Exception('日志清理失败: ' . $e->getMessage());
        }
    }

    /**
     * 执行文章定时发布
     * @param array $params 参数
     * @return array
     */
    protected function executeArticlePublish($params)
    {
        try {
            // 这里可以实现定时发布文章的逻辑
            // 例如：查找scheduled_time <= now() 且 status = 'scheduled' 的文章，然后发布它们

            return [
                'success' => true,
                'output'  => '定时发布文章任务执行完成',
            ];
        } catch (\Exception $e) {
            throw new Exception('文章发布失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取任务日志列表
     * @param array $params 查询参数
     * @return array
     */
    public function getLogList($params = [])
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 15;

        // 过滤掉非搜索参数
        $searchParams = $params;
        unset($searchParams['page'], $searchParams['limit']);

        $query = CronJobLog::withSearch(array_keys($searchParams), $searchParams);

        $total = $query->count();
        $list = $query->page($page, $limit)
            ->order('start_time', 'desc')
            ->select();

        return [
            'total' => $total,
            'list'  => $list,
            'page'  => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取任务的日志列表
     * @param int $jobId 任务ID
     * @param int $limit 数量限制
     * @return array
     */
    public function getJobLogs($jobId, $limit = 50)
    {
        $logs = CronJobLog::where('job_id', $jobId)
            ->order('start_time', 'desc')
            ->limit($limit)
            ->select();

        return $logs->toArray();
    }

    /**
     * 清理日志
     * @param int $days 保留天数
     * @return int 删除数量
     */
    public function cleanLogs($days = 30)
    {
        return CronJobLog::cleanOldLogs($days);
    }
}
