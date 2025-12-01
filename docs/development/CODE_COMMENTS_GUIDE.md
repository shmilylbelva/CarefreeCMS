# 代码注释完善指南

## 1. 现状分析

### 1.1 注释缺陷

检查项目代码发现：

| 问题 | 严重级别 | 数量 | 示例 |
|------|---------|------|------|
| 缺少类注释 | HIGH | 多 | 控制器、服务类 |
| 缺少方法注释 | HIGH | 多 | API 端点 |
| 缺少参数说明 | MEDIUM | 多 | 方法参数 |
| 缺少返回值说明 | MEDIUM | 多 | 方法返回 |
| 缺少异常说明 | MEDIUM | 多 | 可能抛出异常 |
| 业务逻辑注释不足 | MEDIUM | 部分 | 复杂算法 |

### 1.2 代码示例（改进前后）

**❌ 改进前**：

```php
class Article extends BaseController
{
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $title = $request->get('title', '');
        $categoryId = $request->get('category_id', '');

        $query = ArticleModel::with(['category', 'user', 'tags']);

        if (!empty($title)) {
            $query->where('title', 'like', '%' . $title . '%');
        }
        // ... 更多代码
    }
}
```

## 2. PHPDoc 标准

### 2.1 文件头部注释

```php
<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use think\Request;

/**
 * 文章管理API控制器
 *
 * 提供文章的增删改查功能，包括：
 * - 文章列表查询（支持分页、搜索、筛选）
 * - 文章详情获取
 * - 文章创建/更新/删除
 * - 文章版本控制
 * - 文章软删除恢复
 *
 * @package app\controller\api
 * @author  Your Name <your@email.com>
 * @copyright  2024 Company
 * @license    Apache 2.0
 * @version    1.0.0
 * @since      1.0.0
 */
class Article extends BaseController
{
    // ...
}
```

### 2.2 类属性注释

```php
/**
 * 请求对象
 *
 * 用于获取请求参数、headers 等信息
 *
 * @var \think\Request
 */
protected Request $request;

/**
 * 应用对象
 *
 * ThinkPHP 应用实例，用于依赖注入
 *
 * @var \think\App
 */
protected \think\App $app;

/**
 * 当前登录用户 ID
 *
 * @var int
 */
protected int $userId;

/**
 * 缓存实例
 *
 * @var \Psr\SimpleCache\CacheInterface
 */
protected $cache;
```

### 2.3 方法注释

**基础格式**：

```php
/**
 * 方法简短描述
 *
 * 详细的方法功能说明，可以多行。
 * 说明该方法的业务逻辑、处理流程等。
 *
 * @param  type   $param1    参数1的说明
 * @param  type   $param2    参数2的说明
 * @return type   返回值说明
 * @throws \RuntimeException 可能抛出的异常说明
 */
public function methodName(type $param1, type $param2): type
{
    // 实现代码
}
```

**完整示例**：

```php
/**
 * 获取文章列表
 *
 * 支持按标题、分类、作者、状态等条件筛选和搜索。
 * 支持按发布时间、更新时间等多种排序方式。
 * 返回分页数据。
 *
 * @param  Request $request 请求对象，包含以下查询参数：
 *                          - page (int): 当前页码，默认为1
 *                          - page_size (int): 每页数量，默认为20
 *                          - title (string): 文章标题（模糊查询）
 *                          - category_id (int): 分类ID
 *                          - user_id (int): 作者ID
 *                          - status (int): 文章状态
 *                          - is_top (int): 是否置顶
 *                          - is_recommend (int): 是否推荐
 *
 * @return array  返回分页数据结构：
 *                {
 *                    "code": 200,
 *                    "msg": "success",
 *                    "data": [
 *                        {
 *                            "id": 1,
 *                            "title": "文章标题",
 *                            "category_id": 1,
 *                            "publish_time": "2024-01-01 10:00:00",
 *                            ...
 *                        }
 *                    ],
 *                    "total": 100,
 *                    "page": 1,
 *                    "page_size": 20
 *                }
 *
 * @throws \think\exception\ValidateException 当请求参数验证失败时
 * @throws \RuntimeException 当查询数据库失败时
 *
 * @see Article::detail() 获取单篇文章详情
 * @see Article::create() 创建新文章
 */
public function index(Request $request): array
{
    // 实现代码
}
```

### 2.4 常用 PHPDoc 标签

| 标签 | 用途 | 示例 |
|------|------|------|
| `@param` | 参数说明 | `@param string $name 用户名` |
| `@return` | 返回值说明 | `@return array 用户数组` |
| `@throws` | 异常说明 | `@throws \Exception 数据库错误` |
| `@deprecated` | 废弃标记 | `@deprecated 1.0.0 使用 newMethod()` |
| `@see` | 相关引用 | `@see Article::detail()` |
| `@link` | 外部链接 | `@link https://example.com` |
| `@author` | 作者信息 | `@author John Doe <john@example.com>` |
| `@version` | 版本信息 | `@version 1.0.0` |
| @copyright` | 版权信息 | `@copyright 2024 Company` |
| `@license` | 许可证 | `@license Apache 2.0` |
| `@since` | 起始版本 | `@since 1.0.0` |

## 3. 具体实施

### 3.1 控制器注释规范

```php
<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\Article;
use think\Request;

