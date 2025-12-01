# 专题/栏目管理功能文档

## 功能概述

专题功能允许将相关文章按主题组织和展示，为网站提供更丰富的内容组织方式。通过专题，可以将不同分类下的相关文章汇聚在一起，形成主题性的内容集合。

## 功能特性

✅ **专题管理** - 创建、编辑、删除专题，支持封面图、SEO设置等
✅ **文章关联** - 灵活管理专题与文章的关联关系
✅ **双向关联** - 支持从专题添加文章，也支持从文章选择专题
✅ **文章排序** - 支持自定义文章在专题中的显示顺序
✅ **精选文章** - 支持标记专题中的精选文章
✅ **推荐专题** - 支持专题推荐标记，便于首页展示
✅ **URL友好** - 支持自定义URL别名，SEO优化
✅ **模板支持** - 支持为不同专题指定不同的模板

---

## 数据库设计

### 1. 专题表（topics）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 专题ID |
| name | varchar(100) | 专题名称 |
| slug | varchar(100) | URL别名（唯一） |
| description | text | 专题描述 |
| cover_image | varchar(255) | 专题封面图 |
| template | varchar(100) | 专题模板 |
| seo_title | varchar(200) | SEO标题 |
| seo_keywords | varchar(255) | SEO关键词 |
| seo_description | varchar(500) | SEO描述 |
| is_recommended | tinyint | 是否推荐：0=否，1=是 |
| status | tinyint | 状态：0=禁用，1=启用 |
| sort | int | 排序 |
| view_count | int | 浏览次数 |
| article_count | int | 文章数量 |
| create_time | datetime | 创建时间 |
| update_time | datetime | 更新时间 |
| deleted_at | datetime | 删除时间（软删除） |

### 2. 专题-文章关联表（topic_articles）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 关联ID |
| topic_id | int | 专题ID |
| article_id | int | 文章ID |
| sort | int | 在专题中的排序 |
| is_featured | tinyint | 是否精选：0=否，1=是 |
| create_time | datetime | 添加时间 |

**联合唯一索引**：`topic_id + article_id`

---

## 后端实现

### 模型类

**Topic.php** - 专题模型
```php
class Topic extends Model
{
    use SoftDelete;

    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用

    // 关联文章（多对多）
    public function articles()
    {
        return $this->belongsToMany(
            Article::class,
            TopicArticle::class,
            'article_id',
            'topic_id'
        )->order('topic_articles.sort', 'asc');
    }

    // 添加文章到专题
    public function addArticle($articleId, $sort = 0, $isFeatured = 0)

    // 从专题移除文章
    public function removeArticle($articleId)

    // 批量设置专题文章
    public function setArticles($articleIds)

    // 更新文章数量
    public function updateArticleCount()

    // 增加浏览次数
    public function incrementViewCount()
}
```

**TopicArticle.php** - 专题-文章关联模型
```php
class TopicArticle extends Model
{
    // 关联专题
    public function topic()

    // 关联文章
    public function article()

    // 更新文章在专题中的排序
    public static function updateArticleSort($topicId, $articleId, $sort)

    // 设置文章为精选
    public static function setFeatured($topicId, $articleId, $isFeatured = 1)
}
```

### 控制器

**TopicController.php** - 专题管理控制器

**基础CRUD接口**：
- `index()` - 获取专题列表（分页、搜索、筛选）
- `all()` - 获取所有启用的专题（不分页）
- `read($id)` - 获取专题详情
- `save()` - 创建专题
- `update($id)` - 更新专题
- `delete($id)` - 删除专题（软删除）

**文章管理接口**：
- `articles($id)` - 获取专题的文章列表
- `addArticle($id)` - 添加文章到专题
- `removeArticle($id)` - 从专题移除文章
- `setArticles($id)` - 批量设置专题文章
- `updateArticleSort($id)` - 更新文章排序
- `setArticleFeatured($id)` - 设置精选文章

### API路由

```php
// 专题管理
Route::get('topics/all', 'TopicController@all');                               // 获取所有专题
Route::get('topics/:id/articles', 'TopicController@articles');                 // 获取专题文章
Route::post('topics/:id/add-article', 'TopicController@addArticle');           // 添加文章
Route::post('topics/:id/remove-article', 'TopicController@removeArticle');     // 移除文章
Route::post('topics/:id/set-articles', 'TopicController@setArticles');         // 批量设置
Route::post('topics/:id/update-article-sort', 'TopicController@updateArticleSort'); // 更新排序
Route::post('topics/:id/set-article-featured', 'TopicController@setArticleFeatured'); // 设置精选
Route::resource('topics', 'TopicController');                                   // RESTful资源
```

---

