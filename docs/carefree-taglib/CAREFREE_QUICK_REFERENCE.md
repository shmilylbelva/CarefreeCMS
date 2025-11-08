# Carefree 标签库快速参考 v1.6

## 🆕 V1.6 重要更新

**全面支持变量参数！** 现在可以在标签中使用模板变量作为参数值：

```html
<!-- ✅ 支持变量参数 -->
{carefree:article typeid='$category.id' limit='10'}
{carefree:related aid='$article.id' limit='5'}
{carefree:contribution userid='$current_user_id'}

<!-- ✅ 仍然支持固定值 -->
{carefree:article typeid='1' limit='10'}
```

**支持变量的标签**: article, category, link, slider, related, prevnext, contribution, notification, pagelist

详见 **[V1.6 更新说明](CAREFREE_TAGLIB_V1.6.md)**

---

## 快速索引

| 标签 | 用途 | 版本 | 缓存 |
|------|------|------|------|
| `{carefree:article}` | 文章列表 | v1.0 | ❌ |
| `{carefree:category}` | 分类列表 | v1.0 | ❌ |
| `{carefree:tag}` | 标签列表 | v1.0 | ❌ |
| `{carefree:config}` | 网站配置 | v1.0 | ✅ 1小时 |
| `{carefree:nav}` | 导航菜单 | v1.0 | ✅ 30分钟 |
| `{carefree:link}` | 友情链接 | v1.1 | ✅ 30分钟 |
| `{carefree:breadcrumb}` | 面包屑 | v1.1 | ❌ |
| `{carefree:arcinfo}` | 单篇文章 | v1.1 | ❌ |
| `{carefree:catinfo}` | 单个分类 | v1.1 | ❌ |
| `{carefree:taginfo}` | 单个标签 | v1.1 | ❌ |

---

## 常用代码片段

### 1. 首页推荐文章
```html
{carefree:article flag='recommend' limit='6'}
<div class="article-card">
    <a href="/article/{$article.id}.html">
        <img src="{$article.cover_image}" alt="{$article.title}">
        <h3>{$article.title}</h3>
    </a>
</div>
{/carefree:article}
```

### 2. 侧边栏热门文章
```html
{carefree:article flag='hot' limit='10' id='hot'}
<div class="hot-item">
    <span>{$i}</span>
    <a href="/article/{$hot.id}.html">{$hot.title}</a>
</div>
{/carefree:article}
```

### 3. 导航菜单
```html
<nav>
    <ul>
    {carefree:nav limit='8'}
        <li><a href="{$nav.url}">{$nav.title}</a></li>
    {/carefree:nav}
    </ul>
</nav>
```

### 4. 面包屑导航 (v1.1 新增)
```html
{carefree:breadcrumb}
    {if condition="$i gt 1"} / {/if}
    {if condition="$item.is_current"}
    <span>{$item.title}</span>
    {else}
    <a href="{$item.url}">{$item.title}</a>
    {/if}
{/carefree:breadcrumb}
```

### 5. 友情链接 (v1.1 新增)
```html
<div class="links">
{carefree:link group='1' limit='20'}
    <a href="{$link.url}" target="_blank" rel="nofollow">
        {$link.title}
    </a>
{/carefree:link}
</div>
```

### 6. 推荐文章展示 (v1.1 新增)
```html
{carefree:arcinfo aid='1'}
<div class="featured">
    <h1>{$article.title}</h1>
    <div>{$article.description}</div>
    <a href="/article/{$article.id}.html">阅读全文</a>
</div>
{/carefree:arcinfo}
```

### 7. 分类及其文章
```html
{carefree:catinfo catid='1'}
<section>
    <h2>{$category.name}</h2>
    <p>{$category.description}</p>

    {carefree:article typeid='{$category.id}' limit='5'}
    <article>
        <a href="/article/{$article.id}.html">{$article.title}</a>
    </article>
    {/carefree:article}
</section>
{/carefree:catinfo}
```

### 8. 热门标签云
```html
<div class="tag-cloud">
{carefree:tag limit='30' order='article_count desc'}
    <a href="/tag/{$tag.id}.html">{$tag.name}</a>
{/carefree:tag}
</div>
```

### 9. 网站配置
```html
<title>{carefree:config name='site_name' /}</title>
<meta name="keywords" content="{carefree:config name='seo_keywords' /}">
<meta name="description" content="{carefree:config name='seo_description' /}">

<!-- 其他常用配置 -->
<img src="{carefree:config name='site_logo' /}" alt="网站Logo">
<p>{carefree:config name='site_copyright' /}</p>
<a href="https://beian.miit.gov.cn/">{carefree:config name='site_icp' /}</a>
```

