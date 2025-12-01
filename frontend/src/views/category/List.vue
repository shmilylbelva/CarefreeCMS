<template>
  <div class="category-list">
    <el-card>
      <template #header>
        <div class="header-actions">
          <h3>分类管理</h3>
          <div class="header-right">
            <el-select
              v-model="currentSiteId"
              placeholder="选择站点"
              @change="handleSiteChange"
              style="width: 200px; margin-right: 10px;"
              clearable
            >
              <el-option label="全部站点" :value="null" />
              <el-option
                v-for="site in siteOptions"
                :key="site.id"
                :label="site.name"
                :value="site.id"
              />
            </el-select>
            <el-button type="primary" @click="handleAdd">
              <el-icon><plus /></el-icon>
              添加分类
            </el-button>
          </div>
        </div>
      </template>

      <el-table :data="categoryList" v-loading="loading" border row-key="id" default-expand-all>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="名称" min-width="200" />
        <el-table-column prop="slug" label="别名" width="150" />
        <el-table-column prop="sort" label="排序" width="100" />
        <el-table-column label="所属站点" width="120">
          <template #default="{ row }">
            <el-tag size="small">{{ row.site?.name || '-' }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="文章数" width="100">
          <template #default="{ row }">
            {{ row.articles_count || 0 }}
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200">
          <template #default="{ row }">
            <el-button size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button size="small" type="danger" @click="handleDelete(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑分类' : '添加分类'"
      width="500px"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="80px">
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
              编辑时只能修改当前站点的分类，不影响其他站点
            </template>
            <template v-else>
              创建时可选择多个站点，系统将为每个站点创建独立副本
            </template>
          </div>
        </el-form-item>
        <el-form-item label="父级分类">
          <el-tree-select
            v-model="form.parent_id"
            :data="categoryTreeOptions"
            :props="{ value: 'id', label: 'name', children: 'children' }"
            placeholder="请选择父级分类（不选则为顶级分类）"
            clearable
            check-strictly
            style="width: 100%;"
          />
        </el-form-item>
        <el-form-item label="名称" prop="name">
          <el-input v-model="form.name" placeholder="请输入分类名称" />
        </el-form-item>
        <el-form-item label="别名" prop="slug">
          <el-input v-model="form.slug" placeholder="请输入分类别名（用于URL）" />
        </el-form-item>
        <el-form-item label="排序">
          <el-input-number v-model="form.sort" :min="0" />
        </el-form-item>
        <el-form-item label="模板">
          <el-select v-model="form.template" placeholder="请选择模板" clearable style="width: 100%;">
            <el-option label="默认模板(category)" value="category" />
            <el-option
              v-for="tpl in templates"
              :key="tpl.template_key"
              :label="tpl.name"
              :value="tpl.template_key"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="描述">
          <el-input v-model="form.description" type="textarea" :rows="3" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="saving">确定</el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getCategoryTree,
  createCategory,
  updateCategory,
  deleteCategory
} from '@/api/category'
import { getTemplates } from '@/api/template'
import { getSiteOptions, getCurrentSite } from '@/api/site'

const loading = ref(false)
const saving = ref(false)
const dialogVisible = ref(false)
const isEdit = ref(false)
const formRef = ref(null)
const categoryList = ref([])
const templates = ref([])
const siteOptions = ref([])
const currentSiteId = ref(null)

const form = reactive({
  id: null,
  site_id: null,
  site_ids: [], // 多站点创建
  parent_id: null,
  name: '',
  slug: '',
  sort: 0,
  template: 'category',
  description: ''
})

const rules = {
  site_id: [{ required: true, message: '请选择所属站点', trigger: 'change' }],
  site_ids: [{ required: true, type: 'array', min: 1, message: '请至少选择一个站点', trigger: 'change' }],
  name: [{ required: true, message: '请输入分类名称', trigger: 'blur' }],
  slug: [{ required: true, message: '请输入分类别名', trigger: 'blur' }]
}

// 分类树选项（排除当前编辑的分类）
const categoryTreeOptions = computed(() => {
  // 深拷贝整个分类列表，避免引用问题
  const clonedList = JSON.parse(JSON.stringify(categoryList.value))

  const filterTree = (items) => {
    return items
      .filter(item => item.id !== form.id)
      .map(item => {
        const newItem = { ...item }
        if (item.children) {
          newItem.children = filterTree(item.children)
        }
        return newItem
      })
  }
  return filterTree(clonedList)
})

// 获取分类列表
const fetchCategories = async () => {
  loading.value = true
  try {
    const params = {}
    if (currentSiteId.value) {
      params.site_id = currentSiteId.value
    }
    const res = await getCategoryTree(params)
    // 深拷贝数据，避免响应式引用问题
    categoryList.value = JSON.parse(JSON.stringify(res.data || []))
  } catch (error) {
    ElMessage.error('获取分类列表失败')
  } finally {
    loading.value = false
  }
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

// 添加
const handleAdd = () => {
  isEdit.value = false
  Object.assign(form, {
    id: null,
    site_id: currentSiteId.value || null,
    parent_id: null,
    name: '',
    slug: '',
    sort: 0,
    template: 'category',
    description: ''
  })
  dialogVisible.value = true
}

// 编辑
const handleEdit = (row) => {
  isEdit.value = true
  // 显式复制每个属性，避免引用共享
  Object.assign(form, {
    id: row.id,
    site_id: row.site_id,
    parent_id: row.parent_id,
    name: row.name,
    slug: row.slug,
    sort: row.sort,
    template: row.template || 'category',
    description: row.description || ''
  })
  dialogVisible.value = true
}

// 提交
const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (valid) {
      saving.value = true
      try {
        // 准备提交的数据，只包含需要的字段
        const submitData = {
          parent_id: form.parent_id || 0,
          name: form.name,
          slug: form.slug,
          sort: form.sort || 0,
          template: form.template || 'category',
          description: form.description || ''
        }

        // 区分创建和编辑模式的站点字段
        if (isEdit.value) {
          submitData.site_id = form.site_id
        } else {
          submitData.site_ids = form.site_ids
        }

        if (isEdit.value) {
          await updateCategory(form.id, submitData)
          ElMessage.success('更新成功')
        } else {
          await createCategory(submitData)
          ElMessage.success('创建成功')
        }
        dialogVisible.value = false
        fetchCategories()
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
    await ElMessageBox.confirm('确定要删除这个分类吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await deleteCategory(id)
    ElMessage.success('删除成功')
    fetchCategories()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('删除失败')
    }
  }
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

// 获取当前站点
const fetchCurrentSite = async () => {
  try {
    const res = await getCurrentSite()
    if (res.data) {
      currentSiteId.value = res.data.id
    }
  } catch (error) {
    console.error('获取当前站点失败:', error)
  }
}

// 站点切换
const handleSiteChange = () => {
  fetchCategories()
}

// 全选站点
const selectAllSites = () => {
  form.site_ids = siteOptions.value.map(site => site.id)
}

// 取消全选站点
const deselectAllSites = () => {
  form.site_ids = []
}

onMounted(() => {
  fetchSiteOptions()
  // fetchCurrentSite() // 默认显示全部站点，不自动选择当前站点
  fetchCategories()
  fetchTemplates()
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

.header-right {
  display: flex;
  align-items: center;
}
</style>
