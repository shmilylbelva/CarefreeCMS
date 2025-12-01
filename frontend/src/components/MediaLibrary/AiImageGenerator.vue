<template>
  <div class="ai-image-generator">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>AI 图片生成</span>
          <el-button link @click="showHistory = !showHistory">
            {{ showHistory ? '返回生成' : '查看历史' }}
          </el-button>
        </div>
      </template>

      <!-- 生成表单 -->
      <div v-if="!showHistory" class="generator-form">
        <el-form ref="formRef" :model="formData" :rules="formRules" label-width="100px">
          <!-- AI 模型选择 -->
          <el-form-item label="AI 模型" prop="ai_model_id">
            <el-select v-model="formData.ai_model_id" placeholder="请选择 AI 模型" style="width: 100%">
              <el-option
                v-for="model in aiModels"
                :key="model.id"
                :label="model.model_name"
                :value="model.id"
              >
                <span>{{ model.model_name }}</span>
                <span style="color: #999; font-size: 12px; margin-left: 10px;">
                  {{ model.provider?.name }}
                </span>
              </el-option>
            </el-select>
          </el-form-item>

          <!-- 提示词模板 -->
          <el-form-item label="提示词模板">
            <el-select
              v-model="formData.template_id"
              placeholder="选择模板（可选）"
              clearable
              style="width: 100%"
              @change="handleTemplateChange"
            >
              <el-option
                v-for="template in templates"
                :key="template.id"
                :label="template.name"
                :value="template.id"
              />
            </el-select>
          </el-form-item>

          <!-- 提示词 -->
          <el-form-item label="提示词" prop="prompt">
            <el-input
              v-model="formData.prompt"
              type="textarea"
              :rows="4"
              placeholder="描述你想生成的图片内容..."
              show-word-limit
              :maxlength="1000"
            />
          </el-form-item>

          <!-- 负面提示词 -->
          <el-form-item label="负面提示词">
            <el-input
              v-model="formData.negative_prompt"
              type="textarea"
              :rows="2"
              placeholder="描述你不想在图片中出现的内容..."
            />
          </el-form-item>

          <!-- 图片尺寸 -->
          <el-form-item label="图片尺寸">
            <el-row :gutter="10">
              <el-col :span="8">
                <el-select v-model="sizePreset" @change="handleSizePresetChange">
                  <el-option label="1:1 方形" value="1024x1024" />
                  <el-option label="16:9 横屏" value="1792x1024" />
                  <el-option label="9:16 竖屏" value="1024x1792" />
                  <el-option label="自定义" value="custom" />
                </el-select>
              </el-col>
              <el-col :span="8">
                <el-input-number
                  v-model="formData.width"
                  :min="256"
                  :max="2048"
                  :step="64"
                  :disabled="sizePreset !== 'custom'"
                />
              </el-col>
              <el-col :span="8">
                <el-input-number
                  v-model="formData.height"
                  :min="256"
                  :max="2048"
                  :step="64"
                  :disabled="sizePreset !== 'custom'"
                />
              </el-col>
            </el-row>
          </el-form-item>

          <!-- 生成数量 -->
          <el-form-item label="生成数量">
            <el-slider
              v-model="formData.image_count"
              :min="1"
              :max="4"
              :step="1"
              show-stops
              :marks="{ 1: '1', 2: '2', 3: '3', 4: '4' }"
            />
          </el-form-item>

          <!-- 质量和风格 -->
          <el-form-item label="质量">
            <el-radio-group v-model="formData.quality">
              <el-radio value="standard">标准</el-radio>
              <el-radio value="hd">高清</el-radio>
            </el-radio-group>
          </el-form-item>

          <el-form-item label="风格">
            <el-radio-group v-model="formData.style">
              <el-radio value="vivid">鲜明</el-radio>
              <el-radio value="natural">自然</el-radio>
            </el-radio-group>
          </el-form-item>

          <!-- 操作按钮 -->
          <el-form-item>
            <el-button type="primary" @click="handleGenerate" :loading="generating">
              <el-icon><MagicStick /></el-icon>
              开始生成
            </el-button>
            <el-button @click="handleReset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>

      <!-- 生成历史 -->
      <div v-else class="history-list">
        <el-table :data="taskList" v-loading="loadingTasks">
          <el-table-column prop="id" label="ID" width="80" />
          <el-table-column prop="prompt" label="提示词" show-overflow-tooltip />
          <el-table-column prop="status" label="状态" width="100">
            <template #default="{ row }">
              <el-tag :type="getStatusType(row.status)">
                {{ getStatusText(row.status) }}
              </el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="progress" label="进度" width="100">
            <template #default="{ row }">
              <el-progress :percentage="row.progress" :status="row.status === 'completed' ? 'success' : ''" />
            </template>
          </el-table-column>
          <el-table-column prop="created_at" label="创建时间" width="180" />
          <el-table-column label="操作" width="150" fixed="right">
            <template #default="{ row }">
              <el-button
                v-if="row.status === 'pending'"
                link
                size="small"
                type="primary"
                @click="handleExecuteTask(row)"
              >
                执行
              </el-button>
              <el-button
                v-if="row.status === 'pending'"
                link
                size="small"
                type="danger"
                @click="handleCancelTask(row)"
              >
                取消
              </el-button>
              <el-button
                v-if="row.status === 'failed'"
                link
                size="small"
                type="warning"
                @click="handleRetryTask(row)"
              >
                重试
              </el-button>
              <el-button
                v-if="row.status === 'completed'"
                link
                size="small"
                @click="handleViewResult(row)"
              >
                查看
              </el-button>
            </template>
          </el-table-column>
        </el-table>

        <el-pagination
          v-model:current-page="taskPage"
          v-model:page-size="taskPageSize"
          :total="taskTotal"
          layout="total, prev, pager, next"
          @change="loadTasks"
          style="margin-top: 15px; justify-content: center;"
        />
      </div>
    </el-card>

    <!-- 结果预览对话框 -->
    <el-dialog
      v-model="resultDialogVisible"
      title="生成结果"
      width="800px"
    >
      <div class="result-preview" v-loading="loadingResult">
        <div v-if="currentResult" class="result-grid">
          <div
            v-for="(media, index) in currentResult.generated_media"
            :key="index"
            class="result-item"
          >
            <el-image
              :src="media.url || media.file?.url"
              fit="contain"
              :preview-src-list="previewList"
              :initial-index="index"
            />
          </div>
        </div>
        <el-empty v-else description="暂无结果" />
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { MagicStick } from '@element-plus/icons-vue'
import {
  getAiModels,
  getPromptTemplates,
  generateAiImage,
  getAiImageList,
  cancelGeneration
} from '@/api/aiImage'

