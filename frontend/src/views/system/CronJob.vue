<template>
  <div class="cron-job-container">
    <!-- 搜索区域 -->
    <el-card class="search-card" shadow="never">
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="任务名称">
          <el-input
            v-model="searchForm.name"
            placeholder="请输入任务名称"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="任务标题">
          <el-input
            v-model="searchForm.title"
            placeholder="请输入任务标题"
            clearable
            style="width: 200px"
          />
        </el-form-item>
        <el-form-item label="状态">
          <el-select
            v-model="searchForm.is_enabled"
            placeholder="请选择状态"
            clearable
            style="width: 120px"
          >
            <el-option label="全部" value="" />
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="任务类型">
          <el-select
            v-model="searchForm.is_system"
            placeholder="请选择类型"
            clearable
            style="width: 120px"
          >
            <el-option label="全部" value="" />
            <el-option label="系统任务" :value="1" />
            <el-option label="自定义任务" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" icon="Search" @click="handleSearch">
            搜索
          </el-button>
          <el-button icon="Refresh" @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 操作按钮 -->
    <el-card class="toolbar-card" shadow="never">
      <el-row :gutter="10">
        <el-col :span="1.5">
          <el-button
            type="primary"
            icon="Plus"
            @click="handleCreate"
          >
            新建任务
          </el-button>
        </el-col>
        <el-col :span="1.5">
          <el-button
            type="success"
            icon="DocumentCopy"
            @click="handleShowPresets"
          >
            预设任务
          </el-button>
        </el-col>
        <el-col :span="1.5">
          <el-button
            type="warning"
            icon="Delete"
            :disabled="selectedIds.length === 0"
            @click="handleBatchDelete"
          >
            批量删除
          </el-button>
        </el-col>
        <el-col :span="1.5">
          <el-button
            type="info"
            icon="Document"
            @click="handleShowLogs"
          >
            执行日志
          </el-button>
        </el-col>
        <el-col :span="1.5">
          <el-button
            type="danger"
            icon="Delete"
            @click="handleCleanLogs"
          >
            清理日志
          </el-button>
        </el-col>
      </el-row>
    </el-card>

    <!-- 数据表格 -->
    <el-card class="table-card" shadow="never">
      <el-table
        v-loading="loading"
        :data="tableData"
        @selection-change="handleSelectionChange"
        border
        stripe
      >
        <el-table-column type="selection" width="55" align="center" />
        <el-table-column prop="id" label="ID" width="80" align="center" />
        <el-table-column prop="name" label="任务名称" min-width="150">
          <template #default="{ row }">
            <el-tag v-if="row.is_system" type="warning" size="small">
              系统
            </el-tag>
            {{ row.name }}
          </template>
        </el-table-column>
        <el-table-column prop="title" label="任务标题" min-width="150" />
        <el-table-column prop="cron_expression" label="Cron表达式" width="130" align="center">
          <template #default="{ row }">
            <el-tooltip :content="getCronDescription(row.cron_expression)" placement="top">
              <el-tag type="info" size="small">{{ row.cron_expression }}</el-tag>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="command" label="执行命令" width="150" />
        <el-table-column prop="is_enabled" label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-switch
              v-model="row.is_enabled"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="run_count" label="运行次数" width="100" align="center" />
        <el-table-column prop="last_run_time" label="最后运行" width="160" align="center">
          <template #default="{ row }">
            <div v-if="row.last_run_time">
              <div>{{ row.last_run_time }}</div>
              <el-tag
                v-if="row.last_run_status"
                :type="getStatusType(row.last_run_status)"
                size="small"
              >
                {{ getStatusText(row.last_run_status) }}
              </el-tag>
            </div>
            <span v-else>-</span>
          </template>
        </el-table-column>
        <el-table-column prop="next_run_time" label="下次运行" width="160" align="center">
          <template #default="{ row }">
            {{ row.next_run_time || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="280" align="center" fixed="right">
          <template #default="{ row }">
            <el-button
              type="success"
              size="small"
              icon="VideoPlay"
              @click="handleRun(row)"
            >
              执行
            </el-button>
            <el-button
              type="primary"
              size="small"
              icon="Edit"
              @click="handleEdit(row)"
            >
              编辑
            </el-button>
            <el-button
              type="info"
              size="small"
              icon="Document"
              @click="handleViewLogs(row)"
            >
              日志
            </el-button>
            <el-button
              v-if="!row.is_system"
              type="danger"
              size="small"
              icon="Delete"
              @click="handleDelete(row)"
            >
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.limit"
        :total="pagination.total"
        :page-sizes="[10, 15, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
        class="pagination"
      />
    </el-card>

    <!-- 任务表单对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="800px"
      @close="handleDialogClose"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="formRules"
        label-width="120px"
      >
        <el-form-item label="任务名称" prop="name">
          <el-input
            v-model="form.name"
            placeholder="请输入任务名称（英文字母、数字、下划线）"
            :disabled="form.is_system"
          />
        </el-form-item>
        <el-form-item label="任务标题" prop="title">
          <el-input v-model="form.title" placeholder="请输入任务标题" />
        </el-form-item>
        <el-form-item label="Cron表达式" prop="cron_expression">
          <el-input v-model="form.cron_expression" placeholder="请输入Cron表达式">
            <template #append>
              <el-button @click="showCronHelper = true">选择</el-button>
            </template>
          </el-input>
          <div v-if="cronValid" class="cron-hint">
            <el-text type="success">
              <el-icon><CircleCheck /></el-icon>
              下次运行时间: {{ nextRunTime }}
            </el-text>
          </div>
          <div v-else-if="form.cron_expression" class="cron-hint">
            <el-text type="danger">
              <el-icon><CircleClose /></el-icon>
              表达式格式不正确
            </el-text>
          </div>
        </el-form-item>
        <el-form-item label="执行命令" prop="command">
          <el-select
            v-model="form.command"
            placeholder="请选择执行命令"
            style="width: 100%"
            :disabled="form.is_system"
          >
            <el-option label="数据库备份" value="database:backup" />
            <el-option label="缓存清理" value="cache:clear" />
            <el-option label="日志清理" value="log:clean" />
            <el-option label="文章定时发布" value="article:publish" />
          </el-select>
        </el-form-item>
        <el-form-item label="命令参数">
          <el-input
            v-model="form.params"
            type="textarea"
            :rows="3"
            placeholder='请输入JSON格式参数，如: {"days":30}'
          />
        </el-form-item>
        <el-form-item label="任务描述">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请输入任务描述"
          />
        </el-form-item>
        <el-form-item label="启用状态">
          <el-switch
            v-model="form.is_enabled"
            :active-value="1"
            :inactive-value="0"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">
          确定
        </el-button>
      </template>
    </el-dialog>

    <!-- Cron表达式选择器 -->
    <el-dialog
      v-model="showCronHelper"
      title="选择Cron表达式"
      width="600px"
    >
      <el-table :data="cronExpressions" @row-click="handleSelectCron" highlight-current-row>
        <el-table-column prop="label" label="名称" width="120" />
        <el-table-column prop="expression" label="表达式" width="150" />
        <el-table-column prop="description" label="说明" />
      </el-table>
    </el-dialog>

    <!-- 预设任务对话框 -->
    <el-dialog
      v-model="showPresets"
      title="预设任务"
      width="800px"
    >
      <el-table :data="presets">
        <el-table-column prop="title" label="任务标题" width="150" />
        <el-table-column prop="cron_expression" label="Cron表达式" width="120" />
        <el-table-column prop="description" label="说明" />
        <el-table-column label="操作" width="100" align="center">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleUsePreset(row)">
              使用
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>

    <!-- 执行日志对话框 -->
    <el-dialog
      v-model="showLogDialog"
      :title="logDialogTitle"
      width="900px"
    >
      <el-table :data="logs" v-loading="logsLoading">
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column prop="status" label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)" size="small">
              {{ getStatusText(row.status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="start_time" label="开始时间" width="160" />
        <el-table-column prop="duration" label="耗时" width="80">
          <template #default="{ row }">
            {{ row.duration ? row.duration + 's' : '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="output" label="输出" min-width="200" show-overflow-tooltip />
        <el-table-column prop="error_message" label="错误信息" min-width="200" show-overflow-tooltip>
          <template #default="{ row }">
            <el-text v-if="row.error_message" type="danger">{{ row.error_message }}</el-text>
            <span v-else>-</span>
          </template>
        </el-table-column>
      </el-table>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { CircleCheck, CircleClose } from '@element-plus/icons-vue'
import {
  getCronJobList,
  createCronJob,
  updateCronJob,
  deleteCronJob,
  batchDeleteCronJobs,
  updateCronJobStatus,
  runCronJob,
  getJobLogs,
  cleanCronJobLogs,
  validateCronExpression,
  getCronJobPresets,
  getCronExpressions
} from '@/api/cronJob'

// 搜索表单
const searchForm = reactive({
  name: '',
  title: '',
  is_enabled: '',
  is_system: ''
})

// 表格数据
const loading = ref(false)
const tableData = ref([])
const selectedIds = ref([])

// 分页
const pagination = reactive({
  page: 1,
  limit: 15,
  total: 0
})

// 对话框
const dialogVisible = ref(false)
const dialogTitle = ref('')
const formRef = ref(null)
const submitting = ref(false)

// 表单数据
const form = reactive({
  id: null,
  name: '',
  title: '',
  cron_expression: '',
  command: '',
  params: '',
  description: '',
  is_enabled: 1,
  is_system: 0
})

// 表单验证规则
const formRules = {
  name: [
    { required: true, message: '请输入任务名称', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9_]+$/, message: '只能包含字母、数字和下划线', trigger: 'blur' }
  ],
  title: [
    { required: true, message: '请输入任务标题', trigger: 'blur' }
  ],
  cron_expression: [
    { required: true, message: '请输入Cron表达式', trigger: 'blur' }
  ],
  command: [
    { required: true, message: '请选择执行命令', trigger: 'change' }
  ]
}

// Cron表达式验证
const cronValid = ref(false)
const nextRunTime = ref('')
const showCronHelper = ref(false)
const cronExpressions = ref([])

// 预设任务
const showPresets = ref(false)
const presets = ref([])

// 日志
const showLogDialog = ref(false)
const logDialogTitle = ref('')
const logs = ref([])
const logsLoading = ref(false)

// 监听Cron表达式变化
watch(() => form.cron_expression, async (newVal) => {
  if (!newVal) {
    cronValid.value = false
    nextRunTime.value = ''
    return
  }

  try {
    const res = await validateCronExpression(newVal)
    cronValid.value = res.data.valid
    nextRunTime.value = res.data.next_run_time || ''
  } catch {
    cronValid.value = false
    nextRunTime.value = ''
  }
})

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    const params = {
      ...searchForm,
      page: pagination.page,
      limit: pagination.limit
    }
    const res = await getCronJobList(params)
    tableData.value = res.data.list
    pagination.total = res.data.total
  } catch (error) {
    ElMessage.error('加载数据失败')
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  loadData()
}

// 重置
const handleReset = () => {
  Object.keys(searchForm).forEach(key => {
    searchForm[key] = ''
  })
  handleSearch()
}

// 新建
const handleCreate = async () => {
  dialogTitle.value = '新建任务'
  resetForm()
  dialogVisible.value = true

  // 加载Cron表达式列表
  try {
    const res = await getCronExpressions()
    cronExpressions.value = res.data
  } catch (error) {
    console.error('加载Cron表达式失败', error)
  }
}

// 编辑
const handleEdit = (row) => {
  dialogTitle.value = '编辑任务'
  Object.keys(form).forEach(key => {
    form[key] = row[key]
  })
  // 处理params
  if (row.params && typeof row.params === 'object') {
    form.params = JSON.stringify(row.params)
  }
  dialogVisible.value = true
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm(`确定要删除任务"${row.title}"吗？`, '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      await deleteCronJob(row.id)
      ElMessage.success('删除成功')
      loadData()
    } catch (error) {
      ElMessage.error('删除失败')
    }
  })
}

// 批量删除
const handleBatchDelete = () => {
  ElMessageBox.confirm(`确定要删除选中的 ${selectedIds.value.length} 个任务吗？`, '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      await batchDeleteCronJobs(selectedIds.value)
      ElMessage.success('删除成功')
      loadData()
    } catch (error) {
      ElMessage.error('删除失败')
    }
  })
}

