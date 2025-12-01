<template>
  <div class="template-package-list">
    <el-card>
      <template #header>
        <div class="header-actions">
          <h3>模板包管理</h3>
          <el-button type="primary" @click="handleAdd">
            <el-icon><Plus /></el-icon>
            添加模板包
          </el-button>
        </div>
      </template>

      <!-- 搜索栏 -->
      <div class="search-bar">
        <el-form :inline="true" :model="searchForm">
          <el-form-item label="名称">
            <el-input v-model="searchForm.name" placeholder="请输入模板包名称" clearable />
          </el-form-item>
          <el-form-item label="代码">
            <el-input v-model="searchForm.code" placeholder="请输入模板包代码" clearable />
          </el-form-item>
          <el-form-item label="状态">
            <el-select v-model="searchForm.status" placeholder="请选择状态" clearable>
              <el-option label="禁用" :value="0" />
              <el-option label="启用" :value="1" />
            </el-select>
          </el-form-item>
          <el-form-item label="类型">
            <el-select v-model="searchForm.is_system" placeholder="请选择类型" clearable>
              <el-option label="系统内置" :value="1" />
              <el-option label="自定义" :value="0" />
            </el-select>
          </el-form-item>
          <el-form-item>
            <el-button type="primary" @click="handleSearch">搜索</el-button>
            <el-button @click="handleReset">重置</el-button>
          </el-form-item>
        </el-form>
      </div>

      <!-- 模板包列表 -->
      <el-table :data="packageList" v-loading="loading" border>
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="code" label="代码" width="150" />
        <el-table-column prop="name" label="名称" min-width="200" />
        <el-table-column prop="description" label="描述" min-width="250" show-overflow-tooltip />
        <el-table-column label="类型" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.is_system" type="warning">系统内置</el-tag>
            <el-tag v-else type="primary">自定义</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag v-if="row.status === 1" type="success">启用</el-tag>
            <el-tag v-else type="danger">禁用</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="version" label="版本" width="100" />
        <el-table-column prop="sort" label="排序" width="80" />
        <el-table-column label="操作" width="320" fixed="right">
          <template #default="{ row }">
            <el-button size="small" @click="handleViewTemplates(row)">模板列表</el-button>
            <el-button size="small" @click="handleEdit(row)">编辑</el-button>
            <el-button size="small" @click="handleCopy(row)">复制</el-button>
            <el-button
              size="small"
              type="danger"
              @click="handleDelete(row.id)"
              :disabled="row.is_system"
            >
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-if="total > 0"
        v-model:current-page="pagination.page"
        v-model:page-size="pagination.page_size"
        :total="total"
        :page-sizes="[20, 50, 100]"
        layout="total, sizes, prev, pager, next"
        @size-change="fetchPackages"
        @current-change="fetchPackages"
        style="margin-top: 20px; justify-content: flex-end;"
      />
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑模板包' : '添加模板包'"
      width="600px"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="120px">
        <el-form-item label="代码" prop="code">
          <el-input v-model="form.code" placeholder="template_package_code" :disabled="isEdit" />
          <div class="form-tip">仅允许字母、数字、下划线，创建后不可修改</div>
        </el-form-item>

        <el-form-item label="名称" prop="name">
          <el-input v-model="form.name" placeholder="模板包名称" />
        </el-form-item>

        <el-form-item label="描述">
          <el-input v-model="form.description" type="textarea" :rows="3" placeholder="模板包描述" />
        </el-form-item>

        <el-form-item label="版本">
          <el-input v-model="form.version" placeholder="1.0.0" />
        </el-form-item>

        <el-form-item label="作者">
          <el-input v-model="form.author" placeholder="作者名称" />
        </el-form-item>

        <el-form-item label="排序">
          <el-input-number v-model="form.sort" :min="0" :max="999" />
        </el-form-item>

        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :label="1">启用</el-radio>
            <el-radio :label="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="默认配置">
          <div style="margin-bottom: 10px;">
            <el-button size="small" @click="useConfigExample('basic')">基础配置示例</el-button>
            <el-button size="small" @click="useConfigExample('color')">配色方案示例</el-button>
            <el-button size="small" @click="useConfigExample('layout')">布局配置示例</el-button>
            <el-button size="small" @click="useConfigExample('full')">完整示例</el-button>
          </div>
          <el-input v-model="defaultConfigStr" type="textarea" :rows="12" placeholder='点击上方按钮加载配置示例，或手动输入JSON格式配置' />
          <div class="form-tip">
            <p><strong>配置说明：</strong>JSON格式，定义站点可自定义的选项和默认值</p>
            <p>• 站点可以基于这些配置进行个性化定制</p>
            <p>• 支持颜色、字体、布局、功能开关等配置项</p>
          </div>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">确定</el-button>
      </template>
    </el-dialog>

    <!-- 复制对话框 -->
    <el-dialog
      v-model="copyDialogVisible"
      title="复制模板包"
      width="500px"
    >
      <el-form :model="copyForm" label-width="100px">
        <el-form-item label="新名称">
          <el-input v-model="copyForm.name" placeholder="新模板包名称" />
        </el-form-item>

        <el-form-item label="新代码">
          <el-input v-model="copyForm.code" placeholder="new_package_code" />
          <div class="form-tip">仅允许字母、数字、下划线</div>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="copyDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleCopyConfirm" :loading="copying">确定</el-button>
      </template>
    </el-dialog>

    <!-- 模板管理组件 -->
    <el-dialog
      v-model="templatesDialogVisible"
      title=""
      width="95%"
      top="3vh"
      :show-close="false"
      :close-on-click-modal="false"
    >
      <template #header>
        <span></span>
      </template>
      <TemplateManager
        v-if="currentPackage"
        :package-id="currentPackage.id"
        :package-info="currentPackage"
        @close="templatesDialogVisible = false"
        @refresh="fetchPackages"
      />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import TemplateManager from './TemplateManager.vue'
