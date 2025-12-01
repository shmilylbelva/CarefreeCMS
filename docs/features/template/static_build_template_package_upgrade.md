# 静态生成功能模板包系统升级方案

## 一、概述

当前的静态生成功能使用硬编码的模板路径和配置，需要升级以支持新的模板包系统。

## 二、核心变更

### 1. 新增模板解析服务

**文件**: `app/service/TemplateResolver.php` ✅ 已创建

**功能**:
- 根据站点ID自动选择对应的模板包
- 实现模板解析优先级：站点覆盖 > 站点包 > 默认包
- 合并模板包配置和站点自定义配置
- 提供统一的模板路径解析接口

**主要方法**:
```php
// 构造函数，传入站点ID
public function __construct(int $siteId = 0)

// 解析模板路径（返回完整文件路径）
public function resolveTemplatePath(string $templateType): string

// 获取模板视图路径（返回相对路径，用于View::fetch）
public function getTemplateViewPath(string $templateType): string

// 获取合并后的配置
public function getConfig(): array

// 准备通用模板数据
public function prepareTemplateData(): array
```

### 2. Build控制器需要调整的地方

**文件**: `app/controller/api/Build.php`

#### 2.1 添加站点ID支持

**当前问题**: 所有方法都没有站点ID参数

**需要修改**:

```php
// 在 initialize() 方法中添加
protected $siteId = 0;  // 站点ID
protected $resolver;     // 模板解析器

protected function initialize()
{
    parent::initialize();

    // 从请求或参数获取站点ID
    $this->siteId = (int)$this->request->param('site_id', 0);

    // 创建模板解析器
    $this->resolver = new \app\service\TemplateResolver($this->siteId);

    // 根据站点设置输出路径
    $siteFolder = $this->siteId > 0 ? 'site_' . $this->siteId : 'main';
    $this->outputPath = app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . $siteFolder . DIRECTORY_SEPARATOR;

    // 确保输出目录存在
    if (!is_dir($this->outputPath)) {
        mkdir($this->outputPath, 0755, true);
    }
}
```

#### 2.2 删除旧的配置加载方法

**需要删除**:
- `loadConfig()` 方法（第52-88行）
- `$this->config` 属性的使用改为 `$this->resolver->getConfig()`
- `$this->currentTheme` 属性改为 `$this->resolver->getPackageCode()`

#### 2.3 替换 getTemplatePath() 方法

**当前实现**（第95-105行）:
```php
protected function getTemplatePath($template)
{
    $themePath = root_path() . 'templates' . DIRECTORY_SEPARATOR . $this->currentTheme . DIRECTORY_SEPARATOR;
    View::config([
        'view_path' => $themePath
    ]);
    return '/' . $template;
}
```

**新实现**:
```php
protected function getTemplatePath($templateType)
{
    // 使用解析器获取模板路径
    $viewPath = $this->resolver->getTemplateViewPath($templateType);

    // 设置视图根目录为templates目录
    View::config([
        'view_path' => root_path() . 'templates' . DIRECTORY_SEPARATOR
    ]);

    // 返回相对路径
    return '/' . $viewPath;
}
```

#### 2.4 更新所有生成方法的数据传递

**需要修改的方法**:
- `index()` - 第111-132行
- `articles()` - 第138-176行
- `article()` - 第181-255行
- `category()` - 第260-323行
- `tag()` - 第328-406行
- `topic()` - 第469-550行
- `page()` - 第613-653行

**修改模式**:

**当前**:
```php
$content = View::fetch($this->getTemplatePath('article'), [
    'article' => $article->toArray(),
    'config' => $this->config,  // 使用旧配置
    'is_home' => false,
    // ...
]);
```

**修改为**:
```php
// 准备基础数据
$templateData = $this->resolver->prepareTemplateData();

// 添加页面特定数据
$templateData = array_merge($templateData, [
    'article' => $article->toArray(),
    'is_home' => false,
    'title' => $article->title,
    // ...
]);

$content = View::fetch($this->getTemplatePath('article'), $templateData);
```

#### 2.5 支持多站点批量生成

**新增方法**:
```php
/**
 * 生成指定站点的所有静态页面
 * @param int $siteId 站点ID
 */
public function buildSite(int $siteId)
{
    $this->siteId = $siteId;
    $this->initialize();
    return $this->all();
}

/**
 * 批量生成所有站点
 */
public function buildAllSites()
{
    $sites = \app\model\Site::where('status', 1)->select();
    $result = [];

    foreach ($sites as $site) {
        try {
            $siteResult = $this->buildSite($site->id);
            $result[$site->domain] = $siteResult->getData();
        } catch (\Exception $e) {
            $result[$site->domain] = ['error' => $e->getMessage()];
        }
    }

    return Response::success($result, '所有站点生成完成');
}
```

## 三、详细修改清单

### 3.1 属性修改

```php
// 添加
protected $siteId = 0;
protected $resolver;

// 删除
protected $config = [];
protected $currentTheme = 'default';
```

### 3.2 initialize() 方法

**第32-48行，完全重写**:

```php
protected function initialize()
{
    parent::initialize();

    // 从请求获取站点ID
    $this->siteId = (int)$this->request->param('site_id', 0);

    // 创建模板解析器
    $this->resolver = new \app\service\TemplateResolver($this->siteId);

    // 根据站点设置输出路径
    $siteFolder = $this->siteId > 0 ? 'site_' . $this->siteId : 'main';
    $this->outputPath = app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . $siteFolder . DIRECTORY_SEPARATOR;

    // 确保输出目录存在
    if (!is_dir($this->outputPath)) {
        mkdir($this->outputPath, 0755, true);
    }
}
```

