<template>
  <div class="article-edit">
    <el-card>
      <template #header>
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <h3>{{ isEdit ? '编辑文章' : '创建文章' }}</h3>
          <el-button v-if="isEdit" type="info" @click="showVersionHistory">
            版本历史
          </el-button>
        </div>
      </template>

      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="文章标题" prop="title">
          <el-input v-model="form.title" placeholder="请输入文章标题" />
        </el-form-item>

        <el-form-item label="所属站点" :prop="isEdit ? 'site_id' : 'site_ids'">
          <!-- 编辑时：单选 -->
          <el-select v-if="isEdit" v-model="form.site_id" placeholder="请选择站点" style="width: 100%;">
            <el-option
              v-for="site in siteOptions"
              :key="site.id"
              :label="site.name"
              :value="site.id"
            />
          </el-select>
          <!-- 创建时：多选 -->
          <div v-else>
            <el-select v-model="form.site_ids" placeholder="请选择站点（可多选）" multiple style="width: 100%;">
              <el-option
                v-for="site in siteOptions"
                :key="site.id"
                :label="site.name"
                :value="site.id"
              />
            </el-select>
            <div style="margin-top: 8px;">
              <el-button size="small" @click="selectAllSites">全选</el-button>
              <el-button size="small" @click="deselectAllSites">取消全选</el-button>
            </div>
          </div>
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            <template v-if="isEdit">
              编辑时只能修改当前站点的文章，不影响其他站点
            </template>
            <template v-else>
              创建时可选择多个站点，系统将为每个站点创建独立副本
            </template>
          </div>
        </el-form-item>

        <el-form-item label="主分类" prop="category_id">
          <el-tree-select
            v-model="form.category_id"
            :data="categories"
            :props="{ value: 'id', label: 'name', children: 'children' }"
            placeholder="请选择主分类"
            check-strictly
            style="width: 100%;"
          >
            <template #default="{ data }">
              <span>{{ formatCategoryLabel(data) }}</span>
            </template>
          </el-tree-select>
        </el-form-item>

        <el-form-item label="副分类" v-if="subCategoryEnabled">
          <el-tree-select
            v-model="form.sub_categories"
            :data="categories"
            :props="{ value: 'id', label: 'name', children: 'children' }"
            placeholder="请选择副分类（可选）"
            multiple
            check-strictly
            style="width: 100%;"
          >
            <template #default="{ data }">
              <span>{{ formatCategoryLabel(data) }}</span>
            </template>
          </el-tree-select>
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            选择的副分类将与主分类一起显示该文章，不选择则只在主分类下显示
          </div>
        </el-form-item>

        <el-form-item label="标签" prop="tags">
          <el-select
            v-model="form.tags"
            placeholder="选择标签或输入新标签名称（按回车添加）"
            multiple
            filterable
            allow-create
            default-first-option
            :reserve-keyword="false"
            style="width: 100%;"
            @change="handleTagChange"
          >
            <el-option
              v-for="tag in tags"
              :key="tag.id"
              :label="formatTagLabel(tag)"
              :value="tag.id"
            >
              <span style="float: left">{{ formatTagLabel(tag) }}</span>
              <span style="float: right; color: #8492a6; font-size: 13px">
                {{ tag.article_count || 0 }} 篇
              </span>
            </el-option>
          </el-select>
          <div style="margin-top: 10px; color: #909399; font-size: 12px;">
            提示：可直接输入新标签名称后按回车，保存文章时会自动加入标签库
          </div>
        </el-form-item>

        <!-- 标签预览 -->
        <el-form-item label="已选标签" v-if="displayTags.length > 0">
          <el-tag
            v-for="(tag, index) in displayTags"
            :key="index"
            closable
            @close="removeTag(index)"
            :type="tag.isNew ? 'success' : ''"
            style="margin-right: 10px; margin-bottom: 5px"
          >
            {{ tag.name }}
            <span v-if="tag.isNew" style="font-size: 10px; margin-left: 3px">(新)</span>
          </el-tag>
        </el-form-item>

        <el-form-item label="所属专题">
          <el-select v-model="form.topics" placeholder="请选择专题（可选）" multiple style="width: 100%;">
            <el-option
              v-for="topic in topics"
              :key="topic.id"
              :label="formatTopicLabel(topic)"
              :value="topic.id"
            />
          </el-select>
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            选择专题后，文章将出现在对应专题页面中
          </div>
        </el-form-item>

        <el-form-item label="文章属性">
          <el-checkbox-group v-model="form.flags">
            <el-checkbox
              v-for="flag in articleFlags"
              :key="flag.id"
              :label="flag.flag_value"
            >
              {{ flag.name }}
            </el-checkbox>
          </el-checkbox-group>
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            选择文章属性后，可在模板中根据属性筛选文章
          </div>
        </el-form-item>

        <el-form-item label="摘要" prop="summary">
          <el-input
            v-model="form.summary"
            type="textarea"
            :rows="3"
            placeholder="请输入文章摘要"
          />
        </el-form-item>

        <el-form-item label="封面图片">
          <el-upload
            class="cover-uploader"
            :action="uploadAction"
            :headers="uploadHeaders"
            :show-file-list="false"
            :on-success="handleCoverSuccess"
            :before-upload="beforeCoverUpload"
            name="file"
          >
            <img v-if="coverImageUrl" :src="coverImageUrl" class="cover-image" />
            <el-icon v-else class="cover-uploader-icon"><Plus /></el-icon>
          </el-upload>
          <el-button
            v-if="form.cover_image"
            size="small"
            type="danger"
            @click="handleRemoveCover"
            style="margin-top: 10px;"
          >
            删除封面
          </el-button>
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            建议尺寸：800x450像素（16:9比例），支持jpg、png、gif格式，大小不超过2MB。图片将自动缩放适应展示框。
          </div>
        </el-form-item>

        <el-form-item label="内容" prop="content">
          <div style="margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
            <span style="color: #909399; font-size: 12px;">支持HTML格式</span>
            <el-button
              type="primary"
              size="small"
              @click="showAiGenerateDialog = true"
              :icon="MagicStick"
            >
              AI生成内容
            </el-button>
          </div>
          <TinyMCE v-model="form.content" :height="500" />
        </el-form-item>

        <el-form-item label="SEO关键词">
          <el-input v-model="form.seo_keywords" placeholder="请输入SEO关键词，多个用逗号分隔" />
        </el-form-item>

        <el-form-item label="SEO描述">
          <el-input
            v-model="form.seo_description"
            type="textarea"
            :rows="2"
            placeholder="请输入SEO描述"
          />
        </el-form-item>

        <el-form-item label="修改说明" v-if="isEdit">
          <el-input
            v-model="form.change_log"
            placeholder="请输入本次修改的说明（可选）"
            maxlength="500"
            show-word-limit
          />
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            记录本次修改的内容说明，方便日后查看版本历史
          </div>
        </el-form-item>

        <!-- 自定义字段 -->
        <el-divider v-if="formattedCustomFields.length > 0" content-position="left">
          自定义字段
        </el-divider>
        <CustomFieldRenderer
          v-if="formattedCustomFields.length > 0"
          :fields="formattedCustomFields"
          v-model="customFieldValues"
        />

        <el-form-item>
          <el-button type="primary" @click="handleSave('draft')" :loading="saving">
            保存草稿
          </el-button>
          <el-button type="success" @click="handleSave('published')" :loading="saving">
            发布文章
          </el-button>
          <el-button @click="$router.back()">取消</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- AI生成内容对话框 -->
    <el-dialog
      v-model="showAiGenerateDialog"
      title="AI生成文章内容"
      width="600px"
      @open="loadAiGenerateOptions"
    >
      <el-form label-width="120px">
        <el-form-item label="文章标题">
          <el-input :value="form.title" disabled />
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            将基于此标题生成文章内容
          </div>
        </el-form-item>

        <el-form-item label="AI配置">
          <el-select v-model="aiGenerateOptions.ai_config_id" placeholder="请选择AI配置" style="width: 100%;">
            <el-option
              v-for="config in aiConfigs"
              :key="config.id"
              :label="config.name + (config.is_default ? ' (默认)' : '')"
              :value="config.id"
            >
              <span>{{ config.name }}</span>
              <span style="float: right; color: #8492a6; font-size: 13px; margin-left: 10px;">
                {{ config.provider_name }} - {{ config.model_name }}
              </span>
            </el-option>
          </el-select>
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            选择用于生成文章的AI服务配置
          </div>
        </el-form-item>

        <el-form-item label="提示词模板">
          <el-select
            v-model="aiGenerateOptions.prompt_template_id"
            placeholder="不使用模板（默认）"
            clearable
            style="width: 100%;"
            @change="handleTemplateChange"
          >
            <el-option label="不使用模板" :value="null" />
            <el-option-group
              v-for="(templates, category) in groupedPromptTemplates"
              :key="category"
              :label="category"
            >
              <el-option
                v-for="template in templates"
                :key="template.id"
                :label="template.name"
                :value="template.id"
              >
                <span>{{ template.name }}</span>
                <span v-if="template.description" style="float: right; color: #8492a6; font-size: 12px; margin-left: 10px;">
                  {{ template.description.substring(0, 20) }}{{ template.description.length > 20 ? '...' : '' }}
                </span>
              </el-option>
            </el-option-group>
          </el-select>
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            使用预设模板可生成更专业的内容。不选择则使用系统默认提示词
          </div>
        </el-form-item>

        <!-- 模板变量输入 -->
        <div v-if="selectedTemplateVariables.length > 0" style="margin-left: 120px; margin-bottom: 20px;">
          <el-divider content-position="left">模板参数</el-divider>
          <el-form-item
            v-for="variable in selectedTemplateVariables"
            :key="variable.name"
            :label="variable.label"
            label-width="150px"
          >
            <el-input
              v-model="aiGenerateOptions.template_variables[variable.name]"
              :placeholder="variable.description || `请输入${variable.label}`"
              :type="variable.type === 'textarea' ? 'textarea' : 'text'"
              :rows="3"
            />
            <div v-if="variable.example" style="margin-top: 5px; color: #909399; font-size: 12px;">
              示例: {{ variable.example }}
            </div>
          </el-form-item>
        </div>

        <!-- 不使用模板时显示的选项 -->
        <template v-if="!aiGenerateOptions.prompt_template_id">
          <el-form-item label="文章长度">
            <el-radio-group v-model="aiGenerateOptions.length">
              <el-radio label="short">短篇 (500-800字)</el-radio>
              <el-radio label="medium">中篇 (1000-1500字)</el-radio>
              <el-radio label="long">长篇 (2000-3000字)</el-radio>
            </el-radio-group>
          </el-form-item>

          <el-form-item label="写作风格">
            <el-radio-group v-model="aiGenerateOptions.style">
              <el-radio label="professional">专业严谨</el-radio>
              <el-radio label="casual">轻松随意</el-radio>
              <el-radio label="technical">技术深度</el-radio>
            </el-radio-group>
          </el-form-item>
        </template>

        <el-alert
          v-if="!form.title"
          title="请先填写文章标题"
          type="warning"
          :closable="false"
        />

        <el-alert
          v-if="aiConfigs.length === 0"
          title="未找到可用的AI配置，请先在系统设置中配置AI服务"
          type="error"
          :closable="false"
          style="margin-top: 10px;"
        />
      </el-form>

      <template #footer>
        <el-button @click="showAiGenerateDialog = false">取消</el-button>
        <el-button
          type="primary"
          @click="handleAiGenerate"
          :loading="aiGenerating"
          :disabled="!form.title || aiConfigs.length === 0"
        >
          {{ aiGenerating ? '生成中...' : '开始生成' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- 版本历史对话框 -->
    <ArticleVersionList
      v-if="isEdit"
      v-model="versionHistoryVisible"
      :article-id="route.params.id"
      @rollback="handleVersionRollback"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, MagicStick } from '@element-plus/icons-vue'
import { getArticleDetail, createArticle, updateArticle, generateArticleContent } from '@/api/article'
import { getCategoryTree } from '@/api/category'
import { getAllTags } from '@/api/tag'
import { getAllArticleFlags } from '@/api/articleFlag'
import { getAllTopics } from '@/api/topic'
import { getSiteOptions, getSiteDetail } from '@/api/site'
import { getAllAiConfigs } from '@/api/ai'
import { getAllPromptTemplates, getPromptTemplateDetail } from '@/api/ai'
import { getToken } from '@/utils/auth'
import TinyMCE from '@/components/TinyMCE.vue'
import ArticleVersionList from '@/components/ArticleVersionList.vue'
import CustomFieldRenderer from '@/components/CustomFieldRenderer.vue'
import { getFieldsByModel, getEntityValues, saveEntityValues } from '@/api/customField'

const route = useRoute()
const router = useRouter()

const formRef = ref(null)
const saving = ref(false)
const siteOptions = ref([])
const categories = ref([])
const tags = ref([])
const articleFlags = ref([])
const topics = ref([])
const subCategoryEnabled = ref(false)
const versionHistoryVisible = ref(false)
const customFields = ref([])
const customFieldValues = ref({})

// AI生成相关
const showAiGenerateDialog = ref(false)
const aiGenerating = ref(false)
const aiConfigs = ref([])
const promptTemplates = ref([])
const selectedTemplate = ref(null)
const aiGenerateOptions = reactive({
  ai_config_id: '',
  prompt_template_id: null,
  template_variables: {},
  length: 'medium',
  style: 'professional'
})

// 计算属性：按分类分组的提示词模板
const groupedPromptTemplates = computed(() => {
  const groups = {}
  promptTemplates.value.forEach(template => {
    const category = template.category || '其他'
    if (!groups[category]) {
      groups[category] = []
    }
    groups[category].push(template)
  })
  return groups
})

// 计算属性：选中模板的变量
const selectedTemplateVariables = computed(() => {
  if (!selectedTemplate.value || !selectedTemplate.value.variables) {
    return []
  }
  return selectedTemplate.value.variables
})

// 获取站点名称的辅助函数
const getSiteName = (siteId) => {
  const site = siteOptions.value.find(s => s.id === siteId)
  return site ? site.name : '未知'
}

// 格式化分类显示名称
const formatCategoryLabel = (category) => {
  const siteName = getSiteName(category.site_id)
  return `[${siteName}] ${category.name}`
}

// 格式化标签显示名称
const formatTagLabel = (tag) => {
  const siteName = getSiteName(tag.site_id)
  return `[${siteName}] ${tag.name}`
}

// 格式化专题显示名称
const formatTopicLabel = (topic) => {
  const siteName = getSiteName(topic.site_id)
  return `[${siteName}] ${topic.name}`
}

const isEdit = computed(() => !!route.params.id)

// 格式化后的自定义字段（添加站点前缀）
const formattedCustomFields = computed(() => {
  return customFields.value.map(field => {
    if (!field.site_id) return field
    const siteName = getSiteName(field.site_id)
    return {
      ...field,
      name: `[${siteName}] ${field.name}`
    }
  })
})

const form = reactive({
  title: '',
  site_id: 1,
  site_ids: [], // 多站点创建
  category_id: '',
  sub_categories: [],
  tags: [],
  topics: [],
  flags: [],
  summary: '',
  cover_image: '',
  content: '',
  seo_keywords: '',
  seo_description: '',
  change_log: ''
})

const rules = {
  title: [{ required: true, message: '请输入文章标题', trigger: 'blur' }],
  category_id: [{ required: true, message: '请选择分类', trigger: 'change' }],
  content: [{ required: true, message: '请输入文章内容', trigger: 'blur' }],
  site_ids: [{ required: true, type: 'array', min: 1, message: '请至少选择一个站点', trigger: 'change' }]
}

// 计算显示的标签（用于预览）
const displayTags = computed(() => {
  return form.tags.map(tag => {
    if (typeof tag === 'number') {
      // 如果是ID，从tags中查找
      const found = tags.value.find(t => t.id === tag)
      return {
        name: found ? found.name : `标签#${tag}`,
        isNew: false
      }
    } else {
      // 如果是字符串，说明是新标签
      return {
        name: tag,
        isNew: true
      }
    }
  })
})

// 从站点配置加载副分类开关
const loadSiteConfig = async (siteId) => {
  if (!siteId) return

  try {
    const res = await getSiteDetail(siteId)
    // 添加空值检查，防止配置值不存在时报错
    subCategoryEnabled.value = res.data?.config?.article_sub_category === 'open'
  } catch (error) {
    console.error('加载站点配置失败', error)
    // 默认关闭副分类
    subCategoryEnabled.value = false
  }
}

// 加载分类、标签、文章属性和专题
const loadOptions = async (siteId = null) => {
  try {
    const params = siteId ? { site_id: siteId } : {}
    const [siteRes, categoryRes, tagRes, flagRes, topicRes] = await Promise.all([
      getSiteOptions(),
      getCategoryTree(params),
      getAllTags(params),
      getAllArticleFlags(params),
      getAllTopics(params)
    ])
    siteOptions.value = siteRes.data || []
    categories.value = categoryRes.data || []
    tags.value = tagRes.data || []
    articleFlags.value = flagRes.data || []
    topics.value = topicRes.data.list || []
  } catch (error) {
    ElMessage.error('加载选项失败')
  }
}

// 加载多个站点的选项（用于创建文章时的多站点选择）
const loadOptionsForSites = async (siteIds) => {
  try {
    // 将站点ID数组转换为逗号分隔的字符串，或使用site_ids参数
    const params = siteIds && siteIds.length > 0 ? { site_ids: siteIds.join(',') } : {}
    const [siteRes, categoryRes, tagRes, flagRes, topicRes] = await Promise.all([
      getSiteOptions(),
      getCategoryTree(params),
      getAllTags(params),
      getAllArticleFlags(params),
      getAllTopics(params)
    ])
    siteOptions.value = siteRes.data || []
    categories.value = categoryRes.data || []
    tags.value = tagRes.data || []
    articleFlags.value = flagRes.data || []
    topics.value = topicRes.data.list || []
  } catch (error) {
    ElMessage.error('加载选项失败')
  }
}

// 加载文章详情
const loadArticle = async () => {
  try {
    const res = await getArticleDetail(route.params.id)
    Object.assign(form, res.data)
    form.tags = res.data.tags?.map(t => t.id) || []
    form.topics = res.data.topics?.map(t => t.id) || []

    // 加载副分类
    if (res.data.sub_categories) {
      form.sub_categories = res.data.sub_categories.map(c => c.id) || []
    }

    // 加载文章属性（确保转换为数组）
    form.flags = res.data.flags ? res.data.flags.split('') : []

    // 加载自定义字段值
    await loadCustomFieldValues()
  } catch (error) {
    ElMessage.error('加载文章详情失败')
  }
}

// 加载自定义字段定义
const loadCustomFields = async (siteId = null) => {
  try {
    const res = await getFieldsByModel('article', null, siteId)
    customFields.value = res.data.fields || []
  } catch (error) {
    console.error('加载自定义字段失败', error)
  }
}

// 加载自定义字段值
const loadCustomFieldValues = async () => {
  if (!route.params.id) return
  try {
    const res = await getEntityValues('article', route.params.id)
    customFieldValues.value = res.data || {}
  } catch (error) {
    console.error('加载自定义字段值失败', error)
  }
}

// 上传配置
const uploadAction = computed(() => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL || ''
  return baseUrl + '/media/upload'
})

