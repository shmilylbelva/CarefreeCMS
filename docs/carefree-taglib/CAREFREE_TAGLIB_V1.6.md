# Carefree 标签库 V1.6 更新说明

## 版本信息

- **版本号**: V1.6
- **发布日期**: 2025年11月04日
- **更新类型**: 重大功能增强
- **CMS版本**: 1.3.0+

## 新增功能概览

V1.6 版本带来了一个重要的功能增强：**全面支持变量参数**。现在你可以在标签中使用模板变量作为参数值，大大提升了标签的灵活性和实用性。

### 🎯 核心改进

**变量参数支持** - 所有主要标签现在都支持使用变量作为参数值，实现动态数据查询。

---

## 一、变量参数支持

### 功能说明

在 V1.6 之前，标签参数只能使用固定值：

```html
<!-- ❌ 旧版本：只能使用固定值 -->
{carefree:article typeid='1' limit='10'}
    <!-- ... -->
{/carefree:article}
```

V1.6 版本开始，可以使用变量作为参数值：

```html
<!-- ✅ 新版本：支持变量参数 -->
{carefree:article typeid='$category.id' limit='10'}
    <!-- ... -->
{/carefree:article}
```

### 适用场景

#### 1. 分类页面 - 动态加载分类文章

```html
<!-- category.html - 分类页面模板 -->
<h1>{$category.name}</h1>
<p>{$category.description}</p>

{carefree:article typeid='$category.id' limit='10' order='create_time desc'}
    <article class="article-item">
        <h2><a href="/article/{$article.id}.html">{$article.title}</a></h2>
        <p>{$article.summary}</p>
    </article>
{/carefree:article}
```

**说明**: `typeid='$category.id'` 会根据当前分类动态查询该分类下的文章。

#### 2. 标签页面 - 动态加载标签文章

```html
<!-- tag.html - 标签页面模板 -->
<h1>标签：{$tag.name}</h1>

{carefree:article tagid='$tag.id' limit='10' order='create_time desc'}
    <article class="article-item">
        <h2><a href="/article/{$article.id}.html">{$article.title}</a></h2>
    </article>
{/carefree:article}
```

**说明**: `tagid='$tag.id'` 会根据当前标签动态查询带有该标签的文章。

#### 3. 文章详情页 - 相关文章推荐

```html
<!-- article.html - 文章详情页模板 -->
<article>
    <h1>{$article.title}</h1>
    <div>{$article.content}</div>
</article>

<!-- 相关文章 -->
<div class="related-articles">
    <h3>相关推荐</h3>
    {carefree:related aid='$article.id' limit='5' type='tag'}
        <div class="related-item">
            <a href="/article/{$related.id}.html">{$related.title}</a>
        </div>
    {/carefree:related}
</div>
```

**说明**: `aid='$article.id'` 会根据当前文章ID动态推荐相关文章。

#### 4. 上下篇导航

```html
<!-- article.html - 文章详情页模板 -->
{carefree:prevnext aid='$article.id' catid='$article.category_id' type='same'}
    {if $prev}
        <a href="/article/{$prev.id}.html" class="prev">
            上一篇：{$prev.title}
        </a>
    {/if}
    {if $next}
        <a href="/article/{$next.id}.html" class="next">
            下一篇：{$next.title}
        </a>
    {/if}
{/carefree:prevnext}
```

**说明**: `aid='$article.id'` 和 `catid='$article.category_id'` 动态获取上下篇文章。

#### 5. 会员中心 - 用户投稿列表

```html
<!-- contributions.html - 我的投稿页面 -->
{carefree:contribution userid='$current_user_id' status='$status' limit='10'}
    <div class="contrib-item">
        <h3>{$contrib.title}</h3>
        <span class="status">{$contrib.status_text}</span>
        <span class="time">{$contrib.create_time|date='Y-m-d'}</span>
    </div>
{/carefree:contribution}
```

**说明**: `userid='$current_user_id'` 和 `status='$status'` 动态筛选当前用户的投稿。

#### 6. 通知中心 - 用户通知列表

```html
<!-- notifications.html - 通知中心页面 -->
{carefree:notification userid='$current_user_id' type='$type' limit='20'}
    <div class="notice-item {$notice.is_read ? '' : 'unread'}">
        <div class="notice-content">{$notice.content}</div>
        <div class="notice-time">{$notice.create_time|date='Y-m-d H:i'}</div>
    </div>
{/carefree:notification}
```

**说明**: `userid='$current_user_id'` 和 `type='$type'` 动态筛选当前用户的通知。

#### 7. 分页功能

```html
<!-- 文章列表分页 -->
{carefree:pagelist
    total='$total'
    pagesize='$pagesize'
    currentpage='$current_page'
    url='/articles.html?page={page}'
    style='full' /}
```

**说明**: 使用变量动态生成分页导航。

---

## 二、支持变量参数的标签

### 完整列表

以下标签已全面支持变量参数：

