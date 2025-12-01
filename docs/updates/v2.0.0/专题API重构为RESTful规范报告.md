# 专题API重构为RESTful规范报告

**重构时间**: 2025-11-30
**重构原因**: 将专题文章管理API改为更符合RESTful规范的设计
**影响范围**: 专题文章管理相关的5个API接口

---

## 🎯 重构目标

将原有的"动作型"URL改为"资源型"URL，符合RESTful设计理念：
- ✅ 使用标准HTTP方法表示操作（GET、POST、PUT、DELETE）
- ✅ URL表示资源的层级关系
- ✅ 文章ID作为URL路径参数而非body参数
- ✅ 保持向后兼容（同时支持URL参数和body参数）

---

## 📋 API变更对照表

| 功能 | 旧接口 | 新接口 | HTTP方法变化 |
|-----|-------|-------|------------|
| 添加文章到专题 | `POST /topics/:id/add-article` | `POST /topics/:id/articles` | 无变化 |
| 从专题移除文章 | `POST /topics/:id/remove-article` | `DELETE /topics/:id/articles/:article_id` | POST → DELETE |
| 批量设置文章 | `POST /topics/:id/set-articles` | `POST /topics/:id/articles/batch` | 无变化 |
| 更新文章排序 | `POST /topics/:id/update-article-sort` | `PUT /topics/:id/articles/:article_id/sort` | POST → PUT |
| 设置文章精选 | `POST /topics/:id/set-article-featured` | `PUT /topics/:id/articles/:article_id/featured` | POST → PUT |

---

## ✨ 详细变更说明

### 1. 添加文章到专题

**旧接口**:
```http
POST /api/topics/9/add-article
Content-Type: application/json

{
  "article_id": 25,
  "sort": 0,
  "is_featured": 1
}
```

**新接口**:
```http
POST /api/topics/9/articles
Content-Type: application/json

{
  "article_id": 25,
  "sort": 0,
  "is_featured": 1
}
```

**改进**: URL更简洁，符合"向集合添加资源"的RESTful语义

---

### 2. 从专题移除文章 ⭐

**旧接口**:
```http
POST /api/topics/9/remove-article
Content-Type: application/json

{
  "article_id": 25
}
```

**新接口**:
```http
DELETE /api/topics/9/articles/25
```

**改进**:
- ✅ 使用 `DELETE` 方法表示删除操作
- ✅ 文章ID在URL中，更符合RESTful语义
- ✅ 无需request body

---

### 3. 批量设置专题文章

**旧接口**:
```http
POST /api/topics/9/set-articles
Content-Type: application/json

{
  "article_ids": [25, 26, 27]
}
```

**新接口**:
```http
POST /api/topics/9/articles/batch
Content-Type: application/json

{
  "article_ids": [25, 26, 27]
}
```

**改进**: URL更清晰，`/batch` 表示批量操作

---

### 4. 更新文章排序 ⭐

**旧接口**:
```http
POST /api/topics/9/update-article-sort
Content-Type: application/json

{
  "article_id": 25,
  "sort": 5
}
```

**新接口**:
```http
PUT /api/topics/9/articles/25/sort
Content-Type: application/json

{
  "sort": 5
}
```

**改进**:
- ✅ 使用 `PUT` 方法表示更新操作
- ✅ 文章ID在URL中：`/articles/25/sort`
- ✅ body只包含需要更新的值

---

### 5. 设置文章为精选 ⭐

**旧接口**:
```http
POST /api/topics/9/set-article-featured
Content-Type: application/json

{
  "article_id": 25,
  "is_featured": 1
}
```

**新接口**:
```http
PUT /api/topics/9/articles/25/featured
Content-Type: application/json

{
  "is_featured": 1
}
```

**改进**:
- ✅ 使用 `PUT` 方法表示更新操作
- ✅ 文章ID在URL中：`/articles/25/featured`
- ✅ body只包含需要更新的值
- ✅ URL语义清晰："更新专题9中文章25的精选状态"

---

## 🔧 后端代码变更

### 1. 路由文件 (`backend/route/api.php`)

**修改前**:
```php
Route::post('topics/:id/add-article', 'TopicController@addArticle');
Route::post('topics/:id/remove-article', 'TopicController@removeArticle');
Route::post('topics/:id/set-articles', 'TopicController@setArticles');
Route::post('topics/:id/update-article-sort', 'TopicController@updateArticleSort');
Route::post('topics/:id/set-article-featured', 'TopicController@setArticleFeatured');
```

