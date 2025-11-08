<template>
  <div class="config-page">
    <el-card>
      <template #header>
        <h3>基本信息</h3>
      </template>

      <el-tabs v-model="activeTab" v-loading="loading">
        <!-- 网站信息 -->
        <el-tab-pane label="网站信息" name="site">
          <el-form :model="form" label-width="140px" style="max-width: 1000px;">
            <el-form-item label="网站名称">
              <el-input v-model="form.site_name" placeholder="请输入网站名称" style="width: 400px;" />
              <span style="margin-left: 10px; color: #909399;">变量名: site_name</span>
            </el-form-item>

            <el-form-item label="网站LOGO">
              <div>
                <el-upload
                  action="#"
                  :show-file-list="false"
                  :http-request="(e) => handleUpload(e, 'site_logo')"
                  :before-upload="beforeUpload"
                >
                  <el-button size="small">选择上传</el-button>
                </el-upload>
                <div v-if="form.site_logo" style="margin-top: 10px;">
                  <img :src="form.site_logo" style="max-width: 200px; max-height: 100px;" />
                </div>
                <span style="margin-left: 10px; color: #909399;">变量名: web_logo</span>
              </div>
            </el-form-item>

            <el-form-item label="地址栏图标">
              <div>
                <el-input v-model="form.site_favicon" placeholder="/favicon.ico" style="width: 400px;" />
                <el-upload
                  action="#"
                  :show-file-list="false"
                  :http-request="(e) => handleUpload(e, 'site_favicon')"
                  :before-upload="beforeUpload"
                  style="display: inline-block; margin-left: 10px;"
                >
                  <el-button size="small">选择上传</el-button>
                </el-upload>
                <div style="margin-top: 5px; color: #909399;">变量名: web_ico</div>
              </div>
            </el-form-item>

            <el-form-item label="前端网站网址">
              <el-input v-model="form.site_url" placeholder="http://www.example.com" style="width: 400px;" />
              <div style="margin-top: 5px; color: #909399; font-size: 12px;">
                此网址将直接指向后端api目录下的html文件夹，访问该网址即可显示网站内容
              </div>
              <span style="color: #909399;">变量名: web_basehost</span>
            </el-form-item>

            <el-form-item label="版权信息">
              <el-input
                v-model="form.site_copyright"
                type="textarea"
                :rows="3"
                placeholder="Copyright © 2024 网站名称 版权所有"
                style="width: 600px;"
              />
              <div style="margin-top: 5px; color: #909399; font-size: 12px;">
                此内容将在模板底部调用显示，变量名: web_copyright
              </div>
            </el-form-item>

            <el-form-item label="备案号">
              <el-input
                v-model="form.site_icp"
                type="textarea"
                :rows="2"
                placeholder="粤ICP备2024245973号"
                style="width: 600px;"
              />
              <div style="margin-top: 5px; color: #909399; font-size: 12px;">
                此内容将在模板底部调用显示，变量名: web_recordnum
              </div>
            </el-form-item>

            <el-form-item label="公安备案号">
              <el-input
                v-model="form.site_police"
                type="textarea"
                :rows="2"
                placeholder="请输入公安备案号"
                style="width: 600px;"
              />
              <div style="margin-top: 5px; color: #909399; font-size: 12px;">
                此内容将在模板底部调用显示，变量名: web_garecordnum
              </div>
            </el-form-item>

            <el-divider>第三方代码（出于安全考虑，以下输入的代码请慎选）</el-divider>

            <el-form-item label="代码">
              <el-input
                v-model="form.thirdparty_code_pc"
                type="textarea"
                :rows="5"
                placeholder='<script charset="UTF-8"></script>'
                style="width: 600px;"
              />
              <div style="margin-top: 5px; color: #909399; font-size: 12px;">
                此代码将在模板底部调用，变量名: web_thirdcode_pc
              </div>
            </el-form-item>
          </el-form>
        </el-tab-pane>

        <!-- 核心设置 -->
        <el-tab-pane label="核心设置" name="core">
          <el-form :model="form" label-width="140px" style="max-width: 800px;">
            <el-divider>模板设置</el-divider>

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

            <el-form-item label="首页模板">
              <el-select v-model="form.index_template" placeholder="请选择首页模板" clearable style="width: 400px;">
                <el-option label="默认首页模板(index)" value="index" />
                <el-option
                  v-for="tpl in templates"
                  :key="tpl.template_key"
                  :label="tpl.name"
                  :value="tpl.template_key"
                />
              </el-select>
              <div style="margin-top: 5px; color: #909399; font-size: 12px;">
                选择用于生成首页的模板文件
              </div>
            </el-form-item>

            <el-divider>系统功能</el-divider>

            <el-form-item label="回收站">
              <el-radio-group v-model="form.recycle_bin_enable">
                <el-radio value="open">开启</el-radio>
                <el-radio value="close">关闭</el-radio>
              </el-radio-group>
              <div style="margin-top: 5px; color: #909399; font-size: 12px;">
                开启后，删除文章和媒体文件将进入回收站而不是物理删除；关闭则直接物理删除
              </div>
            </el-form-item>

            <el-form-item label="文档副栏目">
              <el-radio-group v-model="form.article_sub_category">
                <el-radio value="open">开启</el-radio>
                <el-radio value="close">关闭</el-radio>
              </el-radio-group>
              <div style="margin-top: 5px; color: #909399; font-size: 12px;">
                开启后，一篇文章可以同时属于多个分类（一个主分类+多个副分类）
              </div>
            </el-form-item>
          </el-form>
        </el-tab-pane>

        <!-- 附件扩展 -->
        <el-tab-pane label="附件扩展" name="attachment">
          <el-form :model="form" label-width="160px" style="max-width: 800px;">
            <el-form-item label="面包屑首页名">
              <el-input v-model="form.breadcrumb_home" placeholder="首页" style="width: 300px;" />
            </el-form-item>

            <el-form-item label="面包屑间隔符">
              <el-input v-model="form.breadcrumb_separator" placeholder=">" style="width: 100px;" />
            </el-form-item>

            <el-form-item label="上传图片格式">
              <el-input
                v-model="form.upload_image_ext"
                type="textarea"
                :rows="2"
                placeholder="jpg|gif|png|bmp|jpeg|ico|webp"
                style="width: 500px;"
              />
            </el-form-item>

            <el-form-item label="上传软件格式">
              <el-input
                v-model="form.upload_file_ext"
                type="textarea"
                :rows="2"
                placeholder="zip|gz|rar|iso|doc|xls|ppt|wps|docx|xlsx|pptx"
                style="width: 500px;"
              />
            </el-form-item>

            <el-form-item label="视频格式">
              <el-input
                v-model="form.upload_video_ext"
                type="textarea"
                :rows="2"
                placeholder="swf|mpg|mp3|rm|rmvb|wmv|wma|wav|mid|mov|mp4"
                style="width: 500px;"
              />
            </el-form-item>

            <el-form-item label="附件上传大小">
              <el-input-number v-model="form.upload_max_size" :min="1" :max="100" />
              <span style="margin-left: 10px;">MB</span>
            </el-form-item>

            <el-form-item label="附件命名">
              <el-radio-group v-model="form.upload_rename">
                <el-radio value="keep">不做改动</el-radio>
                <el-radio value="random">随机重命名</el-radio>
              </el-radio-group>
            </el-form-item>

            <el-form-item label="内容图片加功能">
              <el-checkbox-group v-model="imageFeatures">
                <el-checkbox value="wap_adapt">wap端自适应</el-checkbox>
                <el-checkbox value="add_title">追加title属性</el-checkbox>
                <el-checkbox value="add_alt">追加alt属性</el-checkbox>
              </el-checkbox-group>
            </el-form-item>

            <el-form-item label="文档默认点击数">
              <el-input v-model="form.article_default_views" placeholder="500|1000" style="width: 200px;" />
            </el-form-item>

            <el-form-item label="文档默认下载数">
              <el-input v-model="form.article_default_downloads" placeholder="100|500" style="width: 200px;" />
            </el-form-item>
          </el-form>
        </el-tab-pane>
      </el-tabs>

      <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #dcdfe6;">
        <el-button type="primary" size="large" @click="handleSave" :loading="saving">
          确认提交
        </el-button>
      </div>
    </el-card>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { getConfig, saveConfig } from '@/api/config'
