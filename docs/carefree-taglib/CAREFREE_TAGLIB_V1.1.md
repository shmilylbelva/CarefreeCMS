# Carefree 标签库 v1.1 更新说明

## 版本信息

- **版本号**: v1.1.0
- **发布日期**: 2025-10-28
- **更新类型**: 功能增强

## 更新内容

### 1. Bug 修复

#### ✅ 修复 NavTagService 数据库字段问题
- **问题**: NavTagService 引用了不存在的 `show_in_nav` 字段
- **影响**: 导致页面构建失败 (HTTP 500 错误)
- **修复**: 移除了对不存在字段的引用，简化导航查询逻辑
- **涉及文件**: `app/service/tag/NavTagService.php`

### 2. 新增标签

#### 🆕 友情链接标签 (link)

显示友情链接列表。

**语法：**
```html
{carefree:link group='1' limit='10' id='link'}
    <a href="{$link.url}" target="_blank" title="{$link.title}">
        {$link.title}
    </a>
{/carefree:link}
```

**属性：**
- `group`: 链接分组ID（默认: 1）
- `limit`: 显示数量（默认: 0，显示全部）
- `id`: 循环变量名（默认: link）

**可用字段：**
```php
{$link.id}          // 链接ID
{$link.title}       // 链接标题
{$link.url}         // 链接地址
{$link.description} // 链接描述
{$link.logo}        // 链接Logo
{$link.sort}        // 排序
```

**示例：**
```html
<!-- 页脚友情链接 -->
<div class="友情链接">
    <h3>友情链接</h3>
    <div class="links">
    {carefree:link group='1' limit='20'}
        <a href="{$link.url}" target="_blank" rel="nofollow" title="{$link.title}">
            {$link.title}
        </a>
    {/carefree:link}
    </div>
</div>
```

---

#### 🆕 面包屑导航标签 (breadcrumb)

自动生成面包屑导航，支持多级分类。

**语法：**
```html
{carefree:breadcrumb separator=' > ' id='item'}
    {if condition="$item.is_current"}
    <span class="current">{$item.title}</span>
    {else}
    <a href="{$item.url}">{$item.title}</a>
    {/if}
{/carefree:breadcrumb}
```

**属性：**
- `separator`: 分隔符（默认: ' > '，仅用于display，不影响HTML结构）
- `id`: 循环变量名（默认: item）

**可用字段：**
```php
{$item.title}       // 导航项标题
{$item.url}         // 导航项链接
{$item.is_current}  // 是否为当前页（true/false）
```

**示例：**
```html
<!-- 文章详情页面包屑 -->
<nav class="breadcrumb">
{carefree:breadcrumb}
    {if condition="$i gt 1"}<span class="sep"> / </span>{/if}
    {if condition="$item.is_current"}
    <span class="current">{$item.title}</span>
    {else}
    <a href="{$item.url}">{$item.title}</a>
    {/if}
{/carefree:breadcrumb}
</nav>
```

---

#### 🆕 单篇文章标签 (arcinfo)

获取指定ID的单篇文章信息，适用于推荐、置顶等场景。

**语法：**
```html
{carefree:arcinfo aid='1'}
    <h2>{$article.title}</h2>
    <div>{$article.content|raw}</div>
{/carefree:arcinfo}
```

**属性：**
- `aid`: 文章ID（必填）

**可用字段：**
所有文章字段都可用，与 `article` 标签中的 `$article` 变量相同。

**示例：**
```html
<!-- 首页置顶推荐文章 -->
{carefree:arcinfo aid='1'}
<div class="featured-article">
    <h1>{$article.title}</h1>
    <div class="cover">
        <img src="{$article.cover_image}" alt="{$article.title}">
    </div>
    <div class="excerpt">{$article.description}</div>
    <a href="/article/{$article.id}.html" class="read-more">阅读全文</a>
</div>
{/carefree:arcinfo}
```

