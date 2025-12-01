<template>
  <el-dialog
    v-model="visible"
    title="上传文件"
    width="700px"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="chunked-upload">
      <!-- 上传区域 -->
      <el-upload
        v-if="!uploading && uploadList.length === 0"
        class="upload-area"
        drag
        :auto-upload="false"
        :accept="accept"
        :multiple="true"
        :show-file-list="false"
        :on-change="handleFileChange"
        :before-upload="beforeUpload"
      >
        <el-icon class="el-icon--upload"><upload-filled /></el-icon>
        <div class="el-upload__text">
          将文件拖到此处，或<em>点击上传</em>
        </div>
        <template #tip>
          <div class="el-upload__tip">
            <div v-if="accept">支持格式：{{ accept }}</div>
            <div>单个文件大小不超过 {{ formatFileSize(maxSize) }}</div>
            <div v-if="chunkSize">分片大小：{{ formatFileSize(chunkSize) }}</div>
          </div>
        </template>
      </el-upload>

      <!-- 文件列表 -->
      <div v-if="uploadList.length > 0" class="file-list">
        <div
          v-for="file in uploadList"
          :key="file.uid"
          class="file-item"
        >
          <div class="file-info">
            <el-icon class="file-icon"><Document /></el-icon>
            <div class="file-details">
              <div class="file-name">{{ file.name }}</div>
              <div class="file-meta">
                {{ formatFileSize(file.size) }}
                <span v-if="file.uploadId" class="upload-id">• ID: {{ file.uploadId.substring(0, 8) }}...</span>
              </div>
            </div>
          </div>

          <div class="file-progress">
            <el-progress
              :percentage="file.progress"
              :status="file.status === 'success' ? 'success' : file.status === 'error' ? 'exception' : undefined"
              :stroke-width="8"
            >
              <template #default="{ percentage }">
                <span v-if="file.status === 'uploading'">
                  {{ percentage }}% ({{ file.uploadedChunks }}/{{ file.totalChunks }})
                </span>
                <span v-else-if="file.status === 'merging'">合并中...</span>
                <span v-else-if="file.status === 'success'">✓ 完成</span>
                <span v-else-if="file.status === 'error'">✗ 失败</span>
                <span v-else>{{ percentage }}%</span>
              </template>
            </el-progress>
            <div v-if="file.speed" class="upload-speed">{{ file.speed }}</div>
          </div>

          <div class="file-actions">
            <el-button
              v-if="file.status === 'waiting' || file.status === 'error'"
              type="primary"
              size="small"
              :icon="VideoPlay"
              @click="startUpload(file)"
            >
              {{ file.status === 'error' ? '重试' : '开始' }}
            </el-button>
            <el-button
              v-if="file.status === 'uploading'"
              type="warning"
              size="small"
              :icon="VideoPause"
              @click="pauseUpload(file)"
            >
              暂停
            </el-button>
            <el-button
              v-if="file.status !== 'uploading' && file.status !== 'success'"
              type="danger"
              size="small"
              :icon="Delete"
              @click="removeFile(file)"
            >
              删除
            </el-button>
          </div>

          <div v-if="file.errorMessage" class="error-message">
            {{ file.errorMessage }}
          </div>
        </div>
      </div>

      <!-- 配置表单 -->
      <el-form
        v-if="uploadList.length > 0 && !uploading"
        :model="formData"
        label-width="80px"
        class="upload-form"
      >
        <el-form-item label="分类">
          <el-select v-model="formData.category_ids" multiple placeholder="选择分类">
            <el-option
              v-for="cat in categories"
              :key="cat.id"
              :label="cat.name"
              :value="cat.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="标签">
          <el-select
            v-model="formData.tags"
            multiple
            filterable
            allow-create
            placeholder="输入标签"
          >
            <el-option
              v-for="tag in tags"
              :key="tag"
              :label="tag"
              :value="tag"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="存储配置">
          <el-select v-model="formData.storage_config_id" placeholder="使用默认存储">
            <el-option label="默认存储" :value="null" />
            <el-option
              v-for="config in storageConfigs"
              :key="config.id"
              :label="config.name"
              :value="config.id"
            />
          </el-select>
        </el-form-item>
      </el-form>
    </div>

    <template #footer>
      <div class="dialog-footer">
        <div class="stats" v-if="uploadList.length > 0">
          <span>总计: {{ uploadList.length }} 个文件</span>
          <span>成功: {{ successCount }}</span>
          <span v-if="errorCount > 0" class="error">失败: {{ errorCount }}</span>
        </div>
        <div>
          <el-button @click="handleClose">关闭</el-button>
          <el-button
            v-if="!uploading && uploadList.length > 0"
            type="primary"
            @click="startAllUploads"
          >
            全部上传
          </el-button>
        </div>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { ElMessage } from 'element-plus'
