# Carefree 标签库 V1.5 更新说明

## 版本信息

- **版本号**: V1.5
- **发布日期**: 2025年10月
- **更新类型**: 重大功能更新

## 新增功能概览

V1.5 版本带来了4个全新标签和article标签的重大增强：

### 🎯 新增标签

1. **author（热门作者）** - 展示网站热门作者排行
2. **archive（文章归档）** - 按年/月/日归档文章
3. **seo（SEO优化）** - 自动生成完整SEO meta标签
4. **share（社交分享）** - 快速生成社交分享按钮

### ⚡ 功能增强

5. **article标签增强** - 新增7个强大参数，支持更精细的文章筛选

---

## 一、热门作者标签（author）

### 功能说明
展示网站的热门作者，支持按发文数、浏览量、点赞数排序。

### 基本语法
```html
{carefree:author limit='10' orderby='article' id='author'}
    <div class="author-item">
        <img src="{$author.avatar}" alt="{$author.display_name}">
        <div class="author-name">{$author.display_name}</div>
        <div class="author-stats">
            {$author.article_count} 篇 • {$author.total_views} 阅读
        </div>
    </div>
{/carefree:author}
```

### 参数说明

| 参数 | 默认值 | 说明 |
|------|--------|------|
| limit | 10 | 显示数量 |
| orderby | article | 排序方式：article(发文数)、view(浏览量)、like(点赞数) |
| id | author | 循环变量名 |
| empty | - | 空数据提示 |

### 可用字段
- `id` - 用户ID
- `username` - 用户名
- `display_name` - 显示名称
- `avatar` - 头像
- `role_name` - 角色名称
- `article_count` - 发文数
- `total_views` - 总浏览量
- `total_likes` - 总点赞数
- `avg_views` - 平均浏览量
- `url` - 作者主页URL

### 使用示例

```html
<!-- 侧边栏热门作者 -->
<div class="hot-authors">
    <h3>热门作者</h3>
    {carefree:author limit='5' orderby='view' id='author'}
        <div class="author-card">
            <img src="{$author.avatar}" class="avatar">
            <div class="info">
                <strong>{$author.display_name}</strong>
                <span>{$author.article_count}篇文章</span>
            </div>
        </div>
    {/carefree:author}
</div>
```

---

## 二、归档标签（archive）

### 功能说明
按年、月或日归档文章，自动统计每个时间段的文章数量。

### 基本语法
```html
{carefree:archive type='month' limit='12' format='Y年m月' id='archive'}
    <div class="archive-item">
        <a href="{$archive.url}">
            {$archive.display_date} ({$archive.article_count})
        </a>
    </div>
{/carefree:archive}
```

### 参数说明

| 参数 | 默认值 | 说明 |
|------|--------|------|
| type | month | 归档类型：year(按年)、month(按月)、day(按日) |
| limit | 12 | 显示数量 |
| format | Y年m月 | PHP日期格式 |
| id | archive | 循环变量名 |
| empty | - | 空数据提示 |

### 可用字段
- `archive_date` - 归档日期（如：2025-10）
- `display_date` - 格式化显示日期
- `article_count` - 该时间段文章数
- `year` - 年份
- `month` - 月份
- `day` - 日期
- `url` - 归档页面URL

### 使用示例

```html
<!-- 侧边栏归档列表 -->
<div class="archives">
    <h3>文章归档</h3>
    <ul>
        {carefree:archive type='month' limit='12' id='arc'}
            <li>
                <a href="{$arc.url}">
                    {$arc.display_date} <span>({$arc.article_count})</span>
                </a>
            </li>
        {/carefree:archive}
    </ul>
</div>

<!-- 按年归档 -->
{carefree:archive type='year' limit='5' format='Y年' id='year'}
    <div class="year-archive">
        <h4>{$year.display_date}</h4>
        <p>共 {$year.article_count} 篇文章</p>
    </div>
{/carefree:archive}
```

---

## 三、SEO标签（seo）

### 功能说明
自动生成完整的SEO meta标签，包括基础meta、Open Graph和Twitter Card。

### 基本语法
```html
<head>
    <title>{$article.seo_title}</title>
    {carefree:seo
        title='$article.seo_title'
        keywords='$article.seo_keywords'
        description='$article.seo_description'
        image='$article.cover_image'
        type='article' /}
</head>
```

