# 移除全局site_url配置，改用站点domain字段

## 修改说明

根据需求，系统不再使用"基本信息"中的全局"前端网站网址"配置（`system_config`表中`site_id=0`的`site_url`），改为使用各站点自己的`domain`字段（`sites`表的`domain`字段）。

## 修改的文件

### 1. ConfigTagService.php
**位置**: `app/service/tag/ConfigTagService.php`

**修改内容**:
- `getSiteFieldValue()` 方法 (line 190-213)
  - 移除了site_url和url字段的特殊处理
  - 简化为只支持domain字段

- `getSiteFieldsArray()` 方法 (line 222-229)
  - 移除site_url和url字段
  - 只保留domain字段

### 2. Build.php
**位置**: `app/controller/api/Build.php`

**修改内容**:
- `setTemplateGlobalConfig()` 方法 (line 101-106)
  - 移除site_url和url字段
  - 只保留domain字段

### 3. Article.php
**位置**: `app/controller/api/Article.php:178-186`

**修改前**:
```php
$siteUrl = \app\model\Config::getConfig('site_url', '');
if (!empty($siteUrl)) {
    $articleData['cover_image'] = rtrim($siteUrl, '/') . '/' . $articleData['cover_image'];
}
```

**修改后**:
```php
$site = \app\service\SiteContextService::getSite();
$siteDomain = $site ? $site->domain : '';
if (!empty($siteDomain)) {
    $articleData['cover_image'] = rtrim($siteDomain, '/') . '/' . $articleData['cover_image'];
}
```

### 4. Media.php
**位置**: `app/model/Media.php:56-66`

**修改前**:
```php
$siteUrl = Config::getConfig('site_url', '');
if (!empty($siteUrl)) {
    return rtrim($siteUrl, '/') . '/' . $data['file_path'];
}
```

**修改后**:
```php
$site = \app\service\SiteContextService::getSite();
$siteDomain = $site ? $site->domain : '';
if (!empty($siteDomain)) {
    return rtrim($siteDomain, '/') . '/' . $data['file_path'];
}
```

### 5. FrontProfile.php
**位置**: `app/controller/api/FrontProfile.php:164-171`

**修改前**:
```php
$siteUrl = \app\model\Config::getConfig('site_url', '');
if (!empty($siteUrl)) {
    $avatarUrl = rtrim($siteUrl, '/') . '/' . $filePath;
}
```

**修改后**:
```php
$site = \app\service\SiteContextService::getSite();
$siteDomain = $site ? $site->domain : '';
if (!empty($siteDomain)) {
    $avatarUrl = rtrim($siteDomain, '/') . '/' . $filePath;
}
```

### 6. Page.php
**位置**: `app/controller/api/Page.php:58-69`

**修改前**:
```php
$siteUrl = \app\model\Config::getConfig('site_url', '');
if (!empty($siteUrl)) {
    $pageData['cover_image'] = rtrim($siteUrl, '/') . '/' . $pageData['cover_image'];
}
```

**修改后**:
```php
$site = \app\service\SiteContextService::getSite();
$siteDomain = $site ? $site->domain : '';
if (!empty($siteDomain)) {
    $pageData['cover_image'] = rtrim($siteDomain, '/') . '/' . $pageData['cover_image'];
}
```

### 7. Profile.php
**位置**: `app/controller/api/Profile.php`

**第一处 (line 32-44)**:
```php
// 修改前
$siteUrl = \app\model\Config::getConfig('site_url', '');

// 修改后
$site = \app\service\SiteContextService::getSite();
$siteDomain = $site ? $site->domain : '';
```

**第二处 (line 207-214)**:
```php
// 修改前
$siteUrl = \app\model\Config::getConfig('site_url', '');

// 修改后
$site = \app\service\SiteContextService::getSite();
$siteDomain = $site ? $site->domain : '';
```

## 修改总结

### 修改的文件数量
- 共修改 **7个文件**
- 共修改 **8处代码**（Profile.php有2处）

### 核心变更
1. **废弃**: 全局配置`Config::getConfig('site_url')`
2. **改用**: 当前站点的`domain`字段
3. **获取方式**: 通过`SiteContextService::getSite()->domain`

### 优势
- ✅ 多站点独立：每个站点使用自己的domain
- ✅ 配置简化：不需要维护全局site_url配置
- ✅ 站点隔离：各站点URL互不干扰
- ✅ 灵活性：每个站点可以配置不同的域名

### 回退方案
所有修改都保留了回退逻辑：
```php
if (!empty($siteDomain)) {
    // 使用站点domain
} else {
    // 回退到request()->domain()
}
```

## 数据库影响

### system_config表
- `site_url`字段（site_id=0）不再被代码使用
- 可以保留该配置（向后兼容），但不会被读取
- 建议在管理后台隐藏或标记为"已废弃"

### sites表
- `domain`字段变为主要的URL配置字段
- 建议为所有站点配置domain值
- domain应包含协议和域名，例如：`https://www.example.com`

## 使用说明

### 模板中使用
```html
<!-- 使用Carefree标签获取站点domain -->
{carefree:config name="domain" default="/" /}

<!-- 或使用ThinkPHP变量 -->
{$site.domain}
```

### 代码中使用
```php
// 获取当前站点domain
$site = \app\service\SiteContextService::getSite();
$domain = $site ? $site->domain : '';
```

## 测试建议

1. **测试文章封面图URL生成**
   ```bash
   curl http://localhost:8000/api/article/1
   # 检查cover_image字段是否使用站点domain
   ```

2. **测试媒体文件URL生成**
   ```bash
   curl http://localhost:8000/api/media/list
   # 检查file_url字段是否使用站点domain
   ```

3. **测试用户头像URL生成**
   ```bash
   curl http://localhost:8000/api/profile/info
   # 检查avatar字段是否使用站点domain
   ```

4. **测试单页封面图URL生成**
   ```bash
   curl http://localhost:8000/api/page/1
   # 检查cover_image字段是否使用站点domain
   ```

## 注意事项

1. **domain字段格式**: 建议配置完整的URL，包含协议
   - ✅ 正确: `https://www.example.com`
   - ❌ 错误: `www.example.com`（缺少协议）

2. **站点上下文**: 所有使用domain的代码都依赖`SiteContextService::getSite()`
   - 确保请求经过MultiSite中间件
   - 确保站点上下文正确设置

3. **静态资源**: 如果使用CDN，需要在站点domain中配置CDN域名

4. **向后兼容**: 如果domain为空，系统会回退到使用`request()->domain()`

## 相关文档

- [站点管理文档](./site_management.md)
- [多站点支持文档](./multisite_support.md)
- [Carefree标签库文档](./carefree_taglib_multisite_upgrade_plan.md)