import { UploadFilled, Document, VideoPlay, VideoPause, Delete } from '@element-plus/icons-vue'
import { initChunkedUpload, uploadChunk, mergeChunks } from '@/api/chunkedUpload'
import { getCategories, getTags } from '@/api/media'
import { getStorageConfigs } from '@/api/storage'
import SparkMD5 from 'spark-md5'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  accept: {
    type: String,
    default: ''
  },
  maxSize: {
    type: Number,
    default: 100 * 1024 * 1024 // 100MB
  },
  chunkSize: {
    type: Number,
    default: 2 * 1024 * 1024 // 2MB
  },
  concurrent: {
    type: Number,
    default: 3 // 并发上传数
  }
})

const emit = defineEmits(['update:modelValue', 'success'])

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

// 数据
const uploading = ref(false)
const uploadList = ref([])
const categories = ref([])
const tags = ref([])
const storageConfigs = ref([])

const formData = reactive({
  category_ids: [],
  tags: [],
  storage_config_id: null
})

// 统计
const successCount = computed(() => uploadList.value.filter(f => f.status === 'success').length)
const errorCount = computed(() => uploadList.value.filter(f => f.status === 'error').length)

// 文件选择
const handleFileChange = (file) => {
  const uploadFile = {
    uid: Date.now() + Math.random(),
    name: file.name,
    size: file.size,
    raw: file.raw,
    status: 'waiting',
    progress: 0,
    uploadId: null,
    totalChunks: 0,
    uploadedChunks: 0,
    currentChunk: 0,
    speed: '',
    errorMessage: null
  }

  uploadList.value.push(uploadFile)
}

// 上传前检查
const beforeUpload = (file) => {
  if (file.size > props.maxSize) {
    ElMessage.error(`文件 ${file.name} 超过最大限制 ${formatFileSize(props.maxSize)}`)
    return false
  }
  return true
}

// 计算文件MD5
const calculateFileMD5 = (file) => {
  return new Promise((resolve) => {
    const spark = new SparkMD5.ArrayBuffer()
    const fileReader = new FileReader()
    fileReader.onload = (e) => {
      spark.append(e.target.result)
      resolve(spark.end())
    }
    fileReader.readAsArrayBuffer(file)
  })
}

// 计算分片MD5
const calculateChunkMD5 = (chunk) => {
  return new Promise((resolve) => {
    const spark = new SparkMD5.ArrayBuffer()
    const fileReader = new FileReader()
    fileReader.onload = (e) => {
      spark.append(e.target.result)
      resolve(spark.end())
    }
    fileReader.readAsArrayBuffer(chunk)
  })
}

