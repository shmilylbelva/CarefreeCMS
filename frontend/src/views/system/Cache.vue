<template>
  <div class="cache-management">
    <el-row :gutter="20">
      <el-col :span="12">
        <el-card shadow="never">
          <template #header>
            <div class="card-header">
              <span>缓存信息</span>
              <el-button type="primary" @click="loadCacheInfo" :icon="Refresh" circle />
            </div>
          </template>
          <el-descriptions :column="2" border>
            <el-descriptions-item label="缓存类型">{{ cacheInfo.type }}</el-descriptions-item>
            <el-descriptions-item label="驱动">{{ cacheInfo.driver }}</el-descriptions-item>
            <el-descriptions-item label="版本" v-if="cacheInfo.stats?.version">
              {{ cacheInfo.stats.version }}
            </el-descriptions-item>
            <el-descriptions-item label="已用内存" v-if="cacheInfo.stats?.used_memory_human">
              {{ cacheInfo.stats.used_memory_human }}
            </el-descriptions-item>
            <el-descriptions-item label="键数量" v-if="cacheInfo.stats?.keys_count">
              {{ cacheInfo.stats.keys_count }}
            </el-descriptions-item>
            <el-descriptions-item label="命中率" v-if="cacheInfo.stats?.hit_rate">
              {{ cacheInfo.stats.hit_rate }}%
            </el-descriptions-item>
            <el-descriptions-item label="文件数量" v-if="cacheInfo.stats?.files_count">
              {{ cacheInfo.stats.files_count }}
            </el-descriptions-item>
            <el-descriptions-item label="总大小" v-if="cacheInfo.stats?.total_size_human">
              {{ cacheInfo.stats.total_size_human }}
            </el-descriptions-item>
          </el-descriptions>
        </el-card>
      </el-col>

      <el-col :span="12">
        <el-card shadow="never">
          <template #header>
            <div class="card-header">
              <span>缓存驱动设置</span>
            </div>
          </template>

          <el-form label-width="100px">
            <el-form-item label="当前驱动">
              <el-tag :type="currentDriver === 'redis' ? 'success' : 'info'" size="large">
                {{ currentDriver === 'redis' ? 'Redis' : 'File (文件)' }}
              </el-tag>
            </el-form-item>

            <el-form-item label="切换驱动">
              <el-radio-group v-model="driverForm.driver" @change="handleDriverChange">
                <el-radio label="file">File (文件缓存)</el-radio>
                <el-radio label="redis">Redis (内存缓存)</el-radio>
              </el-radio-group>
            </el-form-item>

            <template v-if="driverForm.driver === 'redis'">
              <el-form-item label="服务器地址">
                <el-input v-model="driverForm.host" placeholder="127.0.0.1" />
              </el-form-item>
              <el-form-item label="端口">
                <el-input-number v-model="driverForm.port" :min="1" :max="65535" />
              </el-form-item>
              <el-form-item label="密码">
                <el-input v-model="driverForm.password" type="password" placeholder="留空表示无密码" show-password />
              </el-form-item>
              <el-form-item>
                <el-button type="info" @click="handleTestRedis" :loading="testing">测试连接</el-button>
              </el-form-item>
            </template>

            <el-form-item>
              <el-button
                type="primary"
                @click="handleSwitchDriver"
                :loading="switching"
                :disabled="currentDriver === driverForm.driver"
              >
                应用更改
              </el-button>
            </el-form-item>
          </el-form>

          <el-alert type="warning" :closable="false" style="margin-top: 10px;">
            <template #title>
              <div style="font-size: 12px; line-height: 1.6;">
                <strong>注意：</strong><br>
                • 切换缓存驱动会清空现有缓存<br>
                • Redis需要先安装PHP Redis扩展和Redis服务器<br>
                • 可以点击"测试连接"按钮验证Redis配置<br>
                • 切换后需要刷新页面使配置生效
              </div>
            </template>
          </el-alert>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="20" class="mt-4">
      <el-col :span="8">
        <el-card shadow="hover" class="action-card">
          <el-icon :size="48" color="#409EFF"><Delete /></el-icon>
          <h3>清空所有缓存</h3>
          <p>清除所有缓存数据</p>
          <el-button type="danger" @click="handleClearAll">立即清空</el-button>
        </el-card>
      </el-col>

      <el-col :span="8">
        <el-card shadow="hover" class="action-card">
          <el-icon :size="48" color="#67C23A"><DocumentDelete /></el-icon>
          <h3>清除模板缓存</h3>
          <p>清除编译后的模板文件</p>
          <el-button type="warning" @click="handleClearTemplate">清除模板</el-button>
        </el-card>
      </el-col>

      <el-col :span="8">
        <el-card shadow="hover" class="action-card">
          <el-icon :size="48" color="#E6A23C"><Promotion /></el-icon>
          <h3>缓存预热</h3>
          <p>预加载常用数据到缓存</p>
          <el-button type="success" @click="handleWarmup">开始预热</el-button>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox, ElLoading } from 'element-plus'
