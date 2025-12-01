<template>
  <div class="task-records">
    <!-- 筛选 -->
    <el-form :inline="true" :model="searchForm" class="search-form">
      <el-form-item label="状态">
        <el-select v-model="searchForm.status" placeholder="全部" clearable style="width: 120px">
          <el-option label="成功" value="success" />
          <el-option label="失败" value="failed" />
          <el-option label="待处理" value="pending" />
        </el-select>
      </el-form-item>
      <el-form-item>
        <el-button type="primary" @click="fetchRecords">查询</el-button>
        <el-button @click="handleReset">重置</el-button>
      </el-form-item>
    </el-form>

    <el-table :data="recordList" v-loading="loading" border stripe max-height="600">
      <el-table-column prop="id" label="ID" width="80" />
      <el-table-column label="生成主题" min-width="200">
        <template #default="{ row }">
          {{ row.prompt }}
        </template>
      </el-table-column>
      <el-table-column label="生成标题" min-width="250">
        <template #default="{ row }">
          <el-tooltip :content="row.generated_title" placement="top">
            <div class="title-text">{{ row.generated_title || '-' }}</div>
          </el-tooltip>
        </template>
      </el-table-column>
      <el-table-column label="关联文章" width="100" align="center">
        <template #default="{ row }">
          <el-link
            v-if="row.article_id"
            type="primary"
            :href="`/article/edit/${row.article_id}`"
            target="_blank"
          >
            查看文章
          </el-link>
          <span v-else>-</span>
        </template>
      </el-table-column>
      <el-table-column label="Token数" width="100" align="center">
        <template #default="{ row }">
          {{ row.tokens_used || 0 }}
        </template>
      </el-table-column>
      <el-table-column label="状态" width="100">
        <template #default="{ row }">
          <el-tag :type="getStatusType(row.status)">
            {{ getStatusText(row.status) }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="create_time" label="生成时间" width="180" />
      <el-table-column label="操作" width="280" fixed="right">
        <template #default="{ row }">
          <el-button
            size="small"
            v-if="row.status === 'success'"
            @click="handlePreview(row)"
          >
            预览
          </el-button>
          <el-button
            size="small"
            v-if="row.status === 'failed'"
            type="info"
            @click="handleViewError(row)"
          >
            查看错误
          </el-button>
          <el-button
            size="small"
            type="warning"
            @click="handleViewDebug(row)"
          >
            调试信息
          </el-button>
          <el-button
            size="small"
            v-if="row.status === 'success' && !row.article_id"
            type="success"
            @click="handlePublish(row)"
          >
            发布为文章
          </el-button>
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
      @size-change="fetchRecords"
      @current-change="fetchRecords"
      style="margin-top: 20px; justify-content: flex-end;"
    />

    <!-- 内容预览对话框 -->
    <el-dialog
      v-model="previewDialogVisible"
      title="文章预览"
      width="70%"
      destroy-on-close
    >
      <div class="preview-content" v-if="currentRecord">
        <h2>{{ currentRecord.generated_title }}</h2>
        <div class="content-html" v-safe-html="currentRecord.generated_content"></div>
      </div>
    </el-dialog>

    <!-- 发布为文章对话框 -->
    <el-dialog
      v-model="publishDialogVisible"
      title="发布为文章"
      width="500px"
    >
      <el-form ref="publishFormRef" :model="publishForm" label-width="100px">
        <el-form-item label="文章分类">
          <el-select v-model="publishForm.category_id" placeholder="请选择分类" style="width: 100%">
            <el-option
              v-for="cat in categories"
              :key="cat.id"
              :label="cat.name"
              :value="cat.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="发布状态">
          <el-radio-group v-model="publishForm.status">
            <el-radio :label="0">草稿</el-radio>
            <el-radio :label="1">已发布</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="publishDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleConfirmPublish" :loading="publishing">
          确定
        </el-button>
      </template>
    </el-dialog>

    <!-- 调试信息对话框 -->
    <el-dialog
      v-model="debugDialogVisible"
      title="AI调试信息"
      width="80%"
      destroy-on-close
    >
      <div class="debug-content" v-if="currentRecord">
        <el-tabs type="border-card">
          <el-tab-pane label="原始主题">
            <div class="info-section">
              <pre>{{ currentRecord.prompt || '无' }}</pre>
            </div>
          </el-tab-pane>
          <el-tab-pane label="实际发送的提示词">
            <div class="info-section">
              <pre>{{ currentRecord.request_prompt || '无' }}</pre>
            </div>
          </el-tab-pane>
          <el-tab-pane label="AI原始返回">
            <div class="info-section">
              <pre>{{ formatJSON(currentRecord.raw_response) }}</pre>
            </div>
          </el-tab-pane>
          <el-tab-pane label="生成的标题">
            <div class="info-section">
              <pre>{{ currentRecord.generated_title || '无' }}</pre>
            </div>
          </el-tab-pane>
          <el-tab-pane label="生成的内容">
            <div class="info-section">
              <div class="content-html" v-safe-html="currentRecord.generated_content || '无'"></div>
            </div>
          </el-tab-pane>
          <el-tab-pane label="错误信息" v-if="currentRecord.status === 'failed'">
            <div class="info-section error-message">
              <pre>{{ currentRecord.error_message || '无' }}</pre>
            </div>
          </el-tab-pane>
        </el-tabs>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getTaskGeneratedArticles } from '@/api/ai'