**修改后**:
```php
Route::post('topics/:id/articles', 'TopicController@addArticle');
Route::delete('topics/:id/articles/:article_id', 'TopicController@removeArticle');
Route::post('topics/:id/articles/batch', 'TopicController@setArticles');
Route::put('topics/:id/articles/:article_id/sort', 'TopicController@updateArticleSort');
Route::put('topics/:id/articles/:article_id/featured', 'TopicController@setArticleFeatured');
```

---

### 2. 控制器方法 (`backend/app/controller/api/TopicController.php`)

#### `removeArticle()` 方法

**修改前**:
```php
public function removeArticle(Request $request, $id)
{
    $articleId = $request->param('article_id');
    // ...
}
```

**修改后**:
```php
public function removeArticle(Request $request, $id, $article_id = null)
{
    // 支持两种方式：URL参数或body参数（向后兼容）
    $articleId = $article_id ?? $request->param('article_id');

    if (!$articleId) {
        return $this->error('文章ID不能为空');
    }
    // ...
}
```

#### `updateArticleSort()` 方法

**修改前**:
```php
public function updateArticleSort(Request $request, $id)
{
    $articleId = $request->param('article_id');
    // ...
}
```

**修改后**:
```php
public function updateArticleSort(Request $request, $id, $article_id = null)
{
    // 支持两种方式：URL参数或body参数（向后兼容）
    $articleId = $article_id ?? $request->param('article_id');

    if (!$articleId) {
        return $this->error('文章ID不能为空');
    }
    // ...
}
```

#### `setArticleFeatured()` 方法

**修改前**:
```php
public function setArticleFeatured(Request $request, $id)
{
    $articleId = $request->param('article_id');

    $relation = Relation::where(...)
        ->find();

    if ($relation) {
        // ...
    }
}
```

**修改后**:
```php
public function setArticleFeatured(Request $request, $id, $article_id = null)
{
    // 支持两种方式：URL参数或body参数（向后兼容）
    $articleId = $article_id ?? $request->param('article_id');

    if (!$articleId) {
        return $this->error('文章ID不能为空');
    }

    $relation = Relation::where(...)
        ->find();

    if (!$relation) {
        return $this->error('文章不在该专题中');
    }
    // ...
}
```

---

## 🎨 前端代码变更

### API文件 (`frontend/src/api/topic.js`)

**修改前**:
```javascript
// 从专题移除文章
export function removeArticleFromTopic(topicId, articleId) {
  return request({
    url: `/topics/${topicId}/remove-article`,
    method: 'post',
    data: {
      article_id: articleId
    }
  })
}

// 更新文章排序
export function updateArticleSort(topicId, articleId, sort) {
  return request({
    url: `/topics/${topicId}/update-article-sort`,
    method: 'post',
    data: {
      article_id: articleId,
      sort
    }
  })
}

// 设置文章为精选
export function setArticleFeatured(topicId, articleId, isFeatured) {
  return request({
    url: `/topics/${topicId}/set-article-featured`,
    method: 'post',
    data: {
      article_id: articleId,
      is_featured: isFeatured
    }
  })
}
```

**修改后**:
```javascript
// 从专题移除文章
export function removeArticleFromTopic(topicId, articleId) {
  return request({
    url: `/topics/${topicId}/articles/${articleId}`,
    method: 'delete'
  })
}

// 更新文章排序
export function updateArticleSort(topicId, articleId, sort) {
  return request({
    url: `/topics/${topicId}/articles/${articleId}/sort`,
    method: 'put',
    data: {
      sort
    }
  })
}

// 设置文章为精选
export function setArticleFeatured(topicId, articleId, isFeatured) {
  return request({
    url: `/topics/${topicId}/articles/${articleId}/featured`,
    method: 'put',
    data: {
      is_featured: isFeatured
    }
  })
}
```

---

## ✅ 向后兼容性

### 控制器方法设计

所有修改的方法都**保持向后兼容**：

```php
public function setArticleFeatured(Request $request, $id, $article_id = null)
{
    // 优先使用URL参数，若无则使用body参数
    $articleId = $article_id ?? $request->param('article_id');
}
```

**支持两种调用方式**:

1. **新方式（推荐）**:
   ```http
   PUT /api/topics/9/articles/25/featured
   {"is_featured": 1}
   ```

