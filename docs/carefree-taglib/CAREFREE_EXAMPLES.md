# Carefree 标签库实战示例集

本文档包含大量实际项目中的完整示例代码，可直接复制使用。

---

## 🆕 V1.6 新特性 - 变量参数使用示例

### 1. 分类页面 - 动态加载分类文章

```html
<!-- templates/category.html -->
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <title>{$category.name} - 文章列表</title>
</head>
<body>
    <!-- 分类信息 -->
    <div class="category-header">
        <h1>{$category.name}</h1>
        <p>{$category.description}</p>
    </div>

    <!-- 使用变量参数动态加载该分类的文章 -->
    <div class="article-list">
        {carefree:article typeid='$category.id' limit='10' order='create_time desc'}
            <article class="article-item">
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

### 2. 标签页面 - 动态加载标签文章

```html
<!-- templates/tag.html -->
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <title>标签：{$tag.name}</title>
</head>
<body>
    <div class="tag-header">
        <h1>#{$tag.name}</h1>
        <p>共 {$tag.article_count} 篇文章</p>
    </div>

    <!-- 使用变量参数加载该标签的文章 -->
    <div class="article-list">
        {carefree:article tagid='$tag.id' limit='20' order='create_time desc'}
            <div class="article-card">
                <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
                <p>{$article.summary}</p>
            </div>
        {/carefree:article}
    </div>
</body>
</html>
```

### 3. 文章详情页 - 相关文章和上下篇

```html
<!-- templates/article.html -->
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <title>{$article.title}</title>
</head>
<body>
    <!-- 文章内容 -->
    <article class="article-detail">
        <h1>{$article.title}</h1>
        <div class="article-content">
            {$article.content|raw}
        </div>
    </article>

    <!-- 上下篇导航 - 使用变量参数 -->
    <nav class="article-nav">
        {carefree:prevnext aid='$article.id' catid='$article.category_id' type='same'}
            <div class="nav-prev">
                {if $prev}
                    <a href="/article/{$prev.id}.html">
                        <span>← 上一篇</span>
                        <p>{$prev.title}</p>
                    </a>
                {/if}
            </div>
            <div class="nav-next">
                {if $next}
                    <a href="/article/{$next.id}.html">
                        <span>下一篇 →</span>
                        <p>{$next.title}</p>
                    </a>
                {/if}
            </div>
        {/carefree:prevnext}
    </nav>

    <!-- 相关文章推荐 - 使用变量参数 -->
    <section class="related-articles">
        <h3>相关推荐</h3>
        <div class="related-grid">
            {carefree:related aid='$article.id' limit='6' type='tag'}
                <div class="related-item">
                    <a href="/article/{$related.id}.html">
                        {if $related.cover_image}
                            <img src="{$related.cover_image}" alt="{$related.title}">
                        {/if}
                        <h4>{$related.title}</h4>
                        <span>{$related.view_count} 阅读</span>
                    </a>
                </div>
            {/carefree:related}
        </div>
    </section>
</body>
</html>
```

### 4. 会员投稿管理页面

```html
<!-- templates/contributions.html -->
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <title>我的投稿</title>
</head>
<body>
    <div class="user-center">
        <h1>我的投稿</h1>

        <!-- 状态筛选标签 -->
        <div class="status-tabs">
            <a href="?status=" class="{$status == '' ? 'active' : ''}">全部</a>
            <a href="?status=pending">待审核</a>
            <a href="?status=approved">已通过</a>
            <a href="?status=rejected">已拒绝</a>
        </div>

        <!-- 使用变量参数动态筛选投稿 -->
        <div class="contrib-list">
            {carefree:contribution
                userid='$current_user_id'
                status='$status'
                limit='10'
                orderby='create_time'
                empty='<div class="empty">暂无投稿记录</div>'}

                <div class="contrib-item status-{$contrib.status}">
                    <div class="contrib-header">
                        <h3>{$contrib.title}</h3>
                        <span class="badge badge-{$contrib.status}">
                            {$contrib.status_text}
                        </span>
                    </div>
                    <div class="contrib-meta">
                        <span>提交时间：{$contrib.create_time|date='Y-m-d H:i'}</span>
                    </div>
                    {if $contrib.status == 'rejected' && $contrib.reject_reason}
                        <div class="reject-reason">
                            <strong>拒绝原因：</strong>{$contrib.reject_reason}
                        </div>
                    {/if}
                    <div class="contrib-actions">
                        <a href="/contribution/view/{$contrib.id}">查看</a>
                        {if $contrib.status == 'pending' || $contrib.status == 'rejected'}
                            <a href="/contribution/edit/{$contrib.id}">编辑</a>
                        {/if}
                        <a href="/contribution/delete/{$contrib.id}" class="delete">删除</a>
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

