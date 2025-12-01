<template>
  <div class="watermark-apply">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>应用水印</span>
        </div>
      </template>

      <el-form :model="formData" label-width="100px">
        <el-form-item label="选择文件">
          <div class="media-selection">
            <el-button @click="handleShowMediaSelector">
              <el-icon><Plus /></el-icon>
              选择图片
            </el-button>
            <span v-if="selectedMedia.length > 0" class="selected-count">
              已选择 {{ selectedMedia.length }} 个文件
            </span>
          </div>
        </el-form-item>

        <!-- 已选择的文件列表 -->
        <el-form-item v-if="selectedMedia.length > 0">
          <div class="selected-media-list">
            <div
              v-for="media in selectedMedia"
              :key="media.id"
              class="media-thumb"
            >
              <img :src="media.file_url" :alt="media.file_name" />
              <div class="media-remove" @click="removeMedia(media.id)">
                <el-icon><Close /></el-icon>
              </div>
            </div>
          </div>
        </el-form-item>

        <el-form-item label="水印预设">
          <el-select
            v-model="formData.preset_id"
            placeholder="请选择水印预设"
            style="width: 300px;"
            @change="handlePresetChange"
          >
            <el-option
              v-for="preset in presets"
              :key="preset.id"
              :label="preset.name"
              :value="preset.id"
            >
              <span>{{ preset.name }}</span>
              <el-tag :type="getTypeTagType(preset.type)" size="small" style="margin-left: 10px;">
                {{ getTypeText(preset.type) }}
              </el-tag>
            </el-option>
          </el-select>
          <el-button
            type="text"
            @click="loadPresets"
            style="margin-left: 10px;"
          >
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </el-form-item>

        <!-- 预设详情 -->
        <el-form-item v-if="currentPreset" label="预设详情">
          <el-descriptions :column="2" border size="small">
            <el-descriptions-item label="类型">
              {{ getTypeText(currentPreset.type) }}
            </el-descriptions-item>
            <el-descriptions-item label="位置">
              {{ getPositionText(currentPreset.position) }}
            </el-descriptions-item>
            <el-descriptions-item label="透明度">
              {{ currentPreset.opacity }}%
            </el-descriptions-item>
            <el-descriptions-item label="文字内容" v-if="currentPreset.text_content">
              {{ currentPreset.text_content }}
            </el-descriptions-item>
          </el-descriptions>
        </el-form-item>

        <el-form-item label="处理方式">
          <el-radio-group v-model="formData.use_queue">
            <el-radio :value="false">立即处理</el-radio>
            <el-radio :value="true">队列处理（批量推荐）</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item>
          <el-button
            type="primary"
            @click="handleApply"
            :loading="applying"
            :disabled="!canApply"
          >
            <el-icon><Check /></el-icon>
            应用水印
          </el-button>
          <el-button @click="handleClear">
            <el-icon><Delete /></el-icon>
            清空选择
          </el-button>
        </el-form-item>
      </el-form>

      <!-- 处理进度 -->
      <el-card v-if="processing" shadow="never" style="margin-top: 20px;">
        <template #header>
          <span>处理进度</span>
        </template>
        <el-progress
          :percentage="progress"
          :status="progressStatus"
        />
        <div class="progress-info">
          <span>已处理: {{ processedCount }} / {{ totalCount }}</span>
          <span v-if="failedCount > 0" style="color: #f56c6c; margin-left: 20px;">
            失败: {{ failedCount }}
          </span>
        </div>
      </el-card>
    </el-card>

    <!-- 处理日志 -->
    <el-card style="margin-top: 20px;">
      <template #header>
        <div class="card-header">
          <span>处理日志</span>
          <el-button size="small" @click="loadLogs">
            <el-icon><Refresh /></el-icon>
            刷新
          </el-button>
        </div>
      </template>

      <el-table :data="logs" v-loading="logsLoading">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column label="文件" min-width="200">
          <template #default="{ row }">
            <div class="log-media">
              <img v-if="row.media" :src="row.media.file_url" class="log-thumb" />
              <span>{{ row.media?.file_name || '-' }}</span>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="水印预设" width="150">
          <template #default="{ row }">
            {{ row.preset?.name || '-' }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.status === 'success'" type="success">成功</el-tag>
            <el-tag v-else-if="row.status === 'failed'" type="danger">失败</el-tag>
            <el-tag v-else type="info">处理中</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="error_message" label="错误信息" min-width="200" show-overflow-tooltip />
        <el-table-column label="处理时间" width="180">
          <template #default="{ row }">
            {{ formatDateTime(row.created_at) }}
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-model:current-page="logPagination.page"
        v-model:page-size="logPagination.pageSize"
        :total="logPagination.total"
        layout="total, prev, pager, next"
        @change="loadLogs"
        style="margin-top: 15px; justify-content: center;"
      />
    </el-card>

    <!-- 媒体选择器对话框（简化版） -->
    <el-dialog
      v-model="showMediaSelector"
      title="选择图片"
      width="800px"
      destroy-on-close
    >
      <div class="media-selector">
        <div class="media-grid" v-loading="mediaLoading">
          <div
            v-for="item in mediaList"
            :key="item.id"
            class="media-item"
            :class="{ selected: isMediaSelected(item.id) }"
            @click="toggleMediaSelection(item)"
          >
            <div class="media-preview">
              <img :src="item.file_url" :alt="item.file_name" />
            </div>
            <div class="media-name">{{ item.file_name }}</div>
          </div>
        </div>

        <el-pagination
          v-model:current-page="mediaPagination.page"
          v-model:page-size="mediaPagination.pageSize"
          :total="mediaPagination.total"
          layout="total, prev, pager, next"
          @change="loadMediaList"
          style="margin-top: 15px; justify-content: center;"
        />
      </div>

      <template #footer>
        <el-button @click="showMediaSelector = false">取消</el-button>
        <el-button type="primary" @click="confirmMediaSelection">
          确定（已选{{ selectedMedia.length }}个）
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus, Close, Check, Delete, Refresh } from '@element-plus/icons-vue'
import {
  getPresets,
  addWatermark,
  batchAddWatermark,
  getWatermarkLogs,
  pushBatchWatermarkJob
} from '@/api/watermark'
import request from '@/api/request'

