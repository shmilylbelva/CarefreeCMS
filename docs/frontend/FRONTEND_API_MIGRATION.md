# 前端API迁移总结

本文档记录了前端代码从旧API迁移到新的RESTful API的详细信息。

## 迁移日期
2025-01-26

## 迁移概述

将前端API调用从非RESTful方式迁移到标准的RESTful PATCH方法，提升API设计的一致性和语义化。

---

## 已完成的迁移

### 1. 文章状态管理 API

#### 文件：`frontend/src/api/article.js`

**发布文章 (publishArticle)**
```javascript
// ❌ 迁移前
export function publishArticle(id) {
  return request({
    url: `/articles/${id}/publish`,
    method: 'post'
  })
}

// ✅ 迁移后
export function publishArticle(id) {
  return request({
    url: `/articles/${id}`,
    method: 'patch',
    data: { status: 'published' }
  })
}
```

**下线文章 (offlineArticle)**
```javascript
// ❌ 迁移前
export function offlineArticle(id) {
  return request({
    url: `/articles/${id}/offline`,
    method: 'post'
  })
}

// ✅ 迁移后
export function offlineArticle(id) {
  return request({
    url: `/articles/${id}`,
    method: 'patch',
    data: { status: 'offline' }
  })
}
```

**新增功能：通用PATCH方法**
```javascript
// 新增：部分更新文章（可用于任意字段更新）
export function patchArticle(id, data) {
  return request({
    url: `/articles/${id}`,
    method: 'patch',
    data
  })
}

// 使用示例
patchArticle(123, { is_top: 1 })           // 置顶
patchArticle(123, { is_recommend: 1 })     // 推荐
patchArticle(123, { status: 'draft' })     // 改为草稿
patchArticle(123, {
  status: 'published',
  is_top: 1,
  is_recommend: 1
})  // 批量更新多个字段
```

**影响的前端文件**：
- `frontend/src/views/article/List.vue` - 使用 publishArticle 和 offlineArticle
- 函数签名未改变，前端业务代码无需修改

---

### 2. 认证 API - 密码修改

#### 文件：`frontend/src/api/auth.js`

```javascript
// ❌ 迁移前
export function changePassword(data) {
  return request({
    url: '/auth/change-password',
    method: 'post',
    data
  })
}

// ✅ 迁移后
export function changePassword(data) {
  return request({
    url: '/auth/password',
    method: 'patch',
    data
  })
}
```

**注意**：此函数当前未在前端业务代码中使用，但已迁移以保持API一致性。

---

### 3. 个人资料 API - 密码修改

#### 文件：`frontend/src/api/profile.js`

```javascript
// ❌ 迁移前
export function updatePassword(data) {
  return request({
    url: '/profile/password',
    method: 'post',
    data
  })
}

// ✅ 迁移后
export function updatePassword(data) {
  return request({
    url: '/profile/password',
    method: 'patch',
    data
  })
}
```

**影响的前端文件**：
- `frontend/src/views/Profile.vue` - 使用 updatePassword
- 函数签名未改变，前端业务代码无需修改

---

## 后端兼容性

### 旧API保留策略

为确保平滑过渡，后端同时支持新旧两种API：

#### 路由配置：`backend/route/api.php`

```php
// 文章管理
Route::patch('articles/:id', 'Article@patch');                    // ✅ 新API（推荐）
Route::post('articles/:id/publish', 'Article@publish');           // ⚠️ 已废弃，6个月后移除
Route::post('articles/:id/offline', 'Article@offline');           // ⚠️ 已废弃，6个月后移除

// 认证管理
Route::patch('auth/password', 'Auth@updatePassword');             // ✅ 新API（推荐）
Route::post('auth/change-password', 'Auth@changePassword');       // ⚠️ 已废弃，6个月后移除

// 个人资料管理
Route::patch('profile/password', 'Profile@updatePassword');       // ✅ 新API（推荐）
Route::post('profile/password', 'Profile@updatePassword');        // ⚠️ 已废弃，6个月后移除
```

**废弃时间表**：
- 新旧API并行运行：2025-01-26 至 2025-07-26 (6个月)
- 旧API移除时间：2025-07-26

---

