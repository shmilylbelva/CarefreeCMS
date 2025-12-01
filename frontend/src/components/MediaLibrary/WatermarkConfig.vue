<template>
  <div class="watermark-config">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>水印管理</span>
          <el-button type="primary" size="small" @click="showPresetDialog()">
            <el-icon><Plus /></el-icon>
            新建预设
          </el-button>
        </div>
      </template>

      <!-- 水印预设列表 -->
      <el-table :data="presetList" v-loading="loading">
        <el-table-column prop="name" label="预设名称" />
        <el-table-column prop="type" label="水印类型" width="100">
          <template #default="{ row }">
            <el-tag :type="getTypeTagType(row.type)">
              {{ getTypeText(row.type) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="position" label="位置" width="120">
          <template #default="{ row }">
            {{ getPositionText(row.position) }}
          </template>
        </el-table-column>
        <el-table-column prop="opacity" label="透明度" width="100">
          <template #default="{ row }">
            {{ row.opacity }}%
          </template>
        </el-table-column>
        <el-table-column label="状态" width="100">
          <template #default="{ row }">
            <el-tag v-if="row.is_default" type="success" size="small">默认</el-tag>
            <el-tag v-else-if="row.is_active" type="info" size="small">启用</el-tag>
            <el-tag v-else type="info" size="small" effect="plain">禁用</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="预览" width="100">
          <template #default="{ row }">
            <el-button link size="small" @click="showPreview(row)">
              <el-icon><View /></el-icon>
              预览
            </el-button>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="200" fixed="right">
          <template #default="{ row }">
            <el-button link size="small" @click="showPresetDialog(row)">编辑</el-button>
            <el-button
              link
              size="small"
              :type="row.is_default ? 'info' : 'success'"
              @click="handleSetDefault(row)"
              :disabled="row.is_default"
            >
              {{ row.is_default ? '默认' : '设为默认' }}
            </el-button>
            <el-button link size="small" type="danger" @click="handleDelete(row)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-model:current-page="currentPage"
        v-model:page-size="pageSize"
        :total="total"
        layout="total, prev, pager, next"
        @change="loadPresets"
        style="margin-top: 15px; justify-content: center;"
      />
    </el-card>

    <!-- 预设编辑对话框 -->
    <el-dialog
      v-model="dialogVisible"
      :title="isEdit ? '编辑预设' : '新建预设'"
      width="600px"
      :close-on-click-modal="false"
    >
      <el-form
        ref="formRef"
        :model="formData"
        :rules="formRules"
        label-width="100px"
      >
        <el-form-item label="预设名称" prop="name">
          <el-input v-model="formData.name" placeholder="请输入预设名称" />
        </el-form-item>

        <el-form-item label="水印类型" prop="type">
          <el-radio-group v-model="formData.type">
            <el-radio value="text">文字水印</el-radio>
            <el-radio value="image">图片水印</el-radio>
            <el-radio value="tiled">平铺水印</el-radio>
          </el-radio-group>
        </el-form-item>

        <!-- 文字水印配置 -->
        <template v-if="formData.type === 'text' || formData.type === 'tiled'">
          <el-form-item label="水印文字" prop="text_content">
            <el-input
              v-model="formData.text_content"
              placeholder="请输入水印文字"
            />
          </el-form-item>

          <el-form-item label="文字大小">
            <el-input-number v-model="formData.text_size" :min="12" :max="72" />
          </el-form-item>

          <el-form-item label="文字颜色">
            <el-color-picker v-model="formData.text_color" show-alpha />
          </el-form-item>
        </template>

        <!-- 图片水印配置 -->
        <template v-if="formData.type === 'image'">
          <el-form-item label="水印图片" prop="image_path">
            <el-input v-model="formData.image_path" placeholder="请选择水印图片">
              <template #append>
                <el-button @click="selectWatermarkImage">选择图片</el-button>
              </template>
            </el-input>
          </el-form-item>

          <el-form-item label="缩放比例">
            <el-slider v-model="formData.scale" :min="10" :max="200" :step="10" />
            <span>{{ formData.scale }}%</span>
          </el-form-item>
        </template>

        <!-- 通用配置 -->
        <el-form-item label="位置" v-if="formData.type !== 'tiled'">
          <el-select v-model="formData.position">
            <el-option label="左上角" value="top-left" />
            <el-option label="右上角" value="top-right" />
            <el-option label="左下角" value="bottom-left" />
            <el-option label="右下角" value="bottom-right" />
            <el-option label="居中" value="center" />
          </el-select>
        </el-form-item>

        <el-form-item label="偏移量" v-if="formData.type !== 'tiled'">
          <el-row :gutter="10">
            <el-col :span="12">
              <el-input-number v-model="formData.offset_x" :min="0" placeholder="X" />
            </el-col>
            <el-col :span="12">
              <el-input-number v-model="formData.offset_y" :min="0" placeholder="Y" />
            </el-col>
          </el-row>
        </el-form-item>

        <el-form-item label="平铺间距" v-if="formData.type === 'tiled'">
          <el-input-number v-model="formData.tile_spacing" :min="50" :max="300" />
        </el-form-item>

        <el-form-item label="透明度">
          <el-slider v-model="formData.opacity" :min="0" :max="100" />
          <span>{{ formData.opacity }}%</span>
        </el-form-item>

        <el-form-item label="设为默认">
          <el-switch v-model="formData.is_default" :active-value="1" :inactive-value="0" />
        </el-form-item>

        <el-form-item label="启用">
          <el-switch v-model="formData.is_active" :active-value="1" :inactive-value="0" />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">
          {{ isEdit ? '保存' : '创建' }}
        </el-button>
      </template>
    </el-dialog>

    <!-- 预览对话框 -->
    <el-dialog v-model="previewVisible" title="水印预览" width="800px">
      <div class="preview-container">
        <p class="preview-hint">预览功能开发中...</p>
        <div class="preview-info">
          <el-descriptions :column="2" border>
            <el-descriptions-item label="预设名称">{{ previewData?.name }}</el-descriptions-item>
            <el-descriptions-item label="水印类型">{{ getTypeText(previewData?.type) }}</el-descriptions-item>
            <el-descriptions-item label="位置">{{ getPositionText(previewData?.position) }}</el-descriptions-item>
            <el-descriptions-item label="透明度">{{ previewData?.opacity }}%</el-descriptions-item>
            <el-descriptions-item label="文字内容" v-if="previewData?.text_content" :span="2">
              {{ previewData?.text_content }}
            </el-descriptions-item>
          </el-descriptions>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, View } from '@element-plus/icons-vue'
import {
  getPresets,
  createPreset,
  updatePreset,
  deletePreset
} from '@/api/watermark'

const emit = defineEmits(['change'])

// 数据
const presetList = ref([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)

// 对话框
const dialogVisible = ref(false)
const isEdit = ref(false)
const submitting = ref(false)
const formRef = ref(null)

const formData = reactive({
  id: null,
  name: '',
  type: 'text',
  text_content: '',
  text_font: '',
  text_size: 20,
  text_color: '#000000',
  image_path: '',
  position: 'bottom-right',
  offset_x: 10,
  offset_y: 10,
  opacity: 50,
  scale: 100,
  tile_spacing: 100,
  is_default: 0,
  is_active: 1
})

const formRules = {
  name: [{ required: true, message: '请输入预设名称', trigger: 'blur' }],
  type: [{ required: true, message: '请选择水印类型', trigger: 'change' }],
  text_content: [
    { required: true, message: '请输入水印文字', trigger: 'blur',
      validator: (rule, value, callback) => {
        if ((formData.type === 'text' || formData.type === 'tiled') && !value) {
          callback(new Error('请输入水印文字'))
        } else {
          callback()
        }
      }
    }
  ],
  image_path: [
    { required: true, message: '请选择水印图片', trigger: 'change',
      validator: (rule, value, callback) => {
        if (formData.type === 'image' && !value) {
          callback(new Error('请选择水印图片'))
        } else {
          callback()
        }
      }
    }
  ]
}

// 预览
const previewVisible = ref(false)
const previewData = ref(null)

// 加载预设列表
const loadPresets = async () => {
  loading.value = true
  try {
    const res = await getPresets({
      page: currentPage.value,
      pageSize: pageSize.value
    })
    presetList.value = res.data?.list || res.data || []
    total.value = res.data?.total || presetList.value.length
  } catch (error) {
    ElMessage.error('加载预设失败')
  } finally {
    loading.value = false
  }
}

// 显示预设对话框
const showPresetDialog = (preset = null) => {
  isEdit.value = !!preset

  if (preset) {
    Object.assign(formData, {
      id: preset.id,
      name: preset.name,
      type: preset.type,
      text_content: preset.text_content || '',
      text_font: preset.text_font || '',
      text_size: preset.text_size || 20,
      text_color: preset.text_color || '#000000',
      image_path: preset.image_path || '',
      position: preset.position || 'bottom-right',
      offset_x: preset.offset_x || 10,
      offset_y: preset.offset_y || 10,
      opacity: preset.opacity || 50,
      scale: preset.scale || 100,
      tile_spacing: preset.tile_spacing || 100,
      is_default: preset.is_default || 0,
      is_active: preset.is_active ?? 1
    })
  } else {
    Object.assign(formData, {
      id: null,
      name: '',
      type: 'text',
      text_content: '',
      text_font: '',
      text_size: 20,
      text_color: '#000000',
      image_path: '',
      position: 'bottom-right',
      offset_x: 10,
      offset_y: 10,
      opacity: 50,
      scale: 100,
      tile_spacing: 100,
      is_default: 0,
      is_active: 1
    })
  }

  dialogVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
  await formRef.value?.validate()

  submitting.value = true
  try {
    if (isEdit.value) {
      await updatePreset(formData.id, formData)
      ElMessage.success('更新成功')
    } else {
      await createPreset(formData)
      ElMessage.success('创建成功')
    }
    dialogVisible.value = false
    loadPresets()
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '操作失败')
  } finally {
    submitting.value = false
  }
}

// 设为默认
const handleSetDefault = async (row) => {
  try {
    await updatePreset(row.id, { is_default: 1 })
    ElMessage.success('设置成功')
    loadPresets()
  } catch (error) {
    ElMessage.error(error.message || '设置失败')
  }
}

// 删除预设
const handleDelete = async (row) => {
  await ElMessageBox.confirm(
    `确定要删除预设"${row.name}"吗？`,
    '删除确认',
    { type: 'warning' }
  )

  try {
    await deletePreset(row.id)
    ElMessage.success('删除成功')
    loadPresets()
    emit('change')
  } catch (error) {
    ElMessage.error(error.message || '删除失败')
  }
}

// 选择水印图片
const selectWatermarkImage = () => {
  ElMessage.info('请从媒体库选择图片（功能待集成）')
}

// 显示预览
const showPreview = (row) => {
  previewData.value = row
  previewVisible.value = true
}

// 获取类型文本
const getTypeText = (type) => {
  const types = {
    text: '文字',
    image: '图片',
    tiled: '平铺'
  }
  return types[type] || type
}

// 获取类型标签类型
const getTypeTagType = (type) => {
  const types = {
    text: 'primary',
    image: 'success',
    tiled: 'warning'
  }
  return types[type] || 'info'
}

// 获取位置文本
const getPositionText = (position) => {
  const positions = {
    'top-left': '左上角',
    'top-right': '右上角',
    'bottom-left': '左下角',
    'bottom-right': '右下角',
    'center': '居中'
  }
  return positions[position] || position
}

onMounted(() => {
  loadPresets()
})
</script>

<style scoped>
.watermark-config {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.preview-container {
  min-height: 300px;
}

.preview-hint {
  text-align: center;
  color: #999;
  margin-bottom: 20px;
}

.preview-info {
  margin-top: 20px;
}
</style>
