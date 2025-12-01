# CMS 系统 API 文档

## 📚 文档导航

- [基础信息](#基础信息)
- [认证方式](#认证方式)
- [请求格式](#请求格式)
- [响应格式](#响应格式)
- [文章管理 API](#文章管理-api)
- [分类管理 API](#分类管理-api)
- [标签管理 API](#标签管理-api)
- [用户认证 API](#用户认证-api)
- [错误处理](#错误处理)
- [实例代码](#实例代码)

---

## 基础信息

| 项目 | 值 |
|------|-----|
| API Base URL | `http://api.example.com/api` |
| 文档版本 | 1.0.0 |
| 更新时间 | 2025-10-24 |
| 支持格式 | JSON |

---

## 认证方式

### JWT Token 认证

所有需要认证的接口都需要在请求头中包含 JWT Token：

```
Authorization: Bearer <token>
```

#### 获取 Token

**请求**：
```http
POST /auth/login HTTP/1.1
Host: api.example.com
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123"
}
```

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "success",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 86400,
    "token_type": "Bearer"
  }
}
```

#### Token 有效期

- 默认有效期：24 小时（86400 秒）
- 自动刷新：支持
- 刷新端点：`POST /auth/refresh`

---

## 请求格式

### 通用请求头

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer <token>
X-Client-Version: 1.0.0
X-Request-Id: <unique-id>
```

### 查询参数

**分页参数**：
```
GET /backend/articles?page=1&page_size=20
```

**筛选参数**：
```
GET /backend/articles?category_id=1&status=1&is_top=1
```

**搜索参数**：
```
GET /backend/articles?title=关键词&search_type=fuzzy
```

**排序参数**：
```
GET /backend/articles?sort=publish_time&order=desc
```

### 请求体示例

```json
{
  "title": "文章标题",
  "content": "文章内容",
  "category_id": 1,
  "tags": [1, 2, 3],
  "summary": "文章摘要",
  "cover_image": "https://example.com/image.jpg"
}
```

---

## 响应格式

### 成功响应

**HTTP 状态码**: `200 OK`

```json
{
  "code": 0,
  "message": "success",
  "data": {
    "id": 1,
    "title": "Article Title",
    "content": "Article content...",
    "created_at": "2024-01-01T10:00:00Z"
  }
}
```

### 分页响应

**HTTP 状态码**: `200 OK`

```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "id": 1,
      "title": "Article 1",
      "status": 1
    },
    {
      "id": 2,
      "title": "Article 2",
      "status": 1
    }
  ],
  "pagination": {
    "total": 100,
    "page": 1,
    "page_size": 20,
    "total_pages": 5
  }
}
```

### 错误响应

**验证错误** (HTTP 422):
```json
{
  "code": 2001,
  "message": "Validation failed",
  "data": null,
  "errors": {
    "title": ["Title is required"],
    "email": ["Email format is invalid"]
  }
}
```

**认证错误** (HTTP 401):
```json
{
  "code": 4001,
  "message": "Unauthorized",
  "data": null,
  "errors": {}
}
```

**服务器错误** (HTTP 500):
```json
{
  "code": 5000,
  "message": "Internal Server Error",
  "data": null,
  "errors": {}
}
```

---

## 文章管理 API

### 获取文章列表

**请求**：
```http
GET /backend/article/list?page=1&page_size=20&category_id=1&status=1
```

**参数**：

| 参数 | 类型 | 必需 | 说明 |
|------|------|------|------|
| page | integer | 否 | 当前页码，默认为 1 |
| page_size | integer | 否 | 每页数量，默认为 20 |
| title | string | 否 | 文章标题（模糊查询） |
| category_id | integer | 否 | 分类 ID |
| user_id | integer | 否 | 作者 ID |
| status | integer | 否 | 文章状态 (0=草稿, 1=已发布, 2=待审核, 3=已下线) |
| is_top | integer | 否 | 是否置顶 (0=否, 1=是) |
| is_recommend | integer | 否 | 是否推荐 (0=否, 1=是) |
| start_time | string | 否 | 开始时间 (YYYY-MM-DD) |
| end_time | string | 否 | 结束时间 (YYYY-MM-DD) |

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "id": 1,
      "title": "文章标题",
      "category_id": 1,
      "category": {
        "id": 1,
        "name": "分类名称"
      },
      "user_id": 1,
      "user": {
        "id": 1,
        "username": "admin",
        "real_name": "管理员"
      },
      "summary": "文章摘要",
      "content": "文章内容...",
      "view_count": 100,
      "like_count": 10,
      "comment_count": 5,
      "is_top": 0,
      "is_recommend": 1,
      "status": 1,
      "publish_time": "2024-01-01T10:00:00Z",
      "create_time": "2024-01-01T10:00:00Z",
      "update_time": "2024-01-01T10:00:00Z",
      "tags": [
        {
          "id": 1,
          "name": "标签1"
        }
      ]
    }
  ],
  "pagination": {
    "total": 100,
    "page": 1,
    "page_size": 20,
    "total_pages": 5
  }
}
```

### 获取文章详情

**请求**：
```http
GET /backend/article/detail/{id}
```

**参数**：

| 参数 | 类型 | 必需 | 说明 |
|------|------|------|------|
| id | integer | 是 | 文章 ID |

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "success",
  "data": {
    "id": 1,
    "title": "文章标题",
    "content": "完整的文章内容...",
    "category": {
      "id": 1,
      "name": "分类名称"
    },
    "user": {
      "id": 1,
      "username": "admin"
    },
    "tags": [
      {
        "id": 1,
        "name": "标签1"
      }
    ],
    "view_count": 100,
    "like_count": 10,
    "comment_count": 5,
    "created_at": "2024-01-01T10:00:00Z"
  }
}
```

### 创建文章

**请求**：
```http
POST /backend/article/create
Authorization: Bearer <token>
Content-Type: application/json

{
  "title": "新文章标题",
  "content": "文章内容...",
  "category_id": 1,
  "summary": "文章摘要",
  "cover_image": "https://example.com/image.jpg",
  "tags": [1, 2, 3],
  "status": 0
}
```

**参数**：

| 参数 | 类型 | 必需 | 说明 |
|------|------|------|------|
| title | string | 是 | 文章标题 (3-200 字符) |
| content | string | 是 | 文章内容 (至少 10 字符) |
| category_id | integer | 是 | 分类 ID |
| summary | string | 否 | 文章摘要 (最多 500 字符) |
| cover_image | string | 否 | 封面图片 URL |
| tags | array | 否 | 标签 ID 数组 |
| status | integer | 否 | 状态，默认为 0 (草稿) |

**响应** (201 Created):
```json
{
  "code": 0,
  "message": "Article created successfully",
  "data": {
    "id": 1,
    "title": "新文章标题",
    "status": 0
  }
}
```

### 更新文章

**请求**：
```http
PUT /backend/article/update/{id}
Authorization: Bearer <token>
Content-Type: application/json

{
  "title": "更新的标题",
  "content": "更新的内容...",
  "category_id": 2
}
```

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "Article updated successfully",
  "data": null
}
```

### 删除文章

**请求**：
```http
DELETE /backend/article/delete/{id}
Authorization: Bearer <token>
```

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "Article deleted successfully",
  "data": null
}
```

### 发布文章

**请求**：
```http
POST /backend/article/publish/{id}
Authorization: Bearer <token>
```

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "Article published successfully",
  "data": {
    "id": 1,
    "status": 1,
    "publish_time": "2024-01-01T10:00:00Z"
  }
}
```

### 获取文章版本

**请求**：
```http
GET /backend/article/versions/{id}
```

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "version": 1,
      "title": "版本 1 的标题",
      "created_by": "admin",
      "created_at": "2024-01-01T10:00:00Z"
    },
    {
      "version": 2,
      "title": "版本 2 的标题",
      "created_by": "admin",
      "created_at": "2024-01-01T10:30:00Z"
    }
  ]
}
```

---

## 分类管理 API

### 获取分类列表

**请求**：
```http
GET /backend/category/list?parent_id=0
```

**参数**：

| 参数 | 类型 | 必需 | 说明 |
|------|------|------|------|
| parent_id | integer | 否 | 父分类 ID，默认为 0 |
| status | integer | 否 | 状态筛选 |

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "id": 1,
      "name": "分类 1",
      "slug": "category-1",
      "description": "分类描述",
      "parent_id": 0,
      "children": [
        {
          "id": 2,
          "name": "子分类",
          "parent_id": 1
        }
      ]
    }
  ]
}
```

### 创建分类

**请求**：
```http
POST /backend/category/create
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "新分类",
  "slug": "new-category",
  "description": "分类描述",
  "parent_id": 0
}
```

**响应** (201 Created):
```json
{
  "code": 0,
  "message": "Category created successfully",
  "data": {
    "id": 1,
    "name": "新分类"
  }
}
```

---

## 标签管理 API

### 获取标签列表

**请求**：
```http
GET /backend/tag/list?page=1&page_size=20
```

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "id": 1,
      "name": "标签 1",
      "slug": "tag-1",
      "article_count": 10
    }
  ],
  "pagination": {
    "total": 50,
    "page": 1,
    "page_size": 20
  }
}
```