import {
  getTemplatePackageList,
  createTemplatePackage,
  updateTemplatePackage,
  deleteTemplatePackage,
  copyTemplatePackage,
  getPackageTemplates
} from '@/api/templatePackage'

const loading = ref(false)
const submitting = ref(false)
const copying = ref(false)
const dialogVisible = ref(false)
const copyDialogVisible = ref(false)
const templatesDialogVisible = ref(false)
const isEdit = ref(false)
const packageList = ref([])
const templates = ref([])
const total = ref(0)
const formRef = ref(null)
const currentPackage = ref(null)

const searchForm = reactive({
  name: '',
  code: '',
  status: '',
  is_system: ''
})

const pagination = reactive({
  page: 1,
  page_size: 20
})

const form = reactive({
  code: '',
  name: '',
  description: '',
  version: '1.0.0',
  author: '',
  sort: 0,
  status: 1,
  default_config: {}
})

const copyForm = reactive({
  id: null,
  name: '',
  code: ''
})

const defaultConfigStr = ref('{}')

const rules = {
  code: [
    { required: true, message: '请输入模板包代码', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9_]+$/, message: '仅允许字母、数字、下划线', trigger: 'blur' }
  ],
  name: [
    { required: true, message: '请输入模板包名称', trigger: 'blur' }
  ]
}

// 获取模板包列表
const fetchPackages = async () => {
  loading.value = true
  try {
    const params = {
      ...searchForm,
      page: pagination.page,
      page_size: pagination.page_size
    }
    const res = await getTemplatePackageList(params)
    packageList.value = res.data.list
    total.value = res.data.total
  } catch (error) {
    ElMessage.error(error.message || '获取模板包列表失败')
  } finally {
    loading.value = false
  }
}

// 搜索
const handleSearch = () => {
  pagination.page = 1
  fetchPackages()
}

// 重置
const handleReset = () => {
  Object.assign(searchForm, {
    name: '',
    code: '',
    status: '',
    is_system: ''
  })
  handleSearch()
}

// 添加
const handleAdd = () => {
  isEdit.value = false
  Object.assign(form, {
    code: '',
    name: '',
    description: '',
    version: '1.0.0',
    author: '',
    sort: 0,
    status: 1,
    default_config: {}
  })
  defaultConfigStr.value = '{}'
  dialogVisible.value = true
}

// 编辑
const handleEdit = (row) => {
  isEdit.value = true
  Object.assign(form, {
    id: row.id,
    code: row.code,
    name: row.name,
    description: row.description || '',
    version: row.version || '1.0.0',
    author: row.author || '',
    sort: row.sort || 0,
    status: row.status
  })
  defaultConfigStr.value = JSON.stringify(row.default_config || {}, null, 2)
  dialogVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (!valid) return

    // 解析 default_config JSON
    try {
      form.default_config = JSON.parse(defaultConfigStr.value)
    } catch (e) {
      ElMessage.error('默认配置JSON格式错误')
      return
    }

    submitting.value = true
    try {
      if (isEdit.value) {
        await updateTemplatePackage(form.id, form)
        ElMessage.success('更新成功')
      } else {
        await createTemplatePackage(form)
        ElMessage.success('创建成功')
      }
      dialogVisible.value = false
      fetchPackages()
    } catch (error) {
      ElMessage.error(error.message || '操作失败')
    } finally {
      submitting.value = false
    }
  })
}