/**
 * 文章管理 API 控制器
 *
 * 处理与文章相关的所有业务逻辑，包括查询、创建、更新、删除等操作。
 * 所有接口都需要通过认证中间件验证。
 *
 * @package app\controller\api
 * @author  Your Name
 * @version 1.0.0
 */
class Article extends BaseController
{
    /**
     * 获取文章列表
     *
     * 分页查询所有文章或按条件筛选。支持多条件组合查询。
     *
     * @param Request $request 请求对象
     * @return array 分页数据
     * @throws \think\exception\ValidateException 参数验证失败
     */
    public function index(Request $request): array
    {
        // 获取分页参数
        $page = (int)$request->get('page', 1);
        $pageSize = (int)$request->get('page_size', 20);

        // 获取筛选参数
        $title = $request->get('title', '');
        $categoryId = $request->get('category_id', '');

        // 构建查询
        $query = Article::with(['category', 'user', 'tags']);

        // 标题搜索
        if (!empty($title)) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        // 分类筛选
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // 获取总数
        $total = $query->count();

        // 分页查询
        $list = $query->page($page, $pageSize)->select();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取文章详情
     *
     * 获取单篇文章的详细信息，包括内容、分类、标签等。
     * 自动记录文章浏览次数。
     *
     * @param Request $request 请求对象，包含 id 参数
     * @return array 文章详情数据
     * @throws \think\exception\ModelNotFoundException 文章不存在
     */
    public function detail(Request $request): array
    {
        $id = (int)$request->param('id');

        // 获取文章
        $article = Article::with(['category', 'user', 'tags'])
            ->findOrFail($id);

        // 更新浏览次数
        $article->increment('view_count');

        return Response::success($article->toArray());
    }

    /**
     * 创建文章
     *
     * 创建一篇新文章，需要提供标题、内容、分类等必要信息。
     * 创建后的文章默认为草稿状态。
     *
     * @param Request $request 请求对象，包含以下数据：
     *                          - title (string, required): 文章标题
     *                          - content (string, required): 文章内容
     *                          - category_id (int, required): 分类ID
     *                          - summary (string, optional): 文章摘要
     * @return array 创建成功的文章数据
     * @throws \think\exception\ValidateException 参数验证失败
     * @throws \RuntimeException 创建失败
     */
    public function create(Request $request): array
    {
        // 验证数据
        $data = $this->validate($request->post(), [
            'title' => 'require|string|max:200',
            'content' => 'require|string',
            'category_id' => 'require|integer',
            'summary' => 'string|max:500',
        ]);

        // 添加作者信息
        $data['user_id'] = $this->userId;
        $data['status'] = Article::STATUS_DRAFT;

        // 创建文章
        $article = Article::create($data);

        return Response::success($article->toArray(), 'Article created successfully', 201);
    }
}
```

### 3.2 模型注释规范

```php
<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 文章模型
 *
 * 处理文章数据的增删改查，以及与其他模型的关联关系。
 * 支持软删除、版本控制等功能。
 *
 * @property int $id 文章ID
 * @property string $title 文章标题
 * @property string $content 文章内容
 * @property int $category_id 分类ID
 * @property int $user_id 作者ID
 * @property \DateTime $create_time 创建时间
 * @property \DateTime $update_time 更新时间
 * @property \DateTime $deleted_at 删除时间（软删除）
 *
 * @package app\model
 * @author  Your Name
 * @version 1.0.0
 */
class Article extends Model
{
    // 文章状态常量
    const STATUS_DRAFT = 0;        // 草稿
    const STATUS_PUBLISHED = 1;    // 已发布
    const STATUS_REVIEWING = 2;    // 待审核
    const STATUS_OFFLINE = 3;      // 已下线

    /**
     * 表名
     *
     * @var string
     */
    protected $table = 'articles';

    /**
     * 关联分类
     *
     * 获取文章所属的分类信息
     *
     * @return \think\model\relation\HasOne
     */
    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    /**
     * 关联作者
     *
     * 获取文章作者的信息
     *
     * @return \think\model\relation\HasOne
     */
    public function user()
    {
        return $this->hasOne(AdminUser::class, 'id', 'user_id');
    }