const uploadHeaders = computed(() => {
  const token = getToken() || ''
  return {
    Authorization: 'Bearer ' + token
  }
})

// 封面图片URL
const coverImageUrl = computed(() => {
  // 直接使用后端返回的完整URL
  return form.cover_image || ''
})

// 上传前校验
const beforeCoverUpload = (file) => {
  const isImage = file.type.startsWith('image/')
  const isLt2M = file.size / 1024 / 1024 < 2

  if (!isImage) {
    ElMessage.error('只能上传图片文件!')
    return false
  }
  if (!isLt2M) {
    ElMessage.error('图片大小不能超过 2MB!')
    return false
  }
  return true
}

// 上传成功回调
const handleCoverSuccess = (response) => {
  if (response.code === 200) {
    // 使用后端返回的完整URL
    form.cover_image = response.data.file_url || response.data.file_path
    ElMessage.success('封面上传成功')
  } else {
    ElMessage.error(response.message || '上传失败')
  }
}

// 删除封面
const handleRemoveCover = () => {
  form.cover_image = ''
  ElMessage.success('封面已删除')
}

// 标签变化处理
const handleTagChange = (values) => {
  // 检查新标签长度
  const newTags = values.filter(v => typeof v === 'string')
  for (const tag of newTags) {
    if (tag.length > 50) {
      ElMessage.warning(`标签"${tag}"长度超过50字符，请修改后重试`)
      // 移除过长的标签
      form.tags = form.tags.filter(t => t !== tag)
      return
    }
  }

  // 输出调试信息
  if (newTags.length > 0) {
    console.log('新标签:', newTags)
    ElMessage.success(`已添加 ${newTags.length} 个新标签，保存后将自动加入标签库`)
  }
}

