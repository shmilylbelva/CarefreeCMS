# Carefree 自定义标签库使用指南

## 概述

Carefree 标签库是为本CMS系统开发的自定义模板标签系统，扩展了 ThinkPHP 8.0 的模板引擎功能。通过使用 Carefree 标签，您可以在模板中轻松调用系统数据，无需在控制器中预先分配变量。

标签库以 `carefree` 作为前缀，参考了 EyouCMS 的设计理念，提供了一套简洁、强大的模板标签系统。

## 安装配置

### 1. 文件结构

```
backend/
├── app/
│   ├── taglib/
│   │   └── Carefree.php           # 标签库主类
│   └── service/
│       └── tag/
│           ├── ArticleTagService.php    # 文章标签服务
│           ├── CategoryTagService.php   # 分类标签服务
│           ├── TagTagService.php        # 标签标签服务
│           ├── ConfigTagService.php     # 配置标签服务
│           └── NavTagService.php        # 导航标签服务
└── config/
    └── view.php                    # 视图配置（已配置）
```

### 2. 配置说明

在 `config/view.php` 中已添加以下配置：

```php
'taglib_pre_load' => 'app\\taglib\\Carefree',
```

这将自动加载 Carefree 标签库，无需在每个模板中手动声明。

## 标签语法

### 基本语法

```html
{carefree:tagname attr='value' attr2='value2' /}          <!-- 单标签 -->
{carefree:tagname attr='value'}...内容...{/carefree:tagname}  <!-- 闭合标签 -->
```

### 通用属性

大多数列表类标签支持以下通用属性：

- `id`: 循环变量名（默认值因标签而异）
- `key`: 索引变量名（默认为 `key`，从 0 开始）
- `i`: 序号变量名（自动生成，从 1 开始）
- `mod`: 奇偶数变量名（自动生成，0 或 1）

## 可用标签

### 1. 文章列表标签 (article)

用于输出文章列表，支持多种筛选和排序方式。

**标签语法：**

```html
{carefree:article typeid='分类ID' limit='数量' order='排序' flag='标识' titlelen='标题长度' id='变量名'}
    <a href="/article/{$article.id}.html">{$article.title}</a>
{/carefree:article}
```

**属性说明：**

| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| `typeid` | 分类ID，0表示所有分类 | 0 | `typeid='1'` |
| `limit` | 显示数量 | 10 | `limit='20'` |
| `order` | 排序方式 | `create_time desc` | `order='view_count desc'` |
| `flag` | 文章标识：`hot`(热门)、`recommend`(推荐)、`top`(置顶) | 无 | `flag='hot'` |
| `titlelen` | 标题截取长度（字符数） | 0（不截取） | `titlelen='30'` |
| `id` | 循环变量名 | `article` | `id='item'` |

**可用字段：**

```php
{$article.id}              // 文章ID
{$article.title}           // 文章标题
{$article.description}     // 文章描述
{$article.content}         // 文章内容
{$article.cover_image}     // 封面图
{$article.view_count}      // 浏览量
{$article.create_time}     // 创建时间
{$article.category.id}     // 分类ID
{$article.category.name}   // 分类名称
{$article.user.username}   // 作者用户名
{$article.tags}            // 标签数组
{$key}                     // 索引（从0开始）
{$i}                       // 序号（从1开始）
{$mod}                     // 奇偶数（0或1）
```

**使用示例：**