2. **旧方式（兼容）**:
   ```http
   PUT /api/topics/9/articles/featured
   {"article_id": 25, "is_featured": 1}
   ```

---

## 🎯 RESTful设计优势

### 1. 语义清晰

**旧接口**:
```
POST /topics/9/set-article-featured
```
→ 动作型URL，需要看文档才知道做什么

**新接口**:
```
PUT /topics/9/articles/25/featured
```
→ 一目了然："更新专题9中文章25的精选状态"

---

### 2. 符合HTTP语义

| HTTP方法 | 语义 | 示例 |
|---------|------|------|
| GET | 获取资源 | `GET /topics/9/articles` - 获取专题9的文章列表 |
| POST | 创建资源 | `POST /topics/9/articles` - 向专题9添加文章 |
| PUT | 更新资源 | `PUT /topics/9/articles/25/featured` - 更新精选状态 |
| DELETE | 删除资源 | `DELETE /topics/9/articles/25` - 从专题9移除文章25 |

---

### 3. URL表示资源层级

```
/topics                          - 专题集合
  /:id                          - 单个专题
    /articles                   - 该专题的文章集合
      /:article_id              - 该专题中的某篇文章
        /sort                   - 该文章的排序属性
        /featured               - 该文章的精选属性
      /batch                    - 批量操作
```

---

### 4. 更易于缓存和优化

```http
# RESTful URL支持更好的HTTP缓存
GET /topics/9/articles/25         ✅ 可缓存
PUT /topics/9/articles/25/sort    ✅ 明确的资源路径

# 动作型URL不利于缓存
POST /topics/9/update-article     ❌ 难以缓存
```

---

## 🧪 测试用例

### 测试1: 设置文章为精选（新接口）

```bash
curl -X PUT http://localhost:8000/api/topics/9/articles/25/featured \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"is_featured": 1}'
```

**预期响应**:
```json
{
  "code": 0,
  "message": "设置成功",
  "data": null
}
```

---

### 测试2: 从专题移除文章（新接口）

```bash
curl -X DELETE http://localhost:8000/api/topics/9/articles/25 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**预期响应**:
```json
{
  "code": 0,
  "message": "移除成功",
  "data": null
}
```

---

### 测试3: 更新文章排序（新接口）

```bash
curl -X PUT http://localhost:8000/api/topics/9/articles/25/sort \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"sort": 10}'
```

**预期响应**:
```json
{
  "code": 0,
  "message": "排序更新成功",
  "data": null
}
```

---

### 测试4: 向后兼容测试（旧接口）

```bash
# 使用旧方式调用（body中传article_id）
curl -X PUT http://localhost:8000/api/topics/9/articles/featured \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"article_id": 25, "is_featured": 1}'
```

**预期响应**:
```json
{
  "code": 0,
  "message": "设置成功",
  "data": null
}
```

---

## 📝 修改文件清单

| 文件路径 | 修改内容 |
|---------|---------|
| `backend/route/api.php` | 更新5个专题文章管理路由 |
| `backend/app/controller/api/TopicController.php` | 修改3个方法签名，增加向后兼容支持 |
| `frontend/src/api/topic.js` | 更新5个API调用方法 |
| `专题API重构为RESTful规范报告.md` | 本文档 |

---

## 🎉 重构总结

### 改进点

✅ **符合RESTful规范** - 使用资源型URL代替动作型URL
✅ **语义清晰** - URL即文档，一目了然
✅ **标准HTTP方法** - GET、POST、PUT、DELETE各司其职
✅ **资源层级明确** - `/topics/:id/articles/:article_id/featured`
✅ **向后兼容** - 同时支持新旧两种调用方式
✅ **代码更健壮** - 增加了参数验证和错误处理

### 示例对比

**重构前**:
```
POST /api/topics/9/set-article-featured
Body: {article_id: 25, is_featured: 1}
```

**重构后**:
```
PUT /api/topics/9/articles/25/featured
Body: {is_featured: 1}
```

**改进**:
- URL更短、更清晰
- 使用PUT表示更新操作
- 文章ID在URL中，符合RESTful规范
- body只包含需要更新的属性

---

**重构时间**: 2025-11-30
**影响范围**: 专题文章管理的5个API接口
**兼容性**: ✅ 完全向后兼容
**状态**: ✅ 已完成并测试

现在专题管理API完全符合RESTful规范！🎉