---

#### 🆕 单个分类标签 (catinfo)

获取指定ID的单个分类信息。

**语法：**
```html
{carefree:catinfo catid='1'}
    <h1>{$category.name}</h1>
    <p>{$category.description}</p>
{/carefree:catinfo}
```

**属性：**
- `catid`: 分类ID（必填）

**可用字段：**
所有分类字段都可用。

**示例：**
```html
<!-- 显示特定分类信息 -->
{carefree:catinfo catid='1'}
<div class="category-info">
    <h2>{$category.name}</h2>
    {if condition="$category.cover_image"}
    <img src="{$category.cover_image}" alt="{$category.name}">
    {/if}
    <p>{$category.description}</p>
</div>
{/carefree:catinfo}
```

---

#### 🆕 单个标签标签 (taginfo)

获取指定ID的单个标签信息。

**语法：**
```html
{carefree:taginfo tagid='1'}
    <h1>#{$tag.name}</h1>
    <p>{$tag.description}</p>
{/carefree:taginfo}
```

**属性：**
- `tagid`: 标签ID（必填）

**可用字段：**
所有标签字段都可用。

**示例：**
```html
<!-- 显示特定标签信息 -->
{carefree:taginfo tagid='1'}
<div class="tag-info">
    <h2>#{$tag.name}</h2>
    <p>{$tag.description}</p>
    <a href="/tag/{$tag.id}.html">查看更多文章</a>
</div>
{/carefree:taginfo}
```

---

### 3. 新增服务类

#### LinkTagService
- **文件**: `app/service/tag/LinkTagService.php`
- **功能**: 处理友情链接数据查询
- **缓存**: 30分钟

#### BreadcrumbTagService
- **文件**: `app/service/tag/BreadcrumbTagService.php`
- **功能**: 自动生成面包屑导航
- **特性**: 支持多级分类、自动识别页面类型

---

## 完整示例

### 带友情链接和面包屑的文章页

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{$article.title} - {carefree:config name='web_name' /}</title>
</head>
<body>
    <!-- 导航栏 -->
    <header>
        <nav>
            <ul>
            {carefree:nav limit='8'}
                <li><a href="{$nav.url}">{$nav.title}</a></li>
            {/carefree:nav}
            </ul>
        </nav>
    </header>

    <!-- 面包屑导航 -->
    <nav class="breadcrumb">
    {carefree:breadcrumb}
        {if condition="$i gt 1"} / {/if}
        {if condition="$item.is_current"}
        <span>{$item.title}</span>
        {else}
        <a href="{$item.url}">{$item.title}</a>
        {/if}
    {/carefree:breadcrumb}
    </nav>

    <!-- 文章内容 -->
    <main>
        <article>
            <h1>{$article.title}</h1>
            <div class="meta">
                {if condition="$article.category"}
                <a href="/category/{$article.category.id}.html">{$article.category.name}</a>
                {/if}
                <span>{$article.create_time|date='Y-m-d H:i'}</span>
            </div>
            <div class="content">
                {$article.content|raw}
            </div>
        </article>

        <!-- 相关文章 -->
        <section class="related">
            <h3>相关文章</h3>
            {carefree:article typeid='{$article.category_id}' limit='5'}
            <div class="related-item">
                <a href="/article/{$article.id}.html">{$article.title}</a>
            </div>
            {/carefree:article}
        </section>
    </main>

    <!-- 侧边栏 -->
    <aside>
        <!-- 热门文章 -->
        <div class="widget">
            <h4>热门文章</h4>
            {carefree:article flag='hot' limit='10' id='hot'}
            <div class="hot-item">
                <span>{$i}</span>
                <a href="/article/{$hot.id}.html">{$hot.title}</a>
            </div>
            {/carefree:article}
        </div>

        <!-- 标签云 -->
        <div class="widget">
            <h4>热门标签</h4>
            <div class="tags">
            {carefree:tag limit='20' order='article_count desc'}
                <a href="/tag/{$tag.id}.html">{$tag.name}</a>
            {/carefree:tag}
            </div>
        </div>
    </aside>

    <!-- 页脚友情链接 -->
    <footer>
        <div class="links-section">
            <h3>友情链接</h3>
            <div class="links">
            {carefree:link group='1' limit='30'}
                <a href="{$link.url}" target="_blank" rel="nofollow" title="{$link.title}">
                    {$link.title}
                </a>
            {/carefree:link}
            </div>
        </div>

        <div class="copyright">
            <p>Copyright © 2024 {carefree:config name='web_name' /}</p>
        </div>
    </footer>