### 5. 通知中心页面

```html
<!-- templates/notifications.html -->
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <title>我的通知</title>
</head>
<body>
    <div class="notification-center">
        <h1>通知中心</h1>

        <!-- 类型筛选 -->
        <div class="type-tabs">
            <a href="?type=">全部</a>
            <a href="?type=system">系统通知</a>
            <a href="?type=reply">评论回复</a>
            <a href="?type=like">点赞</a>
        </div>

        <!-- 使用变量参数加载通知 -->
        <div class="notice-list">
            {carefree:notification
                userid='$current_user_id'
                type='$type'
                limit='20'
                empty='<div class="empty">暂无通知消息</div>'}

                <div class="notice-item {$notice.is_read ? '' : 'unread'}">
                    <div class="notice-icon notice-{$notice.type}">
                        {if $notice.type == 'system'}
                            <i class="icon-bell"></i>
                        {elseif $notice.type == 'reply'}
                            <i class="icon-comment"></i>
                        {elseif $notice.type == 'like'}
                            <i class="icon-heart"></i>
                        {/if}
                    </div>
                    <div class="notice-content">
                        <p>{$notice.content|raw}</p>
                        <span class="notice-time">
                            {$notice.create_time|date='Y-m-d H:i'}
                        </span>
                    </div>
                    {if !$notice.is_read}
                        <span class="unread-badge"></span>
                    {/if}
                </div>
            {/carefree:notification}
        </div>

        <!-- 分页 -->
        {carefree:pagelist
            total='$total'
            pagesize='$pagesize'
            currentpage='$current_page'
            url='/notifications.html?type={$type}&page={page}'
            style='simple' /}
    </div>
</body>
</html>
```

---

## 📄 完整页面模板

