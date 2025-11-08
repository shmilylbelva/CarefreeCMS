<template>
  <div class="sitemap-page">
    <el-row :gutter="20">
      <!-- 基础格式生成 -->
      <el-col :span="12">
        <el-card>
          <template #header>
            <h3>基础格式生成</h3>
          </template>

          <div class="sitemap-actions">
            <el-button
              type="primary"
              size="large"
              :loading="generating.all"
              @click="handleGenerateAll"
              style="width: 100%; margin-bottom: 15px;"
            >
              <el-icon><refresh /></el-icon>
              生成所有基础格式
            </el-button>

            <el-divider>单独生成</el-divider>

            <el-button
              type="success"
              :loading="generating.txt"
              @click="handleGenerateTxt"
              style="width: 100%; margin-bottom: 10px;"
            >
              <el-icon><document /></el-icon>
              生成TXT格式
            </el-button>

            <el-button
              type="warning"
              :loading="generating.xml"
              @click="handleGenerateXml"
              style="width: 100%; margin-bottom: 10px;"
            >
              <el-icon><document-copy /></el-icon>
              生成XML格式
            </el-button>

            <el-button
              type="info"
              :loading="generating.html"
              @click="handleGenerateHtml"
              style="width: 100%; margin-bottom: 10px;"
            >
              <el-icon><tickets /></el-icon>
              生成HTML格式
            </el-button>
          </div>

          <el-alert
            type="info"
            :closable="false"
            style="margin-top: 20px;"
          >
            <template #title>
              <div style="font-size: 13px; line-height: 1.6;">
                <strong>说明：</strong><br>
                • TXT：纯文本URL列表，每行一个链接<br>
                • XML：符合搜索引擎标准的sitemap.xml<br>
                • HTML：结构化的网站地图页面<br>
                • 生成的文件保存在网站根目录
              </div>
            </template>
          </el-alert>
        </el-card>
      </el-col>

      <!-- 高级类型生成 -->
      <el-col :span="12">
        <el-card>
          <template #header>
            <h3>高级类型生成</h3>
          </template>

          <el-form label-width="120px">
            <el-form-item label="Sitemap类型">
              <el-radio-group v-model="advancedType">
                <el-radio label="all">全部类型</el-radio>
                <el-radio label="main">标准Sitemap</el-radio>
                <el-radio label="images">图片Sitemap</el-radio>
                <el-radio label="news">新闻Sitemap</el-radio>
                <el-radio label="index">索引文件</el-radio>
              </el-radio-group>
            </el-form-item>

            <el-form-item>
              <el-button
                type="primary"
                size="large"
                @click="handleGenerateAdvanced"
                :loading="generatingAdvanced"
              >
                <el-icon><magic-stick /></el-icon>
                生成高级Sitemap
              </el-button>
            </el-form-item>
          </el-form>

          <el-alert type="info" :closable="false">
            <template #title>
              <div style="font-size: 13px; line-height: 1.6;">
                <strong>类型说明：</strong><br>
                • 标准Sitemap：包含所有页面URL<br>
                • 图片Sitemap：专门索引文章中的图片<br>
                • 新闻Sitemap：针对新闻类文章优化<br>
                • 索引文件：汇总所有sitemap的索引<br>
                • 全部类型：一次性生成所有类型
              </div>
            </template>
          </el-alert>
        </el-card>
      </el-col>
    </el-row>

    <!-- Ping搜索引擎 -->
    <el-row :gutter="20" style="margin-top: 20px">
      <el-col :span="24">
        <el-card>
          <template #header>
            <h3>Ping搜索引擎</h3>
          </template>

          <el-form :inline="true" style="margin-bottom: 20px">
            <el-form-item label="Sitemap URL">
              <el-input
                v-model="pingForm.sitemapUrl"
                placeholder="留空使用默认 sitemap.xml"
                style="width: 400px"
                clearable
              />
            </el-form-item>
            <el-form-item>
              <el-button
                type="primary"
                :loading="pinging"
                @click="handlePingSitemap"
              >
                <el-icon><position /></el-icon>
                通知搜索引擎
              </el-button>
            </el-form-item>
          </el-form>

          <el-alert
            type="info"
            :closable="false"
            style="margin-bottom: 15px"
          >
            <template #title>
              <div style="font-size: 13px; line-height: 1.6;">
                <strong>说明：</strong><br>
                • Ping功能会主动通知搜索引擎您的sitemap已更新<br>
                • 支持Google、Bing等主流搜索引擎<br>
                • 建议在生成sitemap后立即执行ping操作
              </div>
            </template>
          </el-alert>

          <div v-if="pingResult" class="ping-results">
            <div
              v-for="(result, engine) in pingResult"
              :key="engine"
              class="ping-result-item"
            >
              <el-tag :type="result.success ? 'success' : 'danger'" size="large">
                {{ engine }}
              </el-tag>
              <span class="ping-message">{{ result.message || result.code }}</span>
            </div>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import {
  generateAllSitemaps,
  generateTxtSitemap,
  generateXmlSitemap,
  generateHtmlSitemap,
  pingSitemap
} from '@/api/sitemap'
import { generateSitemap } from '@/api/seoAnalyzer'