## 前端实现

### API服务

**topic.js** - 专题API服务
```javascript
export function getTopicList(params)              // 获取专题列表
export function getAllTopics()                     // 获取所有专题
export function getTopicDetail(id)                // 获取专题详情
export function createTopic(data)                 // 创建专题
export function updateTopic(id, data)             // 更新专题
export function deleteTopic(id)                   // 删除专题
export function getTopicArticles(id, params)      // 获取专题文章
export function addArticleToTopic(topicId, articleId, sort, isFeatured)  // 添加文章
export function removeArticleFromTopic(topicId, articleId)               // 移除文章
export function setTopicArticles(topicId, articleIds)                    // 批量设置
export function updateArticleSort(topicId, articleId, sort)              // 更新排序
export function setArticleFeatured(topicId, articleId, isFeatured)       // 设置精选
```

### 页面组件

**topic/List.vue** - 专题列表管理页面

功能：
- 专题列表展示（搜索、筛选、分页）
- 创建/编辑专题对话框
- 封面图上传
- SEO信息设置
- 删除专题
- 跳转到文章管理

**topic/ArticleManager.vue** - 专题文章管理对话框

功能：
- 左侧：当前专题的文章列表
  - 显示文章封面、标题、精选标记
  - 调整文章排序
  - 设置/取消精选
  - 移除文章
- 右侧：添加文章
  - 搜索文章
  - 显示可添加的文章列表
  - 添加文章到专题
  - 已添加文章自动标记

### 文章编辑集成

在文章编辑页面（`article/Edit.vue`）中添加了专题选择功能：

```vue
<el-form-item label="所属专题">
  <el-select v-model="form.topics" placeholder="请选择专题（可选）" multiple>
    <el-option
      v-for="topic in topics"
      :key="topic.id"
      :label="topic.name"
      :value="topic.id"
    />
  </el-select>
</el-form-item>
```

**功能说明**：
- 支持多选，一篇文章可以属于多个专题
- 加载所有启用的专题列表
- 编辑文章时自动加载已关联的专题
- 保存文章时自动更新专题关联

---

## 使用场景

### 场景1：创建产品推荐专题

**需求**：创建一个"热门产品"专题，汇集各分类下的热门产品文章

**步骤**：
1. 进入"专题管理"页面
2. 点击"新建专题"
3. 填写专题信息：
   - 专题名称：热门产品
   - URL别名：hot-products
   - 专题描述：精选热门产品推荐
   - 上传封面图
   - 设置SEO信息
   - 标记为推荐：是
   - 状态：启用
4. 点击"确定"保存
5. 在专题列表中点击"管理文章"
6. 在右侧搜索框搜索产品文章
7. 点击"添加"按钮将文章加入专题
8. 调整文章排序
9. 将重点产品设为"精选"

### 场景2：从文章编辑页面关联专题

**需求**：编辑文章时直接选择所属专题

**步骤**：
1. 进入文章编辑页面
2. 在"所属专题"下拉框中选择一个或多个专题
3. 保存文章
4. 文章自动出现在所选专题的文章列表中

### 场景3：管理专题文章排序

**需求**：调整专题中文章的显示顺序

**步骤**：
1. 进入专题管理页面
2. 点击某个专题的"管理文章"
3. 在左侧文章列表中，调整每篇文章的排序数字
4. 排序会自动保存
5. 前台展示时按排序从小到大显示

### 场景4：设置精选文章

**需求**：在专题中突出显示重点文章

**步骤**：
1. 进入专题的文章管理对话框
2. 找到要设为精选的文章
3. 点击"设为精选"按钮
4. 文章将标记为精选状态
5. 前台模板可以根据精选标记优先展示这些文章

---

## 前台模板使用

### 获取所有推荐专题

```php
// 在控制器中
$topics = \app\model\Topic::where('is_recommended', 1)
    ->where('status', 1)
    ->order('sort', 'asc')
    ->limit(6)
    ->select();

// 传递给模板
return view('index', ['topics' => $topics]);
```

```html
<!-- 在模板中 -->
<div class="topics">
  {volist name="topics" id="topic"}
  <div class="topic-item">
    <img src="{$topic.cover_image}" alt="{$topic.name}">
    <h3><a href="/topic/{$topic.slug}.html">{$topic.name}</a></h3>
    <p>{$topic.description}</p>
    <span>共{$topic.article_count}篇文章</span>
  </div>
  {/volist}
</div>
```

### 显示专题详情页

