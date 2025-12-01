<template>
  <div class="template-type-container">
    <!-- 搜索栏 -->
    <div class="search-bar">
      <el-form :inline="true" :model="searchForm" @submit.native.prevent="handleSearch">
        <el-form-item label="关键词">
          <el-input
            v-model="searchForm.keyword"
            placeholder="搜索类型名称、代码或描述"
            clearable
            @clear="handleSearch"
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="系统内置">
          <el-select v-model="searchForm.is_system" placeholder="全部" clearable @change="handleSearch">
            <el-option label="全部" value="" />
            <el-option label="是" :value="1" />
            <el-option label="否" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="全部" clearable @change="handleSearch">
            <el-option label="全部" value="" />
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>
    </div>

    <!-- 工具栏 -->
    <div class="toolbar">
      <el-button type="primary" icon="el-icon-plus" @click="handleAdd">新增类型</el-button>
      <el-button type="danger" icon="el-icon-delete" @click="handleBatchDelete" :disabled="selectedIds.length === 0">
        批量删除
      </el-button>
      <el-button icon="el-icon-refresh" @click="loadData">刷新</el-button>
    </div>

    <!-- 数据表格 -->
    <el-table
      :data="tableData"
      v-loading="loading"
      @selection-change="handleSelectionChange"
      row-key="id"
      border
      stripe
    >
      <el-table-column type="selection" width="55" />
      <el-table-column prop="sort" label="排序" width="80" sortable>
        <template #default="{ row }">
          <el-input-number
            v-model="row.sort"
            :min="0"
            size="mini"
            @change="handleSortChange(row)"
          />
        </template>
      </el-table-column>
      <el-table-column prop="icon" label="图标" width="60" align="center">
        <template #default="{ row }">
          <i :class="row.icon || 'el-icon-document'" style="font-size: 20px;"></i>
        </template>
      </el-table-column>
      <el-table-column prop="name" label="类型名称" min-width="120" />
      <el-table-column prop="code" label="类型代码" width="120">
        <template #default="{ row }">
          <el-tag type="info">{{ row.code }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
      <el-table-column prop="file_naming" label="文件命名" width="150" show-overflow-tooltip />
      <el-table-column prop="template_count" label="模板数" width="80" align="center">
        <template #default="{ row }">
          <el-badge :value="row.template_count" class="item" type="primary">
            <span style="padding: 0 10px;">{{ row.template_count }}</span>
          </el-badge>
        </template>
      </el-table-column>
      <el-table-column prop="allow_multiple" label="允许多个" width="90" align="center">
        <template #default="{ row }">
          <el-tag :type="row.allow_multiple ? 'success' : 'info'" size="small">
            {{ row.allow_multiple ? '是' : '否' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="is_system" label="系统内置" width="90" align="center">
        <template #default="{ row }">
          <el-tag :type="row.is_system ? 'warning' : 'info'" size="small">
            {{ row.is_system ? '是' : '否' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="status" label="状态" width="80" align="center">
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
          <el-button type="text" size="small" @click="handleView(row)">查看</el-button>
          <el-button type="text" size="small" @click="handleEdit(row)">编辑</el-button>
          <el-button
            type="text"
            size="small"
            style="color: #f56c6c"
            @click="handleDelete(row)"
            :disabled="!row.can_delete"
          >
            删除
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- 分页 -->
    <div class="pagination">
      <el-pagination
        background
        layout="total, sizes, prev, pager, next, jumper"
        :current-page="currentPage"
        :page-sizes="[10, 20, 50, 100]"
        :page-size="pageSize"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    </div>

    <!-- 新增/编辑对话框 -->
    <el-dialog
      :title="dialogTitle"
      v-model="dialogVisible"
      width="70%"
      :close-on-click-modal="false"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="120px"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="类型名称" prop="name">
              <el-input v-model="formData.name" placeholder="请输入类型名称" />
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="类型代码" prop="code">
              <el-input
                v-model="formData.code"
                placeholder="请输入类型代码"
                :disabled="isEdit && formData.is_system"
              />
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="类型描述">
          <el-input
            v-model="formData.description"
            type="textarea"
            :rows="2"
            placeholder="请输入类型描述"
          />
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="图标">
              <el-input v-model="formData.icon" placeholder="如：el-icon-document">
                <template #prepend>
                  <i :class="formData.icon || 'el-icon-document'"></i>
                </template>
              </el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="文件命名规则" prop="file_naming">
              <el-input v-model="formData.file_naming" placeholder="如：article.html 或 article_*.html" />
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="允许多个模板">
              <el-switch v-model="formData.allow_multiple" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="排序">
              <el-input-number v-model="formData.sort" :min="0" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="状态">
              <el-switch v-model="formData.status" :active-value="1" :inactive-value="0" />
            </el-form-item>
          </el-col>
        </el-row>

        <!-- 参数设置 -->
        <el-form-item label="支持的参数">
          <div class="param-list">
            <div v-for="(param, index) in formData.params" :key="index" class="param-item">
              <el-input v-model="param.name" placeholder="参数名" style="width: 150px;" />
              <el-input v-model="param.description" placeholder="参数说明" style="width: 250px; margin-left: 10px;" />
              <el-button type="danger" icon="el-icon-delete" size="small" circle @click="removeParam(index)" />
            </div>
            <el-button type="primary" size="small" @click="addParam">添加参数</el-button>
          </div>
        </el-form-item>

        <!-- 模板变量设置 -->
        <el-form-item label="模板变量">
          <div class="var-list">
            <div v-for="(varItem, index) in formData.template_vars" :key="index" class="var-item">
              <el-input v-model="varItem.name" placeholder="变量名" style="width: 150px;" />
              <el-input v-model="varItem.description" placeholder="变量说明" style="width: 250px; margin-left: 10px;" />
              <el-button type="danger" icon="el-icon-delete" size="small" circle @click="removeVar(index)" />
            </div>
            <el-button type="primary" size="small" @click="addVar">添加变量</el-button>
          </div>
        </el-form-item>

        <!-- 示例代码 -->
        <el-form-item label="示例代码">
          <el-input
            v-model="formData.example_code"
            type="textarea"
            :rows="6"
            placeholder="请输入模板示例代码"
            style="font-family: monospace;"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit">确定</el-button>
      </template>
    </el-dialog>

    <!-- 查看详情对话框 -->
    <el-dialog
      title="模板类型详情"
      v-model="detailVisible"
      width="60%"
    >
      <el-descriptions :column="2" border>
        <el-descriptions-item label="类型名称">{{ detailData.name }}</el-descriptions-item>
        <el-descriptions-item label="类型代码">
          <el-tag type="info">{{ detailData.code }}</el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="类型描述" :span="2">{{ detailData.description }}</el-descriptions-item>
        <el-descriptions-item label="图标">
          <i :class="detailData.icon || 'el-icon-document'" style="font-size: 20px;"></i>
          {{ detailData.icon }}
        </el-descriptions-item>
        <el-descriptions-item label="文件命名规则">{{ detailData.file_naming }}</el-descriptions-item>
        <el-descriptions-item label="命名示例" :span="2">
          <el-tag v-for="example in detailData.naming_examples" :key="example" style="margin-right: 5px;">
            {{ example }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="允许多个模板">
          <el-tag :type="detailData.allow_multiple ? 'success' : 'info'">
            {{ detailData.allow_multiple ? '是' : '否' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="系统内置">
          <el-tag :type="detailData.is_system ? 'warning' : 'info'">
            {{ detailData.is_system ? '是' : '否' }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="模板数量">
          <el-badge :value="detailData.template_count" type="primary">
            <span style="padding: 0 10px;">{{ detailData.template_count }}</span>
          </el-badge>
        </el-descriptions-item>
        <el-descriptions-item label="状态">
          <el-tag :type="detailData.status ? 'success' : 'danger'">
            {{ detailData.status ? '启用' : '禁用' }}
          </el-tag>
        </el-descriptions-item>
      </el-descriptions>

      <!-- 参数列表 -->
      <div v-if="detailData.formatted_params && detailData.formatted_params.length > 0" style="margin-top: 20px;">
        <h4>支持的参数</h4>
        <el-table :data="detailData.formatted_params" border>
          <el-table-column prop="name" label="参数名" width="150" />
          <el-table-column prop="description" label="说明" />
          <el-table-column prop="example" label="示例值" width="150" />
        </el-table>
      </div>

      <!-- 模板变量列表 -->
      <div v-if="detailData.formatted_vars && detailData.formatted_vars.length > 0" style="margin-top: 20px;">
        <h4>模板变量</h4>
        <el-table :data="detailData.formatted_vars" border>
          <el-table-column prop="name" label="变量名" width="150" />
          <el-table-column prop="description" label="说明" />
          <el-table-column prop="type" label="类型" width="100">
            <template #default="{ row }">
              <el-tag size="small">{{ row.type }}</el-tag>
            </template>
          </el-table-column>
        </el-table>
      </div>

      <!-- 示例代码 -->
      <div v-if="detailData.example_code" style="margin-top: 20px;">
        <h4>示例代码</h4>
        <el-input
          :value="detailData.example_code"
          type="textarea"
          :rows="10"
          readonly
          style="font-family: monospace;"
        />
      </div>

      <template #footer>
        <el-button @click="detailVisible = false">关闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

// 搜索表单
const searchForm = reactive({
  keyword: '',
  is_system: '',
  status: ''
})

// 表格数据
const tableData = ref([])
const loading = ref(false)
const selectedIds = ref([])
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

// 对话框
const dialogVisible = ref(false)
const dialogTitle = ref('')
const isEdit = ref(false)
const formRef = ref(null)
const formData = reactive({
  name: '',
  code: '',
  description: '',
  icon: '',
  file_naming: '',
  params: [],
  template_vars: [],
  example_code: '',
  allow_multiple: false,
  is_system: false,
  sort: 0,
  status: 1
})

// 表单验证规则
const formRules = {
  name: [
    { required: true, message: '请输入类型名称', trigger: 'blur' },
    { min: 1, max: 50, message: '长度在 1 到 50 个字符', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入类型代码', trigger: 'blur' },
    { min: 1, max: 30, message: '长度在 1 到 30 个字符', trigger: 'blur' },
    { pattern: /^[a-z][a-z0-9_]*$/, message: '只能包含小写字母、数字和下划线，且以字母开头', trigger: 'blur' }
  ],
  file_naming: [
    { required: true, message: '请输入文件命名规则', trigger: 'blur' },
    { min: 1, max: 100, message: '长度在 1 到 100 个字符', trigger: 'blur' }
  ]
}

// 详情对话框
const detailVisible = ref(false)
const detailData = ref({})

// 修改的排序数据
const sortChanges = ref({})

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    const res = await request({
      url: '/template-type/index',
      method: 'get',
      params: {
        ...searchForm,
        page: currentPage.value,
        page_size: pageSize.value
      }
    })

    if (res.code === 200) {
      // 确保数值类型字段正确转换
      tableData.value = res.data.list.map(item => ({
        ...item,
        status: Number(item.status),
        is_system: Number(item.is_system),
        allow_multiple: Number(item.allow_multiple),
        sort: Number(item.sort),
        template_count: Number(item.template_count || 0)
      }))
      total.value = res.data.total
    }
  } catch (error) {
    console.error(error)
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

// 重置搜索
const handleReset = () => {
  searchForm.keyword = ''
  searchForm.is_system = ''
  searchForm.status = ''
  handleSearch()
}

// 选择变化
const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

// 分页变化
const handleSizeChange = (val) => {
  pageSize.value = val
  loadData()
}

const handleCurrentChange = (val) => {
  currentPage.value = val
  loadData()
}

// 新增
const handleAdd = () => {
  isEdit.value = false
  dialogTitle.value = '新增模板类型'
  resetForm()
  dialogVisible.value = true
}

// 编辑
const handleEdit = (row) => {
  isEdit.value = true
  dialogTitle.value = '编辑模板类型'

  // 处理参数和变量格式
  const params = []
  if (row.params && typeof row.params === 'object') {
    for (const key in row.params) {
      params.push({ name: key, description: row.params[key] })
    }
  }

  const vars = []
  if (row.template_vars && typeof row.template_vars === 'object') {
    for (const key in row.template_vars) {
      vars.push({ name: key, description: row.template_vars[key] })
    }
  }

  Object.assign(formData, {
    id: row.id,
    name: row.name,
    code: row.code,
    description: row.description || '',
    icon: row.icon || '',
    file_naming: row.file_naming,
    params: params.length > 0 ? params : [],
    template_vars: vars.length > 0 ? vars : [],
    example_code: row.example_code || '',
    allow_multiple: row.allow_multiple,
    is_system: row.is_system,
    sort: row.sort,
    status: row.status
  })

  dialogVisible.value = true
}

// 查看详情
const handleView = async (row) => {
  try {
    const res = await request({
      url: `/template-type/detail/${row.id}`,
      method: 'get'
    })

    if (res.code === 200) {
      detailData.value = res.data
      detailVisible.value = true
    }
  } catch (error) {
    console.error(error)
    ElMessage.error('获取详情失败')
  }
}

// 删除
const handleDelete = (row) => {
  if (!row.can_delete) {
    if (row.is_system) {
      ElMessage.warning('系统内置类型不能删除')
    } else {
      ElMessage.warning(`该类型下有 ${row.template_count} 个模板在使用，不能删除`)
    }
    return
  }

  ElMessageBox.confirm(
    `确定要删除模板类型"${row.name}"吗？`,
    '提示',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      const res = await request({
        url: `/template-type/delete/${row.id}`,
        method: 'delete'
      })

      if (res.code === 200) {
        ElMessage.success('删除成功')
        loadData()
      } else {
        ElMessage.error(res.msg || '删除失败')
      }
    } catch (error) {
      console.error(error)
      ElMessage.error('删除失败')
    }
  }).catch(() => {})
}

// 批量删除
const handleBatchDelete = () => {
  if (selectedIds.value.length === 0) {
    ElMessage.warning('请选择要删除的项')
    return
  }

  ElMessageBox.confirm(
    `确定要删除选中的 ${selectedIds.value.length} 个模板类型吗？`,
    '提示',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      const res = await request({
        url: '/template-type/batch-delete',
        method: 'post',
        data: { ids: selectedIds.value }
      })

      if (res.code === 200) {
        ElMessage.success(res.msg || '批量删除成功')
        loadData()
      } else {
        ElMessage.error(res.msg || '批量删除失败')
      }
    } catch (error) {
      console.error(error)
      ElMessage.error('批量删除失败')
    }
  }).catch(() => {})
}

// 状态切换
const handleStatusChange = async (row) => {
  try {
    const res = await request({
      url: `/template-type/toggle-status/${row.id}`,
      method: 'post'
    })

    if (res.code === 200) {
      ElMessage.success(res.msg || '操作成功')
    } else {
      ElMessage.error(res.msg || '操作失败')
      row.status = row.status === 1 ? 0 : 1  // 恢复原状态
    }
  } catch (error) {
    console.error(error)
    ElMessage.error('操作失败')
    row.status = row.status === 1 ? 0 : 1  // 恢复原状态
  }
}

// 排序变化
const handleSortChange = (row) => {
  sortChanges.value[row.id] = row.sort
  // 延迟提交排序更新
  setTimeout(() => {
    if (sortChanges.value[row.id] === row.sort) {
      updateSort()
    }
  }, 1000)
}

// 更新排序
const updateSort = async () => {
  const items = Object.keys(sortChanges.value).map(id => ({
    id: parseInt(id),
    sort: sortChanges.value[id]
  }))

  if (items.length === 0) return

  try {
    const res = await request({
      url: '/template-type/update-sort',
      method: 'post',
      data: { items }
    })

    if (res.code === 200) {
      ElMessage.success('排序更新成功')
      sortChanges.value = {}
    } else {
      ElMessage.error(res.msg || '排序更新失败')
    }
  } catch (error) {
    console.error(error)
    ElMessage.error('排序更新失败')
  }
}

// 添加参数
const addParam = () => {
  if (!formData.params) {
    formData.params = []
  }
  formData.params.push({ name: '', description: '' })
}

// 删除参数
const removeParam = (index) => {
  formData.params.splice(index, 1)
}

// 添加变量
const addVar = () => {
  if (!formData.template_vars) {
    formData.template_vars = []
  }
  formData.template_vars.push({ name: '', description: '' })
}

// 删除变量
const removeVar = (index) => {
  formData.template_vars.splice(index, 1)
}

// 重置表单
const resetForm = () => {
  Object.assign(formData, {
    name: '',
    code: '',
    description: '',
    icon: '',
    file_naming: '',
    params: [],
    template_vars: [],
    example_code: '',
    allow_multiple: false,
    is_system: false,
    sort: 0,
    status: 1
  })
}

// 提交表单
const handleSubmit = async () => {
  formRef.value.validate(async (valid) => {
    if (!valid) return

    try {
      const url = isEdit.value ? `/template-type/update/${formData.id}` : '/template-type/save'
      const method = isEdit.value ? 'put' : 'post'

      const res = await request({
        url,
        method,
        data: formData
      })

      if (res.code === 200) {
        ElMessage.success(isEdit.value ? '编辑成功' : '新增成功')
        dialogVisible.value = false
        loadData()
      } else {
        ElMessage.error(res.msg || '操作失败')
      }
    } catch (error) {
      console.error(error)
      ElMessage.error('操作失败')
    }
  })
}

// 页面加载
onMounted(() => {
  loadData()
})
</script>

<style lang="scss" scoped>
.template-type-container {
  padding: 20px;

  .search-bar {
    background: #fff;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 4px;
  }

  .toolbar {
    background: #fff;
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 4px;
    display: flex;
    gap: 10px;
  }

  .pagination {
    background: #fff;
    padding: 20px;
    margin-top: 20px;
    border-radius: 4px;
    display: flex;
    justify-content: flex-end;
  }

  .param-list,
  .var-list {
    width: 100%;

    .param-item,
    .var-item {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      gap: 10px;
    }
  }
}
</style>