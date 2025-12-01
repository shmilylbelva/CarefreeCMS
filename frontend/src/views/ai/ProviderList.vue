<template>
  <div class="provider-list-container">
    <el-card shadow="never">
      <!-- 搜索栏 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="全部" style="width: 120px" clearable>
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="类型">
          <el-select v-model="searchForm.is_custom" placeholder="全部" style="width: 120px" clearable>
            <el-option label="预设厂商" :value="0" />
            <el-option label="自定义厂商" :value="1" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
          <el-button type="success" @click="handleAdd">添加厂商</el-button>
        </el-form-item>
      </el-form>

      <!-- 厂商列表 -->
      <el-table :data="providerList" v-loading="loading" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="厂商名称" width="200">
          <template #default="{ row }">
            <div>
              <div>{{ row.name }}</div>
              <div style="font-size: 12px; color: #999;">{{ row.name_en }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="code" label="厂商代码" width="150" />
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column label="类型" width="100">
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
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button link type="primary" size="small" @click="handleViewModels(row)">模型管理</el-button>
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
        @size-change="fetchProviders"
        @current-change="fetchProviders"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑厂商' : '添加厂商'"
      width="600px"
      @close="resetForm"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="厂商代码" prop="code">
          <el-input
            v-model="form.code"
            placeholder="请输入厂商代码（唯一标识，如：openai）"
            :disabled="isEdit && form.is_builtin"
          />
          <span class="form-tip">英文小写，用于系统内部标识</span>
        </el-form-item>

        <el-form-item label="厂商名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入厂商名称（中文）" />
        </el-form-item>

        <el-form-item label="英文名称" prop="name_en">
          <el-input v-model="form.name_en" placeholder="请输入厂商英文名称" />
        </el-form-item>

        <el-form-item label="描述">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请输入厂商描述"
          />
        </el-form-item>

        <el-form-item label="官网地址">
          <el-input v-model="form.website" placeholder="请输入官网地址" />
        </el-form-item>

        <el-form-item label="API文档">
          <el-input v-model="form.api_doc_url" placeholder="请输入API文档地址" />
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
          <span class="form-tip">自定义厂商可以完全删除</span>
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
import {
  getAiProviderList,
  createAiProvider,
  updateAiProvider,
  deleteAiProvider
} from '@/api/ai'
import { useRouter } from 'vue-router'

const router = useRouter()

// 数据
const loading = ref(false)
const providerList = ref([])

const searchForm = reactive({
  status: '',
  is_custom: ''
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
  code: '',
  name: '',
  name_en: '',
  description: '',
  website: '',
  api_doc_url: '',
  is_custom: 0,
  status: 1,
  sort_order: 0
})

const rules = {
  code: [
    { required: true, message: '请输入厂商代码', trigger: 'blur' },
    { pattern: /^[a-z0-9_-]+$/, message: '只能包含小写字母、数字、下划线和短横线', trigger: 'blur' }
  ],
  name: [{ required: true, message: '请输入厂商名称', trigger: 'blur' }],
  sort_order: [{ required: true, message: '请输入排序', trigger: 'blur' }]
}

// 获取厂商列表
const fetchProviders = async () => {
  loading.value = true
  try {
    const res = await getAiProviderList({
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    })
    providerList.value = res.data.list
    pagination.total = res.data.total
  } catch (error) {
    ElMessage.error('获取厂商列表失败')
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  fetchProviders()
}

// 重置
const handleReset = () => {
  searchForm.status = ''
  searchForm.is_custom = ''
  pagination.page = 1
  fetchProviders()
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
}

// 重置表单
const resetForm = () => {
  form.id = null
  form.code = ''
  form.name = ''
  form.name_en = ''
  form.description = ''
  form.website = ''
  form.api_doc_url = ''
  form.is_custom = 0
  form.status = 1
  form.sort_order = 0
  formRef.value?.clearValidate()
}

// 提交
const handleSubmit = async () => {
  await formRef.value.validate()

  saving.value = true
  try {
    if (isEdit.value) {
      await updateAiProvider(form.id, form)
      ElMessage.success('更新成功')
    } else {
      await createAiProvider(form)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    fetchProviders()
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  } finally {
    saving.value = false
  }
}

// 状态切换
const handleStatusChange = async (row) => {
  try {
    await updateAiProvider(row.id, { status: row.status })
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
    await ElMessageBox.confirm(`确定要删除厂商"${row.name}"吗？删除后无法恢复。`, '提示', {
      type: 'warning',
      confirmButtonText: '确定删除',
      cancelButtonText: '取消'
    })

    await deleteAiProvider(row.id)
    ElMessage.success('删除成功')
    fetchProviders()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

// 查看模型
const handleViewModels = (row) => {
  router.push({
    name: 'AiModelList',
    query: { provider_id: row.id }
  })
}

onMounted(() => {
  fetchProviders()
})
</script>

<style scoped>
.provider-list-container {
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
</style>
