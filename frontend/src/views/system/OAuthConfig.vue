<template>
  <div class="oauth-config-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>第三方登录配置</span>
          <el-button type="primary" @click="handleBatchEnable" :disabled="!selectedIds.length">批量启用</el-button>
          <el-button @click="handleBatchDisable" :disabled="!selectedIds.length">批量禁用</el-button>
        </div>
      </template>

      <!-- 配置列表 -->
      <el-table
        :data="configList"
        v-loading="loading"
        @selection-change="handleSelectionChange"
        style="width: 100%">
        <el-table-column type="selection" width="55" />
        <el-table-column prop="platform_name" label="平台名称" width="120">
          <template #default="{ row }">
            <el-tag>{{ row.platform_name }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="app_id" label="AppID / Client ID" min-width="200" show-overflow-tooltip />
        <el-table-column prop="app_secret" label="AppSecret / Client Secret" min-width="200" show-overflow-tooltip />
        <el-table-column prop="redirect_uri" label="回调地址" min-width="250" show-overflow-tooltip />
        <el-table-column prop="scope" label="授权范围" width="150" />
        <el-table-column prop="is_enabled" label="状态" width="100">
          <template #default="{ row }">
            <el-switch
              v-model="row.is_enabled"
              :active-value="1"
              :inactive-value="0"
              @change="handleStatusChange(row)" />
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
            <el-button type="success" link @click="handleTest(row)">测试</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="'编辑 ' + (form.platform_name || '')"
      width="700px"
      @close="handleDialogClose">
      <el-form :model="form" :rules="rules" ref="formRef" label-width="140px">
        <el-form-item label="平台名称">
          <el-input v-model="form.platform_name" disabled />
        </el-form-item>
        <el-form-item label="平台标识">
          <el-input v-model="form.platform" disabled />
        </el-form-item>
        <el-form-item label="AppID/ClientID" prop="app_id">
          <el-input v-model="form.app_id" placeholder="请输入应用ID" />
        </el-form-item>
        <el-form-item label="AppSecret/Secret" prop="app_secret">
          <el-input
            v-model="form.app_secret"
            type="password"
            placeholder="请输入应用密钥"
            show-password />
          <span class="form-tip">如不修改密钥，请留空</span>
        </el-form-item>
        <el-form-item label="回调地址" prop="redirect_uri">
          <el-input v-model="form.redirect_uri" placeholder="https://your-domain.com/oauth/callback" />
        </el-form-item>
        <el-form-item label="授权范围" prop="scope">
          <el-input v-model="form.scope" placeholder="例如: snsapi_login" />
        </el-form-item>
        <el-form-item label="排序权重">
          <el-input-number v-model="form.sort_order" :min="0" />
        </el-form-item>
        <el-form-item label="启用状态">
          <el-switch
            v-model="form.is_enabled"
            :active-value="1"
            :inactive-value="0" />
        </el-form-item>
        <el-form-item label="备注说明">
          <el-input v-model="form.remark" type="textarea" :rows="3" />
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
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getOAuthConfigList,
  getOAuthConfigDetail,
  updateOAuthConfig,
  batchUpdateOAuthStatus,
  testOAuthConfig
} from '@/api/oauth'

// 数据
const loading = ref(false)
const configList = ref([])
const selectedIds = ref([])
const dialogVisible = ref(false)
const submitting = ref(false)
const formRef = ref(null)

const form = ref({
  id: null,
  platform: '',
  platform_name: '',
  app_id: '',
  app_secret: '',
  redirect_uri: '',
  scope: '',
  sort_order: 0,
  is_enabled: 0,
  remark: ''
})

const rules = {
  app_id: [{ required: true, message: '请输入AppID', trigger: 'blur' }],
  redirect_uri: [
    { required: true, message: '请输入回调地址', trigger: 'blur' },
    { type: 'url', message: '请输入有效的URL', trigger: 'blur' }
  ]
}

// 加载列表
const loadList = async () => {
  loading.value = true
  try {
    const res = await getOAuthConfigList()
    configList.value = res.data.list || []
  } catch (error) {
    ElMessage.error('加载配置列表失败')
  } finally {
    loading.value = false
  }
}

// 选择变化
const handleSelectionChange = (selection) => {
  selectedIds.value = selection.map(item => item.id)
}

// 编辑
const handleEdit = async (row) => {
  try {
    const res = await getOAuthConfigDetail(row.id)
    form.value = { ...res.data }
    // 如果app_secret包含*，清空（表示不修改）
    if (form.value.app_secret && form.value.app_secret.includes('*')) {
      form.value.app_secret = ''
    }
    dialogVisible.value = true
  } catch (error) {
    ElMessage.error('获取配置详情失败')
  }
}

// 状态变化
const handleStatusChange = async (row) => {
  try {
    await batchUpdateOAuthStatus([row.id], row.is_enabled)
    ElMessage.success('状态更新成功')
    loadList()
  } catch (error) {
    ElMessage.error('状态更新失败')
    row.is_enabled = row.is_enabled === 1 ? 0 : 1 // 回滚
  }
}

// 批量启用
const handleBatchEnable = async () => {
  try {
    await batchUpdateOAuthStatus(selectedIds.value, 1)
    ElMessage.success('批量启用成功')
    loadList()
  } catch (error) {
    ElMessage.error('批量启用失败')
  }
}

// 批量禁用
const handleBatchDisable = async () => {
  try {
    await batchUpdateOAuthStatus(selectedIds.value, 0)
    ElMessage.success('批量禁用成功')
    loadList()
  } catch (error) {
    ElMessage.error('批量禁用失败')
  }
}

// 测试配置
const handleTest = async (row) => {
  try {
    await testOAuthConfig(row.id)
    ElMessage.success('配置检测通过，所有必填项已配置')
  } catch (error) {
    ElMessage.error(error.message || '配置检测失败')
  }
}

// 提交
const handleSubmit = async () => {
  if (!formRef.value) return

  try {
    await formRef.value.validate()
  } catch {
    return
  }

  submitting.value = true
  try {
    // 如果app_secret为空，不传递（表示不修改）
    const data = { ...form.value }
    if (!data.app_secret) {
      delete data.app_secret
    }

    await updateOAuthConfig(form.value.id, data)
    ElMessage.success('配置更新成功')
    dialogVisible.value = false
    loadList()
  } catch (error) {
    ElMessage.error('配置更新失败')
  } finally {
    submitting.value = false
  }
}

// 对话框关闭
const handleDialogClose = () => {
  formRef.value?.resetFields()
  form.value = {
    id: null,
    platform: '',
    platform_name: '',
    app_id: '',
    app_secret: '',
    redirect_uri: '',
    scope: '',
    sort_order: 0,
    is_enabled: 0,
    remark: ''
  }
}

onMounted(() => {
  loadList()
})
</script>

<style scoped>
.oauth-config-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-tip {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
}
</style>
