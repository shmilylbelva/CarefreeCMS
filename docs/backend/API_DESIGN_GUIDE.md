# RESTful API 设计规范

## 版本控制

### 策略
采用URL路径版本控制，格式：`/api/v{version}/resource`

### 版本号规则
- **v1**: 当前稳定版本
- **v2**: 下一个主要版本（重大变更）
- 子版本通过响应头 `API-Version: v1.2` 标识

### 版本兼容性
- 向后兼容的更新不改变主版本号
- 破坏性更新必须升级主版本号
- 旧版本至少维护6个月

### 使用方式
```bash
# 方式1：URL路径（推荐）
GET /api/v1/articles

# 方式2：请求头
GET /api/articles
Header: API-Version: v1
```

---

## HTTP 方法使用规范

### GET - 获取资源
```bash
GET /api/v1/articles          # 获取文章列表
GET /api/v1/articles/123      # 获取单篇文章
GET /api/v1/articles/123/versions  # 获取文章版本列表
```

### POST - 创建资源
```bash
POST /api/v1/articles
Content-Type: application/json
{
  "title": "文章标题",
  "content": "文章内容"
}
```

### PUT - 完整更新资源
```bash
PUT /api/v1/articles/123
Content-Type: application/json
{
  "title": "新标题",
  "content": "新内容",
  "status": 1,
  "category_id": 5
  # 必须包含所有必填字段
}
```

### PATCH - 部分更新资源（推荐用于状态变更）
```bash
# 发布文章
PATCH /api/v1/articles/123
{
  "status": "published"  # 或 "status": 1
}

# 置顶文章
PATCH /api/v1/articles/123
{
  "is_top": 1
}

# 批量更新字段
PATCH /api/v1/articles/123
{
  "status": "published",
  "is_top": 1,
  "is_recommend": 1
}
```

### DELETE - 删除资源
```bash
DELETE /api/v1/articles/123
```

---

## 状态转换规范

### 状态字段值
使用字符串表示状态（推荐），也支持数字：
```json
{
  "status": "draft",      // 草稿（0）
  "status": "published",  // 已发布（1）
  "status": "pending",    // 待审核（2）
  "status": "offline"     // 已下线（3）
}
```

### 状态转换示例

#### ❌ 旧方式（不推荐）
```bash
POST /api/articles/123/publish   # 发布
POST /api/articles/123/offline   # 下线
POST /api/articles/123/top       # 置顶
```

#### ✅ 新方式（RESTful）
```bash
# 发布文章
PATCH /api/v1/articles/123
{"status": "published"}

# 下线文章
PATCH /api/v1/articles/123
{"status": "offline"}

# 置顶文章
PATCH /api/v1/articles/123
{"is_top": 1}
```

---

## 批量操作规范

### 批量删除
```bash
DELETE /api/v1/articles
Content-Type: application/json
{
  "ids": [1, 2, 3, 4, 5]
}
```

### 批量更新
```bash
PATCH /api/v1/articles
Content-Type: application/json
{
  "ids": [1, 2, 3],
  "data": {
    "status": "published",
    "is_recommend": 1
  }
}
```

---

## 资源命名规范

### 使用复数名词
```bash
✅ /api/v1/articles
✅ /api/v1/categories
✅ /api/v1/tags

❌ /api/v1/article
❌ /api/v1/category
```

### 使用小写和连字符
```bash
✅ /api/v1/article-flags
✅ /api/v1/comment-reports

❌ /api/v1/ArticleFlags
❌ /api/v1/comment_reports
```

### 嵌套资源
```bash
# 文章的评论
GET /api/v1/articles/123/comments

# 文章的版本
GET /api/v1/articles/123/versions

# 最大嵌套层级：2层
```

---

## 查询参数规范

### 分页
```bash
GET /api/v1/articles?page=1&page_size=20
```

### 排序
```bash
GET /api/v1/articles?sort=create_time&order=desc
GET /api/v1/articles?sort=-create_time  # -表示降序
```

### 筛选
```bash
GET /api/v1/articles?status=published&category_id=5
GET /api/v1/articles?title=关键词&author_id=10
```

### 字段选择
```bash
GET /api/v1/articles?fields=id,title,create_time
```

### 搜索
```bash
GET /api/v1/articles?q=搜索关键词
GET /api/v1/articles/search?keyword=关键词&type=fulltext
```

---

## 响应格式规范

### 成功响应
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 123,
    "title": "文章标题"
  }
}
```

### 列表响应
```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [...],
    "pagination": {
      "total": 100,
      "page": 1,
      "page_size": 20,
      "total_pages": 5
    }
  }
}
```

### 错误响应
```json
{
  "code": 400,
  "message": "参数错误：标题不能为空",
  "errors": {
    "title": ["标题不能为空", "标题长度不能超过200个字符"]
  }
}
```

---

## 状态码使用规范

| 状态码 | 说明 | 使用场景 |
|--------|------|----------|
| 200 | OK | 成功获取资源或操作成功 |
| 201 | Created | 资源创建成功 |
| 204 | No Content | 删除成功或更新成功但无返回内容 |
| 400 | Bad Request | 请求参数错误 |
| 401 | Unauthorized | 未认证 |
| 403 | Forbidden | 无权限 |
| 404 | Not Found | 资源不存在 |
| 409 | Conflict | 资源冲突（如重复创建） |
| 422 | Unprocessable Entity | 验证失败 |
| 429 | Too Many Requests | 请求过于频繁 |
| 500 | Internal Server Error | 服务器内部错误 |

---

## 认证规范

### JWT Token
```bash
Authorization: Bearer {token}
```

### Token刷新
```bash
POST /api/v1/auth/refresh
Authorization: Bearer {refresh_token}
```

---

## 弃用API处理

### 响应头标识
```
Deprecation: true
Sunset: 2025-12-31
Link: </api/v2/articles>; rel="alternate"
```

### 文档标注
在API文档中明确标注：
```
@deprecated 此接口已废弃，请使用 PATCH /api/v1/articles/:id
```

---

## 最佳实践

1. **幂等性**：PUT、PATCH、DELETE应保证幂等性
2. **缓存**：合理使用 ETag、Last-Modified 实现缓存
3. **限流**：使用 X-RateLimit-* 头部告知客户端限流信息
4. **CORS**：生产环境严格配置允许的域名
5. **HTTPS**：生产环境强制使用HTTPS
6. **日志**：记录所有API调用日志用于审计
7. **文档**：使用Swagger/OpenAPI自动生成文档

---

## 迁移指南

### 从旧API迁移到新API

#### 文章发布
```bash
# 旧方式
POST /api/articles/123/publish

# 新方式
PATCH /api/v1/articles/123
{"status": "published"}
```

#### 文章下线
```bash
# 旧方式
POST /api/articles/123/offline

# 新方式
PATCH /api/v1/articles/123
{"status": "offline"}
```

#### 批量操作
```bash
# 旧方式
POST /api/articles/batch-publish
{"ids": [1,2,3]}

# 新方式
PATCH /api/v1/articles
{"ids": [1,2,3], "data": {"status": "published"}}
```

---

## 兼容性说明

- 旧API将保持运行6个月
- 新旧API可同时使用
- 推荐新项目使用新API
- 旧项目逐步迁移到新API
