<template>
  <div class="seo-settings-page">
    <el-card>
      <template #header>
        <h3>SEO设置</h3>
      </template>

      <el-form :model="form" label-width="140px" style="max-width: 800px;" v-loading="loading">
        <el-divider>首页TDK信息</el-divider>

        <el-alert type="info" :closable="false" style="margin-bottom: 20px;">
          <template #title>
            <div style="font-size: 13px; line-height: 1.6;">
              <strong>TDK说明：</strong><br>
              • Title（标题）：显示在浏览器标签页，搜索引擎结果的标题<br>
              • Description（描述）：显示在搜索引擎结果下方的网站描述<br>
              • Keywords（关键词）：帮助搜索引擎理解页面主题的关键词
            </div>
          </template>
        </el-alert>

        <el-form-item label="标题">
          <el-input
            v-model="form.seo_title"
            placeholder="请输入网站标题"
            style="width: 600px;"
            maxlength="100"
            show-word-limit
          />
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            将在首页模板的 &lt;title&gt; 标签中调用，变量名: seo_title
          </div>
          <div style="margin-top: 5px; color: #E6A23C; font-size: 12px;">
            建议长度：30-60个字符，包含主要关键词
          </div>
        </el-form-item>

        <el-form-item label="关键词">
          <el-input
            v-model="form.seo_keywords"
            placeholder="关键词1,关键词2,关键词3"
            style="width: 600px;"
            maxlength="200"
            show-word-limit
          />
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            将在首页模板的 &lt;meta name="keywords"&gt; 中调用，变量名: seo_keywords
          </div>
          <div style="margin-top: 5px; color: #E6A23C; font-size: 12px;">
            多个关键词用英文逗号分隔，建议3-5个核心关键词
          </div>
        </el-form-item>

        <el-form-item label="描述">
          <el-input
            v-model="form.seo_description"
            type="textarea"
            :rows="4"
            placeholder="请输入网站描述"
            style="width: 600px;"
            maxlength="300"
            show-word-limit
          />
          <div style="margin-top: 5px;">
            <span style="color: #909399; font-size: 12px;">
              将在首页模板的 &lt;meta name="description"&gt; 中调用，变量名: seo_description
            </span>
          </div>
          <div style="margin-top: 5px; color: #E6A23C; font-size: 12px;">
            建议长度：80-200个字符，清晰描述网站主要内容和特点
          </div>
        </el-form-item>

        <el-divider>SEO优化建议</el-divider>

        <el-card shadow="never" style="margin-bottom: 20px;">
          <template #header>
            <span style="font-size: 14px; font-weight: 500;">
              <el-icon style="vertical-align: middle;"><info-filled /></el-icon>
              优化提示
            </span>
          </template>

          <div class="seo-tips">
            <div class="tip-item">
              <div class="tip-icon success">✓</div>
              <div class="tip-content">
                <strong>标题优化：</strong>
                <p>• 包含1-2个核心关键词</p>
                <p>• 突出品牌名称</p>
                <p>• 简洁明了，避免堆砌关键词</p>
              </div>
            </div>

            <div class="tip-item">
              <div class="tip-icon success">✓</div>
              <div class="tip-content">
                <strong>描述优化：</strong>
                <p>• 自然融入关键词</p>
                <p>• 突出网站特色和优势</p>
                <p>• 包含行动号召（如"立即访问"）</p>
              </div>
            </div>

            <div class="tip-item">
              <div class="tip-icon success">✓</div>
              <div class="tip-content">
                <strong>关键词优化：</strong>
                <p>• 选择与网站主题相关的词</p>
                <p>• 避免重复和无关关键词</p>
                <p>• 定期根据数据分析调整</p>
              </div>
            </div>
          </div>
        </el-card>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dcdfe6;">
          <el-button type="primary" size="large" @click="handleSave" :loading="saving">
            <el-icon><select /></el-icon>
            保存设置
          </el-button>
          <el-button size="large" @click="handleReset">
            <el-icon><refresh-left /></el-icon>
            重置
          </el-button>
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getConfig, saveConfig } from '@/api/config'
import { InfoFilled, Select, RefreshLeft } from '@element-plus/icons-vue'

const loading = ref(false)
const saving = ref(false)

const form = reactive({
  seo_title: '',
  seo_keywords: '',
  seo_description: ''
})

const originalForm = reactive({})

// 获取配置
const fetchConfig = async () => {
  loading.value = true
  try {
    const res = await getConfig()
    form.seo_title = res.data.seo_title || ''
    form.seo_keywords = res.data.seo_keywords || ''
    form.seo_description = res.data.seo_description || ''

    // 保存原始值
    Object.assign(originalForm, {
      seo_title: form.seo_title,
      seo_keywords: form.seo_keywords,
      seo_description: form.seo_description
    })
  } catch (error) {
    ElMessage.error('获取配置失败')
  } finally {
    loading.value = false
  }
}

// 保存配置
const handleSave = async () => {
  // 验证
  if (!form.seo_title) {
    ElMessage.warning('请输入网站标题')
    return
  }

  saving.value = true
  try {
    await saveConfig(form)
    ElMessage.success('SEO设置保存成功')

    // 更新原始值
    Object.assign(originalForm, {
      seo_title: form.seo_title,
      seo_keywords: form.seo_keywords,
      seo_description: form.seo_description
    })
  } catch (error) {
    ElMessage.error('保存失败')
  } finally {
    saving.value = false
  }
}

// 重置
const handleReset = () => {
  form.seo_title = originalForm.seo_title
  form.seo_keywords = originalForm.seo_keywords
  form.seo_description = originalForm.seo_description
  ElMessage.info('已恢复到上次保存的状态')
}

onMounted(() => {
  fetchConfig()
})
</script>

<style scoped>
.seo-settings-page h3 {
  margin: 0;
}

:deep(.el-form-item__label) {
  font-weight: 500;
}

.seo-tips {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.tip-item {
  display: flex;
  gap: 12px;
  padding: 12px;
  background-color: #f9fafb;
  border-radius: 6px;
}

.tip-icon {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: bold;
  flex-shrink: 0;
}

.tip-icon.success {
  background-color: #67C23A;
  color: white;
}

.tip-content {
  flex: 1;
}

.tip-content strong {
  color: #303133;
  font-size: 14px;
  display: block;
  margin-bottom: 8px;
}

.tip-content p {
  margin: 4px 0;
  color: #606266;
  font-size: 13px;
  line-height: 1.5;
}
</style>