    /**
     * 关联标签
     *
     * 获取文章关联的所有标签
     *
     * @return \think\model\relation\BelongsToMany
     */
    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'article_tags',
            'tag_id',
            'article_id'
        );
    }

    /**
     * 获取已发布的文章
     *
     * 局部作用域：只查询状态为已发布的文章
     *
     * @param \think\db\Query $query 查询对象
     * @return \think\db\Query
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * 获取推荐的文章
     *
     * 局部作用域：只查询被标记为推荐的文章
     *
     * @param \think\db\Query $query 查询对象
     * @return \think\db\Query
     */
    public function scopeRecommended($query)
    {
        return $query->where('is_recommend', 1);
    }
}
```

### 3.3 服务层注释规范

```php
<?php
declare(strict_types=1);

namespace app\service;

use app\model\Article;

/**
 * 文章业务服务
 *
 * 处理与文章相关的所有业务逻辑，是控制器和模型之间的中间层。
 * 提供高级业务操作接口，隐藏数据库实现细节。
 *
 * @package app\service
 * @author  Your Name
 * @version 1.0.0
 */
class ArticleService
{
    /**
     * 创建文章
     *
     * 创建新文章并执行相关的业务逻辑：
     * 1. 数据验证
     * 2. 生成 URL slug
     * 3. 创建文章记录
     * 4. 关联标签
     * 5. 记录操作日志
     *
     * @param array $data 文章数据，包含：
     *                    - title (string): 文章标题
     *                    - content (string): 文章内容
     *                    - category_id (int): 分类ID
     *                    - tags (array): 标签ID数组
     * @return int 创建成功的文章ID
     * @throws \Exception 创建失败时抛出异常
     */
    public function create(array $data): int
    {
        // 验证数据
        $this->validateArticleData($data);

        // 生成 URL slug
        $data['slug'] = $this->generateSlug($data['title']);

        // 创建文章
        $article = Article::create($data);

        // 关联标签
        if (!empty($data['tags'])) {
            $article->tags()->attach($data['tags']);
        }

        return $article->id;
    }

    /**
     * 验证文章数据
     *
     * 验证文章数据的合法性
     *
     * @param array $data 文章数据
     * @return void
     * @throws \InvalidArgumentException 数据验证失败
     */
    private function validateArticleData(array $data): void
    {
        // 验证逻辑
    }

    /**
     * 生成 URL slug
     *
     * 根据标题生成 URL 友好的 slug，用于美化 URL。
     * 处理中文转拼音、特殊字符等。
     *
     * @param string $title 文章标题
     * @return string 生成的 slug
     */
    private function generateSlug(string $title): string
    {
        // 生成逻辑
    }
}
```

## 4. 复杂业务逻辑注释

### 4.1 示例：文章发布流程

```php
/**
 * 发布文章
 *
 * 将文章从草稿状态发布出去。发布过程包括：
 * 1. 状态检查：文章必须是草稿状态
 * 2. 内容检查：验证内容完整性
 * 3. SEO 检查：验证 SEO 信息
 * 4. 权限检查：验证用户有发布权限
 * 5. 更新状态：将状态改为已发布
 * 6. 更新发布时间
 * 7. 清除缓存
 * 8. 记录日志
 * 9. 发送通知
 *
 * @param int $articleId 文章ID
 * @return bool 是否发布成功
 * @throws \DomainException 文章已发布或状态不合法
 * @throws \LogicException 缺少必要信息无法发布
 */
public function publish(int $articleId): bool
{
    // 1. 获取文章
    $article = Article::findOrFail($articleId);

    // 2. 检查状态
    if ($article->status !== Article::STATUS_DRAFT) {
        throw new \DomainException('Article status is not draft');
    }

    // 3. 检查必填字段
    if (empty($article->title) || empty($article->content)) {
        throw new \LogicException('Article title or content is empty');
    }

    // 4. 更新状态
    $article->update([
        'status' => Article::STATUS_PUBLISHED,
        'publish_time' => date('Y-m-d H:i:s'),
    ]);

    // 5. 清除缓存
    cache()->forget('article:' . $articleId);

    // 6. 记录日志
    Logger::info('Article published', ['article_id' => $articleId]);

    // 7. 发送通知
    $this->notifySubscribers($article);

    return true;
}
```

## 5. 检查清单

- [ ] 所有文件都有头部注释
- [ ] 所有类都有类注释
- [ ] 所有公共方法都有方法注释
- [ ] 所有方法参数都有 `@param` 说明
- [ ] 所有方法都有 `@return` 说明
- [ ] 可能抛出异常的方法有 `@throws` 说明
- [ ] 所有类属性都有文档注释
- [ ] 复杂业务逻辑有详细的中文注释
- [ ] 注释用英文或中文清晰表述
- [ ] 注释与代码保持同步

## 6. 工具支持

### 6.1 IDE 支持

- PhpStorm：自动生成 PHPDoc
- VS Code + PHP Intelephense：自动提示和检查

### 6.2 生成文档

```bash
# 使用 phpDocumentor 生成 HTML 文档
phpdoc run -d ./backend/app -t ./docs/api
```

---

**更新时间**：2025-10-24
**优先级**：MEDIUM
**预计工作量**：12-16小时
