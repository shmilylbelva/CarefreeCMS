# CMS系统角色权限更新总结

**更新日期**: 2025-11-30
**更新人员**: Claude AI Assistant

## 1. 更新概述

本次更新根据项目现状，全面梳理并更新了CMS系统的角色权限配置，包括：
- 完整的权限体系设计（20个功能模块，200+个细粒度权限）
- 4个角色的权限配置优化
- 权限API接口的添加
- 相关文档的创建

## 2. 新增文件

### 2.1 权限配置文档
**文件**: `backend/database/permissions_config.md`
**内容**: 完整的权限配置方案，包括20个功能模块的详细权限列表

### 2.2 权限更新脚本
**文件**: `backend/database/update_role_permissions.sql`
**内容**: 数据库更新脚本，用于更新所有角色的权限配置

### 2.3 权限使用指南
**文件**: `backend/database/permissions_usage_guide.md`
**内容**:
- 权限系统工作原理
- 代码中如何检查权限
- 权限管理最佳实践
- 常见问题解答

## 3. 角色权限配置

### 3.1 超级管理员 (ID=1)
- **权限数量**: 1
- **权限范围**: `["*"]` - 所有权限
- **描述**: 拥有系统所有权限，包括系统配置、用户管理、内容管理等全部功能

### 3.2 管理员 (ID=2)
- **权限数量**: 74
- **权限范围**:
  - 内容管理（文章、分类、标签、页面、专题）
  - 媒体管理（上传、编辑、水印、缩略图、视频）
  - 评论管理（审核、删除、举报处理）
  - 用户管理（查看、编辑前台用户）
  - 广告营销（广告、轮播图、友链）
  - AI功能（查看配置、使用AI生成）
  - 模板管理（查看、编辑模板）
  - SEO管理（分析、重定向、sitemap）
  - 数据库备份与下载
  - 缓存管理
  - 通知管理
  - 投稿管理
  - 积分商城
  - 回收站
- **不包括**:
  - 系统核心配置修改
  - 后台用户和角色管理
  - 存储配置修改
  - 邮件/短信配置修改
  - 定时任务管理
  - 数据库还原

### 3.3 编辑 (ID=3)
- **权限数量**: 68
- **权限范围**:
  - 文章管理（完整权限，包括版本管理）
  - 分类管理（查看、创建、编辑）
  - 标签管理（完整权限）
  - 页面管理（完整权限）
  - 专题管理（完整权限）
  - 媒体管理（上传、编辑、删除）
  - 评论管理（审核、删除）
  - AI内容生成
  - 静态页面生成
  - SEO基础功能
  - 投稿审核
- **不包括**:
  - 用户管理
  - 系统配置
  - 广告营销
  - 数据库操作
  - 定时任务

### 3.4 作者 (ID=4)
- **权限数量**: 19
- **权限范围**:
  - 查看文章列表
  - 创建文章
  - 编辑自己的文章（`article.edit_own`）
  - 查看文章版本
  - 查看分类和标签
  - 创建标签
  - 上传和编辑媒体
  - 使用AI生成内容
  - 管理个人信息
- **限制**:
  - 只能编辑自己创建的文章
  - 无法删除文章
  - 无法发布/下线文章
  - 无法管理分类

## 4. 代码修改

### 4.1 添加权限API接口

#### 文件: `backend/app/controller/api/Profile.php`
**修改内容**:
1. 在`index()`方法中添加权限信息到返回数据
2. 新增`permissions()`方法，专门返回用户权限列表

```php
/**
 * 获取当前用户的权限列表
 */
public function permissions(Request $request)
{
    $permissions = AdminUser::getUserPermissions($request->user['id']);

    return Response::success([
        'permissions' => $permissions,
        'is_super_admin' => in_array('*', $permissions)
    ]);
}
```

#### 文件: `backend/route/api.php`
**修改内容**: 添加权限查询路由

```php
Route::get('profile/permissions', 'app\controller\api\Profile@permissions'); // 获取当前用户权限
```

### 4.2 修复权限获取bug

#### 文件: `backend/app/model/AdminUser.php`
**问题**: `getUserPermissions()`方法对已经是数组的权限数据再次执行`json_decode()`
**原因**: `AdminRole`模型已经配置了`'permissions' => 'json'`自动类型转换
**修复**: 移除多余的`json_decode()`调用

修改前:
```php
$permissions = json_decode($user->role->permissions ?? '[]', true);
```

