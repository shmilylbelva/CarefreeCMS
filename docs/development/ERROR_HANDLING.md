# 错误处理统一方案

## 1. 现状分析

### 1.1 当前错误处理问题

| 问题 | 严重级别 | 影响范围 |
|------|---------|---------|
| 异常处理不一致 | HIGH | 整个项目 |
| 错误日志不规范 | HIGH | 调试困难 |
| API 响应格式不统一 | HIGH | 前端对接困难 |
| 缺少自定义异常类 | MEDIUM | 错误识别困难 |
| 异常信息暴露内部信息 | CRITICAL | 安全隐患 |
| 没有异常重试机制 | MEDIUM | 容错能力弱 |

### 1.2 项目现状代码

```php
// ❌ 不规范的错误处理
try {
    $article = Article::find($id);
} catch (\Exception $e) {
    die($e->getMessage());  // 暴露内部信息！
}

// ❌ 不规范的响应
return [
    'status' => 'error',
    'message' => '数据库错误: ' . $e->getMessage()
];
```

## 2. 自定义异常体系

### 2.1 异常类层级

```
\Exception (PHP 内置)
├── \app\exception\ApiException (API 异常基类)
│   ├── \app\exception\ValidationException (验证异常)
│   ├── \app\exception\AuthenticationException (认证异常)
│   ├── \app\exception\AuthorizationException (授权异常)
│   ├── \app\exception\ResourceNotFoundException (资源不存在)
│   ├── \app\exception\ConflictException (冲突异常)
│   ├── \app\exception\DatabaseException (数据库异常)
│   ├── \app\exception\ServiceException (服务异常)
│   └── \app\exception\InternalServerException (服务器异常)
└── \app\exception\BusinessException (业务异常)
    ├── \app\exception\InvalidParameterException (无效参数)
    ├── \app\exception\OperationFailedException (操作失败)
    └── \app\exception\ResourceLimitException (资源限制)
```

### 2.2 基础异常类 - `app/exception/ApiException.php`

```php
<?php
declare(strict_types=1);

namespace app\exception;

/**
 * API 异常基类
 *
 * 所有 API 相关异常的父类，定义统一的异常处理方式
 *
 * @package app\exception
 * @author  Your Name
 * @version 1.0.0
 */
abstract class ApiException extends \Exception
{
    /**
     * HTTP 状态码
     *
     * @var int
     */
    protected int $httpCode = 500;

    /**
     * 业务错误码
     *
     * @var int
     */
    protected int $errorCode = 1000;

    /**
     * 是否需要记录日志
     *
     * @var bool
     */
    protected bool $needsLogging = true;

    /**
     * 构造方法
     *
     * @param string $message 错误消息
     * @param int $code 错误码
     * @param \Throwable|null $previous 上一个异常
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        if ($code !== 0) {
            $this->errorCode = $code;
        }
    }

    /**
     * 获取 HTTP 状态码
     *
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * 获取业务错误码
     *
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * 是否需要记录日志
     *
     * @return bool
     */
    public function isNeedsLogging(): bool
    {
        return $this->needsLogging;
    }

    /**
     * 转换为 API 响应
     *
     * @return array
     */
    public function toResponse(): array
    {
        return [
            'code' => $this->getErrorCode(),
            'message' => $this->getMessage(),
            'data' => null,
        ];
    }
}
```

### 2.3 具体异常类

**验证异常** - `app/exception/ValidationException.php`：

```php
<?php
declare(strict_types=1);

namespace app\exception;

/**
 * 验证异常
 *
 * 当请求参数验证失败时抛出
 *
 * @package app\exception
 */
class ValidationException extends ApiException
{
    protected int $httpCode = 422;
    protected int $errorCode = 2001;
    protected bool $needsLogging = false;

    /**
     * 验证错误详情
     *
     * @var array
     */
    private array $errors = [];

    /**
     * 设置验证错误
     *
     * @param array $errors 错误数组
     * @return self
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * 获取验证错误
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 转换为 API 响应
     *
     * @return array
     */
    public function toResponse(): array
    {
        return [
            'code' => $this->getErrorCode(),
            'message' => $this->getMessage(),
            'errors' => $this->errors,
            'data' => null,
        ];
    }
}
```

