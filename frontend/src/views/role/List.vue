<template>
  <div class="role-list">
    <el-card>
      <template #header>
        <div class="header-actions">
          <h3>角色管理</h3>
          <el-button type="primary" @click="handleAdd">
            <el-icon><plus /></el-icon>
            添加角色
          </el-button>
        </div>
      </template>

      <!-- 搜索表单 -->
      <el-form :inline="true" :model="searchForm" class="search-form">
        <el-form-item label="角色名称">
          <el-input v-model="searchForm.name" placeholder="请输入角色名称" clearable />
        </el-form-item>
        <el-form-item label="状态">
          <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
            <el-option label="启用" :value="1" />
            <el-option label="禁用" :value="0" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="handleSearch">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 角色列表 -->
      <el-table :data="roleList" v-loading="loading" border stripe>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="角色名称" min-width="150" />
        <el-table-column prop="description" label="描述" min-width="200" />
        <el-table-column prop="sort" label="排序" width="100" />
        <el-table-column label="用户数" width="100">
          <template #default="{ row }">
            {{ row.users_count || 0 }}
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag v-if="row.status === 1" type="success" size="small">启用</el-tag>
            <el-tag v-else type="danger" size="small">禁用</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="180" />
        <el-table-column label="操作" width="280" fixed="right">
          <template #default="{ row }">
            <el-button size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button size="small" type="primary" @click="handleSetPermissions(row)">
              设置权限
            </el-button>
            <el-button
              v-if="row.id !== 1"
              size="small"
              type="danger"
              @click="handleDelete(row.id)"
            >
              删除
            </el-button>
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
        @size-change="fetchRoles"
        @current-change="fetchRoles"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑角色' : '添加角色'"
      width="600px"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="90px">
        <el-form-item label="角色名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入角色名称" />
        </el-form-item>
        <el-form-item label="描述">
          <el-input
            v-model="form.description"
            type="textarea"
            :rows="3"
            placeholder="请输入角色描述"
          />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.sort" :min="0" />
        </el-form-item>
        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :value="1">启用</el-radio>
            <el-radio :value="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="saving">确定</el-button>
      </template>
    </el-dialog>

    <!-- 权限设置对话框 -->
    <el-dialog
      v-model="permissionDialogVisible"
      title="设置权限"
      width="700px"
      :close-on-click-modal="false"
    >
      <div v-loading="permissionLoading">
        <el-alert
          :title="`正在为角色 ${currentRole?.name} 设置权限`"
          type="info"
          :closable="false"
          style="margin-bottom: 15px;"
        />

        <div style="margin-bottom: 15px;">
          <el-checkbox
            v-model="checkAll"
            :indeterminate="isIndeterminate"
            @change="handleCheckAllChange"
          >
            全选/取消全选
          </el-checkbox>
        </div>

        <el-tree
          ref="permissionTree"
          :data="permissionTreeData"
          show-checkbox
          node-key="id"
          :default-checked-keys="checkedPermissions"
          :props="{ children: 'children', label: 'name' }"
          @check="handlePermissionCheck"
        >
          <template #default="{ node, data }">
            <span class="custom-tree-node">
              <span>{{ data.name }}</span>
              <el-tag v-if="data.type === 'menu'" type="primary" size="small">菜单</el-tag>
              <el-tag v-else-if="data.type === 'page'" type="success" size="small">页面</el-tag>
              <el-tag v-else type="info" size="small">操作</el-tag>
            </span>
          </template>
        </el-tree>
      </div>
      <template #footer>
        <el-button @click="permissionDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="savePermissions" :loading="saving">保存权限</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getRoleList,
  createRole,
  updateRole,
  deleteRole
} from '@/api/role'
import { getPermissionTree } from '@/config/permissions'

const loading = ref(false)
const saving = ref(false)
const dialogVisible = ref(false)
const permissionDialogVisible = ref(false)
const permissionLoading = ref(false)
const isEdit = ref(false)
const formRef = ref(null)
const permissionTree = ref(null)
const roleList = ref([])
const currentRole = ref(null)
const checkedPermissions = ref([])
const checkAll = ref(false)
const isIndeterminate = ref(false)

const searchForm = reactive({
  name: '',
  status: ''
})

