<template>
  <el-dialog
    v-model="visible"
    title="删除确认"
    width="600px"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div v-loading="loading">
      <el-alert
        type="warning"
        :closable="false"
        show-icon
        style="margin-bottom: 20px;"
      >
        <template #title>
          <div style="font-weight: bold;">确定要删除此文章吗？</div>
        </template>
      </el-alert>

      <!-- 媒体使用情况 -->
      <div v-if="!loading && mediaList.length > 0" class="media-section">
        <el-divider content-position="left">
          <el-icon><Picture /></el-icon>
          文章中使用的图片 ({{ mediaList.length }}个)
        </el-divider>

        <div class="media-grid">
          <div
            v-for="media in mediaList"
            :key="media.id"
            class="media-item"
            :class="{ selected: selectedMediaIds.includes(media.id) }"
          >
            <div class="media-preview">
              <img :src="media.file_url" :alt="media.file_name" />
            </div>
            <div class="media-info">
              <div class="media-name" :title="media.file_name">
                {{ media.file_name }}
              </div>
              <div class="media-field">
                <el-tag size="small">{{ getFieldText(media.field_name) }}</el-tag>
                <span v-if="media.usage_count > 1" style="margin-left: 5px; font-size: 12px; color: #909399;">
                  使用{{ media.usage_count }}次
                </span>
              </div>
            </div>
          </div>
        </div>

        <el-divider />

        <el-checkbox v-model="deleteMedia" style="margin-top: 10px;">
          <span style="font-weight: bold; color: #f56c6c;">同时删除以上图片</span>
        </el-checkbox>

        <div v-if="deleteMedia" class="warning-text">
          <el-icon><WarningFilled /></el-icon>
          注意：删除后无法恢复，请谨慎操作！
        </div>
      </div>

      <div v-else-if="!loading" class="no-media">
        <el-empty description="此文章未使用任何图片" :image-size="80" />
      </div>
    </div>

    <template #footer>
      <el-button @click="handleClose">取消</el-button>
      <el-button
        type="danger"
        @click="handleConfirm"
        :loading="deleting"
      >
        {{ deleteMedia && mediaList.length > 0 ? '删除文章和图片' : '仅删除文章' }}
      </el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { Picture, WarningFilled } from '@element-plus/icons-vue'
import { checkArticleDeleteMedia } from '@/api/mediaUsage'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  articleId: {
    type: Number,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'confirm'])

const visible = ref(false)
const loading = ref(false)
const deleting = ref(false)
const mediaList = ref([])
const deleteMedia = ref(false)
const selectedMediaIds = ref([])

// 监听modelValue变化
watch(() => props.modelValue, (val) => {
  visible.value = val
  if (val) {
    loadMediaList()
  }
})

// 监听visible变化
watch(visible, (val) => {
  emit('update:modelValue', val)
})

// 加载媒体列表
const loadMediaList = async () => {
  if (!props.articleId) {
    return
  }

  loading.value = true
  mediaList.value = []
  deleteMedia.value = false

  try {
    const res = await checkArticleDeleteMedia(props.articleId)
    mediaList.value = res.data?.media_list || []

    // 默认全选
    if (mediaList.value.length > 0) {
      selectedMediaIds.value = mediaList.value.map(m => m.id)
    }
  } catch (error) {
    ElMessage.error('加载媒体列表失败')
  } finally {
    loading.value = false
  }
}

// 获取字段文本
const getFieldText = (field) => {
  const fieldMap = {
    content: '正文',
    thumb: '缩略图',
    cover_image: '封面图片',
    images: '图片集合',
    og_image: 'OG图片',
    banner: '横幅'
  }
  return fieldMap[field] || field
}

// 确认删除
const handleConfirm = () => {
  const mediaIds = deleteMedia.value ? selectedMediaIds.value : []

  emit('confirm', {
    deleteMedia: deleteMedia.value,
    mediaIds: mediaIds
  })
}

// 关闭对话框
const handleClose = () => {
  visible.value = false
  mediaList.value = []
  deleteMedia.value = false
  selectedMediaIds.value = []
}
</script>

<style scoped>
.media-section {
  margin: 10px 0;
}

.media-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
  max-height: 300px;
  overflow-y: auto;
  padding: 10px;
  background-color: #f5f7fa;
  border-radius: 4px;
}

.media-item {
  border: 2px solid #e4e7ed;
  border-radius: 4px;
  padding: 8px;
  background-color: #fff;
  transition: all 0.3s;
}

.media-item:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.media-item.selected {
  border-color: #409EFF;
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

.media-info {
  font-size: 12px;
}

.media-name {
  color: #303133;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 5px;
}

.media-field {
  display: flex;
  align-items: center;
  color: #909399;
}

.no-media {
  padding: 20px;
  text-align: center;
}

.warning-text {
  margin-top: 10px;
  padding: 8px 12px;
  background-color: #fef0f0;
  border: 1px solid #fde2e2;
  border-radius: 4px;
  color: #f56c6c;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
}
</style>
