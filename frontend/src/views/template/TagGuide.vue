<template>
  <div class="tag-guide-container">
    <el-card class="header-card">
      <template #header>
        <div class="card-header">
          <h2>
            <el-icon><Document /></el-icon>
            Carefree 模板标签使用教程
          </h2>
        </div>
      </template>
      <el-alert
        title="关于 Carefree 模板标签"
        type="info"
        description="Carefree 模板标签是本 CMS 系统提供的自定义模板标签库，用于在模板中快速调用系统数据。所有标签都支持服务端渲染，有利于 SEO 优化。目前共提供 40 个功能标签。"
        :closable="false"
        show-icon
      />
    </el-card>

    <el-card class="content-card">
      <el-container class="guide-container">
        <!-- 左侧菜单 -->
        <el-aside width="260px" class="menu-aside">
          <el-menu
            :default-active="activeSection"
            @select="handleMenuSelect"
            class="guide-menu"
          >
            <!-- 基础标签 -->
            <el-sub-menu index="basic">
              <template #title>
                <el-icon><Setting /></el-icon>
                <span>基础标签</span>
              </template>
              <el-menu-item index="config">
                <el-icon><Tools /></el-icon>
                <span>配置标签</span>
              </el-menu-item>
              <el-menu-item index="stats">
                <el-icon><DataLine /></el-icon>
                <span>统计标签</span>
              </el-menu-item>
            </el-sub-menu>

            <!-- 内容标签 -->
            <el-sub-menu index="content">
              <template #title>
                <el-icon><Document /></el-icon>
                <span>内容标签</span>
              </template>
              <el-menu-item index="article">
                <el-icon><Reading /></el-icon>
                <span>文章列表</span>
              </el-menu-item>
              <el-menu-item index="arcinfo">
                <el-icon><Tickets /></el-icon>
                <span>单篇文章</span>
              </el-menu-item>
              <el-menu-item index="category">
                <el-icon><Folder /></el-icon>
                <span>分类列表</span>
              </el-menu-item>
              <el-menu-item index="catinfo">
                <el-icon><FolderOpened /></el-icon>
                <span>单个分类</span>
              </el-menu-item>
              <el-menu-item index="tag">
                <el-icon><PriceTag /></el-icon>
                <span>标签列表</span>
              </el-menu-item>
              <el-menu-item index="taginfo">
                <el-icon><CollectionTag /></el-icon>
                <span>单个标签</span>
              </el-menu-item>
              <el-menu-item index="topic">
                <el-icon><Collection /></el-icon>
                <span>专题列表</span>
              </el-menu-item>
              <el-menu-item index="topicinfo">
                <el-icon><Memo /></el-icon>
                <span>单个专题</span>
              </el-menu-item>
              <el-menu-item index="page">
                <el-icon><Notebook /></el-icon>
                <span>单页内容</span>
              </el-menu-item>
              <el-menu-item index="pageinfo">
                <el-icon><Notebook /></el-icon>
                <span>单页信息</span>
              </el-menu-item>
              <el-menu-item index="related">
                <el-icon><Connection /></el-icon>
                <span>相关文章</span>
              </el-menu-item>
              <el-menu-item index="prevnext">
                <el-icon><Connection /></el-icon>
                <span>上下篇导航</span>
              </el-menu-item>
              <el-menu-item index="articleflag">
                <el-icon><PriceTag /></el-icon>
                <span>文章属性</span>
              </el-menu-item>
              <el-menu-item index="customfield">
                <el-icon><Tools /></el-icon>
                <span>自定义字段</span>
              </el-menu-item>
            </el-sub-menu>

            <!-- 导航标签 -->
            <el-sub-menu index="navigation">
              <template #title>
                <el-icon><Menu /></el-icon>
                <span>导航标签</span>
              </template>
              <el-menu-item index="nav">
                <el-icon><Operation /></el-icon>
                <span>导航菜单</span>
              </el-menu-item>
              <el-menu-item index="breadcrumb">
                <el-icon><Position /></el-icon>
                <span>面包屑导航</span>
              </el-menu-item>
            </el-sub-menu>

            <!-- 媒体标签 -->
            <el-sub-menu index="media">
              <template #title>
                <el-icon><Picture /></el-icon>
                <span>媒体标签</span>
              </template>
              <el-menu-item index="slider">
                <el-icon><PictureFilled /></el-icon>
                <span>幻灯片</span>
              </el-menu-item>
              <el-menu-item index="ad">
                <el-icon><Promotion /></el-icon>
                <span>广告</span>
              </el-menu-item>
              <el-menu-item index="randomimg">
                <el-icon><Picture /></el-icon>
                <span>随机图片</span>
              </el-menu-item>
            </el-sub-menu>

            <!-- 交互标签 -->
            <el-sub-menu index="interaction">
              <template #title>
                <el-icon><ChatDotRound /></el-icon>
                <span>交互标签</span>
              </template>
              <el-menu-item index="comment">
                <el-icon><ChatLineRound /></el-icon>
                <span>评论列表</span>
              </el-menu-item>
              <el-menu-item index="search">
                <el-icon><Search /></el-icon>
                <span>搜索框</span>
              </el-menu-item>
              <el-menu-item index="share">
                <el-icon><Share /></el-icon>
                <span>社交分享</span>
              </el-menu-item>
              <el-menu-item index="seo">
                <el-icon><Compass /></el-icon>
                <span>SEO标签</span>
              </el-menu-item>
            </el-sub-menu>

            <!-- 用户标签 -->
            <el-sub-menu index="user">
              <template #title>
                <el-icon><User /></el-icon>
                <span>用户标签</span>
              </template>
              <el-menu-item index="userinfo">
                <el-icon><Avatar /></el-icon>
                <span>用户信息</span>
              </el-menu-item>
              <el-menu-item index="author">
                <el-icon><UserFilled /></el-icon>
                <span>作者列表</span>
              </el-menu-item>
              <el-menu-item index="frontuser">
                <el-icon><User /></el-icon>
                <span>前台用户</span>
              </el-menu-item>
              <el-menu-item index="memberlevel">
                <el-icon><Medal /></el-icon>
                <span>会员等级</span>
              </el-menu-item>
              <el-menu-item index="contribution">
                <el-icon><Edit /></el-icon>
                <span>投稿列表</span>
              </el-menu-item>
            </el-sub-menu>

            <!-- 扩展标签 -->
            <el-sub-menu index="extension">
              <template #title>
                <el-icon><Grid /></el-icon>
                <span>扩展标签</span>
              </template>
              <el-menu-item index="link">
                <el-icon><Link /></el-icon>
                <span>友情链接</span>
              </el-menu-item>
              <el-menu-item index="notification">
                <el-icon><Bell /></el-icon>
                <span>消息通知</span>
              </el-menu-item>
              <el-menu-item index="archive">
                <el-icon><Calendar /></el-icon>
                <span>归档列表</span>
              </el-menu-item>
              <el-menu-item index="tagcloud">
                <el-icon><Sunny /></el-icon>
                <span>标签云</span>
              </el-menu-item>
              <el-menu-item index="hotwords">
                <el-icon><Sunny /></el-icon>
                <span>热门关键词</span>
              </el-menu-item>
              <el-menu-item index="pagelist">
                <el-icon><DCaret /></el-icon>
                <span>分页</span>
              </el-menu-item>
              <el-menu-item index="rank">
                <el-icon><Medal /></el-icon>
                <span>排行榜</span>
              </el-menu-item>
              <el-menu-item index="position">
                <el-icon><Grid /></el-icon>
                <span>内容区块</span>
              </el-menu-item>
              <el-menu-item index="loop">
                <el-icon><Operation /></el-icon>
                <span>通用循环</span>
              </el-menu-item>
              <el-menu-item index="sql">
                <el-icon><Tools /></el-icon>
                <span>SQL查询</span>
              </el-menu-item>
            </el-sub-menu>

            <!-- 通用说明 -->
            <el-menu-item index="common">
              <el-icon><QuestionFilled /></el-icon>
              <span>通用说明</span>
            </el-menu-item>
          </el-menu>
        </el-aside>

        <!-- 右侧内容 -->
        <el-main class="content-main">
          <!-- 配置标签 -->
          <div v-show="activeSection === 'config'" class="tag-section">
            <h3>carefree:config - 获取系统配置</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取系统配置信息，如网站名称、关键词、描述等。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[{name: 'name', required: '是', default: '-', description: '配置项名称'}]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例 1：获取网站名称</div>
              <pre><code>{{`<title>{carefree:config name='site_name' /}</title>`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 2：获取SEO关键词</div>
              <pre><code>{{`<meta name="keywords" content="{carefree:config name='seo_keywords' /}">`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 3：获取版权信息</div>
              <pre><code>{{`<p>{carefree:config name='site_copyright' /}</p>`}}</code></pre>
            </el-card>

            <el-divider content-position="left">可用配置项</el-divider>
            <div style="margin-bottom: 15px;">
              <strong>基础配置：</strong>
              <el-tag v-for="item in ['site_name', 'site_logo', 'site_favicon', 'site_url', 'site_copyright', 'site_icp', 'site_police']" :key="item" class="config-tag">{{ item }}</el-tag>
            </div>
            <div style="margin-bottom: 15px;">
              <strong>SEO配置：</strong>
              <el-tag v-for="item in ['seo_title', 'seo_keywords', 'seo_description', 'site_keywords', 'site_description']" :key="item" class="config-tag">{{ item }}</el-tag>
            </div>
            <div style="margin-bottom: 15px;">
              <strong>上传配置：</strong>
              <el-tag v-for="item in ['upload_max_size', 'upload_image_ext', 'upload_file_ext', 'upload_video_ext']" :key="item" class="config-tag">{{ item }}</el-tag>
            </div>
            <div style="margin-bottom: 15px;">
              <strong>文章配置：</strong>
              <el-tag v-for="item in ['article_page_size', 'article_default_views', 'article_default_downloads']" :key="item" class="config-tag">{{ item }}</el-tag>
            </div>
            <div style="margin-bottom: 15px;">
              <strong>模板配置：</strong>
              <el-tag v-for="item in ['default_template', 'current_template_theme']" :key="item" class="config-tag">{{ item }}</el-tag>
            </div>
          </div>

          <!-- 统计标签 -->
          <div v-show="activeSection === 'stats'" class="tag-section">
            <h3>carefree:stats - 获取统计信息</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于显示网站统计数据，如文章总数、浏览量等。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'type', required: '是', default: 'article', description: '统计类型：article, category, tag, view, todayarticle, todayview'},
              {name: 'catid', required: '否', default: '0', description: '分类ID（用于分类统计）'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：显示网站统计</div>
              <pre><code>{{`<div class="site-stats">
    <span>文章总数: {carefree:stats type='article' /}</span>
    <span>分类总数: {carefree:stats type='category' /}</span>
    <span>标签总数: {carefree:stats type='tag' /}</span>
    <span>总浏览量: {carefree:stats type='view' /}</span>
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 文章列表 -->
          <div v-show="activeSection === 'article'" class="tag-section">
            <h3>carefree:article - 获取文章列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取文章列表数据，支持多种筛选条件和排序方式。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="articleParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例 1：获取最新文章</div>
              <pre><code>{{`{carefree:article limit='10' order='create_time desc' id='article'}
<div class="article-item">
    <h3><a href="/article/{$article.id}.html">{$article.title}</a></h3>
    <p>{$article.summary}</p>
</div>
{/carefree:article}`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 2：获取推荐文章</div>
              <pre><code>{{`{carefree:article flag='recommend' hascover='1' limit='6' id='article'}
<div class="recommend-article">
    <img src="{$article.cover_image}" alt="{$article.title}">
    <h4>{$article.title}</h4>
</div>
{/carefree:article}`}}</code></pre>
            </el-card>
          </div>

          <!-- 单篇文章 -->
          <div v-show="activeSection === 'arcinfo'" class="tag-section">
            <h3>carefree:arcinfo - 获取单篇文章</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取指定ID的单篇文章完整信息。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[{name: 'aid', required: '是', default: '-', description: '文章ID'}]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：获取指定文章</div>
              <pre><code>{{`{carefree:arcinfo aid='1'}
<article>
    <h1>{$article.title}</h1>
    <div class="meta">
        <span>作者: {$article.user.username}</span>
        <span>发布时间: {$article.create_time}</span>
    </div>
    <div class="content">{$article.content|raw}</div>
</article>
{/carefree:arcinfo}`}}</code></pre>
            </el-card>
          </div>

          <!-- 分类列表 -->
          <div v-show="activeSection === 'category'" class="tag-section">
            <h3>carefree:category - 获取分类列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取文章分类数据，支持获取顶级分类或指定父级的子分类。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="categoryParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：获取顶级分类</div>
              <pre><code>{{`{carefree:category parent='0' limit='10' id='category'}
<li>
    <a href="/category/{$category.id}.html">
        {$category.name} ({$category.article_count})
    </a>
</li>
{/carefree:category}`}}</code></pre>
            </el-card>
          </div>

          <!-- 单个分类 -->
          <div v-show="activeSection === 'catinfo'" class="tag-section">
            <h3>carefree:catinfo - 获取单个分类</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取指定ID的单个分类完整信息。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[{name: 'catid', required: '是', default: '-', description: '分类ID'}]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：显示分类信息</div>
              <pre><code>{{`{carefree:catinfo catid='1'}
<div class="category-info">
    <h1>{$category.name}</h1>
    <p>{$category.description}</p>
    <span>文章数: {$category.article_count}</span>
</div>
{/carefree:catinfo}`}}</code></pre>
            </el-card>
          </div>

          <!-- 标签列表 -->
          <div v-show="activeSection === 'tag'" class="tag-section">
            <h3>carefree:tag - 获取标签列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取文章标签数据，支持按文章数量排序。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="tagParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：热门标签</div>
              <pre><code>{{`<div class="tag-cloud">
{carefree:tag limit='20' order='article_count desc' id='tag'}
    <a href="/tag/{$tag.id}.html">{$tag.name}</a>
{/carefree:tag}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 单个标签 -->
          <div v-show="activeSection === 'taginfo'" class="tag-section">
            <h3>carefree:taginfo - 获取单个标签</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取指定ID的单个标签完整信息。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[{name: 'tagid', required: '是', default: '-', description: '标签ID'}]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：显示标签信息</div>
              <pre><code>{{`{carefree:taginfo tagid='1'}
<div class="tag-info">
    <h1>#{$tag.name}</h1>
    <p>{$tag.description}</p>
    <span>文章数: {$tag.article_count}</span>
</div>
{/carefree:taginfo}`}}</code></pre>
            </el-card>
          </div>

          <!-- 专题列表 -->
          <div v-show="activeSection === 'topic'" class="tag-section">
            <h3>carefree:topic - 获取专题列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取专题列表数据，支持按浏览量、文章数等排序。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'limit', required: '否', default: '10', description: '返回数量'},
              {name: 'status', required: '否', default: '-', description: '状态：1启用，0禁用'},
              {name: 'orderby', required: '否', default: 'sort_order', description: '排序：sort_order, view_count, article_count, create_time'},
              {name: 'empty', required: '否', default: '-', description: '空数据时显示的内容'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：显示专题列表</div>
              <pre><code>{{`{carefree:topic limit='10' status='1' orderby='view_count' id='topic'}
<div class="topic-item">
    <img src="{$topic.cover_image}" alt="{$topic.name}">
    <h3><a href="/topic/{$topic.id}.html">{$topic.name}</a></h3>
    <p>{$topic.description}</p>
    <div class="stats">
        文章: {$topic.article_count} | 浏览: {$topic.view_count}
    </div>
</div>
{/carefree:topic}`}}</code></pre>
            </el-card>
          </div>

          <!-- 单个专题 -->
          <div v-show="activeSection === 'topicinfo'" class="tag-section">
            <h3>carefree:topicinfo - 获取单个专题</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取指定ID的单个专题完整信息。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[{name: 'topicid', required: '是', default: '-', description: '专题ID'}]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：显示专题信息</div>
              <pre><code>{{`{carefree:topicinfo topicid='1'}
<div class="topic-header">
    <h1>{$topic.name}</h1>
    <p>{$topic.description}</p>
    <div>文章数: {$topic.article_count} | 浏览: {$topic.view_count}</div>
</div>
{/carefree:topicinfo}`}}</code></pre>
            </el-card>
          </div>

          <!-- 单页内容 -->
          <div v-show="activeSection === 'page'" class="tag-section">
            <h3>carefree:page - 获取单页内容</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取单页面内容，如关于我们、联系我们等静态页面。支持按ID、别名查询单个，或查询列表。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="pageParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例 1：按ID获取单页</div>
              <pre><code>{{`{carefree:page id='1' name='page'}
<div class="page-content">
    <h1>{$page.title}</h1>
    <div>{$page.content|raw}</div>
</div>
{/carefree:page}`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 2：按别名获取单页</div>
              <pre><code>{{`{carefree:page alias='about' name='page'}
<article>
    <h2>{$page.title}</h2>
    <div>{$page.content|raw}</div>
</article>
{/carefree:page}`}}</code></pre>
            </el-card>
          </div>

          <!-- 单页信息 -->
          <div v-show="activeSection === 'pageinfo'" class="tag-section">
            <h3>carefree:pageinfo - 获取单页详细信息</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取单个单页的详细信息，类似 arcinfo 和 catinfo，专门用于获取单页数据。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '否', default: '-', description: '单页ID'},
              {name: 'alias', required: '否', default: '-', description: '单页别名'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例 1：按ID获取单页信息</div>
              <pre><code>{{`{carefree:pageinfo id='1'}
<div class="page-detail">
    <h1>{$page.title}</h1>
    <div class="page-meta">
        <span>创建时间：{$page.create_time}</span>
        <span>更新时间：{$page.update_time}</span>
    </div>
    <div class="page-body">
        {$page.content|raw}
    </div>
</div>
{/carefree:pageinfo}`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 2：按别名获取单页</div>
              <pre><code>{{`{carefree:pageinfo alias='contact'}
<section class="contact-page">
    <h2>{$page.title}</h2>
    <div>{$page.content|raw}</div>
</section>
{/carefree:pageinfo}`}}</code></pre>
            </el-card>
          </div>

          <!-- 相关文章 -->
          <div v-show="activeSection === 'related'" class="tag-section">
            <h3>carefree:related - 获取相关文章</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取当前文章的相关文章，支持按标签或分类匹配。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="relatedParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：基于标签的相关文章</div>
              <pre><code>{{`{carefree:related aid='$article.id' limit='5' type='tag' id='related'}
<div class="related-item">
    <a href="/article/{$related.id}.html">
        <img src="{$related.cover_image}" alt="{$related.title}">
        <h5>{$related.title}</h5>
    </a>
</div>
{/carefree:related}`}}</code></pre>
            </el-card>
          </div>

          <!-- 上下篇导航 -->
          <div v-show="activeSection === 'prevnext'" class="tag-section">
            <h3>carefree:prevnext - 上一篇/下一篇文章导航</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于在文章详情页生成上一篇和下一篇的导航链接，方便用户浏览相邻文章。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'aid', required: '是', default: '-', description: '当前文章ID'},
              {name: 'catid', required: '否', default: '0', description: '分类ID'},
              {name: 'type', required: '否', default: 'same', description: '导航类型：same-同分类，all-所有分类'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：同分类上下篇导航</div>
              <pre><code>{{`{carefree:prevnext aid='$article.id' catid='$article.category_id' type='same'}
<div class="article-navigation">
    {if $prev}
    <div class="prev-article">
        <a href="/article/{$prev.id}.html">
            <span class="nav-label">← 上一篇</span>
            <span class="nav-title">{$prev.title}</span>
        </a>
    </div>
    {/if}

    {if $next}
    <div class="next-article">
        <a href="/article/{$next.id}.html">
            <span class="nav-label">下一篇 →</span>
            <span class="nav-title">{$next.title}</span>
        </a>
    </div>
    {/if}
</div>
{/carefree:prevnext}`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例：全站上下篇导航</div>
              <pre><code>{{`{carefree:prevnext aid='$article.id' type='all'}
<nav class="post-nav">
    <div class="nav-links">
        {if $prev}
        <a href="/article/{$prev.id}.html" class="prev">
            {$prev.title}
        </a>
        {/if}
        {if $next}
        <a href="/article/{$next.id}.html" class="next">
            {$next.title}
        </a>
        {/if}
    </div>
</nav>
{/carefree:prevnext}`}}</code></pre>
            </el-card>
          </div>

          <!-- 文章属性 -->
          <div v-show="activeSection === 'articleflag'" class="tag-section">
            <h3>carefree:articleflag - 获取文章属性列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取系统中定义的文章属性（如推荐、热门、置顶等），可在筛选文章时使用。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'limit', required: '否', default: '0', description: '数量限制，0表示不限制'},
              {name: 'status', required: '否', default: '', description: '状态：1-启用，0-禁用'},
              {name: 'id', required: '否', default: 'flag', description: '循环变量名'},
              {name: 'empty', required: '否', default: '', description: '无数据时的提示文本'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：显示所有文章属性</div>
              <pre><code>{{`{carefree:articleflag status='1' id='flag'}
<div class="article-flag-list">
    <span class="badge badge-{$flag.flag_value}">
        {$flag.name}
    </span>
</div>
{/carefree:articleflag}`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例：用于筛选导航</div>
              <pre><code>{{`<div class="article-filter">
    <a href="/articles" class="all">全部</a>
    {carefree:articleflag status='1' id='flag'}
    <a href="/articles?flag={$flag.flag_value}">
        {$flag.name}
    </a>
    {/carefree:articleflag}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 自定义字段 -->
          <div v-show="activeSection === 'customfield'" class="tag-section">
            <h3>carefree:customfield - 获取自定义字段值</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取文章、单页、分类等内容的自定义字段值，支持扩展内容属性。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'aid', required: '否', default: '-', description: '文章ID'},
              {name: 'pageid', required: '否', default: '-', description: '单页ID'},
              {name: 'catid', required: '否', default: '-', description: '分类ID'},
              {name: 'fieldname', required: '是', default: '-', description: '字段键名'},
              {name: 'modeltype', required: '否', default: 'article', description: '模型类型：article, page, category'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例 1：获取文章自定义字段</div>
              <pre><code>{{`<!-- 在文章详情页获取作者简介 -->
{carefree:arcinfo aid='1'}
<article>
    <h1>{$article.title}</h1>
    <div class="author-intro">
        <h4>作者简介</h4>
        <p>{carefree:customfield aid='$article.id' fieldname='author_intro' modeltype='article' /}</p>
    </div>
    <div class="article-source">
        来源：{carefree:customfield aid='$article.id' fieldname='source' modeltype='article' /}
    </div>
</article>
{/carefree:arcinfo}`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 2：获取单页自定义字段</div>
              <pre><code>{{`{carefree:pageinfo id='1'}
<div class="contact-page">
    <h2>{$page.title}</h2>
    <div class="contact-info">
        <p>电话：{carefree:customfield pageid='$page.id' fieldname='phone' modeltype='page' /}</p>
        <p>邮箱：{carefree:customfield pageid='$page.id' fieldname='email' modeltype='page' /}</p>
        <p>地址：{carefree:customfield pageid='$page.id' fieldname='address' modeltype='page' /}</p>
    </div>
</div>
{/carefree:pageinfo}`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 3：分类扩展信息</div>
              <pre><code>{{`{carefree:catinfo catid='1'}
<div class="category-header">
    <h1>{$category.name}</h1>
    <p>{$category.description}</p>
    <!-- 分类自定义横幅图 -->
    <img src="{carefree:customfield catid='$category.id' fieldname='banner_image' modeltype='category' /}" alt="Banner">
</div>
{/carefree:catinfo}`}}</code></pre>
            </el-card>
          </div>

          <!-- 导航菜单 -->
          <div v-show="activeSection === 'nav'" class="tag-section">
            <h3>carefree:nav - 获取导航菜单</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取网站导航菜单数据。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'limit', required: '否', default: '0', description: '返回数量，0表示不限制'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：顶部导航</div>
              <pre><code>{{`<nav class="main-nav">
{carefree:nav limit='10' id='nav'}
    <a href="{$nav.url}" {if condition="$nav.target"}target="{$nav.target}"{/if}>
        {$nav.title}
    </a>
{/carefree:nav}
</nav>`}}</code></pre>
            </el-card>
          </div>

          <!-- 面包屑导航 -->
          <div v-show="activeSection === 'breadcrumb'" class="tag-section">
            <h3>carefree:breadcrumb - 面包屑导航</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于生成当前页面的面包屑导航。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'separator', required: '否', default: ' > ', description: '分隔符'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：面包屑导航</div>
              <pre><code>{{`<nav class="breadcrumb">
{carefree:breadcrumb separator=' > ' id='item'}
    <a href="{$item.url}">{$item.title}</a>
    {if condition="!$item.last"} > {/if}
{/carefree:breadcrumb}
</nav>`}}</code></pre>
            </el-card>
          </div>

          <!-- 幻灯片 -->
          <div v-show="activeSection === 'slider'" class="tag-section">
            <h3>carefree:slider - 获取幻灯片</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取幻灯片数据，支持按分组筛选。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="sliderParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：首页轮播图</div>
              <pre><code>{{`<div class="swiper-container">
    <div class="swiper-wrapper">
    {carefree:slider group='home' limit='5' id='slider'}
        <div class="swiper-slide">
            <a href="{$slider.link_url}">
                <img src="{$slider.image_url}" alt="{$slider.title}">
                <div class="caption">
                    <h3>{$slider.title}</h3>
                    <p>{$slider.description}</p>
                </div>
            </a>
        </div>
    {/carefree:slider}
    </div>
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 广告 -->
          <div v-show="activeSection === 'ad'" class="tag-section">
            <h3>carefree:ad - 获取广告</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取广告数据，支持按广告位筛选。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="adParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：顶部广告</div>
              <pre><code>{{`<div class="top-ads">
{carefree:ad position='top' limit='3' id='ad'}
    <div class="ad-item">
        <a href="{$ad.link_url}" target="_blank">
            <img src="{$ad.image_url}" alt="{$ad.title}">
        </a>
    </div>
{/carefree:ad}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 评论列表 -->
          <div v-show="activeSection === 'comment'" class="tag-section">
            <h3>carefree:comment - 获取评论列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取评论数据，支持获取最新评论或热门评论。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'limit', required: '否', default: '10', description: '返回数量'},
              {name: 'aid', required: '否', default: '0', description: '文章ID，0表示所有文章'},
              {name: 'type', required: '否', default: 'latest', description: '类型：latest最新，hot热门'},
              {name: 'empty', required: '否', default: '-', description: '空数据时显示的内容'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：最新评论</div>
              <pre><code>{{`{carefree:comment limit='10' type='latest' id='comment'}
<div class="comment-item">
    <div class="comment-author">{$comment.display_name}</div>
    <div class="comment-content">{$comment.short_content}</div>
    <div class="comment-time">{$comment.formatted_time}</div>
</div>
{/carefree:comment}`}}</code></pre>
            </el-card>
          </div>

          <!-- 搜索框 -->
          <div v-show="activeSection === 'search'" class="tag-section">
            <h3>carefree:search - 生成搜索框</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于生成网站搜索表单。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'action', required: '否', default: '/search', description: '搜索表单提交地址'},
              {name: 'placeholder', required: '否', default: '请输入关键词...', description: '输入框占位文本'},
              {name: 'button', required: '否', default: '搜索', description: '按钮文本'},
              {name: 'class', required: '否', default: 'search-form', description: '表单CSS类名'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：搜索框</div>
              <pre><code>{{`{carefree:search action='/search' placeholder='搜索文章...' button='搜索' class='header-search' /}`}}</code></pre>
            </el-card>
          </div>

          <!-- 社交分享 -->
          <div v-show="activeSection === 'share'" class="tag-section">
            <h3>carefree:share - 社交分享按钮</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于生成社交分享按钮。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'platforms', required: '否', default: 'wechat,weibo,qq,twitter,facebook', description: '分享平台，逗号分隔'},
              {name: 'size', required: '否', default: 'normal', description: '按钮大小：small, normal, large'},
              {name: 'style', required: '否', default: 'icon', description: '显示样式：icon图标，text文本'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：分享按钮</div>
              <pre><code>{{`{carefree:share platforms='wechat,weibo,qq' size='normal' style='icon' /}`}}</code></pre>
            </el-card>
          </div>

          <!-- SEO标签 -->
          <div v-show="activeSection === 'seo'" class="tag-section">
            <h3>carefree:seo - SEO元标签</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>自动生成完整的SEO meta标签，包括基础meta、Open Graph和Twitter Card。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'title', required: '否', default: '-', description: 'SEO标题'},
              {name: 'keywords', required: '否', default: '-', description: 'SEO关键词'},
              {name: 'description', required: '否', default: '-', description: 'SEO描述'},
              {name: 'image', required: '否', default: '-', description: 'SEO图片'},
              {name: 'type', required: '否', default: 'website', description: '页面类型：website, article'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：文章页SEO</div>
              <pre><code>{{`<head>
    <title>{$article.seo_title}</title>
    {carefree:seo
        title='$article.seo_title'
        keywords='$article.seo_keywords'
        description='$article.seo_description'
        image='$article.cover_image'
        type='article' /}
</head>`}}</code></pre>
            </el-card>
          </div>

          <!-- 用户信息 -->
          <div v-show="activeSection === 'userinfo'" class="tag-section">
            <h3>carefree:userinfo - 获取用户信息</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取指定用户的详细信息。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'uid', required: '是', default: '-', description: '用户ID'},
              {name: 'id', required: '否', default: 'user', description: '变量名'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：显示用户信息</div>
              <pre><code>{{`{carefree:userinfo uid='$article.user_id' id='author'}
<div class="author-card">
    <img src="{$author.avatar}" alt="{$author.display_name}">
    <div class="author-name">{$author.display_name}</div>
    <div class="author-stats">
        文章: {$author.article_count} | 浏览: {$author.total_views}
    </div>
</div>
{/carefree:userinfo}`}}</code></pre>
            </el-card>
          </div>

          <!-- 作者列表 -->
          <div v-show="activeSection === 'author'" class="tag-section">
            <h3>carefree:author - 获取作者列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取网站作者列表，按发文数或浏览量排序。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'limit', required: '否', default: '10', description: '返回数量'},
              {name: 'orderby', required: '否', default: 'article', description: '排序：article发文数，view浏览量，like点赞数'},
              {name: 'empty', required: '否', default: '-', description: '空数据时显示的内容'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：热门作者</div>
              <pre><code>{{`{carefree:author limit='10' orderby='article' id='author'}
<div class="author-item">
    <img src="{$author.avatar}" alt="{$author.display_name}">
    <div class="author-name">{$author.display_name}</div>
    <div class="author-stats">
        {$author.article_count} 篇文章 • {$author.total_views} 阅读
    </div>
</div>
{/carefree:author}`}}</code></pre>
            </el-card>
          </div>

          <!-- 前台用户 -->
          <div v-show="activeSection === 'frontuser'" class="tag-section">
            <h3>carefree:frontuser - 获取前台用户</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取前台用户列表，支持按会员等级、VIP状态等筛选。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'limit', required: '否', default: '10', description: '返回数量'},
              {name: 'level', required: '否', default: '-', description: '会员等级'},
              {name: 'isvip', required: '否', default: '-', description: 'VIP状态：1是，0否'},
              {name: 'status', required: '否', default: '-', description: '用户状态：1正常，0禁用'},
              {name: 'orderby', required: '否', default: 'points', description: '排序：points积分，create_time注册时间，login_time登录时间'},
              {name: 'empty', required: '否', default: '-', description: '空数据时显示的内容'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：积分排行榜</div>
              <pre><code>{{`{carefree:frontuser limit='10' orderby='points' status='1' id='user'}
<div class="user-item">
    <img src="{$user.avatar}" alt="{$user.nickname}">
    <div class="user-name">{$user.nickname}</div>
    <div class="user-level">{$user.level_name}</div>
    <div class="user-points">积分: {$user.points}</div>
</div>
{/carefree:frontuser}`}}</code></pre>
            </el-card>
          </div>

          <!-- 会员等级 -->
          <div v-show="activeSection === 'memberlevel'" class="tag-section">
            <h3>carefree:memberlevel - 获取会员等级</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取会员等级列表。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'limit', required: '否', default: '0', description: '返回数量，0表示不限制'},
              {name: 'empty', required: '否', default: '-', description: '空数据时显示的内容'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：会员等级展示</div>
              <pre><code>{{`{carefree:memberlevel limit='10' id='level'}
<div class="level-item">
    <div class="level-name">{$level.name}</div>
    <div class="level-info">升级条件: {$level.upgrade_points} 积分</div>
    <div class="level-benefits">{$level.description}</div>
</div>
{/carefree:memberlevel}`}}</code></pre>
            </el-card>
          </div>

          <!-- 投稿列表 -->
          <div v-show="activeSection === 'contribution'" class="tag-section">
            <h3>carefree:contribution - 获取投稿列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取用户投稿列表，支持按状态筛选。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'limit', required: '否', default: '10', description: '返回数量'},
              {name: 'status', required: '否', default: '-', description: '状态：0待审核，1已通过，2已拒绝'},
              {name: 'userid', required: '否', default: '-', description: '用户ID'},
              {name: 'orderby', required: '否', default: 'create_time', description: '排序：create_time创建时间，update_time更新时间'},
              {name: 'empty', required: '否', default: '-', description: '空数据时显示的内容'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：我的投稿</div>
              <pre><code>{{`{carefree:contribution userid='$user.id' limit='10' id='contrib'}
<div class="contrib-item">
    <h4>{$contrib.title}</h4>
    <div class="contrib-author">{$contrib.author}</div>
    <div class="contrib-status">{$contrib.status_text}</div>
    <div class="contrib-time">{$contrib.create_time}</div>
</div>
{/carefree:contribution}`}}</code></pre>
            </el-card>
          </div>

          <!-- 友情链接 -->
          <div v-show="activeSection === 'link'" class="tag-section">
            <h3>carefree:link - 获取友情链接</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取友情链接数据，支持按分组筛选。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="linkParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：底部友情链接</div>
              <pre><code>{{`<div class="friend-links">
    <h4>友情链接</h4>
    {carefree:link group='home' limit='20' id='link'}
    <a href="{$link.url}" target="_blank" rel="nofollow">{$link.name}</a>
    {/carefree:link}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 消息通知 -->
          <div v-show="activeSection === 'notification'" class="tag-section">
            <h3>carefree:notification - 获取消息通知</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取用户消息通知列表。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'limit', required: '否', default: '10', description: '返回数量'},
              {name: 'userid', required: '否', default: '-', description: '用户ID'},
              {name: 'type', required: '否', default: '-', description: '类型：system系统，reply回复，like点赞，follow关注'},
              {name: 'isread', required: '否', default: '-', description: '是否已读：0未读，1已读'},
              {name: 'empty', required: '否', default: '-', description: '空数据时显示的内容'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：未读通知</div>
              <pre><code>{{`{carefree:notification userid='$user.id' isread='0' limit='10' id='notice'}
<div class="notice-item">
    <div class="notice-title">{$notice.title}</div>
    <div class="notice-content">{$notice.content}</div>
    <div class="notice-time">{$notice.create_time}</div>
</div>
{/carefree:notification}`}}</code></pre>
            </el-card>
          </div>

          <!-- 归档列表 -->
          <div v-show="activeSection === 'archive'" class="tag-section">
            <h3>carefree:archive - 获取归档列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取文章归档列表，支持按年、月、日归档。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'limit', required: '否', default: '12', description: '返回数量'},
              {name: 'type', required: '否', default: 'month', description: '归档类型：year年，month月，day日'},
              {name: 'format', required: '否', default: 'Y年m月', description: '日期格式'},
              {name: 'empty', required: '否', default: '-', description: '空数据时显示的内容'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：按月归档</div>
              <pre><code>{{`{carefree:archive type='month' limit='12' format='Y年m月' id='archive'}
<div class="archive-item">
    <a href="{$archive.url}">
        {$archive.display_date} ({$archive.article_count})
    </a>
</div>
{/carefree:archive}`}}</code></pre>
            </el-card>
          </div>

          <!-- 标签云 -->
          <div v-show="activeSection === 'tagcloud'" class="tag-section">
            <h3>carefree:tagcloud - 生成标签云</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于生成标签云，标签大小根据使用频率自动调整。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'limit', required: '否', default: '30', description: '标签数量'},
              {name: 'orderby', required: '否', default: 'count', description: '排序：count使用次数，name名称，random随机'},
              {name: 'minsize', required: '否', default: '12', description: '最小字号(px)'},
              {name: 'maxsize', required: '否', default: '28', description: '最大字号(px)'},
              {name: 'style', required: '否', default: 'html', description: '输出方式：html直接输出，data输出数组'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：标签云</div>
              <pre><code>{{`<div class="tag-cloud-box">
    <h3>标签云</h3>
    {carefree:tagcloud limit='30' orderby='count' minsize='12' maxsize='28' style='html' /}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 分页 -->
          <div v-show="activeSection === 'pagelist'" class="tag-section">
            <h3>carefree:pagelist - 生成分页</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于生成分页导航。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'total', required: '是', default: '-', description: '总记录数'},
              {name: 'pagesize', required: '是', default: '-', description: '每页显示数量'},
              {name: 'currentpage', required: '是', default: '-', description: '当前页码'},
              {name: 'url', required: '是', default: '-', description: 'URL模板，{page}为页码占位符'},
              {name: 'style', required: '否', default: 'full', description: '样式：full完整，simple简单'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：分页导航</div>
              <pre><code>{{`{carefree:pagelist
    total='$total'
    pagesize='$pagesize'
    currentpage='$current_page'
    url='/articles/page-{page}.html'
    style='full' /}`}}</code></pre>
            </el-card>
          </div>

          <!-- 排行榜 -->
          <div v-show="activeSection === 'rank'" class="tag-section">
            <h3>carefree:rank - 排行榜</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取文章排行榜数据，支持按浏览量、评论数、点赞数等多种方式排序。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'type', required: '否', default: 'view', description: '排行类型：view-浏览量, comment-评论数, like-点赞数, collect-收藏数'},
              {name: 'limit', required: '否', default: '10', description: '数量限制'},
              {name: 'catid', required: '否', default: '0', description: '分类ID，0表示全部'},
              {name: 'days', required: '否', default: '0', description: '最近N天的数据，0表示所有时间'},
              {name: 'id', required: '否', default: 'item', description: '循环变量名'},
              {name: 'empty', required: '否', default: '', description: '无数据时的提示'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例 1：浏览量排行榜</div>
              <pre><code>{{`<div class="rank-list">
    <h3>热门文章 TOP 10</h3>
    <ol>
    {carefree:rank type='view' limit='10' id='item'}
        <li>
            <a href="/article/{$item.id}.html">{$item.title}</a>
            <span class="count">{$item.view_count} 次浏览</span>
        </li>
    {/carefree:rank}
    </ol>
</div>`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 2：本周热门排行</div>
              <pre><code>{{`{carefree:rank type='view' limit='5' days='7' id='article'}
<div class="hot-article">
    <span class="rank">{$i}</span>
    <a href="/article/{$article.id}.html">{$article.title}</a>
</div>
{/carefree:rank}`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 3：评论最多的文章</div>
              <pre><code>{{`{carefree:rank type='comment' limit='10' catid='1' id='item'}
<div class="comment-rank-item">
    <h4>{$item.title}</h4>
    <p>{$item.comment_count} 条评论</p>
</div>
{/carefree:rank}`}}</code></pre>
            </el-card>
          </div>

          <!-- 热门关键词 -->
          <div v-show="activeSection === 'hotwords'" class="tag-section">
            <h3>carefree:hotwords - 热门关键词</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取热门搜索关键词或标签，支持按使用频率排序，可用于制作标签云效果。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'limit', required: '否', default: '20', description: '返回数量'},
              {name: 'days', required: '否', default: '30', description: '统计最近N天的数据'},
              {name: 'orderby', required: '否', default: 'count', description: '排序方式：count-使用次数, random-随机'},
              {name: 'id', required: '否', default: 'word', description: '循环变量名'},
              {name: 'empty', required: '否', default: '', description: '无数据时的提示'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：标签云样式</div>
              <pre><code>{{`<div class="tag-cloud">
    {carefree:hotwords limit='30' days='30' id='word'}
    <a href="{$word.url}" class="tag-level-{$word.level}">
        {$word.keyword}
    </a>
    {/carefree:hotwords}
</div>

<!-- CSS 样式 -->
<style>
.tag-level-1 { font-size: 12px; }
.tag-level-2 { font-size: 14px; }
.tag-level-3 { font-size: 16px; }
.tag-level-4 { font-size: 18px; }
.tag-level-5 { font-size: 20px; font-weight: bold; }
</style>`}}</code></pre>
            </el-card>
          </div>

          <!-- 随机图片 -->
          <div v-show="activeSection === 'randomimg'" class="tag-section">
            <h3>carefree:randomimg - 随机图片</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于从不同来源随机获取图片，可用于图片墙、背景图等场景。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'limit', required: '否', default: '5', description: '返回数量'},
              {name: 'source', required: '否', default: 'article', description: '图片来源：article-文章封面, media-媒体库, slider-幻灯片'},
              {name: 'id', required: '否', default: 'img', description: '循环变量名'},
              {name: 'empty', required: '否', default: '', description: '无数据时的提示'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例 1：图片墙</div>
              <pre><code>{{`<div class="photo-wall">
    {carefree:randomimg limit='12' source='article' id='img'}
    <div class="photo-item">
        <a href="{$img.link}">
            <img src="{$img.url}" alt="{$img.title}">
        </a>
    </div>
    {/carefree:randomimg}
</div>`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 2：随机背景图</div>
              <pre><code>{{`{carefree:randomimg limit='1' source='slider' id='bg'}
<div class="hero-banner" style="background-image: url({$bg.url})">
    <h1>欢迎来到我的网站</h1>
</div>
{/carefree:randomimg}`}}</code></pre>
            </el-card>
          </div>

          <!-- 内容区块 -->
          <div v-show="activeSection === 'position'" class="tag-section">
            <h3>carefree:position - 内容区块</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于在指定位置显示自定义内容块，适合制作侧边栏、广告位等固定内容区域。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'name', required: '是', default: '-', description: '位置名称'},
              {name: 'id', required: '否', default: 'block', description: '循环变量名'},
              {name: 'empty', required: '否', default: '', description: '无数据时的提示'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：侧边栏区块</div>
              <pre><code>{{`<aside class="sidebar">
    {carefree:position name='sidebar' id='block'}
    <div class="widget">
        <h3 class="widget-title">{$block.title}</h3>
        <div class="widget-content">
            {$block.content|raw}
        </div>
    </div>
    {/carefree:position}
</aside>`}}</code></pre>
            </el-card>

            <el-alert type="warning" :closable="false" show-icon style="margin-top: 15px;">
              <template #title>
                <div>需要在系统配置中预先定义位置和内容块数据。</div>
              </template>
            </el-alert>
          </div>

          <!-- 通用循环 -->
          <div v-show="activeSection === 'loop'" class="tag-section">
            <h3>carefree:loop - 通用循环</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于遍历任意数组变量，提供灵活的循环功能，适合处理自定义数据结构。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'data', required: '是', default: '-', description: '要循环的数组变量'},
              {name: 'id', required: '否', default: 'item', description: '循环项变量名'},
              {name: 'key', required: '否', default: 'key', description: '索引变量名'},
              {name: 'empty', required: '否', default: '', description: '无数据时的提示'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：遍历自定义数组</div>
              <pre><code>{{`<?php
// 控制器中准备数据
$categories = [
    ['id' => 1, 'name' => '技术'],
    ['id' => 2, 'name' => '生活'],
    ['id' => 3, 'name' => '摄影']
];
$this->assign('categories', $categories);
?>

<!-- 模板中使用 -->
<ul class="category-list">
{carefree:loop data='$categories' id='cat' key='index'}
    <li>
        <span class="no">{$index + 1}</span>
        <a href="/category/{$cat.id}">{$cat.name}</a>
    </li>
{/carefree:loop}
</ul>`}}</code></pre>
            </el-card>
          </div>

          <!-- SQL查询 -->
          <div v-show="activeSection === 'sql'" class="tag-section">
            <h3>carefree:sql - SQL查询</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于执行自定义SQL查询，适合高级用户实现复杂的数据需求。</p>

            <el-alert type="error" :closable="false" show-icon style="margin-bottom: 15px;">
              <template #title>
                <div><strong>安全警告</strong>：此标签仅支持SELECT查询，禁止执行增删改等危险操作。请谨慎使用！</div>
              </template>
            </el-alert>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'sql', required: '是', default: '-', description: 'SQL查询语句（仅支持SELECT）'},
              {name: 'id', required: '否', default: 'row', description: '循环变量名'},
              {name: 'empty', required: '否', default: '', description: '无数据时的提示'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：自定义查询</div>
              <pre><code>{{`{carefree:sql sql="SELECT a.*, c.name as category_name
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.id
                WHERE a.status = 1 AND a.view_count > 1000
                ORDER BY a.create_time DESC
                LIMIT 10" id='row'}
<div class="article-item">
    <h3>{$row.title}</h3>
    <p>分类：{$row.category_name} | 浏览：{$row.view_count}</p>
</div>
{/carefree:sql}`}}</code></pre>
            </el-card>

            <el-alert type="info" :closable="false" show-icon style="margin-top: 15px;">
              <template #title>
                <div>建议：优先使用专用标签（如 article、category 等），仅在确实需要复杂查询时使用 SQL 标签。</div>
              </template>
            </el-alert>
          </div>

          <!-- 通用说明 -->
          <div v-show="activeSection === 'common'" class="tag-section">
            <h3>通用参数和用法</h3>

            <el-divider content-position="left">循环变量命名</el-divider>
            <el-alert type="info" :closable="false" show-icon>
              <template #title>
                <div class="alert-content">
                  <p><strong>id 参数</strong>：用于指定循环中的变量名，在标签体内可以通过 <code>$变量名</code> 访问当前项的数据。</p>
                  <p><strong>示例</strong>：<code>id='article'</code> 则在循环体内使用 <code>$article.title</code> 访问标题。</p>
                </div>
              </template>
            </el-alert>

            <el-divider content-position="left">空数据处理</el-divider>
            <el-alert type="info" :closable="false" show-icon>
              <template #title>
                <div class="alert-content">
                  <p><strong>empty 参数</strong>：当查询结果为空时显示的内容，可以是 HTML 代码。</p>
                  <p><strong>示例</strong>：<code>empty='&lt;p class="no-data"&gt;暂无数据&lt;/p&gt;'</code></p>
                  <p><strong>注意</strong>：如果不想显示任何内容，使用 <code>empty=''</code></p>
                </div>
              </template>
            </el-alert>

            <el-divider content-position="left">数量限制</el-divider>
            <el-alert type="info" :closable="false" show-icon>
              <template #title>
                <div class="alert-content">
                  <p><strong>limit 参数</strong>：限制返回的数据条数。</p>
                  <p><strong>示例</strong>：<code>limit='10'</code> 返回10条数据</p>
                  <p><strong>特殊值</strong>：<code>limit='0'</code> 表示不限制数量，返回所有数据</p>
                </div>
              </template>
            </el-alert>

            <el-divider content-position="left">变量访问</el-divider>
            <el-card class="code-card">
              <div class="code-header">在标签体内访问当前项数据</div>
              <pre><code>{{`{carefree:article limit='5' id='article'}
    <!-- 通过 $article 访问当前文章数据 -->
    <h3>{$article.title}</h3>           <!-- 标题 -->
    <p>{$article.summary}</p>           <!-- 摘要 -->
    <span>{$article.view_count}</span>  <!-- 浏览量 -->

    <!-- 访问关联数据 -->
    <span>{$article.category.name}</span>    <!-- 分类名称 -->
    <span>{$article.user.username}</span>    <!-- 作者用户名 -->

    <!-- 使用模板函数 -->
    <time>{$article.create_time|date='Y-m-d H:i'}</time>
{/carefree:article}`}}</code></pre>
            </el-card>

            <el-divider content-position="left">条件判断</el-divider>
            <el-card class="code-card">
              <div class="code-header">在标签体内使用条件判断</div>
              <pre><code>{{`{carefree:article limit='5' id='article'}
<div class="article">
    {if condition="$article.cover_image"}
    <img src="{$article.cover_image}" alt="{$article.title}">
    {else/}
    <img src="/assets/images/default.jpg" alt="默认图片">
    {/if}

    <h3>{$article.title}</h3>

    {if condition="$article.tags"}
    <div class="tags">
        {volist name="article.tags" id="tag"}
        <a href="/tag/{$tag.id}.html">{$tag.name}</a>
        {/volist}
    </div>
    {/if}
</div>
{/carefree:article}`}}</code></pre>
            </el-card>

            <el-divider content-position="left">最佳实践</el-divider>
            <el-alert type="success" :closable="false" show-icon>
              <template #title>
                <div class="alert-content">
                  <ol>
                    <li><strong>合理设置 limit</strong>：避免一次性查询过多数据影响性能</li>
                    <li><strong>使用 empty 参数</strong>：提供良好的空数据展示</li>
                    <li><strong>变量命名清晰</strong>：使用有意义的 id 参数值，如 article、category 等</li>
                    <li><strong>注意变量作用域</strong>：嵌套使用时确保变量名不冲突</li>
                    <li><strong>善用筛选条件</strong>：利用各种参数精确获取需要的数据</li>
                    <li><strong>配合条件判断</strong>：处理可能为空的字段，如封面图、描述等</li>
                  </ol>
                </div>
              </template>
            </el-alert>
          </div>
        </el-main>
      </el-container>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import {
  Document,
  Setting,
  Tools,
  DataLine,
  Reading,
  Tickets,
  Folder,
  FolderOpened,
  PriceTag,
  CollectionTag,
  Collection,
  Memo,
  Notebook,
  Connection,
  Menu,
  Operation,
  Position,
  Picture,
  PictureFilled,
  Promotion,
  ChatDotRound,
  ChatLineRound,
  Search,
  Share,
  Compass,
  User,
  Avatar,
  UserFilled,
  Medal,
  Edit,
  Grid,
  Link,
  Bell,
  Calendar,
  Sunny,
  DCaret,
  QuestionFilled
} from '@element-plus/icons-vue'

const activeSection = ref('config')

const handleMenuSelect = (key) => {
  activeSection.value = key
}

// 文章标签参数
const articleParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'limit', required: '否', default: '10', description: '返回数量，0表示不限制' },
  { name: 'typeid', required: '否', default: '-', description: '分类ID，支持多个用逗号分隔' },
  { name: 'tagid', required: '否', default: '-', description: '标签ID' },
  { name: 'flag', required: '否', default: '-', description: '文章属性：recommend推荐, hot热门, top置顶' },
  { name: 'hascover', required: '否', default: '-', description: '是否有封面：1有，0没有' },
  { name: 'order', required: '否', default: 'create_time desc', description: '排序方式' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 分类标签参数
const categoryParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'parent', required: '否', default: '0', description: '父级ID，0表示顶级分类' },
  { name: 'limit', required: '否', default: '10', description: '返回数量，0表示不限制' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 标签标签参数
const tagParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'limit', required: '否', default: '20', description: '返回数量' },
  { name: 'order', required: '否', default: 'article_count desc', description: '排序方式' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 单页标签参数
const pageParams = [
  { name: 'name', required: '是', default: '-', description: '变量名（查询单个）或循环变量名（查询列表）' },
  { name: 'id', required: '否', default: '-', description: '单页ID（查询指定单页时使用）' },
  { name: 'alias', required: '否', default: '-', description: '单页别名（查询指定单页时使用）' },
  { name: 'limit', required: '否', default: '10', description: '返回数量（查询列表时使用）' }
]

// 相关文章标签参数
const relatedParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'aid', required: '是', default: '-', description: '当前文章ID，通常使用 $article.id' },
  { name: 'limit', required: '否', default: '5', description: '返回数量' },
  { name: 'type', required: '否', default: 'auto', description: '匹配方式：auto自动, tag标签, category分类' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 幻灯片标签参数
const sliderParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'group', required: '否', default: 'home', description: '分组名称' },
  { name: 'limit', required: '否', default: '10', description: '返回数量' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 广告标签参数
const adParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'position', required: '否', default: '-', description: '广告位：top顶部, side侧边, bottom底部' },
  { name: 'limit', required: '否', default: '5', description: '返回数量' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 友情链接标签参数
const linkParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'group', required: '否', default: 'home', description: '分组名称' },
  { name: 'limit', required: '否', default: '20', description: '返回数量' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]
</script>

<style scoped>
.tag-guide-container {
  padding: 20px;
}

.header-card {
  margin-bottom: 20px;
}

.card-header h2 {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
  font-size: 24px;
  color: #303133;
}

.content-card {
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.guide-container {
  min-height: 600px;
}

.menu-aside {
  background-color: #f5f7fa;
  border-right: 1px solid #e4e7ed;
  overflow-y: auto;
}

.guide-menu {
  border: none;
  background-color: transparent;
}

.guide-menu .el-sub-menu__title,
.guide-menu .el-menu-item {
  height: 48px;
  line-height: 48px;
}

.guide-menu .el-menu-item {
  padding-left: 50px !important;
}

.content-main {
  background-color: #fff;
  overflow-y: auto;
  max-height: calc(100vh - 250px);
}

.tag-section {
  animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.tag-section h3 {
  color: #409EFF;
  font-size: 20px;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 2px solid #409EFF;
}

.tag-section p {
  line-height: 1.8;
  color: #606266;
  margin-bottom: 15px;
}

.code-card {
  margin-bottom: 20px;
  border-left: 4px solid #409EFF;
}

.code-header {
  font-weight: bold;
  color: #303133;
  margin-bottom: 10px;
  padding: 5px 0;
  border-bottom: 1px solid #EBEEF5;
}

.code-card pre {
  background-color: #f5f7fa;
  padding: 15px;
  border-radius: 4px;
  overflow-x: auto;
  margin: 0;
}

.code-card code {
  font-family: 'Courier New', Courier, monospace;
  font-size: 13px;
  line-height: 1.6;
  color: #303133;
  white-space: pre;
}

.config-tag {
  margin: 5px;
}

.alert-content {
  line-height: 1.8;
}

.alert-content p {
  margin: 8px 0;
}

.alert-content code {
  background-color: #f5f7fa;
  padding: 2px 6px;
  border-radius: 3px;
  color: #e74c3c;
  font-family: 'Courier New', Courier, monospace;
}

.alert-content ul {
  margin: 10px 0;
  padding-left: 20px;
}

.alert-content ol {
  margin: 10px 0;
  padding-left: 20px;
}

.alert-content li {
  margin: 5px 0;
}

:deep(.el-table) {
  margin-bottom: 20px;
}

:deep(.el-divider__text) {
  font-weight: bold;
  color: #409EFF;
}

:deep(.el-alert) {
  margin-bottom: 20px;
}

:deep(.el-sub-menu__title) {
  font-weight: 500;
}

:deep(.el-menu-item.is-active) {
  background-color: #ecf5ff !important;
  color: #409EFF;
  font-weight: 500;
}
</style>
