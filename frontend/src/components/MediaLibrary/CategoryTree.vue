<template>
  <div class="category-tree">
    <!-- 工具栏 -->
    <div class="toolbar">
      <el-button type="primary" size="small" @click="showCreateDialog(0)">
        <el-icon><Plus /></el-icon>
        新建分类
      </el-button>
      <el-button size="small" @click="loadTree">
        <el-icon><Refresh /></el-icon>
        刷新
      </el-button>
      <el-button size="small" @click="expandAll">全部展开</el-button>
      <el-button size="small" @click="collapseAll">全部折叠</el-button>
    </div>

    <!-- 分类树 -->
    <el-tree
      ref="treeRef"
      :data="treeData"
      :props="treeProps"
      node-key="id"
      :expand-on-click-node="false"
      :default-expanded-keys="expandedKeys"
      draggable
      :allow-drop="allowDrop"
      @node-drop="handleDrop"
      @node-click="handleNodeClick"
      v-loading="loading"
    >
      <template #default="{ node, data }">
        <div class="tree-node">
          <span class="node-icon" v-if="data.icon">
            <el-icon><component :is="data.icon" /></el-icon>
          </span>
          <span class="node-label">{{ data.name }}</span>
          <span class="node-count">({{ data.media_count || 0 }})</span>
          <div class="node-actions">
            <el-button link size="small" @click.stop="showCreateDialog(data.id)">
              <el-icon><Plus /></el-icon>
            </el-button>
            <el-button link size="small" @click.stop="showEditDialog(data)">
              <el-icon><Edit /></el-icon>
            </el-button>
            <el-button link size="small" type="danger" @click.stop="handleDelete(data)">
              <el-icon><Delete /></el-icon>
            </el-button>
          </div>
        </div>
      </template>
    </el-tree>

    <!-- 空状态 -->
    <el-empty v-if="!loading && treeData.length === 0" description="暂无分类" />

    <!-- 创建/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑分类' : '新建分类'"
      width="500px"
      :close-on-click-modal="false"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="80px"
      >
        <el-form-item label="分类名称" prop="name">
          <el-input v-model="formData.name" placeholder="请输入分类名称" />
        </el-form-item>
        <el-form-item label="父级分类">
          <el-tree-select
            v-model="formData.parent_id"
            :data="treeDataForSelect"
            :props="{ label: 'name', value: 'id', children: 'children' }"
            placeholder="请选择父级分类"
            check-strictly
            clearable
            :disabled="isEdit && hasChildren"
          />
        </el-form-item>
        <el-form-item label="图标">
          <el-input v-model="formData.icon" placeholder="请输入图标名称" />
        </el-form-item>
        <el-form-item label="描述">
          <el-input
            v-model="formData.description"
            type="textarea"
            :rows="3"
            placeholder="请输入分类描述"
          />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="formData.sort_order" :min="0" />
        </el-form-item>
        <el-form-item label="是否可见">
          <el-switch v-model="formData.is_visible" :active-value="1" :inactive-value="0" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">
          {{ isEdit ? '保存' : '创建' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- 合并对话框 -->
    <el-dialog
      v-model="mergeDialogVisible"
      title="合并分类"
      width="400px"
    >
      <el-form label-width="100px">
        <el-form-item label="源分类">
          <el-tag>{{ mergeSource?.name }}</el-tag>
        </el-form-item>
        <el-form-item label="目标分类">
          <el-tree-select
            v-model="mergeTargetId"
            :data="treeDataForSelect"
            :props="{ label: 'name', value: 'id', children: 'children' }"
            placeholder="请选择目标分类"
            check-strictly
          />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="mergeDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleMerge" :loading="merging">合并</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Edit, Delete, Refresh } from '@element-plus/icons-vue'
import {
  getCategoryTree,
  createCategory,
  updateCategory,
  deleteCategory,
  moveCategory,
  mergeCategories
} from '@/api/mediaCategory'

const emit = defineEmits(['select', 'change'])

// 树形数据
const treeRef = ref(null)
const treeData = ref([])
const loading = ref(false)
const expandedKeys = ref([])

const treeProps = {
  label: 'name',
  children: 'children'
}

// 对话框
const dialogVisible = ref(false)
const isEdit = ref(false)
const hasChildren = ref(false)
const submitting = ref(false)
const formRef = ref(null)

const formData = reactive({
  id: null,
  name: '',
  parent_id: 0,
  icon: '',
  description: '',
  sort_order: 0,
  is_visible: 1
})

const formRules = {
  name: [
    { required: true, message: '请输入分类名称', trigger: 'blur' },
    { max: 100, message: '分类名称不能超过100个字符', trigger: 'blur' }
  ]
}

// 合并对话框
const mergeDialogVisible = ref(false)
const mergeSource = ref(null)
const mergeTargetId = ref(null)
const merging = ref(false)

// 用于选择的树形数据（带根节点）
const treeDataForSelect = computed(() => {
  return [
    {
      id: 0,
      name: '根目录',
      children: treeData.value
    }
  ]
})

// 加载分类树
const loadTree = async () => {
  loading.value = true
  try {
    const res = await getCategoryTree()
    treeData.value = res.data || []
  } catch (error) {
    ElMessage.error('加载分类失败')
  } finally {
    loading.value = false
  }
}

// 展开全部
const expandAll = () => {
  const keys = []
  const traverse = (nodes) => {
    nodes.forEach(node => {
      keys.push(node.id)
      if (node.children?.length) {
        traverse(node.children)
      }
    })
  }
  traverse(treeData.value)
  expandedKeys.value = keys
}

// 折叠全部
const collapseAll = () => {
  expandedKeys.value = []
}

// 显示创建对话框
const showCreateDialog = (parentId = 0) => {
  isEdit.value = false
  hasChildren.value = false
  Object.assign(formData, {
    id: null,
    name: '',
    parent_id: parentId,
    icon: '',
    description: '',
    sort_order: 0,
    is_visible: 1
  })
  dialogVisible.value = true
}

// 显示编辑对话框
const showEditDialog = (data) => {
  isEdit.value = true
  hasChildren.value = data.children?.length > 0
  Object.assign(formData, {
    id: data.id,
    name: data.name,
    parent_id: data.parent_id || 0,
    icon: data.icon || '',
    description: data.description || '',
    sort_order: data.sort_order || 0,
    is_visible: data.is_visible ?? 1
  })
  dialogVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
  await formRef.value?.validate()

  submitting.value = true
  try {
    if (isEdit.value) {
      await updateCategory(formData.id, formData)
      ElMessage.success('更新成功')
    } else {
      await createCategory(formData)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    loadTree()
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  } finally {
    submitting.value = false
  }
}

// 删除分类
const handleDelete = async (data) => {
  if (data.children?.length > 0) {
    ElMessage.warning('该分类下有子分类，无法删除')
    return
  }

  if (data.media_count > 0) {
    ElMessage.warning(`该分类下有 ${data.media_count} 个媒体文件，无法删除`)
    return
  }

  await ElMessageBox.confirm(
    `确定要删除分类"${data.name}"吗？`,
    '删除确认',
    { type: 'warning' }
  )

  try {
    await deleteCategory(data.id)
    ElMessage.success('删除成功')
    loadTree()
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '删除失败')
  }
}

// 允许拖放判断
const allowDrop = (draggingNode, dropNode, type) => {
  // 不允许放在自己的子节点下
  const checkParent = (node, targetId) => {
    if (node.data.id === targetId) return false
    if (node.parent?.data) {
      return checkParent(node.parent, targetId)
    }
    return true
  }

  if (type !== 'inner') return true
  return checkParent(dropNode, draggingNode.data.id)
}

// 处理拖放
const handleDrop = async (draggingNode, dropNode, dropType) => {
  let targetParentId = 0

  if (dropType === 'inner') {
    targetParentId = dropNode.data.id
  } else {
    targetParentId = dropNode.data.parent_id || 0
  }

  try {
    await moveCategory(draggingNode.data.id, targetParentId)
    ElMessage.success('移动成功')
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '移动失败')
    loadTree()
  }
}

