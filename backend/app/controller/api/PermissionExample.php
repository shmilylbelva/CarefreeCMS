<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\AdminUser;
use app\middleware\Permission;
use think\Request;

/**
 * 权限检查使用示例
 *
 * 本文件展示了在控制器中如何使用权限检查的各种方法
 * 实际使用时可以参考这些示例，将权限检查添加到相应的控制器方法中
 */
class PermissionExample extends BaseController
{
    // ========================================
    // 方法1: 使用 Permission::require() 静态方法
    // 优点: 简洁明了，代码少
    // 缺点: 抛出异常，中断后续代码
    // ========================================

    /**
     * 示例1: 检查单个权限
     */
    public function example1(Request $request)
    {
        // 要求article.create权限，如果没有权限会抛出异常并返回403
        Permission::require($request, 'article.create');

        // 如果有权限，继续执行
        return Response::success([], '创建成功');
    }

    /**
     * 示例2: 检查任一权限
     */
    public function example2(Request $request)
    {
        // 要求至少有其中一个权限
        Permission::requireAny($request, [
            'article.edit',
            'article.edit_own'
        ]);

        // 继续执行
        return Response::success([], '编辑成功');
    }

    /**
     * 示例3: 检查所有权限
     */
    public function example3(Request $request)
    {
        // 要求同时拥有这些权限
        Permission::requireAll($request, [
            'article.view',
            'article.edit'
        ]);

        // 继续执行
        return Response::success([], '操作成功');
    }

    // ========================================
    // 方法2: 使用 AdminUser::hasPermission() 进行判断
    // 优点: 灵活，可以自定义错误信息
    // 缺点: 代码稍多
    // ========================================

    /**
     * 示例4: 手动检查权限
     */
    public function example4(Request $request)
    {
        $userId = $request->user['id'];

        // 手动检查权限
        if (!AdminUser::hasPermission($userId, 'article.delete')) {
            return Response::forbidden('您没有删除文章的权限');
        }

        // 继续执行
        return Response::success([], '删除成功');
    }

    /**
     * 示例5: 检查编辑自己的文章权限
     */
    public function example5(Request $request, $articleId)
    {
        $userId = $request->user['id'];
        $article = \app\model\Article::find($articleId);

        // 检查是否有编辑所有文章的权限
        $canEditAll = AdminUser::hasPermission($userId, 'article.edit');

        // 检查是否有编辑自己文章的权限
        $canEditOwn = AdminUser::hasPermission($userId, 'article.edit_own');

        // 判断权限
        if (!$canEditAll && !($canEditOwn && $article->user_id == $userId)) {
            return Response::forbidden('您没有编辑此文章的权限');
        }

        // 继续执行
        return Response::success([], '编辑成功');
    }

    // ========================================
    // 方法3: 在批量操作中检查权限
    // ========================================

    /**
     * 示例6: 批量操作权限检查
     */
    public function example6(Request $request)
    {
        $userId = $request->user['id'];
        $action = $request->post('action'); // 'publish', 'delete', 'offline'

        // 首先检查是否有批量操作权限
        if (!AdminUser::hasPermission($userId, 'article.batch')) {
            return Response::forbidden('您没有批量操作的权限');
        }

        // 根据不同操作检查具体权限
        switch ($action) {
            case 'publish':
            case 'offline':
                if (!AdminUser::hasPermission($userId, 'article.publish')) {
                    return Response::forbidden('您没有发布/下线文章的权限');
                }
                break;

            case 'delete':
                if (!AdminUser::hasPermission($userId, 'article.delete')) {
                    return Response::forbidden('您没有删除文章的权限');
                }
                break;

            default:
                return Response::error('不支持的操作');
        }

        // 执行批量操作
        return Response::success([], '批量操作成功');
    }

    // ========================================
    // 方法4: 在路由中使用权限中间件
    // 在 route/api.php 中配置
    // ========================================

