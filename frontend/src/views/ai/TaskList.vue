<template>
  <div class="ai-task-list">
    <!-- 统计卡片 -->
    <el-row :gutter="20" class="stats-row">
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-item">
            <div class="stat-label">总任务数</div>
            <div class="stat-value">{{ statistics.total || 0 }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-item">
            <div class="stat-label">处理中</div>
            <div class="stat-value processing">{{ statistics.processing || 0 }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-item">
            <div class="stat-label">已完成</div>
            <div class="stat-value success">{{ statistics.completed || 0 }}</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-item">
            <div class="stat-label">已生成文章</div>
            <div class="stat-value">{{ statistics.total_success || 0 }}</div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <el-card style="margin-top: 20px">
      <template #header>
        <div class="header-actions">
          <h3>AI文章生成任务</h3>
          <el-button type="primary" @click="handleAdd">
            <el-icon><plus /></el-icon>
            创建任务
          </el-button>
        </div>
      </template>

      <!-- 搜索过滤 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="全部" clearable style="width: 150px">
            <el-option
              v-for="(label, value) in statuses"
              :key="value"
              :label="label"
              :value="value"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="fetchTasks">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <el-table :data="taskList" v-loading="loading" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="title" label="任务名称" min-width="200" />
        <el-table-column label="主题" min-width="200">
          <template #default="{ row }">
            <el-tooltip :content="row.topic" placement="top" :show-after="300">
              <div class="topic-text">
                <template v-if="getTopicCount(row.topic) > 1">
                  {{ getFirstTopic(row.topic) }}
                  <el-tag size="small" type="info" style="margin-left: 5px;">
                    等{{ getTopicCount(row.topic) }}个主题
                  </el-tag>
                </template>
                <template v-else>
                  {{ row.topic }}
                </template>
              </div>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column label="AI配置" width="150">
          <template #default="{ row }">
            {{ row.aiConfig?.name || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="进度" width="200">
          <template #default="{ row }">
            <div class="progress-info">
              <el-progress
                :percentage="row.progress"
                :status="getProgressStatus(row.status)"
              />
              <span class="progress-text">
                {{ row.success_count }}/{{ row.total_count }} 成功
              </span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag :type="getStatusType(row.status)">
              {{ row.status_text }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="180" />
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button
              size="small"
              v-if="row.status === 'pending' || row.status === 'stopped'"
              type="success"
              @click="handleStart(row.id)"
            >
              启动
            </el-button>
            <el-button
              size="small"
              v-if="row.status === 'processing'"
              type="warning"
              @click="handleStop(row.id)"
            >
              停止
            </el-button>
            <el-button size="small" @click="handleViewRecords(row)">查看记录</el-button>
            <el-button
              size="small"
              v-if="row.status === 'pending' || row.status === 'stopped'"
              @click="handleEdit(row)"
            >
              编辑
            </el-button>
            <el-button
              size="small"
              type="danger"
              v-if="row.status !== 'processing'"
              @click="handleDelete(row.id)"
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
        @size-change="fetchTasks"
        @current-change="fetchTasks"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑任务' : '创建任务'"
      width="700px"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="120px">
        <el-form-item label="任务名称" prop="title">
          <el-input v-model="form.title" placeholder="请输入任务名称" />
        </el-form-item>
        <el-form-item label="文章主题" prop="topic">
          <el-input
            v-model="form.topic"
            type="textarea"
            :rows="5"
            placeholder="请输入文章主题，每行一个主题&#10;例如：&#10;人工智能的发展趋势&#10;机器学习在医疗领域的应用&#10;深度学习技术解析"
          />
          <span class="form-tip">必填，每行一个主题，将为每个主题创建一个生成任务</span>
        </el-form-item>

        <el-form-item label="提示词模板">
          <el-select
            v-model="form.prompt_template_id"
            placeholder="可选，选择模板辅助控制文章风格和格式"
            clearable
            style="width: 100%"
            @change="handleTemplateSelect"
          >
            <el-option
              v-for="template in promptTemplates"
              :key="template.id"
              :label="template.name"
              :value="template.id"
            >
              <div style="display: flex; justify-content: space-between;">
                <span>{{ template.name }}</span>
                <el-tag size="small" style="margin-left: 10px;">{{ template.category }}</el-tag>
              </div>
            </el-option>
          </el-select>
          <span class="form-tip">可选，模板会辅助控制文章的字数、风格等参数</span>
        </el-form-item>

        <!-- 如果选择了模板，显示模板描述和变量输入 -->
        <template v-if="selectedTemplate">
          <el-form-item label="模板说明">
            <el-alert :title="selectedTemplate.description" type="info" :closable="false" show-icon />
          </el-form-item>

          <!-- 模板变量输入区域（用卡片框起来） -->
          <el-form-item v-if="templateVariables.length > 0" label="模板参数" class="template-variables-wrapper">
            <el-card shadow="never" class="template-variables-card">
              <template #header>
                <div class="card-header">
                  <span class="card-title">📝 请填写模板参数</span>
                  <el-tag type="warning" size="small">这些参数将替换提示词中的占位符</el-tag>
                </div>
              </template>

              <!-- 动态生成变量输入框 -->
              <el-form-item
                v-for="variable in templateVariables"
                :key="variable.name"
                :label="variable.label"
                :prop="'prompt_variables.' + variable.name"
                :rules="variable.required ? [{ required: true, message: `请输入${variable.label}` }] : []"
                style="margin-bottom: 18px;"
              >
                <!-- 文本输入 -->
                <el-input
                  v-if="variable.type === 'text'"
                  v-model="form.prompt_variables[variable.name]"
                  :placeholder="variable.placeholder || `请输入${variable.label}`"
                />
                <!-- 数字输入 -->
                <el-input-number
                  v-else-if="variable.type === 'number'"
                  v-model="form.prompt_variables[variable.name]"
                  :placeholder="variable.placeholder || `请输入${variable.label}`"
                  style="width: 200px"
                />
                <!-- 多行文本 -->
                <el-input
                  v-else-if="variable.type === 'textarea'"
                  v-model="form.prompt_variables[variable.name]"
                  type="textarea"
                  :rows="3"
                  :placeholder="variable.placeholder || `请输入${variable.label}`"
                />
                <!-- 下拉选择 -->
                <el-select
                  v-else-if="variable.type === 'select'"
                  v-model="form.prompt_variables[variable.name]"
                  :placeholder="`请选择${variable.label}`"
                  style="width: 100%"
                >
                  <el-option
                    v-for="option in variable.options"
                    :key="option"
                    :label="option"
                    :value="option"
                  />
                </el-select>
              </el-form-item>
            </el-card>
          </el-form-item>
        </template>
        <el-form-item label="AI配置" prop="ai_config_id">
          <el-select v-model="form.ai_config_id" placeholder="请选择AI配置" style="width: 100%">
            <el-option
              v-for="config in aiConfigs"
              :key="config.id"
              :label="config.name"
              :value="config.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="文章长度" v-if="!selectedTemplate">
          <el-radio-group v-model="form.settings.length">
            <el-radio label="short">短文（500-800字）</el-radio>
            <el-radio label="medium">中等（1000-1500字）</el-radio>
            <el-radio label="long">长文（2000-3000字）</el-radio>
          </el-radio-group>
          <span class="form-tip">使用模板时，长度由模板控制</span>
        </el-form-item>
        <el-form-item label="写作风格" v-if="!selectedTemplate">
          <el-radio-group v-model="form.settings.style">
            <el-radio label="professional">专业严谨</el-radio>
            <el-radio label="casual">轻松口语</el-radio>
            <el-radio label="creative">创意想象</el-radio>
          </el-radio-group>
          <span class="form-tip">使用模板时，风格由模板控制</span>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="saving">确定</el-button>
      </template>
    </el-dialog>

    <!-- 生成记录对话框 -->
    <el-dialog
      v-model="recordsDialogVisible"
      title="生成记录"
      width="80%"
      destroy-on-close
    >
      <TaskRecords v-if="currentTaskId" :task-id="currentTaskId" />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import {
  getTaskStatistics,
  getTaskStatuses,
  getTaskList,
  createTask,
  updateTask,
  deleteTask,
  startTask,
  stopTask,
  getAllPromptTemplates
} from '@/api/ai'
import { getAllAiConfigs } from '@/api/ai'
import TaskRecords from './TaskRecords.vue'

const loading = ref(false)
const saving = ref(false)
const dialogVisible = ref(false)
const recordsDialogVisible = ref(false)
const isEdit = ref(false)
const formRef = ref(null)
const currentTaskId = ref(null)

const taskList = ref([])
const statistics = ref({})
const statuses = ref({})
const aiConfigs = ref([])
const categories = ref([])
const promptTemplates = ref([])

const searchForm = reactive({
  status: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

const form = reactive({
  title: '',
  topic: '',
  prompt_template_id: null,
  prompt_variables: {},
  ai_config_id: null,
  settings: {
    length: 'medium',
    style: 'professional'
  }
})

const rules = {
  title: [{ required: true, message: '请输入任务名称', trigger: 'blur' }],
  topic: [{ required: true, message: '请输入文章主题，每行一个', trigger: 'blur' }],
  ai_config_id: [{ required: true, message: '请选择AI配置', trigger: 'change' }]
}

// 计算属性：当前选中的模板
const selectedTemplate = computed(() => {
  if (!form.prompt_template_id) return null
  return promptTemplates.value.find(t => t.id === form.prompt_template_id)
})

// 计算属性：模板变量列表
const templateVariables = computed(() => {
  if (!selectedTemplate.value || !selectedTemplate.value.variables) return []
  try {
    // ThinkPHP的json类型字段可能已经是对象，也可能是字符串
    let vars = selectedTemplate.value.variables
    if (typeof vars === 'string') {
      vars = JSON.parse(vars)
    }

    // 确保vars是数组
    if (!Array.isArray(vars)) {
      console.warn('模板变量格式错误，应该是数组:', vars)
      return []
    }

    // 为每个变量设置默认值
    vars.forEach(v => {
      if (v.default && !form.prompt_variables[v.name]) {
        form.prompt_variables[v.name] = v.default
      }
    })
    return vars
  } catch (e) {
    console.error('解析模板变量失败:', e)
    return []
  }
})

// 获取统计信息
const fetchStatistics = async () => {
  try {
    const res = await getTaskStatistics()
    statistics.value = res.data
  } catch (error) {
    console.error('获取统计信息失败:', error)
  }
}

// 获取状态列表
const fetchStatuses = async () => {
  try {
    const res = await getTaskStatuses()
    statuses.value = res.data
  } catch (error) {
    console.error('获取状态列表失败:', error)
  }
}

// 获取AI配置列表（只获取支持文本生成的配置）
const fetchAiConfigs = async () => {
  try {
    const res = await getAllAiConfigs({ text_generation_only: true })
    aiConfigs.value = res.data
  } catch (error) {
    console.error('获取AI配置失败:', error)
  }
}

// 获取分类列表
const fetchCategories = async () => {
  try {
    const res = await getCategoryList({ page_size: 999 })
    categories.value = res.data.list || []
  } catch (error) {
    console.error('获取分类失败:', error)
  }
}

// 获取提示词模板列表
const fetchPromptTemplates = async () => {
  try {
    const res = await getAllPromptTemplates()
    promptTemplates.value = res.data
  } catch (error) {
    console.error('获取提示词模板失败:', error)
  }
}

// 获取任务列表
const fetchTasks = async () => {
  loading.value = true
  try {
    const res = await getTaskList({
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    })
    taskList.value = res.data.list
    pagination.total = res.data.total
  } catch (error) {
    ElMessage.error('获取任务列表失败')
  } finally {
    loading.value = false
  }
}

// 重置搜索
const handleReset = () => {
  searchForm.status = ''
  pagination.page = 1
  fetchTasks()
}

// 添加
const handleAdd = () => {
  isEdit.value = false
  dialogVisible.value = true
  resetForm()
}

// 模板选择处理
const handleTemplateSelect = (templateId) => {
  // 清空变量值
  form.prompt_variables = {}

  if (!templateId) {
    // 清除模板时不清空主题，主题是独立的
    return
  }

  // 解析变量并设置默认值
  const template = promptTemplates.value.find(t => t.id === templateId)
  if (template && template.variables) {
    try {
      // ThinkPHP的json类型字段可能已经是对象，也可能是字符串
      let vars = template.variables
      if (typeof vars === 'string') {
        vars = JSON.parse(vars)
      }

      // 确保vars是数组
      if (Array.isArray(vars)) {
        vars.forEach(v => {
          if (v.default) {
            form.prompt_variables[v.name] = v.default
          }
        })
      }
    } catch (e) {
      console.error('解析模板变量失败:', e)
    }
  }
}

// 编辑
const handleEdit = (row) => {
  // 检查任务状态
  if (row.status !== 'pending' && row.status !== 'stopped') {
    handleShowEditTip(row)
    return
  }

  isEdit.value = true
  dialogVisible.value = true
  form.id = row.id
  form.title = row.title
  form.topic = row.topic
  form.prompt_template_id = row.prompt_template_id
  form.prompt_variables = row.prompt_variables || {}
  form.ai_config_id = row.ai_config_id
  form.settings = row.settings || {
    length: 'medium',
    style: 'professional'
  }
}

// 显示编辑提示
const handleShowEditTip = (row) => {
  const statusMessages = {
    processing: '任务正在处理中，暂时无法编辑。您可以先停止任务后再进行编辑。',
    completed: '任务已完成，无法再次编辑。如需修改，请创建新任务。',
    failed: '任务已失败，无法继续编辑。建议重新创建任务或联系管理员。'
  }

  const message = statusMessages[row.status] || '当前任务状态不支持编辑，只有待处理或已停止的任务可以编辑。'

  ElMessage({
    message: message,
    type: 'warning',
    duration: 4000
  })
}

// 重置表单
const resetForm = () => {
  form.title = ''
  form.topic = ''
  form.prompt_template_id = null
  form.prompt_variables = {}
  form.ai_config_id = null
  form.settings = {
    length: 'medium',
    style: 'professional'
  }
  formRef.value?.clearValidate()
}

// 提交
const handleSubmit = async () => {
  await formRef.value.validate()

  saving.value = true
  try {
    if (isEdit.value) {
      await updateTask(form.id, form)
      ElMessage.success('更新成功')
    } else {
      await createTask(form)
      ElMessage.success('任务创建成功')
    }
    dialogVisible.value = false
    fetchTasks()
    fetchStatistics()
  } catch (error) {
    // 错误消息已由 request.js 拦截器处理，这里不再重复显示
    console.error('提交失败:', error)
  } finally {
    saving.value = false
  }
}

// 启动任务
const handleStart = async (id) => {
  try {
    await ElMessageBox.confirm('确定要启动这个任务吗？', '提示', {
      type: 'info'
    })

    await startTask(id)
    ElMessage.success('任务已启动，正在后台生成文章')
    fetchTasks()
    fetchStatistics()

    // 启动轮询检查任务状态
    startPolling()
  } catch (error) {
    // 用户取消操作，不显示错误
    // API 错误已由拦截器处理
    if (error !== 'cancel') {
      console.error('启动任务失败:', error)
    }
  }
}

// 停止任务
const handleStop = async (id) => {
  try {
    await ElMessageBox.confirm('确定要停止这个任务吗？', '提示', {
      type: 'warning'
    })

    await stopTask(id)
    ElMessage.success('任务已停止')
    fetchTasks()
    fetchStatistics()
  } catch (error) {
    // 用户取消操作，不显示错误
    // API 错误已由拦截器处理
    if (error !== 'cancel') {
      console.error('停止任务失败:', error)
    }
  }
}

// 查看生成记录
const handleViewRecords = (row) => {
  currentTaskId.value = row.id
  recordsDialogVisible.value = true
}

// 删除
const handleDelete = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除这个任务吗？删除后所有生成记录也将被删除。', '提示', {
      type: 'warning'
    })

    await deleteTask(id)
    ElMessage.success('删除成功')
    fetchTasks()
    fetchStatistics()
  } catch (error) {
    // 用户取消操作，不显示错误
    // API 错误已由拦截器处理
    if (error !== 'cancel') {
      console.error('删除任务失败:', error)
    }
  }
}

// 获取主题数量
const getTopicCount = (topicString) => {
  if (!topicString) return 0
  const topics = topicString.split('\n').filter(t => t.trim())
  return topics.length
}

// 获取第一个主题
const getFirstTopic = (topicString) => {
  if (!topicString) return ''
  const topics = topicString.split('\n').filter(t => t.trim())
  return topics[0] || ''
}

// 获取进度状态
const getProgressStatus = (status) => {
  if (status === 'completed') return 'success'
  if (status === 'failed') return 'exception'
  if (status === 'processing') return ''
  return ''
}

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    pending: '',
    processing: 'warning',
    completed: 'success',
    failed: 'danger',
    stopped: 'info'
  }
  return types[status] || ''
}

// 轮询检查任务状态
let pollingTimer = null
const startPolling = () => {
  if (pollingTimer) return

  pollingTimer = setInterval(() => {
    const hasProcessing = taskList.value.some(task => task.status === 'processing')
    if (hasProcessing) {
      fetchTasks()
      fetchStatistics()
    } else {
      stopPolling()
    }
  }, 5000) // 每5秒刷新一次
}

const stopPolling = () => {
  if (pollingTimer) {
    clearInterval(pollingTimer)
    pollingTimer = null
  }
}

onMounted(() => {
  fetchStatistics()
  fetchStatuses()
  fetchAiConfigs()
  fetchPromptTemplates()
  fetchTasks()

  // 如果有处理中的任务，启动轮询
  const checkProcessing = () => {
    const hasProcessing = taskList.value.some(task => task.status === 'processing')
    if (hasProcessing) {
      startPolling()
    }
  }
  setTimeout(checkProcessing, 1000)
})

// 组件销毁时清除轮询
import { onUnmounted } from 'vue'
onUnmounted(() => {
  stopPolling()
})
</script>

<style scoped>
.stats-row {
  margin-bottom: 20px;
}

.stat-item {
  text-align: center;
  padding: 10px;
}

.stat-label {
  font-size: 14px;
  color: #666;
  margin-bottom: 10px;
}

.stat-value {
  font-size: 28px;
  font-weight: bold;
  color: #409eff;
}

.stat-value.processing {
  color: #e6a23c;
}

.stat-value.success {
  color: #67c23a;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-form {
  margin-bottom: 20px;
}

.topic-text {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.progress-info {
  padding: 5px 0;
}

.progress-text {
  font-size: 12px;
  color: #666;
  margin-top: 5px;
  display: block;
}

.form-tip {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
  display: block;
}

/* 模板变量卡片样式 */
.template-variables-wrapper {
  margin-top: 10px;
  margin-bottom: 10px;
}

.template-variables-wrapper :deep(.el-form-item__label) {
  font-weight: 600;
  color: #303133;
  align-self: flex-start;
  padding-top: 15px;
}

.template-variables-wrapper :deep(.el-form-item__content) {
  flex: 1;
  max-width: 100%;
}

.template-variables-card {
  border: 2px solid #fef0f0;
  background: #fdf6ec;
  width: 100%;
}

.template-variables-card :deep(.el-card__header) {
  background: linear-gradient(135deg, #fff7e6 0%, #fffbf0 100%);
  border-bottom: 1px solid #f5dab1;
  padding: 15px 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 15px;
  font-weight: 600;
  color: #e6a23c;
}

.template-variables-card :deep(.el-card__body) {
  padding: 25px 30px;
  background: #fffbf5;
}

.template-variables-card :deep(.el-form-item) {
  margin-bottom: 22px;
}

.template-variables-card :deep(.el-form-item:last-child) {
  margin-bottom: 0;
}
</style>
