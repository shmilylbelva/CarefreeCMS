<template>
  <div class="seo-tools-container">
    <el-tabs v-model="activeTab">
      <!-- SEO分析工具 -->
      <el-tab-pane label="SEO分析" name="analyzer">
        <el-card shadow="hover">
          <template #header>
            <span>文章SEO分析</span>
          </template>

          <el-form :model="analyzerForm" label-width="100px">
            <el-form-item label="选择文章">
              <el-input v-model="analyzerForm.articleId" placeholder="输入文章ID" style="width: 200px" />
              <el-button type="primary" @click="handleAnalyze" :loading="analyzing" style="margin-left: 10px">
                开始分析
              </el-button>
            </el-form-item>
          </el-form>

          <!-- 分析结果 -->
          <div v-if="analysisResult" class="analysis-result">
            <!-- 总分卡片 -->
            <el-card shadow="never" class="score-card">
              <div class="score-display">
                <div class="score-circle" :class="'grade-' + analysisResult.grade.level">
                  <div class="score-value">{{ analysisResult.score }}</div>
                  <div class="score-label">SEO分数</div>
                </div>
                <div class="grade-info">
                  <div class="grade-level">{{ analysisResult.grade.level }}</div>
                  <div class="grade-label">{{ analysisResult.grade.label }}</div>
                </div>
              </div>
            </el-card>

            <!-- 详细结果 -->
            <el-row :gutter="20" style="margin-top: 20px">
              <el-col :span="8" v-for="(result, category) in analysisResult.results" :key="category">
                <el-card shadow="hover" class="category-card">
                  <template #header>
                    <span>{{ getCategoryName(category) }}</span>
                    <el-tag :type="getScoreType(result.score, getMaxScore(category))" size="small" style="float: right">
                      {{ result.score }}/{{ getMaxScore(category) }}
                    </el-tag>
                  </template>

                  <div class="category-content">
                    <div v-if="result.issues && result.issues.length > 0" class="issues">
                      <div v-for="(issue, index) in result.issues" :key="index" class="issue-item">
                        <el-icon><InfoFilled /></el-icon>
                        {{ issue }}
                      </div>
                    </div>

                    <div v-if="result.suggestions && result.suggestions.length > 0" class="suggestions">
                      <div v-for="(suggestion, index) in result.suggestions" :key="index" class="suggestion-item">
                        <el-icon><WarningFilled /></el-icon>
                        {{ suggestion }}
                      </div>
                    </div>
                  </div>
                </el-card>
              </el-col>
            </el-row>

            <!-- 总结 -->
            <el-card shadow="hover" style="margin-top: 20px">
              <template #header>
                <span>优化建议</span>
              </template>
              <el-alert
                v-if="analysisResult.summary.suggestions.length === 0"
                type="success"
                :closable="false"
              >
                SEO优化良好，无需改进！
              </el-alert>
              <div v-else>
                <div v-for="(suggestion, index) in analysisResult.summary.suggestions" :key="index"
                     style="margin-bottom: 10px">
                  <el-icon color="#E6A23C"><WarningFilled /></el-icon>
                  {{ suggestion }}
                </div>
              </div>
            </el-card>
          </div>
        </el-card>
      </el-tab-pane>

      <!-- 关键词密度 -->
      <el-tab-pane label="关键词密度" name="density">
        <el-card shadow="hover">
          <template #header>
            <span>关键词密度分析</span>
          </template>

          <el-form :model="densityForm" label-width="100px">
            <el-form-item label="内容">
              <el-input v-model="densityForm.content" type="textarea" :rows="8"
                        placeholder="输入要分析的内容" />
            </el-form-item>

            <el-form-item label="关键词">
              <el-input v-model="densityForm.keywords" placeholder="多个关键词用逗号分隔" />
            </el-form-item>

            <el-form-item>
              <el-button type="primary" @click="handleCalculateDensity" :loading="calculating">
                计算密度
              </el-button>
            </el-form-item>
          </el-form>

          <!-- 密度结果 -->
          <div v-if="densityResult">
            <el-table :data="Object.entries(densityResult.densities).map(([k, v]) => ({keyword: k, ...v}))"
                      border stripe>
              <el-table-column prop="keyword" label="关键词" />
              <el-table-column prop="count" label="出现次数" width="120" />
              <el-table-column prop="density" label="密度" width="120">
                <template #default="{ row }">
                  <el-tag :type="getDensityType(row.density)">{{ row.density }}%</el-tag>
                </template>
              </el-table-column>
              <el-table-column label="评价" width="200">
                <template #default="{ row }">
                  <span v-if="row.density >= 1 && row.density <= 3" style="color: #67C23A">
                    <el-icon><SuccessFilled /></el-icon> 密度合适
                  </span>
                  <span v-else-if="row.density > 0 && row.density < 1" style="color: #E6A23C">
                    <el-icon><WarningFilled /></el-icon> 密度偏低
                  </span>
                  <span v-else-if="row.density > 3" style="color: #F56C6C">
                    <el-icon><CircleCloseFilled /></el-icon> 密度过高
                  </span>
                  <span v-else style="color: #909399">未出现</span>
                </template>
              </el-table-column>
            </el-table>
          </div>
        </el-card>
      </el-tab-pane>

      <!-- 自动优化 -->
      <el-tab-pane label="自动优化" name="optimize">
        <el-card shadow="hover">
          <template #header>
            <span>SEO自动优化</span>
          </template>

          <el-alert type="info" :closable="false" style="margin-bottom: 20px">
            <template #title>
              自动优化说明
            </template>
            <div>自动优化将为文章自动生成：</div>
            <ul>
              <li>SEO标题（基于文章标题和关键词）</li>
              <li>SEO描述（从内容中提取摘要）</li>
              <li>SEO关键词（从内容中自动提取）</li>
            </ul>
            <div>仅优化未填写的字段，已有内容不会被覆盖</div>
          </el-alert>

          <el-form :model="optimizeForm" label-width="100px">
            <el-form-item label="文章ID">
              <el-input v-model="optimizeForm.articleId" placeholder="输入文章ID" style="width: 200px" />
              <el-button type="primary" @click="handleAutoOptimize" :loading="optimizing"
                         style="margin-left: 10px">
                自动优化
              </el-button>
            </el-form-item>
          </el-form>

          <div v-if="optimizeResult">
            <el-alert type="success" :closable="false">
              优化完成！
            </el-alert>
            <div style="margin-top: 15px">
              <div><strong>SEO标题：</strong>{{ optimizeResult.seo_title }}</div>
              <div><strong>SEO描述：</strong>{{ optimizeResult.seo_description }}</div>
              <div><strong>SEO关键词：</strong>{{ optimizeResult.seo_keywords }}</div>
            </div>
          </div>
        </el-card>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import {
  InfoFilled,
  WarningFilled,
  SuccessFilled,
  CircleCloseFilled
} from '@element-plus/icons-vue'
import {
  analyzeArticleSeo,
  calculateKeywordDensity,
  autoOptimizeSeo
} from '@/api/seoAnalyzer'