// 移除标签
const removeTag = (index) => {
  form.tags.splice(index, 1)
}

// 保存文章
const handleSave = async (status) => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      saving.value = true
      try {
        // 将状态字符串转换为数字
        // 0 = 草稿, 1 = 已发布, 2 = 待审核, 3 = 已下线
        const statusMap = {
          'draft': 0,
          'published': 1,
          'review': 2,
          'offline': 3
        }
        const statusValue = statusMap[status] !== undefined ? statusMap[status] : 0

        let data = {
          ...form,
          status: statusValue,
          tags: form.tags,  // 直接传递tags数组（可包含ID或名称）
          flags: Array.isArray(form.flags) ? form.flags.join('') : (form.flags || '')  // 将数组转换为字符串
        }

        // 区分创建和编辑模式的站点字段
        if (isEdit.value) {
          // 编辑模式：只保留 site_id，移除 site_ids
          delete data.site_ids
        } else {
          // 创建模式：只保留 site_ids，移除 site_id
          delete data.site_id
        }

        let articleId = route.params.id

        if (isEdit.value) {
          await updateArticle(route.params.id, data)
          ElMessage.success('更新成功')
          // 清空修改说明
          form.change_log = ''
        } else {
          const res = await createArticle(data)
          articleId = res.data.id

          // 检查是否有新标签
          const newTagsCount = form.tags.filter(t => typeof t === 'string').length
          if (newTagsCount > 0) {
            ElMessage.success(`文章创建成功，${newTagsCount} 个新标签已自动加入标签库`)
          } else {
            ElMessage.success('创建成功')
          }
        }

        // 保存自定义字段值
        if (customFields.value.length > 0 && articleId) {
          try {
            await saveEntityValues('article', articleId, customFieldValues.value)
          } catch (error) {
            console.error('保存自定义字段值失败', error)
          }
        }

        // 如果有新标签，重新加载标签列表
        const hasNewTags = form.tags.some(t => typeof t === 'string')
        if (hasNewTags) {
          await loadOptions()
        }

        router.push('/articles')
      } catch (error) {
        ElMessage.error(error.message || '保存失败')
      } finally {
        saving.value = false
      }
    }
  })
}

