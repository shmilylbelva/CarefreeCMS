<template>
  <div class="log404-container">
    <!-- 统计卡片 -->
    <el-row :gutter="20" style="margin-bottom: 20px">
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-value">{{ stats.total || 0 }}</div>
            <div class="stat-label">未修复404</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-value">{{ stats.total_hits || 0 }}</div>
            <div class="stat-label">总命中次数</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-value">{{ stats.fixed || 0 }}</div>
            <div class="stat-label">已修复</div>
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-value">{{ stats.today || 0 }}</div>
            <div class="stat-label">今日新增</div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <div class="toolbar">
      <el-form :inline="true" :model="query">
        <el-form-item label="状态">
          <el-select v-model="query.is_fixed" placeholder="全部" clearable style="width: 120px">
            <el-option label="未修复" :value="0" />
            <el-option label="已修复" :value="1" />
          </el-select>
        </el-form-item>
        <el-form-item label="排序">
          <el-select v-model="query.sort_by" style="width: 150px">
            <el-option label="按命中次数" value="hit_count" />
            <el-option label="按最后时间" value="last_hit_time" />
          </el-select>
        </el-form-item>
        <el-form-item label="URL">
          <el-input v-model="query.keyword" placeholder="搜索URL" clearable style="width: 250px" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadList">查询</el-button>
          <el-button @click="resetQuery">重置</el-button>
        </el-form-item>
      </el-form>
      <div>
        <el-button type="warning" @click="handleClean">清理旧日志</el-button>
      </div>
    </div>

    <el-table :data="list" border stripe v-loading="loading" @selection-change="handleSelectionChange">
      <el-table-column type="selection" width="55" />
      <el-table-column prop="url" label="404 URL" min-width="250" show-overflow-tooltip />
      <el-table-column prop="referer" label="来源" min-width="200" show-overflow-tooltip />
      <el-table-column prop="hit_count" label="次数" width="80" sortable />
      <el-table-column prop="last_hit_time" label="最后出现" width="160" />
      <el-table-column label="状态" width="100">
        <template #default="{ row }">
          <el-tag :type="row.is_fixed ? 'success' : 'danger'" size="small">
            {{ row.is_fixed ? '已修复' : '未修复' }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="240" fixed="right">
        <template #default="{ row }">
          <el-button v-if="!row.is_fixed" link type="primary" size="small" @click="handleCreateRedirect(row)">
            创建重定向
          </el-button>
          <el-button v-if="!row.is_fixed" link type="warning" size="small" @click="handleIgnore(row)">
            忽略
          </el-button>
          <el-button link type="danger" size="small" @click="handleDelete(row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>

    <div style="margin-top: 10px">
      <el-button @click="handleBatchDelete" :disabled="selectedIds.length === 0">批量删除</el-button>
      <el-button @click="handleBatchIgnore" :disabled="selectedIds.length === 0">批量忽略</el-button>
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

    <!-- 创建重定向对话框 -->
    <el-dialog v-model="redirectDialogVisible" title="创建重定向规则" width="550px">
      <el-form :model="redirectForm" label-width="100px">
        <el-form-item label="源URL">
          <el-input v-model="redirectForm.from_url" disabled />
        </el-form-item>
        <el-form-item label="目标URL" required>
          <el-input v-model="redirectForm.to_url" placeholder="输入重定向的目标URL" />
        </el-form-item>
        <el-form-item label="类型">
          <el-radio-group v-model="redirectForm.redirect_type">
            <el-radio :label="301">301 永久</el-radio>
            <el-radio :label="302">302 临时</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="redirectDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="submitRedirect" :loading="submitting">创建</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  get404LogList,
  delete404Log,
  batchDelete404Logs,
  ignore404,
  batchMark404Fixed,
  createRedirectFrom404,
  clean404Logs,
  get404Statistics
} from '@/api/seo404Log'

