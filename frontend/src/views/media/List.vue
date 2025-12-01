<template>
  <div class="media-list">
    <el-card>
      <template #header>
        <div class="header-actions">
          <h3>媒体库</h3>
          <div class="header-buttons">
            <el-button
              v-if="selectedFiles.length > 0"
              type="danger"
              @click="handleBatchDelete"
              style="margin-right: 10px;"
            >
              <el-icon><Delete /></el-icon>
              批量删除 ({{ selectedFiles.length }})
            </el-button>
            <el-button @click="$router.push('/media/storage')" style="margin-right: 10px;">
              <el-icon><Setting /></el-icon>
              存储配置
            </el-button>
            <el-button @click="$router.push('/media/queue')" style="margin-right: 10px;">
              <el-icon><Monitor /></el-icon>
              队列监控
            </el-button>
            <el-button type="success" @click="$router.push('/media/ai-generate')" style="margin-right: 10px;">
              <el-icon><MagicStick /></el-icon>
              AI生成
            </el-button>
            <el-button @click="$router.push('/media/watermark')" style="margin-right: 10px;">
              <el-icon><PictureFilled /></el-icon>
              水印管理
            </el-button>
            <el-button @click="$router.push('/media/thumbnail')" style="margin-right: 10px;">
              <el-icon><Picture /></el-icon>
              缩略图
            </el-button>
            <el-upload
              action="#"
              :http-request="handleUpload"
              :show-file-list="false"
            >
              <el-button type="primary">
                <el-icon><Upload /></el-icon>
                上传文件
              </el-button>
            </el-upload>
          </div>
        </div>
      </template>

      <!-- 文件类型标签页 -->
      <el-tabs v-model="activeTab" @tab-change="handleTabChange" style="margin-bottom: 15px;">
        <el-tab-pane label="全部" name="all" />
        <el-tab-pane label="图片" name="image" />
        <el-tab-pane label="视频" name="video" />
        <el-tab-pane label="文档" name="document" />
        <el-tab-pane label="其他" name="other" />
      </el-tabs>

      <!-- 搜索表单 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="文件名">
          <el-input v-model="searchForm.filename" placeholder="请输入文件名" clearable style="width: 200px;" />
        </el-form-item>
        <el-form-item label="上传时间">
          <el-date-picker
            v-model="searchForm.dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            clearable
            style="width: 260px;"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
        <el-form-item>
          <el-button type="success" @click="handleSelectAll">全选</el-button>
          <el-button @click="handleDeselectAll">取消全选</el-button>
        </el-form-item>
      </el-form>

      <!-- 文件网格 -->
      <div v-loading="loading" class="media-grid">
        <div v-for="item in mediaList" :key="item.id" class="media-item" :class="{ 'selected': isSelected(item.id) }">
          <div class="media-checkbox">
            <el-checkbox v-model="item.checked" @change="handleSelect(item)" />
          </div>
          <div class="media-preview">
            <img
              v-if="item.file_type === 'image'"
              :src="item.file_url"
              :alt="item.file_name"
            />
            <div v-else class="file-type-badge">
              <el-icon :size="60"><Document /></el-icon>
              <div class="file-ext">{{ getFileExtension(item.file_name) }}</div>
            </div>
          </div>
          <div class="media-info">
            <div class="media-name" :title="item.file_name">
              {{ item.file_name }}
            </div>
            <div class="media-size">{{ formatSize(item.file_size) }}</div>
            <div class="media-type">{{ getFileTypeLabel(item.file_type) }}</div>
          </div>
          <div class="media-actions">
            <el-button size="small" type="primary" @click="handleCopy(item.file_url)">
              复制链接
            </el-button>
            <el-button size="small" type="danger" @click="handleDelete(item.id)">
              删除
            </el-button>
          </div>
        </div>
      </div>

      <!-- 分页 -->
      <el-pagination
        v-if="mediaList.length > 0"
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :total="pagination.total"
        :page-sizes="[12, 24, 48, 96]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="fetchMedia"
        @current-change="fetchMedia"
        style="margin-top: 20px; justify-content: flex-end;"
      />

      <el-empty v-if="!loading && mediaList.length === 0" description="暂无文件" />
    </el-card>

    <!-- 图片裁剪对话框 -->
    <ImageCropper
      v-model="showCropper"
      :imageUrl="cropperImage"
      @success="handleCropSuccess"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Upload, Document, Delete, Setting, Monitor, MagicStick, PictureFilled, Picture } from '@element-plus/icons-vue'
import request from '@/api/request'
import ImageCropper from '@/components/MediaLibrary/ImageCropper.vue'

const loading = ref(false)
const mediaList = ref([])
const selectedFiles = ref([])
const activeTab = ref('all')
const showCropper = ref(false)
const cropperImage = ref('')

const searchForm = reactive({
  filename: '',
  type: '',
  dateRange: null
})

const pagination = reactive({
  page: 1,
  pageSize: 12,
  total: 0
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

    // 处理日期范围参数
    if (searchForm.dateRange && searchForm.dateRange.length === 2) {
      params.start_date = searchForm.dateRange[0]
      params.end_date = searchForm.dateRange[1]
    }

    const res = await request({ url: '/media', method: 'get', params })
    // 初始化每个文件的checked状态
    mediaList.value = (res.data.list || []).map(item => ({
      ...item,
      checked: selectedFiles.value.includes(item.id)
    }))
    pagination.total = res.data.total || 0
  } catch (error) {
    console.error('获取媒体列表失败:', error)
    const message = error.response?.data?.message || error.message || '获取媒体列表失败，请稍后重试'
    ElMessage.error(message)
  } finally {
    loading.value = false
  }
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
  // 保持当前标签页的类型筛选
  if (activeTab.value === 'all') {
    searchForm.type = ''
  } else {
    searchForm.type = activeTab.value
  }
  pagination.page = 1
  fetchMedia()
}