| 标签 | 支持变量的参数 | 示例 |
|------|--------------|------|
| **article** | typeid, tagid | `typeid='$category.id'` `tagid='$tag.id'` |
| **category** | parent | `parent='$category.id'` |
| **link** | group | `group='$group_name'` |
| **slider** | group | `group='$slider_group'` |
| **related** | aid | `aid='$article.id'` |
| **prevnext** | aid, catid | `aid='$article.id'` `catid='$article.category_id'` |
| **contribution** | userid, status | `userid='$current_user_id'` `status='$status'` |
| **notification** | userid, type | `userid='$current_user_id'` `type='$type'` |
| **pagelist** | total, pagesize, currentpage | `total='$total'` `pagesize='$pagesize'` |

---

## 三、变量语法规则

### 1. 基本语法

```html
<!-- 使用变量 -->
{carefree:article typeid='$category.id'}

<!-- 使用固定值（仍然支持） -->
{carefree:article typeid='1'}

<!-- 使用字符串值 -->
{carefree:link group='home'}
```

### 2. 对象属性访问

使用点号 `.` 访问对象属性：

```html
<!-- 正确 ✅ -->
{carefree:article typeid='$category.id'}
{carefree:related aid='$article.id'}
{carefree:prevnext aid='$article.id' catid='$article.category_id'}

<!-- 错误 ❌ -->
{carefree:article typeid='$category[id]'}  <!-- 不要使用数组语法 -->
```

### 3. 变量必须存在

使用的变量必须在模板中已定义（通常由控制器传递）：

```php
// 控制器中
return view('category', [
    'category' => $category,  // 必须传递 category 变量
    'articles' => $articles
]);
```

```html
<!-- 模板中才能使用 -->
{carefree:article typeid='$category.id'}
```

---

## 四、实战案例

### 案例1：完整的分类页面

```html
<!DOCTYPE html>
<html>
<head>
    <title>{$category.name} - 文章列表</title>
</head>
<body>
    <!-- 面包屑导航 -->
    <nav class="breadcrumb">
        <a href="/">首页</a>
        <span>/</span>
        <span>{$category.name}</span>
    </nav>

    <!-- 分类信息 -->
    <div class="category-header">
        <h1>{$category.name}</h1>
        <p>{$category.description}</p>
    </div>

    <!-- 使用变量参数动态加载该分类的文章 -->
    <div class="article-list">
        {carefree:article typeid='$category.id' limit='10' order='create_time desc'}
            <article class="article-card">
                <h2><a href="/article/{$article.id}.html">{$article.title}</a></h2>
                <div class="meta">
                    <span>{$article.create_time|date='Y-m-d'}</span>
                    <span>{$article.view_count} 阅读</span>
                </div>
                <p>{$article.summary}</p>
            </article>
        {/carefree:article}
    </div>
</body>
</html>
```

### 案例2：文章详情页完整示例

```html
<!DOCTYPE html>
<html>
<head>
    <title>{$article.title}</title>
</head>
<body>
    <!-- 文章内容 -->
    <article class="article-detail">
        <h1>{$article.title}</h1>
        <div class="article-meta">
            <span>作者：{$article.author_name}</span>
            <span>发布时间：{$article.create_time|date='Y-m-d H:i'}</span>
            <span>阅读：{$article.view_count}</span>
        </div>
        <div class="article-content">
            {$article.content|raw}
        </div>
    </article>

    <!-- 上下篇导航 - 使用变量参数 -->
    <div class="article-nav">
        {carefree:prevnext aid='$article.id' catid='$article.category_id' type='same'}
            <div class="prev">
                {if $prev}
                    <a href="/article/{$prev.id}.html">
                        <span>上一篇</span>
                        <h4>{$prev.title}</h4>
                    </a>
                {else}
                    <span class="disabled">没有上一篇</span>
                {/if}
            </div>
            <div class="next">
                {if $next}
                    <a href="/article/{$next.id}.html">
                        <span>下一篇</span>
                        <h4>{$next.title}</h4>
                    </a>
                {else}
                    <span class="disabled">没有下一篇</span>
                {/if}
            </div>
        {/carefree:prevnext}
    </div>

    <!-- 相关文章 - 使用变量参数 -->
    <div class="related-articles">
        <h3>相关推荐</h3>
        {carefree:related aid='$article.id' limit='6' type='tag'}
            <div class="related-item">
                <a href="/article/{$related.id}.html">
                    <img src="{$related.cover_image}" alt="{$related.title}">
                    <h4>{$related.title}</h4>
                </a>
            </div>
        {/carefree:related}
    </div>
</body>
</html>
```

### 案例3：会员投稿管理

