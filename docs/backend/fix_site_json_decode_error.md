# 修复站点服务JSON解码错误

## 问题描述

接口 `/api/sites/1` 报错：
```
json_decode(): Argument #1 ($json) must be of type string, array given
```

## 问题原因

在 `app/service/SiteService.php` 的 `update()` 方法中，代码尝试对已经被ThinkPHP类型转换系统自动转换为数组的JSON字段进行 `json_decode` 操作。

### ThinkPHP类型转换机制

在 `app/model/Site.php` 中定义了类型转换：

```php
protected $type = [
    'config'             => 'json',
    'seo_config'         => 'json',
    'analytics_config'   => 'json',
    'storage_config'     => 'json',
    // ...
];
```

当模型字段被标记为 `'json'` 类型时，ThinkPHP会自动：
- **读取时**：将数据库中的JSON字符串转换为PHP数组
- **写入时**：将PHP数组转换为JSON字符串

因此，访问 `$site->seo_config` 或 `$site->config` 时，得到的是**已解码的数组**，而不是JSON字符串。

## 修改的文件

### SiteService.php

**位置**: `app/service/SiteService.php`

#### 第一处修改 (line 229-235)

**修改前**:
```php
if (!empty($seoConfig)) {
    // 如果站点已有SEO配置，合并
    $existingSeoConfig = $site->seo_config ? json_decode($site->seo_config, true) : [];
    $mergedSeoConfig = array_merge($existingSeoConfig, $seoConfig);
    $data['seo_config'] = json_encode($mergedSeoConfig, JSON_UNESCAPED_UNICODE);
}
```

**修改后**:
```php
if (!empty($seoConfig)) {
    // 如果站点已有SEO配置，合并
    // 注意：ThinkPHP的类型转换已将seo_config转为数组，无需json_decode
    $existingSeoConfig = is_array($site->seo_config) ? $site->seo_config : [];
    $mergedSeoConfig = array_merge($existingSeoConfig, $seoConfig);
    $data['seo_config'] = json_encode($mergedSeoConfig, JSON_UNESCAPED_UNICODE);
}
```

#### 第二处修改 (line 251-257)

**修改前**:
```php
if (!empty($coreConfig)) {
    // 如果站点已有核心配置，合并
    $existingConfig = $site->config ? json_decode($site->config, true) : [];
    $mergedConfig = array_merge($existingConfig, $coreConfig);
    $data['config'] = json_encode($mergedConfig, JSON_UNESCAPED_UNICODE);
}
```

**修改后**:
```php
if (!empty($coreConfig)) {
    // 如果站点已有核心配置，合并
    // 注意：ThinkPHP的类型转换已将config转为数组，无需json_decode
    $existingConfig = is_array($site->config) ? $site->config : [];
    $mergedConfig = array_merge($existingConfig, $coreConfig);
    $data['config'] = json_encode($mergedConfig, JSON_UNESCAPED_UNICODE);
}
```

## 修改说明

### 核心变更

1. **移除json_decode调用**: 不再对已转换的数组调用 `json_decode()`
2. **使用is_array检查**: 使用 `is_array()` 确保字段是数组类型
3. **保持兼容性**: 如果字段不是数组，则使用空数组作为默认值

### 为什么使用is_array而不是直接使用?

使用 `is_array($site->seo_config) ? $site->seo_config : []` 而不是 `$site->seo_config ?: []` 的原因：
- 确保类型安全：明确检查是否为数组类型
- 防止类型错误：如果数据库中存储了无效数据，is_array检查可以防止错误
- 代码可读性：明确表达"我们期望这是一个数组"的意图

## 测试验证

### 测试接口

```bash
# 1. 获取认证令牌
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# 2. 测试站点详情接口
curl -X GET http://localhost:8000/api/sites/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### 预期结果

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "id": 1,
    "site_code": "main",
    "site_name": "国产CMS",
    "seo_config": {
      "seo_title": "国产CMS-title",
      "seo_keywords": "国产CMS,cms,cms中国",
      "seo_description": "国产CMS描述文件"
    },
    "config": null,
    ...
  }
}
```

## 最佳实践建议

### 处理ThinkPHP的JSON字段

当模型定义了JSON类型转换时：

```php
// ✅ 正确：直接使用，已经是数组
$config = $model->json_field;
if (is_array($config)) {
    // 处理数组
}

// ✅ 正确：检查后使用
$config = is_array($model->json_field) ? $model->json_field : [];

// ❌ 错误：重复解码
$config = json_decode($model->json_field, true); // 会报错！
```

### 写入JSON字段

```php
// ✅ 正确：可以直接赋值数组，ThinkPHP会自动编码
$model->json_field = ['key' => 'value'];
$model->save();

// ✅ 也可以：手动编码（如需要特定JSON选项）
$model->json_field = json_encode(['key' => 'value'], JSON_UNESCAPED_UNICODE);
$model->save();
```

## 相关文档

- [ThinkPHP 8 模型类型转换](https://doc.thinkphp.cn/v8/model/type.html)
- [站点管理服务](./site_management.md)
- [移除全局site_url配置](./remove_global_site_url.md)

## 注意事项

1. **检查其他服务**: 其他使用JSON字段的Service类也应检查是否存在类似问题
2. **统一处理方式**: 建议在整个项目中统一JSON字段的处理方式
3. **类型检查**: 在处理JSON字段前，建议使用 `is_array()` 进行类型检查
4. **数据库迁移**: 如果数据库中存在格式错误的JSON数据，应进行清理

## 相关问题排查

如果遇到类似的JSON decode错误：

1. 检查模型的 `$type` 属性中是否定义了 `'json'` 类型转换
2. 检查代码中是否对已转换的字段调用了 `json_decode()`
3. 使用 `var_dump()` 或 `dump()` 查看字段的实际类型
4. 检查是否有自定义的获取器(Accessor)也在处理JSON字段