### 10. 完整页面模板
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title} - {carefree:config name='site_name' /}</title>
</head>
<body>
    <!-- 导航 -->
    <nav>
        {carefree:nav limit='8'}
        <a href="{$nav.url}">{$nav.title}</a>
        {/carefree:nav}
    </nav>

    <!-- 面包屑 -->
    {carefree:breadcrumb}
        {if condition="$i gt 1"} > {/if}
        {if condition="$item.is_current"}
        <span>{$item.title}</span>
        {else}
        <a href="{$item.url}">{$item.title}</a>
        {/if}
    {/carefree:breadcrumb}

    <!-- 主内容 -->
    <main>
        <!-- 推荐文章 -->
        {carefree:arcinfo aid='1'}
        <div class="featured">
            <h1>{$article.title}</h1>
            <div>{$article.content|raw}</div>
        </div>
        {/carefree:arcinfo}

        <!-- 最新文章 -->
        {carefree:article limit='10' order='create_time desc'}
        <article>
            <h2><a href="/article/{$article.id}.html">{$article.title}</a></h2>
            <p>{$article.description}</p>
        </article>
        {/carefree:article}
    </main>

    <!-- 侧边栏 -->
    <aside>
        <!-- 热门文章 -->
        {carefree:article flag='hot' limit='10' id='hot'}
        <li><a href="/article/{$hot.id}.html">{$hot.title}</a></li>
        {/carefree:article}

        <!-- 标签云 -->
        {carefree:tag limit='20' order='article_count desc'}
        <a href="/tag/{$tag.id}.html">{$tag.name}</a>
        {/carefree:tag}
    </aside>

    <!-- 页脚友情链接 -->
    <footer>
        {carefree:link group='1' limit='20'}
        <a href="{$link.url}" target="_blank">{$link.title}</a>
        {/carefree:link}

        <p>{carefree:config name='site_copyright' /}</p>
    </footer>
</body>
</html>
```

---

## 属性参考

### article 标签
| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| typeid | 分类ID | 0 (全部) | `typeid='1'` |
| limit | 数量 | 10 | `limit='20'` |
| order | 排序 | `create_time desc` | `order='view_count desc'` |
| flag | 标识 | 无 | `flag='hot'` / `'recommend'` / `'top'` |
| titlelen | 标题长度 | 0 (不截取) | `titlelen='30'` |
| id | 变量名 | article | `id='item'` |

### category 标签
| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| parent | 父分类ID | 0 | `parent='1'` |
| limit | 数量 | 0 (全部) | `limit='10'` |
| id | 变量名 | category | `id='cat'` |

### tag 标签
| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| limit | 数量 | 0 (全部) | `limit='30'` |
| order | 排序 | `sort asc` | `order='article_count desc'` |
| id | 变量名 | tag | `id='t'` |

### nav 标签
| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| limit | 数量 | 0 (全部) | `limit='8'` |
| id | 变量名 | nav | `id='menu'` |

### link 标签 (v1.1 新增)
| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| group | 分组ID | 1 | `group='1'` |
| limit | 数量 | 0 (全部) | `limit='20'` |
| id | 变量名 | link | `id='lnk'` |

### breadcrumb 标签 (v1.1 新增)
| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| separator | 分隔符 | ' > ' | `separator=' / '` |
| id | 变量名 | item | `id='crumb'` |

### arcinfo 标签 (v1.1 新增)
| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| aid | 文章ID | 0 | `aid='1'` (必填) |

### catinfo 标签 (v1.1 新增)
| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| catid | 分类ID | 0 | `catid='1'` (必填) |

### taginfo 标签 (v1.1 新增)
| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| tagid | 标签ID | 0 | `tagid='1'` (必填) |

### config 标签
| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| name | 配置名 | 无 | `name='web_name'` (必填) |

---

## 常用配置项

| 配置项 | 说明 |
|--------|------|
| web_name | 网站名称 |
| web_title | 网站标题 |
| web_keywords | 网站关键词 |
| web_description | 网站描述 |
| web_logo | 网站Logo |
| web_icp | ICP备案号 |

---

## 循环变量

在所有列表标签中，都可以使用以下变量：

| 变量 | 说明 | 示例 |
|------|------|------|
| $key | 索引（从0开始） | `{$key}` → 0, 1, 2... |
| $i | 序号（从1开始） | `{$i}` → 1, 2, 3... |
| $mod | 奇偶数（0或1） | `{$mod}` → 0, 1, 0, 1... |

**使用示例：**
```html
{carefree:article limit='10'}
<div class="item-{$i} {if condition='$mod eq 0'}even{else}odd{/if}">
    <span>#{$i}</span>
    <a href="/article/{$article.id}.html">{$article.title}</a>