**认证异常** - `app/exception/AuthenticationException.php`：

```php
<?php
declare(strict_types=1);

namespace app\exception;

/**
 * 认证异常
 *
 * 当用户未登录或 token 无效时抛出
 *
 * @package app\exception
 */
class AuthenticationException extends ApiException
{
    protected int $httpCode = 401;
    protected int $errorCode = 4001;
    protected bool $needsLogging = false;

    public function __construct(
        string $message = 'Unauthorized',
        int $code = 4001,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
```

**授权异常** - `app/exception/AuthorizationException.php`：

```php
<?php
declare(strict_types=1);

namespace app\exception;

/**
 * 授权异常
 *
 * 当用户权限不足无法执行操作时抛出
 *
 * @package app\exception
 */
class AuthorizationException extends ApiException
{
    protected int $httpCode = 403;
    protected int $errorCode = 4003;
    protected bool $needsLogging = false;

    public function __construct(
        string $message = 'Forbidden',
        int $code = 4003,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
```

**资源不存在异常** - `app/exception/ResourceNotFoundException.php`：

```php
<?php
declare(strict_types=1);

namespace app\exception;

/**
 * 资源不存在异常
 *
 * 当请求的资源不存在时抛出
 *
 * @package app\exception
 */
class ResourceNotFoundException extends ApiException
{
    protected int $httpCode = 404;
    protected int $errorCode = 4004;
    protected bool $needsLogging = false;

    public function __construct(
        string $message = 'Not Found',
        int $code = 4004,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
```

**数据库异常** - `app/exception/DatabaseException.php`：

```php
<?php
declare(strict_types=1);

namespace app\exception;

/**
 * 数据库异常
 *
 * 数据库操作失败时抛出
 *
 * @package app\exception
 */
class DatabaseException extends ApiException
{
    protected int $httpCode = 500;
    protected int $errorCode = 5001;
    protected bool $needsLogging = true;

    public function __construct(
        string $message = 'Database error',
        int $code = 5001,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
```

**服务异常** - `app/exception/ServiceException.php`：

```php
<?php
declare(strict_types=1);

namespace app\exception;

/**
 * 服务异常
 *
 * 业务服务执行失败时抛出
 *
 * @package app\exception
 */
class ServiceException extends ApiException
{
    protected int $httpCode = 500;
    protected int $errorCode = 5002;
    protected bool $needsLogging = true;

    public function __construct(
        string $message = 'Service error',
        int $code = 5002,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
```

## 3. 异常处理配置

### 3.1 全局异常处理 - `app/ExceptionHandle.php`

