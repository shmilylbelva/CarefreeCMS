# Carefree 标签库 V1.4 文档

## 版本信息

- **版本号**: V1.4
- **发布日期**: 2025年10月
- **更新内容**: 相关文章推荐、标签云、搜索框、评论系统、用户信息

## 新增功能概览

V1.4 版本新增了以下重要功能：

1. **相关文章标签（related）** - 智能推荐相关文章，基于标签和分类
2. **标签云（tagcloud）** - 可视化标签展示，支持多种排序和样式
3. **搜索框（search）** - 快速生成搜索表单
4. **评论列表（comment）** - 展示最新评论和热门评论
5. **用户信息（userinfo）** - 展示作者详细信息和统计数据

---

## 一、相关文章标签（related）

### 1.1 功能说明

相关文章标签可以智能推荐与当前文章相关的其他文章，推荐算法支持：
- **同标签推荐** - 查找拥有相同标签的文章
- **同分类推荐** - 查找同一分类下的文章
- **智能混合推荐** - 优先同标签，不足时补充同分类
- 自动排除当前文章
- 支持按浏览量和发布时间排序
- 30分钟缓存机制

### 1.2 基本语法

```html
{carefree:related aid='当前文章ID' limit='数量' type='推荐类型' id='变量名' empty='空提示'}
    <!-- 循环体内容 -->
{/carefree:related}
```

### 1.3 属性说明

| 属性 | 必填 | 默认值 | 说明 |
|------|------|--------|------|
| aid | 是 | 0 | 当前文章ID（必须提供） |
| limit | 否 | 5 | 推荐数量 |
| type | 否 | auto | 推荐类型（auto/category/tag） |
| id | 否 | related | 循环变量名 |
| empty | 否 | - | 无数据时显示的提示 |

### 1.4 推荐类型

| 类型 | 说明 |
|------|------|
| auto | 智能推荐（优先同标签，不足则同分类） |
| category | 仅推荐同分类文章 |
| tag | 仅推荐同标签文章 |

### 1.5 使用示例

#### 示例 1: 文章底部相关推荐

```html
<div class="related-articles">
    <h3>相关推荐</h3>
    <div class="related-list">
        {carefree:related aid='{$article.id}' limit='6' type='auto' id='related'}
            <div class="related-item">
                <a href="/article/{$related.id}.html">
                    <h4>{$related.title}</h4>
                    <p>{$related.summary}</p>
                    <span class="meta">
                        {$related.view_count} 阅读 • {$related.create_time|date='Y-m-d'}
                    </span>
                </a>
            </div>
        {/carefree:related}
    </div>
</div>
```

#### 示例 2: 带缩略图的推荐

```html
<section class="you-may-like">
    <h2>你可能还喜欢</h2>
    <div class="article-grid">
        {carefree:related aid='{$article.id}' limit='4' id='item' empty='暂无相关文章'}
            <article class="article-card">
                <a href="/article/{$item.id}.html">
                    <img src="{$item.cover_image}" alt="{$item.title}">
                    <div class="card-body">
                        <h3>{$item.title}</h3>
                        <p class="summary">{$item.summary}</p>
                    </div>
                </a>
            </article>
        {/carefree:related}
    </div>
</section>
```

#### 示例 3: 仅推荐同类文章

```html
<aside class="sidebar">
    <div class="widget">
        <h4>同类文章</h4>
        <ul>
            {carefree:related aid='{$article.id}' limit='5' type='category' id='item'}
                <li>
                    <a href="/article/{$item.id}.html">{$item.title}</a>
                    <span>{$item.view_count} 阅读</span>
                </li>
            {/carefree:related}
        </ul>
    </div>
</aside>
```

---

## 二、标签云（tagcloud）

### 2.1 功能说明

标签云以可视化方式展示网站的热门标签，支持：
- 字体大小分级（基于使用频率）
- 5级CSS类名（tag-level-1 到 tag-level-5）
- 多种排序方式
- 自定义字体大小范围
- 直接输出HTML或返回数据数组
- 30分钟缓存

### 2.2 基本语法

```html
<!-- 方式1：直接输出HTML -->
{carefree:tagcloud limit='30' orderby='count' minsize='12' maxsize='28' style='html' /}

<!-- 方式2：获取数据自定义渲染 -->
{carefree:tagcloud limit='30' orderby='count' style='data' /}
```

### 2.3 属性说明