import { getCategoryList } from '@/api/category'
import { createArticle } from '@/api/article'
import { vSafeHtml } from '@/utils/sanitize'

const props = defineProps({
  taskId: {
    type: Number,
    required: true
  }
})

const loading = ref(false)
const publishing = ref(false)
const previewDialogVisible = ref(false)
const publishDialogVisible = ref(false)
const debugDialogVisible = ref(false)
const publishFormRef = ref(null)

const recordList = ref([])
const currentRecord = ref(null)
const categories = ref([])

const searchForm = reactive({
  status: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

const publishForm = reactive({
  category_id: null,
  status: 0
})

// 获取分类列表
const fetchCategories = async () => {
  try {
    const res = await getCategoryList({ page_size: 999 })
    categories.value = res.data.list || []
  } catch (error) {
    console.error('获取分类失败:', error)
  }
}

// 获取生成记录
const fetchRecords = async () => {
  loading.value = true
  try {
    const res = await getTaskGeneratedArticles(props.taskId, {
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    })
    recordList.value = res.data.list
    pagination.total = res.data.total
  } catch (error) {
    ElMessage.error('获取记录失败')
  } finally {
    loading.value = false
  }
}

// 重置搜索
const handleReset = () => {
  searchForm.status = ''
  pagination.page = 1
  fetchRecords()
}

// 预览内容
const handlePreview = (row) => {
  currentRecord.value = row
  previewDialogVisible.value = true
}

// 查看错误
const handleViewError = (row) => {
  ElMessageBox.alert(row.error_message || '未知错误', '错误信息', {
    confirmButtonText: '确定',
    type: 'error'
  })
}

// 查看调试信息
const handleViewDebug = (row) => {
  currentRecord.value = row
  debugDialogVisible.value = true
}

// 格式化JSON
const formatJSON = (jsonString) => {
  if (!jsonString) return '无数据'
  try {
    const obj = typeof jsonString === 'string' ? JSON.parse(jsonString) : jsonString
    return JSON.stringify(obj, null, 2)
  } catch (e) {
    return jsonString
  }
}

// 发布为文章
const handlePublish = (row) => {
  currentRecord.value = row
  publishDialogVisible.value = true
}

// 确认发布
const handleConfirmPublish = async () => {
  if (!publishForm.category_id) {
    ElMessage.warning('请选择文章分类')
    return
  }

  publishing.value = true
  try {
    const articleData = {
      title: currentRecord.value.generated_title,
      content: currentRecord.value.generated_content,
      summary: currentRecord.value.generated_content
        ? currentRecord.value.generated_content.replace(/<[^>]+>/g, '').substring(0, 200)
        : '',
      category_id: publishForm.category_id,
      status: publishForm.status
    }

    await createArticle(articleData)

    ElMessage.success('文章发布成功')
    publishDialogVisible.value = false
    fetchRecords()
  } catch (error) {
    ElMessage.error(error.message || '发布失败')
  } finally {
    publishing.value = false
  }
}

// 获取状态类型
const getStatusType = (status) => {
  const types = {
    pending: '',
    success: 'success',
    failed: 'danger'
  }
  return types[status] || ''
}

// 获取状态文本
const getStatusText = (status) => {
  const texts = {
    pending: '待处理',
    success: '成功',
    failed: '失败'
  }
  return texts[status] || status
}

onMounted(() => {
  fetchCategories()
  fetchRecords()
})
</script>

<style scoped>
.search-form {
  margin-bottom: 20px;
}

.title-text {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.preview-content {
  padding: 20px;
}

.preview-content h2 {
  margin-bottom: 20px;
  color: #333;
}

.content-html {
  line-height: 1.8;
  font-size: 14px;
}

.content-html :deep(p) {
  margin: 10px 0;
}

.debug-content {
  padding: 10px;
}

.info-section {
  padding: 15px;
  background: #f5f7fa;
  border-radius: 4px;
  min-height: 200px;
  max-height: 600px;
  overflow-y: auto;
}

.info-section pre {
  white-space: pre-wrap;
  word-wrap: break-word;
  font-family: 'Courier New', monospace;
  font-size: 13px;
  line-height: 1.6;
  margin: 0;
  color: #333;
}

.error-message pre {
  color: #f56c6c;
}

.content-html :deep(h2),
.content-html :deep(h3) {
  margin: 15px 0 10px;
}
</style>
