# Carefree标签库多站点支持升级方案

## 📅 创建时间
2025-11-17

## 🎯 升级目标

将Carefree标签库从单站点模式升级为完整支持多站点架构，确保标签在静态生成和动态渲染两种场景下都能正确获取站点数据。

---

## 一、现状分析

### 1.1 当前架构

**Carefree标签库工作流程**：
```
模板标签 → Carefree::tagXxx() → TagService::getList() → Model查询 → 返回数据
```

**TagService查询模式**：
```php
// ArticleTagService.php 第45行
$query = Article::with(['category', 'tags', 'user'])
    ->where('status', 1);

// TopicTagService.php 第28行
$query = Topic::query();

// ConfigTagService.php 第31行
$setting = Config::where('config_key', $name)->find();
```

### 1.2 识别的问题

#### 问题1: 隐式依赖SiteContextService

**现象**：
- TagService直接使用`Article::with()`等查询
- 这些模型继承自`SiteModel`
- `SiteModel`的`db()`方法自动添加站点过滤：
  ```php
  $query->where('articles.site_id', SiteContextService::getSiteId());
  ```

**风险**：
- 如果`SiteContextService`未正确设置，会查询错误站点的数据
- 静态生成时需要确保已调用`SiteContextService::switchSite()`
- 动态渲染时需要中间件自动设置站点上下文

#### 问题2: 配置系统不匹配

**现象**：
- `ConfigTagService`从全局`Config`表读取数据
- 多站点系统配置来源：
  1. 模板包的`default_config`（JSON字段）
  2. 站点的`custom_config`（JSON字段）
  3. 站点的SEO配置（如logo、title等）

**问题**：
```php
// 当前实现
{carefree:config name='site_name' /}
// 从 system_config 表查询，返回全局配置

// 期望行为
{carefree:config name='site_name' /}
// 从当前站点的配置查询，返回站点专属配置
```

#### 问题3: 缺少站点配置传递机制

**现象**：
- 静态生成时，`TemplateResolver->prepareTemplateData()`已合并配置
- 但模板中的`{carefree:config}`标签仍查询数据库
- 应该直接使用已传递的`$config`变量

---

## 二、技术方案

### 2.1 方案选择

#### 方案A：保持现状 + 文档说明 ⭐️ **推荐**

**优势**：
- ✅ 零代码改动
- ✅ 静态生成已正常工作（Build控制器设置了SiteContextService）
- ✅ 适用于纯静态站点场景

**实施**：
1. 在文档中说明TagService依赖SiteContextService
2. 修复`ConfigTagService`使用站点配置
3. 确保动态渲染场景有MultiSite中间件

**适用场景**：
- ✅ 纯静态站点生成
- ✅ 已有MultiSite中间件的动态站点
- ⚠️ 需要开发者了解SiteContextService机制

#### 方案B：显式传递siteId参数

**示例**：
```php
// 修改所有TagService方法签名
public static function getList($params = [], $siteId = null)
{
    $query = Article::bySite($siteId ?? SiteContextService::getSiteId())
        ->where('status', 1);
}

// 模板使用（需要TemplateResolver传递）
{carefree:article typeid='1' limit='10' siteid='$site.id'}
```

**优势**：
- ✅ 显式清晰，不依赖全局状态
- ✅ 易于测试和调试

**劣势**：
- ❌ 需要修改28个TagService文件
- ❌ 模板需要显式传递`siteid`参数
- ❌ 向后不兼容

#### 方案C：ConfigTagService特殊处理 ⭐️ **必须实施**

**问题**：
- `{carefree:config name='site_name' /}`应该返回当前站点的配置
- 不应该查询全局Config表