| 属性 | 必填 | 默认值 | 说明 |
|------|------|--------|------|
| limit | 否 | 30 | 显示标签数量 |
| orderby | 否 | count | 排序方式（count/name/random） |
| minsize | 否 | 12 | 最小字体大小（px） |
| maxsize | 否 | 28 | 最大字体大小（px） |
| style | 否 | html | 输出方式（html/data） |

### 2.4 排序方式

| 类型 | 说明 |
|------|------|
| count | 按使用次数排序（默认） |
| name | 按标签名称字母顺序 |
| random | 随机排序 |

### 2.5 使用示例

#### 示例 1: 默认标签云

```html
<div class="sidebar-widget">
    <h3>热门标签</h3>
    {carefree:tagcloud limit='30' orderby='count' /}
</div>

<style>
.tag-cloud {
    padding: 15px;
}

.tag-cloud .tag-item {
    display: inline-block;
    margin: 5px;
    padding: 5px 12px;
    border-radius: 3px;
    text-decoration: none;
    transition: all 0.3s;
}

.tag-cloud .tag-item:hover {
    transform: scale(1.1);
}

/* 5级标签样式 */
.tag-level-1 {
    background: #e3f2fd;
    color: #1976d2;
}

.tag-level-2 {
    background: #c5e1a5;
    color: #558b2f;
}

.tag-level-3 {
    background: #fff59d;
    color: #f57f17;
}

.tag-level-4 {
    background: #ffcc80;
    color: #e65100;
}

.tag-level-5 {
    background: #ef9a9a;
    color: #c62828;
}
</style>
```

#### 示例 2: 自定义渲染

```html
{carefree:tagcloud limit='50' orderby='name' minsize='14' maxsize='32' style='data' /}

<div class="custom-tag-cloud">
    {volist name="__tagcloud__" id="tag"}
        <a href="{$tag.url}"
           class="tag-badge tag-level-{$tag.level}"
           style="font-size: {$tag.font_size}px">
            {$tag.name} ({$tag.article_count})
        </a>
    {/volist}
</div>
```

#### 示例 3: 卡片式标签云

```html
<div class="tag-cloud-cards">
    {carefree:tagcloud limit='20' orderby='count' style='data' /}

    {volist name="__tagcloud__" id="tag"}
        <div class="tag-card">
            <a href="{$tag.url}">
                <div class="tag-name">{$tag.name}</div>
                <div class="tag-count">{$tag.article_count} 篇文章</div>
            </a>
        </div>
    {/volist}
</div>
```

---

## 三、搜索框（search）

### 3.1 功能说明

搜索框标签可以快速生成搜索表单HTML，支持：
- 自定义搜索接口URL
- 自定义占位符文本
- 自定义按钮文字
- 自定义CSS类名
- GET方式提交，参数名为 `q`

### 3.2 基本语法

```html
{carefree:search action='/search' placeholder='请输入关键词' button='搜索' class='search-form' /}
```

### 3.3 属性说明

| 属性 | 必填 | 默认值 | 说明 |
|------|------|--------|------|
| action | 否 | /search | 搜索接口URL |
| placeholder | 否 | 请输入关键词... | 输入框占位符 |
| button | 否 | 搜索 | 按钮文字 |
| class | 否 | search-form | 表单CSS类名 |

### 3.4 使用示例

#### 示例 1: 导航栏搜索

```html
<header>
    <div class="header-container">
        <div class="logo">网站Logo</div>

        <nav class="main-nav">
            <!-- 导航菜单 -->
        </nav>

        <div class="header-search">
            {carefree:search action='/search' placeholder='搜索文章...' button='🔍' class='header-search-form' /}
        </div>
    </div>
</header>

<style>
.header-search-form {
    display: flex;
}

.header-search-form .search-input {
    padding: 8px 15px;
    border: 1px solid #ddd;
    border-radius: 20px 0 0 20px;
    width: 200px;
}

.header-search-form .search-button {
    padding: 8px 20px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 0 20px 20px 0;
    cursor: pointer;
}
</style>
```

#### 示例 2: 首页大搜索框

