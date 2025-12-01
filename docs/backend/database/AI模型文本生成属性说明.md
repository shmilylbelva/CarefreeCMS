# AI模型文本生成属性说明

## 📋 更新内容

为AI模型表添加了"文本生成"能力属性，并确保批量文章生成功能只能使用支持文本生成的AI模型。

**更新时间**: 2025-01-13

---

## 🎯 修改目的

### 业务需求
批量文章生成功能需要明确区分哪些AI模型支持文本生成能力。虽然大部分语言模型都支持文本生成，但未来可能会集成：
- 纯图像生成模型（如DALL-E、Midjourney）
- 纯音频生成模型（如音乐生成、音效生成）
- 纯嵌入模型（如text-embedding-ada-002）
- 其他专用模型

这些模型不适合用于批量文章生成，需要在选择时进行过滤。

### 技术实现
1. **数据库层**: 添加 `supports_text_generation` 字段
2. **后端层**: 修改AI配置查询接口，支持按文本生成能力筛选
3. **前端层**: 批量文章生成页面只加载支持文本生成的AI配置

---

## 📊 数据库修改

### 新增字段

```sql
ALTER TABLE ai_models
ADD COLUMN supports_text_generation TINYINT(1) DEFAULT 1
COMMENT '支持文本生成'
AFTER supports_functions;
```

| 字段名 | 类型 | 默认值 | 说明 |
|--------|------|--------|------|
| supports_text_generation | TINYINT(1) | 1 | 是否支持文本生成能力 |

### 字段位置
插入在 `supports_functions` 字段之后，作为第一个能力字段，符合逻辑顺序：
```
supports_functions        ← 基础能力
supports_text_generation  ← 文本生成（新增）✨
supports_image_input      ← 图像能力
supports_audio_input      ← 音频能力
...
```

### 数据初始化

```sql
-- 将所有激活的模型设置为支持文本生成（默认值）
UPDATE ai_models
SET supports_text_generation = 1
WHERE status = 1;
```

**说明**: 当前系统中所有模型都是语言模型，均支持文本生成，因此默认值设为1。

---

## 🔧 后端修改

### 文件: `backend/app/controller/api/AiConfigController.php`

#### 修改点: `all()` 方法

**位置**: 第51-78行

**修改前**:
```php
public function all(Request $request)
{
    $list = AiConfig::where('status', 1)
        ->order('is_default', 'desc')
        ->order('id', 'desc')
        ->field('id,name,provider,model,is_default')
        ->select();

    return Response::success($list->toArray());
}
```

**修改后**:
```php
public function all(Request $request)
{
    // 是否只显示支持文本生成的配置（用于批量文章生成）
    $textGenerationOnly = $request->get('text_generation_only', false);

    // 基础查询
    $query = AiConfig::alias('ac')
        ->where('ac.status', 1)
        ->order('ac.is_default', 'desc')
        ->order('ac.id', 'desc')
        ->field('ac.id,ac.name,ac.provider,ac.model,ac.is_default');

    // 如果需要筛选支持文本生成的配置
    if ($textGenerationOnly) {
        $query->join('ai_providers ap', 'ac.provider = ap.code')
              ->join('ai_models am', 'ap.id = am.provider_id AND ac.model = am.model_code')
              ->where('am.supports_text_generation', 1)
              ->where('am.status', 1);
    }

    $list = $query->select();

    return Response::success($list->toArray());
}
```

#### 修改说明

1. **新增参数**: `text_generation_only` (布尔值，默认false)
   - false: 返回所有AI配置（保持原有行为，兼容性）
   - true: 只返回支持文本生成的AI配置

2. **SQL关联查询**:
   ```
   ai_configs (ac)
   └─ JOIN ai_providers (ap) ON ac.provider = ap.code
      └─ JOIN ai_models (am) ON ap.id = am.provider_id
                            AND ac.model = am.model_code
         └─ WHERE am.supports_text_generation = 1
   ```

3. **向后兼容**:
   - 不传参数时，行为与之前完全一致
   - 其他页面（如文章编辑页）不受影响

---

## 🎨 前端修改

### 文件1: `frontend/src/api/ai.js`

#### 修改点: `getAllAiConfigs()` 函数

**位置**: 第13-21行

**修改前**:
```javascript
export function getAllAiConfigs() {
  return request({
    url: '/ai-configs/all',
    method: 'get'
  })
}
```

**修改后**:
```javascript
// 获取所有AI配置（下拉选择）
// params.text_generation_only: 是否只获取支持文本生成的配置（用于批量文章生成）
export function getAllAiConfigs(params = {}) {
  return request({
    url: '/ai-configs/all',
    method: 'get',
    params
  })
}
```

---

### 文件2: `frontend/src/views/ai/TaskList.vue`

#### 修改点: `fetchAiConfigs()` 函数

**位置**: 第449-457行

**修改前**:
```javascript
// 获取AI配置列表
const fetchAiConfigs = async () => {
  try {
    const res = await getAllAiConfigs()
    aiConfigs.value = res.data
  } catch (error) {
    console.error('获取AI配置失败:', error)
  }
}
```

