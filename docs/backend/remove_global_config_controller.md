# 删除全局配置管理功能

## 修改说明

系统原先有两套配置系统：
1. **全局配置** (`site_config`表) - 通过Config控制器管理的系统级配置
2. **站点配置** (`sites`表) - 每个站点独立的配置

在多站点架构中，全局配置已不再适用。所有配置应该由各站点独立管理。因此删除了全局配置管理功能，并将仍在使用全局配置的代码改为使用站点配置。

## 删除的文件

### 1. Config.php (控制器)
**位置**: `app/controller/api/Config.php`

**原功能**:
- `index()` - 获取所有全局配置
- `save()` - 保存全局配置

**删除原因**: 全局配置管理在多站点架构中已过时，所有配置应该由各站点独立管理。

## 修改的文件

### 2. FrontComment.php
**位置**: `app/controller/api/FrontComment.php`

#### 修改位置 (line 92-102)

**修改前**:
```php
// 获取系统配置
$commentConfig = Config::getConfig('comment_settings', [
    'enable_guest_comment' => true,   // 是否允许游客评论
    'auto_approve'         => false,  // 是否自动审核通过
    'enable_sensitive_filter' => true, // 是否启用敏感词过滤
]);
```

**修改后**:
```php
// 获取站点配置
$siteId = $article->site_id ?? 1;
$site = \app\model\Site::find($siteId);
$siteConfig = $site && is_array($site->config) ? $site->config : [];

// 评论配置（从站点配置中读取，如果没有则使用默认值）
$commentConfig = [
    'enable_guest_comment' => $siteConfig['enable_guest_comment'] ?? true,   // 是否允许游客评论
    'auto_approve'         => $siteConfig['auto_approve'] ?? false,  // 是否自动审核通过
    'enable_sensitive_filter' => $siteConfig['enable_sensitive_filter'] ?? true, // 是否启用敏感词过滤
];
```

**说明**: 评论配置改为从站点的 `config` 字段中读取，支持每个站点独立配置评论策略。

### 3. Article.php
**位置**: `app/controller/api/Article.php`

#### 第一处修改 (line 383-385)

**修改前**:
```php
// 检查副分类功能是否开启
$subCategoryEnabled = \app\model\Config::getConfig('article_sub_category', 'close');
```

**修改后**:
```php
// 检查副分类功能是否开启（从站点配置中读取）
$site = \app\model\Site::find($siteId);
$subCategoryEnabled = $site ? $site->article_sub_category : 'close';
```

#### 第二处修改 (line 576-579)

**修改前**:
```php
// 检查副分类功能是否开启
$subCategoryEnabled = \app\model\Config::getConfig('article_sub_category', 'close');

// 更新分类关联
if ($subCategories !== null) {
    $siteId = $article->site_id ?? 1;
```

**修改后**:
```php
// 检查副分类功能是否开启（从站点配置中读取）
$siteId = $article->site_id ?? 1;
$site = \app\model\Site::find($siteId);
$subCategoryEnabled = $site ? $site->article_sub_category : 'close';

// 更新分类关联
if ($subCategories !== null) {
```

#### 第三处修改 (line 636-639)

**修改前**:
```php
// 检查回收站是否开启
$recycleBinEnabled = \app\model\Config::getConfig('recycle_bin_enable', 'open');
```

**修改后**:
```php
// 检查回收站是否开启（从站点配置中读取）
$siteId = $article->site_id ?? 1;
$site = \app\model\Site::find($siteId);
$recycleBinEnabled = $site ? $site->recycle_bin_enable : 'open';
```

### 4. Media.php
**位置**: `app/controller/api/Media.php`

#### 修改位置 (line 185-188)

**修改前**:
```php
// 检查回收站是否开启
$recycleBinEnabled = \app\model\Config::getConfig('recycle_bin_enable', 'open');
```

**修改后**:
```php
// 检查回收站是否开启（从站点配置中读取）
$siteId = $media->site_id ?? 1;
$site = \app\model\Site::find($siteId);
$recycleBinEnabled = $site ? $site->recycle_bin_enable : 'open';
```

### 5. api.php (路由)
**位置**: `route/api.php`

**删除的路由** (line 123-125):
```php
// 站点配置
Route::get('config', 'app\controller\api\Config@index');                  // 获取配置
Route::post('config', 'app\controller\api\Config@save');                  // 保存配置
```

**删除原因**: 对应的控制器已删除

## 保留的文件和配置

### Config.php (模型)
**位置**: `app/model/Config.php`

**保留原因**:
- 其他功能仍在使用Config模型读取系统级配置
- 数据库表`site_config`仍然存在并存储这些配置