**解决方案**：
```php
// 1. 从TemplateResolver传递的$config获取（静态生成）
// 2. 从SiteContextService获取（动态渲染）

public static function get($name, $default = '')
{
    // 优先从模板变量获取（静态生成时）
    global $__template_config__;
    if (isset($__template_config__[$name])) {
        return $__template_config__[$name];
    }

    // 从站点上下文获取（动态渲染）
    $site = \app\service\SiteContextService::getSite();
    if ($site) {
        // 从站点配置获取
        $siteConfig = \app\model\SiteTemplateConfig::where('site_id', $site->id)->find();
        if ($siteConfig && isset($siteConfig->custom_config[$name])) {
            return $siteConfig->custom_config[$name];
        }

        // 从模板包默认配置获取
        $package = \app\model\TemplatePackage::find($siteConfig->package_id);
        if ($package && isset($package->default_config[$name])) {
            return $package->default_config[$name];
        }
    }

    return $default;
}
```

---

### 2.2 推荐实施方案

综合考虑，推荐采用 **方案A + 方案C** 的组合：

1. **保持现状**：TagService继续使用SiteModel自动过滤
2. **修复Config**：重写ConfigTagService使用站点配置
3. **完善文档**：说明多站点标签使用机制
4. **增强传递**：TemplateResolver传递配置给模板

---

## 三、实施计划

### 3.1 立即实施（必须）

#### Task 1: 重写ConfigTagService ⚠️ **高优先级**

**目标**：使`{carefree:config}`标签返回当前站点的配置

**修改文件**：`app/service/tag/ConfigTagService.php`

**实现逻辑**：
```php
<?php
namespace app\service\tag;

use app\service\SiteContextService;
use app\model\SiteTemplateConfig;
use app\model\TemplatePackage;
use think\facade\Cache;

class ConfigTagService
{
    /**
     * 获取站点配置值
     * 优先级：站点自定义配置 > 模板包默认配置
     */
    public static function get($name, $default = '')
    {
        // 1. 尝试从全局模板变量获取（静态生成时由控制器传递）
        global $__template_site_config__;
        if (isset($__template_site_config__[$name])) {
            return $__template_site_config__[$name];
        }

        // 2. 从站点上下文获取（动态渲染时）
        $site = SiteContextService::getSite();
        if (!$site) {
            return $default;
        }

        // 3. 构建缓存键
        $cacheKey = "site_config:{$site->id}:{$name}";
        $value = Cache::get($cacheKey);
        if ($value !== false && $value !== null) {
            return $value;
        }

        // 4. 查询站点模板配置
        $siteConfig = SiteTemplateConfig::where('site_id', $site->id)->find();

        if ($siteConfig) {
            // 4.1 查找站点自定义配置
            if (isset($siteConfig->custom_config[$name])) {
                $value = $siteConfig->custom_config[$name];
                Cache::set($cacheKey, $value, 3600);
                return $value;
            }

            // 4.2 查找模板包默认配置
            $package = TemplatePackage::find($siteConfig->package_id);
            if ($package && isset($package->default_config[$name])) {
                $value = $package->default_config[$name];
                Cache::set($cacheKey, $value, 3600);
                return $value;
            }
        }

        // 5. 查找站点基本字段（logo、seo等）
        $fieldMapping = [
            'logo' => 'logo',
            'site_name' => 'site_name',
            'seo_title' => 'seo_config.seo_title',
            'seo_keywords' => 'seo_config.seo_keywords',
            'seo_description' => 'seo_config.seo_description',
            'icp_number' => 'icp_number',
            'police_number' => 'police_number',
            'copyright' => 'copyright',
        ];

        if (isset($fieldMapping[$name])) {
            $field = $fieldMapping[$name];

            // 处理嵌套字段（如 seo_config.seo_title）
            if (strpos($field, '.') !== false) {
                [$mainField, $subField] = explode('.', $field, 2);
                $value = $site->{$mainField}[$subField] ?? $default;
            } else {
                $value = $site->{$field} ?? $default;
            }

            Cache::set($cacheKey, $value, 3600);
            return $value;
        }

        return $default;
    }

    /**
     * 清除站点配置缓存
     */
    public static function clearCache($siteId = null, $name = null)
    {
        if ($siteId && $name) {
            Cache::delete("site_config:{$siteId}:{$name}");
        } elseif ($siteId) {
            // 清除指定站点的所有配置缓存
            Cache::tag("site_config:{$siteId}")->clear();
        } else {
            // 清除所有站点配置缓存
            Cache::tag('site_config')->clear();
        }
    }
}
```