</body>
</html>
```

---

## 性能优化

### 缓存策略更新

| 服务 | 缓存时间 | 缓存键 |
|------|---------|--------|
| LinkTagService | 30分钟 | `links_group_{group}_limit_{limit}` |
| BreadcrumbTagService | 无缓存 | - (动态生成) |

### 建议

1. **友情链接**: 已自动缓存30分钟，适合大多数场景
2. **面包屑**: 根据页面上下文动态生成，无缓存
3. **单篇内容标签**: 使用模型查询，建议配合ORM缓存

---

## 升级指南

### 从 v1.0 升级到 v1.1

1. **文件更新**:
   ```bash
   # 更新主标签库文件
   backend/app/taglib/Carefree.php

   # 更新 Nav 服务（修复bug）
   backend/app/service/tag/NavTagService.php

   # 新增服务文件
   backend/app/service/tag/LinkTagService.php
   backend/app/service/tag/BreadcrumbTagService.php
   ```

2. **无需数据库变更**

3. **清除缓存**:
   ```bash
   php think clear
   ```

4. **测试构建**:
   ```bash
   # 测试构建首页
   curl -X POST http://localhost:8000/backend/build/index

   # 测试构建所有页面
   curl -X POST http://localhost:8000/backend/build/all
   ```

---

## 已知问题

无

---

## 路线图

### v1.2 计划功能

1. **空数据处理**: 为列表标签添加 `empty` 属性支持
2. **分页标签**: 添加分页导航标签
3. **评论标签**: 添加评论列表标签
4. **搜索标签**: 添加搜索结果标签
5. **幻灯片标签**: 添加轮播图标签

### v2.0 计划功能

1. **条件筛选**: 支持更复杂的条件查询
2. **关联查询**: 支持文章关联标签、分类的深度查询
3. **自定义字段**: 支持自定义字段输出
4. **JSON数据源**: 支持从API获取数据

---

## 技术支持

如有问题或建议，请：
1. 查看完整文档: `CAREFREE_TAGLIB_GUIDE.md`
2. 查看示例模板: `backend/templates/examples/carefree_tags_demo.html`
3. 提交 Issue 到项目仓库

---

## 更新日志

### v1.1.0 (2025-10-28)

**新增**:
- ✅ 友情链接标签 (link)
- ✅ 面包屑导航标签 (breadcrumb)
- ✅ 单篇文章标签 (arcinfo)
- ✅ 单个分类标签 (catinfo)
- ✅ 单个标签标签 (taginfo)
- ✅ LinkTagService 服务类
- ✅ BreadcrumbTagService 服务类

**修复**:
- ✅ 修复 NavTagService 引用不存在字段导致的构建失败

**优化**:
- ✅ 优化导航菜单查询性能
- ✅ 添加友情链接缓存机制

### v1.0.0 (2025-10-28)

**初始版本**:
- ✅ 文章列表标签 (article)
- ✅ 分类列表标签 (category)
- ✅ 标签列表标签 (tag)
- ✅ 网站配置标签 (config)
- ✅ 导航菜单标签 (nav)
- ✅ 基础服务层
- ✅ 完整文档

---

**感谢使用 Carefree 标签库！**