// 删除
const handleDelete = async (id) => {
  try {
    await ElMessageBox.confirm('确定要删除该模板包吗？删除后无法恢复。', '提示', {
      type: 'warning'
    })
    await deleteTemplatePackage(id)
    ElMessage.success('删除成功')
    fetchPackages()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

// 复制
const handleCopy = (row) => {
  copyForm.id = row.id
  copyForm.name = row.name + '_副本'
  copyForm.code = row.code + '_copy_' + Date.now()
  copyDialogVisible.value = true
}

// 确认复制
const handleCopyConfirm = async () => {
  if (!copyForm.name || !copyForm.code) {
    ElMessage.error('请填写完整信息')
    return
  }

  copying.value = true
  try {
    await copyTemplatePackage(copyForm.id, copyForm.name, copyForm.code)
    ElMessage.success('复制成功')
    copyDialogVisible.value = false
    fetchPackages()
  } catch (error) {
    ElMessage.error(error.message || '复制失败')
  } finally {
    copying.value = false
  }
}

// 查看模板列表
const handleViewTemplates = async (row) => {
  currentPackage.value = row
  templatesDialogVisible.value = true
}

// 使用配置示例
const useConfigExample = (type) => {
  const examples = {
    basic: {
      site_name: "我的网站",
      logo: "/static/logo.png",
      favicon: "/static/favicon.ico",
      keywords: "新闻,资讯",
      description: "这是一个基于逍遥CMS构建的网站"
    },
    color: {
      primary_color: "#409EFF",
      success_color: "#67C23A",
      warning_color: "#E6A23C",
      danger_color: "#F56C6C",
      text_color: "#303133",
      link_color: "#409EFF",
      border_color: "#DCDFE6",
      background_color: "#FFFFFF"
    },
    layout: {
      header_height: "60px",
      footer_height: "120px",
      sidebar_width: "200px",
      content_width: "1200px",
      show_breadcrumb: true,
      show_tags: true,
      fixed_header: true,
      page_animation: "fade"
    },
    full: {
      // 基础信息
      site_name: "我的网站",
      logo: "/static/logo.png",
      favicon: "/static/favicon.ico",
      keywords: "新闻,资讯,CMS",
      description: "这是一个基于逍遥CMS构建的网站",

      // 配色方案
      colors: {
        primary: "#409EFF",
        success: "#67C23A",
        warning: "#E6A23C",
        danger: "#F56C6C",
        text: "#303133",
        link: "#409EFF"
      },

      // 布局配置
      layout: {
        header_height: "60px",
        footer_height: "120px",
        sidebar_width: "200px",
        content_width: "1200px",
        fixed_header: true
      },

      // 字体配置
      typography: {
        font_family: "Microsoft YaHei, Arial, sans-serif",
        font_size: "14px",
        title_font_size: "24px",
        h1_size: "32px",
        h2_size: "28px",
        h3_size: "24px"
      },

      // 功能开关
      features: {
        show_breadcrumb: true,
        show_sidebar: true,
        show_tags: true,
        enable_search: true,
        enable_comment: true,
        enable_share: true
      },

      // 列表配置
      list: {
        articles_per_page: 20,
        show_thumbnail: true,
        show_excerpt: true,
        excerpt_length: 200,
        date_format: "Y-m-d H:i:s"
      },

      // 文章配置
      article: {
        show_author: true,
        show_date: true,
        show_views: true,
        show_category: true,
        show_tags: true,
        show_related: true,
        related_count: 5
      }
    }
  }

  if (examples[type]) {
    defaultConfigStr.value = JSON.stringify(examples[type], null, 2)
    ElMessage.success('已加载' + (type === 'basic' ? '基础' : type === 'color' ? '配色' : type === 'layout' ? '布局' : '完整') + '配置示例')
  }
}

// 初始化
fetchPackages()
</script>

<style scoped>
.template-package-list {
  padding: 20px;
}

.header-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions h3 {
  margin: 0;
  font-size: 18px;
}

.search-bar {
  margin-bottom: 20px;
}

.form-tip {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}

.form-tip p {
  margin: 5px 0;
  line-height: 1.6;
}

.form-tip strong {
  color: #666;
}
</style>
