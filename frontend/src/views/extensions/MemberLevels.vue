<template>
  <div class="member-levels-container">
    <el-card>
      <el-tabs v-model="activeTab">
        <!-- 等级配置管理 -->
        <el-tab-pane label="等级配置" name="config">
          <div class="toolbar">
            <el-input
              v-model="searchKeyword"
              placeholder="搜索等级名称"
              style="width: 200px"
              clearable
              @clear="loadLevels"
            >
              <template #append>
                <el-button :icon="Search" @click="loadLevels" />
              </template>
            </el-input>
            <el-select v-model="searchStatus" placeholder="状态" style="width: 120px; margin-left: 10px" clearable @change="loadLevels">
              <el-option label="启用" :value="1" />
              <el-option label="禁用" :value="0" />
            </el-select>
            <el-button type="primary" :icon="Plus" @click="handleCreate" style="margin-left: 10px">新增等级</el-button>
          </div>

          <el-table :data="levelList" border style="margin-top: 20px" v-loading="loading">
            <el-table-column prop="level" label="等级" width="80" />
            <el-table-column prop="name" label="等级名称" width="120" />
            <el-table-column prop="points_required" label="所需积分" width="100" />
            <el-table-column prop="articles_required" label="所需文章数" width="110" />
            <el-table-column prop="comments_required" label="所需评论数" width="110" />
            <el-table-column prop="days_required" label="所需天数" width="100" />
            <el-table-column prop="icon" label="图标" width="80">
              <template #default="{ row }">
                <el-icon v-if="row.icon" :size="24"><component :is="row.icon" /></el-icon>
              </template>
            </el-table-column>
            <el-table-column prop="color" label="颜色" width="100">
              <template #default="{ row }">
                <div v-if="row.color" :style="{ color: row.color, fontWeight: 'bold' }">{{ row.color }}</div>
              </template>
            </el-table-column>
            <el-table-column prop="sort" label="排序" width="80" />
            <el-table-column prop="status" label="状态" width="80">
              <template #default="{ row }">
                <el-tag :type="row.status === 1 ? 'success' : 'danger'">
                  {{ row.status === 1 ? '启用' : '禁用' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="180" fixed="right">
              <template #default="{ row }">
                <el-button link type="primary" @click="handleEdit(row)">编辑</el-button>
                <el-button link type="danger" @click="handleDelete(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>

          <el-pagination
            v-model:current-page="currentPage"
            v-model:page-size="pageSize"
            :total="total"
            :page-sizes="[10, 20, 50, 100]"
            layout="total, sizes, prev, pager, next, jumper"
            @size-change="loadLevels"
            @current-change="loadLevels"
            style="margin-top: 20px; justify-content: center"
          />
        </el-tab-pane>

        <!-- 升级日志 -->
        <el-tab-pane label="升级日志" name="logs">
          <div class="toolbar">
            <el-input
              v-model="logSearchUserId"
              placeholder="用户ID"
              style="width: 150px"
              clearable
              @clear="loadLogs"
            >
              <template #append>
                <el-button :icon="Search" @click="loadLogs" />
              </template>
            </el-input>
            <el-select v-model="logSearchType" placeholder="升级类型" style="width: 150px; margin-left: 10px" clearable @change="loadLogs">
              <el-option label="自动升级" value="auto" />
              <el-option label="手动调整" value="manual" />
            </el-select>
          </div>

          <el-table :data="logList" border style="margin-top: 20px" v-loading="logLoading">
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="user_id" label="用户ID" width="100" />
            <el-table-column label="用户昵称" width="150">
              <template #default="{ row }">
                {{ row.user?.nickname || '-' }}
              </template>
            </el-table-column>
            <el-table-column prop="old_level" label="原等级" width="100" />
            <el-table-column prop="new_level" label="新等级" width="100" />
            <el-table-column prop="upgrade_type" label="升级类型" width="120">
              <template #default="{ row }">
                <el-tag :type="row.upgrade_type === 'auto' ? 'success' : 'warning'">
                  {{ row.upgrade_type === 'auto' ? '自动升级' : '手动调整' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="reason" label="原因" min-width="200" />
            <el-table-column label="操作人" width="120">
              <template #default="{ row }">
                {{ row.operator?.username || '-' }}
              </template>
            </el-table-column>
            <el-table-column prop="create_time" label="时间" width="180" />
          </el-table>

          <el-pagination
            v-model:current-page="logCurrentPage"
            v-model:page-size="logPageSize"
            :total="logTotal"
            :page-sizes="[10, 20, 50, 100]"
            layout="total, sizes, prev, pager, next, jumper"
            @size-change="loadLogs"
            @current-change="loadLogs"
            style="margin-top: 20px; justify-content: center"
          />
        </el-tab-pane>

        <!-- 统计信息 -->
        <el-tab-pane label="统计信息" name="statistics">
          <el-row :gutter="20" style="margin-bottom: 20px">
            <el-col :span="6">
              <el-card>
                <div class="stat-card">
                  <div class="stat-label">今日升级</div>
                  <div class="stat-value">{{ statistics.today_count || 0 }}</div>
                </div>
              </el-card>
            </el-col>
            <el-col :span="6">
              <el-card>
                <div class="stat-card">
                  <div class="stat-label">本周升级</div>
                  <div class="stat-value">{{ statistics.week_count || 0 }}</div>
                </div>
              </el-card>
            </el-col>
            <el-col :span="6">
              <el-card>
                <div class="stat-card">
                  <div class="stat-label">本月升级</div>
                  <div class="stat-value">{{ statistics.month_count || 0 }}</div>
                </div>
              </el-card>
            </el-col>
            <el-col :span="6">
              <el-card>
                <div class="stat-card">
                  <el-button type="primary" :icon="Refresh" @click="handleBatchUpgrade" :loading="batchUpgrading">
                    批量升级检查
                  </el-button>
                </div>
              </el-card>
            </el-col>
          </el-row>

          <el-card title="等级分布">
            <template #header>
              <span>等级分布</span>
            </template>
            <el-table :data="statistics.level_distribution || []" border>
              <el-table-column prop="level" label="等级" width="100" />
              <el-table-column prop="name" label="等级名称" width="150" />
              <el-table-column prop="count" label="用户数" width="120" />
              <el-table-column label="占比" width="200">
                <template #default="{ row }">
                  <el-progress
                    :percentage="calculatePercentage(row.count)"
                    :color="'#409EFF'"
                  />
                </template>
              </el-table-column>
            </el-table>
          </el-card>

          <el-card title="最近升级记录" style="margin-top: 20px">
            <template #header>
              <span>最近升级记录</span>
            </template>
            <el-table :data="statistics.recent_upgrades || []" border>
              <el-table-column prop="user_id" label="用户ID" width="100" />
              <el-table-column label="用户昵称" width="150">
                <template #default="{ row }">
                  {{ row.user?.nickname || '-' }}
                </template>
              </el-table-column>
              <el-table-column prop="old_level" label="原等级" width="100" />
              <el-table-column prop="new_level" label="新等级" width="100" />
              <el-table-column prop="upgrade_type" label="类型" width="120">
                <template #default="{ row }">
                  <el-tag :type="row.upgrade_type === 'auto' ? 'success' : 'warning'">
                    {{ row.upgrade_type === 'auto' ? '自动' : '手动' }}
                  </el-tag>
                </template>
              </el-table-column>
              <el-table-column prop="create_time" label="时间" width="180" />
            </el-table>
          </el-card>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <!-- 等级配置对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="600px"
      @close="resetForm"
    >
      <el-form :model="formData" :rules="rules" ref="formRef" label-width="120px">
        <el-form-item label="等级" prop="level">
          <el-input-number v-model="formData.level" :min="0" :max="100" :disabled="isEdit" />
          <span style="margin-left: 10px; color: #999; font-size: 12px">等级值，创建后不可修改</span>
        </el-form-item>
        <el-form-item label="等级名称" prop="name">
          <el-input v-model="formData.name" placeholder="如：新手、青铜、白银等" />
        </el-form-item>
        <el-form-item label="所需积分" prop="points_required">
          <el-input-number v-model="formData.points_required" :min="0" />
        </el-form-item>
        <el-form-item label="所需文章数" prop="articles_required">
          <el-input-number v-model="formData.articles_required" :min="0" />
        </el-form-item>
        <el-form-item label="所需评论数" prop="comments_required">
          <el-input-number v-model="formData.comments_required" :min="0" />
        </el-form-item>
        <el-form-item label="所需天数" prop="days_required">
          <el-input-number v-model="formData.days_required" :min="0" />
          <span style="margin-left: 10px; color: #999; font-size: 12px">注册天数</span>
        </el-form-item>
        <el-form-item label="图标" prop="icon">
          <el-input v-model="formData.icon" placeholder="图标名称（Element Plus图标）" />
        </el-form-item>
        <el-form-item label="颜色" prop="color">
          <el-color-picker v-model="formData.color" />
        </el-form-item>
        <el-form-item label="排序" prop="sort">
          <el-input-number v-model="formData.sort" :min="0" />
        </el-form-item>
        <el-form-item label="状态" prop="status">
          <el-radio-group v-model="formData.status">
            <el-radio :value="1">启用</el-radio>
            <el-radio :value="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="等级权益" prop="privileges">
          <el-input
            v-model="privilegesText"
            type="textarea"
            :rows="4"
            placeholder="每行一个权益描述"
          />
          <span style="color: #999; font-size: 12px">每行输入一个权益，如：每日可发布文章数+5</span>
        </el-form-item>
        <el-form-item label="描述" prop="description">
          <el-input v-model="formData.description" type="textarea" :rows="3" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Plus, Refresh } from '@element-plus/icons-vue'
import request from '@/utils/request'

const activeTab = ref('config')

// 等级配置相关
const levelList = ref([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const searchKeyword = ref('')
const searchStatus = ref('')

// 升级日志相关
const logList = ref([])
const logLoading = ref(false)
const logCurrentPage = ref(1)
const logPageSize = ref(20)
const logTotal = ref(0)
const logSearchUserId = ref('')
const logSearchType = ref('')

// 统计信息
const statistics = ref({})
const batchUpgrading = ref(false)

// 对话框相关
const dialogVisible = ref(false)
const dialogTitle = ref('新增等级')
const isEdit = ref(false)
const submitting = ref(false)
const formRef = ref(null)
const formData = reactive({
  level: 0,
  name: '',
  points_required: 0,
  articles_required: 0,
  comments_required: 0,
  days_required: 0,
  icon: '',
  color: '',
  sort: 0,
  status: 1,
  privileges: [],
  description: ''
})

const privilegesText = ref('')

const rules = {
  level: [{ required: true, message: '请输入等级', trigger: 'blur' }],
  name: [{ required: true, message: '请输入等级名称', trigger: 'blur' }]
}

// 加载等级列表
const loadLevels = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('token')
    const params = {
      page: currentPage.value,
      limit: pageSize.value
    }
    if (searchKeyword.value) {
      params.keyword = searchKeyword.value
    }
    if (searchStatus.value !== '') {
      params.status = searchStatus.value
    }

    const response = await request.get('member-level-manage/index', {
      params
    })

    if (response.code === 200) {
      levelList.value = response.data.data || []
      total.value = response.data.total || 0
    }
  } catch (error) {
    console.error('加载等级列表失败:', error)
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

// 加载升级日志
const loadLogs = async () => {
  logLoading.value = true
  try {
    const token = localStorage.getItem('token')
    const params = {
      page: logCurrentPage.value,
      limit: logPageSize.value
    }
    if (logSearchUserId.value) {
      params.user_id = logSearchUserId.value
    }
    if (logSearchType.value) {
      params.upgrade_type = logSearchType.value
    }

    const response = await request.get('member-level-manage/log-index', {
      params
    })

    if (response.code === 200) {
      logList.value = response.data.data || []
      logTotal.value = response.data.total || 0
    }
  } catch (error) {
    console.error('加载日志失败:', error)
    ElMessage.error('加载失败')
  } finally {
    logLoading.value = false
  }
}

// 加载统计信息
const loadStatistics = async () => {
  try {
    const response = await request.get('member-level-manage/statistics')

    if (response.code === 200) {
      statistics.value = response.data
    }
  } catch (error) {
    console.error('加载统计失败:', error)
    ElMessage.error('加载统计失败')
  }
}

// 计算百分比
const calculatePercentage = (count) => {
  const totalUsers = statistics.value.level_distribution?.reduce((sum, item) => sum + item.count, 0) || 0
  return totalUsers > 0 ? Math.round((count / totalUsers) * 100) : 0
}

// 新增等级
const handleCreate = () => {
  dialogTitle.value = '新增等级'
  isEdit.value = false
  dialogVisible.value = true
}

// 编辑等级
const handleEdit = async (row) => {
  dialogTitle.value = '编辑等级'
  isEdit.value = true

  try {
    const response = await request.get(`member-level-manage/read/${row.id}`)

    if (response.code === 200) {
      const data = response.data
      Object.assign(formData, data)

      // 处理privileges
      if (data.privileges) {
        const privs = typeof data.privileges === 'string' ? JSON.parse(data.privileges) : data.privileges
        privilegesText.value = Array.isArray(privs) ? privs.join('\n') : ''
      } else {
        privilegesText.value = ''
      }

      dialogVisible.value = true
    } else {
      ElMessage.error(response.data.message || '加载失败')
    }
  } catch (error) {
    console.error('加载等级详情失败:', error)
    ElMessage.error('加载失败')
  }
}

// 删除等级
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确认删除该等级配置吗？如有用户正在使用该等级将无法删除。', '提示', {
      type: 'warning'
    })

    const response = await request.delete(`member-level-manage/delete/${row.id}`)

    if (response.code === 200) {
      ElMessage.success('删除成功')
      loadLevels()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('删除失败:', error)
      ElMessage.error('删除失败')
    }
  }
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (!valid) return

    submitting.value = true
    try {
      // 处理privileges
      const privileges = privilegesText.value
        .split('\n')
        .map(line => line.trim())
        .filter(line => line.length > 0)

      const submitData = {
        ...formData,
        privileges
      }

      let response
      if (isEdit.value) {
        response = await request.put(`member-level-manage/update/${formData.id}`, submitData)
      } else {
        response = await request.post('member-level-manage/create', submitData)
      }

      if (response.code === 200) {
        ElMessage.success(isEdit.value ? '更新成功' : '创建成功')
        dialogVisible.value = false
        loadLevels()
      }
    } catch (error) {
      console.error('提交失败:', error)
      ElMessage.error('操作失败')
    } finally {
      submitting.value = false
    }
  })
}

