<template>
  <div class="emoji-manage-container">
    <el-card class="filter-card">
      <el-form :inline="true" :model="queryParams" class="filter-form">
        <el-form-item label="分类">
          <el-select v-model="queryParams.category" placeholder="全部分类" clearable style="width: 150px">
            <el-option
              v-for="cat in categories"
              :key="cat"
              :label="cat"
              :value="cat"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="状态">
          <el-select v-model="queryParams.is_enabled" placeholder="全部状态" clearable style="width: 120px">
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>

        <el-form-item label="关键词">
          <el-input
            v-model="queryParams.keyword"
            placeholder="搜索表情名称或代码"
            clearable
            style="width: 200px"
            @keyup.enter="handleFilter"
          />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="handleFilter">
            <el-icon><Search /></el-icon>
            搜索
          </el-button>
          <el-button @click="handleReset">
            <el-icon><Refresh /></el-icon>
            重置
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <span>表情列表</span>
          <div class="header-actions">
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              添加表情
            </el-button>
            <el-button
              type="success"
              :disabled="selectedIds.length === 0"
              @click="handleBatchToggle(1)"
            >
              <el-icon><Check /></el-icon>
              批量启用
            </el-button>
            <el-button
              type="warning"
              :disabled="selectedIds.length === 0"
              @click="handleBatchToggle(0)"
            >
              <el-icon><Close /></el-icon>
              批量禁用
            </el-button>
            <el-button
              type="danger"
              :disabled="selectedIds.length === 0"
              @click="handleBatchDelete"
            >
              <el-icon><Delete /></el-icon>
              批量删除
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        v-loading="loading"
        :data="list"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" />

        <el-table-column label="ID" prop="id" width="60" />

        <el-table-column label="表情预览" width="100">
          <template #default="{ row }">
            <div class="emoji-preview">
              <span v-if="row.unicode" class="emoji-unicode">{{ row.unicode }}</span>
              <img v-else-if="row.image_url" :src="row.image_url" alt="emoji" class="emoji-image" />
              <span v-else class="text-secondary">-</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="表情名称" prop="name" width="150" />

        <el-table-column label="表情代码" prop="code" width="150">
          <template #default="{ row }">
            <el-tag size="small">{{ row.code }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="分类" prop="category" width="120">
          <template #default="{ row }">
            <el-tag type="info" size="small">{{ row.category }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="使用次数" prop="use_count" width="100" sortable>
          <template #default="{ row }">
            <el-tag type="success" size="small">{{ row.use_count || 0 }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="排序" prop="sort" width="100" sortable />

        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-switch
              v-model="row.is_enabled"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(row)"
            />
          </template>
        </el-table-column>

        <el-table-column label="创建时间" width="160">
          <template #default="{ row }">
            {{ row.create_time }}
          </template>
        </el-table-column>

        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" link @click="handleEdit(row)">
              编辑
            </el-button>
            <el-button type="info" size="small" link @click="handleResetCount(row.id)">
              重置计数
            </el-button>
            <el-button type="danger" size="small" link @click="handleDelete(row.id)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-container">
        <el-pagination
          v-model:current-page="queryParams.page"
          v-model:page-size="queryParams.limit"
          :total="total"
          :page-sizes="[20, 50, 100, 200]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="getList"
          @current-change="getList"
        />
      </div>
    </el-card>

    <!-- 添加/编辑表情对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="600px"
    >
      <el-form :model="form" :rules="rules" ref="formRef" label-width="100px">
        <el-form-item label="表情名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入表情名称" />
        </el-form-item>

        <el-form-item label="表情代码" prop="code">
          <el-input v-model="form.code" placeholder="例如: :smile:" />
          <div class="form-tip">用户在评论中输入此代码会被替换为表情</div>
        </el-form-item>

        <el-form-item label="表情类型" required>
          <el-radio-group v-model="emojiType">
            <el-radio value="unicode">Unicode字符</el-radio>
            <el-radio value="image">图片URL</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item v-if="emojiType === 'unicode'" label="Unicode" prop="unicode">
          <el-input v-model="form.unicode" placeholder="例如: 😀" />
          <div class="form-tip">直接输入emoji字符</div>
        </el-form-item>

        <el-form-item v-if="emojiType === 'image'" label="图片URL" prop="image_url">
          <el-input v-model="form.image_url" placeholder="https://example.com/emoji.png" />
          <div class="form-tip">表情图片的完整URL地址</div>
        </el-form-item>

        <el-form-item label="分类" prop="category">
          <el-select v-model="form.category" placeholder="选择或输入分类" filterable allow-create>
            <el-option
              v-for="cat in categories"
              :key="cat"
              :label="cat"
              :value="cat"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="排序值" prop="sort">
          <el-input-number v-model="form.sort" :min="0" :max="999" />
          <div class="form-tip">数值越小越靠前</div>
        </el-form-item>

        <el-form-item label="启用状态">
          <el-switch v-model="form.is_enabled" :active-value="1" :inactive-value="0" />
        </el-form-item>

        <el-form-item label="预览">
          <div class="emoji-preview-box">
            <span v-if="emojiType === 'unicode' && form.unicode" class="preview-emoji">
              {{ form.unicode }}
            </span>
            <img v-else-if="emojiType === 'image' && form.image_url" :src="form.image_url" alt="preview" class="preview-image" />
            <span v-else class="text-secondary">暂无预览</span>
          </div>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="submitForm">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Search,
  Refresh,
  Plus,
  Check,
  Close,
  Delete
} from '@element-plus/icons-vue'
import {
  getEmojiList,
  createEmoji,
  updateEmoji,
  deleteEmoji,
  batchDeleteEmojis,
  batchToggleEmojis,
  resetEmojiUseCount,
  getEmojiCategories
} from '@/api/comment'

const loading = ref(false)
const list = ref([])
const total = ref(0)
const selectedIds = ref([])
const dialogVisible = ref(false)
const categories = ref([])
const formRef = ref(null)
const emojiType = ref('unicode')

const queryParams = reactive({
  page: 1,
  limit: 50,
  category: '',
  is_enabled: '',
  keyword: ''
})

const form = reactive({
  name: '',
  code: '',
  unicode: '',
  image_url: '',
  category: 'default',
  sort: 0,
  is_enabled: 1
})

const rules = {
  name: [
    { required: true, message: '请输入表情名称', trigger: 'blur' }
  ],
  code: [
    { required: true, message: '请输入表情代码', trigger: 'blur' }
  ],
  category: [
    { required: true, message: '请选择分类', trigger: 'change' }
  ]
}

const dialogTitle = computed(() => {
  return form.id ? '编辑表情' : '添加表情'
})

// 获取列表
const getList = async () => {
  loading.value = true
  try {
    const { data } = await getEmojiList(queryParams)
    list.value = data.data
    total.value = data.total
  } catch (error) {
    ElMessage.error('获取表情列表失败')
  } finally {
    loading.value = false
  }
}

// 获取分类列表
const getCategories = async () => {
  try {
    const { data } = await getEmojiCategories()
    categories.value = data
  } catch (error) {
    console.error('获取分类失败', error)
  }
}

// 搜索
const handleFilter = () => {
  queryParams.page = 1
  getList()
}

// 重置
const handleReset = () => {
  Object.assign(queryParams, {
    page: 1,
    limit: 50,
    category: '',
    is_enabled: '',
    keyword: ''
  })
  getList()
}

// 选择改变
const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

// 添加表情
const handleAdd = () => {
  Object.assign(form, {
    id: null,
    name: '',
    code: '',
    unicode: '',
    image_url: '',
    category: 'default',
    sort: 0,
    is_enabled: 1
  })
  emojiType.value = 'unicode'
  dialogVisible.value = true
}

// 编辑表情
const handleEdit = (row) => {
  Object.assign(form, { ...row })
  emojiType.value = row.unicode ? 'unicode' : 'image'
  dialogVisible.value = true
}

// 提交表单
const submitForm = async () => {
  try {
    await formRef.value.validate()

    // 根据类型清空不需要的字段
    if (emojiType.value === 'unicode') {
      form.image_url = ''
      if (!form.unicode) {
        ElMessage.error('请输入Unicode字符')
        return
      }
    } else {
      form.unicode = ''
      if (!form.image_url) {
        ElMessage.error('请输入图片URL')
        return
      }
    }

    if (form.id) {
      await updateEmoji(form.id, form)
      ElMessage.success('更新成功')
    } else {
      await createEmoji(form)
      ElMessage.success('添加成功')
    }

    dialogVisible.value = false
    getList()
    getCategories()
  } catch (error) {
    if (error !== false) {
      ElMessage.error('操作失败')
    }
  }
}

// 状态切换
const handleStatusChange = async (row) => {
  try {
    await updateEmoji(row.id, { is_enabled: row.is_enabled })
    ElMessage.success('状态更新成功')
    getList()
  } catch (error) {
    ElMessage.error('状态更新失败')
    row.is_enabled = row.is_enabled === 1 ? 0 : 1
  }
}

// 删除表情
const handleDelete = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除这个表情吗？', '警告', {
      type: 'warning'
    })
    await deleteEmoji(id)
    ElMessage.success('删除成功')
    getList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

