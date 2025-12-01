# RESTful API 使用示例

本文档提供常用API的调用示例，包括旧方式和新的RESTful方式对比。

---

## 文章管理

### 1. 获取文章列表

```bash
GET /api/articles?page=1&page_size=20&status=1

# 响应
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [...],
    "total": 100
  }
}
```

### 2. 获取单篇文章

```bash
GET /api/articles/123

# 响应
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 123,
    "title": "文章标题",
    "content": "文章内容",
    "status": 1
  }
}
```

### 3. 创建文章

```bash
POST /api/articles
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "新文章标题",
  "content": "文章内容",
  "category_id": 5,
  "status": 0
}

# 响应
{
  "code": 200,
  "message": "文章创建成功",
  "data": {
    "id": 124,
    "title": "新文章标题"
  }
}
```

### 4. 完整更新文章

```bash
PUT /api/articles/123
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "更新后的标题",
  "content": "更新后的内容",
  "category_id": 5,
  "status": 1,
  "is_top": 0,
  "is_recommend": 1
}
```

### 5. 部分更新文章（RESTful - 推荐）

#### 5.1 发布文章

```bash
# ❌ 旧方式（已废弃）
POST /api/articles/123/publish
Authorization: Bearer {token}

# ✅ 新方式（RESTful）
PATCH /api/articles/123
Content-Type: application/json
Authorization: Bearer {token}

{
  "status": "published"
}

# 或使用数字状态
{
  "status": 1
}
```

#### 5.2 下线文章

```bash
# ❌ 旧方式（已废弃）
POST /api/articles/123/offline

# ✅ 新方式（RESTful）
PATCH /api/articles/123
{
  "status": "offline"
}
```

#### 5.3 置顶文章

```bash
PATCH /api/articles/123
{
  "is_top": 1
}
```

#### 5.4 设置推荐

```bash
PATCH /api/articles/123
{
  "is_recommend": 1
}
```

#### 5.5 批量更新多个字段

```bash
PATCH /api/articles/123
{
  "status": "published",
  "is_top": 1,
  "is_recommend": 1,
  "is_hot": 1
}
```

### 6. 删除文章

```bash
DELETE /api/articles/123
Authorization: Bearer {token}

# 响应
{
  "code": 200,
  "message": "文章删除成功"
}
```

---

## 批量操作

### 1. 批量删除文章

```bash
# 当前方式
POST /api/articles/batch-delete
Content-Type: application/json
Authorization: Bearer {token}

{
  "ids": [1, 2, 3, 4, 5]
}

# 未来推荐方式
DELETE /api/v1/articles
{
  "ids": [1, 2, 3, 4, 5]
}
```

### 2. 批量发布文章

```bash
# 当前方式
POST /api/articles/batch-publish
{
  "ids": [1, 2, 3]
}

# 未来推荐方式
PATCH /api/v1/articles
{
  "ids": [1, 2, 3],
  "data": {
    "status": "published"
  }
}
```

### 3. 批量修改分类

```bash
POST /api/articles/batch-update-category
{
  "ids": [1, 2, 3],
  "category_id": 5
}
```

---

## 认证管理

### 1. 登录

```bash
POST /api/auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123"
}

# 响应
{
  "code": 200,
  "message": "登录成功",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "username": "admin",
      "real_name": "管理员"
    }
  }
}
```

### 2. 获取用户信息

```bash
GET /api/auth/info
Authorization: Bearer {token}

# 响应
{
  "code": 200,
  "data": {
    "id": 1,
    "username": "admin",
    "email": "admin@example.com",
    "role": {
      "name": "超级管理员",
      "permissions": ["article.view", "article.create", ...]
    }
  }
}
```

### 3. 修改密码

```bash
# ❌ 旧方式（已废弃）
POST /api/auth/change-password
Authorization: Bearer {token}
{
  "old_password": "old123",
  "new_password": "new123"
}

# ✅ 新方式（RESTful）
PATCH /api/auth/password
Authorization: Bearer {token}
{
  "old_password": "old123",
  "new_password": "new123"
}

# 响应
{
  "code": 200,
  "message": "密码修改成功"
}
```

### 4. 退出登录

```bash
POST /api/auth/logout
Authorization: Bearer {token}

# 响应
{
  "code": 200,
  "message": "退出成功"
}
```

---

## 数据导出

### 导出文章数据

```bash
# 导出全部
GET /api/articles/export?format=xlsx

# 导出筛选的文章
GET /api/articles/export?format=csv&status=1&category_id=5

# 导出选中的文章
GET /api/articles/export?format=xlsx&type=selected&ids=1,2,3,4,5

# 响应：直接下载文件
Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
Content-Disposition: attachment;filename="articles_20250126123456.xlsx"
```

---

## API版本控制

### 方式1：URL路径（推荐）

```bash
# v1版本
GET /api/v1/articles

# v2版本（未来）
GET /api/v2/articles
```

### 方式2：请求头

```bash
GET /api/articles
Header: API-Version: v1

# 响应头会包含版本信息
Response Header: API-Version: v1
```

