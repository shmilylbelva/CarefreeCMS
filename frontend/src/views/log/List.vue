<template>
  <div class="operation-log-list">
    <el-card>
      <!-- 批量操作栏 -->
      <div v-if="selectedIds.length > 0" style="margin-bottom: 15px;">
        <el-button
          type="danger"
          @click="handleBatchDelete"
        >
          批量删除 ({{ selectedIds.length }})
        </el-button>
      </div>

      <!-- 搜索栏 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="用户名">
          <el-input v-model="searchForm.username" placeholder="请输入用户名" clearable style="width: 150px;" />
        </el-form-item>
        <el-form-item label="模块">
          <el-select v-model="searchForm.module" placeholder="请选择模块" clearable style="width: 150px;">
            <el-option
              v-for="module in modules"
              :key="module.value"
              :label="module.label"
              :value="module.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="操作类型">
          <el-select v-model="searchForm.action" placeholder="请选择操作类型" clearable style="width: 150px;">
            <el-option
              v-for="action in actions"
              :key="action.value"
              :label="action.label"
              :value="action.value"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable style="width: 120px;">
            <el-option label="成功" :value="1" />
            <el-option label="失败" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="日期范围">
          <el-date-picker
            v-model="dateRange"
            type="daterange"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            value-format="YYYY-MM-DD"
            style="width: 240px;"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
          <el-button type="danger" @click="handleClear">清空日志</el-button>
        </el-form-item>
      </el-form>

      <!-- 数据表格 -->
      <el-table :data="tableData" v-loading="loading" border stripe @selection-change="handleSelectionChange">
        <el-table-column type="selection" width="55" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="操作用户" width="120" />
        <el-table-column prop="module" label="模块" width="100">
          <template #default="{ row }">
            {{ getModuleName(row.module) }}
          </template>
        </el-table-column>
        <el-table-column prop="action" label="操作类型" width="100">
          <template #default="{ row }">
            {{ getActionName(row.action) }}
          </template>
        </el-table-column>
        <el-table-column prop="description" label="操作描述" min-width="200" show-overflow-tooltip />
        <el-table-column prop="ip" label="IP地址" width="140" />
        <el-table-column prop="status" label="状态" width="80">
          <template #default="{ row }">
            <el-tag :type="row.status === 1 ? 'success' : 'danger'">
              {{ row.status === 1 ? '成功' : '失败' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="execute_time" label="执行时间" width="100">
          <template #default="{ row }">
            {{ row.execute_time }}ms
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="操作时间" width="180" />
        <el-table-column label="操作" width="100" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" size="small" @click="handleView(row)">详情</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :total="pagination.total"
        :page-sizes="[20, 50, 100, 200]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handlePageChange"
        style="margin-top: 20px; justify-content: flex-end"
      />
    </el-card>

    <!-- 详情对话框 -->
    <el-dialog v-model="dialogVisible" title="日志详情" width="900px">
      <el-tabs v-if="currentLog" type="border-card">
        <el-tab-pane label="基本信息">
          <el-descriptions :column="1" border>
            <el-descriptions-item label="ID">{{ currentLog.id }}</el-descriptions-item>
            <el-descriptions-item label="操作用户">{{ currentLog.username || '未知' }}</el-descriptions-item>
            <el-descriptions-item label="模块">{{ getModuleName(currentLog.module) }}</el-descriptions-item>
            <el-descriptions-item label="操作类型">{{ getActionName(currentLog.action) }}</el-descriptions-item>
            <el-descriptions-item label="操作描述">{{ currentLog.description }}</el-descriptions-item>
            <el-descriptions-item label="IP地址">{{ currentLog.ip }}</el-descriptions-item>
            <el-descriptions-item label="用户代理">{{ currentLog.user_agent }}</el-descriptions-item>
            <el-descriptions-item label="请求方法">{{ currentLog.request_method }}</el-descriptions-item>
            <el-descriptions-item label="请求URL">{{ currentLog.request_url }}</el-descriptions-item>
            <el-descriptions-item label="请求参数">
              <pre style="max-height: 200px; overflow: auto; margin: 0;">{{ formatParams(currentLog.request_params) }}</pre>
            </el-descriptions-item>
            <el-descriptions-item label="状态">
              <el-tag :type="currentLog.status === 1 ? 'success' : 'danger'">
                {{ currentLog.status === 1 ? '成功' : '失败' }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="错误信息" v-if="currentLog.error_msg">{{ currentLog.error_msg }}</el-descriptions-item>
            <el-descriptions-item label="执行时间">{{ currentLog.execute_time }}ms</el-descriptions-item>
            <el-descriptions-item label="操作时间">{{ currentLog.create_time }}</el-descriptions-item>
          </el-descriptions>
        </el-tab-pane>

        <el-tab-pane label="变更对比" v-if="hasChanges(currentLog)">
          <el-alert type="info" :closable="false" style="margin-bottom: 15px;">
            <template #title>
              变更字段：{{ currentLog.changed_fields || '无' }}
            </template>
          </el-alert>
          <el-table :data="getChanges(currentLog)" border stripe>
            <el-table-column prop="field" label="字段名" width="150" />
            <el-table-column label="修改前" min-width="250">
              <template #default="{ row }">
                <div style="word-break: break-all; white-space: pre-wrap;">{{ formatValue(row.oldValue) }}</div>
              </template>
            </el-table-column>
            <el-table-column label="修改后" min-width="250">
              <template #default="{ row }">
                <div style="word-break: break-all; white-space: pre-wrap;">{{ formatValue(row.newValue) }}</div>
              </template>
            </el-table-column>
          </el-table>
          <div v-if="!hasChanges(currentLog)" style="text-align: center; padding: 40px; color: #909399;">
            <el-icon size="48"><DocumentCopy /></el-icon>
            <p style="margin-top: 10px;">暂无变更记录</p>
          </div>
        </el-tab-pane>
      </el-tabs>
    </el-dialog>

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
import { DocumentCopy } from '@element-plus/icons-vue'
import {
  getOperationLogs,
  getModules,
  getActions,
  clearLogs
} from '@/api/operationLog'
import request from '@/api/request'

const loading = ref(false)
const clearing = ref(false)
const tableData = ref([])
const modules = ref([])
const actions = ref([])
const dialogVisible = ref(false)
const clearDialogVisible = ref(false)
const currentLog = ref(null)
const dateRange = ref([])
const selectedIds = ref([])

const searchForm = reactive({
  username: '',
  module: '',
  action: '',
  status: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 20,
  total: 0
})

const clearForm = reactive({
  days: 30
})

// 获取模块名称
const getModuleName = (module) => {
  if (!Array.isArray(modules.value)) {
    console.warn('modules.value 不是数组:', modules.value)
    return module
  }
  const item = modules.value.find(m => m.value === module)
  return item ? item.label : module
}

// 获取操作类型名称
const getActionName = (action) => {
  if (!Array.isArray(actions.value)) {
    console.warn('actions.value 不是数组:', actions.value)
    return action
  }
  const item = actions.value.find(a => a.value === action)
  return item ? item.label : action
}

// 格式化参数
const formatParams = (params) => {
  if (!params) return ''
  try {
    return JSON.stringify(JSON.parse(params), null, 2)
  } catch {
    return params
  }
}

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      username: searchForm.username,
      module: searchForm.module,
      action: searchForm.action,
      status: searchForm.status
    }

    if (dateRange.value && dateRange.value.length === 2) {
      params.start_date = dateRange.value[0]
      params.end_date = dateRange.value[1]
    }

    const res = await getOperationLogs(params)
    console.log('API响应:', res)
    console.log('列表数据:', res.data)
    tableData.value = res.data.list || []
    pagination.total = res.data.total || 0
    console.log('tableData:', tableData.value)
  } catch (error) {
    console.error('加载数据失败:', error)
    // 不要在catch中显示错误消息，避免死循环
    tableData.value = []
    pagination.total = 0
  } finally {
    loading.value = false
  }
}

