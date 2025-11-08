<template>
  <div class="robots-container">
    <el-row :gutter="20">
      <!-- 左侧：配置列表 -->
      <el-col :span="10">
        <el-card shadow="hover">
          <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center">
              <span>Robots配置列表</span>
              <el-button type="primary" size="small" @click="handleAdd">添加配置</el-button>
            </div>
          </template>

          <el-table :data="list" v-loading="loading" @row-click="handleRowClick" highlight-current-row>
            <el-table-column prop="name" label="名称" min-width="120" />
            <el-table-column label="状态" width="80">
              <template #default="{ row }">
                <el-tag :type="row.is_active ? 'success' : 'info'" size="small">
                  {{ row.is_active ? '启用' : '禁用' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column label="操作" width="150">
              <template #default="{ row }">
                <el-button v-if="!row.is_active" link type="primary" size="small"
                           @click.stop="handleActivate(row)">启用
                </el-button>
                <el-button link type="primary" size="small" @click.stop="handleEdit(row)">编辑</el-button>
                <el-button v-if="!row.is_active" link type="danger" size="small"
                           @click.stop="handleDelete(row)">删除
                </el-button>
              </template>
            </el-table-column>
          </el-table>
        </el-card>

        <!-- 预设模板 -->
        <el-card shadow="hover" style="margin-top: 20px">
          <template #header>
            <span>预设模板</span>
          </template>
          <div v-for="(template, key) in templates" :key="key" class="template-item">
            <div class="template-info">
              <div class="template-name">{{ template.name }}</div>
              <div class="template-desc">{{ template.description }}</div>
            </div>
            <el-button size="small" @click="applyTemplate(key)">应用</el-button>
          </div>
        </el-card>
      </el-col>

      <!-- 右侧：配置详情 -->
      <el-col :span="14">
        <el-card shadow="hover">
          <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center">
              <span>{{ formMode === 'add' ? '新建配置' : '编辑配置' }}</span>
              <div>
                <el-button size="small" @click="handleValidate">验证</el-button>
                <el-button v-if="form.is_active" type="success" size="small" @click="handleGenerate">
                  生成robots.txt
                </el-button>
              </div>
            </div>
          </template>

          <el-form :model="form" :rules="rules" ref="formRef" label-width="100px">
            <el-form-item label="配置名称" prop="name">
              <el-input v-model="form.name" placeholder="如：默认配置" />
            </el-form-item>

            <el-form-item label="描述">
              <el-input v-model="form.description" placeholder="配置说明" />
            </el-form-item>

            <el-form-item label="配置内容" prop="content">
              <el-input
                v-model="form.content"
                type="textarea"
                :rows="15"
                placeholder="User-agent: *&#10;Disallow: /admin/&#10;&#10;Sitemap: /sitemap.xml"
                style="font-family: monospace"
              />
              <div class="form-tip">
                基本语法：User-agent指定爬虫，Disallow禁止抓取路径，Allow允许抓取，Sitemap指定sitemap位置
              </div>
            </el-form-item>

            <el-form-item label="状态">
              <el-switch v-model="form.is_active" :active-value="1" :inactive-value="0" />
              <span style="margin-left: 10px; color: #999; font-size: 12px">
                启用后会自动禁用其他配置
              </span>
            </el-form-item>

            <el-form-item>
              <el-button type="primary" @click="submitForm" :loading="submitting">保存配置</el-button>
              <el-button @click="resetForm">重置</el-button>
            </el-form-item>
          </el-form>

          <!-- 验证结果 -->
          <el-alert v-if="validationResult" :type="validationResult.valid ? 'success' : 'error'"
                    :closable="false" style="margin-top: 10px">
            <div v-if="validationResult.valid">配置格式正确</div>
            <div v-else>
              <div><strong>配置格式错误：</strong></div>
              <div v-for="(error, index) in validationResult.errors" :key="index" style="margin-top: 5px">
                • {{ error }}
              </div>
            </div>
          </el-alert>
        </el-card>

        <!-- 当前文件内容 -->
        <el-card shadow="hover" style="margin-top: 20px">
          <template #header>
            <div style="display: flex; justify-content: space-between; align-items: center">
              <span>当前robots.txt文件</span>
              <el-button size="small" @click="loadCurrentFile">刷新</el-button>
            </div>
          </template>
          <div v-if="currentFile.exists">
            <div style="margin-bottom: 10px; font-size: 12px; color: #999">
              文件路径: {{ currentFile.file }}<br>
              修改时间: {{ currentFile.modified_time }}
            </div>
            <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 300px; overflow: auto">{{ currentFile.content }}</pre>
          </div>
          <el-empty v-else description="文件不存在" />
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getSeoRobotList,
  getSeoRobot,
  createSeoRobot,
  updateSeoRobot,
  deleteSeoRobot,
  activateSeoRobot,
  validateRobotContent,
  getRobotTemplates,
  applyRobotTemplate,
  generateRobotFile,
  getCurrentRobotFile
} from '@/api/seoRobot'

const loading = ref(false)
const submitting = ref(false)
const list = ref([])
const templates = ref({})
const currentFile = ref({ exists: false })

const formMode = ref('add')
const form = reactive({
  id: null,
  name: '',
  description: '',
  content: '',
  is_active: 0
})

const rules = {
  name: [{ required: true, message: '请输入配置名称', trigger: 'blur' }],
  content: [{ required: true, message: '请输入配置内容', trigger: 'blur' }]
}

const formRef = ref(null)
const validationResult = ref(null)

const loadList = async () => {
  loading.value = true
  try {
    const res = await getSeoRobotList({ per_page: 100 })
    list.value = res.data.data
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  } finally {
    loading.value = false
  }
}

const loadTemplates = async () => {
  try {
    const res = await getRobotTemplates()
    templates.value = res.data
  } catch (error) {
    console.error('加载模板失败', error)
  }
}

const loadCurrentFile = async () => {
  try {
    const res = await getCurrentRobotFile()
    currentFile.value = res.data
  } catch (error) {
    console.error('加载文件失败', error)
  }
}

const handleAdd = () => {
  formMode.value = 'add'
  Object.assign(form, {
    id: null,
    name: '',
    description: '',
    content: '',
    is_active: 0
  })
  validationResult.value = null
}

const handleEdit = async (row) => {
  formMode.value = 'edit'
  try {
    const res = await getSeoRobot(row.id)
    Object.assign(form, res.data)
    validationResult.value = null
  } catch (error) {
    ElMessage.error(error.message || '加载失败')
  }
}

const handleRowClick = (row) => {
  handleEdit(row)
}

const handleDelete = (row) => {
  ElMessageBox.confirm('确定要删除这个配置吗？', '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await deleteSeoRobot(row.id)
      ElMessage.success('删除成功')
      loadList()
      if (form.id === row.id) {
        resetForm()
      }
    } catch (error) {
      ElMessage.error(error.message || '删除失败')
    }
  })
}