// 显示版本历史
const showVersionHistory = () => {
  versionHistoryVisible.value = true
}

// 处理版本回滚
const handleVersionRollback = () => {
  // 回滚成功后重新加载文章数据
  ElMessage.success('版本已回滚，正在重新加载...')
  loadArticle()
}

// 全选站点
const selectAllSites = () => {
  form.site_ids = siteOptions.value.map(site => site.id)
}

// 取消全选站点
const deselectAllSites = () => {
  form.site_ids = []
}

// 监听站点变化（仅在编辑模式下）
watch(() => form.site_id, async (newSiteId, oldSiteId) => {
  // 只有在编辑模式下且站点真正发生变化时才触发
  if (isEdit.value && oldSiteId !== undefined && newSiteId !== oldSiteId) {
    // 保存原来选中的值
    const savedCategoryId = form.category_id
    const savedSubCategories = [...(form.sub_categories || [])]
    const savedTags = [...(form.tags || [])]
    const savedTopics = [...(form.topics || [])]

    // 清空相关选择
    form.category_id = null
    form.sub_categories = []
    form.tags = []
    form.topics = []

    // 重新加载该站点的选项
    await loadOptions(newSiteId)

    // 加载完列表后，检查原值是否在新列表中，如果在就恢复选中
    // 检查主分类
    if (savedCategoryId && categories.value.some(c => c.id === savedCategoryId)) {
      form.category_id = savedCategoryId
    }

    // 检查副分类
    if (savedSubCategories.length > 0) {
      form.sub_categories = savedSubCategories.filter(id =>
        categories.value.some(c => c.id === id)
      )
    }

    // 检查标签
    if (savedTags.length > 0) {
      form.tags = savedTags.filter(id =>
        tags.value.some(t => t.id === id)
      )
    }

    // 检查专题
    if (savedTopics.length > 0) {
      form.topics = savedTopics.filter(id =>
        topics.value.some(t => t.id === id)
      )
    }

    // 注意：文章属性（flags）是全局共享的，不清空

    // 清空自定义字段值并重新加载该站点的自定义字段定义
    customFieldValues.value = {}
    await loadCustomFields(newSiteId)

    ElMessage.info('已切换站点，相关选项已刷新')
  }
})