```php
<?php
declare(strict_types=1);

namespace app;

use app\exception\ApiException;
use app\common\Logger;
use app\common\Response;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Response as ThinkResponse;
use Throwable;

/**
 * 全局异常处理器
 *
 * @package app
 * @author  Your Name
 * @version 1.0.0
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录的异常列表
     *
     * @var array
     */
    protected $dontReport = [
        // \think\exception\HttpException::class,
        \app\exception\ValidationException::class,
        \app\exception\AuthenticationException::class,
        \app\exception\AuthorizationException::class,
        \app\exception\ResourceNotFoundException::class,
    ];

    /**
     * 渲染异常为响应
     *
     * @param \think\Request $request 请求对象
     * @param Throwable $e 异常对象
     * @return ThinkResponse
     */
    public function render($request, Throwable $e): ThinkResponse
    {
        // API 异常处理
        if ($e instanceof ApiException) {
            return $this->handleApiException($e);
        }

        // ThinkPHP 验证异常
        if ($e instanceof ValidateException) {
            return $this->handleValidationException($e);
        }

        // HTTP 异常
        if ($e instanceof HttpException) {
            return $this->handleHttpException($e);
        }

        // 其他异常
        return $this->handleException($e);
    }

    /**
     * 处理 API 异常
     *
     * @param ApiException $e API 异常
     * @return ThinkResponse
     */
    private function handleApiException(ApiException $e): ThinkResponse
    {
        // 记录异常
        if ($e->isNeedsLogging()) {
            Logger::error($e->getMessage(), [
                'code' => $e->getErrorCode(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        // 返回 API 响应
        return Response::error(
            $e->getMessage(),
            $e->getErrorCode(),
            $e->getHttpCode(),
            $e->toResponse()['errors'] ?? []
        );
    }

    /**
     * 处理验证异常
     *
     * @param ValidateException $e 验证异常
     * @return ThinkResponse
     */
    private function handleValidationException(ValidateException $e): ThinkResponse
    {
        $message = $e->getError();

        Logger::warning('Validation failed', ['message' => $message]);

        return Response::error($message, 2001, 422);
    }

    /**
     * 处理 HTTP 异常
     *
     * @param HttpException $e HTTP 异常
     * @return ThinkResponse
     */
    private function handleHttpException(HttpException $e): ThinkResponse
    {
        // 404 异常
        if ($e->getStatusCode() === 404) {
            Logger::warning('Resource not found', [
                'url' => request()->url(),
                'method' => request()->method(),
            ]);

            return Response::error('Not Found', 4004, 404);
        }

        // 405 异常
        if ($e->getStatusCode() === 405) {
            return Response::error('Method Not Allowed', 4005, 405);
        }

        // 其他 HTTP 异常
        return Response::error(
            $e->getMessage(),
            $e->getStatusCode(),
            $e->getStatusCode()
        );
    }

    /**
     * 处理普通异常
     *
     * @param Throwable $e 异常对象
     * @return ThinkResponse
     */
    private function handleException(Throwable $e): ThinkResponse
    {
        // 记录异常
        Logger::error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        // 开发环境返回详细信息
        if (env('APP_DEBUG', false)) {
            return Response::error(
                $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine(),
                5000,
                500
            );
        }

        // 生产环境返回通用错误
        return Response::error('Internal Server Error', 5000, 500);
    }
}
```

## 4. 错误处理最佳实践

### 4.1 在控制器中的使用

```php
<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\exception\ValidationException;
use app\exception\AuthorizationException;
use app\exception\ResourceNotFoundException;
use app\exception\ServiceException;
use app\common\Response;
use app\model\Article;
use think\Request;

class Article extends BaseController
{
    /**
     * 获取文章详情
     *
     * @param Request $request 请求对象
     * @return array
     * @throws ResourceNotFoundException 文章不存在
     */
    public function detail(Request $request): array
    {
        try {
            $id = (int)$request->param('id');

            // 验证参数
            if ($id <= 0) {
                throw new ValidationException('Invalid article ID', 2001);
            }

            // 获取文章
            $article = Article::find($id);

            if (!$article) {
                throw new ResourceNotFoundException('Article not found', 4004);
            }

            return Response::success($article->toArray());
        } catch (ValidationException|ResourceNotFoundException $e) {
            // 这些异常会被全局处理器捕获并转换为 API 响应
            throw $e;
        } catch (\Exception $e) {
            // 捕获其他异常并转换为服务异常
            throw new ServiceException('Failed to get article', 5002, $e);
        }
    }

    /**
     * 更新文章
     *
     * @param Request $request 请求对象
     * @return array
     * @throws AuthorizationException 权限不足
     * @throws ResourceNotFoundException 文章不存在
     */
    public function update(Request $request): array
    {
        try {
            $id = (int)$request->param('id');
            $data = $request->post();

            // 验证权限
            $article = Article::find($id);

            if (!$article) {
                throw new ResourceNotFoundException('Article not found', 4004);
            }

            if ($article->user_id !== $this->userId && !$this->isAdmin()) {
                throw new AuthorizationException('You cannot update this article', 4003);
            }

            // 验证数据
            $this->validate($data, [
                'title' => 'require|string|max:200',
                'content' => 'require|string',
            ]);

            // 更新文章
            $article->update($data);

            return Response::success(null, 'Article updated successfully');
        } catch (ValidationException|AuthorizationException|ResourceNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ServiceException('Failed to update article', 5002, $e);
        }
    }

    /**
     * 删除文章
     *
     * @param Request $request 请求对象
     * @return array
     */
    public function delete(Request $request): array
    {
        try {
            $id = (int)$request->param('id');

            $article = Article::find($id);

            if (!$article) {
                throw new ResourceNotFoundException('Article not found', 4004);
            }

            $article->delete();

            return Response::success(null, 'Article deleted successfully');
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ServiceException('Failed to delete article', 5002, $e);
        }
    }
}
```

