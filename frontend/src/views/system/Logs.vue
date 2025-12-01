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

      <!-- 操作日志 -->
      <el-tab-pane label="操作日志" name="operation">
        <el-card shadow="never">
          <template #header>
            <div class="card-header">
              <el-form :inline="true" :model="operationFilters">
                <el-form-item label="用户名">
                  <el-input v-model="operationFilters.username" placeholder="请输入用户名" clearable style="width: 150px;" />
                </el-form-item>
                <el-form-item label="模块">
                  <el-select v-model="operationFilters.module" placeholder="请选择模块" clearable style="width: 150px;">
                    <el-option
                      v-for="module in modules"
                      :key="module.value"
                      :label="module.label"
                      :value="module.value"
                    />
                  </el-select>
                </el-form-item>
                <el-form-item label="操作类型">
                  <el-select v-model="operationFilters.action" placeholder="请选择操作类型" clearable style="width: 150px;">
                    <el-option
                      v-for="action in actions"
                      :key="action.value"
                      :label="action.label"
                      :value="action.value"
                    />
                  </el-select>
                </el-form-item>
                <el-form-item label="状态">
                  <el-select v-model="operationFilters.status" placeholder="请选择状态" clearable style="width: 120px;">
                    <el-option label="成功" :value="1" />
                    <el-option label="失败" :value="0" />
                  </el-select>
                </el-form-item>
                <el-form-item label="日期范围">
                  <el-date-picker
                    v-model="operationDateRange"
                    type="daterange"
                    range-separator="至"
                    start-placeholder="开始日期"
                    end-placeholder="结束日期"
                    value-format="YYYY-MM-DD"
                    style="width: 240px;"
                  />
                </el-form-item>
                <el-form-item>
                  <el-button type="primary" @click="loadOperationLogs" :icon="Search">搜索</el-button>
                  <el-button @click="resetOperationFilters">重置</el-button>
                  <el-button type="danger" @click="handleBatchDelete('operation')">批量删除</el-button>
                  <el-button type="warning" @click="showCleanDialog('operation')">清空日志</el-button>
                </el-form-item>
              </el-form>
            </div>
          </template>

          <el-table :data="operationLogs" border stripe @selection-change="handleOperationSelection">
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
                <el-button type="primary" size="small" @click="handleViewDetail(row, 'operation')">详情</el-button>
              </template>
            </el-table-column>
          </el-table>

          <el-pagination
            v-model:current-page="operationPagination.page"
            v-model:page-size="operationPagination.page_size"
            :total="operationPagination.total"
            :page-sizes="[20, 50, 100, 200]"
            layout="total, sizes, prev, pager, next, jumper"
            @size-change="handleOperationSizeChange"
            @current-change="loadOperationLogs"
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
    <el-dialog v-model="detailVisible" :title="detailTitle" width="900px">
      <el-tabs v-if="currentLog" type="border-card">
        <el-tab-pane label="基本信息">
          <el-descriptions :column="1" border>
            <el-descriptions-item v-for="(value, key) in detailData" :key="key" :label="getFieldLabel(key)">
              <pre v-if="typeof value === 'object'">{{ JSON.stringify(value, null, 2) }}</pre>
              <span v-else>{{ value }}</span>
            </el-descriptions-item>
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

    <!-- 清理对话框 -->
    <el-dialog v-model="cleanVisible" title="清理旧日志" width="400px">
      <el-form>
        <el-form-item label="保留天数">
          <el-input-number v-model="cleanDays" :min="7" :max="365" />
          <el-text type="info" class="ml-2">将删除指定天数之前的日志</el-text>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="cleanVisible = false">取消</el-button>
        <el-button type="primary" @click="handleCleanLogs" :loading="cleaning">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, DocumentCopy } from '@element-plus/icons-vue'
import {
  getSystemLogs,
  deleteSystemLog,
  batchDeleteSystemLogs,
  cleanOldLogs,
  deleteLoginLog,
  batchDeleteLoginLogs
} from '@/api/log'
import {
  getOperationLogs,
  getModules,
  getActions,
  clearLogs
} from '@/api/operationLog'
import request from '@/api/request'

const activeTab = ref('system')
const cleaning = ref(false)

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

