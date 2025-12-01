<template>
  <div class="queue-logs">
    <!-- 筛选工具栏 -->
    <div class="toolbar">
      <el-form :inline="true" :model="filters" class="filter-form">
        <el-form-item label="队列">
          <el-select v-model="filters.queue" placeholder="全部队列" clearable @change="loadLogs">
            <el-option label="AI图片生成" value="ai-image" />
            <el-option label="缩略图生成" value="thumbnail" />
            <el-option label="水印处理" value="watermark" />
            <el-option label="视频转码" value="video" />
          </el-select>
        </el-form-item>

        <el-form-item label="级别">
          <el-select v-model="filters.level" placeholder="全部级别" clearable @change="loadLogs">
            <el-option label="调试" value="debug" />
            <el-option label="信息" value="info" />
            <el-option label="警告" value="warning" />
            <el-option label="错误" value="error" />
          </el-select>
        </el-form-item>

        <el-form-item label="时间范围">
          <el-date-picker
            v-model="dateRange"
            type="datetimerange"
            range-separator="至"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
            @change="loadLogs"
            style="width: 380px"
          />
        </el-form-item>

        <el-form-item label="关键词">
          <el-input
            v-model="filters.keyword"
            placeholder="搜索日志内容"
            clearable
            @keyup.enter="loadLogs"
            style="width: 200px"
          >
            <template #append>
              <el-button :icon="Search" @click="loadLogs" />
            </template>
          </el-input>
        </el-form-item>
      </el-form>

      <div class="toolbar-actions">
        <el-button :icon="Refresh" @click="loadLogs">刷新</el-button>
        <el-button :icon="Download" @click="exportLogs">导出日志</el-button>
        <el-button :icon="Delete" type="danger" @click="clearLogs">清空日志</el-button>
      </div>
    </div>

    <!-- 日志列表 -->
    <div class="log-container" v-loading="loading">
      <el-table
        :data="logList"
        stripe
        :height="tableHeight"
        style="width: 100%"
      >
        <el-table-column label="时间" width="180" fixed="left">
          <template #default="{ row }">
            {{ formatTime(row.created_at) }}
          </template>
        </el-table-column>

        <el-table-column label="级别" width="90">
          <template #default="{ row }">
            <el-tag :type="getLevelType(row.level)" size="small">
              {{ getLevelText(row.level) }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="队列" width="120">
          <template #default="{ row }">
            <el-tag size="small">{{ getQueueName(row.queue) }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="任务ID" width="100">
          <template #default="{ row }">
            <el-link v-if="row.task_id" type="primary" @click="viewTaskDetail(row)">
              #{{ row.task_id }}
            </el-link>
            <span v-else>-</span>
          </template>
        </el-table-column>

        <el-table-column label="消息" min-width="300">
          <template #default="{ row }">
            <div class="message-cell">
              <div class="message-text">{{ row.message }}</div>
              <el-button
                v-if="row.context"
                size="small"
                text
                @click="showContext(row)"
              >
                查看详情
              </el-button>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="IP地址" width="140">
          <template #default="{ row }">
            {{ row.ip_address || '-' }}
          </template>
        </el-table-column>

        <el-table-column label="用户" width="120">
          <template #default="{ row }">
            {{ row.user_name || '-' }}
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- 分页 -->
    <el-pagination
      v-model:current-page="pagination.page"
      v-model:page-size="pagination.pageSize"
      :total="pagination.total"
      :page-sizes="[20, 50, 100, 200]"
      layout="total, sizes, prev, pager, next, jumper"
      @current-change="loadLogs"
      @size-change="loadLogs"
      style="margin-top: 20px; justify-content: flex-end"
    />

    <!-- 上下文详情对话框 -->
    <el-dialog v-model="contextVisible" title="日志详情" width="800px">
      <div v-if="currentLog" class="log-detail">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="时间">
            {{ formatTime(currentLog.created_at) }}
          </el-descriptions-item>
          <el-descriptions-item label="级别">
            <el-tag :type="getLevelType(currentLog.level)">
              {{ getLevelText(currentLog.level) }}
            </el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="队列">
            {{ getQueueName(currentLog.queue) }}
          </el-descriptions-item>
          <el-descriptions-item label="任务ID">
            {{ currentLog.task_id || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="IP地址">
            {{ currentLog.ip_address || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="用户">
            {{ currentLog.user_name || '-' }}
          </el-descriptions-item>
          <el-descriptions-item label="消息" :span="2">
            {{ currentLog.message }}
          </el-descriptions-item>
        </el-descriptions>

        <div v-if="currentLog.context" style="margin-top: 20px">
          <h4>上下文信息</h4>
          <div class="context-box">
            <pre>{{ formatContext(currentLog.context) }}</pre>
          </div>
        </div>

        <div v-if="currentLog.stack_trace" style="margin-top: 20px">
          <h4>堆栈跟踪</h4>
          <div class="stack-trace-box">
            <pre>{{ currentLog.stack_trace }}</pre>
          </div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Refresh, Download, Delete } from '@element-plus/icons-vue'
import { getQueueLogs, clearQueueLogs, exportQueueLogs } from '@/api/queue'

const loading = ref(false)
const logList = ref([])
const dateRange = ref(null)
const contextVisible = ref(false)
const currentLog = ref(null)

const filters = reactive({
  queue: '',
  level: '',
  keyword: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 50,
  total: 0
})

// 表格高度
const tableHeight = computed(() => {
  return window.innerHeight - 380
})

// 加载日志
const loadLogs = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      pageSize: pagination.pageSize,
      ...filters
    }

    // 添加时间范围
    if (dateRange.value && dateRange.value.length === 2) {
      params.start_time = dateRange.value[0].toISOString()
      params.end_time = dateRange.value[1].toISOString()
    }

    const { data } = await getQueueLogs(params)
    logList.value = data.list || data.data || []
    pagination.total = data.total || 0
  } catch (error) {
    ElMessage.error('加载失败：' + error.message)
  } finally {
    loading.value = false
  }
}

// 级别类型
const getLevelType = (level) => {
  const map = {
    debug: 'info',
    info: '',
    warning: 'warning',
    error: 'danger'
  }
  return map[level] || ''
}

// 级别文本
const getLevelText = (level) => {
  const map = {
    debug: '调试',
    info: '信息',
    warning: '警告',
    error: '错误'
  }
  return map[level] || level
}

// 队列名称
const getQueueName = (queue) => {
  const map = {
    'ai-image': 'AI图片生成',
    thumbnail: '缩略图生成',
    watermark: '水印处理',
    video: '视频转码'
  }
  return map[queue] || queue
}

// 格式化时间
const formatTime = (time) => {
  if (!time) return '-'
  return new Date(time).toLocaleString('zh-CN', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

// 格式化上下文
const formatContext = (context) => {
  if (!context) return ''
  if (typeof context === 'string') {
    try {
      return JSON.stringify(JSON.parse(context), null, 2)
    } catch {
      return context
    }
  }
  return JSON.stringify(context, null, 2)
}

// 查看上下文
const showContext = (row) => {
  currentLog.value = row
  contextVisible.value = true
}

// 查看任务详情
const viewTaskDetail = (row) => {
  // 这里可以跳转到任务详情页面或打开详情对话框
  ElMessage.info(`查看任务 #${row.task_id} 的详情`)
  // 可以emit一个事件让父组件处理
}

// 导出日志
const exportLogs = async () => {
  try {
    const params = { ...filters }

    // 添加时间范围
    if (dateRange.value && dateRange.value.length === 2) {
      params.start_time = dateRange.value[0].toISOString()
      params.end_time = dateRange.value[1].toISOString()
    }

    await ElMessageBox.confirm('确定要导出筛选后的日志吗？', '导出确认', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'info'
    })

    const { data } = await exportQueueLogs(params)

    // 创建下载链接
    const url = window.URL.createObjectURL(new Blob([data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `queue_logs_${Date.now()}.csv`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)

    ElMessage.success('导出成功')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('导出失败：' + error.message)
    }
  }
}

// 清空日志
const clearLogs = async () => {
  try {
    await ElMessageBox.confirm(
      '确定要清空所有队列日志吗？此操作不可恢复！',
      '警告',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
        confirmButtonClass: 'el-button--danger'
      }
    )

    await clearQueueLogs()
    ElMessage.success('日志已清空')
    loadLogs()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('清空失败：' + error.message)
    }
  }
}

// 初始化
loadLogs()
</script>

<style scoped lang="scss">
.queue-logs {
  .toolbar {
    margin-bottom: 20px;

    .filter-form {
      :deep(.el-form-item) {
        margin-bottom: 12px;
      }
    }

    .toolbar-actions {
      margin-top: 10px;
    }
  }

  .log-container {
    border: 1px solid #ebeef5;
    border-radius: 4px;
  }

  .message-cell {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;

    .message-text {
      flex: 1;
      overflow: hidden;
      text-overflow: ellipsis;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      line-height: 1.5;
    }
  }

  .log-detail {
    h4 {
      margin: 0 0 10px 0;
      font-size: 14px;
      color: #303133;
      font-weight: 600;
    }

    .context-box,
    .stack-trace-box {
      background-color: #f5f7fa;
      border: 1px solid #e4e7ed;
      border-radius: 4px;
      padding: 15px;
      max-height: 400px;
      overflow-y: auto;

      pre {
        margin: 0;
        font-family: 'Courier New', monospace;
        font-size: 12px;
        line-height: 1.6;
        color: #303133;
        white-space: pre-wrap;
        word-wrap: break-word;
      }
    }

    .stack-trace-box {
      background-color: #fef0f0;
      border-color: #fde2e2;

      pre {
        color: #f56c6c;
      }
    }
  }
}
</style>
