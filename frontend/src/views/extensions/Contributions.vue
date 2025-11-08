<template>
  <div class="contributions-container">
    <el-card>
      <template #header>
        <span>投稿管理</span>
      </template>

      <el-tabs v-model="activeTab">
        <!-- 投稿列表 -->
        <el-tab-pane label="投稿列表" name="list">
          <el-form :inline="true" :model="searchForm">
            <el-form-item label="关键词">
              <el-input v-model="searchForm.keyword" placeholder="文章标题" clearable />
            </el-form-item>
            <el-form-item label="审核状态">
              <el-select v-model="searchForm.audit_status" placeholder="全部" clearable>
                <el-option label="待审核" :value="0" />
                <el-option label="已通过" :value="1" />
                <el-option label="已拒绝" :value="2" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="loadData">搜索</el-button>
            </el-form-item>
          </el-form>

          <!-- 统计信息 -->
          <div class="stats-bar" v-if="stats">
            <el-tag>总投稿: {{ stats.total }}</el-tag>
            <el-tag type="warning">待审核: {{ stats.pending }}</el-tag>
            <el-tag type="success">已通过: {{ stats.approved }}</el-tag>
            <el-tag type="danger">已拒绝: {{ stats.rejected }}</el-tag>
            <el-tag type="info">今日提交: {{ stats.today_submit }}</el-tag>
          </div>

          <el-table :data="contributions" v-loading="loading">
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column prop="title" label="文章标题" width="250" />
            <el-table-column label="投稿用户" width="150">
              <template #default="{ row }">
                {{ row.front_user?.nickname || '-' }}
              </template>
            </el-table-column>
            <el-table-column label="分类" width="120">
              <template #default="{ row }">
                {{ row.category?.name || '-' }}
              </template>
            </el-table-column>
            <el-table-column prop="audit_status" label="审核状态" width="120">
              <template #default="{ row }">
                <el-tag :type="getAuditStatusType(row.audit_status)">
                  {{ getAuditStatusText(row.audit_status) }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="reward_points" label="奖励积分" width="100">
              <template #default="{ row }">
                {{ row.reward_points || '-' }}
              </template>
            </el-table-column>
            <el-table-column prop="create_time" label="投稿时间" width="160" />
            <el-table-column prop="audit_time" label="审核时间" width="160" />
            <el-table-column label="操作" width="200" fixed="right">
              <template #default="{ row }">
                <el-button v-if="row.audit_status === 0" size="small" type="success" @click="auditPass(row)">
                  通过
                </el-button>
                <el-button v-if="row.audit_status === 0" size="small" type="danger" @click="auditReject(row)">
                  拒绝
                </el-button>
                <el-button size="small" @click="viewDetail(row)">查看</el-button>
              </template>
            </el-table-column>
          </el-table>

          <div class="pagination">
            <el-pagination
              v-model:current-page="pagination.page"
              v-model:page-size="pagination.limit"
              :total="pagination.total"
              layout="total, prev, pager, next"
              @current-change="loadData"
            />
          </div>
        </el-tab-pane>

        <!-- 投稿配置 -->
        <el-tab-pane label="投稿配置" name="config">
          <el-button type="primary" @click="showConfigDialog" style="margin-bottom: 15px">
            新增配置
          </el-button>

          <el-table :data="configs" v-loading="loading">
            <el-table-column label="分类" width="150">
              <template #default="{ row }">
                {{ row.category?.name || '-' }}
              </template>
            </el-table-column>
            <el-table-column prop="allow_contribute" label="允许投稿" width="100">
              <template #default="{ row }">
                <el-tag :type="row.allow_contribute ? 'success' : 'danger'">
                  {{ row.allow_contribute ? '是' : '否' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="need_audit" label="需要审核" width="100">
              <template #default="{ row }">
                <el-tag :type="row.need_audit ? 'warning' : 'success'">
                  {{ row.need_audit ? '是' : '否' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="reward_points" label="奖励积分" width="100" />
            <el-table-column prop="min_words" label="最少字数" width="100" />
            <el-table-column prop="max_per_day" label="每日限制" width="100" />
            <el-table-column prop="level_required" label="等级要求" width="100" />
            <el-table-column label="操作" width="150">
              <template #default="{ row }">
                <el-button size="small" @click="editConfig(row)">编辑</el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-tab-pane>
      </el-tabs>
    </el-card>

    <!-- 审核拒绝对话框 -->
    <el-dialog v-model="rejectDialog.visible" title="拒绝投稿" width="500px">
      <el-form :model="rejectDialog.form" label-width="80px">
        <el-form-item label="拒绝原因">
          <el-input v-model="rejectDialog.form.remark" type="textarea" :rows="4" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="rejectDialog.visible = false">取消</el-button>
        <el-button type="danger" @click="confirmReject">确定拒绝</el-button>
      </template>
    </el-dialog>

    <!-- 投稿配置对话框 -->
    <el-dialog v-model="configDialog.visible" :title="configDialog.isEdit ? '编辑配置' : '新增配置'" width="600px">
      <el-form :model="configDialog.form" label-width="100px">
        <el-form-item label="分类">
          <el-select v-model="configDialog.form.category_id" placeholder="请选择分类" style="width: 100%">
            <el-option
              v-for="category in categories"
              :key="category.id"
              :label="category.name"
              :value="category.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="允许投稿">
          <el-radio-group v-model="configDialog.form.allow_contribute">
            <el-radio :label="1">是</el-radio>
            <el-radio :label="0">否</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="需要审核">
          <el-radio-group v-model="configDialog.form.need_audit">
            <el-radio :label="1">是</el-radio>
            <el-radio :label="0">否</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="奖励积分">
          <el-input-number v-model="configDialog.form.reward_points" :min="0" :max="10000" />
          <div style="color: #999; font-size: 12px; margin-top: 5px;">
            审核通过后奖励的积分
          </div>
        </el-form-item>
        <el-form-item label="最少字数">
          <el-input-number v-model="configDialog.form.min_words" :min="0" :max="100000" />
        </el-form-item>
        <el-form-item label="每日限制">
          <el-input-number v-model="configDialog.form.max_per_day" :min="0" :max="100" />
          <div style="color: #999; font-size: 12px; margin-top: 5px;">
            0表示不限制
          </div>
        </el-form-item>
        <el-form-item label="等级要求">
          <el-input-number v-model="configDialog.form.level_required" :min="0" :max="10" />
          <div style="color: #999; font-size: 12px; margin-top: 5px;">
            0表示不限制
          </div>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="configDialog.visible = false">取消</el-button>
        <el-button type="primary" @click="saveConfig">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import request from '@/utils/request'

const activeTab = ref('list')
const loading = ref(false)
const contributions = ref([])
const configs = ref([])
const stats = ref(null)
const categories = ref([])

const searchForm = reactive({
  keyword: '',
  audit_status: ''
})

const pagination = reactive({
  page: 1,
  limit: 20,
  total: 0
})

const rejectDialog = reactive({
  visible: false,
  article: null,
  form: {
    remark: ''
  }
})

const configDialog = reactive({
  visible: false,
  isEdit: false,
  form: {
    id: null,
    category_id: null,
    allow_contribute: 1,
    need_audit: 1,
    reward_points: 10,
    min_words: 100,
    max_per_day: 5,
    level_required: 0
  }
})

const getAuditStatusText = (status) => {
  const texts = ['待审核', '已通过', '已拒绝']
  return texts[status] || '未知'
}

const getAuditStatusType = (status) => {
  const types = ['warning', 'success', 'danger']
  return types[status] || 'default'
}

const loadData = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      limit: pagination.limit,
      ...searchForm
    }
    const response = await request.get('contribute-manage/index', { params })
    if (response.code === 200) {
      contributions.value = response.data.data || []
      pagination.total = response.data.total || 0
    }
  } catch (error) {
    ElMessage.error('加载失败')
  } finally {
    loading.value = false
  }
}

const loadConfigs = async () => {
  try {
    const response = await request.get('contribute-manage/config-index')
    if (response.code === 200) {
      configs.value = response.data || []
    }
  } catch (error) {
    ElMessage.error('加载配置失败')
  }
}

const loadStats = async () => {
  try {
    const response = await request.get('contribute-manage/statistics')
    if (response.code === 200) {
      stats.value = response.data
    }
  } catch (error) {
    console.error('加载统计失败', error)
  }
}

const auditPass = async (row) => {
  try {
    const { value } = await ElMessageBox.prompt('审核备注（可选）', '审核通过', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      inputPattern: /.*/,
      inputErrorMessage: ''
    })
    const response = await request.post(`/api/contribute-manage/audit-pass/${row.id}`, {
      remark: value || ''
    })
    if (response.data.code === 200) {
      ElMessage.success('审核通过')
      loadData()
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('操作失败')
    }
  }
}

const auditReject = (row) => {
  rejectDialog.article = row
  rejectDialog.form.remark = ''
  rejectDialog.visible = true
}

const confirmReject = async () => {
  if (!rejectDialog.form.remark) {
    ElMessage.warning('请输入拒绝原因')
    return
  }

  try {
    const response = await request.post(
      `/contribute-manage/audit-reject/${rejectDialog.article.id}`,
      rejectDialog.form
    )
    if (response.data.code === 200) {
      ElMessage.success('已拒绝')
      rejectDialog.visible = false
      loadData()
    }
  } catch (error) {
    ElMessage.error('操作失败')
  }
}

const viewDetail = (row) => {
  ElMessage.info('查看详情功能开发中...')
}

const loadCategories = async () => {
  try {
    const response = await request.get('categories/tree')
    if (response.code === 200) {
      categories.value = response.data
    }
  } catch (error) {
    console.error('加载分类失败', error)
  }
}

const showConfigDialog = () => {
  configDialog.isEdit = false
  Object.assign(configDialog.form, {
    id: null,
    category_id: null,
    allow_contribute: 1,
    need_audit: 1,
    reward_points: 10,
    min_words: 100,
    max_per_day: 5,
    level_required: 0
  })
  configDialog.visible = true
  loadCategories()
}

const editConfig = (row) => {
  configDialog.isEdit = true
  Object.assign(configDialog.form, {
    id: row.id,
    category_id: row.category_id,
    allow_contribute: row.allow_contribute,
    need_audit: row.need_audit,
    reward_points: row.reward_points,
    min_words: row.min_words,
    max_per_day: row.max_per_day,
    level_required: row.level_required
  })
  configDialog.visible = true
  loadCategories()
}

const saveConfig = async () => {
  try {
    let response
    if (configDialog.isEdit) {
      response = await request.put(`contribute-manage/config-update/${configDialog.form.id}`, configDialog.form)
    } else {
      response = await request.post('contribute-manage/config-create', configDialog.form)
    }

    if (response.code === 200) {
      ElMessage.success(configDialog.isEdit ? '更新成功' : '创建成功')
      configDialog.visible = false
      loadConfigs()
    }
  } catch (error) {
    ElMessage.error('操作失败')
  }
}

onMounted(() => {
  loadData()
  loadConfigs()
  loadStats()
})
</script>

<style scoped>
.contributions-container {
  padding: 20px;
}

.stats-bar {
  margin-bottom: 20px;
  display: flex;
  gap: 10px;
}

.pagination {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>