const pagination = reactive({
  page: 1,
  pageSize: 10,
  total: 0
})

const form = reactive({
  id: null,
  name: '',
  description: '',
  sort: 0,
  status: 1
})

const rules = {
  name: [{ required: true, message: '请输入角色名称', trigger: 'blur' }]
}

// 权限树数据
const permissionTreeData = computed(() => getPermissionTree())

// 获取角色列表
const fetchRoles = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.page,
      page_size: pagination.pageSize,
      ...searchForm
    }
    const res = await getRoleList(params)
    roleList.value = res.data.list || []
    pagination.total = res.data.total || 0
  } catch (error) {
    ElMessage.error('获取角色列表失败')
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  fetchRoles()
}

// 重置
const handleReset = () => {
  searchForm.name = ''
  searchForm.status = ''
  pagination.page = 1
  fetchRoles()
}

// 添加
const handleAdd = () => {
  isEdit.value = false
  Object.assign(form, {
    id: null,
    name: '',
    description: '',
    sort: 0,
    status: 1
  })
  dialogVisible.value = true
}

// 编辑
const handleEdit = (row) => {
  isEdit.value = true
  Object.assign(form, row)
  dialogVisible.value = true
}

// 提交
const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      saving.value = true
      try {
        if (isEdit.value) {
          await updateRole(form.id, form)
          ElMessage.success('更新成功')
        } else {
          await createRole(form)
          ElMessage.success('创建成功')
        }
        dialogVisible.value = false
        fetchRoles()
      } catch (error) {
        ElMessage.error(error.message || '保存失败')
      } finally {
        saving.value = false
      }
    }
  })
}

// 删除
const handleDelete = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除这个角色吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await deleteRole(id)
    ElMessage.success('删除成功')
    fetchRoles()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
}

// 设置权限
const handleSetPermissions = (row) => {
  currentRole.value = row

  // 解析权限数据
  let permissions = []
  if (row.permissions) {
    try {
      permissions = typeof row.permissions === 'string'
        ? JSON.parse(row.permissions)
        : row.permissions
    } catch (e) {
      permissions = []
    }
  }

  checkedPermissions.value = Array.isArray(permissions) ? permissions : []

  // 更新全选状态
  updateCheckAllState()

  permissionDialogVisible.value = true
}

// 权限勾选变化
const handlePermissionCheck = () => {
  updateCheckAllState()
}

// 更新全选状态
const updateCheckAllState = () => {
  const checkedNodes = permissionTree.value?.getCheckedKeys() || []
  const allNodes = getAllPermissionIds(permissionTreeData.value)

  checkAll.value = checkedNodes.length === allNodes.length && allNodes.length > 0
  isIndeterminate.value = checkedNodes.length > 0 && checkedNodes.length < allNodes.length
}

// 获取所有权限ID
const getAllPermissionIds = (tree) => {
  const ids = []
  const traverse = (nodes) => {
    nodes.forEach(node => {
      ids.push(node.id)
      if (node.children) {
        traverse(node.children)
      }
    })
  }
  traverse(tree)
  return ids
}

// 全选/取消全选
const handleCheckAllChange = (val) => {
  if (val) {
    const allIds = getAllPermissionIds(permissionTreeData.value)
    permissionTree.value?.setCheckedKeys(allIds)
  } else {
    permissionTree.value?.setCheckedKeys([])
  }
  isIndeterminate.value = false
}

// 保存权限
const savePermissions = async () => {
  saving.value = true
  try {
    // 获取所有选中的节点（包括半选中的父节点）
    const checkedKeys = permissionTree.value?.getCheckedKeys() || []
    const halfCheckedKeys = permissionTree.value?.getHalfCheckedKeys() || []
    const allPermissions = [...checkedKeys, ...halfCheckedKeys]

    await updateRole(currentRole.value.id, {
      permissions: JSON.stringify(allPermissions)
    })

    ElMessage.success('权限设置成功')
    permissionDialogVisible.value = false
    fetchRoles()
  } catch (error) {
    ElMessage.error('权限设置失败')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  fetchRoles()
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

.custom-tree-node {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
}

:deep(.el-tree) {
  max-height: 500px;
  overflow-y: auto;
}
</style>