### 1. 博客首页模板

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{carefree:config name='site_name' /} - {carefree:config name='site_slogan' /}</title>

    {carefree:seo
        title='$config.site_name'
        keywords='$config.site_keywords'
        description='$config.site_description'
        type='website' /}

    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <!-- 顶部导航 -->
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <h1>{carefree:config name='site_name' /}</h1>
            </div>

            <nav class="main-nav">
                {carefree:nav limit='7' id='nav'}
                    <a href="{$nav.url}" {if $nav.is_current}class="active"{/if}>
                        {$nav.name}
                    </a>
                {/carefree:nav}
            </nav>

            <div class="header-search">
                {carefree:search action='/search' placeholder='搜索文章...' /}
            </div>
        </div>
    </header>

    <div class="container main-container">
        <!-- 主内容区 -->
        <main class="main-content">
            <!-- 推荐文章（幻灯片） -->
            <section class="featured-posts">
                <h2>精选推荐</h2>
                {carefree:article flag='recommend' limit='5' hascover='1' id='featured'}
                    <div class="featured-item">
                        <img src="{$featured.cover_image}" alt="{$featured.title}">
                        <div class="featured-info">
                            <h3><a href="/article/{$featured.id}.html">{$featured.title}</a></h3>
                            <p>{$featured.summary}</p>
                            <div class="meta">
                                <span>{$featured.category.name}</span>
                                <span>{$featured.view_count} 阅读</span>
                            </div>
                        </div>
                    </div>
                {/carefree:article}
            </section>

            <!-- 最新文章 -->
            <section class="latest-posts">
                <h2>最新文章</h2>
                <div class="article-list">
                    {carefree:article limit='10' order='create_time desc' id='article'}
                        <article class="article-item">
                            {if $article.cover_image}
                                <div class="article-thumb">
                                    <a href="/article/{$article.id}.html">
                                        <img src="{$article.cover_image}" alt="{$article.title}">
                                    </a>
                                </div>
                            {/if}

                            <div class="article-content">
                                <h3>
                                    <a href="/article/{$article.id}.html">{$article.title}</a>
                                    {if $article.is_top}<span class="badge badge-top">置顶</span>{/if}
                                    {if $article.is_recommend}<span class="badge badge-hot">推荐</span>{/if}
                                </h3>
                                <p class="summary">{$article.summary}</p>
                                <div class="article-meta">
                                    {carefree:userinfo uid='{$article.user_id}'}
                                        <span class="author">
                                            <img src="{$userinfo.avatar}" class="avatar-mini">
                                            {$userinfo.display_name}
                                        </span>
                                    {/carefree:userinfo}
                                    <span class="category">{$article.category.name}</span>
                                    <span class="date">{$article.create_time|date='Y-m-d'}</span>
                                    <span class="views">{$article.view_count} 阅读</span>
                                </div>
                            </div>
                        </article>
                    {/carefree:article}
                </div>
            </section>
        </main>

        <!-- 侧边栏 -->
        <aside class="sidebar">
            <!-- 网站统计 -->
            <div class="widget widget-stats">
                <h3>网站统计</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value">{carefree:stats type='article' /}</div>
                        <div class="stat-label">文章</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{carefree:stats type='view' /}</div>
                        <div class="stat-label">浏览</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{carefree:stats type='comment' /}</div>
                        <div class="stat-label">评论</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">{carefree:stats type='tag' /}</div>
                        <div class="stat-label">标签</div>
                    </div>
                </div>
            </div>

            <!-- 热门文章 -->
            <div class="widget widget-hot">
                <h3>热门文章</h3>
                <ul class="hot-list">
                    {carefree:article flag='hot' limit='5' id='hot'}
                        <li>
                            <span class="rank rank-{$i}">{$i}</span>
                            <a href="/article/{$hot.id}.html">{$hot.title}</a>
                            <span class="views">{$hot.view_count}</span>
                        </li>
                    {/carefree:article}
                </ul>
            </div>

            <!-- 分类列表 -->
            <div class="widget widget-categories">
                <h3>文章分类</h3>
                <ul class="category-list">
                    {carefree:category limit='10' id='cat'}
                        <li>
                            <a href="/category/{$cat.id}.html">
                                {$cat.name}
                                <span class="count">({$cat.article_count})</span>
                            </a>
                        </li>
                    {/carefree:category}
                </ul>
            </div>

            <!-- 标签云 -->
            <div class="widget widget-tags">
                <h3>热门标签</h3>
                {carefree:tagcloud limit='30' orderby='count' /}
            </div>

            <!-- 最新评论 -->
            <div class="widget widget-comments">
                <h3>最新评论</h3>
                {carefree:comment limit='5' type='latest' id='comment'}
                    <div class="comment-mini">
                        <div class="comment-author">{$comment.display_name}</div>
                        <div class="comment-text">{$comment.short_content}</div>
                        <div class="comment-time">{$comment.formatted_time}</div>
                    </div>
                {/carefree:comment}
            </div>

            <!-- 归档 -->
            <div class="widget widget-archive">
                <h3>文章归档</h3>
                <ul class="archive-list">
                    {carefree:archive type='month' limit='12' id='archive'}
                        <li>
                            <a href="{$archive.url}">
                                {$archive.display_date}
                                <span>({$archive.article_count})</span>
                            </a>
                        </li>
                    {/carefree:archive}
                </ul>
            </div>

            <!-- 热门作者 -->
            <div class="widget widget-authors">
                <h3>热门作者</h3>
                {carefree:author limit='5' orderby='article' id='author'}
                    <div class="author-mini">
                        <img src="{$author.avatar}" class="avatar">
                        <div class="author-info">
                            <div class="name">{$author.display_name}</div>
                            <div class="stats">{$author.article_count}篇文章</div>
                        </div>
                    </div>
                {/carefree:author}
            </div>

            <!-- 友情链接 -->
            <div class="widget widget-links">
                <h3>友情链接</h3>
                <div class="links-grid">
                    {carefree:link limit='10' id='link'}
                        <a href="{$link.url}" target="_blank" title="{$link.description}">
                            {$link.name}
                        </a>
                    {/carefree:link}
                </div>
            </div>
        </aside>
    </div>

    <!-- 页脚 -->
    <footer class="site-footer">
        <div class="container">
            <p>&copy; 2025 {carefree:config name='site_name' /}. All rights reserved.</p>
            <p>
                本站共有 {carefree:stats type='article' /} 篇文章，
                {carefree:stats type='category' /} 个分类，
                累计浏览 {carefree:stats type='view' /} 次
            </p>
        </div>
    </footer>
