# 错误处理使用指南

## 目录

1. [概述](#概述)
2. [核心组件](#核心组件)
3. [快速开始](#快速开始)
4. [使用方式](#使用方式)
5. [错误代码规范](#错误代码规范)
6. [最佳实践](#最佳实践)
7. [响应格式](#响应格式)
8. [迁移指南](#迁移指南)

---

## 概述

本系统实现了统一的错误处理机制，提供用户友好的错误消息，同时在生产环境隐藏技术细节。

### 核心特性

- ✅ **统一的错误代码**：所有业务错误使用统一的错误代码
- ✅ **友好的错误消息**：自动转换为用户可理解的中文消息
- ✅ **开发/生产环境分离**：开发环境显示详细错误，生产环境隐藏技术细节
- ✅ **结构化错误数据**：支持附加错误详情
- ✅ **类型化异常**：使用自定义异常类区分不同类型的错误

---

## 核心组件

### 1. ErrorCode 类

位置：`app\common\ErrorCode.php`

定义所有错误代码和对应的友好消息。

```php
use app\common\ErrorCode;

// 获取错误消息
$message = ErrorCode::getMessage(ErrorCode::ARTICLE_NOT_FOUND);
// 返回: "文章不存在"

// 检查错误代码是否存在
$exists = ErrorCode::exists(1300);
// 返回: true
```

### 2. BusinessException 类

位置：`app\exception\BusinessException.php`

业务异常类，用于抛出业务逻辑错误。

```php
use app\exception\BusinessException;
use app\common\ErrorCode;

// 抛出文章不存在异常
throw new BusinessException(ErrorCode::ARTICLE_NOT_FOUND);

// 抛出自定义消息
throw new BusinessException(
    ErrorCode::ARTICLE_NOT_FOUND,
    "ID为{$id}的文章不存在"
);

// 附加错误数据
throw new BusinessException(
    ErrorCode::VALIDATION_FAILED,
    "数据验证失败",
    ['title' => '标题不能为空', 'content' => '内容太短']
);
```

### 3. ExceptionHandle 类

位置：`app\ExceptionHandle.php`

全局异常处理器，将异常转换为统一的JSON响应。

---

## 快速开始

### 第1步：抛出业务异常

**旧方式**（不推荐）：
```php
throw new \Exception('文章不存在');
```

**新方式**（推荐）：
```php
use app\exception\BusinessException;
use app\common\ErrorCode;

throw new BusinessException(ErrorCode::ARTICLE_NOT_FOUND);
```

### 第2步：查看响应

**开发环境** (`APP_DEBUG=true`)：
```json
{
  "code": 1300,
  "message": "文章不存在",
  "data": null,
  "debug": {
    "file": "/path/to/ArticleController.php",
    "line": 123,
    "trace": "..."
  }
}
```

**生产环境** (`APP_DEBUG=false`)：
```json
{
  "code": 1300,
  "message": "文章不存在",
  "data": null
}
```

---

## 使用方式

### 方式1：使用错误代码

```php
use app\exception\BusinessException;
use app\common\ErrorCode;

class ArticleController
{
    public function read($id)
    {
        $article = Article::find($id);

        if (!$article) {
            throw new BusinessException(ErrorCode::ARTICLE_NOT_FOUND);
        }

        return Response::success($article);
    }
}
```

### 方式2：使用快捷方法

```php
use app\exception\BusinessException;

class ArticleController
{
    public function delete($id)
    {
        $article = Article::find($id);

        if (!$article) {
            BusinessException::notFound('文章不存在');
        }

        if (!$this->canDelete($article)) {
            BusinessException::permissionDenied('您没有权限删除此文章');
        }

        $article->delete();
        return Response::success();
    }
}
```

**可用快捷方法**：
- `BusinessException::authFailed()` - 认证失败
- `BusinessException::permissionDenied()` - 权限拒绝
- `BusinessException::notFound()` - 资源不存在
- `BusinessException::alreadyExists()` - 资源已存在
- `BusinessException::invalidParams()` - 参数无效
- `BusinessException::validationFailed()` - 验证失败
- `BusinessException::operationFailed()` - 操作失败
- `BusinessException::systemError()` - 系统错误

### 方式3：附加错误数据

```php
use app\exception\BusinessException;
use app\common\ErrorCode;

class ArticleController
{
    public function create($data)
    {
        $validator = Validator::make($data, [
            'title' => 'required|max:100',
            'content' => 'required|min:50',
        ]);

        if ($validator->fails()) {
            throw new BusinessException(
                ErrorCode::VALIDATION_FAILED,
                "数据验证失败",
                $validator->errors()->toArray()
            );
        }

        // ...
    }
}
```

响应：
```json
{
  "code": 1002,
  "message": "数据验证失败",
  "data": {
    "title": ["标题不能为空"],
    "content": ["内容至少50个字符"]
  }
}
```

---

## 错误代码规范

### 错误代码分类

错误代码采用4位数字，按功能模块分类：

| 范围 | 类型 | 说明 |
|------|------|------|
| 0 | 成功 | 操作成功 |
| 1000-1099 | 通用错误 | 参数错误、系统错误等 |
| 1100-1199 | 认证相关 | 登录、token等 |
| 1200-1299 | 用户相关 | 用户管理 |
| 1300-1399 | 文章相关 | 文章CRUD |
| 1400-1499 | 分类相关 | 分类管理 |
| 1500-1599 | 标签相关 | 标签管理 |
| 1600-1699 | 文件上传 | 文件操作 |
| 1700-1799 | 站点相关 | 多站点管理 |
| 1800-1899 | 模板相关 | 模板管理 |
| 1900-1999 | 评论相关 | 评论管理 |
| 2000-2099 | 专题相关 | 专题管理 |
| 2100-2199 | 回收站 | 回收站操作 |
| 2200-2299 | 数据库 | 数据库错误 |
| 2300-2399 | 缓存 | 缓存操作 |

### 添加新错误代码

在 `app\common\ErrorCode.php` 中添加：

```php
class ErrorCode
{
    // 1. 定义常量
    const CUSTOM_ERROR = 9999;

    // 2. 添加到 $messages 数组
    private static $messages = [
        // ...
        self::CUSTOM_ERROR => '自定义错误消息',
    ];
}
```

---

## 最佳实践

### 1. 明确错误类型

```php
// ❌ 不好：使用通用异常
throw new \Exception('操作失败');

// ✅ 好：使用具体的错误代码
throw new BusinessException(ErrorCode::ARTICLE_PUBLISH_FAILED);
```

### 2. 提供有意义的消息

```php
// ❌ 不好：技术性消息
throw new BusinessException(ErrorCode::DB_UPDATE_FAILED, "UPDATE failed");

// ✅ 好：用户友好消息
throw new BusinessException(
    ErrorCode::ARTICLE_PUBLISH_FAILED,
    "文章发布失败，请检查必填字段是否完整"
);
```

### 3. 附加上下文信息

```php
// ❌ 不好：没有上下文
throw new BusinessException(ErrorCode::INVALID_PARAMS);

// ✅ 好：提供详细的验证错误
throw new BusinessException(
    ErrorCode::INVALID_PARAMS,
    "请求参数错误",
    [
        'page' => '页码必须大于0',
        'page_size' => '每页数量必须在1-100之间'
    ]
);
```

### 4. 在合适的层级抛出异常

```php
class ArticleService
{
    public function publish($id)
    {
        $article = Article::find($id);

        // ✅ 在服务层抛出业务异常
        if (!$article) {
            throw new BusinessException(ErrorCode::ARTICLE_NOT_FOUND);
        }

        // 业务逻辑校验
        if ($article->status === Article::STATUS_PUBLISHED) {
            throw new BusinessException(
                ErrorCode::ARTICLE_ALREADY_EXISTS,
                "文章已发布，无需重复操作"
            );
        }

        $article->status = Article::STATUS_PUBLISHED;
        $article->save();

        return $article;
    }
}

class ArticleController
{
    public function publish($id)
    {
        try {
            // 控制器调用服务，让异常自动传播
            $article = $this->articleService->publish($id);
            return Response::success($article);
        } catch (BusinessException $e) {
            // 一般不需要在控制器捕获，让全局异常处理器处理
            // 除非需要特殊处理或记录日志
            throw $e;
        }
    }
}
```

### 5. 区分用户错误和系统错误

```php
// 用户错误（4xx）- 用户可以修正
throw new BusinessException(ErrorCode::INVALID_PARAMS, "参数错误");
throw new BusinessException(ErrorCode::PERMISSION_DENIED, "权限不足");

// 系统错误（5xx）- 需要开发人员修复
throw new BusinessException(ErrorCode::DB_CONNECTION_FAILED, "数据库连接失败");
throw new BusinessException(ErrorCode::CACHE_ERROR, "缓存服务异常");
```

---

## 响应格式

### 成功响应

```json
{
  "code": 0,
  "message": "操作成功",
  "data": {
    "id": 1,
    "title": "文章标题"
  }
}
```

### 业务异常响应

```json
{
  "code": 1300,
  "message": "文章不存在",
  "data": null
}
```

### 验证失败响应

```json
{
  "code": 1002,
  "message": "数据验证失败",
  "data": {
    "title": ["标题不能为空"],
    "content": ["内容至少50个字符"]
  }
}
```

### 系统错误响应（生产环境）

```json
{
  "code": 1007,
  "message": "系统错误，请联系管理员",
  "data": null
}
```

### 系统错误响应（开发环境）

```json
{
  "code": 1007,
  "message": "SQLSTATE[42S02]: Base table or view not found",
  "data": null,
  "debug": {
    "exception": "PDOException",
    "file": "/path/to/file.php",
    "line": 123,
    "trace": ["...", "..."]
  }
}
```

---

## 迁移指南

### 从旧代码迁移

#### 1. 替换通用异常

**旧代码**:
```php
if (!$article) {
    throw new \Exception('文章不存在');
}

if ($article->user_id != $currentUserId) {
    throw new \Exception('无权操作');
}

if (empty($data['title'])) {
    throw new \Exception('标题不能为空');
}
```

**新代码**:
```php
use app\exception\BusinessException;
use app\common\ErrorCode;

if (!$article) {
    throw new BusinessException(ErrorCode::ARTICLE_NOT_FOUND);
}

if ($article->user_id != $currentUserId) {
    BusinessException::permissionDenied();
}

if (empty($data['title'])) {
    throw new BusinessException(ErrorCode::ARTICLE_TITLE_REQUIRED);
}
```

#### 2. 替换Response::error()

**旧代码**:
```php
return Response::error('操作失败', 500);
```

**新代码**:
```php
throw new BusinessException(ErrorCode::OPERATION_FAILED);
```

#### 3. 替换手动JSON响应

**旧代码**:
```php
return json([
    'code' => 1,
    'message' => '文章不存在',
    'data' => null
], 404);
```

**新代码**:
```php
throw new BusinessException(ErrorCode::ARTICLE_NOT_FOUND);
```

### 迁移检查清单

- [ ] 搜索所有 `throw new \Exception`
- [ ] 替换为对应的 `BusinessException`
- [ ] 搜索所有 `Response::error()`
- [ ] 替换为抛出异常
- [ ] 检查所有手动构建的错误响应
- [ ] 验证错误消息的用户友好性
- [ ] 测试开发和生产环境的响应差异

---

## 常见问题

### Q: 什么时候使用BusinessException？

A: 所有业务逻辑错误都应该使用BusinessException，例如：
- 资源不存在
- 权限不足
- 数据验证失败
- 操作冲突（如重复创建）
- 业务规则违反

### Q: 什么时候使用普通Exception？

A: 系统级错误可以使用普通Exception，但会被转换为系统错误响应：
- 数据库连接失败
- 文件系统错误
- 第三方服务异常

建议：即使是系统错误，也使用BusinessException(ErrorCode::SYSTEM_ERROR)以保持一致性。

### Q: 如何在前端显示错误？

A: 前端可以根据错误代码显示不同的提示：

```javascript
try {
  const response = await api.getArticle(id)
  // 处理成功
} catch (error) {
  const { code, message, data } = error.response.data

  if (code === 1300) {
    // 文章不存在，跳转到404页面
    router.push('/404')
  } else if (code === 1002) {
    // 验证失败，显示具体字段错误
    showFieldErrors(data)
  } else {
    // 显示通用错误消息
    showMessage(message)
  }
}
```

### Q: 如何添加多语言支持？

A: 修改 `ErrorCode::getMessage()` 方法，根据语言参数返回不同消息：

```php
public static function getMessage(int $code, string $lang = 'zh-cn'): string
{
    $messages = [
        'zh-cn' => self::$messages,
        'en-us' => self::$messagesEn,
    ];

    return $messages[$lang][$code] ?? '操作失败';
}
```

---

## 参考资料

- ErrorCode类：`backend/app/common/ErrorCode.php`
- BusinessException类：`backend/app/exception/BusinessException.php`
- ExceptionHandle类：`backend/app/ExceptionHandle.php`

**更新日期**: 2025-11-26
