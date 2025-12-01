<template>
  <div class="comment-list-container">
    <el-card class="filter-card">
      <el-form :inline="true" :model="queryParams" class="filter-form">
        <el-form-item label="状态">
          <el-select v-model="queryParams.status" placeholder="全部状态" clearable style="width: 120px">
            <el-option label="待审核" :value="0" />
            <el-option label="已通过" :value="1" />
            <el-option label="已拒绝" :value="2" />
          </el-select>
        </el-form-item>

        <el-form-item label="用户类型">
          <el-select v-model="queryParams.is_guest" placeholder="全部用户" clearable style="width: 120px">
            <el-option label="注册用户" :value="0" />
            <el-option label="游客" :value="1" />
          </el-select>
        </el-form-item>

        <el-form-item label="举报状态">
          <el-select v-model="queryParams.has_reports" placeholder="全部" clearable style="width: 120px">
            <el-option label="有举报" value="1" />
          </el-select>
        </el-form-item>

        <el-form-item label="关键词">
          <el-input
            v-model="queryParams.keyword"
            placeholder="搜索评论内容或用户"
            clearable
            style="width: 200px"
            @keyup.enter="handleFilter"
          />
        </el-form-item>

        <el-form-item label="时间范围">
          <el-date-picker
            v-model="dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            @change="handleDateChange"
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
          <span>评论列表</span>
          <div class="header-actions">
            <el-button
              type="success"
              :disabled="selectedIds.length === 0"
              @click="handleBatchAudit(1)"
            >
              <el-icon><Check /></el-icon>
              批量通过
            </el-button>
            <el-button
              type="warning"
              :disabled="selectedIds.length === 0"
              @click="handleBatchAudit(2)"
            >
              <el-icon><Close /></el-icon>
              批量拒绝
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

        <el-table-column label="评论内容" min-width="300">
          <template #default="{ row }">
            <div class="comment-content">
              <div>{{ row.content }}</div>
              <div class="comment-meta">
                <span v-if="row.article">
                  <el-icon><Document /></el-icon>
                  {{ row.article.title }}
                </span>
                <span v-if="row.parent_id > 0">
                  <el-icon><ChatLineSquare /></el-icon>
                  回复ID: {{ row.parent_id }}
                </span>
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="评论者" width="150">
          <template #default="{ row }">
            <div>
              <el-tag v-if="row.is_guest" type="info" size="small">游客</el-tag>
              <el-tag v-else type="success" size="small">注册</el-tag>
              {{ row.author_name || '匿名' }}
            </div>
            <div v-if="row.user_email" class="text-secondary">
              {{ row.user_email }}
            </div>
          </template>
        </el-table-column>

        <el-table-column label="互动数据" width="120">
          <template #default="{ row }">
            <div>
              <el-icon color="#67c23a"><CaretTop /></el-icon>
              {{ row.like_count || 0 }}
              <el-icon color="#f56c6c" style="margin-left: 8px"><CaretBottom /></el-icon>
              {{ row.dislike_count || 0 }}
            </div>
            <div v-if="row.report_count > 0" class="text-danger">
              <el-icon><Warning /></el-icon>
              举报: {{ row.report_count }}
            </div>
          </template>
        </el-table-column>

        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.status === 0" type="warning">待审核</el-tag>
            <el-tag v-else-if="row.status === 1" type="success">已通过</el-tag>
            <el-tag v-else type="danger">已拒绝</el-tag>
            <div v-if="row.is_hot" class="text-danger">
              <el-icon><Star /></el-icon>
              热门
            </div>
          </template>
        </el-table-column>

        <el-table-column label="评论时间" width="160">
          <template #default="{ row }">
            {{ row.create_time }}
          </template>
        </el-table-column>

        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button
              v-if="row.status === 0"
              type="success"
              size="small"
              link
              @click="handleAudit(row.id, 1)"
            >
              通过
            </el-button>
            <el-button
              v-if="row.status === 0"
              type="warning"
              size="small"
              link
              @click="handleAudit(row.id, 2)"
            >
              拒绝
            </el-button>
            <el-button
              type="primary"
              size="small"
              link
              @click="handleView(row)"
            >
              详情
            </el-button>
            <el-button
              type="info"
              size="small"
              link
              @click="handleToggleHot(row)"
            >
              {{ row.is_hot ? '取消热门' : '设为热门' }}
            </el-button>
            <el-button
              type="danger"
              size="small"
              link
              @click="handleDelete(row.id)"
            >
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
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @size-change="getList"
          @current-change="getList"
        />
      </div>
    </el-card>

    <!-- 评论详情对话框 -->
    <el-dialog
      v-model="dialogVisible"
      title="评论详情"
      width="800px"
    >
      <div v-if="currentComment" class="comment-detail">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="评论ID">{{ currentComment.id }}</el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag v-if="currentComment.status === 0" type="warning">待审核</el-tag>
            <el-tag v-else-if="currentComment.status === 1" type="success">已通过</el-tag>
            <el-tag v-else type="danger">已拒绝</el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="评论者">{{ currentComment.author_name }}</el-descriptions-item>
          <el-descriptions-item label="用户类型">
            <el-tag v-if="currentComment.is_guest" type="info">游客</el-tag>
            <el-tag v-else type="success">注册用户</el-tag>
          </el-descriptions-item>
          <el-descriptions-item label="评论时间" :span="2">{{ currentComment.create_time }}</el-descriptions-item>
          <el-descriptions-item label="评论内容" :span="2">
            <div class="comment-text">{{ currentComment.content }}</div>
          </el-descriptions-item>
          <el-descriptions-item label="点赞数">{{ currentComment.like_count || 0 }}</el-descriptions-item>
          <el-descriptions-item label="点踩数">{{ currentComment.dislike_count || 0 }}</el-descriptions-item>
          <el-descriptions-item label="举报数">{{ currentComment.report_count || 0 }}</el-descriptions-item>
          <el-descriptions-item label="热度分数">{{ currentComment.hot_score || 0 }}</el-descriptions-item>
          <el-descriptions-item label="IP地址" :span="2">{{ currentComment.user_ip }}</el-descriptions-item>
        </el-descriptions>
      </div>
      <template #footer>
        <el-button @click="dialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Search,
  Refresh,
  Check,
  Close,
  Delete,
  Document,
  ChatLineSquare,
  CaretTop,
  CaretBottom,
  Warning,
  Star
} from '@element-plus/icons-vue'
import {
  getCommentList,
  auditComment,
  batchAuditComments,
  deleteComment,
  batchDeleteComments,
  toggleHotComment
} from '@/api/comment'

