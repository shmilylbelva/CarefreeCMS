# CMS系统权限使用指南

## 1. 权限系统概述

本CMS系统采用基于角色的访问控制（RBAC），权限配置存储在`admin_roles`表的`permissions`字段中，采用JSON数组格式。

### 权限格式
- **通配符权限**: `*` - 超级管理员拥有所有权限
- **模块级通配符**: `article.*` - 拥有文章模块的所有权限
- **具体权限**: `article.create` - 只有创建文章的权限
- **特殊权限**: `article.edit_own` - 只能编辑自己的文章

## 2. 在代码中检查权限

### 2.1 在控制器中检查权限

```php
<?php
namespace app\controller\api;

use app\BaseController;
use app\model\AdminUser;
use app\common\Response;

class Article extends BaseController
{
    /**
     * 创建文章
     */
    public function create(Request $request)
    {
        // 获取当前登录用户ID
        $userId = $request->user['id'];

        // 检查是否有创建文章的权限
        if (!AdminUser::hasPermission($userId, 'article.create')) {
            return Response::forbidden('您没有创建文章的权限');
        }

        // 执行创建逻辑
        // ...
    }

    /**
     * 编辑文章
     */
    public function edit(Request $request, $id)
    {
        $userId = $request->user['id'];
        $article = Article::find($id);

        // 检查是否有编辑所有文章的权限
        $canEditAll = AdminUser::hasPermission($userId, 'article.edit');

        // 检查是否有编辑自己文章的权限
        $canEditOwn = AdminUser::hasPermission($userId, 'article.edit_own');

        // 如果有编辑所有文章的权限，或者有编辑自己文章的权限且这是自己的文章
        if (!$canEditAll && !($canEditOwn && $article->user_id == $userId)) {
            return Response::forbidden('您没有编辑此文章的权限');
        }

        // 执行编辑逻辑
        // ...
    }

    /**
     * 删除文章
     */
    public function delete(Request $request, $id)
    {
        $userId = $request->user['id'];

        // 检查删除权限
        if (!AdminUser::hasPermission($userId, 'article.delete')) {
            return Response::forbidden('您没有删除文章的权限');
        }

        // 执行删除逻辑
        // ...
    }
}
```

### 2.2 批量检查权限

```php
/**
 * 批量操作文章
 */
public function batchOperation(Request $request)
{
    $userId = $request->user['id'];
    $action = $request->post('action'); // 'publish', 'delete', 'offline'

    // 定义操作所需的权限
    $requiredPermissions = [
        'publish' => 'article.publish',
        'delete'  => 'article.delete',
        'offline' => 'article.publish',
    ];

    // 检查是否有批量操作权限
    if (!AdminUser::hasPermission($userId, 'article.batch')) {
        return Response::forbidden('您没有批量操作的权限');
    }

    // 检查具体操作的权限
    $permission = $requiredPermissions[$action] ?? null;
    if ($permission && !AdminUser::hasPermission($userId, $permission)) {
        return Response::forbidden("您没有{$action}操作的权限");
    }

    // 执行批量操作
    // ...
}
```

### 2.3 在视图/前端中检查权限

前端可以通过API获取当前用户的权限列表，然后根据权限显示或隐藏功能按钮。

```javascript
// Vue组件示例
<template>
  <div>
    <el-button
      v-if="hasPermission('article.create')"
      type="primary"
      @click="createArticle"
    >
      创建文章
    </el-button>

    <el-button
      v-if="hasPermission('article.edit')"
      type="warning"
      @click="editArticle"
    >
      编辑
    </el-button>

    <el-button
      v-if="hasPermission('article.delete')"
      type="danger"
      @click="deleteArticle"
    >
      删除
    </el-button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      userPermissions: []
    }
  },
  methods: {
    // 检查权限
    hasPermission(permission) {
      return this.userPermissions.includes(permission) ||
             this.userPermissions.includes('*') ||
             this.userPermissions.some(p => {
               const prefix = permission.split('.')[0]
               return p === `${prefix}.*`
             })
    },

    // 获取用户权限
    async loadUserPermissions() {
      const res = await api.get('/user/permissions')
      this.userPermissions = res.data.permissions
    }
  },
  mounted() {
    this.loadUserPermissions()
  }
}
</script>
```

## 3. 权限中间件（可选实现）

可以创建一个权限检查中间件，自动检查接口权限：

```php
<?php
namespace app\middleware;

use app\model\AdminUser;
use app\common\Response;
use Closure;
use think\Request;

/**
 * 权限检查中间件
 */
class Permission
{
    /**
     * 路由权限映射
     */
    protected $routePermissions = [
        // 文章管理
        'api/article/index'  => 'article.view',
        'api/article/read'   => 'article.read',
        'api/article/save'   => 'article.create',
        'api/article/update' => 'article.edit',
        'api/article/delete' => 'article.delete',

        // 分类管理
        'api/category/index'  => 'category.view',
        'api/category/read'   => 'category.read',
        'api/category/save'   => 'category.create',
        'api/category/update' => 'category.edit',
        'api/category/delete' => 'category.delete',

        // 更多路由权限映射...
    ];

    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next)
    {
        // 获取当前路由
        $route = $request->rule()->getRule();

        // 获取需要的权限
        $permission = $this->routePermissions[$route] ?? null;

        // 如果该路由需要权限检查
        if ($permission) {
            $userId = $request->user['id'] ?? 0;

            // 检查权限
            if (!AdminUser::hasPermission($userId, $permission)) {
                return Response::forbidden('您没有访问此功能的权限');
            }
        }

        return $next($request);
    }
}
```

