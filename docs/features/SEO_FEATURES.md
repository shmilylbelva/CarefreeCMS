# SEO优化功能文档

## 功能概述

SEO优化功能提供了完整的搜索引擎优化工具集，包括URL重定向管理、404错误监控、Robots.txt配置、智能SEO分析、关键词密度检测、自动优化、增强Sitemap生成、关键词排名追踪等功能，帮助提升网站在搜索引擎中的可见度和排名。

## 功能模块

### 1. URL重定向管理

URL重定向功能允许管理员配置301/302重定向规则，支持多种匹配模式，适用于网站改版、URL优化等场景。

#### 1.1 功能特性

- **多种重定向类型**
  - 301 永久重定向：适用于URL永久性改变
  - 302 临时重定向：适用于临时性URL更改

- **灵活的匹配模式**
  - **精确匹配**：完全匹配URL路径
  - **通配符匹配**：使用 `*` 匹配任意字符
  - **正则表达式**：支持复杂的URL匹配规则

- **智能统计**
  - 命中次数统计
  - 最后命中时间记录
  - 规则效果分析

- **批量操作**
  - 批量启用/禁用
  - 批量删除
  - CSV格式导入/导出

#### 1.2 使用场景

**场景1：单页面迁移**
```
源URL: /old-page
目标URL: /new-page
类型: 301 永久重定向
匹配: 精确匹配
```

**场景2：目录结构调整**
```
源URL: /blog/*
目标URL: /articles/*
类型: 301 永久重定向
匹配: 通配符
说明: /blog/abc 会重定向到 /articles/abc
```

**场景3：动态参数重定向**
```
源URL: /product\.php\?id=(\d+)
目标URL: /products/$1
类型: 301 永久重定向
匹配: 正则表达式
说明: /product.php?id=123 会重定向到 /products/123
```

#### 1.3 数据库设计

```sql
CREATE TABLE `seo_redirects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_url` varchar(500) NOT NULL COMMENT '源URL',
  `to_url` varchar(500) NOT NULL COMMENT '目标URL',
  `redirect_type` int(11) NOT NULL DEFAULT '301' COMMENT '重定向类型',
  `match_type` varchar(20) NOT NULL DEFAULT 'exact' COMMENT '匹配类型',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否启用',
  `hit_count` int(11) NOT NULL DEFAULT '0' COMMENT '命中次数',
  `last_hit_time` datetime DEFAULT NULL COMMENT '最后命中时间',
  `description` varchar(255) DEFAULT NULL COMMENT '规则描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### 1.4 API 接口

**获取重定向列表**
```http
GET /backend/seo-redirects
参数:
  - page: 页码
  - per_page: 每页数量
  - keyword: 搜索关键词
  - is_enabled: 启用状态
  - redirect_type: 重定向类型
  - match_type: 匹配类型