// 监听站点变化（创建模式下的多站点选择）
watch(() => form.site_ids, async (newSiteIds, oldSiteIds) => {
  // 只在创建模式下且站点真正发生变化时触发
  if (!isEdit.value && oldSiteIds !== undefined && JSON.stringify(newSiteIds) !== JSON.stringify(oldSiteIds)) {
    // 如果选中了站点，重新加载这些站点的选项（显示所有选中站点的数据）
    if (newSiteIds && newSiteIds.length > 0) {
      // 清空相关选择
      form.category_id = null
      form.sub_categories = []
      form.tags = []
      form.topics = []

      // 重新加载选中站点的选项（传递站点IDs数组）
      await loadOptionsForSites(newSiteIds)

      // 清空自定义字段值并重新加载自定义字段定义
      customFieldValues.value = {}
      await loadCustomFields()

	 // 加载第一个选择站点的配置（用于判断是否启用副分类等）
      await loadSiteConfig(newSiteIds[0])
      ElMessage.info('已更新站点选择，相关选项已刷新')
    } else {
      // 如果没有选中站点，清空所有相关数据
      form.category_id = null
      form.sub_categories = []
      form.tags = []
      form.topics = []
      categories.value = []
      tags.value = []
      topics.value = []
      customFields.value = []
      customFieldValues.value = {}
	  subCategoryEnabled.value = false
    }
  }
}, { deep: true })