**仍在使用的系统级配置**:
- `current_template_theme` - 当前模板主题（Template.php、TemplateCheck.php）
  - **用途**: 模板管理后台使用的当前套装
  - **是否改为站点级别**: ❌ 否 - 这是系统级的模板管理配置
  - **说明**: 用于后台管理员管理模板套装，不是站点使用的模板

### 已迁移到站点级别的配置

以下配置已从全局配置迁移到站点的 `config` 字段：

1. **recycle_bin_enable** - 回收站开关
   - ✅ 已迁移到站点配置
   - 访问方式: `$site->recycle_bin_enable`

2. **article_sub_category** - 副分类功能开关
   - ✅ 已迁移到站点配置
   - 访问方式: `$site->article_sub_category`

3. **评论配置** (新增到站点配置)
   - ✅ `enable_guest_comment` - 是否允许游客评论
   - ✅ `auto_approve` - 是否自动审核通过
   - ✅ `enable_sensitive_filter` - 是否启用敏感词过滤
   - 访问方式: `$site->config['enable_guest_comment']` 等

## 站点配置字段说明

站点配置存储在`sites`表的`config`字段（JSON类型）中，通过Site模型的访问器访问：

### recycle_bin_enable
**访问方式**: `$site->recycle_bin_enable`

**定义位置**: `app/model/Site.php:422-426`

```php
public function getRecycleBinEnableAttr($value, $data)
{
    $config = $data['config'] ?? [];
    return is_array($config) ? ($config['recycle_bin_enable'] ?? 'open') : 'open';
}
```

**取值**:
- `'open'` - 开启回收站（软删除）
- `'close'` - 关闭回收站（物理删除）

**默认值**: `'open'`

### article_sub_category
**访问方式**: `$site->article_sub_category`

**定义位置**: `app/model/Site.php:431-435`

```php
public function getArticleSubCategoryAttr($value, $data)
{
    $config = $data['config'] ?? [];
    return is_array($config) ? ($config['article_sub_category'] ?? 'close') : 'close';
}
```

**取值**:
- `'open'` - 开启副分类功能
- `'close'` - 关闭副分类功能

**默认值**: `'close'`

### 评论配置
**访问方式**: `$site->config['enable_guest_comment']` 等

**配置项**:
1. **enable_guest_comment** - 是否允许游客评论
   - 取值: `true` / `false`
   - 默认值: `true`

2. **auto_approve** - 评论是否自动审核通过
   - 取值: `true` / `false`
   - 默认值: `false`

3. **enable_sensitive_filter** - 是否启用敏感词过滤
   - 取值: `true` / `false`
   - 默认值: `true`

**使用示例**:
```php
$site = \app\model\Site::find($siteId);
$siteConfig = $site && is_array($site->config) ? $site->config : [];
$enableGuestComment = $siteConfig['enable_guest_comment'] ?? true;
```

## 数据库影响

### site_config表
- 表结构和数据保持不变
- 以下配置项不再被代码使用（已迁移到站点级别）：
  - `recycle_bin_enable` - 回收站开关
  - `article_sub_category` - 副分类功能开关
  - `comment_settings` - 评论设置（如果存在）
- 这些配置已由各站点的`sites.config`字段管理
- 其他配置项（如`current_template_theme`）仍在使用中

### sites表
- `config`字段（JSON类型）存储站点级别的配置
- 新增/更新站点时，可以设置：
  - `recycle_bin_enable` - 回收站开关
  - `article_sub_category` - 副分类功能开关
  - `index_template` - 首页模板
  - `enable_guest_comment` - 是否允许游客评论
  - `auto_approve` - 评论是否自动审核通过
  - `enable_sensitive_filter` - 是否启用敏感词过滤

## 使用说明

### 代码中读取站点配置

```php
// 获取站点对象
$site = \app\model\Site::find($siteId);

// 读取回收站配置
$recycleBinEnabled = $site ? $site->recycle_bin_enable : 'open';

// 读取副分类配置
$subCategoryEnabled = $site ? $site->article_sub_category : 'close';

// 读取首页模板配置
$indexTemplate = $site ? $site->index_template : 'index';

// 读取评论配置
$siteConfig = $site && is_array($site->config) ? $site->config : [];
$enableGuestComment = $siteConfig['enable_guest_comment'] ?? true;
$autoApprove = $siteConfig['auto_approve'] ?? false;
```

### 通过API更新站点配置

```bash
# 更新站点配置
PUT /api/sites/{id}
{
    "recycle_bin_enable": "close",
    "article_sub_category": "open",
    "index_template": "custom_index",
    "enable_guest_comment": true,
    "auto_approve": false,
    "enable_sensitive_filter": true
}
```

## 迁移建议

### 1. 清理site_config表中的过时配置

如果确认不再需要，可以删除以下配置项：
```sql
DELETE FROM site_config WHERE config_key IN ('recycle_bin_enable', 'article_sub_category');
```

### 2. 为现有站点设置配置

