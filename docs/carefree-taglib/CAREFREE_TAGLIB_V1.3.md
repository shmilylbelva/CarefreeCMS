# Carefree 标签库 V1.3 文档

## 版本信息

- **版本号**: V1.3
- **发布日期**: 2025年10月
- **更新内容**: 广告位管理、随机文章、最近更新、统计数据

## 新增功能概览

V1.3 版本新增了以下重要功能：

1. **广告标签（ad）** - 支持多广告位管理、时间控制、点击统计
2. **随机文章功能** - article 标签新增 random flag，随机展示文章
3. **最近更新功能** - article 标签新增 updated flag，显示最新修改的文章
4. **统计标签（stats）** - 显示网站各项统计数据

---

## 一、广告标签（ad）

### 1.1 功能说明

广告标签用于在网站中展示广告内容，支持：
- 多广告位管理（通过 position 区分）
- 广告时间控制（开始/结束时间）
- 点击量和浏览量统计
- 图片自动处理（支持 JSON 格式）
- 30分钟缓存机制
- 空数据提示

### 1.2 基本语法

```html
{carefree:ad position='广告位ID' limit='数量' id='变量名' empty='空提示'}
    <!-- 循环体内容 -->
{/carefree:ad}
```

### 1.3 属性说明

| 属性 | 必填 | 默认值 | 说明 |
|------|------|--------|------|
| position | 否 | 1 | 广告位ID |
| limit | 否 | 0 | 显示数量，0表示不限制 |
| id | 否 | ad | 循环变量名 |
| empty | 否 | - | 无数据时显示的提示文本 |

### 1.4 可用字段

在循环体内可以使用以下字段：

```php
$ad = [
    'id'          => 1,                    // 广告ID
    'position_id' => 1,                    // 广告位ID
    'name'        => '广告名称',           // 广告名称
    'type'        => 'image',              // 广告类型
    'content'     => '广告内容',           // 广告内容
    'link_url'    => 'https://...',        // 链接地址
    'images'      => '/uploads/ad.jpg',    // 图片地址（已自动处理）
    'start_time'  => '2025-01-01 00:00:00', // 开始时间
    'end_time'    => '2025-12-31 23:59:59', // 结束时间
    'status'      => 1,                    // 状态
    'sort'        => 1,                    // 排序
    'click_count' => 100,                  // 点击量
    'view_count'  => 1000,                 // 浏览量
]
```

### 1.5 使用示例

#### 示例 1: 顶部横幅广告

```html
{carefree:ad position='1' limit='1' id='banner'}
    <div class="top-banner">
        <a href="{$banner.link_url}" target="_blank">
            <img src="{$banner.images}" alt="{$banner.name}">
        </a>
    </div>
{/carefree:ad}
```

#### 示例 2: 侧边栏广告

```html
<div class="sidebar">
    <h3>赞助商</h3>
    {carefree:ad position='2' limit='3' id='ad' empty='暂无广告'}
        <div class="ad-item">
            <a href="{$ad.link_url}">
                <img src="{$ad.images}" alt="{$ad.name}">
            </a>
            <p class="ad-name">{$ad.name}</p>
        </div>
    {/carefree:ad}
</div>
```

#### 示例 3: 文章内容中间广告

```html
<div class="article-content">
    <!-- 文章前半部分 -->

    {carefree:ad position='3' limit='1' id='content_ad'}
        <div class="in-content-ad">
            <span class="ad-label">广告</span>
            <a href="{$content_ad.link_url}">
                <img src="{$content_ad.images}" alt="{$content_ad.name}">
            </a>
        </div>
    {/carefree:ad}

    <!-- 文章后半部分 -->
</div>
```

### 1.6 广告位规划建议

| 广告位ID | 位置 | 尺寸建议 | 说明 |
|---------|------|---------|------|
| 1 | 顶部横幅 | 1200x80 | 网站头部横幅广告 |
| 2 | 侧边栏 | 300x250 | 右侧边栏广告 |
| 3 | 内容中间 | 728x90 | 文章内容中部 |
| 4 | 底部横幅 | 1200x80 | 页面底部广告 |
| 5 | 弹窗广告 | 600x400 | 弹窗或浮层广告 |

---

## 二、随机文章功能

### 2.1 功能说明

