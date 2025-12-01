# Carefree 模板标签完整使用指南 v2.0.0

## 目录

- [概述](#概述)
- [安装配置](#安装配置)
- [标签语法](#标签语法)
- [基础内容标签](#基础内容标签)
- [媒体相关标签](#媒体相关标签)
- [互动功能标签](#互动功能标签)
- [实用工具标签](#实用工具标签)
- [AI推荐标签](#ai推荐标签)
- [表单相关标签](#表单相关标签)
- [用户系统标签](#用户系统标签)
- [SEO与分享标签](#seo与分享标签)
- [高级功能标签](#高级功能标签)
- [完整示例](#完整示例)

---

## 概述

Carefree 标签库是为本CMS系统开发的自定义模板标签系统，扩展了 ThinkPHP 8.0 的模板引擎功能。

### 版本信息

- **当前版本**: v2.0.0
- **更新日期**: 2025年
- **标签总数**: 53个
- **ThinkPHP版本**: 8.0

### 新增功能 (v2.0.0)

✨ **媒体管理** (4个标签)
- 相册图库、视频管理、音频播放、文件下载

✨ **互动功能** (3个标签)
- 投票系统、在线测验、抽奖活动

✨ **实用工具** (4个标签)
- 二维码生成、事件日历、站点地图、天气预报

✨ **智能推荐** (2个标签)
- AI内容推荐、用户个性化

✨ **表单与验证** (3个标签)
- 动态表单、表单字段、验证码

---

## 安装配置

### 文件结构

```
backend/
├── app/
│   ├── taglib/
│   │   └── Carefree.php              # 标签库主类 (53个标签定义)
│   └── service/
│       └── tag/
│           ├── ArticleTagService.php       # 文章服务
│           ├── CategoryTagService.php      # 分类服务
│           ├── GalleryTagService.php       # 相册服务 ⭐新增
│           ├── VideoTagService.php         # 视频服务 ⭐新增
│           ├── VoteTagService.php          # 投票服务 ⭐新增
│           ├── RecommendTagService.php     # 推荐服务 ⭐新增
│           └── ... (共44个服务类)
└── config/
    └── view.php                      # 视图配置
```

### 配置说明

在 `config/view.php` 中已自动配置：

```php
'taglib_pre_load' => 'app\\taglib\\Carefree',
```

---

## 标签语法

### 基本语法

```html
<!-- 单标签 -->
{carefree:tagname attr='value' /}

<!-- 闭合标签 -->
{carefree:tagname attr='value'}
    内容...
{/carefree:tagname}
```

### 通用属性

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `id` | 循环变量名 | 因标签而异 |
| `key` | 索引变量名 (从0开始) | `key` |
| `i` | 序号变量名 (从1开始) | `i` |
| `mod` | 奇偶数 (0或1) | `mod` |
| `empty` | 空数据提示文本 | 空字符串 |

---

## 基础内容标签

### 1. article - 文章列表

显示文章列表，支持多种筛选和排序。

**属性说明**:

| 属性 | 说明 | 默认值 | 示例 |
|------|------|--------|------|
| `typeid` | 分类ID | 0 | `typeid='1'` |
| `tagid` | 标签ID | 0 | `tagid='5'` |
| `limit` | 显示数量 | 10 | `limit='20'` |
| `order` | 排序方式 | `create_time desc` | `order='view_count desc'` |
| `flag` | 标识: hot/recommend/top/random/updated | 无 | `flag='hot'` |
| `titlelen` | 标题截取长度 | 0 | `titlelen='30'` |
| `hascover` | 是否有封面 (1/0) | -1 | `hascover='1'` |
| `exclude` | 排除文章ID (逗号分隔) | 无 | `exclude='1,2,3'` |
| `days` | 最近N天 | 0 | `days='7'` |

**使用示例**:

```html
<!-- 示例1: 首页推荐文章 -->
{carefree:article flag='recommend' limit='6' empty='暂无推荐文章'}
<article class="card">
    <a href="/article/{$article.id}.html">
        <img src="{$article.cover_image}" alt="{$article.title}">
        <h3>{$article.title}</h3>
        <p>{$article.description}</p>
        <span>{$article.view_count} 阅读</span>
    </a>
</article>
{/carefree:article}

<!-- 示例2: 某分类热门文章 -->
{carefree:article typeid='1' flag='hot' limit='10' titlelen='30'}
<li class="{if condition='$i eq 1'}top{/if}">
    <span class="num">{$i}</span>
    <a href="/article/{$article.id}.html">{$article.title}</a>
</li>
{/carefree:article}

<!-- 示例3: 最近7天的文章 -->
{carefree:article days='7' limit='20' hascover='1'}
<div class="item">
    <img src="{$article.cover_image}">
    <h4>{$article.title}</h4>
    <time>{$article.create_time|date='Y-m-d'}</time>
</div>
{/carefree:article}
```

### 2. category - 分类列表

显示文章分类列表。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `parent` | 父分类ID (0=顶级) | 0 |
| `limit` | 显示数量 | 0 (不限) |

**使用示例**:

```html
<!-- 顶级分类导航 -->
{carefree:category parent='0' limit='8'}
<li><a href="/category/{$category.id}.html">{$category.name}</a></li>
{/carefree:category}

<!-- 带子分类的两级菜单 -->
{carefree:category parent='0' id='cat1'}
<li class="parent-cat">
    <a href="/category/{$cat1.id}.html">{$cat1.name}</a>
    <ul class="sub-menu">
    {carefree:category parent='{$cat1.id}' id='cat2'}
        <li><a href="/category/{$cat2.id}.html">{$cat2.name}</a></li>
    {/carefree:category}
    </ul>
</li>
{/carefree:category}
```

### 3. tag - 标签列表

显示文章标签列表或标签云。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `limit` | 显示数量 | 0 (不限) |
| `order` | 排序: sort/article_count/create_time | `sort asc` |

**使用示例**:

```html
<!-- 热门标签云 -->
<div class="tag-cloud">
{carefree:tag limit='50' order='article_count desc'}
    <a href="/tag/{$tag.id}.html" class="tag-{$i}">{$tag.name}</a>
{/carefree:tag}
</div>
```

### 4. config - 网站配置

输出网站配置信息（单标签）。

**常用配置项**:

```html
<!-- 网站基本信息 -->
{carefree:config name='site_name' /}          <!-- 网站名称 -->
{carefree:config name='site_logo' /}          <!-- 网站Logo -->
{carefree:config name='site_copyright' /}     <!-- 版权信息 -->
{carefree:config name='site_icp' /}           <!-- ICP备案号 -->

<!-- SEO信息 -->
{carefree:config name='seo_title' /}
{carefree:config name='seo_keywords' /}
{carefree:config name='seo_description' /}
```

### 5. nav - 导航菜单

输出网站导航菜单。

**使用示例**:

```html
<nav class="main-nav">
    <ul>
    {carefree:nav limit='10'}
        <li><a href="{$nav.url}">{$nav.title}</a></li>
    {/carefree:nav}
    </ul>
</nav>
```

### 6. arcinfo - 文章详情

获取单篇文章的详细信息。

**使用示例**:

```html
{carefree:arcinfo aid='{$article_id}'}
<article>
    <h1>{$article.title}</h1>
    <div class="meta">
        <span>{$article.category.name}</span>
        <time>{$article.create_time}</time>
        <span>{$article.view_count} 阅读</span>
    </div>
    <div class="content">{$article.content|raw}</div>
</article>
{/carefree:arcinfo}
```

### 7. catinfo - 分类详情

获取单个分类的详细信息。

**使用示例**:

```html
{carefree:catinfo catid='{$catid}'}
<div class="category-header">
    <h1>{$category.name}</h1>
    <p>{$category.description}</p>
</div>
{/carefree:catinfo}
```

### 8. related - 相关文章

显示相关文章列表（基于分类或标签）。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `aid` | 文章ID | 必填 |
| `limit` | 数量 | 5 |
| `type` | 类型: same(同分类)/all(所有) | `same` |

**使用示例**:

```html
<!-- 相关推荐 -->
<section class="related-articles">
    <h3>相关阅读</h3>
    {carefree:related aid='{$article.id}' limit='4' type='same'}
    <div class="related-item">
        <a href="/article/{$article.id}.html">
            <img src="{$article.cover_image}">
            <h4>{$article.title}</h4>
        </a>
    </div>
    {/carefree:related}
</section>
```

### 9. prevnext - 上一篇/下一篇

文章上下篇导航。

**使用示例**:

```html
{carefree:prevnext aid='{$article.id}' catid='{$article.category_id}'}
<div class="article-nav">
    {if condition="$prev"}
    <a href="/article/{$prev.id}.html" class="prev">← {$prev.title}</a>
    {/if}

    {if condition="$next"}
    <a href="/article/{$next.id}.html" class="next">{$next.title} →</a>
    {/if}
</div>
{/carefree:prevnext}
```

### 10. breadcrumb - 面包屑导航

显示当前页面的面包屑导航。

**使用示例**:

```html
{carefree:breadcrumb separator=' / '}
<nav class="breadcrumb">
    {volist name="breadcrumb" id="item"}
    <a href="{$item.url}">{$item.title}</a>
    {/volist}
</nav>
{/carefree:breadcrumb}
```

---

## 媒体相关标签

### 11. gallery - 相册图库 ⭐新增

显示相册图片列表，支持瀑布流、网格等布局。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `albumid` | 相册ID | 0 (所有) |
| `limit` | 显示数量 | 12 |
| `orderby` | 排序 | `sort asc` |
| `columns` | 每行列数 | 4 |

**使用示例**:

```html
<!-- 示例1: 相册网格布局 -->
<div class="gallery-grid">
{carefree:gallery albumid='1' limit='20' columns='4'}
    <div class="photo-item col-{$photo.col}">
        <a href="{$photo.image}" data-lightbox="gallery">
            <img src="{$photo.thumb}" alt="{$photo.title}">
        </a>
        <p class="caption">{$photo.title}</p>
    </div>
{/carefree:gallery}
</div>

<!-- 示例2: 瀑布流布局 -->
<div class="masonry">
{carefree:gallery limit='50' columns='3' empty='暂无图片'}
    <div class="masonry-item">
        <img src="{$photo.image}" alt="{$photo.title}">
        <div class="photo-info">
            <h4>{$photo.title}</h4>
            <p>{$photo.description}</p>
        </div>
    </div>
{/carefree:gallery}
</div>
```

### 12. video - 视频列表 ⭐新增

显示视频内容列表。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `catid` | 分类ID | 0 |
| `limit` | 显示数量 | 10 |
| `orderby` | 排序 | `create_time desc` |
| `featured` | 是否精选 (1/0) | 0 |

**使用示例**:

```html
<!-- 示例1: 视频列表 -->
<div class="video-list">
{carefree:video catid='1' limit='12' featured='1'}
    <div class="video-card">
        <div class="video-thumb">
            <img src="{$video.cover}" alt="{$video.title}">
            <span class="duration">{$video.duration_formatted}</span>
            <a href="/video/{$video.id}.html" class="play-btn">▶</a>
        </div>
        <div class="video-info">
            <h3>{$video.title}</h3>
            <p class="views">{$video.view_count_formatted} 播放</p>
            <time>{$video.create_time_formatted}</time>
        </div>
    </div>
{/carefree:video}
</div>

<!-- 示例2: 热门视频排行 -->
<aside class="hot-videos">
    <h4>热门视频</h4>
    {carefree:video limit='10' orderby='view_count desc'}
    <div class="video-item">
        <span class="rank">{$i}</span>
        <a href="/video/{$video.id}.html">{$video.title}</a>
        <span class="count">{$video.view_count_formatted}</span>
    </div>
    {/carefree:video}
</aside>
```

### 13. audio - 音频列表 ⭐新增

显示音频内容列表。

**使用示例**:

```html
<!-- 音乐播放器列表 -->
<div class="audio-player">
    <div class="playlist">
    {carefree:audio catid='2' limit='20' orderby='create_time desc'}
        <div class="track {if condition='$i eq 1'}active{/if}">
            <span class="track-num">{$i}</span>
            <div class="track-info">
                <h4>{$audio.title}</h4>
                <p>{$audio.author_name}</p>
            </div>
            <span class="duration">{$audio.duration_formatted}</span>
            <button class="play-btn" data-src="{$audio.audio_url}">▶</button>
        </div>
    {/carefree:audio}
    </div>
</div>
```

### 14. download - 文件下载 ⭐新增

显示可下载文件列表。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `catid` | 分类ID | 0 |
| `limit` | 显示数量 | 10 |
| `type` | 文件类型: doc/pdf/zip/image/video/software等 | 无 |

**使用示例**:

```html
<!-- 示例1: 软件下载列表 -->
<div class="download-list">
{carefree:download type='software' limit='20'}
    <div class="download-item">
        <img src="{$download.icon}" alt="{$download.type_label}" class="file-icon">
        <div class="file-info">
            <h4>{$download.title}</h4>
            <p class="meta">
                <span class="type">{$download.type_label}</span>
                <span class="size">{$download.file_size_formatted}</span>
                <span class="downloads">{$download.download_count_formatted} 下载</span>
            </p>
        </div>
        <a href="{$download.file_url}" class="btn-download" download>下载</a>
    </div>
{/carefree:download}
</div>

<!-- 示例2: 文档资料下载 -->
{carefree:download catid='5' type='pdf' limit='10'}
<tr>
    <td>{$i}</td>
    <td>{$download.title}</td>
    <td>{$download.file_size_formatted}</td>
    <td>{$download.create_time_formatted}</td>
    <td><a href="{$download.file_url}" download>下载</a></td>
</tr>
{/carefree:download}
```

### 15. slider - 幻灯片

显示轮播图/幻灯片。

**使用示例**:

```html
<div class="swiper">
    <div class="swiper-wrapper">
    {carefree:slider group='home' limit='5'}
        <div class="swiper-slide">
            <a href="{$slider.link}">
                <img src="{$slider.image}" alt="{$slider.title}">
                <h3>{$slider.title}</h3>
            </a>
        </div>
    {/carefree:slider}
    </div>
</div>
```

---

## 互动功能标签

### 16. vote - 投票系统 ⭐新增

显示投票活动及其选项。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `voteid` | 投票ID | 必填 |
| `showresult` | 是否显示结果 (1/0) | 0 |

**使用示例**:

```html
<!-- 示例1: 投票表单 -->
{carefree:vote voteid='1' showresult='0'}
<div class="vote-box">
    <h3>{$vote.title}</h3>
    <p>{$vote.description}</p>
    <form action="/api/vote/submit" method="post">
        <input type="hidden" name="vote_id" value="{$vote.id}">
        {volist name="vote.options" id="option"}
        <label class="vote-option">
            <input type="{if condition='$vote.is_multiple'}checkbox{else}radio{/if}"
                   name="option_ids[]" value="{$option.id}">
            <span>{$option.title}</span>
        </label>
        {/volist}
        <button type="submit" class="btn-vote">投票</button>
    </form>
    <p class="vote-info">
        <span>状态: {$vote.vote_status_text}</span>
        <span>已投票: {$vote.total_votes_formatted}</span>
        <span>截止: {$vote.end_time_formatted}</span>
    </p>
</div>
{/carefree:vote}

<!-- 示例2: 投票结果显示 -->
{carefree:vote voteid='1' showresult='1'}
<div class="vote-result">
    <h3>{$vote.title}</h3>
    {volist name="vote.options" id="option"}
    <div class="result-item">
        <span class="option-title">{$option.title}</span>
        <div class="progress-bar">
            <div class="progress" style="width: {$option.percent}%"></div>
        </div>
        <span class="percent">{$option.percent}%</span>
        <span class="count">({$option.vote_count_formatted}票)</span>
    </div>
    {/volist}
    <p class="total">总票数: {$vote.total_votes_formatted}</p>
</div>
{/carefree:vote}
```

### 17. quiz - 在线测验 ⭐新增

显示测验/问答系统。

**使用示例**:

```html
<!-- 在线考试/测验 -->
{carefree:quiz quizid='1'}
<div class="quiz-container">
    <div class="quiz-header">
        <h2>{$quiz.title}</h2>
        <p>{$quiz.description}</p>
        <div class="quiz-meta">
            <span>题目数: {$quiz.question_count}</span>
            <span>限时: {$quiz.time_limit_formatted}</span>
            <span>通过分: {$quiz.pass_score}分</span>
        </div>
    </div>

    <form class="quiz-form" action="/api/quiz/submit" method="post">
        <input type="hidden" name="quiz_id" value="{$quiz.id}">

        {volist name="quiz.questions" id="question"}
        <div class="question-item">
            <h4>第{$i}题 ({$question.type_text}) - {$question.score}分</h4>
            <p class="question-title">{$question.title}</p>

            <div class="options">
            {volist name="question.options" id="opt"}
                <label>
                    <input type="{if condition='$question.type eq \"multiple\"'}checkbox{else}radio{/if}"
                           name="answer[{$question.id}][]" value="{$opt.id}">
                    <span>{$opt.content}</span>
                </label>
            {/volist}
            </div>
        </div>
        {/volist}

        <button type="submit" class="btn-submit">提交答卷</button>
    </form>

    <div class="quiz-stats">
        <p>已有 {$quiz.total_participants} 人参加</p>
        <p>通过率: {$quiz.pass_rate}%</p>
    </div>
</div>
{/carefree:quiz}
```

### 18. lottery - 抽奖活动 ⭐新增

显示抽奖活动及奖品列表。

**使用示例**:

```html
<!-- 大转盘抽奖 -->
{carefree:lottery lotteryid='1'}
<div class="lottery-box">
    <h2>{$lottery.title}</h2>
    <p>{$lottery.description}</p>

    <div class="lottery-wheel">
        {volist name="lottery.prizes" id="prize"}
        <div class="prize-sector" data-prize-id="{$prize.id}">
            <img src="{$prize.image}" alt="{$prize.name}">
            <span>{$prize.name}</span>
        </div>
        {/volist}
        <button class="btn-draw">立即抽奖</button>
    </div>

    <div class="lottery-info">
        <p>活动时间: {$lottery.start_time_formatted} ~ {$lottery.end_time_formatted}</p>
        <p>状态: {$lottery.activity_status_text}</p>
        <p>已参与: {$lottery.total_participants} 人</p>
        {if condition="$lottery.daily_limit"}
        <p>每日限抽: {$lottery.daily_limit} 次</p>
        {/if}
    </div>

    <div class="prize-list">
        <h3>奖品列表</h3>
        {volist name="lottery.prizes" id="prize"}
        <div class="prize-item {if condition='$prize.is_out_of_stock'}out-of-stock{/if}">
            <img src="{$prize.image}">
            <h4>{$prize.name}</h4>
            <p>中奖概率: {$prize.probability_formatted}</p>
            <p>剩余: {$prize.remaining} / {$prize.total_count}</p>
        </div>
        {/volist}
    </div>
</div>
{/carefree:lottery}
```

### 19. comment - 评论列表

显示文章评论。

**使用示例**:

```html
<div class="comments">
    <h3>评论 ({$comment_count})</h3>
    {carefree:comment aid='{$article.id}' limit='20'}
    <div class="comment-item">
        <div class="avatar">
            <img src="{$comment.user_avatar}">
        </div>
        <div class="comment-content">
            <h5>{$comment.user_name}</h5>
            <p>{$comment.content}</p>
            <time>{$comment.create_time}</time>
        </div>
    </div>
    {/carefree:comment}
</div>
```

---

## 实用工具标签

### 20. qrcode - 二维码生成 ⭐新增

生成二维码图片。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `content` | 二维码内容 | 必填 |
| `size` | 尺寸(px) | 200 |
| `logo` | Logo图片路径 | 无 |
| `level` | 容错级别: L/M/Q/H | M |

**使用示例**:

```html
<!-- 示例1: 文章分享二维码 -->
<div class="qrcode-share">
    <p>扫码阅读</p>
    <img src="{carefree:qrcode content='https://example.com/article/123.html' size='150' /}" alt="二维码">
</div>

<!-- 示例2: 联系方式二维码 -->
<div class="contact-qrcode">
    <img src="{carefree:qrcode content='tel:13800138000' size='200' /}" alt="电话">
    <p>扫码拨打电话</p>
</div>

<!-- 示例3: 微信二维码 -->
<img src="{carefree:qrcode content='weixin://wxid_example' size='250' logo='/logo.png' /}" alt="微信">
```

### 21. calendar - 事件日历 ⭐新增

显示日历及事件。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `year` | 年份 | 当前年 |
| `month` | 月份 | 当前月 |
| `events` | 是否包含事件 (1/0) | 1 |

**使用示例**:

```html
<!-- 月历显示 -->
{carefree:calendar year='2025' month='1' events='1'}
<div class="calendar">
    <div class="calendar-header">
        <h3>{$calendar.year}年 {$calendar.month_name}</h3>
        <div class="nav">
            <a href="?date={$calendar.prev_month}">«</a>
            <a href="?date={$calendar.next_month}">»</a>
        </div>
    </div>

    <table class="calendar-table">
        <thead>
            <tr>
                <th>日</th><th>一</th><th>二</th><th>三</th>
                <th>四</th><th>五</th><th>六</th>
            </tr>
        </thead>
        <tbody>
        {volist name="calendar.weeks" id="week"}
            <tr>
            {volist name="week" id="day"}
                <td class="{if condition='!$day.is_current_month'}other-month{/if}
                           {if condition='$day.is_today'}today{/if}
                           {if condition='$day.event_count gt 0'}has-event{/if}">
                    <span class="day-num">{$day.day}</span>
                    {if condition='$day.event_count gt 0'}
                    <span class="event-badge">{$day.event_count}</span>
                    {/if}
                </td>
            {/volist}
            </tr>
        {/volist}
        </tbody>
    </table>
</div>
{/carefree:calendar}
```

### 22. sitemap - 站点地图 ⭐新增

生成站点地图。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `type` | 类型: article/category/page/all | `all` |
| `format` | 格式: html/xml/json | `html` |

**使用示例**:

```html
<!-- HTML站点地图 -->
<div class="sitemap">
    <h1>网站地图</h1>
    {carefree:sitemap type='all' format='html'}
    <div class="sitemap-item">
        <a href="{$item.loc}">{$item.title}</a>
        <span class="type">{$item.type}</span>
        <time>{$item.lastmod}</time>
    </div>
    {/carefree:sitemap}
</div>
```

### 23. weather - 天气预报 ⭐新增

显示天气信息。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `city` | 城市名称 | 北京 |
| `days` | 预报天数 (1-7) | 3 |
| `unit` | 温度单位: c/f | c |

**使用示例**:

```html
<!-- 天气卡片 -->
<div class="weather-widget">
    {assign name="weather" value="{carefree:weather city='北京' days='3' /}"}

    <h3>{$weather.city} 天气</h3>
    <p class="update-time">更新时间: {$weather.update_time}</p>

    <div class="forecast">
    {volist name="weather.forecasts" id="day"}
        <div class="day-forecast">
            <h4>{$day.week}</h4>
            <p class="date">{$day.date}</p>
            <div class="weather-icon">{$day.weather_day}</div>
            <div class="temp">
                <span class="high">{$day.temp_day}{$day.temp_unit}</span>
                <span class="low">{$day.temp_night}{$day.temp_unit}</span>
            </div>
            <p class="wind">{$day.wind_direction} {$day.wind_power}</p>
        </div>
    {/volist}
    </div>
</div>
```

### 24. search - 搜索框

生成搜索表单。

**使用示例**:

```html
{carefree:search action='/search' placeholder='搜索文章...' button='搜索' class='search-form' /}
```

### 25. link - 友情链接

显示友情链接。

**使用示例**:

```html
<div class="友links">
    <h4>友情链接</h4>
    {carefree:link group='home' limit='20'}
    <a href="{$link.url}" target="_blank" title="{$link.description}">
        {$link.title}
    </a>
    {/carefree:link}
</div>
```

---

## AI推荐标签

### 26. recommend - 智能推荐 ⭐新增

基于AI算法的内容推荐。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `type` | 类型: similar/hot/related/user/collaborative | `hot` |
| `userid` | 用户ID | 0 |
| `aid` | 文章ID (用于相似推荐) | 0 |
| `limit` | 数量 | 10 |

**使用示例**:

```html
<!-- 示例1: 相似内容推荐 -->
<section class="recommend-similar">
    <h3>相似推荐</h3>
    {carefree:recommend type='similar' aid='{$article.id}' limit='6'}
    <div class="recommend-item">
        <img src="{$article.cover_image}">
        <h4>{$article.title}</h4>
        <p class="similarity">相似度: {$article.similarity_score}%</p>
    </div>
    {/carefree:recommend}
</section>

<!-- 示例2: 热门推荐 -->
<aside class="hot-recommend">
    <h4>🔥 热门推荐</h4>
    {carefree:recommend type='hot' limit='10'}
    <div class="hot-item">
        <span class="rank">{$i}</span>
        <a href="/article/{$article.id}.html">{$article.title}</a>
        <span class="score">{$article.hot_score}</span>
    </div>
    {/carefree:recommend}
</aside>

<!-- 示例3: 基于用户的推荐 -->
{if condition="$user_id"}
<section class="personalized-recommend">
    <h3>为你推荐</h3>
    {carefree:recommend type='user' userid='{$user_id}' limit='12'}
    <article class="recommend-card">
        <img src="{$article.cover_image}">
        <h4>{$article.title}</h4>
        <p>{$article.description}</p>
    </article>
    {/carefree:recommend}
</section>
{/if}

<!-- 示例4: 协同过滤推荐 -->
{carefree:recommend type='collaborative' userid='{$user_id}' limit='8'}
<div class="collaborative-item">
    <a href="/article/{$article.id}.html">
        <img src="{$article.cover_image}">
        <h5>{$article.title}</h5>
        <p class="reason">喜欢相似内容的用户也在看</p>
    </a>
</div>
{/carefree:recommend}
```

### 27. personalize - 个性化内容 ⭐新增

基于用户行为的个性化推荐。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `userid` | 用户ID | 必填 |
| `scene` | 场景: home/detail/search | `home` |
| `limit` | 数量 | 10 |

**使用示例**:

```html
<!-- 首页个性化推荐 -->
{if condition="$user_id"}
<section class="personalize-feed">
    <h2>专属推荐</h2>
    <p class="tip">根据你的阅读偏好精选</p>

    {carefree:personalize userid='{$user_id}' scene='home' limit='20'}
    <article class="feed-item">
        <img src="{$article.cover_image}">
        <div class="content">
            <h3>{$article.title}</h3>
            <p>{$article.description}</p>
            <div class="meta">
                <span class="category">{$article.category.name}</span>
                <span class="personalize-score">匹配度: {$article.personalize_score}分</span>
            </div>
        </div>
    </article>
    {/carefree:personalize}
</section>
{else}
<!-- 未登录用户显示默认内容 -->
<section class="default-feed">
    {carefree:article flag='recommend' limit='20'}
    ...
    {/carefree:article}
</section>
{/if}
```

---

## 表单相关标签

### 28. form - 通用表单 ⭐新增

生成表单容器。

**使用示例**:

```html
{carefree:form formid='contact' action='/api/contact/submit' method='post' class='contact-form'}
    <h3>联系我们</h3>

    {carefree:formfield name='name' type='text' label='姓名' required='1' placeholder='请输入姓名' /}

    {carefree:formfield name='email' type='email' label='邮箱' required='1' placeholder='请输入邮箱' /}

    {carefree:formfield name='subject' type='select' label='主题' required='1'
                       options='咨询,建议,投诉,其他' /}

    {carefree:formfield name='message' type='textarea' label='留言' required='1'
                       placeholder='请输入留言内容...' /}

    <div class="form-group">
        <label>验证码</label>
        {carefree:captcha type='image' width='120' height='40' length='4' /}
    </div>

    <button type="submit" class="btn-submit">提交</button>
{/carefree:form}
```

### 29. formfield - 表单字段 ⭐新增

生成表单输入字段。

**属性说明**:

| 属性 | 说明 | 类型选项 |
|------|------|----------|
| `name` | 字段名 | 必填 |
| `type` | 字段类型 | text/textarea/select/radio/checkbox/email/tel/date |
| `label` | 标签文本 | 可选 |
| `required` | 是否必填 | 0/1 |
| `placeholder` | 占位符 | 可选 |
| `options` | 选项(逗号分隔) | select/radio/checkbox使用 |
| `value` | 默认值 | 可选 |

**使用示例**:

```html
<!-- 文本输入 -->
{carefree:formfield name='username' type='text' label='用户名' required='1' /}

<!-- 下拉选择 -->
{carefree:formfield name='gender' type='select' label='性别'
                   options='男,女,保密' value='保密' /}

<!-- 单选框 -->
{carefree:formfield name='level' type='radio' label='会员等级'
                   options='普通会员,VIP会员,SVIP会员' /}

<!-- 多选框 -->
{carefree:formfield name='interests' type='checkbox' label='兴趣爱好'
                   options='阅读,旅游,摄影,音乐,运动' /}

<!-- 文本域 -->
{carefree:formfield name='bio' type='textarea' label='个人简介'
                   placeholder='请输入个人简介...' /}
```

### 30. captcha - 验证码 ⭐新增

生成验证码。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `type` | 类型: image/sms/email | `image` |
| `width` | 宽度 | 120 |
| `height` | 高度 | 40 |
| `length` | 长度 | 4 |

**使用示例**:

```html
<!-- 图片验证码 -->
<div class="captcha-group">
    {carefree:captcha type='image' width='120' height='40' length='4' /}
    <button type="button" class="refresh-captcha">刷新</button>
</div>

<!-- 短信验证码 -->
<div class="sms-captcha">
    <input type="text" name="sms_code" placeholder="输入验证码">
    <button type="button" class="btn-send-sms">发送验证码</button>
</div>
```

---

## 用户系统标签

### 31. userinfo - 用户信息

显示用户详细信息。

**使用示例**:

```html
{carefree:userinfo uid='{$user_id}'}
<div class="user-profile">
    <img src="{$user.avatar}" class="avatar">
    <h3>{$user.nickname}</h3>
    <p>{$user.bio}</p>
    <div class="stats">
        <span>文章: {$user.article_count}</span>
        <span>粉丝: {$user.fans_count}</span>
    </div>
</div>
{/carefree:userinfo}
```

### 32. frontuser - 前台用户列表

显示前台会员列表。

**属性说明**:

| 属性 | 说明 | 默认值 |
|------|------|--------|
| `limit` | 数量 | 10 |
| `level` | 等级ID | 0 |
| `isvip` | 是否VIP (1/0) | -1 |
| `status` | 状态 (1=正常, 0=禁用) | 1 |
| `orderby` | 排序 | `create_time desc` |

**使用示例**:

```html
<!-- 会员排行榜 -->
<div class="member-rank">
    <h3>活跃会员</h3>
    {carefree:frontuser limit='10' orderby='points desc' isvip='1'}
    <div class="member-item">
        <span class="rank">{$i}</span>
        <img src="{$user.avatar}" class="avatar">
        <div class="info">
            <h4>{$user.nickname} {if condition="$user.is_vip"}<span class="vip-badge">VIP</span>{/if}</h4>
            <p>积分: {$user.points}</p>
        </div>
    </div>
    {/carefree:frontuser}
</div>
```

### 33. memberlevel - 会员等级

显示会员等级列表。

**使用示例**:

```html
<div class="level-list">
    {carefree:memberlevel limit='10'}
    <div class="level-card">
        <h4>{$level.name}</h4>
        <p class="price">¥{$level.price}</p>
        <ul class="benefits">
            <li>有效期: {$level.days}天</li>
            <li>积分倍率: {$level.points_rate}倍</li>
            <li>专属标识</li>
        </ul>
        <button class="btn-upgrade">立即开通</button>
    </div>
    {/carefree:memberlevel}
</div>
```

### 34. notification - 消息通知

显示用户消息通知。

**使用示例**:

```html
{if condition="$user_id"}
<div class="notifications">
    {carefree:notification userid='{$user_id}' limit='10' isread='0'}
    <div class="notify-item {if condition='!$notify.is_read'}unread{/if}">
        <div class="notify-icon">{$notify.type}</div>
        <div class="notify-content">
            <p>{$notify.content}</p>
            <time>{$notify.create_time}</time>
        </div>
    </div>
    {/carefree:notification}
</div>
{/if}
```

### 35. author - 作者列表

显示文章作者列表。

**使用示例**:

```html
<div class="author-list">
    {carefree:author limit='12' orderby='article_count desc'}
    <div class="author-card">
        <img src="{$author.avatar}">
        <h4>{$author.username}</h4>
        <p>文章: {$author.article_count}篇</p>
        <a href="/author/{$author.id}.html">查看主页</a>
    </div>
    {/carefree:author}
</div>
```

---

## SEO与分享标签

### 36. seo - SEO标签

生成SEO meta标签。

**使用示例**:

```html
<head>
    {carefree:seo title='{$article.title}'
                  keywords='{$article.keywords}'
                  description='{$article.description}'
                  image='{$article.cover_image}'
                  type='article' /}
</head>
```

### 37. share - 社交分享

生成分享按钮。

**使用示例**:

```html
<div class="share-buttons">
    {carefree:share platforms='wechat,weibo,qq,douban' size='medium' style='flat' /}
</div>
```

---

## 高级功能标签

### 38. multilang - 多语言 ⭐新增

多语言/国际化支持。

**使用示例**:

```html
<!-- 翻译文本 -->
<h1>{carefree:multilang key='site.welcome' default='欢迎访问' /}</h1>

<!-- 语言切换器 -->
<div class="lang-switcher">
    {assign name="langs" value="{carefree:multilang key='supported_langs' /}"}
    {volist name="langs" id="lang"}
    <a href="?lang={$lang.code}" class="{if condition='$lang.is_current'}active{/if}">
        {$lang.name}
    </a>
    {/volist}
</div>
```

### 39. cache - 缓存标签 ⭐新增

缓存模板片段。

**使用示例**:

```html
<!-- 缓存热门文章列表1小时 -->
{carefree:cache key='hot_articles' time='3600'}
    {carefree:article flag='hot' limit='10'}
    <li><a href="/article/{$article.id}.html">{$article.title}</a></li>
    {/carefree:article}
{/carefree:cache}
```

### 40. condition - 条件标签 ⭐新增

条件判断标签。

**使用示例**:

```html
{carefree:condition if='$user_id gt 0'}
<div class="user-menu">
    <a href="/profile">个人中心</a>
    <a href="/logout">退出登录</a>
</div>
{/carefree:condition}
```

### 41. group - 分组标签 ⭐新增

数据分组显示。

**使用示例**:

```html
<!-- 按分类分组显示文章 -->
{carefree:group data='$articles' by='category_id'}
<div class="group-section">
    <h3>{$group_key}</h3>
    {volist name="group_items" id="item"}
    <div class="item">{$item.title}</div>
    {/volist}
</div>
{/carefree:group}
```

### 42. loop - 通用循环

循环遍历任意数据。

**使用示例**:

```html
{carefree:loop data='$custom_data' id='item'}
<div class="item">{$item.name}</div>
{/carefree:loop}
```

### 43. sql - SQL查询

执行自定义SQL查询。

**使用示例**:

```html
{carefree:sql sql="SELECT * FROM articles WHERE status=1 LIMIT 10" id='result'}
<tr>
    <td>{$result.title}</td>
    <td>{$result.author}</td>
</tr>
{/carefree:sql}
```

### 44. stats - 统计数据

显示统计信息。

**使用示例**:

```html
<div class="site-stats">
    <div class="stat-item">
        <span class="num">{carefree:stats type='article' /}</span>
        <span class="label">文章总数</span>
    </div>
    <div class="stat-item">
        <span class="num">{carefree:stats type='view' /}</span>
        <span class="label">总浏览量</span>
    </div>
</div>
```

### 45. rank - 排行榜

显示各类排行榜。

**属性说明**:

| 属性 | 说明 | 选项 |
|------|------|------|
| `type` | 排行类型 | view/comment/like/download |
| `limit` | 数量 | 默认10 |
| `catid` | 分类ID | 可选 |
| `days` | 天数范围 | 可选 |

**使用示例**:

```html
<!-- 本周热门排行 -->
<div class="week-rank">
    <h3>本周热门</h3>
    {carefree:rank type='view' days='7' limit='10'}
    <div class="rank-item">
        <span class="num">{$i}</span>
        <a href="/article/{$article.id}.html">{$article.title}</a>
        <span class="count">{$article.view_count}</span>
    </div>
    {/carefree:rank}
</div>
```

### 46. archive - 归档列表

显示文章归档。

**使用示例**:

```html
<!-- 按月归档 -->
<div class="archive">
    {carefree:archive type='month' limit='12' format='Y年m月'}
    <div class="archive-item">
        <a href="/archive/{$archive.date}.html">
            {$archive.date_formatted} ({$archive.count}篇)
        </a>
    </div>
    {/carefree:archive}
</div>
```

### 47. topic - 专题列表

显示专题内容。

**使用示例**:

```html
<div class="topics">
    {carefree:topic limit='8' status='1'}
    <div class="topic-card">
        <img src="{$topic.cover}">
        <h3>{$topic.title}</h3>
        <p>{$topic.description}</p>
        <span>{$topic.article_count} 篇文章</span>
    </div>
    {/carefree:topic}
</div>
```

### 48. page - 单页列表

显示自定义页面列表。

**使用示例**:

```html
<nav class="footer-nav">
    {carefree:page limit='10'}
    <a href="/page/{$page.id}.html">{$page.title}</a>
    {/carefree:page}
</nav>
```

### 49. contribution - 投稿列表

显示用户投稿。

**使用示例**:

```html
{if condition="$user_id"}
<div class="my-contributions">
    <h3>我的投稿</h3>
    {carefree:contribution userid='{$user_id}' limit='20'}
    <div class="contrib-item">
        <h4>{$contrib.title}</h4>
        <p>状态: {$contrib.status_text}</p>
        <time>{$contrib.create_time}</time>
    </div>
    {/carefree:contribution}
</div>
{/if}
```

### 50. ad - 广告位

显示广告内容。

**使用示例**:

```html
<!-- 首页横幅广告 -->
{carefree:ad position='banner' limit='1'}
<div class="ad-banner">
    <a href="{$ad.link}" target="_blank">
        <img src="{$ad.image}" alt="{$ad.title}">
    </a>
</div>
{/carefree:ad}

<!-- 侧边栏广告 -->
{carefree:ad position='sidebar' limit='3'}
<div class="ad-item">
    <a href="{$ad.link}">
        <img src="{$ad.image}">
    </a>
</div>
{/carefree:ad}
```

### 51. position - 内容区块

显示指定位置的内容区块。

**使用示例**:

```html
{carefree:position name='home_banner'}
<div class="banner-content">
    {$position.content|raw}
</div>
{/carefree:position}
```

### 52. hotwords - 热门关键词

显示热搜词。

**使用示例**:

```html
<div class="hot-search">
    <span>热搜:</span>
    {carefree:hotwords limit='10' days='7'}
    <a href="/search?q={$word.keyword}">{$word.keyword}</a>
    {/carefree:hotwords}
</div>
```

### 53. randomimg - 随机图片

显示随机图片。

**使用示例**:

```html
<div class="random-gallery">
    {carefree:randomimg limit='6' source='unsplash'}
    <img src="{$img.url}" alt="{$img.title}">
    {/carefree:randomimg}
</div>
```

---

## 完整示例

### 博客首页完整模板

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{carefree:config name='site_name' /}</title>
    {carefree:seo type='website' /}
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- 头部导航 -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="{carefree:config name='site_logo' /}" alt="{carefree:config name='site_name' /}">
            </div>
            <nav class="main-nav">
                {carefree:nav limit='8'}
                <a href="{$nav.url}">{$nav.title}</a>
                {/carefree:nav}
            </nav>
            <div class="search">
                {carefree:search action='/search' placeholder='搜索文章...' /}
            </div>
        </div>
    </header>

    <!-- 轮播图 -->
    <section class="hero">
        {carefree:slider group='home' limit='5'}
        <div class="slide">
            <img src="{$slider.image}" alt="{$slider.title}">
            <div class="caption">
                <h2>{$slider.title}</h2>
                <p>{$slider.description}</p>
            </div>
        </div>
        {/carefree:slider}
    </section>

    <div class="container">
        <div class="row">
            <!-- 主内容区 -->
            <main class="col-md-8">
                <!-- 推荐文章 -->
                <section class="featured">
                    <h2>🔥 推荐阅读</h2>
                    <div class="article-grid">
                    {carefree:article flag='recommend' limit='6' hascover='1'}
                        <article class="card">
                            <a href="/article/{$article.id}.html">
                                <img src="{$article.cover_image}">
                                <h3>{$article.title}</h3>
                                <p>{$article.description}</p>
                                <div class="meta">
                                    <span>{$article.category.name}</span>
                                    <span>{$article.view_count} 阅读</span>
                                </div>
                            </a>
                        </article>
                    {/carefree:article}
                    </div>
                </section>

                <!-- 个性化推荐 (登录用户) -->
                {if condition="$user_id"}
                <section class="personalize">
                    <h2>为你推荐</h2>
                    {carefree:personalize userid='{$user_id}' scene='home' limit='10'}
                    <article class="list-item">
                        <img src="{$article.cover_image}">
                        <div class="content">
                            <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
                            <p>{$article.description}</p>
                        </div>
                    </article>
                    {/carefree:personalize}
                </section>
                {/if}

                <!-- 最新文章 -->
                <section class="latest">
                    <h2>最新发布</h2>
                    {carefree:article limit='20' order='create_time desc'}
                    <article class="list-item">
                        <img src="{$article.cover_image}">
                        <div class="content">
                            <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
                            <div class="meta">
                                <span>{$article.category.name}</span>
                                <time>{$article.create_time|date='Y-m-d'}</time>
                                <span>{$article.view_count} 阅读</span>
                            </div>
                        </div>
                    </article>
                    {/carefree:article}
                </section>

                <!-- 视频专区 -->
                <section class="videos">
                    <h2>📺 视频推荐</h2>
                    <div class="video-grid">
                    {carefree:video featured='1' limit='8'}
                        <div class="video-card">
                            <div class="thumb">
                                <img src="{$video.cover}">
                                <span class="duration">{$video.duration_formatted}</span>
                                <a href="/video/{$video.id}.html" class="play-btn">▶</a>
                            </div>
                            <h4>{$video.title}</h4>
                            <p>{$video.view_count_formatted} 播放</p>
                        </div>
                    {/carefree:video}
                    </div>
                </section>
            </main>

            <!-- 侧边栏 -->
            <aside class="col-md-4">
                <!-- 天气 -->
                <div class="widget weather-widget">
                    {carefree:weather city='北京' days='3' /}
                </div>

                <!-- 热门排行 -->
                <div class="widget">
                    <h4>🔥 热门文章</h4>
                    {carefree:rank type='view' days='7' limit='10'}
                    <div class="rank-item">
                        <span class="num">{$i}</span>
                        <a href="/article/{$article.id}.html">{$article.title}</a>
                    </div>
                    {/carefree:rank}
                </div>

                <!-- 分类导航 -->
                <div class="widget">
                    <h4>文章分类</h4>
                    {carefree:category parent='0'}
                    <a href="/category/{$category.id}.html" class="cat-link">
                        {$category.name}
                    </a>
                    {/carefree:category}
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

                <!-- 投票 -->
                <div class="widget">
                    {carefree:vote voteid='1' showresult='0' /}
                </div>

                <!-- 广告位 -->
                {carefree:ad position='sidebar' limit='2'}
                <div class="widget ad-widget">
                    <a href="{$ad.link}">
                        <img src="{$ad.image}">
                    </a>
                </div>
                {/carefree:ad}
            </aside>
        </div>
    </div>

    <!-- 页脚 -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <h5>关于我们</h5>
                    {carefree:page id='1' alias='about'}
                    <p>{$page.summary}</p>
                    {/carefree:page}
                </div>
                <div class="col-md-3">
                    <h5>友情链接</h5>
                    {carefree:link group='footer' limit='10'}
                    <a href="{$link.url}">{$link.title}</a>
                    {/carefree:link}
                </div>
                <div class="col-md-3">
                    <h5>联系我们</h5>
                    <p>邮箱: contact@example.com</p>
                    <p>电话: 400-123-4567</p>
                </div>
                <div class="col-md-3">
                    <h5>关注我们</h5>
                    {carefree:qrcode content='https://example.com' size='120' /}
                </div>
            </div>
            <div class="copyright">
                <p>{carefree:config name='site_copyright' /}</p>
                <p><a href="https://beian.miit.gov.cn/">{carefree:config name='site_icp' /}</a></p>
            </div>
        </div>
    </footer>
</body>
</html>
```

---

## 性能优化建议

### 1. 使用缓存

```html
<!-- 缓存热门文章 -->
{carefree:cache key='hot_articles' time='3600'}
    {carefree:article flag='hot' limit='10'}
    ...
    {/carefree:article}
{/carefree:cache}
```

### 2. 限制查询数量

```html
<!-- ✅ 好的做法 -->
{carefree:article limit='10'}...{/carefree:article}

<!-- ❌ 避免 -->
{carefree:article}...{/carefree:article}
```

### 3. 避免过度嵌套

```html
<!-- ❌ 不推荐：3层嵌套 -->
{carefree:category}
    {carefree:article typeid='{$category.id}'}
        {carefree:tag}...{/carefree:tag}
    {/carefree:article}
{/carefree:category}

<!-- ✅ 推荐：使用Ajax按需加载 -->
{carefree:category}
    <div class="category" data-id="{$category.id}">
        {$category.name}
        <div class="articles" data-load="ajax"></div>
    </div>
{/carefree:category}
```

---

## 常见问题

### Q: 如何调试标签输出？

```html
<!-- 查看完整数据结构 -->
{carefree:article limit='1'}
    <pre>{$article|json_encode:JSON_PRETTY_PRINT}</pre>
{/carefree:article}
```

### Q: 标签不生效怎么办？

1. 检查 `config/view.php` 配置
2. 清除缓存: `php think clear`
3. 检查标签语法

### Q: 如何自定义标签？

参考 `app/taglib/Carefree.php` 中的现有标签实现。

---

## 更新日志

### v2.0.0 (2025-01-11)

**新增标签** (16个):
- ✨ gallery - 相册图库
- ✨ video - 视频列表
- ✨ audio - 音频列表
- ✨ download - 文件下载
- ✨ vote - 投票系统
- ✨ quiz - 在线测验
- ✨ lottery - 抽奖活动
- ✨ qrcode - 二维码生成
- ✨ calendar - 事件日历
- ✨ sitemap - 站点地图
- ✨ weather - 天气预报
- ✨ recommend - 智能推荐
- ✨ personalize - 个性化内容
- ✨ form - 通用表单
- ✨ formfield - 表单字段
- ✨ captcha - 验证码
- ✨ multilang - 多语言
- ✨ cache - 缓存标签
- ✨ condition - 条件标签
- ✨ group - 分组标签

### v1.0.0 (2024-10-28)

**初始版本** (37个标签):
- ✅ 文章、分类、标签等基础内容标签
- ✅ 用户、评论、导航等功能标签
- ✅ SEO、分享等增强标签

---

## 技术支持

- 📖 文档: `/docs/carefree-taglib/`
- 🐛 问题反馈: 项目Issue
- 💬 QQ群: 113572201

---

**CarefreeCMS v2.0.0** - 让模板开发更简单
