<template>
  <div class="sensitive-words-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>敏感词管理</span>
          <div class="header-buttons">
            <el-button type="primary" @click="handleAdd">添加敏感词</el-button>
            <el-button @click="handleBatchImport">批量导入</el-button>
            <el-button type="success" @click="showTestDialog">测试检测</el-button>
            <el-button type="info" @click="loadStatistics">查看统计</el-button>
          </div>
        </div>
      </template>

      <!-- 筛选条件 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="分类">
          <el-select v-model="searchForm.category" clearable placeholder="全部分类" style="width: 150px">
            <el-option label="全部" value="" />
            <el-option v-for="(label, value) in categories" :key="value" :label="label" :value="value" />
          </el-select>
        </el-form-item>
        <el-form-item label="级别">
          <el-select v-model="searchForm.level" clearable placeholder="全部级别" style="width: 150px">
            <el-option label="全部" value="" />
            <el-option v-for="(label, value) in levels" :key="value" :label="label" :value="value" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.is_enabled" clearable placeholder="全部状态" style="width: 120px">
            <el-option label="全部" value="" />
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="searchForm.keyword" placeholder="搜索敏感词" style="width: 200px" clearable />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadList">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 批量操作 -->
      <div class="batch-actions" v-if="selectedIds.length > 0">
        <el-button type="primary" @click="handleBatchEnable">批量启用</el-button>
        <el-button @click="handleBatchDisable">批量禁用</el-button>
        <el-button type="danger" @click="handleBatchDelete">批量删除</el-button>
        <span class="selected-count">已选择 {{ selectedIds.length }} 项</span>
      </div>

      <!-- 数据表格 -->
      <el-table :data="list" v-loading="loading" @selection-change="handleSelectionChange">
        <el-table-column type="selection" width="55" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="word" label="敏感词" min-width="150" />
        <el-table-column prop="category" label="分类" width="120">
          <template #default="{ row }">
            <el-tag :type="getCategoryType(row.category)">{{ categories[row.category] }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="level" label="处理级别" width="120">
          <template #default="{ row }">
            <el-tag :type="getLevelType(row.level)">{{ levels[row.level] }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="replacement" label="替换词" width="100" />
        <el-table-column prop="hit_count" label="命中次数" width="100" sortable />
        <el-table-column prop="is_enabled" label="状态" width="100">
          <template #default="{ row }">
            <el-switch v-model="row.is_enabled" :active-value="1" :inactive-value="0"
              @change="handleStatusChange(row)" />
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="创建时间" width="170" />
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
            <el-button type="danger" link @click="handleDelete(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination v-model:current-page="page" v-model:page-size="pageSize" :total="total"
        @current-change="loadList" @size-change="loadList" layout="total, sizes, prev, pager, next, jumper"
        :page-sizes="[20, 50, 100, 200]" />
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog v-model="dialogVisible" :title="dialogTitle" width="600px" @close="handleDialogClose">
      <el-form :model="form" :rules="rules" ref="formRef" label-width="100px">
        <el-form-item label="敏感词" prop="word">
          <el-input v-model="form.word" placeholder="请输入敏感词" />
        </el-form-item>
        <el-form-item label="分类" prop="category">
          <el-select v-model="form.category" placeholder="请选择分类" style="width: 100%">
            <el-option v-for="(label, value) in categories" :key="value" :label="label" :value="value" />
          </el-select>
        </el-form-item>
        <el-form-item label="处理级别" prop="level">
          <el-select v-model="form.level" placeholder="请选择处理级别" style="width: 100%">
            <el-option v-for="(label, value) in levels" :key="value" :label="label" :value="value" />
          </el-select>
          <div class="form-tip">提示：1-警告 2-替换 3-拒绝发布</div>
        </el-form-item>
        <el-form-item label="替换词" prop="replacement">
          <el-input v-model="form.replacement" placeholder="默认为 ***" />
        </el-form-item>
        <el-form-item label="启用状态">
          <el-switch v-model="form.is_enabled" :active-value="1" :inactive-value="0" />
        </el-form-item>
        <el-form-item label="备注">
          <el-input v-model="form.remark" type="textarea" :rows="3" placeholder="备注说明（选填）" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">确定</el-button>
      </template>
    </el-dialog>

    <!-- 批量导入对话框 -->
    <el-dialog v-model="importDialogVisible" title="批量导入敏感词" width="700px">
      <el-form :model="importForm" label-width="100px">
        <el-form-item label="敏感词列表">
          <el-input v-model="importForm.words" type="textarea" :rows="10"
            placeholder="每行一个敏感词（例如：&#10;反动&#10;暴乱&#10;色情）" />
          <div class="form-tip">请每行输入一个敏感词，系统将自动去重和过滤</div>
        </el-form-item>
        <el-form-item label="分类">
          <el-select v-model="importForm.category" style="width: 100%">
            <el-option v-for="(label, value) in categories" :key="value" :label="label" :value="value" />
          </el-select>
        </el-form-item>
        <el-form-item label="处理级别">
          <el-select v-model="importForm.level" style="width: 100%">
            <el-option v-for="(label, value) in levels" :key="value" :label="label" :value="value" />
          </el-select>
        </el-form-item>
        <el-form-item label="替换词">
          <el-input v-model="importForm.replacement" placeholder="默认为 ***" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="importDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleImportSubmit" :loading="importing">导入</el-button>
      </template>
    </el-dialog>

    <!-- 测试检测对话框 -->
    <el-dialog v-model="testDialogVisible" title="测试敏感词检测" width="800px">
      <el-form label-width="100px">
        <el-form-item label="测试内容">
          <el-input v-model="testContent" type="textarea" :rows="6" placeholder="请输入要检测的文本内容" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleTest" :loading="testing">开始检测</el-button>
        </el-form-item>
        <el-form-item label="检测结果" v-if="testResult">
          <el-alert :type="testResult.has_sensitive ? 'warning' : 'success'"
            :title="testResult.has_sensitive ? `检测到 ${testResult.matched_count} 个敏感词` : '未检测到敏感词'"
            :closable="false" />
          <div class="test-result" v-if="testResult.has_sensitive">
            <div class="matched-words">
              <strong>匹配的敏感词：</strong>
              <el-tag v-for="(detail, index) in testResult.matched_details" :key="index" style="margin: 5px"
                :type="getLevelType(detail.level)">
                {{ detail.word }} ({{ levels[detail.level] }})
              </el-tag>
            </div>
            <div class="filtered-content">
              <strong>过滤后的内容：</strong>
              <pre>{{ testResult.filtered_content }}</pre>
            </div>
          </div>
        </el-form-item>
      </el-form>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getSensitiveWords,
  getSensitiveWord,
  createSensitiveWord,
  updateSensitiveWord,
  deleteSensitiveWord,
  batchDeleteSensitiveWords,
  batchImportSensitiveWords,
  batchUpdateSensitiveWordsStatus,
  getSensitiveWordCategories,
  getSensitiveWordLevels,
  getSensitiveWordStatistics,
  testSensitiveWord
} from '@/api/sensitiveWord'

const loading = ref(false)
const list = ref([])
const total = ref(0)
const page = ref(1)
const pageSize = ref(20)
const selectedIds = ref([])
const categories = ref({})
const levels = ref({})
const dialogVisible = ref(false)
const dialogTitle = ref('添加敏感词')
const submitting = ref(false)
const formRef = ref(null)
const importDialogVisible = ref(false)
const importing = ref(false)
const testDialogVisible = ref(false)
const testing = ref(false)
const testContent = ref('')
const testResult = ref(null)

const searchForm = reactive({
  category: '',
  level: '',
  is_enabled: '',
  keyword: ''
})

const form = reactive({
  id: null,
  word: '',
  category: 'general',
  level: 2,
  replacement: '***',
  is_enabled: 1,
  remark: ''
})

const importForm = reactive({
  words: '',
  category: 'general',
  level: 2,
  replacement: '***'
})

const rules = {
  word: [{ required: true, message: '请输入敏感词', trigger: 'blur' }],
  category: [{ required: true, message: '请选择分类', trigger: 'change' }],
  level: [{ required: true, message: '请选择处理级别', trigger: 'change' }]
}

const loadList = async () => {
  loading.value = true
  try {
    const res = await getSensitiveWords({
      page: page.value,
      page_size: pageSize.value,
      ...searchForm
    })
    list.value = res.data.list
    total.value = res.data.total
  } catch (error) {
    ElMessage.error('加载列表失败')
  } finally {
    loading.value = false
  }
}

const loadOptions = async () => {
  try {
    const [catRes, levelRes] = await Promise.all([
      getSensitiveWordCategories(),
      getSensitiveWordLevels()
    ])
    categories.value = catRes.data
    levels.value = levelRes.data
  } catch (error) {
    ElMessage.error('加载选项失败')
  }
}

const loadStatistics = async () => {
  try {
    const res = await getSensitiveWordStatistics()
    const stats = res.data
    const msg = `
      总计：${stats.total} 个敏感词
      启用：${stats.enabled} 个
      禁用：${stats.disabled} 个
      ${Object.entries(stats.by_category || {}).map(([k, v]) => `${categories.value[k]}: ${v}个`).join('，')}
    `
    ElMessageBox.alert(msg, '统计信息', { confirmButtonText: '确定' })
  } catch (error) {
    ElMessage.error('加载统计失败')
  }
}

const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

const handleAdd = () => {
  dialogTitle.value = '添加敏感词'
  Object.assign(form, {
    id: null,
    word: '',
    category: 'general',
    level: 2,
    replacement: '***',
    is_enabled: 1,
    remark: ''
  })
  dialogVisible.value = true
}

const handleEdit = async (row) => {
  dialogTitle.value = '编辑敏感词'
  try {
    const res = await getSensitiveWord(row.id)
    Object.assign(form, res.data)
    dialogVisible.value = true
  } catch (error) {
    ElMessage.error('加载详情失败')
  }
}

const handleSubmit = async () => {
  if (!formRef.value) return
  try {
    await formRef.value.validate()
  } catch {
    return
  }

  submitting.value = true
  try {
    if (form.id) {
      await updateSensitiveWord(form.id, form)
      ElMessage.success('更新成功')
    } else {
      await createSensitiveWord(form)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    loadList()
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  } finally {
    submitting.value = false
  }
}

const handleDelete = (id) => {
  ElMessageBox.confirm('确定要删除这个敏感词吗？', '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await deleteSensitiveWord(id)
      ElMessage.success('删除成功')
      loadList()
    } catch (error) {
      ElMessage.error('删除失败')
    }
  }).catch(() => { })
}

