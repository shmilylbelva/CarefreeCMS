<template>
  <div class="template-manager">
    <el-card>
      <template #header>
        <div class="header-actions">
          <h3>模板管理 - {{ packageInfo.name }}</h3>
          <div>
            <el-button @click="$emit('close')">返回</el-button>
            <el-button type="primary" @click="handleAdd">
              <el-icon><Plus /></el-icon>
              添加模板
            </el-button>
          </div>
        </div>
      </template>

      <!-- 模板列表 -->
      <el-table :data="templates" v-loading="loading" border>
        <el-table-column prop="id" label="ID" width="60" />
        <el-table-column prop="name" label="模板名称" min-width="150" />
        <el-table-column prop="template_type" label="模板类型" width="120">
          <template #default="{ row }">
            <el-tag>{{ getTemplateTypeName(row.template_type || row.type) }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="template_path" label="文件路径" min-width="200" show-overflow-tooltip />
        <el-table-column prop="description" label="描述" min-width="200" show-overflow-tooltip />
        <el-table-column label="包内默认" width="100">
          <template #default="{ row }">
            <el-switch
              v-model="row.is_package_default"
              :loading="row.switching"
              @change="handleSetDefault(row)"
            />
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80">
          <template #default="{ row }">
            <el-tag v-if="row.status === 1" type="success" size="small">启用</el-tag>
            <el-tag v-else type="danger" size="small">禁用</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="260" fixed="right">
          <template #default="{ row }">
            <el-button size="small" @click="handleEditContent(row)">编辑内容</el-button>
            <el-button size="small" @click="handleEdit(row)">修改</el-button>
            <el-button size="small" @click="handleCopy(row)">复制</el-button>
            <el-button
              size="small"
              type="danger"
              @click="handleDelete(row)"
            >
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- 添加/编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑模板' : '添加模板'"
      width="600px"
    >
      <el-form ref="formRef" :model="form" :rules="rules" label-width="100px">
        <el-form-item label="模板名称" prop="name">
          <el-input v-model="form.name" placeholder="如：首页模板" />
        </el-form-item>

        <el-form-item label="模板类型" prop="type">
          <el-select v-model="form.type" placeholder="请选择模板类型" @change="handleTypeChange">
            <el-option
              v-for="type in templateTypes"
              :key="type.value"
              :label="type.label"
              :value="type.value"
            >
              <div style="display: flex; align-items: center; gap: 5px;">
                <i :class="type.icon || 'el-icon-document'"></i>
                <span>{{ type.label }}</span>
                <el-tag v-if="type.allow_multiple" size="small" type="info" style="margin-left: auto;">可多个</el-tag>
              </div>
            </el-option>
          </el-select>
          <div class="form-tip" v-if="selectedTypeInfo">
            {{ selectedTypeInfo.description }}
          </div>
        </el-form-item>

        <el-form-item label="文件名称" prop="file">
          <div class="file-input-wrapper">
            <el-input v-model="form.file" placeholder="如：index.html" @blur="checkFileExists">
              <template #append>.html</template>
            </el-input>
            <el-button @click="showFileSelector" type="primary" plain size="small" style="margin-left: 10px;">
              <el-icon><FolderOpened /></el-icon>
              选择文件
            </el-button>
          </div>
          <div v-if="fileCheckResult.checked" style="margin-top: 5px;">
            <el-alert
              v-if="fileCheckResult.exists"
              :title="`文件 ${form.file}.html 已存在，${isEdit ? '将使用现有文件' : '将链接到现有文件，不会覆盖内容'}`"
              type="warning"
              :closable="false"
              show-icon
            />
            <el-alert
              v-else
              :title="`文件 ${form.file}.html 不存在，将创建新文件`"
              type="info"
              :closable="false"
              show-icon
            />
          </div>
          <div class="form-tip">只需输入文件名，无需路径，文件将自动创建在模板包目录下</div>
        </el-form-item>

        <el-form-item label="描述">
          <el-input v-model="form.description" type="textarea" :rows="3" placeholder="模板描述" />
        </el-form-item>

        <el-form-item label="包内默认">
          <el-switch v-model="form.is_package_default" />
          <div class="form-tip">设为默认后，同类型的其他模板将自动取消默认</div>
        </el-form-item>

        <el-form-item label="状态">
          <el-radio-group v-model="form.status">
            <el-radio :label="1">启用</el-radio>
            <el-radio :label="0">禁用</el-radio>
          </el-radio-group>
        </el-form-item>

        <el-form-item label="初始内容" v-if="!isEdit">
          <div style="margin-bottom: 10px;">
            <el-button size="small" @click="useTemplateExample('basic')">基础模板</el-button>
            <el-button size="small" @click="useTemplateExample('extend')">继承模板</el-button>
            <el-button size="small" @click="useTemplateExample('full')">完整示例</el-button>
          </div>
          <el-input
            v-model="form.content"
            type="textarea"
            :rows="12"
            placeholder="输入模板初始内容，或点击上方按钮加载示例"
          />
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
      title="复制模板"
      width="500px"
    >
      <el-form :model="copyForm" label-width="100px">
        <el-form-item label="新名称">
          <el-input v-model="copyForm.name" placeholder="新模板名称" />
        </el-form-item>

        <el-form-item label="新文件名">
          <el-input v-model="copyForm.file" placeholder="new_template.html" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="copyDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleCopyConfirm" :loading="copying">确定</el-button>
      </template>
    </el-dialog>

    <!-- 内容编辑对话框 -->
    <el-dialog
      v-model="contentDialogVisible"
      :title="`编辑模板内容 - ${currentTemplate?.name}`"
      width="90%"
      top="5vh"
    >
      <div class="content-editor">
        <div class="editor-toolbar">
          <el-button size="small" @click="formatCode">格式化代码</el-button>
          <el-button size="small" @click="insertVariable">插入变量</el-button>
          <el-button size="small" @click="insertBlock">插入区块</el-button>
          <el-button size="small" type="primary" @click="saveContent" :loading="saving">保存</el-button>
        </div>
        <el-input
          v-model="templateContent"
          type="textarea"
          :rows="25"
          placeholder="输入模板内容"
          class="code-editor"
        />
        <div class="editor-info">
          <span>文件路径: templates/{{ packageInfo.code }}/{{ currentTemplate?.file || 'unknown.html' }}</span>
        </div>
      </div>
    </el-dialog>

    <!-- 文件选择对话框 -->
    <el-dialog
      v-model="fileSelectorVisible"
      title="选择模板文件"
      width="700px"
    >
      <div v-loading="filesLoading">
        <el-table :data="packageFiles" height="400">
          <el-table-column prop="name" label="文件名" />
          <el-table-column prop="path" label="路径" show-overflow-tooltip />
          <el-table-column prop="size" label="大小" width="100">
            <template #default="{ row }">
              {{ formatFileSize(row.size) }}
            </template>
          </el-table-column>
          <el-table-column prop="registered" label="状态" width="100">
            <template #default="{ row }">
              <el-tag v-if="row.registered" type="warning" size="small">已注册</el-tag>
              <el-tag v-else type="success" size="small">可用</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="操作" width="100">
            <template #default="{ row }">
              <el-button
                size="small"
                @click="selectFile(row)"
                :disabled="isEdit && row.registered && row.template_id !== form.id"
              >
                选择
              </el-button>
            </template>
          </el-table-column>
        </el-table>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, FolderOpened } from '@element-plus/icons-vue'
