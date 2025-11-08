<template>
  <div class="dashboard" v-loading="loading">
    <!-- 顶部欢迎区域 -->
    <div class="welcome-header">
      <div class="welcome-left">
        <h1>欢迎使用逍遥内容管理系统</h1>
        <p class="welcome-subtitle">
          <el-tag type="success" size="small">v{{ systemInfo.system_version }}</el-tag>
          <span style="margin-left: 10px; color: #ffe;">轻量级、高性能的PHP内容管理系统</span>
        </p>
      </div>
      <div class="welcome-right">
        <el-statistic title="系统运行时长" :value="stats.system_uptime || '正在获取...'" />
      </div>
    </div>

    <!-- 基础统计 -->
    <el-row :gutter="20" style="margin-top: 30px;">
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-icon" style="background-color: #409EFF;">
              <el-icon :size="40"><document /></el-icon>
            </div>
            <div class="stat-content">
              <div class="stat-title">文章总数</div>
              <div class="stat-value">{{ stats.articles }}</div>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-icon" style="background-color: #67C23A;">
              <el-icon :size="40"><check /></el-icon>
            </div>
            <div class="stat-content">
              <div class="stat-title">已发布</div>
              <div class="stat-value">{{ stats.published_articles }}</div>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-icon" style="background-color: #E6A23C;">
              <el-icon :size="40"><edit-pen /></el-icon>
            </div>
            <div class="stat-content">
              <div class="stat-title">草稿</div>
              <div class="stat-value">{{ stats.draft_articles }}</div>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-icon" style="background-color: #F56C6C;">
              <el-icon :size="40"><view /></el-icon>
            </div>
            <div class="stat-content">
              <div class="stat-title">今日浏览</div>
              <div class="stat-value">{{ stats.today_views }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 更多统计 -->
    <el-row :gutter="20" style="margin-top: 20px;">
      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card-small">
            <el-icon :size="24" color="#409EFF"><folder /></el-icon>
            <span class="stat-small-label">分类</span>
            <span class="stat-small-value">{{ stats.categories }}</span>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card-small">
            <el-icon :size="24" color="#67C23A"><collection-tag /></el-icon>
            <span class="stat-small-label">标签</span>
            <span class="stat-small-value">{{ stats.tags }}</span>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card-small">
            <el-icon :size="24" color="#E6A23C"><picture /></el-icon>
            <span class="stat-small-label">媒体文件</span>
            <span class="stat-small-value">{{ stats.media }}</span>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card shadow="hover">
          <div class="stat-card-small">
            <el-icon :size="24" color="#F56C6C"><user /></el-icon>
            <span class="stat-small-label">用户</span>
            <span class="stat-small-value">{{ stats.users }}</span>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 快捷功能 -->
    <el-card style="margin-top: 20px;">
      <template #header>
        <div style="display: flex; align-items: center; gap: 10px;">
          <el-icon><lightning /></el-icon>
          <span>快捷功能</span>
        </div>
      </template>
      <div class="quick-links">
        <el-button type="primary" @click="$router.push('/articles/create')">
          <el-icon><edit /></el-icon>
          创建文章
        </el-button>
        <el-button type="success" @click="$router.push('/pages/create')">
          <el-icon><document-add /></el-icon>
          创建单页
        </el-button>
        <el-button type="info" @click="$router.push('/media')">
          <el-icon><picture /></el-icon>
          媒体库
        </el-button>
        <el-button @click="$router.push('/categories')">
          <el-icon><folder /></el-icon>
          分类管理
        </el-button>
        <el-button @click="$router.push('/tags')">
          <el-icon><collection-tag /></el-icon>
          标签管理
        </el-button>
        <el-button type="warning" @click="$router.push('/template-editor')">
          <el-icon><edit-pen /></el-icon>
          模板编辑
        </el-button>
        <el-button @click="$router.push('/build')">
          <el-icon><document-copy /></el-icon>
          静态生成
        </el-button>
        <el-button @click="$router.push('/sitemap')">
          <el-icon><compass /></el-icon>
          Sitemap
        </el-button>
        <el-button @click="$router.push('/seo-settings')">
          <el-icon><trend-charts /></el-icon>
          SEO设置
        </el-button>
        <el-button @click="$router.push('/cache')">
          <el-icon><refresh /></el-icon>
          缓存管理
        </el-button>
        <el-button @click="$router.push('/config')">
          <el-icon><setting /></el-icon>
          系统设置
        </el-button>
        <el-button @click="$router.push('/logs')">
          <el-icon><tickets /></el-icon>
          操作日志
        </el-button>
      </div>
    </el-card>

    <!-- 服务器信息和系统信息 -->
    <el-row :gutter="20" style="margin-top: 20px;">
      <el-col :span="12">
        <el-card>
          <template #header>
            <div style="display: flex; align-items: center; gap: 10px;">
              <el-icon><monitor /></el-icon>
              <span>服务器信息</span>
            </div>
          </template>
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">PHP版本：</span>
              <span class="info-value">{{ serverInfo.php_version }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">服务器系统：</span>
              <span class="info-value">{{ serverInfo.server_os }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Web服务器：</span>
              <span class="info-value">{{ serverInfo.server_software }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">数据库版本：</span>
              <span class="info-value">{{ serverInfo.database_version }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">磁盘使用：</span>
              <span class="info-value">
                {{ serverInfo.disk_free }} / {{ serverInfo.disk_total }}
                ({{ serverInfo.disk_usage_percent }}%)
              </span>
            </div>
            <div class="info-item">
              <span class="info-label">内存限制：</span>
              <span class="info-value">{{ serverInfo.memory_limit }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">上传限制：</span>
              <span class="info-value">{{ serverInfo.upload_max_filesize }}</span>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="12">
        <el-card>
          <template #header>
            <div style="display: flex; align-items: center; gap: 10px;">
              <el-icon><info-filled /></el-icon>
              <span>系统信息</span>
            </div>
          </template>
          <div class="info-list">
            <div class="info-item">
              <span class="info-label">系统名称：</span>
              <span class="info-value">{{ systemInfo.system_name }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">系统版本：</span>
              <span class="info-value">{{ systemInfo.system_version }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">ThinkPHP版本：</span>
              <span class="info-value">{{ systemInfo.thinkphp_version }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">开发团队：</span>
              <span class="info-value">{{ systemInfo.system_author }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">授权协议：</span>
              <span class="info-value">{{ systemInfo.system_license }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">QQ交流群：</span>
              <span class="info-value">{{ systemInfo.qq_group }}</span>
            </div>
            <div class="info-item" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #EBEEF5;">
              <span class="info-copyright">{{ systemInfo.system_copyright }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getDashboardStats, getServerInfo, getSystemInfo } from '@/api/dashboard'

const stats = ref({
  articles: 0,
  published_articles: 0,
  draft_articles: 0,
  categories: 0,
  tags: 0,
  media: 0,
  users: 0,
  pages: 0,
  today_articles: 0,
  today_views: 0
})

const serverInfo = ref({
  php_version: '-',
  php_sapi: '-',
  server_os: '-',
  server_software: '-',
  database_type: '-',
  database_version: '-',
  disk_total: '-',
  disk_free: '-',
  disk_usage_percent: 0,
  memory_limit: '-',
  memory_usage: '-',
  memory_peak: '-',
  max_execution_time: '-',
  upload_max_filesize: '-',
  post_max_size: '-'
})

const systemInfo = ref({
  system_name: '-',
  system_version: '-',
  system_author: '-',
  system_copyright: '-',
  system_license: '-',
  qq_group: '-',
  thinkphp_version: '-'
})

const loading = ref(false)

const fetchStats = async () => {
  try {
    const res = await getDashboardStats()
    stats.value = res.data
  } catch (error) {
    ElMessage.error('获取统计数据失败')
  }
}

const fetchServerInfo = async () => {
  try {
    const res = await getServerInfo()
    serverInfo.value = res.data
  } catch (error) {
    console.error('获取服务器信息失败', error)
  }
}

const fetchSystemInfo = async () => {
  try {
    const res = await getSystemInfo()
    systemInfo.value = res.data
  } catch (error) {
    console.error('获取系统信息失败', error)
  }
}

const fetchAllData = async () => {
  loading.value = true
  try {
    await Promise.all([
      fetchStats(),
      fetchServerInfo(),
      fetchSystemInfo()
    ])
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAllData()
})
</script>

<style scoped>
.welcome-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px;
  color: white;
  margin-bottom: 10px;
}

.welcome-left h1 {
  margin: 0;
  font-size: 28px;
  font-weight: 600;
  color: white;
}

.welcome-subtitle {
  margin: 10px 0 0 0;
  display: flex;
  align-items: center;
}

.welcome-right {
  text-align: right;
}

.welcome-right :deep(.el-statistic__head) {
  color: rgba(255, 255, 255, 0.8);
}

.welcome-right :deep(.el-statistic__content) {
  color: white;
  font-weight: bold;
}

.dashboard h1 {
  margin: 0;
  color: #303133;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 15px;
}

.stat-icon {
  width: 70px;
  height: 70px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stat-content {
  flex: 1;
}

.stat-title {
  font-size: 14px;
  color: #909399;
  margin-bottom: 5px;
}

.stat-value {
  font-size: 28px;
  font-weight: bold;
  color: #303133;
}

.stat-card-small {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 0;
}

.stat-small-label {
  flex: 1;
  font-size: 14px;
  color: #606266;
}

.stat-small-value {
  font-size: 20px;
  font-weight: bold;
  color: #303133;
}

.quick-links {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
}

.info-list {
  padding: 10px 0;
}

.info-item {
  display: flex;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px dashed #EBEEF5;
}

.info-item:last-child {
  border-bottom: none;
}

.info-label {
  min-width: 120px;
  font-size: 14px;
  color: #909399;
}

.info-value {
  flex: 1;
  font-size: 14px;
  color: #303133;
  font-weight: 500;
}

.info-copyright {
  display: block;
  text-align: center;
  font-size: 13px;
  color: #909399;
  width: 100%;
}
</style>