// 加载模块和操作类型
const loadOptions = async () => {
  try {
    const [modulesRes, actionsRes] = await Promise.all([
      getModules(),
      getActions()
    ])
    console.log('modulesRes:', modulesRes)
    console.log('modulesRes.data:', modulesRes.data)
    console.log('是否为数组:', Array.isArray(modulesRes.data))

    // 确保是数组
    modules.value = Array.isArray(modulesRes.data) ? modulesRes.data : []
    actions.value = Array.isArray(actionsRes.data) ? actionsRes.data : []
  } catch (error) {
    console.error('加载选项失败:', error)
    // 确保即使失败也设置为空数组
    modules.value = []
    actions.value = []
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  loadData()
}

// 重置
const handleReset = () => {
  searchForm.username = ''
  searchForm.module = ''
  searchForm.action = ''
  searchForm.status = ''
  dateRange.value = []
  pagination.page = 1
  loadData()
}

// 查看详情
const handleView = (row) => {
  currentLog.value = row
  dialogVisible.value = true
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
    const res = await clearLogs(clearForm.days)
    ElMessage.success(res.message || '清空成功')
    clearDialogVisible.value = false
    loadData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '清空失败')
    }
  } finally {
    clearing.value = false
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
      url: '/operation-log/batch-delete',
      method: 'post',
      data: { ids: selectedIds.value }
    })

    ElMessage.success('删除成功')
    selectedIds.value = []
    loadData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

