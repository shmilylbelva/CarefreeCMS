# PHP 单元测试实施方案

## 1. 项目现状

### 1.1 当前测试情况

- ❌ 未配置 PHPUnit
- ❌ 无测试用例
- ❌ 无测试覆盖率统计
- 📁 项目有 129 个 PHP 文件，但全部无测试

### 1.2 测试框架选择

**PHPUnit** - PHP 官方标准测试框架
- ✅ 成熟稳定（已有20+年历史）
- ✅ 支持 PHP 8.0+
- ✅ 集成便利
- ✅ 集成 ThinkPHP 的首选

## 2. PHPUnit 安装与配置

### 2.1 安装依赖

```bash
composer require --dev phpunit/phpunit ^10.0
composer require --dev phpunit/php-code-coverage
```

### 2.2 项目结构

```
project/
├── backend/
│   ├── app/
│   │   ├── controller/
│   │   ├── model/
│   │   ├── service/
│   │   └── ...
│   ├── config/
│   ├── tests/
│   │   ├── Feature/
│   │   │   ├── ControllerTest.php
│   │   │   └── AuthTest.php
│   │   ├── Unit/
│   │   │   ├── ServiceTest.php
│   │   │   └── ValidatorTest.php
│   │   └── bootstrap.php
│   └── phpunit.xml
```

## 3. PHPUnit 配置文件

### 3.1 `backend/phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.0/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         beStrictAboutOutputDuringTests="true"
         beStrictAboutTestsThatDoNotTestAnything="true"
         beStrictAboutTodoTestedCode="true"
         beStrictAboutUselessTests="true"
         cacheDirectory=".phpunit.cache"
         colors="true"
         displayDetailsOnIncompleteTests="true"
         displayDetailsOnSkippedTests="true"
         displayDetailsOnTestsThatTriggerErrors="true"
         displayDetailsOnTestsThatTriggerWarnings="true"
         displayDetailsOnTestsThatTriggerNotices="true"
         displayDetailsOnTestsThatTriggerDeprecations="true"
         failOnRisky="true"
         failOnWarning="true"
         stopOnDefect="false"
         verbose="true">
    <testsuites>
        <testsuite name="Unit Tests">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature Tests">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            <directory suffix="Exception.php">./app</directory>
        </exclude>
    </source>
    <coverage processUncoveredFiles="true"
              pathCoverage="false"
              ignoreDeprecatedCodeUnits="true"
              disableCodeCoverageIgnore="false">
        <report>
            <html outputDirectory=".phpunit.coverage"/>
            <text outputFile="php://stdout" showUncoveredFiles="false"/>
            <clover outputFile=".phpunit.coverage/coverage.xml"/>
        </report>
        <include>
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            <directory>./app/event.php</directory>
            <directory>./app/common.php</directory>
        </exclude>
    </coverage>
    <php>
        <ini name="display_errors" value="On"/>
        <ini name="display_startup_errors" value="On"/>
        <ini name="error_reporting" value="-1"/>
        <ini name="xdebug.mode" value="coverage"/>
    </php>
</phpunit>
```

### 3.2 `backend/tests/bootstrap.php`

```php
<?php
declare(strict_types=1);

error_reporting(E_ALL);

// 加载 composer 自动加载
require __DIR__ . '/../vendor/autoload.php';

// 设置测试环境变量
putenv('APP_DEBUG=true');
putenv('APP_ENV=testing');
```

## 4. 单元测试实例

### 4.1 模型测试 - `tests/Unit/Model/ArticleTest.php`

```php
<?php
declare(strict_types=1);

namespace app\tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use app\model\Article;

/**
 * 文章模型单元测试
 *
 * @covers \app\model\Article
 */
class ArticleTest extends TestCase
{
    /**
     * 测试获取已发布文章
     */
    public function testGetPublishedArticles(): void
    {
        // 准备测试数据
        $articles = Article::where('status', 1)
            ->select();

        // 断言
        $this->assertIsArray($articles->toArray());
    }

    /**
     * 测试文章标题长度验证
     */
    public function testArticleTitleValidation(): void
    {
        $article = new Article();
        $article->title = '';

        // 应该导致验证失败
        $this->assertFalse(strlen($article->title) > 0);
    }

