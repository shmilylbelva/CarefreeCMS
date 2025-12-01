<template>
  <div class="content-violations-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>违规记录管理</span>
          <el-button type="primary" @click="loadStatistics">刷新统计</el-button>
        </div>
      </template>

      <!-- 统计信息 -->
      <el-row :gutter="20" class="statistics-row" v-if="statistics">
        <el-col :span="6">
          <el-statistic title="总违规次数" :value="statistics.total_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="待审核" :value="statistics.pending_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="已审核" :value="statistics.reviewed_count" />
        </el-col>
        <el-col :span="6">
          <el-statistic title="已忽略" :value="statistics.ignored_count" />
        </el-col>
      </el-row>

      <el-divider />

      <!-- 搜索表单 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="内容类型">
          <el-select v-model="searchForm.content_type" placeholder="请选择" clearable style="width: 150px">
            <el-option label="文章" value="article" />
            <el-option label="评论" value="comment" />
            <el-option label="页面" value="page" />
          </el-select>
        </el-form-item>
        <el-form-item label="处理动作">
          <el-select v-model="searchForm.action" placeholder="请选择" clearable style="width: 150px">
            <el-option label="警告" value="warn" />
            <el-option label="替换" value="replace" />
            <el-option label="拒绝" value="reject" />
          </el-select>
        </el-form-item>
        <el-form-item label="审核状态">
          <el-select v-model="searchForm.review_status" placeholder="请选择" clearable style="width: 150px">
            <el-option label="待审核" value="pending" />
            <el-option label="已审核" value="reviewed" />
            <el-option label="已忽略" value="ignored" />
          </el-select>
        </el-form-item>
        <el-form-item label="用户ID">
          <el-input v-model="searchForm.user_id" placeholder="请输入用户ID" clearable style="width: 150px" />
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="searchForm.keyword" placeholder="搜索内容或敏感词" clearable style="width: 200px" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 批量操作 -->
      <div class="batch-actions">
        <el-button type="success" :disabled="selectedIds.length === 0" @click="handleBatchReview('reviewed')">
          批量标记为已审核
        </el-button>
        <el-button type="warning" :disabled="selectedIds.length === 0" @click="handleBatchReview('ignored')">
          批量标记为已忽略
        </el-button>
        <el-button type="danger" :disabled="selectedIds.length === 0" @click="handleBatchDelete">
          批量删除
        </el-button>
      </div>

      <!-- 数据表格 -->
      <el-table
        :data="tableData"
        v-loading="loading"
        @selection-change="handleSelectionChange"
        style="width: 100%; margin-top: 20px"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="content_type" label="内容类型" width="100">
          <template #default="{ row }">
            <el-tag :type="getContentTypeTag(row.content_type)">
              {{ getContentTypeLabel(row.content_type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="content_id" label="内容ID" width="100" />
        <el-table-column prop="user_id" label="用户ID" width="100">
          <template #default="{ row }">
            <el-link type="primary" @click="viewUser(row.user_id)">{{ row.user_id }}</el-link>
          </template>
        </el-table-column>
        <el-table-column prop="matched_words" label="匹配敏感词" width="200">
          <template #default="{ row }">
            <el-tag
              v-for="(word, index) in parseMatchedWords(row.matched_words)"
              :key="index"
              type="danger"
              size="small"
              style="margin-right: 5px; margin-bottom: 5px"
            >
              {{ word }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="original_content" label="原始内容" min-width="250">
          <template #default="{ row }">
            <el-text line-clamp="2" class="content-preview">{{ row.original_content }}</el-text>
          </template>
        </el-table-column>
        <el-table-column prop="filtered_content" label="过滤后内容" min-width="250">
          <template #default="{ row }">
            <el-text line-clamp="2" class="content-preview" v-if="row.filtered_content">
              {{ row.filtered_content }}
            </el-text>
            <span v-else style="color: #999">-</span>
          </template>
        </el-table-column>
        <el-table-column prop="action" label="处理动作" width="100">
          <template #default="{ row }">
            <el-tag :type="getActionTypeTag(row.action)">
              {{ getActionLabel(row.action) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="review_status" label="审核状态" width="120">
          <template #default="{ row }">
            <el-tag :type="getReviewStatusTag(row.review_status)">
              {{ getReviewStatusLabel(row.review_status) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="违规时间" width="180" />
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button link type="primary" size="small" @click="viewDetail(row)">详情</el-button>
            <el-button
              link
              type="success"
              size="small"
              @click="markAsReviewed(row.id)"
              v-if="row.review_status === 'pending'"
            >
              标记已审核
            </el-button>
            <el-button
              link
              type="warning"
              size="small"
              @click="markAsIgnored(row.id)"
              v-if="row.review_status === 'pending'"
            >
              忽略
            </el-button>
            <el-button link type="danger" size="small" @click="handleDelete(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :page-sizes="[10, 20, 50, 100]"
        :total="pagination.total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handlePageChange"
        style="margin-top: 20px; justify-content: flex-end"
      />
    </el-card>

    <!-- 详情对话框 -->
    <el-dialog v-model="detailDialogVisible" title="违规记录详情" width="800px">
      <el-descriptions :column="2" border v-if="currentRecord">
        <el-descriptions-item label="ID">{{ currentRecord.id }}</el-descriptions-item>
        <el-descriptions-item label="内容类型">
          <el-tag :type="getContentTypeTag(currentRecord.content_type)">
            {{ getContentTypeLabel(currentRecord.content_type) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="内容ID">{{ currentRecord.content_id }}</el-descriptions-item>
        <el-descriptions-item label="用户ID">
          <el-link type="primary" @click="viewUser(currentRecord.user_id)">
            {{ currentRecord.user_id }}
          </el-link>
        </el-descriptions-item>
        <el-descriptions-item label="处理动作">
          <el-tag :type="getActionTypeTag(currentRecord.action)">
            {{ getActionLabel(currentRecord.action) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="审核状态">
          <el-tag :type="getReviewStatusTag(currentRecord.review_status)">
            {{ getReviewStatusLabel(currentRecord.review_status) }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="匹配敏感词" :span="2">
          <el-tag
            v-for="(word, index) in parseMatchedWords(currentRecord.matched_words)"
            :key="index"
            type="danger"
            size="small"
            style="margin-right: 5px; margin-bottom: 5px"
          >
            {{ word }}
          </el-tag>
        </el-descriptions-item>
        <el-descriptions-item label="原始内容" :span="2">
          <div class="content-detail">{{ currentRecord.original_content }}</div>
        </el-descriptions-item>
        <el-descriptions-item label="过滤后内容" :span="2" v-if="currentRecord.filtered_content">
          <div class="content-detail">{{ currentRecord.filtered_content }}</div>
        </el-descriptions-item>
        <el-descriptions-item label="违规时间">{{ currentRecord.created_at }}</el-descriptions-item>
        <el-descriptions-item label="审核时间" v-if="currentRecord.reviewed_at">
          {{ currentRecord.reviewed_at }}
        </el-descriptions-item>
        <el-descriptions-item label="审核人" v-if="currentRecord.reviewed_by">
          {{ currentRecord.reviewed_by }}
        </el-descriptions-item>
      </el-descriptions>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="detailDialogVisible = false">关闭</el-button>
          <el-button
            type="success"
            @click="markAsReviewed(currentRecord.id)"
            v-if="currentRecord.review_status === 'pending'"
          >
            标记已审核
          </el-button>
          <el-button
            type="warning"
            @click="markAsIgnored(currentRecord.id)"
            v-if="currentRecord.review_status === 'pending'"
          >
            忽略
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getContentViolations,
  getContentViolation,
  markViolationAsReviewed,
  markViolationAsIgnored,
  batchReviewViolations,
  deleteContentViolation,
  getContentViolationStatistics
} from '@/api/contentViolation'

// 搜索表单
const searchForm = reactive({
  content_type: '',
  action: '',
  review_status: '',
  user_id: '',
  keyword: ''
})

// 表格数据
const tableData = ref([])
const loading = ref(false)
const selectedIds = ref([])

// 分页
const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

// 统计数据
const statistics = ref(null)

// 详情对话框
const detailDialogVisible = ref(false)
const currentRecord = ref(null)

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    }

    // 移除空值参数
    Object.keys(params).forEach(key => {
      if (params[key] === '' || params[key] === null || params[key] === undefined) {
        delete params[key]
      }
    })

    const response = await getContentViolations(params)
    if (response.code === 0) {
      tableData.value = response.data.list || []
      pagination.total = response.data.total || 0
    } else {
      ElMessage.error(response.message || '加载失败')
    }
  } catch (error) {
    console.error('加载违规记录失败:', error)
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

// 加载统计信息
const loadStatistics = async () => {
  try {
    const response = await getContentViolationStatistics()
    if (response.code === 0) {
      statistics.value = response.data
      ElMessage.success('统计信息已更新')
    }
  } catch (error) {
    console.error('加载统计信息失败:', error)
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  loadData()
}

// 重置
const handleReset = () => {
  Object.assign(searchForm, {
    content_type: '',
    action: '',
    review_status: '',
    user_id: '',
    keyword: ''
  })
  handleSearch()
}

// 选择变化
const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

// 分页变化
const handlePageChange = (page) => {
  pagination.page = page
  loadData()
}

const handleSizeChange = (size) => {
  pagination.pageSize = size
  pagination.page = 1
  loadData()
}

// 标记为已审核
const markAsReviewed = async (id) => {
  try {
    const response = await markViolationAsReviewed(id)
    if (response.code === 0) {
      ElMessage.success('已标记为已审核')
      detailDialogVisible.value = false
      loadData()
      loadStatistics()
    } else {
      ElMessage.error(response.message || '操作失败')
    }
  } catch (error) {
    console.error('标记已审核失败:', error)
    ElMessage.error('操作失败')
  }
}

// 标记为已忽略
const markAsIgnored = async (id) => {
  try {
    const response = await markViolationAsIgnored(id)
    if (response.code === 0) {
      ElMessage.success('已标记为已忽略')
      detailDialogVisible.value = false
      loadData()
      loadStatistics()
    } else {
      ElMessage.error(response.message || '操作失败')
    }
  } catch (error) {
    console.error('标记已忽略失败:', error)
    ElMessage.error('操作失败')
  }
}

// 批量审核
const handleBatchReview = async (status) => {
  const statusText = status === 'reviewed' ? '已审核' : '已忽略'
  ElMessageBox.confirm(
    `确定要将选中的 ${selectedIds.value.length} 条记录标记为${statusText}吗？`,
    '批量操作确认',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  )
    .then(async () => {
      try {
        const response = await batchReviewViolations(selectedIds.value, status)
        if (response.code === 0) {
          ElMessage.success(`已批量标记为${statusText}`)
          loadData()
          loadStatistics()
        } else {
          ElMessage.error(response.message || '操作失败')
        }
      } catch (error) {
        console.error('批量审核失败:', error)
        ElMessage.error('操作失败')
      }
    })
    .catch(() => {
      // 取消操作
    })
}

// 删除
const handleDelete = async (id) => {
  ElMessageBox.confirm('确定要删除这条违规记录吗？', '删除确认', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  })
    .then(async () => {
      try {
        const response = await deleteContentViolation(id)
        if (response.code === 0) {
          ElMessage.success('删除成功')
          loadData()
          loadStatistics()
        } else {
          ElMessage.error(response.message || '删除失败')
        }
      } catch (error) {
        console.error('删除失败:', error)
        ElMessage.error('删除失败')
      }
    })
    .catch(() => {
      // 取消操作
    })
}

// 批量删除
const handleBatchDelete = () => {
  ElMessageBox.confirm(
    `确定要删除选中的 ${selectedIds.value.length} 条记录吗？此操作不可恢复！`,
    '批量删除确认',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  )
    .then(async () => {
      try {
        // 逐个删除
        const deletePromises = selectedIds.value.map(id => deleteContentViolation(id))
        await Promise.all(deletePromises)
        ElMessage.success('批量删除成功')
        loadData()
        loadStatistics()
      } catch (error) {
        console.error('批量删除失败:', error)
        ElMessage.error('批量删除失败')
      }
    })
    .catch(() => {
      // 取消操作
    })
}

// 查看详情
const viewDetail = async (row) => {
  try {
    const response = await getContentViolation(row.id)
    if (response.code === 0) {
      currentRecord.value = response.data
      detailDialogVisible.value = true
    } else {
      ElMessage.error(response.message || '获取详情失败')
    }
  } catch (error) {
    console.error('获取详情失败:', error)
    ElMessage.error('获取详情失败')
  }
}

// 查看用户
const viewUser = (userId) => {
  // TODO: 跳转到用户详情页
  ElMessage.info(`查看用户 ${userId} 的详细信息`)
}

// 解析匹配的敏感词
const parseMatchedWords = (matchedWords) => {
  if (!matchedWords) return []
  try {
    return JSON.parse(matchedWords)
  } catch {
    return matchedWords.split(',')
  }
}

// 获取内容类型标签
const getContentTypeTag = (type) => {
  const map = {
    article: 'success',
    comment: 'primary',
    page: 'info'
  }
  return map[type] || ''
}

const getContentTypeLabel = (type) => {
  const map = {
    article: '文章',
    comment: '评论',
    page: '页面'
  }
  return map[type] || type
}

// 获取动作类型标签
const getActionTypeTag = (action) => {
  const map = {
    warn: 'warning',
    replace: 'info',
    reject: 'danger'
  }
  return map[action] || ''
}

const getActionLabel = (action) => {
  const map = {
    warn: '警告',
    replace: '替换',
    reject: '拒绝'
  }
  return map[action] || action
}

// 获取审核状态标签
const getReviewStatusTag = (status) => {
  const map = {
    pending: 'warning',
    reviewed: 'success',
    ignored: 'info'
  }
  return map[status] || ''
}

const getReviewStatusLabel = (status) => {
  const map = {
    pending: '待审核',
    reviewed: '已审核',
    ignored: '已忽略'
  }
  return map[status] || status
}

// 组件挂载时加载数据
onMounted(() => {
  loadData()
  loadStatistics()
})
</script>

<style scoped>
.content-violations-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.statistics-row {
  margin-bottom: 20px;
}

.search-form {
  margin-bottom: 20px;
}

.batch-actions {
  margin-bottom: 10px;
}

.content-preview {
  display: block;
  word-break: break-all;
}

.content-detail {
  white-space: pre-wrap;
  word-break: break-all;
  max-height: 300px;
  overflow-y: auto;
  padding: 10px;
  background-color: #f5f7fa;
  border-radius: 4px;
}
</style>