### 参数说明

| 参数 | 说明 |
|------|------|
| title | SEO标题 |
| keywords | 关键词 |
| description | 描述 |
| image | 封面图片URL |
| type | 页面类型（website/article） |

### 生成的标签

- `<meta name="keywords">` - 关键词
- `<meta name="description">` - 描述
- `<meta property="og:*">` - Open Graph标签（Facebook分享）
- `<meta property="twitter:*">` - Twitter Card标签

### 使用示例

```html
<!-- 首页 -->
{carefree:seo
    title='$config.site_name'
    keywords='$config.site_keywords'
    description='$config.site_description'
    type='website' /}

<!-- 文章详情页 -->
{carefree:seo
    title='$article.seo_title'
    keywords='$article.seo_keywords'
    description='$article.seo_description'
    image='$article.cover_image'
    type='article' /}
```

---

## 四、社交分享标签（share）

### 功能说明
快速生成社交分享按钮，支持多个主流平台。

### 基本语法
```html
{carefree:share platforms='wechat,weibo,qq,twitter,facebook' size='normal' style='icon' /}
```

### 参数说明

| 参数 | 默认值 | 说明 |
|------|--------|------|
| platforms | wechat,weibo,qq,twitter,facebook | 平台列表（逗号分隔） |
| size | normal | 大小：small/normal/large |
| style | icon | 样式：icon(仅图标)/text(图标+文字) |

### 支持平台

- `wechat` - 微信
- `weibo` - 微博
- `qq` - QQ
- `twitter` - Twitter
- `facebook` - Facebook
- `linkedin` - LinkedIn

### 使用示例

```html
<!-- 文章底部分享 -->
<div class="article-share">
    <h4>分享到：</h4>
    {carefree:share platforms='wechat,weibo,qq,twitter,facebook' style='text' /}
</div>

<!-- 侧边浮动分享 -->
<div class="sidebar-share">
    {carefree:share platforms='wechat,weibo,qq' size='small' style='icon' /}
</div>
```

### CSS样式参考

```css
.social-share {
    display: flex;
    gap: 10px;
}

.social-share a {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    transition: all 0.3s;
}

.share-wechat { background: #09bb07; color: white; }
.share-weibo { background: #e6162d; color: white; }
.share-qq { background: #12b7f5; color: white; }
.share-twitter { background: #1da1f2; color: white; }
.share-facebook { background: #1877f2; color: white; }

.social-share a:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
```

---

## 五、article标签增强

### 新增参数

V1.5 为 article 标签新增了7个强大参数，支持更精细的文章筛选：

| 参数 | 说明 | 示例 |
|------|------|------|
| tagid | 按标签ID筛选 | `tagid='5'` |
| userid | 按作者ID筛选 | `userid='1'` |
| offset | 偏移量（分页） | `offset='10' limit='10'` |
| hascover | 是否有封面图 | `hascover='1'`（1-有，0-无） |
| exclude | 排除文章ID | `exclude='1,2,3'` |
| days | 最近N天的文章 | `days='7'`（最近7天） |

### 原有参数

- `typeid` - 分类ID
- `limit` - 数量限制
- `order` - 排序方式
- `flag` - 文章标识（hot/recommend/top/random/updated）
- `titlelen` - 标题截取长度
- `id` - 循环变量名
- `empty` - 空数据提示

### 使用示例

#### 示例1：显示某个标签下的文章

```html
<!-- 显示"技术"标签下的文章 -->
{carefree:article tagid='5' limit='10' id='article'}
    <div class="article-item">
        <h3>{$article.title}</h3>
        <p>{$article.summary}</p>
    </div>
{/carefree:article}
```

#### 示例2：显示某个作者的最新文章

```html
<!-- 显示作者ID为1的最新10篇文章 -->
{carefree:author limit='1' id='author'}
    <h2>{$author.display_name}的最新文章</h2>
    {carefree:article userid='{$author.id}' limit='10' id='article'}
        <div>{$article.title}</div>
    {/carefree:article}
{/carefree:author}
```

#### 示例3：分页显示

```html
<!-- 第一页 -->
{carefree:article limit='10' id='article'}
    ...
{/carefree:article}

<!-- 第二页 -->
{carefree:article offset='10' limit='10' id='article'}
    ...
{/carefree:article}
```

