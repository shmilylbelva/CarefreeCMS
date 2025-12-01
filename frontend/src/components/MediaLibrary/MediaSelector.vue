<template>
  <el-dialog
    v-model="visible"
    :title="title"
    :width="dialogWidth"
    :close-on-click-modal="false"
    class="media-selector-dialog"
    @close="handleClose"
  >
    <div class="media-selector">
      <!-- 工具栏 -->
      <div class="toolbar">
        <div class="left">
          <el-button type="primary" :icon="Upload" @click="showUploadDialog">
            上传文件
          </el-button>
          <el-button :icon="Refresh" @click="refreshList">刷新</el-button>
        </div>
        <div class="right">
          <el-select v-model="filters.category_id" placeholder="分类" clearable @change="handleSearch">
            <el-option
              v-for="cat in categories"
              :key="cat.id"
              :label="cat.name"
              :value="cat.id"
            />
          </el-select>
          <el-input
            v-model="filters.keyword"
            placeholder="搜索文件名"
            :prefix-icon="Search"
            clearable
            style="width: 200px; margin-left: 10px"
            @input="handleSearch"
          />
          <el-radio-group v-model="viewMode" style="margin-left: 10px">
            <el-radio-button value="grid">网格</el-radio-button>
            <el-radio-button value="list">列表</el-radio-button>
          </el-radio-group>
        </div>
      </div>

      <!-- 媒体列表 - 网格视图 -->
      <div v-if="viewMode === 'grid'" class="media-grid" v-loading="loading">
        <div
          v-for="item in mediaList"
          :key="item.id"
          :class="['media-item', { selected: isSelected(item.id) }]"
          @click="handleSelect(item)"
        >
          <div class="media-preview">
            <img v-if="item.file.file_type === 'image'" :src="item.file.file_url" :alt="item.title" />
            <video v-else-if="item.file.file_type === 'video'" :src="item.file.file_url" />
            <div v-else class="file-icon">
              <el-icon :size="48"><Document /></el-icon>
              <span>.{{ item.file.file_ext }}</span>
            </div>
            <div v-if="multiple" class="checkbox">
              <el-checkbox :model-value="isSelected(item.id)" />
            </div>
          </div>
          <div class="media-info">
            <div class="title" :title="item.title">{{ item.title }}</div>
            <div class="meta">{{ formatFileSize(item.file.file_size) }}</div>
          </div>
        </div>
        <el-empty v-if="!loading && mediaList.length === 0" description="暂无文件" />
      </div>

      <!-- 媒体列表 - 列表视图 -->
      <el-table
        v-else
        :data="mediaList"
        v-loading="loading"
        @selection-change="handleSelectionChange"
        @row-click="handleRowClick"
      >
        <el-table-column v-if="multiple" type="selection" width="55" />
        <el-table-column label="预览" width="100">
          <template #default="{ row }">
            <img
              v-if="row.file.file_type === 'image'"
              :src="row.file.file_url"
              style="width: 60px; height: 60px; object-fit: cover"
            />
            <el-icon v-else :size="40"><Document /></el-icon>
          </template>
        </el-table-column>
        <el-table-column prop="title" label="标题" />
        <el-table-column prop="file.file_ext" label="类型" width="80" />
        <el-table-column label="大小" width="120">
          <template #default="{ row }">{{ formatFileSize(row.file.file_size) }}</template>
        </el-table-column>
        <el-table-column prop="created_at" label="上传时间" width="160" />
      </el-table>

      <!-- 分页 -->
      <div class="pagination">
        <el-pagination
          v-model:current-page="pagination.page"
          v-model:page-size="pagination.pageSize"
          :total="pagination.total"
          :page-sizes="[20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @current-change="loadMediaList"
          @size-change="loadMediaList"
        />
      </div>
    </div>

    <template #footer>
      <div class="dialog-footer">
        <div class="selected-info" v-if="selectedMedia.length > 0">
          已选择 {{ selectedMedia.length }} 个文件
        </div>
        <div>
          <el-button @click="handleClose">取消</el-button>
          <el-button type="primary" @click="handleConfirm" :disabled="selectedMedia.length === 0">
            确定
          </el-button>
        </div>
      </div>
    </template>

    <!-- 上传对话框 -->
    <ChunkedUpload
      v-model="uploadVisible"
      :accept="accept"
      :max-size="maxSize"
      @success="handleUploadSuccess"
    />
  </el-dialog>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { Upload, Refresh, Search, Document } from '@element-plus/icons-vue'