```php
// 专题控制器
public function detail($slug)
{
    $topic = \app\model\Topic::where('slug', $slug)
        ->where('status', 1)
        ->find();

    if (!$topic) {
        abort(404, '专题不存在');
    }

    // 增加浏览次数
    $topic->incrementViewCount();

    // 获取专题文章
    $articles = $topic->articles()
        ->where('articles.status', 1)
        ->paginate(10);

    return view($topic->template, [
        'topic' => $topic,
        'articles' => $articles
    ]);
}
```

```html
<!-- 专题详情模板 -->
<div class="topic-header">
  <h1>{$topic.name}</h1>
  <p>{$topic.description}</p>
</div>

<div class="topic-articles">
  {volist name="articles" id="article"}
  <article>
    <h2><a href="/article/{$article.id}.html">{$article.title}</a></h2>
    {if $article.pivot.is_featured}
    <span class="featured">精选</span>
    {/if}
    <p>{$article.summary}</p>
  </article>
  {/volist}
</div>

<!-- 分页 -->
{$articles|raw}
```

### 在文章页显示所属专题

```php
// 文章控制器
$article = \app\model\Article::with(['topics'])->find($id);

return view('article', ['article' => $article]);
```

```html
<!-- 文章模板 -->
<div class="article-topics">
  <span>相关专题：</span>
  {volist name="article.topics" id="topic"}
  <a href="/topic/{$topic.slug}.html">{$topic.name}</a>
  {/volist}
</div>
```

---

## 注意事项

### 1. URL别名规范
- 只能包含小写字母、数字和连字符
- 系统会自动转换为小写并替换空格为连字符
- URL别名必须唯一
- 建议使用英文，便于SEO

### 2. 专题模板
- 默认模板：`topic_default`
- 模板文件位置：`/template/{theme}/topic_default.html`
- 可以为不同专题指定不同模板
- 模板可访问变量：`$topic`（专题信息）、`$articles`（文章列表）

### 3. 文章关联
- 一篇文章可以属于多个专题
- 从专题中移除文章不会删除文章本身
- 删除专题会删除所有文章关联，但不会删除文章
- 软删除的专题不会在前台显示

### 4. 排序机制
- 专题列表排序：按 `sort` 升序，相同则按 `id` 降序
- 专题内文章排序：按 `topic_articles.sort` 升序
- 排序数字越小越靠前
- 默认排序为0

### 5. 精选功能
- 精选是针对特定专题的，同一文章在不同专题可以有不同的精选状态
- 精选文章可在模板中优先展示
- 通过 `$article.pivot.is_featured` 判断是否精选

### 6. 性能考虑
- 文章数量字段 `article_count` 会自动更新
- 浏览次数字段 `view_count` 需要手动调用 `incrementViewCount()`
- 专题-文章关联表有联合唯一索引，避免重复关联
- 查询专题文章时使用了预加载（with），减少SQL查询

### 7. SEO优化
- 每个专题支持独立的SEO标题、关键词、描述
- URL别名支持自定义，生成友好的URL
- 专题页面应设置合适的Meta标签
- 建议为专题设置封面图，提升分享效果

---

## 扩展功能建议

- [ ] **专题分组** - 支持专题分组管理，如"技术类专题"、"产品类专题"
- [ ] **专题订阅** - 允许用户订阅感兴趣的专题
- [ ] **专题统计** - 统计专题的访问量、热度趋势
- [ ] **专题编辑器** - 可视化拖拽管理专题文章
- [ ] **专题评论** - 支持对专题整体进行评论
- [ ] **专题导航** - 自动生成专题导航菜单
- [ ] **专题RSS** - 为每个专题生成独立RSS订阅
- [ ] **相关专题推荐** - 根据文章内容推荐相关专题
- [ ] **专题权限** - 支持会员专题、付费专题

---

## 常见问题

**Q: 如何批量导入文章到专题？**
A: 可以使用 `setArticles()` 方法批量设置，或在后台文章列表添加批量操作功能。

**Q: 一个专题最多可以包含多少篇文章？**
A: 理论上没有限制，但建议不超过1000篇，以保证前台展示性能。

**Q: 如何在首页显示推荐专题？**
A: 查询 `is_recommended=1` 且 `status=1` 的专题即可。

**Q: 专题URL如何生成静态页面？**
A: 需要在静态生成模块中添加专题页面生成功能，遍历所有专题调用详情页模板。

**Q: 如何统计专题的总访问量？**
A: 每次访问专题详情页时调用 `incrementViewCount()` 方法，访问量会累加到 `view_count` 字段。

**Q: 删除的专题可以恢复吗？**
A: 可以，专题使用软删除机制，可以在回收站中恢复。

**Q: 如何自定义专题页面样式？**
A: 为专题指定不同的模板文件，在模板中自定义HTML和CSS样式。

---

**更新时间**: 2025-10-18
**版本**: 1.0
