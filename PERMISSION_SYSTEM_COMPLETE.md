# CMS权限系统完整实现指南

**最后更新**: 2025-11-30 18:00
**状态**: ✅ 完成

---

## 📋 目录

1. [系统概述](#系统概述)
2. [文件清单](#文件清单)
3. [快速开始](#快速开始)
4. [后端使用](#后端使用)
5. [前端使用](#前端使用)
6. [测试验证](#测试验证)
7. [常见问题](#常见问题)

---

## 系统概述

本权限系统基于 **RBAC (基于角色的访问控制)** 模型，提供了完整的前后端权限管理解决方案。

### 核心特性

- ✅ **20个功能模块** - 涵盖内容、用户、系统等所有功能
- ✅ **200+细粒度权限** - 从查看到删除的精细控制
- ✅ **4个预设角色** - 超级管理员、管理员、编辑、作者
- ✅ **后端中间件** - 自动路由权限检查
- ✅ **前端组件/指令** - 便捷的UI权限控制
- ✅ **权限缓存** - 提升性能
- ✅ **变更日志** - 记录所有权限修改

---

## 文件清单

### 📁 后端文件

#### 核心代码
```
backend/
├── app/
│   ├── middleware/
│   │   └── Permission.php                    # ⭐ 权限检查中间件
│   ├── controller/api/
│   │   ├── Profile.php                       # 修改：添加权限API
│   │   ├── Role.php                          # 修改：添加权限变更日志
│   │   └── PermissionExample.php             # ⭐ 使用示例
│   └── model/
│       ├── AdminUser.php                     # 修改：修复权限获取bug
│       └── AdminRole.php                     # 自动JSON转换
└── route/
    └── api.php                               # 添加权限API路由
```

#### 文档与脚本
```
backend/database/
├── permissions_config.md                     # ⭐ 完整权限配置方案
├── update_role_permissions.sql               # ⭐ 数据库更新脚本
├── permissions_usage_guide.md                # ⭐ 开发者使用指南
├── permissions_update_summary.md             # 更新总结
└── PERMISSION_SYSTEM_COMPLETE.md             # 本文档
```

### 📁 前端文件

```
frontend/src/
├── utils/
│   └── permission.js                         # ⭐ 权限工具类
├── components/
│   └── Permission/
│       └── index.vue                         # ⭐ 权限组件
└── views/
    └── PermissionExample.vue                 # ⭐ 完整示例页面
```

---

## 快速开始

### 1. 数据库更新

执行SQL脚本更新角色权限：

```bash
mysql -uroot -p cms_database < backend/database/update_role_permissions.sql
```

或使用命令行：
```bash
"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -uroot -p密码 < D:\work\cms\backend\database\update_role_permissions.sql
```

### 2. 清空权限缓存

```php
// 清空所有用户权限缓存
\think\facade\Cache::tag('admin_users')->clear();
```

### 3. 验证更新

```bash
# 登录获取token
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# 获取权限
curl -X GET http://localhost:8000/api/profile/permissions \
  -H "Authorization: Bearer {token}"
```

---

## 后端使用

### 方法1: 使用路由中间件（推荐）

在 `route/api.php` 中配置：

```php
// 为单个路由指定权限
Route::post('articles', 'Article@save')
    ->middleware(['Auth', 'Permission:article.create']);

// 为路由组指定权限
Route::group('articles', function() {
    Route::get('', 'Article@index');      // 自动检查 article.view
    Route::post('', 'Article@save');      // 自动检查 article.create
    Route::put(':id', 'Article@update');  // 自动检查 article.edit
})->middleware(['Auth', 'Permission']);
```

### 方法2: 在控制器中使用（灵活）

```php
use app\middleware\Permission;

class ArticleController {
    /**
     * 简单权限检查
     */
    public function create(Request $request) {
        // 要求 article.create 权限
        Permission::require($request, 'article.create');

        // 继续执行
        return Response::success([], '创建成功');
    }

    /**
     * 任一权限检查
     */
    public function edit(Request $request, $id) {
        // 需要 article.edit 或 article.edit_own 之一
        Permission::requireAny($request, [
            'article.edit',
            'article.edit_own'
        ]);

        return Response::success([], '编辑成功');
    }

    /**
     * 手动检查（自定义逻辑）
     */
    public function update(Request $request, $id) {
        $userId = $request->user['id'];
        $article = Article::find($id);

        $canEdit = AdminUser::hasPermission($userId, 'article.edit');
        $canEditOwn = AdminUser::hasPermission($userId, 'article.edit_own');

        if (!$canEdit && !($canEditOwn && $article->user_id == $userId)) {
            return Response::forbidden('您没有编辑此文章的权限');
        }

        // 继续执行
    }
}
```

### 方法3: 批量权限检查

```php
use app\middleware\Permission;

// 检查所有权限
Permission::requireAll($request, [
    'article.view',
    'article.edit'
]);

// 检查任一权限
Permission::requireAny($request, [
    'article.edit',
    'article.edit_own'
]);
```

---

## 前端使用

### 1. 在 main.js 中注册

```javascript
import { createApp } from 'vue'
import permission from '@/utils/permission'
import Permission from '@/components/Permission/index.vue'

const app = createApp(App)

// 注册全局指令
app.directive('permission', permission.permissionDirective)
app.directive('permission-any', permission.permissionAnyDirective)
app.directive('permission-all', permission.permissionAllDirective)
app.directive('permission-disable', permission.permissionDisableDirective)

// 注册全局组件
app.component('Permission', Permission)

// 注册全局方法
app.config.globalProperties.$hasPermission = permission.hasPermission
app.config.globalProperties.$isSuperAdmin = permission.isSuperAdmin

app.mount('#app')
```

### 2. 在组件中使用

#### 方式1: 使用指令（简洁）

```vue
<template>
  <!-- 没有权限时移除元素 -->
  <el-button v-permission="'article.create'" type="primary">
    创建文章
  </el-button>

  <!-- 任一权限即可 -->
  <el-button v-permission-any="['article.edit', 'article.edit_own']">
    编辑文章
  </el-button>

  <!-- 需要所有权限 -->
  <el-button v-permission-all="['article.view', 'article.edit']">
    查看并编辑
  </el-button>

  <!-- 没有权限时禁用而不是移除 -->
  <el-button v-permission-disable="'article.delete'">
    删除文章
  </el-button>
</template>
```

#### 方式2: 使用组件（功能强大）

```vue
<template>
  <!-- 单个权限 -->
  <Permission permission="article.create">
    <el-button type="primary">创建文章</el-button>
  </Permission>

  <!-- 任一权限 -->
  <Permission :permission="['article.edit', 'article.edit_own']" mode="any">
    <el-button type="warning">编辑文章</el-button>
  </Permission>

  <!-- 所有权限 -->
  <Permission :permission="['article.view', 'article.edit']" mode="all">
    <el-button type="info">查看并编辑</el-button>
  </Permission>
</template>

<script setup>
import Permission from '@/components/Permission/index.vue'
</script>
```

#### 方式3: 在脚本中检查（灵活）

```vue
<script setup>
import { computed } from 'vue'
import { hasPermission, hasAnyPermission, isSuperAdmin } from '@/utils/permission'

// 检查单个权限
const canCreate = computed(() => hasPermission('article.create'))

// 检查多个权限
const canEdit = computed(() => {
  return hasAnyPermission(['article.edit', 'article.edit_own'])
})

// 超级管理员检查
const isAdmin = computed(() => isSuperAdmin())

// 条件渲染
const showAdvancedOptions = computed(() => {
  return hasPermission('system_config.edit')
})
</script>

<template>
  <el-button v-if="canCreate" @click="handleCreate">创建</el-button>
  <el-button v-if="canEdit" @click="handleEdit">编辑</el-button>
  <el-tag v-if="isAdmin" type="danger">超级管理员</el-tag>
</template>
```

### 3. 过滤菜单

```javascript
import { filterByPermission } from '@/utils/permission'

const menuItems = [
  { title: '文章管理', permission: 'article.view' },
  { title: '用户管理', permission: 'admin_user.view' },
  { title: '系统设置', permission: 'system_config.view' }
]

// 只显示有权限的菜单
const filteredMenu = filterByPermission(menuItems, item => item.permission)
```

### 4. 根据权限映射值

```javascript
import { mapByPermission } from '@/utils/permission'

// 根据权限显示不同的用户级别
const userLevel = mapByPermission({
  '*': '超级管理员',
  'article.*': '文章管理员',
  'article.edit': '编辑',
  'article.create': '作者'
}, '访客')
```

---

## 测试验证

### 1. API测试

```bash
# 获取权限列表
curl -X GET "http://localhost:8000/api/profile/permissions" \
  -H "Authorization: Bearer {token}"

# 响应示例
{
  "code": 200,
  "data": {
    "permissions": ["*"],
    "is_super_admin": true
  }
}
```

### 2. 权限检查测试

```php
// 在控制器中测试
$userId = 1;

// 测试1: 检查单个权限
$result = AdminUser::hasPermission($userId, 'article.create');
// 超级管理员: true, 其他角色: 根据配置

// 测试2: 检查通配符
$result = AdminUser::hasPermission($userId, 'article.delete');
// 有 article.* 或 * 的用户: true

// 测试3: 获取所有权限
$permissions = AdminUser::getUserPermissions($userId);
// 返回: ['*'] 或 ['article.view', 'article.create', ...]
```

### 3. 前端测试

访问 `/permission-example` 页面查看完整示例。

---

## 常见问题

### Q1: 修改了权限配置但不生效？

**A**: 权限会被缓存1小时，需要清空缓存：

```php
// 清空单个用户缓存
AdminUser::clearUserPermissionsCache($userId);

// 清空所有用户缓存
\think\facade\Cache::tag('admin_users')->clear();
```

或重新登录。

### Q2: 如何添加新权限？

**A**: 三个步骤：

1. 在 `permissions_config.md` 中添加权限说明
2. 在 `admin_roles` 表中为相应角色添加权限
3. 在代码中使用新权限

```sql
-- 为管理员添加新权限
UPDATE admin_roles
SET permissions = JSON_ARRAY_APPEND(
    permissions,
    '$',
    'new_module.new_action'
)
WHERE id = 2;
```

### Q3: 如何实现数据级权限？

**A**: 在业务逻辑中额外判断：

```php
public function index(Request $request) {
    $query = Article::query();

    // 功能级权限
    if (!AdminUser::hasPermission($userId, 'article.view')) {
        return Response::forbidden();
    }

    // 数据级权限
    if (!AdminUser::hasPermission($userId, 'article.*')) {
        // 只能查看自己的文章
        $query->where('user_id', $userId);
    }

    return $query->paginate();
}
```

### Q4: 权限检查的性能如何？

**A**: 权限数据会缓存1小时，单次检查仅需：
- 从缓存读取权限列表
- 数组查找判断（O(n)）

对于高频调用，建议：
- 在控制器初始化时一次性检查
- 避免在循环中重复检查
- 使用路由中间件自动检查

### Q5: 前端权限检查是否安全？

**A**: 前端权限检查仅用于UI显示/隐藏，**不是安全措施**。

- ✅ 前端：提升用户体验
- ✅ 后端：真正的安全保障

**绝对不要**仅依赖前端权限控制！

### Q6: 如何查看权限变更历史？

**A**: 查询操作日志：

```sql
SELECT * FROM operation_logs
WHERE module = 'role'
  AND action LIKE '%权限%'
ORDER BY create_time DESC;
```

或通过API：
```bash
curl -X GET "http://localhost:8000/api/operation-logs?module=role"
```

---

## 权限清单速查

### 超级管理员
```json
["*"]
```

### 管理员 (74个权限)
- 内容管理：article.*, category.*, tag.*, page.*, topic.*
- 媒体管理：media.*, watermark.*, thumbnail.*, video.*
- 评论管理：comment.*, comment_report.*, violation.*
- 用户管理：front_user.view/read/edit/block, member_level.*
- 营销工具：ad.*, ad_position.*, slider.*, link.*
- AI功能：ai_prompt.*, ai_article.*, ai_image.*
- 模板管理：template.view/edit/check, build.*
- SEO管理：seo_*.*, sitemap.*
- 数据管理：database.view/backup/download, cache.*
- 其他：notification.*, contribute.*, point_shop_*.*

### 编辑 (68个权限)
- 内容管理：article.*, category.view/read/create/edit, tag.*, page.*, topic.*
- 媒体管理：media.view/upload/edit/delete/move
- 评论管理：comment.*, comment_report.*, violation.*
- AI功能：ai_article.view/create, ai_image.view/create
- 静态生成：build.index/article/category/tag/page
- SEO基础：seo_analyzer.view, seo_404.view, sitemap.*
- 投稿审核：contribute.*
- 回收站：recycle_bin.view/restore

### 作者 (19个权限)
- 文章：article.view/read/create/edit_own/version
- 分类：category.view/read
- 标签：tag.view/read/create
- 媒体：media.view/upload/edit
- AI：ai_article.view/create, ai_image.view/create
- 个人：profile.*

---

## 技术支持

- 📖 详细文档：`backend/database/permissions_usage_guide.md`
- 💡 使用示例：`backend/app/controller/api/PermissionExample.php`
- 🎨 前端示例：`frontend/src/views/PermissionExample.vue`
- 🐛 问题反馈：查看操作日志或系统日志

---

**祝使用愉快！** 🎉
