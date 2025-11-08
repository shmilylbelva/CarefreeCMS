<template>
  <div class="redirect-container">
    <div class="toolbar">
      <el-form :inline="true" :model="query">
        <el-form-item label="匹配类型">
          <el-select v-model="query.match_type" placeholder="全部" clearable style="width: 150px">
            <el-option v-for="(label, value) in matchTypes" :key="value" :label="label" :value="value" />
          </el-select>
        </el-form-item>
        <el-form-item label="重定向类型">
          <el-select v-model="query.redirect_type" placeholder="全部" clearable style="width: 150px">
            <el-option v-for="(label, value) in redirectTypes" :key="value" :label="label" :value="value" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="query.is_enabled" placeholder="全部" clearable style="width: 120px">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="query.keyword" placeholder="URL/描述" clearable style="width: 200px" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadList">查询</el-button>
          <el-button @click="resetQuery">重置</el-button>
        </el-form-item>
      </el-form>
      <div>
        <el-button type="primary" @click="handleAdd">添加规则</el-button>
        <el-button @click="handleTest">测试URL</el-button>
        <el-button @click="showStatistics">查看统计</el-button>
      </div>
    </div>

    <el-table :data="list" border stripe v-loading="loading" @selection-change="handleSelectionChange">
      <el-table-column type="selection" width="55" />
      <el-table-column prop="id" label="ID" width="70" />
      <el-table-column prop="from_url" label="源URL" min-width="200" show-overflow-tooltip />
      <el-table-column prop="to_url" label="目标URL" min-width="200" show-overflow-tooltip />
      <el-table-column label="类型" width="100">
        <template #default="{ row }">
          <el-tag :type="row.redirect_type === 301 ? 'success' : 'warning'" size="small">
            {{ row.redirect_type }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="匹配方式" width="120">
        <template #default="{ row }">
          {{ matchTypes[row.match_type] }}
        </template>
      </el-table-column>
      <el-table-column prop="hit_count" label="命中次数" width="100" sortable />
      <el-table-column label="状态" width="80">
        <template #default="{ row }">
          <el-tag :type="row.is_enabled ? 'success' : 'info'" size="small">
            {{ row.is_enabled ? '启用' : '禁用' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="180" fixed="right">
        <template #default="{ row }">
          <el-button link type="primary" size="small" @click="handleEdit(row)">编辑</el-button>
          <el-button link :type="row.is_enabled ? 'warning' : 'success'" size="small"
                     @click="handleToggle(row)">
            {{ row.is_enabled ? '禁用' : '启用' }}
          </el-button>
          <el-button link type="danger" size="small" @click="handleDelete(row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <div style="margin-top: 10px">
      <el-button @click="handleBatchDelete" :disabled="selectedIds.length === 0">批量删除</el-button>
      <el-button @click="handleBatchToggle(1)" :disabled="selectedIds.length === 0">批量启用</el-button>
      <el-button @click="handleBatchToggle(0)" :disabled="selectedIds.length === 0">批量禁用</el-button>
    </div>

    <el-pagination
      v-model:current-page="query.page"
      v-model:page-size="query.per_page"
      :total="total"
      :page-sizes="[10, 15, 20, 50]"
      layout="total, sizes, prev, pager, next, jumper"
      @current-change="loadList"
      @size-change="loadList"
    />

    <!-- 表单对话框 -->
    <el-dialog v-model="dialogVisible" :title="formMode === 'add' ? '添加规则' : '编辑规则'" width="650px">
      <el-form :model="form" :rules="rules" ref="formRef" label-width="100px">
        <el-form-item label="源URL" prop="from_url">
          <el-input v-model="form.from_url" placeholder="如：/old-page 或 /blog/*" />
          <div class="form-tip">精确匹配填完整路径，通配符用*，正则填表达式</div>
        </el-form-item>

        <el-form-item label="目标URL" prop="to_url">
          <el-input v-model="form.to_url" placeholder="如：/new-page 或 /articles/*" />
        </el-form-item>

        <el-form-item label="匹配类型" prop="match_type">
          <el-radio-group v-model="form.match_type">
            <el-radio label="exact">精确匹配</el-radio>
            <el-radio label="wildcard">通配符</el-radio>
            <el-radio label="regex">正则表达式</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="重定向类型" prop="redirect_type">
          <el-radio-group v-model="form.redirect_type">
            <el-radio :label="301">301 永久重定向</el-radio>
            <el-radio :label="302">302 临时重定向</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="状态">
          <el-switch v-model="form.is_enabled" :active-value="1" :inactive-value="0" />
        </el-form-item>

        <el-form-item label="描述">
          <el-input v-model="form.description" type="textarea" :rows="3" placeholder="规则说明" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="submitForm" :loading="submitting">保存</el-button>
      </template>
    </el-dialog>

    <!-- 测试对话框 -->
    <el-dialog v-model="testDialogVisible" title="测试重定向规则" width="600px">
      <el-input v-model="testUrl" placeholder="输入要测试的URL，如：/old-page" />
      <div v-if="testResult" style="margin-top: 20px">
        <el-alert v-if="testResult.matched" type="success" :closable="false">
          <div><strong>匹配成功！</strong></div>
          <div style="margin-top: 10px">
            <div>规则ID: {{ testResult.rule.id }}</div>
            <div>源URL: {{ testResult.rule.from_url }}</div>
            <div>目标URL: {{ testResult.target_url }}</div>
            <div>类型: {{ testResult.rule.redirect_type }}</div>
          </div>
        </el-alert>
        <el-alert v-else type="info" :closable="false">
          未找到匹配的重定向规则
        </el-alert>
      </div>
      <template #footer>
        <el-button @click="testDialogVisible = false">关闭</el-button>
        <el-button type="primary" @click="performTest">测试</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getSeoRedirectList,
  getSeoRedirect,
  createSeoRedirect,
  updateSeoRedirect,
  deleteSeoRedirect,
  batchDeleteSeoRedirects,
  batchToggleSeoRedirects,
  testSeoRedirect,
  getSeoRedirectOptions
} from '@/api/seoRedirect'

const loading = ref(false)
const submitting = ref(false)
const list = ref([])
const total = ref(0)
const selectedIds = ref([])

const query = reactive({
  page: 1,
  per_page: 15,
  keyword: '',
  match_type: '',
  redirect_type: '',
  is_enabled: ''
})

const dialogVisible = ref(false)
const formMode = ref('add')
const form = reactive({
  id: null,
  from_url: '',
  to_url: '',
  match_type: 'exact',
  redirect_type: 301,
  is_enabled: 1,
  description: ''
})

const rules = {
  from_url: [{ required: true, message: '请输入源URL', trigger: 'blur' }],
  to_url: [{ required: true, message: '请输入目标URL', trigger: 'blur' }]
}

const formRef = ref(null)

const matchTypes = ref({})
const redirectTypes = ref({})

const testDialogVisible = ref(false)
const testUrl = ref('')
const testResult = ref(null)

const loadList = async () => {
  loading.value = true
  try {
    const res = await getSeoRedirectList(query)
    list.value = res.data.data
    total.value = res.data.total
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.value = false
  }
}

const loadOptions = async () => {
  try {
    const res = await getSeoRedirectOptions()
    matchTypes.value = res.data.match_types
    redirectTypes.value = res.data.redirect_types
  } catch (error) {
    console.error('加载选项失败', error)
  }
}

const resetQuery = () => {
  query.keyword = ''
  query.match_type = ''
  query.redirect_type = ''
  query.is_enabled = ''
  query.page = 1
  loadList()
}

const handleAdd = () => {
  formMode.value = 'add'
  Object.assign(form, {
    id: null,
    from_url: '',
    to_url: '',
    match_type: 'exact',
    redirect_type: 301,
    is_enabled: 1,
    description: ''
  })
  dialogVisible.value = true
}

const handleEdit = async (row) => {
  formMode.value = 'edit'
  try {
    const res = await getSeoRedirect(row.id)
    Object.assign(form, res.data)
    dialogVisible.value = true
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  }
}

const handleDelete = (row) => {
  ElMessageBox.confirm('确定要删除这条规则吗？', '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await deleteSeoRedirect(row.id)
      ElMessage.success('删除成功')
      loadList()
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  })
}

const handleToggle = async (row) => {
  try {
    await updateSeoRedirect(row.id, { is_enabled: row.is_enabled ? 0 : 1 })
    ElMessage.success('操作成功')
    loadList()
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  }
}

const submitForm = () => {
  formRef.value.validate(async (valid) => {
    if (!valid) return

    submitting.value = true
    try {
      if (formMode.value === 'add') {
        await createSeoRedirect(form)
        ElMessage.success('添加成功')
      } else {
        await updateSeoRedirect(form.id, form)
        ElMessage.success('更新成功')
      }
      dialogVisible.value = false
      loadList()
    } catch (error) {
      ElMessage.error(error.message || '操作失败')
    } finally {
      submitting.value = false
    }
  })
}

const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

const handleBatchDelete = () => {
  ElMessageBox.confirm(`确定要删除选中的 ${selectedIds.value.length} 条规则吗？`, '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await batchDeleteSeoRedirects(selectedIds.value)
      ElMessage.success('批量删除成功')
      loadList()
    } catch (error) {
      ElMessage.error(error.message || '批量删除失败')
    }
  })
}

const handleBatchToggle = async (isEnabled) => {
  try {
    await batchToggleSeoRedirects(selectedIds.value, isEnabled)
    ElMessage.success('批量操作成功')
    loadList()
  } catch (error) {
    ElMessage.error(error.message || '批量操作失败')
  }
}

const handleTest = () => {
  testUrl.value = ''
  testResult.value = null
  testDialogVisible.value = true
}

const performTest = async () => {
  if (!testUrl.value) {
    ElMessage.warning('请输入要测试的URL')
    return
  }

  try {
    const res = await testSeoRedirect(testUrl.value)
    testResult.value = res.data
  } catch (error) {
    ElMessage.error(error.message || '测试失败')
  }
}

const showStatistics = () => {
  ElMessage.info('统计功能开发中')
}

onMounted(() => {
  loadList()
  loadOptions()
})
</script>

<style scoped>
.redirect-container {
  padding: 20px;
}

.toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.el-pagination {
  margin-top: 20px;
  justify-content: center;
}

.form-tip {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}
</style>