</body>
</html>
```

### 2. 文章详情页模板

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{$article.seo_title} - {carefree:config name='site_name' /}</title>

    {carefree:seo
        title='$article.seo_title'
        keywords='$article.seo_keywords'
        description='$article.seo_description'
        image='$article.cover_image'
        type='article' /}

    <link rel="stylesheet" href="/static/css/style.css">
</head>
<body>
    <header class="site-header">
        <!-- 同首页 -->
    </header>

    <div class="container">
        <!-- 面包屑导航 -->
        <div class="breadcrumb-wrapper">
            {carefree:breadcrumb separator=' > ' id='crumb'}
                <a href="{$crumb.url}">{$crumb.name}</a>
            {/carefree:breadcrumb}
        </div>

        <main class="main-content">
            <article class="article-detail">
                <!-- 文章头部 -->
                <header class="article-header">
                    <h1 class="article-title">{$article.title}</h1>

                    <div class="article-meta">
                        {carefree:userinfo uid='{$article.user_id}'}
                            <div class="author-info">
                                <img src="{$userinfo.avatar}" class="avatar">
                                <div>
                                    <div class="author-name">{$userinfo.display_name}</div>
                                    <div class="author-role">{$userinfo.role_name}</div>
                                </div>
                            </div>
                        {/carefree:userinfo}

                        <div class="meta-items">
                            <span><i class="icon-calendar"></i>{$article.create_time|date='Y-m-d H:i'}</span>
                            <span><i class="icon-category"></i>{$article.category.name}</span>
                            <span><i class="icon-eye"></i>{$article.view_count} 阅读</span>
                            <span><i class="icon-comment"></i>{$article.comment_count} 评论</span>
                        </div>
                    </div>

                    {if $article.cover_image}
                        <div class="article-cover">
                            <img src="{$article.cover_image}" alt="{$article.title}">
                        </div>
                    {/if}
                </header>

                <!-- 文章内容 -->
                <div class="article-body">
                    {$article.content}
                </div>

                <!-- 文章标签 -->
                <div class="article-tags">
                    <strong>标签：</strong>
                    {volist name="article.tags" id="tag"}
                        <a href="/tag/{$tag.id}.html" class="tag">{$tag.name}</a>
                    {/volist}
                </div>

                <!-- 社交分享 -->
                <div class="article-share">
                    <h4>分享到：</h4>
                    {carefree:share platforms='wechat,weibo,qq,twitter,facebook' style='text' /}
                </div>

                <!-- 作者信息卡片 -->
                {carefree:userinfo uid='{$article.user_id}'}
                    <div class="author-card">
                        <img src="{$userinfo.avatar}" class="author-avatar">
                        <div class="author-content">
                            <h3>{$userinfo.display_name}</h3>
                            <p class="author-role">{$userinfo.role_name}</p>
                            <div class="author-stats">
                                <span>{$userinfo.article_count} 篇文章</span>
                                <span>{$userinfo.total_views} 阅读</span>
                                <span>{$userinfo.total_likes} 点赞</span>
                            </div>
                        </div>
                    </div>
                {/carefree:userinfo}

                <!-- 相关文章推荐 -->
                <section class="related-section">
                    <h3>相关推荐</h3>
                    <div class="related-grid">
                        {carefree:related aid='{$article.id}' limit='6' type='auto' id='related'}
                            <div class="related-card">
                                {if $related.cover_image}
                                    <img src="{$related.cover_image}" alt="{$related.title}">
                                {/if}
                                <h4>
                                    <a href="/article/{$related.id}.html">{$related.title}</a>
                                </h4>
                                <div class="meta">
                                    <span>{$related.view_count} 阅读</span>
                                </div>
                            </div>
                        {/carefree:related}
                    </div>
                </section>

                <!-- 评论区 -->
                <section class="comments-section">
                    <h3>文章评论 ({carefree:stats type='comment' aid='{$article.id}' /})</h3>

                    <!-- 评论列表 -->
                    <div class="comment-list">
                        {carefree:comment aid='{$article.id}' limit='20' type='latest' id='comment'}
                            <div class="comment-item" id="comment-{$comment.id}">
                                <div class="comment-avatar">
                                    <img src="/static/avatar.png" alt="{$comment.display_name}">
                                </div>
                                <div class="comment-content">
                                    <div class="comment-author">
                                        {$comment.display_name}
                                        {if $comment.is_admin}
                                            <span class="admin-badge">管理员</span>
                                        {/if}
                                    </div>
                                    <div class="comment-text">{$comment.content}</div>
                                    <div class="comment-footer">
                                        <span class="time">{$comment.formatted_time}</span>
                                        <button class="btn-like">👍 {$comment.like_count}</button>
                                        <button class="btn-reply">回复</button>
                                    </div>
                                </div>
                            </div>
                        {/carefree:comment}
                    </div>
                </section>
            </article>
        </main>

        <aside class="sidebar">
            <!-- 目录导航 -->
            <div class="widget widget-toc sticky">
                <h3>文章目录</h3>
                <div id="toc"></div>
            </div>

            <!-- 该作者的其他文章 -->
            {carefree:userinfo uid='{$article.user_id}'}
                <div class="widget">
                    <h3>{$userinfo.display_name} 的其他文章</h3>
                    {carefree:article userid='{$userinfo.id}' exclude='{$article.id}' limit='5' id='more'}
                        <div class="article-mini">
                            <a href="/article/{$more.id}.html">{$more.title}</a>
                        </div>
                    {/carefree:article}
                </div>
            {/carefree:userinfo}

            <!-- 同类文章 -->
            <div class="widget">
                <h3>同类文章</h3>
                {carefree:article
                    typeid='{$article.category_id}'
                    exclude='{$article.id}'
                    limit='5'
                    id='same'}
                    <div class="article-mini">
                        <a href="/article/{$same.id}.html">{$same.title}</a>
                    </div>
                {/carefree:article}
            </div>
        </aside>
    </div>

    <footer class="site-footer">
        <!-- 同首页 -->
    </footer>
</body>
</html>
```

