<?php
declare (strict_types = 1);

namespace app\service;

use think\facade\Db;
use think\Exception;

/**
 * 站点表管理服务
 * 共享表模式：所有站点共用同一套表，通过 site_id 字段区分
 */
class SiteTableService
{
    /**
     * 包含 site_id 字段的共享表列表
     * @var array
     */
    protected static $sharedTables = [
        // 核心内容表
        'articles',              // 文章
        'article_versions',      // 文章版本
        'article_flags',         // 文章属性
        'categories',            // 分类
        'tags',                  // 标签
        'pages',                 // 单页
        'comments',              // 评论
        'comment_likes',         // 评论点赞
        'comment_reports',       // 评论举报
        'media',                 // 媒体文件
        'links',                 // 友情链接
        'sliders',               // 幻灯片
        'ads',                   // 广告
        'topics',                // 专题

        // 统一关联表
        'relations',             // 通用关联表（替代article_categories, article_tags, topic_articles）
        'groups',                // 通用分组表（替代link_groups, slider_groups, ad_positions等）

        // 用户相关表（如果站点用户独立）
        'front_users',           // 前台用户
        'front_user_oauth',      // 用户OAuth
        'user_actions',          // 用户行为表（替代user_likes, user_favorites, user_follows）
        'user_read_history',     // 阅读历史
        'user_point_logs',       // 积分日志

        // SEO相关表
        'seo_redirects',         // URL重定向
        'seo_404_logs',          // 404日志
        'seo_keyword_rankings',  // 关键词排名
    ];

    /**
     * 验证表名/列名是否安全（防止SQL注入）
     * @param string $name
     * @return bool
     */
    private static function isValidIdentifier(string $name): bool
    {
        // 只允许字母、数字、下划线
        return preg_match('/^[a-zA-Z0-9_]+$/', $name) === 1;
    }

    /**
     * 检查表是否存在
     * @param string $tableName 表名
     * @return bool
     */
    public static function tableExists(string $tableName): bool
    {
        // 验证表名
        if (!self::isValidIdentifier($tableName)) {
            return false;
        }

        $result = Db::query("SHOW TABLES LIKE ?", [$tableName]);
        return !empty($result);
    }

    /**
     * 检查列是否存在
     * @param string $tableName 表名
     * @param string $columnName 列名
     * @return bool
     */
    public static function columnExists(string $tableName, string $columnName): bool
    {
        // 验证表名和列名
        if (!self::isValidIdentifier($tableName) || !self::isValidIdentifier($columnName)) {
            return false;
        }

        try {
            $result = Db::query("SHOW COLUMNS FROM `{$tableName}` LIKE ?", [$columnName]);
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取表的所有列名
     * @param string $tableName 表名
     * @return array
     */
    public static function getTableColumns(string $tableName): array
    {
        // 验证表名
        if (!self::isValidIdentifier($tableName)) {
            return [];
        }

        $columns = Db::query("SHOW COLUMNS FROM `{$tableName}`");
        return array_column($columns ?: [], 'Field');
    }

    /**
     * 获取共享表列表
     * @return array
     */
    public static function getSharedTableList(): array
    {
        return self::$sharedTables;
    }

    /**
     * 检查所有共享表是否都有 site_id 字段
     * @return array [complete, missing]
     */
    public static function checkSiteIdColumns(): array
    {
        $missing = [];
        $existing = [];

        foreach (self::$sharedTables as $tableName) {
            if (!self::tableExists($tableName)) {
                continue; // 表不存在，跳过
            }

            if (self::columnExists($tableName, 'site_id')) {
                $existing[] = $tableName;
            } else {
                $missing[] = $tableName;
            }
        }

        return [
            'complete' => empty($missing),
            'total' => count(self::$sharedTables),
            'existing_count' => count($existing),
            'missing_count' => count($missing),
            'existing' => $existing,
            'missing' => $missing
        ];
    }

    /**
     * 清空指定站点的数据
     * @param int $siteId 站点ID
     * @param array $tables 要清空的表列表（为空则清空所有共享表）
     * @return array 清空结果
     */
    public static function clearSiteData(int $siteId, array $tables = []): array
    {
        if (empty($tables)) {
            $tables = self::$sharedTables;
        }

        $result = [
            'success' => [],
            'failed' => [],
            'counts' => []
        ];

        Db::startTrans();
        try {
            foreach ($tables as $tableName) {
                if (!self::tableExists($tableName)) {
                    continue;
                }

                if (!self::columnExists($tableName, 'site_id')) {
                    continue;
                }

                try {
                    $count = Db::table($tableName)
                        ->where('site_id', $siteId)
                        ->delete();

                    $result['success'][] = $tableName;
                    $result['counts'][$tableName] = $count;
                } catch (\Exception $e) {
                    $result['failed'][] = [
                        'table' => $tableName,
                        'error' => $e->getMessage()
                    ];
                }
            }

            Db::commit();
            return $result;
        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception('清空站点数据失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取指定站点的数据统计
     * @param int $siteId 站点ID
     * @return array 各表的记录数
     */
    public static function getSiteDataStats(int $siteId): array
    {
        $stats = [];

        foreach (self::$sharedTables as $tableName) {
            if (!self::tableExists($tableName)) {
                continue;
            }

            if (!self::columnExists($tableName, 'site_id')) {
                continue;
            }

            try {
                $count = Db::table($tableName)
                    ->where('site_id', $siteId)
                    ->count();

                $stats[$tableName] = $count;
            } catch (\Exception $e) {
                $stats[$tableName] = -1; // 表示查询失败
            }
        }

        return $stats;
    }

    /**
     * 创建站点表（共享表模式下不需要创建，直接返回成功）
     * @param string $siteCode 站点代码
     * @param string $dbPrefix 数据库前缀
     * @return array 创建结果
     */
    public static function createSiteTables(string $siteCode, string $dbPrefix): array
    {
        // 当前系统使用共享表模式，所有站点共用一套表，通过 site_id 字段区分
        // 不需要为每个站点创建独立的表，直接返回成功
        return [
            'success' => self::$sharedTables,
            'failed' => [],
            'skipped' => [],
            'message' => '使用共享表模式，无需创建独立表'
        ];
    }

    /**
     * 检查站点表状态（共享表模式）
     * @param string $dbPrefix 数据库前缀
     * @return array 检查结果
     */
    public static function checkSiteTables(string $dbPrefix): array
    {
        // 共享表模式下，检查共享表的 site_id 字段
        $result = self::checkSiteIdColumns();
        $result['mode'] = 'shared';
        $result['message'] = '共享表模式：所有站点共用一套表';
        return $result;
    }

    /**
     * 迁移数据到站点表（共享表模式下不需要）
     * @param int $siteId 站点ID
     * @param string $dbPrefix 数据库前缀
     * @param array $tables 要迁移的表
     * @return array 迁移结果
     */
    public static function migrateData(int $siteId, string $dbPrefix, array $tables = []): array
    {
        // 共享表模式下不需要迁移数据
        return [
            'success' => [],
            'failed' => [],
            'counts' => [],
            'message' => '共享表模式，无需迁移数据'
        ];
    }

    /**
     * 清空站点表数据（共享表模式下清空指定 site_id 的数据）
     * @param string $dbPrefix 数据库前缀
     * @return int 清空的表数量
     */
    public static function truncateSiteTables(string $dbPrefix): int
    {
        // 共享表模式下无法通过表前缀清空
        // 应该通过 site_id 来删除数据
        return 0;
    }
}