// 数据
const selectedMedia = ref([])
const presets = ref([])
const currentPreset = ref(null)
const applying = ref(false)
const processing = ref(false)
const processedCount = ref(0)
const totalCount = ref(0)
const failedCount = ref(0)
const logs = ref([])
const logsLoading = ref(false)

const formData = reactive({
  preset_id: null,
  use_queue: false
})

const logPagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})

// 媒体选择器
const showMediaSelector = ref(false)
const mediaList = ref([])
const mediaLoading = ref(false)
const mediaPagination = reactive({
  page: 1,
  pageSize: 12,
  total: 0
})

// 计算属性
const canApply = computed(() => {
  return selectedMedia.value.length > 0 && formData.preset_id
})

const progress = computed(() => {
  if (totalCount.value === 0) return 0
  return Math.round((processedCount.value / totalCount.value) * 100)
})

const progressStatus = computed(() => {
  if (failedCount.value > 0) return 'exception'
  if (processedCount.value === totalCount.value && totalCount.value > 0) return 'success'
  return undefined
})

// 加载预设列表
const loadPresets = async () => {
  try {
    const res = await getPresets({ page: 1, pageSize: 100 })
    presets.value = res.data?.list || res.data || []

    // 自动选择默认预设
    if (!formData.preset_id && presets.value.length > 0) {
      const defaultPreset = presets.value.find(p => p.is_default)
      if (defaultPreset) {
        formData.preset_id = defaultPreset.id
        handlePresetChange(defaultPreset.id)
      }
    }
  } catch (error) {
    ElMessage.error('加载预设失败')
  }
}

// 预设变化
const handlePresetChange = (presetId) => {
  currentPreset.value = presets.value.find(p => p.id === presetId)
}

// 加载媒体列表
const loadMediaList = async () => {
  mediaLoading.value = true
  try {
    const res = await request({
      url: '/media',
      method: 'get',
      params: {
        page: mediaPagination.page,
        page_size: mediaPagination.pageSize,
        type: 'image'
      }
    })
    mediaList.value = res.data?.list || []
    mediaPagination.total = res.data?.total || 0
  } catch (error) {
    ElMessage.error('加载媒体列表失败')
  } finally {
    mediaLoading.value = false
  }
}

// 判断媒体是否已选中
const isMediaSelected = (id) => {
  return selectedMedia.value.some(m => m.id === id)
}

// 切换媒体选择
const toggleMediaSelection = (media) => {
  const index = selectedMedia.value.findIndex(m => m.id === media.id)
  if (index > -1) {
    selectedMedia.value.splice(index, 1)
  } else {
    selectedMedia.value.push(media)
  }
}

// 确认媒体选择
const confirmMediaSelection = () => {
  showMediaSelector.value = false
  ElMessage.success(`已选择 ${selectedMedia.value.length} 个文件`)
}

// 移除媒体
const removeMedia = (id) => {
  const index = selectedMedia.value.findIndex(m => m.id === id)
  if (index > -1) {
    selectedMedia.value.splice(index, 1)
  }
}