### 3. 分类列表页模板

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{$category.name} - {carefree:config name='site_name' /}</title>

    {carefree:seo
        title='$category.seo_title'
        keywords='$category.seo_keywords'
        description='$category.seo_description'
        type='website' /}
</head>
<body>
    <header class="site-header">
        <!-- 导航 -->
    </header>

    <div class="container">
        <!-- 面包屑 -->
        {carefree:breadcrumb separator=' > '}
        {/carefree:breadcrumb}

        <main class="main-content">
            <!-- 分类信息 -->
            <div class="category-header">
                <h1>{$category.name}</h1>
                {if $category.description}
                    <p class="category-desc">{$category.description}</p>
                {/if}
                <div class="category-stats">
                    共 {carefree:stats type='article' catid='{$category.id}' /} 篇文章，
                    {carefree:stats type='view' catid='{$category.id}' /} 次浏览
                </div>
            </div>

            <!-- 子分类 -->
            {carefree:category parent='{$category.id}' id='sub'}
                <div class="sub-categories">
                    <h3>子分类</h3>
                    <div class="sub-cat-grid">
                        <a href="/category/{$sub.id}.html" class="sub-cat-item">
                            <h4>{$sub.name}</h4>
                            <span>{$sub.article_count} 篇</span>
                        </a>
                    </div>
                </div>
            {/carefree:category}

            <!-- 分类文章列表 -->
            <div class="article-list">
                {carefree:article typeid='{$category.id}' limit='20' id='article'}
                    <article class="article-item">
                        <!-- 文章内容 -->
                    </article>
                {/carefree:article}
            </div>

            <!-- 分页 -->
            {carefree:pagelist
                total='{$total}'
                pagesize='20'
                currentpage='{$page}'
                url='/category/{$category.id}/page-{page}.html'
                style='full' /}
        </main>

        <aside class="sidebar">
            <!-- 本分类热门 -->
            <div class="widget">
                <h3>本分类热门</h3>
                {carefree:article typeid='{$category.id}' flag='hot' limit='5' id='hot'}
                    <div>{$hot.title}</div>
                {/carefree:article}
            </div>
        </aside>
    </div>
</body>
</html>
```

---

## 🎯 特定功能示例

### 4. 搜索结果页

```html
<div class="search-results">
    <h1>搜索结果：{$keyword}</h1>
    <p>找到 {$total} 篇相关文章</p>

    {carefree:article limit='20' id='article'}
        <div class="search-item">
            <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
            <p class="excerpt">{$article.summary}</p>
            <div class="meta">
                <span>{$article.category.name}</span>
                <span>{$article.create_time|date='Y-m-d'}</span>
            </div>
        </div>
    {/carefree:article}
</div>
```

### 5. 标签聚合页

```html
<div class="tag-page">
    <h1>标签：{$tag.name}</h1>
    <p>共 {$tag.article_count} 篇文章</p>

    {carefree:article tagid='{$tag.id}' limit='20' id='article'}
        <div class="article-card">
            <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
            <p>{$article.summary}</p>
        </div>
    {/carefree:article}
