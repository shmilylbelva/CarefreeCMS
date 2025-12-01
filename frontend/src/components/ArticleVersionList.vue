<template>
  <div class="article-version-list">
    <el-dialog
      v-model="visible"
      title="文章版本历史"
      width="80%"
      :before-close="handleClose"
    >
      <!-- 统计信息 -->
      <el-alert
        v-if="statistics"
        :title="`当前文章共有 ${statistics.version_count} 个版本`"
        type="info"
        :closable="false"
        style="margin-bottom: 20px;"
      >
        <template #default>
          <div style="margin-top: 10px;">
            <p>最新版本：V{{ statistics.latest_version?.version_number }} - {{ statistics.latest_version?.create_time }}</p>
            <p>首个版本：V{{ statistics.first_version?.version_number }} - {{ statistics.first_version?.create_time }}</p>
          </div>
        </template>
      </el-alert>

      <!-- 版本列表 -->
      <el-table
        :data="versions"
        v-loading="loading"
        stripe
        style="width: 100%"
      >
        <el-table-column prop="version_number" label="版本号" width="80" align="center">
          <template #default="scope">
            <el-tag type="success">V{{ scope.row.version_number }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column prop="title" label="标题" min-width="200" show-overflow-tooltip />

        <el-table-column prop="change_log" label="修改说明" min-width="200" show-overflow-tooltip>
          <template #default="scope">
            <span>{{ scope.row.change_log || '-' }}</span>
          </template>
        </el-table-column>

        <el-table-column prop="creator.username" label="修改人" width="120" align="center">
          <template #default="scope">
            <el-tag size="small">{{ scope.row.creator?.username || '-' }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column prop="create_time" label="修改时间" width="180" align="center" />

        <el-table-column label="操作" width="280" align="center" fixed="right">
          <template #default="scope">
            <el-button size="small" type="primary" @click="handleView(scope.row)">
              查看
            </el-button>
            <el-button size="small" type="info" @click="handleCompare(scope.row)">
              对比
            </el-button>
            <el-button size="small" type="warning" @click="handleRollback(scope.row)">
              回滚
            </el-button>
            <el-button size="small" type="danger" @click="handleDelete(scope.row)">
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <div style="margin-top: 20px; text-align: center;">
        <el-pagination
          v-model:current-page="currentPage"
          v-model:page-size="pageSize"
          :total="total"
          :page-sizes="[10, 20, 50]"
          layout="total, sizes, prev, pager, next, jumper"
          @current-change="handlePageChange"
          @size-change="handleSizeChange"
        />
      </div>

      <template #footer>
        <el-button @click="handleClose">关闭</el-button>
      </template>
    </el-dialog>

    <!-- 版本详情对话框 -->
    <el-dialog
      v-model="detailVisible"
      title="版本详情"
      width="70%"
    >
      <el-descriptions v-if="currentVersion" :column="2" border>
        <el-descriptions-item label="版本号">
          V{{ currentVersion.version_number }}
        </el-descriptions-item>
        <el-descriptions-item label="创建时间">
          {{ currentVersion.create_time }}
        </el-descriptions-item>
        <el-descriptions-item label="标题" :span="2">
          {{ currentVersion.title }}
        </el-descriptions-item>
        <el-descriptions-item label="摘要" :span="2">
          {{ currentVersion.summary || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="修改说明" :span="2">
          {{ currentVersion.change_log || '-' }}
        </el-descriptions-item>
        <el-descriptions-item label="内容" :span="2">
          <div v-safe-html="currentVersion.content" style="max-height: 400px; overflow-y: auto;"></div>
        </el-descriptions-item>
      </el-descriptions>
    </el-dialog>

    <!-- 版本对比对话框 -->
    <ArticleVersionCompare
      v-if="compareVisible"
      v-model="compareVisible"
      :old-version-id="compareOldVersionId"
      :new-version-id="compareNewVersionId"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getArticleVersions,
  getVersionDetail,
  getVersionStatistics,
  rollbackToVersion,
  deleteVersion
} from '@/api/articleVersion'
import ArticleVersionCompare from './ArticleVersionCompare.vue'
import { vSafeHtml } from '@/utils/sanitize'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  articleId: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'rollback'])

const visible = ref(false)
const loading = ref(false)
const versions = ref([])
const statistics = ref(null)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

// 版本详情
const detailVisible = ref(false)
const currentVersion = ref(null)

// 版本对比
const compareVisible = ref(false)
const compareOldVersionId = ref(null)
const compareNewVersionId = ref(null)

// 监听外部值变化
watch(() => props.modelValue, (val) => {
  visible.value = val
  if (val) {
    loadData()
  }
})

watch(visible, (val) => {
  emit('update:modelValue', val)
})

// 加载数据
const loadData = async () => {
  loading.value = true
  try {
    // 加载版本列表
    const res = await getArticleVersions(props.articleId, {
      page: currentPage.value,
      page_size: pageSize.value
    })
    versions.value = res.data.list || []
    total.value = res.data.pagination?.total || 0

    // 加载统计信息
    const statsRes = await getVersionStatistics(props.articleId)
    statistics.value = statsRes.data
  } catch (error) {
    ElMessage.error('加载版本列表失败')
  } finally {
    loading.value = false
  }
}

// 查看版本详情
const handleView = async (row) => {
  try {
    const res = await getVersionDetail(row.id)
    currentVersion.value = res.data
    detailVisible.value = true
  } catch (error) {
    ElMessage.error('加载版本详情失败')
  }
}

// 对比版本
const handleCompare = (row) => {
  // 默认与上一个版本对比
  const currentIndex = versions.value.findIndex(v => v.id === row.id)
  if (currentIndex < versions.value.length - 1) {
    compareOldVersionId.value = versions.value[currentIndex + 1].id
    compareNewVersionId.value = row.id
    compareVisible.value = true
  } else {
    ElMessage.warning('这是最早的版本，无法对比')
  }
}

// 回滚版本
const handleRollback = (row) => {
  ElMessageBox.confirm(
    `确定要回滚到版本 V${row.version_number} 吗？当前内容将被覆盖，但会自动备份为新版本。`,
    '确认回滚',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      await rollbackToVersion(row.id)
      ElMessage.success('版本回滚成功')
      emit('rollback')
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '回滚失败')
    }
  })
}

// 删除版本
const handleDelete = (row) => {
  ElMessageBox.confirm(
    `确定要删除版本 V${row.version_number} 吗？此操作不可恢复。`,
    '确认删除',
    {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    }
  ).then(async () => {
    try {
      await deleteVersion(row.id)
      ElMessage.success('版本删除成功')
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  })
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

// 关闭对话框
const handleClose = () => {
  visible.value = false
}
</script>

<style scoped>
.article-version-list :deep(.el-dialog__body) {
  padding-top: 10px;
}
</style>
