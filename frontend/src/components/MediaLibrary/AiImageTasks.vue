<template>
  <div class="ai-image-tasks">
    <!-- 筛选工具栏 -->
    <div class="toolbar">
      <el-form :inline="true" :model="filters" class="filter-form">
        <el-form-item label="状态">
          <el-select v-model="filters.status" placeholder="全部状态" clearable @change="loadTasks">
            <el-option label="待处理" value="pending" />
            <el-option label="处理中" value="processing" />
            <el-option label="已完成" value="completed" />
            <el-option label="失败" value="failed" />
            <el-option label="已取消" value="cancelled" />
          </el-select>
        </el-form-item>

        <el-form-item label="关键词">
          <el-input
            v-model="filters.keyword"
            placeholder="搜索提示词"
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

      <el-table-column label="预览" width="100">
        <template #default="{ row }">
          <el-image
            v-if="row.result_url"
            :src="row.result_url"
            :preview-src-list="[row.result_url]"
            fit="cover"
            style="width: 80px; height: 80px; border-radius: 4px"
          >
            <template #error>
              <div class="image-slot">
                <el-icon><Picture /></el-icon>
              </div>
            </template>
          </el-image>
          <div v-else class="image-placeholder">
            <el-icon :size="40">
              <Loading v-if="row.status === 'processing'" />
              <Picture v-else />
            </el-icon>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="提示词" min-width="250">
        <template #default="{ row }">
          <div class="prompt-cell">
            <el-tooltip :content="row.prompt" placement="top">
              <div class="prompt-text">{{ row.prompt }}</div>
            </el-tooltip>
            <div class="task-meta">
              <el-tag size="small" v-if="row.model">{{ row.model }}</el-tag>
              <span class="meta-item">{{ row.width }}x{{ row.height }}</span>
            </div>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="状态" width="120">
        <template #default="{ row }">
          <el-tag :type="getStatusType(row.status)">
            {{ getStatusText(row.status) }}
          </el-tag>
          <el-progress
            v-if="row.status === 'processing' && row.progress"
            :percentage="row.progress"
            :stroke-width="4"
            style="margin-top: 4px"
          />
        </template>
      </el-table-column>

      <el-table-column label="耗时" width="100">
        <template #default="{ row }">
          {{ row.duration ? formatDuration(row.duration) : '-' }}
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
    <el-dialog v-model="detailVisible" title="任务详情" width="800px">
      <div v-if="currentTask" class="task-detail">
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="detail-section">
              <h4>生成结果</h4>
              <el-image
                v-if="currentTask.result_url"
                :src="currentTask.result_url"
                :preview-src-list="[currentTask.result_url]"
                fit="contain"
                style="width: 100%; border-radius: 4px"
              />
              <el-empty v-else description="暂无结果" />
            </div>
          </el-col>
          <el-col :span="12">
            <div class="detail-section">
              <h4>任务信息</h4>
              <el-descriptions :column="1" border>
                <el-descriptions-item label="任务ID">{{ currentTask.id }}</el-descriptions-item>
                <el-descriptions-item label="状态">
                  <el-tag :type="getStatusType(currentTask.status)">
                    {{ getStatusText(currentTask.status) }}
                  </el-tag>
                </el-descriptions-item>
                <el-descriptions-item label="模型">{{ currentTask.model || '-' }}</el-descriptions-item>
                <el-descriptions-item label="尺寸">
                  {{ currentTask.width }}x{{ currentTask.height }}
                </el-descriptions-item>
                <el-descriptions-item label="步数">{{ currentTask.steps || '-' }}</el-descriptions-item>
                <el-descriptions-item label="CFG Scale">{{ currentTask.cfg_scale || '-' }}</el-descriptions-item>
                <el-descriptions-item label="采样器">{{ currentTask.sampler || '-' }}</el-descriptions-item>
                <el-descriptions-item label="随机种子">{{ currentTask.seed || '-' }}</el-descriptions-item>
                <el-descriptions-item label="耗时">
                  {{ currentTask.duration ? formatDuration(currentTask.duration) : '-' }}
                </el-descriptions-item>
                <el-descriptions-item label="创建时间">
                  {{ formatTime(currentTask.created_at) }}
                </el-descriptions-item>
                <el-descriptions-item label="完成时间">
                  {{ currentTask.finished_at ? formatTime(currentTask.finished_at) : '-' }}
                </el-descriptions-item>
              </el-descriptions>

              <div style="margin-top: 15px">
                <h5>提示词</h5>
                <el-input
                  v-model="currentTask.prompt"
                  type="textarea"
                  :rows="4"
                  readonly
                />
              </div>

              <div v-if="currentTask.negative_prompt" style="margin-top: 10px">
                <h5>负面提示词</h5>
                <el-input
                  v-model="currentTask.negative_prompt"
                  type="textarea"
                  :rows="3"
                  readonly
                />
              </div>

              <div v-if="currentTask.error_message" style="margin-top: 10px">
                <h5>错误信息</h5>
                <el-alert
                  :title="currentTask.error_message"
                  type="error"
                  :closable="false"
                />
              </div>
            </div>
          </el-col>
        </el-row>
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
  Picture,
  Loading
} from '@element-plus/icons-vue'
import {
  getAiImageTasks,
  retryAiImageTask,
  cancelAiImageTask,
  deleteAiImageTask
} from '@/api/queue'
import { batchRetryFailedTasks, batchDeleteTasks } from '@/api/queue'

const loading = ref(false)
const taskList = ref([])
const selectedTasks = ref([])
const detailVisible = ref(false)
const currentTask = ref(null)

const filters = reactive({
  status: '',
  keyword: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

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
    const { data } = await getAiImageTasks(params)
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
    processing: '处理中',
    completed: '已完成',
    failed: '失败',
    cancelled: '已取消'
  }
  return map[status] || status
}

// 格式化时长
const formatDuration = (seconds) => {
  if (seconds < 60) {
    return `${seconds}秒`
  }
  const minutes = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${minutes}分${secs}秒`
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

// 重试任务
const retryTask = async (row) => {
  try {
    await retryAiImageTask(row.id)
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

    await cancelAiImageTask(row.id)
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

    await deleteAiImageTask(row.id)
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
    await batchRetryFailedTasks({ task_ids: taskIds, queue: 'ai-image' })
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
    await batchDeleteTasks({ task_ids: taskIds, queue: 'ai-image' })
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
.ai-image-tasks {
  .toolbar {
    margin-bottom: 20px;

    .filter-form {
      :deep(.el-form-item) {
        margin-bottom: 0;
      }
    }
  }

  .image-placeholder {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f5f7fa;
    border-radius: 4px;
    color: #909399;
  }

  .image-slot {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    background-color: #f5f7fa;
    color: #909399;
    font-size: 30px;
  }

  .prompt-cell {
    .prompt-text {
      margin-bottom: 8px;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      line-height: 1.5;
    }

    .task-meta {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      color: #909399;

      .meta-item {
        padding: 2px 6px;
        background-color: #f5f7fa;
        border-radius: 3px;
      }
    }
  }

  .task-detail {
    .detail-section {
      h4 {
        margin: 0 0 15px 0;
        font-size: 16px;
        color: #303133;
      }

      h5 {
        margin: 0 0 8px 0;
        font-size: 14px;
        color: #606266;
      }
    }
  }
}
</style>
