<template>
  <div class="build-page">
    <el-row :gutter="20">
      <!-- 操作面板 -->
      <el-col :span="24">
        <el-card>
          <template #header>
            <h3>静态页面生成</h3>
          </template>

          <div class="build-actions">
            <el-button
              type="primary"
              size="large"
              :loading="building.all"
              @click="handleBuildAll"
              style="width: 100%; margin-bottom: 15px;"
            >
              <el-icon><refresh /></el-icon>
              生成所有静态页面
            </el-button>

            <el-divider>单独生成</el-divider>

            <el-button
              type="success"
              :loading="building.index"
              @click="handleBuildIndex"
              style="width: 100%; margin-bottom: 10px;"
            >
              <el-icon><home-filled /></el-icon>
              生成首页
            </el-button>

            <el-button
              type="success"
              :loading="building.articles"
              @click="handleBuildArticles"
              style="width: 100%; margin-bottom: 10px;"
            >
              <el-icon><document /></el-icon>
              生成文章列表页
            </el-button>

            <el-button
              type="success"
              :loading="building.pages"
              @click="handleBuildPages"
              style="width: 100%; margin-bottom: 10px;"
            >
              <el-icon><files /></el-icon>
              生成所有单页
            </el-button>

            <el-button
              type="success"
              :loading="building.categories"
              @click="handleBuildCategories"
              style="width: 100%; margin-bottom: 10px;"
            >
              <el-icon><folder /></el-icon>
              生成所有分类页
            </el-button>

            <el-button
              type="success"
              :loading="building.tags"
              @click="handleBuildTags"
              style="width: 100%; margin-bottom: 10px;"
            >
              <el-icon><price-tag /></el-icon>
              生成所有标签页
            </el-button>

            <el-button
              type="success"
              :loading="building.topics"
              @click="handleBuildTopics"
              style="width: 100%; margin-bottom: 10px;"
            >
              <el-icon><collection-tag /></el-icon>
              生成所有专题页
            </el-button>

            <el-form :inline="true" style="margin-top: 20px;">
              <el-form-item label="文章ID">
                <el-input-number
                  v-model="articleId"
                  :min="1"
                  placeholder="输入文章ID"
                  style="width: 120px;"
                />
              </el-form-item>
              <el-form-item>
                <el-button
                  type="primary"
                  :loading="building.article"
                  :disabled="!articleId"
                  @click="handleBuildArticle"
                >
                  生成文章
                </el-button>
              </el-form-item>
            </el-form>

            <el-form :inline="true">
              <el-form-item label="分类ID">
                <el-input-number
                  v-model="categoryId"
                  :min="1"
                  placeholder="输入分类ID"
                  style="width: 120px;"
                />
              </el-form-item>
              <el-form-item>
                <el-button
                  type="warning"
                  :loading="building.category"
                  :disabled="!categoryId"
                  @click="handleBuildCategory"
                >
                  生成分类
                </el-button>
              </el-form-item>
            </el-form>

            <el-form :inline="true">
              <el-form-item label="页面ID">
                <el-input-number
                  v-model="pageId"
                  :min="1"
                  placeholder="输入页面ID"
                  style="width: 120px;"
                />
              </el-form-item>
              <el-form-item>
                <el-button
                  type="info"
                  :loading="building.page"
                  :disabled="!pageId"
                  @click="handleBuildPage"
                >
                  生成页面
                </el-button>
              </el-form-item>
            </el-form>

            <el-form :inline="true">
              <el-form-item label="标签ID">
                <el-input-number
                  v-model="tagId"
                  :min="1"
                  placeholder="输入标签ID"
                  style="width: 120px;"
                />
              </el-form-item>
              <el-form-item>
                <el-button
                  type="success"
                  :loading="building.tag"
                  :disabled="!tagId"
                  @click="handleBuildTag"
                >
                  生成标签
                </el-button>
              </el-form-item>
            </el-form>

            <el-form :inline="true">
              <el-form-item label="专题ID">
                <el-input-number
                  v-model="topicId"
                  :min="1"
                  placeholder="输入专题ID"
                  style="width: 120px;"
                />
              </el-form-item>
              <el-form-item>
                <el-button
                  type="primary"
                  :loading="building.topic"
                  :disabled="!topicId"
                  @click="handleBuildTopic"
                >
                  生成专题
                </el-button>
              </el-form-item>
            </el-form>
          </div>

          <el-alert
            type="info"
            :closable="false"
            style="margin-top: 20px;"
          >
            <template #title>
              <div style="font-size: 13px; line-height: 1.6;">
                <strong>说明：</strong><br>
                • 静态文件生成在 api/html 目录下<br>
                • 文章发布/更新时会自动生成<br>
                • 可使用定时任务批量生成：php think build:static
              </div>
            </template>
          </el-alert>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import {
  buildAll,
  buildIndex,
  buildArticles,
  buildArticle,
  buildCategories,
  buildCategory,
  buildTags,
  buildTag,
  buildTopics,
  buildTopic,
  buildPages,
  buildPage
} from '@/api/build'