#### Task 2: 修改Build控制器传递配置

**目标**：将配置设置为全局变量，供ConfigTagService读取

**修改文件**：`app/controller/api/Build.php`

**修改所有生成方法**，在调用`View::fetch()`之前设置全局变量：

```php
// 在每个生成方法中添加
protected function setTemplateGlobalConfig()
{
    // 设置全局配置变量供ConfigTagService使用
    global $__template_site_config__;
    $__template_site_config__ = $this->resolver->getConfig();
}

// 修改index()方法示例
public function index()
{
    try {
        $templateData = $this->resolver->prepareTemplateData();
        $templateData['is_home'] = true;

        // 设置全局配置
        $this->setTemplateGlobalConfig();

        $content = View::fetch($this->getTemplatePath('index'), $templateData);

        $filePath = $this->outputPath . 'index.html';
        file_put_contents($filePath, $content);

        return Response::success([], '首页生成成功');
    } catch (\Exception $e) {
        return Response::error('生成失败：' . $e->getMessage());
    }
}
```

**需要修改的方法**：
- `index()`
- `articles()`
- `article()`
- `category()`
- `tag()`
- `topic()`
- `page()`

---

### 3.2 中期优化（建议）

#### Task 3: 创建TagService基类

**目标**：统一站点上下文处理逻辑

**新增文件**：`app/service/tag/BaseTagService.php`

```php
<?php
namespace app\service\tag;

use app\service\SiteContextService;

abstract class BaseTagService
{
    /**
     * 获取当前站点ID
     */
    protected static function getCurrentSiteId()
    {
        return SiteContextService::getSiteId() ?? 1;
    }

    /**
     * 获取当前站点
     */
    protected static function getCurrentSite()
    {
        return SiteContextService::getSite();
    }

    /**
     * 构建查询（自动应用站点过滤）
     */
    protected static function buildQuery($model, $params = [])
    {
        // SiteModel会自动添加site_id过滤
        // 这里只是提供统一的查询构建入口
        return $model::query();
    }
}
```

**重构ArticleTagService继承BaseTagService**：
```php
class ArticleTagService extends BaseTagService
{
    public static function getList($params = [])
    {
        // 使用父类方法，确保站点过滤
        $query = self::buildQuery(Article::class, $params)
            ->with(['category', 'tags', 'user'])
            ->where('status', 1);
        // ... 后续逻辑
    }
}
```

#### Task 4: 添加调试信息

**目标**：在开发模式下显示当前站点上下文

**修改文件**：`app/service/SiteContextService.php`

```php
/**
 * 获取当前站点（增加调试信息）
 */
public static function getSite()
{
    if (self::$currentSite === null) {
        self::identifySite();
    }

    // 调试模式记录日志
    if (env('app.debug')) {
        trace('Current Site ID: ' . (self::$currentSite ? self::$currentSite->id : 'None'), 'info');
        trace('Current Site Name: ' . (self::$currentSite ? self::$currentSite->site_name : 'None'), 'info');
    }

    return self::$currentSite;
}
```

---

### 3.3 长期优化（可选）

#### Task 5: 模板标签增加siteid参数支持

**目标**：允许跨站点查询数据

**示例**：
```twig
{# 查询当前站点的文章 #}
{carefree:article typeid='1' limit='10' id='article'}
    <a href="/article/{$article.id}.html">{$article.title}</a>
{/carefree:article}

{# 查询指定站点的文章（跨站点） #}
{carefree:article typeid='1' limit='10' siteid='2' id='article'}
    <a href="//site2.example.com/article/{$article.id}.html">{$article.title}</a>
{/carefree:article}
```