// 标签页切换
const handleTabChange = (tabName) => {
  // 清空搜索条件
  searchForm.filename = ''
  searchForm.dateRange = null
  // 设置类型筛选
  if (tabName === 'all') {
    searchForm.type = ''
  } else {
    searchForm.type = tabName
  }
  pagination.page = 1
  selectedFiles.value = []
  fetchMedia()
}

// 选择文件
const handleSelect = (item) => {
  if (item.checked) {
    if (!selectedFiles.value.includes(item.id)) {
      selectedFiles.value.push(item.id)
    }
  } else {
    const index = selectedFiles.value.indexOf(item.id)
    if (index > -1) {
      selectedFiles.value.splice(index, 1)
    }
  }
}

// 全选
const handleSelectAll = () => {
  mediaList.value.forEach(item => {
    item.checked = true
    if (!selectedFiles.value.includes(item.id)) {
      selectedFiles.value.push(item.id)
    }
  })
  ElMessage.success(`已全选 ${mediaList.value.length} 个文件`)
}

// 取消全选
const handleDeselectAll = () => {
  mediaList.value.forEach(item => {
    item.checked = false
  })
  selectedFiles.value = []
  ElMessage.info('已取消全部选择')
}

// 判断是否已选中
const isSelected = (id) => {
  return selectedFiles.value.includes(id)
}

// 批量删除
const handleBatchDelete = async () => {
  if (selectedFiles.value.length === 0) {
    ElMessage.warning('请选择要删除的文件')
    return
  }

  try {
    await ElMessageBox.confirm(`确定要删除选中的 ${selectedFiles.value.length} 个文件吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    // 依次删除选中的文件
    let successCount = 0
    let failCount = 0

    for (const id of selectedFiles.value) {
      try {
        await request({ url: `/media/${id}`, method: 'delete' })
        successCount++
      } catch (error) {
        failCount++
      }
    }

    if (failCount === 0) {
      ElMessage.success(`成功删除 ${successCount} 个文件`)
    } else {
      ElMessage.warning(`成功删除 ${successCount} 个文件，${failCount} 个文件删除失败`)
    }

    selectedFiles.value = []
    fetchMedia()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('批量删除失败')
    }
  }
}

// 上传文件
const handleUpload = async ({ file }) => {
  const loadingMsg = ElMessage({
    message: `正在上传 ${file.name}...`,
    type: 'info',
    duration: 0
  })

  const formData = new FormData()
  formData.append('file', file)

  try {
    await request({
      url: '/media/upload',
      method: 'post',
      data: formData,
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    loadingMsg.close()
    ElMessage.success(`${file.name} 上传成功`)
    fetchMedia()
  } catch (error) {
    loadingMsg.close()
    ElMessage.error(`${file.name} 上传失败`)
  }
}

// 复制链接
const handleCopy = async (url) => {
  try {
    await navigator.clipboard.writeText(url)
    ElMessage.success('链接已复制到剪贴板')
  } catch (error) {
    ElMessage.error('复制失败')
  }
}

// 删除文件
const handleDelete = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除这个文件吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await request({ url: `/media/${id}`, method: 'delete' })
    ElMessage.success('删除成功')
    fetchMedia()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

// 格式化文件大小
const formatSize = (bytes) => {
  if (!bytes || bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i]
}

// 获取文件扩展名
const getFileExtension = (filename) => {
  if (!filename) return ''
  const parts = filename.split('.')
  return parts.length > 1 ? parts.pop().toUpperCase() : ''
}

// 获取文件类型标签
const getFileTypeLabel = (type) => {
  const labels = {
    'image': '图片',
    'video': '视频',
    'audio': '音频',
    'document': '文档',
    'other': '其他'
  }
  return labels[type] || '未知'
}

// 图片裁剪成功
const handleCropSuccess = (result) => {
  ElMessage.success('裁剪成功')
  showCropper.value = false
  fetchMedia()
}

onMounted(() => {
  fetchMedia()
})
</script>

<style scoped>
.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions h3 {
  margin: 0;
}

.search-form {
  margin-bottom: 20px;
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  min-height: 200px;
}

.media-item {
  border: 1px solid #e4e7ed;
  border-radius: 4px;
  padding: 10px;
  transition: all 0.3s;
  position: relative;
}

.media-item:hover {
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.media-item.selected {
  border-color: #409EFF;
  background-color: #ecf5ff;
}

.media-checkbox {
  position: absolute;
  top: 15px;
  left: 15px;
  z-index: 10;
}

.header-buttons {
  display: flex;
  align-items: center;
}

.media-preview {
  width: 100%;
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f5f7fa;
  border-radius: 4px;
  margin-bottom: 10px;
  overflow: hidden;
}

.media-preview img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.media-info {
  margin-bottom: 10px;
}

.media-name {
  font-size: 14px;
  color: #303133;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 5px;
}

.media-size {
  font-size: 12px;
  color: #909399;
}

.media-actions {
  display: flex;
  gap: 5px;
}

.media-actions .el-button {
  flex: 1;
}

.file-type-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
}

.file-ext {
  margin-top: 10px;
  font-size: 14px;
  font-weight: bold;
  color: #409EFF;
}

.media-type {
  font-size: 12px;
  color: #909399;
  margin-top: 3px;
}
</style>