// 开始上传
const startUpload = async (file) => {
  try {
    file.status = 'uploading'
    file.errorMessage = null
    const startTime = Date.now()

    // 初始化上传会话
    if (!file.uploadId) {
      const { data } = await initChunkedUpload({
        file_name: file.name,
        file_size: file.size,
        mime_type: file.raw.type,
        chunk_size: props.chunkSize
      })

      file.uploadId = data.upload_id
      file.totalChunks = data.total_chunks
    }

    // 上传分片
    const totalChunks = Math.ceil(file.size / props.chunkSize)
    const uploadPromises = []
    let uploadedSize = file.uploadedChunks * props.chunkSize

    for (let i = file.currentChunk; i < totalChunks; i++) {
      // 控制并发数
      if (uploadPromises.length >= props.concurrent) {
        await Promise.race(uploadPromises)
      }

      const start = i * props.chunkSize
      const end = Math.min(start + props.chunkSize, file.size)
      const chunk = file.raw.slice(start, end)

      // 计算分片哈希
      const chunkHash = await calculateChunkMD5(chunk)

      const uploadPromise = uploadChunk(file.uploadId, i, chunk, chunkHash)
        .then(() => {
          file.uploadedChunks++
          uploadedSize += chunk.size

          // 更新进度
          file.progress = Math.round((file.uploadedChunks / totalChunks) * 100)

          // 计算速度
          const elapsed = (Date.now() - startTime) / 1000
          const speed = uploadedSize / elapsed
          file.speed = formatFileSize(speed) + '/s'

          // 移除已完成的promise
          const index = uploadPromises.indexOf(uploadPromise)
          if (index > -1) {
            uploadPromises.splice(index, 1)
          }
        })
        .catch((error) => {
          file.currentChunk = i
          throw error
        })

      uploadPromises.push(uploadPromise)
    }

    // 等待所有分片上传完成
    await Promise.all(uploadPromises)

    // 合并分片
    file.status = 'merging'
    await mergeChunks(file.uploadId, {
      ...formData,
      title: file.name.replace(/\.[^/.]+$/, '')
    })

    file.status = 'success'
    file.progress = 100
    ElMessage.success(`${file.name} 上传成功`)
    emit('success', file)

  } catch (error) {
    file.status = 'error'
    file.errorMessage = error.message || '上传失败'
    ElMessage.error(`${file.name} 上传失败: ${file.errorMessage}`)
  }
}

// 暂停上传
const pauseUpload = (file) => {
  file.status = 'paused'
}

// 删除文件
const removeFile = (file) => {
  const index = uploadList.value.findIndex(f => f.uid === file.uid)
  if (index > -1) {
    uploadList.value.splice(index, 1)
  }
}

// 全部上传
const startAllUploads = async () => {
  uploading.value = true
  const waitingFiles = uploadList.value.filter(f => f.status === 'waiting' || f.status === 'error')

  for (const file of waitingFiles) {
    await startUpload(file)
  }

  uploading.value = false
}

// 加载分类
const loadCategories = async () => {
  try {
    const { data } = await getCategories()
    categories.value = data
  } catch (error) {
    console.error('加载分类失败', error)
  }
}

// 加载标签
const loadTags = async () => {
  try {
    const { data } = await getTags()
    tags.value = (data.list || []).map(t => t.name)
  } catch (error) {
    console.error('加载标签失败', error)
  }
}

// 加载存储配置
const loadStorageConfigs = async () => {
  try {
    const { data } = await getStorageConfigs()
    storageConfigs.value = data.list
  } catch (error) {
    console.error('加载存储配置失败', error)
  }
}

// 关闭
const handleClose = () => {
  if (uploading.value) {
    ElMessage.warning('请等待上传完成')
    return
  }
  uploadList.value = []
  visible.value = false
}

// 格式化文件大小
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

// 初始化
const init = () => {
  loadCategories()
  loadTags()
  loadStorageConfigs()
}

init()
</script>

<style scoped lang="scss">
.chunked-upload {
  .upload-area {
    margin-bottom: 20px;

    :deep(.el-upload-dragger) {
      padding: 40px;
    }
  }

  .file-list {
    max-height: 400px;
    overflow-y: auto;

    .file-item {
      border: 1px solid #e4e7ed;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;

      .file-info {
        display: flex;
        align-items: center;
        margin-bottom: 10px;

        .file-icon {
          font-size: 32px;
          color: #409eff;
          margin-right: 12px;
        }

        .file-details {
          flex: 1;

          .file-name {
            font-size: 14px;
            color: #303133;
            margin-bottom: 4px;
          }

          .file-meta {
            font-size: 12px;
            color: #909399;

            .upload-id {
              margin-left: 8px;
            }
          }
        }
      }

      .file-progress {
        margin-bottom: 10px;

        .upload-speed {
          text-align: right;
          font-size: 12px;
          color: #67c23a;
          margin-top: 4px;
        }
      }

      .file-actions {
        display: flex;
        gap: 8px;
      }

      .error-message {
        margin-top: 10px;
        padding: 8px;
        background-color: #fef0f0;
        color: #f56c6c;
        border-radius: 4px;
        font-size: 12px;
      }
    }
  }

  .upload-form {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e4e7ed;
  }
}

.dialog-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;

  .stats {
    display: flex;
    gap: 15px;
    font-size: 14px;

    span {
      color: #606266;

      &.error {
        color: #f56c6c;
      }
    }
  }
}
</style>
