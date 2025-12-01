<template>
  <div class="page-edit">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>{{ isEdit ? '编辑单页' : '创建单页' }}</span>
          <el-button @click="handleBack">返回列表</el-button>
        </div>
      </template>

      <el-form :model="form" :rules="rules" ref="formRef" label-width="120px" v-loading="loading">
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
              编辑时只能修改当前站点的单页，不影响其他站点
            </template>
            <template v-else>
              创建时可选择多个站点，系统将为每个站点创建独立副本
            </template>
          </div>
        </el-form-item>

        <el-form-item label="标题" prop="title">
          <el-input v-model="form.title" placeholder="请输入标题" maxlength="200" show-word-limit />
        </el-form-item>

        <el-form-item label="URL别名" prop="slug">
          <el-input v-model="form.slug" placeholder="请输入URL别名，如：about" maxlength="200" show-word-limit>
            <template #prepend>/</template>
            <template #append>.html</template>
          </el-input>
          <span class="form-tip">用于生成页面访问路径，建议使用英文</span>
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
          <tinymce-editor v-model="form.content" :height="500" />
        </el-form-item>

        <el-form-item label="模板">
          <el-select v-model="form.template" placeholder="请选择模板" clearable style="width: 300px;">
            <el-option label="默认模板(page)" value="page" />
            <el-option
              v-for="tpl in templates"
              :key="tpl.template_key"
              :label="tpl.name"
              :value="tpl.template_key"
            />
          </el-select>
          <span class="form-tip">选择用于渲染此单页的模板</span>
        </el-form-item>

        <el-form-item label="SEO标题">
          <el-input v-model="form.seo_title" placeholder="留空则使用页面标题" maxlength="100" show-word-limit />
        </el-form-item>

        <el-form-item label="SEO关键词">
          <el-input v-model="form.seo_keywords" type="textarea" :rows="2" placeholder="多个关键词用英文逗号分隔" maxlength="255" show-word-limit />
        </el-form-item>

        <el-form-item label="SEO描述">
          <el-input v-model="form.seo_description" type="textarea" :rows="3" placeholder="页面描述" maxlength="500" show-word-limit />
        </el-form-item>

        <el-form-item label="排序">
          <el-input-number v-model="form.sort" :min="0" :max="9999" />
          <span class="form-tip">数值越大越靠前</span>
        </el-form-item>

        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :label="0">草稿</el-radio>
            <el-radio :label="1">已发布</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="handleSubmit" :loading="submitting">保存</el-button>
          <el-button @click="handleBack">取消</el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { getPageDetail, createPage, updatePage } from '@/api/page'
import { getTemplates } from '@/api/template'
import { getSiteOptions } from '@/api/site'
import { getToken } from '@/utils/auth'
import TinymceEditor from '@/components/TinyMCE.vue'

const route = useRoute()
const router = useRouter()
const formRef = ref(null)
const loading = ref(false)
const submitting = ref(false)
const templates = ref([])
const siteOptions = ref([])

const isEdit = computed(() => !!route.params.id)

const form = reactive({
  site_id: null,
  site_ids: [], // 多站点创建
  title: '',
  slug: '',
  content: '',
  cover_image: '',
  template: 'default',
  seo_title: '',
  seo_keywords: '',
  seo_description: '',
  sort: 0,
  status: 0
})

const rules = {
  site_id: [{ required: true, message: '请选择所属站点', trigger: 'change' }],
  site_ids: [{ required: true, type: 'array', min: 1, message: '请至少选择一个站点', trigger: 'change' }],
  title: [
    { required: true, message: '请输入标题', trigger: 'blur' }
  ],
  slug: [
    { required: true, message: '请输入URL别名', trigger: 'blur' },
    { pattern: /^[a-z0-9-]+$/, message: 'URL别名只能包含小写字母、数字和连字符', trigger: 'blur' }
  ],
  content: [
    { required: true, message: '请输入内容', trigger: 'blur' }
  ]
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

// 加载数据
const loadData = async () => {
  if (!isEdit.value) return

  loading.value = true
  try {
    const res = await getPageDetail(route.params.id)
    Object.assign(form, res.data)
  } catch (error) {
    ElMessage.error('加载数据失败')
  } finally {
    loading.value = false
  }
}

// 提交
const handleSubmit = async () => {
  if (!formRef.value) return

  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return

  submitting.value = true
  try {
    const submitData = { ...form }

    // 区分创建和编辑模式的站点字段
    if (isEdit.value) {
      // 编辑模式：只保留 site_id，移除 site_ids
      delete submitData.site_ids
    } else {
      // 创建模式：只保留 site_ids，移除 site_id
      delete submitData.site_id
    }

    if (isEdit.value) {
      await updatePage(route.params.id, submitData)
      ElMessage.success('保存成功')
    } else {
      await createPage(submitData)
      ElMessage.success('创建成功')
    }
    router.push('/pages')
  } catch (error) {
    ElMessage.error(error.response?.data?.message || '保存失败')
  } finally {
    submitting.value = false
  }
}

// 全选站点
const selectAllSites = () => {
  form.site_ids = siteOptions.value.map(site => site.id)
}

// 取消全选站点
const deselectAllSites = () => {
  form.site_ids = []
}

// 获取站点选项
const fetchSiteOptions = async () => {
  try {
    const res = await getSiteOptions()
    siteOptions.value = res.data || []
  } catch (error) {
    console.error('获取站点列表失败:', error)
  }
}

// 返回
const handleBack = () => {
  router.push('/pages')
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

onMounted(() => {
  fetchSiteOptions()
  loadData()
  fetchTemplates()
})
</script>

<style scoped>
.page-edit {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-tip {
  font-size: 12px;
  color: #999;
  margin-left: 10px;
}

.image-preview {
  margin-top: 10px;
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
