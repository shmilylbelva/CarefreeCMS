# 媒体库系统 API 文档

## 概述

全新的媒体库系统，支持文件上传、分类管理、标签管理、缩略图生成、水印处理、图片编辑和AI图片生成等功能。

### 核心特性

- ✅ **文件去重**：基于SHA256哈希自动去重，节省存储空间
- ✅ **多站点支持**：完整的数据隔离
- ✅ **分类标签**：无限级分类 + 灵活标签系统
- ✅ **自动缩略图**：9种内置预设，支持自定义
- ✅ **水印处理**：文字/图片/平铺三种模式
- ✅ **在线编辑**：10+种图片编辑操作
- ✅ **AI生成**：集成AI模型生成图片
- ✅ **元数据提取**：自动提取EXIF信息
- ✅ **操作日志**：完整的操作历史记录

---

## 1. 媒体文件管理

### 1.1 上传文件

**接口**: `POST /api/media/upload`

**请求参数**:
```javascript
{
  file: File,                    // 文件对象（必填）
  title: "图片标题",              // 标题（选填）
  description: "图片描述",        // 描述（选填）
  alt_text: "SEO文本",           // Alt文本（选填）
  is_public: 1,                  // 是否公开（选填，默认1）
  category_ids: [1, 2, 3],       // 分类ID数组（选填）
  tag_names: ["风景", "自然"]     // 标签名称数组（选填）
}
```

**响应示例**:
```json
{
  "code": 200,
  "message": "文件上传成功",
  "data": {
    "id": 1,
    "title": "美丽风景",
    "file_name": "landscape.jpg",
    "file_url": "http://domain.com/uploads/2025/01/19/xxx.jpg",
    "file_type": "image",
    "file_size": 1024000,
    "width": 1920,
    "height": 1080,
    "thumbnails": {
      "thumbnail": "http://domain.com/uploads/thumbnails/xxx_thumbnail.jpg",
      "small": "http://domain.com/uploads/thumbnails/xxx_small.jpg",
      "medium": "http://domain.com/uploads/thumbnails/xxx_medium.jpg",
      "large": "http://domain.com/uploads/thumbnails/xxx_large.jpg"
    }
  }
}
```

### 1.2 媒体列表

**接口**: `GET /api/media`

**查询参数**:
- `page`: 页码（默认1）
- `pageSize`: 每页数量（默认20）
- `type`: 文件类型（image/video/audio/document）
- `filename`: 文件名搜索
- `start_date`: 开始日期
- `end_date`: 结束日期
- `category_id`: 分类ID
- `tag_id`: 标签ID

**响应示例**:
```json
{
  "code": 200,
  "data": {
    "list": [...],
    "total": 100,
    "page": 1,
    "pageSize": 20
  }
}
```

### 1.3 获取媒体详情

**接口**: `GET /api/media/{id}`

### 1.4 更新媒体信息

**接口**: `PUT /api/media/{id}`

**请求参数**:
```json
{
  "title": "新标题",
  "description": "新描述",
  "alt_text": "新Alt文本",
  "is_public": 1,
  "category_ids": [1, 2],
  "tag_names": ["标签1", "标签2"]
}
```

### 1.5 删除媒体

**接口**: `DELETE /api/media/{id}`

**请求参数**:
```json
{
  "permanent": false  // 是否永久删除（默认false，进入回收站）
}
```

### 1.6 批量上传

**接口**: `POST /api/media/batch-upload`

### 1.7 存储统计

**接口**: `GET /api/media/stats`

**响应示例**:
```json
{
  "code": 200,
  "data": {
    "total_files": 1000,
    "total_size": 1073741824,
    "by_type": {
      "image": {"count": 800, "size": 858993459},
      "video": {"count": 100, "size": 107374182},
      "document": {"count": 100, "size": 107374182}
    },
    "by_storage": {
      "local": {"count": 1000, "size": 1073741824}
    }
  }
}
```

---

## 2. 分类管理

### 2.1 分类列表/树

**接口**: `GET /api/media-category`

**查询参数**:
- `parent_id`: 父分类ID（默认0，获取顶级分类）
- `flat`: 是否返回扁平列表（默认false，返回树形）

### 2.2 创建分类

**接口**: `POST /api/media-category`