// 操作日志
const operationLogs = ref([])
const operationFilters = reactive({
  username: '',
  module: '',
  action: '',
  status: ''
})
const operationPagination = reactive({
  page: 1,
  page_size: 20,
  total: 0
})
const selectedOperationLogs = ref([])
const operationDateRange = ref([])
const modules = ref([])
const actions = ref([])

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
const currentLog = ref(null)
const cleanVisible = ref(false)
const cleanDays = ref(30)
const cleanLogType = ref('system')

onMounted(() => {
  loadSystemLogs()
  loadOperationOptions()
})

const handleTabChange = (tab) => {
  if (tab === 'system') {
    loadSystemLogs()
  } else if (tab === 'operation') {
    loadOperationLogs()
  } else if (tab === 'login') {
    loadLoginLogs()
  } else if (tab === 'security') {
    loadSecurityLogs()
  }
}

const loadSystemLogs = async () => {
  try {
    const { data } = await getSystemLogs({
      page: systemPagination.page,
      per_page: systemPagination.per_page,
      ...systemFilters
    })
    systemLogs.value = data.list || data.data || []
    systemPagination.total = data.total
  } catch (error) {
    console.error('加载系统日志失败:', error)
    systemLogs.value = []
  }
}

const loadOperationLogs = async () => {
  try {
    const params = {
      page: operationPagination.page,
      page_size: operationPagination.page_size,
      username: operationFilters.username,
      module: operationFilters.module,
      action: operationFilters.action,
      status: operationFilters.status
    }

    if (operationDateRange.value && operationDateRange.value.length === 2) {
      params.start_date = operationDateRange.value[0]
      params.end_date = operationDateRange.value[1]
    }

    const res = await getOperationLogs(params)
    operationLogs.value = res.data.list || []
    operationPagination.total = res.data.total || 0
  } catch (error) {
    console.error('加载操作日志失败:', error)
    operationLogs.value = []
  }
}

const loadOperationOptions = async () => {
  try {
    const [modulesRes, actionsRes] = await Promise.all([
      getModules(),
      getActions()
    ])
    modules.value = Array.isArray(modulesRes.data) ? modulesRes.data : []
    actions.value = Array.isArray(actionsRes.data) ? actionsRes.data : []
  } catch (error) {
    console.error('加载选项失败:', error)
    modules.value = []
    actions.value = []
  }
}

const loadLoginLogs = async () => {
  try {
    const { data } = await getOperationLogs({
      page: loginPagination.page,
      per_page: loginPagination.per_page,
      module: 'auth',
      action: 'login',
      username: loginFilters.username,
      status: loginFilters.status,
      ip: loginFilters.ip
    })
    loginLogs.value = (data.list || []).map(log => ({
      id: log.id,
      username: log.username,
      ip: log.ip,
      location: log.location || '-',
      status: log.status === 1 ? 'success' : 'failed',
      fail_reason: log.status === 0 ? (log.description || '登录失败') : '',
      login_time: log.create_time
    }))
    loginPagination.total = data.total
  } catch (error) {
    console.error('加载登录日志失败:', error)
    loginLogs.value = []
  }
}

const loadSecurityLogs = async () => {
  try {
    const { data } = await getSystemLogs({
      page: securityPagination.page,
      per_page: securityPagination.per_page,
      category: 'security',
      keyword: securityFilters.type,
      level: securityFilters.level,
      ip: securityFilters.ip
    })
    securityLogs.value = ((data.list || data.data || []).map(log => ({
      id: log.id,
      type: log.message?.includes('SQL') ? 'sql_injection' :
            log.message?.includes('XSS') ? 'xss_attack' :
            log.message?.includes('CSRF') ? 'csrf_attack' :
            log.message?.includes('暴力') ? 'brute_force' : log.category,
      level: log.level || 'low',
      ip: log.ip,
      description: log.message,
      is_blocked: log.level === 'error' || log.level === 'critical',
      create_time: log.create_time
    })))
    securityPagination.total = data.total
  } catch (error) {
    console.error('加载安全日志失败:', error)
    securityLogs.value = []
  }
}

const resetSystemFilters = () => {
  systemFilters.level = ''
  systemFilters.category = ''
  systemFilters.keyword = ''
  systemPagination.page = 1
  loadSystemLogs()
}

