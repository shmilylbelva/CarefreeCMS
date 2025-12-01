<template>
  <div class="config-page">
    <el-alert
      type="warning"
      :closable="false"
      style="margin-bottom: 20px;"
      title="功能调整提示"
    >
      <template #default>
        <div style="line-height: 1.8;">
          <p style="margin: 0 0 10px 0;"><strong>多站点配置已迁移至站点管理</strong></p>
          <p style="margin: 0 0 5px 0;">以下配置已废弃，请前往 <strong>站点管理 → 编辑站点 → 核心设置</strong> 进行配置：</p>
          <ul style="margin: 5px 0; padding-left: 20px;">
            <li>回收站开关</li>
            <li>文档副栏目开关</li>
            <li>评论相关设置（允许游客评论、自动审核、敏感词过滤）</li>
          </ul>
          <p style="margin: 10px 0 0 0; color: #E6A23C;">
            <el-icon><InfoFilled /></el-icon>
            每个站点可以独立配置，实现多站点差异化管理
          </p>
        </div>
      </template>
    </el-alert>

    <el-card>
      <template #header>
        <h3>系统级配置</h3>
      </template>

      <el-tabs v-model="activeTab" v-loading="loading">
        <!-- 模板管理 -->
        <el-tab-pane label="模板管理" name="core">
          <el-alert type="info" :closable="false" style="margin-bottom: 20px;">
            模板套装配置用于后台管理，站点使用的模板请到站点管理中配置
          </el-alert>

          <el-form :model="form" label-width="140px" style="max-width: 800px;">
            <el-divider>模板套装管理</el-divider>

            <el-form-item label="当前模板套装">
              <el-select
                v-model="currentThemeKey"
                placeholder="请选择模板套装"
                @change="handleThemeChange"
                style="width: 400px;"
              >
                <el-option
                  v-for="theme in themes"
                  :key="theme.key"
                  :label="`${theme.name} (${theme.key})`"
                  :value="theme.key"
                >
                  <div>
                    <div><strong>{{ theme.name }}</strong></div>
                    <div style="font-size: 12px; color: #999;">{{ theme.description || '暂无描述' }}</div>
                  </div>
                </el-option>
              </el-select>
              <div style="margin-top: 5px; color: #909399; font-size: 12px;">
                切换模板套装会自动将所有页面设置为该套装的默认模板
              </div>
            </el-form-item>
          </el-form>
        </el-tab-pane>
      </el-tabs>
    </el-card>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { InfoFilled } from '@element-plus/icons-vue'
import { getTemplates, scanThemes, getCurrentTheme, switchTheme } from '@/api/template'

const loading = ref(false)
const activeTab = ref('core')
const templates = ref([])
const themes = ref([])
const currentThemeKey = ref('default')

// 表单已废弃，网站信息和附件扩展已迁移到其他管理页面


// 获取模板列表
const fetchTemplates = async () => {
  try {
    const res = await getTemplates()
    templates.value = res.data || []
  } catch (error) {
    console.error('获取模板列表失败:', error)
  }
}

// 获取模板套装列表
const fetchThemes = async () => {
  try {
    const res = await scanThemes()
    themes.value = res.data || []
  } catch (error) {
    console.error('获取模板套装列表失败:', error)
  }
}

// 获取当前模板套装
const fetchCurrentTheme = async () => {
  try {
    const res = await getCurrentTheme()
    currentThemeKey.value = res.data.key || 'default'
  } catch (error) {
    console.error('获取当前模板套装失败:', error)
  }
}

// 切换模板套装
const handleThemeChange = async (themeKey) => {
  try {
    await ElMessageBox.confirm(
      '切换模板套装会将所有页面的模板设置为新套装的默认模板，是否继续？',
      '确认切换',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const res = await switchTheme(themeKey)
    ElMessage.success(res.msg || '模板套装切换成功')

    // 重新加载模板列表
    await fetchTemplates()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.response?.data?.msg || '切换失败')
      // 恢复原值
      await fetchCurrentTheme()
    } else {
      // 用户取消，恢复原值
      await fetchCurrentTheme()
    }
  }
}

onMounted(() => {
  fetchTemplates()
  fetchThemes()
  fetchCurrentTheme()
})
</script>

<style scoped>
.config-page h3 {
  margin: 0;
}

:deep(.el-form-item__label) {
  font-weight: 500;
}
</style>