// 节点点击
const handleNodeClick = (data) => {
  emit('select', data)
}

// 处理合并
const handleMerge = async () => {
  if (!mergeTargetId.value) {
    ElMessage.warning('请选择目标分类')
    return
  }

  if (mergeTargetId.value === mergeSource.value?.id) {
    ElMessage.warning('不能合并到自身')
    return
  }

  merging.value = true
  try {
    await mergeCategories(mergeSource.value.id, mergeTargetId.value)
    ElMessage.success('合并成功')
    mergeDialogVisible.value = false
    loadTree()
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '合并失败')
  } finally {
    merging.value = false
  }
}

// 暴露方法
defineExpose({
  loadTree,
  showCreateDialog
})

onMounted(() => {
  loadTree()
})
</script>

<style scoped>
.category-tree {
  padding: 10px;
}

.toolbar {
  margin-bottom: 15px;
  display: flex;
  gap: 10px;
}

.tree-node {
  flex: 1;
  display: flex;
  align-items: center;
  padding: 5px 0;
}

.node-icon {
  margin-right: 5px;
}

.node-label {
  flex: 1;
}

.node-count {
  color: #999;
  font-size: 12px;
  margin-left: 5px;
}

.node-actions {
  display: none;
  margin-left: 10px;
}

.tree-node:hover .node-actions {
  display: flex;
}
</style>