import { getTemplates, scanThemes, getCurrentTheme, switchTheme } from '@/api/template'
import request from '@/api/request'

const loading = ref(false)
const saving = ref(false)
const activeTab = ref('site')
const templates = ref([])
const themes = ref([])
const currentThemeKey = ref('default')

const form = reactive({
  // 网站信息
  site_name: '',
  site_logo: '',
  site_favicon: '',
  site_url: '',
  site_copyright: '',
  site_icp: '',
  site_police: '',
  index_template: 'index',
  seo_title: '',
  seo_keywords: '',
  seo_description: '',
  thirdparty_code_pc: '',

  // 核心设置
  recycle_bin_enable: 'open',
  article_sub_category: 'close',

  // 附件扩展
  breadcrumb_home: '首页',
  breadcrumb_separator: '>',
  upload_image_ext: 'jpg|gif|png|bmp|jpeg|ico|webp',
  upload_file_ext: 'zip|gz|rar|iso|doc|xls|ppt|wps|docx|xlsx|pptx',
  upload_video_ext: 'swf|mpg|mp3|rm|rmvb|wmv|wma|wav|mid|mov|mp4',
  upload_max_size: 2,
  upload_rename: 'random',
  content_image_features: '',
  article_default_views: '500|1000',
  article_default_downloads: '100|500'
})