```html
<!-- 示例1: 获取首页推荐文章（10篇） -->
{carefree:article flag='recommend' limit='10'}
<article class="article-card">
    <div class="article-image">
        <a href="/article/{$article.id}.html">
            <img src="{$article.cover_image}" alt="{$article.title}">
        </a>
    </div>
    <div class="article-body">
        <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
        <p>{$article.description}</p>
        <div class="meta">
            <span>{$article.create_time|date='Y-m-d'}</span>
            <span>{$article.view_count} 阅读</span>
        </div>
    </div>
</article>
{/carefree:article}

<!-- 示例2: 获取某分类下的热门文章 -->
{carefree:article typeid='1' flag='hot' limit='5' id='hot'}
<li>
    <a href="/article/{$hot.id}.html">{$hot.title}</a>
    <span class="count">{$hot.view_count}</span>
</li>
{/carefree:article}

<!-- 示例3: 获取最新文章，标题截取30字 -->
{carefree:article limit='20' order='create_time desc' titlelen='30'}
<div class="item {if condition='$mod eq 0'}even{else}odd{/if}">
    <span class="num">{$i}</span>
    <a href="/article/{$article.id}.html">{$article.title}</a>
</div>
{/carefree:article}
```

### 2. 分类列表标签 (category)

用于输出分类列表。

**标签语法：**

```html
{carefree:category parent='父ID' limit='数量' id='变量名'}
    <a href="/category/{$category.id}.html">{$category.name}</a>
{/carefree:category}
```

**属性说明：**

| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| `parent` | 父分类ID，0表示顶级分类 | 0 | `parent='0'` |
| `limit` | 显示数量，0表示不限制 | 0 | `limit='10'` |
| `id` | 循环变量名 | `category` | `id='cat'` |

**可用字段：**

```php
{$category.id}          // 分类ID
{$category.name}        // 分类名称
{$category.description} // 分类描述
{$category.parent_id}   // 父分类ID
{$category.sort}        // 排序
```

**使用示例：**

```html
<!-- 示例1: 获取所有顶级分类 -->
{carefree:category parent='0'}
<li><a href="/category/{$category.id}.html">{$category.name}</a></li>
{/carefree:category}

<!-- 示例2: 获取指定分类的子分类 -->
{carefree:category parent='1' limit='5' id='cat'}
<div class="category-item">
    <h4>{$cat.name}</h4>
    <p>{$cat.description}</p>
</div>
{/carefree:category}
```

### 3. 标签列表标签 (tag)

用于输出标签云或标签列表。

**标签语法：**

```html
{carefree:tag limit='数量' order='排序' id='变量名'}
    <a href="/tag/{$tag.id}.html">{$tag.name}</a>
{/carefree:tag}
```

**属性说明：**

| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| `limit` | 显示数量，0表示不限制 | 0 | `limit='20'` |
| `order` | 排序方式 | `sort asc` | `order='article_count desc'` |
| `id` | 循环变量名 | `tag` | `id='t'` |

**排序选项：**

- `sort asc` / `sort desc` - 按排序字段
- `article_count desc` - 按文章数量（热门标签）
- `create_time desc` - 按创建时间

**可用字段：**

```php
{$tag.id}            // 标签ID
{$tag.name}          // 标签名称
{$tag.description}   // 标签描述
{$tag.sort}          // 排序
```

**使用示例：**

```html
<!-- 示例1: 热门标签云（按文章数量排序） -->
<div class="tag-cloud">
{carefree:tag limit='30' order='article_count desc'}
    <a href="/tag/{$tag.id}.html" class="tag-item">{$tag.name}</a>
{/carefree:tag}
</div>

<!-- 示例2: 所有标签列表 -->
{carefree:tag order='sort asc' id='t'}
<li>
    <a href="/tag/{$t.id}.html">{$t.name}</a>
</li>
{/carefree:tag}
```

### 4. 网站配置标签 (config)

用于输出网站配置信息（单标签）。

**标签语法：**

```html
{carefree:config name='配置名' /}
```

**属性说明：**

| 属性 | 说明 | 必填 | 示例 |
|------|------|------|------|
| `name` | 配置项名称 | 是 | `name='site_name'` |

**常用配置项：**

**基础配置：**
- `site_name` - 网站名称
- `site_logo` - 网站Logo
- `site_favicon` - 网站图标
- `site_url` - 网站URL
- `site_copyright` - 版权信息
- `site_icp` - ICP备案号
- `site_police` - 公安备案号

