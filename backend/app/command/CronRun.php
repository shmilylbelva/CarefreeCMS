<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\model\CronJob;
use app\model\CronJobLog;
use app\service\CronJobService;

/**
 * 定时任务执行命令
 * 使用方法：php think cron:run
 * 配置定时任务（Linux Crontab）：* * * * * php /path/to/think cron:run >> /dev/null 2>&1
 */
class CronRun extends Command
{
    protected function configure()
    {
        $this->setName('cron:run')
            ->setDescription('Execute scheduled cron jobs');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('Starting cron job scheduler...');

        // 获取待执行的任务
        $jobs = CronJob::getPendingJobs();

        if ($jobs->isEmpty()) {
            $output->writeln('No pending jobs to execute.');
            return;
        }

        $output->writeln('Found ' . count($jobs) . ' job(s) to execute.');

        $cronJobService = new CronJobService();

        foreach ($jobs as $job) {
            $output->writeln("Executing job: {$job->title} ({$job->name})");

            // 创建日志
            $log = CronJobLog::createLog($job->id, $job->name);

            try {
                // 执行任务
                $result = $cronJobService->runNow($job->id);

                $output->writeln("  ✓ Success: {$job->title}");
                $output->writeln("  Output: " . ($result['output'] ?? ''));
            } catch (\Exception $e) {
                $output->writeln("  ✗ Failed: {$job->title}");
                $output->writeln("  Error: " . $e->getMessage());
            }
        }

        $output->writeln('Cron job scheduler finished.');
    }
}