// 分页
const handlePageChange = (page) => {
  pagination.page = page
  loadData()
}

const handleSizeChange = (size) => {
  pagination.pageSize = size
  pagination.page = 1
  loadData()
}

// 检查是否有变更
const hasChanges = (log) => {
  return log && (log.old_values || log.new_values || log.changed_fields)
}

// 获取变更列表
const getChanges = (log) => {
  if (!log || !log.old_values || !log.new_values) {
    return []
  }

  try {
    const oldValues = JSON.parse(log.old_values)
    const newValues = JSON.parse(log.new_values)
    const changes = []

    // 字段名称映射
    const fieldNames = {
      'title': '标题',
      'category_id': '分类ID',
      'status': '状态',
      'is_top': '置顶',
      'is_recommend': '推荐',
      'is_hot': '热门',
      'summary': '摘要',
      'seo_keywords': 'SEO关键词',
      'seo_description': 'SEO描述'
    }

    // 状态值映射
    const statusMap = {
      0: '草稿',
      1: '已发布',
      2: '待审核',
      3: '已下线'
    }

    const booleanMap = {
      0: '否',
      1: '是'
    }

    for (const key in oldValues) {
      const field = fieldNames[key] || key
      let oldValue = oldValues[key]
      let newValue = newValues[key]

      // 格式化值
      if (key === 'status') {
        oldValue = statusMap[oldValue] || oldValue
        newValue = statusMap[newValue] || newValue
      } else if (['is_top', 'is_recommend', 'is_hot'].includes(key)) {
        oldValue = booleanMap[oldValue] || oldValue
        newValue = booleanMap[newValue] || newValue
      }

      changes.push({
        field,
        oldValue,
        newValue
      })
    }

    return changes
  } catch (e) {
    console.error('解析变更数据失败:', e)
    return []
  }
}

// 格式化值
const formatValue = (value) => {
  if (value === null || value === undefined) {
    return '(空)'
  }
  if (value === '') {
    return '(空字符串)'
  }
  return value
}

onMounted(async () => {
  await loadOptions()  // 先等待选项加载完成
  await loadData()     // 再加载数据
})
</script>

<style scoped>
.operation-log-list {
  padding: 20px;
}

.search-form {
  margin-bottom: 20px;
}
</style>
