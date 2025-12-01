# 媒体库系统前端集成文档

## 📦 已创建的文件清单

### Vue组件 (6个)
位置: `frontend/src/components/MediaLibrary/`

1. **MediaSelector.vue** - 媒体文件选择器
2. **ChunkedUpload.vue** - 分片上传组件
3. **ImageCropper.vue** - 图片裁剪组件
4. **VideoPlayer.vue** - 视频播放器
5. **StorageConfig.vue** - 存储配置管理
6. **QueueMonitor.vue** - 队列监控面板

### 子组件 (3个)
位置: `frontend/src/components/MediaLibrary/`

7. **AiImageTasks.vue** - AI图片任务列表
8. **VideoTranscodeTasks.vue** - 视频转码任务列表
9. **QueueLogs.vue** - 队列日志查看器

### API服务 (7个)
位置: `frontend/src/api/`

1. **media.js** - 媒体文件管理API
2. **chunkedUpload.js** - 分片上传API
3. **storage.js** - 存储配置API
4. **queue.js** - 队列管理API
5. **mediaEdit.js** - 媒体编辑API
6. **video.js** - 视频处理API
7. **aiImage.js** - AI图片生成API

---

## 📋 前端依赖安装

需要安装以下npm包:

```bash
cd frontend

# 核心依赖 (如果还没安装)
npm install vue@^3.3.0
npm install element-plus@^2.4.0
npm install axios@^1.5.0

# 新增依赖
npm install cropperjs@^1.6.0           # 图片裁剪
npm install spark-md5@^3.0.2           # MD5哈希计算
```

### package.json 更新

在 `frontend/package.json` 的 `dependencies` 中添加:

```json
{
  "dependencies": {
    "vue": "^3.3.0",
    "element-plus": "^2.4.0",
    "axios": "^1.5.0",
    "cropperjs": "^1.6.0",
    "spark-md5": "^3.0.2"
  }
}
```

---

## 🚀 组件使用指南

### 1. MediaSelector - 媒体选择器

**功能**: 媒体库文件选择,支持网格/列表视图、分类筛选、多选

**使用示例**:

```vue
<template>
  <div>
    <el-button @click="showSelector = true">选择媒体文件</el-button>

    <MediaSelector
      v-model="showSelector"
      :multiple="true"
      accept="image/*"
      :maxSize="10 * 1024 * 1024"
      fileType="image"
      @confirm="handleMediaSelect"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import MediaSelector from '@/components/MediaLibrary/MediaSelector.vue'

const showSelector = ref(false)

const handleMediaSelect = (files) => {
  console.log('选中的文件:', files)
  // files是一个数组,包含选中的媒体文件对象
}
</script>
```

**Props**:
- `modelValue` - 是否显示对话框
- `multiple` - 是否允许多选 (默认: false)
- `accept` - 接受的文件类型 (默认: '')
- `maxSize` - 最大文件大小,字节 (默认: 100MB)
- `fileType` - 文件类型过滤 (image/video/audio)
- `title` - 对话框标题

**Events**:
- `confirm` - 确认选择时触发,参数: 选中的文件数组

---

### 2. ChunkedUpload - 分片上传

**功能**: 大文件分片上传,支持断点续传、进度显示、并发控制

**使用示例**:

```vue
<template>
  <ChunkedUpload
    ref="uploadRef"
    :chunkSize="2 * 1024 * 1024"
    :concurrent="3"
    :autoStart="false"
    @success="handleUploadSuccess"
    @error="handleUploadError"
  />
</template>

<script setup>
import { ref } from 'vue'
import ChunkedUpload from '@/components/MediaLibrary/ChunkedUpload.vue'

const uploadRef = ref(null)

const handleUploadSuccess = (file, response) => {
  console.log('上传成功:', file, response)
}

const handleUploadError = (file, error) => {
  console.error('上传失败:', file, error)
}
</script>
```

**Props**:
- `chunkSize` - 分片大小 (默认: 2MB)
- `concurrent` - 并发上传数 (默认: 3)
- `autoStart` - 是否自动开始上传 (默认: true)
- `accept` - 接受的文件类型
- `maxSize` - 单文件最大大小
- `limit` - 文件数量限制