// 加载AI生成选项
const loadAiGenerateOptions = async () => {
  try {
    // 加载AI配置列表
    const aiConfigRes = await getAllAiConfigs({ text_generation_only: true })
    aiConfigs.value = aiConfigRes.data || []

    // 自动选择默认配置
    if (aiConfigs.value.length > 0 && !aiGenerateOptions.ai_config_id) {
      const defaultConfig = aiConfigs.value.find(c => c.is_default)
      aiGenerateOptions.ai_config_id = defaultConfig ? defaultConfig.id : aiConfigs.value[0].id
    }

    // 加载提示词模板列表
    const templatesRes = await getAllPromptTemplates()
    promptTemplates.value = templatesRes.data || []
  } catch (error) {
    console.error('加载AI选项失败', error)
  }
}

// 处理模板选择变化
const handleTemplateChange = async (templateId) => {
  if (!templateId) {
    selectedTemplate.value = null
    aiGenerateOptions.template_variables = {}
    return
  }

  try {
    // 获取模板详情
    const res = await getPromptTemplateDetail(templateId)
    selectedTemplate.value = res.data

    // 初始化模板变量
    aiGenerateOptions.template_variables = {}
    if (selectedTemplate.value.variables && Array.isArray(selectedTemplate.value.variables)) {
      selectedTemplate.value.variables.forEach(variable => {
        aiGenerateOptions.template_variables[variable.name] = variable.default || ''
      })
    }

    // 自动填充文章标题到 {title} 变量
    if (aiGenerateOptions.template_variables.hasOwnProperty('title')) {
      aiGenerateOptions.template_variables.title = form.title
    }
  } catch (error) {
    console.error('加载模板详情失败', error)
  }
}