const emit = defineEmits(['generated'])

// 表单数据
const formRef = ref(null)
const formData = reactive({
  ai_model_id: null,
  template_id: null,
  prompt: '',
  negative_prompt: '',
  width: 1024,
  height: 1024,
  image_count: 1,
  quality: 'standard',
  style: 'vivid'
})

const formRules = {
  ai_model_id: [{ required: true, message: '请选择 AI 模型', trigger: 'change' }],
  prompt: [{ required: true, message: '请输入提示词', trigger: 'blur' }]
}

// 尺寸预设
const sizePreset = ref('1024x1024')

// 数据
const aiModels = ref([])
const templates = ref([])
const generating = ref(false)

// 历史记录
const showHistory = ref(false)
const taskList = ref([])
const loadingTasks = ref(false)
const taskPage = ref(1)
const taskPageSize = ref(10)
const taskTotal = ref(0)

// 结果预览
const resultDialogVisible = ref(false)
const currentResult = ref(null)
const loadingResult = ref(false)

const previewList = computed(() => {
  if (!currentResult.value?.generated_media) return []
  return currentResult.value.generated_media.map(m => m.url || m.file?.url)
})

// 加载 AI 模型
const loadModels = async () => {
  try {
    const res = await getAiModels()
    aiModels.value = res.data || []
    if (aiModels.value.length > 0) {
      formData.ai_model_id = aiModels.value[0].id
    }
  } catch (error) {
    ElMessage.error('加载 AI 模型失败')
  }
}

