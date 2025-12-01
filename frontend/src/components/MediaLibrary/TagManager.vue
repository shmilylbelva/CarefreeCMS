<template>
  <div class="tag-manager">
    <!-- 工具栏 -->
    <div class="toolbar">
      <el-input
        v-model="searchKeyword"
        placeholder="搜索标签..."
        clearable
        style="width: 200px"
        @input="handleSearch"
      >
        <template #prefix>
          <el-icon><Search /></el-icon>
        </template>
      </el-input>
      <el-button type="primary" size="small" @click="showCreateDialog">
        <el-icon><Plus /></el-icon>
        新建标签
      </el-button>
      <el-button size="small" @click="loadTags">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
      <el-dropdown @command="handleCommand">
        <el-button size="small">
          更多操作
          <el-icon class="el-icon--right"><ArrowDown /></el-icon>
        </el-button>
        <template #dropdown>
          <el-dropdown-menu>
            <el-dropdown-item command="cleanUnused">清理未使用</el-dropdown-item>
            <el-dropdown-item command="recalculate">重新计算</el-dropdown-item>
            <el-dropdown-item command="batchDelete" :disabled="!selectedTags.length">
              批量删除
            </el-dropdown-item>
          </el-dropdown-menu>
        </template>
      </el-dropdown>
      <div class="view-switch">
        <el-radio-group v-model="viewMode" size="small">
          <el-radio-button value="list">列表</el-radio-button>
          <el-radio-button value="cloud">标签云</el-radio-button>
        </el-radio-group>
      </div>
    </div>

    <!-- 列表视图 -->
    <el-table
      v-if="viewMode === 'list'"
      :data="tagList"
      v-loading="loading"
      @selection-change="handleSelectionChange"
    >
      <el-table-column type="selection" width="50" />
      <el-table-column prop="name" label="标签名称">
        <template #default="{ row }">
          <el-tag :color="row.color || undefined" effect="plain">
            {{ row.name }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="usage_count" label="使用次数" width="100" sortable />
      <el-table-column prop="created_at" label="创建时间" width="180" />
      <el-table-column label="操作" width="150" fixed="right">
        <template #default="{ row }">
          <el-button link size="small" @click="showEditDialog(row)">编辑</el-button>
          <el-button link size="small" @click="showMergeDialog(row)">合并</el-button>
          <el-button link size="small" type="danger" @click="handleDelete(row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- 标签云视图 -->
    <div v-else class="tag-cloud" v-loading="cloudLoading">
      <el-tag
        v-for="tag in cloudData"
        :key="tag.id"
        :style="getTagStyle(tag)"
        :color="tag.color || undefined"
        effect="plain"
        @click="handleTagClick(tag)"
      >
        {{ tag.name }}
        <span class="tag-count">({{ tag.usage_count }})</span>
      </el-tag>
      <el-empty v-if="cloudData.length === 0" description="暂无标签" />
    </div>

    <!-- 分页 -->
    <el-pagination
      v-if="viewMode === 'list'"
      v-model:current-page="currentPage"
      v-model:page-size="pageSize"
      :total="total"
      :page-sizes="[20, 50, 100]"
      layout="total, sizes, prev, pager, next"
      @change="loadTags"
      style="margin-top: 15px;"
    />

    <!-- 创建/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑标签' : '新建标签'"
      width="400px"
      :close-on-click-modal="false"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="80px"
      >
        <el-form-item label="标签名称" prop="name">
          <el-input v-model="formData.name" placeholder="请输入标签名称" />
        </el-form-item>
        <el-form-item label="颜色">
          <el-color-picker v-model="formData.color" />
        </el-form-item>
        <el-form-item label="描述">
          <el-input
            v-model="formData.description"
            type="textarea"
            :rows="3"
            placeholder="请输入标签描述"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">
          {{ isEdit ? '保存' : '创建' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- 合并对话框 -->
    <el-dialog
      v-model="mergeDialogVisible"
      title="合并标签"
      width="400px"
    >
      <el-form label-width="80px">
        <el-form-item label="源标签">
          <el-tag v-if="mergeSource" :color="mergeSource.color || undefined">
            {{ mergeSource.name }}
          </el-tag>
        </el-form-item>
        <el-form-item label="目标标签">
          <el-select
            v-model="mergeTargetId"
            filterable
            placeholder="请选择目标标签"
            style="width: 100%"
          >
            <el-option
              v-for="tag in tagList.filter(t => t.id !== mergeSource?.id)"
              :key="tag.id"
              :label="tag.name"
              :value="tag.id"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="mergeDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleMerge" :loading="merging">
          合并
        </el-button>
      </template>
    </el-dialog>

    <!-- 批量创建对话框 -->
    <el-dialog
      v-model="batchCreateDialogVisible"
      title="批量创建标签"
      width="400px"
    >
      <el-form label-width="80px">
        <el-form-item label="标签名称">
          <el-input
            v-model="batchTagNames"
            type="textarea"
            :rows="5"
            placeholder="每行一个标签名称"
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="batchCreateDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleBatchCreate" :loading="batchCreating">
          创建
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Search, Refresh, ArrowDown } from '@element-plus/icons-vue'
import {
  getTagList,
  getTagCloud,
  searchTags,
  createTag,
  updateTag,
  deleteTag,
  batchCreateTags,
  batchDeleteTags,
  mergeTags,
  cleanUnusedTags,
  recalculateTagCounts
} from '@/api/mediaTag'

const emit = defineEmits(['select', 'change'])

// 视图模式
const viewMode = ref('list')
const searchKeyword = ref('')

// 列表数据
const tagList = ref([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const selectedTags = ref([])

// 标签云数据
const cloudData = ref([])
const cloudLoading = ref(false)

// 对话框
const dialogVisible = ref(false)
const isEdit = ref(false)
const submitting = ref(false)
const formRef = ref(null)

const formData = reactive({
  id: null,
  name: '',
  color: '',
  description: ''
})

const formRules = {
  name: [
    { required: true, message: '请输入标签名称', trigger: 'blur' },
    { max: 50, message: '标签名称不能超过50个字符', trigger: 'blur' }
  ]
}

// 合并对话框
const mergeDialogVisible = ref(false)
const mergeSource = ref(null)
const mergeTargetId = ref(null)
const merging = ref(false)

// 批量创建
const batchCreateDialogVisible = ref(false)
const batchTagNames = ref('')
const batchCreating = ref(false)

// 搜索防抖
let searchTimer = null

// 加载标签列表
const loadTags = async () => {
  loading.value = true
  try {
    const res = await getTagList({
      page: currentPage.value,
      pageSize: pageSize.value,
      keyword: searchKeyword.value
    })
    tagList.value = res.data?.list || res.data || []
    total.value = res.data?.total || tagList.value.length
  } catch (error) {
    ElMessage.error('加载标签失败')
  } finally {
    loading.value = false
  }
}

// 加载标签云
const loadCloud = async () => {
  cloudLoading.value = true
  try {
    const res = await getTagCloud(100)
    cloudData.value = res.data || []
  } catch (error) {
    ElMessage.error('加载标签云失败')
  } finally {
    cloudLoading.value = false
  }
}

// 搜索处理
const handleSearch = () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    currentPage.value = 1
    loadTags()
  }, 300)
}

// 获取标签云样式
const getTagStyle = (tag) => {
  const baseSize = 12
  const maxSize = 24
  const size = baseSize + (tag.weight - 1) * ((maxSize - baseSize) / 4)
  return {
    fontSize: `${size}px`,
    margin: '5px',
    cursor: 'pointer'
  }
}

// 表格选择变化
const handleSelectionChange = (selection) => {
  selectedTags.value = selection
}

// 显示创建对话框
const showCreateDialog = () => {
  isEdit.value = false
  Object.assign(formData, {
    id: null,
    name: '',
    color: '',
    description: ''
  })
  dialogVisible.value = true
}

// 显示编辑对话框
const showEditDialog = (row) => {
  isEdit.value = true
  Object.assign(formData, {
    id: row.id,
    name: row.name,
    color: row.color || '',
    description: row.description || ''
  })
  dialogVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
  await formRef.value?.validate()

  submitting.value = true
  try {
    if (isEdit.value) {
      await updateTag(formData.id, formData)
      ElMessage.success('更新成功')
    } else {
      await createTag(formData)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    loadTags()
    if (viewMode.value === 'cloud') {
      loadCloud()
    }
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  } finally {
    submitting.value = false
  }
}

// 删除标签
const handleDelete = async (row) => {
  if (row.usage_count > 0) {
    ElMessage.warning(`该标签正在被 ${row.usage_count} 个媒体使用，无法删除`)
    return
  }

  await ElMessageBox.confirm(
    `确定要删除标签"${row.name}"吗？`,
    '删除确认',
    { type: 'warning' }
  )

  try {
    await deleteTag(row.id)
    ElMessage.success('删除成功')
    loadTags()
    if (viewMode.value === 'cloud') {
      loadCloud()
    }
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '删除失败')
  }
}

// 显示合并对话框
const showMergeDialog = (row) => {
  mergeSource.value = row
  mergeTargetId.value = null
  mergeDialogVisible.value = true
}

// 处理合并
const handleMerge = async () => {
  if (!mergeTargetId.value) {
    ElMessage.warning('请选择目标标签')
    return
  }

  merging.value = true
  try {
    await mergeTags([mergeSource.value.id], mergeTargetId.value)
    ElMessage.success('合并成功')
    mergeDialogVisible.value = false
    loadTags()
    if (viewMode.value === 'cloud') {
      loadCloud()
    }
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '合并失败')
  } finally {
    merging.value = false
  }
}

