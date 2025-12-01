<template>
  <div class="comment-reports-container">
    <el-card class="filter-card">
      <el-form :inline="true" :model="queryParams" class="filter-form">
        <el-form-item label="处理状态">
          <el-select v-model="queryParams.status" placeholder="全部状态" clearable style="width: 120px">
            <el-option label="待处理" :value="0" />
            <el-option label="已处理" :value="1" />
            <el-option label="已忽略" :value="2" />
          </el-select>
        </el-form-item>

        <el-form-item label="举报原因">
          <el-select v-model="queryParams.reason" placeholder="全部原因" clearable style="width: 150px">
            <el-option label="垃圾信息" value="spam" />
            <el-option label="辱骂攻击" value="abuse" />
            <el-option label="色情低俗" value="porn" />
            <el-option label="广告信息" value="ad" />
            <el-option label="其他原因" value="other" />
          </el-select>
        </el-form-item>

        <el-form-item label="评论ID">
          <el-input
            v-model.number="queryParams.comment_id"
            placeholder="评论ID"
            clearable
            style="width: 150px"
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
          <span>举报列表</span>
          <div class="header-actions">
            <el-button
              type="success"
              :disabled="selectedIds.length === 0"
              @click="handleBatchHandle('deleted')"
            >
              <el-icon><Check /></el-icon>
              批量删除评论
            </el-button>
            <el-button
              type="primary"
              :disabled="selectedIds.length === 0"
              @click="handleBatchHandle('approved')"
            >
              <el-icon><CircleCheck /></el-icon>
              批量标记误报
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

        <el-table-column label="举报ID" prop="id" width="80" />

        <el-table-column label="被举报评论" min-width="300">
          <template #default="{ row }">
            <div v-if="row.comment" class="comment-preview">
              <div class="comment-content">{{ row.comment.content }}</div>
              <div class="comment-info">
                <el-tag size="small">ID: {{ row.comment_id }}</el-tag>
                <span>作者: {{ row.comment.author_name || '匿名' }}</span>
              </div>
            </div>
            <div v-else class="text-secondary">评论已删除</div>
          </template>
        </el-table-column>

        <el-table-column label="举报原因" width="120">
          <template #default="{ row }">
            <el-tag :type="getReasonType(row.reason)">
              {{ row.reason_text }}
            </el-tag>
            <div v-if="row.reason_detail" class="reason-detail">
              {{ row.reason_detail }}
            </div>
          </template>
        </el-table-column>

        <el-table-column label="举报人" width="150">
          <template #default="{ row }">
            <div v-if="row.reporter">
              <div>{{ row.reporter.nickname || row.reporter.username }}</div>
              <div class="text-secondary">ID: {{ row.reporter_id }}</div>
            </div>
            <div v-else>
              <el-tag type="info" size="small">游客</el-tag>
              <div class="text-secondary">{{ row.reporter_email || row.reporter_ip }}</div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="处理状态" width="120">
          <template #default="{ row }">
            <el-tag v-if="row.status === 0" type="warning">待处理</el-tag>
            <el-tag v-else-if="row.status === 1" type="success">已处理</el-tag>
            <el-tag v-else type="info">已忽略</el-tag>
            <div v-if="row.handle_result" class="text-secondary" style="margin-top: 4px">
              {{ row.handle_result_text }}
            </div>
          </template>
        </el-table-column>

        <el-table-column label="举报时间" width="160">
          <template #default="{ row }">
            {{ row.create_time }}
          </template>
        </el-table-column>

        <el-table-column label="操作" width="220" fixed="right">
          <template #default="{ row }">
            <template v-if="row.status === 0">
              <el-button
                type="danger"
                size="small"
                link
                @click="handleReportAction(row.id, 'deleted')"
              >
                删除评论
              </el-button>
              <el-button
                type="success"
                size="small"
                link
                @click="handleReportAction(row.id, 'approved')"
              >
                误报
              </el-button>
              <el-button
                type="info"
                size="small"
                link
                @click="handleIgnore(row.id)"
              >
                忽略
              </el-button>
            </template>
            <el-button
              type="primary"
              size="small"
              link
              @click="handleView(row)"
            >
              详情
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

    <!-- 举报详情对话框 -->
    <el-dialog
      v-model="dialogVisible"
      title="举报详情"
      width="800px"
    >
      <div v-if="currentReport">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="举报ID">{{ currentReport.id }}</el-descriptions-item>
          <el-descriptions-item label="状态">
            <el-tag v-if="currentReport.status === 0" type="warning">待处理</el-tag>
            <el-tag v-else-if="currentReport.status === 1" type="success">已处理</el-tag>
            <el-tag v-else type="info">已忽略</el-tag>
          </el-descriptions-item>

          <el-descriptions-item label="举报原因">
            {{ currentReport.reason_text }}
          </el-descriptions-item>
          <el-descriptions-item label="举报时间">
            {{ currentReport.create_time }}
          </el-descriptions-item>

          <el-descriptions-item label="详细说明" :span="2">
            {{ currentReport.reason_detail || '无' }}
          </el-descriptions-item>

          <el-descriptions-item label="被举报评论" :span="2">
            <div v-if="currentReport.comment" class="comment-detail-box">
              <div><strong>评论内容:</strong> {{ currentReport.comment.content }}</div>
              <div><strong>评论作者:</strong> {{ currentReport.comment.author_name || '匿名' }}</div>
              <div><strong>评论时间:</strong> {{ currentReport.comment.create_time }}</div>
            </div>
            <div v-else class="text-danger">评论已删除</div>
          </el-descriptions-item>

          <el-descriptions-item label="举报人信息" :span="2">
            <div v-if="currentReport.reporter">
              <div>用户: {{ currentReport.reporter.nickname || currentReport.reporter.username }}</div>
              <div>ID: {{ currentReport.reporter_id }}</div>
            </div>
            <div v-else>
              <div>游客举报</div>
              <div>邮箱: {{ currentReport.reporter_email || '-' }}</div>
              <div>IP: {{ currentReport.reporter_ip }}</div>
            </div>
          </el-descriptions-item>

          <el-descriptions-item v-if="currentReport.status !== 0" label="处理结果">
            {{ currentReport.handle_result_text || '-' }}
          </el-descriptions-item>
          <el-descriptions-item v-if="currentReport.status !== 0" label="处理时间">
            {{ currentReport.handle_time || '-' }}
          </el-descriptions-item>

          <el-descriptions-item v-if="currentReport.handler" label="处理人" :span="2">
            {{ currentReport.handler.username }} (ID: {{ currentReport.handler_id }})
          </el-descriptions-item>

          <el-descriptions-item v-if="currentReport.handle_remark" label="处理备注" :span="2">
            {{ currentReport.handle_remark }}
          </el-descriptions-item>
        </el-descriptions>
      </div>

      <template #footer>
        <el-button @click="dialogVisible = false">关闭</el-button>
      </template>
    </el-dialog>

    <!-- 处理举报对话框 -->
    <el-dialog
      v-model="handleDialogVisible"
      title="处理举报"
      width="500px"
    >
      <el-form :model="handleForm" label-width="80px">
        <el-form-item label="处理结果">
          <el-radio-group v-model="handleForm.result">
            <el-radio value="deleted">删除评论（举报属实）</el-radio>
            <el-radio value="approved">误报（评论正常）</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="处理备注">
          <el-input
            v-model="handleForm.remark"
            type="textarea"
            :rows="4"
            placeholder="可选填写处理说明"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="handleDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="submitHandle">确定</el-button>
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
  CircleCheck
} from '@element-plus/icons-vue'
import {
  getReportList,
  handleReport,
  ignoreReport,
  batchHandleReports
} from '@/api/comment'