    /**
     * 测试文章软删除
     */
    public function testArticleSoftDelete(): void
    {
        // 测试删除后是否仍存在
        $article = Article::find(1);
        if ($article) {
            $article->delete();
            // 验证软删除标记
            $this->assertNotNull($article->deleted_at ?? null);
        }
    }
}
```

### 4.2 服务测试 - `tests/Unit/Service/ArticleServiceTest.php`

```php
<?php
declare(strict_types=1);

namespace app\tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use app\service\ArticleService;

/**
 * 文章服务单元测试
 *
 * @covers \app\service\ArticleService
 */
class ArticleServiceTest extends TestCase
{
    private ArticleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ArticleService();
    }

    /**
     * 测试获取文章列表
     */
    public function testGetArticleList(): void
    {
        $result = $this->service->getList(1, 20);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
    }

    /**
     * 测试创建文章
     */
    public function testCreateArticle(): void
    {
        $data = [
            'title' => '测试文章',
            'category_id' => 1,
            'content' => '这是一篇测试文章',
            'user_id' => 1,
        ];

        $result = $this->service->create($data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
    }

    /**
     * 测试更新文章
     */
    public function testUpdateArticle(): void
    {
        $data = [
            'title' => '更新标题',
            'content' => '更新内容',
        ];

        $result = $this->service->update(1, $data);

        $this->assertTrue($result);
    }

    /**
     * 测试删除文章
     */
    public function testDeleteArticle(): void
    {
        $result = $this->service->delete(1);

        $this->assertTrue($result);
    }
}
```

### 4.3 验证器测试 - `tests/Unit/Validator/ArticleValidatorTest.php`

```php
<?php
declare(strict_types=1);

namespace app\tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use app\validate\Article;
use think\exception\ValidateException;

/**
 * 文章验证器单元测试
 *
 * @covers \app\validate\Article
 */
class ArticleValidatorTest extends TestCase
{
    private Article $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new Article();
    }

    /**
     * 测试标题验证 - 成功
     */
    public function testTitleValidationSuccess(): void
    {
        $data = [
            'title' => '这是一个有效的标题',
        ];

        $result = $this->validator->check($data);

        $this->assertTrue($result);
    }

    /**
     * 测试标题验证 - 失败（为空）
     */
    public function testTitleValidationFailEmpty(): void
    {
        $data = [
            'title' => '',
        ];

        $this->expectException(ValidateException::class);

        $this->validator->failException(true)->check($data);
    }

    /**
     * 测试标题验证 - 失败（过长）
     */
    public function testTitleValidationFailTooLong(): void
    {
        $data = [
            'title' => str_repeat('a', 300),
        ];

        $this->expectException(ValidateException::class);

        $this->validator->failException(true)->check($data);
    }
}
```

### 4.4 API 功能测试 - `tests/Feature/ArticleControllerTest.php`

```php
<?php
declare(strict_types=1);

namespace app\tests\Feature;

use PHPUnit\Framework\TestCase;
use think\facade\Http;

/**
 * 文章 API 功能测试
 *
 * @covers \app\controller\api\Article
 */