**SEO配置：**
- `seo_title` - SEO标题
- `seo_keywords` - SEO关键词
- `seo_description` - SEO描述
- `site_keywords` - 网站关键词（同seo_keywords）
- `site_description` - 网站描述（同seo_description）

**上传配置：**
- `upload_max_size` - 最大上传大小(MB)
- `upload_allowed_ext` - 允许的文件扩展名
- `upload_image_ext` - 允许的图片扩展名
- `upload_file_ext` - 允许的文件扩展名
- `upload_video_ext` - 允许的视频扩展名

**文章配置：**
- `article_page_size` - 文章列表每页数量
- `article_default_views` - 文章默认浏览量
- `article_default_downloads` - 文章默认下载量

**模板配置：**
- `default_template` - 默认模板
- `current_template_theme` - 当前模板主题

**使用示例：**

```html
<!-- 示例1: 输出网站名称 -->
<title>{carefree:config name='site_name' /}</title>

<!-- 示例2: 页脚版权信息 -->
<footer>
    <p>{carefree:config name='site_copyright' /}</p>
    <p><a href="https://beian.miit.gov.cn/">{carefree:config name='site_icp' /}</a></p>
</footer>

<!-- 示例3: SEO信息 -->
<meta name="keywords" content="{carefree:config name='seo_keywords' /}">
<meta name="description" content="{carefree:config name='seo_description' /}">

<!-- 示例4: 网站Logo -->
<img src="{carefree:config name='site_logo' /}" alt="{carefree:config name='site_name' /}">
```

### 5. 导航菜单标签 (nav)

用于输出网站导航菜单。

**标签语法：**

```html
{carefree:nav limit='数量' id='变量名'}
    <a href="{$nav.url}">{$nav.title}</a>
{/carefree:nav}
```

**属性说明：**

| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| `limit` | 显示数量，0表示不限制 | 0 | `limit='10'` |
| `id` | 循环变量名 | `nav` | `id='menu'` |

**可用字段：**

```php
{$nav.id}            // 导航ID
{$nav.title}         // 导航标题
{$nav.url}           // 导航链接
{$nav.type}          // 导航类型（home/articles/category/page）
{$nav.sort}          // 排序
```

**使用示例：**

```html
<!-- 示例1: 主导航菜单 -->
<nav class="main-nav">
    <ul>
    {carefree:nav limit='10'}
        <li><a href="{$nav.url}">{$nav.title}</a></li>
    {/carefree:nav}
    </ul>
</nav>

<!-- 示例2: 响应式导航（带当前状态） -->
{carefree:nav id='menu'}
<li class="nav-item">
    <a href="{$menu.url}" class="{if condition='$menu.url eq $current_url'}active{/if}">
        {$menu.title}
    </a>
</li>
{/carefree:nav}
```

## 完整模板示例

