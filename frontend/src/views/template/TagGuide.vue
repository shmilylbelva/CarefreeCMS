<template>
  <div class="tag-guide-container">
    <el-card class="header-card">
      <template #header>
        <div class="card-header">
          <h2>
            <el-icon><Document /></el-icon>
            Carefree 模板标签使用教程 v2.0.0
          </h2>
          <el-button-group>
            <el-button type="primary" link @click="openCompleteGuide">
              <el-icon><Reading /></el-icon> 查看完整文档
            </el-button>
            <el-button type="success" link @click="openQuickReference">
              <el-icon><Tickets /></el-icon> 快速参考手册
            </el-button>
          </el-button-group>
        </div>
      </template>
      <el-alert
        title="关于 Carefree 模板标签"
        type="info"
        :closable="false"
        show-icon
      >
        <template #default>
          <p><strong>Carefree 模板标签库 v2.0.0</strong> - 本 CMS 系统提供的自定义模板标签库，用于在模板中快速调用系统数据。</p>
          <p>✅ 所有标签支持服务端渲染，有利于 SEO 优化</p>
          <p>🎯 <strong>目前共提供 53 个功能标签</strong>，包含：</p>
          <ul style="margin: 10px 0; padding-left: 20px;">
            <li>基础内容标签（文章、分类、标签等）</li>
            <li><strong>⭐ 媒体管理</strong>（相册、视频、音频、下载）</li>
            <li><strong>⭐ 互动功能</strong>（投票、测验、抽奖）</li>
            <li><strong>⭐ 实用工具</strong>（二维码、日历、地图、天气）</li>
            <li><strong>⭐ AI推荐</strong>（智能推荐、个性化内容）</li>
            <li><strong>⭐ 表单验证</strong>（动态表单、验证码）</li>
            <li><strong>⭐ 高级功能</strong>（多语言、缓存、条件判断）</li>
          </ul>
          <el-tag type="success" size="small">v2.0新增16个标签</el-tag>
        </template>
      </el-alert>
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
              <el-menu-item index="gallery">
                <el-icon><Picture /></el-icon>
                <span>相册图库 ⭐</span>
              </el-menu-item>
              <el-menu-item index="video">
                <el-icon><VideoPlay /></el-icon>
                <span>视频列表 ⭐</span>
              </el-menu-item>
              <el-menu-item index="audio">
                <el-icon><Headset /></el-icon>
                <span>音频列表 ⭐</span>
              </el-menu-item>
              <el-menu-item index="download">
                <el-icon><Download /></el-icon>
                <span>文件下载 ⭐</span>
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
              <el-menu-item index="vote">
                <el-icon><Checked /></el-icon>
                <span>投票系统 ⭐</span>
              </el-menu-item>
              <el-menu-item index="quiz">
                <el-icon><QuestionFilled /></el-icon>
                <span>在线测验 ⭐</span>
              </el-menu-item>
              <el-menu-item index="lottery">
                <el-icon><Present /></el-icon>
                <span>抽奖活动 ⭐</span>
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
              <el-menu-item index="oauth">
                <el-icon><Connection /></el-icon>
                <span>第三方登录</span>
              </el-menu-item>
            </el-sub-menu>

            <!-- 实用工具标签 ⭐新增 -->
            <el-sub-menu index="utility">
              <template #title>
                <el-icon><Tools /></el-icon>
                <span>实用工具 ⭐</span>
              </template>
              <el-menu-item index="qrcode">
                <el-icon><Stamp /></el-icon>
                <span>二维码生成</span>
              </el-menu-item>
              <el-menu-item index="calendar">
                <el-icon><Calendar /></el-icon>
                <span>事件日历</span>
              </el-menu-item>
              <el-menu-item index="sitemap">
                <el-icon><Menu /></el-icon>
                <span>站点地图</span>
              </el-menu-item>
              <el-menu-item index="weather">
                <el-icon><Sunny /></el-icon>
                <span>天气预报</span>
              </el-menu-item>
            </el-sub-menu>

            <!-- AI推荐标签 ⭐新增 -->
            <el-sub-menu index="ai">
              <template #title>
                <el-icon><MagicStick /></el-icon>
                <span>AI推荐 ⭐</span>
              </template>
              <el-menu-item index="recommend">
                <el-icon><Star /></el-icon>
                <span>智能推荐</span>
              </el-menu-item>
              <el-menu-item index="personalize">
                <el-icon><User /></el-icon>
                <span>个性化内容</span>
              </el-menu-item>
            </el-sub-menu>

            <!-- 表单验证标签 ⭐新增 -->
            <el-sub-menu index="form">
              <template #title>
                <el-icon><Edit /></el-icon>
                <span>表单验证 ⭐</span>
              </template>
              <el-menu-item index="form">
                <el-icon><Document /></el-icon>
                <span>通用表单</span>
              </el-menu-item>
              <el-menu-item index="formfield">
                <el-icon><Edit /></el-icon>
                <span>表单字段</span>
              </el-menu-item>
              <el-menu-item index="captcha">
                <el-icon><PictureFilled /></el-icon>
                <span>验证码</span>
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
              <el-menu-item index="multilang">
                <el-icon><ChatDotRound /></el-icon>
                <span>多语言 ⭐</span>
              </el-menu-item>
              <el-menu-item index="cache">
                <el-icon><Timer /></el-icon>
                <span>缓存标签 ⭐</span>
              </el-menu-item>
              <el-menu-item index="condition">
                <el-icon><Select /></el-icon>
                <span>条件标签 ⭐</span>
              </el-menu-item>
              <el-menu-item index="group">
                <el-icon><Grid /></el-icon>
                <span>分组标签 ⭐</span>
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

          <!-- OAuth第三方登录 -->
          <div v-show="activeSection === 'oauth'" class="tag-section">
            <h3>carefree:oauth - 第三方登录按钮</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于在登录页面显示第三方登录按钮（微信、QQ、微博、GitHub等），支持自定义样式。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="[
              {name: 'id', required: '是', default: '-', description: '循环变量名'},
              {name: 'platform', required: '否', default: '-', description: '指定平台：wechat, qq, weibo, github，不指定则显示所有启用的平台'},
              {name: 'empty', required: '否', default: '-', description: '无启用平台时显示的内容'}
            ]" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">可用字段</el-divider>
            <div style="margin-bottom: 15px;">
              <el-tag class="config-tag">platform</el-tag>
              <el-tag class="config-tag">platform_name</el-tag>
              <el-tag class="config-tag">auth_url</el-tag>
              <el-tag class="config-tag">sort_order</el-tag>
            </div>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例 1：显示所有第三方登录按钮</div>
              <pre><code>{{`<div class="oauth-login">
    <h3>第三方登录</h3>
    {carefree:oauth id='oauth'}
    <a href="{$oauth.auth_url}" class="oauth-btn oauth-{$oauth.platform}">
        <i class="icon-{$oauth.platform}"></i>
        {$oauth.platform_name}
    </a>
    {\carefree:oauth}
</div>`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 2：只显示微信登录</div>
              <pre><code>{{`{carefree:oauth platform='wechat' id='oauth'}
<a href="{$oauth.auth_url}" class="wechat-login-btn">
    <img src="/static/images/wechat-icon.png" alt="微信登录">
    使用微信登录
</a>
{\carefree:oauth}`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例 3：带图标的登录按钮</div>
              <pre><code>{{`<div class="social-login">
    <div class="divider">或使用以下方式登录</div>
    <div class="oauth-buttons">
        {carefree:oauth id='oauth'}
        <a href="{$oauth.auth_url}" class="social-btn" title="{$oauth.platform_name}">
            <img src="/static/images/{$oauth.platform}.svg" alt="{$oauth.platform_name}">
        </a>
        {\carefree:oauth}
    </div>
</div>

<style>
.oauth-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
}
.social-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s;
}
.social-btn:hover {
    transform: scale(1.1);
}
</style>`}}</code></pre>
            </el-card>

            <el-alert
              title="使用提示"
              type="warning"
              :closable="false"
              show-icon>
              <p>1. 第三方登录需要在后台"系统管理 → OAuth配置"中配置相应平台的AppID和AppSecret</p>
              <p>2. auth_url为授权登录地址，用户点击后会跳转到第三方平台进行授权</p>
              <p>3. 平台标识：wechat(微信)、qq(QQ)、weibo(微博)、github(GitHub)</p>
              <p>4. 建议配合CSS样式设计美观的登录按钮，提升用户体验</p>
            </el-alert>
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

          <!-- 相册图库 ⭐新增 -->
          <div v-show="activeSection === 'gallery'" class="tag-section">
            <h3>carefree:gallery - 相册图库</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取相册中的照片列表，支持按相册ID筛选、多列布局等。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="galleryParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：瀑布流相册</div>
              <pre><code>{{`<div class="photo-gallery">
    {carefree:gallery albumid='1' limit='20' columns='4' id='photo'}
    <div class="photo-item">
        <a href="{$photo.image_url}" data-lightbox="gallery">
            <img src="{$photo.thumb_url}" alt="{$photo.title}">
            <div class="photo-info">
                <h4>{$photo.title}</h4>
                <p>{$photo.description}</p>
            </div>
        </a>
    </div>
    {/carefree:gallery}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 视频列表 ⭐新增 -->
          <div v-show="activeSection === 'video'" class="tag-section">
            <h3>carefree:video - 视频列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取视频内容列表，支持按分类筛选、精选推荐等。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="videoParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：精选视频列表</div>
              <pre><code>{{`<div class="video-list">
    {carefree:video catid='5' featured='1' limit='8' id='video'}
    <div class="video-item">
        <div class="video-thumb">
            <img src="{$video.cover_image}" alt="{$video.title}">
            <span class="duration">{$video.duration}</span>
            <div class="play-btn"><i class="icon-play"></i></div>
        </div>
        <h4>{$video.title}</h4>
        <div class="video-meta">
            <span><i class="icon-view"></i> {$video.view_count}</span>
            <span><i class="icon-time"></i> {$video.publish_time|date='Y-m-d'}</span>
        </div>
    </div>
    {/carefree:video}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 音频列表 ⭐新增 -->
          <div v-show="activeSection === 'audio'" class="tag-section">
            <h3>carefree:audio - 音频列表</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取音频内容列表，支持播客、音乐等音频资源管理。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="audioParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：播客列表</div>
              <pre><code>{{`<div class="audio-list">
    {carefree:audio catid='3' limit='10' orderby='publish_time desc' id='audio'}
    <div class="audio-item">
        <div class="audio-cover">
            <img src="{$audio.cover_image}" alt="{$audio.title}">
        </div>
        <div class="audio-info">
            <h4>{$audio.title}</h4>
            <p>{$audio.description}</p>
            <div class="audio-meta">
                <span class="duration">{$audio.duration}</span>
                <span class="author">{$audio.author}</span>
            </div>
            <audio controls>
                <source src="{$audio.file_url}" type="audio/mpeg">
            </audio>
        </div>
    </div>
    {/carefree:audio}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 文件下载 ⭐新增 -->
          <div v-show="activeSection === 'download'" class="tag-section">
            <h3>carefree:download - 文件下载</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于获取可下载文件列表，支持按类型筛选、文件分类管理。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="downloadParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：软件下载列表</div>
              <pre><code>{{`<div class="download-list">
    {carefree:download type='software' limit='15' id='file'}
    <div class="download-item">
        <div class="file-icon">
            <i class="icon-{$file.file_type}"></i>
        </div>
        <div class="file-info">
            <h4>{$file.title}</h4>
            <p>{$file.description}</p>
            <div class="file-meta">
                <span class="size">{$file.file_size}</span>
                <span class="downloads">{$file.download_count} 次下载</span>
                <span class="version">v{$file.version}</span>
            </div>
        </div>
        <a href="{$file.download_url}" class="btn-download" download>
            <i class="icon-download"></i> 下载
        </a>
    </div>
    {/carefree:download}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 投票系统 ⭐新增 -->
          <div v-show="activeSection === 'vote'" class="tag-section">
            <h3>carefree:vote - 投票系统</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于显示投票/问卷调查，支持单选、多选，实时查看投票结果。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="voteParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：在线投票</div>
              <pre><code>{{`<div class="vote-container">
    {carefree:vote voteid='1' showresult='1' id='vote'}
    <h3>{$vote.title}</h3>
    <p>{$vote.description}</p>
    <form action="/api/vote/submit" method="post">
        <input type="hidden" name="vote_id" value="{$vote.id}">
        {volist name="vote.options" id="option"}
        <div class="vote-option">
            <input type="{$vote.type}" name="option[]" value="{$option.id}" id="opt_{$option.id}">
            <label for="opt_{$option.id}">
                {$option.title}
                {if condition="$vote.show_result"}
                <span class="percentage">({$option.percentage}%)</span>
                <div class="progress-bar" style="width: {$option.percentage}%"></div>
                {/if}
            </label>
        </div>
        {/volist}
        <button type="submit" class="btn-submit">提交投票</button>
        <p class="vote-stats">共 {$vote.total_votes} 人参与</p>
    </form>
    {/carefree:vote}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 在线测验 ⭐新增 -->
          <div v-show="activeSection === 'quiz'" class="tag-section">
            <h3>carefree:quiz - 在线测验</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于创建在线测验/考试，支持题目管理、自动评分。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="quizParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：知识测验</div>
              <pre><code>{{`<div class="quiz-container">
    {carefree:quiz quizid='1' id='quiz'}
    <div class="quiz-header">
        <h2>{$quiz.title}</h2>
        <p>{$quiz.description}</p>
        <div class="quiz-info">
            <span>题目数：{$quiz.question_count}</span>
            <span>总分：{$quiz.total_score}</span>
            <span>时限：{$quiz.time_limit}分钟</span>
        </div>
    </div>
    <form action="/api/quiz/submit" method="post" class="quiz-form">
        {volist name="quiz.questions" id="question" key="qnum"}
        <div class="question-item">
            <h4>第{$qnum}题：{$question.title} ({$question.score}分)</h4>
            {if condition="$question.type == 'choice'"}
            <div class="options">
                {volist name="question.options" id="opt"}
                <label>
                    <input type="radio" name="answer[{$question.id}]" value="{$opt.id}">
                    {$opt.content}
                </label>
                {/volist}
            </div>
            {elseif condition="$question.type == 'text'/}
            <textarea name="answer[{$question.id}]" rows="4"></textarea>
            {/if}
        </div>
        {/volist}
        <button type="submit" class="btn-submit">提交答案</button>
    </form>
    {/carefree:quiz}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 抽奖活动 ⭐新增 -->
          <div v-show="activeSection === 'lottery'" class="tag-section">
            <h3>carefree:lottery - 抽奖活动</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于创建抽奖活动，支持奖品管理、中奖概率设置、抽奖记录。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="lotteryParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：九宫格抽奖</div>
              <pre><code>{{`<div class="lottery-container">
    {carefree:lottery lotteryid='1' id='lottery'}
    <div class="lottery-header">
        <h2>{$lottery.title}</h2>
        <p>剩余抽奖次数：<span class="chance">{$lottery.user_chances}</span></p>
    </div>
    <div class="lottery-grid">
        {volist name="lottery.prizes" id="prize" key="i"}
        <div class="lottery-cell" data-prize-id="{$prize.id}">
            <img src="{$prize.image}" alt="{$prize.name}">
            <p>{$prize.name}</p>
        </div>
        {/volist}
    </div>
    <button class="btn-lottery" onclick="startLottery({$lottery.id})">
        开始抽奖
    </button>
    <div class="lottery-records">
        <h4>中奖记录</h4>
        <ul>
        {volist name="lottery.records" id="record"}
            <li>{$record.username} 抽中了 {$record.prize_name}</li>
        {/volist}
        </ul>
    </div>
    {/carefree:lottery}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 二维码生成 ⭐新增 -->
          <div v-show="activeSection === 'qrcode'" class="tag-section">
            <h3>carefree:qrcode - 二维码生成</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于生成二维码，支持自定义内容、尺寸、Logo、纠错级别。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="qrcodeParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：文章分享二维码</div>
              <pre><code>{{`<!-- 简单二维码 -->
<div class="qrcode">
    {carefree:qrcode content='https://www.example.com' size='200' /}
</div>

<!-- 带Logo的二维码 -->
<div class="qrcode-with-logo">
    {carefree:qrcode
        content='{$article.url}'
        size='300'
        logo='/assets/logo.png'
        level='H'
    /}
    <p>扫码查看文章</p>
</div>

<!-- 动态生成二维码 -->
<div class="share-qrcode">
    <h4>分享到微信</h4>
    {carefree:qrcode content='https://www.example.com/article/{$article.id}.html' size='250' /}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 日历事件 ⭐新增 -->
          <div v-show="activeSection === 'calendar'" class="tag-section">
            <h3>carefree:calendar - 日历事件</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于显示日历和事件，支持按年月筛选、事件标记。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="calendarParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：活动日历</div>
              <pre><code>{{`<div class="calendar-container">
    {carefree:calendar year='2025' month='1' events='1' id='calendar'}
    <div class="calendar-header">
        <h3>{$calendar.year}年{$calendar.month}月</h3>
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
                <td class="{$day.is_today ? 'today' : ''} {$day.has_event ? 'has-event' : ''}">
                    <span class="day-number">{$day.day}</span>
                    {if condition="$day.events"}
                    <div class="events">
                        {volist name="day.events" id="event"}
                        <span class="event-dot" title="{$event.title}"></span>
                        {/volist}
                    </div>
                    {/if}
                </td>
            {/volist}
            </tr>
        {/volist}
        </tbody>
    </table>
    {/carefree:calendar}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 站点地图 ⭐新增 -->
          <div v-show="activeSection === 'sitemap'" class="tag-section">
            <h3>carefree:sitemap - 站点地图</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于生成站点地图，支持HTML和XML格式，SEO优化。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="sitemapParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：HTML站点地图</div>
              <pre><code>{{`<div class="sitemap">
    <h1>网站地图</h1>
    {carefree:sitemap type='all' format='html' id='item'}
    <div class="sitemap-section">
        <h3>{$item.category}</h3>
        <ul>
        {volist name="item.links" id="link"}
            <li>
                <a href="{$link.url}" title="{$link.title}">
                    {$link.title}
                </a>
                <span class="update-time">{$link.update_time|date='Y-m-d'}</span>
            </li>
        {/volist}
        </ul>
    </div>
    {/carefree:sitemap}
</div>`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例：XML站点地图（用于SEO）</div>
              <pre><code>{{`<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
{carefree:sitemap type='all' format='xml' id='url'}
    <url>
        <loc>{$url.loc}</loc>
        <lastmod>{$url.lastmod}</lastmod>
        <changefreq>{$url.changefreq}</changefreq>
        <priority>{$url.priority}</priority>
    </url>
{/carefree:sitemap}
</urlset>`}}</code></pre>
            </el-card>
          </div>

          <!-- 天气信息 ⭐新增 -->
          <div v-show="activeSection === 'weather'" class="tag-section">
            <h3>carefree:weather - 天气信息</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于显示天气信息，支持多城市、多天预报、温度单位切换。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="weatherParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：7天天气预报</div>
              <pre><code>{{`<div class="weather-widget">
    {carefree:weather city='beijing' days='7' unit='celsius' /}
    <div class="weather-current">
        <h3>{$weather.city}</h3>
        <div class="temp">{$weather.current.temp}°{$weather.unit}</div>
        <div class="condition">
            <img src="{$weather.current.icon}" alt="{$weather.current.text}">
            <span>{$weather.current.text}</span>
        </div>
        <div class="details">
            <span>湿度：{$weather.current.humidity}%</span>
            <span>风速：{$weather.current.wind_speed}km/h</span>
        </div>
    </div>
    <div class="weather-forecast">
        {volist name="weather.forecast" id="day"}
        <div class="forecast-day">
            <span class="date">{$day.date|date='m-d'}</span>
            <img src="{$day.icon}" alt="{$day.text}">
            <span class="temp-range">{$day.temp_min}° / {$day.temp_max}°</span>
        </div>
        {/volist}
    </div>
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 智能推荐 ⭐新增 -->
          <div v-show="activeSection === 'recommend'" class="tag-section">
            <h3>carefree:recommend - 智能推荐</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>基于用户行为和内容相似度的AI智能推荐系统，支持协同过滤、内容推荐。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="recommendParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：相关文章推荐</div>
              <pre><code>{{`<!-- 基于当前文章的相似推荐 -->
<div class="related-articles">
    <h4>您可能还喜欢</h4>
    {carefree:recommend type='similar' aid='{$article.id}' limit='6' id='rec'}
    <div class="recommend-item">
        <a href="{$rec.url}">
            <img src="{$rec.cover_image}" alt="{$rec.title}">
            <h5>{$rec.title}</h5>
            <div class="score">匹配度：{$rec.similarity_score}%</div>
        </a>
    </div>
    {/carefree:recommend}
</div>

<!-- 基于用户行为的个性化推荐 -->
<div class="personalized-feed">
    <h3>为你推荐</h3>
    {carefree:recommend type='user' userid='{$user.id}' limit='10' id='item'}
    <article class="feed-item">
        <h4><a href="{$item.url}">{$item.title}</a></h4>
        <p>{$item.summary}</p>
        <div class="reason">推荐理由：{$item.reason}</div>
    </article>
    {/carefree:recommend}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 个性化内容 ⭐新增 -->
          <div v-show="activeSection === 'personalize'" class="tag-section">
            <h3>carefree:personalize - 个性化内容</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>根据用户画像和偏好提供个性化内容，支持多场景定制。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="personalizeParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：首页个性化推荐</div>
              <pre><code>{{`<div class="personalized-homepage">
    {carefree:personalize userid='{$user.id}' scene='homepage' limit='20' id='content'}
    <div class="content-card">
        <span class="tag">{$content.type}</span>
        <h3><a href="{$content.url}">{$content.title}</a></h3>
        <p>{$content.description}</p>
        <div class="meta">
            <span class="score">推荐指数：{$content.score}</span>
            <span class="tags">
                {volist name="content.matched_tags" id="tag"}
                <em>#{$tag}</em>
                {/volist}
            </span>
        </div>
    </div>
    {/carefree:personalize}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 动态表单 ⭐新增 -->
          <div v-show="activeSection === 'form'" class="tag-section">
            <h3>carefree:form - 动态表单</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于创建动态表单，支持各种字段类型、验证规则、自动生成。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="formParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：联系表单</div>
              <pre><code>{{`{carefree:form formid='contact' action='/api/form/submit' method='post' class='contact-form' id='form'}
<div class="form-container">
    <h3>{$form.title}</h3>
    <p>{$form.description}</p>

    {volist name="form.fields" id="field"}
    <div class="form-group {$field.required ? 'required' : ''}">
        <label for="{$field.name}">{$field.label}</label>

        {if condition="$field.type == 'text'"}
        <input type="text" id="{$field.name}" name="{$field.name}"
               placeholder="{$field.placeholder}"
               {$field.required ? 'required' : ''}>

        {elseif condition="$field.type == 'textarea'"/}
        <textarea id="{$field.name}" name="{$field.name}"
                  rows="{$field.rows|default='4'}"
                  placeholder="{$field.placeholder}"
                  {$field.required ? 'required' : ''}></textarea>

        {elseif condition="$field.type == 'select'"/}
        <select id="{$field.name}" name="{$field.name}" {$field.required ? 'required' : ''}>
            <option value="">请选择</option>
            {volist name="field.options" id="opt"}
            <option value="{$opt.value}">{$opt.label}</option>
            {/volist}
        </select>

        {elseif condition="$field.type == 'checkbox'"/}
        <div class="checkbox-group">
            {volist name="field.options" id="opt"}
            <label>
                <input type="checkbox" name="{$field.name}[]" value="{$opt.value}">
                {$opt.label}
            </label>
            {/volist}
        </div>
        {/if}

        {if condition="$field.help_text"}
        <small class="help-text">{$field.help_text}</small>
        {/if}
    </div>
    {/volist}

    <button type="submit" class="btn-submit">提交</button>
</div>
{/carefree:form}`}}</code></pre>
            </el-card>
          </div>

          <!-- 表单字段 ⭐新增 -->
          <div v-show="activeSection === 'formfield'" class="tag-section">
            <h3>carefree:formfield - 表单字段</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于快速生成单个表单字段，简化表单创建流程。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="formfieldParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：快速创建表单字段</div>
              <pre><code>{{`<form action="/api/register" method="post">
    <!-- 文本输入框 -->
    {carefree:formfield
        name='username'
        type='textbox'
        label='用户名'
        required='1'
        placeholder='请输入用户名'
    /}

    <!-- 密码框 -->
    {carefree:formfield
        name='password'
        type='textbox'
        label='密码'
        required='1'
        placeholder='请输入密码'
    /}

    <!-- 下拉选择 -->
    {carefree:formfield
        name='gender'
        type='combobox'
        label='性别'
        options='男,女,保密'
        value='保密'
    /}

    <!-- 复选框 -->
    {carefree:formfield
        name='agree'
        type='checkbox'
        label='同意用户协议'
        required='1'
    /}

    <button type="submit">注册</button>
</form>`}}</code></pre>
            </el-card>
          </div>

          <!-- 验证码 ⭐新增 -->
          <div v-show="activeSection === 'captcha'" class="tag-section">
            <h3>carefree:captcha - 验证码</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于生成各类验证码，支持图片验证码、短信验证码、邮件验证码。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="captchaParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：图片验证码</div>
              <pre><code>{{`<form action="/api/login" method="post">
    <div class="form-group">
        <label>用户名</label>
        <input type="text" name="username" required>
    </div>

    <div class="form-group">
        <label>密码</label>
        <input type="password" name="password" required>
    </div>

    <div class="form-group captcha-group">
        <label>验证码</label>
        <div class="captcha-input">
            <input type="text" name="captcha" required placeholder="请输入验证码">
            {carefree:captcha type='image' width='120' height='40' length='4' /}
            <img src="{$captcha.url}"
                 alt="验证码"
                 class="captcha-image"
                 onclick="this.src='{$captcha.url}?'+Math.random()">
            <input type="hidden" name="captcha_key" value="{$captcha.key}">
        </div>
        <span class="refresh" onclick="refreshCaptcha()">刷新</span>
    </div>

    <button type="submit">登录</button>
</form>`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例：短信验证码</div>
              <pre><code>{{`<div class="phone-verify">
    {carefree:captcha type='sms' length='6' /}
    <input type="tel" name="phone" placeholder="手机号" required>
    <button type="button"
            onclick="sendSms('{$captcha.key}')"
            class="btn-send-code">
        发送验证码
    </button>
    <input type="text"
           name="sms_code"
           placeholder="验证码"
           maxlength="6"
           required>
    <input type="hidden" name="captcha_key" value="{$captcha.key}">
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 多语言 ⭐新增 -->
          <div v-show="activeSection === 'multilang'" class="tag-section">
            <h3>carefree:multilang - 多语言支持</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于实现网站国际化，支持多语言翻译、自动语言切换。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="multilangParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：多语言网站</div>
              <pre><code>{{`<!-- 页面标题 -->
<h1>{carefree:multilang key='site.welcome' default='欢迎访问' /}</h1>

<!-- 导航菜单 -->
<nav>
    <a href="/">{carefree:multilang key='nav.home' default='首页' /}</a>
    <a href="/about">{carefree:multilang key='nav.about' default='关于' /}</a>
    <a href="/contact">{carefree:multilang key='nav.contact' default='联系' /}</a>
</nav>

<!-- 指定语言 -->
<p>{carefree:multilang key='greeting' lang='en' default='Hello' /}</p>
<p>{carefree:multilang key='greeting' lang='zh-cn' default='你好' /}</p>
<p>{carefree:multilang key='greeting' lang='ja' default='こんにちは' /}</p>

<!-- 表单标签 -->
<form>
    <label>{carefree:multilang key='form.username' /}</label>
    <input type="text" placeholder="{carefree:multilang key='form.username.placeholder' /}">

    <label>{carefree:multilang key='form.email' /}</label>
    <input type="email" placeholder="{carefree:multilang key='form.email.placeholder' /}">

    <button>{carefree:multilang key='form.submit' default='提交' /}</button>
</form>

<!-- 语言切换器 -->
<div class="lang-switcher">
    {volist name=":app\service\tag\MultilangTagService::getSupportedLangs()" id="lang"}
    <a href="?lang={$lang.code}" class="{$lang.is_current ? 'active' : ''}">
        {$lang.name}
    </a>
    {/volist}
</div>`}}</code></pre>
            </el-card>
          </div>

          <!-- 缓存标签 ⭐新增 -->
          <div v-show="activeSection === 'cache'" class="tag-section">
            <h3>carefree:cache - 缓存标签</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于缓存模板片段，提升页面性能，减少数据库查询。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="cacheParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：缓存侧边栏内容</div>
              <pre><code>{{`<!-- 缓存热门文章（1小时） -->
{carefree:cache key='sidebar_hot_articles' time='3600'}
<div class="hot-articles">
    <h4>热门文章</h4>
    {carefree:article orderby='view_count desc' limit='10' id='hot'}
    <div class="hot-item">
        <a href="{$hot.url}">{$hot.title}</a>
        <span class="views">{$hot.view_count}</span>
    </div>
    {/carefree:article}
</div>
{/carefree:cache}

<!-- 缓存标签云（24小时） -->
{carefree:cache key='sidebar_tag_cloud' time='86400'}
<div class="tag-cloud">
    <h4>标签云</h4>
    {carefree:tagcloud limit='30' id='tag'}
    <a href="{$tag.url}" style="font-size: {$tag.font_size}px">
        {$tag.name}
    </a>
    {/carefree:tagcloud}
</div>
{/carefree:cache}

<!-- 缓存分类导航（永久，手动清除） -->
{carefree:cache key='header_nav' time='0'}
<nav class="category-nav">
    {carefree:category type='article' parentid='0' id='cat'}
    <a href="{$cat.url}">{$cat.name}</a>
    {/carefree:category}
</nav>
{/carefree:cache}`}}</code></pre>
            </el-card>

            <el-alert type="warning" :closable="false" show-icon>
              <p><strong>性能提示</strong>：缓存标签非常适合缓存以下内容：</p>
              <ul>
                <li>导航菜单、分类列表等不常变化的内容</li>
                <li>热门文章、推荐内容等查询成本较高的数据</li>
                <li>复杂的统计数据、排行榜等</li>
                <li>第三方API获取的数据（如天气、股票等）</li>
              </ul>
            </el-alert>
          </div>

          <!-- 条件标签 ⭐新增 -->
          <div v-show="activeSection === 'condition'" class="tag-section">
            <h3>carefree:condition - 条件标签</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于条件判断和分支逻辑，支持复杂表达式。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="conditionParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：根据条件显示不同内容</div>
              <pre><code>{{`<!-- 判断用户登录状态 -->
{carefree:condition if='$user.id > 0'}
<div class="user-info">
    <span>欢迎，{$user.username}</span>
    <a href="/logout">退出</a>
</div>
{else}
<div class="login-btns">
    <a href="/login">登录</a>
    <a href="/register">注册</a>
</div>
{/carefree:condition}

<!-- 判断文章分类 -->
{carefree:condition if='$article.catid == 1'}
<div class="article-tech">
    <!-- 技术类文章的特殊样式 -->
</div>
{elseif condition='$article.catid == 2'/}
<div class="article-life">
    <!-- 生活类文章的特殊样式 -->
</div>
{else}
<div class="article-other">
    <!-- 其他分类的样式 -->
</div>
{/carefree:condition}

<!-- 复杂条件判断 -->
{carefree:condition if='$article.view_count > 1000 && $article.is_featured == 1'}
<span class="badge hot">热门精选</span>
{/carefree:condition}

<!-- 判断时间 -->
{carefree:condition if='strtotime($article.create_time) > time() - 86400'}
<span class="badge new">新文章</span>
{/carefree:condition}`}}</code></pre>
            </el-card>
          </div>

          <!-- 分组标签 ⭐新增 -->
          <div v-show="activeSection === 'group'" class="tag-section">
            <h3>carefree:group - 分组标签</h3>
            <el-divider content-position="left">标签说明</el-divider>
            <p>用于对数据进行分组展示，支持按字段分组、多级分组。</p>

            <el-divider content-position="left">参数说明</el-divider>
            <el-table :data="groupParams" border>
              <el-table-column prop="name" label="参数名" width="150" />
              <el-table-column prop="required" label="必填" width="100" />
              <el-table-column prop="default" label="默认值" width="150" />
              <el-table-column prop="description" label="说明" />
            </el-table>

            <el-divider content-position="left">使用示例</el-divider>
            <el-card class="code-card">
              <div class="code-header">示例：按分类分组显示文章</div>
              <pre><code>{{`<!-- 获取所有文章 -->
{assign name="articles" value=":app\service\tag\ArticleTagService::getList(['limit' => 50])" /}

<!-- 按分类分组 -->
{carefree:group data='$articles' by='category_name' id='group'}
<div class="article-group">
    <h3 class="group-title">{$group.key}</h3>
    <div class="article-list">
        {volist name="group.items" id="article"}
        <div class="article-item">
            <h4><a href="{$article.url}">{$article.title}</a></h4>
            <p>{$article.summary}</p>
            <span class="date">{$article.publish_time|date='Y-m-d'}</span>
        </div>
        {/volist}
    </div>
    <p class="group-count">共 {$group.count} 篇文章</p>
</div>
{/carefree:group}`}}</code></pre>
            </el-card>

            <el-card class="code-card">
              <div class="code-header">示例：按年份分组展示归档</div>
              <pre><code>{{`{assign name="articles" value=":app\service\tag\ArticleTagService::getList(['limit' => 0, 'orderby' => 'publish_time desc'])" /}

{carefree:group data='$articles' by='year' id='yearGroup'}
<div class="archive-year">
    <h2>{$yearGroup.key}年</h2>

    <!-- 按月份再分组 -->
    {carefree:group data='$yearGroup.items' by='month' id='monthGroup'}
    <div class="archive-month">
        <h4>{$monthGroup.key}月 ({$monthGroup.count}篇)</h4>
        <ul>
            {volist name="monthGroup.items" id="article"}
            <li>
                <span class="date">{$article.publish_time|date='m-d'}</span>
                <a href="{$article.url}">{$article.title}</a>
            </li>
            {/volist}
        </ul>
    </div>
    {/carefree:group}
</div>
{/carefree:group}`}}</code></pre>
            </el-card>
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
  QuestionFilled,
  // 新增icon
  VideoPlay,
  Headset,
  Download,
  Checked,
  Present,
  Stamp,
  MagicStick,
  Star,
  Timer,
  Select
} from '@element-plus/icons-vue'

const activeSection = ref('config')

const handleMenuSelect = (key) => {
  activeSection.value = key
}

// 打开完整文档
const openCompleteGuide = () => {
  const docsUrl = '/docs/carefree-taglib/CAREFREE_TAGLIB_COMPLETE_GUIDE.md'
  window.open(docsUrl, '_blank')
}

// 打开快速参考
const openQuickReference = () => {
  const docsUrl = '/docs/carefree-taglib/CAREFREE_QUICK_REFERENCE_V2.md'
  window.open(docsUrl, '_blank')
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

// ========== 新增标签参数 ⭐ ==========

// 相册图库标签参数
const galleryParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'albumid', required: '否', default: '-', description: '相册ID' },
  { name: 'limit', required: '否', default: '20', description: '返回数量' },
  { name: 'orderby', required: '否', default: 'create_time desc', description: '排序方式' },
  { name: 'columns', required: '否', default: '4', description: '列数（用于布局）' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 视频列表标签参数
const videoParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'catid', required: '否', default: '-', description: '分类ID' },
  { name: 'limit', required: '否', default: '10', description: '返回数量' },
  { name: 'orderby', required: '否', default: 'publish_time desc', description: '排序方式' },
  { name: 'featured', required: '否', default: '0', description: '是否精选：1-是，0-否' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 音频列表标签参数
const audioParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'catid', required: '否', default: '-', description: '分类ID' },
  { name: 'limit', required: '否', default: '10', description: '返回数量' },
  { name: 'orderby', required: '否', default: 'publish_time desc', description: '排序方式' },
  { name: 'featured', required: '否', default: '0', description: '是否精选：1-是，0-否' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 文件下载标签参数
const downloadParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'catid', required: '否', default: '-', description: '分类ID' },
  { name: 'type', required: '否', default: '-', description: '文件类型：software-软件，document-文档，media-媒体' },
  { name: 'limit', required: '否', default: '15', description: '返回数量' },
  { name: 'orderby', required: '否', default: 'download_count desc', description: '排序方式' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 投票系统标签参数
const voteParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'voteid', required: '是', default: '-', description: '投票ID' },
  { name: 'showresult', required: '否', default: '0', description: '是否显示结果：1-是，0-否' }
]

// 在线测验标签参数
const quizParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'quizid', required: '是', default: '-', description: '测验ID' }
]

// 抽奖活动标签参数
const lotteryParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'lotteryid', required: '是', default: '-', description: '抽奖活动ID' }
]

// 二维码生成标签参数
const qrcodeParams = [
  { name: 'content', required: '是', default: '-', description: '二维码内容（URL或文本）' },
  { name: 'size', required: '否', default: '200', description: '尺寸（像素）' },
  { name: 'logo', required: '否', default: '-', description: 'Logo图片URL' },
  { name: 'level', required: '否', default: 'L', description: '纠错级别：L、M、Q、H' }
]

// 日历事件标签参数
const calendarParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'year', required: '否', default: '当前年', description: '年份' },
  { name: 'month', required: '否', default: '当前月', description: '月份' },
  { name: 'events', required: '否', default: '1', description: '是否包含事件：1-是，0-否' }
]

// 站点地图标签参数
const sitemapParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'type', required: '否', default: 'all', description: '类型：all-全部，article-文章，page-单页' },
  { name: 'format', required: '否', default: 'html', description: '格式：html、xml' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 天气信息标签参数
const weatherParams = [
  { name: 'city', required: '是', default: 'beijing', description: '城市名称或代码' },
  { name: 'days', required: '否', default: '3', description: '预报天数：1-7' },
  { name: 'unit', required: '否', default: 'celsius', description: '温度单位：celsius-摄氏，fahrenheit-华氏' }
]

// 智能推荐标签参数
const recommendParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'type', required: '是', default: 'similar', description: '推荐类型：similar-相似内容，user-基于用户，hot-热门推荐' },
  { name: 'userid', required: '否', default: '-', description: '用户ID（type=user时必填）' },
  { name: 'aid', required: '否', default: '-', description: '文章ID（type=similar时必填）' },
  { name: 'limit', required: '否', default: '6', description: '返回数量' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 个性化内容标签参数
const personalizeParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'userid', required: '是', default: '-', description: '用户ID' },
  { name: 'scene', required: '否', default: 'homepage', description: '场景：homepage-首页，detail-详情页，sidebar-侧边栏' },
  { name: 'limit', required: '否', default: '10', description: '返回数量' },
  { name: 'empty', required: '否', default: '-', description: '空数据时显示的内容' }
]

// 动态表单标签参数
const formParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'formid', required: '是', default: '-', description: '表单ID' },
  { name: 'action', required: '否', default: '-', description: '提交地址' },
  { name: 'method', required: '否', default: 'post', description: '提交方式：post、get' },
  { name: 'class', required: '否', default: '-', description: 'CSS类名' }
]

// 表单字段标签参数
const formfieldParams = [
  { name: 'name', required: '是', default: '-', description: '字段名称' },
  { name: 'type', required: '是', default: 'textbox', description: '字段类型：textbox、checkbox、radio、combobox、slider' },
  { name: 'label', required: '否', default: '-', description: '字段标签' },
  { name: 'required', required: '否', default: '0', description: '是否必填：1-是，0-否' },
  { name: 'placeholder', required: '否', default: '-', description: '占位提示文本' },
  { name: 'options', required: '否', default: '-', description: '选项（用于select、checkbox、radio）' },
  { name: 'value', required: '否', default: '-', description: '默认值' }
]

// 验证码标签参数
const captchaParams = [
  { name: 'type', required: '否', default: 'image', description: '类型：image-图片，sms-短信，email-邮件' },
  { name: 'width', required: '否', default: '120', description: '宽度（仅image类型）' },
  { name: 'height', required: '否', default: '40', description: '高度（仅image类型）' },
  { name: 'length', required: '否', default: '4', description: '验证码长度' }
]

// 多语言标签参数
const multilangParams = [
  { name: 'key', required: '是', default: '-', description: '翻译键' },
  { name: 'lang', required: '否', default: '当前语言', description: '语言代码：zh-cn、en、ja等' },
  { name: 'default', required: '否', default: 'key', description: '默认值（未找到翻译时显示）' }
]

// 缓存标签参数
const cacheParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'key', required: '是', default: '-', description: '缓存键名（唯一标识）' },
  { name: 'time', required: '否', default: '3600', description: '缓存时间（秒），0为永久缓存' }
]

// 条件标签参数
const conditionParams = [
  { name: 'id', required: '否', default: '-', description: '循环变量名' },
  { name: 'if', required: '是', default: '-', description: '条件表达式（PHP语法）' }
]

// 分组标签参数
const groupParams = [
  { name: 'id', required: '是', default: '-', description: '循环变量名' },
  { name: 'data', required: '是', default: '-', description: '要分组的数据数组' },
  { name: 'by', required: '是', default: '-', description: '分组依据字段' },
  { name: 'key', required: '否', default: 'key', description: '分组键的变量名' },
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
