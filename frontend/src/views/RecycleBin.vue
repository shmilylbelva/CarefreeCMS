<template>
  <div class="recycle-bin">
    <el-card>
      <template #header>
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <h3>回收站</h3>
          <div>
            <el-button
              type="danger"
              :icon="Delete"
              @click="handleClearAll"
              :disabled="statistics?.total_count === 0"
            >
              清空回收站
            </el-button>
          </div>
        </div>
      </template>

      <!-- 提示信息 -->
      <el-alert
        type="info"
        :closable="false"
        style="margin-bottom: 20px;"
        title="回收站配置说明"
      >
        <template #default>
          <div style="line-height: 1.8;">
            <p>回收站功能现已调整为站点级别配置，每个站点可以独立设置。</p>
            <p>
              如需配置回收站开关，请前往：
              <router-link to="/site/list" style="color: #409EFF; text-decoration: underline; font-weight: bold;">
                系统管理 > 站点管理 > 编辑站点 > 核心设置
              </router-link>
            </p>
            <p style="margin-top: 5px; color: #909399; font-size: 13px;">
              • 开启回收站：删除的内容将进入回收站，可恢复<br>
              • 关闭回收站：删除的内容将直接永久删除，无法恢复
            </p>
          </div>
        </template>
      </el-alert>

      <!-- 统计信息 -->
      <el-alert
        v-if="statistics"
        :title="`回收站共有 ${statistics.total_count} 个项目`"
        type="info"
        :closable="false"
        style="margin-bottom: 20px;"
      >
        <template #default>
          <div style="margin-top: 10px;">
            <el-tag type="info" style="margin-right: 10px;">文章: {{ statistics.article_count }}</el-tag>
            <el-tag type="success" style="margin-right: 10px;">分类: {{ statistics.category_count }}</el-tag>
            <el-tag type="warning" style="margin-right: 10px;">标签: {{ statistics.tag_count }}</el-tag>
            <el-tag type="danger" style="margin-right: 10px;">单页: {{ statistics.page_count }}</el-tag>
            <el-tag>媒体: {{ statistics.media_count }}</el-tag>
          </div>
        </template>
      </el-alert>

      <!-- 筛选栏 -->
      <el-form :inline="true" :model="searchForm" style="margin-bottom: 20px;">
        <el-form-item label="类型">
          <el-select v-model="searchForm.type" placeholder="全部类型" @change="handleSearch" style="width: 150px;">
            <el-option label="全部" value="all" />
            <el-option label="文章" value="article" />
            <el-option label="分类" value="category" />
            <el-option label="标签" value="tag" />
            <el-option label="单页" value="page" />
            <el-option label="媒体" value="media" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input
            v-model="searchForm.keyword"
            placeholder="请输入关键词"
            clearable
            @clear="handleSearch"
            @keyup.enter="handleSearch"
            style="width: 200px;"
          />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 批量操作 -->
      <div v-if="selectedItems.length > 0" style="margin-bottom: 10px;">
        <el-button type="success" size="small" @click="handleBatchRestore">
          批量恢复 ({{ selectedItems.length }})
        </el-button>
        <el-button type="danger" size="small" @click="handleBatchDestroy">
          批量彻底删除 ({{ selectedItems.length }})
        </el-button>
      </div>

      <!-- 列表 -->
      <el-table
        v-if="recycleBinEnabled"
        :data="list"
        v-loading="loading"
        @selection-change="handleSelectionChange"
        stripe
        style="width: 100%"
      >
        <el-table-column type="selection" width="55" />

        <el-table-column prop="item_type_text" label="类型" width="100" align="center">
          <template #default="scope">
            <el-tag
              :type="getTypeTagColor(scope.row.item_type)"
              size="small"
            >
              {{ scope.row.item_type_text }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column prop="item_title" label="标题" min-width="300" show-overflow-tooltip />

        <el-table-column prop="deleted_at" label="删除时间" width="180" align="center" />

        <el-table-column label="操作" width="200" align="center" fixed="right">
          <template #default="scope">
            <el-button
              size="small"
              type="success"
              @click="handleRestore(scope.row)"
            >
              恢复
            </el-button>
            <el-button
              size="small"
              type="danger"
              @click="handleDestroy(scope.row)"
            >
              彻底删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div v-if="recycleBinEnabled" style="margin-top: 20px; text-align: center;">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :total="total"
          :page-sizes="[10, 20, 50, 100]"
          layout="total, sizes, prev, pager, next, jumper"
          @current-change="handlePageChange"
          @size-change="handleSizeChange"
        />
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Delete } from '@element-plus/icons-vue'
import {
  getRecycleBinList,
  getRecycleBinStatistics,
  restoreItem,
  batchRestore,
  destroyItem,
  batchDestroy,
  clearRecycleBin
} from '@/api/recycleBin'