```

**创建重定向规则**
```http
POST /backend/seo-redirects
Body:
{
  "from_url": "/old-page",
  "to_url": "/new-page",
  "redirect_type": 301,
  "match_type": "exact",
  "is_enabled": 1,
  "description": "页面迁移"
}
```

**测试重定向规则**
```http
POST /backend/seo-redirects/test
Body:
{
  "url": "/old-page"
}
Response:
{
  "matched": true,
  "rule": { ... },
  "target_url": "/new-page"
}
```

**导入重定向规则（CSV格式）**
```http
POST /backend/seo-redirects/import
Body:
{
  "content": "from_url,to_url,redirect_type,match_type,description\n/old,/new,301,exact,迁移"
}
```

---

### 2. 404错误监控

404错误监控功能自动记录网站的404错误，帮助管理员快速发现和修复失效链接。

#### 2.1 功能特性

- **自动记录**
  - 自动记录404错误的URL
  - 记录来源页面（Referer）
  - 记录访客IP和User-Agent
  - 首次和最后出现时间

- **智能统计**
  - 高频404错误（按命中次数排序）
  - 最近404错误（按时间排序）
  - 每日新增统计
  - 修复状态追踪

- **快速修复**
  - 一键创建重定向规则
  - 标记为已修复（重定向/已删除/已忽略）
  - 批量处理

- **数据清理**
  - 定期清理旧日志
  - 导出日志数据

#### 2.2 使用流程

**步骤1：查看404统计**
- 进入"404监控"页面
- 查看统计卡片：未修复数量、总命中次数、已修复数量、今日新增

**步骤2：分析高频错误**
- 按命中次数排序，找出访问最多的404页面
- 检查来源页面，确定问题根源

**步骤3：修复404错误**

**方式1：创建重定向**
```
点击"创建重定向"按钮
输入目标URL
选择301或302
提交后自动创建重定向规则并标记为已修复
```

**方式2：标记为已删除**
```
如果内容确实已删除且不需要重定向
点击"忽略"按钮，标记为已修复
添加备注说明原因
```

**步骤4：定期清理**
- 设置清理周期（如90天）
- 自动清理已修复的旧日志

#### 2.3 自动记录实现

在前台网站的404错误处理中添加以下代码：

**ThinkPHP示例**
```php
// app/ExceptionHandle.php
public function render($request, Throwable $e): Response
{
    if ($e instanceof HttpException && $e->getStatusCode() === 404) {
        // 记录404错误
        \app\model\Seo404Log::record(
            $request->url(),
            $request->header('referer', ''),
            $request->ip(),
            $request->header('user-agent', '')
        );
    }

    return parent::render($request, $e);
}
```

**Nginx配置示例**
```nginx
error_page 404 = /404_handler.php;