**修改后**:
```javascript
// 获取AI配置列表（只获取支持文本生成的配置）
const fetchAiConfigs = async () => {
  try {
    const res = await getAllAiConfigs({ text_generation_only: true })
    aiConfigs.value = res.data
  } catch (error) {
    console.error('获取AI配置失败:', error)
  }
}
```

#### 影响范围

**只影响**: AI文章生成任务页面 (`/ai/tasks`)
- 创建任务时的AI配置选择器
- 编辑任务时的AI配置选择器

**不影响**: 其他页面
- AI配置管理页面
- AI模型管理页面
- 文章编辑页面的AI助手
- 其他任何使用AI配置的地方

---

## ✅ 测试验证

### 数据库验证

```sql
-- 1. 验证字段已添加
DESCRIBE ai_models;
-- 应该能看到 supports_text_generation 字段

-- 2. 查看支持文本生成的模型数量
SELECT
    COUNT(*) AS total_models,
    SUM(supports_text_generation) AS text_generation_models
FROM ai_models
WHERE status = 1;
-- 当前应该都是1（所有模型都支持）

-- 3. 查看各厂商支持文本生成的模型
SELECT
    p.name AS provider,
    COUNT(*) AS model_count
FROM ai_models m
JOIN ai_providers p ON m.provider_id = p.id
WHERE m.supports_text_generation = 1
  AND m.status = 1
GROUP BY p.id, p.name
ORDER BY model_count DESC;
```

### API测试

```bash
# 1. 不带参数（应返回所有配置）
curl http://localhost:8000/api/ai-configs/all

# 2. 带text_generation_only=false（应返回所有配置）
curl "http://localhost:8000/api/ai-configs/all?text_generation_only=false"

# 3. 带text_generation_only=true（应只返回支持文本生成的配置）
curl "http://localhost:8000/api/ai-configs/all?text_generation_only=true"
```

### 前端功能测试

1. **批量文章生成页面**:
   - 访问 `/ai/tasks`
   - 点击"创建任务"
   - 检查"AI配置"下拉框是否正常显示
   - 应该只显示支持文本生成的AI配置

2. **其他页面验证**:
   - 确认文章编辑页面的AI助手功能正常
   - 确认AI配置管理页面正常工作
   - 确认其他使用AI的功能不受影响

---

## 🚀 未来扩展

### 添加不支持文本生成的模型时

当系统集成纯图像/音频/嵌入模型时，需要将这些模型的 `supports_text_generation` 设为 0：

```sql
-- 示例：添加DALL-E图像生成模型
INSERT INTO ai_models (
    provider_id,
    model_code,
    model_name,
    supports_text_generation,  -- 设为0
    supports_image_generation, -- 设为1
    ...
) VALUES (
    1,  -- OpenAI provider_id
    'dall-e-3',
    'DALL-E 3',
    0,  -- 不支持文本生成
    1,  -- 支持图像生成
    ...
);
```

### 其他功能的类似限制

未来可以根据需要添加类似的筛选逻辑：

1. **图像生成功能**: 只显示 `supports_image_generation = 1` 的模型
2. **语音对话功能**: 只显示 `supports_realtime_voice = 1` 的模型
3. **文档分析功能**: 只显示 `supports_document_parsing = 1` 的模型
4. **代码生成功能**: 只显示 `supports_code_generation = 1` 的模型

实现方式完全相同，只需：
1. 在API调用时传递对应的筛选参数
2. 后端根据参数筛选对应的能力字段

---

## 📝 关键要点

### ✨ 优点

1. **业务逻辑清晰**: 明确区分了文本生成能力
2. **向后兼容**: 不影响现有功能
3. **易于扩展**: 未来可轻松添加其他能力限制
4. **性能优化**: 通过JOIN查询，一次性获取满足条件的配置

### ⚠️ 注意事项

1. **数据一致性**:
   - 添加新AI配置时，确保model字段与ai_models表中的model_code匹配
   - provider字段必须与ai_providers表中的code匹配

2. **默认值设置**:
   - 新增语言模型时，supports_text_generation应设为1
   - 新增图像/音频模型时，supports_text_generation应设为0

3. **API兼容性**:
   - 保持text_generation_only参数为可选
   - 默认值false确保不破坏现有调用

---

## 🔗 相关文件

- **数据库**: `ai_models` 表
- **后端控制器**: `backend/app/controller/api/AiConfigController.php`
- **前端API**: `frontend/src/api/ai.js`
- **前端页面**: `frontend/src/views/ai/TaskList.vue`
- **说明文档**: 本文件

---

## 📚 相关属性

与文本生成相关的其他能力属性：

| 属性 | 说明 | 与文本生成的关系 |
|------|------|-----------------|
| supports_text_generation | 文本生成 | 核心能力 |
| supports_code_generation | 代码生成 | 特殊的文本生成 |
| supports_document_parsing | 文档解析 | 输入能力，常配合文本生成 |
| supports_streaming | 流式输出 | 文本生成的输出方式 |

---

**文档版本**: v1.0
**最后更新**: 2025-01-13
**维护者**: AI系统管理员
