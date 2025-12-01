# 逍遥CMS默认模板包

## 简介

这是逍遥CMS的默认模板包，提供了完整的网站功能模板，包括首页、分类页、文章详情、搜索、归档等页面。

## 模板文件

- `layout.html` - 布局框架，所有页面的基础模板
- `index.html` - 首页模板
- `category.html` - 分类页模板
- `article.html` - 文章详情页模板
- `articles.html` - 文章列表页模板
- `tag.html` - 标签页模板
- `page.html` - 单页模板
- `search.html` - 搜索页模板
- `topic.html` - 专题页模板
- `archive.html` - 归档页模板
- `404.html` - 404错误页模板
- `sidebar.html` - 侧边栏模板
- `theme.json` - 主题配置文件

## 特性

- 响应式设计，支持移动端
- 支持自定义配色方案
- 支持自定义布局参数
- 内置多种小工具（搜索、热门文章、标签云等）
- SEO友好
- 代码规范，易于二次开发

## 模板变量说明

### 全局变量

- `site` - 站点信息对象
  - `site.site_name` - 站点名称
  - `site.logo` - 站点Logo
  - `site.copyright` - 版权信息
  - `site.icp_no` - ICP备案号
  - `site.seo_config` - SEO配置（seo_title, seo_keywords, seo_description）

### 首页变量

- `sliders` - 轮播图列表
- `recommended_articles` - 推荐文章列表
- `latest_articles` - 最新文章列表
- `category_articles` - 分类文章数据
- `links` - 友情链接列表

### 文章列表变量

- `articles` - 文章列表
- `total` - 文章总数
- `page` - 当前页码
- `pages` - 总页数
- `order` - 排序方式

### 文章详情变量

- `article` - 文章对象
  - `article.title` - 文章标题
  - `article.content` - 文章内容
  - `article.author` - 作者
  - `article.publish_time` - 发布时间
  - `article.views` - 浏览次数
  - `article.tags` - 标签列表
- `prev_article` - 上一篇文章
- `next_article` - 下一篇文章
- `related_articles` - 相关文章列表

### 分类页变量

- `category` - 分类对象
  - `category.name` - 分类名称
  - `category.description` - 分类描述
  - `category.keywords` - 关键词
- `sub_categories` - 子分类列表
- `articles` - 该分类下的文章列表

### 侧边栏变量

- `hot_articles` - 热门文章列表
- `latest_articles` - 最新文章列表
- `categories` - 分类列表
- `tags` - 标签列表
- `sidebar_links` - 侧边栏友情链接
- `sidebar_ads` - 侧边栏广告

## 自定义配置

在站点模板配置中，可以自定义以下参数：

### 配色方案
```json
{
  "colors": {
    "primary": "#409EFF",
    "success": "#67C23A",
    "warning": "#E6A23C",
    "danger": "#F56C6C"
  }
}
```

### 布局配置
```json
{
  "layout": {
    "header_height": "60px",
    "sidebar_width": "300px",
    "content_width": "1200px",
    "fixed_header": true
  }
}
```

### 功能开关
```json
{
  "features": {
    "show_breadcrumb": true,
    "show_sidebar": true,
    "enable_comment": true,
    "enable_share": true
  }
}
```

## 使用方法

1. 在后台"系统管理" -> "多站点管理"中选择站点
2. 切换到"模板配置"标签
3. 选择"默认模板包"
4. 根据需要自定义配置
5. 保存配置即可生效

## 技术支持

- 官方网站：https://www.xiaoyao-cms.com
- 文档中心：https://docs.xiaoyao-cms.com
- 问题反馈：https://github.com/xiaoyao-cms/issues

## 版权信息

Copyright © 2025 逍遥CMS
MIT License
