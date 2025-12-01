<?php

namespace app\middleware;

use app\model\AdminUser;
use app\common\Response;
use Closure;
use think\Request;

/**
 * 权限检查中间件
 *
 * 使用方法：
 * 1. 在路由中使用：Route::get('...')->middleware(['Auth', 'Permission:article.create'])
 * 2. 在控制器方法注解中使用（需要配置）
 * 3. 手动在代码中调用检查方法
 */
class Permission
{
    /**
     * 路由到权限的映射关系
     * 格式：'路由规则' => '所需权限'
     *
     * 支持多种匹配方式：
     * - 精确匹配：'api/article/create' => 'article.create'
     * - 通配符匹配：'api/article/*' => 'article.*'
     * - RESTful匹配：根据HTTP方法自动映射
     */
    protected $routePermissionMap = [
        // 文章管理
        'api/articles' => [
            'GET'    => 'article.view',
            'POST'   => 'article.create',
        ],
        'api/articles/:id' => [
            'GET'    => 'article.read',
            'PUT'    => 'article.edit',
            'DELETE' => 'article.delete',
        ],
        'api/articles/:id/publish' => 'article.publish',
        'api/articles/batch-delete' => 'article.batch',
        'api/articles/batch-publish' => 'article.batch',
        'api/articles/export' => 'article.export',

        // 文章版本
        'api/articles/:id/versions' => 'article.version',
        'api/articles/:id/versions/statistics' => 'article.version',
        'api/article-versions/:id/rollback' => 'article.version',

        // 分类管理
        'api/categories' => [
            'GET'    => 'category.view',
            'POST'   => 'category.create',
        ],
        'api/categories/:id' => [
            'GET'    => 'category.read',
            'PUT'    => 'category.edit',
            'DELETE' => 'category.delete',
        ],

        // 标签管理
        'api/tags' => [
            'GET'    => 'tag.view',
            'POST'   => 'tag.create',
        ],
        'api/tags/:id' => [
            'GET'    => 'tag.read',
            'PUT'    => 'tag.edit',
            'DELETE' => 'tag.delete',
        ],

        // 用户管理
        'api/users' => [
            'GET'    => 'admin_user.view',
            'POST'   => 'admin_user.create',
        ],
        'api/users/:id' => [
            'GET'    => 'admin_user.read',
            'PUT'    => 'admin_user.edit',
            'DELETE' => 'admin_user.delete',
        ],

        // 角色管理
        'api/roles' => [
            'GET'    => 'role.view',
            'POST'   => 'role.create',
        ],
        'api/roles/:id' => [
            'GET'    => 'role.read',
            'PUT'    => 'role.edit',
            'DELETE' => 'role.delete',
        ],

        // 媒体管理
        'api/media' => [
            'GET'    => 'media.view',
            'POST'   => 'media.upload',
        ],
        'api/media/:id' => [
            'DELETE' => 'media.delete',
        ],

        // 系统配置
        'api/system-config' => [
            'GET'    => 'system_config.view',
            'PUT'    => 'system_config.edit',
        ],

        // 数据库管理
        'api/database/backup' => 'database.backup',
        'api/database/restore' => 'database.restore',

        // 缓存管理
        'api/cache/clear' => 'cache.clear',
    ];

    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $permission 指定需要检查的权限（可选）
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $permission = null)
    {
        // 获取当前用户ID
        $userId = $request->user['id'] ?? 0;

        if (!$userId) {
            return Response::unauthorized('请先登录');
        }

        // 如果指定了权限，直接检查
        if ($permission) {
            if (!$this->checkPermission($userId, $permission)) {
                return Response::forbidden("您没有访问此功能的权限 [{$permission}]");
            }
            return $next($request);
        }

        // 根据路由自动检查权限
        $requiredPermission = $this->getRoutePermission($request);

        if ($requiredPermission) {
            if (!$this->checkPermission($userId, $requiredPermission)) {
                return Response::forbidden("您没有访问此功能的权限 [{$requiredPermission}]");
            }
        }

        return $next($request);
    }

    /**
     * 检查用户是否有指定权限
     *
     * @param int $userId
     * @param string $permission
     * @return bool
     */
    protected function checkPermission(int $userId, string $permission): bool
    {
        return AdminUser::hasPermission($userId, $permission);
    }

    /**
     * 根据路由获取所需权限
     *
     * @param Request $request
     * @return string|null
     */
    protected function getRoutePermission(Request $request): ?string
    {
        $route = $request->rule()->getRule();
        $method = $request->method();

        // 精确匹配
        if (isset($this->routePermissionMap[$route])) {
            $map = $this->routePermissionMap[$route];

            // 如果是字符串，直接返回
            if (is_string($map)) {
                return $map;
            }

            // 如果是数组，根据HTTP方法返回
            if (is_array($map) && isset($map[$method])) {
                return $map[$method];
            }
        }

        // 模糊匹配（支持通配符）
        foreach ($this->routePermissionMap as $pattern => $permissions) {
            if ($this->matchRoute($route, $pattern)) {
                if (is_string($permissions)) {
                    return $permissions;
                }
                if (is_array($permissions) && isset($permissions[$method])) {
                    return $permissions[$method];
                }
            }
        }

        return null;
    }

    /**
     * 匹配路由规则
     *
     * @param string $route 实际路由
     * @param string $pattern 路由规则
     * @return bool
     */
    protected function matchRoute(string $route, string $pattern): bool
    {
        // 精确匹配
        if ($route === $pattern) {
            return true;
        }

        // 将路由规则转换为正则表达式
        $regex = str_replace(
            [':id', ':article_id', '*'],
            ['[0-9]+', '[0-9]+', '.*'],
            $pattern
        );
        $regex = '#^' . $regex . '$#';

        return (bool) preg_match($regex, $route);
    }

    /**
     * 批量检查权限（至少有一个权限即可）
     *
     * @param int $userId
     * @param array $permissions
     * @return bool
     */
    public static function hasAnyPermission(int $userId, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (AdminUser::hasPermission($userId, $permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 批量检查权限（必须拥有所有权限）
     *
     * @param int $userId
     * @param array $permissions
     * @return bool
     */
    public static function hasAllPermissions(int $userId, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!AdminUser::hasPermission($userId, $permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 要求指定权限（用于在控制器中手动检查）
     *
     * @param Request $request
     * @param string $permission
     * @return void
     * @throws \think\exception\HttpResponseException
     */
    public static function require(Request $request, string $permission): void
    {
        $userId = $request->user['id'] ?? 0;

        if (!$userId) {
            throw new \think\exception\HttpResponseException(
                Response::unauthorized('请先登录')
            );
        }

        if (!AdminUser::hasPermission($userId, $permission)) {
            throw new \think\exception\HttpResponseException(
                Response::forbidden("您没有访问此功能的权限 [{$permission}]")
            );
        }
    }

    /**
     * 要求任一权限
     *
     * @param Request $request
     * @param array $permissions
     * @return void
     * @throws \think\exception\HttpResponseException
     */
    public static function requireAny(Request $request, array $permissions): void
    {
        $userId = $request->user['id'] ?? 0;

        if (!$userId) {
            throw new \think\exception\HttpResponseException(
                Response::unauthorized('请先登录')
            );
        }

        if (!self::hasAnyPermission($userId, $permissions)) {
            $permList = implode(', ', $permissions);
            throw new \think\exception\HttpResponseException(
                Response::forbidden("您没有访问此功能的权限，需要以下权限之一: [{$permList}]")
            );
        }
    }

    /**
     * 要求所有权限
     *
     * @param Request $request
     * @param array $permissions
     * @return void
     * @throws \think\exception\HttpResponseException
     */
    public static function requireAll(Request $request, array $permissions): void
    {
        $userId = $request->user['id'] ?? 0;

        if (!$userId) {
            throw new \think\exception\HttpResponseException(
                Response::unauthorized('请先登录')
            );
        }

        if (!self::hasAllPermissions($userId, $permissions)) {
            $permList = implode(', ', $permissions);
            throw new \think\exception\HttpResponseException(
                Response::forbidden("您没有访问此功能的权限，需要以下所有权限: [{$permList}]")
            );
        }
    }
}