// 处理批量创建
const handleBatchCreate = async () => {
  const names = batchTagNames.value
    .split('\n')
    .map(name => name.trim())
    .filter(name => name)

  if (names.length === 0) {
    ElMessage.warning('请输入标签名称')
    return
  }

  batchCreating.value = true
  try {
    await batchCreateTags(names)
    ElMessage.success(`成功创建 ${names.length} 个标签`)
    batchCreateDialogVisible.value = false
    batchTagNames.value = ''
    loadTags()
    if (viewMode.value === 'cloud') {
      loadCloud()
    }
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '批量创建失败')
  } finally {
    batchCreating.value = false
  }
}

// 下拉命令处理
const handleCommand = async (command) => {
  switch (command) {
    case 'cleanUnused':
      await handleCleanUnused()
      break
    case 'recalculate':
      await handleRecalculate()
      break
    case 'batchDelete':
      await handleBatchDelete()
      break
  }
}

// 清理未使用的标签
const handleCleanUnused = async () => {
  await ElMessageBox.confirm(
    '确定要清理所有未使用的标签吗？',
    '清理确认',
    { type: 'warning' }
  )

  try {
    const res = await cleanUnusedTags()
    ElMessage.success(res.message || '清理成功')
    loadTags()
    if (viewMode.value === 'cloud') {
      loadCloud()
    }
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '清理失败')
  }
}