// 重置表单
const resetForm = () => {
  Object.assign(formData, {
    level: 0,
    name: '',
    points_required: 0,
    articles_required: 0,
    comments_required: 0,
    days_required: 0,
    icon: '',
    color: '',
    sort: 0,
    status: 1,
    privileges: [],
    description: ''
  })
  privilegesText.value = ''
  formRef.value?.resetFields()
}

// 批量升级
const handleBatchUpgrade = async () => {
  try {
    await ElMessageBox.confirm('确认执行批量升级检查吗？系统将检查所有用户的等级并自动升级符合条件的用户。', '提示', {
      type: 'info'
    })

    batchUpgrading.value = true
    const response = await request.post('member-level-manage/batch-upgrade', {
      limit: 100
    })

    if (response.code === 200) {
      ElMessage.success(response.message || '批量升级完成')
      loadStatistics()
      loadLogs()
    }
  } catch (error) {
    if (error !== 'cancel') {
      console.error('批量升级失败:', error)
      ElMessage.error('批量升级失败')
    }
  } finally {
    batchUpgrading.value = false
  }
}

onMounted(() => {
  loadLevels()
  loadLogs()
  loadStatistics()
})
</script>

<style scoped>
.member-levels-container {
  padding: 20px;
}

.toolbar {
  display: flex;
  align-items: center;
}

.stat-card {
  text-align: center;
  padding: 20px 0;
}

.stat-label {
  font-size: 14px;
  color: #999;
  margin-bottom: 10px;
}

.stat-value {
  font-size: 28px;
  font-weight: bold;
  color: #409EFF;
}
</style>