```html
<section class="hero">
    <div class="hero-content">
        <h1>欢迎来到我的博客</h1>
        <p>在这里找到你想要的内容</p>

        <div class="hero-search">
            {carefree:search action='/search' placeholder='输入关键词，开始探索...' button='立即搜索' class='hero-search-form' /}
        </div>
    </div>
</section>

<style>
.hero-search-form {
    margin: 30px auto;
    max-width: 600px;
}

.hero-search-form .search-input-wrapper {
    display: flex;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-radius: 50px;
    overflow: hidden;
}

.hero-search-form .search-input {
    flex: 1;
    padding: 15px 25px;
    border: none;
    font-size: 16px;
}

.hero-search-form .search-button {
    padding: 15px 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s;
}

.hero-search-form .search-button:hover {
    transform: scale(1.05);
}
</style>
```

#### 示例 3: 侧边栏搜索

```html
<aside class="sidebar">
    <div class="widget search-widget">
        <h3>搜索文章</h3>
        {carefree:search action='/search' placeholder='输入关键词' button='搜' /}
    </div>
</aside>
```

---

## 四、评论列表（comment）

### 4.1 功能说明

评论列表标签用于展示网站评论，支持：
- 显示最新评论或热门评论
- 全站评论或指定文章评论
- 自动截取长评论
- 友好时间显示（如"3小时前"）
- 管理员评论标识
- 10分钟缓存（最新）/ 30分钟缓存（热门）

### 4.2 基本语法

```html
{carefree:comment limit='10' aid='文章ID' type='类型' id='变量名' empty='空提示'}
    <!-- 循环体内容 -->
{/carefree:comment}
```

### 4.3 属性说明

| 属性 | 必填 | 默认值 | 说明 |
|------|------|--------|------|
| limit | 否 | 10 | 显示数量 |
| aid | 否 | 0 | 文章ID（0表示全站） |
| type | 否 | latest | 评论类型（latest/hot） |
| id | 否 | comment | 循环变量名 |
| empty | 否 | - | 无数据时显示的提示 |

### 4.4 可用字段

```php
$comment = [
    'id'             => 1,
    'article_id'     => 10,
    'article_title'  => '文章标题',
    'article_url'    => '/article/10.html',
    'user_name'      => '用户名',
    'display_name'   => '显示名称',
    'is_admin'       => 0,
    'content'        => '完整评论内容',
    'short_content'  => '截取后的评论...',
    'like_count'     => 5,
    'create_time'    => '2025-10-28 10:00:00',
    'formatted_time' => '2小时前',
]
```

### 4.5 使用示例

#### 示例 1: 侧边栏最新评论

```html
<div class="sidebar-widget">
    <h3>最新评论</h3>
    <div class="recent-comments">
        {carefree:comment limit='5' type='latest' id='cmt' empty='暂无评论'}
            <div class="comment-item">
                <div class="comment-author">
                    <strong>{$cmt.display_name}</strong>
                    {if $cmt.is_admin}<span class="admin-badge">管理员</span>{/if}
                </div>
                <div class="comment-content">{$cmt.short_content}</div>
                <div class="comment-meta">
                    <span>{$cmt.formatted_time}</span> 在
                    <a href="{$cmt.article_url}">{$cmt.article_title}</a>
                </div>
            </div>
        {/carefree:comment}
    </div>
</div>
```

#### 示例 2: 热门评论

```html
<section class="hot-comments">
    <h2>热门评论</h2>
    <div class="comments-list">
        {carefree:comment limit='10' type='hot' id='hot'}
            <div class="hot-comment-item">
                <div class="comment-header">
                    <span class="author">{$hot.display_name}</span>
                    <span class="time">{$hot.formatted_time}</span>
                </div>
                <div class="comment-body">
                    <p>{$hot.content}</p>
                </div>
                <div class="comment-footer">
                    <a href="{$hot.article_url}" class="article-link">
                        查看文章: {$hot.article_title}
                    </a>
                    <span class="likes">👍 {$hot.like_count}</span>
                </div>
            </div>
        {/carefree:comment}
    </div>
</section>
```

#### 示例 3: 文章评论列表

```html
<div class="article-comments">
    <h3>评论 ({carefree:stats type='comment' aid='{$article.id}' /})</h3>

    <div class="comment-list">
        {carefree:comment aid='{$article.id}' limit='20' id='comment'}
            <div class="comment" id="comment-{$comment.id}">
                <div class="comment-avatar">
                    <img src="/static/avatar.png" alt="{$comment.display_name}">
                </div>
                <div class="comment-content-wrapper">
                    <div class="comment-author">
                        {$comment.display_name}
                        {if $comment.is_admin}<span class="badge">作者</span>{/if}
                    </div>
                    <div class="comment-text">{$comment.content}</div>
                    <div class="comment-actions">
                        <span class="time">{$comment.formatted_time}</span>
                        <button class="like-btn">点赞 ({$comment.like_count})</button>
                        <button class="reply-btn">回复</button>
                    </div>
                </div>
            </div>
        {/carefree:comment}
    </div>
</div>
```