const handleBatchDelete = () => {
  ElMessageBox.confirm(`确定要删除选中的 ${selectedIds.value.length} 个敏感词吗？`, '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await batchDeleteSensitiveWords(selectedIds.value)
      ElMessage.success('批量删除成功')
      loadList()
    } catch (error) {
      ElMessage.error('批量删除失败')
    }
  }).catch(() => { })
}

const handleBatchEnable = async () => {
  try {
    await batchUpdateSensitiveWordsStatus(selectedIds.value, 1)
    ElMessage.success('批量启用成功')
    loadList()
  } catch (error) {
    ElMessage.error('批量启用失败')
  }
}

const handleBatchDisable = async () => {
  try {
    await batchUpdateSensitiveWordsStatus(selectedIds.value, 0)
    ElMessage.success('批量禁用成功')
    loadList()
  } catch (error) {
    ElMessage.error('批量禁用失败')
  }
}

const handleStatusChange = async (row) => {
  try {
    await updateSensitiveWord(row.id, { is_enabled: row.is_enabled })
    ElMessage.success('状态更新成功')
  } catch (error) {
    row.is_enabled = row.is_enabled === 1 ? 0 : 1
    ElMessage.error('状态更新失败')
  }
}

const handleBatchImport = () => {
  importForm.words = ''
  importForm.category = 'general'
  importForm.level = 2
  importForm.replacement = '***'
  importDialogVisible.value = true
}