const generating = reactive({
  all: false,
  txt: false,
  xml: false,
  html: false
})

// 高级类型生成相关
const generatingAdvanced = ref(false)
const advancedType = ref('all')

// Ping搜索引擎相关
const pinging = ref(false)
const pingForm = reactive({
  sitemapUrl: ''
})
const pingResult = ref(null)

// 生成所有格式
const handleGenerateAll = async () => {
  generating.all = true
  try {
    const res = await generateAllSitemaps()
    ElMessage.success('所有格式sitemap生成成功')
  } catch (error) {
    ElMessage.error(error.message || '生成失败')
  } finally {
    generating.all = false
  }
}

// 生成TXT格式
const handleGenerateTxt = async () => {
  generating.txt = true
  try {
    const res = await generateTxtSitemap()
    ElMessage.success(res.msg || 'TXT格式sitemap生成成功')
  } catch (error) {
    ElMessage.error(error.message || '生成失败')
  } finally {
    generating.txt = false
  }
}

// 生成XML格式
const handleGenerateXml = async () => {
  generating.xml = true
  try {
    const res = await generateXmlSitemap()
    ElMessage.success(res.msg || 'XML格式sitemap生成成功')
  } catch (error) {
    ElMessage.error(error.message || '生成失败')
  } finally {
    generating.xml = false
  }
}

// 生成HTML格式
const handleGenerateHtml = async () => {
  generating.html = true
  try {
    const res = await generateHtmlSitemap()
    ElMessage.success(res.msg || 'HTML格式sitemap生成成功')
  } catch (error) {
    ElMessage.error(error.message || '生成失败')
  } finally {
    generating.html = false
  }
}

// 生成高级类型Sitemap
const handleGenerateAdvanced = async () => {
  generatingAdvanced.value = true

  try {
    const res = await generateSitemap(advancedType.value)

    // 构建成功消息
    let message = 'Sitemap生成成功！'
    if (res.data) {
      const details = []
      if (res.data.main) details.push(`标准Sitemap: ${res.data.main.count}条`)
      if (res.data.images) details.push(`图片Sitemap: ${res.data.images.count}条`)
      if (res.data.news) details.push(`新闻Sitemap: ${res.data.news.count}条`)
      if (res.data.index) details.push(`索引文件: ${res.data.index.count}个`)

      if (details.length > 0) {
        message += ' ' + details.join(', ')
      }
    }

    ElMessage.success(message)
  } catch (error) {
    ElMessage.error(error.message || '生成失败')
  } finally {
    generatingAdvanced.value = false
  }
}

// Ping搜索引擎
const handlePingSitemap = async () => {
  pinging.value = true
  pingResult.value = null

  try {
    const res = await pingSitemap(pingForm.sitemapUrl)
    pingResult.value = res.data

    // 统计成功和失败的数量
    const successCount = Object.values(res.data).filter(r => r.success).length
    const totalCount = Object.keys(res.data).length

    if (successCount === totalCount) {
      ElMessage.success('所有搜索引擎ping成功')
    } else if (successCount > 0) {
      ElMessage.warning(`部分成功：${successCount}/${totalCount} 个搜索引擎`)
    } else {
      ElMessage.error('Ping失败')
    }
  } catch (error) {
    ElMessage.error(error.message || 'Ping失败')
  } finally {
    pinging.value = false
  }
}
</script>

<style scoped>
.sitemap-page h3 {
  margin: 0;
}

.sitemap-actions {
  padding: 10px 0;
}


.ping-results {
  margin-top: 15px;
}

.ping-result-item {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 12px;
  margin-bottom: 10px;
  border: 1px solid #e4e7ed;
  border-radius: 4px;
  background-color: #f9fafb;
}

.ping-message {
  font-size: 14px;
  color: #606266;
}
</style>
