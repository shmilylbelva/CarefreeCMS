<?php
declare(strict_types=1);

namespace app\controller\api;

use app\common\Response;
use app\service\QueryAnalyzer;
use app\middleware\QueryLogger;
use think\Request;

/**
 * 数据库查询监控API
 */
class QueryMonitor
{
    /**
     * 查询分析服务
     */
    protected QueryAnalyzer $analyzer;

    public function __construct()
    {
        $this->analyzer = new QueryAnalyzer();
    }

    /**
     * 获取查询统计摘要
     *
     * @route GET /api/query-monitor/summary
     * @param Request $request
     * @return \think\Response
     */
    public function summary(Request $request)
    {
        $date = $request->get('date', date('Ymd'));

        $summary = $this->analyzer->getSummary($date);

        return Response::success($summary);
    }

    /**
     * 获取慢查询列表
     *
     * @route GET /api/query-monitor/slow-queries
     * @param Request $request
     * @return \think\Response
     */
    public function slowQueries(Request $request)
    {
        $date = $request->get('date', date('Ymd'));
        $limit = (int)$request->get('limit', 50);

        $slowQueries = $this->analyzer->getSlowQueries($date, $limit);

        return Response::success([
            'total' => count($slowQueries),
            'data' => $slowQueries
        ]);
    }

    /**
     * 获取N+1查询问题列表
     *
     * @route GET /api/query-monitor/nplus1-issues
     * @param Request $request
     * @return \think\Response
     */
    public function nplus1Issues(Request $request)
    {
        $date = $request->get('date', date('Ymd'));
        $limit = (int)$request->get('limit', 50);

        $issues = $this->analyzer->getNPlusOneIssues($date, $limit);

        return Response::success([
            'total' => count($issues),
            'data' => $issues
        ]);
    }

    /**
     * 获取指定路由的查询详情
     *
     * @route GET /api/query-monitor/route
     * @param Request $request
     * @return \think\Response
     */
    public function routeQueries(Request $request)
    {
        $route = $request->get('route', '');
        $date = $request->get('date', date('Ymd'));

        if (empty($route)) {
            return Response::error('请提供路由参数');
        }

        $data = $this->analyzer->getRouteQueries($route, $date);

        if (!$data['found']) {
            return Response::error('未找到该路由的查询记录', 404);
        }

        return Response::success($data);
    }

    /**
     * 生成优化报告
     *
     * @route GET /api/query-monitor/report
     * @param Request $request
     * @return \think\Response
     */
    public function report(Request $request)
    {
        $date = $request->get('date', date('Ymd'));

        $report = $this->analyzer->generateReport($date);

        return Response::success($report);
    }

    /**
     * 清除查询日志
     *
     * @route POST /api/query-monitor/clear
     * @param Request $request
     * @return \think\Response
     */
    public function clear(Request $request)
    {
        $date = $request->post('date');

        QueryLogger::clear($date);

        return Response::success(null, '查询日志已清除');
    }

    /**
     * 获取查询监控配置
     *
     * @route GET /api/query-monitor/config
     * @return \think\Response
     */
    public function config()
    {
        $config = [
            'enabled' => config('database.query_log_enabled', false),
            'slow_query_threshold' => QueryLogger::SLOW_QUERY_THRESHOLD,
            'debug_mode' => config('app.debug', false),
            'cache_expire' => QueryLogger::CACHE_EXPIRE
        ];

        return Response::success($config);
    }

    /**
     * 更新查询监控配置
     *
     * @route POST /api/query-monitor/config
     * @param Request $request
     * @return \think\Response
     */
    public function updateConfig(Request $request)
    {
        $enabled = $request->post('enabled');

        if ($enabled !== null) {
            // 更新配置文件（需要有写权限）
            $envFile = base_path() . '.env';
            if (file_exists($envFile)) {
                $content = file_get_contents($envFile);

                // 查找并替换配置
                if (strpos($content, 'QUERY_LOG_ENABLED') !== false) {
                    $content = preg_replace(
                        '/QUERY_LOG_ENABLED\s*=\s*\w+/',
                        'QUERY_LOG_ENABLED = ' . ($enabled ? 'true' : 'false'),
                        $content
                    );
                } else {
                    // 添加配置
                    $content .= "\nQUERY_LOG_ENABLED = " . ($enabled ? 'true' : 'false');
                }

                file_put_contents($envFile, $content);
            }
        }

        return Response::success(null, '配置已更新');
    }

    /**
     * 实时查询监控（WebSocket或长轮询）
     *
     * @route GET /api/query-monitor/realtime
     * @param Request $request
     * @return \think\Response
     */
    public function realtime(Request $request)
    {
        // 获取最新的查询统计
        $stats = QueryLogger::getStats(date('Ymd'));

        // 获取最近的慢查询（最近10条）
        $slowQueries = QueryLogger::getSlowQueries(date('Ymd'));
        $recentSlowQueries = array_slice($slowQueries, -10);

        // 获取最近的N+1问题（最近5条）
        $nplus1Issues = QueryLogger::getNPlusOneIssues(date('Ymd'));
        $recentNplus1 = array_slice($nplus1Issues, -5);

        return Response::success([
            'timestamp' => time(),
            'stats' => [
                'total_queries' => $stats['total_queries'],
                'total_time' => round($stats['total_time'], 2),
                'slow_queries' => $stats['slow_queries_count']
            ],
            'recent_slow_queries' => $recentSlowQueries,
            'recent_nplus1_issues' => $recentNplus1
        ]);
    }

    /**
     * 导出查询报告
     *
     * @route GET /api/query-monitor/export
     * @param Request $request
     * @return \think\Response
     */
    public function export(Request $request)
    {
        $date = $request->get('date', date('Ymd'));
        $format = $request->get('format', 'json'); // json, csv, excel

        $report = $this->analyzer->generateReport($date);

        if ($format === 'json') {
            return Response::success($report);
        }

        // TODO: 实现CSV和Excel导出
        return Response::error('暂不支持该格式');
    }

    /**
     * 获取查询趋势（过去7天）
     *
     * @route GET /api/query-monitor/trend
     * @return \think\Response
     */
    public function trend()
    {
        $trend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Ymd', strtotime("-$i days"));
            $stats = QueryLogger::getStats($date);

            $trend[] = [
                'date' => date('Y-m-d', strtotime("-$i days")),
                'total_queries' => $stats['total_queries'],
                'total_time' => round($stats['total_time'], 2),
                'slow_queries' => $stats['slow_queries_count'],
                'avg_time' => $stats['total_queries'] > 0
                    ? round($stats['total_time'] / $stats['total_queries'], 2)
                    : 0
            ];
        }

        return Response::success($trend);
    }
}
