<template>
  <div class="database-management">
    <el-card class="info-card" shadow="never">
      <template #header>
        <div class="card-header">
          <span>数据库信息</span>
          <el-button type="primary" @click="loadDatabaseInfo" :icon="Refresh" circle />
        </div>
      </template>
      <el-descriptions :column="3" border>
        <el-descriptions-item label="数据库名">{{ dbInfo.database }}</el-descriptions-item>
        <el-descriptions-item label="主机">{{ dbInfo.host }}</el-descriptions-item>
        <el-descriptions-item label="表数量">{{ dbInfo.tables_count }}</el-descriptions-item>
        <el-descriptions-item label="数据大小">{{ dbInfo.data_size_format }}</el-descriptions-item>
        <el-descriptions-item label="索引大小">{{ dbInfo.index_size_format }}</el-descriptions-item>
        <el-descriptions-item label="总大小">{{ dbInfo.total_size_format }}</el-descriptions-item>
      </el-descriptions>
    </el-card>

    <el-tabs v-model="activeTab" class="main-tabs">
      <!-- 备份管理 -->
      <el-tab-pane label="备份管理" name="backup">
        <el-card shadow="never">
          <template #header>
            <div class="card-header">
              <span>备份操作</span>
              <div>
                <el-button type="primary" @click="handleBackup">完整备份</el-button>
                <el-button type="success" @click="showTableSelect">选择表备份</el-button>
              </div>
            </div>
          </template>

          <el-table :data="backupList" border stripe>
            <el-table-column prop="filename" label="文件名" min-width="200" />
            <el-table-column prop="filesize_format" label="文件大小" width="120" />
            <el-table-column prop="tables_count" label="表数量" width="100" />
            <el-table-column prop="backup_type" label="类型" width="100">
              <template #default="{ row }">
                <el-tag :type="row.backup_type === 'full' ? 'success' : 'info'">
                  {{ row.backup_type === 'full' ? '完整备份' : '指定表' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="description" label="描述" min-width="150" />
            <el-table-column prop="create_time" label="备份时间" width="180" />
            <el-table-column label="操作" width="200" fixed="right">
              <template #default="{ row }">
                <el-button type="primary" size="small" @click="handleRestore(row)">恢复</el-button>
                <el-button type="info" size="small" @click="handleDownload(row)">下载</el-button>
                <el-button type="danger" size="small" @click="handleDelete(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>

          <el-pagination
            v-model:current-page="pagination.page"
            v-model:page-size="pagination.per_page"
            :total="pagination.total"
            @current-change="loadBackupList"
            layout="total, prev, pager, next"
            class="mt-4"
          />
        </el-card>
      </el-tab-pane>

      <!-- 表管理 -->
      <el-tab-pane label="表管理" name="tables">
        <el-card shadow="never">
          <template #header>
            <div class="card-header">
              <span>数据表列表 ({{ tableList.length }})</span>
              <div>
                <el-button type="primary" @click="handleOptimizeSelected">优化选中</el-button>
                <el-button type="warning" @click="handleRepairSelected">修复选中</el-button>
              </div>
            </div>
          </template>

          <el-table :data="tableList" border stripe @selection-change="handleTableSelectionChange">
            <el-table-column type="selection" width="55" />
            <el-table-column prop="name" label="表名" min-width="200" />
            <el-table-column prop="rows" label="行数" width="120" />
            <el-table-column prop="data_length_format" label="数据大小" width="120" />
            <el-table-column prop="engine" label="引擎" width="100" />
            <el-table-column prop="collation" label="排序规则" width="150" />
            <el-table-column prop="comment" label="备注" min-width="200" />
          </el-table>
        </el-card>
      </el-tab-pane>
    </el-tabs>

    <!-- 表选择对话框 -->
    <el-dialog v-model="tableSelectVisible" title="选择要备份的表" width="600px">
      <el-transfer
        v-model="selectedTables"
        :data="tableOptions"
        :titles="['可用表', '已选表']"
        filterable
        filter-placeholder="搜索表名"
      />
      <template #footer>
        <el-button @click="tableSelectVisible = false">取消</el-button>
        <el-button type="primary" @click="handleBackupTables">开始备份</el-button>
      </template>
    </el-dialog>

    <!-- 备份描述对话框 -->
    <el-dialog v-model="descriptionVisible" title="备份描述" width="400px">
      <el-input v-model="backupDescription" type="textarea" :rows="4" placeholder="输入备份描述（可选）" />
      <template #footer>
        <el-button @click="descriptionVisible = false">取消</el-button>
        <el-button type="primary" @click="confirmBackup">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox, ElLoading } from 'element-plus'
import { Refresh } from '@element-plus/icons-vue'
import {
  getDatabaseInfo,
  getTables,
  backup,
  backupTables,
  getBackups,
  restore,
  deleteBackup,
  downloadBackup,
  optimizeTables,
  repairTables
} from '@/api/database'

const activeTab = ref('backup')
const dbInfo = ref({})
const backupList = ref([])
const tableList = ref([])
const selectedTableRows = ref([])
const pagination = reactive({
  page: 1,
  per_page: 15,
  total: 0
})

const tableSelectVisible = ref(false)
const descriptionVisible = ref(false)
const selectedTables = ref([])
const tableOptions = ref([])
const backupDescription = ref('')
const currentBackupType = ref('full')

onMounted(() => {
  loadDatabaseInfo()
  loadBackupList()
  loadTables()
})

const loadDatabaseInfo = async () => {
  const { data } = await getDatabaseInfo()
  dbInfo.value = data
}

const loadBackupList = async () => {
  const { data } = await getBackups({
    page: pagination.page,
    per_page: pagination.per_page
  })
  backupList.value = data.list
  pagination.total = data.total
}

const loadTables = async () => {
  const { data } = await getTables()
  tableList.value = data
  tableOptions.value = data.map(t => ({
    key: t.name,
    label: `${t.name} (${t.rows} 行, ${t.data_length_format})`
  }))
}

const handleBackup = () => {
  currentBackupType.value = 'full'
  descriptionVisible.value = true
}

const showTableSelect = () => {
  selectedTables.value = []
  tableSelectVisible.value = true
}

const handleBackupTables = () => {
  if (selectedTables.value.length === 0) {
    ElMessage.warning('请选择要备份的表')
    return
  }
  tableSelectVisible.value = false
  currentBackupType.value = 'tables'
  descriptionVisible.value = true
}

const confirmBackup = async () => {
  descriptionVisible.value = false
  const loading = ElLoading.service({ text: '正在备份...' })

  try {
    let result
    if (currentBackupType.value === 'full') {
      result = await backup({ description: backupDescription.value })
    } else {
      result = await backupTables({
        tables: selectedTables.value,
        description: backupDescription.value
      })
    }

    if (result.code === 0) {
      ElMessage.success('备份成功')
      backupDescription.value = ''
      loadBackupList()
    }
  } finally {
    loading.close()
  }
}

const handleRestore = async (row) => {
  try {
    await ElMessageBox.confirm(
      `确定要恢复备份 "${row.filename}" 吗？此操作将覆盖当前数据库！`,
      '警告',
      { type: 'warning', confirmButtonText: '确定恢复', cancelButtonText: '取消' }
    )

    const loading = ElLoading.service({ text: '正在恢复...' })
    try {
      const { data } = await restore({ filename: row.filename })
      ElMessage.success('数据库恢复成功')
    } finally {
      loading.close()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '恢复失败')
    }
  }
}

