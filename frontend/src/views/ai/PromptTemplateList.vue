<template>
  <div class="prompt-template-list">
    <el-card>
      <template #header>
        <div class="header-actions">
          <h3>提示词模板管理</h3>
          <el-button type="primary" @click="handleAdd">
            <el-icon><plus /></el-icon>
            创建模板
          </el-button>
        </div>
      </template>

      <!-- 搜索过滤 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="分类">
          <el-select v-model="searchForm.category" placeholder="全部" clearable style="width: 150px">
            <el-option
              v-for="(label, value) in categories"
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
        <el-form-item label="关键词">
          <el-input v-model="searchForm.keyword" placeholder="搜索名称或描述" clearable style="width: 200px" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="fetchTemplates">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <el-table :data="templateList" v-loading="loading" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="模板名称" min-width="150" />
        <el-table-column label="分类" width="100">
          <template #default="{ row }">
            <el-tag>{{ categories[row.category] || row.category }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="提示词内容" min-width="250">
          <template #default="{ row }">
            <el-tooltip :content="row.prompt" placement="top">
              <div class="prompt-text">{{ row.prompt }}</div>
            </el-tooltip>
          </template>
        </el-table-column>
        <el-table-column prop="description" label="描述" min-width="150" show-overflow-tooltip />
        <el-table-column label="类型" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.is_system" type="info">系统</el-tag>
            <el-tag v-else type="success">自定义</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="usage_count" label="使用次数" width="100" />
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-switch
              v-model="row.status"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button
              size="small"
              type="danger"
              v-if="!row.is_system"
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
        @size-change="fetchTemplates"
        @current-change="fetchTemplates"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑模板' : '创建模板'"
      width="600px"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="模板名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入模板名称" />
        </el-form-item>
        <el-form-item label="分类" prop="category">
          <el-select v-model="form.category" placeholder="请选择分类" style="width: 100%">
            <el-option
              v-for="(label, value) in categories"
              :key="value"
              :label="label"
              :value="value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="提示词内容" prop="prompt">
          <el-input
            v-model="form.prompt"
            type="textarea"
            :rows="8"
            placeholder="请输入提示词模板内容，使用 {变量名} 作为占位符"
            @blur="parseVariablesFromPrompt"
          />
          <span class="form-tip">提示：使用 {topic} 作为主题占位符，使用 {length} {style} 等作为其他参数</span>
        </el-form-item>

        <!-- 变量配置 -->
        <el-form-item label="模板变量">
          <el-button size="small" @click="parseVariablesFromPrompt" style="margin-bottom: 10px;">
            <el-icon><refresh /></el-icon>
            从提示词中解析变量
          </el-button>
          <div v-if="form.variables && form.variables.length > 0">
            <div v-for="(variable, index) in form.variables" :key="index" style="margin-bottom: 15px; padding: 10px; border: 1px solid #dcdfe6; border-radius: 4px;">
              <el-row :gutter="10">
                <el-col :span="6">
                  <el-input v-model="variable.name" placeholder="变量名" size="small" disabled />
                </el-col>
                <el-col :span="6">
                  <el-input v-model="variable.label" placeholder="显示标签" size="small" />
                </el-col>
                <el-col :span="8">
                  <el-input v-model="variable.description" placeholder="描述" size="small" />
                </el-col>
                <el-col :span="4">
                  <el-button size="small" type="danger" @click="removeVariable(index)">删除</el-button>
                </el-col>
              </el-row>
              <el-row :gutter="10" style="margin-top: 8px;">
                <el-col :span="6">
                  <el-select v-model="variable.type" placeholder="类型" size="small" style="width: 100%;">
                    <el-option label="单行文本" value="text" />
                    <el-option label="多行文本" value="textarea" />
                  </el-select>
                </el-col>
                <el-col :span="8">
                  <el-input v-model="variable.default" placeholder="默认值" size="small" />
                </el-col>
                <el-col :span="10">
                  <el-input v-model="variable.example" placeholder="示例" size="small" />
                </el-col>
              </el-row>
            </div>
          </div>
          <div v-else style="color: #909399; font-size: 12px;">
            在提示词中使用 {变量名} 格式，系统会自动解析变量
          </div>
        </el-form-item>

        <el-form-item label="描述">
          <el-input v-model="form.description" type="textarea" :rows="3" placeholder="请输入描述" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.sort_order" :min="0" :max="9999" />
        </el-form-item>
        <el-form-item label="状态">
          <el-switch v-model="form.status" :active-value="1" :inactive-value="0" />
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
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Refresh } from '@element-plus/icons-vue'
import {
  getPromptTemplateList,
  getPromptTemplateCategories,
  createPromptTemplate,
  updatePromptTemplate,
  deletePromptTemplate
} from '@/api/ai'

const loading = ref(false)
const saving = ref(false)
const dialogVisible = ref(false)
const isEdit = ref(false)
const formRef = ref(null)

