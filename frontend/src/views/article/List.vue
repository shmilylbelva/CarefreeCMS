<template>
  <div class="article-list">
    <el-card>
      <template #header>
        <div class="header-actions">
          <h3>文章列表</h3>
          <div>
            <el-button @click="showAdvancedSearch = true">
              <el-icon><search /></el-icon>
              高级搜索
            </el-button>
            <el-button type="primary" @click="$router.push('/articles/create')">
              <el-icon><plus /></el-icon>
              创建文章
            </el-button>
          </div>
        </div>
      </template>

      <!-- 当前搜索条件显示 -->
      <el-alert
        v-if="currentSearchType"
        :title="getSearchTypeText()"
        type="info"
        closable
        @close="clearAdvancedSearch"
        style="margin-bottom: 20px;"
      >
        <template #default>
          <div v-if="currentSearchType === 'fulltext'">
            关键词: <strong>{{ currentSearchParams.keyword }}</strong> |
            模式: <strong>{{ getSearchModeText(currentSearchParams.mode) }}</strong>
          </div>
          <div v-else>
            已应用 {{ countSearchConditions() }} 个搜索条件
          </div>
        </template>
      </el-alert>

      <!-- 搜索表单 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="标题">
          <el-input v-model="searchForm.title" placeholder="请输入文章标题" clearable style="width: 200px;" />
        </el-form-item>
        <el-form-item label="站点">
          <el-select v-model="searchForm.site_id" placeholder="全部站点" clearable style="width: 150px;">
            <el-option label="全部站点" value="" />
            <el-option
              v-for="site in siteOptions"
              :key="site.id"
              :label="site.name"
              :value="site.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="分类">
          <el-select v-model="searchForm.category_id" placeholder="请选择分类" clearable style="width: 150px;">
            <el-option
              v-for="category in categories"
              :key="category.id"
              :label="category.name"
              :value="category.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="作者">
          <el-select v-model="searchForm.user_id" placeholder="请选择作者" clearable filterable style="width: 150px;">
            <el-option
              v-for="user in users"
              :key="user.id"
              :label="user.real_name || user.username"
              :value="user.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable style="width: 120px;">
            <el-option label="草稿" :value="0" />
            <el-option label="已发布" :value="1" />
            <el-option label="待审核" :value="2" />
            <el-option label="已下线" :value="3" />
          </el-select>
        </el-form-item>
        <el-form-item label="发布时间">
          <el-date-picker
            v-model="dateRange"
            type="daterange"
            range-separator="-"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
            style="width: 240px;"
            @change="handleDateChange"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 文章表格 -->
      <el-table :data="articleList" v-loading="loading" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column label="标题" min-width="200">
          <template #default="{ row }">
            <div v-if="row.highlighted_title" v-safe-highlight="row.highlighted_title"></div>
            <div v-else>{{ row.title }}</div>
            <div v-if="row.highlighted_summary" class="article-summary" v-safe-highlight="row.highlighted_summary"></div>
          </template>
        </el-table-column>
        <el-table-column label="分类" width="120">
          <template #default="{ row }">
            {{ row.category?.name || '未分类' }}
          </template>
        </el-table-column>
        <el-table-column label="所属站点" width="120">
          <template #default="{ row }">
            <el-tag size="small">{{ row.site?.name || '-' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="作者" width="120">
          <template #default="{ row }">
            {{ row.user?.real_name || row.user?.username || '未知' }}
          </template>
        </el-table-column>
        <el-table-column prop="status" label="状态" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.status === 1" type="success">已发布</el-tag>
            <el-tag v-else-if="row.status === 0" type="info">草稿</el-tag>
            <el-tag v-else-if="row.status === 2" type="warning">待审核</el-tag>
            <el-tag v-else-if="row.status === 3" type="danger">已下线</el-tag>
            <el-tag v-else type="info">未知</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="view_count" label="浏览量" width="100" />
        <el-table-column prop="create_time" label="创建时间" width="180" />
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button size="small" @click="handleEdit(row.id)">编辑</el-button>
            <el-button
              v-if="row.status === 0 || row.status === 2 || row.status === 3"
              size="small"
              type="success"
              @click="handlePublish(row.id)"
            >
              {{ row.status === 3 ? '重新上线' : '发布' }}
            </el-button>
            <el-button
              v-if="row.status === 1"
              size="small"
              type="warning"
              @click="handleOffline(row.id)"
            >
              下线
            </el-button>
            <el-button size="small" type="danger" @click="handleDelete(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.pageSize"
        :total="pagination.total"
        :page-sizes="[10, 20, 50, 100]"
        layout="total, sizes, prev, pager, next, jumper"
        @size-change="fetchArticles"
        @current-change="fetchArticles"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 高级搜索对话框 -->
    <AdvancedSearch
      v-model="showAdvancedSearch"
      :categories="categories"
      :tags="tags"
      @search="handleAdvancedSearch"
    />

    <!-- 删除确认对话框 -->
    <DeleteConfirmDialog
      v-model="showDeleteDialog"
      :article-id="deleteArticleId"
      @confirm="handleConfirmDelete"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Search, Plus } from '@element-plus/icons-vue'
import { getArticleList, deleteArticle, publishArticle, offlineArticle, fullTextSearch, advancedSearch } from '@/api/article'
import DeleteConfirmDialog from '@/components/Article/DeleteConfirmDialog.vue'
import { getCategoryTree } from '@/api/category'
import { getUserList } from '@/api/user'
import { getAllTags } from '@/api/tag'
import { getSiteOptions } from '@/api/site'
import AdvancedSearch from '@/components/AdvancedSearch.vue'
import { vSafeHighlight } from '@/utils/sanitize'

const router = useRouter()
const loading = ref(false)
const articleList = ref([])
const siteOptions = ref([])
const categories = ref([])
const users = ref([])
const tags = ref([])
const dateRange = ref(null)
const showAdvancedSearch = ref(false)
const showDeleteDialog = ref(false)
const deleteArticleId = ref(null)

// 当前搜索类型和参数
const currentSearchType = ref('') // 'fulltext' 或 'advanced'
const currentSearchParams = ref({})

const searchForm = reactive({
  title: '',
  site_id: '',
  category_id: '',
  user_id: '',
  start_time: '',
  end_time: '',
  status: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})

// 获取文章列表
const fetchArticles = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,  // 使用下划线命名以匹配后端
      ...searchForm
    }
    const res = await getArticleList(params)
    articleList.value = res.data.list || []
    pagination.total = res.data.total || 0
  } catch (error) {
    ElMessage.error('获取文章列表失败')
  } finally {
    loading.value = false
  }
}

