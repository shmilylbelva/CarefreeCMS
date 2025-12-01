<?php

namespace app\middleware;

use Closure;
use think\Request;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Config;

/**
 * 数据库查询日志监控中间件
 * 用于监控和记录数据库查询性能
 */
class QueryLogger
{
    /**
     * 慢查询阈值（毫秒）
     */
    const SLOW_QUERY_THRESHOLD = 100;

    /**
     * 缓存键前缀
     */
    const CACHE_PREFIX = 'query_log:';

    /**
     * 缓存过期时间（秒）
     */
    const CACHE_EXPIRE = 3600;

    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next)
    {
        // 只在开发环境或配置启用时记录
        if (!$this->shouldLog()) {
            return $next($request);
        }

        // 开启SQL监听
        $this->startLogging();

        // 执行请求
        $response = $next($request);

        // 停止SQL监听
        $this->stopLogging();

        // 分析并记录查询
        $this->analyzeQueries($request);

        return $response;
    }

    /**
     * 是否应该记录查询
     */
    protected function shouldLog(): bool
    {
        // 开发环境或显式启用
        return Config::get('app.debug', false) ||
               Config::get('database.query_log_enabled', false);
    }

    /**
     * 开始记录查询
     */
    protected function startLogging()
    {
        // 清除之前的日志
        Db::clearQueryTimes();

        // 启动SQL监听
        Db::listen(function ($sql, $time, $master) {
            // 记录每条SQL及其执行时间
            $logEntry = [
                'sql' => $sql,
                'time' => $time,
                'master' => $master,
                'timestamp' => microtime(true)
            ];

            // 暂存到请求上下文
            $this->addQueryLog($logEntry);

            // 慢查询警告
            if ($time > self::SLOW_QUERY_THRESHOLD) {
                $this->logSlowQuery($logEntry);
            }
        });
    }

    /**
     * 停止记录查询
     */
    protected function stopLogging()
    {
        // ThinkPHP会自动清理监听
    }

    /**
     * 添加查询日志到上下文
     */
    protected function addQueryLog(array $logEntry)
    {
        static $queries = [];
        $queries[] = $logEntry;

        // 存储到请求上下文
        app()->bind('query_logs', $queries);
    }

    /**
     * 获取当前请求的查询日志
     */
    protected function getQueryLogs(): array
    {
        return app()->get('query_logs') ?: [];
    }

    /**
     * 分析查询并记录统计
     */
    protected function analyzeQueries(Request $request)
    {
        $queries = $this->getQueryLogs();

        if (empty($queries)) {
            return;
        }

        $analysis = [
            'total_queries' => count($queries),
            'total_time' => array_sum(array_column($queries, 'time')),
            'slow_queries' => array_filter($queries, function ($q) {
                return $q['time'] > self::SLOW_QUERY_THRESHOLD;
            }),
            'route' => $request->rule()->getName() ?: $request->url(),
            'method' => $request->method(),
            'timestamp' => time()
        ];

        // 记录到缓存（用于统计分析）
        $this->saveQueryAnalysis($analysis);

        // 检查N+1查询
        $this->detectNPlusOne($queries, $request);
    }

    /**
     * 记录慢查询
     */
    protected function logSlowQuery(array $logEntry)
    {
        $message = sprintf(
            "[Slow Query] Time: %.2fms, SQL: %s",
            $logEntry['time'],
            $logEntry['sql']
        );

        // 记录到日志文件
        trace($message, 'warning');

        // 保存到缓存供后续分析
        $cacheKey = self::CACHE_PREFIX . 'slow:' . date('Ymd');
        $slowQueries = Cache::get($cacheKey, []);
        $slowQueries[] = [
            'sql' => $logEntry['sql'],
            'time' => $logEntry['time'],
            'timestamp' => $logEntry['timestamp']
        ];

        // 只保留最近100条慢查询
        if (count($slowQueries) > 100) {
            $slowQueries = array_slice($slowQueries, -100);
        }

        Cache::set($cacheKey, $slowQueries, self::CACHE_EXPIRE * 24);
    }

    /**
     * 检测N+1查询问题
     */
    protected function detectNPlusOne(array $queries, Request $request)
    {
        // 简单检测：如果查询数量超过10，且有相似的查询模式
        if (count($queries) < 10) {
            return;
        }

        $sqlPatterns = [];
        foreach ($queries as $query) {
            // 提取SQL模式（去掉具体值）
            $pattern = preg_replace('/\d+/', '?', $query['sql']);
            $pattern = preg_replace("/'[^']*'/", '?', $pattern);

            if (!isset($sqlPatterns[$pattern])) {
                $sqlPatterns[$pattern] = 0;
            }
            $sqlPatterns[$pattern]++;
        }

        // 如果同一模式的查询超过5次，可能是N+1问题
        foreach ($sqlPatterns as $pattern => $count) {
            if ($count >= 5) {
                $this->logNPlusOne([
                    'pattern' => $pattern,
                    'count' => $count,
                    'route' => $request->url(),
                    'total_queries' => count($queries)
                ]);
            }
        }
    }

    /**
     * 记录N+1查询问题
     */
    protected function logNPlusOne(array $data)
    {
        $message = sprintf(
            "[N+1 Query Detected] Pattern repeated %d times on route: %s (Total queries: %d)",
            $data['count'],
            $data['route'],
            $data['total_queries']
        );

        trace($message, 'warning');

        // 保存到缓存
        $cacheKey = self::CACHE_PREFIX . 'nplus1:' . date('Ymd');
        $nplus1Issues = Cache::get($cacheKey, []);
        $nplus1Issues[] = [
            'pattern' => $data['pattern'],
            'count' => $data['count'],
            'route' => $data['route'],
            'total_queries' => $data['total_queries'],
            'timestamp' => time()
        ];

        // 只保留最近50条
        if (count($nplus1Issues) > 50) {
            $nplus1Issues = array_slice($nplus1Issues, -50);
        }

        Cache::set($cacheKey, $nplus1Issues, self::CACHE_EXPIRE * 24);
    }

    /**
     * 保存查询分析到缓存
     */
    protected function saveQueryAnalysis(array $analysis)
    {
        $cacheKey = self::CACHE_PREFIX . 'stats:' . date('Ymd');
        $stats = Cache::get($cacheKey, [
            'routes' => [],
            'total_queries' => 0,
            'total_time' => 0,
            'slow_queries_count' => 0
        ]);

        // 按路由统计
        $route = $analysis['route'];
        if (!isset($stats['routes'][$route])) {
            $stats['routes'][$route] = [
                'count' => 0,
                'queries' => 0,
                'time' => 0,
                'slow' => 0
            ];
        }

        $stats['routes'][$route]['count']++;
        $stats['routes'][$route]['queries'] += $analysis['total_queries'];
        $stats['routes'][$route]['time'] += $analysis['total_time'];
        $stats['routes'][$route]['slow'] += count($analysis['slow_queries']);

        $stats['total_queries'] += $analysis['total_queries'];
        $stats['total_time'] += $analysis['total_time'];
        $stats['slow_queries_count'] += count($analysis['slow_queries']);

        Cache::set($cacheKey, $stats, self::CACHE_EXPIRE * 24);
    }

    /**
     * 获取查询统计
     */
    public static function getStats(?string $date = null): array
    {
        $date = $date ?: date('Ymd');
        $cacheKey = self::CACHE_PREFIX . 'stats:' . $date;
        return Cache::get($cacheKey, [
            'routes' => [],
            'total_queries' => 0,
            'total_time' => 0,
            'slow_queries_count' => 0
        ]);
    }

    /**
     * 获取慢查询列表
     */
    public static function getSlowQueries(?string $date = null): array
    {
        $date = $date ?: date('Ymd');
        $cacheKey = self::CACHE_PREFIX . 'slow:' . $date;
        return Cache::get($cacheKey, []);
    }

    /**
     * 获取N+1查询问题列表
     */
    public static function getNPlusOneIssues(?string $date = null): array
    {
        $date = $date ?: date('Ymd');
        $cacheKey = self::CACHE_PREFIX . 'nplus1:' . $date;
        return Cache::get($cacheKey, []);
    }

    /**
     * 清除查询日志
     */
    public static function clear(?string $date = null)
    {
        $date = $date ?: date('Ymd');
        Cache::delete(self::CACHE_PREFIX . 'stats:' . $date);
        Cache::delete(self::CACHE_PREFIX . 'slow:' . $date);
        Cache::delete(self::CACHE_PREFIX . 'nplus1:' . $date);
    }
}
