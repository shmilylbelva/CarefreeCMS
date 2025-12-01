<template>
  <div class="model-list-container">
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="所属厂商">
          <el-select v-model="searchForm.provider_id" placeholder="全部" style="width: 200px" clearable filterable>
            <el-option
              v-for="provider in providers"
              :key="provider.id"
              :label="provider.name"
              :value="provider.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="全部" style="width: 120px" clearable>
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="searchForm.keyword" placeholder="模型代码或名称" style="width: 200px" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
          <el-button type="success" @click="handleAdd">添加模型</el-button>
        </el-form-item>
      </el-form>

      <!-- 模型列表 -->
      <el-table :data="modelList" v-loading="loading" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column label="所属厂商" width="150">
          <template #default="{ row }">
            <el-tag size="small">{{ row.provider_name }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="模型信息" width="250">
          <template #default="{ row }">
            <div>
              <div style="font-weight: bold;">{{ row.model_name }}</div>
              <div style="font-size: 12px; color: #666; margin-top: 4px;">
                <code>{{ row.model_code }}</code>
              </div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column label="上下文" width="100" align="center">
          <template #default="{ row }">
            <span v-if="row.context_window">{{ formatContextWindow(row.context_window) }}</span>
            <span v-else style="color: #999;">-</span>
          </template>
        </el-table-column>
        <el-table-column label="核心能力" width="180">
          <template #default="{ row }">
            <div style="display: flex; gap: 4px; flex-wrap: wrap; line-height: 1.5;">
              <el-tag v-if="row.supports_text_generation" size="small" type="primary">文本</el-tag>
              <el-tag v-if="row.supports_code_generation" size="small" type="success">代码</el-tag>
              <el-tag v-if="row.supports_image_input" size="small" type="warning">图像</el-tag>
              <el-tag v-if="row.supports_audio_input" size="small" type="info">音频</el-tag>
              <el-tag v-if="row.supports_video_input" size="small" type="danger">视频</el-tag>
              <el-tag v-if="row.supports_document_parsing" size="small">文档</el-tag>
              <el-tag v-if="row.supports_web_search" size="small" effect="plain">联网</el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="类型" width="80">
          <template #default="{ row }">
            <el-tag v-if="row.is_builtin" type="info" size="small">内置</el-tag>
            <el-tag v-else-if="row.is_custom" type="warning" size="small">自定义</el-tag>
            <el-tag v-else type="success" size="small">预设</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-switch
              v-model="row.status"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(row)"
            />
          </template>
        </el-table-column>
        <el-table-column prop="sort_order" label="排序" width="80" />
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button
              link
              type="danger"
              size="small"
              @click="handleDelete(row)"
              v-if="!row.is_builtin"
            >删除</el-button>
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
        @size-change="fetchModels"
        @current-change="fetchModels"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑模型' : '添加模型'"
      width="700px"
      @close="resetForm"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="120px">
        <el-form-item label="所属厂商" prop="provider_id">
          <el-select
            v-model="form.provider_id"
            placeholder="请选择所属厂商"
            style="width: 100%"
            :disabled="isEdit && form.is_builtin"
            filterable
          >
            <el-option
              v-for="provider in providers"
              :key="provider.id"
              :label="provider.name"
              :value="provider.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="模型代码" prop="model_code">
          <el-input
            v-model="form.model_code"
            placeholder="请输入模型代码（如：gpt-4）"
            :disabled="isEdit && form.is_builtin"
          />
          <span class="form-tip">用于API调用的模型标识</span>
        </el-form-item>

        <el-form-item label="模型名称" prop="model_name">
          <el-input v-model="form.model_name" placeholder="请输入模型显示名称（如：GPT-4）" />
        </el-form-item>

        <el-form-item label="模型描述">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="2"
            placeholder="请输入模型描述"
          />
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="上下文窗口">
              <el-input-number
                v-model="form.context_window"
                :min="0"
                :max="10000000"
                :step="1024"
                style="width: 100%"
              />
              <span class="form-tip">单位：tokens</span>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="最大输出">
              <el-input-number
                v-model="form.max_output_tokens"
                :min="0"
                :max="1000000"
                style="width: 100%"
              />
              <span class="form-tip">单位：tokens</span>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="基础能力">
          <el-checkbox-group v-model="features">
            <el-checkbox label="text_generation">文本生成</el-checkbox>
            <el-checkbox label="streaming">流式输出</el-checkbox>
            <el-checkbox label="functions">函数调用</el-checkbox>
            <el-checkbox label="embeddings">嵌入向量</el-checkbox>
          </el-checkbox-group>
        </el-form-item>

        <el-form-item label="多模态能力">
          <div style="display: flex; flex-direction: column; gap: 10px;">
            <div>
              <div style="font-size: 12px; color: #666; margin-bottom: 5px;">图像能力</div>
              <el-checkbox-group v-model="features">
                <el-checkbox label="image_input">图像理解</el-checkbox>
                <el-checkbox label="image_generation">图像生成</el-checkbox>
              </el-checkbox-group>
            </div>
            <div>
              <div style="font-size: 12px; color: #666; margin-bottom: 5px;">音频能力</div>
              <el-checkbox-group v-model="features">
                <el-checkbox label="audio_input">音频输入(STT)</el-checkbox>
                <el-checkbox label="audio_output">音频输出(TTS)</el-checkbox>
                <el-checkbox label="audio_generation">音频生成</el-checkbox>
                <el-checkbox label="realtime_voice">实时语音</el-checkbox>
              </el-checkbox-group>
            </div>
            <div>
              <div style="font-size: 12px; color: #666; margin-bottom: 5px;">视频能力</div>
              <el-checkbox-group v-model="features">
                <el-checkbox label="video_input">视频理解</el-checkbox>
                <el-checkbox label="video_generation">视频生成</el-checkbox>
              </el-checkbox-group>
            </div>
          </div>
        </el-form-item>

        <el-form-item label="专项能力">
          <el-checkbox-group v-model="features">
            <el-checkbox label="code_generation">代码生成</el-checkbox>
            <el-checkbox label="code_interpreter">代码解释器</el-checkbox>
            <el-checkbox label="document_parsing">文档解析</el-checkbox>
            <el-checkbox label="web_search">网络搜索</el-checkbox>
          </el-checkbox-group>
        </el-form-item>

        <el-form-item label="排序" prop="sort_order">
          <el-input-number v-model="form.sort_order" :min="0" :max="999" />
          <span class="form-tip">数字越小越靠前</span>
        </el-form-item>

        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :value="1">启用</el-radio>
            <el-radio :value="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="自定义" v-if="!isEdit">
          <el-switch v-model="form.is_custom" :active-value="1" :inactive-value="0" />
          <span class="form-tip">自定义模型可以完全删除</span>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="saving">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { useRoute } from 'vue-router'
import {
  getAllAiProviders,
  getAiModelList,
  createAiModel,
  updateAiModel,
  deleteAiModel
} from '@/api/ai'

const route = useRoute()

// 数据
const loading = ref(false)
const modelList = ref([])
const providers = ref([])

const searchForm = reactive({
  provider_id: route.query.provider_id || '',
  status: '',
  keyword: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

// 对话框
const dialogVisible = ref(false)
const isEdit = ref(false)
const formRef = ref(null)
const saving = ref(false)

const form = reactive({
  id: null,
  provider_id: null,
  model_code: '',
  model_name: '',
  description: '',
  context_window: null,
  max_output_tokens: null,
  // 基础能力
  supports_text_generation: 0,
  supports_functions: 0,
  supports_streaming: 0,
  supports_embeddings: 0,
  // 多模态能力 - 图像
  supports_image_input: 0,
  supports_image_generation: 0,
  // 多模态能力 - 音频
  supports_audio_input: 0,
  supports_audio_output: 0,
  supports_audio_generation: 0,
  supports_realtime_voice: 0,
  // 多模态能力 - 视频
  supports_video_input: 0,
  supports_video_generation: 0,
  // 专项能力
  supports_code_generation: 0,
  supports_code_interpreter: 0,
  supports_document_parsing: 0,
  supports_web_search: 0,
  // 其他
  is_custom: 0,
  status: 1,
  sort_order: 0
})

const features = ref([])

// 监听features变化，更新form中的能力字段
const updateFeatures = () => {
  // 基础能力
  form.supports_text_generation = features.value.includes('text_generation') ? 1 : 0
  form.supports_functions = features.value.includes('functions') ? 1 : 0
  form.supports_streaming = features.value.includes('streaming') ? 1 : 0
  form.supports_embeddings = features.value.includes('embeddings') ? 1 : 0
  // 多模态能力 - 图像
  form.supports_image_input = features.value.includes('image_input') ? 1 : 0
  form.supports_image_generation = features.value.includes('image_generation') ? 1 : 0
  // 多模态能力 - 音频
  form.supports_audio_input = features.value.includes('audio_input') ? 1 : 0
  form.supports_audio_output = features.value.includes('audio_output') ? 1 : 0
  form.supports_audio_generation = features.value.includes('audio_generation') ? 1 : 0
  form.supports_realtime_voice = features.value.includes('realtime_voice') ? 1 : 0
  // 多模态能力 - 视频
  form.supports_video_input = features.value.includes('video_input') ? 1 : 0
  form.supports_video_generation = features.value.includes('video_generation') ? 1 : 0
  // 专项能力
  form.supports_code_generation = features.value.includes('code_generation') ? 1 : 0
  form.supports_code_interpreter = features.value.includes('code_interpreter') ? 1 : 0
  form.supports_document_parsing = features.value.includes('document_parsing') ? 1 : 0
  form.supports_web_search = features.value.includes('web_search') ? 1 : 0
}

const rules = {
  provider_id: [{ required: true, message: '请选择所属厂商', trigger: 'change' }],
  model_code: [{ required: true, message: '请输入模型代码', trigger: 'blur' }],
  model_name: [{ required: true, message: '请输入模型名称', trigger: 'blur' }],
  sort_order: [{ required: true, message: '请输入排序', trigger: 'blur' }]
}

// 格式化上下文窗口
const formatContextWindow = (value) => {
  if (value >= 1000000) {
    return (value / 1000000).toFixed(1) + 'M'
  } else if (value >= 1000) {
    return (value / 1000).toFixed(0) + 'K'
  }
  return value
}

// 获取厂商列表
const fetchProviders = async () => {
  try {
    const res = await getAllAiProviders()
    providers.value = res.data
  } catch (error) {
    ElMessage.error('获取厂商列表失败')
  }
}

// 获取模型列表
const fetchModels = async () => {
  loading.value = true
  try {
    const res = await getAiModelList({
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    })
    modelList.value = res.data.list
    pagination.total = res.data.total
  } catch (error) {
    ElMessage.error('获取模型列表失败')
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  fetchModels()
}

// 重置
const handleReset = () => {
  searchForm.provider_id = ''
  searchForm.status = ''
  searchForm.keyword = ''
  pagination.page = 1
  fetchModels()
}

// 添加
const handleAdd = () => {
  isEdit.value = false
  dialogVisible.value = true
  resetForm()
}

// 编辑
const handleEdit = (row) => {
  isEdit.value = true
  dialogVisible.value = true

  // 深拷贝数据
  const rowCopy = JSON.parse(JSON.stringify(row))

  Object.keys(form).forEach(key => {
    if (rowCopy[key] !== undefined) {
      form[key] = rowCopy[key]
    }
  })

  // 设置特性复选框
  features.value = []
  // 基础能力
  if (form.supports_text_generation) features.value.push('text_generation')
  if (form.supports_functions) features.value.push('functions')
  if (form.supports_streaming) features.value.push('streaming')
  if (form.supports_embeddings) features.value.push('embeddings')
  // 多模态能力 - 图像
  if (form.supports_image_input) features.value.push('image_input')
  if (form.supports_image_generation) features.value.push('image_generation')
  // 多模态能力 - 音频
  if (form.supports_audio_input) features.value.push('audio_input')
  if (form.supports_audio_output) features.value.push('audio_output')
  if (form.supports_audio_generation) features.value.push('audio_generation')
  if (form.supports_realtime_voice) features.value.push('realtime_voice')
  // 多模态能力 - 视频
  if (form.supports_video_input) features.value.push('video_input')
  if (form.supports_video_generation) features.value.push('video_generation')
  // 专项能力
  if (form.supports_code_generation) features.value.push('code_generation')
  if (form.supports_code_interpreter) features.value.push('code_interpreter')
  if (form.supports_document_parsing) features.value.push('document_parsing')
  if (form.supports_web_search) features.value.push('web_search')
}

// 重置表单
const resetForm = () => {
  form.id = null
  form.provider_id = searchForm.provider_id || null
  form.model_code = ''
  form.model_name = ''
  form.description = ''
  form.context_window = null
  form.max_output_tokens = null
  // 重置所有能力字段
  form.supports_text_generation = 0
  form.supports_functions = 0
  form.supports_streaming = 0
  form.supports_embeddings = 0
  form.supports_image_input = 0
  form.supports_image_generation = 0
  form.supports_audio_input = 0
  form.supports_audio_output = 0
  form.supports_audio_generation = 0
  form.supports_realtime_voice = 0
  form.supports_video_input = 0
  form.supports_video_generation = 0
  form.supports_code_generation = 0
  form.supports_code_interpreter = 0
  form.supports_document_parsing = 0
  form.supports_web_search = 0
  // 其他
  form.is_custom = 0
  form.status = 1
  form.sort_order = 0
  features.value = []
  formRef.value?.clearValidate()
}

// 提交
const handleSubmit = async () => {
  await formRef.value.validate()

  // 更新特性字段
  updateFeatures()

  saving.value = true
  try {
    if (isEdit.value) {
      await updateAiModel(form.id, form)
      ElMessage.success('更新成功')
    } else {
      await createAiModel(form)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    fetchModels()
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  } finally {
    saving.value = false
  }
}

// 状态切换
const handleStatusChange = async (row) => {
  try {
    await updateAiModel(row.id, { status: row.status })
    ElMessage.success('状态更新成功')
  } catch (error) {
    // 失败时恢复状态
    row.status = row.status === 1 ? 0 : 1
    ElMessage.error('状态更新失败')
  }
}

// 删除
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除模型"${row.model_name}"吗？删除后无法恢复。`, '提示', {
      type: 'warning',
      confirmButtonText: '确定删除',
      cancelButtonText: '取消'
    })

    await deleteAiModel(row.id)
    ElMessage.success('删除成功')
    fetchModels()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

onMounted(() => {
  fetchProviders()
  fetchModels()
})
</script>

<style scoped>
.model-list-container {
  padding: 20px;
}

.search-form {
  margin-bottom: 0;
}

.form-tip {
  margin-left: 10px;
  font-size: 12px;
  color: #999;
}

code {
  background: #f5f5f5;
  padding: 2px 6px;
  border-radius: 3px;
  font-size: 12px;
  color: #e03997;
}
</style>
