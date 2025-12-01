<?php
declare(strict_types=1);

namespace app\service;

use app\middleware\QueryLogger;

/**
 * 数据库查询分析服务
 * 提供查询性能分析和优化建议
 */
class QueryAnalyzer
{
    /**
     * 获取查询统计摘要
     */
    public function getSummary(?string $date = null): array
    {
        $stats = QueryLogger::getStats($date);
        $slowQueries = QueryLogger::getSlowQueries($date);
        $nplus1Issues = QueryLogger::getNPlusOneIssues($date);

        // 计算平均查询时间
        $avgQueryTime = $stats['total_queries'] > 0
            ? $stats['total_time'] / $stats['total_queries']
            : 0;

        // 找出最慢的路由
        $slowestRoutes = $this->getSlowestRoutes($stats['routes']);

        // 找出查询最多的路由
        $mostQueriedRoutes = $this->getMostQueriedRoutes($stats['routes']);

        return [
            'date' => $date ?: date('Y-m-d'),
            'total_queries' => $stats['total_queries'],
            'total_time' => round($stats['total_time'], 2),
            'avg_query_time' => round($avgQueryTime, 2),
            'slow_queries_count' => $stats['slow_queries_count'],
            'nplus1_issues_count' => count($nplus1Issues),
            'routes_count' => count($stats['routes']),
            'slowest_routes' => $slowestRoutes,
            'most_queried_routes' => $mostQueriedRoutes,
            'health_score' => $this->calculateHealthScore($stats, $slowQueries, $nplus1Issues)
        ];
    }

    /**
     * 获取最慢的路由（Top 10）
     */
    protected function getSlowestRoutes(array $routes): array
    {
        // 按平均查询时间排序
        $sortedRoutes = [];
        foreach ($routes as $route => $data) {
            $avgTime = $data['queries'] > 0 ? $data['time'] / $data['queries'] : 0;
            $sortedRoutes[] = [
                'route' => $route,
                'avg_time' => round($avgTime, 2),
                'total_time' => round($data['time'], 2),
                'queries' => $data['queries'],
                'requests' => $data['count'],
                'slow_queries' => $data['slow']
            ];
        }

        usort($sortedRoutes, function ($a, $b) {
            return $b['avg_time'] <=> $a['avg_time'];
        });

        return array_slice($sortedRoutes, 0, 10);
    }

    /**
     * 获取查询最多的路由（Top 10）
     */
    protected function getMostQueriedRoutes(array $routes): array
    {
        $sortedRoutes = [];
        foreach ($routes as $route => $data) {
            $sortedRoutes[] = [
                'route' => $route,
                'queries' => $data['queries'],
                'avg_queries_per_request' => round($data['queries'] / max($data['count'], 1), 2),
                'requests' => $data['count'],
                'total_time' => round($data['time'], 2)
            ];
        }

        usort($sortedRoutes, function ($a, $b) {
            return $b['queries'] <=> $a['queries'];
        });

        return array_slice($sortedRoutes, 0, 10);
    }

    /**
     * 计算健康评分（0-100）
     */
    protected function calculateHealthScore(array $stats, array $slowQueries, array $nplus1Issues): int
    {
        $score = 100;

        // 慢查询惩罚
        $slowQueryRatio = $stats['total_queries'] > 0
            ? count($slowQueries) / $stats['total_queries']
            : 0;
        $score -= min($slowQueryRatio * 100, 30);

        // N+1查询惩罚
        $nplus1Penalty = min(count($nplus1Issues) * 5, 30);
        $score -= $nplus1Penalty;

        // 平均查询时间惩罚
        $avgQueryTime = $stats['total_queries'] > 0
            ? $stats['total_time'] / $stats['total_queries']
            : 0;
        if ($avgQueryTime > 50) {
            $score -= min(($avgQueryTime - 50) / 10, 20);
        }

        // 查询数量惩罚（单个请求查询过多）
        foreach ($stats['routes'] as $route => $data) {
            $avgQueries = $data['queries'] / max($data['count'], 1);
            if ($avgQueries > 20) {
                $score -= min(($avgQueries - 20) / 5, 10);
                break;
            }
        }

        return max(0, (int)$score);
    }