---

## 五、用户信息（userinfo）

### 5.1 功能说明

用户信息标签用于显示作者的详细信息和统计数据，支持：
- 用户基本信息（用户名、真实姓名、邮箱、头像）
- 文章统计（发文数、总浏览量、总点赞数）
- 角色信息
- 1小时缓存机制

### 5.2 基本语法

```html
{carefree:userinfo uid='用户ID'}
    <!-- 显示用户信息 -->
{/carefree:userinfo}
```

### 5.3 属性说明

| 属性 | 必填 | 默认值 | 说明 |
|------|------|--------|------|
| uid | 是 | 0 | 用户ID（必须提供） |

### 5.4 可用字段

```php
$userinfo = [
    'id'            => 1,
    'username'      => 'admin',
    'real_name'     => '张三',
    'display_name'  => '张三',          // 优先显示真实姓名
    'email'         => 'admin@example.com',
    'avatar'        => '/uploads/avatar.jpg',
    'role_id'       => 1,
    'role_name'     => '管理员',
    'article_count' => 50,              // 发文数量
    'total_views'   => 10000,           // 总浏览量
    'total_likes'   => 500,             // 总点赞数
    'create_time'   => '2025-01-01',    // 注册时间
]
```

### 5.5 使用示例

#### 示例 1: 文章作者信息

```html
<article class="article-detail">
    <header class="article-header">
        <h1>{$article.title}</h1>

        {carefree:userinfo uid='{$article.user_id}'}
            <div class="article-author">
                <img src="{$userinfo.avatar}" alt="{$userinfo.display_name}" class="author-avatar">
                <div class="author-info">
                    <div class="author-name">{$userinfo.display_name}</div>
                    <div class="author-meta">
                        发布于 {$article.create_time|date='Y-m-d'} •
                        已发布 {$userinfo.article_count} 篇文章
                    </div>
                </div>
            </div>
        {/carefree:userinfo}
    </header>

    <div class="article-content">
        {$article.content}
    </div>
</article>
```

#### 示例 2: 作者卡片

```html
{carefree:userinfo uid='{$article.user_id}'}
    <div class="author-card">
        <div class="author-card-header">
            <img src="{$userinfo.avatar}" alt="{$userinfo.display_name}">
        </div>
        <div class="author-card-body">
            <h3>{$userinfo.display_name}</h3>
            <p class="role-badge">{$userinfo.role_name}</p>

            <div class="author-stats">
                <div class="stat-item">
                    <div class="stat-value">{$userinfo.article_count}</div>
                    <div class="stat-label">文章</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{$userinfo.total_views}</div>
                    <div class="stat-label">阅读</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{$userinfo.total_likes}</div>
                    <div class="stat-label">点赞</div>
                </div>
            </div>

            <a href="/author/{$userinfo.id}.html" class="view-profile">
                查看主页 →
            </a>
        </div>
    </div>
{/carefree:userinfo}
```

#### 示例 3: 文章列表中的作者信息

```html
{carefree:article limit='10' id='article'}
    <div class="article-item">
        <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
        <p>{$article.summary}</p>

        {carefree:userinfo uid='{$article.user_id}'}
            <div class="article-footer">
                <div class="author-mini">
                    <img src="{$userinfo.avatar}" alt="{$userinfo.display_name}">
                    <span>{$userinfo.display_name}</span>
                </div>
                <span class="article-date">{$article.create_time|date='Y-m-d'}</span>
            </div>
        {/carefree:userinfo}
    </div>
{/carefree:article}
```

---

## 六、综合应用示例

### 6.1 完整的文章详情页

