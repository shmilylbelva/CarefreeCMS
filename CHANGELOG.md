# Changelog

本文档记录了CarefreeCMS项目的所有重要更改。

格式基于 [Keep a Changelog](https://keepachangelog.com/zh-CN/1.0.0/)，
并且本项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。

---

## [2.0.0] - 2025-12-01

### 🌟 重大更新

#### 1. AI文章生成功能
- **AI模型库集成**：106个AI模型，18个主流提供商
  - OpenAI (GPT-5, GPT-5.1, O3, O4-mini)
  - Claude (Opus 4.5, Sonnet 4.5, Haiku 4.5)
  - Google (Gemini 3, Gemini 3 Pro, Gemini 3 Deep Think)
  - DeepSeek (V3.2-Exp, V3.1, R2)
  - 百度文心、智谱ChatGLM、字节豆包、月之暗面Kimi、讯飞星火、MiniMax等
- **批量生成**：支持多主题批量生成文章
- **AI配置管理**：灵活配置API密钥、模型参数（温度、最大Token等）
- **自定义参数**：文章长度（短/中/长）、写作风格（专业/轻松/创意）
- **任务管理**：启动、停止、进度追踪、生成记录
- **Token统计**：详细的Token使用记录和成本追踪
- **AI图片生成**：支持AI生成图片功能

#### 2. 多站点功能
- **数据隔离**：支持同一数据库管理多个独立站点
- **自动站点过滤**：查询自动添加 site_id 条件，无需手动干预
- **全局查询作用域**：基于 ThinkPHP 的查询事件系统
- **灵活的查询方法**：提供明确语义的方法切换不同站点查询
  - `withoutSiteScope()` - 禁用站点过滤
  - `forSite($siteId)` - 指定站点查询
  - `forCurrentSite()` - 当前站点查询
  - `forAllSites()` - 所有站点查询
- **统一的站点上下文**：通过中间件和应用容器统一管理当前站点
- **独立站点配置**：每个站点独立的SEO、模板等配置

#### 3. 模板包系统
- **多模板包管理**：支持安装和管理多个模板包
- **站点级别模板**：每个站点可选择不同模板包
- **模板优先级解析**：站点覆盖 > 站点包 > 默认包
- **配置合并**：模板包默认配置 + 站点自定义配置
- **完整模板文件**：包含14个模板文件
  - 布局框架、首页、分类页、文章详情、文章列表
  - 标签页、单页、搜索页、专题页、归档页
  - 404页面、侧边栏、主题配置、使用文档
- **模板解析服务**：TemplateResolver 统一管理模板解析逻辑
- **静态生成支持**：支持根据站点选择的模板包生成静态页面
- **批量生成所有站点**：一键生成所有站点的静态页面

#### 4. 媒体库系统全面升级
- **文件去重**：基于SHA256哈希自动去重，节省存储空间
- **分离架构**：
  - `media_files` - 物理文件表（去重）
  - `media_library` - 媒体库表（逻辑引用）
- **分类标签系统**：
  - 无限级分类管理
  - 灵活的标签系统
  - 支持多分类、多标签
- **缩略图生成**：
  - 9种内置预设（thumbnail、small、medium、large等）
  - 支持自定义尺寸和裁剪模式
  - 自动生成和按需生成
- **水印处理**：
  - 文字水印（自定义字体、颜色、位置、透明度）
  - 图片水印（自定义图片、位置、缩放、透明度）
  - 平铺水印（密集平铺模式）
- **在线图片编辑**：
  - 裁剪、旋转、翻转、缩放
  - 亮度、对比度、锐化、模糊
  - 灰度、负片等10+种操作
- **元数据提取**：自动提取EXIF信息（拍摄时间、设备、GPS等）
- **完整操作日志**：记录所有媒体操作历史
- **使用记录追踪**：追踪媒体文件在文章中的使用情况

#### 5. AI模型库全面升级
- 新增4个国际顶级AI厂商
  - Meta (Llama 4 Scout)
  - Mistral AI (Large 2.1)
  - xAI (Grok 4.1 Thinking - LMArena #1)
  - Cohere (Command A)
- 更新10个主流厂商的最新模型
  - OpenAI: GPT-5, GPT-5.1, O3, O4-mini
  - Claude: Claude Opus 4.5, Sonnet 4.5, Haiku 4.5
  - Google: Gemini 3, Gemini 3 Pro, Gemini 3 Deep Think
  - DeepSeek: V3.2-Exp, V3.1, R2
  - 百度: ERNIE 5.0 Preview, ERNIE 4.5 MoE系列
  - 智谱: GLM-4.5, GLM-Realtime
  - 字节: Doubao Seed 1.6系列, Doubao Seed Code
  - 月之暗面: Kimi K2 Thinking, Kimi Linear
  - 讯飞: Spark X1.5, Spark 4.0系列
  - MiniMax: M2, M1, MiniMax-01系列
- 模型总数达到106个，活跃模型91个
- 清理重复和过时模型36个
- 技术亮点：
  - 超长上下文：Llama 4（10M）、MiniMax-01（4M）、Gemini 3（2M）
  - 线性注意力：MiniMax Linear、Kimi Linear（速度6倍提升）
  - 原生全模态：ERNIE 5.0、Gemini 3 Pro
  - MoE架构：Llama 4（400B）、ERNIE 4.5（424B）、GLM-4.5（355B）

### 🐛 Bug修复

#### 高危Bug
- **SiteModel批量删除Bug**（影响11个控制器）
  - 修复`$model->delete()`导致的批量误删问题
  - 采用安全删除模式：`Db::name()->where('id', $id)->limit(1)->delete()`
  - 影响模块：AI配置、AI提示词、广告位、SEO日志、SEO重定向、模板管理等

#### 中等Bug
- **分类删除clearCacheTag错误**
  - 修复`method not exist:think\db\Query->clearCacheTag`错误
  - 将Cacheable trait中的clearCacheTag方法改为public
- **文章媒体使用追踪**
  - 修复文章删除时媒体使用检测不准确问题
  - 优化URL格式匹配（完整URL vs 路径）
  - 修正单URL字段的处理逻辑

#### 低危Bug
- **专题API重构**
  - 重构5个专题相关API为RESTful标准
  - 修复ThinkPHP 8 JSON字段自动转换导致的bug

### 📚 文档更新

#### 文档结构重组
- **统一文档位置** - 所有项目文档移入 `docs/` 文件夹
- **创建清晰分类** - 建立20个子目录（api、backend、frontend、features等）
- **清理无效文档** - 删除14个过期、重复和无效的文档（SQL、conf、txt、php文件）
- **完全重写索引** - 重写 `DOCUMENTATION_INDEX.md`，提供完整的文档导航
- **文档总数** - 整理142个markdown文档

#### 新增文档（详见 docs/updates/v2.0.0/）
- `AI模型库完整更新报告_2025年12月.md` - 详细的AI模型更新说明
- `AI文章生成功能说明.md` - AI文章生成功能完整文档
- `AI文章生成功能开发总结.md` - AI功能开发总结
- `SiteModel批量删除bug全面修复报告.md` - 批量删除bug修复记录
- `分类删除clearCacheTag错误修复报告.md` - 缓存清理修复
- `文章媒体使用追踪修复报告.md` - 媒体追踪修复
- `专题API重构为RESTful规范报告.md` - API重构说明
- `2025年12月系统更新总结.md` - 系统更新总结
- `文档整理总结.md` - 文档整理详细记录

#### 新增功能文档
- `MULTI_SITE_GUIDE.md` - 多站点功能使用指南
- `MEDIA_LIBRARY_API.md` - 媒体库系统API文档
- `docs/features/template/template_package_system_final_summary.md` - 模板包系统完整实施总结
- `docs/backend/TEMPLATE_PACKAGE_GUIDE.md` - 模板包开发指南

#### 更新文档
- `README.md` - 全面更新v2.0.0所有新功能，更新技术栈、功能模块、数据库设计、API文档
- `CHANGELOG.md` - 完整记录v2.0.0所有更新内容（本文件）
- `DOCUMENTATION_INDEX.md` - 完全重写，包含所有142个文档的详细索引

### 🔧 技术改进

#### 数据库层
- **表结构扩展**：从13张表扩展到92张表
- **多站点支持**：核心表添加 site_id 字段和索引
- **文件去重设计**：media_files 和 media_library 分离架构
- **AI模型管理**：ai_providers、ai_models、ai_configs 完整体系
- **模板包系统**：template_packages、site_template_config、site_template_overrides
- **清理重复数据**：清理36个重复和过时的AI模型记录
- **性能优化**：添加必要的索引，提升查询效率

#### 后端架构
- **SiteModel基类**：所有需要站点隔离的模型统一继承
- **SiteScoped Trait**：全局查询作用域，自动添加站点过滤
- **MultiSite中间件**：统一管理站点上下文
- **TemplateResolver服务**：统一模板解析逻辑，支持优先级
- **MediaUsageService**：媒体使用追踪和去重管理
- **AI服务集成**：支持18个AI提供商的统一接口
- **代码质量**：修改100+个文件，新增10000+行代码

#### API设计
- **RESTful规范**：所有新API遵循RESTful设计标准
- **多站点API**：完整的站点管理和配置API
- **模板包API**：模板包安装、管理、配置API
- **媒体库API**：上传、编辑、缩略图、水印等完整API
- **AI文章生成API**：配置、任务、生成、记录等完整流程
- **批量操作**：支持批量生成所有站点静态页面

#### 安全性提升
- **数据安全**：修复SiteModel批量删除高危bug，防止误删整站数据
- **站点隔离**：严格的数据隔离，防止跨站点数据访问
- **文件安全**：媒体文件SHA256哈希验证，防止恶意文件
- **权限控制**：基于角色的访问控制，细粒度权限管理

#### 性能优化
- **文件去重**：相同文件只存储一次，节省存储空间
- **缩略图缓存**：自动生成和缓存缩略图，提升加载速度
- **站点过滤**：自动添加站点过滤，避免全表扫描
- **查询优化**：添加必要索引，优化查询性能
- **缓存策略**：配置缓存、模板缓存、数据缓存分层管理

---

## [1.3.0] - 2025-11-04

### ✨ 新增功能

#### 日志系统完善
- 修复系统日志、登录日志、安全日志无内容问题
- 新增SystemLog中间件，自动记录所有API请求
- 完善登录/登出事件的日志记录机制
- 添加安全事件监控（失败登录、异常访问等）

#### 权限管理增强
- 补充所有新增功能的权限定义（从177行扩展到450+行）
- 新增内容管理权限：文章属性、专题、友情链接、内容模型、自定义字段、回收站
- 新增SEO管理权限：SEO设置、URL重定向、404监控、Robots.txt、SEO工具
- 新增系统管理权限：数据库管理、缓存管理、系统日志、操作日志
- 新增扩展功能权限：广告、幻灯片、会员、投稿、通知、短信、积分商城等
- 新增模板管理权限：模板编辑器、模板标签教程

### 🐛 Bug修复
- 修复切换缓存驱动后信息显示错误问题
- 修复投稿配置分类下拉列表无法加载问题
- 修复通知记录不显示问题
- 修复短信服务统计数据显示错误
- 修复`MODULE_SYSTEM`常量未定义错误

### 🔧 优化改进
- 缓存管理优化：清除配置缓存确保驱动切换立即生效
- 广告管理增强：新增快捷调用代码功能
- 幻灯片管理增强：新增分组快捷调用代码
- 媒体库改进：添加全选/取消全选按钮
- 会员管理完善：会员列表新增VIP到期时间列

### 📖 模板系统增强
- **Carefree标签库 V1.6** - 全面支持变量参数
  - 9个核心标签支持变量参数（article, category, link, slider, related等）
  - 支持动态数据查询：`typeid='$category.id'`, `tagid='$tag.id'`
- **Config标签修复**
  - 修正ConfigTagService使用错误的模型和字段名
  - 更新所有文档中的配置键名

### 📚 文档更新
- 新增`问题修复总结.md` - 详细记录所有11个问题的修复过程
- 完善项目文档结构
- 更新版本号至1.3.0

---

## [1.2.0] - 2025-10-28

### ✨ 新增功能

#### 全文搜索功能
- 基于MySQL FULLTEXT INDEX的高性能搜索
- 支持三种搜索模式：
  - 自然语言模式（按相关度排序）
  - 布尔模式（+word -word "phrase"等操作符）
  - 查询扩展模式（自动扩展相关词汇）
- 搜索结果自动高亮关键词

#### 高级搜索功能
- 支持15+个搜索字段和筛选条件
- 支持标题、内容、摘要、作者等多字段查询
- 支持分类、标签、状态等多维度筛选
- 支持浏览量范围筛选
- 支持多种排序方式（发布时间、浏览量、点赞数、评论数等）

#### 新增API接口
- `/api/articles/fulltext-search` - 全文搜索
- `/api/articles/advanced-search` - 高级搜索
- `/api/articles/search-suggestions` - 搜索建议

### 🎨 前端更新
- 新增`AdvancedSearch.vue`高级搜索对话框组件
  - 美观的双标签页布局（全文搜索/高级搜索）
  - 实时搜索建议/自动完成
  - 搜索历史记录功能（localStorage存储，最多10条）
- 更新文章列表页面，集成高级搜索功能
  - 搜索结果关键词高亮显示
  - 显示当前搜索条件和结果数量

### 🔧 优化改进
- 新增媒体库选择器组件，支持从媒体库插入文件到文章编辑器
- 优化Sitemap生成页面布局

### 🐛 Bug修复
- 修复分类和标签模板中的分页代码错误
- 修复文章模板中categories字段引用错误

---

## [1.1.0] - 2025-10-21

### ✨ 新增功能
- 新增缓存驱动切换功能，支持File和Redis两种驱动
- 增强缓存管理：支持Redis连接测试和实时驱动切换

### 🔧 优化改进
- 优化Sitemap生成界面，合并基础格式和高级类型为单页
- TinyMCE编辑器优化：移除帮助功能，工具栏改为2行布局

### 🐛 Bug修复
- 修复API路由404错误
- 优化PHP Redis扩展检测和错误提示

---

## [1.0.0] - 2025-10-15

### 🎉 首个正式版本发布

#### 核心功能
- 完整的内容管理功能
  - 文章管理（增删改查、分类、标签、富文本编辑）
  - 分类管理（树形结构、自定义模板）
  - 标签系统
  - 单页面管理
- 用户权限管理系统（RBAC）
- 媒体文件管理
  - 图片、文件上传
  - 媒体库统一管理
- SEO优化功能
  - 自动提取TDK
  - Sitemap生成
- 操作日志记录

#### 静态页面生成
- 手动生成：后台按钮点击生成
- 自动生成：文章发布/更新时自动生成
- 生成范围：首页、列表页、详情页、栏目页、标签聚合页
- 生成日志记录

#### 模板系统
- 多套模板支持
- 模板切换功能
- Carefree标签库

#### 技术架构
- 后端：PHP 8.2 + ThinkPHP 8.0 + MySQL 8.0
- 前端：Vue 3 + Vite 7 + Element Plus
- 认证：JWT
- 编辑器：TinyMCE

---

## 版本说明

### 版本号规则
本项目遵循[语义化版本](https://semver.org/lang/zh-CN/)规范：

- **主版本号（Major）**：当做了不兼容的API修改
- **次版本号（Minor）**：当做了向下兼容的功能性新增
- **修订号（Patch）**：当做了向下兼容的问题修正

### 更新类型标识

- ✨ **新增功能** (Added)
- 🔧 **优化改进** (Changed)
- 🐛 **Bug修复** (Fixed)
- ⚠️ **废弃功能** (Deprecated)
- 🗑️ **移除功能** (Removed)
- 🔒 **安全修复** (Security)
- 📚 **文档更新** (Documentation)

---

## 贡献指南

如果你想为CarefreeCMS做出贡献，请遵循以下规范：

1. Fork本仓库
2. 创建你的特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交你的更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启一个Pull Request

详见 [CONTRIBUTING.md](CONTRIBUTING.md)

---

## 许可证

本项目采用 MIT 开源协议。详见 [LICENSE](LICENSE) 文件。

---

Made with ❤️ by CarefreeCMS Team © 2025
