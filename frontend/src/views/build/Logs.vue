<template>
  <div class="build-logs">
    <el-card>
      <template #header>
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <h3>生成日志</h3>
          <div>
            <el-button
              v-if="selectedIds.length > 0"
              type="danger"
              size="small"
              @click="handleBatchDelete"
              style="margin-right: 10px;"
            >
              批量删除 ({{ selectedIds.length }})
            </el-button>
            <el-button type="danger" size="small" @click="handleClear" style="margin-right: 10px;">清空日志</el-button>
            <el-button size="small" @click="fetchLogs">刷新</el-button>
          </div>
        </div>
      </template>

      <!-- 筛选 -->
      <el-form :inline="true" style="margin-bottom: 15px;">
        <el-form-item label="类型">
          <el-select v-model="filterType" placeholder="全部" clearable style="width: 140px;">
            <el-option label="首页" value="index" />
            <el-option label="文章列表" value="articles" />
            <el-option label="文章" value="article" />
            <el-option label="分类" value="category" />
            <el-option label="页面" value="page" />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="filterStatus" placeholder="全部" clearable style="width: 120px;">
            <el-option label="成功" :value="1" />
            <el-option label="失败" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="fetchLogs">查询</el-button>
        </el-form-item>
      </el-form>

      <!-- 日志列表 -->
      <el-table :data="logs" v-loading="loading" border stripe size="small" @selection-change="handleSelectionChange">
        <el-table-column type="selection" width="55" />
        <el-table-column label="类型" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.build_scope === 'index'" type="primary" size="small">首页</el-tag>
            <el-tag v-else-if="row.build_scope === 'article'" type="success" size="small">文章</el-tag>
            <el-tag v-else-if="row.build_scope === 'articles'" type="success" size="small">文章列表</el-tag>
            <el-tag v-else-if="row.build_scope === 'category'" type="warning" size="small">分类</el-tag>
            <el-tag v-else-if="row.build_scope === 'page'" type="info" size="small">页面</el-tag>
            <el-tag v-else type="info" size="small">{{ row.build_scope }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="target_id" label="目标ID" width="80" />
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag v-if="row.status === 1" type="success" size="small">成功</el-tag>
            <el-tag v-else-if="row.status === 0" type="danger" size="small">失败</el-tag>
            <el-tag v-else type="warning" size="small">部分成功</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="生成方式" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.build_type === 'manual'" size="small">手动</el-tag>
            <el-tag v-else-if="row.build_type === 'auto'" type="success" size="small">自动</el-tag>
            <el-tag v-else type="info" size="small">定时</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="error_msg" label="消息" min-width="200" show-overflow-tooltip />
        <el-table-column prop="create_time" label="生成时间" width="180" />
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :total="pagination.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="fetchLogs"
        @current-change="fetchLogs"
        style="margin-top: 15px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 清空日志对话框 -->
    <el-dialog v-model="clearDialogVisible" title="清空日志" width="400px">
      <el-form :model="clearForm" label-width="120px">
        <el-form-item label="保留天数">
          <el-input-number v-model="clearForm.days" :min="7" :max="365" />
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            将删除{{clearForm.days}}天前的所有日志，至少保留7天
          </div>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="clearDialogVisible = false">取消</el-button>
        <el-button type="danger" @click="handleConfirmClear" :loading="clearing">确定清空</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getBuildLogs } from '@/api/build'
import request from '@/api/request'

const loading = ref(false)
const clearing = ref(false)
const logs = ref([])
const filterType = ref('')
const filterStatus = ref('')
const selectedIds = ref([])
const clearDialogVisible = ref(false)

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

const clearForm = reactive({
  days: 30
})

// 获取日志
const fetchLogs = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      pageSize: pagination.pageSize,
      type: filterType.value,
      status: filterStatus.value
    }
    const res = await getBuildLogs(params)
    logs.value = res.data.list || []
    pagination.total = res.data.total || 0
  } catch (error) {
    ElMessage.error('获取日志失败')
  } finally {
    loading.value = false
  }
}

// 选择变化
const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

// 批量删除
const handleBatchDelete = async () => {
  try {
    await ElMessageBox.confirm(`确定要删除选中的 ${selectedIds.value.length} 条日志吗？`, '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    await request({
      url: '/build/batch-delete-logs',
      method: 'post',
      data: { ids: selectedIds.value }
    })

    ElMessage.success('删除成功')
    selectedIds.value = []
    fetchLogs()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

// 清空日志
const handleClear = () => {
  clearDialogVisible.value = true
}

// 确认清空
const handleConfirmClear = async () => {
  try {
    await ElMessageBox.confirm(
      `确定要清空${clearForm.days}天前的所有日志吗？此操作不可恢复！`,
      '警告',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    clearing.value = true
    const res = await request({
      url: '/build/clear-logs',
      method: 'post',
      data: { days: clearForm.days }
    })

    ElMessage.success(res.message || '清空成功')
    clearDialogVisible.value = false
    fetchLogs()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '清空失败')
    }
  } finally {
    clearing.value = false
  }
}

onMounted(() => {
  fetchLogs()
})
</script>

<style scoped>
.build-logs h3 {
  margin: 0;
}
</style>