### 4.2 在服务层中的使用

```php
<?php
declare(strict_types=1);

namespace app\service;

use app\exception\ServiceException;
use app\exception\InvalidParameterException;
use app\model\Article;

class ArticleService
{
    /**
     * 创建文章
     *
     * @param array $data 文章数据
     * @return int 文章ID
     * @throws InvalidParameterException 参数不合法
     * @throws ServiceException 服务异常
     */
    public function create(array $data): int
    {
        try {
            // 验证必填字段
            if (empty($data['title']) || empty($data['content'])) {
                throw new InvalidParameterException('Title and content are required', 3001);
            }

            // 验证分类存在
            if (empty($data['category_id'])) {
                throw new InvalidParameterException('Category ID is required', 3001);
            }

            // 生成 slug
            $data['slug'] = $this->generateSlug($data['title']);

            // 创建文章
            $article = Article::create($data);

            return $article->id;
        } catch (InvalidParameterException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ServiceException('Failed to create article', 5002, $e);
        }
    }

    /**
     * 生成 URL slug
     *
     * @param string $title 标题
     * @return string
     * @throws ServiceException 生成失败
     */
    private function generateSlug(string $title): string
    {
        try {
            // 生成逻辑...
            return strtolower(str_replace(' ', '-', $title));
        } catch (\Exception $e) {
            throw new ServiceException('Failed to generate slug', 5002, $e);
        }
    }
}
```

## 5. 错误码规范

### 5.1 错误码划分

| 范围 | 类型 | 示例 |
|------|------|------|
| 2000-2999 | 验证错误 | 2001-参数验证失败 |
| 3000-3999 | 业务错误 | 3001-无效参数 |
| 4000-4999 | 客户端错误 | 4001-未授权 |
| 5000-5999 | 服务器错误 | 5001-数据库错误 |

### 5.2 具体错误码

```php
// 验证错误 (2000-2999)
const ERROR_VALIDATION = 2001;
const ERROR_INVALID_EMAIL = 2002;
const ERROR_PASSWORD_TOO_SHORT = 2003;

// 业务错误 (3000-3999)
const ERROR_ARTICLE_NOT_FOUND = 3001;
const ERROR_INVALID_PARAMETER = 3002;
const ERROR_OPERATION_FAILED = 3003;

// 客户端错误 (4000-4999)
const ERROR_UNAUTHORIZED = 4001;
const ERROR_TOKEN_EXPIRED = 4002;
const ERROR_FORBIDDEN = 4003;
const ERROR_NOT_FOUND = 4004;
const ERROR_METHOD_NOT_ALLOWED = 4005;
const ERROR_CONFLICT = 4009;

// 服务器错误 (5000-5999)
const ERROR_DATABASE = 5001;
const ERROR_SERVICE = 5002;
const ERROR_INTERNAL_SERVER = 5000;
```

## 6. 响应格式统一

### 6.1 成功响应

```json
{
    "code": 0,
    "message": "success",
    "data": {
        "id": 1,
        "title": "Article Title"
    }
}
```

### 6.2 分页响应

```json
{
    "code": 0,
    "message": "success",
    "data": [
        { "id": 1, "title": "Article 1" },
        { "id": 2, "title": "Article 2" }
    ],
    "pagination": {
        "total": 100,
        "page": 1,
        "page_size": 20,
        "total_pages": 5
    }
}
```

### 6.3 错误响应

```json
{
    "code": 4004,
    "message": "Article not found",
    "data": null,
    "errors": []
}
```

### 6.4 验证错误响应

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

## 7. 检查清单

- [ ] 创建所有自定义异常类
- [ ] 更新全局异常处理器
- [ ] 在所有控制器中使用异常
- [ ] 在所有服务中使用异常
- [ ] 编写异常处理单元测试
- [ ] 统一 API 响应格式
- [ ] 定义完整的错误码
- [ ] 记录异常日志
- [ ] 不暴露内部错误信息
- [ ] 生产环境隐藏 debug 信息

---

**更新时间**：2025-10-24
**优先级**：HIGH
**预计工作量**：8-10小时