const resetOperationFilters = () => {
  operationFilters.username = ''
  operationFilters.module = ''
  operationFilters.action = ''
  operationFilters.status = ''
  operationDateRange.value = []
  operationPagination.page = 1
  loadOperationLogs()
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

const handleOperationSelection = (selection) => {
  selectedOperationLogs.value = selection
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

    if (type === 'system' || type === 'security') {
      await deleteSystemLog(id)
      if (type === 'system') {
        loadSystemLogs()
      } else {
        loadSecurityLogs()
      }
    } else if (type === 'login') {
      await deleteLoginLog(id)
      loadLoginLogs()
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
  if (type === 'system' || type === 'security') {
    selected = type === 'system' ? selectedSystemLogs.value : selectedSecurityLogs.value
  } else if (type === 'login') {
    selected = selectedLoginLogs.value
  } else if (type === 'operation') {
    selected = selectedOperationLogs.value
  }

  if (!selected || selected.length === 0) {
    ElMessage.warning('请选择要删除的日志')
    return
  }

  try {
    await ElMessageBox.confirm(`确定要删除选中的 ${selected.length} 条日志吗？`, '提示', { type: 'warning' })

    const ids = selected.map(item => item.id)

    if (type === 'system' || type === 'security') {
      await batchDeleteSystemLogs({ ids })
      if (type === 'system') {
        loadSystemLogs()
      } else {
        loadSecurityLogs()
      }
    } else if (type === 'login') {
      await batchDeleteLoginLogs({ ids })
      loadLoginLogs()
    } else if (type === 'operation') {
      await request({
        url: '/operation-log/batch-delete',
        method: 'post',
        data: { ids }
      })
      loadOperationLogs()
    }

    ElMessage.success('删除成功')
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

const handleViewDetail = (row, type) => {
  currentLog.value = row
  if (type === 'system') {
    detailTitle.value = '系统日志详情'
  } else if (type === 'operation') {
    detailTitle.value = '操作日志详情'
  } else if (type === 'security') {
    detailTitle.value = '安全日志详情'
  }
  detailData.value = { ...row }
  detailVisible.value = true
}

const showCleanDialog = (type) => {
  cleanLogType.value = type
  cleanVisible.value = true
}

const handleCleanLogs = async () => {
  try {
    cleaning.value = true

    if (cleanLogType.value === 'operation') {
      // 操作日志使用自己的清理API
      await clearLogs(cleanDays.value)
      ElMessage.success('清理成功')
      loadOperationLogs()
    } else {
      // 系统日志、登录日志、安全日志使用通用清理API
      const { data } = await cleanOldLogs({
        days: cleanDays.value,
        log_type: cleanLogType.value
      })
      ElMessage.success(`成功清理 ${data.count} 条日志`)

      if (cleanLogType.value === 'system') {
        loadSystemLogs()
      } else if (cleanLogType.value === 'login') {
        loadLoginLogs()
      } else if (cleanLogType.value === 'security') {
        loadSecurityLogs()
      }
    }

    cleanVisible.value = false
  } catch (error) {
    ElMessage.error(error.message || '清理失败')
  } finally {
    cleaning.value = false
  }
}

const handleOperationSizeChange = (size) => {
  operationPagination.page_size = size
  operationPagination.page = 1
  loadOperationLogs()
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

const getModuleName = (module) => {
  if (!Array.isArray(modules.value)) return module
  const item = modules.value.find(m => m.value === module)
  return item ? item.label : module
}

const getActionName = (action) => {
  if (!Array.isArray(actions.value)) return action
  const item = actions.value.find(a => a.value === action)
  return item ? item.label : action
}

const getFieldLabel = (key) => {
  const labels = {
    id: 'ID',
    username: '用户名',
    module: '模块',
    action: '操作类型',
    description: '操作描述',
    ip: 'IP地址',
    user_agent: '用户代理',
    request_method: '请求方法',
    request_url: '请求URL',
    request_params: '请求参数',
    status: '状态',
    error_msg: '错误信息',
    execute_time: '执行时间',
    create_time: '创建时间',
    level: '级别',
    category: '分类',
    message: '消息',
    method: '方法',
    url: 'URL',
    context: '上下文'
  }
  return labels[key] || key
}

const hasChanges = (log) => {
  return log && (log.old_values || log.new_values || log.changed_fields)
}

const getChanges = (log) => {
  if (!log || !log.old_values || !log.new_values) {
    return []
  }

  try {
    const oldValues = JSON.parse(log.old_values)
    const newValues = JSON.parse(log.new_values)
    const changes = []

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

const formatValue = (value) => {
  if (value === null || value === undefined) {
    return '(空)'
  }
  if (value === '') {
    return '(空字符串)'
  }
  return value
}

const loadLoginStats = () => {
  ElMessage.info('登录统计功能开发中...')
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
