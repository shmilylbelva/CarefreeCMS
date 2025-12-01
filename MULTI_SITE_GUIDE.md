# 多站点功能使用指南

## 目录

1. [系统概述](#系统概述)
2. [核心组件](#核心组件)
3. [快速开始](#快速开始)
4. [SiteScoped Trait 详解](#sitescoped-trait-详解)
5. [SiteQuery 门面使用](#sitequery-门面使用)
6. [迁移指南](#迁移指南)
7. [最佳实践](#最佳实践)
8. [常见场景](#常见场景)
9. [测试策略](#测试策略)
10. [故障排查](#故障排查)

---

## 系统概述

本CMS系统支持多站点数据隔离，允许在同一数据库中管理多个独立站点的数据。核心特性：

- **自动站点过滤**：查询自动添加 `site_id` 条件，无需手动干预
- **全局查询作用域**：基于 ThinkPHP 的查询事件系统
- **灵活的查询方法**：提供明确语义的方法切换不同站点查询
- **统一的站点上下文**：通过中间件和应用容器统一管理当前站点

### 架构图

```
请求 → MultiSite中间件 → 设置站点上下文 → 应用容器
                                          ↓
                            模型查询 → SiteScoped Trait → 自动添加 site_id 过滤
```

---

## 核心组件

### 1. SiteScoped Trait

位置：`app\traits\SiteScoped.php`

全局查询作用域trait，自动为所有查询添加站点过滤。

**核心方法**：
- `withoutSiteScope()` - 禁用站点过滤
- `forSite($siteId)` - 指定站点查询
- `forCurrentSite()` - 当前站点查询（明确意图）
- `forAllSites()` - 所有站点查询（明确意图）

### 2. SiteModel 基类

位置：`app\model\SiteModel.php`

所有需要站点隔离的模型应继承此类。

```php
abstract class SiteModel extends Model
{
    use SiteScoped;

    // 向后兼容的属性
    protected $multiSiteEnabled = true;
}
```

### 3. MultiSite 中间件

位置：`app\middleware\MultiSite.php`

负责识别当前请求的站点，并将站点信息注入到应用容器。

**核心功能**：
```php
// 识别站点
$site = SiteContextService::identifySite();

// 注入到应用容器（供全局作用域使用）
app()->bind('current_site_id', $site->id);
app()->bind('current_site', $site);
```

### 4. SiteQuery 门面

位置：`app\facade\SiteQuery.php`

便捷的站点查询门面类。

**核心方法**：
```php
use app\facade\SiteQuery;

SiteQuery::current();           // 获取当前站点ID
SiteQuery::ids();               // 获取所有站点ID列表
SiteQuery::get($siteId);        // 获取指定站点
SiteQuery::getByCode($code);    // 根据站点代码获取
SiteQuery::enabled();           // 获取所有启用的站点
SiteQuery::exists($siteId);     // 检查站点是否存在
SiteQuery::isEnabled($siteId);  // 检查站点是否启用
```

### 5. SiteQueryService 服务

位置：`app\service\SiteQueryService.php`

站点查询和管理的底层服务，提供数据过滤和验证功能。

---

## 快速开始

### 1. 创建支持多站点的模型

```php
<?php
namespace app\model;

/**
 * 文章模型
 */
class Article extends SiteModel
{
    protected $name = 'articles';

    // 其他模型配置...
}
```

**仅需两步**：
1. 继承 `SiteModel`
2. 确保数据表有 `site_id` 字段

### 2. 基本查询（自动站点过滤）

```php
// 默认只查询当前站点的文章
$articles = Article::select();

// 分页查询（自动过滤）
$articles = Article::paginate(10);

// 条件查询（自动过滤）
$article = Article::where('status', 1)->find();
```

### 3. 跨站点查询

```php
// 查询所有站点的文章（明确意图）
$allArticles = Article::forAllSites()->select();

// 查询指定站点的文章
$site1Articles = Article::forSite(1)->select();

// 查询多个站点的文章
$multiSiteArticles = Article::forSite([1, 2, 3])->select();

// 查询当前站点（明确意图，推荐在共享代码中使用）
$currentArticles = Article::forCurrentSite()->select();
```

---

## SiteScoped Trait 详解

### 工作原理

`SiteScoped` trait 通过 ThinkPHP 的查询事件系统，在每次查询前自动注入站点条件：

```php
// 模型初始化时注册事件
protected static function init()
{
    parent::init();

    static::beforeSelect(function (Query $query) {
        self::applySiteScope($query);
    });
}

// 应用站点作用域
protected static function applySiteScope(Query $query)
{
    // 检查是否应该应用站点过滤
    if (!static::shouldApplySiteScope($query)) {
        return;
    }

    // 获取当前站点ID
    $siteId = static::getCurrentSiteId();

    if ($siteId !== null) {
        $query->where("{$table}.site_id", $siteId);
    }
}
```

### 站点ID获取优先级

1. **应用容器**：`app()->get('current_site_id')`（由 MultiSite 中间件注入）
2. **SiteContextService**：`SiteContextService::getSiteId()`

### 禁用站点过滤的方式

#### 方法1：临时禁用（推荐）

```php
// 使用 withoutSiteScope() - 向后兼容
$allArticles = Article::withoutSiteScope()->select();
```

#### 方法2：使用 forAllSites()（推荐，更语义化）

```php
// 明确表示查询所有站点
$allArticles = Article::forAllSites()->select();
```

#### 方法3：全局禁用（不推荐）

```php
// 全局禁用站点过滤（慎用！）
Article::disableSiteScope();
$allArticles = Article::select();

// 记得重新启用
Article::enableSiteScope();
```

### 自定义站点字段名

如果你的表使用非标准的站点字段名（不是 `site_id`）：

```php
class CustomModel extends SiteModel
{
    protected $siteField = 'custom_site_id';
}
```

---

## SiteQuery 门面使用

### 获取站点信息

```php
use app\facade\SiteQuery;

// 获取当前站点ID
$currentSiteId = SiteQuery::current();  // 返回: int|null

// 获取所有站点ID（仅启用）
$siteIds = SiteQuery::ids();  // 返回: [1, 2, 3]

// 获取所有站点ID（包括禁用）
$allSiteIds = SiteQuery::ids(false);

// 获取指定站点对象
$site = SiteQuery::get(1);  // 返回: Site 对象

// 根据站点代码获取
$site = SiteQuery::getByCode('main');

// 获取主站
$mainSite = SiteQuery::main();

// 获取所有启用的站点
$enabledSites = SiteQuery::enabled();
```

### 站点验证

```php
// 检查站点是否存在
if (SiteQuery::exists($siteId)) {
    // 站点存在
}

// 检查站点是否启用
if (SiteQuery::isEnabled($siteId)) {
    // 站点已启用
}

// 检查是否为当前站点
if (SiteQuery::isCurrent($siteId)) {
    // 是当前站点
}
```

### 切换站点

```php
// 切换到指定站点
SiteQuery::setCurrent($siteId);

// 后续所有查询都会使用新的站点ID
$articles = Article::select();  // 查询新站点的文章
```

---

## 迁移指南

### 从旧方法迁移到新方法

#### 1. 查询所有站点

**旧方法**（不推荐）：
```php
$allArticles = Article::withoutSiteScope()->select();
```

**新方法**（推荐）：
```php
$allArticles = Article::forAllSites()->select();
```

**理由**：`forAllSites()` 更语义化，明确表达了查询所有站点的意图。

#### 2. 查询指定站点

**旧方法**（手动添加条件）：
```php
$articles = Article::withoutSiteScope()
    ->where('site_id', $siteId)
    ->select();
```

**新方法**（使用 forSite）：
```php
$articles = Article::forSite($siteId)->select();
```

#### 3. 查询当前站点

**旧方法**（依赖自动过滤）：
```php
// 没有明确表达意图
$articles = Article::select();
```

**新方法**（明确意图）：
```php
// 在共享代码或可能被其他站点调用的代码中
$articles = Article::forCurrentSite()->select();
```

### 迁移检查清单

- [ ] 搜索代码中所有的 `withoutSiteScope()` 调用
- [ ] 评估每个调用的意图（是否真的需要查询所有站点）
- [ ] 将 `withoutSiteScope()` 替换为 `forAllSites()` 或 `forSite($siteId)`
- [ ] 检查手动添加 `site_id` 条件的查询，改用 `forSite()`
- [ ] 在共享代码中使用 `forCurrentSite()` 明确意图

---

## 最佳实践

### 1. 明确查询意图

在编写查询时，始终明确表达你的查询意图：

```php
// ✅ 好：明确表示查询当前站点
$articles = Article::forCurrentSite()->select();

// ✅ 好：明确表示查询所有站点
$allArticles = Article::forAllSites()->select();

// ✅ 好：明确表示查询指定站点
$site1Articles = Article::forSite(1)->select();

// ⚠️ 可以接受：在控制器中依赖自动过滤
$articles = Article::select();  // 仅在控制器等明确的请求上下文中使用

// ❌ 不好：在服务类或共享代码中依赖自动过滤
class ArticleService
{
    public function getList()
    {
        // 意图不明确，可能引起误解
        return Article::select();
    }
}
```

### 2. 服务类和共享代码

在服务类中，始终明确传递站点ID参数：

```php
class ArticleService
{
    /**
     * 获取文章列表
     * @param int|null $siteId 站点ID，null表示当前站点
     */
    public function getList(?int $siteId = null)
    {
        if ($siteId === null) {
            // 明确使用当前站点
            return Article::forCurrentSite()->select();
        }

        // 使用指定站点
        return Article::forSite($siteId)->select();
    }

    /**
     * 获取所有站点的文章统计
     */
    public function getAllSitesStats()
    {
        // 明确表示查询所有站点
        return Article::forAllSites()
            ->field('site_id, count(*) as count')
            ->group('site_id')
            ->select();
    }
}
```

### 3. 关联查询

在关联查询中，站点过滤会自动应用：

```php
// 文章模型
class Article extends SiteModel
{
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}

// 查询时，关联的 category 也会自动应用站点过滤
$article = Article::with('category')->find(1);
// $article->category 自动限制在当前站点的分类
```

**特殊情况**：如果需要跨站点关联：

```php
class Article extends SiteModel
{
    /**
     * 关联分类（跨站点）
     */
    public function categoryAcrossSites()
    {
        return $this->belongsTo(Category::class, 'category_id')
            ->bind([
                'category_name' => 'name'
            ]);
    }
}

// 使用时需要禁用站点过滤
$article = Article::with(['categoryAcrossSites' => function($query) {
    $query->withoutSiteScope();
}])->find(1);
```

### 4. JOIN 查询

在 JOIN 查询中，确保使用表名限定字段：

```php
$articles = Article::alias('a')
    ->join('categories c', 'a.category_id = c.id')
    ->field('a.*, c.name as category_name')
    ->select();

// SiteScoped 会自动添加：a.site_id = ? 和 c.site_id = ?
```

### 5. 统计和聚合

```php
// 当前站点的文章数量
$count = Article::count();

// 所有站点的文章数量
$totalCount = Article::forAllSites()->count();

// 按站点统计文章数量
$stats = Article::forAllSites()
    ->field('site_id, count(*) as count')
    ->group('site_id')
    ->select();
```

### 6. 软删除与站点过滤

软删除和站点过滤可以同时使用：

```php
class Article extends SiteModel
{
    use SoftDelete;
}

// 查询当前站点的未删除文章
$articles = Article::select();

// 查询当前站点的所有文章（包括已删除）
$allArticles = Article::withTrashed()->select();

// 查询所有站点的未删除文章
$crossSiteArticles = Article::forAllSites()->select();

// 查询所有站点的所有文章（包括已删除）
$everything = Article::forAllSites()->withTrashed()->select();
```

---

## 常见场景

### 场景1：管理后台 - 超级管理员查看所有站点数据

```php
namespace app\controller\admin;

class ArticleController
{
    /**
     * 文章列表（超级管理员可切换站点）
     */
    public function index()
    {
        $siteId = input('site_id', '');

        if ($siteId === '') {
            // 查看所有站点
            $articles = Article::forAllSites()
                ->with('site')
                ->paginate(20);
        } else {
            // 查看指定站点
            $articles = Article::forSite($siteId)
                ->paginate(20);
        }

        return json($articles);
    }
}
```

### 场景2：前台展示 - 仅显示当前站点数据

```php
namespace app\controller\index;

class ArticleController
{
    /**
     * 文章列表（仅当前站点）
     */
    public function index()
    {
        // 依赖自动过滤即可，或明确使用 forCurrentSite()
        $articles = Article::where('status', 1)
            ->paginate(20);

        return json($articles);
    }
}
```

### 场景3：数据同步 - 从一个站点复制到另一个站点

```php
namespace app\service;

class ArticleSyncService
{
    /**
     * 从源站点复制文章到目标站点
     */
    public function copyArticle(int $articleId, int $sourceSiteId, int $targetSiteId)
    {
        // 查询源站点的文章
        $sourceArticle = Article::forSite($sourceSiteId)->find($articleId);

        if (!$sourceArticle) {
            throw new \Exception('源文章不存在');
        }

        // 创建新文章到目标站点
        $newArticle = Article::create([
            'title' => $sourceArticle->title,
            'content' => $sourceArticle->content,
            'site_id' => $targetSiteId,
            // ... 其他字段
        ]);

        return $newArticle;
    }
}
```

### 场景4：内容共享 - 多站点共享某些数据

```php
namespace app\service;

class ContentShareService
{
    /**
     * 共享文章到多个站点
     */
    public function shareToSites(int $articleId, array $targetSiteIds)
    {
        // 获取源文章
        $article = Article::forCurrentSite()->find($articleId);

        if (!$article) {
            throw new \Exception('文章不存在');
        }

        $sharedArticles = [];

        foreach ($targetSiteIds as $siteId) {
            // 检查目标站点是否已存在
            $exists = Article::forSite($siteId)
                ->where('original_id', $articleId)
                ->find();

            if ($exists) {
                continue;  // 已存在，跳过
            }

            // 创建共享文章
            $sharedArticle = Article::create([
                'title' => $article->title,
                'content' => $article->content,
                'site_id' => $siteId,
                'original_id' => $articleId,
                'is_shared' => 1,
                // ... 其他字段
            ]);

            $sharedArticles[] = $sharedArticle;
        }

        return $sharedArticles;
    }
}
```

### 场景5：站点数据统计

```php
namespace app\service;

use app\facade\SiteQuery;

class SiteStatsService
{
    /**
     * 获取所有站点的数据统计
     */
    public function getAllSitesStats()
    {
        $sites = SiteQuery::enabled();
        $stats = [];

        foreach ($sites as $site) {
            $stats[] = [
                'site_id' => $site['id'],
                'site_name' => $site['site_name'],
                'article_count' => Article::forSite($site['id'])->count(),
                'category_count' => Category::forSite($site['id'])->count(),
                'tag_count' => Tag::forSite($site['id'])->count(),
            ];
        }

        return $stats;
    }

    /**
     * 获取当前站点的统计
     */
    public function getCurrentSiteStats()
    {
        return [
            'site_id' => SiteQuery::current(),
            'article_count' => Article::count(),  // 自动过滤当前站点
            'category_count' => Category::count(),
            'tag_count' => Tag::count(),
        ];
    }
}
```

---

## 测试策略

### 1. 单元测试

```php
namespace tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use app\model\Article;
use app\facade\SiteQuery;

class ArticleSiteTest extends TestCase
{
    public function testAutoSiteFilter()
    {
        // 设置当前站点
        app()->bind('current_site_id', 1);

        // 查询应该自动过滤
        $query = Article::db();
        $sql = $query->buildSql();

        $this->assertStringContainsString('site_id = 1', $sql);
    }

    public function testForAllSites()
    {
        app()->bind('current_site_id', 1);

        // 查询所有站点
        $query = Article::forAllSites()->db();
        $sql = $query->buildSql();

        // 不应该包含 site_id 条件
        $this->assertStringNotContainsString('site_id', $sql);
    }

    public function testForSite()
    {
        // 查询指定站点
        $query = Article::forSite(2)->db();
        $sql = $query->buildSql();

        $this->assertStringContainsString('site_id = 2', $sql);
    }
}
```

### 2. 功能测试

```php
namespace tests\Feature;

use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    public function testIndexReturnsCurrentSiteArticles()
    {
        // 设置当前站点
        $this->setSite(1);

        // 创建测试数据
        $article1 = Article::create(['title' => 'Article 1', 'site_id' => 1]);
        $article2 = Article::create(['title' => 'Article 2', 'site_id' => 2]);

        // 请求文章列表
        $response = $this->get('/api/articles');

        // 应该只返回站点1的文章
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['title' => 'Article 1']);
        $response->assertJsonMissing(['title' => 'Article 2']);
    }
}
```

### 3. 集成测试

```php
namespace tests\Integration;

use Tests\TestCase;
use app\service\ArticleService;

class ArticleServiceTest extends TestCase
{
    public function testGetListWithDifferentSites()
    {
        $service = new ArticleService();

        // 创建不同站点的文章
        Article::create(['title' => 'Site 1 Article', 'site_id' => 1]);
        Article::create(['title' => 'Site 2 Article', 'site_id' => 2]);

        // 获取站点1的文章
        $articles = $service->getList(1);
        $this->assertCount(1, $articles);
        $this->assertEquals('Site 1 Article', $articles[0]['title']);

        // 获取站点2的文章
        $articles = $service->getList(2);
        $this->assertCount(1, $articles);
        $this->assertEquals('Site 2 Article', $articles[0]['title']);
    }
}
```

---

## 故障排查

### 问题1：查询返回空结果

**症状**：明明数据库有数据，但查询返回空。

**原因**：站点过滤生效，但当前站点ID不正确。

**排查步骤**：

1. 检查当前站点ID
```php
dump(app()->get('current_site_id'));  // 应该返回正确的站点ID
dump(SiteQuery::current());           // 应该返回正确的站点ID
```

2. 检查数据库中的 site_id
```sql
SELECT * FROM articles WHERE id = 1;
-- 检查 site_id 字段的值是否与当前站点ID匹配
```

3. 临时禁用站点过滤验证
```php
$article = Article::forAllSites()->find($id);
dump($article->site_id);  // 查看文章实际的站点ID
```

**解决方案**：
- 确保 MultiSite 中间件已启用
- 检查站点识别逻辑是否正确
- 验证数据的 site_id 是否正确

### 问题2：JOIN 查询时站点过滤失效

**症状**：JOIN 查询时，关联表的数据没有被站点过滤。

**原因**：ThinkPHP 的全局作用域只对主模型生效，不会自动应用到 JOIN 的表。

**解决方案**：

```php
// ❌ 错误：关联表没有站点过滤
$articles = Article::alias('a')
    ->join('categories c', 'a.category_id = c.id')
    ->select();

// ✅ 正确：手动添加关联表的站点过滤
$siteId = SiteQuery::current();
$articles = Article::alias('a')
    ->join('categories c', 'a.category_id = c.id')
    ->where('c.site_id', $siteId)  // 手动添加
    ->select();

// ✅ 更好：使用关联查询代替 JOIN
$articles = Article::with('category')->select();
```

### 问题3：统计数据不准确

**症状**：统计的数据量与预期不符。

**原因**：忘记考虑站点过滤，或错误地使用了 `forAllSites()`。

**排查步骤**：

1. 确认统计的范围
```php
// 当前站点的统计
$count = Article::count();

// 所有站点的统计
$totalCount = Article::forAllSites()->count();
```

2. 检查查询SQL
```php
$query = Article::db();
echo $query->buildSql();  // 查看实际执行的SQL
```

### 问题4：软删除与站点过滤冲突

**症状**：使用 `withTrashed()` 后，站点过滤失效。

**原因**：某些情况下，多个作用域可能冲突。

**解决方案**：

```php
// ✅ 正确：明确指定查询范围
$articles = Article::forCurrentSite()
    ->withTrashed()
    ->select();

// 或者
$articles = Article::withTrashed()
    ->where('site_id', SiteQuery::current())
    ->select();
```

### 问题5：性能问题

**症状**：查询速度慢，N+1 问题。

**原因**：站点过滤可能在关联查询中导致额外的查询。

**解决方案**：

1. 使用预加载
```php
// ❌ N+1 问题
$articles = Article::select();
foreach ($articles as $article) {
    echo $article->category->name;  // 每次都查询一次
}

// ✅ 预加载
$articles = Article::with('category')->select();
foreach ($articles as $article) {
    echo $article->category->name;  // 不会产生额外查询
}
```

2. 检查索引
```sql
-- 确保 site_id 有索引
ALTER TABLE articles ADD INDEX idx_site_id (site_id);

-- 复合索引（常用查询条件）
ALTER TABLE articles ADD INDEX idx_site_status (site_id, status);
```

3. 使用查询监控
```php
// 启用 SQL 日志
\think\facade\Db::listen(function($sql, $time) {
    if ($time > 100) {  // 超过100ms
        \think\facade\Log::warning("Slow query: {$sql} ({$time}ms)");
    }
});
```

### 问题6：在命令行或队列中使用

**症状**：在命令行或队列中查询数据为空。

**原因**：没有 HTTP 请求上下文，MultiSite 中间件不会执行，没有设置当前站点。

**解决方案**：

```php
namespace app\command;

use think\console\Command;
use app\facade\SiteQuery;

class ArticleSync extends Command
{
    protected function execute(Input $input, Output $output)
    {
        // 方法1：明确指定站点
        $articles = Article::forSite(1)->select();

        // 方法2：手动设置当前站点
        SiteQuery::setCurrent(1);
        $articles = Article::select();

        // 方法3：遍历所有站点
        $siteIds = SiteQuery::ids();
        foreach ($siteIds as $siteId) {
            SiteQuery::setCurrent($siteId);
            $articles = Article::select();
            // 处理文章...
        }
    }
}
```

---

## 总结

多站点功能的核心原则：

1. **默认隔离**：默认情况下，所有查询都自动限制在当前站点
2. **明确意图**：需要跨站点查询时，使用明确的方法（`forAllSites()`, `forSite()`）
3. **统一管理**：通过 SiteModel 和 SiteScoped trait 统一管理
4. **灵活切换**：提供多种方法灵活切换查询范围

**记住**：
- 使用 `forAllSites()` 代替 `withoutSiteScope()`
- 在服务类中明确传递站点ID参数
- 在共享代码中使用 `forCurrentSite()` 明确意图
- JOIN 查询时注意手动添加关联表的站点过滤
- 在命令行和队列中手动设置当前站点

---

## 参考资料

- `app\traits\SiteScoped.php` - 站点作用域 trait
- `app\model\SiteModel.php` - 站点模型基类
- `app\middleware\MultiSite.php` - 多站点中间件
- `app\facade\SiteQuery.php` - 站点查询门面
- `app\service\SiteQueryService.php` - 站点查询服务
- `app\service\SiteContextService.php` - 站点上下文服务

**更新日期**: 2025-11-26