const handleImportSubmit = async () => {
  if (!importForm.words.trim()) {
    ElMessage.warning('请输入要导入的敏感词')
    return
  }

  importing.value = true
  try {
    const res = await batchImportSensitiveWords(importForm)
    ElMessage.success(res.message || '导入成功')
    importDialogVisible.value = false
    loadList()
  } catch (error) {
    ElMessage.error(error.message || '导入失败')
  } finally {
    importing.value = false
  }
}

const showTestDialog = () => {
  testContent.value = ''
  testResult.value = null
  testDialogVisible.value = true
}

const handleTest = async () => {
  if (!testContent.value.trim()) {
    ElMessage.warning('请输入测试内容')
    return
  }

  testing.value = true
  try {
    const res = await testSensitiveWord(testContent.value)
    testResult.value = res.data
  } catch (error) {
    ElMessage.error('检测失败')
  } finally {
    testing.value = false
  }
}

const handleReset = () => {
  Object.assign(searchForm, {
    category: '',
    level: '',
    is_enabled: '',
    keyword: ''
  })
  page.value = 1
  loadList()
}

const handleDialogClose = () => {
  formRef.value?.resetFields()
}

const getCategoryType = (category) => {
  const types = {
    politics: 'danger',
    porn: 'danger',
    violence: 'warning',
    ad: 'info',
    abuse: 'warning',
    general: ''
  }
  return types[category] || ''
}

const getLevelType = (level) => {
  const types = { 1: 'info', 2: 'warning', 3: 'danger' }
  return types[level] || ''
}

onMounted(() => {
  loadOptions()
  loadList()
})
</script>

<style scoped>
.sensitive-words-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-buttons {
  display: flex;
  gap: 10px;
}

.search-form {
  margin-bottom: 20px;
}

.batch-actions {
  margin-bottom: 15px;
  padding: 10px;
  background-color: #f0f9ff;
  border-radius: 4px;
}

.selected-count {
  margin-left: 15px;
  color: #409EFF;
  font-weight: bold;
}

.form-tip {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
}

.test-result {
  margin-top: 15px;
}

.test-result .matched-words,
.test-result .filtered-content {
  margin-top: 15px;
}

.test-result pre {
  background-color: #f5f7fa;
  padding: 10px;
  border-radius: 4px;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