const loading = ref(false)
const list = ref([])
const total = ref(0)
const selectedIds = ref([])
const dialogVisible = ref(false)
const handleDialogVisible = ref(false)
const currentReport = ref(null)
const currentHandleId = ref(null)

const queryParams = reactive({
  page: 1,
  limit: 20,
  status: '',
  reason: '',
  comment_id: ''
})

const handleForm = reactive({
  result: 'deleted',
  remark: ''
})

// 获取列表
const getList = async () => {
  loading.value = true
  try {
    const { data } = await getReportList(queryParams)
    list.value = data.data
    total.value = data.total
  } catch (error) {
    ElMessage.error('获取举报列表失败')
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
    reason: '',
    comment_id: ''
  })
  getList()
}

// 选择改变
const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

// 获取举报原因类型
const getReasonType = (reason) => {
  const typeMap = {
    'spam': 'warning',
    'abuse': 'danger',
    'porn': 'danger',
    'ad': 'warning',
    'other': 'info'
  }
  return typeMap[reason] || 'info'
}

// 打开处理举报对话框
const handleReportAction = async (id, result) => {
  currentHandleId.value = id
  handleForm.result = result
  handleForm.remark = ''
  handleDialogVisible.value = true
}

// 提交处理
const submitHandle = async () => {
  try {
    await handleReport(currentHandleId.value, handleForm)
    ElMessage.success('处理成功')
    handleDialogVisible.value = false
    getList()
  } catch (error) {
    ElMessage.error('处理失败')
  }
}

// 忽略举报
const handleIgnore = async (id) => {
  try {
    await ElMessageBox.confirm('确定要忽略这条举报吗？', '提示', {
      type: 'warning'
    })
    await ignoreReport(id)
    ElMessage.success('已忽略')
    getList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('操作失败')
    }
  }
}

// 批量处理
const handleBatchHandle = async (result) => {
  const resultText = result === 'deleted' ? '删除评论' : '标记为误报'
  try {
    await ElMessageBox.confirm(`确定要批量${resultText}吗？`, '提示', {
      type: 'warning'
    })
    await batchHandleReports({ ids: selectedIds.value, result })
    ElMessage.success(`批量${resultText}成功`)
    getList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(`批量${resultText}失败`)
    }
  }
}

// 查看详情
const handleView = (row) => {
  currentReport.value = row
  dialogVisible.value = true
}

onMounted(() => {
  getList()
})
</script>

<style scoped>
.comment-reports-container {
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

.comment-preview {
  line-height: 1.6;
}

.comment-content {
  margin-bottom: 8px;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.comment-info {
  display: flex;
  gap: 12px;
  align-items: center;
  font-size: 12px;
  color: #909399;
}

.reason-detail {
  margin-top: 4px;
  font-size: 12px;
  color: #606266;
}

.text-secondary {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
}

.text-danger {
  color: #f56c6c;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.comment-detail-box {
  padding: 12px;
  background-color: #f5f7fa;
  border-radius: 4px;
  line-height: 1.8;
}

.comment-detail-box div {
  margin-bottom: 8px;
}

.comment-detail-box div:last-child {
  margin-bottom: 0;
}
</style>