// 获取站点列表
const fetchSiteOptions = async () => {
  try {
    const res = await getSiteOptions()
    siteOptions.value = res.data || []
  } catch (error) {
    console.error('获取站点列表失败', error)
  }
}

// 获取分类列表
const fetchCategories = async () => {
  try {
    const res = await getCategoryTree()
    categories.value = res.data || []
  } catch (error) {
    console.error('获取分类列表失败', error)
  }
}

// 获取用户列表
const fetchUsers = async () => {
  try {
    const res = await getUserList({ page: 1, page_size: 1000 })
    users.value = res.data.list || []
  } catch (error) {
    console.error('获取用户列表失败', error)
  }
}

// 获取标签列表
const fetchTags = async () => {
  try {
    const res = await getAllTags()
    tags.value = res.data || []
  } catch (error) {
    console.error('获取标签列表失败', error)
  }
}

// 处理日期范围变化
const handleDateChange = (dates) => {
  if (dates && dates.length === 2) {
    searchForm.start_time = dates[0].toISOString().split('T')[0]
    searchForm.end_time = dates[1].toISOString().split('T')[0]
  } else {
    searchForm.start_time = ''
    searchForm.end_time = ''
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  fetchArticles()
}

// 重置
const handleReset = () => {
  searchForm.title = ''
  searchForm.site_id = ''
  searchForm.category_id = ''
  searchForm.user_id = ''
  searchForm.start_time = ''
  searchForm.end_time = ''
  searchForm.status = ''
  dateRange.value = null
  pagination.page = 1
  fetchArticles()
}

// 编辑
const handleEdit = (id) => {
  router.push(`/articles/edit/${id}`)
}

// 发布
const handlePublish = async (id) => {
  try {
    await publishArticle(id)
    ElMessage.success('发布成功')
    fetchArticles()
  } catch (error) {
    ElMessage.error('发布失败')
  }
}

// 下线
const handleOffline = async (id) => {
  try {
    await offlineArticle(id)
    ElMessage.success('下线成功')
    fetchArticles()
  } catch (error) {
    ElMessage.error('下线失败')
  }
}

// 删除
const handleDelete = (id) => {
  deleteArticleId.value = id
  showDeleteDialog.value = true
}

// 确认删除
const handleConfirmDelete = async ({ deleteMedia, mediaIds }) => {
  try {
    await deleteArticle(deleteArticleId.value, {
      delete_media: deleteMedia,
      media_ids: mediaIds
    })
    ElMessage.success('删除成功')
    showDeleteDialog.value = false
    fetchArticles()
  } catch (error) {
    ElMessage.error('删除失败')
  }
}

// 处理高级搜索
const handleAdvancedSearch = async ({ type, params }) => {
  loading.value = true
  pagination.page = 1

  try {
    let res
    if (type === 'fulltext') {
      // 全文搜索
      params.page = pagination.page
      params.page_size = pagination.pageSize  // 使用下划线命名
      res = await fullTextSearch(params)
    } else {
      // 高级搜索
      params.page = pagination.page
      params.page_size = pagination.pageSize  // 使用下划线命名
      res = await advancedSearch(params)
    }

    articleList.value = res.data.list || []
    pagination.total = res.data.total || 0

    // 保存当前搜索类型和参数
    currentSearchType.value = type
    currentSearchParams.value = params

    ElMessage.success(`找到 ${pagination.total} 篇文章`)
  } catch (error) {
    ElMessage.error('搜索失败')
    console.error('搜索失败', error)
  } finally {
    loading.value = false
  }
}

// 清除高级搜索
const clearAdvancedSearch = () => {
  currentSearchType.value = ''
  currentSearchParams.value = {}
  handleReset()
}

// 获取搜索类型文本
const getSearchTypeText = () => {
  if (currentSearchType.value === 'fulltext') {
    return '全文搜索结果'
  } else if (currentSearchType.value === 'advanced') {
    return '高级搜索结果'
  }
  return ''
}

// 获取搜索模式文本
const getSearchModeText = (mode) => {
  const modes = {
    natural: '自然语言模式',
    boolean: '布尔模式',
    query_expansion: '查询扩展模式'
  }
  return modes[mode] || mode
}

// 统计搜索条件数量
const countSearchConditions = () => {
  if (!currentSearchParams.value) return 0

  let count = 0
  const excludeKeys = ['page', 'page_size', 'sort_by', 'sort_order']

  for (const [key, value] of Object.entries(currentSearchParams.value)) {
    if (excludeKeys.includes(key)) continue
    if (value !== '' && value !== null && value !== undefined) {
      if (Array.isArray(value) && value.length > 0) {
        count++
      } else if (!Array.isArray(value)) {
        count++
      }
    }
  }

  return count
}

onMounted(() => {
  fetchSiteOptions()
  fetchCategories()
  fetchUsers()
  fetchTags()
  fetchArticles()
})
</script>

<style scoped>
.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions h3 {
  margin: 0;
}

.search-form {
  margin-bottom: 20px;
}

.article-summary {
  margin-top: 8px;
  font-size: 12px;
  color: #666;
  line-height: 1.5;
}

/* 高亮关键词样式 */
:deep(mark) {
  background-color: #ffeb3b;
  color: #000;
  padding: 2px 4px;
  border-radius: 2px;
  font-weight: 500;
}
</style>