// 状态切换
const handleStatusChange = async (row) => {
  try {
    await updateCronJobStatus(row.id, row.is_enabled)
    const status = row.is_enabled ? '启用' : '禁用'
    ElMessage.success(`${status}成功`)
    loadData()
  } catch (error) {
    ElMessage.error('操作失败')
    row.is_enabled = row.is_enabled === 1 ? 0 : 1
  }
}

// 执行任务
const handleRun = (row) => {
  ElMessageBox.confirm(`确定要立即执行任务"${row.title}"吗？`, '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'info'
  }).then(async () => {
    const loading = ElMessage({
      message: '正在执行任务...',
      type: 'info',
      duration: 0
    })
    try {
      const res = await runCronJob(row.id)
      loading.close()
      ElMessage.success(res.data.output || '任务执行成功')
      loadData()
    } catch (error) {
      loading.close()
      ElMessage.error('任务执行失败')
    }
  })
}

// 查看日志
const handleViewLogs = async (row) => {
  logDialogTitle.value = `${row.title} - 执行日志`
  showLogDialog.value = true
  logsLoading.value = true
  try {
    const res = await getJobLogs(row.id, 50)
    logs.value = res.data
  } catch (error) {
    ElMessage.error('加载日志失败')
  } finally {
    logsLoading.value = false
  }
}