**Events**:
- `success` - 上传成功
- `error` - 上传失败
- `progress` - 上传进度更新

---

### 3. ImageCropper - 图片裁剪

**功能**: 专业级图片裁剪,支持多种宽高比、旋转、翻转、缩放

**使用示例**:

```vue
<template>
  <ImageCropper
    v-model="showCropper"
    :imageUrl="currentImage"
    :mediaId="imageId"
    :aspectRatio="16/9"
    :saveToServer="true"
    @success="handleCropSuccess"
  />
</template>

<script setup>
import { ref } from 'vue'
import ImageCropper from '@/components/MediaLibrary/ImageCropper.vue'

const showCropper = ref(false)
const currentImage = ref('')
const imageId = ref(null)

const handleCropSuccess = (result) => {
  console.log('裁剪成功:', result)
  // result: { url, blob, mediaId }
}
</script>
```

**Props**:
- `modelValue` - 是否显示
- `imageUrl` - 图片URL
- `mediaId` - 媒体ID (服务端裁剪时需要)
- `aspectRatio` - 宽高比 (如: 16/9, 1, NaN表示自由)
- `saveToServer` - 是否保存到服务器
- `outputFormat` - 输出格式 (jpeg/png/webp)
- `quality` - 输出质量 (1-100)

**Events**:
- `success` - 裁剪成功
- `cancel` - 取消裁剪

---

### 4. VideoPlayer - 视频播放器

**功能**: 全功能HTML5视频播放器,支持画中画、倍速、快捷键

**使用示例**:

```vue
<template>
  <VideoPlayer
    :src="videoUrl"
    :poster="posterUrl"
    :autoplay="false"
    :controls="true"
    :qualities="videoQualities"
    @play="handlePlay"
    @pause="handlePause"
  />
</template>

<script setup>
import VideoPlayer from '@/components/MediaLibrary/VideoPlayer.vue'

const videoUrl = ref('https://example.com/video.mp4')
const posterUrl = ref('https://example.com/poster.jpg')
const videoQualities = ref([
  { label: '1080P', url: 'https://example.com/video_1080p.mp4' },
  { label: '720P', url: 'https://example.com/video_720p.mp4' },
  { label: '480P', url: 'https://example.com/video_480p.mp4' }
])

const handlePlay = () => {
  console.log('开始播放')
}

const handlePause = () => {
  console.log('暂停播放')
}
</script>
```

**Props**:
- `src` - 视频源URL
- `poster` - 封面图URL
- `autoplay` - 自动播放
- `loop` - 循环播放
- `controls` - 显示控制栏
- `qualities` - 清晰度选项数组

**快捷键**:
- `Space` - 播放/暂停
- `←/→` - 快退/快进5秒
- `F` - 全屏
- `M` - 静音

---

### 5. StorageConfig - 存储配置

**功能**: 云存储配置管理,支持本地、阿里云OSS、腾讯云COS、七牛云

**使用示例**:

```vue
<template>
  <div>
    <StorageConfig />
  </div>
</template>

<script setup>
import StorageConfig from '@/components/MediaLibrary/StorageConfig.vue'
</script>
```

**特性**:
- 支持4种存储驱动 (local/aliyun_oss/tencent_cos/qiniu)
- 动态表单根据驱动类型生成
- 连接测试功能
- 设置默认存储
- 启用/禁用切换

---

### 6. QueueMonitor - 队列监控

**功能**: 实时监控队列任务状态,查看任务详情和日志

**使用示例**:

```vue
<template>
  <div>
    <QueueMonitor />
  </div>
</template>

<script setup>
import QueueMonitor from '@/components/MediaLibrary/QueueMonitor.vue'
</script>
```

**特性**:
- 4种队列统计卡片 (AI图片、缩略图、水印、视频)
- 自动刷新 (每5秒)
- 清空队列功能
- 任务列表查看
- 日志查看

---

## 🔌 路由配置

在 `frontend/src/router/index.js` 中添加路由:

```javascript
{
  path: '/media',
  name: 'MediaLibrary',
  component: () => import('@/views/MediaLibrary.vue'),
  children: [
    {
      path: 'list',
      name: 'MediaList',
      component: () => import('@/views/media/List.vue')
    },
    {
      path: 'upload',
      name: 'MediaUpload',
      component: () => import('@/views/media/Upload.vue')
    },
    {
      path: 'storage',
      name: 'StorageConfig',
      component: () => import('@/components/MediaLibrary/StorageConfig.vue')
    },
    {
      path: 'queue',
      name: 'QueueMonitor',
      component: () => import('@/components/MediaLibrary/QueueMonitor.vue')
    }
  ]
}
```

---

## 📡 API端点参考

### 媒体管理
```
GET    /api/media                    # 获取媒体列表
GET    /api/media/{id}                # 获取媒体详情
POST   /api/media/upload              # 上传媒体
PUT    /api/media/{id}                # 更新媒体
DELETE /api/media/{id}                # 删除媒体
GET    /api/media/stats               # 获取统计
```

### 分片上传
```
POST   /api/chunked-upload/init       # 初始化上传
POST   /api/chunked-upload/chunk      # 上传分片
POST   /api/chunked-upload/merge      # 合并分片
GET    /api/chunked-upload/progress   # 获取进度
POST   /api/chunked-upload/cancel     # 取消上传
```

### 存储配置
```
GET    /api/storage-config            # 获取配置列表
POST   /api/storage-config            # 创建配置
PUT    /api/storage-config/{id}       # 更新配置
DELETE /api/storage-config/{id}       # 删除配置
POST   /api/storage-config/test       # 测试连接
POST   /api/storage-config/{id}/set-default  # 设为默认
GET    /api/storage-config/drivers    # 获取驱动列表
```

### 队列管理
```
GET    /api/queue/stats               # 获取统计
POST   /api/queue/clear               # 清空队列
GET    /api/queue/ai-image/tasks      # AI图片任务
GET    /api/queue/video/tasks         # 视频任务
GET    /api/queue/logs                # 队列日志
```

### 媒体编辑
```
POST   /api/media/crop                # 裁剪图片
POST   /api/media/resize              # 调整大小
POST   /api/media/rotate              # 旋转
POST   /api/media/watermark           # 添加水印
POST   /api/media/thumbnail           # 生成缩略图
```

### 视频处理
```
POST   /api/video/transcode           # 转码视频
GET    /api/video/{id}/info           # 获取视频信息
POST   /api/video/clip                # 截取片段
POST   /api/video/extract-cover       # 提取封面
POST   /api/video/merge               # 合并视频
```

### AI图片生成
```
POST   /api/ai-image/generate         # 生成图片
GET    /api/ai-image/progress/{id}    # 获取进度
POST   /api/ai-image/image-to-image   # 图生图
POST   /api/ai-image/upscale          # 超分辨率
POST   /api/ai-image/remove-background # 移除背景
```

---

## 🎨 完整页面示例

### 媒体库管理页面