**实现**：
```php
public function tagArticle($tag, $content)
{
    // ... 现有代码

    // 新增siteid参数支持
    $siteid = $tag['siteid'] ?? 0;
    $siteidVar = $siteid ? $this->autoBuildVar($siteid) : '0';

    $parseStr = '<?php ';
    $parseStr .= '$__articles__ = \app\service\tag\ArticleTagService::getList([';
    $parseStr .= "'typeid' => {$typeidVar}, ";
    // ... 其他参数
    $parseStr .= "'siteid' => {$siteidVar}";  // 新增
    $parseStr .= ']); ';
    // ... 后续代码
}
```

---

## 四、测试方案

### 4.1 单元测试

#### 测试ConfigTagService

```php
// tests/service/tag/ConfigTagServiceTest.php
class ConfigTagServiceTest extends TestCase
{
    public function testGetSiteConfig()
    {
        // 设置站点上下文
        SiteContextService::switchSite(1);

        // 测试获取站点配置
        $siteName = ConfigTagService::get('site_name');
        $this->assertEquals('主站点', $siteName);

        // 切换到站点2
        SiteContextService::switchSite(2);
        $siteName = ConfigTagService::get('site_name');
        $this->assertEquals('子站点', $siteName);
    }

    public function testGetFromGlobalVariable()
    {
        // 模拟静态生成场景
        global $__template_site_config__;
        $__template_site_config__ = [
            'site_name' => '测试站点',
            'logo' => '/logo.png'
        ];

        $siteName = ConfigTagService::get('site_name');
        $this->assertEquals('测试站点', $siteName);
    }
}
```

### 4.2 集成测试

#### 测试静态生成

```bash
# 1. 生成主站点
curl -X POST http://localhost:8000/api/build/index

# 2. 检查生成的index.html中的配置
# 应该显示主站点的site_name、logo等

# 3. 生成站点2
curl -X POST "http://localhost:8000/api/build/index?site_id=2"

# 4. 检查生成的html/site_2/index.html
# 应该显示站点2的配置
```

#### 测试模板标签

创建测试模板 `templates/default/test.html`：
```twig
<!DOCTYPE html>
<html>
<head>
    <title>{carefree:config name='site_name' /}</title>
</head>
<body>
    <h1>站点名称: {carefree:config name='site_name' /}</h1>
    <img src="{carefree:config name='logo' /}" alt="Logo">

    <h2>文章列表</h2>
    {carefree:article limit='5' id='article'}
        <div>
            <h3>{$article.title}</h3>
            <p>站点ID: {$article.site_id}</p>
        </div>
    {/carefree:article}

    <h2>专题列表</h2>
    {carefree:topic limit='3' id='topic'}
        <div>
            <h3>{$topic.name}</h3>
            <p>站点ID: {$topic.site_id}</p>
        </div>
    {/carefree:topic}
</body>
</html>
```

生成并验证：
```bash
# 生成主站点
curl -X POST http://localhost:8000/api/build/test

# 验证
cat html/main/test.html | grep "站点ID"
# 应该只显示site_id=1的数据

# 生成站点2
curl -X POST "http://localhost:8000/api/build/test?site_id=2"

# 验证
cat html/site_2/test.html | grep "站点ID"
# 应该只显示site_id=2的数据
```

---

## 五、向后兼容性

### 5.1 兼容性保证

✅ **完全兼容** - 所有现有模板无需修改

| 场景 | 旧版行为 | 新版行为 | 兼容性 |
|------|---------|---------|--------|
| 单站点使用 | 从Config表读取全局配置 | 从站点配置读取（回退到全局） | ✅ 兼容 |
| `{carefree:article}` | 查询所有文章 | 查询当前站点文章 | ✅ 兼容 |
| `{carefree:config}` | 返回全局配置 | 返回站点配置 | ⚠️ 需迁移数据 |

### 5.2 迁移指南

