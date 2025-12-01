<template>
  <div class="link-list">
    <el-row :gutter="20">
      <!-- 左侧：友链分组 -->
      <el-col :span="6">
        <el-card>
          <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span>友链分组</span>
              <el-button size="small" type="primary" @click="handleCreateGroup">新建分组</el-button>
            </div>
          </template>

          <div class="group-list">
            <div
              v-for="group in groups"
              :key="group.id"
              class="group-item"
              :class="{ active: currentGroupId === group.id }"
              @click="handleSelectGroup(group.id)"
            >
              <div class="group-info">
                <div class="group-name">{{ group.name }}</div>
                <el-tag size="small" type="info">{{ group.link_count || 0 }}</el-tag>
              </div>
              <div class="group-actions">
                <el-button
                  size="small"
                  text
                  @click.stop="handleEditGroup(group)"
                >
                  编辑
                </el-button>
                <el-button
                  size="small"
                  text
                  type="danger"
                  @click.stop="handleDeleteGroup(group)"
                >
                  删除
                </el-button>
              </div>
            </div>
          </div>
        </el-card>
      </el-col>

      <!-- 右侧：友情链接列表 -->
      <el-col :span="18">
        <el-card>
          <!-- 搜索栏 -->
          <el-form :inline="true" :model="searchForm" class="search-form">
            <el-form-item label="所属站点">
              <el-select v-model="searchForm.site_id" placeholder="选择站点" clearable style="width: 200px;">
                <el-option label="全部站点" :value="null" />
                <el-option
                  v-for="site in siteOptions"
                  :key="site.id"
                  :label="site.name"
                  :value="site.id"
                />
              </el-select>
            </el-form-item>
            <el-form-item label="关键词">
              <el-input
                v-model="searchForm.keyword"
                placeholder="网站名称或URL"
                clearable
                @keyup.enter="handleSearch"
              />
            </el-form-item>
            <el-form-item label="状态">
              <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
                <el-option label="待审核" :value="0" />
                <el-option label="已通过" :value="1" />
                <el-option label="已拒绝" :value="2" />
              </el-select>
            </el-form-item>
            <el-form-item>
              <el-button type="primary" @click="handleSearch">搜索</el-button>
              <el-button @click="handleReset">重置</el-button>
              <el-button type="success" @click="handleCreate">新建友链</el-button>
            </el-form-item>
          </el-form>

          <!-- 数据表格 -->
          <el-table :data="list" border style="width: 100%">
            <el-table-column prop="id" label="ID" width="80" />
            <el-table-column label="Logo" width="100">
              <template #default="{ row }">
                <img
                  v-if="row.logo"
                  :src="row.logo"
                  style="width: 60px; height: 40px; object-fit: cover;"
                />
                <span v-else style="color: #999;">无</span>
              </template>
            </el-table-column>
            <el-table-column prop="name" label="网站名称" min-width="120" />
            <el-table-column prop="url" label="网站URL" min-width="180" show-overflow-tooltip />
            <el-table-column label="所属站点" width="120">
              <template #default="{ row }">
                <el-tag size="small">{{ row.site?.name || '-' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="group.name" label="分组" width="100" />
            <el-table-column label="状态" width="80" align="center">
              <template #default="{ row }">
                <el-tag v-if="row.status === 0" type="warning" size="small">待审核</el-tag>
                <el-tag v-else-if="row.status === 1" type="success" size="small">已通过</el-tag>
                <el-tag v-else type="danger" size="small">已拒绝</el-tag>
              </template>
            </el-table-column>
            <el-table-column label="首页显示" width="90" align="center">
              <template #default="{ row }">
                <el-tag v-if="row.is_home" type="success" size="small">是</el-tag>
                <el-tag v-else type="info" size="small">否</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="view_count" label="点击数" width="80" align="center" />
            <el-table-column prop="sort" label="排序" width="70" align="center" />
            <el-table-column label="操作" width="220" fixed="right">
              <template #default="{ row }">
                <el-button v-if="row.status === 0" size="small" type="success" @click="handleAudit(row, 'approve')">通过</el-button>
                <el-button v-if="row.status === 0" size="small" type="danger" @click="handleAudit(row, 'reject')">拒绝</el-button>
                <el-button size="small" type="primary" @click="handleEdit(row)">编辑</el-button>
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
      </el-col>
    </el-row>

    <!-- 分组对话框 -->
    <el-dialog
      v-model="groupDialogVisible"
      :title="groupDialogTitle"
      width="500px"
    >
      <el-form
        ref="groupFormRef"
        :model="groupForm"
        :rules="groupRules"
        label-width="100px"
      >
        <el-form-item label="所属站点" :prop="isGroupEdit ? 'site_id' : 'site_ids'">
          <!-- 编辑时：单选 -->
          <el-select v-if="isGroupEdit" v-model="groupForm.site_id" placeholder="请选择站点" style="width: 100%;">
            <el-option
              v-for="site in siteOptions"
              :key="site.id"
              :label="site.name"
              :value="site.id"
            />
          </el-select>
          <!-- 创建时：多选 -->
          <div v-else>
            <el-select v-model="groupForm.site_ids" placeholder="请选择站点（可多选）" multiple style="width: 100%;">
              <el-option
                v-for="site in siteOptions"
                :key="site.id"
                :label="site.name"
                :value="site.id"
              />
            </el-select>
            <div style="margin-top: 8px;">
              <el-button size="small" @click="selectAllSitesForGroup">全选</el-button>
              <el-button size="small" @click="deselectAllSitesForGroup">取消全选</el-button>
            </div>
          </div>
        </el-form-item>
        <el-form-item label="分组名称" prop="name">
          <el-input v-model="groupForm.name" placeholder="请输入分组名称" />
        </el-form-item>
        <el-form-item label="分组描述">
          <el-input v-model="groupForm.description" type="textarea" :rows="3" placeholder="请输入分组描述" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="groupForm.sort" :min="0" />
        </el-form-item>
        <el-form-item label="状态">
          <el-radio-group v-model="groupForm.status">
            <el-radio :label="1">启用</el-radio>
            <el-radio :label="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="groupDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmitGroup" :loading="submitting">确定</el-button>
      </template>
    </el-dialog>

    <!-- 友链对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="dialogTitle"
      width="600px"
    >
      <el-form
        ref="formRef"
        :model="form"
        :rules="rules"
        label-width="100px"
      >
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
        </el-form-item>

        <el-form-item label="网站名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入网站名称" />
        </el-form-item>

        <el-form-item label="网站URL" prop="url">
          <el-input v-model="form.url" placeholder="https://example.com" />
        </el-form-item>

        <el-form-item label="所属分组">
          <el-select v-model="form.group_id" placeholder="请选择分组" style="width: 100%;">
            <el-option
              v-for="group in groups.filter(g => g.status === 1)"
              :key="group.id"
              :label="group.name"
              :value="group.id"
            />
          </el-select>
        </el-form-item>

        <el-form-item label="网站Logo">
          <el-upload
            class="logo-uploader"
            :action="uploadAction"
            :headers="uploadHeaders"
            :show-file-list="false"
            :on-success="handleLogoSuccess"
            :before-upload="beforeLogoUpload"
            name="file"
          >
            <img v-if="logoUrl" :src="logoUrl" class="logo-image" />
            <el-icon v-else class="logo-uploader-icon"><Plus /></el-icon>
          </el-upload>
          <el-button
            v-if="form.logo"
            size="small"
            type="danger"
            @click="handleRemoveLogo"
            style="margin-top: 10px;"
          >
            删除Logo
          </el-button>
        </el-form-item>

        <el-form-item label="网站描述">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请输入网站描述"
          />
        </el-form-item>

        <el-form-item label="联系邮箱">
          <el-input v-model="form.email" placeholder="webmaster@example.com" />
        </el-form-item>

        <el-form-item label="排序">
          <el-input-number v-model="form.sort" :min="0" />
        </el-form-item>

        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :label="0">待审核</el-radio>
            <el-radio :label="1">已通过</el-radio>
            <el-radio :label="2">已拒绝</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="首页显示">
          <el-switch v-model="form.is_home" :active-value="1" :inactive-value="0" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import {
  getLinkList,
  getLinkDetail,
  createLink,
  updateLink,
  deleteLink,
  auditLink
} from '@/api/link'
import {
  getAllLinkGroups,
  createLinkGroup,
  updateLinkGroup,
  deleteLinkGroup
} from '@/api/linkGroup'
import { getSiteOptions } from '@/api/site'
import { getToken } from '@/utils/auth'

const siteOptions = ref([])
const searchForm = reactive({
  site_id: null,
  keyword: '',
  status: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})

const list = ref([])
const groups = ref([])
const currentGroupId = ref(null)
const loading = ref(false)
const dialogVisible = ref(false)
const dialogTitle = ref('')
const groupDialogVisible = ref(false)
const groupDialogTitle = ref('')
const submitting = ref(false)
const formRef = ref(null)
const groupFormRef = ref(null)
const isEdit = ref(false)
const editId = ref(0)
const isGroupEdit = ref(false)
const editGroupId = ref(0)

const form = reactive({
  site_id: null,
  site_ids: [], // 多站点创建
  group_id: null,
  name: '',
  url: '',
  logo: '',
  description: '',
  email: '',
  sort: 0,
  status: 0,
  is_home: 0
})

const groupForm = reactive({
  site_id: null,
  site_ids: [], // 多站点创建
  name: '',
  description: '',
  sort: 0,
  status: 1
})

const rules = {
  site_id: [{ required: true, message: '请选择所属站点', trigger: 'change' }],
  site_ids: [{ required: true, type: 'array', min: 1, message: '请至少选择一个站点', trigger: 'change' }],
  name: [{ required: true, message: '请输入网站名称', trigger: 'blur' }],
  url: [
    { required: true, message: '请输入网站URL', trigger: 'blur' },
    { type: 'url', message: 'URL格式不正确', trigger: 'blur' }
  ]
}

const groupRules = {
  site_id: [{ required: true, message: '请选择所属站点', trigger: 'change' }],
  site_ids: [{ required: true, type: 'array', min: 1, message: '请至少选择一个站点', trigger: 'change' }],
  name: [{ required: true, message: '请输入分组名称', trigger: 'blur' }]
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

const logoUrl = computed(() => {
  return form.logo || ''
})

// 上传前校验
const beforeLogoUpload = (file) => {
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
const handleLogoSuccess = (response) => {
  if (response.code === 200) {
    form.logo = response.data.file_url || response.data.file_path
    ElMessage.success('Logo上传成功')
  } else {
    ElMessage.error(response.message || '上传失败')
  }
}

// 删除Logo
const handleRemoveLogo = () => {
  form.logo = ''
  ElMessage.success('Logo已删除')
}

// 加载分组
const loadGroups = async () => {
  try {
    const res = await getAllLinkGroups()
    groups.value = res.data.list || []
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  }
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

    if (currentGroupId.value) {
      params.group_id = currentGroupId.value
    }

    const res = await getLinkList(params)
    list.value = res.data.list
    pagination.total = res.data.total
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.value = false
  }
}

// 选择分组
const handleSelectGroup = (groupId) => {
  currentGroupId.value = groupId
  pagination.page = 1
  loadData()
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  loadData()
}

// 重置
const handleReset = () => {
  searchForm.site_id = null
  searchForm.keyword = ''
  searchForm.status = ''
  currentGroupId.value = null
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

// 新建分组
const handleCreateGroup = () => {
  isGroupEdit.value = false
  groupDialogTitle.value = '新建分组'
  resetGroupForm()
  groupDialogVisible.value = true
}

// 编辑分组
const handleEditGroup = (row) => {
  isGroupEdit.value = true
  editGroupId.value = row.id
  groupDialogTitle.value = '编辑分组'
  Object.assign(groupForm, row)
  groupDialogVisible.value = true
}

// 删除分组
const handleDeleteGroup = (row) => {
  ElMessageBox.confirm('确定要删除该分组吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      await deleteLinkGroup(row.id)
      ElMessage.success('删除成功')
      loadGroups()
      if (currentGroupId.value === row.id) {
        currentGroupId.value = null
      }
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  }).catch(() => {})
}

// 提交分组表单
const handleSubmitGroup = async () => {
  if (!groupFormRef.value) return

  await groupFormRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true
      try {
        const submitData = { ...groupForm }

        // 区分创建和编辑模式的站点字段
        if (isGroupEdit.value) {
          delete submitData.site_ids
        } else {
          delete submitData.site_id
        }

        if (isGroupEdit.value) {
          await updateLinkGroup(editGroupId.value, submitData)
          ElMessage.success('更新成功')
        } else {
          await createLinkGroup(submitData)
          ElMessage.success('创建成功')
        }
        groupDialogVisible.value = false
        loadGroups()
      } catch (error) {
        ElMessage.error(error.message || '保存失败')
      } finally {
        submitting.value = false
      }
    }
  })
}