</div>
{/carefree:article}
```

---

## 排序选项

### article 标签排序
- `create_time desc` - 最新发布
- `create_time asc` - 最早发布
- `view_count desc` - 浏览最多
- `update_time desc` - 最近更新

### tag 标签排序
- `sort asc` - 按排序字段升序
- `article_count desc` - 按文章数量降序（热门标签）
- `create_time desc` - 按创建时间降序

---

## 文件位置

### 核心文件
```
backend/
├── app/
│   ├── taglib/
│   │   └── Carefree.php              # 标签库主类
│   └── service/
│       └── tag/
│           ├── ArticleTagService.php    # 文章服务
│           ├── CategoryTagService.php   # 分类服务
│           ├── TagTagService.php        # 标签服务
│           ├── ConfigTagService.php     # 配置服务
│           ├── NavTagService.php        # 导航服务
│           ├── LinkTagService.php       # 友情链接服务 (v1.1)
│           └── BreadcrumbTagService.php # 面包屑服务 (v1.1)
└── config/
    └── view.php                      # 视图配置
```

### 文档文件
```
D:\work\cms\
├── CAREFREE_TAGLIB_GUIDE.md          # 完整使用指南
├── CAREFREE_TAGLIB_V1.1.md           # v1.1 更新说明
└── CAREFREE_QUICK_REFERENCE.md       # 本文件：快速参考
```

### 示例文件
```
backend/templates/examples/
├── carefree_tags_demo.html           # 基础功能演示
└── advanced_features_demo.html       # 高级功能演示 (v1.1)
```

---

## 故障排除

### 问题1: 标签不生效
**检查清单：**
1. ✅ 确认 `config/view.php` 中配置了 `taglib_pre_load`
2. ✅ 清除缓存：`php think clear`
3. ✅ 检查标签语法是否正确
4. ✅ 检查服务类文件是否存在

### 问题2: 构建失败 (HTTP 500)
**解决方案：**
1. 查看日志：`backend/runtime/log/`
2. 检查数据库连接
3. 确认所需字段存在
4. 测试单个页面构建

### 问题3: 数据不显示
**检查清单：**
1. ✅ 数据库中是否有数据
2. ✅ 状态字段是否为1（已发布）
3. ✅ 查询条件是否正确
4. ✅ 使用 `{$article|json_encode}` 查看原始数据

### 问题4: 缓存未更新
**解决方案：**
```bash
# 清除所有缓存
php think clear

# 清除指定缓存
php think cache:clear
```

---

## API 接口

### 构建静态页面
```bash
# 获取 Token
curl -X POST http://localhost:8000/backend/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# 构建首页
curl -X POST http://localhost:8000/backend/build/index \
  -H "Authorization: Bearer {token}"

# 构建所有页面
curl -X POST http://localhost:8000/backend/build/all \
  -H "Authorization: Bearer {token}"

# 同步资源文件
curl -X POST http://localhost:8000/backend/build/sync-assets \
  -H "Authorization: Bearer {token}"
```

---

## 性能建议

1. **合理设置 limit**: 避免一次查询过多数据
2. **使用 flag**: 利用预定义的筛选条件（hot/recommend/top）
3. **缓存机制**: config 和 nav 已自动缓存
4. **数据库索引**: 确保常用查询字段有索引
5. **静态化**: 使用构建功能生成静态HTML

---

## 快速开始

### 第一步：确认配置
```php
// config/view.php
'taglib_pre_load' => 'app\\taglib\\Carefree',
```

### 第二步：创建模板
```html
<!-- templates/blog/test.html -->
{extend name="layout" /}

{block name="content"}
    {carefree:article limit='10'}
    <div>{$article.title}</div>
    {/carefree:article}
{/block}
```

### 第三步：构建测试
```bash
curl -X POST http://localhost:8000/backend/build/index
```

---

## 获取帮助

- 📖 完整文档: `CAREFREE_TAGLIB_GUIDE.md`
- 🆕 更新说明: `CAREFREE_TAGLIB_V1.1.md`
- 💡 基础示例: `backend/templates/examples/carefree_tags_demo.html`
- 🎨 高级示例: `backend/templates/examples/advanced_features_demo.html`

---

**版本**: v1.1.0
**更新日期**: 2025-10-28
**作者**: Carefree Team
