<template>
  <div class="thumbnail-generator">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>批量生成缩略图</span>
        </div>
      </template>

      <el-form :model="formData" label-width="120px">
        <el-form-item label="选择图片">
          <div class="media-selection">
            <el-button @click="handleShowMediaSelector">
              <el-icon><Plus /></el-icon>
              选择图片
            </el-button>
            <span v-if="selectedMedia.length > 0" class="selected-count">
              已选择 {{ selectedMedia.length }} 个图片
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

        <el-form-item label="缩略图预设">
          <el-select
            v-model="formData.preset_name"
            placeholder="选择预设（留空则生成所有自动生成的预设）"
            style="width: 400px;"
            clearable
          >
            <el-option value="" label="所有自动生成的预设" />
            <el-option
              v-for="preset in presets"
              :key="preset.name"
              :label="`${preset.display_name} (${preset.width}x${preset.height})`"
              :value="preset.name"
            />
          </el-select>
        </el-form-item>

        <el-form-item>
          <el-button
            type="primary"
            @click="handleGenerate"
            :loading="generating"
            :disabled="selectedMedia.length === 0"
          >
            <el-icon><PictureFilled /></el-icon>
            开始生成
          </el-button>
          <el-button @click="handleClear">
            <el-icon><Delete /></el-icon>
            清空选择
          </el-button>
        </el-form-item>
      </el-form>

      <!-- 生成进度 -->
      <el-card v-if="processing" shadow="never" style="margin-top: 20px;">
        <template #header>
          <span>生成进度</span>
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

        <!-- 详细结果 -->
        <el-collapse v-if="processedCount > 0" style="margin-top: 15px;">
          <el-collapse-item title="查看详细结果">
            <el-table :data="results" size="small" max-height="300">
              <el-table-column prop="media_id" label="媒体ID" width="80" />
              <el-table-column label="状态" width="80">
                <template #default="{ row }">
                  <el-tag v-if="row.success" type="success" size="small">成功</el-tag>
                  <el-tag v-else type="danger" size="small">失败</el-tag>
                </template>
              </el-table-column>
              <el-table-column label="生成结果" min-width="200">
                <template #default="{ row }">
                  <div v-if="row.success && Array.isArray(row.result)">
                    <el-tag
                      v-for="(r, idx) in row.result"
                      :key="idx"
                      :type="r.success ? 'success' : 'danger'"
                      size="small"
                      style="margin-right: 5px;"
                    >
                      {{ r.preset }}
                    </el-tag>
                  </div>
                  <span v-else-if="row.error" style="color: #f56c6c;">{{ row.error }}</span>
                </template>
              </el-table-column>
            </el-table>
          </el-collapse-item>
        </el-collapse>
      </el-card>
    </el-card>

    <!-- 快捷操作 -->
    <el-card style="margin-top: 20px;">
      <template #header>
        <span>快捷操作</span>
      </template>

      <el-space wrap>
        <el-button @click="handleRegenerateForMedia">
          <el-icon><RefreshRight /></el-icon>
          重新生成（选中图片的所有缩略图）
        </el-button>

        <el-button type="warning" @click="handleDeleteAllForMedia">
          <el-icon><Delete /></el-icon>
          删除所有缩略图（选中图片）
        </el-button>
      </el-space>
    </el-card>

    <!-- 媒体选择器对话框 -->
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
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Close, Delete, PictureFilled, RefreshRight } from '@element-plus/icons-vue'
import {
  getPresets,
  batchGenerate,
  regenerateThumbnails,
  deleteAllThumbnails
} from '@/api/thumbnail'
import request from '@/api/request'

// 数据
const selectedMedia = ref([])
const presets = ref([])
const generating = ref(false)
const processing = ref(false)
const processedCount = ref(0)
const totalCount = ref(0)
const failedCount = ref(0)
const results = ref([])

const formData = reactive({
  preset_name: ''
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
  } catch (error) {
    ElMessage.error('加载预设失败')
  }
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

// 生成缩略图
const handleGenerate = async () => {
  if (selectedMedia.value.length === 0) {
    ElMessage.warning('请选择图片')
    return
  }

  generating.value = true
  processing.value = true
  processedCount.value = 0
  failedCount.value = 0
  totalCount.value = selectedMedia.value.length
  results.value = []

  try {
    const mediaIds = selectedMedia.value.map(m => m.id)

    const res = await batchGenerate({
      media_ids: mediaIds,
      preset_name: formData.preset_name
    })

    if (res.data?.results) {
      results.value = res.data.results
      processedCount.value = res.data.success || 0
      failedCount.value = res.data.failed || 0
    } else {
      processedCount.value = totalCount.value
    }

    if (failedCount.value === 0) {
      ElMessage.success(`成功为 ${processedCount.value} 个图片生成缩略图`)
    } else {
      ElMessage.warning(
        `成功: ${processedCount.value}, 失败: ${failedCount.value}`
      )
    }

  } catch (error) {
    ElMessage.error(error.message || '生成失败')
  } finally {
    generating.value = false
  }
}

// 重新生成
const handleRegenerateForMedia = async () => {
  if (selectedMedia.value.length === 0) {
    ElMessage.warning('请先选择图片')
    return
  }

  await ElMessageBox.confirm(
    `确定要重新生成选中的 ${selectedMedia.value.length} 个图片的所有缩略图吗？这将删除现有缩略图并重新生成。`,
    '重新生成确认',
    { type: 'warning' }
  )

  processing.value = true
  processedCount.value = 0
  failedCount.value = 0
  totalCount.value = selectedMedia.value.length
  results.value = []

  let successCount = 0
  let failCount = 0

  for (const media of selectedMedia.value) {
    try {
      await regenerateThumbnails(media.id)
      successCount++
      processedCount.value++
    } catch (error) {
      failCount++
      failedCount.value++
    }
  }

  processing.value = false

  if (failCount === 0) {
    ElMessage.success(`成功重新生成 ${successCount} 个图片的缩略图`)
  } else {
    ElMessage.warning(`成功: ${successCount}, 失败: ${failCount}`)
  }
}

// 删除所有缩略图
const handleDeleteAllForMedia = async () => {
  if (selectedMedia.value.length === 0) {
    ElMessage.warning('请先选择图片')
    return
  }

  await ElMessageBox.confirm(
    `确定要删除选中的 ${selectedMedia.value.length} 个图片的所有缩略图吗？`,
    '删除确认',
    { type: 'warning' }
  )

  let successCount = 0
  let totalDeleted = 0

  for (const media of selectedMedia.value) {
    try {
      const res = await deleteAllThumbnails(media.id)
      successCount++
      totalDeleted += res.data?.count || 0
    } catch (error) {
      console.error(error)
    }
  }

  ElMessage.success(`已删除 ${totalDeleted} 个缩略图`)
}

// 清空选择
const handleClear = () => {
  selectedMedia.value = []
  formData.preset_name = ''
  processing.value = false
  processedCount.value = 0
  failedCount.value = 0
  totalCount.value = 0
  results.value = []
}

// 显示媒体选择器
const handleShowMediaSelector = () => {
  showMediaSelector.value = true
  loadMediaList()
}

onMounted(() => {
  loadPresets()
})
</script>

<style scoped>
.thumbnail-generator {
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