const templateList = ref([])
const categories = ref({})

const searchForm = reactive({
  category: '',
  status: '',
  keyword: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

const form = reactive({
  id: null,
  name: '',
  category: 'article',
  prompt: '',
  description: '',
  sort_order: 0,
  status: 1,
  is_system: 0,
  variables: []
})

const rules = {
  name: [{ required: true, message: '请输入模板名称', trigger: 'blur' }],
  category: [{ required: true, message: '请选择分类', trigger: 'change' }],
  prompt: [{ required: true, message: '请输入提示词内容', trigger: 'blur' }]
}

// 获取分类列表
const fetchCategories = async () => {
  try {
    const res = await getPromptTemplateCategories()
    categories.value = res.data
  } catch (error) {
    console.error('获取分类失败:', error)
  }
}

// 获取模板列表
const fetchTemplates = async () => {
  loading.value = true
  try {
    const res = await getPromptTemplateList({
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    })
    templateList.value = res.data.list
    pagination.total = res.data.total
  } catch (error) {
    console.error('获取模板列表失败:', error)
  } finally {
    loading.value = false
  }
}

// 重置搜索
const handleReset = () => {
  searchForm.category = ''
  searchForm.status = ''
  searchForm.keyword = ''
  pagination.page = 1
  fetchTemplates()
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

  // 深拷贝
  const rowCopy = JSON.parse(JSON.stringify(row))
  Object.keys(form).forEach(key => {
    if (rowCopy[key] !== undefined) {
      form[key] = rowCopy[key]
    }
  })
}

// 重置表单
const resetForm = () => {
  form.id = null
  form.name = ''
  form.category = 'article'
  form.prompt = ''
  form.description = ''
  form.sort_order = 0
  form.status = 1
  form.is_system = 0
  form.variables = []
  formRef.value?.clearValidate()
}

// 提交
const handleSubmit = async () => {
  await formRef.value.validate()

  saving.value = true
  try {
    if (isEdit.value) {
      await updatePromptTemplate(form.id, form)
      ElMessage.success('更新成功')
    } else {
      await createPromptTemplate(form)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    fetchTemplates()
  } catch (error) {
    console.error('提交失败:', error)
  } finally {
    saving.value = false
  }
}

// 状态切换
const handleStatusChange = async (row) => {
  try {
    await updatePromptTemplate(row.id, { status: row.status })
    ElMessage.success('状态更新成功')
  } catch (error) {
    row.status = row.status === 1 ? 0 : 1
    console.error('状态更新失败:', error)
  }
}

// 删除
const handleDelete = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除这个模板吗？', '提示', {
      type: 'warning'
    })

    await deletePromptTemplate(id)
    ElMessage.success('删除成功')
    fetchTemplates()
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
    }
  }
}

// 从提示词中解析变量
const parseVariablesFromPrompt = () => {
  if (!form.prompt) {
    form.variables = []
    return
  }

  // 使用正则表达式匹配 {变量名} 格式
  const regex = /\{([a-zA-Z_][a-zA-Z0-9_]*)\}/g
  const matches = form.prompt.matchAll(regex)
  const variableNames = new Set()

  // 收集所有唯一的变量名
  for (const match of matches) {
    variableNames.add(match[1])
  }

  // 保留已存在变量的配置
  const existingVariables = new Map()
  if (form.variables && Array.isArray(form.variables)) {
    form.variables.forEach(v => {
      existingVariables.set(v.name, v)
    })
  }

  // 创建新的变量数组
  const newVariables = []
  variableNames.forEach(name => {
    if (existingVariables.has(name)) {
      // 保留已存在的配置
      newVariables.push(existingVariables.get(name))
    } else {
      // 创建新变量，使用智能默认值
      newVariables.push({
        name: name,
        label: getDefaultLabel(name),
        description: '',
        type: 'text',
        default: '',
        example: ''
      })
    }
  })

  form.variables = newVariables

  if (newVariables.length > 0) {
    ElMessage.success(`已解析出 ${newVariables.length} 个变量`)
  } else {
    ElMessage.info('未检测到变量，请使用 {变量名} 格式')
  }
}

// 根据变量名生成默认标签
const getDefaultLabel = (name) => {
  const labelMap = {
    'topic': '主题',
    'title': '标题',
    'length': '文章长度',
    'style': '写作风格',
    'product_type': '产品类型',
    'key_features': '核心特性',
    'target_audience': '目标读者',
    'tone': '语气',
    'keywords': '关键词',
    'category': '分类',
    'brand': '品牌',
    'price': '价格'
  }
  return labelMap[name] || name
}

// 删除变量
const removeVariable = (index) => {
  form.variables.splice(index, 1)
}

onMounted(() => {
  fetchCategories()
  fetchTemplates()
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

.prompt-text {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.form-tip {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
  display: block;
}
</style>