```vue
<template>
  <div class="media-library-page">
    <el-page-header @back="goBack" content="媒体库管理" />

    <el-row :gutter="20" style="margin-top: 20px">
      <!-- 左侧: 媒体列表 -->
      <el-col :span="18">
        <el-card>
          <template #header>
            <div class="card-header">
              <span>媒体文件</span>
              <el-button type="primary" @click="showUpload = true">
                上传文件
              </el-button>
            </div>
          </template>

          <MediaSelector
            v-model="showSelector"
            :multiple="true"
            @confirm="handleSelect"
          />
        </el-card>
      </el-col>

      <!-- 右侧: 工具面板 -->
      <el-col :span="6">
        <el-card>
          <template #header>快捷操作</template>
          <el-menu>
            <el-menu-item @click="showUpload = true">
              <el-icon><Upload /></el-icon>
              <span>上传文件</span>
            </el-menu-item>
            <el-menu-item @click="$router.push('/media/storage')">
              <el-icon><Setting /></el-icon>
              <span>存储配置</span>
            </el-menu-item>
            <el-menu-item @click="$router.push('/media/queue')">
              <el-icon><Monitor /></el-icon>
              <span>队列监控</span>
            </el-menu-item>
          </el-menu>
        </el-card>
      </el-col>
    </el-row>

    <!-- 上传对话框 -->
    <el-dialog v-model="showUpload" title="上传文件" width="800px">
      <ChunkedUpload
        :autoStart="false"
        @success="handleUploadSuccess"
      />
    </el-dialog>

    <!-- 图片裁剪 -->
    <ImageCropper
      v-model="showCropper"
      :imageUrl="cropperImage"
      @success="handleCropSuccess"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import MediaSelector from '@/components/MediaLibrary/MediaSelector.vue'
import ChunkedUpload from '@/components/MediaLibrary/ChunkedUpload.vue'
import ImageCropper from '@/components/MediaLibrary/ImageCropper.vue'

const router = useRouter()
const showSelector = ref(false)
const showUpload = ref(false)
const showCropper = ref(false)
const cropperImage = ref('')

const handleSelect = (files) => {
  console.log('选中文件:', files)
}

const handleUploadSuccess = (file, response) => {
  showUpload.value = false
  ElMessage.success('上传成功')
}

const handleCropSuccess = (result) => {
  ElMessage.success('裁剪成功')
}

const goBack = () => {
  router.back()
}
</script>
```

---

## ⚙️ Axios配置

确保 `frontend/src/utils/request.js` 正确配置:

```javascript
import axios from 'axios'
import { ElMessage } from 'element-plus'

const service = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  timeout: 60000
})

// 请求拦截器
service.interceptors.request.use(
  config => {
    // 添加token
    const token = localStorage.getItem('token')
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }
    return config
  },
  error => {
    return Promise.reject(error)
  }
)

// 响应拦截器
service.interceptors.response.use(
  response => {
    const res = response.data

    if (res.code !== 200 && res.code !== 0) {
      ElMessage.error(res.message || '请求失败')
      return Promise.reject(new Error(res.message || 'Error'))
    }

    return res
  },
  error => {
    ElMessage.error(error.message || '网络错误')
    return Promise.reject(error)
  }
)

export default service
```

---

## 🔧 环境变量配置

在 `frontend/.env` 中配置:

```env
# API基础URL
VITE_API_BASE_URL=http://localhost:8000

# 上传相关
VITE_UPLOAD_MAX_SIZE=104857600  # 100MB
VITE_CHUNK_SIZE=2097152         # 2MB
```

---

## 📝 注意事项

1. **Cropper.js样式**: ImageCropper组件已导入cropperjs样式,确保不重复导入

2. **SparkMD5使用**: 用于计算文件MD5哈希,支持分片计算

3. **并发控制**: ChunkedUpload默认3个并发,可根据需要调整

4. **错误处理**: 所有API调用都已包含错误处理,使用ElMessage显示错误

5. **权限控制**: 某些操作可能需要权限验证,在路由守卫中添加

6. **文件大小限制**:
   - 默认单文件最大100MB
   - 服务端需同步配置php.ini的upload_max_filesize和post_max_size

7. **CORS配置**: 如前后端分离,确保后端正确配置CORS

---

## 🚀 快速开始

```bash
# 1. 安装依赖
cd frontend
npm install

# 2. 启动开发服务器
npm run dev

# 3. 访问页面
# 打开浏览器访问 http://localhost:5173
```

---

## 📚 参考文档

- [Vue 3](https://cn.vuejs.org/)
- [Element Plus](https://element-plus.org/)
- [Cropper.js](https://github.com/fengyuanchen/cropperjs)
- [SparkMD5](https://github.com/satazor/js-spark-md5)

---

## 🐛 常见问题

### Q: 上传大文件失败?
A: 检查服务端配置和网络超时设置,建议调整chunk_size和concurrent参数

### Q: 图片裁剪后质量下降?
A: 调整ImageCropper的quality参数 (1-100),默认90

### Q: 视频播放器在某些浏览器不工作?
A: 确保视频格式浏览器支持,推荐使用MP4 (H.264编码)

### Q: 队列任务处理缓慢?
A: 检查后端队列worker数量和Redis配置

---

完成时间: 2025-11-19
版本: v1.0.0