const handleActivate = (row) => {
  ElMessageBox.confirm('确定要启用这个配置吗？这将禁用其他配置', '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await activateSeoRobot(row.id)
      ElMessage.success('启用成功')
      loadList()
    } catch (error) {
      ElMessage.error(error.message || '启用失败')
    }
  })
}

const submitForm = () => {
  formRef.value.validate(async (valid) => {
    if (!valid) return

    submitting.value = true
    try {
      if (formMode.value === 'add') {
        await createSeoRobot(form)
        ElMessage.success('添加成功')
      } else {
        await updateSeoRobot(form.id, form)
        ElMessage.success('更新成功')
      }
      loadList()
    } catch (error) {
      ElMessage.error(error.message || '操作失败')
    } finally {
      submitting.value = false
    }
  })
}

const resetForm = () => {
  handleAdd()
}

const handleValidate = async () => {
  if (!form.content) {
    ElMessage.warning('请输入配置内容')
    return
  }

  try {
    const res = await validateRobotContent(form.content)
    validationResult.value = res.data
  } catch (error) {
    ElMessage.error(error.message || '验证失败')
  }
}

const applyTemplate = async (templateKey) => {
  try {
    const res = await applyRobotTemplate(templateKey)
    form.content = res.data.content
    form.description = res.data.description
    ElMessage.success('模板应用成功')
    validationResult.value = null
  } catch (error) {
    ElMessage.error(error.message || '应用模板失败')
  }
}

const handleGenerate = () => {
  ElMessageBox.confirm('确定要生成robots.txt文件到网站根目录吗？', '提示', {
    type: 'warning'
  }).then(async () => {
    try {
      await generateRobotFile()
      ElMessage.success('robots.txt文件生成成功')
      loadCurrentFile()
    } catch (error) {
      ElMessage.error(error.message || '生成失败')
    }
  })
}

onMounted(() => {
  loadList()
  loadTemplates()
  loadCurrentFile()
})
</script>

<style scoped>
.robots-container {
  padding: 20px;
}

.form-tip {
  font-size: 12px;
  color: #999;
  margin-top: 5px;
}

.template-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border-bottom: 1px solid #eee;
}

.template-item:last-child {
  border-bottom: none;
}

.template-info {
  flex: 1;
}

.template-name {
  font-weight: bold;
  margin-bottom: 5px;
}

.template-desc {
  font-size: 12px;
  color: #999;
}
</style>