// 应用水印
const handleApply = async () => {
  if (!canApply.value) {
    ElMessage.warning('请选择文件和水印预设')
    return
  }

  applying.value = true
  processing.value = true
  processedCount.value = 0
  failedCount.value = 0
  totalCount.value = selectedMedia.value.length

  try {
    const mediaIds = selectedMedia.value.map(m => m.id)

    if (formData.use_queue) {
      // 使用队列处理
      await pushBatchWatermarkJob({
        media_ids: mediaIds,
        preset_id: formData.preset_id
      })
      ElMessage.success('批量水印任务已加入队列')
      processing.value = false
    } else {
      // 立即处理
      if (mediaIds.length === 1) {
        // 单个文件
        await addWatermark({
          media_id: mediaIds[0],
          preset_id: formData.preset_id
        })
        processedCount.value = 1
        ElMessage.success('水印添加成功')
      } else {
        // 批量处理
        for (let i = 0; i < mediaIds.length; i++) {
          try {
            await addWatermark({
              media_id: mediaIds[i],
              preset_id: formData.preset_id
            })
            processedCount.value++
          } catch (error) {
            failedCount.value++
          }
        }

        if (failedCount.value === 0) {
          ElMessage.success(`成功为 ${processedCount.value} 个文件添加水印`)
        } else {
          ElMessage.warning(
            `成功: ${processedCount.value}, 失败: ${failedCount.value}`
          )
        }
      }
      processing.value = false
    }

    loadLogs()
  } catch (error) {
    ElMessage.error(error.message || '处理失败')
    processing.value = false
  } finally {
    applying.value = false
  }
}

// 清空选择
const handleClear = () => {
  selectedMedia.value = []
  formData.preset_id = null
  currentPreset.value = null
  processing.value = false
  processedCount.value = 0
  failedCount.value = 0
  totalCount.value = 0
}

// 加载日志
const loadLogs = async () => {
  logsLoading.value = true
  try {
    const res = await getWatermarkLogs({
      page: logPagination.page,
      pageSize: logPagination.pageSize
    })
    logs.value = res.data?.list || res.data || []
    logPagination.total = res.data?.total || logs.value.length
  } catch (error) {
    ElMessage.error('加载日志失败')
  } finally {
    logsLoading.value = false
  }
}

// 格式化日期时间
const formatDateTime = (datetime) => {
  if (!datetime) return '-'
  return new Date(datetime).toLocaleString('zh-CN')
}

// 获取类型文本
const getTypeText = (type) => {
  const types = {
    text: '文字',
    image: '图片',
    tiled: '平铺'
  }
  return types[type] || type
}

// 获取类型标签类型
const getTypeTagType = (type) => {
  const types = {
    text: 'primary',
    image: 'success',
    tiled: 'warning'
  }
  return types[type] || 'info'
}

// 获取位置文本
const getPositionText = (position) => {
  const positions = {
    'top-left': '左上角',
    'top-right': '右上角',
    'bottom-left': '左下角',
    'bottom-right': '右下角',
    'center': '居中'
  }
  return positions[position] || position
}

// 打开媒体选择器时加载列表
const handleShowMediaSelector = () => {
  showMediaSelector.value = true
  loadMediaList()
}

onMounted(() => {
  loadPresets()
  loadLogs()
})
</script>

<style scoped>
.watermark-apply {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.media-selection {
  display: flex;
  align-items: center;
  gap: 15px;
}

.selected-count {
  color: #409EFF;
  font-size: 14px;
}

.selected-media-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  padding: 10px;
  background-color: #f5f7fa;
  border-radius: 4px;
  max-height: 200px;
  overflow-y: auto;
}

.media-thumb {
  position: relative;
  width: 80px;
  height: 80px;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  overflow: hidden;
}

.media-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.media-remove {
  position: absolute;
  top: 2px;
  right: 2px;
  width: 20px;
  height: 20px;
  background-color: rgba(0, 0, 0, 0.6);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: #fff;
}

.media-remove:hover {
  background-color: #f56c6c;
}

.progress-info {
  margin-top: 10px;
  font-size: 14px;
  color: #606266;
}

.log-media {
  display: flex;
  align-items: center;
  gap: 10px;
}

.log-thumb {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 4px;
}

.media-selector {
  min-height: 400px;
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}

.media-item {
  border: 2px solid #e4e7ed;
  border-radius: 4px;
  padding: 8px;
  cursor: pointer;
  transition: all 0.3s;
}

.media-item:hover {
  border-color: #409EFF;
}

.media-item.selected {
  border-color: #409EFF;
  background-color: #ecf5ff;
}

.media-preview {
  width: 100%;
  height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f5f7fa;
  border-radius: 4px;
  margin-bottom: 8px;
  overflow: hidden;
}

.media-preview img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.media-name {
  font-size: 12px;
  color: #606266;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  text-align: center;
}
</style>