    /**
     * 获取慢查询详情
     */
    public function getSlowQueries(?string $date = null, int $limit = 50): array
    {
        $slowQueries = QueryLogger::getSlowQueries($date);

        // 按时间倒序排序
        usort($slowQueries, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // 限制数量
        $slowQueries = array_slice($slowQueries, 0, $limit);

        // 格式化
        return array_map(function ($query) {
            return [
                'sql' => $query['sql'],
                'time' => round($query['time'], 2),
                'timestamp' => date('Y-m-d H:i:s', (int)$query['timestamp']),
                'optimization_hints' => $this->generateOptimizationHints($query['sql'])
            ];
        }, $slowQueries);
    }

    /**
     * 获取N+1查询问题详情
     */
    public function getNPlusOneIssues(?string $date = null, int $limit = 50): array
    {
        $issues = QueryLogger::getNPlusOneIssues($date);

        // 按数量倒序排序
        usort($issues, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        // 限制数量
        $issues = array_slice($issues, 0, $limit);

        // 格式化
        return array_map(function ($issue) {
            return [
                'route' => $issue['route'],
                'pattern' => $issue['pattern'],
                'repeat_count' => $issue['count'],
                'total_queries' => $issue['total_queries'],
                'timestamp' => date('Y-m-d H:i:s', $issue['timestamp']),
                'optimization_hints' => $this->generateNPlusOneHints($issue['pattern'])
            ];
        }, $issues);
    }

    /**
     * 生成优化建议（慢查询）
     */
    protected function generateOptimizationHints(string $sql): array
    {
        $hints = [];

        // 检查是否缺少WHERE条件
        if (!preg_match('/WHERE/i', $sql) && preg_match('/SELECT.*FROM/i', $sql)) {
            $hints[] = '考虑添加WHERE条件限制查询范围';
        }

        // 检查是否使用SELECT *
        if (preg_match('/SELECT\s+\*/i', $sql)) {
            $hints[] = '避免使用SELECT *，只查询需要的字段';
        }

        // 检查是否缺少索引
        if (preg_match('/WHERE.*=.*AND/i', $sql)) {
            $hints[] = '检查WHERE条件字段是否有索引';
        }

        // 检查是否使用了LIKE %xxx%
        if (preg_match('/LIKE\s+[\'"]%/i', $sql)) {
            $hints[] = 'LIKE前置通配符无法使用索引，考虑使用全文搜索';
        }

        // 检查是否有JOIN
        if (preg_match('/JOIN/i', $sql)) {
            $hints[] = '检查JOIN条件字段是否有索引';
        }

        // 检查是否有子查询
        if (preg_match('/\(\s*SELECT/i', $sql)) {
            $hints[] = '考虑将子查询改写为JOIN';
        }

        return $hints ?: ['查询时间较长，建议使用EXPLAIN分析执行计划'];
    }

    /**
     * 生成N+1查询优化建议
     */
    protected function generateNPlusOneHints(string $pattern): array
    {
        $hints = [];

        // 检查是否是关联查询
        if (preg_match('/WHERE\s+\w+_id\s*=/i', $pattern)) {
            $hints[] = '使用with()预加载关联数据，避免循环查询';
            $hints[] = '示例: Model::with([\'relation\'])->get()';
        }

        // 检查是否是IN查询
        if (preg_match('/WHERE\s+\w+\s+IN\s*\(/i', $pattern)) {
            $hints[] = '批量查询已优化，但考虑是否需要缓存结果';
        }

        $hints[] = '检查是否在循环中执行查询';
        $hints[] = '考虑使用缓存存储频繁访问的数据';

        return $hints;
    }

    /**
     * 获取路由查询详情
     */
    public function getRouteQueries(string $route, ?string $date = null): array
    {
        $stats = QueryLogger::getStats($date);

        if (!isset($stats['routes'][$route])) {
            return [
                'route' => $route,
                'found' => false
            ];
        }

        $data = $stats['routes'][$route];

        return [
            'route' => $route,
            'found' => true,
            'requests' => $data['count'],
            'total_queries' => $data['queries'],
            'avg_queries_per_request' => round($data['queries'] / max($data['count'], 1), 2),
            'total_time' => round($data['time'], 2),
            'avg_time' => round($data['time'] / max($data['queries'], 1), 2),
            'slow_queries' => $data['slow'],
            'performance_grade' => $this->calculateRouteGrade($data)
        ];
    }

    /**
     * 计算路由性能等级
     */
    protected function calculateRouteGrade(array $data): string
    {
        $avgQueries = $data['queries'] / max($data['count'], 1);
        $avgTime = $data['time'] / max($data['queries'], 1);
        $slowRatio = $data['slow'] / max($data['queries'], 1);

        // 综合评分
        $score = 100;
        $score -= min($avgQueries * 2, 40); // 查询数量惩罚
        $score -= min($avgTime / 10, 30);    // 平均时间惩罚
        $score -= $slowRatio * 30;           // 慢查询比例惩罚

        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    /**
     * 生成优化报告
     */
    public function generateReport(?string $date = null): array
    {
        $summary = $this->getSummary($date);
        $slowQueries = $this->getSlowQueries($date, 10);
        $nplus1Issues = $this->getNPlusOneIssues($date, 10);

        // 生成优化建议
        $recommendations = [];

        if ($summary['slow_queries_count'] > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'issue' => '检测到慢查询',
                'count' => $summary['slow_queries_count'],
                'action' => '查看慢查询列表，优化SQL或添加索引'
            ];
        }

        if ($summary['nplus1_issues_count'] > 0) {
            $recommendations[] = [
                'priority' => 'high',
                'issue' => '检测到N+1查询问题',
                'count' => $summary['nplus1_issues_count'],
                'action' => '使用with()预加载关联数据'
            ];
        }

        if ($summary['avg_query_time'] > 50) {
            $recommendations[] = [
                'priority' => 'medium',
                'issue' => '平均查询时间较长',
                'value' => $summary['avg_query_time'] . 'ms',
                'action' => '检查查询效率和索引使用'
            ];
        }

        foreach ($summary['most_queried_routes'] as $route) {
            if ($route['avg_queries_per_request'] > 15) {
                $recommendations[] = [
                    'priority' => 'medium',
                    'issue' => '路由查询次数过多',
                    'route' => $route['route'],
                    'queries' => $route['avg_queries_per_request'],
                    'action' => '优化查询逻辑或使用缓存'
                ];
            }
        }

        return [
            'summary' => $summary,
            'slow_queries' => $slowQueries,
            'nplus1_issues' => $nplus1Issues,
            'recommendations' => $recommendations,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
}