const loading = ref(false)
const submitting = ref(false)
const list = ref([])
const total = ref(0)
const selectedIds = ref([])
const stats = ref({})

const query = reactive({
  page: 1,
  per_page: 15,
  keyword: '',
  is_fixed: '',
  sort_by: 'hit_count'
})

const redirectDialogVisible = ref(false)
const redirectForm = reactive({
  log_id: null,
  from_url: '',
  to_url: '',
  redirect_type: 301
})

const loadList = async () => {
  loading.value = true
  try {
    const res = await get404LogList(query)
    list.value = res.data.data
    total.value = res.data.total
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.value = false
  }
}

const loadStatistics = async () => {
  try {
    const res = await get404Statistics()
    stats.value = res.data.stats
  } catch (error) {
    console.error('加载统计失败', error)
  }
}

const resetQuery = () => {
  query.keyword = ''
  query.is_fixed = ''
  query.sort_by = 'hit_count'
  query.page = 1
  loadList()
}

const handleCreateRedirect = (row) => {
  redirectForm.log_id = row.id
  redirectForm.from_url = row.url
  redirectForm.to_url = ''
  redirectForm.redirect_type = 301
  redirectDialogVisible.value = true
}

const submitRedirect = async () => {
  if (!redirectForm.to_url) {
    ElMessage.warning('请输入目标URL')
    return
  }

  submitting.value = true
  try {
    await createRedirectFrom404(redirectForm.log_id, redirectForm.to_url, redirectForm.redirect_type)
    ElMessage.success('重定向规则创建成功')
    redirectDialogVisible.value = false
    loadList()
    loadStatistics()
  } catch (error) {
    ElMessage.error(error.message || '创建失败')
  } finally {
    submitting.value = false
  }
}

const handleIgnore = (row) => {
  ElMessageBox.confirm('确定要忽略这个404错误吗？', '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await ignore404(row.id)
      ElMessage.success('已忽略')
      loadList()
      loadStatistics()
    } catch (error) {
      ElMessage.error(error.message || '操作失败')
    }
  })
}

const handleDelete = (row) => {
  ElMessageBox.confirm('确定要删除这条日志吗？', '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await delete404Log(row.id)
      ElMessage.success('删除成功')
      loadList()
      loadStatistics()
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  })
}

const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

const handleBatchDelete = () => {
  ElMessageBox.confirm(`确定要删除选中的 ${selectedIds.value.length} 条日志吗？`, '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await batchDelete404Logs(selectedIds.value)
      ElMessage.success('批量删除成功')
      loadList()
      loadStatistics()
    } catch (error) {
      ElMessage.error(error.message || '批量删除失败')
    }
  })
}

const handleBatchIgnore = () => {
  ElMessageBox.confirm(`确定要忽略选中的 ${selectedIds.value.length} 条404错误吗？`, '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await batchMark404Fixed(selectedIds.value, 'ignored')
      ElMessage.success('批量忽略成功')
      loadList()
      loadStatistics()
    } catch (error) {
      ElMessage.error(error.message || '批量忽略失败')
    }
  })
}

const handleClean = () => {
  ElMessageBox.prompt('清理多少天前的已修复日志？', '清理旧日志', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    inputPattern: /^\d+$/,
    inputErrorMessage: '请输入有效的天数',
    inputValue: '90'
  }).then(async ({ value }) => {
    try {
      const res = await clean404Logs(parseInt(value))
      ElMessage.success(`已清理 ${res.data.count} 条旧日志`)
      loadList()
      loadStatistics()
    } catch (error) {
      ElMessage.error(error.message || '清理失败')
    }
  })
}

onMounted(() => {
  loadList()
  loadStatistics()
})
</script>

<style scoped>
.log404-container {
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

.stat-card {
  text-align: center;
}

.stat-value {
  font-size: 32px;
  font-weight: bold;
  color: #409eff;
}

.stat-label {
  font-size: 14px;
  color: #909399;
  margin-top: 10px;
}
</style>