const loading = ref(false)
const list = ref([])
const statistics = ref({
  article_count: 0,
  category_count: 0,
  tag_count: 0,
  page_count: 0,
  media_count: 0,
  total_count: 0
})
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const selectedItems = ref([])

const searchForm = reactive({
  type: 'all',
  keyword: ''
})

// 回收站配置已迁移到站点级别，此处不再需要全局检查
// 后端会根据每个站点的配置决定是否进入回收站

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    const params = {
      type: searchForm.type,
      keyword: searchForm.keyword,
      page: currentPage.value,
      page_size: pageSize.value
    }

    const [listRes, statsRes] = await Promise.all([
      getRecycleBinList(params),
      getRecycleBinStatistics()
    ])

    list.value = listRes.data.list || []
    total.value = listRes.data.pagination?.total || 0
    statistics.value = statsRes.data
  } catch (error) {
    ElMessage.error('加载数据失败')
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  currentPage.value = 1
  loadData()
}

// 重置
const handleReset = () => {
  searchForm.type = 'all'
  searchForm.keyword = ''
  currentPage.value = 1
  loadData()
}

// 恢复单个项目
const handleRestore = (row) => {
  ElMessageBox.confirm(
    `确定要恢复该${row.item_type_text}吗？`,
    '确认恢复',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      await restoreItem({
        type: row.item_type,
        id: row.id
      })
      ElMessage.success('恢复成功')
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '恢复失败')
    }
  })
}

// 批量恢复
const handleBatchRestore = () => {
  if (selectedItems.value.length === 0) {
    ElMessage.warning('请选择要恢复的项目')
    return
  }

  ElMessageBox.confirm(
    `确定要恢复选中的 ${selectedItems.value.length} 个项目吗？`,
    '批量恢复',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      const items = selectedItems.value.map(item => ({
        type: item.item_type,
        id: item.id
      }))
      await batchRestore({ items })
      ElMessage.success('批量恢复成功')
      selectedItems.value = []
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '批量恢复失败')
    }
  })
}

// 彻底删除单个项目
const handleDestroy = (row) => {
  ElMessageBox.confirm(
    `确定要彻底删除该${row.item_type_text}吗？此操作不可恢复！`,
    '确认删除',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'error'
    }
  ).then(async () => {
    try {
      await destroyItem(row.item_type, row.id)
      ElMessage.success('彻底删除成功')
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  })
}

// 批量彻底删除
const handleBatchDestroy = () => {
  if (selectedItems.value.length === 0) {
    ElMessage.warning('请选择要删除的项目')
    return
  }

  ElMessageBox.confirm(
    `确定要彻底删除选中的 ${selectedItems.value.length} 个项目吗？此操作不可恢复！`,
    '批量删除',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'error'
    }
  ).then(async () => {
    try {
      const items = selectedItems.value.map(item => ({
        type: item.item_type,
        id: item.id
      }))
      await batchDestroy({ items })
      ElMessage.success('批量删除成功')
      selectedItems.value = []
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '批量删除失败')
    }
  })
}

// 清空回收站
const handleClearAll = () => {
  ElMessageBox.confirm(
    `确定要清空回收站吗？这将彻底删除所有 ${statistics.value.total_count} 个项目，此操作不可恢复！`,
    '清空回收站',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'error'
    }
  ).then(async () => {
    try {
      await clearRecycleBin({ type: searchForm.type })
      ElMessage.success('回收站已清空')
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '清空失败')
    }
  })
}

// 选择变化
const handleSelectionChange = (selection) => {
  selectedItems.value = selection
}

// 分页
const handlePageChange = (page) => {
  currentPage.value = page
  loadData()
}

const handleSizeChange = (size) => {
  pageSize.value = size
  currentPage.value = 1
  loadData()
}

// 获取类型标签颜色
const getTypeTagColor = (type) => {
  const colors = {
    article: 'primary',
    category: 'success',
    tag: 'warning',
    page: 'danger',
    media: 'info'
  }
  return colors[type] || 'info'
}

onMounted(() => {
  loadData()
})
</script>

<style scoped>
.recycle-bin h3 {
  margin: 0;
}
</style>
