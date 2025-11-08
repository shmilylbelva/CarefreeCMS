# Carefree 标签库快速入门指南

## 5分钟快速上手

### 第一步：理解基本概念

Carefree 标签库是一个为 ThinkPHP 模板引擎设计的自定义标签库，让你可以在模板中使用简单的标签来获取数据，而无需在控制器中传递。

**基本格式：**
```html
{carefree:标签名 参数1='值1' 参数2='值2'}
    <!-- 标签内容 -->
{/carefree:标签名}
```

### 第二步：在模板中启用

在模板文件头部添加：
```html
<!DOCTYPE html>
<html>
<head>
    <!-- 标签库会自动加载，无需额外配置 -->
</head>
```

### 第三步：使用第一个标签

让我们从最简单的标签开始：

#### 示例1：显示网站配置
```html
<h1>{carefree:config name='site_name' /}</h1>
<p>{carefree:config name='site_description' /}</p>
```

#### 示例2：显示文章列表
```html
<div class="articles">
    {carefree:article limit='10' id='article'}
        <div class="article-item">
            <h3>{$article.title}</h3>
            <p>{$article.summary}</p>
        </div>
    {/carefree:article}
</div>
```

#### 示例3：显示分类列表
```html
<ul class="categories">
    {carefree:category limit='10' id='cat'}
        <li><a href="/category/{$cat.id}.html">{$cat.name}</a></li>
    {/carefree:category}
</ul>
```

### 第四步：组合使用

```html
<!-- 完整的首页示例 -->
<!DOCTYPE html>
<html>
<head>
    <title>{carefree:config name='site_name' /}</title>
</head>
<body>
    <!-- 导航菜单 -->
    <nav>
        {carefree:nav limit='5' id='nav'}
            <a href="{$nav.url}">{$nav.name}</a>
        {/carefree:nav}
    </nav>

    <!-- 主要内容 -->
    <main>
        <h1>最新文章</h1>
        {carefree:article limit='5' id='article'}
            <article>
                <h2>{$article.title}</h2>
                <p>{$article.summary}</p>
                <a href="/article/{$article.id}.html">阅读更多</a>
            </article>
        {/carefree:article}
    </main>

    <!-- 侧边栏 -->
    <aside>
        <h3>分类</h3>
        {carefree:category limit='10' id='cat'}
            <div>{$cat.name} ({$cat.article_count})</div>
        {/carefree:category}
    </aside>
</body>
</html>
```

---

## 常用标签速查

### 内容展示类

| 标签 | 用途 | 示例 |
|------|------|------|
| article | 文章列表 | `{carefree:article limit='10'}` |
| category | 分类列表 | `{carefree:category}` |
| tag | 标签列表 | `{carefree:tag limit='20'}` |
| comment | 评论列表 | `{carefree:comment limit='10'}` |

### 导航类

| 标签 | 用途 | 示例 |
|------|------|------|
| nav | 导航菜单 | `{carefree:nav}` |
| breadcrumb | 面包屑 | `{carefree:breadcrumb}` |
| pagelist | 分页 | `{carefree:pagelist total='100'}` |

### 功能类

| 标签 | 用途 | 示例 |
|------|------|------|
| search | 搜索框 | `{carefree:search /}` |
| stats | 统计数据 | `{carefree:stats type='article' /}` |
| tagcloud | 标签云 | `{carefree:tagcloud /}` |

---

## 10个最常用的场景

### 场景1：首页显示最新文章

```html
<h2>最新文章</h2>
{carefree:article limit='5' order='create_time desc' id='article'}
    <div class="article">
        <h3>{$article.title}</h3>
        <p>{$article.summary}</p>
        <span>{$article.create_time}</span>
    </div>
{/carefree:article}
```

### 场景2：显示热门文章

```html
<h2>热门文章</h2>
{carefree:article flag='hot' limit='10' id='hot'}
    <div class="hot-article">
        <a href="/article/{$hot.id}.html">{$hot.title}</a>
        <span>{$hot.view_count} 阅读</span>
    </div>
{/carefree:article}
```

### 场景3：侧边栏分类列表

```html
<div class="sidebar-categories">
    <h3>文章分类</h3>
    <ul>
        {carefree:category limit='10' id='cat'}
            <li>
                <a href="/category/{$cat.id}.html">
                    {$cat.name} ({$cat.article_count})
                </a>
            </li>
        {/carefree:category}
    </ul>
</div>
```

### 场景4：显示某个分类下的文章

```html
<h2>技术分类文章</h2>
{carefree:article typeid='2' limit='10' id='article'}
    <div>{$article.title}</div>
{/carefree:article}
```

### 场景5：搜索框

```html
<div class="header-search">
    {carefree:search action='/search' placeholder='搜索文章...' button='搜索' /}
</div>
```

### 场景6：标签云

```html
<div class="sidebar-tags">
    <h3>热门标签</h3>
    {carefree:tagcloud limit='30' orderby='count' /}
</div>
```

### 场景7：最新评论

```html
<div class="recent-comments">
    <h3>最新评论</h3>
    {carefree:comment limit='5' type='latest' id='comment'}
        <div class="comment">
            <strong>{$comment.display_name}</strong>:
            {$comment.short_content}
        </div>
    {/carefree:comment}
</div>
```

### 场景8：友情链接