### 创建标签

**请求**：
```http
POST /backend/tag/create
Authorization: Bearer <token>
Content-Type: application/json

{
  "name": "新标签",
  "description": "标签描述"
}
```

---

## 用户认证 API

### 用户登录

**请求**：
```http
POST /backend/auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123"
}
```

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "Login successful",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 86400,
    "user": {
      "id": 1,
      "username": "admin",
      "real_name": "管理员",
      "email": "admin@example.com",
      "avatar": "https://example.com/avatar.jpg",
      "role": {
        "id": 1,
        "name": "超级管理员"
      }
    }
  }
}
```

### 用户退出

**请求**：
```http
POST /backend/auth/logout
Authorization: Bearer <token>
```

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "Logout successful",
  "data": null
}
```

### 刷新 Token

**请求**：
```http
POST /backend/auth/refresh
Authorization: Bearer <token>
```

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "Token refreshed",
  "data": {
    "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
    "expires_in": 86400
  }
}
```

### 获取当前用户信息

**请求**：
```http
GET /backend/auth/me
Authorization: Bearer <token>
```

**响应** (200 OK):
```json
{
  "code": 0,
  "message": "success",
  "data": {
    "id": 1,
    "username": "admin",
    "real_name": "管理员",
    "email": "admin@example.com",
    "role": {
      "id": 1,
      "name": "超级管理员",
      "permissions": ["*"]
    }
  }
}
```

---

## 错误处理

### HTTP 状态码

| 状态码 | 含义 | 说明 |
|--------|------|------|
| 200 | OK | 请求成功 |
| 201 | Created | 资源创建成功 |
| 204 | No Content | 请求成功但无返回数据 |
| 400 | Bad Request | 请求参数错误 |
| 401 | Unauthorized | 未授权/Token 失效 |
| 403 | Forbidden | 禁止访问 |
| 404 | Not Found | 资源不存在 |
| 422 | Unprocessable Entity | 验证失败 |
| 500 | Internal Server Error | 服务器内部错误 |
| 503 | Service Unavailable | 服务不可用 |

### 错误码对应表

| 错误码 | HTTP 状态 | 含义 |
|--------|----------|------|
| 0 | 200 | 成功 |
| 2001 | 422 | 验证失败 |
| 3001 | 400 | 无效参数 |
| 4001 | 401 | 未授权 |
| 4002 | 401 | Token 过期 |
| 4003 | 403 | 权限不足 |
| 4004 | 404 | 资源不存在 |
| 5000 | 500 | 服务器错误 |
| 5001 | 500 | 数据库错误 |

---

## 实例代码

### PHP cURL 示例

```php
<?php
// 获取 Token
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'http://api.example.com/backend/auth/login',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'username' => 'admin',
        'password' => 'admin123'
    ]),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);

