<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\service\CronJobService;
use think\Request;

/**
 * 定时任务管理控制器
 */
class CronJobController extends BaseController
{
    /**
     * 定时任务服务
     * @var CronJobService
     */
    protected $cronJobService;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->cronJobService = new CronJobService();
    }

    /**
     * 任务列表
     */
    public function index(Request $request)
    {
        $params = $request->get();

        try {
            $result = $this->cronJobService->getList($params);
            return Response::paginate(
                $result['list']->toArray(),
                $result['total'],
                $result['page'],
                $result['limit']
            );
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 任务详情
     */
    public function read($id)
    {
        try {
            $job = $this->cronJobService->getDetail($id);
            return Response::success($job->toArray());
        } catch (\Exception $e) {
            return Response::notFound($e->getMessage());
        }
    }

    /**
     * 创建任务
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['name'])) {
            return Response::error('任务名称不能为空');
        }
        if (empty($data['title'])) {
            return Response::error('任务标题不能为空');
        }
        if (empty($data['cron_expression'])) {
            return Response::error('Cron表达式不能为空');
        }
        if (empty($data['command'])) {
            return Response::error('执行命令不能为空');
        }

        try {
            $job = $this->cronJobService->create($data);
            return Response::success($job->toArray(), '任务创建成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 更新任务
     */
    public function update(Request $request, $id)
    {
        $data = $request->post();

        try {
            $this->cronJobService->update($id, $data);
            return Response::success(null, '任务更新成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 删除任务
     */
    public function delete($id)
    {
        try {
            $this->cronJobService->delete($id);
            return Response::success(null, '任务删除成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 批量删除任务
     */
    public function batchDelete(Request $request)
    {
        $ids = $request->post('ids', []);

        if (empty($ids)) {
            return Response::error('请选择要删除的任务');
        }

        $count = $this->cronJobService->batchDelete($ids);

        return Response::success(['deleted_count' => $count], "成功删除 {$count} 个任务");
    }

    /**
     * 更新任务状态
     */
    public function updateStatus(Request $request, $id)
    {
        $isEnabled = $request->post('is_enabled');

        if ($isEnabled === null) {
            return Response::error('状态参数缺失');
        }

        try {
            $this->cronJobService->updateStatus($id, $isEnabled);
            $message = $isEnabled ? '任务已启用' : '任务已禁用';
            return Response::success(null, $message);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 手动执行任务
     */
    public function run(Request $request, $id)
    {
        try {
            $result = $this->cronJobService->runNow($id);
            return Response::success($result, '任务执行成功');
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 获取任务日志
     */
    public function logs(Request $request)
    {
        $params = $request->get();

        try {
            $result = $this->cronJobService->getLogList($params);
            return Response::paginate(
                $result['list']->toArray(),
                $result['total'],
                $result['page'],
                $result['limit']
            );
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 获取指定任务的日志
     */
    public function jobLogs(Request $request, $id)
    {
        $limit = $request->get('limit', 50);

        try {
            $logs = $this->cronJobService->getJobLogs($id, $limit);
            return Response::success($logs);
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 清理日志
     */
    public function cleanLogs(Request $request)
    {
        $days = $request->post('days', 30);

        try {
            $count = $this->cronJobService->cleanLogs($days);
            return Response::success(['count' => $count], "成功清理 {$count} 条日志");
        } catch (\Exception $e) {
            return Response::error($e->getMessage());
        }
    }

    /**
     * 验证Cron表达式
     */
    public function validateCron(Request $request)
    {
        $expression = $request->post('expression', '');

        if (empty($expression)) {
            return Response::error('表达式不能为空');
        }

        $isValid = \app\model\CronJob::validateCronExpression($expression);

        if ($isValid) {
            $nextRunTime = \app\model\CronJob::calculateNextRunTime($expression);
            return Response::success([
                'valid'        => true,
                'next_run_time' => $nextRunTime,
                'message'      => '表达式格式正确',
            ]);
        } else {
            return Response::error('表达式格式不正确');
        }
    }

    /**
     * 获取预设任务列表
     */
    public function presets()
    {
        $presets = [
            [
                'name'            => 'database_backup',
                'title'           => '数据库自动备份',
                'cron_expression' => '0 2 * * *',
                'command'         => 'database:backup',
                'params'          => [],
                'description'     => '每天凌晨2点自动备份数据库',
            ],
            [
                'name'            => 'cache_clear',
                'title'           => '定时清理缓存',
                'cron_expression' => '0 3 * * 0',
                'command'         => 'cache:clear',
                'params'          => ['type' => 'all'],
                'description'     => '每周日凌晨3点清理所有缓存',
            ],
            [
                'name'            => 'log_clean',
                'title'           => '清理旧日志',
                'cron_expression' => '0 4 * * 0',
                'command'         => 'log:clean',
                'params'          => ['days' => 30],
                'description'     => '每周日凌晨4点清理30天前的日志',
            ],
            [
                'name'            => 'article_publish',
                'title'           => '文章定时发布',
                'cron_expression' => '*/10 * * * *',
                'command'         => 'article:publish',
                'params'          => [],
                'description'     => '每10分钟检查并发布定时文章',
            ],
        ];

        return Response::success($presets);
    }

    /**
     * 获取常用Cron表达式
     */
    public function cronExpressions()
    {
        $expressions = [
            [
                'label'      => '每分钟',
                'expression' => '* * * * *',
                'description' => '每分钟执行一次',
            ],
            [
                'label'      => '每5分钟',
                'expression' => '*/5 * * * *',
                'description' => '每5分钟执行一次',
            ],
            [
                'label'      => '每10分钟',
                'expression' => '*/10 * * * *',
                'description' => '每10分钟执行一次',
            ],
            [
                'label'      => '每小时',
                'expression' => '0 * * * *',
                'description' => '每小时执行一次',
            ],
            [
                'label'      => '每天凌晨',
                'expression' => '0 0 * * *',
                'description' => '每天凌晨0点执行',
            ],
            [
                'label'      => '每天凌晨2点',
                'expression' => '0 2 * * *',
                'description' => '每天凌晨2点执行',
            ],
            [
                'label'      => '每周一',
                'expression' => '0 0 * * 1',
                'description' => '每周一凌晨0点执行',
            ],
            [
                'label'      => '每周日',
                'expression' => '0 0 * * 0',
                'description' => '每周日凌晨0点执行',
            ],
            [
                'label'      => '每月1号',
                'expression' => '0 0 1 * *',
                'description' => '每月1号凌晨0点执行',
            ],
        ];

        return Response::success($expressions);
    }
}