```html
<!-- contributions.html -->
<!DOCTYPE html>
<html>
<head>
    <title>我的投稿</title>
</head>
<body>
    <div class="container">
        <h1>我的投稿</h1>

        <!-- 状态筛选 -->
        <div class="filter-tabs">
            <a href="?status=" class="{$status == '' ? 'active' : ''}">全部</a>
            <a href="?status=pending" class="{$status == 'pending' ? 'active' : ''}">待审核</a>
            <a href="?status=approved" class="{$status == 'approved' ? 'active' : ''}">已通过</a>
            <a href="?status=rejected" class="{$status == 'rejected' ? 'active' : ''}">已拒绝</a>
        </div>

        <!-- 投稿列表 - 使用变量参数动态筛选 -->
        <div class="contrib-list">
            {carefree:contribution
                userid='$current_user_id'
                status='$status'
                limit='10'
                orderby='create_time'}

                <div class="contrib-item status-{$contrib.status}">
                    <h3>{$contrib.title}</h3>
                    <div class="contrib-meta">
                        <span class="status-badge">{$contrib.status_text}</span>
                        <span>{$contrib.create_time|date='Y-m-d H:i'}</span>
                    </div>
                    {if $contrib.status == 'rejected' && $contrib.reject_reason}
                        <p class="reject-reason">拒绝原因：{$contrib.reject_reason}</p>
                    {/if}
                    <div class="contrib-actions">
                        <a href="/contribution/edit/{$contrib.id}">编辑</a>
                        <a href="/contribution/delete/{$contrib.id}">删除</a>
                    </div>
                </div>
            {/carefree:contribution}
        </div>

        <!-- 分页 - 使用变量参数 -->
        {carefree:pagelist
            total='$total'
            pagesize='$pagesize'
            currentpage='$current_page'
            url='/contributions.html?status={$status}&page={page}'
            style='full' /}
    </div>
</body>
</html>
```

---

## 五、升级指南

### 对现有代码的影响

**完全向后兼容！** V1.6 版本完全兼容之前的代码，无需修改现有模板。

```html
<!-- 旧代码依然正常工作 ✅ -->
{carefree:article typeid='1' limit='10'}
    <!-- ... -->
{/carefree:article}

<!-- 新功能可选使用 ✅ -->
{carefree:article typeid='$category.id' limit='10'}
    <!-- ... -->
{/carefree:article}
```

### 升级步骤

1. **更新代码**: 将 `backend/app/taglib/Carefree.php` 更新到最新版本
2. **清理缓存**: 删除 `runtime/temp/*.php` 中的模板缓存
3. **测试功能**: 在开发环境测试新功能
4. **逐步应用**: 在需要的地方使用变量参数

### 推荐升级场景

优先在以下场景使用变量参数：

1. ✅ 分类页面 - 动态加载分类文章
2. ✅ 标签页面 - 动态加载标签文章
3. ✅ 文章详情 - 相关推荐、上下篇
4. ✅ 会员中心 - 用户专属数据
5. ✅ 动态分页 - 灵活的分页URL

---

## 六、技术实现

### 自动变量解析

V1.6 使用 ThinkPHP 的 `autoBuildVar()` 方法自动解析变量：

```php
// 模板中
{carefree:article typeid='$category.id'}

// 编译后的PHP代码
<?php
$articles = \app\service\tag\ArticleTagService::getList([
    'typeid' => $category['id'],  // 自动转换
    'limit' => 10
]);
?>
```

### 支持的变量格式

- `$var` - 简单变量
- `$obj.prop` - 对象属性（自动转换为 `$obj['prop']`）
- `$obj.nested.prop` - 嵌套属性（自动转换为 `$obj['nested']['prop']`）

---

## 七、常见问题

### Q1: 变量不存在会怎样？

如果变量不存在，查询参数会使用默认值（通常是0或空）：

```html
<!-- 如果 $category 不存在 -->
{carefree:article typeid='$category.id'}
    <!-- 等同于 typeid='0'，不会报错 -->
{/carefree:article}
```

### Q2: 可以混用变量和固定值吗？

可以！在同一个标签中自由混用：

```html
{carefree:article typeid='$category.id' limit='10' order='create_time desc'}
    <!-- typeid用变量，limit和order用固定值 -->
{/carefree:article}
```

### Q3: 支持字符串变量吗？

支持！字符串变量会自动处理：

```html
{carefree:link group='$group_name'}
{carefree:slider group='$slider_position'}
{carefree:contribution status='$filter_status'}
```

### Q4: 如何调试变量值？

在模板中直接输出变量查看：

```html
<!-- 调试输出 -->
<p>分类ID: {$category.id}</p>

<!-- 使用变量 -->
{carefree:article typeid='$category.id'}
```

---

## 八、总结

### 主要优势

1. **更灵活** - 模板可以根据上下文动态调整数据
2. **更强大** - 一个模板适配多种场景
3. **更简洁** - 减少控制器中的重复代码
4. **完全兼容** - 不影响现有代码

### 适用场景

- ✅ 需要动态数据的页面（分类、标签、详情）
- ✅ 用户个性化内容（会员中心、通知）
- ✅ 关联数据查询（相关文章、上下篇）
- ✅ 动态筛选和分页

### 下一步

1. 查看 **[实战示例集](CAREFREE_EXAMPLES.md)** 了解更多应用场景
2. 参考 **[最佳实践](CAREFREE_BEST_PRACTICES.md)** 优化使用方式
3. 遇到问题查看 **[故障排查指南](CAREFREE_TROUBLESHOOTING.md)**

---

**Carefree 标签库 V1.6 - 让模板更灵活，让开发更高效！**