import { Refresh, Delete, DocumentDelete, Promotion } from '@element-plus/icons-vue'
import {
  getCacheInfo,
  clearAll,
  clearTemplate,
  warmup,
  getDriver,
  switchDriver,
  testRedis
} from '@/api/cache'

const cacheInfo = ref({})
const currentDriver = ref('file')
const switching = ref(false)
const testing = ref(false)

const driverForm = reactive({
  driver: 'file',
  host: '127.0.0.1',
  port: 6379,
  password: ''
})

onMounted(() => {
  loadCacheInfo()
  loadDriverInfo()

  // 检查是否刚切换过驱动，如果是则刷新缓存信息
  const justSwitched = localStorage.getItem('cache_driver_switched')
  if (justSwitched) {
    // 延迟刷新以确保配置已更新
    setTimeout(() => {
      loadCacheInfo()
      loadDriverInfo()
      localStorage.removeItem('cache_driver_switched')
    }, 500)
  }
})

const loadCacheInfo = async () => {
  const { data } = await getCacheInfo()
  cacheInfo.value = data
}

const loadDriverInfo = async () => {
  try {
    const { data } = await getDriver()
    currentDriver.value = data.driver
    driverForm.driver = data.driver

    if (data.driver === 'redis' && data.config) {
      driverForm.host = data.config.host || '127.0.0.1'
      driverForm.port = data.config.port || 6379
      driverForm.password = data.config.password || ''
    }
  } catch (error) {
    console.error('加载驱动信息失败:', error)
  }
}

const handleDriverChange = () => {
  // 当切换到Redis时，如果字段为空，设置默认值
  if (driverForm.driver === 'redis') {
    if (!driverForm.host) driverForm.host = '127.0.0.1'
    if (!driverForm.port) driverForm.port = 6379
  }
}

const handleTestRedis = async () => {
  testing.value = true
  try {
    await testRedis({
      host: driverForm.host,
      port: driverForm.port,
      password: driverForm.password
    })
    ElMessage.success('Redis连接测试成功')
  } catch (error) {
    ElMessage.error(error.message || 'Redis连接测试失败')
  } finally {
    testing.value = false
  }
}

const handleSwitchDriver = async () => {
  try {
    await ElMessageBox.confirm(
      '切换缓存驱动会清空现有缓存，确定要继续吗？',
      '警告',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    switching.value = true

    const requestData = {
      driver: driverForm.driver
    }

    if (driverForm.driver === 'redis') {
      requestData.config = {
        host: driverForm.host,
        port: driverForm.port,
        password: driverForm.password
      }
    }

    const { message } = await switchDriver(requestData)
    ElMessage.success(message || '缓存驱动切换成功')

    // 设置标记，表示刚切换过驱动
    localStorage.setItem('cache_driver_switched', 'true')

    // 延迟刷新页面
    setTimeout(() => {
      location.reload()
    }, 1500)
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '缓存驱动切换失败')
    }
  } finally {
    switching.value = false
  }
}

const handleClearAll = async () => {
  try {
    await ElMessageBox.confirm('确定要清空所有缓存吗？', '警告', { type: 'warning' })
    await clearAll()
    ElMessage.success('缓存已清空')
    loadCacheInfo()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('清空失败')
    }
  }
}

const handleClearTemplate = async () => {
  const loading = ElLoading.service({ text: '正在清除...' })
  try {
    const { data } = await clearTemplate()
    ElMessage.success(data.message)
  } finally {
    loading.close()
  }
}

const handleWarmup = async () => {
  const loading = ElLoading.service({ text: '正在预热...' })
  try {
    const { data } = await warmup()
    ElMessage.success(`${data.message}，已预热 ${data.count} 项`)
    loadCacheInfo()
  } finally {
    loading.close()
  }
}
</script>

<style scoped>
.cache-management {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.mt-2 {
  margin-top: 10px;
}

.mt-4 {
  margin-top: 20px;
}

.ml-2 {
  margin-left: 10px;
}

.main-tabs {
  margin-top: 20px;
}

.action-card {
  text-align: center;
  padding: 20px;
  cursor: pointer;
  transition: all 0.3s;
}

.action-card:hover {
  transform: translateY(-5px);
}

.action-card .el-icon {
  margin-bottom: 15px;
}

.action-card h3 {
  margin: 10px 0;
  font-size: 18px;
}

.action-card p {
  color: #909399;
  margin-bottom: 15px;
}

.tool-item h4 {
  margin: 0 0 10px 0;
  font-size: 16px;
}

.tool-item p {
  color: #909399;
  margin-bottom: 15px;
}

.performance-result {
  margin-top: 20px;
}
</style>