### 博客首页模板

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{carefree:config name='site_name' /}</title>
    <meta name="keywords" content="{carefree:config name='seo_keywords' /}">
    <meta name="description" content="{carefree:config name='seo_description' /}">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- 导航栏 -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="/">{carefree:config name='site_name' /}</a>
            </div>
            <nav class="nav">
                <ul>
                {carefree:nav limit='8'}
                    <li><a href="{$nav.url}">{$nav.title}</a></li>
                {/carefree:nav}
                </ul>
            </nav>
        </div>
    </header>

    <!-- 主内容 -->
    <main class="main">
        <div class="container">
            <div class="content">
                <!-- 推荐文章 -->
                <section class="featured">
                    <h2>推荐文章</h2>
                    <div class="article-grid">
                    {carefree:article flag='recommend' limit='6'}
                        <article class="article-card">
                            <a href="/article/{$article.id}.html">
                                <img src="{$article.cover_image}" alt="{$article.title}">
                            </a>
                            <div class="article-info">
                                <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
                                <p>{$article.description}</p>
                                <div class="meta">
                                    <span><i class="icon-calendar"></i> {$article.create_time|date='Y-m-d'}</span>
                                    <span><i class="icon-eye"></i> {$article.view_count}</span>
                                </div>
                            </div>
                        </article>
                    {/carefree:article}
                    </div>
                </section>

                <!-- 最新文章 -->
                <section class="latest">
                    <h2>最新文章</h2>
                    {carefree:article limit='20' order='create_time desc' titlelen='50'}
                    <article class="article-item {if condition='$mod eq 0'}even{else}odd{/if}">
                        <div class="article-image">
                            <a href="/article/{$article.id}.html">
                                <img src="{$article.cover_image}" alt="{$article.title}">
                            </a>
                        </div>
                        <div class="article-content">
                            <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
                            <p>{$article.description}</p>
                            <div class="meta">
                                {if condition="$article.category"}
                                <a href="/category/{$article.category.id}.html" class="category">
                                    {$article.category.name}
                                </a>
                                {/if}
                                <span>{$article.create_time|date='Y-m-d H:i'}</span>
                            </div>
                        </div>
                    </article>
                    {/carefree:article}
                </section>
            </div>

            <!-- 侧边栏 -->
            <aside class="sidebar">
                <!-- 热门文章 -->
                <div class="widget">
                    <h4>热门文章</h4>
                    <ul class="hot-list">
                    {carefree:article flag='hot' limit='10' id='hot'}
                        <li>
                            <span class="num">{$i}</span>
                            <a href="/article/{$hot.id}.html">{$hot.title}</a>
                        </li>
                    {/carefree:article}
                    </ul>
                </div>

                <!-- 分类列表 -->
                <div class="widget">
                    <h4>文章分类</h4>
                    <ul class="category-list">
                    {carefree:category parent='0' limit='10'}
                        <li><a href="/category/{$category.id}.html">{$category.name}</a></li>
                    {/carefree:category}
                    </ul>
                </div>

                <!-- 标签云 -->
                <div class="widget">
                    <h4>热门标签</h4>
                    <div class="tag-cloud">
                    {carefree:tag limit='30' order='article_count desc'}
                        <a href="/tag/{$tag.id}.html">{$tag.name}</a>
                    {/carefree:tag}
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="footer">
        <div class="container">
            <p>{carefree:config name='site_copyright' /} |
               <a href="https://beian.miit.gov.cn/">{carefree:config name='site_icp' /}</a>
            </p>
        </div>
    </footer>
</body>
</html>
```

### 文章详情页模板

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{$article.title} - {carefree:config name='site_name' /}</title>
    <meta name="keywords" content="{$article.keywords}">
    <meta name="description" content="{$article.description}">
</head>
<body>
    <header class="header">
        <!-- 导航同首页 -->
    </header>

    <main class="main">
        <div class="container">
            <article class="article-detail">
                <h1>{$article.title}</h1>
                <div class="article-meta">
                    {if condition="$article.category"}
                    <a href="/category/{$article.category.id}.html">{$article.category.name}</a>
                    {/if}
                    <span>{$article.create_time|date='Y-m-d H:i'}</span>
                    <span>{$article.view_count} 阅读</span>
                </div>
                <div class="article-content">
                    {$article.content|raw}
                </div>
            </article>

            <!-- 相关文章 -->
            <section class="related">
                <h3>相关文章</h3>
                <div class="related-grid">
                {carefree:article typeid='{$article.category_id}' limit='3' id='related'}
                    {if condition="$related.id neq $article.id"}
                    <div class="related-item">
                        <a href="/article/{$related.id}.html">
                            <img src="{$related.cover_image}" alt="{$related.title}">
                            <h4>{$related.title}</h4>
                        </a>
                    </div>
                    {/if}
                {/carefree:article}
                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <!-- 页脚同首页 -->
    </footer>
</body>
</html>
```

## 高级用法

### 1. 嵌套使用标签

