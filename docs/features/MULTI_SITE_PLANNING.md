# CarefreeCMS 多城市/多站点功能规划

**规划日期**: 2025-11-08
**状态**: 📋 规划阶段（未开发）
**优先级**: ⭐⭐⭐ 高优先级

---

## 📋 目录

1. [功能概述](#功能概述)
2. [应用场景](#应用场景)
3. [技术方案对比](#技术方案对比)
4. [推荐方案](#推荐方案)
5. [数据库设计](#数据库设计)
6. [功能模块设计](#功能模块设计)
7. [实施路线图](#实施路线图)
8. [技术难点](#技术难点)
9. [参考案例](#参考案例)

---

## 功能概述

### 什么是多城市/多站点？

多城市/多站点功能允许在一套CMS系统中管理多个独立或半独立的站点，每个站点可以有：

- **独立域名**：如 beijing.example.com、shanghai.example.com
- **独立内容**：各站点有自己的文章、分类、标签等
- **独立配置**：各站点可自定义LOGO、SEO、联系方式等
- **独立模板**：各站点可使用不同的模板主题
- **统一管理**：通过一个后台管理所有站点

### 核心价值

1. **降低成本**：一套系统管理多个站点，节省服务器和维护成本
2. **提高效率**：统一的管理后台，减少重复操作
3. **数据共享**：站点间可选择性共享内容、用户等数据
4. **品牌扩展**：方便企业在不同地区/业务线部署站点

---

## 应用场景

### 场景一：本地生活服务平台

**案例**：58同城、赶集网模式

- **需求**：为不同城市提供本地化服务
- **特点**：
  - 每个城市独立域名（如 bj.example.com、sh.example.com）
  - 内容完全独立（北京的租房信息只在北京站显示）
  - 用户可跨城市访问，但数据按城市隔离
  - 统一的用户体系

**示例结构**：
```
北京站 (beijing.example.com)
├── 本地新闻
├── 租房信息
├── 招聘信息
└── 本地商家

上海站 (shanghai.example.com)
├── 本地新闻
├── 租房信息
├── 招聘信息
└── 本地商家
```

### 场景二：连锁企业官网

**案例**：星巴克、麦当劳、连锁酒店

- **需求**：为各地分店/分公司建站
- **特点**：
  - 公司新闻、产品信息全局共享
  - 各分站展示本地门店、优惠活动
  - 统一品牌形象，局部差异化
  - 总部统一管理内容发布

**示例结构**：
```
总站 (www.example.com)
├── 公司简介（全局）
├── 产品介绍（全局）
└── 新闻中心（全局）

北京分站 (beijing.example.com)
├── 继承总站内容
├── 北京门店列表
├── 北京活动
└── 北京联系方式

上海分站 (shanghai.example.com)
├── 继承总站内容
├── 上海门店列表
├── 上海活动
└── 上海联系方式
```

### 场景三：新闻媒体矩阵

**案例**：澎湃新闻、界面新闻

- **需求**：运营多个垂直领域站点
- **特点**：
  - 财经、科技、娱乐等独立子站
  - 内容可选择性共享
  - 统一的采编系统
  - 统一的广告系统

**示例结构**：
```
主站 (www.example.com)
├── 综合新闻

财经频道 (finance.example.com)
├── 财经新闻
└── 可引用主站热点

科技频道 (tech.example.com)
├── 科技新闻
└── 可引用主站热点
```

### 场景四：SaaS多租户

**案例**：WordPress.com、Shopify

- **需求**：为不同客户提供独立站点
- **特点**：
  - 完全数据隔离
  - 客户自主管理内容
  - 统一的计费系统
  - 资源配额管理

---

## 技术方案对比

### 方案一：共享数据库 + 站点ID隔离

#### 架构设计

```
┌─────────────────────────────────────┐
│         统一数据库                   │
│  ┌─────────────────────────────┐   │
│  │  articles 表                 │   │
│  ├─────────┬──────────┬────────┤   │
│  │ site_id │  title   │ content│   │
│  │    1    │  文章A   │  ...   │   │
│  │    2    │  文章B   │  ...   │   │
│  │    1    │  文章C   │  ...   │   │
│  └─────────┴──────────┴────────┘   │
└─────────────────────────────────────┘
```

#### 优点
✅ 实现简单，改动最小
✅ 数据统一管理，便于统计分析
✅ 可灵活实现内容共享
✅ 服务器资源消耗低

#### 缺点
❌ 数据量大时查询性能下降
❌ 数据安全性相对较低（需要严格的权限控制）
❌ 无法独立备份某个站点
❌ 索引膨胀，影响性能

#### 适用场景
- 站点数量：< 100个
- 数据量：中小型（< 1000万条）
- 数据隔离要求：中等
- 内容共享需求：高

---

### 方案二：独立数据库

#### 架构设计

```
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│  DB_site1    │  │  DB_site2    │  │  DB_site3    │
│ ┌──────────┐ │  │ ┌──────────┐ │  │ ┌──────────┐ │
│ │ articles │ │  │ │ articles │ │  │ │ articles │ │
│ │ users    │ │  │ │ users    │ │  │ │ users    │ │
│ └──────────┘ │  │ └──────────┘ │  │ └──────────┘ │
└──────────────┘  └──────────────┘  └──────────────┘
         ↓                ↓                ↓
    ┌────────────────────────────────────────┐
    │         主控数据库 (Master DB)          │
    │  - 站点列表                            │
    │  - 数据库连接配置                       │
    │  - 全局用户（超级管理员）               │
    └────────────────────────────────────────┘
```

#### 优点
✅ 数据完全隔离，安全性高
✅ 可独立备份/恢复某个站点
✅ 单站点查询性能优秀
✅ 便于后期拆分/迁移
✅ 故障隔离（一个站点DB故障不影响其他）

#### 缺点
❌ 实现复杂度高
❌ 服务器资源消耗大
❌ 跨站点数据共享困难
❌ 数据库连接数消耗大
❌ 统计分析需要跨库查询

#### 适用场景
- 站点数量：> 100个或未来可能很多
- 数据量：大型（> 1000万条）
- 数据隔离要求：高（SaaS多租户）
- 内容共享需求：低

---

### 方案三：混合方案（推荐）

#### 架构设计

```
┌─────────────────────────────────────────────────┐
│              主数据库 (Master DB)                │
│  ┌───────────────────────────────────────────┐ │
│  │  sites 表（站点配置）                      │ │
│  │  global_users 表（全局管理员）             │ │
│  │  global_content 表（可选：共享内容）       │ │
│  └───────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
                        ↓
        ┌───────────────┼───────────────┐
        ↓               ↓               ↓
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│  站点1数据表  │ │  站点2数据表  │ │  站点3数据表  │
│ (前缀site1_) │ │ (前缀site2_) │ │ (前缀site3_) │
│              │ │              │ │              │
│ site1_       │ │ site2_       │ │ site3_       │
│ - articles   │ │ - articles   │ │ - articles   │
│ - categories │ │ - categories │ │ - categories │
│ - users      │ │ - users      │ │ - users      │
│ - ...        │ │ - ...        │ │ - ...        │
└──────────────┘ └──────────────┘ └──────────────┘
```

#### 优点
✅ 兼顾性能和隔离性
✅ 可独立导出某个站点数据
✅ 便于站点扩容（可后期迁移到独立DB）
✅ 实现难度适中
✅ 支持灵活的内容共享策略

#### 缺点
⚠️ 需要动态表前缀切换
⚠️ 备份策略需特殊处理
⚠️ 表数量会增加

#### 适用场景
- 站点数量：10-100个
- 数据量：中大型
- 数据隔离要求：高
- 内容共享需求：中等
- **最适合CarefreeCMS当前定位**

---

## 推荐方案

### 方案选择：混合方案（方案三）

基于CarefreeCMS的定位和技术栈，**推荐采用混合方案**：

**理由：**

1. **适合目标用户**
   - 中小企业、连锁企业、地方门户
   - 站点数量通常在10-50个之间

2. **技术可行性高**
   - ThinkPHP 8支持动态表前缀切换
   - 无需修改底层架构
   - 可渐进式实施

3. **平衡性能和隔离**
   - 数据隔离度高于方案一
   - 性能优于方案二
   - 便于后期优化

4. **扩展性好**
   - 初期可在同一数据库
   - 后期可将热门站点迁移到独立数据库
   - 支持垂直和水平扩展

---

## 数据库设计

### 核心表结构

#### 1. sites 站点表（主控表）

```sql
CREATE TABLE `sites` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '站点ID',
  `site_code` varchar(50) NOT NULL COMMENT '站点代码（唯一标识）',
  `site_name` varchar(100) NOT NULL COMMENT '站点名称',
  `site_type` tinyint(1) DEFAULT '1' COMMENT '站点类型：1=主站 2=子站 3=独立站',

  -- 域名配置
  `domain` varchar(255) DEFAULT NULL COMMENT '主域名',
  `domains` text COMMENT '绑定域名列表（JSON）',
  `is_default` tinyint(1) DEFAULT '0' COMMENT '是否默认站点',

  -- 数据库配置
  `db_type` tinyint(1) DEFAULT '1' COMMENT '数据类型：1=表前缀隔离 2=独立数据库',
  `db_prefix` varchar(50) DEFAULT NULL COMMENT '表前缀（如 site1_）',
  `db_config` text COMMENT '独立数据库配置（JSON）',

  -- 继承配置
  `parent_site_id` int(11) DEFAULT '0' COMMENT '父站点ID（0=无父站点）',
  `inherit_config` text COMMENT '继承配置（JSON：哪些数据继承父站）',

  -- 站点配置
  `site_config` text COMMENT '站点配置（JSON：LOGO、SEO、联系方式等）',
  `template_theme` varchar(50) DEFAULT 'default' COMMENT '模板主题',
  `language` varchar(10) DEFAULT 'zh-cn' COMMENT '默认语言',
  `timezone` varchar(50) DEFAULT 'Asia/Shanghai' COMMENT '时区',

  -- 资源限制（可选，用于SaaS）
  `quota_storage` bigint(20) DEFAULT '0' COMMENT '存储配额（字节，0=无限制）',
  `quota_articles` int(11) DEFAULT '0' COMMENT '文章数量限制（0=无限制）',
  `quota_users` int(11) DEFAULT '0' COMMENT '用户数量限制（0=无限制）',

  -- 状态
  `status` tinyint(1) DEFAULT '1' COMMENT '状态：0=禁用 1=启用 2=维护中',
  `expire_time` datetime DEFAULT NULL COMMENT '过期时间（用于SaaS）',

  -- 统计
  `visit_count` int(11) DEFAULT '0' COMMENT '访问量',
  `article_count` int(11) DEFAULT '0' COMMENT '文章数',
  `user_count` int(11) DEFAULT '0' COMMENT '用户数',

  -- 时间
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '软删除时间',

  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_site_code` (`site_code`),
  UNIQUE KEY `uk_domain` (`domain`),
  KEY `idx_status` (`status`),
  KEY `idx_site_type` (`site_type`),
  KEY `idx_parent` (`parent_site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='多站点配置表';
```

#### 2. site_admins 站点管理员关联表

```sql
CREATE TABLE `site_admins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL COMMENT '站点ID',
  `admin_id` int(11) NOT NULL COMMENT '管理员ID',
  `role_type` tinyint(1) DEFAULT '1' COMMENT '角色类型：1=站点管理员 2=站点编辑 3=站点审核',
  `permissions` text COMMENT '权限配置（JSON）',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_site_admin` (`site_id`, `admin_id`),
  KEY `idx_admin` (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点管理员关联表';
```

#### 3. site_content_share 内容共享配置表

```sql
CREATE TABLE `site_content_share` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from_site_id` int(11) NOT NULL COMMENT '源站点ID',
  `to_site_id` int(11) NOT NULL COMMENT '目标站点ID',
  `content_type` varchar(50) NOT NULL COMMENT '内容类型：article/category/tag/page',
  `content_id` int(11) NOT NULL COMMENT '内容ID',
  `share_type` tinyint(1) DEFAULT '1' COMMENT '共享类型：1=引用 2=复制',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_from` (`from_site_id`, `content_type`),
  KEY `idx_to` (`to_site_id`, `content_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='站点内容共享表';
```

#### 4. 现有表改造（添加site_id字段）

所有需要多站点隔离的表都需要添加 `site_id` 字段：

```sql
-- 示例：articles表改造
ALTER TABLE `articles`
  ADD COLUMN `site_id` int(11) DEFAULT '1' COMMENT '站点ID' AFTER `id`,
  ADD INDEX `idx_site_id` (`site_id`);

-- 需要改造的表清单：
-- articles, categories, tags, pages, comments
-- article_flags, topics, links, link_groups
-- ads, ad_positions, sliders, slider_groups
-- media, custom_fields, content_models
-- front_users（可选，看是否需要跨站点）
```

---

## 功能模块设计

### 一、站点管理模块

#### 1.1 站点列表

**功能**：
- 展示所有站点列表
- 显示站点状态、访问量、文章数等统计
- 支持搜索、筛选、排序

**界面设计**：
```
┌─────────────────────────────────────────────────┐
│  站点管理                           [+ 新建站点] │
├─────────────────────────────────────────────────┤
│  搜索：[________]  状态：[全部▼]  类型：[全部▼] │
├─────────────────────────────────────────────────┤
│  站点名称    域名           状态   文章  访问量   │
│  ○ 主站     www.ex.com     启用   1234  12.3万  │
│  ○ 北京站   bj.ex.com      启用    456   3.2万  │
│  ○ 上海站   sh.ex.com      维护     89   1.5万  │
│  ○ 测试站   test.ex.com    禁用      0       0  │
└─────────────────────────────────────────────────┘
```

#### 1.2 创建/编辑站点

**表单字段**：

**基础信息**
- 站点代码：site_code（英文，创建后不可改）
- 站点名称：site_name
- 站点类型：主站/子站/独立站
- 父站点：选择父站点（可选）

**域名配置**
- 主域名：domain
- 绑定域名：支持多个（逗号分隔）
- SSL配置：是否强制HTTPS

**数据隔离**
- 隔离方式：表前缀/独立数据库
- 表前缀：自动生成或手动设置

**继承设置**（子站点可用）
- [ ] 继承父站点导航菜单
- [ ] 继承父站点分类
- [ ] 继承父站点标签
- [ ] 继承父站点用户
- [ ] 继承父站点模板

**站点配置**
- 网站标题、关键词、描述
- LOGO、Favicon
- 联系方式
- 统计代码

**资源限制**（可选）
- 存储配额：__ GB
- 文章数量：__ 篇
- 用户数量：__ 人
- 过期时间：____-__-__

#### 1.3 站点切换

**功能**：
- 在后台顶部提供站点切换器
- 切换后只显示当前站点的内容
- 超级管理员可切换所有站点
- 普通管理员只能切换有权限的站点

**界面设计**：
```
┌─────────────────────────────────────────┐
│  当前站点：北京站 ▼                      │
│  ┌───────────────────────────────────┐  │
│  │  ○ 主站 (www.example.com)        │  │
│  │  ● 北京站 (bj.example.com)       │  │
│  │  ○ 上海站 (sh.example.com)       │  │
│  │  ○ 广州站 (gz.example.com)       │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
```

---

### 二、内容管理增强

#### 2.1 文章管理

**新增功能**：
- 选择发布到的站点（支持多选）
- 设置文章归属站点
- 跨站点引用文章

**界面设计**：
```
┌─────────────────────────────────────┐
│  发布设置                            │
├─────────────────────────────────────┤
│  归属站点：[北京站 ▼]              │
│                                     │
│  同步发布到：                        │
│  ☑ 主站                             │
│  ☑ 上海站                           │
│  ☐ 广州站                           │
│                                     │
│  发布方式：                          │
│  ○ 引用（推荐，共享一份数据）        │
│  ○ 复制（独立数据，可单独修改）      │
└─────────────────────────────────────┘
```

#### 2.2 分类/标签管理

**新增功能**：
- 全局分类：所有站点共享
- 站点分类：仅特定站点可见
- 分类继承：子站点继承父站点分类

---

### 三、用户权限管理

#### 3.1 角色定义

**全局角色**（跨站点）：
- 超级管理员：管理所有站点
- 平台运营：查看所有站点数据，部分管理权限

**站点角色**（站点级）：
- 站点管理员：管理单个站点的所有内容
- 站点编辑：编辑单个站点的内容
- 站点审核：审核单个站点的内容

#### 3.2 权限矩阵

```
┌──────────────┬────────┬────────┬──────────┬────────┐
│   功能       │ 超管   │ 运营   │站点管理员│站点编辑│
├──────────────┼────────┼────────┼──────────┼────────┤
│ 创建站点     │   ✓   │   ✗   │    ✗    │   ✗   │
│ 删除站点     │   ✓   │   ✗   │    ✗    │   ✗   │
│ 站点配置     │   ✓   │   ✗   │    ✓    │   ✗   │
│ 查看所有站点 │   ✓   │   ✓   │    ✗    │   ✗   │
│ 管理内容     │   ✓   │   部分 │    ✓    │   ✓   │
│ 用户管理     │   ✓   │   部分 │    ✓    │   ✗   │
└──────────────┴────────┴────────┴──────────┴────────┘
```

---

### 四、模板系统增强

#### 4.1 模板隔离

**方案**：
- 全局模板：所有站点共享（在 templates/global/）
- 站点模板：站点专属（在 templates/sites/{site_code}/）

**目录结构**：
```
templates/
├── global/                  # 全局模板（所有站点可用）
│   ├── default/
│   ├── official/
│   └── ...
└── sites/                   # 站点专属模板
    ├── beijing/            # 北京站模板
    │   └── custom_theme/
    ├── shanghai/           # 上海站模板
    │   └── custom_theme/
    └── ...
```

#### 4.2 模板变量增强

**新增全局变量**：
```php
{$site.id}              // 当前站点ID
{$site.name}            // 当前站点名称
{$site.domain}          // 当前站点域名
{$site.logo}            // 当前站点LOGO
{$site.config}          // 当前站点配置

// Carefree标签增强
{carefree:site field='name' /}
{carefree:site field='logo' /}
{carefree:site field='config' key='phone' /}
```

---

### 五、域名绑定与路由

#### 5.1 域名识别

**实现逻辑**：
```php
// 1. 根据访问域名识别站点
$domain = request()->domain();
$site = Site::where('domain', $domain)
    ->orWhereRaw("FIND_IN_SET('{$domain}', domains)")
    ->find();

// 2. 如果未匹配到，使用默认站点
if (!$site) {
    $site = Site::where('is_default', 1)->find();
}

// 3. 设置当前站点上下文
app()->instance('current_site', $site);

// 4. 切换数据库表前缀
if ($site->db_type == 1) {
    config(['database.connections.mysql.prefix' => $site->db_prefix]);
}
```

#### 5.2 URL生成

**需要考虑的问题**：
- 文章详情页URL：`{domain}/article/123` 还是 `{domain}/{site_code}/article/123`
- 跨站点链接生成
- 静态化文件路径

**建议方案**：
```php
// 当前站点URL（不带站点标识）
url('article/read', ['id' => 123])
// 输出：http://bj.example.com/article/123

// 指定站点URL（跨站点链接）
url('article/read', ['id' => 123], true, 'shanghai')
// 输出：http://sh.example.com/article/123
```

---

### 六、静态化增强

#### 6.1 目录结构

**方案一：按站点分目录**
```
html/
├── main/               # 主站静态文件
│   ├── index.html
│   ├── article/
│   └── ...
├── beijing/            # 北京站静态文件
│   ├── index.html
│   ├── article/
│   └── ...
└── shanghai/           # 上海站静态文件
    ├── index.html
    ├── article/
    └── ...
```

**方案二：按域名独立部署**
```
/var/www/
├── www.example.com/html/       # 主站
├── bj.example.com/html/        # 北京站
└── sh.example.com/html/        # 上海站
```

#### 6.2 生成策略

- 手动生成：管理员选择站点后生成
- 自动生成：文章发布时自动生成对应站点的静态页
- 批量生成：支持批量生成多个站点

---

## 实施路线图

### 第一阶段：基础架构（2-3周）

**目标**：搭建多站点基础框架

**任务清单**：
1. 数据库设计与实施
   - [x] 创建 sites 表
   - [ ] 创建 site_admins 表
   - [ ] 创建 site_content_share 表
   - [ ] 为现有表添加 site_id 字段

2. 核心Service开发
   - [ ] SiteService（站点管理服务）
   - [ ] SiteContextService（站点上下文服务）
   - [ ] MultiSiteMiddleware（多站点中间件）

3. 模型层改造
   - [ ] 添加 SiteScope（全局作用域）
   - [ ] 改造现有Model支持 site_id

4. 基础配置
   - [ ] 多站点配置文件
   - [ ] 站点识别逻辑
   - [ ] 表前缀动态切换

**验收标准**：
- ✅ 可以创建站点并保存到数据库
- ✅ 访问不同域名能识别不同站点
- ✅ 数据能按站点隔离

---

### 第二阶段：后台管理（2-3周）

**目标**：实现站点管理功能

**任务清单**：
1. 站点管理界面
   - [ ] 站点列表页面（frontend/src/views/site/List.vue）
   - [ ] 站点创建/编辑页面（frontend/src/views/site/Edit.vue）
   - [ ] 站点配置页面（frontend/src/views/site/Config.vue）
   - [ ] 站点统计页面（frontend/src/views/site/Statistics.vue）

2. 站点切换功能
   - [ ] 顶部站点切换器组件
   - [ ] 站点上下文状态管理（Pinia）
   - [ ] API请求自动带上当前站点ID

3. 权限管理
   - [ ] 站点管理员关联
   - [ ] 站点级权限控制
   - [ ] 管理员站点权限配置页面

**验收标准**：
- ✅ 可以在后台创建、编辑、删除站点
- ✅ 可以切换站点查看不同站点的内容
- ✅ 普通管理员只能看到有权限的站点

---

### 第三阶段：内容管理（2-3周）

**目标**：内容支持多站点

**任务清单**：
1. 文章管理增强
   - [ ] 文章发布时选择站点
   - [ ] 文章跨站点发布
   - [ ] 文章列表显示所属站点
   - [ ] 文章筛选支持按站点

2. 分类/标签管理
   - [ ] 全局分类与站点分类
   - [ ] 分类继承配置
   - [ ] 标签跨站点共享

3. 其他内容模块
   - [ ] 单页支持站点
   - [ ] 评论支持站点
   - [ ] 媒体库支持站点（可选）

**验收标准**：
- ✅ 文章可以发布到指定站点
- ✅ 不同站点看到的文章列表不同
- ✅ 支持内容跨站点引用

---

### 第四阶段：模板与静态化（1-2周）

**目标**：模板和静态化支持多站点

**任务清单**：
1. 模板系统
   - [ ] 站点模板目录隔离
   - [ ] 模板变量增强（$site）
   - [ ] Carefree标签库增强

2. 静态化
   - [ ] 静态文件按站点分目录
   - [ ] 生成时支持选择站点
   - [ ] 批量生成多站点

**验收标准**：
- ✅ 不同站点可使用不同模板
- ✅ 模板中可访问站点信息
- ✅ 可为指定站点生成静态页

---

### 第五阶段：前台功能（1周）

**目标**：前台展示支持多站点

**任务清单**：
1. 前台用户系统
   - [ ] 决定用户是否跨站点共享
   - [ ] 如果不共享，用户注册/登录需加站点标识

2. 评论系统
   - [ ] 评论数据按站点隔离

3. 其他前台功能
   - [ ] 搜索按当前站点
   - [ ] 专题按站点

**验收标准**：
- ✅ 前台访问不同域名看到不同内容
- ✅ 用户系统工作正常
- ✅ 评论等互动功能正常

---

### 第六阶段：测试与优化（1-2周）

**任务清单**：
- [ ] 单元测试
- [ ] 性能测试
- [ ] 压力测试
- [ ] 安全测试
- [ ] 用户体验优化

**验收标准**：
- ✅ 所有功能测试通过
- ✅ 性能满足要求
- ✅ 无安全漏洞
- ✅ 文档完善

---

## 技术难点

### 难点一：数据查询性能

**问题**：
- 添加 site_id 后，所有查询都需要带上站点条件
- 联合索引设计复杂
- 数据量大时性能下降

**解决方案**：
1. **全局作用域（Global Scope）**
   ```php
   // 在Model中自动添加site_id条件
   protected static function boot()
   {
       parent::boot();
       static::addGlobalScope('site', function (Builder $builder) {
           $builder->where('site_id', app('current_site')->id);
       });
   }
   ```

2. **联合索引优化**
   ```sql
   -- 重要：site_id必须放在索引第一位
   ALTER TABLE articles ADD INDEX idx_site_status (site_id, status, created_at);
   ```

3. **缓存策略**
   ```php
   // 按站点分别缓存
   Cache::tags(['site:' . $siteId])->remember('articles', ...);
   ```

---

### 难点二：跨站点内容共享

**问题**：
- 文章在多个站点显示，数据如何存储？
- 修改共享文章，如何同步？
- 删除文章，其他站点怎么办？

**解决方案**：

**方案A：引用模式（推荐）**
```php
// 只在源站点存储数据
Article::create([
    'site_id' => 1,  // 主站
    'title' => '文章标题',
    // ...
]);

// 其他站点通过关联表引用
SiteContentShare::create([
    'from_site_id' => 1,       // 主站
    'to_site_id' => 2,         // 北京站
    'content_type' => 'article',
    'content_id' => 123,
    'share_type' => 1,         // 引用
]);

// 查询时联合查询
$articles = Article::where('site_id', $currentSiteId)
    ->orWhereIn('id', function($query) use ($currentSiteId) {
        $query->select('content_id')
            ->from('site_content_share')
            ->where('to_site_id', $currentSiteId)
            ->where('content_type', 'article')
            ->where('share_type', 1);
    })
    ->get();
```

**方案B：复制模式**
```php
// 复制文章到目标站点
$originalArticle = Article::find(123);
$copiedArticle = $originalArticle->replicate();
$copiedArticle->site_id = 2;  // 北京站
$copiedArticle->save();

// 记录复制关系（用于追溯）
SiteContentShare::create([
    'from_site_id' => 1,
    'to_site_id' => 2,
    'content_type' => 'article',
    'content_id' => $copiedArticle->id,
    'share_type' => 2,  // 复制
]);
```

---

### 难点三：URL生成与路由

**问题**：
- 跨站点链接生成
- 域名切换后的链接正确性
- 静态化文件路径

**解决方案**：

1. **Helper函数封装**
   ```php
   /**
    * 生成站点URL
    * @param string $url 路由
    * @param array $params 参数
    * @param string $siteCode 站点代码（空=当前站点）
    */
   function site_url($url, $params = [], $siteCode = null)
   {
       if ($siteCode) {
           $site = Site::where('site_code', $siteCode)->find();
           $domain = $site->domain;
       } else {
           $domain = app('current_site')->domain;
       }

       return $domain . '/' . $url . '?' . http_build_query($params);
   }
   ```

2. **路由命名规范**
   ```php
   // 当前站点
   route('article.detail', ['id' => 123]);
   // 输出：http://bj.example.com/article/123

   // 指定站点
   route('article.detail', ['id' => 123, 'site' => 'shanghai']);
   // 输出：http://sh.example.com/article/123
   ```

---

### 难点四：事务与数据一致性

**问题**：
- 跨站点操作的事务处理
- 独立数据库情况下的分布式事务

**解决方案**：

1. **同一数据库**（表前缀模式）
   ```php
   DB::transaction(function () {
       // 在主站创建文章
       $article = Article::create([...]);

       // 同步到其他站点
       foreach ($sites as $site) {
           SiteContentShare::create([...]);
       }
   });
   ```

2. **独立数据库**（需要分布式事务）
   ```php
   // 使用Saga模式或最终一致性
   try {
       DB::connection('site1')->beginTransaction();
       DB::connection('site2')->beginTransaction();

       // 操作站点1
       // 操作站点2

       DB::connection('site1')->commit();
       DB::connection('site2')->commit();
   } catch (\Exception $e) {
       DB::connection('site1')->rollBack();
       DB::connection('site2')->rollBack();
   }
   ```

---

### 难点五：SEO与搜索引擎

**问题**：
- 多个站点可能有重复内容
- 搜索引擎可能认为是作弊
- 如何处理canonical标签

**解决方案**：

1. **Canonical标签**
   ```html
   <!-- 在北京站页面 -->
   <link rel="canonical" href="http://www.example.com/article/123" />

   <!-- 告诉搜索引擎主站是原始来源 -->
   ```

2. **Robots.txt差异化**
   ```
   # 主站 - 允许索引
   User-agent: *
   Allow: /

   # 子站 - 限制索引
   User-agent: *
   Disallow: /article/
   Allow: /local/  # 只索引本地化内容
   ```

3. **Sitemap分离**
   ```xml
   <!-- 主站sitemap只包含原创内容 -->
   <!-- 子站sitemap只包含本地化内容 -->
   ```

---

## 参考案例

### 案例一：WordPress Multisite

**特点**：
- 共享数据库 + 表前缀
- 站点表：wp_sites, wp_blogs
- 每个站点独立表前缀：wp_2_, wp_3_
- 支持子目录和子域名模式

**可借鉴**：
- 表结构设计
- 站点切换逻辑
- 插件/主题共享机制

---

### 案例二：58同城

**特点**：
- 完全独立的城市站点
- 城市间数据隔离
- 统一的用户系统（可跨城市）
- 首页可切换城市

**可借鉴**：
- 城市切换器设计
- 本地化内容策略
- 跨城市用户漫游

---

### 案例三：帝国CMS（EmpireCMS）

**特点**：
- 支持多终端：主站、WAP站、小程序
- 数据表前缀隔离
- 模板独立

**可借鉴**：
- 数据隔离方案
- 模板独立管理

---

## 总结与建议

### 实施建议

1. **先试点，后推广**
   - 先在1-2个站点试运行
   - 验证性能和稳定性
   - 再逐步扩展到更多站点

2. **灰度上线**
   - 新功能先在新站点使用
   - 老站点逐步迁移
   - 保证业务连续性

3. **充分测试**
   - 多站点切换测试
   - 内容共享测试
   - 权限隔离测试
   - 性能压力测试

4. **文档先行**
   - 编写详细的开发文档
   - 提供使用手册
   - 准备培训材料

### 风险控制

⚠️ **主要风险**：

1. **数据安全**
   - 站点间数据泄露
   - 权限控制失效
   - 建议：严格的权限测试 + 数据审计

2. **性能风险**
   - 站点数量增多后查询变慢
   - 建议：设置站点数量上限 + 性能监控

3. **复杂度**
   - 代码复杂度增加
   - 维护成本上升
   - 建议：充分的代码注释 + 单元测试

### 后期优化方向

1. **性能优化**
   - 热门站点独立数据库
   - Redis缓存站点配置
   - CDN加速

2. **功能扩展**
   - 站点克隆（快速复制站点）
   - 站点模板市场
   - 站点数据迁移工具

3. **商业化**
   - SaaS模式（按站点收费）
   - 资源配额管理
   - 流量统计与计费

---

**规划文档结束**

如有疑问或需要讨论，请联系开发团队。

📧 邮箱: sinma@qq.com
💬 QQ群: 113572201