// 图片功能多选
const imageFeatures = ref([])

// 监听imageFeatures变化，同步到form
watch(imageFeatures, (val) => {
  form.content_image_features = val.join(',')
}, { deep: true })

// 获取配置
const fetchConfig = async () => {
  loading.value = true
  try {
    const res = await getConfig()
    Object.assign(form, res.data)

    // 处理图片功能多选
    if (res.data.content_image_features) {
      imageFeatures.value = res.data.content_image_features.split(',')
    }
  } catch (error) {
    ElMessage.error('获取配置失败')
  } finally {
    loading.value = false
  }
}

// 上传前检查
const beforeUpload = (file) => {
  // 检查文件扩展名
  const fileName = file.name.toLowerCase()
  const validExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.ico', '.bmp']
  const hasValidExt = validExtensions.some(ext => fileName.endsWith(ext))

  // 检查MIME类型（.ico文件可能有不同的MIME类型）
  const isImage = file.type.startsWith('image/') || file.type === 'image/x-icon' || file.type === 'image/vnd.microsoft.icon'
  const isLt5M = file.size / 1024 / 1024 < 5

  if (!isImage && !hasValidExt) {
    ElMessage.error('只能上传图片文件!')
    return false
  }
  if (!isLt5M) {
    ElMessage.error('图片大小不能超过 5MB!')
    return false
  }
  return true
}

// 上传图片
const handleUpload = async ({ file }, field) => {
  const formData = new FormData()
  formData.append('file', file)

  try {
    const res = await request({
      url: '/media/upload',
      method: 'post',
      data: formData,
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    form[field] = res.data.file_url
    ElMessage.success('上传成功')
  } catch (error) {
    ElMessage.error('上传失败')
  }
}

// 保存配置
const handleSave = async () => {
  saving.value = true
  try {
    await saveConfig(form)
    ElMessage.success('配置保存成功')
  } catch (error) {
    ElMessage.error('配置保存失败')
  } finally {
    saving.value = false
  }
}

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

    // 重新加载配置和模板列表
    await fetchConfig()
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
  fetchConfig()
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
