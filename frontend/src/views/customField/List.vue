<template>
  <div class="custom-field-list">
    <el-card>
      <template #header>
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <h3>自定义字段</h3>
          <el-button type="primary" @click="handleCreate">
            新建字段
          </el-button>
        </div>
      </template>

      <!-- 搜索栏 -->
      <el-form :inline="true" :model="searchForm" style="margin-bottom: 20px;">
        <el-form-item label="关键词">
          <el-input
            v-model="searchForm.keyword"
            placeholder="请输入字段名称"
            clearable
            @clear="handleSearch"
            @keyup.enter="handleSearch"
            style="width: 200px;"
          />
        </el-form-item>
        <el-form-item label="模型类型">
          <el-select v-model="searchForm.model_type" placeholder="全部类型" clearable @change="handleSearch" style="width: 150px;">
            <el-option
              v-for="item in modelTypes"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="内容模型" v-if="searchForm.model_type === 'custom'">
          <el-select v-model="searchForm.model_id" placeholder="请选择" clearable @change="handleSearch" style="width: 150px;">
            <el-option
              v-for="item in contentModels"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 列表 -->
      <el-table :data="list" v-loading="loading" stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="字段名称" width="150" />
        <el-table-column prop="field_key" label="字段键名" width="150" />
        <el-table-column prop="field_type_text" label="字段类型" width="120" />
        <el-table-column prop="model_type_text" label="模型类型" width="120" />
        <el-table-column prop="group_name" label="字段组" width="120">
          <template #default="scope">
            {{ scope.row.group_name || '-' }}
          </template>
        </el-table-column>
        <el-table-column prop="is_required" label="必填" width="80" align="center">
          <template #default="scope">
            <el-tag :type="scope.row.is_required ? 'danger' : 'info'" size="small">
              {{ scope.row.is_required ? '是' : '否' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="80" align="center">
          <template #default="scope">
            <el-tag :type="scope.row.status === 1 ? 'success' : 'danger'" size="small">
              {{ scope.row.status === 1 ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="sort" label="排序" width="80" align="center" />
        <el-table-column label="操作" width="180" align="center" fixed="right">
          <template #default="scope">
            <el-button size="small" type="primary" @click="handleEdit(scope.row)">
              编辑
            </el-button>
            <el-button size="small" type="danger" @click="handleDelete(scope.row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div style="margin-top: 20px; text-align: center;">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :total="total"
          :page-sizes="[10, 20, 50]"
          layout="total, sizes, prev, pager, next, jumper"
          @current-change="handlePageChange"
          @size-change="handleSizeChange"
        />
      </div>
    </el-card>

    <!-- 编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑字段' : '新建字段'"
      width="700px"
      @close="handleDialogClose"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="120px">
        <el-form-item label="字段名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入字段名称" />
        </el-form-item>
        <el-form-item label="字段键名" prop="field_key">
          <el-input v-model="form.field_key" placeholder="例如：product_price" />
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            只能包含字母、数字和下划线，且不能以数字开头
          </div>
        </el-form-item>
        <el-form-item label="字段类型" prop="field_type">
          <el-select v-model="form.field_type" placeholder="请选择" style="width: 100%;">
            <el-option
              v-for="item in fieldTypes"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="模型类型" prop="model_type">
          <el-select v-model="form.model_type" placeholder="请选择" @change="handleModelTypeChange" style="width: 100%;">
            <el-option
              v-for="item in modelTypes"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="内容模型" prop="model_id" v-if="form.model_type === 'custom'">
          <el-select v-model="form.model_id" placeholder="请选择" style="width: 100%;">
            <el-option
              v-for="item in contentModels"
              :key="item.id"
              :label="item.name"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="字段组" prop="group_name">
          <el-input v-model="form.group_name" placeholder="用于分组显示，例如：基本信息" />
        </el-form-item>

        <!-- 选项配置（select/radio/checkbox类型） -->
        <el-form-item label="字段选项" v-if="['select', 'radio', 'checkbox'].includes(form.field_type)">
          <div style="width: 100%;">
            <div v-for="(option, index) in fieldOptions" :key="index" style="display: flex; margin-bottom: 10px;">
              <el-input v-model="option.value" placeholder="值" style="width: 200px; margin-right: 10px;" />
              <el-input v-model="option.label" placeholder="显示文本" style="width: 200px; margin-right: 10px;" />
              <el-button type="danger" @click="removeOption(index)">删除</el-button>
            </div>
            <el-button @click="addOption" style="width: 100%;">添加选项</el-button>
          </div>
        </el-form-item>

        <el-form-item label="默认值" prop="default_value">
          <el-input v-model="form.default_value" placeholder="字段的默认值" />
        </el-form-item>
        <el-form-item label="占位符" prop="placeholder">
          <el-input v-model="form.placeholder" placeholder="输入框的占位符文本" />
        </el-form-item>
        <el-form-item label="帮助说明" prop="help_text">
          <el-input v-model="form.help_text" type="textarea" :rows="2" placeholder="显示在字段下方的帮助文本" />
        </el-form-item>
        <el-form-item label="是否必填" prop="is_required">
          <el-switch v-model="form.is_required" :active-value="1" :inactive-value="0" />
        </el-form-item>
        <el-form-item label="列表显示" prop="is_show_in_list">
          <el-switch v-model="form.is_show_in_list" :active-value="1" :inactive-value="0" />
        </el-form-item>
        <el-form-item label="可搜索" prop="is_searchable">
          <el-switch v-model="form.is_searchable" :active-value="1" :inactive-value="0" />
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="form.sort" :min="0" :max="999" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="form.status">
            <el-radio :label="1">启用</el-radio>
            <el-radio :label="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">
          确定
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getCustomFieldList,
  getCustomFieldDetail,
  createCustomField,
  updateCustomField,
  deleteCustomField,
  getFieldTypes,
  getModelTypes
} from '@/api/customField'
import { getAllContentModels } from '@/api/contentModel'

const route = useRoute()

const loading = ref(false)
const list = ref([])
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

const fieldTypes = ref([])
const modelTypes = ref([])
const contentModels = ref([])

const searchForm = reactive({
  keyword: '',
  model_type: '',
  model_id: ''
})

const dialogVisible = ref(false)
const isEdit = ref(false)
const submitting = ref(false)
const formRef = ref(null)

const form = reactive({
  name: '',
  field_key: '',
  field_type: 'text',
  model_type: 'article',
  model_id: null,
  group_name: '',
  options: null,
  default_value: '',
  placeholder: '',
  help_text: '',
  is_required: 0,
  is_searchable: 0,
  is_show_in_list: 0,
  sort: 0,
  status: 1
})

const fieldOptions = ref([])

const rules = {
  name: [{ required: true, message: '请输入字段名称', trigger: 'blur' }],
  field_key: [
    { required: true, message: '请输入字段键名', trigger: 'blur' },
    { pattern: /^[a-zA-Z_][a-zA-Z0-9_]*$/, message: '只能包含字母、数字和下划线，且不能以数字开头', trigger: 'blur' }
  ],
  field_type: [{ required: true, message: '请选择字段类型', trigger: 'change' }],
  model_type: [{ required: true, message: '请选择模型类型', trigger: 'change' }]
}

// 加载字段类型
const loadFieldTypes = async () => {
  try {
    const res = await getFieldTypes()
    fieldTypes.value = res.data || []
  } catch (error) {
    console.error('加载字段类型失败', error)
  }
}

// 加载模型类型
const loadModelTypes = async () => {
  try {
    const res = await getModelTypes()
    modelTypes.value = res.data || []
  } catch (error) {
    console.error('加载模型类型失败', error)
  }
}

// 加载内容模型
const loadContentModels = async () => {
  try {
    const res = await getAllContentModels()
    contentModels.value = res.data || []
  } catch (error) {
    console.error('加载内容模型失败', error)
  }
}

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    const res = await getCustomFieldList({
      keyword: searchForm.keyword,
      model_type: searchForm.model_type,
      model_id: searchForm.model_id,
      page: currentPage.value,
      page_size: pageSize.value
    })
    list.value = res.data.list || []
    total.value = res.data.pagination?.total || 0
  } catch (error) {
    ElMessage.error('加载数据失败')
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  currentPage.value = 1
  loadData()
}

// 重置
const handleReset = () => {
  searchForm.keyword = ''
  searchForm.model_type = ''
  searchForm.model_id = ''
  currentPage.value = 1
  loadData()
}

// 新建
const handleCreate = () => {
  isEdit.value = false
  resetForm()
  dialogVisible.value = true
}

// 编辑
const handleEdit = async (row) => {
  isEdit.value = true
  try {
    const res = await getCustomFieldDetail(row.id)
    Object.assign(form, res.data)

    // 解析选项
    if (form.options && typeof form.options === 'object') {
      fieldOptions.value = Object.entries(form.options).map(([value, label]) => ({
        value,
        label
      }))
    } else {
      fieldOptions.value = []
    }

    dialogVisible.value = true
  } catch (error) {
    ElMessage.error('加载字段详情失败')
  }
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm(
    `确定要删除字段「${row.name}」吗？删除后无法恢复，且会删除该字段的所有值！`,
    '确认删除',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      await deleteCustomField(row.id)
      ElMessage.success('删除成功')
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  })
}

// 模型类型改变
const handleModelTypeChange = () => {
  if (form.model_type !== 'custom') {
    form.model_id = null
  }
}

// 添加选项
const addOption = () => {
  fieldOptions.value.push({ value: '', label: '' })
}

// 删除选项
const removeOption = (index) => {
  fieldOptions.value.splice(index, 1)
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true
      try {
        // 处理选项
        if (['select', 'radio', 'checkbox'].includes(form.field_type)) {
          const options = {}
          fieldOptions.value.forEach(opt => {
            if (opt.value && opt.label) {
              options[opt.value] = opt.label
            }
          })
          form.options = options
        } else {
          form.options = null
        }

        if (isEdit.value) {
          await updateCustomField(form.id, form)
          ElMessage.success('更新成功')
        } else {
          await createCustomField(form)
          ElMessage.success('创建成功')
        }
        dialogVisible.value = false
        loadData()
      } catch (error) {
        ElMessage.error(error.message || '操作失败')
      } finally {
        submitting.value = false
      }
    }
  })
}