---

## 错误处理

### 参数错误

```bash
POST /api/articles
{
  "title": "",
  "content": ""
}

# 响应
{
  "code": 400,
  "message": "参数错误",
  "errors": {
    "title": ["标题不能为空"],
    "content": ["内容不能为空"]
  }
}
```

### 认证错误

```bash
GET /api/articles/123
# 未提供token或token无效

# 响应
{
  "code": 401,
  "message": "未认证，请先登录"
}
```

### 权限错误

```bash
DELETE /api/articles/123
Authorization: Bearer {token}
# 用户没有删除权限

# 响应
{
  "code": 403,
  "message": "无权限执行此操作"
}
```

### 资源不存在

```bash
GET /api/articles/99999
# 文章不存在

# 响应
{
  "code": 404,
  "message": "文章不存在"
}
```

---

## JavaScript 调用示例

### 使用 Axios

```javascript
import axios from 'axios'

// 配置拦截器
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// 获取文章列表
const getArticles = async () => {
  try {
    const response = await axios.get('/api/articles', {
      params: {
        page: 1,
        page_size: 20,
        status: 1
      }
    })
    return response.data
  } catch (error) {
    console.error('获取文章失败:', error)
    throw error
  }
}

// 发布文章（RESTful方式）
const publishArticle = async (id) => {
  try {
    const response = await axios.patch(`/api/articles/${id}`, {
      status: 'published'
    })
    return response.data
  } catch (error) {
    console.error('发布文章失败:', error)
    throw error
  }
}

// 部分更新文章
const updateArticle = async (id, data) => {
  try {
    const response = await axios.patch(`/api/articles/${id}`, data)
    return response.data
  } catch (error) {
    console.error('更新文章失败:', error)
    throw error
  }
}

// 修改密码（RESTful方式）
const changePassword = async (oldPassword, newPassword) => {
  try {
    const response = await axios.patch('/api/auth/password', {
      old_password: oldPassword,
      new_password: newPassword
    })
    return response.data
  } catch (error) {
    console.error('修改密码失败:', error)
    throw error
  }
}

// 批量发布文章
const batchPublish = async (ids) => {
  try {
    const response = await axios.post('/api/articles/batch-publish', { ids })
    return response.data
  } catch (error) {
    console.error('批量发布失败:', error)
    throw error
  }
}

// 导出文章
const exportArticles = (format = 'xlsx', filters = {}) => {
  const queryString = new URLSearchParams({
    format,
    ...filters
  }).toString()

  window.open(`/api/articles/export?${queryString}`, '_blank')
}
```

---

## 前端迁移指南

### 1. 文章状态变更迁移

```javascript
// ❌ 旧方式
const publishArticle = (id) => {
  return axios.post(`/api/articles/${id}/publish`)
}

// ✅ 新方式
const publishArticle = (id) => {
  return axios.patch(`/api/articles/${id}`, {
    status: 'published'
  })
}
```

### 2. 密码修改迁移

```javascript
// ❌ 旧方式
const changePassword = (oldPwd, newPwd) => {
  return axios.post('/api/auth/change-password', {
    old_password: oldPwd,
    new_password: newPwd
  })
}

// ✅ 新方式
const changePassword = (oldPwd, newPwd) => {
  return axios.patch('/api/auth/password', {
    old_password: oldPwd,
    new_password: newPwd
  })
}
```

### 3. 统一封装方法

```javascript
// api/article.js
export const articleApi = {
  // 获取列表
  getList: (params) => axios.get('/api/articles', { params }),

  // 获取详情
  getDetail: (id) => axios.get(`/api/articles/${id}`),

  // 创建
  create: (data) => axios.post('/api/articles', data),

  // 完整更新
  update: (id, data) => axios.put(`/api/articles/${id}`, data),

  // 部分更新（推荐）
  patch: (id, data) => axios.patch(`/api/articles/${id}`, data),

  // 删除
  delete: (id) => axios.delete(`/api/articles/${id}`),

  // 发布（使用patch）
  publish: (id) => axios.patch(`/api/articles/${id}`, { status: 'published' }),

  // 下线（使用patch）
  offline: (id) => axios.patch(`/api/articles/${id}`, { status: 'offline' }),

  // 批量操作
  batchDelete: (ids) => axios.post('/api/articles/batch-delete', { ids }),
  batchPublish: (ids) => axios.post('/api/articles/batch-publish', { ids }),

  // 导出
  export: (params) => {
    const query = new URLSearchParams(params).toString()
    window.open(`/api/articles/export?${query}`, '_blank')
  }
}
```

---

## 测试建议

1. **使用Postman/Insomnia测试API**
2. **先测试新API，确保正常后再迁移前端代码**
3. **保持新旧API同时可用一段时间**
4. **监控旧API使用情况，逐步废弃**
5. **在响应头中添加废弃警告**

---

## 更多帮助

- API文档：访问 `/api/api-doc` 查看完整的Swagger文档
- 设计规范：查看 `API_DESIGN_GUIDE.md`
- 问题反馈：提交Issue到项目仓库