```html
<!-- 分类及其文章列表 -->
{carefree:category parent='0' limit='5' id='cat'}
<div class="category-section">
    <h3>{$cat.name}</h3>
    <div class="articles">
    {carefree:article typeid='{$cat.id}' limit='5'}
        <div class="article-item">
            <a href="/article/{$article.id}.html">{$article.title}</a>
        </div>
    {/carefree:article}
    </div>
</div>
{/carefree:category}
```

### 2. 结合 ThinkPHP 原生标签

```html
{carefree:article limit='10' flag='recommend'}
    {if condition="$key lt 3"}
    <!-- 前3篇显示大图 -->
    <div class="article-featured">
        <img src="{$article.cover_image}" alt="{$article.title}">
        <h2>{$article.title}</h2>
    </div>
    {else}
    <!-- 其他显示列表 -->
    <div class="article-list-item">
        <h3>{$article.title}</h3>
    </div>
    {/if}
{/carefree:article}
```

### 3. 使用循环变量

```html
{carefree:article limit='20'}
<div class="item item-{$i} {if condition='$mod eq 0'}even{else}odd{/if}">
    <span class="index">{$i}</span>
    <a href="/article/{$article.id}.html">{$article.title}</a>
    {if condition="$i eq 1"}
    <span class="badge">最新</span>
    {/if}
</div>
{/carefree:article}
```

## 性能优化

### 1. 使用缓存

配置和导航数据已自动缓存：
- 配置数据：缓存1小时
- 导航菜单：缓存30分钟

### 2. 限制查询数量

始终为列表标签设置合理的 `limit` 值：

```html
<!-- 好的做法 -->
{carefree:article limit='10'}...{/carefree:article}

<!-- 避免的做法 -->
{carefree:article}...{/carefree:article}  <!-- 可能查询大量数据 -->
```

### 3. 避免过度嵌套

过多的标签嵌套会影响性能：

```html
<!-- 不推荐：3层嵌套 -->
{carefree:category}
    {carefree:article typeid='{$category.id}'}
        {carefree:tag}...{/carefree:tag}
    {/carefree:article}
{/carefree:category}
```

## 常见问题

### Q: 标签不生效怎么办？

A: 检查以下几点：
1. 确认 `config/view.php` 中已配置 `taglib_pre_load`
2. 清除模板缓存：`php think clear`
3. 检查标签语法是否正确

### Q: 如何添加自定义标签？

A: 在 `app/taglib/Carefree.php` 中：
1. 在 `$tags` 数组中添加标签定义
2. 创建对应的 `tag标签名` 方法
3. 创建对应的服务类（如需要）

### Q: 标签可以在哪些地方使用？

A: Carefree 标签可以在所有 ThinkPHP 模板文件（.html）中使用，包括：
- 页面模板
- 布局模板
- 区块模板

### Q: 如何调试标签输出？

A: 可以在标签内使用 `{$article|json_encode}` 查看完整数据结构。

## 扩展开发

如需添加新标签，请参考以下步骤：

1. **在 `Carefree.php` 中定义标签**：
```php
protected $tags = [
    'custom' => ['attr' => 'param1,param2', 'close' => 1],
];
```

2. **实现标签处理方法**：
```php
public function tagCustom($tag, $content)
{
    $param1 = $tag['param1'] ?? '';
    // 处理逻辑...
    return $parseStr;
}
```

3. **创建服务类**（如需要）：
```php
namespace app\service\tag;

class CustomTagService
{
    public static function getData($params)
    {
        // 数据查询逻辑...
    }
}
```

## 更新日志

### v1.0.0 (2024-10-28)

- ✅ 实现文章列表标签 (article)
- ✅ 实现分类列表标签 (category)
- ✅ 实现标签列表标签 (tag)
- ✅ 实现网站配置标签 (config)
- ✅ 实现导航菜单标签 (nav)
- ✅ 创建完整的服务层
- ✅ 集成缓存机制
- ✅ 完善文档和示例

## 技术支持

如有问题或建议，请参考项目文档或联系开发团队。
