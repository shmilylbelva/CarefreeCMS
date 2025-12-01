# CMS系统架构文档

## 目录

1. [系统概述](#系统概述)
2. [整体架构](#整体架构)
3. [技术栈](#技术栈)
4. [核心模块](#核心模块)
5. [数据库设计](#数据库设计)
6. [多站点架构](#多站点架构)
7. [缓存架构](#缓存架构)
8. [安全架构](#安全架构)
9. [扩展性设计](#扩展性设计)

---

## 系统概述

本CMS（内容管理系统）是一个现代化的、支持多站点的内容管理平台，采用前后端分离架构，提供灵活的内容管理和发布功能。

### 核心特性

- ✅ **前后端分离**：前端Vue3 + 后端ThinkPHP8
- ✅ **AI文章生成** ⭐ v2.0：集成106个AI模型，18个提供商
- ✅ **多站点支持** ⭐ v2.0：单数据库管理多个独立站点，数据自动隔离
- ✅ **模板包系统** ⭐ v2.0：多模板包管理，站点级模板选择和优先级解析
- ✅ **媒体库系统** ⭐ v2.0：文件去重、缩略图、水印、在线编辑
- ✅ **RESTful API**：标准化的API设计
- ✅ **权限管理**：基于RBAC的细粒度权限控制
- ✅ **双层缓存**：前端LocalStorage + 后端Redis
- ✅ **模块化设计**：高内聚低耦合的模块结构
- ✅ **可扩展性**：插件化、钩子系统（规划中）

---

## 整体架构

### 系统架构图

```
┌─────────────────────────────────────────────────────────────┐
│                        用户层                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Web浏览器   │  │   移动应用    │  │   第三方系统  │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
                            ↓  HTTPS
┌─────────────────────────────────────────────────────────────┐
│                      Nginx (反向代理)                         │
└─────────────────────────────────────────────────────────────┘
        ↓                                         ↓
┌──────────────────┐                    ┌──────────────────┐
│   前端静态资源      │                    │   后端API服务     │
│   (Vue 3 + Vite)  │                    │  (ThinkPHP 8)    │
│                   │                    │                  │
│  ├── 路由管理      │                    │  ├── 控制器层     │
│  ├── 状态管理      │                    │  ├── 服务层       │
│  ├── 组件库        │                    │  ├── 模型层       │
│  └── 本地缓存      │                    │  └── 中间件       │
└──────────────────┘                    └──────────────────┘
                                                  ↓
                            ┌────────────────────┴────────────────────┐
                            ↓                                         ↓
                    ┌──────────────┐                        ┌──────────────┐
                    │   MySQL      │                        │    Redis     │
                    │  (主数据库)   │                        │   (缓存)      │
                    └──────────────┘                        └──────────────┘
```

### 请求流程

```
1. 用户请求 → Nginx
2. Nginx → 静态资源 或 API路由
3. API请求 → 中间件链
   ├── CORS处理
   ├── 认证验证
   ├── 权限检查
   ├── 站点识别
   └── 查询日志
4. 控制器 → 服务层 → 模型层
5. 数据库查询（带缓存）
6. 响应封装 → 返回JSON
```

---

## 技术栈

### 后端技术栈

| 技术 | 版本 | 用途 |
|------|------|------|
| PHP | 8.0+ | 核心语言 |
| ThinkPHP | 8.0 | 后端框架 |
| MySQL | 8.0 | 关系数据库 |
| Redis | 6.0+ | 缓存、会话 |
| Composer | 2.0+ | 依赖管理 |
| JWT | - | 身份认证 |
| PHPUnit | - | 单元测试 |

### 前端技术栈

| 技术 | 版本 | 用途 |
|------|------|------|
| Vue | 3.3+ | 前端框架 |
| Vite | 4.0+ | 构建工具 |
| Element Plus | 2.3+ | UI组件库 |
| Pinia | 2.1+ | 状态管理 |
| Vue Router | 4.2+ | 路由管理 |
| Axios | 1.4+ | HTTP客户端 |
| Vitest | - | 单元测试 |

---

## 核心模块

### 后端模块架构

```
backend/
├── app/
│   ├── controller/          # 控制器层（处理HTTP请求）
│   │   ├── api/             # API控制器
│   │   │   ├── Article.php  # 文章管理
│   │   │   ├── Category.php # 分类管理
│   │   │   ├── Tag.php      # 标签管理
│   │   │   ├── User.php     # 用户管理
│   │   │   ├── Auth.php     # 认证相关
│   │   │   ├── AiConfigController.php        # ⭐ v2.0 AI配置管理
│   │   │   ├── AiArticleTaskController.php   # ⭐ v2.0 AI文章生成任务
│   │   │   ├── SiteController.php            # ⭐ v2.0 多站点管理
│   │   │   ├── TemplatePackageController.php # ⭐ v2.0 模板包管理
│   │   │   ├── MediaController.php           # ⭐ v2.0 媒体库管理（全面升级）
│   │   │   ├── MediaThumbnailController.php  # ⭐ v2.0 缩略图管理
│   │   │   └── MediaWatermarkController.php  # ⭐ v2.0 水印管理
│   │   └── admin/           # 后台控制器
│   │
│   ├── model/               # 模型层（数据访问）
│   │   ├── SiteModel.php    # 站点基类（支持多站点）
│   │   ├── Article.php      # 文章模型
│   │   ├── Category.php     # 分类模型
│   │   ├── AiProvider.php   # ⭐ v2.0 AI提供商模型（18个）
│   │   ├── AiModel.php      # ⭐ v2.0 AI模型库（106个）
│   │   ├── AiConfig.php     # ⭐ v2.0 AI配置模型
│   │   ├── Site.php         # ⭐ v2.0 站点模型
│   │   ├── TemplatePackage.php  # ⭐ v2.0 模板包模型
│   │   ├── MediaLibrary.php     # ⭐ v2.0 媒体库模型（逻辑）
│   │   ├── MediaFile.php        # ⭐ v2.0 媒体文件模型（物理，去重）
│   │   └── ...
│   │
│   ├── service/             # 服务层（业务逻辑）
│   │   ├── ArticleService.php
│   │   ├── CacheManager.php
│   │   ├── QueryAnalyzer.php
│   │   ├── TemplateResolver.php   # ⭐ v2.0 模板解析服务（优先级）
│   │   ├── MediaUsageService.php  # ⭐ v2.0 媒体使用追踪服务
│   │   ├── AiService.php          # ⭐ v2.0 AI服务集成
│   │   └── ...
│   │
│   ├── middleware/          # 中间件（请求处理）
│   │   ├── Auth.php         # 认证中间件
│   │   ├── Permission.php   # 权限中间件
│   │   ├── MultiSite.php    # ⭐ v2.0 多站点中间件（站点识别）
│   │   └── QueryLogger.php  # 查询日志中间件
│   │
│   ├── traits/              # 可复用特征
│   │   ├── Cacheable.php    # 缓存trait
│   │   ├── SiteScoped.php   # 站点作用域
│   │   └── QueryFilterTrait.php
│   │
│   ├── common/              # 公共类
│   │   ├── ErrorCode.php    # 错误代码
│   │   ├── Response.php     # 响应封装
│   │   ├── Jwt.php          # JWT工具
│   │   └── Logger.php       # 日志工具
│   │
│   └── exception/           # 自定义异常
│       └── BusinessException.php
```

### 前端模块架构

```
frontend/src/
├── api/                     # API接口层
│   ├── request.js           # 请求封装
│   ├── article.js           # 文章API
│   ├── category.js          # 分类API
│   ├── aiConfig.js          # ⭐ v2.0 AI配置API
│   ├── aiArticleTask.js     # ⭐ v2.0 AI文章生成API
│   ├── site.js              # ⭐ v2.0 站点管理API
│   ├── templatePackage.js   # ⭐ v2.0 模板包API
│   ├── media.js             # ⭐ v2.0 媒体库API（全面升级）
│   └── ...
│
├── router/                  # 路由配置
│   ├── index.js             # 主路由
│   └── modules/             # 模块路由
│
├── store/                   # 状态管理
│   ├── user.js              # 用户状态
│   ├── cache.js             # 缓存状态
│   ├── site.js              # ⭐ v2.0 站点状态
│   └── ...
│
├── views/                   # 页面组件
│   ├── article/             # 文章管理
│   │   ├── List.vue
│   │   └── Edit.vue
│   ├── category/            # 分类管理
│   ├── aiConfig/            # ⭐ v2.0 AI配置管理
│   │   └── List.vue
│   ├── aiArticleTask/       # ⭐ v2.0 AI文章生成任务
│   │   ├── List.vue
│   │   └── Create.vue
│   ├── site/                # ⭐ v2.0 站点管理
│   │   ├── List.vue
│   │   └── Edit.vue
│   ├── templatePackage/     # ⭐ v2.0 模板包管理
│   │   └── List.vue
│   ├── media/               # ⭐ v2.0 媒体库管理（全面升级）
│   │   ├── List.vue
│   │   ├── Upload.vue
│   │   └── Edit.vue
│   └── ...
│
├── components/              # 公共组件
│   ├── Layout/              # 布局组件
│   ├── Form/                # 表单组件
│   └── Table/               # 表格组件
│
└── utils/                   # 工具函数
    ├── localCache.js        # 本地缓存
    ├── auth.js              # 认证工具
    └── validate.js          # 验证工具
```

---

## 数据库设计

### ER图（简化版）

```
┌─────────────┐
│   sites     │ 站点表
└─────────────┘
      │ 1
      │
      │ N
┌─────────────┐      N      ┌─────────────┐
│  articles   │ ──────────  │  relations  │ 关系表
└─────────────┘             └─────────────┘
      │                            │
      │ N                          │ N
      │                            │
┌─────────────┐              ┌─────────────┐
│ categories  │              │    tags     │
└─────────────┘              └─────────────┘
```

### 核心表结构

#### 站点表 (sites)

```sql
CREATE TABLE sites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_code VARCHAR(50) UNIQUE NOT NULL,     -- 站点代码
    site_name VARCHAR(100) NOT NULL,            -- 站点名称
    site_type TINYINT DEFAULT 1,                -- 站点类型（1:主站,2:子站）
    site_url VARCHAR(255),                      -- 站点URL
    status TINYINT DEFAULT 1,                   -- 状态（0:禁用,1:启用,2:维护）
    config JSON,                                 -- 站点配置
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (site_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 文章表 (articles)

```sql
CREATE TABLE articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_id INT UNSIGNED NOT NULL,              -- 站点ID
    user_id INT UNSIGNED NOT NULL,              -- 作者ID
    category_id INT UNSIGNED,                   -- 主分类ID
    title VARCHAR(255) NOT NULL,                -- 标题
    slug VARCHAR(255),                          -- URL别名
    summary TEXT,                               -- 摘要
    content LONGTEXT,                           -- 内容
    cover_image VARCHAR(255),                   -- 封面图
    status TINYINT DEFAULT 0,                   -- 状态（0:草稿,1:已发布,2:待审核,3:已下线）
    is_top TINYINT DEFAULT 0,                   -- 是否置顶
    is_recommend TINYINT DEFAULT 0,             -- 是否推荐
    view_count INT UNSIGNED DEFAULT 0,          -- 浏览量
    like_count INT UNSIGNED DEFAULT 0,          -- 点赞数
    comment_count INT UNSIGNED DEFAULT 0,       -- 评论数
    published_at TIMESTAMP NULL,                 -- 发布时间
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,                   -- 软删除
    INDEX idx_site_id (site_id),
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_site_status_created (site_id, status, created_at),
    FULLTEXT INDEX ft_title_content (title, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 关系表 (relations) - 统一管理多对多关系

```sql
CREATE TABLE relations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_type VARCHAR(50) NOT NULL,           -- 源类型（article,topic等）
    source_id INT UNSIGNED NOT NULL,            -- 源ID
    target_type VARCHAR(50) NOT NULL,           -- 目标类型（tag,category等）
    target_id INT UNSIGNED NOT NULL,            -- 目标ID
    relation_type VARCHAR(50) DEFAULT 'default', -- 关系类型（main,sub等）
    sort INT DEFAULT 0,                         -- 排序
    extra JSON,                                  -- 扩展信息
    site_id INT UNSIGNED NOT NULL,              -- 站点ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_source (source_type, source_id),
    INDEX idx_target (target_type, target_id),
    INDEX idx_site_id (site_id),
    UNIQUE INDEX idx_relation (source_type, source_id, target_type, target_id, relation_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

### v2.0.0 新增表结构 ⭐

#### AI系统表（6张）

**ai_providers** - AI提供商表（18个提供商）
```sql
- id, provider_code, provider_name, api_endpoint
- status, features, created_at, updated_at
```

**ai_models** - AI模型库表（106个模型）
```sql
- id, provider_id, model_code, model_name
- max_tokens, context_window, supports_streaming
- pricing_input, pricing_output, status
```

**ai_configs** - AI配置表
```sql
- id, site_id, name, provider_id, model_id
- api_key, temperature, max_tokens, is_default
```

**ai_article_tasks** - AI文章生成任务表
```sql
- id, site_id, title, topic, category_id
- ai_config_id, total_count, generated_count, settings
```

**ai_article_records** - AI文章生成记录表
**ai_prompt_templates** - AI提示词模板表

---

#### 多站点表（3张）

**sites** - 站点表（已列上方）

**site_template_config** - 站点模板配置表
```sql
- id, site_id, package_id, config_data
- created_at, updated_at
```

**site_template_overrides** - 站点模板覆盖表
```sql
- id, site_id, template_name, template_content
- created_at, updated_at
```

---

#### 模板包表（2张）

**template_packages** - 模板包表
```sql
- id, package_name, package_code, version
- description, author, config, status
```

**templates** - 模板文件表
```sql
- id, package_id, template_name, template_type
- template_content, is_default
```

---

#### 媒体库表（8张） ⭐ 全面升级

**media_files** - 媒体文件表（物理，去重）
```sql
- id, file_name, file_path, file_size
- file_hash (SHA256), mime_type, created_at
```

**media_library** - 媒体库表（逻辑引用）
```sql
- id, site_id, file_id, title, description
- alt_text, is_public, created_at
```

**media_categories** - 媒体分类表
**media_tags** - 媒体标签表
**media_thumbnail_presets** - 缩略图预设表（9种预设）
**media_watermark_presets** - 水印预设表（3种模式）
**media_usage_records** - 媒体使用记录表
**media_operation_logs** - 媒体操作日志表

---

### 数据库设计原则

1. **范式化设计**: 遵循第三范式，减少数据冗余
2. **字段类型选择**:
   - ID使用`INT UNSIGNED`
   - 布尔值使用`TINYINT(1)`
   - 时间使用`TIMESTAMP`
3. **索引策略**:
   - 为外键添加索引
   - 为常用查询字段添加索引
   - 复合索引按选择性排序
4. **字符集**: 使用`utf8mb4`支持emoji和特殊字符
5. **软删除**: 重要数据使用`deleted_at`字段实现软删除

---

## 多站点架构

### 架构设计

采用**共享数据库、数据隔离**的多站点架构：

```
┌─────────────────────────────────────────────────────────┐
│                    MySQL数据库                           │
│                                                          │
│  ┌────────────┐  ┌────────────┐  ┌────────────┐       │
│  │  site_id=1 │  │  site_id=2 │  │  site_id=3 │       │
│  │  (主站)     │  │  (子站A)   │  │  (子站B)   │       │
│  │            │  │            │  │            │       │
│  │  articles  │  │  articles  │  │  articles  │       │
│  │categories  │  │categories  │  │categories  │       │
│  │  tags      │  │  tags      │  │  tags      │       │
│  └────────────┘  └────────────┘  └────────────┘       │
└─────────────────────────────────────────────────────────┘
```

### 站点识别流程

```
1. 请求到达 → MultiSite中间件
2. 识别站点（通过域名/subdomain/参数）
3. 设置站点上下文
   ├── app()->bind('current_site_id', $siteId)
   └── app()->bind('current_site', $site)
4. 模型查询自动添加 site_id 过滤（SiteScoped trait）
5. 返回当前站点的数据
```

### 站点隔离机制

使用 **SiteScoped Trait** 实现自动站点过滤：

```php
// 模型定义
class Article extends SiteModel
{
    use SiteScoped;  // 自动添加站点过滤
}

// 查询示例
Article::all();                    // 自动限制在当前站点
Article::forSite(2)->get();        // 查询指定站点
Article::forAllSites()->get();     // 查询所有站点（明确意图）
```

---

## 缓存架构

### 双层缓存设计

```
┌──────────────┐
│  前端请求     │
└──────────────┘
       ↓
┌──────────────┐
│ LocalStorage │ ← 前端缓存（1层）
│  TTL: 10-30m │
└──────────────┘
       ↓ 未命中
┌──────────────┐
│  后端API     │
└──────────────┘
       ↓
┌──────────────┐
│ Redis缓存    │ ← 后端缓存（2层）
│  TTL: 30-60m │
└──────────────┘
       ↓ 未命中
┌──────────────┐
│  MySQL数据库 │
└──────────────┘
```

### 缓存策略

| 数据类型 | 前端缓存 | 后端缓存 | 更新策略 |
|---------|----------|----------|----------|
| 分类树 | 30分钟 | 1小时 | 写入时清除 |
| 标签列表 | 30分钟 | 1小时 | 写入时清除 |
| 文章列表 | 10分钟 | 30分钟 | 写入时清除 |
| 站点配置 | 10分钟 | 永久 | 手动清除 |

### 缓存使用

```php
// 使用Cacheable trait
class Category extends SiteModel
{
    use Cacheable;

    protected static $cacheTag = 'categories';
    protected static $cacheExpire = 3600;
}

// 自动缓存
$categories = Category::getCachedList('tree', function() {
    return Category::buildTree();
});

// 清除缓存（模型事件自动触发）
Category::create($data);  // 自动清除categories标签的缓存
```

---

## 安全架构

### 认证与授权

```
┌─────────────┐
│  用户登录    │
└─────────────┘
       ↓
┌─────────────┐
│ JWT Token   │ ← 生成JWT token
│ (有效期2小时)│
└─────────────┘
       ↓
┌─────────────┐
│  请求携带    │ ← Authorization: Bearer <token>
│  Token      │
└─────────────┘
       ↓
┌─────────────┐
│ Auth中间件  │ ← 验证token有效性
└─────────────┘
       ↓
┌─────────────┐
│Permission   │ ← 检查用户权限
│ 中间件      │
└─────────────┘
       ↓
┌─────────────┐
│ 业务处理    │
└─────────────┘
```

### 安全防护措施

1. **SQL注入防护**: 使用ORM参数绑定
2. **XSS防护**: 输出转义、CSP头
3. **CSRF防护**: Token验证
4. **文件上传安全**: 类型验证、大小限制、随机文件名
5. **密码安全**: Bcrypt加密
6. **Rate Limiting**: API请求频率限制
7. **HTTPS**: 强制使用SSL/TLS

---

## 扩展性设计

### 插件化架构（规划中）

```
backend/
├── plugins/
│   ├── seo/                 # SEO优化插件
│   │   ├── Plugin.php       # 插件主类
│   │   ├── config.php       # 配置文件
│   │   └── hooks.php        # 钩子定义
│   ├── sitemap/             # 站点地图插件
│   └── wechat/              # 微信集成插件
```

### 钩子系统（规划中）

```php
// 注册钩子
Hook::listen('article.created', function($article) {
    // 文章创建后的操作
    NotificationService::notify($article);
});

// 触发钩子
Hook::trigger('article.created', $article);
```

### 模块化设计

- **控制器**：负责HTTP请求处理
- **服务层**：负责业务逻辑
- **模型层**：负责数据访问
- **中间件**：负责请求过滤和处理
- **Trait**：提供可复用功能

---

## 性能优化策略

### 数据库优化

1. **索引优化**: 为常用查询字段添加索引
2. **查询优化**: 使用预加载避免N+1查询
3. **连接池**: 复用数据库连接
4. **慢查询监控**: QueryLogger中间件

### 缓存优化

1. **双层缓存**: 前端+后端缓存
2. **缓存预热**: 提前加载热点数据
3. **缓存标签**: 批量清除相关缓存

### 代码优化

1. **Composer优化**: `--optimize-autoloader`
2. **OPcache**: PHP字节码缓存
3. **代码分割**: 前端按需加载

---

**最后更新**: 2025-11-26
**维护者**: CMS项目团队
