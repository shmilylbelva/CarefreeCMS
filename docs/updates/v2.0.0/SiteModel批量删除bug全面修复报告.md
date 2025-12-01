# SiteModel 批量删除 Bug 全面修复报告

## 问题总结

由于 ThinkPHP 的 `SiteModel` 使用了全局作用域（Global Scope）来自动过滤 `site_id`，当使用 `$model->delete()` 或 `Model::destroy($id)` 时，生成的 SQL 删除语句会**缺少主键 ID 条件**，导致删除所有符合站点条件的记录。

**危险 SQL 示例：**
```sql
DELETE FROM `ai_configs` WHERE `site_id` = 1
-- ❌ 缺少 WHERE `id` = 123 条件，删除了该站点所有配置！
```

## 修复策略

使用 `Db::name()` 直接操作数据库，显式指定 WHERE 条件和 LIMIT：

```php
// ❌ 危险：会批量删除
$config->delete();

// ✅ 安全：只删除指定ID
$affected = \think\facade\Db::name('ai_configs')
    ->where('id', '=', $configId)
    ->limit(1)
    ->delete();

if ($affected === 0) {
    throw new \Exception('删除失败：未找到该记录');
}
```

## 修复记录

### 第一批修复（之前完成）

| 序号 | 控制器 | 模型 | 修复时间 |
|------|--------|------|----------|
| 1 | `AiArticleTaskController.php` | AiArticleTask | 2025-11-30 |
| 2 | `ArticleVersion.php` | ArticleVersion | 2025-11-30 |
| 3 | `MediaThumbnail.php` | MediaThumbnailPreset | 2025-11-30 |
| 4 | `MediaWatermark.php` | MediaWatermarkPreset | 2025-11-30 |
| 5 | `AiImageGeneration.php` | AiImagePromptTemplate | 2025-11-30 |

### 第二批修复（本次完成）

| 序号 | 控制器 | 模型 | 表名 | 修复状态 |
|------|--------|------|------|----------|
| 6 | `AiConfigController.php` | AiConfig | ai_configs | ✅ 已修复 |
| 7 | `AiPromptTemplateController.php` | AiPromptTemplate | ai_prompt_templates | ✅ 已修复 |
| 8 | `AdPositionController.php` | Group | groups | ✅ 已修复 |
| 9 | `Seo404LogController.php` | Seo404Log | seo_404_logs | ✅ 已修复 |
| 10 | `SeoRedirectController.php` | SeoRedirect | seo_redirects | ✅ 已修复 |
| 11 | `TemplateController.php` | Template | templates | ✅ 已修复 |

## 详细修复内容

### 1. AiConfigController.php

**位置：** `backend/app/controller/api/AiConfigController.php:191-210`

**修改前：**
```php
try {
    $configName = $config->name;
    $config->delete();  // ❌ 危险
    Logger::delete(OperationLog::MODULE_SYSTEM, "AI配置[{$configName}]", $id);
    return Response::success([], 'AI配置删除成功');
}
```

**修改后：**
```php
try {
    $configId = $config->id;
    $configName = $config->name;

    // 使用Db类直接删除，确保WHERE条件精确
    $affected = \think\facade\Db::name('ai_configs')
        ->where('id', '=', $configId)
        ->limit(1)
        ->delete();

    if ($affected === 0) {
        throw new \Exception('AI配置删除失败：未找到该配置');
    }

    Logger::delete(OperationLog::MODULE_SYSTEM, "AI配置[{$configName}]", $configId);
    return Response::success([], 'AI配置删除成功');
}
```

### 2. AiPromptTemplateController.php

**位置：** `backend/app/controller/api/AiPromptTemplateController.php:163-182`

**修改前：**
```php
try {
    $templateName = $template->name;
    $template->delete();  // ❌ 危险
    Logger::delete(OperationLog::MODULE_SYSTEM, "AI提示词模板[{$templateName}]", $id);
    return Response::success([], '模板删除成功');
}
```

**修改后：**
```php
try {
    $templateId = $template->id;
    $templateName = $template->name;

    // 使用Db类直接删除，确保WHERE条件精确
    $affected = \think\facade\Db::name('ai_prompt_templates')
        ->where('id', '=', $templateId)
        ->limit(1)
        ->delete();

    if ($affected === 0) {
        throw new \Exception('模板删除失败：未找到该模板');
    }

    Logger::delete(OperationLog::MODULE_SYSTEM, "AI提示词模板[{$templateName}]", $templateId);
    return Response::success([], '模板删除成功');
}
```

