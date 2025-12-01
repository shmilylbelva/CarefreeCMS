<template>
  <div class="ai-config-list">
    <el-card>
      <template #header>
        <div class="header-actions">
          <h3>AI配置管理</h3>
          <el-button type="primary" @click="handleAdd">
            <el-icon><plus /></el-icon>
            添加配置
          </el-button>
        </div>
      </template>

      <!-- 搜索过滤 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="AI提供商">
          <el-select v-model="searchForm.provider" placeholder="全部" clearable style="width: 150px">
            <el-option
              v-for="(label, value) in providers"
              :key="value"
              :label="label"
              :value="value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="全部" clearable style="width: 120px">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="fetchConfigs">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <el-table :data="configList" v-loading="loading" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="配置名称" min-width="150" />
        <el-table-column label="AI提供商" width="180">
          <template #default="{ row }">
            <el-tag type="primary">{{ providers[row.provider] || row.provider }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="model" label="模型" width="200" show-overflow-tooltip />
        <el-table-column label="参数配置" width="200">
          <template #default="{ row }">
            <div class="config-params">
              <span>Tokens: {{ row.max_tokens }}</span>
              <span>Temp: {{ row.temperature }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="默认" width="80" align="center">
          <template #default="{ row }">
            <el-icon v-if="row.is_default" color="#67c23a" size="20"><check /></el-icon>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'">
              {{ row.status === 1 ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="180" />
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button size="small" @click="handleTest(row.id)" :loading="testingId === row.id">
              测试
            </el-button>
            <el-button
              size="small"
              v-if="!row.is_default"
              @click="handleSetDefault(row.id)"
            >
              设为默认
            </el-button>
            <el-button size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button size="small" type="danger" @click="handleDelete(row.id)">删除</el-button>
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
        @size-change="fetchConfigs"
        @current-change="fetchConfigs"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑AI配置' : '添加AI配置'"
      width="700px"
      :close-on-click-modal="false"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="120px">
        <el-form-item label="配置名称" prop="name">
          <el-input v-model="form.name" placeholder="如：OpenAI GPT-4 生产环境" />
        </el-form-item>

        <el-form-item label="AI提供商" prop="provider">
          <el-select
            v-model="form.provider"
            placeholder="请选择AI提供商"
            style="width: 100%"
            @change="handleProviderChange"
          >
            <el-option
              v-for="(label, value) in providers"
              :key="value"
              :label="label"
              :value="value"
            />
          </el-select>
        </el-form-item>

        <!-- 根据选择的提供商显示配置说明 -->
        <el-alert
          v-if="currentConfigGuide"
          :title="`${providers[form.provider]} 配置说明`"
          type="info"
          :closable="false"
          style="margin-bottom: 20px"
        >
          <template v-if="form.provider === 'wenxin'">
            <p>• 百度文心一言需要API Key和Secret Key</p>
            <p>• API Key作为Client ID使用</p>
          </template>
          <template v-else-if="form.provider === 'doubao'">
            <p>• 豆包使用火山引擎API</p>
            <p>• 需要配置Endpoint ID</p>
          </template>
          <template v-else-if="form.provider === 'custom'">
            <p>• 自定义服务需要兼容OpenAI API格式</p>
            <p>• 必须填写API端点地址</p>
          </template>
          <template v-else>
            <p>• 请在对应平台申请API密钥</p>
            <p>• API端点可选，留空使用默认地址</p>
          </template>
        </el-alert>

        <!-- API密钥 -->
        <el-form-item
          :label="currentConfigGuide?.api_key?.label || 'API密钥'"
          prop="api_key"
        >
          <el-input
            v-model="form.api_key"
            type="password"
            show-password
            :placeholder="currentConfigGuide?.api_key?.placeholder || '请输入API密钥'"
          />
        </el-form-item>

        <!-- API端点 -->
        <el-form-item
          :label="currentConfigGuide?.api_endpoint?.label || 'API端点'"
          :required="currentConfigGuide?.api_endpoint?.required"
        >
          <el-input
            v-model="form.api_endpoint"
            :placeholder="currentConfigGuide?.api_endpoint?.placeholder || '留空使用默认端点'"
          />
        </el-form-item>

        <!-- 额外字段（如百度的Secret Key） -->
        <template v-if="currentConfigGuide?.extra_fields">
          <el-form-item
            v-for="(field, key) in currentConfigGuide.extra_fields"
            :key="key"
            :label="field.label"
            :prop="`settings.${key}`"
            :required="field.required"
          >
            <el-input
              v-model="form.settings[key]"
              :type="field.type || 'text'"
              :show-password="field.type === 'password'"
              :placeholder="field.placeholder"
            />
          </el-form-item>
        </template>

        <!-- 模型选择 -->
        <el-form-item label="模型" prop="model">
          <el-select
            v-model="form.model"
            placeholder="请选择或输入模型名称"
            style="width: 100%"
            :disabled="!form.provider"
            filterable
            allow-create
            default-first-option
          >
            <el-option
              v-for="model in availableModels"
              :key="model.value"
              :label="model.label"
              :value="model.value"
            >
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>{{ model.label }}</span>
                <span style="font-size: 12px; color: #999; margin-left: 10px;">{{ model.description }}</span>
              </div>
            </el-option>
          </el-select>
          <span class="form-tip" v-if="!form.provider">请先选择AI提供商</span>
          <span class="form-tip" v-else-if="form.provider === 'custom'">
            自定义服务请手动输入模型名称（如：gpt-3.5-turbo）
          </span>
          <span class="form-tip" v-else>
            可从列表选择，或输入其他模型名称
          </span>
        </el-form-item>

        <el-form-item label="最大Tokens">
          <el-input-number v-model="form.max_tokens" :min="100" :max="200000" :step="100" style="width: 100%" />
          <span class="form-tip">不同模型支持的最大token数不同</span>
        </el-form-item>

        <el-form-item label="温度参数">
          <el-slider v-model="form.temperature" :min="0" :max="2" :step="0.1" show-input />
          <span class="form-tip">温度越低输出越稳定，越高越有创造性（建议0.7-1.0）</span>
        </el-form-item>

        <el-form-item label="设为默认">
          <el-switch v-model="form.is_default" :active-value="1" :inactive-value="0" />
          <span class="form-tip">默认配置将在AI文章生成时优先使用</span>
        </el-form-item>

        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :label="1">启用</el-radio>
            <el-radio :label="0">禁用</el-radio>
          </el-radio-group>
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
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Check } from '@element-plus/icons-vue'
import {
  getAiProviders,
  getAiConfigList,
  createAiConfig,
  updateAiConfig,
  deleteAiConfig,
  testAiConfig,
  setDefaultAiConfig,
  getProviderModels,
  getProviderConfigGuide
} from '@/api/ai'

const loading = ref(false)
const saving = ref(false)
const dialogVisible = ref(false)
const isEdit = ref(false)
const formRef = ref(null)
const testingId = ref(null)

const configList = ref([])
const providers = ref({})
const providerModels = ref({})
const providerConfigGuides = ref({})

const searchForm = reactive({
  provider: '',
  status: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

const form = reactive({
  id: null,
  name: '',
  provider: '',
  api_key: '',
  api_endpoint: '',
  model: '',
  max_tokens: 2000,
  temperature: 0.7,
  is_default: 0,
  status: 1,
  settings: {}
})

const rules = {
  name: [{ required: true, message: '请输入配置名称', trigger: 'blur' }],
  provider: [{ required: true, message: '请选择AI提供商', trigger: 'change' }],
  api_key: [{ required: true, message: '请输入API密钥', trigger: 'blur' }],
  model: [{ required: true, message: '请选择模型', trigger: 'change' }]
}

// 当前提供商的可用模型
const availableModels = computed(() => {
  if (!form.provider) return []
  return providerModels.value[form.provider] || []
})

// 当前提供商的配置指南
const currentConfigGuide = computed(() => {
  if (!form.provider) return null
  return providerConfigGuides.value[form.provider] || null
})

// 获取AI提供商列表
const fetchProviders = async () => {
  try {
    const res = await getAiProviders()
    providers.value = res.data
  } catch (error) {
    console.error('获取AI提供商失败:', error)
  }
}

// 获取所有提供商的模型列表
const fetchAllProviderModels = async () => {
  try {
    const res = await getProviderModels()
    providerModels.value = res.data
  } catch (error) {
    console.error('获取模型列表失败:', error)
  }
}

// 获取所有提供商的配置指南
const fetchAllProviderConfigGuides = async () => {
  try {
    const res = await getProviderConfigGuide()
    providerConfigGuides.value = res.data
  } catch (error) {
    console.error('获取配置指南失败:', error)
  }
}

// 获取配置列表
const fetchConfigs = async () => {
  loading.value = true
  try {
    const res = await getAiConfigList({
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    })
    configList.value = res.data.list
    pagination.total = res.data.total
  } catch (error) {
    ElMessage.error('获取配置列表失败')
  } finally {
    loading.value = false
  }
}

// 重置搜索
const handleReset = () => {
  searchForm.provider = ''
  searchForm.status = ''
  pagination.page = 1
  fetchConfigs()
}

// 提供商变化时的处理
const handleProviderChange = () => {
  // 清空模型选择
  form.model = ''
  // 清空额外设置
  form.settings = {}
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

  // 深拷贝 row 数据，避免响应式引用共享导致数据污染
  const rowCopy = JSON.parse(JSON.stringify(row))

  Object.keys(form).forEach(key => {
    if (rowCopy[key] !== undefined) {
      form[key] = rowCopy[key]
    } else if (key === 'settings') {
      form[key] = {}
    }
  })
}

// 重置表单
const resetForm = () => {
  form.id = null
  form.name = ''
  form.provider = ''
  form.api_key = ''
  form.api_endpoint = ''
  form.model = ''
  form.max_tokens = 2000
  form.temperature = 0.7
  form.is_default = 0
  form.status = 1
  form.settings = {}
  formRef.value?.clearValidate()
}

// 提交
const handleSubmit = async () => {
  await formRef.value.validate()

  saving.value = true
  try {
    if (isEdit.value) {
      await updateAiConfig(form.id, form)
      ElMessage.success('更新成功')
    } else {
      await createAiConfig(form)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    fetchConfigs()
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  } finally {
    saving.value = false
  }
}

// 测试连接
const handleTest = async (id) => {
  testingId.value = id
  try {
    const res = await testAiConfig(id)
    ElMessage.success(res.message || 'AI连接测试成功')
  } catch (error) {
    ElMessage.error(error.message || 'AI连接测试失败')
  } finally {
    testingId.value = null
  }
}

// 设为默认
const handleSetDefault = async (id) => {
  try {
    await setDefaultAiConfig(id)
    ElMessage.success('设置成功')
    fetchConfigs()
  } catch (error) {
    ElMessage.error('设置失败')
  }
}

// 删除
const handleDelete = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除这个配置吗？删除后无法恢复。', '提示', {
      type: 'warning',
      confirmButtonText: '确定删除',
      cancelButtonText: '取消'
    })

    await deleteAiConfig(id)
    ElMessage.success('删除成功')
    fetchConfigs()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

onMounted(async () => {
  await fetchProviders()
  await fetchAllProviderModels()
  await fetchAllProviderConfigGuides()
  fetchConfigs()
})
</script>

<style scoped>
.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-form {
  margin-bottom: 20px;
}

.config-params {
  display: flex;
  flex-direction: column;
  font-size: 12px;
}

.config-params span {
  margin: 2px 0;
}

.form-tip {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
  display: block;
}

:deep(.el-alert__description p) {
  margin: 4px 0;
  font-size: 13px;
}
</style>
