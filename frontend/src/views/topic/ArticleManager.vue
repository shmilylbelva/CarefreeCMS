<template>
  <el-dialog
    :model-value="modelValue"
    :title="`管理专题文章 - ${topicName}`"
    width="1000px"
    @update:model-value="handleClose"
  >
    <div class="article-manager">
      <!-- 左侧：当前专题的文章列表 -->
      <div class="current-articles">
        <h4>当前文章（{{ currentArticles.length }}）</h4>
        <div class="article-list">
          <div
            v-for="article in currentArticles"
            :key="article.id"
            class="article-item"
          >
            <div class="article-info">
              <img
                v-if="article.cover_image"
                :src="article.cover_image"
                class="article-cover"
              />
              <div class="article-details">
                <div class="article-title">{{ article.title }}</div>
                <div class="article-meta">
                  <el-tag size="small">ID: {{ article.id }}</el-tag>
                  <el-tag v-if="article.is_featured" type="success" size="small">精选</el-tag>
                </div>
              </div>
            </div>
            <div class="article-actions">
              <el-input-number
                v-model="article.topic_sort"
                :min="0"
                size="small"
                @change="handleSortChange(article)"
              />
              <el-button
                size="small"
                :type="article.is_featured ? 'success' : 'info'"
                @click="handleToggleFeatured(article)"
              >
                {{ article.is_featured ? '取消精选' : '设为精选' }}
              </el-button>
              <el-button
                size="small"
                type="danger"
                @click="handleRemoveArticle(article)"
              >
                移除
              </el-button>
            </div>
          </div>
          <el-empty v-if="currentArticles.length === 0" description="暂无文章" />
        </div>
      </div>

      <!-- 右侧：添加文章 -->
      <div class="add-articles">
        <h4>添加文章</h4>
        <el-form :inline="true" size="small">
          <el-form-item>
            <el-input
              v-model="searchKeyword"
              placeholder="搜索文章标题"
              clearable
              @keyup.enter="searchArticles"
            />
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="searchArticles">搜索</el-button>
          </el-form-item>
        </el-form>

        <div class="available-list">
          <div
            v-for="article in availableArticles"
            :key="article.id"
            class="available-item"
          >
            <div class="available-info">
              <img
                v-if="article.cover_image"
                :src="article.cover_image"
                class="available-cover"
              />
              <div class="available-details">
                <div class="available-title">{{ article.title }}</div>
                <div class="available-meta">
                  <el-tag size="small">ID: {{ article.id }}</el-tag>
                </div>
              </div>
            </div>
            <el-button
              size="small"
              type="success"
              :disabled="isArticleInTopic(article.id)"
              @click="handleAddArticle(article)"
            >
              {{ isArticleInTopic(article.id) ? '已添加' : '添加' }}
            </el-button>
          </div>
          <el-empty v-if="availableArticles.length === 0" description="暂无可添加的文章" />
        </div>
      </div>
    </div>

    <template #footer>
      <el-button @click="handleClose">关闭</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import {
  getTopicArticles,
  addArticleToTopic,
  removeArticleFromTopic,
  updateArticleSort,
  setArticleFeatured,
  getTopicDetail
} from '@/api/topic'
import { getArticleList } from '@/api/article'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  topicId: {
    type: Number,
    required: true
  },
  topicName: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const currentArticles = ref([])
const availableArticles = ref([])
const searchKeyword = ref('')
const topicSiteId = ref(null)

// 加载专题详情（获取站点ID）
const loadTopicDetail = async () => {
  if (!props.topicId) return

  try {
    const res = await getTopicDetail(props.topicId)
    topicSiteId.value = res.data.site_id
  } catch (error) {
    console.error('加载专题详情失败:', error)
  }
}

// 加载当前专题的文章
const loadCurrentArticles = async () => {
  if (!props.topicId) return

  try {
    const res = await getTopicArticles(props.topicId, {
      page: 1,
      page_size: 1000
    })
    currentArticles.value = res.data.list || []
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  }
}

// 搜索可用文章
const searchArticles = async () => {
  try {
    const params = {
      page: 1,
      page_size: 20,
      keyword: searchKeyword.value,
      status: 1 // 只显示已发布的文章
    }

    // 只显示当前专题所属站点的文章
    if (topicSiteId.value !== null) {
      params.site_id = topicSiteId.value
    }

    const res = await getArticleList(params)
    availableArticles.value = res.data.list || []
  } catch (error) {
    ElMessage.error(error.message || '搜索失败')
  }
}

// 检查文章是否已在专题中
const isArticleInTopic = (articleId) => {
  return currentArticles.value.some(a => a.id === articleId)
}

// 添加文章到专题
const handleAddArticle = async (article) => {
  try {
    await addArticleToTopic(props.topicId, article.id, 0, 0)
    ElMessage.success('添加成功')
    await loadCurrentArticles()
  } catch (error) {
    ElMessage.error(error.message || '添加失败')
  }
}

// 从专题移除文章
const handleRemoveArticle = async (article) => {
  try {
    await removeArticleFromTopic(props.topicId, article.id)
    ElMessage.success('移除成功')
    await loadCurrentArticles()
  } catch (error) {
    ElMessage.error(error.message || '移除失败')
  }
}

// 更新排序
const handleSortChange = async (article) => {
  try {
    await updateArticleSort(props.topicId, article.id, article.topic_sort)
    ElMessage.success('排序已更新')
  } catch (error) {
    ElMessage.error(error.message || '更新失败')
  }
}

// 切换精选状态
const handleToggleFeatured = async (article) => {
  const newValue = article.is_featured ? 0 : 1
  try {
    await setArticleFeatured(props.topicId, article.id, newValue)
    article.is_featured = newValue
    ElMessage.success(newValue ? '已设为精选' : '已取消精选')
  } catch (error) {
    ElMessage.error(error.message || '设置失败')
  }
}

// 关闭对话框
const handleClose = () => {
  emit('update:modelValue', false)
}

// 监听对话框打开
watch(() => props.modelValue, async (val) => {
  if (val) {
    await loadTopicDetail() // 先加载专题详情获取站点ID
    loadCurrentArticles()
    searchArticles()
  }
})
</script>

<style scoped>
.article-manager {
  display: flex;
  gap: 20px;
  min-height: 500px;
}

.current-articles,
.add-articles {
  flex: 1;
}

.current-articles h4,
.add-articles h4 {
  margin: 0 0 15px 0;
  padding-bottom: 10px;
  border-bottom: 2px solid #409eff;
}

.article-list,
.available-list {
  max-height: 500px;
  overflow-y: auto;
}

.article-item,
.available-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px;
  border: 1px solid #eee;
  border-radius: 4px;
  margin-bottom: 10px;
  transition: all 0.3s;
}

.article-item:hover,
.available-item:hover {
  background-color: #f5f7fa;
  border-color: #409eff;
}

.article-info,
.available-info {
  display: flex;
  align-items: center;
  flex: 1;
  gap: 10px;
}

.article-cover,
.available-cover {
  width: 60px;
  height: 40px;
  object-fit: cover;
  border-radius: 4px;
}

.article-details,
.available-details {
  flex: 1;
}

.article-title,
.available-title {
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 5px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.article-meta,
.available-meta {
  display: flex;
  gap: 5px;
}

.article-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}
</style>
