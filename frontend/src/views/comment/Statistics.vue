<template>
  <div class="comment-statistics-container">
    <!-- 统计卡片 -->
    <el-row :gutter="20">
      <el-col :span="6">
        <el-card class="stat-card">
          <div class="stat-content">
            <div class="stat-icon total">
              <el-icon :size="40"><ChatDotRound /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-label">总评论数</div>
              <div class="stat-value">{{ statistics.total || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card class="stat-card">
          <div class="stat-content">
            <div class="stat-icon approved">
              <el-icon :size="40"><Select /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-label">已通过</div>
              <div class="stat-value">{{ statistics.approved || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card class="stat-card">
          <div class="stat-content">
            <div class="stat-icon pending">
              <el-icon :size="40"><Clock /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-label">待审核</div>
              <div class="stat-value">{{ statistics.pending || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>

      <el-col :span="6">
        <el-card class="stat-card">
          <div class="stat-content">
            <div class="stat-icon rejected">
              <el-icon :size="40"><CloseBold /></el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-label">已拒绝</div>
              <div class="stat-value">{{ statistics.rejected || 0 }}</div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 时间维度统计 -->
    <el-row :gutter="20" style="margin-top: 20px">
      <el-col :span="8">
        <el-card>
          <template #header>
            <span>今日新增</span>
          </template>
          <div class="time-stat">
            <el-statistic :value="statistics.today || 0">
              <template #suffix>条</template>
            </el-statistic>
          </div>
        </el-card>
      </el-col>

      <el-col :span="8">
        <el-card>
          <template #header>
            <span>本周新增</span>
          </template>
          <div class="time-stat">
            <el-statistic :value="statistics.week || 0">
              <template #suffix>条</template>
            </el-statistic>
          </div>
        </el-card>
      </el-col>

      <el-col :span="8">
        <el-card>
          <template #header>
            <span>本月新增</span>
          </template>
          <div class="time-stat">
            <el-statistic :value="statistics.month || 0">
              <template #suffix>条</template>
            </el-statistic>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 用户类型分布 -->
    <el-row :gutter="20" style="margin-top: 20px">
      <el-col :span="12">
        <el-card>
          <template #header>
            <span>用户类型分布</span>
          </template>
          <div ref="userTypeChartRef" style="height: 300px"></div>
        </el-card>
      </el-col>

      <el-col :span="12">
        <el-card>
          <template #header>
            <span>评论趋势（最近30天）</span>
          </template>
          <div ref="trendChartRef" style="height: 300px"></div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 活跃用户排行 -->
    <el-card style="margin-top: 20px">
      <template #header>
        <div class="card-header">
          <span>活跃用户排行 TOP 10</span>
          <el-select v-model="daysRange" @change="getActiveUsers" style="width: 150px">
            <el-option label="最近7天" :value="7" />
            <el-option label="最近30天" :value="30" />
            <el-option label="最近90天" :value="90" />
          </el-select>
        </div>
      </template>

      <el-table :data="activeUsers" v-loading="activeUsersLoading">
        <el-table-column label="排名" type="index" width="80" :index="(index) => index + 1" />

        <el-table-column label="用户" width="200">
          <template #default="{ row }">
            <div v-if="row.user">
              <div>{{ row.user.nickname || row.user.username }}</div>
              <div class="text-secondary">ID: {{ row.user.id }}</div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="评论数" prop="comment_count" width="120">
          <template #default="{ row }">
            <el-tag type="success">{{ row.comment_count }} 条</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="用户信息">
          <template #default="{ row }">
            <div v-if="row.user">
              <div>邮箱: {{ row.user.email || '-' }}</div>
              <div class="text-secondary">注册时间: {{ row.user.create_time }}</div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="操作" width="150">
          <template #default="{ row }">
            <el-button type="primary" size="small" link @click="viewUserComments(row.user_id)">
              查看评论
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 热门评论 -->
    <el-card style="margin-top: 20px">
      <template #header>
        <span>热门评论 TOP 10</span>
      </template>

      <el-table :data="hotComments" v-loading="hotCommentsLoading">
        <el-table-column label="评论内容" min-width="300">
          <template #default="{ row }">
            <div class="comment-content">{{ row.content }}</div>
            <div class="comment-meta">
              <span v-if="row.article">
                <el-icon><Document /></el-icon>
                {{ row.article.title }}
              </span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="评论者" width="150">
          <template #default="{ row }">
            {{ row.author_name || '匿名' }}
          </template>
        </el-table-column>

        <el-table-column label="热度分数" width="120" sortable prop="hot_score">
          <template #default="{ row }">
            <el-tag type="danger">{{ row.hot_score }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="点赞/点踩" width="120">
          <template #default="{ row }">
            <div>
              <el-icon color="#67c23a"><CaretTop /></el-icon>
              {{ row.like_count || 0 }}
              /
              <el-icon color="#f56c6c"><CaretBottom /></el-icon>
              {{ row.dislike_count || 0 }}
            </div>
          </template>
        </el-table-column>

        <el-table-column label="评论时间" width="160">
          <template #default="{ row }">
            {{ row.create_time }}
          </template>
        </el-table-column>
      </el-table>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { ElMessage } from 'element-plus'
import {
  ChatDotRound,
  Select,
  Clock,
  CloseBold,
  Document,
  CaretTop,
  CaretBottom
} from '@element-plus/icons-vue'
import * as echarts from 'echarts'
import {
  getCommentStatistics,
  getCommentTrend,
  getActiveUsers,
  getHotComments
} from '@/api/comment'
import { useRouter } from 'vue-router'

const router = useRouter()

const statistics = ref({})
const trendData = ref([])
const activeUsers = ref([])
const hotComments = ref([])
const daysRange = ref(30)
const activeUsersLoading = ref(false)
const hotCommentsLoading = ref(false)

const userTypeChartRef = ref(null)
const trendChartRef = ref(null)
let userTypeChart = null
let trendChart = null

// 获取统计数据
const getStatistics = async () => {
  try {
    const { data } = await getCommentStatistics()
    statistics.value = data

    // 等待DOM更新后初始化图表
    await nextTick()
    initUserTypeChart()
  } catch (error) {
    ElMessage.error('获取统计数据失败')
  }
}

// 获取趋势数据
const getTrend = async () => {
  try {
    const { data } = await getCommentTrend()
    trendData.value = data

    await nextTick()
    initTrendChart()
  } catch (error) {
    ElMessage.error('获取趋势数据失败')
  }
}

// 获取活跃用户
const getActiveUsersList = async () => {
  activeUsersLoading.value = true
  try {
    const { data } = await getActiveUsers({ limit: 10, days: daysRange.value })
    activeUsers.value = data
  } catch (error) {
    ElMessage.error('获取活跃用户失败')
  } finally {
    activeUsersLoading.value = false
  }
}

// 获取热门评论
const getHotCommentsList = async () => {
  hotCommentsLoading.value = true
  try {
    const { data } = await getHotComments({ limit: 10 })
    hotComments.value = data
  } catch (error) {
    ElMessage.error('获取热门评论失败')
  } finally {
    hotCommentsLoading.value = false
  }
}

// 初始化用户类型分布图表
const initUserTypeChart = () => {
  if (!userTypeChartRef.value) return

  userTypeChart = echarts.init(userTypeChartRef.value)

  const option = {
    tooltip: {
      trigger: 'item',
      formatter: '{b}: {c} ({d}%)'
    },
    legend: {
      orient: 'vertical',
      left: 'left'
    },
    series: [
      {
        name: '用户类型',
        type: 'pie',
        radius: '50%',
        data: [
          { value: statistics.value.user || 0, name: '注册用户' },
          { value: statistics.value.guest || 0, name: '游客' }
        ],
        emphasis: {
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: 'rgba(0, 0, 0, 0.5)'
          }
        }
      }
    ]
  }

  userTypeChart.setOption(option)
}

// 初始化趋势图表
const initTrendChart = () => {
  if (!trendChartRef.value) return

  trendChart = echarts.init(trendChartRef.value)

  const dates = trendData.value.map(item => item.date)
  const counts = trendData.value.map(item => item.count)

  const option = {
    tooltip: {
      trigger: 'axis'
    },
    xAxis: {
      type: 'category',
      data: dates,
      axisLabel: {
        rotate: 45
      }
    },
    yAxis: {
      type: 'value'
    },
    series: [
      {
        name: '评论数',
        type: 'line',
        data: counts,
        smooth: true,
        areaStyle: {
          color: 'rgba(64, 158, 255, 0.2)'
        },
        itemStyle: {
          color: '#409EFF'
        }
      }
    ],
    grid: {
      left: '3%',
      right: '4%',
      bottom: '15%',
      containLabel: true
    }
  }

  trendChart.setOption(option)
}

// 查看用户评论
const viewUserComments = (userId) => {
  router.push({
    path: '/comment/list',
    query: { user_id: userId }
  })
}

// 监听窗口大小变化
const handleResize = () => {
  userTypeChart?.resize()
  trendChart?.resize()
}

onMounted(() => {
  getStatistics()
  getTrend()
  getActiveUsersList()
  getHotCommentsList()

  window.addEventListener('resize', handleResize)
})

// 组件卸载时清理
import { onBeforeUnmount } from 'vue'
onBeforeUnmount(() => {
  window.removeEventListener('resize', handleResize)
  userTypeChart?.dispose()
  trendChart?.dispose()
})
</script>

<style scoped>
.comment-statistics-container {
  padding: 20px;
}

.stat-card {
  cursor: pointer;
  transition: all 0.3s;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-content {
  display: flex;
  align-items: center;
  gap: 20px;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stat-icon.total {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.approved {
  background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
}

.stat-icon.pending {
  background: linear-gradient(135deg, #fccb90 0%, #d57eeb 100%);
}

.stat-icon.rejected {
  background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.stat-info {
  flex: 1;
}

.stat-label {
  font-size: 14px;
  color: #909399;
  margin-bottom: 8px;
}

.stat-value {
  font-size: 28px;
  font-weight: bold;
  color: #303133;
}

.time-stat {
  padding: 20px 0;
  text-align: center;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.comment-content {
  line-height: 1.6;
  margin-bottom: 8px;
}

.comment-meta {
  font-size: 12px;
  color: #909399;
  display: flex;
  align-items: center;
  gap: 4px;
}

.text-secondary {
  font-size: 12px;
  color: #909399;
  margin-top: 4px;
}
</style>