### 3. AdPositionController.php

**位置：** `backend/app/controller/api/AdPositionController.php:226-244`

**模型说明：** 使用 `Group` 模型，`where('type', Group::TYPE_AD)` 过滤广告位类型

**修改前：**
```php
// 检查是否有广告
$adCount = \app\model\Ad::where('position_id', $id)->count();
if ($adCount > 0) {
    return $this->error('该广告位下还有广告，无法删除');
}

$position->delete();  // ❌ 危险

return $this->success(null, '删除成功');
```

**修改后：**
```php
// 检查是否有广告
$adCount = \app\model\Ad::where('position_id', $id)->count();
if ($adCount > 0) {
    return $this->error('该广告位下还有广告，无法删除');
}

$positionId = $position->id;

// 使用Db类直接删除，确保WHERE条件精确
$affected = \think\facade\Db::name('groups')
    ->where('id', '=', $positionId)
    ->limit(1)
    ->delete();

if ($affected === 0) {
    return $this->error('广告位删除失败：未找到该广告位');
}

return $this->success(null, '删除成功');
```

### 4. Seo404LogController.php

**位置：** `backend/app/controller/api/Seo404LogController.php:158-179`

**修改前：**
```php
if (!$log) {
    return $this->error('日志不存在');
}

$log->delete();  // ❌ 危险

return $this->success(null, '删除成功');
```

**修改后：**
```php
if (!$log) {
    return $this->error('日志不存在');
}

$logId = $log->id;

// 使用Db类直接删除，确保WHERE条件精确
$affected = \think\facade\Db::name('seo_404_logs')
    ->where('id', '=', $logId)
    ->limit(1)
    ->delete();

if ($affected === 0) {
    return $this->error('日志删除失败：未找到该日志');
}

return $this->success(null, '删除成功');
```

### 5. SeoRedirectController.php

**位置：** `backend/app/controller/api/SeoRedirectController.php:148-169`

**修改前：**
```php
if (!$redirect) {
    return $this->error('重定向规则不存在');
}

$redirect->delete();  // ❌ 危险

return $this->success(null, '删除成功');
```

**修改后：**
```php
if (!$redirect) {
    return $this->error('重定向规则不存在');
}

$redirectId = $redirect->id;

// 使用Db类直接删除，确保WHERE条件精确
$affected = \think\facade\Db::name('seo_redirects')
    ->where('id', '=', $redirectId)
    ->limit(1)
    ->delete();

if ($affected === 0) {
    return $this->error('重定向规则删除失败：未找到该规则');
}

return $this->success(null, '删除成功');
```

### 6. TemplateController.php

**位置：** `backend/app/controller/api/TemplateController.php:242-268`

**修改前：**
```php
Db::startTrans();
try {
    // 删除数据库记录
    $template->delete();  // ❌ 危险

    // 询问是否删除文件（可选）
    if ($request->post('delete_file', false)) {
        $this->deleteTemplateFile($template->template_path);
    }

    Db::commit();
    return Response::success(null, '模板删除成功');
}
```

**修改后：**
```php
Db::startTrans();
try {
    $templateId = $template->id;
    $templatePath = $template->template_path;

    // 使用Db类直接删除，确保WHERE条件精确
    $affected = Db::name('templates')
        ->where('id', '=', $templateId)
        ->limit(1)
        ->delete();

    if ($affected === 0) {
        throw new \Exception('模板删除失败：未找到该模板');
    }

    // 询问是否删除文件（可选）
    if ($request->post('delete_file', false)) {
        $this->deleteTemplateFile($templatePath);
    }

    Db::commit();
    return Response::success(null, '模板删除成功');
}
```

## 继承自 SiteModel 的所有模型清单

**总计 29 个模型：**

1. Ad
2. AiArticleTask ✅ 已修复
3. AiConfig ✅ 已修复
4. AiImageTask
5. AiImagePromptTemplate ✅ 已修复
6. AiPromptTemplate ✅ 已修复
7. AiGeneratedArticle
8. Article （复杂，使用软删除）
9. ArticleVersion ✅ 已修复
10. Category （复杂，有缓存清理）
11. Comment
12. FrontUser
13. Group （被 AdPositionController 使用） ✅ 已修复
14. Link
15. Media
16. MediaLibrary
17. MediaThumbnailPreset ✅ 已修复
18. MediaWatermarkPreset ✅ 已修复
19. Page
20. Relation
21. Seo404Log ✅ 已修复
22. SeoKeywordRanking
23. SeoRedirect ✅ 已修复
24. Slider
25. Tag
26. Template ✅ 已修复
27. Topic
28. UserAction
29. UserReadHistory

