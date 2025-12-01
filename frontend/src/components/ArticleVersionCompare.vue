<template>
  <div class="article-version-compare">
    <el-dialog
      v-model="visible"
      title="版本对比"
      width="90%"
      :before-close="handleClose"
    >
      <div v-loading="loading">
        <!-- 版本选择器 -->
        <el-row :gutter="20" style="margin-bottom: 20px;">
          <el-col :span="12">
            <el-card shadow="hover">
              <template #header>
                <div class="card-header">
                  <span>旧版本</span>
                  <el-tag type="info" size="small">
                    V{{ compareData?.old_version?.version_number }}
                  </el-tag>
                </div>
              </template>
              <el-descriptions :column="1" size="small" border>
                <el-descriptions-item label="修改时间">
                  {{ compareData?.old_version?.create_time }}
                </el-descriptions-item>
                <el-descriptions-item label="修改人">
                  {{ compareData?.old_version?.created_by }}
                </el-descriptions-item>
                <el-descriptions-item label="修改说明">
                  {{ compareData?.old_version?.change_log || '-' }}
                </el-descriptions-item>
              </el-descriptions>
            </el-card>
          </el-col>
          <el-col :span="12">
            <el-card shadow="hover">
              <template #header>
                <div class="card-header">
                  <span>新版本</span>
                  <el-tag type="success" size="small">
                    V{{ compareData?.new_version?.version_number }}
                  </el-tag>
                </div>
              </template>
              <el-descriptions :column="1" size="small" border>
                <el-descriptions-item label="修改时间">
                  {{ compareData?.new_version?.create_time }}
                </el-descriptions-item>
                <el-descriptions-item label="修改人">
                  {{ compareData?.new_version?.created_by }}
                </el-descriptions-item>
                <el-descriptions-item label="修改说明">
                  {{ compareData?.new_version?.change_log || '-' }}
                </el-descriptions-item>
              </el-descriptions>
            </el-card>
          </el-col>
        </el-row>

        <!-- 差异对比 -->
        <div v-if="compareData && compareData.diff">
          <el-divider content-position="left">
            <el-tag type="warning">
              共发现 {{ diffCount }} 处修改
            </el-tag>
          </el-divider>

          <div v-if="diffCount === 0" style="text-align: center; padding: 40px;">
            <el-empty description="两个版本内容相同" />
          </div>

          <div v-else class="diff-list">
            <el-card
              v-for="(value, key) in compareData.diff"
              :key="key"
              shadow="hover"
              style="margin-bottom: 20px;"
            >
              <template #header>
                <div class="card-header">
                  <span>{{ getFieldLabel(key) }}</span>
                  <el-tag type="warning" size="small">已修改</el-tag>
                </div>
              </template>

              <el-row :gutter="20">
                <el-col :span="12">
                  <div class="diff-content old-content">
                    <div class="diff-label">旧值：</div>
                    <div class="diff-value">
                      <div v-if="isHtmlField(key)" v-safe-html="value.old || '-'"></div>
                      <pre v-else-if="isJsonField(key)">{{ formatJson(value.old) }}</pre>
                      <span v-else>{{ value.old || '-' }}</span>
                    </div>
                  </div>
                </el-col>
                <el-col :span="12">
                  <div class="diff-content new-content">
                    <div class="diff-label">新值：</div>
                    <div class="diff-value">
                      <div v-if="isHtmlField(key)" v-safe-html="value.new || '-'"></div>
                      <pre v-else-if="isJsonField(key)">{{ formatJson(value.new) }}</pre>
                      <span v-else>{{ value.new || '-' }}</span>
                    </div>
                  </div>
                </el-col>
              </el-row>
            </el-card>
          </div>
        </div>
      </div>

      <template #footer>
        <el-button @click="handleClose">关闭</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { compareVersions } from '@/api/articleVersion'
import { vSafeHtml } from '@/utils/sanitize'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  oldVersionId: {
    type: [String, Number],
    required: true
  },
  newVersionId: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const visible = ref(false)
const loading = ref(false)
const compareData = ref(null)

// 字段标签映射
const fieldLabels = {
  title: '标题',
  slug: '别名',
  summary: '摘要',
  content: '内容',
  cover_image: '封面图片',
  images: '图片集',
  category_id: '分类ID',
  tags: '标签',
  author: '作者',
  source: '来源',
  source_url: '来源地址',
  is_top: '置顶',
  is_recommend: '推荐',
  is_hot: '热门',
  seo_title: 'SEO标题',
  seo_keywords: 'SEO关键词',
  seo_description: 'SEO描述',
  sort: '排序',
  flags: '文章属性'
}

// 计算差异数量
const diffCount = computed(() => {
  return compareData.value?.diff ? Object.keys(compareData.value.diff).length : 0
})

// 获取字段标签
const getFieldLabel = (key) => {
  return fieldLabels[key] || key
}

// 判断是否为HTML字段
const isHtmlField = (key) => {
  return ['content'].includes(key)
}

// 判断是否为JSON字段
const isJsonField = (key) => {
  return ['tags', 'images'].includes(key)
}

// 格式化JSON
const formatJson = (value) => {
  if (!value) return '-'
  if (typeof value === 'object') {
    return JSON.stringify(value, null, 2)
  }
  try {
    return JSON.stringify(JSON.parse(value), null, 2)
  } catch {
    return value
  }
}

// 监听外部值变化
watch(() => props.modelValue, (val) => {
  visible.value = val
  if (val) {
    loadCompareData()
  }
})

watch(visible, (val) => {
  emit('update:modelValue', val)
})

// 加载对比数据
const loadCompareData = async () => {
  loading.value = true
  try {
    const res = await compareVersions(props.oldVersionId, props.newVersionId)
    compareData.value = res.data
  } catch (error) {
    ElMessage.error('加载对比数据失败')
  } finally {
    loading.value = false
  }
}

// 关闭对话框
const handleClose = () => {
  visible.value = false
}

onMounted(() => {
  if (props.modelValue) {
    loadCompareData()
  }
})
</script>

<style scoped>
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.diff-list {
  max-height: 600px;
  overflow-y: auto;
}

.diff-content {
  padding: 10px;
  border-radius: 4px;
  min-height: 80px;
}

.old-content {
  background-color: #fff1f0;
  border: 1px solid #ffccc7;
}

.new-content {
  background-color: #f0f9ff;
  border: 1px solid #91d5ff;
}

.diff-label {
  font-weight: bold;
  margin-bottom: 8px;
  font-size: 13px;
  color: #666;
}

.diff-value {
  font-size: 14px;
  line-height: 1.6;
  word-break: break-all;
  max-height: 400px;
  overflow-y: auto;
}

.diff-value pre {
  margin: 0;
  padding: 10px;
  background-color: #f5f5f5;
  border-radius: 4px;
  font-size: 12px;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.diff-value :deep(img) {
  max-width: 100%;
  height: auto;
}
</style>