// 重新计算使用次数
const handleRecalculate = async () => {
  try {
    const res = await recalculateTagCounts()
    ElMessage.success(res.message || '重新计算成功')
    loadTags()
    if (viewMode.value === 'cloud') {
      loadCloud()
    }
  } catch (error) {
    ElMessage.error(error.message || '重新计算失败')
  }
}

// 批量删除
const handleBatchDelete = async () => {
  const ids = selectedTags.value.map(tag => tag.id)
  const hasUsed = selectedTags.value.some(tag => tag.usage_count > 0)

  if (hasUsed) {
    ElMessage.warning('选中的标签中有正在使用的，无法删除')
    return
  }

  await ElMessageBox.confirm(
    `确定要删除选中的 ${ids.length} 个标签吗？`,
    '批量删除确认',
    { type: 'warning' }
  )

  try {
    await batchDeleteTags(ids)
    ElMessage.success('批量删除成功')
    selectedTags.value = []
    loadTags()
    if (viewMode.value === 'cloud') {
      loadCloud()
    }
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '批量删除失败')
  }
}

// 标签点击
const handleTagClick = (tag) => {
  emit('select', tag)
}

// 监听视图模式变化
watch(viewMode, (mode) => {
  if (mode === 'cloud') {
    loadCloud()
  }
})

// 暴露方法
defineExpose({
  loadTags,
  showCreateDialog
})

onMounted(() => {
  loadTags()
})
</script>

<style scoped>
.tag-manager {
  padding: 10px;
}

.toolbar {
  margin-bottom: 15px;
  display: flex;
  gap: 10px;
  align-items: center;
}

.view-switch {
  margin-left: auto;
}

.tag-cloud {
  min-height: 200px;
  padding: 20px;
  background: #f5f7fa;
  border-radius: 4px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
}

.tag-count {
  font-size: 0.8em;
  opacity: 0.7;
}
</style>