location = /404_handler.php {
    # 记录404到数据库
    # 然后展示404页面
}
```

#### 2.4 数据库设计

```sql
CREATE TABLE `seo_404_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(500) NOT NULL COMMENT '404 URL',
  `referer` varchar(500) DEFAULT NULL COMMENT '来源页面',
  `ip` varchar(50) DEFAULT NULL COMMENT 'IP地址',
  `user_agent` varchar(500) DEFAULT NULL COMMENT '用户代理',
  `hit_count` int(11) NOT NULL DEFAULT '1' COMMENT '出现次数',
  `first_hit_time` datetime DEFAULT NULL COMMENT '首次出现时间',
  `last_hit_time` datetime DEFAULT NULL COMMENT '最后出现时间',
  `is_fixed` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已修复',
  `fixed_time` datetime DEFAULT NULL COMMENT '修复时间',
  `fixed_method` varchar(50) DEFAULT NULL COMMENT '修复方式',
  `notes` text COMMENT '备注',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_url` (`url`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

### 3. Robots.txt 管理

Robots.txt是告诉搜索引擎爬虫哪些页面可以抓取、哪些不能抓取的配置文件。

#### 3.1 功能特性

- **多配置管理**
  - 创建多个配置版本
  - 一键切换启用配置
  - 配置历史记录

- **预设模板**
  - 默认配置（推荐）
  - 全部允许
  - 全部禁止（开发环境）
  - 仅百度
  - 谷歌和必应
  - 阻止恶意爬虫
  - 限制抓取频率

- **内容验证**
  - 实时语法检查
  - 错误提示
  - 格式规范验证

- **文件生成**
  - 一键生成到网站根目录
  - 查看当前文件内容
  - 修改时间记录

#### 3.2 预设模板说明

**1. 默认配置（推荐）**
```
User-agent: *
Disallow: /admin/
Disallow: /backend/
Disallow: *.json$
Disallow: *.xml$

Sitemap: /sitemap.xml
```
适用场景：大多数网站的标准配置，允许抓取公开内容，禁止后台和API

**2. 全部允许**
```
User-agent: *
Disallow:

Sitemap: /sitemap.xml
```
适用场景：完全开放的网站，所有内容都可抓取

**3. 全部禁止**
```
User-agent: *
Disallow: /
```
适用场景：开发/测试环境，不希望搜索引擎抓取

**4. 仅百度**
```
User-agent: Baiduspider
Disallow: /admin/
Disallow: /backend/

User-agent: *
Disallow: /

Sitemap: /sitemap.xml
```
适用场景：只针对中国市场，只允许百度抓取

**5. 谷歌和必应**
```
User-agent: Googlebot
Disallow: /admin/
Disallow: /backend/

User-agent: Bingbot
Disallow: /admin/
Disallow: /backend/

User-agent: *
Disallow: /

Sitemap: /sitemap.xml
```
适用场景：针对国际市场，只允许主流搜索引擎

**6. 阻止恶意爬虫**
```
User-agent: *
Disallow: /admin/
Disallow: /backend/

# 阻止恶意爬虫
User-agent: SemrushBot
User-agent: AhrefsBot
User-agent: DotBot
User-agent: MJ12bot
Disallow: /

Sitemap: /sitemap.xml
```
适用场景：防止SEO工具和采集器过度抓取

**7. 限制抓取频率**
```
User-agent: *
Crawl-delay: 10
Disallow: /admin/
Disallow: /backend/

Sitemap: /sitemap.xml
```
适用场景：服务器资源有限，需要限制爬虫抓取速度

#### 3.3 Robots.txt 语法

**基本指令**

```
User-agent: 指定爬虫
  - * : 所有爬虫
  - Baiduspider : 百度爬虫
  - Googlebot : 谷歌爬虫
  - Bingbot : 必应爬虫

Disallow: 禁止抓取的路径
  - / : 禁止所有
  - /admin/ : 禁止admin目录
  - *.pdf$ : 禁止PDF文件

Allow: 允许抓取的路径（优先级高于Disallow）

Crawl-delay: 抓取延迟（秒）

Sitemap: Sitemap文件位置
```

**示例：复杂配置**
```
# 百度：允许抓取，但限制频率
User-agent: Baiduspider
Crawl-delay: 5
Disallow: /admin/
Disallow: /backend/
Disallow: /user/
Allow: /user/profile/

# 谷歌：正常抓取
User-agent: Googlebot
Disallow: /admin/
Disallow: /backend/

# 其他搜索引擎：禁止所有
User-agent: *
Disallow: /

# Sitemap位置
Sitemap: https://example.com/sitemap.xml
Sitemap: https://example.com/sitemap-news.xml
```

#### 3.4 验证规则

系统会自动验证以下规则：

1. **格式检查**
   - 每行必须是 `Key: Value` 格式
   - 键值之间用冒号分隔
   - 支持 # 开头的注释

2. **指令检查**
   - 只允许标准指令（User-agent, Disallow, Allow, Crawl-delay, Sitemap等）
   - 不允许的指令会报错

3. **逻辑检查**
   - Disallow/Allow 前必须有 User-agent
   - 路径必须以 / 开头（或为空）

#### 3.5 最佳实践

**1. 分阶段配置**
- 开发环境：使用"全部禁止"
- 测试环境：使用"全部禁止"
- 生产环境：使用"默认配置"或自定义

**2. 优先级顺序**
```
Allow 指令 > Disallow 指令
更具体的路径 > 更通用的路径
```

**3. 常见错误**
```
❌ 错误：Disallow admin/
✅ 正确：Disallow: /admin/

❌ 错误：User-agent *
✅ 正确：User-agent: *

❌ 错误：在User-agent之前使用Disallow
✅ 正确：先定义User-agent，再使用Disallow
```

**4. 性能建议**
- 使用 Crawl-delay 防止爬虫过度抓取
- 定期更新 Sitemap 路径
- 监控爬虫访问日志

---

### 4. SEO元数据管理

#### 4.1 文章SEO字段

系统为文章表添加了完整的SEO元数据字段：

```sql
ALTER TABLE `articles`
ADD COLUMN `seo_title` varchar(100) DEFAULT NULL COMMENT 'SEO标题',
ADD COLUMN `seo_keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键词',
ADD COLUMN `seo_description` varchar(500) DEFAULT NULL COMMENT 'SEO描述',
ADD COLUMN `og_title` varchar(100) DEFAULT NULL COMMENT 'Open Graph标题',
ADD COLUMN `og_description` varchar(500) DEFAULT NULL COMMENT 'Open Graph描述',
ADD COLUMN `og_image` varchar(255) DEFAULT NULL COMMENT 'Open Graph图片',
ADD COLUMN `twitter_card` varchar(20) DEFAULT 'summary' COMMENT 'Twitter卡片类型',
ADD COLUMN `canonical_url` varchar(255) DEFAULT NULL COMMENT '规范链接',
ADD COLUMN `schema_type` varchar(50) DEFAULT 'Article' COMMENT 'Schema.org类型';
```

#### 4.2 前台模板使用

**基本SEO标签**
```html
<head>
  <title>{$article.seo_title ?: $article.title}</title>
  <meta name="keywords" content="{$article.seo_keywords}">
  <meta name="description" content="{$article.seo_description ?: $article.summary}">
  <link rel="canonical" href="{$article.canonical_url ?: $article.url}">
</head>
```

**Open Graph标签（社交分享）**
```html
<meta property="og:type" content="article">
<meta property="og:title" content="{$article.og_title ?: $article.title}">
<meta property="og:description" content="{$article.og_description ?: $article.summary}">
<meta property="og:image" content="{$article.og_image ?: $article.cover_image}">
<meta property="og:url" content="{$article.url}">
```

**Twitter Card标签**
```html
<meta name="twitter:card" content="{$article.twitter_card}">
<meta name="twitter:title" content="{$article.og_title ?: $article.title}">
<meta name="twitter:description" content="{$article.og_description ?: $article.summary}">
<meta name="twitter:image" content="{$article.og_image ?: $article.cover_image}">
```

**Schema.org结构化数据**
```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "{$article.schema_type}",
  "headline": "{$article.title}",
  "description": "{$article.summary}",
  "image": "{$article.cover_image}",
  "author": {
    "@type": "Person",
    "name": "{$article.author}"
  },
  "datePublished": "{$article.publish_time}",
  "dateModified": "{$article.update_time}"
}
</script>
```

---

### 5. 关键词排名追踪（可选）

关键词排名追踪功能用于监控网站关键词在搜索引擎中的排名变化。

#### 5.1 数据库设计

```sql
CREATE TABLE `seo_keyword_rankings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(100) NOT NULL COMMENT '关键词',
  `url` varchar(500) NOT NULL COMMENT '目标URL',
  `search_engine` varchar(20) NOT NULL DEFAULT 'baidu' COMMENT '搜索引擎',
  `ranking` int(11) DEFAULT NULL COMMENT '排名位置',
  `check_date` date NOT NULL COMMENT '检查日期',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_keyword_engine_date` (`keyword`, `search_engine`, `check_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### 5.2 使用示例

```php
// 记录关键词排名
SeoKeywordRanking::record('CMS系统', 'https://example.com', 'baidu', 15);

// 获取排名历史
$history = SeoKeywordRanking::getHistory('CMS系统', 'baidu', 30);

// 获取排名趋势
$trend = SeoKeywordRanking::getTrend('CMS系统', 'baidu', 30);
// 返回: ['trend' => 'up', 'change' => 5, 'latest' => 10, 'previous' => 15]
```

---

### 6. SEO分析工具

SEO分析工具提供智能的内容SEO评估、关键词密度分析、元数据自动生成等功能，帮助优化内容的搜索引擎表现。

#### 6.1 功能特性

- **SEO评分系统**
  - 7维度综合评估（总分100分）
  - A-E等级评定
  - 详细问题和优化建议
  - 批量分析多篇文章

- **关键词密度分析**
  - 计算关键词出现频率
  - 密度百分比统计
  - 最优密度提示（1-3%）
  - 可视化展示

- **智能内容生成**
  - 自动生成SEO标题
  - 自动生成SEO描述
  - 自动提取关键词
  - 一键优化文章

- **增强Sitemap生成**
  - 标准XML Sitemap
  - 图片Sitemap（含alt、title）
  - 新闻Sitemap（最近2天）
  - Sitemap索引文件
  - 多语言Sitemap（含hreflang）
  - Ping搜索引擎（Google、Bing、Baidu）

#### 6.2 SEO评分系统

**评分维度（7项，总分100）**

1. **标题分析（20分）**
   - 标题存在（5分）
   - 标题长度30-60字符（10分，部分得分）
   - SEO标题独立设置（5分）

2. **描述分析（15分）**
   - 描述存在（5分）
   - 描述长度80-160字符（10分，部分得分）

3. **关键词分析（15分）**
   - 关键词存在（5分）
   - 关键词数量3-5个最优（10分）

4. **内容分析（20分）**
   - 内容长度≥300字（10分）
   - 关键词密度1-3%（10分）

5. **图片分析（10分）**
   - 有封面图片（5分）
   - 图片含alt属性（5分）

6. **链接分析（10分）**
   - 有内部链接（5分）
   - 有外部链接（5分）

7. **可读性分析（10分）**
   - 分段合理（5分）
   - 有标题结构（5分）

**等级评定**
```
A级：90-100分 - 优秀
B级：80-89分  - 良好
C级：70-79分  - 一般
D级：60-69分  - 较差
E级：<60分   - 差
```

#### 6.3 关键词密度分析

**密度评估标准**
```
最优：1-3%
偏低：0-1%
偏高：>3%
```

**计算公式**
```
密度 = (关键词出现次数 × 关键词长度 / 内容总长度) × 100%
```

**分析结果**
```json
{
  "total_words": 1500,
  "keywords": [
    {
      "keyword": "CMS系统",
      "count": 15,
      "density": 2.5,
      "evaluation": "optimal"
    },
    {
      "keyword": "内容管理",
      "count": 3,
      "density": 0.8,
      "evaluation": "too_low"
    }
  ]
}
```

#### 6.4 智能内容生成

**1. 自动生成SEO标题**
```php
SeoAnalyzer::generateSeoTitle($title, $keywords);
```
- 整合首个关键词到标题中
- 控制长度在60字符以内
- 保持标题可读性

**2. 自动生成SEO描述**
```php
SeoAnalyzer::generateSeoDescription($content, $keywords, $maxLength = 160);
```
- 从内容中提取关键段落
- 整合关键词自然出现的句子
- 在句子结尾处截断，保持完整性

**3. 自动提取关键词**
```php
SeoAnalyzer::extractKeywords($content, $count = 5);
```
- 基于词频统计
- 过滤停用词
- 返回高频关键词（2-10字符）

#### 6.5 增强Sitemap功能

**标准Sitemap**
```xml
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>https://example.com/article/1.html</loc>
    <lastmod>2025-10-19</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
</urlset>
```

**图片Sitemap**
```xml
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
  <url>
    <loc>https://example.com/article/1.html</loc>
    <image:image>
      <image:loc>https://example.com/images/cover.jpg</image:loc>
      <image:title>文章标题</image:title>
      <image:caption>图片说明</image:caption>
    </image:image>
  </url>
</urlset>
```

**新闻Sitemap（最近2天）**
```xml
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
  <url>
    <loc>https://example.com/article/1.html</loc>
    <news:news>
      <news:publication>
        <news:name>My Site</news:name>
        <news:language>zh-cn</news:language>
      </news:publication>
      <news:publication_date>2025-10-19T10:00:00+08:00</news:publication_date>
      <news:title>文章标题</news:title>
      <news:keywords>关键词1, 关键词2</news:keywords>
    </news:news>
  </url>
</urlset>
```

**Sitemap索引文件**
```xml
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <sitemap>
    <loc>https://example.com/sitemap.xml</loc>
    <lastmod>2025-10-19T10:00:00+08:00</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://example.com/sitemap-images.xml</loc>
    <lastmod>2025-10-19T10:00:00+08:00</lastmod>
  </sitemap>
  <sitemap>
    <loc>https://example.com/sitemap-news.xml</loc>
    <lastmod>2025-10-19T10:00:00+08:00</lastmod>
  </sitemap>
</sitemapindex>
```

**多语言Sitemap**
```xml
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xhtml="http://www.w3.org/1999/xhtml">
  <url>
    <loc>https://example.com/zh-cn/article/1.html</loc>
    <xhtml:link rel="alternate" hreflang="zh-cn"
                href="https://example.com/zh-cn/article/1.html" />
    <xhtml:link rel="alternate" hreflang="en"
                href="https://example.com/en/article/1.html" />
  </url>
</urlset>
```

#### 6.6 API 接口

**分析文章SEO**
```http
GET /backend/seo-analyzer/analyze/:id
POST /backend/seo-analyzer/analyze
Body:
{
  "title": "文章标题",
  "seo_title": "SEO标题",
  "seo_description": "SEO描述",
  "seo_keywords": "关键词1,关键词2",
  "summary": "摘要",
  "content": "文章内容",
  "cover_image": "/images/cover.jpg"
}

Response:
{
  "score": 85,
  "grade": {
    "level": "B",
    "label": "良好",
    "color": "#67C23A"
  },
  "results": {
    "title": {
      "score": 18,
      "max_score": 20,
      "issues": ["标题略短，建议30-60字符"],
      "suggestions": ["适当扩充标题内容"]
    },
    ...
  },
  "summary": {
    "total_score": 85,
    "issues": ["标题略短", "缺少外部链接"],
    "suggestions": ["扩充标题", "添加相关外链"]
  }
}
```

**计算关键词密度**
```http
POST /backend/seo-analyzer/keyword-density
Body:
{
  "content": "文章内容...",
  "keywords": "CMS系统,内容管理"
}

Response:
{
  "total_words": 1500,
  "keywords": [
    {
      "keyword": "CMS系统",
      "count": 15,
      "density": 2.5,
      "evaluation": "optimal"
    }
  ]
}
```

**自动生成SEO标题**
```http
POST /backend/seo-analyzer/generate-title
Body:
{
  "title": "如何搭建内容管理系统",
  "keywords": "CMS系统,开源"
}

Response:
{
  "seo_title": "如何搭建内容管理系统 - CMS系统开发指南"
}
```

**自动生成SEO描述**
```http
POST /backend/seo-analyzer/generate-description
Body:
{
  "content": "文章内容...",
  "keywords": "CMS系统",
  "max_length": 160
}

Response:
{
  "seo_description": "本文介绍CMS系统的搭建方法，包括环境配置、功能开发..."
}
```

**自动提取关键词**
```http
POST /backend/seo-analyzer/extract-keywords
Body:
{
  "content": "文章内容...",
  "count": 5
}

Response:
{
  "keywords": "CMS系统,内容管理,数据库,PHP,Vue"
}
```

**一键优化文章**
```http
POST /backend/seo-analyzer/auto-optimize/:id

Response:
{
  "id": 1,
  "title": "文章标题",
  "seo_title": "自动生成的SEO标题",
  "seo_description": "自动生成的SEO描述",
  "seo_keywords": "自动提取的关键词"
}
```

**批量分析文章**
```http
POST /backend/seo-analyzer/batch-analyze
Body:
{
  "ids": [1, 2, 3, 4, 5]
}

Response:
[
  {
    "id": 1,
    "title": "文章标题",
    "score": 85,
    "grade": "B"
  },
  ...
]
```

**生成Sitemap**
```http
POST /backend/seo-sitemap/generate
Body:
{
  "type": "all"  // all, main, images, news, index
}

Response:
{
  "main": {
    "success": true,
    "file": "/path/to/sitemap.xml",
    "count": 150
  },
  "images": {
    "success": true,
    "file": "/path/to/sitemap-images.xml",
    "count": 80
  },
  "news": {
    "success": true,
    "file": "/path/to/sitemap-news.xml",
    "count": 5
  },
  "index": {
    "success": true,
    "file": "/path/to/sitemap-index.xml",
    "count": 3
  }
}
```

**Ping搜索引擎**
```http
POST /backend/seo-sitemap/ping
Body:
{
  "sitemap_url": "https://example.com/sitemap-index.xml"
}

Response:
{
  "google": {
    "success": true,
    "code": 200,
    "message": "Success"
  },
  "bing": {
    "success": true,
    "code": 200,
    "message": "Success"
  },
  "baidu": {
    "success": true,
    "message": "提交成功"
  }
}
```

#### 6.7 使用示例

**场景1：分析并优化单篇文章**
```php
// 1. 分析文章SEO
$analysis = SeoAnalyzer::analyze([
    'title' => $article->title,
    'content' => $article->content,
    'seo_title' => $article->seo_title,
    // ...
]);

// 2. 查看评分和建议
echo "SEO分数: {$analysis['score']}\n";
echo "等级: {$analysis['grade']['level']}\n";
print_r($analysis['summary']['suggestions']);

// 3. 自动优化（如果分数低于80）
if ($analysis['score'] < 80) {
    $article->seo_title = SeoAnalyzer::generateSeoTitle($article->title);
    $article->seo_description = SeoAnalyzer::generateSeoDescription($article->content);
    $article->seo_keywords = SeoAnalyzer::extractKeywords($article->content);
    $article->save();
}
```

**场景2：关键词密度优化**
```php
// 计算关键词密度
$density = SeoAnalyzer::calculateKeywordDensity(
    $article->content,
    ['CMS系统', '内容管理']
);

// 检查是否在最优范围
foreach ($density['keywords'] as $kw) {
    if ($kw['evaluation'] === 'too_low') {
        echo "关键词 '{$kw['keyword']}' 密度过低，建议增加\n";
    } elseif ($kw['evaluation'] === 'too_high') {
        echo "关键词 '{$kw['keyword']}' 密度过高，建议减少\n";
    }
}
```

**场景3：批量生成Sitemap并Ping**
```php
// 生成所有类型的Sitemap
$generator = new EnhancedSitemapGenerator();
$results = $generator->generateAll();

// Ping所有搜索引擎
$pingResults = $generator->pingSearchEngines('https://example.com/sitemap-index.xml');

// 检查结果
foreach ($pingResults as $engine => $result) {
    if ($result['success']) {
        echo "{$engine}: Ping成功\n";
    } else {
        echo "{$engine}: Ping失败 - {$result['message']}\n";
    }
}
```

**场景4：定时任务自动优化**
```php
// 每周自动优化所有文章
$articles = Article::where('status', 1)
    ->whereNull('seo_title')
    ->limit(50)
    ->select();

foreach ($articles as $article) {
    $article->seo_title = SeoAnalyzer::generateSeoTitle($article->title);
    $article->seo_description = SeoAnalyzer::generateSeoDescription($article->content);
    $article->seo_keywords = SeoAnalyzer::extractKeywords($article->content);
    $article->save();

    echo "文章 #{$article->id} 已优化\n";
}
```

#### 6.8 最佳实践

**1. SEO评分优化策略**
```
E级（<60分）→ 立即优化，优先级最高
D级（60-69分）→ 需要优化
C级（70-79分）→ 可以优化
B级（80-89分）→ 保持监控
A级（90-100分）→ 优秀，继续保持
```

**2. 关键词密度建议**
- 主关键词：2-3%
- 次关键词：1-2%
- 相关词：0.5-1%
- 避免关键词堆砌

**3. Sitemap更新策略**
```
标准Sitemap：每日更新
图片Sitemap：每周更新
新闻Sitemap：每小时更新（新闻站）
Sitemap索引：与上述一起更新
```

**4. 搜索引擎Ping时机**
- 发布新文章后
- 更新重要内容后
- 生成新Sitemap后
- 避免频繁Ping（每天不超过1次）

**5. 性能优化**
```php
// 使用队列异步处理
Queue::push(function() use ($article) {
    SeoAnalyzer::analyze($article->toArray());
});

// 批量处理减少数据库查询
$articles = Article::limit(100)->select();
foreach ($articles as $article) {
    // 处理...
}
```

---

## 部署配置

### 1. Nginx配置

**处理重定向**
```nginx
location / {
    # 先尝试访问文件
    try_files $uri $uri/ /index.php?$query_string;

    # 在PHP中处理重定向
}
```

**处理404错误**
```nginx
error_page 404 /index.php;
```

### 2. Apache配置

**.htaccess**
```apache
RewriteEngine On

# 处理404错误
ErrorDocument 404 /index.php

# URL重写
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [L]
```

### 3. PHP配置

**在应用入口处理重定向**
```php
// public/index.php
use app\model\SeoRedirect;

// 检查是否有匹配的重定向规则
$currentUrl = $_SERVER['REQUEST_URI'];
$rule = SeoRedirect::findMatchingRule($currentUrl);

if ($rule && $rule->is_enabled) {
    $targetUrl = $rule->applyRedirect($currentUrl);
    $rule->incrementHitCount();

    http_response_code($rule->redirect_type);
    header("Location: $targetUrl");
    exit;
}

// 继续正常的请求处理
```

---

## 性能优化建议

### 1. 重定向规则缓存

```php
// 使用缓存减少数据库查询
use think\facade\Cache;

$cacheKey = 'seo_redirects_' . md5($url);
$redirect = Cache::remember($cacheKey, function() use ($url) {
    return SeoRedirect::findMatchingRule($url);
}, 3600); // 缓存1小时
```

### 2. 404日志限流

```php
// 防止恶意请求导致大量404日志
use think\facade\Cache;

$cacheKey = '404_limit_' . $ip;
if (!Cache::get($cacheKey)) {
    Seo404Log::record($url, $referer, $ip, $userAgent);
    Cache::set($cacheKey, 1, 60); // 同一IP 1分钟只记录一次
}
```

### 3. 定期清理

```php
// 使用定时任务清理旧数据
// think cron

// 清理90天前的已修复404日志
Seo404Log::cleanOldLogs(90);

// 清理6个月前的排名数据
SeoKeywordRanking::where('check_date', '<', date('Y-m-d', strtotime('-6 months')))->delete();
```

---

## 常见问题

### Q1: 重定向规则不生效？

**检查项：**
1. 规则是否启用
2. 匹配类型是否正确
3. URL格式是否正确（需以 / 开头）
4. 正则表达式是否有语法错误
5. 是否有优先级更高的规则

**调试方法：**
使用"测试URL"功能检查匹配结果

### Q2: 404日志没有记录？

**检查项：**
1. 是否在异常处理器中添加了记录代码
2. 数据库连接是否正常
3. 是否被限流（同一IP短时间内多次404）

### Q3: Robots.txt文件无法生成？

**检查项：**
1. 网站根目录是否有写权限
2. 路径配置是否正确
3. PHP是否有文件写入权限

**解决方法：**
```bash
# 设置目录权限
chmod 755 /path/to/public
chmod 666 /path/to/public/robots.txt
```

### Q4: 社交分享时图片不显示？

**检查项：**
1. og_image 是否为完整URL（包含域名）
2. 图片是否可公开访问
3. 图片大小是否符合要求（建议1200x630）

**调试工具：**
- Facebook分享调试器：https://developers.facebook.com/tools/debug/
- Twitter卡片验证器：https://cards-dev.twitter.com/validator

---

## 总结

SEO优化功能提供了全面的搜索引擎优化工具，通过合理使用这些功能，可以：

1. **提升搜索排名**：通过正确的元数据配置和智能SEO分析
2. **改善用户体验**：及时修复404错误
3. **优化网站结构**：使用301重定向引导流量
4. **控制爬虫行为**：通过Robots.txt合理分配资源
5. **监控SEO效果**：追踪关键词排名变化
6. **自动化优化**：智能生成SEO标题、描述和关键词
7. **增强可见性**：通过多类型Sitemap提升索引效率

**最佳实践：**
- 定期检查404错误日志
- 及时更新重定向规则
- 根据环境选择合适的Robots配置
- 为所有公开内容设置SEO元数据
- 监控关键词排名变化
- 定期分析文章SEO评分，针对性优化
- 保持关键词密度在最优范围（1-3%）
- 定期生成并Ping Sitemap到搜索引擎
- 对低分文章使用自动优化功能

---

**创建时间**: 2025-10-19
**版本**: 1.0
**维护**: CMS开发团队