```html
<div class="友情links">
    <h3>友情链接</h3>
    {carefree:link limit='10' id='link'}
        <a href="{$link.url}" target="_blank">{$link.name}</a>
    {/carefree:link}
</div>
```

### 场景9：网站统计

```html
<div class="site-stats">
    <div>文章: {carefree:stats type='article' /} 篇</div>
    <div>分类: {carefree:stats type='category' /} 个</div>
    <div>标签: {carefree:stats type='tag' /} 个</div>
    <div>浏览: {carefree:stats type='view' /} 次</div>
</div>
```

### 场景10：文章归档

```html
<div class="archives">
    <h3>文章归档</h3>
    <ul>
        {carefree:archive type='month' limit='12' id='archive'}
            <li>
                <a href="{$archive.url}">
                    {$archive.display_date} ({$archive.article_count})
                </a>
            </li>
        {/carefree:archive}
    </ul>
</div>
```

---

## 参数详解

### 通用参数

所有列表类标签都支持这些参数：

- **limit** - 显示数量，如 `limit='10'`
- **id** - 循环变量名，如 `id='article'`
- **empty** - 无数据时的提示，如 `empty='暂无数据'`

### 文章标签特殊参数

- **typeid** - 分类ID
- **tagid** - 标签ID
- **userid** - 作者ID
- **flag** - 文章标识
  - `hot` - 热门文章
  - `recommend` - 推荐文章
  - `top` - 置顶文章
  - `random` - 随机文章
  - `updated` - 最近更新
- **order** - 排序方式，如 `order='create_time desc'`
- **titlelen** - 标题截取长度
- **hascover** - 是否有封面（1-有，0-无）
- **exclude** - 排除的文章ID
- **days** - 最近N天的文章

---

## 常见错误

### 错误1：标签没有输出

**原因**：变量名使用错误

```html
<!-- 错误 -->
{carefree:article id='article'}
    {$art.title}  <!-- 变量名不匹配 -->
{/carefree:article}

<!-- 正确 -->
{carefree:article id='article'}
    {$article.title}  <!-- 使用正确的变量名 -->
{/carefree:article}
```

### 错误2：标签不显示数据

**原因**：没有满足筛选条件的数据

```html
<!-- 使用 empty 参数显示提示 -->
{carefree:article limit='10' empty='暂无文章' id='article'}
    <div>{$article.title}</div>
{/carefree:article}
```

### 错误3：参数格式错误

```html
<!-- 错误：参数值没有引号 -->
{carefree:article limit=10}

<!-- 正确：参数值要用引号 -->
{carefree:article limit='10'}
```

---

## 进阶技巧

### 技巧1：嵌套使用标签

```html
<!-- 显示分类及其文章 -->
{carefree:category limit='5' id='cat'}
    <div class="category-section">
        <h2>{$cat.name}</h2>

        {carefree:article typeid='{$cat.id}' limit='5' id='article'}
            <div>{$article.title}</div>
        {/carefree:article}
    </div>
{/carefree:category}
```

### 技巧2：使用循环索引

```html
{carefree:article limit='10' id='article'}
    <div class="article-{$i}">  <!-- $i 是循环索引，从1开始 -->
        <span class="rank">{$i}</span>
        <span class="title">{$article.title}</span>
    </div>
{/carefree:article}
```

### 技巧3：条件判断

```html
{carefree:article limit='10' id='article'}
    <div class="article">
        <h3>{$article.title}</h3>

        <!-- 判断是否有封面 -->
        {if $article.cover_image}
            <img src="{$article.cover_image}" alt="{$article.title}">
        {/if}

        <!-- 判断是否推荐 -->
        {if $article.is_recommend}
            <span class="badge">推荐</span>
        {/if}
    </div>
{/carefree:article}
```

### 技巧4：格式化输出

```html
{carefree:article limit='10' id='article'}
    <div>
        <!-- 日期格式化 -->
        <span>{$article.create_time|date='Y-m-d H:i'}</span>

        <!-- 数字格式化 -->
        <span>{$article.view_count|number_format} 次浏览</span>

        <!-- 字符串截取 -->
        <p>{$article.summary|substr=0,100}...</p>
    </div>
{/carefree:article}
```

---

## 性能优化建议

1. **合理使用 limit 参数**
   - 首页文章列表：10-20篇
   - 侧边栏推荐：5-10篇
   - 标签云：20-30个

2. **避免过度嵌套**
   - 嵌套层级不超过3层
   - 单页标签使用不超过20个

3. **利用缓存**
   - 大部分标签都有自动缓存
   - 静态页面生成后无性能影响

4. **按需加载**
   - 不在首页加载所有内容
   - 使用AJAX动态加载评论等

---

## 下一步学习

1. 📖 查看 [完整标签列表](CAREFREE_TAGLIB_V1.5.md)
2. 🎯 学习 [实战示例集](CAREFREE_EXAMPLES.md)
3. 🔧 参考 [故障排查指南](CAREFREE_TROUBLESHOOTING.md)
4. 💡 浏览 [最佳实践](CAREFREE_BEST_PRACTICES.md)

---

## 获取帮助

遇到问题？

1. 查看文档的常见问题部分
2. 检查参数是否正确
3. 清理缓存后重试：`php think clear`
4. 查看服务器错误日志

---

**恭喜！你已经掌握了 Carefree 标签库的基础使用。开始创建你的第一个模板吧！** 🎉