// AI生成文章内容
const handleAiGenerate = async () => {
  if (!form.title) {
    ElMessage.warning('请先填写文章标题')
    return
  }

  // 如果已有内容，提示用户确认
  if (form.content && form.content.trim()) {
    try {
      await ElMessageBox.confirm(
        '当前已有文章内容，AI生成的内容将会覆盖现有内容，是否继续？',
        '提示',
        {
          confirmButtonText: '继续生成',
          cancelButtonText: '取消',
          type: 'warning'
        }
      )
    } catch {
      return
    }
  }

  aiGenerating.value = true
  try {
    const params = {
      title: form.title,
      ai_config_id: aiGenerateOptions.ai_config_id
    }

    // 如果使用了模板
    if (aiGenerateOptions.prompt_template_id) {
      params.prompt_template_id = aiGenerateOptions.prompt_template_id
      params.template_variables = aiGenerateOptions.template_variables
    } else {
      // 如果不使用模板，传递长度和风格参数
      params.length = aiGenerateOptions.length
      params.style = aiGenerateOptions.style
    }

    console.log('=== AI文章生成开始 ===')
    console.log('1. 文章标题:', form.title)
    console.log('2. AI配置ID:', aiGenerateOptions.ai_config_id)
    console.log('3. 是否使用模板:', !!aiGenerateOptions.prompt_template_id)
    if (aiGenerateOptions.prompt_template_id) {
      console.log('4. 模板ID:', aiGenerateOptions.prompt_template_id)
      console.log('5. 模板变量:', aiGenerateOptions.template_variables)
    } else {
      console.log('4. 文章长度:', aiGenerateOptions.length)
      console.log('5. 写作风格:', aiGenerateOptions.style)
    }
    console.log('6. 完整请求参数:', JSON.stringify(params, null, 2))

    const res = await generateArticleContent(params)

    console.log('7. 服务器响应:', res)
    console.log('8. 生成的内容长度:', res.data.content?.length || 0, '字符')
    console.log('9. 是否生成摘要:', !!res.data.summary)
    console.log('10. 使用的AI配置:', res.data.ai_config_name)
    console.log('=== AI文章生成成功 ===')

    // 填充生成的内容
    form.content = res.data.content

    // 如果有生成摘要，并且当前没有摘要，则填充摘要
    if (res.data.summary && !form.summary) {
      form.summary = res.data.summary
    }

    ElMessage.success(`AI内容生成成功（使用: ${res.data.ai_config_name}）`)
    showAiGenerateDialog.value = false
  } catch (error) {
    console.error('=== AI文章生成失败 ===')
    console.error('错误详情:', error)
    console.error('错误响应:', error.response?.data)

    // 提供更详细的错误提示
    let errorMessage = 'AI内容生成失败'
    if (error.response) {
      const status = error.response.status
      const data = error.response.data

      if (status === 401) {
        errorMessage = '认证失败，请重新登录'
      } else if (status === 403) {
        errorMessage = '没有权限使用AI功能'
      } else if (status === 429) {
        errorMessage = 'AI调用频率过高，请稍后重试'
      } else if (status >= 500) {
        errorMessage = 'AI服务暂时不可用，请稍后重试'
      } else if (data?.message) {
        errorMessage = data.message
      }
    } else if (error.message) {
      errorMessage = error.message
    }

    ElMessage.error(errorMessage)
  } finally {
    aiGenerating.value = false
  }
}

