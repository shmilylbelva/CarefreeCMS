<template>
  <div class="topic-list">
    <el-card>
      <!-- 搜索栏 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="关键词">
          <el-input
            v-model="searchForm.keyword"
            placeholder="请输入专题名称"
            clearable
            @keyup.enter="handleSearch"
          />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item label="是否推荐">
          <el-select v-model="searchForm.is_recommended" placeholder="请选择" clearable>
            <el-option label="是" :value="1" />
            <el-option label="否" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
          <el-button type="success" @click="handleCreate">新建专题</el-button>
        </el-form-item>
      </el-form>

      <!-- 数据表格 -->
      <el-table :data="list" border style="width: 100%">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column label="封面图" width="100">
          <template #default="{ row }">
            <img
              v-if="row.cover_image"
              :src="row.cover_image"
              style="width: 60px; height: 40px; object-fit: cover;"
            />
            <span v-else style="color: #999;">无</span>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="专题名称" min-width="150" />
        <el-table-column prop="slug" label="URL别名" min-width="120" />
        <el-table-column prop="article_count" label="文章数" width="80" align="center" />
        <el-table-column prop="view_count" label="浏览数" width="80" align="center" />
        <el-table-column label="推荐" width="80" align="center">
          <template #default="{ row }">
            <el-tag v-if="row.is_recommended" type="success" size="small">是</el-tag>
            <el-tag v-else type="info" size="small">否</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-tag v-if="row.status === 1" type="success" size="small">启用</el-tag>
            <el-tag v-else type="danger" size="small">禁用</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="sort" label="排序" width="80" align="center" />
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button size="small" type="primary" @click="handleEdit(row)">编辑</el-button>
            <el-button size="small" type="success" @click="handleManageArticles(row)">管理文章</el-button>
            <el-button size="small" type="danger" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :page-sizes="[10, 20, 50, 100]"
        :total="pagination.total"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="handleSizeChange"
        @current-change="handlePageChange"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 新建/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="800px"
      @close="handleDialogClose"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
        <el-form-item label="专题名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入专题名称" />
        </el-form-item>

        <el-form-item label="URL别名" prop="slug">
          <el-input v-model="form.slug" placeholder="例如：hot-recommend" />
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            URL别名用于生成专题页面的访问地址，只能包含小写字母、数字和连字符
          </div>
        </el-form-item>

        <el-form-item label="专题描述">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请输入专题描述"
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
        </el-form-item>

        <el-form-item label="专题模板">
          <el-input v-model="form.template" placeholder="例如：topic_default" />
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            模板文件名，不含扩展名
          </div>
        </el-form-item>

        <el-form-item label="SEO标题">
          <el-input v-model="form.seo_title" placeholder="请输入SEO标题" />
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

        <el-form-item label="是否推荐">
          <el-switch v-model="form.is_recommended" :active-value="1" :inactive-value="0" />
        </el-form-item>

        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :label="1">启用</el-radio>
            <el-radio :label="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="排序">
          <el-input-number v-model="form.sort" :min="0" />
          <div style="margin-top: 5px; color: #909399; font-size: 12px;">
            数字越小越靠前
          </div>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">确定</el-button>
      </template>
    </el-dialog>

    <!-- 管理文章对话框 -->
    <TopicArticleManager
      v-model="articleManagerVisible"
      :topic-id="currentTopicId"
      :topic-name="currentTopicName"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import {
  getTopicList,
  getTopicDetail,
  createTopic,
  updateTopic,
  deleteTopic
} from '@/api/topic'
import { getToken } from '@/utils/auth'
import TopicArticleManager from './ArticleManager.vue'

const searchForm = reactive({
  keyword: '',
  status: '',
  is_recommended: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})

const list = ref([])
const loading = ref(false)
const dialogVisible = ref(false)
const dialogTitle = ref('')
const submitting = ref(false)
const formRef = ref(null)
const isEdit = ref(false)
const editId = ref(0)
const articleManagerVisible = ref(false)
const currentTopicId = ref(0)
const currentTopicName = ref('')

const form = reactive({
  name: '',
  slug: '',
  description: '',
  cover_image: '',
  template: 'topic_default',
  seo_title: '',
  seo_keywords: '',
  seo_description: '',
  is_recommended: 0,
  status: 1,
  sort: 0
})

const rules = {
  name: [{ required: true, message: '请输入专题名称', trigger: 'blur' }],
  slug: [
    { required: true, message: '请输入URL别名', trigger: 'blur' },
    { pattern: /^[a-z0-9\-]+$/, message: 'URL别名只能包含小写字母、数字和连字符', trigger: 'blur' }
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

const coverImageUrl = computed(() => {
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

// 上传成功
const handleCoverSuccess = (response) => {
  if (response.code === 200) {
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
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    }
    const res = await getTopicList(params)
    list.value = res.data.list
    pagination.total = res.data.total
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  loadData()
}

// 重置
const handleReset = () => {
  searchForm.keyword = ''
  searchForm.status = ''
  searchForm.is_recommended = ''
  pagination.page = 1
  loadData()
}

// 分页变化
const handleSizeChange = () => {
  loadData()
}

const handlePageChange = () => {
  loadData()
}

// 新建
const handleCreate = () => {
  isEdit.value = false
  dialogTitle.value = '新建专题'
  resetForm()
  dialogVisible.value = true
}

// 编辑
const handleEdit = async (row) => {
  isEdit.value = true
  editId.value = row.id
  dialogTitle.value = '编辑专题'

  try {
    const res = await getTopicDetail(row.id)
    Object.assign(form, res.data)
    dialogVisible.value = true
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  }
}

// 删除
const handleDelete = (row) => {
  ElMessageBox.confirm('确定要删除该专题吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      await deleteTopic(row.id)
      ElMessage.success('删除成功')
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  }).catch(() => {})
}

// 管理文章
const handleManageArticles = (row) => {
  currentTopicId.value = row.id
  currentTopicName.value = row.name
  articleManagerVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true
      try {
        if (isEdit.value) {
          await updateTopic(editId.value, form)
          ElMessage.success('更新成功')
        } else {
          await createTopic(form)
          ElMessage.success('创建成功')
        }
        dialogVisible.value = false
        loadData()
      } catch (error) {
        ElMessage.error(error.message || '保存失败')
      } finally {
        submitting.value = false
      }
    }
  })
}

// 重置表单
const resetForm = () => {
  form.name = ''
  form.slug = ''
  form.description = ''
  form.cover_image = ''
  form.template = 'topic_default'
  form.seo_title = ''
  form.seo_keywords = ''
  form.seo_description = ''
  form.is_recommended = 0
  form.status = 1
  form.sort = 0
}

// 对话框关闭
const handleDialogClose = () => {
  formRef.value?.clearValidate()
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.topic-list {
  padding: 20px;
}

.search-form {
  margin-bottom: 20px;
}

.cover-uploader .cover-image {
  max-width: 200px;
  max-height: 120px;
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
  width: 200px;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #fafafa;
}

.cover-uploader :deep(.el-upload:hover) {
  border-color: #409eff;
}

.cover-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