**步骤1**：迁移Config数据到站点配置
```sql
-- 已完成（参考 migrate_system_config_to_site.sql）
UPDATE sites SET
    logo = (SELECT config_value FROM system_config WHERE config_key = 'logo' LIMIT 1),
    seo_config = JSON_OBJECT(
        'seo_title', (SELECT config_value FROM system_config WHERE config_key = 'seo_title' LIMIT 1),
        'seo_keywords', (SELECT config_value FROM system_config WHERE config_key = 'seo_keywords' LIMIT 1),
        'seo_description', (SELECT config_value FROM system_config WHERE config_key = 'seo_description' LIMIT 1)
    )
WHERE site_type = 1;
```

**步骤2**：清除配置缓存
```php
// 执行一次
\app\service\tag\ConfigTagService::clearCache();
```

**步骤3**：重新生成所有静态页面
```bash
curl -X POST http://localhost:8000/api/build/all-sites
```

---

## 六、文档更新

### 6.1 需要更新的文档

1. **模板开发指南** - 添加多站点标签使用说明
2. **标签库文档** - 更新`{carefree:config}`标签说明
3. **开发者文档** - 添加SiteContextService使用说明
4. **升级指南** - 添加ConfigTagService改造说明

### 6.2 示例文档

#### 模板中使用站点配置

```markdown
## 站点配置标签

### 基本用法

```twig
{# 获取站点名称 #}
{carefree:config name='site_name' /}

{# 获取Logo #}
<img src="{carefree:config name='logo' /}" alt="Logo">

{# 获取SEO配置 #}
<title>{carefree:config name='seo_title' /}</title>
<meta name="keywords" content="{carefree:config name='seo_keywords' /}">
```

### 配置优先级

1. **站点自定义配置**（最高优先级）
   - 在后台"站点管理 > 模板配置"中设置

2. **模板包默认配置**
   - 在后台"模板包管理"中设置

3. **站点基本字段**
   - logo、site_name、seo_config等站点表字段

### 多站点自动识别

标签会自动根据当前站点上下文获取对应的配置：

- 静态生成时：由Build控制器设置站点上下文
- 动态渲染时：由MultiSite中间件自动识别站点
```

---

## 七、风险评估

### 7.1 技术风险

| 风险项 | 风险等级 | 影响范围 | 缓解措施 |
|--------|---------|---------|---------|
| ConfigTagService改造失败 | 中 | 配置显示错误 | 充分测试 + 回滚方案 |
| 站点上下文未设置 | 高 | 数据查询错误 | 添加默认回退逻辑 |
| 缓存失效问题 | 低 | 性能下降 | 优化缓存策略 |
| 向后兼容性问题 | 中 | 旧模板失效 | 保持API兼容 + 文档 |

### 7.2 回滚方案

如果升级后出现问题，可以快速回滚：

```bash
# 1. 恢复旧版ConfigTagService
git checkout HEAD~1 -- app/service/tag/ConfigTagService.php

# 2. 清除缓存
php think cache:clear

# 3. 重新生成静态页面
curl -X POST http://localhost:8000/api/build/all
```

---

## 八、总结

### 8.1 核心改动

1. ✅ **重写ConfigTagService** - 从站点配置读取而不是全局Config表
2. ✅ **修改Build控制器** - 设置全局配置变量
3. ✅ **保持TagService现状** - 依赖SiteModel自动过滤
4. ✅ **完善文档** - 说明多站点标签使用机制

### 8.2 预期效果

- ✅ 模板标签自动识别当前站点
- ✅ 配置标签返回站点专属配置
- ✅ 完全向后兼容
- ✅ 静态生成和动态渲染都正常工作

### 8.3 后续优化

- 📋 创建TagService基类统一处理
- 📋 添加siteid参数支持跨站点查询
- 📋 性能优化（查询缓存、懒加载等）
- 📋 监控和日志完善

---

**文档版本**：v1.0
**创建时间**：2025-11-17
**作者**：Claude Code Assistant
**状态**：✅ 方案确定，等待实施