### 3.3 删除 loadConfig() 方法

**删除第52-88行**

### 3.4 修改 getTemplatePath() 方法

**第95-105行，修改为**:

```php
protected function getTemplatePath($templateType)
{
    try {
        $viewPath = $this->resolver->getTemplateViewPath($templateType);

        View::config([
            'view_path' => root_path() . 'templates' . DIRECTORY_SEPARATOR
        ]);

        return '/' . $viewPath;
    } catch (\Exception $e) {
        trace('模板解析失败: ' . $e->getMessage(), 'error');
        throw $e;
    }
}
```

### 3.5 修改 index() 方法

**第111-132行，修改为**:

```php
public function index()
{
    try {
        // 准备模板数据
        $templateData = $this->resolver->prepareTemplateData();
        $templateData['is_home'] = true;

        // 渲染模板
        $content = View::fetch($this->getTemplatePath('index'), $templateData);

        // 保存文件
        $filePath = $this->outputPath . 'index.html';
        file_put_contents($filePath, $content);

        return Response::success([], '首页生成成功');
    } catch (\Exception $e) {
        return Response::error('生成失败：' . $e->getMessage());
    }
}
```

### 3.6 修改 article() 方法

**第181-255行的关键部分**:

```php
// 准备基础模板数据
$templateData = $this->resolver->prepareTemplateData();

// 添加文章特定数据
$templateData = array_merge($templateData, [
    'article' => $article->toArray(),
    'prev' => $prev ? $prev->toArray() : null,
    'next' => $next ? $next->toArray() : null,
    'is_home' => false,
    'title' => $article->title,
    'keywords' => $article->seo_keywords ?? '',
    'description' => $article->seo_description ?? $article->summary
]);

// 渲染模板
$content = View::fetch($this->getTemplatePath('article'), $templateData);
```

### 3.7 其他方法类似修改

`articles()`, `category()`, `tag()`, `topic()`, `page()` 方法都按照相同模式修改。

## 四、使用示例

### 4.1 生成主站点静态页面

```php
// API调用
GET /api/build/all

// 命令行
php think build:static
```

### 4.2 生成指定站点

```php
// API调用
GET /api/build/all?site_id=2

// 或在代码中
$build = new Build();
$build->buildSite(2);
```

### 4.3 生成所有站点

```php
GET /api/build/all-sites

// 或
$build = new Build();
$build->buildAllSites();
```

## 五、兼容性说明

### 5.1 向后兼容

- 不传site_id时，默认使用主站点（site_type=1）
- 保持现有API接口不变
- 输出路径改为 `html/main/` 而不是 `html/`

### 5.2 模板回退机制

如果站点配置的模板包中缺少某个模板：
1. 优先查找站点包模板
2. 回退到默认包（ID=1）模板
3. 如果都不存在，抛出异常

### 5.3 配置合并

```php
// 模板包默认配置
{
  "colors": {"primary": "#409EFF"},
  "layout": {"sidebar_width": "300px"}
}

// 站点自定义配置
{
  "colors": {"primary": "#FF0000"}  // 覆盖primary
}

// 最终合并结果
{
  "colors": {"primary": "#FF0000"},  // 使用站点配置
  "layout": {"sidebar_width": "300px"}  // 保留默认配置
}
```

## 六、测试建议

### 6.1 单元测试

- 测试TemplateResolver的模板解析逻辑
- 测试配置合并功能
- 测试模板优先级

### 6.2 集成测试

- 测试生成主站点静态页面
- 测试生成子站点静态页面
- 测试站点覆盖模板功能
- 测试模板包切换

### 6.3 边界情况

- 模板包不存在时的回退
- 站点没有配置模板包
- 模板文件不存在的处理

## 七、迁移步骤

### 步骤1: 备份当前代码
```bash
cp app/controller/api/Build.php app/controller/api/Build.php.backup
```

### 步骤2: 创建模板解析器
已完成 ✅

### 步骤3: 修改Build控制器
按照上述清单逐一修改

### 步骤4: 测试
- 测试主站点生成
- 测试子站点生成
- 测试不同模板包

### 步骤5: 更新文档
更新用户文档和API文档

## 八、FAQ

**Q: 是否需要修改现有模板文件？**
A: 不需要。模板文件本身不需要修改，只是引用路径改为通过数据库查找。

**Q: 旧的静态页面是否需要重新生成？**
A: 建议重新生成一次，以使用新的模板系统。

**Q: 是否支持同时使用多个模板包？**
A: 每个站点只能配置一个模板包，但可以通过站点覆盖功能自定义特定模板。

**Q: 性能影响如何？**
A: TemplateResolver在初始化时会查询数据库，但查询结果会缓存，对性能影响很小。

## 九、总结

通过引入`TemplateResolver`服务，静态生成功能将：

✅ 支持多站点独立生成
✅ 支持模板包系统
✅ 支持站点自定义覆盖
✅ 支持配置合并
✅ 保持向后兼容
✅ 提供更好的灵活性

---

**文档创建时间**: 2025-11-17
**状态**: 设计完成，待实施