const building = reactive({
  all: false,
  index: false,
  articles: false,
  article: false,
  categories: false,
  category: false,
  tags: false,
  tag: false,
  topics: false,
  topic: false,
  pages: false,
  page: false
})

const articleId = ref(null)
const categoryId = ref(null)
const tagId = ref(null)
const topicId = ref(null)
const pageId = ref(null)

// 生成所有
const handleBuildAll = async () => {
  building.all = true
  try {
    const res = await buildAll()
    const data = res.data
    ElMessage.success(
      `生成完成！首页:${data.index} 文章:${data.articles} 分类:${data.categories} 标签:${data.tags} 专题:${data.topics} 页面:${data.pages}`
    )
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.all = false
  }
}

// 生成首页
const handleBuildIndex = async () => {
  building.index = true
  try {
    await buildIndex()
    ElMessage.success('首页生成成功')
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.index = false
  }
}

// 生成文章列表页
const handleBuildArticles = async () => {
  building.articles = true
  try {
    const res = await buildArticles()
    const pages = res.data?.pages || 1
    ElMessage.success(`文章列表页生成成功，共${pages}页`)
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.articles = false
  }
}

// 生成所有单页
const handleBuildPages = async () => {
  building.pages = true
  try {
    const res = await buildPages()
    const pages = res.data?.pages || 0
    ElMessage.success(`单页生成成功，共${pages}个页面`)
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.pages = false
  }
}

// 生成文章
const handleBuildArticle = async () => {
  building.article = true
  try {
    await buildArticle(articleId.value)
    ElMessage.success('文章生成成功')
    articleId.value = null
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.article = false
  }
}

// 生成所有分类页
const handleBuildCategories = async () => {
  building.categories = true
  try {
    const res = await buildCategories()
    const categories = res.data?.categories || 0
    ElMessage.success(`分类页生成成功，共${categories}个页面`)
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.categories = false
  }
}

// 生成分类
const handleBuildCategory = async () => {
  building.category = true
  try {
    await buildCategory(categoryId.value)
    ElMessage.success('分类页生成成功')
    categoryId.value = null
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.category = false
  }
}

// 生成页面
const handleBuildPage = async () => {
  building.page = true
  try {
    await buildPage(pageId.value)
    ElMessage.success('页面生成成功')
    pageId.value = null
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.page = false
  }
}

// 生成所有标签页
const handleBuildTags = async () => {
  building.tags = true
  try {
    const res = await buildTags()
    const tags = res.data?.tags || 0
    ElMessage.success(`标签页生成成功，共${tags}个页面`)
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.tags = false
  }
}

// 生成标签
const handleBuildTag = async () => {
  building.tag = true
  try {
    await buildTag(tagId.value)
    ElMessage.success('标签页生成成功')
    tagId.value = null
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.tag = false
  }
}

// 生成所有专题页
const handleBuildTopics = async () => {
  building.topics = true
  try {
    const res = await buildTopics()
    const topics = res.data?.topics || 0
    ElMessage.success(`专题页生成成功，共${topics}个页面`)
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.topics = false
  }
}

// 生成专题
const handleBuildTopic = async () => {
  building.topic = true
  try {
    await buildTopic(topicId.value)
    ElMessage.success('专题页生成成功')
    topicId.value = null
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.topic = false
  }
}
</script>

<style scoped>
.build-page h3 {
  margin: 0;
}

.build-actions {
  padding: 10px 0;
}
</style>