## 迁移效果

### 优势

1. **语义化**：PATCH方法明确表示部分更新
2. **统一性**：所有状态变更使用同一个endpoint
3. **灵活性**：新增的patchArticle可以更新任意字段
4. **扩展性**：支持批量更新多个字段

### 代码简化

**迁移前**：
- 每种操作一个endpoint：`/publish`, `/offline`, `/top`, `/recommend`...
- 需要维护多个路由和控制器方法

**迁移后**：
- 统一endpoint：`PATCH /articles/:id`
- 通过data参数区分不同操作
- 单一控制器方法处理所有部分更新

---

## 兼容性说明

### 前端代码兼容性

✅ **完全兼容** - 所有修改的API函数签名未改变：
```javascript
// 函数调用方式完全相同
publishArticle(id)           // ✅ 参数未变
offlineArticle(id)          // ✅ 参数未变
changePassword(data)        // ✅ 参数未变
updatePassword(data)        // ✅ 参数未变
```

### 后端API兼容性

✅ **向后兼容** - 旧API仍然可用6个月
```bash
# 新旧API都能正常工作
PATCH /api/articles/123 {"status": "published"}  # ✅ 推荐
POST  /api/articles/123/publish                  # ✅ 仍然可用
```

---

## 未迁移的API

以下API未迁移，原因已标注：

### 批量操作API

**当前方式**：
```javascript
// comment.js
batch-delete         // POST /admin/comments/batch-delete
batch-update-status  // POST /admin/comments/batch-update-status

// 其他批量操作
batch-publish        // 文章批量发布
batch-delete         // 各种批量删除
```

**未迁移原因**：
1. 批量操作的RESTful设计需要更全面的规划
2. 需要考虑API版本控制策略（/api/v1/、/api/v2/）
3. 后端需要实现批量PATCH逻辑
4. 建议在API v2中统一处理

**未来推荐方式**：
```javascript
// 未来可能的设计
PATCH /api/v2/articles
{
  "ids": [1, 2, 3],
  "data": { "status": "published" }
}
```

---

## 测试建议

### 手动测试清单

- [ ] 文章发布功能
- [ ] 文章下线功能
- [ ] 个人资料密码修改
- [ ] 验证旧API仍然可用（兼容性测试）

### 测试步骤

#### 1. 测试文章发布

```bash
# 前端操作
1. 进入文章列表页面
2. 选择一篇草稿文章
3. 点击"发布"按钮
4. 验证文章状态变为"已发布"

# 开发者工具网络面板验证
Request URL: http://localhost:8000/api/articles/123
Request Method: PATCH
Request Payload: {"status":"published"}
```

#### 2. 测试文章下线

```bash
# 前端操作
1. 进入文章列表页面
2. 选择一篇已发布文章
3. 点击"下线"按钮
4. 验证文章状态变为"已下线"

# 开发者工具网络面板验证
Request URL: http://localhost:8000/api/articles/123
Request Method: PATCH
Request Payload: {"status":"offline"}
```

#### 3. 测试密码修改

```bash
# 前端操作
1. 进入个人中心
2. 切换到"修改密码"标签
3. 输入旧密码、新密码、确认密码
4. 点击"修改密码"按钮
5. 验证密码修改成功

# 开发者工具网络面板验证
Request URL: http://localhost:8000/api/profile/password
Request Method: PATCH
Request Payload: {
  "old_password":"old123",
  "new_password":"new123",
  "confirm_password":"new123"
}
```

---

## 文档参考

- 后端API设计指南：`backend/docs/API_DESIGN_GUIDE.md`
- 后端API使用示例：`backend/docs/API_EXAMPLES.md`
- 主要待办事项：`todo.md`

---

## 总结

✅ **已完成**：
- 3个API函数迁移到RESTful PATCH方式
- 新增1个通用的patchArticle方法
- 后端保持向后兼容（6个月过渡期）
- 前端业务代码零修改（函数签名未变）

⏳ **下一步**：
- 监控新API使用情况
- 6个月后移除旧API
- 规划批量操作API的RESTful设计
- 考虑引入API版本控制（v1, v2）

---

## 迁移负责人

Claude Code - 2025-01-26