class ArticleControllerTest extends TestCase
{
    /**
     * 测试获取文章列表 API
     */
    public function testGetArticleList(): void
    {
        // 发送 GET 请求
        $response = $this->get('/backend/article/list', [
            'page' => 1,
            'page_size' => 20,
        ]);

        // 断言响应
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    /**
     * 测试创建文章 API
     */
    public function testCreateArticle(): void
    {
        $data = [
            'title' => '测试文章标题',
            'category_id' => 1,
            'content' => '这是测试文章内容',
        ];

        $response = $this->post('/backend/article/create', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * 测试更新文章 API
     */
    public function testUpdateArticle(): void
    {
        $data = [
            'title' => '更新的标题',
            'content' => '更新的内容',
        ];

        $response = $this->put('/backend/article/update/1', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * 测试删除文章 API
     */
    public function testDeleteArticle(): void
    {
        $response = $this->delete('/backend/article/delete/1');

        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * 测试未授权访问
     */
    public function testUnauthorizedAccess(): void
    {
        $response = $this->post('/backend/article/create', []);

        $this->assertEquals(401, $response->getStatusCode());
    }
}
```

## 5. 测试运行

### 5.1 运行所有测试

```bash
cd backend
./vendor/bin/phpunit
```

### 5.2 运行特定测试套件

```bash
# 运行单元测试
./vendor/bin/phpunit --testsuite "Unit Tests"

# 运行功能测试
./vendor/bin/phpunit --testsuite "Feature Tests"
```

### 5.3 生成覆盖率报告

```bash
# 生成 HTML 覆盖率报告
./vendor/bin/phpunit --coverage-html=.phpunit.coverage

# 生成 Clover 覆盖率报告
./vendor/bin/phpunit --coverage-clover=.phpunit.coverage/coverage.xml
```

### 5.4 监听模式（持续测试）

```bash
./vendor/bin/phpunit --watch
```

## 6. Composer 脚本

更新 `backend/composer.json`：

```json
{
    "scripts": {
        "test": "phpunit",
        "test:unit": "phpunit --testsuite=\"Unit Tests\"",
        "test:feature": "phpunit --testsuite=\"Feature Tests\"",
        "test:coverage": "phpunit --coverage-html=.phpunit.coverage",
        "test:watch": "phpunit --watch",
        "test:ci": "phpunit --coverage-clover=coverage.xml"
    }
}
```

## 7. CI/CD 集成

### 7.1 GitHub Actions 示例

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v2

      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: pdo, mysql
          coverage: xdebug

      - uses: "ramsey/composer-install@v2"

      - name: Run tests
        run: composer test:ci

      - name: Upload coverage
        uses: codecov/codecov-action@v2
```

## 8. 测试覆盖率目标

| 类型 | 目标 | 优先级 |
|------|------|--------|
| 总体覆盖率 | ≥ 80% | HIGH |
| 关键路径 | ≥ 95% | CRITICAL |
| 模型层 | ≥ 85% | HIGH |
| 服务层 | ≥ 80% | HIGH |
| 控制器层 | ≥ 70% | MEDIUM |
| 验证器 | ≥ 90% | HIGH |

## 9. 最佳实践

### 9.1 测试命名规范

- ✅ `testShouldReturnTrueWhenConditionMet`
- ✅ `testThrowsExceptionWhenInputInvalid`
- ✅ `testCalculatesTotalCorrectly`
- ❌ `test1`
- ❌ `testData`

### 9.2 断言最佳实践

```php
// ✅ 好的做法
$this->assertTrue($result);
$this->assertEquals(10, $value);
$this->assertIsArray($data);
$this->assertInstanceOf(Article::class, $article);
$this->assertThrows(\Exception::class, fn() => throw new \Exception());

// ❌ 不好的做法
$this->assertTrue($result == true);
$this->assertEquals(10, $value, '');
if ($data) { } // 没有断言
```

### 9.3 Mock 与 Stub

```php
// Mock 对象
$mockService = $this->createMock(ArticleService::class);
$mockService->expects($this->once())
    ->method('get')
    ->willReturn(['id' => 1]);

// Stub 对象
$stubService = $this->createStub(ArticleService::class);
$stubService->method('get')->willReturn(['id' => 1]);
```

## 10. 测试文件检查清单

- [ ] 测试类继承 `PHPUnit\Framework\TestCase`
- [ ] 测试方法以 `test` 开头
- [ ] 每个测试只测试一个功能
- [ ] 使用 `setUp()` 初始化共享资源
- [ ] 使用 `tearDown()` 清理资源
- [ ] 使用明确的断言
- [ ] 有清晰的测试数据
- [ ] 有注释说明测试意图

## 11. 相关文件

需要创建以下文件：
1. ✅ `backend/phpunit.xml` - PHPUnit 配置
2. ✅ `backend/tests/bootstrap.php` - 测试引导文件
3. `backend/tests/Unit/Model/ArticleTest.php` - 示例模型测试
4. `backend/tests/Unit/Service/ArticleServiceTest.php` - 示例服务测试
5. `backend/tests/Feature/ArticleControllerTest.php` - 示例功能测试

## 12. 进度跟踪

### 第一阶段：基础设置
- [ ] 安装 PHPUnit
- [ ] 创建配置文件
- [ ] 创建测试目录结构

### 第二阶段：关键模块测试
- [ ] 模型层测试（Article, Category, Tag）
- [ ] 验证器测试
- [ ] 服务层测试

### 第三阶段：API 测试
- [ ] 控制器功能测试
- [ ] API 端点测试
- [ ] 权限测试

### 第四阶段：持续集成
- [ ] 配置 CI/CD
- [ ] 自动化测试
- [ ] 覆盖率报告

---

**更新时间**：2025-10-24
**优先级**：HIGH
**预计工作量**：8-12小时
