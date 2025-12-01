<template>
  <div class="build-page">
    <el-row :gutter="20">
      <!-- 站点和模板信息 -->
      <el-col :span="24">
        <el-card style="margin-bottom: 20px;">
          <template #header>
            <h3>生成配置</h3>
          </template>

          <el-form label-width="100px">
            <el-form-item label="选择站点">
              <el-select v-model="selectedSiteId" placeholder="请选择站点" style="width: 300px;" @change="handleSiteChange">
                <el-option
                  v-for="site in siteList"
                  :key="site.id"
                  :label="`${site.site_name} (${site.site_url || site.domain || site.site_code})`"
                  :value="site.id"
                />
              </el-select>
            </el-form-item>

            <el-form-item label="当前模板包" v-if="templatePackage">
              <div>
                <el-tag type="success" size="large">{{ templatePackage.name }}</el-tag>
                <span style="margin-left: 10px; color: #666;">{{ templatePackage.description }}</span>
              </div>
            </el-form-item>

            <el-form-item label="输出目录">
              <el-input :value="outputPath" readonly style="width: 400px;" />
            </el-form-item>
          </el-form>
        </el-card>
      </el-col>

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
                • 静态文件生成在 /html 目录下<br>
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
import { ref, reactive, onMounted, computed } from 'vue'
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
import { getSiteList } from '@/api/site'
import { getSiteTemplateConfig } from '@/api/site'

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

// 站点相关
const siteList = ref([])
const selectedSiteId = ref(null)
const templatePackage = ref(null)
const currentSite = ref(null)

// 计算输出目录
const outputPath = computed(() => {
  if (!selectedSiteId.value || !currentSite.value) return ''

  // 优先使用站点配置的静态生成目录
  if (currentSite.value.static_output_dir) {
    return `html/${currentSite.value.static_output_dir}/`
  }

  // 默认目录：主站为html根目录，其他站点为site_X
  if (currentSite.value.site_type === 1) {
    return 'html/'
  } else {
    return `html/site_${selectedSiteId.value}/`
  }
})

// 获取站点列表
const fetchSites = async () => {
  try {
    const res = await getSiteList({ page: 1, limit: 100 })
    siteList.value = res.data.list || []
    // 默认选择主站点
    if (siteList.value.length > 0) {
      const mainSite = siteList.value.find(s => s.site_type === 1)
      selectedSiteId.value = mainSite ? mainSite.id : siteList.value[0].id
      await fetchTemplateConfig()
    }
  } catch (error) {
    ElMessage.error('获取站点列表失败')
  }
}

// 获取站点模板配置
const fetchTemplateConfig = async () => {
  if (!selectedSiteId.value) return
  try {
    // 获取站点详情（包含static_output_dir）
    const site = siteList.value.find(s => s.id === selectedSiteId.value)
    currentSite.value = site

    // 获取模板配置
    const res = await getSiteTemplateConfig(selectedSiteId.value)
    if (res.data.has_config && res.data.package) {
      templatePackage.value = res.data.package
    } else {
      templatePackage.value = null
    }
  } catch (error) {
    templatePackage.value = null
  }
}

// 站点切换
const handleSiteChange = () => {
  fetchTemplateConfig()
}

// 生成所有
const handleBuildAll = async () => {
  if (!selectedSiteId.value) {
    ElMessage.warning('请先选择站点')
    return
  }
  building.all = true
  try {
    const res = await buildAll({ site_id: selectedSiteId.value })
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
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.index = true
  try {
    await buildIndex({ site_id: selectedSiteId.value })
    ElMessage.success('首页生成成功')
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.index = false
  }
}

// 生成文章列表页
const handleBuildArticles = async () => {
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.articles = true
  try {
    const res = await buildArticles({ site_id: selectedSiteId.value })
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
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.pages = true
  try {
    const res = await buildPages({ site_id: selectedSiteId.value })
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
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.article = true
  try {
    await buildArticle(articleId.value, { site_id: selectedSiteId.value })
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
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.categories = true
  try {
    const res = await buildCategories({ site_id: selectedSiteId.value })
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
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.category = true
  try {
    await buildCategory(categoryId.value, { site_id: selectedSiteId.value })
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
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.page = true
  try {
    await buildPage(pageId.value, { site_id: selectedSiteId.value })
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
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.tags = true
  try {
    const res = await buildTags({ site_id: selectedSiteId.value })
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
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.tag = true
  try {
    await buildTag(tagId.value, { site_id: selectedSiteId.value })
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
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.topics = true
  try {
    const res = await buildTopics({ site_id: selectedSiteId.value })
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
  if (!selectedSiteId.value) return ElMessage.warning('请先选择站点')
  building.topic = true
  try {
    await buildTopic(topicId.value, { site_id: selectedSiteId.value })
    ElMessage.success('专题页生成成功')
    topicId.value = null
  } catch (error) {
    ElMessage.error('生成失败')
  } finally {
    building.topic = false
  }
}

// 初始化
onMounted(() => {
  fetchSites()
})
</script>

<style scoped>
.build-page h3 {
  margin: 0;
}

.build-actions {
  padding: 10px 0;
}
</style>
