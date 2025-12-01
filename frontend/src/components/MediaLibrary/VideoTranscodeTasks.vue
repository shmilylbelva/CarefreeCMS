<template>
  <div class="video-transcode-tasks">
    <!-- 筛选工具栏 -->
    <div class="toolbar">
      <el-form :inline="true" :model="filters" class="filter-form">
        <el-form-item label="状态">
          <el-select v-model="filters.status" placeholder="全部状态" clearable @change="loadTasks">
            <el-option label="待处理" value="pending" />
            <el-option label="转码中" value="processing" />
            <el-option label="已完成" value="completed" />
            <el-option label="失败" value="failed" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>

        <el-form-item label="输出格式">
          <el-select v-model="filters.output_format" placeholder="全部格式" clearable @change="loadTasks">
            <el-option label="MP4" value="mp4" />
            <el-option label="WebM" value="webm" />
            <el-option label="AVI" value="avi" />
            <el-option label="MOV" value="mov" />
            <el-option label="FLV" value="flv" />
          </el-select>
        </el-form-item>

        <el-form-item label="关键词">
          <el-input
            v-model="filters.keyword"
            placeholder="搜索文件名"
            clearable
            @keyup.enter="loadTasks"
            style="width: 200px"
          >
            <template #append>
              <el-button :icon="Search" @click="loadTasks" />
            </template>
          </el-input>
        </el-form-item>

        <el-form-item>
          <el-button :icon="Refresh" @click="loadTasks">刷新</el-button>
          <el-button :icon="Delete" type="danger" @click="batchDelete" :disabled="!selectedTasks.length">
            批量删除
          </el-button>
          <el-button type="warning" @click="batchRetry" :disabled="!selectedFailedTasks.length">
            重试失败 ({{ selectedFailedTasks.length }})
          </el-button>
        </el-form-item>
      </el-form>
    </div>

    <!-- 任务列表 -->
    <el-table
      :data="taskList"
      v-loading="loading"
      @selection-change="handleSelectionChange"
      stripe
    >
      <el-table-column type="selection" width="55" />

      <el-table-column label="源文件" min-width="200">
        <template #default="{ row }">
          <div class="file-cell">
            <el-icon :size="20" class="file-icon"><VideoCamera /></el-icon>
            <div class="file-info">
              <div class="file-name">{{ row.source_file_name }}</div>
              <div class="file-size">{{ formatFileSize(row.source_file_size) }}</div>
            </div>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="转码配置" width="200">
        <template #default="{ row }">
          <div class="config-cell">
            <el-tag size="small">{{ row.output_format?.toUpperCase() }}</el-tag>
            <span class="config-item">{{ row.output_width }}x{{ row.output_height }}</span>
            <span class="config-item" v-if="row.bitrate">{{ row.bitrate }}kbps</span>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="状态/进度" width="180">
        <template #default="{ row }">
          <el-tag :type="getStatusType(row.status)">
            {{ getStatusText(row.status) }}
          </el-tag>
          <div v-if="row.status === 'processing'" class="progress-info">
            <el-progress
              :percentage="row.progress || 0"
              :stroke-width="6"
              :color="customColors"
            />
            <div class="progress-text">
              <span>{{ row.current_time || '00:00' }} / {{ row.total_time || '00:00' }}</span>
              <span v-if="row.speed">{{ row.speed }}x</span>
            </div>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="耗时" width="100">
        <template #default="{ row }">
          {{ row.duration ? formatDuration(row.duration) : '-' }}
        </template>
      </el-table-column>

      <el-table-column label="文件大小" width="120">
        <template #default="{ row }">
          <div v-if="row.output_file_size">
            {{ formatFileSize(row.output_file_size) }}
            <el-tag
              v-if="row.source_file_size"
              size="small"
              :type="row.output_file_size < row.source_file_size ? 'success' : 'info'"
            >
              {{ calculateCompressionRatio(row.source_file_size, row.output_file_size) }}
            </el-tag>
          </div>
          <span v-else>-</span>
        </template>
      </el-table-column>

      <el-table-column label="创建时间" width="160">
        <template #default="{ row }">
          {{ formatTime(row.created_at) }}
        </template>
      </el-table-column>

      <el-table-column label="操作" width="200" fixed="right">
        <template #default="{ row }">
          <el-button
            size="small"
            :icon="View"
            @click="viewDetail(row)"
            link
          >
            详情
          </el-button>
          <el-button
            v-if="row.status === 'completed' && row.output_url"
            size="small"
            :icon="Download"
            @click="downloadFile(row)"
            link
            type="primary"
          >
            下载
          </el-button>
          <el-button
            v-if="row.status === 'failed'"
            size="small"
            :icon="RefreshRight"
            @click="retryTask(row)"
            link
            type="warning"
          >
            重试
          </el-button>
          <el-button
            v-if="row.status === 'processing'"
            size="small"
            :icon="Close"
            @click="cancelTask(row)"
            link
            type="danger"
          >
            取消
          </el-button>
          <el-button
            v-if="['completed', 'failed', 'cancelled'].includes(row.status)"
            size="small"
            :icon="Delete"
            @click="deleteTask(row)"
            link
            type="danger"
          >
            删除
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- 分页 -->
    <el-pagination
      v-model:current-page="pagination.page"
      v-model:page-size="pagination.pageSize"
      :total="pagination.total"
      :page-sizes="[10, 20, 50, 100]"
      layout="total, sizes, prev, pager, next, jumper"
      @current-change="loadTasks"
      @size-change="loadTasks"
      style="margin-top: 20px; justify-content: flex-end"
    />

    <!-- 详情对话框 -->
    <el-dialog v-model="detailVisible" title="任务详情" width="900px">
      <div v-if="currentTask" class="task-detail">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="任务ID">{{ currentTask.id }}</el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag :type="getStatusType(currentTask.status)">
              {{ getStatusText(currentTask.status) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="源文件名" :span="2">
            {{ currentTask.source_file_name }}
          </el-descriptions-item>
          <el-descriptions-item label="源文件大小">
            {{ formatFileSize(currentTask.source_file_size) }}
          </el-descriptions-item>
          <el-descriptions-item label="输出文件大小">
            {{ currentTask.output_file_size ? formatFileSize(currentTask.output_file_size) : '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="输出格式">
            {{ currentTask.output_format?.toUpperCase() }}
          </el-descriptions-item>
          <el-descriptions-item label="分辨率">
            {{ currentTask.output_width }}x{{ currentTask.output_height }}
          </el-descriptions-item>
          <el-descriptions-item label="视频编码">
            {{ currentTask.video_codec || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="音频编码">
            {{ currentTask.audio_codec || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="比特率">
            {{ currentTask.bitrate ? currentTask.bitrate + 'kbps' : '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="帧率">
            {{ currentTask.frame_rate ? currentTask.frame_rate + 'fps' : '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="处理进度" v-if="currentTask.status === 'processing'">
            {{ currentTask.progress || 0 }}%
          </el-descriptions-item>
          <el-descriptions-item label="转码速度" v-if="currentTask.speed">
            {{ currentTask.speed }}x
          </el-descriptions-item>
          <el-descriptions-item label="耗时">
            {{ currentTask.duration ? formatDuration(currentTask.duration) : '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="预估剩余时间" v-if="currentTask.estimated_time">
            {{ formatDuration(currentTask.estimated_time) }}
          </el-descriptions-item>
          <el-descriptions-item label="创建时间">
            {{ formatTime(currentTask.created_at) }}
          </el-descriptions-item>
          <el-descriptions-item label="完成时间">
            {{ currentTask.finished_at ? formatTime(currentTask.finished_at) : '-' }}
          </el-descriptions-item>
        </el-descriptions>

        <div v-if="currentTask.ffmpeg_command" style="margin-top: 20px">
          <h4>FFmpeg命令</h4>
          <el-input
            v-model="currentTask.ffmpeg_command"
            type="textarea"
            :rows="4"
            readonly
          />
        </div>

        <div v-if="currentTask.error_message" style="margin-top: 15px">
          <h4>错误信息</h4>
          <el-alert
            :title="currentTask.error_message"
            type="error"
            :closable="false"
          />
        </div>

        <div v-if="currentTask.output_url" style="margin-top: 20px; text-align: center">
          <el-button type="primary" :icon="Download" @click="downloadFile(currentTask)">
            下载输出文件
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Search,
  Refresh,
  Delete,
  View,
  RefreshRight,
  Close,
  Download,
  VideoCamera
} from '@element-plus/icons-vue'
import {
  getVideoTranscodeTasks,
  retryVideoTranscodeTask,
  cancelVideoTranscodeTask,
  deleteVideoTranscodeTask
} from '@/api/queue'
import { batchRetryFailedTasks, batchDeleteTasks } from '@/api/queue'

const loading = ref(false)
const taskList = ref([])
const selectedTasks = ref([])
const detailVisible = ref(false)
const currentTask = ref(null)

const filters = reactive({
  status: '',
  output_format: '',
  keyword: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

// 进度条颜色
const customColors = [
  { color: '#f56c6c', percentage: 20 },
  { color: '#e6a23c', percentage: 40 },
  { color: '#5cb87a', percentage: 60 },
  { color: '#1989fa', percentage: 80 },
  { color: '#6f7ad3', percentage: 100 }
]

// 选中的失败任务
const selectedFailedTasks = computed(() => {
  return selectedTasks.value.filter(task => task.status === 'failed')
})

// 加载任务列表
const loadTasks = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      pageSize: pagination.pageSize,
      ...filters
    }
    const { data } = await getVideoTranscodeTasks(params)
    taskList.value = data.list || data.data || []
    pagination.total = data.total || 0
  } catch (error) {
    ElMessage.error('加载失败：' + error.message)
  } finally {
    loading.value = false
  }
}

// 状态类型
const getStatusType = (status) => {
  const map = {
    pending: 'info',
    processing: 'warning',
    completed: 'success',
    failed: 'danger',
    cancelled: 'info'
  }
  return map[status] || ''
}

// 状态文本
const getStatusText = (status) => {
  const map = {
    pending: '待处理',
    processing: '转码中',
    completed: '已完成',
    failed: '失败',
    cancelled: '已取消'
  }
  return map[status] || status
}

// 格式化文件大小
const formatFileSize = (bytes) => {
  if (!bytes) return '-'
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i]
}

// 计算压缩比例
const calculateCompressionRatio = (sourceSize, outputSize) => {
  if (!sourceSize || !outputSize) return '-'
  const ratio = ((1 - outputSize / sourceSize) * 100).toFixed(1)
  return ratio > 0 ? `-${ratio}%` : `+${Math.abs(ratio)}%`
}

// 格式化时长
const formatDuration = (seconds) => {
  if (!seconds) return '-'
  if (seconds < 60) {
    return `${seconds}秒`
  }
  const minutes = Math.floor(seconds / 60)
  const secs = seconds % 60
  if (minutes < 60) {
    return `${minutes}分${secs}秒`
  }
  const hours = Math.floor(minutes / 60)
  const mins = minutes % 60
  return `${hours}时${mins}分${secs}秒`
}

// 格式化时间
const formatTime = (time) => {
  if (!time) return '-'
  return new Date(time).toLocaleString('zh-CN')
}

// 查看详情
const viewDetail = (row) => {
  currentTask.value = { ...row }
  detailVisible.value = true
}

// 下载文件
const downloadFile = (row) => {
  if (row.output_url) {
    window.open(row.output_url, '_blank')
  }
}

// 重试任务
const retryTask = async (row) => {
  try {
    await retryVideoTranscodeTask(row.id)
    ElMessage.success('已重新加入队列')
    loadTasks()
  } catch (error) {
    ElMessage.error('重试失败：' + error.message)
  }
}

// 取消任务
const cancelTask = async (row) => {
  try {
    await ElMessageBox.confirm('确定要取消此任务吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    await cancelVideoTranscodeTask(row.id)
    ElMessage.success('任务已取消')
    loadTasks()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('取消失败：' + error.message)
    }
  }
}

// 删除任务
const deleteTask = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除此任务吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    await deleteVideoTranscodeTask(row.id)
    ElMessage.success('删除成功')
    loadTasks()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败：' + error.message)
    }
  }
}

// 选择变化
const handleSelectionChange = (selection) => {
  selectedTasks.value = selection
}

// 批量重试
const batchRetry = async () => {
  if (!selectedFailedTasks.value.length) {
    ElMessage.warning('请选择失败的任务')
    return
  }

  try {
    await ElMessageBox.confirm(
      `确定要重试选中的 ${selectedFailedTasks.value.length} 个失败任务吗？`,
      '批量重试',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const taskIds = selectedFailedTasks.value.map(task => task.id)
    await batchRetryFailedTasks({ task_ids: taskIds, queue: 'video' })
    ElMessage.success('已重新加入队列')
    loadTasks()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('批量重试失败：' + error.message)
    }
  }
}

// 批量删除
const batchDelete = async () => {
  if (!selectedTasks.value.length) {
    ElMessage.warning('请选择要删除的任务')
    return
  }

  try {
    await ElMessageBox.confirm(
      `确定要删除选中的 ${selectedTasks.value.length} 个任务吗？`,
      '批量删除',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const taskIds = selectedTasks.value.map(task => task.id)
    await batchDeleteTasks({ task_ids: taskIds, queue: 'video' })
    ElMessage.success('删除成功')
    loadTasks()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('批量删除失败：' + error.message)
    }
  }
}

// 初始化
loadTasks()
</script>

<style scoped lang="scss">
.video-transcode-tasks {
  .toolbar {
    margin-bottom: 20px;

    .filter-form {
      :deep(.el-form-item) {
        margin-bottom: 0;
      }
    }
  }

  .file-cell {
    display: flex;
    align-items: center;
    gap: 10px;

    .file-icon {
      color: #409eff;
    }

    .file-info {
      flex: 1;
      overflow: hidden;

      .file-name {
        font-size: 14px;
        color: #303133;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      .file-size {
        font-size: 12px;
        color: #909399;
        margin-top: 2px;
      }
    }
  }

  .config-cell {
    display: flex;
    flex-direction: column;
    gap: 6px;

    .config-item {
      font-size: 12px;
      color: #606266;
      padding: 2px 6px;
      background-color: #f5f7fa;
      border-radius: 3px;
      display: inline-block;
    }
  }

  .progress-info {
    margin-top: 8px;

    .progress-text {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      color: #909399;
      margin-top: 4px;
    }
  }

  .task-detail {
    h4 {
      margin: 0 0 10px 0;
      font-size: 14px;
      color: #303133;
    }
  }
}
</style>