在原有的 article 标签基础上，新增 `flag='random'` 参数，可以随机展示文章。这个功能特别适合：
- 侧边栏随机推荐
- 增加旧文章曝光度
- 提供探索性阅读体验
- 增加页面停留时间

### 2.2 基本语法

```html
{carefree:article flag='random' limit='数量' typeid='分类ID' id='变量名'}
    <!-- 文章展示内容 -->
{/carefree:article}
```

### 2.3 使用示例

#### 示例 1: 侧边栏随机推荐

```html
<div class="sidebar-random">
    <h3>随机推荐</h3>
    {carefree:article flag='random' limit='5' id='article'}
        <div class="random-item">
            <a href="/article/{$article.id}.html">{$article.title}</a>
            <span class="views">{$article.view_count} 阅读</span>
        </div>
    {/carefree:article}
</div>
```

#### 示例 2: 文章底部"相关推荐"

```html
<div class="related-articles">
    <h3>你可能还喜欢</h3>
    <div class="article-grid">
        {carefree:article flag='random' limit='4' id='article'}
            <div class="article-card">
                <h4>{$article.title}</h4>
                <p>{$article.summary}</p>
                <a href="/article/{$article.id}.html">阅读更多 →</a>
            </div>
        {/carefree:article}
    </div>
</div>
```

#### 示例 3: 指定分类的随机文章

```html
<!-- 从技术分类中随机选择3篇文章 -->
{carefree:article flag='random' typeid='2' limit='3' id='tech_article'}
    <div class="tech-random">
        <h4>{$tech_article.title}</h4>
        <p>{$tech_article.summary}</p>
    </div>
{/carefree:article}
```

### 2.4 性能说明

- 随机查询使用 `ORDER BY RAND()` 实现
- 建议配合 limit 参数控制数量
- 大数据量时考虑使用缓存优化
- 不适合频繁刷新的场景

---

## 三、最近更新功能

### 3.1 功能说明

新增 `flag='updated'` 参数，按文章的 `update_time` 字段降序排列，展示最近更新的内容。适用场景：
- 显示最新修改的文章
- 内容维护日志
- 持续更新的教程
- 版本更新记录

### 3.2 基本语法

```html
{carefree:article flag='updated' limit='数量' typeid='分类ID' id='变量名'}
    <!-- 文章展示内容 -->
{/carefree:article}
```

### 3.3 使用示例

#### 示例 1: 首页最近更新

```html
<section class="recently-updated">
    <h2>最近更新</h2>
    {carefree:article flag='updated' limit='10' id='article'}
        <div class="update-item">
            <div class="update-title">
                <a href="/article/{$article.id}.html">{$article.title}</a>
            </div>
            <div class="update-time">
                更新于 {$article.update_time|date='Y-m-d H:i'}
            </div>
        </div>
    {/carefree:article}
</section>
```

#### 示例 2: 带时间线的更新列表

```html
<div class="timeline">
    <h3>内容更新时间线</h3>
    {carefree:article flag='updated' limit='15' id='article'}
        <div class="timeline-item">
            <div class="timeline-date">
                {$article.update_time|date='Y-m-d'}
            </div>
            <div class="timeline-content">
                <h4>{$article.title}</h4>
                <p>{$article.summary}</p>
                <a href="/article/{$article.id}.html">查看详情</a>
            </div>
        </div>
    {/carefree:article}
</div>
```

#### 示例 3: 文档更新日志

```html
<div class="update-log">
    <h2>文档更新记录</h2>
    {carefree:article flag='updated' typeid='5' limit='20' id='doc'}
        <div class="log-entry">
            <span class="log-date">{$doc.update_time|date='Y-m-d'}</span>
            <span class="log-title">{$doc.title}</span>
            <span class="log-category">{$doc.category.name}</span>
        </div>
    {/carefree:article}
</div>
```

---

## 四、统计标签（stats）

### 4.1 功能说明

统计标签用于显示网站的各项统计数据，支持：
- 文章总数统计
- 分类总数统计
- 标签总数统计
- 总浏览量统计
- 今日文章数统计
- 指定分类的统计
- 1小时缓存机制

### 4.2 基本语法

```html
{carefree:stats type='统计类型' catid='分类ID' /}
```

### 4.3 属性说明

