<template>
  <div class="queue-monitor">
    <!--统计卡片 -->
    <el-row :gutter="20" class="stats-cards">
      <el-col :span="6" v-for="(queue, name) in queueStats" :key="name">
        <el-card shadow="hover">
          <div class="stat-card">
            <div class="stat-icon" :class="`icon-${name}`">
              <el-icon :size="32">
                <Picture v-if="name === 'ai-image'" />
                <Crop v-else-if="name === 'thumbnail'" />
                <Stamp v-else-if="name === 'watermark'" />
                <Film v-else />
              </el-icon>
            </div>
            <div class="stat-info">
              <div class="stat-title">{{ getQueueTitle(name) }}</div>
              <div class="stat-numbers">
                <span class="pending">待处理: {{ queue.pending }}</span>
                <span class="processing">处理中: {{ queue.processing }}</span>
              </div>
              <div class="stat-complete">
                <span class="success">成功: {{ queue.completed }}</span>
                <span class="failed">失败: {{ queue.failed }}</span>
              </div>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 操作按钮 -->
    <div class="toolbar">
      <el-button-group>
        <el-button :icon="Refresh" @click="refreshStats">刷新统计</el-button>
        <el-button :icon="Delete" type="danger" @click="clearQueue">清空队列</el-button>
      </el-button-group>

      <el-switch
        v-model="autoRefresh"
        active-text="自动刷新"
        inactive-text=""
        style="margin-left: 20px"
      />
    </div>

    <!-- 任务列表 -->
    <el-tabs v-model="activeTab" @tab-change="handleTabChange">
      <el-tab-pane label="AI图片生成" name="ai-image">
        <AiImageTasks />
      </el-tab-pane>
      <el-tab-pane label="视频转码" name="video">
        <VideoTranscodeTasks />
      </el-tab-pane>
      <el-tab-pane label="队列日志" name="logs">
        <QueueLogs />
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onBeforeUnmount } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Refresh, Delete, Picture, Crop, Stamp, Film } from '@element-plus/icons-vue'
import { getQueueStats, clearQueueData } from '@/api/queue'
import AiImageTasks from './AiImageTasks.vue'
import VideoTranscodeTasks from './VideoTranscodeTasks.vue'
import QueueLogs from './QueueLogs.vue'

const autoRefresh = ref(true)
const activeTab = ref('ai-image')
let refreshTimer = null

const queueStats = reactive({
  'ai-image': {
    pending: 0,
    processing: 0,
    completed: 0,
    failed: 0
  },
  thumbnail: {
    pending: 0,
    processing: 0,
    completed: 0,
    failed: 0
  },
  watermark: {
    pending: 0,
    processing: 0,
    completed: 0,
    failed: 0
  },
  video: {
    pending: 0,
    processing: 0,
    completed: 0,
    failed: 0
  }
})

// 获取队列标题
const getQueueTitle = (name) => {
  const titles = {
    'ai-image': 'AI图片生成',
    thumbnail: '缩略图生成',
    watermark: '水印处理',
    video: '视频转码'
  }
  return titles[name] || name
}

// 刷新统计
const refreshStats = async () => {
  try {
    const { data } = await getQueueStats()

    if (data.queues) {
      Object.keys(queueStats).forEach(key => {
        if (data.queues[key]) {
          Object.assign(queueStats[key], data.queues[key])
        }
      })
    }
  } catch (error) {
    console.error('获取队列统计失败', error)
  }
}

// 清空队列
const clearQueue = async () => {
  try {
    await ElMessageBox.confirm(
      '确定要清空所有队列吗？此操作不可恢复！',
      '警告',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    await clearQueueData()
    ElMessage.success('队列已清空')
    refreshStats()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('清空失败：' + error.message)
    }
  }
}

// 标签页切换
const handleTabChange = (tab) => {
  // 可以在这里加载对应标签页的数据
  console.log('切换到标签页：', tab)
}

// 启动自动刷新
const startAutoRefresh = () => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
  }

  refreshTimer = setInterval(() => {
    if (autoRefresh.value) {
      refreshStats()
    }
  }, 5000) // 每5秒刷新一次
}

// 生命周期
onMounted(() => {
  refreshStats()
  startAutoRefresh()
})

onBeforeUnmount(() => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
  }
})
</script>

<style scoped lang="scss">
.queue-monitor {
  .stats-cards {
    margin-bottom: 20px;

    .stat-card {
      display: flex;
      align-items: center;

      .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;

        &.icon-ai-image {
          background-color: #ecf5ff;
          color: #409eff;
        }

        &.icon-thumbnail {
          background-color: #f0f9ff;
          color: #67c23a;
        }

        &.icon-watermark {
          background-color: #fdf6ec;
          color: #e6a23c;
        }

        &.icon-video {
          background-color: #fef0f0;
          color: #f56c6c;
        }
      }

      .stat-info {
        flex: 1;

        .stat-title {
          font-size: 14px;
          color: #606266;
          margin-bottom: 8px;
        }

        .stat-numbers {
          margin-bottom: 4px;

          span {
            font-size: 13px;
            margin-right: 15px;

            &.pending {
              color: #909399;
            }

            &.processing {
              color: #409eff;
              font-weight: 600;
            }
          }
        }

        .stat-complete {
          font-size: 12px;

          span {
            margin-right: 15px;

            &.success {
              color: #67c23a;
            }

            &.failed {
              color: #f56c6c;
            }
          }
        }
      }
    }
  }

  .toolbar {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
  }
}
</style>