// 新建友链
const handleCreate = () => {
  isEdit.value = false
  dialogTitle.value = '新建友链'
  resetForm()
  if (currentGroupId.value) {
    form.group_id = currentGroupId.value
  }
  dialogVisible.value = true
}

// 编辑友链
const handleEdit = async (row) => {
  isEdit.value = true
  editId.value = row.id
  dialogTitle.value = '编辑友链'

  try {
    const res = await getLinkDetail(row.id)
    Object.assign(form, res.data)
    dialogVisible.value = true
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  }
}

// 删除友链
const handleDelete = (row) => {
  ElMessageBox.confirm('确定要删除该友链吗？', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(async () => {
    try {
      await deleteLink(row.id)
      ElMessage.success('删除成功')
      loadData()
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  }).catch(() => {})
}

// 审核友链
const handleAudit = async (row, action) => {
  const actionText = action === 'approve' ? '通过' : '拒绝'

  try {
    const { value: note } = await ElMessageBox.prompt('审核备注（可选）', `审核${actionText}`, {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      inputType: 'textarea'
    })

    await auditLink(row.id, action, note || '')
    ElMessage.success(`${actionText}成功`)
    loadData()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || `${actionText}失败`)
    }
  }
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      submitting.value = true
      try {
        const submitData = { ...form }

        // 区分创建和编辑模式的站点字段
        if (isEdit.value) {
          delete submitData.site_ids
        } else {
          delete submitData.site_id
        }

        if (isEdit.value) {
          await updateLink(editId.value, submitData)
          ElMessage.success('更新成功')
        } else {
          await createLink(submitData)
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
  form.site_id = null
  form.site_ids = []
  form.group_id = null
  form.name = ''
  form.url = ''
  form.logo = ''
  form.description = ''
  form.email = ''
  form.sort = 0
  form.status = 0
  form.is_home = 0
}

const resetGroupForm = () => {
  groupForm.site_id = null
  groupForm.site_ids = []
  groupForm.name = ''
  groupForm.description = ''
  groupForm.sort = 0
  groupForm.status = 1
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

// 全选站点（友链）
const selectAllSites = () => {
  form.site_ids = siteOptions.value.map(site => site.id)
}

// 取消全选站点（友链）
const deselectAllSites = () => {
  form.site_ids = []
}

// 全选站点（分组）
const selectAllSitesForGroup = () => {
  groupForm.site_ids = siteOptions.value.map(site => site.id)
}

// 取消全选站点（分组）
const deselectAllSitesForGroup = () => {
  groupForm.site_ids = []
}

onMounted(async () => {
  await fetchSiteOptions()
  await loadGroups()
  await loadData()
})
</script>

<style scoped>
.link-list {
  padding: 20px;
}

.search-form {
  margin-bottom: 20px;
}

.group-list {
  max-height: 600px;
  overflow-y: auto;
}

.group-item {
  padding: 12px;
  border-radius: 4px;
  margin-bottom: 8px;
  cursor: pointer;
  transition: all 0.3s;
  border: 1px solid #eee;
}

.group-item:hover {
  background-color: #f5f7fa;
  border-color: #409eff;
}

.group-item.active {
  background-color: #ecf5ff;
  border-color: #409eff;
}

.group-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.group-name {
  font-weight: 500;
}

.group-actions {
  display: flex;
  gap: 5px;
}

.logo-uploader .logo-image {
  max-width: 150px;
  max-height: 80px;
  width: auto;
  height: auto;
  display: block;
  object-fit: contain;
  border-radius: 4px;
}

.logo-uploader :deep(.el-upload) {
  border: 1px dashed #d9d9d9;
  border-radius: 4px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s;
  width: 150px;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #fafafa;
}

.logo-uploader :deep(.el-upload:hover) {
  border-color: #409eff;
}

.logo-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
