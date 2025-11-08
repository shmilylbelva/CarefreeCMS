<template>
  <div class="sms-service-container">
    <el-card>
      <template #header>
        <span>短信服务管理</span>
      </template>

      <el-tabs v-model="activeTab">
        <!-- 短信配置 -->
        <el-tab-pane label="短信配置" name="config">
          <el-button type="primary" @click="showConfigDialog" style="margin-bottom: 15px">
            新增配置
          </el-button>

          <el-table :data="configs" v-loading="loading">
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="provider" label="服务商" width="150">
              <template #default="{ row }">
                {{ getProviderName(row.provider) }}
              </template>
            </el-table-column>
            <el-table-column prop="sign_name" label="签名" width="150" />
            <el-table-column prop="status" label="状态" width="100">
              <template #default="{ row }">
                <el-tag :type="row.status ? 'success' : 'danger'">
                  {{ row.status ? '启用' : '禁用' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="is_default" label="默认" width="100">
              <template #default="{ row }">
                <el-tag v-if="row.is_default" type="warning">默认</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="150">
              <template #default="{ row }">
                <el-button size="small" @click="editConfig(row)">编辑</el-button>
                <el-button size="small" type="danger" @click="deleteConfig(row.id)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-tab-pane>

        <!-- 短信日志 -->
        <el-tab-pane label="短信日志" name="logs">
          <el-form :inline="true" :model="searchForm">
            <el-form-item label="手机号">
              <el-input v-model="searchForm.phone" placeholder="手机号" clearable />
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="loadLogs">搜索</el-button>
            </el-form-item>
          </el-form>

          <el-table :data="logs" v-loading="loading">
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="phone" label="手机号" width="120" />
            <el-table-column prop="content" label="内容" />
            <el-table-column prop="provider" label="服务商" width="120" />
            <el-table-column prop="status" label="状态" width="100">
              <template #default="{ row }">
                <el-tag :type="row.status ? 'success' : 'danger'">
                  {{ row.status ? '成功' : '失败' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="send_time" label="发送时间" width="160" />
          </el-table>

          <div class="pagination">
            <el-pagination
              v-model:current-page="pagination.page"
              v-model:page-size="pagination.limit"
              :total="pagination.total"
              layout="total, prev, pager, next"
              @current-change="loadLogs"
            />
          </div>
        </el-tab-pane>

        <!-- 发送统计 -->
        <el-tab-pane label="发送统计" name="stats">
          <el-descriptions v-if="stats" :column="2" border style="max-width: 600px">
            <el-descriptions-item label="总发送数">{{ stats.success_rate?.total || 0 }}</el-descriptions-item>
            <el-descriptions-item label="成功数">{{ stats.success_rate?.success || 0 }}</el-descriptions-item>
            <el-descriptions-item label="失败数">{{ stats.success_rate?.failed || 0 }}</el-descriptions-item>
            <el-descriptions-item label="成功率">{{ stats.success_rate?.success_rate || 0 }}%</el-descriptions-item>
            <el-descriptions-item label="今日发送">{{ stats.today_count || 0 }}</el-descriptions-item>
            <el-descriptions-item label="本周发送">{{ stats.week_count || 0 }}</el-descriptions-item>
            <el-descriptions-item label="本月发送">{{ stats.month_count || 0 }}</el-descriptions-item>
          </el-descriptions>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <!-- 短信配置对话框 -->
    <el-dialog v-model="configDialog.visible" :title="configDialog.isEdit ? '编辑配置' : '新增配置'" width="600px">
      <el-form :model="configDialog.form" label-width="100px">
        <el-form-item label="服务商">
          <el-select v-model="configDialog.form.provider" placeholder="请选择服务商">
            <el-option label="阿里云" value="aliyun" />
            <el-option label="腾讯云" value="tencent" />
            <el-option label="云片" value="yunpian" />
            <el-option label="模拟发送" value="mock" />
          </el-select>
        </el-form-item>
        <el-form-item label="Access Key">
          <el-input v-model="configDialog.form.access_key" placeholder="请输入Access Key" />
        </el-form-item>
        <el-form-item label="Access Secret">
          <el-input v-model="configDialog.form.access_secret" type="password" placeholder="请输入Access Secret" show-password />
        </el-form-item>
        <el-form-item label="签名">
          <el-input v-model="configDialog.form.sign_name" placeholder="请输入短信签名" />
        </el-form-item>
        <el-form-item label="状态">
          <el-radio-group v-model="configDialog.form.status">
            <el-radio :label="1">启用</el-radio>
            <el-radio :label="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="设为默认">
          <el-radio-group v-model="configDialog.form.is_default">
            <el-radio :label="1">是</el-radio>
            <el-radio :label="0">否</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="configDialog.visible = false">取消</el-button>
        <el-button type="primary" @click="saveConfig">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

const activeTab = ref('config')
const loading = ref(false)
const configs = ref([])
const logs = ref([])
const stats = ref(null)

const searchForm = reactive({
  phone: ''
})

const pagination = reactive({
  page: 1,
  limit: 20,
  total: 0
})

const configDialog = reactive({
  visible: false,
  isEdit: false,
  form: {
    id: null,
    provider: 'mock',
    access_key: '',
    access_secret: '',
    sign_name: '',
    status: 1,
    is_default: 0
  }
})

const getProviderName = (provider) => {
  const names = {
    aliyun: '阿里云',
    tencent: '腾讯云',
    yunpian: '云片',
    mock: '模拟发送'
  }
  return names[provider] || provider
}

const loadConfigs = async () => {
  loading.value = true
  try {
    const response = await request.get('sms-manage/config-index')
    if (response.code === 200) {
      configs.value = response.data
    }
  } catch (error) {
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

const loadLogs = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      limit: pagination.limit,
      ...searchForm
    }
    const response = await request.get('sms-manage/log-index', { params })
    if (response.code === 200) {
      logs.value = response.data.data
      pagination.total = response.data.total
    }
  } catch (error) {
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await request.get('sms-manage/statistics')
    if (response.code === 200) {
      stats.value = response.data
    }
  } catch (error) {
    console.error('加载统计失败', error)
  }
}

const showConfigDialog = () => {
  configDialog.isEdit = false
  Object.assign(configDialog.form, {
    id: null,
    provider: 'mock',
    access_key: '',
    access_secret: '',
    sign_name: '',
    status: 1,
    is_default: 0
  })
  configDialog.visible = true
}

const editConfig = (row) => {
  configDialog.isEdit = true
  Object.assign(configDialog.form, {
    id: row.id,
    provider: row.provider,
    access_key: row.access_key,
    access_secret: row.access_secret,
    sign_name: row.sign_name,
    status: row.status,
    is_default: row.is_default
  })
  configDialog.visible = true
}

const saveConfig = async () => {
  try {
    let response
    if (configDialog.isEdit) {
      response = await request.put(`sms-manage/config-update/${configDialog.form.id}`, configDialog.form)
    } else {
      response = await request.post('sms-manage/config-create', configDialog.form)
    }

    if (response.code === 200) {
      ElMessage.success(configDialog.isEdit ? '更新成功' : '创建成功')
      configDialog.visible = false
      loadConfigs()
    }
  } catch (error) {
    ElMessage.error('操作失败')
  }
}

const deleteConfig = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除该配置吗？', '确认', { type: 'warning' })
    const response = await request.delete(`/sms-manage/config-delete/${id}`)
    if (response.data.code === 200) {
      ElMessage.success('删除成功')
      loadConfigs()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

onMounted(() => {
  loadConfigs()
  loadLogs()
  loadStats()
})
</script>

<style scoped>
.sms-service-container {
  padding: 20px;
}

.pagination {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>