// 批量删除
const handleBatchDelete = async () => {
  try {
    await ElMessageBox.confirm('确定要批量删除选中的表情吗？', '警告', {
      type: 'warning'
    })
    await batchDeleteEmojis({ ids: selectedIds.value })
    ElMessage.success('批量删除成功')
    getList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('批量删除失败')
    }
  }
}

// 批量启用/禁用
const handleBatchToggle = async (isEnabled) => {
  const action = isEnabled ? '启用' : '禁用'
  try {
    await ElMessageBox.confirm(`确定要批量${action}选中的表情吗？`, '提示', {
      type: 'warning'
    })
    await batchToggleEmojis({ ids: selectedIds.value, is_enabled: isEnabled })
    ElMessage.success(`批量${action}成功`)
    getList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(`批量${action}失败`)
    }
  }
}

// 重置使用次数
const handleResetCount = async (id) => {
  try {
    await ElMessageBox.confirm('确定要重置这个表情的使用次数吗？', '提示', {
      type: 'warning'
    })
    await resetEmojiUseCount(id)
    ElMessage.success('重置成功')
    getList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('重置失败')
    }
  }
}

onMounted(() => {
  getList()
  getCategories()
})
</script>

<style scoped>
.emoji-manage-container {
  padding: 20px;
}

.filter-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.emoji-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 40px;
}

.emoji-unicode {
  font-size: 32px;
}

.emoji-image {
  max-width: 32px;
  max-height: 32px;
}

.text-secondary {
  color: #909399;
  font-size: 12px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.form-tip {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
}

.emoji-preview-box {
  padding: 20px;
  background-color: #f5f7fa;
  border-radius: 4px;
  text-align: center;
}

.preview-emoji {
  font-size: 48px;
}

.preview-image {
  max-width: 64px;
  max-height: 64px;
}
</style>