#### 示例4：只显示有封面图的文章

```html
{carefree:article hascover='1' limit='6' id='article'}
    <div class="article-card">
        <img src="{$article.cover_image}" alt="{$article.title}">
        <h3>{$article.title}</h3>
    </div>
{/carefree:article}
```

#### 示例5：排除当前文章，显示相关推荐

```html
<!-- 在文章详情页 -->
<h3>更多推荐</h3>
{carefree:article
    typeid='{$article.category_id}'
    exclude='{$article.id}'
    limit='5'
    id='more'}
    <div>{$more.title}</div>
{/carefree:article}
```

#### 示例6：最近7天的热门文章

```html
<h3>本周热门</h3>
{carefree:article days='7' flag='hot' limit='10' id='hot'}
    <div class="hot-item">
        <span class="rank">{$i}</span>
        <a href="/article/{$hot.id}.html">{$hot.title}</a>
        <span class="views">{$hot.view_count}</span>
    </div>
{/carefree:article}
```

---

## 六、性能优化

### 缓存策略

| 功能 | 缓存时间 | 说明 |
|------|---------|------|
| 热门作者 | 1小时 | 作者数据变化较慢 |
| 文章归档 | 1小时 | 归档列表变化较慢 |
| article增强查询 | 无缓存 | 保证数据实时性 |

### 优化建议

1. **author标签**
   - 限制显示数量（建议 ≤ 20）
   - 优先使用 orderby='article'（最快）

2. **archive标签**
   - 按月归档性能最佳
   - limit建议不超过24个月

3. **article增强参数**
   - tagid查询会使用子查询，注意性能
   - exclude参数不要排除太多ID
   - offset+limit适合分页，不适合大偏移量

4. **SEO和share标签**
   - 纯HTML生成，无性能影响
   - 建议在<head>中使用seo标签

---

## 七、升级指南

### 从V1.4升级到V1.5

1. **文件更新**
   - 替换 `backend/app/taglib/Carefree.php`
   - 更新 `backend/app/service/tag/ArticleTagService.php`
   - 新增 `backend/app/service/tag/AuthorTagService.php`
   - 新增 `backend/app/service/tag/ArchiveTagService.php`

2. **兼容性**
   - ✅ 完全兼容V1.0-V1.4所有功能
   - ✅ article标签新增参数不影响原有用法
   - ✅ 无需修改现有模板

3. **测试新功能**
   ```bash
   # 清理缓存
   php think clear

   # 测试构建
   curl -X POST "http://localhost:8000/backend/build/index" \
     -H "Authorization: Bearer YOUR_TOKEN"
   ```

---

## 八、完整标签列表

V1.5 版本共包含 **18个核心标签**：

### 内容标签
- article - 文章列表（✨增强）
- category - 分类列表
- tag - 标签列表
- related - 相关文章
- comment - 评论列表

### 导航标签
- nav - 导航菜单
- breadcrumb - 面包屑
- pagelist - 分页导航
- archive - 文章归档（🆕）

### 信息标签
- arcinfo - 单篇文章
- catinfo - 单个分类
- taginfo - 单个标签
- userinfo - 用户信息
- author - 热门作者（🆕）

### 功能标签
- config - 网站配置
- stats - 统计数据
- search - 搜索框
- slider - 幻灯片
- ad - 广告位
- link - 友情链接
- tagcloud - 标签云
- seo - SEO优化（🆕）
- share - 社交分享（🆕）

---

## 九、下一步规划

V1.6 版本计划功能：
- 面包屑导航增强
- 排行榜标签（热门、最新、推荐）
- RSS订阅标签
- Sitemap生成标签
- 更多社交平台分享支持

---

## 版本历史

- **V1.5** (2025-10) - 热门作者、归档、SEO、分享、article增强
- **V1.4** (2025-10) - 相关文章、标签云、搜索、评论、用户信息
- **V1.3** (2025-10) - 广告位、随机文章、最近更新、统计
- **V1.2** (2025-09) - 空数据处理、幻灯片、分页
- **V1.1** (2025-08) - 友链、面包屑、单项信息
- **V1.0** (2025-07) - 基础标签功能

---

© 2025 Carefree 标签库 - 让模板开发更加自由
