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

        <el-form-item label="主分类" prop="category_id">
          <el-select v-model="form.category_id" placeholder="请选择主分类" style="width: 100%;">
            <el-option
              v-for="category in categories"
              :key="category.id"
              :label="category.name"
              :value="category.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="副分类" v-if="subCategoryEnabled">
          <el-select v-model="form.sub_categories" placeholder="请选择副分类（可选）" multiple style="width: 100%;">
            <el-option
              v-for="category in categories"
              :key="category.id"
              :label="category.name"
              :value="category.id"
            />
          </el-select>
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            选择的副分类将与主分类一起显示该文章，不选择则只在主分类下显示
          </div>
        </el-form-item>

        <el-form-item label="标签" prop="tags">
          <el-select v-model="form.tags" placeholder="请选择标签" multiple style="width: 100%;">
            <el-option
              v-for="tag in tags"
              :key="tag.id"
              :label="tag.name"
              :value="tag.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="所属专题">
          <el-select v-model="form.topics" placeholder="请选择专题（可选）" multiple style="width: 100%;">
            <el-option
              v-for="topic in topics"
              :key="topic.id"
              :label="topic.name"
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
        <el-divider v-if="customFields.length > 0" content-position="left">
          自定义字段
        </el-divider>
        <CustomFieldRenderer
          v-if="customFields.length > 0"
          :fields="customFields"
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
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getArticleDetail, createArticle, updateArticle } from '@/api/article'
import { getCategoryTree } from '@/api/category'
import { getAllTags } from '@/api/tag'
import { getAllArticleFlags } from '@/api/articleFlag'
import { getAllTopics } from '@/api/topic'
import { getConfig } from '@/api/config'
import { getToken } from '@/utils/auth'
import TinyMCE from '@/components/TinyMCE.vue'
import ArticleVersionList from '@/components/ArticleVersionList.vue'
import CustomFieldRenderer from '@/components/CustomFieldRenderer.vue'
import { getFieldsByModel, getEntityValues, saveEntityValues } from '@/api/customField'

const route = useRoute()
const router = useRouter()

const formRef = ref(null)
const saving = ref(false)
const categories = ref([])
const tags = ref([])
const articleFlags = ref([])
const topics = ref([])
const subCategoryEnabled = ref(false)
const versionHistoryVisible = ref(false)
const customFields = ref([])
const customFieldValues = ref({})

const isEdit = computed(() => !!route.params.id)

const form = reactive({
  title: '',
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
  content: [{ required: true, message: '请输入文章内容', trigger: 'blur' }]
}

// 加载系统配置
const loadSystemConfig = async () => {
  try {
    const res = await getConfig()
    subCategoryEnabled.value = res.data.article_sub_category === 'open'
  } catch (error) {
    console.error('加载系统配置失败', error)
  }
}

// 加载分类、标签、文章属性和专题
const loadOptions = async () => {
  try {
    const [categoryRes, tagRes, flagRes, topicRes] = await Promise.all([
      getCategoryTree(),
      getAllTags(),
      getAllArticleFlags(),
      getAllTopics()
    ])
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
const loadCustomFields = async () => {
  try {
    const res = await getFieldsByModel('article')
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

        const data = {
          ...form,
          status: statusValue,
          tag_ids: form.tags,
          flags: Array.isArray(form.flags) ? form.flags.join('') : (form.flags || '')  // 将数组转换为字符串
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
          ElMessage.success('创建成功')
        }

        // 保存自定义字段值
        if (customFields.value.length > 0 && articleId) {
          try {
            await saveEntityValues('article', articleId, customFieldValues.value)
          } catch (error) {
            console.error('保存自定义字段值失败', error)
          }
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

onMounted(async () => {
  await loadSystemConfig()
  await loadOptions()
  await loadCustomFields()
  if (isEdit.value) {
    await loadArticle()
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