// 显示所有日志
const handleShowLogs = () => {
  logDialogTitle.value = '全部执行日志'
  // 这里可以扩展为显示所有任务的日志
  ElMessage.info('功能开发中...')
}

// 清理日志
const handleCleanLogs = () => {
  ElMessageBox.confirm('确定要清理30天前的日志吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      const res = await cleanCronJobLogs(30)
      ElMessage.success(`成功清理 ${res.data.count} 条日志`)
    } catch (error) {
      ElMessage.error('清理失败')
    }
  })
}

// 显示预设任务
const handleShowPresets = async () => {
  try {
    const res = await getCronJobPresets()
    presets.value = res.data
    showPresets.value = true
  } catch (error) {
    ElMessage.error('加载预设任务失败')
  }
}

// 使用预设任务
const handleUsePreset = (preset) => {
  Object.assign(form, preset)
  form.params = JSON.stringify(preset.params)
  showPresets.value = false
  dialogVisible.value = true
}

// 选择Cron表达式
const handleSelectCron = (row) => {
  form.cron_expression = row.expression
  showCronHelper.value = false
}

// 表格选择
const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

// 分页
const handleSizeChange = () => {
  loadData()
}

const handleCurrentChange = () => {
  loadData()
}

// 提交表单
const handleSubmit = async () => {
  const valid = await formRef.value.validate()
  if (!valid) return

  submitting.value = true
  try {
    const data = { ...form }
    // 解析params
    if (data.params) {
      try {
        data.params = JSON.parse(data.params)
      } catch {
        data.params = {}
      }
    }

    if (form.id) {
      await updateCronJob(form.id, data)
      ElMessage.success('更新成功')
    } else {
      await createCronJob(data)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    loadData()
  } catch (error) {
    ElMessage.error(form.id ? '更新失败' : '创建失败')
  } finally {
    submitting.value = false
  }
}

// 关闭对话框
const handleDialogClose = () => {
  resetForm()
  formRef.value?.resetFields()
}

// 重置表单
const resetForm = () => {
  form.id = null
  form.name = ''
  form.title = ''
  form.cron_expression = ''
  form.command = ''
  form.params = ''
  form.description = ''
  form.is_enabled = 1
  form.is_system = 0
}

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    'success': 'success',
    'failed': 'danger',
    'running': 'warning'
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    'success': '成功',
    'failed': '失败',
    'running': '运行中'
  }
  return texts[status] || '未知'
}

// 获取Cron描述
const getCronDescription = (expression) => {
  // 简单的描述映射
  const map = {
    '* * * * *': '每分钟',
    '*/5 * * * *': '每5分钟',
    '*/10 * * * *': '每10分钟',
    '0 * * * *': '每小时',
    '0 0 * * *': '每天凌晨',
    '0 2 * * *': '每天凌晨2点',
    '0 0 * * 1': '每周一',
    '0 0 * * 0': '每周日',
    '0 3 * * 0': '每周日凌晨3点',
    '0 4 * * 0': '每周日凌晨4点',
    '0 0 1 * *': '每月1号'
  }
  return map[expression] || expression
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.cron-job-container {
  padding: 20px;
}

.search-card,
.toolbar-card,
.table-card {
  margin-bottom: 20px;
}

.search-form {
  margin-bottom: -10px;
}

.pagination {
  margin-top: 20px;
  text-align: right;
}

.cron-hint {
  margin-top: 5px;
  font-size: 13px;
}
</style>