**请求参数**:
```json
{
  "parent_id": 0,
  "name": "风景照片",
  "slug": "landscape",
  "description": "各种风景照片",
  "icon": "icon-image",
  "cover_image": "/uploads/xxx.jpg",
  "sort_order": 0,
  "is_visible": 1
}
```

### 2.3 更新分类

**接口**: `PUT /api/media-category/{id}`

### 2.4 删除分类

**接口**: `DELETE /api/media-category/{id}`

### 2.5 批量排序

**接口**: `POST /api/media-category/sort`

**请求参数**:
```json
{
  "sort_data": [
    {"id": 1, "sort_order": 1},
    {"id": 2, "sort_order": 2}
  ]
}
```

### 2.6 获取分类下的媒体

**接口**: `GET /api/media-category/{id}/media`

---

## 3. 标签管理

### 3.1 标签列表

**接口**: `GET /api/media-tag`

**查询参数**:
- `page`: 页码
- `pageSize`: 每页数量
- `keyword`: 关键词搜索
- `order_by`: 排序字段（usage_count/created_at/name）
- `order_dir`: 排序方向（desc/asc）

### 3.2 热门标签

**接口**: `GET /api/media-tag/popular?limit=20`

### 3.3 创建标签

**接口**: `POST /api/media-tag`

**请求参数**:
```json
{
  "name": "风景",
  "slug": "landscape",
  "description": "风景照片标签",
  "color": "#FF5733"
}
```

### 3.4 批量创建标签

**接口**: `POST /api/media-tag/batch-create`

**请求参数**:
```json
{
  "names": ["标签1", "标签2", "标签3"]
}
```

### 3.5 合并标签

**接口**: `POST /api/media-tag/merge`

**请求参数**:
```json
{
  "source_ids": [2, 3, 4],  // 要合并的标签ID
  "target_id": 1            // 目标标签ID
}
```

---

## 4. 缩略图管理

### 4.1 预设列表

**接口**: `GET /api/media-thumbnail/presets`

**内置预设**:
- `thumbnail`: 150x150 裁剪
- `small`: 320宽度 等比例
- `medium`: 640宽度 等比例
- `large`: 1024宽度 等比例
- `xlarge`: 1920宽度 等比例
- `square_sm`: 300x300 裁剪
- `square_md`: 600x600 裁剪
- `banner`: 1200x400 填充
- `webp_medium`: 640宽度 WebP格式

### 4.2 创建预设

**接口**: `POST /api/media-thumbnail/presets`

**请求参数**:
```json
{
  "name": "custom",
  "display_name": "自定义尺寸",
  "width": 800,
  "height": 600,
  "mode": "fit",          // fit/fill/crop/exact
  "quality": 85,
  "format": null,         // jpg/png/webp，null为原格式
  "is_auto_generate": 1,
  "description": "自定义缩略图"
}
```

### 4.3 生成缩略图

**接口**: `POST /api/media-thumbnail/generate`

**请求参数**:
```json
{
  "media_id": 1,
  "preset_name": "medium"  // 不传则生成所有自动生成的预设
}
```

### 4.4 批量生成

**接口**: `POST /api/media-thumbnail/batch-generate`

**请求参数**:
```json
{
  "media_ids": [1, 2, 3],
  "preset_name": "medium"
}
```

### 4.5 重新生成

**接口**: `POST /api/media-thumbnail/regenerate`

**请求参数**:
```json
{
  "media_id": 1  // 删除并重新生成所有缩略图
}
```

---

## 5. 水印管理

### 5.1 创建水印预设

**接口**: `POST /api/media-watermark/presets`

**文字水印示例**:
```json
{
  "name": "版权文字",
  "type": "text",
  "text_content": "© 2025 Company",
  "text_size": 20,
  "text_color": "#FFFFFF",
  "position": "bottom-right",
  "offset_x": 10,
  "offset_y": 10,
  "opacity": 50,
  "is_default": 0,
  "is_active": 1
}
```

**图片水印示例**:
```json
{
  "name": "Logo水印",
  "type": "image",
  "image_path": "uploads/watermark/logo.png",
  "position": "bottom-right",
  "offset_x": 10,
  "offset_y": 10,
  "opacity": 70,
  "scale": 100,
  "is_default": 1
}
```

**平铺水印示例**:
```json
{
  "name": "平铺文字",
  "type": "tiled",
  "text_content": "CONFIDENTIAL",
  "text_size": 20,
  "text_color": "#CCCCCC",
  "opacity": 20,
  "tile_spacing": 100
}
```