在路由中使用权限中间件：

```php
// route/api.php
Route::group('api', function () {
    Route::resource('article', 'app\controller\api\Article');
    Route::resource('category', 'app\controller\api\Category');
})->middleware(['Auth', 'Permission']);
```

## 4. 角色权限管理

### 4.1 查看角色权限

```sql
-- 查看所有角色及其权限数量
SELECT
    id,
    name,
    description,
    JSON_LENGTH(permissions) as permission_count,
    status
FROM admin_roles
ORDER BY id;

-- 查看特定角色的完整权限
SELECT name, permissions
FROM admin_roles
WHERE id = 3;
```

### 4.2 修改角色权限

```php
<?php
namespace app\controller\api;

use app\model\AdminRole;
use app\model\AdminUser;

class Role extends BaseController
{
    /**
     * 更新角色权限
     */
    public function updatePermissions(Request $request, $id)
    {
        $permissions = $request->post('permissions', []);

        $role = AdminRole::find($id);
        if (!$role) {
            return Response::notFound('角色不存在');
        }

        // 更新权限
        $role->permissions = json_encode($permissions);
        $role->save();

        // 清除该角色下所有用户的权限缓存
        $users = AdminUser::where('role_id', $id)->select();
        foreach ($users as $user) {
            AdminUser::clearUserPermissionsCache($user->id);
        }

        return Response::success([], '权限更新成功');
    }
}
```

### 4.3 添加新角色

```sql
-- 添加新角色：审核员
INSERT INTO admin_roles (name, description, permissions, status)
VALUES (
    '审核员',
    '负责审核用户投稿和评论',
    JSON_ARRAY(
        'dashboard.view',
        'article.view',
        'article.read',
        'comment.*',
        'contribute.*',
        'violation.*',
        'profile.*'
    ),
    1
);
```

## 5. 权限最佳实践

### 5.1 最小权限原则
- 为用户分配完成工作所需的最小权限集
- 避免过度授权
- 定期审查和调整权限配置

### 5.2 权限命名规范
- 使用统一的命名格式：`模块.操作`
- 操作动词使用：view, read, create, edit, delete, batch 等
- 特殊权限使用下划线：`edit_own`, `delete_own`

### 5.3 权限缓存
- 用户权限会被缓存1小时
- 修改角色权限后需要清空相关用户的缓存
- 可以通过重启应用或调用清除缓存接口

```php
// 清除单个用户权限缓存
AdminUser::clearUserPermissionsCache($userId);

// 清除所有用户权限缓存
\think\facade\Cache::tag('admin_users')->clear();
```

### 5.4 前后端权限一致性
- 前端权限检查只用于UI显示/隐藏
- 后端必须进行严格的权限验证
- 不要仅依赖前端权限控制

### 5.5 特殊权限处理
```php
// 检查是否可以编辑文章
public function canEdit($userId, $articleId)
{
    $article = Article::find($articleId);

    // 超级管理员或有完整编辑权限
    if (AdminUser::hasPermission($userId, '*') ||
        AdminUser::hasPermission($userId, 'article.edit')) {
        return true;
    }

    // 只能编辑自己的文章
    if (AdminUser::hasPermission($userId, 'article.edit_own') &&
        $article->user_id == $userId) {
        return true;
    }

    return false;
}
```

## 6. 常见问题

### Q1: 修改了权限配置，为什么不生效？
A: 权限信息会被缓存，需要清空缓存或等待缓存过期（1小时）。

### Q2: 如何查看当前用户的权限？
A: 可以通过API接口获取：
```php
public function getMyPermissions(Request $request)
{
    $userId = $request->user['id'];
    $permissions = AdminUser::getUserPermissions($userId);
    return Response::success(['permissions' => $permissions]);
}
```

### Q3: 如何实现更复杂的权限控制（如数据级权限）？
A: 可以在业务逻辑中额外添加检查：
```php
// 只能查看自己部门的数据
if (!AdminUser::hasPermission($userId, 'article.*')) {
    $query->where('department_id', $user->department_id);
}
```

### Q4: 如何批量授权多个权限？
A: 使用通配符可以简化权限配置：
- `article.*` - 文章模块所有权限
- `*` - 所有权限

## 7. 权限清单速查表

### 超级管理员 (ID=1)
- 权限数量: 1
- 权限范围: 所有功能（`*`）

### 管理员 (ID=2)
- 权限数量: 74
- 权限范围: 内容管理、用户管理、营销工具、部分系统配置

### 编辑 (ID=3)
- 权限数量: 68
- 权限范围: 文章、分类、标签、页面、专题、评论、媒体、SEO

### 作者 (ID=4)
- 权限数量: 19
- 权限范围: 创建和编辑自己的文章、上传媒体、查看分类标签

---

**注意**: 本文档基于当前系统状态生成，如有功能模块变更，需要同步更新权限配置和本文档。