onMounted(async () => {
  // 根据模式加载不同的选项数据
  if (isEdit.value) {
    // 编辑模式：先加载文章数据获取站点ID
    await loadOptions(form.site_id || null)
    await loadCustomFields(form.site_id || null)
    await loadArticle()
    // 根据文章的站点ID加载站点配置
    await loadSiteConfig(form.site_id)
  } else {
    // 创建模式：根据选中的多个站点ID加载
    if (form.site_ids && form.site_ids.length > 0) {
      await loadOptionsForSites(form.site_ids)
	  await loadCustomFields()
      // 加载第一个选择站点的配置
      await loadSiteConfig(form.site_ids[0])
    } else {
      await loadOptions()
      await loadCustomFields()
      // 默认加载站点1的配置（用户未选择站点时）
      await loadSiteConfig(1)
    }    
  }
})

// 监听站点ID变化，重新加载站点配置
watch(() => form.site_id, (newSiteId) => {
  if (newSiteId) {
    loadSiteConfig(newSiteId)
  }
})
</script>

<style scoped>
.article-edit h3 {
  margin: 0;
}

.cover-uploader .cover-image {
  max-width: 240px;
  max-height: 135px;
  width: auto;
  height: auto;
  display: block;
  object-fit: contain;
  border-radius: 4px;
}

.cover-uploader :deep(.el-upload) {
  border: 1px dashed #d9d9d9;
  border-radius: 4px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s;
  width: 240px;
  height: 135px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #fafafa;
}

.cover-uploader :deep(.el-upload:hover) {
  border-color: #409eff;
}

.cover-uploader-icon {
  font-size: 36px;
  color: #8c939d;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
