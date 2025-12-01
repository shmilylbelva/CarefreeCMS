<template>
  <el-dialog
    v-model="visible"
    title="选择媒体文件"
    width="80%"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="media-selector">
      <!-- 文件类型标签页 -->
      <el-tabs v-model="activeTab" @tab-change="handleTabChange" style="margin-bottom: 15px;">
        <el-tab-pane label="全部" name="all" />
        <el-tab-pane label="图片" name="image" />
        <el-tab-pane label="视频" name="video" />
        <el-tab-pane label="文档" name="document" />
        <el-tab-pane label="其他" name="other" />
      </el-tabs>

      <!-- 搜索和上传 -->
      <el-form :inline="true" :model="searchForm" class="search-form" style="margin-bottom: 15px;">
        <el-form-item label="文件名">
          <el-input v-model="searchForm.filename" placeholder="请输入文件名" clearable style="width: 200px;" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
        <el-form-item style="float: right;">
          <el-upload
            action="#"
            :http-request="handleUpload"
            :show-file-list="false"
            :before-upload="beforeUpload"
          >
            <el-button type="success">
              <el-icon><Upload /></el-icon>
              上传新文件
            </el-button>
          </el-upload>
        </el-form-item>
      </el-form>

      <!-- 文件网格 -->
      <div v-loading="loading" class="media-grid">
        <div
          v-for="item in mediaList"
          :key="item.id"
          class="media-item"
          :class="{ 'selected': isSelected(item) }"
          @click="handleSelect(item)"
        >
          <div class="media-preview">
            <img
              v-if="item.file_type === 'image'"
              :src="item.file_url"
              :alt="item.file_name"
            />
            <video
              v-else-if="item.file_type === 'video'"
              :src="item.file_url"
              style="width: 100%; height: 100%; object-fit: cover;"
            />
            <div v-else class="file-type-badge">
              <el-icon :size="50"><Document /></el-icon>
              <div class="file-ext">{{ getFileExtension(item.file_name) }}</div>
            </div>
          </div>
          <div class="media-info">
            <div class="media-name" :title="item.file_name">
              {{ item.file_name }}
            </div>
            <div class="media-size">{{ formatSize(item.file_size) }}</div>
          </div>
          <div v-if="isSelected(item)" class="selected-badge">
            <el-icon><Check /></el-icon>
          </div>
        </div>
      </div>

      <!-- 分页 -->
      <el-pagination
        v-if="mediaList.length > 0"
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :total="pagination.total"
        :page-sizes="[12, 24, 48]"
        layout="total, sizes, prev, pager, next"
        @size-change="fetchMedia"
        @current-change="fetchMedia"
        style="margin-top: 20px; justify-content: flex-end;"
      />

      <el-empty v-if="!loading && mediaList.length === 0" description="暂无文件" />
    </div>

    <template #footer>
      <div class="dialog-footer">
        <div style="text-align: left; flex: 1;">
          <span v-if="selectedItem" style="color: #606266;">
            已选择: {{ selectedItem.file_name }}
          </span>
        </div>
        <el-button @click="handleClose">取消</el-button>
        <el-button type="primary" :disabled="!selectedItem" @click="handleConfirm">
          确定
        </el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { Upload, Document, Check } from '@element-plus/icons-vue'
import request from '@/api/request'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  // 文件类型过滤: 'image', 'video', 'document', 'all'
  fileType: {
    type: String,
    default: 'all'
  }
})

const emit = defineEmits(['update:modelValue', 'select'])

const visible = ref(props.modelValue)
const loading = ref(false)
const mediaList = ref([])
const selectedItem = ref(null)
const activeTab = ref(props.fileType)

const searchForm = reactive({
  filename: '',
  type: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 12,
  total: 0
})

// 监听 modelValue 变化
watch(() => props.modelValue, (val) => {
  visible.value = val
  if (val) {
    // 对话框打开时重置并加载数据
    selectedItem.value = null
    activeTab.value = props.fileType
    searchForm.type = props.fileType === 'all' ? '' : props.fileType
    fetchMedia()
  }
})

watch(visible, (val) => {
  emit('update:modelValue', val)
})

// 获取媒体列表
const fetchMedia = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      filename: searchForm.filename,
      type: searchForm.type
    }

    if (searchForm.dateRange && searchForm.dateRange.length === 2) {
      params.start_date = searchForm.dateRange[0]
      params.end_date = searchForm.dateRange[1]
    }

    const res = await request({
      url: '/media',
      method: 'get',
      params
    })

    mediaList.value = res.data.list || []
    pagination.total = res.data.total || 0
  } catch (error) {
    ElMessage.error('获取媒体列表失败')
  } finally {
    loading.value = false
  }
}

// 标签页切换
const handleTabChange = (tab) => {
  searchForm.type = tab === 'all' ? '' : tab
  pagination.page = 1
  fetchMedia()
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  fetchMedia()
}

// 重置
const handleReset = () => {
  searchForm.filename = ''
  searchForm.dateRange = null
  pagination.page = 1
  fetchMedia()
}

// 上传文件
const handleUpload = async (options) => {
  const formData = new FormData()
  formData.append('file', options.file)

  try {
    const res = await request({
      url: '/media/upload',
      method: 'post',
      data: formData,
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    ElMessage.success('上传成功')
    fetchMedia()
  } catch (error) {
    ElMessage.error('上传失败')
  }
}

const beforeUpload = (file) => {
  const maxSize = 100 * 1024 * 1024 // 100MB
  if (file.size > maxSize) {
    ElMessage.error('文件大小不能超过 100MB')
    return false
  }
  return true
}

// 选择文件
const handleSelect = (item) => {
  selectedItem.value = item
}

// 判断是否选中
const isSelected = (item) => {
  return selectedItem.value && selectedItem.value.id === item.id
}

// 获取文件扩展名
const getFileExtension = (filename) => {
  const ext = filename.split('.').pop()
  return ext ? ext.toUpperCase() : ''
}

// 格式化文件大小
const formatSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

// 确认选择
const handleConfirm = () => {
  if (selectedItem.value) {
    emit('select', selectedItem.value)
    handleClose()
  }
}

// 关闭对话框
const handleClose = () => {
  visible.value = false
  selectedItem.value = null
}
</script>

<style scoped>
.media-selector {
  min-height: 400px;
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 15px;
  min-height: 300px;
}

.media-item {
  position: relative;
  border: 2px solid #e4e7ed;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s;
  background: #fff;
}

.media-item:hover {
  border-color: #409eff;
  box-shadow: 0 2px 12px rgba(64, 158, 255, 0.2);
}

.media-item.selected {
  border-color: #409eff;
  background-color: #ecf5ff;
}

.media-preview {
  width: 100%;
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f7fa;
  overflow: hidden;
}

.media-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.file-type-badge {
  text-align: center;
  color: #909399;
}

.file-ext {
  font-size: 14px;
  font-weight: bold;
  margin-top: 5px;
}

.media-info {
  padding: 10px;
  background: #fff;
}

.media-name {
  font-size: 13px;
  color: #303133;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-bottom: 5px;
}

.media-size {
  font-size: 12px;
  color: #909399;
}

.selected-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 30px;
  height: 30px;
  background: #409eff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 18px;
}

.dialog-footer {
  display: flex;
  align-items: center;
}
</style>