// 加载模板
const loadTemplates = async () => {
  try {
    const res = await getPromptTemplates({ page: 1, pageSize: 100 })
    templates.value = res.data?.list || res.data || []
  } catch (error) {
    console.error('加载模板失败', error)
  }
}

// 加载任务列表
const loadTasks = async () => {
  loadingTasks.value = true
  try {
    const res = await getAiImageList({
      page: taskPage.value,
      pageSize: taskPageSize.value
    })
    taskList.value = res.data?.list || res.data || []
    taskTotal.value = res.data?.total || taskList.value.length
  } catch (error) {
    ElMessage.error('加载任务失败')
  } finally {
    loadingTasks.value = false
  }
}

// 处理模板选择
const handleTemplateChange = (templateId) => {
  if (!templateId) return

  const template = templates.value.find(t => t.id === templateId)
  if (template) {
    if (template.prompt_template) {
      formData.prompt = template.prompt_template
    }
    if (template.negative_prompt) {
      formData.negative_prompt = template.negative_prompt
    }
    if (template.default_width) {
      formData.width = template.default_width
    }
    if (template.default_height) {
      formData.height = template.default_height
    }
  }
}

// 处理尺寸预设变化
const handleSizePresetChange = (value) => {
  if (value === 'custom') return

  const [width, height] = value.split('x').map(Number)
  formData.width = width
  formData.height = height
}

// 生成图片
const handleGenerate = async () => {
  await formRef.value?.validate()

  generating.value = true
  try {
    const res = await generateAiImage({
      ai_model_id: formData.ai_model_id,
      prompt: formData.prompt,
      negative_prompt: formData.negative_prompt,
      template_id: formData.template_id,
      image_count: formData.image_count,
      width: formData.width,
      height: formData.height,
      quality: formData.quality,
      style: formData.style
    })

    ElMessage.success('生成任务已创建')
    emit('generated', res.data)

    // 切换到历史页面
    showHistory.value = true
    loadTasks()

  } catch (error) {
    ElMessage.error(error.message || '生成失败')
  } finally {
    generating.value = false
  }
}

// 重置表单
const handleReset = () => {
  formRef.value?.resetFields()
  formData.template_id = null
  sizePreset.value = '1024x1024'
}

// 执行任务
const handleExecuteTask = async (row) => {
  // 此功能需要后端实现execute接口
  ElMessage.info('任务将自动执行')
}

// 取消任务
const handleCancelTask = async (row) => {
  try {
    await cancelGeneration(row.id)
    ElMessage.success('任务已取消')
    loadTasks()
  } catch (error) {
    ElMessage.error(error.message || '取消失败')
  }
}

// 重试任务
const handleRetryTask = async (row) => {
  ElMessage.info('重试功能开发中')
}

// 查看结果
const handleViewResult = async (row) => {
  resultDialogVisible.value = true
  loadingResult.value = true
  try {
    // 这里需要调用获取任务详情的接口
    currentResult.value = {
      task: row,
      generated_media: row.generated_media || []
    }
  } catch (error) {
    ElMessage.error('加载结果失败')
  } finally {
    loadingResult.value = false
  }
}

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    pending: 'info',
    processing: 'warning',
    completed: 'success',
    failed: 'danger'
  }
  return types[status] || 'info'
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    pending: '待处理',
    processing: '处理中',
    completed: '已完成',
    failed: '失败'
  }
  return texts[status] || status
}

onMounted(() => {
  loadModels()
  loadTemplates()
})
</script>

<style scoped>
.ai-image-generator {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.generator-form {
  max-width: 800px;
}

.history-list {
  min-height: 400px;
}

.result-preview {
  min-height: 300px;
}

.result-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
}

.result-item {
  border: 1px solid #eee;
  border-radius: 4px;
  overflow: hidden;
}

.result-item .el-image {
  width: 100%;
  height: 300px;
}
</style>
