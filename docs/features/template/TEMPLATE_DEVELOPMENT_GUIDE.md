# CMS模板开发指南

## 目录
1. [模板结构](#模板结构)
2. [语法规范](#语法规范)
3. [变量使用](#变量使用)
4. [常见问题](#常见问题)
5. [最佳实践](#最佳实践)
6. [调试技巧](#调试技巧)

---

## 模板结构

### 目录结构
```
templates/
└── your-template/          # 模板名称（小写字母、数字、中划线）
    ├── index.html          # 首页模板（必需）
    ├── layout.html         # 布局模板（必需）
    ├── article.html        # 文章详情模板（必需）
    ├── articles.html       # 文章列表模板（可选）
    ├── category.html       # 分类页模板（必需）
    ├── tag.html           # 标签页模板（必需）
    ├── page.html          # 单页模板（必需）
    ├── assets/            # 静态资源目录
    │   ├── css/          # 样式文件
    │   ├── js/           # JavaScript文件
    │   └── images/       # 图片资源
    └── README.md          # 模板说明文档
```

### 必需文件说明

- **layout.html**: 页面布局框架，包含头部、导航、页脚等公共部分
- **index.html**: 网站首页
- **article.html**: 文章详情页
- **category.html**: 分类文章列表页
- **tag.html**: 标签文章列表页
- **page.html**: 单页面（如关于我们、联系方式等）

---

## 语法规范

### 1. 模板引擎基础

本CMS使用ThinkPHP模板引擎，语法如下：

#### 变量输出
```html
<!-- 基础输出 -->
{$变量名}

<!-- 对象属性 -->
{$user.username}
{$article.title}

<!-- 数组元素 -->
{$data[0]}
{$data['key']}

<!-- 默认值（Elvis运算符） -->
{$config.site_name ?: '默认名称'}

<!-- 注意：支持三元运算符 -->
{$status == 1 ? '已发布' : '草稿'}
```

#### 条件判断
```html
<!-- if 条件 -->
{if condition="$is_home"}
    首页内容
{else /}
    其他页面内容
{/if}

<!-- 多条件 -->
{if condition="$status == 1"}
    已发布
{elseif condition="$status == 0" /}
    草稿
{else /}
    其他状态
{/if}

<!-- 判断变量是否存在 -->
{if condition="isset($categories) && count($categories) > 0"}
    有分类
{/if}
```

#### 循环遍历
```html
<!-- volist 循环 -->
{volist name="articles" id="article"}
    <div>{$article.title}</div>
{/volist}

<!-- 带偏移和限制 -->
{volist name="categories" id="cat" offset="0" length="5"}
    <li>{$cat.name}</li>
{/volist}

<!-- 空数据提示 -->
{volist name="articles" id="article"}
    <div>{$article.title}</div>
{empty /}
    <div>暂无数据</div>
{/volist}

<!-- foreach 循环 -->
{foreach $articles as $k => $article}
    <div>{$k}: {$article.title}</div>
{/foreach}
```

#### 模板继承
```html
<!-- 父模板 layout.html -->
<!DOCTYPE html>
<html>
<head>
    {block name="style"}{/block}
</head>
<body>
    {block name="content"}{/block}
    {block name="script"}{/block}
</body>
</html>

<!-- 子模板 index.html -->
{extend name="layout" /}

{block name="content"}
    页面内容
{/block}

{block name="script"}
    <script src="..."></script>
{/block}
```

### 2. JavaScript语法限制 ⚠️

**重要：ThinkPHP模板引擎会解析`=>`符号，导致箭头函数语法错误**

#### ❌ 错误写法（会导致解析错误）
```html
<script>
// 不要使用箭头函数！
array.forEach(item => console.log(item));

array.map(x => x * 2);

element.addEventListener('click', () => {
    console.log('clicked');
});
</script>
```

#### ✅ 正确写法（使用传统函数）
```html
<script>
// 使用传统函数语法
array.forEach(function(item) {
    console.log(item);
});

array.map(function(x) {
    return x * 2;
});

element.addEventListener('click', function() {
    console.log('clicked');
});
</script>
```

#### 解决方案选项

**方案1：使用传统函数（推荐）**
```javascript
// 简单明了，兼容性最好
elements.forEach(function(el) {
    el.classList.add('active');
});
```

**方案2：将JS代码放到外部文件**
```html
<!-- 在模板中引用外部JS文件 -->
<script src="/assets/js/main.js"></script>
```
外部JS文件可以随意使用箭头函数，因为不会被模板引擎解析。

**方案3：使用{literal}标签（仅特殊情况）**
```html
{literal}
<script>
// 在literal标签内可以使用箭头函数
// 但这会禁用该区域的所有模板语法
array.forEach(item => console.log(item));
</script>
{/literal}
```

### 3. 运算符使用

#### 支持的运算符
```html
<!-- Elvis运算符（三元简写） -->
{$title ?: '默认标题'}

<!-- 三元运算符 -->
{$status == 1 ? '在线' : '离线'}

<!-- 逻辑运算符 -->
{if condition="$age > 18 && $verified"}
{if condition="$status == 1 || $status == 2"}
{if condition="!empty($name)"}
```

#### ❌ 不支持的运算符
```html
<!-- null合并运算符 - 有时会出问题 -->
{$link.url ?? '#'}  <!-- 可能报错，改用 ?: -->

<!-- 解构赋值 -->
<!-- 不支持 -->
```

#### 建议写法
```html
<!-- 推荐使用 Elvis 运算符 -->
{$config.site_name ?: '默认网站名'}

<!-- 嵌套三元 -->
{$config.seo_title ?: ($config.site_name ?: '默认标题')}
```

---

## 变量使用

### 系统预定义变量

#### 全局配置变量 `$config`
```html
{$config.site_name}         <!-- 网站名称 -->
{$config.seo_title}         <!-- SEO标题 -->
{$config.seo_keywords}      <!-- SEO关键词 -->
{$config.seo_description}   <!-- SEO描述 -->
{$config.site_logo}         <!-- 网站Logo -->
{$config.site_icp}          <!-- ICP备案号 -->
{$config.site_copyright}    <!-- 版权信息 -->

<!-- 或者使用 carefree:config 标签 -->
{carefree:config name='site_name' /}
{carefree:config name='seo_title' /}
{carefree:config name='seo_keywords' /}
{carefree:config name='seo_description' /}
{carefree:config name='site_logo' /}
{carefree:config name='site_icp' /}
{carefree:config name='site_copyright' /}
```

#### 首页变量 `index.html`
```html
{$articles}                 <!-- 文章列表数组 -->
{$categories}               <!-- 分类列表数组 -->
{$hot_articles}             <!-- 热门文章数组 -->
{$tags}                     <!-- 标签列表数组 -->
{$links}                    <!-- 友情链接数组 -->
{$is_home}                  <!-- 是否首页 true/false -->
```

#### 文章详情变量 `article.html`
```html
{$article.id}               <!-- 文章ID -->
{$article.title}            <!-- 文章标题 -->
{$article.content}          <!-- 文章内容（HTML） -->
{$article.summary}          <!-- 文章摘要 -->
{$article.cover_image}      <!-- 封面图 -->
{$article.author}           <!-- 作者 -->
{$article.publish_time}     <!-- 发布时间 -->
{$article.view_count}       <!-- 浏览量 -->
{$article.like_count}       <!-- 点赞数 -->
{$article.comment_count}    <!-- 评论数 -->
{$article.category.name}    <!-- 所属分类名称 -->
{$article.tags}             <!-- 标签数组 -->

{$related_articles}         <!-- 相关文章数组 -->
{$prev_article}             <!-- 上一篇文章对象 -->
{$next_article}             <!-- 下一篇文章对象 -->
```

#### 分类页变量 `category.html`
```html
{$category.id}              <!-- 分类ID -->
{$category.name}            <!-- 分类名称 -->
{$category.description}     <!-- 分类描述 -->
{$articles}                 <!-- 该分类下的文章列表 -->
{$total}                    <!-- 文章总数 -->
{$page}                     <!-- 当前页码 -->
```

#### 标签页变量 `tag.html`
```html
{$tag.id}                   <!-- 标签ID -->
{$tag.name}                 <!-- 标签名称 -->
{$articles}                 <!-- 该标签下的文章列表 -->
{$total}                    <!-- 文章总数 -->
```

#### 单页变量 `page.html`
```html
{$page.id}                  <!-- 页面ID -->
{$page.title}               <!-- 页面标题 -->
{$page.content}             <!-- 页面内容 -->
{$page.cover_image}         <!-- 封面图 -->
```

### SEO变量使用
```html
<!-- 在 layout.html 中使用 -->
<head>
    {if condition="$is_home"}
        <title>{carefree:config name='site_name' /}</title>
        <meta name="keywords" content="{carefree:config name='seo_keywords' /}">
        <meta name="description" content="{carefree:config name='seo_description' /}">
    {else /}
        <title>{$title} - {carefree:config name='site_name' /}</title>
        <meta name="keywords" content="{$keywords}">
        <meta name="description" content="{$description}">
    {/if}
</head>

<!-- 或者使用 $config 变量 -->
<head>
    {if condition="$is_home"}
        <title>{$config.site_name}</title>
        <meta name="keywords" content="{$config.seo_keywords}">
        <meta name="description" content="{$config.seo_description}">
    {else /}
        <title>{$title} - {$config.site_name}</title>
        <meta name="keywords" content="{$keywords}">
        <meta name="description" content="{$description}">
    {/if}
</head>
```

---

## 常见问题

### 1. 语法错误: unexpected token "="

**症状**：生成静态页面时报错 "语法错误: unexpected token \"=\""

**原因**：模板中使用了JavaScript箭头函数（`=>`）

**解决方案**：
```javascript
// ❌ 错误
array.forEach(item => console.log(item));

// ✅ 正确
array.forEach(function(item) {
    console.log(item);
});
```

### 2. 语法错误: unexpected token ":"

**症状**：生成静态页面时报错 "syntax error, unexpected token \":\""

**原因**：使用了ThinkPHP不支持的复杂管道语法，特别是在Elvis运算符内部使用管道函数

**错误示例**：
```html
<!-- ❌ 错误 - 管道参数使用了等号 -->
{$article.content|strip_tags|mb_substr=0,120,'utf-8'}

<!-- ❌ 错误 - Elvis运算符内使用复杂管道 -->
{$article.description ?: ($article.content|raw|strip_tags|mb_substr=0,120,'utf-8').'...'}
```

**解决方案**：改用函数调用语法
```html
<!-- ✅ 正确 - 使用函数调用 -->
{mb_substr(strip_tags($article.content), 0, 120, 'utf-8')}

<!-- ✅ 正确 - Elvis运算符配合函数调用 -->
{$article.description ?: mb_substr(strip_tags($article.content), 0, 120, 'utf-8')}...
```

**重要提示**：
- ThinkPHP模板引擎的管道语法在某些复杂场景下会解析失败
- 推荐使用传统的函数调用语法，更可靠且易读
- 如果必须使用管道，避免嵌套和在运算符表达式内使用

### 3. 变量未定义

**症状**：页面显示 `{$variable}` 原样输出

**原因**：
- 变量名拼写错误
- 后端未传递该变量
- 变量作用域问题

**解决方案**：
```html
<!-- 使用默认值 -->
{$article.title ?: '无标题'}

<!-- 先判断是否存在 -->
{if condition="isset($article)"}
    {$article.title}
{/if}
```

### 3. 图片路径问题

**症状**：图片无法显示

**解决方案**：
```html
<!-- 使用绝对路径 -->
<img src="/assets/images/logo.png" alt="Logo">

<!-- 提供占位图 -->
<img src="{$article.cover_image ?: '/assets/images/article.png'}" alt="{$article.title}">

<!-- 使用onerror处理 -->
<img src="{$image}" onerror="this.src='/assets/images/error.png'" alt="">
```

### 4. 样式不生效

**原因**：
- CSS文件路径错误
- CSS选择器优先级问题
- 缓存问题

**解决方案**：
```html
<!-- 使用绝对路径 -->
<link rel="stylesheet" href="/assets/css/style.css">

<!-- 开发时添加版本号防止缓存 -->
<link rel="stylesheet" href="/assets/css/style.css?v=1.0.0">
```

### 5. 分页问题

**解决方案**：
```html
<!-- 文章列表分页 -->
{if condition="$total > $limit"}
<div class="pagination">
    {if condition="$page > 1"}
    <a href="?page={$page - 1}">上一页</a>
    {/if}

    <span>第 {$page} 页 / 共 {ceil($total / $limit)} 页</span>

    {if condition="$page < ceil($total / $limit)"}
    <a href="?page={$page + 1}">下一页</a>
    {/if}
</div>
{/if}
```

---

## 最佳实践

### 1. 资源管理 ⚠️

#### 禁止使用 CDN 资源（强制规则）

**规则**：所有模板不允许使用 CDN 资源，所有资源必须在本地

```html
<!-- ❌ 禁止 - 不要使用任何 CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- ✅ 正确 - 使用本地资源 -->
<link href="/assets/css/bootstrap.min.css" rel="stylesheet">
<script src="/assets/js/main.js"></script>
<link href="/assets/css/bootstrap-icons.min.css" rel="stylesheet">
```

**原因**：
- 确保离线环境可用
- 避免外部依赖导致的加载失败
- 提高页面加载速度和稳定性
- 满足安全合规要求
- 避免 CDN 服务商问题影响网站

**本地化第三方库**：
- 下载库文件到 `templates/{模板名}/assets/` 目录
- 修改引用路径为本地路径
- 参考文档：`/docs/LOCAL_ASSETS_GUIDE.md`

#### 资源目录结构

```
templates/{模板名}/assets/
├── css/
│   ├── bootstrap.min.css          # 第三方CSS库
│   ├── bootstrap-icons.min.css    # 图标库CSS
│   ├── common.css                 # 公共样式
│   ├── layout.css                 # 布局样式
│   └── ...
├── js/
│   ├── bootstrap.bundle.min.js    # 第三方JS库
│   ├── main.js                    # 主要功能
│   └── ...
├── fonts/
│   ├── bootstrap-icons.woff2      # 字体文件
│   └── ...
└── images/
    ├── logo.png
    ├── placeholder/
    └── ...
```

### 2. 代码组织

#### 模块化CSS
```
assets/
└── css/
    ├── common.css      # 公共样式（重置、工具类）
    ├── layout.css      # 布局样式（头部、导航、页脚）
    ├── components.css  # 组件样式（卡片、按钮、表单）
    └── pages/
        ├── index.css   # 首页特定样式
        ├── article.css # 文章页特定样式
        └── ...
```

#### 模块化JavaScript
```
assets/
└── js/
    ├── main.js         # 主要功能（导航、回到顶部等）
    ├── slider.js       # 轮播图
    ├── search.js       # 搜索功能
    └── utils.js        # 工具函数
```

### 2. 性能优化

#### 图片优化
```html
<!-- 使用WebP格式 -->
<picture>
    <source srcset="{$article.cover_image}.webp" type="image/webp">
    <img src="{$article.cover_image}" alt="{$article.title}">
</picture>

<!-- 懒加载 -->
<img data-src="{$image}" class="lazyload" alt="">
```

#### CSS优化
```html
<!-- 关键CSS内联 -->
<style>
    /* 首屏关键样式 */
    .header { ... }
</style>

<!-- 非关键CSS延迟加载 -->
<link rel="preload" href="/assets/css/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
```

#### JavaScript优化
```html
<!-- 使用defer属性 -->
<script src="/assets/js/main.js" defer></script>

<!-- 或放在body底部 -->
<body>
    <!-- 页面内容 -->
    <script src="/assets/js/main.js"></script>
</body>
```

### 3. 响应式设计

```css
/* 移动优先 */
.container {
    width: 100%;
    padding: 0 15px;
}

/* 平板 */
@media (min-width: 768px) {
    .container {
        max-width: 750px;
        margin: 0 auto;
    }
}

/* 桌面 */
@media (min-width: 1200px) {
    .container {
        max-width: 1170px;
    }
}
```

### 4. 可访问性

```html
<!-- 语义化HTML -->
<header>...</header>
<nav>...</nav>
<main>...</main>
<article>...</article>
<aside>...</aside>
<footer>...</footer>

<!-- ARIA标签 -->
<button aria-label="搜索">
    <i class="bi bi-search"></i>
</button>

<!-- Alt属性 -->
<img src="..." alt="具体描述图片内容">
```

### 5. SEO优化

```html
<!-- 结构化数据 -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{$article.title}",
    "datePublished": "{$article.publish_time}",
    "author": {
        "@type": "Person",
        "name": "{$article.author}"
    }
}
</script>

<!-- Open Graph -->
<meta property="og:title" content="{$article.title}">
<meta property="og:description" content="{$article.summary}">
<meta property="og:image" content="{$article.cover_image}">
```

---

## 调试技巧

### 1. 变量调试

```html
<!-- 查看变量内容 -->
{$article|dump}

<!-- 查看数据类型 -->
{$article|var_dump}

<!-- 输出JSON -->
<script>
console.log({$article|json_encode});
</script>
```

### 2. 条件调试

```html
<!-- 显示调试信息 -->
{if condition="true"}
<div style="background: yellow; padding: 10px; margin: 10px;">
    <strong>调试信息：</strong><br>
    文章ID: {$article.id}<br>
    标题: {$article.title}<br>
    状态: {$article.status}<br>
</div>
{/if}
```

### 3. 浏览器开发工具

- **Elements**: 检查HTML结构和CSS样式
- **Console**: 查看JavaScript错误和日志
- **Network**: 检查资源加载情况
- **Application**: 查看缓存和存储

### 4. 常见错误排查

#### 模板语法错误
```bash
# 检查PHP语法
php -l template.html

# 查看ThinkPHP日志
tail -f runtime/log/*.log
```

#### 静态生成失败
1. 检查模板文件权限
2. 检查输出目录是否可写
3. 查看错误日志
4. 验证模板语法

---

## 模板提交清单

开发完成后，请确认：

- [ ] 所有必需文件都已创建（layout.html, index.html等）
- [ ] JavaScript代码使用传统函数语法（不使用箭头函数）
- [ ] 所有图片都有alt属性
- [ ] CSS文件已压缩
- [ ] JavaScript文件已压缩
- [ ] 测试了响应式布局（手机、平板、桌面）
- [ ] 验证了SEO标签（title, meta, og标签）
- [ ] 测试了所有页面类型（首页、文章、分类、标签、单页）
- [ ] 检查了浏览器兼容性
- [ ] 编写了README.md说明文档
- [ ] 提供了模板截图

---

## 示例模板

参考现有模板：
- `templates/default/` - 默认模板（简洁风格）
- `templates/blog/` - 博客模板（现代风格）
- `templates/green/` - 绿色模板（环保主题）

---

## 技术支持

如有问题，请查看：
- ThinkPHP模板引擎文档: https://www.kancloud.cn/manual/thinkphp6_0/1037637
- 项目文档: `/docs/`
- 提交Issue: GitHub仓库

---

**文档版本**: v1.0
**最后更新**: 2025-10-28
**维护者**: CMS开发团队