const handleDownload = async (row) => {
  try {
    const blob = await downloadBackup({ filename: row.filename })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = row.filename
    link.click()
    window.URL.revokeObjectURL(url)
    ElMessage.success('下载成功')
  } catch (error) {
    ElMessage.error('下载失败')
  }
}

const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除备份 "${row.filename}" 吗？`, '提示', { type: 'warning' })
    await deleteBackup(row.id)
    ElMessage.success('删除成功')
    loadBackupList()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

const handleTableSelectionChange = (selection) => {
  selectedTableRows.value = selection
}

const handleOptimizeSelected = async () => {
  if (selectedTableRows.value.length === 0) {
    ElMessage.warning('请选择要优化的表')
    return
  }

  const loading = ElLoading.service({ text: '正在优化...' })
  try {
    const tables = selectedTableRows.value.map(t => t.name)
    const { data } = await optimizeTables({ tables })
    ElMessage.success(`优化完成，成功 ${data.success} 个，失败 ${data.failed} 个`)
  } finally {
    loading.close()
  }
}

const handleRepairSelected = async () => {
  if (selectedTableRows.value.length === 0) {
    ElMessage.warning('请选择要修复的表')
    return
  }

  const loading = ElLoading.service({ text: '正在修复...' })
  try {
    const tables = selectedTableRows.value.map(t => t.name)
    const { data } = await repairTables({ tables })
    ElMessage.success(`修复完成，成功 ${data.success} 个，失败 ${data.failed} 个`)
  } finally {
    loading.close()
  }
}
</script>

<style scoped>
.database-management {
  padding: 20px;
}

.info-card {
  margin-bottom: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.main-tabs {
  margin-top: 20px;
}

.mt-4 {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}
</style>