// 关闭对话框
const handleDialogClose = () => {
  resetForm()
}

// 重置表单
const resetForm = () => {
  form.id = null
  form.name = ''
  form.field_key = ''
  form.field_type = 'text'
  form.model_type = 'article'
  form.model_id = null
  form.group_name = ''
  form.options = null
  form.default_value = ''
  form.placeholder = ''
  form.help_text = ''
  form.is_required = 0
  form.is_searchable = 0
  form.is_show_in_list = 0
  form.sort = 0
  form.status = 1
  fieldOptions.value = []

  if (formRef.value) {
    formRef.value.clearValidate()
  }
}

// 分页
const handlePageChange = (page) => {
  currentPage.value = page
  loadData()
}

const handleSizeChange = (size) => {
  pageSize.value = size
  currentPage.value = 1
  loadData()
}

// 监听路由查询参数
watch(() => route.query, (query) => {
  if (query.model_type) {
    searchForm.model_type = query.model_type
    searchForm.model_id = query.model_id || ''
    loadData()
  }
}, { immediate: true })

onMounted(() => {
  loadFieldTypes()
  loadModelTypes()
  loadContentModels()
  if (!route.query.model_type) {
    loadData()
  }
})
</script>

<style scoped>
.custom-field-list h3 {
  margin: 0;
}
</style>