import request from '@/api/request'

const props = defineProps({
  packageId: {
    type: Number,
    required: true
  },
  packageInfo: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close', 'refresh'])

const loading = ref(false)
const submitting = ref(false)
const copying = ref(false)
const saving = ref(false)
const dialogVisible = ref(false)
const copyDialogVisible = ref(false)
const contentDialogVisible = ref(false)
const fileSelectorVisible = ref(false)
const isEdit = ref(false)
const templates = ref([])
const packageFiles = ref([])
const filesLoading = ref(false)
const fileCheckResult = reactive({
  checked: false,
  exists: false
})
const formRef = ref(null)
const currentTemplate = ref(null)
const templateContent = ref('')
const templateTypes = ref([])
const selectedTypeInfo = computed(() => {
  if (!form.type) return null
  return templateTypes.value.find(t => t.value === form.type)
})

const form = reactive({
  name: '',
  type: 'index',
  template_type: 'index',
  template_key: '',
  file: '',
  description: '',
  is_package_default: false,
  status: 1,
  content: ''
})

const copyForm = reactive({
  id: null,
  name: '',
  file: ''
})

const rules = {
  name: [
    { required: true, message: '请输入模板名称', trigger: 'blur' }
  ],
  type: [
    { required: true, message: '请选择模板类型', trigger: 'change' }
  ],
  file: [
    { required: true, message: '请输入文件名称', trigger: 'blur' },
    { pattern: /^[a-zA-Z0-9_\-]+$/, message: '文件名只能包含字母、数字、下划线和横线', trigger: 'blur' }
  ]
}

// 获取模板类型选项
const fetchTemplateTypes = async () => {
  try {
    const res = await request({
      url: '/template-type/options',
      method: 'get'
    })
    if (res.code === 200) {
      templateTypes.value = res.data || []
    } else {
      console.error('获取模板类型失败:', res.msg)
      // 使用默认类型
      templateTypes.value = [
        { value: 'index', label: '首页', icon: 'el-icon-house' },
        { value: 'category', label: '分类页', icon: 'el-icon-folder' },
        { value: 'article', label: '文章页', icon: 'el-icon-document' },
        { value: 'page', label: '单页', icon: 'el-icon-document-copy' },
        { value: 'search', label: '搜索页', icon: 'el-icon-search' },
        { value: 'tag', label: '标签页', icon: 'el-icon-price-tag' }
      ]
    }
  } catch (error) {
    console.error('获取模板类型失败:', error)
    // 使用默认类型
    templateTypes.value = [
      { value: 'index', label: '首页', icon: 'el-icon-house' },
      { value: 'category', label: '分类页', icon: 'el-icon-folder' },
      { value: 'article', label: '文章页', icon: 'el-icon-document' },
      { value: 'page', label: '单页', icon: 'el-icon-document-copy' },
      { value: 'search', label: '搜索页', icon: 'el-icon-search' },
      { value: 'tag', label: '标签页', icon: 'el-icon-price-tag' }
    ]
  }
}

// 获取模板类型名称
const getTemplateTypeName = (type) => {
  const typeInfo = templateTypes.value.find(t => t.value === type)
  return typeInfo ? typeInfo.label : type
}

// 模板类型改变时
const handleTypeChange = (value) => {
  // 同步更新template_type字段
  form.template_type = value

  const typeInfo = templateTypes.value.find(t => t.value === value)
  if (typeInfo && !isEdit.value) {
    // 根据类型自动生成文件名建议
    if (!form.file || form.file === '') {
      if (typeInfo.allow_multiple) {
        form.file = value  // 允许多个时，使用类型作为基础名称
      } else {
        form.file = value  // 单个时，直接使用类型名
      }
    }
  }
}

// 获取模板列表
const fetchTemplates = async () => {
  loading.value = true
  try {
    const res = await request({
      url: '/template/index',
      method: 'get',
      params: {
        package_id: props.packageId,
        page_size: 100
      }
    })
    // 规范化数据，确保字段一致性
    templates.value = (res.data.list || []).map(item => {
      // 从template_path中提取文件名
      let fileName = ''
      if (item.template_path) {
        const pathParts = item.template_path.split('/')
        fileName = pathParts[pathParts.length - 1]
      }

      return {
        ...item,
        type: item.template_type || item.type || 'index',
        file: fileName || item.file || '',
        // 确保布尔值和数字类型正确
        is_package_default: item.is_package_default === 1 || item.is_package_default === '1' || item.is_package_default === true,
        status: item.status === 1 || item.status === '1' ? 1 : 0
      }
    })
  } catch (error) {
    ElMessage.error(error.message || '获取模板列表失败')
  } finally {
    loading.value = false
  }
}

// 添加模板
const handleAdd = () => {
  isEdit.value = false
  Object.assign(form, {
    name: '',
    type: 'index',
    template_type: 'index',
    template_key: '',
    file: '',
    description: '',
    is_package_default: false,
    status: 1,
    content: ''
  })
  fileCheckResult.checked = false
  fileCheckResult.exists = false
  dialogVisible.value = true
}

// 编辑模板
const handleEdit = (row) => {
  isEdit.value = true
  // 重置form，确保没有遗留的content字段
  Object.keys(form).forEach(key => {
    if (key !== 'content') {
      form[key] = ''
    }
  })
  // 只复制需要编辑的基本信息，不包含content
  Object.assign(form, {
    id: row.id,
    name: row.name,
    type: row.template_type || row.type || 'index',  // 兼容两种字段名
    template_type: row.template_type || row.type || 'index',
    template_key: row.template_key || '',
    file: row.file ? row.file.replace('.html', '') : '',
    description: row.description || '',
    is_package_default: row.is_package_default ? true : false,
    status: row.status === 1 || row.status === '1' ? 1 : 0
  })
  // 删除content字段，避免误传到后端
  delete form.content
  fileCheckResult.checked = false
  fileCheckResult.exists = false
  dialogVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
  if (!formRef.value) return

  await formRef.value.validate(async (valid) => {
    if (!valid) return

    submitting.value = true
    try {
      const data = {
        ...form,
        package_id: props.packageId,
        file: form.file ? (form.file.endsWith('.html') ? form.file : form.file + '.html') : 'template.html',
        template_type: form.template_type || form.type || 'index'
      }

      // 生成template_key（如果没有提供）
      if (!data.template_key) {
        // 使用包ID + 文件名生成唯一key
        const fileName = data.file.replace('.html', '')
        data.template_key = `pkg${props.packageId}_${fileName}_${Date.now()}`
      }

      // 编辑时，确保不发送content字段（除非用户明确要编辑内容）
      if (isEdit.value) {
        // 删除可能存在的content字段
        delete data.content

        await request({
          url: `/template/update/${form.id}`,
          method: 'put',
          data
        })
        ElMessage.success('更新成功')
      } else {
        // 新建时，如果文件已存在且没有提供content，则不发送content
        if (fileCheckResult.exists && !data.content) {
          delete data.content
        }

        await request({
          url: '/template/save',
          method: 'post',
          data
        })
        ElMessage.success('创建成功')
      }
      dialogVisible.value = false
      fetchTemplates()
    } catch (error) {
      ElMessage.error(error.message || '操作失败')
    } finally {
      submitting.value = false
    }
  })
}

// 删除模板
const handleDelete = async (row) => {
  try {
    await ElMessageBox.confirm(`确定要删除模板"${row.name}"吗？`, '提示', {
      type: 'warning'
    })
    await request({
      url: `/template/delete/${row.id}`,
      method: 'delete'
    })
    ElMessage.success('删除成功')
    fetchTemplates()
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error(error.message || '删除失败')
    }
  }
}

// 复制模板
const handleCopy = (row) => {
  copyForm.id = row.id
  copyForm.name = row.name + '_副本'
  copyForm.file = row.file ? row.file.replace('.html', '_copy.html') : 'copy.html'
  copyDialogVisible.value = true
}

// 确认复制
const handleCopyConfirm = async () => {
  if (!copyForm.name || !copyForm.file) {
    ElMessage.error('请填写完整信息')
    return
  }

  copying.value = true
  try {
    await request({
      url: '/template/copy',
      method: 'post',
      data: {
        id: copyForm.id,
        name: copyForm.name,
        file: copyForm.file
      }
    })
    ElMessage.success('复制成功')
    copyDialogVisible.value = false
    fetchTemplates()
  } catch (error) {
    ElMessage.error(error.message || '复制失败')
  } finally {
    copying.value = false
  }
}

// 设置默认模板
const handleSetDefault = async (row) => {
  row.switching = true
  try {
    await request({
      url: `/template/update/${row.id}`,
      method: 'put',
      data: {
        is_package_default: row.is_package_default,
        template_type: row.template_type || row.type  // 确保传递模板类型
      }
    })
    ElMessage.success('设置成功')
    // 如果设为默认，更新其他同类型模板
    if (row.is_package_default) {
      const currentType = row.template_type || row.type
      templates.value.forEach(t => {
        const tType = t.template_type || t.type
        if (t.id !== row.id && tType === currentType) {
          t.is_package_default = false
        }
      })
    }
  } catch (error) {
    row.is_package_default = !row.is_package_default
    ElMessage.error(error.message || '设置失败')
  } finally {
    row.switching = false
  }
}

// 编辑模板内容
const handleEditContent = async (row) => {
  currentTemplate.value = row
  loading.value = true
  try {
    const res = await request({
      url: `/template/read/${row.id}`,
      method: 'get'
    })
    templateContent.value = res.data.content || ''
    contentDialogVisible.value = true
  } catch (error) {
    ElMessage.error(error.message || '获取模板内容失败')
  } finally {
    loading.value = false
  }
}

// 保存模板内容
const saveContent = async () => {
  saving.value = true
  try {
    await request({
      url: '/template/save-content',
      method: 'post',
      data: {
        id: currentTemplate.value.id,
        content: templateContent.value
      }
    })
    ElMessage.success('保存成功')
    contentDialogVisible.value = false
  } catch (error) {
    ElMessage.error(error.message || '保存失败')
  } finally {
    saving.value = false
  }
}

// 格式化代码
const formatCode = () => {
  // 简单的HTML格式化
  try {
    // 这里可以集成更专业的代码格式化库
    ElMessage.info('代码格式化功能开发中')
  } catch (error) {
    ElMessage.error('格式化失败')
  }
}

// 插入变量
const insertVariable = () => {
  const variables = [
    '{{ site.name }}',
    '{{ site.url }}',
    '{{ article.title }}',
    '{{ article.content }}',
    '{{ category.name }}',
    '{{ page.title }}'
  ]
  ElMessageBox.alert(
    variables.map(v => `<div>${v}</div>`).join(''),
    '常用变量',
    {
      dangerouslyUseHTMLString: true
    }
  )
}

// 插入区块
const insertBlock = () => {
  const blocks = [
    '{% block title %}页面标题{% endblock %}',
    '{% block content %}内容区域{% endblock %}',
    '{% for item in list %}...{% endfor %}',
    '{% if condition %}...{% endif %}'
  ]
  ElMessageBox.alert(
    blocks.map(b => `<div>${b}</div>`).join(''),
    '常用区块',
    {
      dangerouslyUseHTMLString: true
    }
  )
}

// 使用模板示例
const useTemplateExample = (type) => {
  const packageCode = props.packageInfo.code
  const examples = {
    basic: `<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ site.name }}</title>
    <link rel="stylesheet" href="{{ base_url }}/templates/${packageCode}/css/style.css">
</head>
<body>
    <header>
        <h1>{{ site.name }}</h1>
    </header>

    <main>
        <!-- 页面内容 -->
    </main>

    <footer>
        <p>&copy; 2024 {{ site.name }}</p>
    </footer>
</body>
</html>`,
    extend: `{% extends "${packageCode}/layout.html" %}

{% block title %}
    {{ page_title }} - {{ site.name }}
{% endblock %}

{% block content %}
    <div class="container">
        <h1>{{ page_title }}</h1>
        <div class="content">
            <!-- 页面具体内容 -->
        </div>
    </div>
{% endblock %}`,
    full: `{% extends "${packageCode}/layout.html" %}

{% block title %}
    {{ article.title }} - {{ site.name }}
{% endblock %}

{% block content %}
    <article class="article-detail">
        <header class="article-header">
            <h1 class="article-title">{{ article.title }}</h1>
            <div class="article-meta">
                <span class="author">作者：{{ article.author }}</span>
                <span class="date">发布时间：{{ article.created_at|date("Y-m-d H:i") }}</span>
                <span class="views">浏览量：{{ article.views }}</span>
            </div>
            <div class="article-tags">
                {% for tag in article.tags %}
                    <a href="{{ url('tag', {id: tag.id}) }}" class="tag">{{ tag.name }}</a>
                {% endfor %}
            </div>
        </header>

        <div class="article-content">
            {{ article.content|raw }}
        </div>

        <footer class="article-footer">
            <div class="article-nav">
                {% if prev_article %}
                    <a href="{{ url('article', {id: prev_article.id}) }}" class="prev">
                        上一篇：{{ prev_article.title }}
                    </a>
                {% endif %}
                {% if next_article %}
                    <a href="{{ url('article', {id: next_article.id}) }}" class="next">
                        下一篇：{{ next_article.title }}
                    </a>
                {% endif %}
            </div>
        </footer>
    </article>

    <!-- 相关文章 -->
    {% if related_articles %}
    <div class="related-articles">
        <h3>相关文章</h3>
        <ul>
            {% for article in related_articles %}
                <li>
                    <a href="{{ url('article', {id: article.id}) }}">{{ article.title }}</a>
                </li>
            {% endfor %}
        </ul>
    </div>
    {% endif %}
{% endblock %}`
  }

  if (examples[type]) {
    form.content = examples[type]
    ElMessage.success('已加载' + (type === 'basic' ? '基础' : type === 'extend' ? '继承' : '完整') + '模板示例')
  }
}

// 检查文件是否存在
const checkFileExists = async () => {
  if (!form.file) {
    fileCheckResult.checked = false
    return
  }

  try {
    const res = await request({
      url: '/template/check-file',
      method: 'get',
      params: {
        package_id: props.packageId,
        file_name: form.file + '.html'
      }
    })

    fileCheckResult.checked = true
    fileCheckResult.exists = res.data.exists

    // 如果是新建模板且文件已存在，提示用户
    if (!isEdit.value && res.data.exists) {
      // 可以选择是否使用现有文件内容
      ElMessageBox.confirm(
        '该文件已存在，是否使用现有文件的内容作为初始内容？',
        '文件已存在',
        {
          confirmButtonText: '使用现有内容',
          cancelButtonText: '保持空白',
          type: 'info'
        }
      ).then(() => {
        // 使用现有内容
        form.content = res.data.content
      }).catch(() => {
        // 保持空白
        delete form.content
      })
    }
  } catch (error) {
    console.error('检查文件失败:', error)
  }
}

// 显示文件选择器
const showFileSelector = async () => {
  filesLoading.value = true
  try {
    const res = await request({
      url: '/template/package-files',
      method: 'get',
      params: {
        package_id: props.packageId
      }
    })
    packageFiles.value = res.data || []
    fileSelectorVisible.value = true
  } catch (error) {
    ElMessage.error('获取文件列表失败')
  } finally {
    filesLoading.value = false
  }
}

// 选择文件
const selectFile = (row) => {
  form.file = row.name.replace(/\.(html|htm|twig|tpl)$/i, '')
  fileCheckResult.checked = true
  fileCheckResult.exists = true
  fileSelectorVisible.value = false

  // 如果是新建模板，询问是否加载文件内容
  if (!isEdit.value && !row.registered) {
    ElMessageBox.confirm(
      '是否加载该文件的内容作为初始内容？',
      '选择文件',
      {
        confirmButtonText: '加载内容',
        cancelButtonText: '不加载',
        type: 'info'
      }
    ).then(async () => {
      // 获取文件内容
      try {
        const res = await request({
          url: '/template/check-file',
          method: 'get',
          params: {
            package_id: props.packageId,
            file_name: row.name
          }
        })
        if (res.data.content) {
          form.content = res.data.content
          ElMessage.success('已加载文件内容')
        }
      } catch (error) {
        ElMessage.error('加载文件内容失败')
      }
    }).catch(() => {
      // 不加载内容
    })
  }
}

// 格式化文件大小
const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  const units = ['B', 'KB', 'MB', 'GB']
  const k = 1024
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + units[i]
}

// 初始化
onMounted(() => {
  fetchTemplateTypes()
  fetchTemplates()
})
</script>

<style scoped>
.template-manager {
  height: 100%;
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

.form-tip {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}

.file-input-wrapper {
  display: flex;
  align-items: center;
  width: 100%;
}

.file-input-wrapper .el-input {
  flex: 1;
}

.content-editor {
  display: flex;
  flex-direction: column;
  height: 600px;
}

.editor-toolbar {
  padding: 10px;
  background: #f5f5f5;
  border-bottom: 1px solid #ddd;
  display: flex;
  gap: 10px;
}

.code-editor {
  flex: 1;
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
  font-size: 14px;
  line-height: 1.5;
}

.code-editor :deep(.el-textarea__inner) {
  font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
  font-size: 14px;
  line-height: 1.5;
}

.editor-info {
  padding: 10px;
  background: #f9f9f9;
  border-top: 1px solid #ddd;
  font-size: 12px;
  color: #666;
}
</style>