    /**
     * 示例7: 路由中间件检查（在路由文件中配置）
     *
     * 在 route/api.php 中：
     *
     * // 方式1: 为单个路由指定权限
     * Route::post('articles', 'Article@save')
     *     ->middleware(['Auth', 'Permission:article.create']);
     *
     * // 方式2: 为路由组指定权限
     * Route::group('articles', function() {
     *     Route::post('', 'Article@save');
     *     Route::put(':id', 'Article@update');
     * })->middleware(['Auth', 'Permission']);
     *
     * // 中间件会根据路由规则自动匹配所需权限
     */
    public function example7()
    {
        // 如果在路由中配置了权限中间件，这里不需要再检查
        return Response::success([], '操作成功');
    }

    // ========================================
    // 方法5: 组合使用多种权限检查
    // ========================================

    /**
     * 示例8: 复杂权限逻辑
     */
    public function example8(Request $request)
    {
        $userId = $request->user['id'];

        // 获取用户所有权限
        $permissions = AdminUser::getUserPermissions($userId);

        // 判断是否是超级管理员
        if (in_array('*', $permissions)) {
            // 超级管理员可以执行任何操作
            return Response::success([], '操作成功');
        }

        // 检查模块级权限
        if (in_array('article.*', $permissions)) {
            // 拥有文章模块所有权限
            return Response::success([], '操作成功');
        }

        // 检查具体权限
        $requiredPermissions = ['article.view', 'article.edit'];
        $hasAll = true;
        foreach ($requiredPermissions as $perm) {
            if (!in_array($perm, $permissions)) {
                $hasAll = false;
                break;
            }
        }

        if (!$hasAll) {
            return Response::forbidden('权限不足');
        }

        return Response::success([], '操作成功');
    }

    // ========================================
    // 最佳实践建议
    // ========================================

    /**
     * 示例9: 推荐的实践方式
     *
     * 1. 简单的CRUD操作，使用路由中间件自动检查
     * 2. 复杂的业务逻辑，在控制器方法中使用 Permission::require()
     * 3. 需要自定义逻辑的，使用 AdminUser::hasPermission() 手动判断
     * 4. 涉及数据权限的，结合业务逻辑检查
     */
    public function bestPractice(Request $request, $articleId)
    {
        $userId = $request->user['id'];

        // 步骤1: 检查基本权限
        $article = \app\model\Article::find($articleId);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        // 步骤2: 检查操作权限
        $canEdit = AdminUser::hasPermission($userId, 'article.edit');
        $canEditOwn = AdminUser::hasPermission($userId, 'article.edit_own');

        // 步骤3: 检查数据权限
        $isOwner = ($article->user_id == $userId);

        // 步骤4: 综合判断
        if (!$canEdit && !($canEditOwn && $isOwner)) {
            // 记录权限拒绝日志（可选）
            \think\facade\Log::warning("用户 {$userId} 尝试编辑文章 {$articleId} 但权限不足");

            return Response::forbidden('您没有编辑此文章的权限');
        }

        // 步骤5: 执行业务逻辑
        $article->title = $request->post('title');
        $article->save();

        // 步骤6: 记录操作日志
        \app\common\Logger::update(
            \app\model\OperationLog::MODULE_ARTICLE,
            '编辑文章',
            $articleId
        );

        return Response::success($article->toArray(), '编辑成功');
    }

    // ========================================
    // 前端配合示例
    // ========================================

    /**
     * 示例10: 返回用户可执行的操作列表
     *
     * 前端可以根据这个列表显示/隐藏按钮
     */
    public function getAvailableActions(Request $request, $articleId)
    {
        $userId = $request->user['id'];
        $article = \app\model\Article::find($articleId);

        if (!$article) {
            return Response::notFound('文章不存在');
        }

        $actions = [];

        // 检查各种操作权限
        if (AdminUser::hasPermission($userId, 'article.view')) {
            $actions[] = 'view';
        }

        if (AdminUser::hasPermission($userId, 'article.edit') ||
            (AdminUser::hasPermission($userId, 'article.edit_own') && $article->user_id == $userId)) {
            $actions[] = 'edit';
        }

        if (AdminUser::hasPermission($userId, 'article.delete')) {
            $actions[] = 'delete';
        }

        if (AdminUser::hasPermission($userId, 'article.publish')) {
            $actions[] = 'publish';
            $actions[] = 'offline';
        }

        if (AdminUser::hasPermission($userId, 'article.version')) {
            $actions[] = 'version';
        }

        return Response::success([
            'article_id' => $articleId,
            'available_actions' => $actions
        ]);
    }
}