### 5.2 添加水印

**接口**: `POST /api/media-watermark/add`

**请求参数**:
```json
{
  "media_id": 1,
  "preset_id": 1,         // 使用预设ID
  "custom_config": {}     // 或使用自定义配置
}
```

### 5.3 批量添加水印

**接口**: `POST /api/media-watermark/batch-add`

**请求参数**:
```json
{
  "media_ids": [1, 2, 3],
  "preset_id": 1
}
```

### 5.4 水印日志

**接口**: `GET /api/media-watermark/logs`

---

## 6. 图片编辑

### 6.1 调整大小

**接口**: `POST /api/media-edit/resize`

**请求参数**:
```json
{
  "media_id": 1,
  "width": 800,
  "height": 600,
  "mode": "fit"  // fit/fill/crop/exact
}
```

### 6.2 裁剪

**接口**: `POST /api/media-edit/crop`

**请求参数**:
```json
{
  "media_id": 1,
  "x": 100,
  "y": 100,
  "width": 500,
  "height": 500
}
```

### 6.3 旋转

**接口**: `POST /api/media-edit/rotate`

**请求参数**:
```json
{
  "media_id": 1,
  "angle": 90,          // 旋转角度
  "bg_color": "#FFFFFF" // 背景色
}
```

### 6.4 翻转

**接口**: `POST /api/media-edit/flip`

**请求参数**:
```json
{
  "media_id": 1,
  "direction": "horizontal"  // horizontal/vertical
}
```

### 6.5 调整亮度

**接口**: `POST /api/media-edit/brightness`

**请求参数**:
```json
{
  "media_id": 1,
  "level": 50  // -255 到 255
}
```

### 6.6 调整对比度

**接口**: `POST /api/media-edit/contrast`

**请求参数**:
```json
{
  "media_id": 1,
  "level": 50  // -100 到 100
}
```

### 6.7 灰度化

**接口**: `POST /api/media-edit/grayscale`

### 6.8 锐化

**接口**: `POST /api/media-edit/sharpen`

### 6.9 模糊

**接口**: `POST /api/media-edit/blur`

**请求参数**:
```json
{
  "media_id": 1,
  "level": 3  // 模糊程度
}
```

### 6.10 应用滤镜

**接口**: `POST /api/media-edit/filter`

**请求参数**:
```json
{
  "media_id": 1,
  "filter_name": "sepia"  // sepia/negative/emboss/edge/sketch
}
```

### 6.11 获取滤镜列表

**接口**: `GET /api/media-edit/filters`

### 6.12 编辑历史

**接口**: `GET /api/media-edit/history?media_id=1`

---

## 7. AI图片生成

### 7.1 获取支持的AI模型

**接口**: `GET /api/ai-image/models`

### 7.2 创建生成任务

**接口**: `POST /api/ai-image/tasks`

**请求参数**:
```json
{
  "ai_model_id": 1,
  "prompt": "a beautiful sunset over mountains",
  "negative_prompt": "ugly, blurry",
  "image_count": 4,
  "width": 1024,
  "height": 1024,
  "quality": "standard",
  "style": "vivid"
}
```

**使用模板**:
```json
{
  "ai_model_id": 1,
  "template_id": 1,
  "template_variables": {
    "subject": "mountains",
    "mood": "peaceful"
  },
  "image_count": 1
}
```

### 7.3 执行任务

**接口**: `POST /api/ai-image/execute`

**请求参数**:
```json
{
  "task_id": 1
}
```

### 7.4 任务列表

**接口**: `GET /api/ai-image/tasks`

**查询参数**:
- `status`: pending/processing/completed/failed
- `user_id`: 用户ID

### 7.5 任务详情

**接口**: `GET /api/ai-image/tasks/{id}`

### 7.6 取消任务

**接口**: `POST /api/ai-image/cancel`

### 7.7 重试失败任务

**接口**: `POST /api/ai-image/retry`

### 7.8 任务统计

**接口**: `GET /api/ai-image/stats`

### 7.9 提示词模板列表

**接口**: `GET /api/ai-image/templates`

### 7.10 热门模板

**接口**: `GET /api/ai-image/templates/popular`

### 7.11 创建模板

**接口**: `POST /api/ai-image/templates`