$response = curl_exec($curl);
$data = json_decode($response, true);
$token = $data['data']['token'];

// 使用 Token 获取文章列表
curl_setopt_array($curl, [
    CURLOPT_URL => 'http://api.example.com/backend/article/list',
    CURLOPT_POST => false,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

echo $response;
?>
```

### JavaScript/Fetch 示例

```javascript
// 获取 Token
const loginResponse = await fetch('http://api.example.com/backend/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    username: 'admin',
    password: 'admin123'
  })
});

const loginData = await loginResponse.json();
const token = loginData.data.token;

// 获取文章列表
const listResponse = await fetch('http://api.example.com/backend/article/list', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});

const articles = await listResponse.json();
console.log(articles);
```

### Python 示例

```python
import requests
import json

# 获取 Token
login_url = 'http://api.example.com/backend/auth/login'
login_data = {
    'username': 'admin',
    'password': 'admin123'
}

response = requests.post(login_url, json=login_data)
data = response.json()
token = data['data']['token']

# 获取文章列表
article_url = 'http://api.example.com/backend/article/list'
headers = {
    'Authorization': f'Bearer {token}'
}

response = requests.get(article_url, headers=headers)
articles = response.json()
print(json.dumps(articles, indent=2))
```

### TypeScript/Axios 示例

```typescript
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://api.example.com/api'
})

// 登录
async function login(username: string, password: string) {
  const response = await api.post('/auth/login', {
    username,
    password
  })
  return response.data.data.token
}

// 获取文章列表
async function getArticles(token: string, page = 1, pageSize = 20) {
  api.defaults.headers.common['Authorization'] = `Bearer ${token}`

  const response = await api.get('/article/list', {
    params: { page, page_size: pageSize }
  })

  return response.data
}

// 使用
const token = await login('admin', 'admin123')
const articles = await getArticles(token)
console.log(articles)
```

---

## 速率限制

| 端点 | 限制 | 时间窗口 |
|------|------|---------|
| 登录 | 5 次 | 15 分钟 |
| 获取列表 | 100 次 | 1 分钟 |
| 创建资源 | 50 次 | 1 分钟 |
| 更新资源 | 50 次 | 1 分钟 |
| 删除资源 | 20 次 | 1 分钟 |

---

## 相关资源

- [完整 Swagger/OpenAPI 文档](./swagger.yaml)
- [错误处理指南](./ERROR_HANDLING.md)
- [开发者指南](./DEVELOPER_GUIDE.md)

---

**API 版本**: 1.0.0
**最后更新**: 2025-10-24
**维护者**: Your Team