为所有现有站点设置默认配置：
```sql
UPDATE sites
SET config = JSON_SET(
    COALESCE(config, '{}'),
    '$.recycle_bin_enable', 'open',
    '$.article_sub_category', 'close',
    '$.enable_guest_comment', true,
    '$.auto_approve', false,
    '$.enable_sensitive_filter', true
)
WHERE config IS NULL
   OR JSON_EXTRACT(config, '$.recycle_bin_enable') IS NULL
   OR JSON_EXTRACT(config, '$.enable_guest_comment') IS NULL;
```

### 3. 迁移其他配置项

建议将以下全局配置也迁移到站点级别：
- `article_page_size` - 文章列表每页数量
- `comment_need_audit` - 评论是否需要审核
- `upload_*` - 上传相关配置
- `breadcrumb_*` - 面包屑配置

## 测试验证

### 1. 测试评论配置

```bash
# 1. 设置站点允许游客评论
PUT /api/sites/1
{
    "enable_guest_comment": true,
    "auto_approve": false
}

# 2. 游客发表评论（不需要登录）
POST /api/comments
{
    "article_id": 1,
    "content": "这是一条测试评论",
    "user_name": "游客",
    "user_email": "guest@example.com"
}
# 应返回成功，评论状态为待审核

# 3. 设置站点评论自动审核通过
PUT /api/sites/1
{
    "auto_approve": true
}

# 4. 再次发表评论
POST /api/comments
{
    "article_id": 1,
    "content": "这是另一条测试评论",
    "user_name": "游客",
    "user_email": "guest@example.com"
}
# 应返回成功，评论状态为已审核

# 5. 设置站点禁止游客评论
PUT /api/sites/1
{
    "enable_guest_comment": false
}

# 6. 游客发表评论
POST /api/comments
{
    "article_id": 1,
    "content": "这是第三条测试评论",
    "user_name": "游客",
    "user_email": "guest@example.com"
}
# 应返回错误：请先登录后再评论
```

### 2. 测试副分类功能

```bash
# 1. 设置站点开启副分类
PUT /api/sites/1
{
    "article_sub_category": "open"
}

# 2. 创建文章时添加副分类
POST /api/articles
{
    "title": "测试文章",
    "category_id": 1,
    "sub_categories": [2, 3],
    "site_id": 1,
    ...
}

# 3. 验证副分类是否保存
GET /api/articles/{id}
```

### 3. 测试回收站功能

```bash
# 1. 设置站点开启回收站
PUT /api/sites/1
{
    "recycle_bin_enable": "open"
}

# 2. 删除文章
DELETE /api/articles/{id}
# 应返回：文章已移入回收站

# 3. 设置站点关闭回收站
PUT /api/sites/1
{
    "recycle_bin_enable": "close"
}

# 4. 删除文章
DELETE /api/articles/{id}
# 应返回：文章删除成功
```

## 向后兼容

- ✅ Config模型保留，不影响其他使用该模型的功能
- ✅ site_config表数据保持不变
- ✅ 站点配置提供默认值，即使数据库中没有配置也能正常工作
- ✅ 删除全局配置API不影响站点配置API

## 潜在问题

### 1. 性能考虑

每次检查配置都会查询站点表：
```php
$site = \app\model\Site::find($siteId);
```

**优化建议**:
- 使用缓存减少数据库查询
- 在控制器初始化时获取站点对象，避免重复查询
- 使用SiteContextService统一管理当前站点

### 2. 默认值不一致

不同地方可能使用不同的默认值。

**解决方案**:
在Site模型中统一定义默认值：
```php
const DEFAULT_RECYCLE_BIN_ENABLE = 'open';
const DEFAULT_ARTICLE_SUB_CATEGORY = 'close';
```

### 3. 站点配置缺失

如果站点的config字段为空或格式错误，可能导致问题。

**解决方案**:
访问器已包含容错逻辑：
```php
$config = $data['config'] ?? [];
return is_array($config) ? ($config['recycle_bin_enable'] ?? 'open') : 'open';
```

## 后续工作

### 1. 迁移其他全局配置

将以下配置从全局迁移到站点级别：
- 上传配置 (upload_*)
- 面包屑配置 (breadcrumb_*)
- 文章默认配置 (article_default_*)
- 内容图片特性 (content_image_features)

### 2. 优化性能

- 实现站点配置缓存
- 使用SiteContextService统一获取当前站点
- 减少重复的数据库查询

### 3. 完善文档

- 更新管理后台文档
- 更新API文档
- 添加站点配置字段说明

## 相关文档

- [移除全局site_url配置](./remove_global_site_url.md)
- [修复站点JSON解码错误](./fix_site_json_decode_error.md)
- [站点管理文档](./site_management.md)
- [多站点支持文档](./multisite_support.md)