</div>
```

### 6. 作者主页

```html
{carefree:userinfo uid='{$author_id}'}
    <div class="author-page">
        <div class="author-profile">
            <img src="{$userinfo.avatar}" class="author-avatar-large">
            <h1>{$userinfo.display_name}</h1>
            <p>{$userinfo.role_name}</p>

            <div class="author-stats-large">
                <div class="stat">
                    <div class="value">{$userinfo.article_count}</div>
                    <div class="label">文章</div>
                </div>
                <div class="stat">
                    <div class="value">{$userinfo.total_views}</div>
                    <div class="label">阅读</div>
                </div>
                <div class="stat">
                    <div class="value">{$userinfo.total_likes}</div>
                    <div class="label">点赞</div>
                </div>
            </div>
        </div>

        <h2>Ta的文章</h2>
        {carefree:article userid='{$userinfo.id}' limit='20' id='article'}
            <div class="article-item">
                <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
                <p>{$article.summary}</p>
            </div>
        {/carefree:article}
    </div>
{/carefree:userinfo}
```

### 7. 归档页面

```html
<div class="archive-page">
    <h1>文章归档 - {$year}年{$month}月</h1>

    {carefree:article days='30' limit='100' id='article'}
        <div class="archive-item">
            <span class="date">{$article.create_time|date='Y-m-d'}</span>
            <a href="/article/{$article.id}.html">{$article.title}</a>
            <span class="views">{$article.view_count}</span>
        </div>
    {/carefree:article}
</div>
```

---

## 🎨 特殊效果示例

### 8. 瀑布流布局

```html
<div class="masonry-grid">
    {carefree:article hascover='1' limit='20' id='article'}
        <div class="masonry-item">
            <img src="{$article.cover_image}" alt="{$article.title}">
            <div class="item-content">
                <h3>{$article.title}</h3>
                <p>{$article.summary}</p>
            </div>
        </div>
    {/carefree:article}
</div>
```

### 9. 卡片式布局

```html
<div class="card-grid">
    {carefree:article limit='12' id='article'}
        <div class="card">
            <div class="card-image">
                {if $article.cover_image}
                    <img src="{$article.cover_image}">
                {else}
                    <div class="no-image">{$article.title|substr=0,1}</div>
                {/if}
            </div>
            <div class="card-body">
                <h3>{$article.title}</h3>
                <p>{$article.summary}</p>
                <div class="card-footer">
                    <span>{$article.create_time|date='Y-m-d'}</span>
                    <span>{$article.view_count} 阅读</span>
                </div>
            </div>
        </div>
    {/carefree:article}
</div>
```

### 10. 时间线布局

```html
<div class="timeline">
    {carefree:article limit='20' order='create_time desc' id='article'}
        <div class="timeline-item">
            <div class="timeline-date">
                {$article.create_time|date='Y-m-d'}
            </div>
            <div class="timeline-content">
                <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
                <p>{$article.summary}</p>
                {carefree:userinfo uid='{$article.user_id}'}
                    <div class="author">作者：{$userinfo.display_name}</div>
                {/carefree:userinfo}
            </div>
        </div>
    {/carefree:article}
</div>
```

---

## 📱 响应式布局示例

### 11. 移动端优化

```html
<!-- PC端显示完整侧边栏，移动端隐藏 -->
<aside class="sidebar hidden-mobile">
    {carefree:category limit='10' id='cat'}
        <div>{$cat.name}</div>
    {/carefree:category}
</aside>

<!-- 移动端显示精简版 -->
<div class="mobile-menu hidden-desktop">
    {carefree:nav limit='5' id='nav'}
        <a href="{$nav.url}">{$nav.name}</a>
    {/carefree:nav}
</div>
```

---

## 🔧 性能优化示例

### 12. 懒加载

```html
<!-- 首屏加载少量数据 -->
{carefree:article limit='5' id='article'}
    <div class="article-item">{$article.title}</div>
{/carefree:article}

<!-- 其余数据通过AJAX加载 -->
<div id="more-articles" data-page="2"></div>
<button onclick="loadMore()">加载更多</button>
```

### 13. 缓存优化

```html
<!-- 使用静态页面生成，所有标签都会被编译成静态HTML -->
<!-- 无需担心性能问题 -->
```

---

这些示例涵盖了绝大多数实际应用场景，可以直接复制使用或根据需求修改。