| 属性 | 必填 | 默认值 | 说明 |
|------|------|--------|------|
| type | 否 | article | 统计类型 |
| catid | 否 | 0 | 分类ID，用于统计指定分类的数据 |

### 4.4 支持的统计类型

| 类型 | 说明 | 示例 |
|------|------|------|
| article | 文章总数 | `{carefree:stats type='article' /}` |
| category | 分类总数 | `{carefree:stats type='category' /}` |
| tag | 标签总数 | `{carefree:stats type='tag' /}` |
| view | 总浏览量 | `{carefree:stats type='view' /}` |
| todayarticle | 今日文章数 | `{carefree:stats type='todayarticle' /}` |
| todayview | 今日浏览量 | `{carefree:stats type='todayview' /}` |

### 4.5 使用示例

#### 示例 1: 网站统计面板

```html
<div class="stats-panel">
    <div class="stat-item">
        <div class="stat-number">{carefree:stats type='article' /}</div>
        <div class="stat-label">文章总数</div>
    </div>

    <div class="stat-item">
        <div class="stat-number">{carefree:stats type='category' /}</div>
        <div class="stat-label">分类数</div>
    </div>

    <div class="stat-item">
        <div class="stat-number">{carefree:stats type='tag' /}</div>
        <div class="stat-label">标签数</div>
    </div>

    <div class="stat-item">
        <div class="stat-number">{carefree:stats type='view' /}</div>
        <div class="stat-label">总浏览量</div>
    </div>
</div>
```

#### 示例 2: 页脚统计信息

```html
<footer>
    <div class="footer-stats">
        本站共有 {carefree:stats type='article' /} 篇文章，
        {carefree:stats type='category' /} 个分类，
        {carefree:stats type='tag' /} 个标签，
        累计浏览 {carefree:stats type='view' /} 次
    </div>
</footer>
```

#### 示例 3: 分类页面统计

```html
<div class="category-stats">
    <h3>分类统计</h3>
    <p>该分类共有 {carefree:stats type='article' catid='{$category.id}' /} 篇文章</p>
    <p>总浏览量: {carefree:stats type='view' catid='{$category.id}' /} 次</p>
</div>
```

#### 示例 4: 今日数据展示

```html
<div class="today-stats">
    <h4>今日数据</h4>
    <ul>
        <li>今日发布: {carefree:stats type='todayarticle' /} 篇</li>
        <li>今日浏览: {carefree:stats type='todayview' /} 次</li>
    </ul>
</div>
```

#### 示例 5: 美化的统计卡片

```html
<div class="stats-cards">
    <div class="card card-primary">
        <div class="card-icon">📝</div>
        <div class="card-number">{carefree:stats type='article' /}</div>
        <div class="card-label">文章</div>
    </div>

    <div class="card card-success">
        <div class="card-icon">👁️</div>
        <div class="card-number">{carefree:stats type='view' /}</div>
        <div class="card-label">浏览</div>
    </div>

    <div class="card card-info">
        <div class="card-icon">🏷️</div>
        <div class="card-number">{carefree:stats type='tag' /}</div>
        <div class="card-label">标签</div>
    </div>

    <div class="card card-warning">
        <div class="card-icon">📁</div>
        <div class="card-number">{carefree:stats type='category' /}</div>
        <div class="card-label">分类</div>
    </div>
</div>
```

---

## 五、完整应用示例

### 5.1 博客首页综合示例

```html
<!DOCTYPE html>
<html>
<head>
    <title>{carefree:config name='site_name' /}</title>
</head>
<body>
    <!-- 顶部广告 -->
    {carefree:ad position='1' limit='1' id='banner'}
        <div class="top-ad">
            <a href="{$banner.link_url}">
                <img src="{$banner.images}" alt="{$banner.name}">
            </a>
        </div>
    {/carefree:ad}

    <!-- 网站统计 -->
    <div class="site-stats">
        <span>{carefree:stats type='article' /} 篇文章</span>
        <span>{carefree:stats type='view' /} 次浏览</span>
        <span>今日更新 {carefree:stats type='todayarticle' /} 篇</span>
    </div>

    <!-- 主要内容 -->
    <div class="main-content">
        <!-- 最近更新 -->
        <section class="recent-updates">
            <h2>最近更新</h2>
            {carefree:article flag='updated' limit='5' id='article'}
                <article>
                    <h3>{$article.title}</h3>
                    <time>{$article.update_time}</time>
                    <p>{$article.summary}</p>
                </article>
            {/carefree:article}
        </section>
    </div>

    <!-- 侧边栏 -->
    <aside class="sidebar">
        <!-- 侧边广告 -->
        {carefree:ad position='2' limit='1' id='side_ad'}
            <div class="sidebar-ad">
                <a href="{$side_ad.link_url}">
                    <img src="{$side_ad.images}">
                </a>
            </div>
        {/carefree:ad}

        <!-- 随机推荐 -->
        <div class="random-posts">
            <h3>随机推荐</h3>
            {carefree:article flag='random' limit='5' id='random'}
                <div class="random-item">
                    <a href="/article/{$random.id}.html">
                        {$random.title}
                    </a>
                </div>
            {/carefree:article}
        </div>
    </aside>
</body>
</html>
```