```html
<!DOCTYPE html>
<html>
<head>
    <title>{$article.title} - {carefree:config name='site_name' /}</title>
</head>
<body>
    <div class="container">
        <!-- 主要内容 -->
        <main class="main-content">
            <article class="article-detail">
                <!-- 文章头部 -->
                <header>
                    <h1>{$article.title}</h1>

                    {carefree:userinfo uid='{$article.user_id}'}
                        <div class="author-info">
                            <img src="{$userinfo.avatar}" alt="{$userinfo.display_name}">
                            <div>
                                <strong>{$userinfo.display_name}</strong>
                                <span>{$article.create_time}</span>
                            </div>
                        </div>
                    {/carefree:userinfo}
                </header>

                <!-- 文章内容 -->
                <div class="content">
                    {$article.content}
                </div>

                <!-- 标签 -->
                <div class="article-tags">
                    {volist name="article.tags" id="tag"}
                        <a href="/tag/{$tag.id}.html" class="tag">{$tag.name}</a>
                    {/volist}
                </div>
            </article>

            <!-- 相关文章 -->
            <section class="related-articles">
                <h2>相关推荐</h2>
                <div class="article-grid">
                    {carefree:related aid='{$article.id}' limit='4' id='related'}
                        <div class="article-card">
                            <a href="/article/{$related.id}.html">
                                <h3>{$related.title}</h3>
                                <p>{$related.summary}</p>
                            </a>
                        </div>
                    {/carefree:related}
                </div>
            </section>

            <!-- 评论区 -->
            <section class="comments-section">
                <h2>评论</h2>
                {carefree:comment aid='{$article.id}' limit='20' id='cmt'}
                    <div class="comment-item">
                        <strong>{$cmt.display_name}</strong>
                        <p>{$cmt.content}</p>
                        <span>{$cmt.formatted_time}</span>
                    </div>
                {/carefree:comment}
            </section>
        </main>

        <!-- 侧边栏 -->
        <aside class="sidebar">
            <!-- 搜索 -->
            <div class="widget">
                {carefree:search /}
            </div>

            <!-- 标签云 -->
            <div class="widget">
                <h3>热门标签</h3>
                {carefree:tagcloud limit='30' /}
            </div>

            <!-- 最新评论 -->
            <div class="widget">
                <h3>最新评论</h3>
                {carefree:comment limit='5' type='latest' id='c'}
                    <div>{$c.display_name}: {$c.short_content}</div>
                {/carefree:comment}
            </div>
        </aside>
    </div>
</body>
</html>
```

---

## 七、性能优化

### 7.1 缓存策略

| 功能 | 缓存时间 | 说明 |
|------|---------|------|
| 相关文章 | 30分钟 | 基于文章ID和参数缓存 |
| 标签云 | 30分钟 | 基于限制数量和排序方式缓存 |
| 最新评论 | 10分钟 | 实时性要求高 |
| 热门评论 | 30分钟 | 变化较慢 |
| 用户信息 | 1小时 | 用户信息变化不频繁 |

### 7.2 优化建议

1. **相关文章**
   - 合理设置 limit，避免查询过多
   - auto 模式下会查询两次，type='category' 性能更好
   - 文章更新时清除相关缓存

2. **标签云**
   - 限制显示数量（建议 ≤ 50）
   - 使用 style='html' 直接输出，无需二次处理
   - 新增标签后清除缓存

3. **评论**
   - 最新评论缓存时间短，减少数据库压力
   - 全站评论比单文章评论查询更快（无需JOIN）
   - 避免在列表页加载评论

4. **用户信息**
   - 用户信息已自动缓存
   - 批量显示时使用预加载（with关联）
   - 更新用户信息后清除缓存

---

## 八、常见问题

### Q1: 相关文章推荐不准确？
**A**:
- 确保文章有标签和分类
- 使用 type='auto' 获得最佳推荐
- 检查文章是否已发布（status=1）

### Q2: 标签云字体大小不变化？
**A**:
- 检查标签使用次数差异是否足够大
- 调整 minsize 和 maxsize 参数
- 清除缓存后重试

### Q3: 搜索框无法提交？
**A**:
- 检查搜索接口是否存在
- 确认表单method为GET
- 检查JavaScript是否有冲突

### Q4: 评论不显示？
**A**:
- 检查评论状态（status=1为已审核）
- 确认 aid 参数是否正确
- 清除缓存后重试

### Q5: 用户信息显示为空？
**A**:
- 检查用户ID是否正确
- 确认用户账号状态正常
- 检查数据库用户表数据

---

## 九、版本历史

- **V1.4** (2025-10) - 相关文章、标签云、搜索框、评论、用户信息
- **V1.3** (2025-10) - 广告位、随机文章、最近更新、统计数据
- **V1.2** (2025-09) - 空数据处理、幻灯片、分页导航
- **V1.1** (2025-08) - 友链、面包屑、单项信息标签
- **V1.0** (2025-07) - 基础标签功能

---

© 2025 Carefree 标签库 - 让模板开发更加自由