const activeTab = ref('analyzer')

// SEO分析
const analyzing = ref(false)
const analyzerForm = reactive({ articleId: '' })
const analysisResult = ref(null)

const handleAnalyze = async () => {
  if (!analyzerForm.articleId) {
    ElMessage.warning('请输入文章ID')
    return
  }

  analyzing.value = true
  try {
    const res = await analyzeArticleSeo(analyzerForm.articleId)
    analysisResult.value = res.data
    ElMessage.success('分析完成')
  } catch (error) {
    ElMessage.error(error.message || '分析失败')
  } finally {
    analyzing.value = false
  }
}

const getCategoryName = (category) => {
  const names = {
    title: '标题',
    description: '描述',
    keywords: '关键词',
    content: '内容',
    images: '图片',
    links: '链接',
    readability: '可读性'
  }
  return names[category] || category
}

const getMaxScore = (category) => {
  const scores = {
    title: 20,
    description: 15,
    keywords: 15,
    content: 20,
    images: 10,
    links: 10,
    readability: 10
  }
  return scores[category] || 10
}

const getScoreType = (score, max) => {
  const percent = (score / max) * 100
  if (percent >= 80) return 'success'
  if (percent >= 60) return 'warning'
  return 'danger'
}

// 关键词密度
const calculating = ref(false)
const densityForm = reactive({
  content: '',
  keywords: ''
})
const densityResult = ref(null)

const handleCalculateDensity = async () => {
  if (!densityForm.content) {
    ElMessage.warning('请输入内容')
    return
  }
  if (!densityForm.keywords) {
    ElMessage.warning('请输入关键词')
    return
  }

  calculating.value = true
  try {
    const res = await calculateKeywordDensity(densityForm.content, densityForm.keywords)
    densityResult.value = res.data
  } catch (error) {
    ElMessage.error(error.message || '计算失败')
  } finally {
    calculating.value = false
  }
}

const getDensityType = (density) => {
  if (density >= 1 && density <= 3) return 'success'
  if (density > 0 && density < 1) return 'warning'
  if (density > 3) return 'danger'
  return 'info'
}

// 自动优化
const optimizing = ref(false)
const optimizeForm = reactive({ articleId: '' })
const optimizeResult = ref(null)

const handleAutoOptimize = async () => {
  if (!optimizeForm.articleId) {
    ElMessage.warning('请输入文章ID')
    return
  }

  optimizing.value = true
  try {
    const res = await autoOptimizeSeo(optimizeForm.articleId)
    optimizeResult.value = res.data
    ElMessage.success('优化完成')
  } catch (error) {
    ElMessage.error(error.message || '优化失败')
  } finally {
    optimizing.value = false
  }
}
</script>

<style scoped>
.seo-tools-container {
  padding: 20px;
}

.analysis-result {
  margin-top: 20px;
}

.score-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.score-display {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.score-circle {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-right: 30px;
}

.score-value {
  font-size: 48px;
  font-weight: bold;
}

.score-label {
  font-size: 14px;
  margin-top: 5px;
}

.grade-info {
  text-align: center;
}

.grade-level {
  font-size: 64px;
  font-weight: bold;
}

.grade-label {
  font-size: 20px;
  margin-top: 10px;
}

.category-card {
  margin-bottom: 20px;
  min-height: 200px;
}

.category-content {
  font-size: 14px;
}

.issues .issue-item,
.suggestions .suggestion-item {
  margin-bottom: 8px;
  display: flex;
  align-items: flex-start;
}

.issues .issue-item {
  color: #67C23A;
}

.suggestions .suggestion-item {
  color: #E6A23C;
}

.issues .issue-item .el-icon,
.suggestions .suggestion-item .el-icon {
  margin-right: 8px;
  margin-top: 2px;
}
</style>