---

## 六、升级指南

### 6.1 从 V1.2 升级到 V1.3

1. **文件更新**
   - 替换 `backend/app/taglib/Carefree.php`
   - 新增 `backend/app/service/tag/AdTagService.php`
   - 新增 `backend/app/service/tag/StatsTagService.php`
   - 更新 `backend/app/service/tag/ArticleTagService.php`

2. **数据库检查**
   - 确认 `ads` 表存在且包含必要字段
   - 确认 `articles` 表有 `update_time` 字段

3. **缓存清理**
   ```bash
   # 清理应用缓存
   php think clear
   ```

4. **测试新功能**
   - 测试广告标签显示
   - 测试随机文章功能
   - 测试最近更新功能
   - 测试统计数据显示

### 6.2 兼容性说明

- V1.3 完全兼容 V1.0、V1.1、V1.2 的所有功能
- 新增功能不影响现有模板
- 可以逐步迁移使用新功能

---

## 七、性能优化

### 7.1 缓存策略

- **广告缓存**: 30分钟（1800秒）
- **统计缓存**: 1小时（3600秒）
- **随机文章**: 不缓存（保证每次随机）
- **最近更新**: 不缓存（保证实时性）

### 7.2 优化建议

1. **广告位**
   - 合理规划广告位数量
   - 避免同一页面加载过多广告
   - 使用 limit 参数控制数量

2. **统计数据**
   - 统计数据已自动缓存
   - 高流量站点可延长缓存时间
   - 分类统计比全局统计更快

3. **随机文章**
   - 限制随机查询的数量（建议 ≤ 10）
   - 配合分类使用减少查询范围
   - 考虑在低流量时段刷新

4. **最近更新**
   - 使用索引优化 update_time 字段
   - 合理设置 limit 参数
   - 避免在首页加载过多

---

## 八、常见问题

### Q1: 广告不显示？
**A**: 检查以下几点：
1. 广告状态是否为启用（status=1）
2. 当前时间是否在广告有效期内
3. position 参数是否匹配
4. 清理缓存后重试

### Q2: 随机文章每次都一样？
**A**:
- 检查是否开启了页面缓存
- 确认数据库支持 RAND() 函数
- 尝试清理应用缓存

### Q3: 统计数据不准确？
**A**:
- 统计有1小时缓存延迟
- 可以手动清理缓存：`Cache::delete('stats_' . $type)`
- 确认数据库数据正确

### Q4: 最近更新显示的不是最新的？
**A**:
- 检查文章的 update_time 字段
- 确认文章状态为已发布
- 清理缓存后重试

---

## 九、下一步规划

V1.4 版本计划新增功能：
- 评论标签
- 点赞/收藏标签
- 热搜词标签
- 相关文章推荐算法
- 用户中心标签

---

## 十、技术支持

- **文档**: 查看完整标签库文档
- **示例**: 参考 `templates/examples/v1.3_demo.html`
- **源码**: 查看 `backend/app/taglib/Carefree.php`

---

## 版本历史

- **V1.3** (2025-10) - 广告位、随机文章、最近更新、统计数据
- **V1.2** (2025-09) - 空数据处理、幻灯片、分页导航
- **V1.1** (2025-08) - 友链、面包屑、单项信息标签
- **V1.0** (2025-07) - 基础标签功能

---

© 2025 Carefree 标签库 - 让模板开发更加自由