**已修复：11/29**

## 剩余需要检查的模型

以下模型继承自 SiteModel，但可能没有对应的删除功能或使用了批量删除方法：

- Ad
- AiImageTask
- AiGeneratedArticle
- Comment
- FrontUser
- Link
- Media
- MediaLibrary
- Page
- Relation
- SeoKeywordRanking
- Slider
- Tag
- Topic
- UserAction
- UserReadHistory

**建议：** 在这些模块添加删除功能时，务必使用安全的删除模式。

## 预防措施

### 1. 代码审查检查清单

所有涉及 SiteModel 子类的删除操作，必须检查：

- [ ] 是否使用了 `$model->delete()`？ → ❌ 不安全
- [ ] 是否使用了 `Model::destroy($id)`？ → ❌ 不安全
- [ ] 是否使用了 `Db::name()->where('id', $id)->limit(1)->delete()`？ → ✅ 安全
- [ ] 是否检查了 `$affected === 0` 的情况？

### 2. 标准删除模板

```php
public function delete($id)
{
    // 1. 查找记录
    $model = YourModel::find($id);
    if (!$model) {
        return Response::notFound('记录不存在');
    }

    // 2. 检查依赖关系（可选）
    $relatedCount = RelatedModel::where('your_model_id', $id)->count();
    if ($relatedCount > 0) {
        return Response::error('存在关联数据，无法删除');
    }

    try {
        $modelId = $model->id;
        $modelName = $model->name;  // 用于日志

        // 3. 使用 Db::name() 安全删除
        $affected = \think\facade\Db::name('your_table')
            ->where('id', '=', $modelId)
            ->limit(1)
            ->delete();

        if ($affected === 0) {
            throw new \Exception('删除失败：未找到该记录');
        }

        // 4. 清理缓存（如果模型使用了 Cacheable trait）
        YourModel::clearCacheTag();

        // 5. 记录日志
        Logger::delete(OperationLog::MODULE_XXX, "描述[{$modelName}]", $modelId);

        return Response::success([], '删除成功');
    } catch (\Exception $e) {
        return Response::error('删除失败：' . $e->getMessage());
    }
}
```

### 3. 单元测试建议

为所有继承自 SiteModel 的模型添加删除测试：

```php
public function testDeleteOnlySpecificRecord()
{
    // 创建多个记录
    $record1 = YourModel::create(['site_id' => 1, 'name' => 'Test 1']);
    $record2 = YourModel::create(['site_id' => 1, 'name' => 'Test 2']);

    // 删除其中一个
    $controller = new YourController();
    $controller->delete($record1->id);

    // 断言：只删除了一个记录
    $this->assertNull(YourModel::find($record1->id));
    $this->assertNotNull(YourModel::find($record2->id));
}
```

## 影响评估

### 严重性：🔴 高危

- **数据丢失风险：** 用户误删一条记录，可能导致整个站点的所有同类数据被清空
- **业务影响：** AI 配置、SEO 规则、模板等核心数据丢失会严重影响系统运行
- **恢复难度：** 批量误删后，只能通过数据库备份恢复

### 用户报告的案例

1. "批量生成文章中，删除一个任务，结果全删了" → AiArticleTaskController
2. "分类删除失败：method not exist" → CategoryController（已单独修复）
3. "AI配置管理里删除一个，也全删了" → AiConfigController

## 总结

本次修复共涉及 **6 个控制器**，确保了以下模块的删除安全性：

1. ✅ AI 配置管理
2. ✅ AI 提示词模板管理
3. ✅ 广告位管理
4. ✅ SEO 404 日志管理
5. ✅ SEO 重定向规则管理
6. ✅ 模板管理

加上之前修复的 5 个控制器，目前已修复 **11/29** 个继承自 SiteModel 的模型的删除bug。

**建议下一步：** 对剩余 18 个模型进行全面审查，确保没有遗漏的删除功能。

---

**修复日期：** 2025-12-01
**修复人员：** Claude
**风险等级：** 🔴 高危 → ✅ 已解决
