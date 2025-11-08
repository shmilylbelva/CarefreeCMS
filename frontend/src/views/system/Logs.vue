<template>
  <div class="logs-management">
    <el-tabs v-model="activeTab" @tab-change="handleTabChange">
      <!-- 系统日志 -->
      <el-tab-pane label="系统日志" name="system">
        <el-card shadow="never">
          <template #header>
            <div class="card-header">
              <el-form :inline="true" :model="systemFilters">
                <el-form-item label="级别">
                  <el-select v-model="systemFilters.level" placeholder="全部" clearable style="width: 120px">
                    <el-option label="调试" value="debug" />
                    <el-option label="信息" value="info" />
                    <el-option label="警告" value="warning" />
                    <el-option label="错误" value="error" />
                    <el-option label="严重" value="critical" />
                  </el-select>
                </el-form-item>
                <el-form-item label="分类">
                  <el-select v-model="systemFilters.category" placeholder="全部" clearable style="width: 120px">
                    <el-option label="系统" value="system" />
                    <el-option label="数据库" value="database" />
                    <el-option label="API" value="api" />
                    <el-option label="认证" value="auth" />
                    <el-option label="安全" value="security" />
                    <el-option label="操作" value="operation" />
                  </el-select>
                </el-form-item>
                <el-form-item label="关键词">
                  <el-input v-model="systemFilters.keyword" placeholder="搜索消息" style="width: 200px" />
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="loadSystemLogs" :icon="Search">搜索</el-button>
                  <el-button @click="resetSystemFilters">重置</el-button>
                  <el-button type="danger" @click="handleBatchDelete('system')">批量删除</el-button>
                  <el-button type="warning" @click="showCleanDialog('system')">清理</el-button>
                </el-form-item>
              </el-form>
            </div>
          </template>

          <el-table :data="systemLogs" border stripe @selection-change="handleSystemSelection">
            <el-table-column type="selection" width="55" />
            <el-table-column prop="level" label="级别" width="90">
              <template #default="{ row }">
                <el-tag :type="getLevelType(row.level)">{{ getLevelLabel(row.level) }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="category" label="分类" width="100" />
            <el-table-column prop="message" label="消息" min-width="300" show-overflow-tooltip />
            <el-table-column prop="ip" label="IP" width="130" />
            <el-table-column prop="method" label="方法" width="80" />
            <el-table-column prop="create_time" label="时间" width="180" />
            <el-table-column label="操作" width="150" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" size="small" @click="handleViewDetail(row, 'system')">详情</el-button>
                <el-button type="danger" size="small" @click="handleDelete(row.id, 'system')">删除</el-button>
              </template>
            </el-table-column>
          </el-table>

          <el-pagination
            v-model:current-page="systemPagination.page"
            v-model:page-size="systemPagination.per_page"
            :total="systemPagination.total"
            @current-change="loadSystemLogs"
            layout="total, prev, pager, next"
            class="mt-4"
          />
        </el-card>
      </el-tab-pane>

      <!-- 登录日志 -->
      <el-tab-pane label="登录日志" name="login">
        <el-card shadow="never">
          <template #header>
            <div class="card-header">
              <el-form :inline="true" :model="loginFilters">
                <el-form-item label="用户名">
                  <el-input v-model="loginFilters.username" placeholder="搜索用户名" style="width: 150px" />
                </el-form-item>
                <el-form-item label="状态">
                  <el-select v-model="loginFilters.status" placeholder="全部" clearable style="width: 120px">
                    <el-option label="成功" value="success" />
                    <el-option label="失败" value="failed" />
                  </el-select>
                </el-form-item>
                <el-form-item label="IP">
                  <el-input v-model="loginFilters.ip" placeholder="搜索IP" style="width: 150px" />
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="loadLoginLogs" :icon="Search">搜索</el-button>
                  <el-button @click="resetLoginFilters">重置</el-button>
                  <el-button type="danger" @click="handleBatchDelete('login')">批量删除</el-button>
                  <el-button type="info" @click="loadLoginStats">查看统计</el-button>
                </el-form-item>
              </el-form>
            </div>
          </template>

          <el-table :data="loginLogs" border stripe @selection-change="handleLoginSelection">
            <el-table-column type="selection" width="55" />
            <el-table-column prop="username" label="用户名" width="150" />
            <el-table-column prop="ip" label="IP地址" width="130" />
            <el-table-column prop="location" label="地理位置" width="120" />
            <el-table-column prop="status" label="状态" width="90">
              <template #default="{ row }">
                <el-tag :type="row.status === 'success' ? 'success' : 'danger'">
                  {{ row.status === 'success' ? '成功' : '失败' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="fail_reason" label="失败原因" min-width="200" show-overflow-tooltip />
            <el-table-column prop="login_time" label="登录时间" width="180" />
            <el-table-column label="操作" width="100" fixed="right">
              <template #default="{ row }">
                <el-button type="danger" size="small" @click="handleDelete(row.id, 'login')">删除</el-button>
              </template>
            </el-table-column>
          </el-table>

          <el-pagination
            v-model:current-page="loginPagination.page"
            v-model:page-size="loginPagination.per_page"
            :total="loginPagination.total"
            @current-change="loadLoginLogs"
            layout="total, prev, pager, next"
            class="mt-4"
          />
        </el-card>
      </el-tab-pane>

      <!-- 安全日志 -->
      <el-tab-pane label="安全日志" name="security">
        <el-card shadow="never">
          <template #header>
            <div class="card-header">
              <el-form :inline="true" :model="securityFilters">
                <el-form-item label="类型">
                  <el-select v-model="securityFilters.type" placeholder="全部" clearable style="width: 150px">
                    <el-option label="SQL注入" value="sql_injection" />
                    <el-option label="XSS攻击" value="xss_attack" />
                    <el-option label="CSRF攻击" value="csrf_attack" />
                    <el-option label="暴力破解" value="brute_force" />
                  </el-select>
                </el-form-item>
                <el-form-item label="级别">
                  <el-select v-model="securityFilters.level" placeholder="全部" clearable style="width: 120px">
                    <el-option label="低危" value="low" />
                    <el-option label="中危" value="medium" />
                    <el-option label="高危" value="high" />
                    <el-option label="严重" value="critical" />
                  </el-select>
                </el-form-item>
                <el-form-item label="IP">
                  <el-input v-model="securityFilters.ip" placeholder="搜索IP" style="width: 150px" />
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="loadSecurityLogs" :icon="Search">搜索</el-button>
                  <el-button @click="resetSecurityFilters">重置</el-button>
                  <el-button type="danger" @click="handleBatchDelete('security')">批量删除</el-button>
                </el-form-item>
              </el-form>
            </div>
          </template>

          <el-table :data="securityLogs" border stripe @selection-change="handleSecuritySelection">
            <el-table-column type="selection" width="55" />
            <el-table-column prop="type" label="类型" width="120" />
            <el-table-column prop="level" label="级别" width="90">
              <template #default="{ row }">
                <el-tag :type="getSecurityLevelType(row.level)">{{ row.level }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="ip" label="IP地址" width="130" />
            <el-table-column prop="description" label="描述" min-width="250" show-overflow-tooltip />
            <el-table-column prop="is_blocked" label="已拦截" width="90">
              <template #default="{ row }">
                <el-tag :type="row.is_blocked ? 'success' : 'warning'">
                  {{ row.is_blocked ? '是' : '否' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="create_time" label="时间" width="180" />
            <el-table-column label="操作" width="150" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" size="small" @click="handleViewDetail(row, 'security')">详情</el-button>
                <el-button type="danger" size="small" @click="handleDelete(row.id, 'security')">删除</el-button>
              </template>
            </el-table-column>
          </el-table>

          <el-pagination
            v-model:current-page="securityPagination.page"
            v-model:page-size="securityPagination.per_page"
            :total="securityPagination.total"
            @current-change="loadSecurityLogs"
            layout="total, prev, pager, next"
            class="mt-4"
          />
        </el-card>
      </el-tab-pane>
    </el-tabs>

    <!-- 详情对话框 -->
    <el-dialog v-model="detailVisible" :title="detailTitle" width="700px">
      <el-descriptions :column="1" border>
        <el-descriptions-item v-for="(value, key) in detailData" :key="key" :label="key">
          <pre v-if="typeof value === 'object'">{{ JSON.stringify(value, null, 2) }}</pre>
          <span v-else>{{ value }}</span>
        </el-descriptions-item>
      </el-descriptions>
    </el-dialog>

    <!-- 清理对话框 -->
    <el-dialog v-model="cleanVisible" title="清理旧日志" width="400px">
      <el-form>
        <el-form-item label="保留天数">
          <el-input-number v-model="cleanDays" :min="1" :max="365" />
          <el-text type="info" class="ml-2">将删除指定天数之前的日志</el-text>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="cleanVisible = false">取消</el-button>
        <el-button type="primary" @click="handleCleanLogs">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search } from '@element-plus/icons-vue'
import {
  getSystemLogs,
  getLoginLogs,
  getSecurityLogs,
  deleteSystemLog,
  deleteLoginLog,
  deleteSecurityLog,
  batchDeleteSystemLogs,
  batchDeleteLoginLogs,
  batchDeleteSecurityLogs,
  cleanOldLogs
} from '@/api/log'

const activeTab = ref('system')

// 系统日志
const systemLogs = ref([])
const systemFilters = reactive({
  level: '',
  category: '',
  keyword: ''
})
const systemPagination = reactive({
  page: 1,
  per_page: 20,
  total: 0
})
const selectedSystemLogs = ref([])

// 登录日志
const loginLogs = ref([])
const loginFilters = reactive({
  username: '',
  status: '',
  ip: ''
})
const loginPagination = reactive({
  page: 1,
  per_page: 20,
  total: 0
})
const selectedLoginLogs = ref([])

// 安全日志
const securityLogs = ref([])
const securityFilters = reactive({
  type: '',
  level: '',
  ip: ''
})
const securityPagination = reactive({
  page: 1,
  per_page: 20,
  total: 0
})
const selectedSecurityLogs = ref([])

// 详情和清理
const detailVisible = ref(false)
const detailTitle = ref('')
const detailData = ref({})
const cleanVisible = ref(false)
const cleanDays = ref(30)
const cleanLogType = ref('system')

onMounted(() => {
  loadSystemLogs()
})

const handleTabChange = (tab) => {
  if (tab === 'system') {
    loadSystemLogs()
  } else if (tab === 'login') {
    loadLoginLogs()
  } else if (tab === 'security') {
    loadSecurityLogs()
  }
}

const loadSystemLogs = async () => {
  const { data } = await getSystemLogs({
    page: systemPagination.page,
    per_page: systemPagination.per_page,
    ...systemFilters
  })
  systemLogs.value = data.list
  systemPagination.total = data.total
}

const loadLoginLogs = async () => {
  const { data } = await getLoginLogs({
    page: loginPagination.page,
    per_page: loginPagination.per_page,
    ...loginFilters
  })
  loginLogs.value = data.list
  loginPagination.total = data.total
}

const loadSecurityLogs = async () => {
  const { data } = await getSecurityLogs({
    page: securityPagination.page,
    per_page: securityPagination.per_page,
    ...securityFilters
  })
  securityLogs.value = data.list
  securityPagination.total = data.total
}

const resetSystemFilters = () => {
  systemFilters.level = ''
  systemFilters.category = ''
  systemFilters.keyword = ''
  systemPagination.page = 1
  loadSystemLogs()
}

const resetLoginFilters = () => {
  loginFilters.username = ''
  loginFilters.status = ''
  loginFilters.ip = ''
  loginPagination.page = 1
  loadLoginLogs()
}

const resetSecurityFilters = () => {
  securityFilters.type = ''
  securityFilters.level = ''
  securityFilters.ip = ''
  securityPagination.page = 1
  loadSecurityLogs()
}

const handleSystemSelection = (selection) => {
  selectedSystemLogs.value = selection
}

const handleLoginSelection = (selection) => {
  selectedLoginLogs.value = selection
}

const handleSecuritySelection = (selection) => {
  selectedSecurityLogs.value = selection
}

const handleDelete = async (id, type) => {
  try {
    await ElMessageBox.confirm('确定要删除这条日志吗？', '提示', { type: 'warning' })

    if (type === 'system') {
      await deleteSystemLog(id)
      loadSystemLogs()
    } else if (type === 'login') {
      await deleteLoginLog(id)
      loadLoginLogs()
    } else if (type === 'security') {
      await deleteSecurityLog(id)
      loadSecurityLogs()
    }

    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

const handleBatchDelete = async (type) => {
  let selected
  if (type === 'system') {
    selected = selectedSystemLogs.value
  } else if (type === 'login') {
    selected = selectedLoginLogs.value
  } else if (type === 'security') {
    selected = selectedSecurityLogs.value
  }

  if (selected.length === 0) {
    ElMessage.warning('请选择要删除的日志')
    return
  }

  try {
    await ElMessageBox.confirm(`确定要删除选中的 ${selected.length} 条日志吗？`, '提示', { type: 'warning' })

    const ids = selected.map(item => item.id)

    if (type === 'system') {
      await batchDeleteSystemLogs({ ids })
      loadSystemLogs()
    } else if (type === 'login') {
      await batchDeleteLoginLogs({ ids })
      loadLoginLogs()
    } else if (type === 'security') {
      await batchDeleteSecurityLogs({ ids })
      loadSecurityLogs()
    }

    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

const handleViewDetail = (row, type) => {
  detailTitle.value = type === 'system' ? '系统日志详情' : '安全日志详情'
  detailData.value = { ...row }
  detailVisible.value = true
}

const showCleanDialog = (type) => {
  cleanLogType.value = type
  cleanVisible.value = true
}

const handleCleanLogs = async () => {
  try {
    const { data } = await cleanOldLogs({
      days: cleanDays.value,
      log_type: cleanLogType.value
    })
    ElMessage.success(`成功清理 ${data.count} 条日志`)
    cleanVisible.value = false

    if (cleanLogType.value === 'system') {
      loadSystemLogs()
    } else if (cleanLogType.value === 'login') {
      loadLoginLogs()
    } else if (cleanLogType.value === 'security') {
      loadSecurityLogs()
    }
  } catch (error) {
    ElMessage.error('清理失败')
  }
}

const getLevelType = (level) => {
  const types = {
    debug: 'info',
    info: '',
    warning: 'warning',
    error: 'danger',
    critical: 'danger'
  }
  return types[level] || ''
}

const getLevelLabel = (level) => {
  const labels = {
    debug: '调试',
    info: '信息',
    warning: '警告',
    error: '错误',
    critical: '严重'
  }
  return labels[level] || level
}

const getSecurityLevelType = (level) => {
  const types = {
    low: 'info',
    medium: 'warning',
    high: 'danger',
    critical: 'danger'
  }
  return types[level] || ''
}
</script>

<style scoped>
.logs-management {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.mt-4 {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}

.ml-2 {
  margin-left: 10px;
}

pre {
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
