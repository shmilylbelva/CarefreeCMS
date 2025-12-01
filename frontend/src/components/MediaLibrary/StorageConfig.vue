<template>
  <div class="storage-config">
    <!-- 顶部工具栏 -->
    <div class="toolbar">
      <el-button type="primary" :icon="Plus" @click="showAddDialog">
        添加存储配置
      </el-button>
      <el-button :icon="Refresh" @click="loadConfigs">刷新</el-button>
    </div>

    <!-- 配置列表 -->
    <el-table :data="configList" v-loading="loading">
      <el-table-column prop="name" label="配置名称" width="200" />
      <el-table-column label="驱动类型" width="150">
        <template #default="{ row }">
          <el-tag :type="getDriverTagType(row.driver)">
            {{ getDriverName(row.driver) }}
          </el-tag>
        </template>
      </el-table-column>
      <el-table-column prop="description" label="描述" />
      <el-table-column label="状态" width="100">
        <template #default="{ row }">
          <el-switch
            v-model="row.is_enabled"
            :active-value="1"
            :inactive-value="0"
            @change="handleStatusChange(row)"
          />
        </template>
      </el-table-column>
      <el-table-column label="默认" width="80">
        <template #default="{ row }">
          <el-tag v-if="row.is_default" type="success">是</el-tag>
          <span v-else>-</span>
        </template>
      </el-table-column>
      <el-table-column label="操作" width="280" fixed="right">
        <template #default="{ row }">
          <el-button size="small" :icon="Connection" @click="testConnection(row)">
            测试连接
          </el-button>
          <el-button size="small" :icon="Star" @click="setDefault(row)" v-if="!row.is_default">
            设为默认
          </el-button>
          <el-button size="small" :icon="Edit" @click="handleEdit(row)">
            编辑
          </el-button>
          <el-button
            size="small"
            type="danger"
            :icon="Delete"
            @click="handleDelete(row)"
            :disabled="row.is_default"
          >
            删除
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="700px"
      @close="handleDialogClose"
    >
      <el-form :model="formData" :rules="formRules" ref="formRef" label-width="120px">
        <el-form-item label="配置名称" prop="name">
          <el-input v-model="formData.name" placeholder="请输入配置名称" />
        </el-form-item>

        <el-form-item label="存储驱动" prop="driver">
          <el-select
            v-model="formData.driver"
            placeholder="请选择存储驱动"
            @change="handleDriverChange"
            :disabled="isEdit"
          >
            <el-option
              v-for="driver in drivers"
              :key="driver.value"
              :label="driver.label"
              :value="driver.value"
            >
              <div style="display: flex; align-items: center;">
                <span>{{ driver.label }}</span>
                <span style="margin-left: 10px; font-size: 12px; color: #909399;">
                  {{ driver.description }}
                </span>
              </div>
            </el-option>
          </el-select>
        </el-form-item>

        <!-- 动态配置项 -->
        <template v-if="formData.driver">
          <el-divider content-position="left">驱动配置</el-divider>

          <template v-for="(config, key) in driverTemplate" :key="key">
            <el-form-item
              :label="config.label"
              :prop="`config_data.${key}`"
              :rules="config.required ? [{ required: true, message: '请填写' + config.label }] : []"
            >
              <el-input
                v-if="config.type === 'text' || config.type === 'password'"
                v-model="formData.config_data[key]"
                :type="config.type"
                :placeholder="config.placeholder || config.label"
              >
                <template #append v-if="config.description">
                  <el-tooltip :content="config.description" placement="top">
                    <el-icon><QuestionFilled /></el-icon>
                  </el-tooltip>
                </template>
              </el-input>

              <el-switch
                v-else-if="config.type === 'boolean'"
                v-model="formData.config_data[key]"
                :active-value="true"
                :inactive-value="false"
              />
            </el-form-item>
          </template>
        </template>

        <el-form-item label="描述">
          <el-input
            v-model="formData.description"
            type="textarea"
            :rows="3"
            placeholder="配置描述（可选）"
          />
        </el-form-item>

        <el-form-item label="启用">
          <el-switch v-model="formData.is_enabled" :active-value="1" :inactive-value="0" />
        </el-form-item>

        <el-form-item label="设为默认">
          <el-switch v-model="formData.is_default" :active-value="1" :inactive-value="0" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">
          确定
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Plus,
  Refresh,
  Edit,
  Delete,
  Connection,
  Star,
  QuestionFilled
} from '@element-plus/icons-vue'
import {
  getStorageConfigs,
  createStorageConfig,
  updateStorageConfig,
  deleteStorageConfig,
  testStorageConfig,
  setDefaultStorage,
  getDrivers,
  getDriverTemplate
} from '@/api/storage'

const loading = ref(false)
const submitting = ref(false)
const dialogVisible = ref(false)
const isEdit = ref(false)
const configList = ref([])
const drivers = ref([])
const driverTemplate = ref({})
const formRef = ref(null)