**请求参数**:
```json
{
  "name": "风景模板",
  "category": "风景",
  "prompt_template": "a {{adjective}} {{subject}} with {{weather}} weather",
  "negative_prompt": "ugly, distorted",
  "variables": [
    {"name": "adjective", "label": "形容词", "default": "beautiful"},
    {"name": "subject", "label": "主题", "default": "landscape"},
    {"name": "weather", "label": "天气", "default": "sunny"}
  ],
  "default_width": 1024,
  "default_height": 1024,
  "default_style": "vivid",
  "description": "用于生成风景图片的模板",
  "is_public": 1
}
```

---

## 8. 数据迁移

### 8.1 从旧系统迁移

**命令**: `php think media:migrate`

**选项**:
- `--dry-run`: 试运行，不实际迁移
- `--limit=100`: 限制迁移数量
- `--force`: 强制迁移（即使已存在）

**示例**:
```bash
# 试运行，查看迁移情况
php think media:migrate --dry-run --limit=10

# 实际迁移所有数据
php think media:migrate

# 迁移前100条数据
php think media:migrate --limit=100
```

---

## 9. 前端集成示例

### 9.1 Vue组件 - 文件上传

```vue
<template>
  <div>
    <input
      type="file"
      @change="handleFileSelect"
      accept="image/*"
    />
    <button @click="upload">上传</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      selectedFile: null
    }
  },
  methods: {
    handleFileSelect(e) {
      this->selectedFile = e.target.files[0]
    },
    async upload() {
      const formData = new FormData()
      formData.append('file', this.selectedFile)
      formData.append('title', '测试图片')
      formData.append('category_ids', '1,2')
      formData.append('tag_names', '风景,自然')

      const response = await axios.post('/api/media/upload', formData)
      console.log('上传成功:', response.data)
    }
  }
}
</script>
```

### 9.2 图片编辑

```javascript
// 裁剪图片
async function cropImage(mediaId, cropData) {
  const response = await axios.post('/api/media-edit/crop', {
    media_id: mediaId,
    x: cropData.x,
    y: cropData.y,
    width: cropData.width,
    height: cropData.height
  })

  return response.data.data.file_url
}

// 应用滤镜
async function applyFilter(mediaId, filterName) {
  const response = await axios.post('/api/media-edit/filter', {
    media_id: mediaId,
    filter_name: filterName
  })

  return response.data.data.file_url
}
```

### 9.3 AI生成图片

```javascript
async function generateAIImage(prompt) {
  // 1. 创建任务
  const taskResponse = await axios.post('/api/ai-image/tasks', {
    ai_model_id: 1,
    prompt: prompt,
    image_count: 4,
    width: 1024,
    height: 1024
  })

  const taskId = taskResponse.data.data.id

  // 2. 执行任务
  const execResponse = await axios.post('/api/ai-image/execute', {
    task_id: taskId
  })

  return execResponse.data.data.media
}
```

---

## 10. 错误码说明

| 错误码 | 说明 |
|--------|------|
| 200 | 成功 |
| 400 | 参数错误 |
| 401 | 未授权 |
| 403 | 无权限 |
| 404 | 资源不存在 |
| 422 | 验证失败 |
| 500 | 服务器错误 |

---

## 11. 最佳实践

### 11.1 文件上传

1. 支持的文件类型：`jpg,jpeg,png,gif,webp,ico,pdf,doc,docx,xls,xlsx,zip,rar`
2. 最大文件大小：10MB
3. 建议为图片添加Alt文本，有利于SEO
4. 使用分类和标签便于管理

### 11.2 缩略图

1. 上传图片后自动生成4个常用尺寸缩略图
2. 可创建自定义预设
3. 使用`fit`模式保持比例，使用`crop`模式裁剪

### 11.3 水印

1. 建议创建默认水印预设
2. 文字水印使用白色+50%透明度效果最佳
3. 图片水印建议使用PNG格式（支持透明）

### 11.4 性能优化

1. 列表查询时使用分页
2. 批量操作建议分批处理
3. 大文件上传建议分片上传（TODO）
4. AI图片生成建议使用队列异步处理（TODO）

---

## 12. TODO功能

- [ ] 云存储支持（OSS/COS/七牛云）
- [ ] 视频转码
- [ ] 视频截图
- [ ] 分片上传
- [ ] 队列系统集成
- [ ] 前端媒体选择器组件
- [ ] 前端图片裁剪组件
- [ ] CDN加速支持