修改后:
```php
$permissions = $user->role->permissions ?? [];
```

## 5. 数据库更新结果

### 更新前
```sql
SELECT id, name, JSON_LENGTH(permissions) as count FROM admin_roles;
```
| ID | 角色名称 | 权限数量 |
|----|---------|---------|
| 1  | 超级管理员 | 1 |
| 2  | 管理员 | 6 |
| 3  | 编辑 | 5 |
| 4  | 作者 | 3 |

### 更新后
| ID | 角色名称 | 权限数量 |
|----|---------|---------|
| 1  | 超级管理员 | 1 |
| 2  | 管理员 | 74 |
| 3  | 编辑 | 68 |
| 4  | 作者 | 19 |

## 6. API接口使用

### 6.1 获取个人信息（包含权限）
```bash
GET /api/profile
Authorization: Bearer {token}
```

**响应示例**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "username": "admin",
    "real_name": "系统管理员",
    "role_id": 1,
    "permissions": ["*"],
    ...
  }
}
```

### 6.2 仅获取权限列表
```bash
GET /api/profile/permissions
Authorization: Bearer {token}
```

**响应示例**:
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "permissions": ["*"],
    "is_super_admin": true
  }
}
```

## 7. 权限系统特性

### 7.1 通配符支持
- `*` - 所有权限（超级管理员）
- `article.*` - 文章模块所有权限
- `article.edit` - 文章编辑权限

### 7.2 特殊权限
- `article.edit_own` - 只能编辑自己的文章
- `article.delete_own` - 只能删除自己的文章

### 7.3 权限缓存
- 用户权限会缓存1小时
- 修改角色权限后需要清空缓存
- 可以手动清空: `AdminUser::clearUserPermissionsCache($userId)`

## 8. 前端集成建议

### 8.1 获取权限
```javascript
// 登录后获取用户信息和权限
const { data } = await api.get('/profile')
const permissions = data.permissions

// 或者单独获取权限
const { data } = await api.get('/profile/permissions')
const permissions = data.permissions
const isSuperAdmin = data.is_super_admin
```

### 8.2 检查权限
```javascript
function hasPermission(userPermissions, required) {
  // 超级管理员
  if (userPermissions.includes('*')) return true

  // 完全匹配
  if (userPermissions.includes(required)) return true

  // 通配符匹配
  const prefix = required.split('.')[0]
  if (userPermissions.includes(`${prefix}.*`)) return true

  return false
}

// 使用示例
if (hasPermission(permissions, 'article.create')) {
  // 显示创建按钮
}
```

### 8.3 指令封装（Vue示例）
```javascript
// directives/permission.js
export default {
  mounted(el, binding) {
    const { value } = binding
    const permissions = store.getters.permissions

    if (!hasPermission(permissions, value)) {
      el.parentNode?.removeChild(el)
    }
  }
}

// 使用
<el-button v-permission="'article.create'">创建文章</el-button>
```

## 9. 注意事项

### 9.1 重要提醒
1. **双重验证**: 前端权限检查只用于UI显示，后端必须严格验证权限
2. **缓存清理**: 修改角色权限后，建议通知相关用户重新登录或手动清空缓存
3. **权限命名**: 新增功能时遵循`模块.操作`的命名规范
4. **文档更新**: 新增权限时同步更新`permissions_config.md`

### 9.2 安全建议
- 不要在前端存储敏感权限逻辑
- 定期审查角色权限配置
- 遵循最小权限原则
- 记录权限变更日志

## 10. 后续工作建议

### 10.1 短期优化
- [ ] 在前端实现权限指令/组件
- [ ] 添加权限变更日志记录
- [ ] 创建权限管理界面（可视化编辑）

### 10.2 长期规划
- [ ] 实现数据级权限（如：只能查看自己部门的数据）
- [ ] 支持自定义角色和动态权限分配
- [ ] 实现权限模板功能
- [ ] 添加权限审计功能

## 11. 相关文档

1. **permissions_config.md** - 完整权限配置方案
2. **update_role_permissions.sql** - 数据库更新脚本
3. **permissions_usage_guide.md** - 开发者使用指南
4. **permissions_update_summary.md** - 本文档

---

**更新完成时间**: 2025-11-30 17:45
**测试状态**: ✅ 所有角色权限已更新并测试通过
**API状态**: ✅ 权限查询接口已添加并测试通过