const formData = reactive({
  id: null,
  name: '',
  driver: '',
  config_data: {},
  description: '',
  is_enabled: 1,
  is_default: 0
})

const formRules = {
  name: [{ required: true, message: '请输入配置名称', trigger: 'blur' }],
  driver: [{ required: true, message: '请选择存储驱动', trigger: 'change' }]
}

const dialogTitle = computed(() => isEdit.value ? '编辑存储配置' : '添加存储配置')

// 加载配置列表
const loadConfigs = async () => {
  loading.value = true
  try {
    const { data } = await getStorageConfigs()
    configList.value = data.list || data
  } catch (error) {
    ElMessage.error('加载失败：' + error.message)
  } finally {
    loading.value = false
  }
}

// 加载驱动列表
const loadDrivers = async () => {
  try {
    const { data } = await getDrivers()
    drivers.value = data
  } catch (error) {
    console.error('加载驱动失败', error)
  }
}

// 驱动名称
const getDriverName = (driver) => {
  const map = {
    local: '本地存储',
    aliyun_oss: '阿里云OSS',
    tencent_cos: '腾讯云COS',
    qiniu: '七牛云'
  }
  return map[driver] || driver
}

// 驱动标签类型
const getDriverTagType = (driver) => {
  const map = {
    local: '',
    aliyun_oss: 'success',
    tencent_cos: 'warning',
    qiniu: 'danger'
  }
  return map[driver] || ''
}

// 驱动改变
const handleDriverChange = async (driver) => {
  try {
    const { data } = await getDriverTemplate(driver)
    driverTemplate.value = data
    formData.config_data = {}

    // 设置默认值
    Object.keys(data).forEach(key => {
      if (data[key].default !== undefined) {
        formData.config_data[key] = data[key].default
      }
    })
  } catch (error) {
    console.error('加载驱动模板失败', error)
  }
}

// 显示添加对话框
const showAddDialog = () => {
  isEdit.value = false
  resetForm()
  dialogVisible.value = true
}

// 编辑
const handleEdit = async (row) => {
  isEdit.value = true
  Object.assign(formData, {
    id: row.id,
    name: row.name,
    driver: row.driver,
    config_data: row.config_data || {},
    description: row.description,
    is_enabled: row.is_enabled,
    is_default: row.is_default
  })

  // 加载驱动模板
  await handleDriverChange(row.driver)

  dialogVisible.value = true
}

// 删除
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm('确定要删除此配置吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })

    await deleteStorageConfig(row.id)
    ElMessage.success('删除成功')
    loadConfigs()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败：' + error.message)
    }
  }
}

// 状态改变
const handleStatusChange = async (row) => {
  try {
    await updateStorageConfig(row.id, { is_enabled: row.is_enabled })
    ElMessage.success('状态更新成功')
  } catch (error) {
    ElMessage.error('更新失败：' + error.message)
    // 还原状态
    row.is_enabled = row.is_enabled === 1 ? 0 : 1
  }
}

// 测试连接
const testConnection = async (row) => {
  const loading = ElMessage({
    message: '正在测试连接...',
    type: 'info',
    duration: 0
  })

  try {
    const { data } = await testStorageConfig({
      driver: row.driver,
      config_data: row.config_data
    })

    loading.close()

    if (data.success) {
      ElMessage.success(`连接成功！Provider: ${data.provider}, Bucket: ${data.bucket}`)
    } else {
      ElMessage.error('连接失败：' + data.message)
    }
  } catch (error) {
    loading.close()
    ElMessage.error('测试失败：' + error.message)
  }
}

// 设为默认
const setDefault = async (row) => {
  try {
    await setDefaultStorage(row.id)
    ElMessage.success('设置成功')
    loadConfigs()
  } catch (error) {
    ElMessage.error('设置失败：' + error.message)
  }
}

// 提交表单
const handleSubmit = async () => {
  try {
    await formRef.value.validate()

    submitting.value = true

    if (isEdit.value) {
      await updateStorageConfig(formData.id, formData)
      ElMessage.success('更新成功')
    } else {
      await createStorageConfig(formData)
      ElMessage.success('创建成功')
    }

    dialogVisible.value = false
    loadConfigs()
  } catch (error) {
    if (error !== false) {
      ElMessage.error('保存失败：' + error.message)
    }
  } finally {
    submitting.value = false
  }
}

// 重置表单
const resetForm = () => {
  Object.assign(formData, {
    id: null,
    name: '',
    driver: '',
    config_data: {},
    description: '',
    is_enabled: 1,
    is_default: 0
  })
  driverTemplate.value = {}
  formRef.value?.resetFields()
}

// 对话框关闭
const handleDialogClose = () => {
  resetForm()
}

// 初始化
const init = () => {
  loadConfigs()
  loadDrivers()
}

init()
</script>

<style scoped lang="scss">
.storage-config {
  .toolbar {
    margin-bottom: 20px;
  }
}
</style>