const loading = ref(false)
const list = ref([])
const total = ref(0)
const selectedIds = ref([])
const dateRange = ref([])
const dialogVisible = ref(false)
const currentComment = ref(null)

const queryParams = reactive({
  page: 1,
  limit: 20,
  status: '',
  is_guest: '',
  has_reports: '',
  keyword: '',
  start_date: '',
  end_date: ''
})

// 获取列表
const getList = async () => {
  loading.value = true
  try {
    const { data } = await getCommentList(queryParams)
    list.value = data.data
    total.value = data.total
  } catch (error) {
    ElMessage.error('获取评论列表失败')
  } finally {
    loading.value = false
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
    limit: 20,
    status: '',
    is_guest: '',
    has_reports: '',
    keyword: '',
    start_date: '',
    end_date: ''
  })
  dateRange.value = []
  getList()
}

// 日期范围改变
const handleDateChange = (value) => {
  if (value) {
    queryParams.start_date = value[0]
    queryParams.end_date = value[1]
  } else {
    queryParams.start_date = ''
    queryParams.end_date = ''
  }
}

// 选择改变
const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

// 审核评论
const handleAudit = async (id, status) => {
  const statusText = status === 1 ? '通过' : '拒绝'
  try {
    await ElMessageBox.confirm(`确定要${statusText}这条评论吗？`, '提示', {
      type: 'warning'
    })
    await auditComment(id, { status })
    ElMessage.success(`${statusText}成功`)
    getList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(`${statusText}失败`)
    }
  }
}

// 批量审核
const handleBatchAudit = async (status) => {
  const statusText = status === 1 ? '通过' : '拒绝'
  try {
    await ElMessageBox.confirm(`确定要批量${statusText}选中的评论吗？`, '提示', {
      type: 'warning'
    })
    await batchAuditComments({ ids: selectedIds.value, status })
    ElMessage.success(`批量${statusText}成功`)
    getList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(`批量${statusText}失败`)
    }
  }
}

// 删除评论
const handleDelete = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除这条评论吗？此操作将同时删除所有子评论', '警告', {
      type: 'warning'
    })
    await deleteComment(id)
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
    await ElMessageBox.confirm('确定要批量删除选中的评论吗？此操作将同时删除所有子评论', '警告', {
      type: 'warning',
      confirmButtonText: '确定',
      cancelButtonText: '取消'
    })
    await batchDeleteComments({ ids: selectedIds.value })
    ElMessage.success('批量删除成功')
    getList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('批量删除失败')
    }
  }
}

// 切换热门
const handleToggleHot = async (row) => {
  try {
    await toggleHotComment(row.id)
    ElMessage.success(row.is_hot ? '已取消热门' : '已设为热门')
    getList()
  } catch (error) {
    ElMessage.error('操作失败')
  }
}

// 查看详情
const handleView = (row) => {
  currentComment.value = row
  dialogVisible.value = true
}

onMounted(() => {
  getList()
})
</script>

<style scoped>
.comment-list-container {
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

.comment-content {
  line-height: 1.6;
}

.comment-meta {
  margin-top: 8px;
  font-size: 12px;
  color: #909399;
  display: flex;
  gap: 16px;
}

.comment-meta span {
  display: flex;
  align-items: center;
  gap: 4px;
}

.text-secondary {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
}

.text-danger {
  color: #f56c6c;
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.comment-detail .comment-text {
  white-space: pre-wrap;
  line-height: 1.6;
}
</style>