import ChunkedUpload from './ChunkedUpload.vue'
import { getMediaList, getCategories } from '@/api/media'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  multiple: {
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
  title: {
    type: String,
    default: '选择媒体文件'
  },
  fileType: {
    type: String,
    default: '' // image/video/audio/document
  }
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const dialogWidth = computed(() => props.multiple ? '1200px' : '900px')

// 数据
const loading = ref(false)
const uploadVisible = ref(false)
const viewMode = ref('grid')
const mediaList = ref([])
const categories = ref([])
const selectedMedia = ref([])

const filters = reactive({
  keyword: '',
  category_id: '',
  file_type: props.fileType
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

// 加载媒体列表
const loadMediaList = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      pageSize: pagination.pageSize,
      ...filters
    }
    const { data } = await getMediaList(params)
    mediaList.value = data.list
    pagination.total = data.total
  } catch (error) {
    ElMessage.error('加载失败：' + error.message)
  } finally {
    loading.value = false
  }
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

// 搜索
const handleSearch = () => {
  pagination.page = 1
  loadMediaList()
}

// 刷新
const refreshList = () => {
  loadMediaList()
}

// 选择
const isSelected = (id) => {
  return selectedMedia.value.some(item => item.id === id)
}

const handleSelect = (item) => {
  if (props.multiple) {
    const index = selectedMedia.value.findIndex(m => m.id === item.id)
    if (index > -1) {
      selectedMedia.value.splice(index, 1)
    } else {
      selectedMedia.value.push(item)
    }
  } else {
    selectedMedia.value = [item]
  }
}

const handleSelectionChange = (selection) => {
  selectedMedia.value = selection
}

const handleRowClick = (row) => {
  if (!props.multiple) {
    selectedMedia.value = [row]
  }
}

// 上传
const showUploadDialog = () => {
  uploadVisible.value = true
}

const handleUploadSuccess = () => {
  refreshList()
}

// 确定
const handleConfirm = () => {
  emit('confirm', props.multiple ? selectedMedia.value : selectedMedia.value[0])
  handleClose()
}

// 关闭
const handleClose = () => {
  selectedMedia.value = []
  visible.value = false
}

// 格式化文件大小
const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

// 监听对话框显示
watch(visible, (val) => {
  if (val) {
    loadMediaList()
    loadCategories()
  }
})
</script>

<style scoped lang="scss">
.media-selector-dialog {
  .media-selector {
    .toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;

      .left, .right {
        display: flex;
        align-items: center;
      }
    }

    .media-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 15px;
      min-height: 400px;
      max-height: 500px;
      overflow-y: auto;
      padding: 10px;

      .media-item {
        border: 2px solid #e4e7ed;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
        overflow: hidden;

        &:hover {
          border-color: #409eff;
          box-shadow: 0 2px 12px rgba(64, 158, 255, 0.3);
        }

        &.selected {
          border-color: #409eff;
          background-color: #ecf5ff;
        }

        .media-preview {
          position: relative;
          width: 100%;
          height: 150px;
          background-color: #f5f7fa;
          display: flex;
          align-items: center;
          justify-content: center;

          img, video {
            width: 100%;
            height: 100%;
            object-fit: cover;
          }

          .file-icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #909399;

            span {
              margin-top: 5px;
              font-size: 12px;
            }
          }

          .checkbox {
            position: absolute;
            top: 5px;
            right: 5px;
          }
        }

        .media-info {
          padding: 8px;

          .title {
            font-size: 13px;
            color: #303133;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
          }

          .meta {
            font-size: 12px;
            color: #909399;
            margin-top: 4px;
          }
        }
      }
    }

    .pagination {
      margin-top: 20px;
      display: flex;
      justify-content: center;
    }
  }

  .dialog-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;

    .selected-info {
      color: #409eff;
      font-size: 14px;
    }
  }
}
</style>
