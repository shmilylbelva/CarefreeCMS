<template>
  <div class="thumbnail-presets">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>缩略图预设管理</span>
          <el-button type="primary" size="small" @click="showDialog()">
            <el-icon><Plus /></el-icon>
            新建预设
          </el-button>
        </div>
      </template>

      <!-- 预设列表 -->
      <el-table :data="presets" v-loading="loading" border>
        <el-table-column prop="name" label="预设标识" width="120" />
        <el-table-column prop="display_name" label="显示名称" width="150" />
        <el-table-column label="尺寸" width="150">
          <template #default="{ row }">
            <span>{{ row.width }} × {{ row.height }}</span>
          </template>
        </el-table-column>
        <el-table-column label="缩放模式" width="120">
          <template #default="{ row }">
            <el-tag :type="getModeTagType(row.mode)" size="small">
              {{ getModeText(row.mode) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="quality" label="质量" width="80">
          <template #default="{ row }">
            {{ row.quality }}%
          </template>
        </el-table-column>
        <el-table-column prop="format" label="格式" width="80">
          <template #default="{ row }">
            <el-tag size="small">{{ (row.format || 'auto').toUpperCase() }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="自动生成" width="100" align="center">
          <template #default="{ row }">
            <el-switch
              v-model="row.is_auto_generate"
              :active-value="1"
              :inactive-value="0"
              @change="handleAutoGenerateChange(row)"
              :disabled="row.is_builtin === 1"
            />
          </template>
        </el-table-column>
        <el-table-column label="状态" width="80" align="center">
          <template #default="{ row }">
            <el-tag v-if="row.is_builtin" type="info" size="small">内置</el-tag>
            <el-tag v-else type="success" size="small">自定义</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="description" label="说明" min-width="200" show-overflow-tooltip />
        <el-table-column label="操作" width="180" fixed="right">
          <template #default="{ row }">
            <el-button link size="small" @click="showDialog(row)">编辑</el-button>
            <el-button
              link
              size="small"
              type="danger"
              @click="handleDelete(row)"
              :disabled="row.is_builtin === 1"
            >
              删除
            </el-button>
          </template>
        </el-table-column>
      </el-table>

      <el-pagination
        v-if="total > pageSize"
        v-model:current-page="page"
        v-model:page-size="pageSize"
        :total="total"
        layout="total, prev, pager, next"
        @change="loadPresets"
        style="margin-top: 15px; justify-content: center;"
      />
    </el-card>

    <!-- 编辑对话框 -->
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
        label-width="120px"
      >
        <el-form-item label="预设标识" prop="name">
          <el-input
            v-model="formData.name"
            placeholder="如: medium, large, small"
            :disabled="isEdit && formData.is_builtin === 1"
          />
          <div class="form-tip">用于代码中引用此预设的唯一标识</div>
        </el-form-item>

        <el-form-item label="显示名称" prop="display_name">
          <el-input v-model="formData.display_name" placeholder="如: 中等尺寸" />
        </el-form-item>

        <el-form-item label="尺寸设置">
          <el-row :gutter="10">
            <el-col :span="12">
              <el-form-item prop="width" label-width="0">
                <el-input-number
                  v-model="formData.width"
                  :min="0"
                  :max="4000"
                  placeholder="宽度"
                  style="width: 100%;"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item prop="height" label-width="0">
                <el-input-number
                  v-model="formData.height"
                  :min="0"
                  :max="4000"
                  placeholder="高度"
                  style="width: 100%;"
                />
              </el-form-item>
            </el-col>
          </el-row>
          <div class="form-tip">宽度或高度为0表示自动计算（保持比例）</div>
        </el-form-item>

        <el-form-item label="缩放模式" prop="mode">
          <el-select v-model="formData.mode" style="width: 100%;">
            <el-option value="fit" label="适应（等比缩放，不裁剪）">
              <div>
                <div><strong>适应（Fit）</strong></div>
                <div style="font-size: 12px; color: #999;">等比缩放，完整显示，可能有留白</div>
              </div>
            </el-option>
            <el-option value="fill" label="填充（等比缩放，可能裁剪）">
              <div>
                <div><strong>填充（Fill）</strong></div>
                <div style="font-size: 12px; color: #999;">等比缩放，填满区域，可能裁剪</div>
              </div>
            </el-option>
            <el-option value="crop" label="裁剪（居中裁剪到指定尺寸）">
              <div>
                <div><strong>裁剪（Crop）</strong></div>
                <div style="font-size: 12px; color: #999;">从中心裁剪到精确尺寸</div>
              </div>
            </el-option>
            <el-option value="exact" label="精确（强制缩放到指定尺寸）">
              <div>
                <div><strong>精确（Exact）</strong></div>
                <div style="font-size: 12px; color: #999;">强制缩放，可能变形</div>
              </div>
            </el-option>
          </el-select>
        </el-form-item>

        <el-form-item label="图片质量" prop="quality">
          <el-slider
            v-model="formData.quality"
            :min="1"
            :max="100"
            :step="1"
            show-input
          />
          <div class="form-tip">质量越高，文件越大；建议85-95</div>
        </el-form-item>

        <el-form-item label="输出格式" prop="format">
          <el-radio-group v-model="formData.format">
            <el-radio value="">自动</el-radio>
            <el-radio value="jpg">JPG</el-radio>
            <el-radio value="png">PNG</el-radio>
            <el-radio value="webp">WebP</el-radio>
          </el-radio-group>
          <div class="form-tip">自动将保持原图格式</div>
        </el-form-item>

        <el-form-item label="自动生成">
          <el-switch
            v-model="formData.is_auto_generate"
            :active-value="1"
            :inactive-value="0"
          />
          <div class="form-tip">上传图片时自动生成此规格的缩略图</div>
        </el-form-item>

        <el-form-item label="说明">
          <el-input
            v-model="formData.description"
            type="textarea"
            :rows="3"
            placeholder="请输入预设的用途说明"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="dialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleSubmit" :loading="submitting">
          {{ isEdit ? '保存' : '创建' }}
        </el-button>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import {
  getPresets,
  createPreset,
  updatePreset,
  deletePreset
} from '@/api/thumbnail'

const emit = defineEmits(['change'])

// 数据
const presets = ref([])
const loading = ref(false)
const page = ref(1)
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
  display_name: '',
  width: 800,
  height: 600,
  mode: 'fit',
  quality: 85,
  format: '',
  is_auto_generate: 1,
  description: '',
  is_builtin: 0
})

const formRules = {
  name: [
    { required: true, message: '请输入预设标识', trigger: 'blur' },
    { pattern: /^[a-z0-9_-]+$/, message: '只能包含小写字母、数字、下划线和横杠', trigger: 'blur' }
  ],
  display_name: [
    { required: true, message: '请输入显示名称', trigger: 'blur' }
  ],
  mode: [
    { required: true, message: '请选择缩放模式', trigger: 'change' }
  ],
  quality: [
    { required: true, message: '请设置图片质量', trigger: 'blur' }
  ]
}

// 加载预设列表
const loadPresets = async () => {
  loading.value = true
  try {
    const res = await getPresets({
      page: page.value,
      pageSize: pageSize.value
    })
    presets.value = res.data?.list || res.data || []
    total.value = res.data?.total || presets.value.length
  } catch (error) {
    ElMessage.error('加载预设失败')
  } finally {
    loading.value = false
  }
}

// 显示对话框
const showDialog = (preset = null) => {
  isEdit.value = !!preset

  if (preset) {
    Object.assign(formData, {
      id: preset.id,
      name: preset.name,
      display_name: preset.display_name,
      width: preset.width,
      height: preset.height,
      mode: preset.mode,
      quality: preset.quality,
      format: preset.format || '',
      is_auto_generate: preset.is_auto_generate,
      description: preset.description || '',
      is_builtin: preset.is_builtin
    })
  } else {
    Object.assign(formData, {
      id: null,
      name: '',
      display_name: '',
      width: 800,
      height: 600,
      mode: 'fit',
      quality: 85,
      format: '',
      is_auto_generate: 1,
      description: '',
      is_builtin: 0
    })
  }

  dialogVisible.value = true
}

// 提交表单
const handleSubmit = async () => {
  await formRef.value?.validate()

  submitting.value = true
  try {
    const data = {
      name: formData.name,
      display_name: formData.display_name,
      width: formData.width,
      height: formData.height,
      mode: formData.mode,
      quality: formData.quality,
      format: formData.format,
      is_auto_generate: formData.is_auto_generate,
      description: formData.description
    }

    if (isEdit.value) {
      await updatePreset(formData.id, data)
      ElMessage.success('更新成功')
    } else {
      await createPreset(data)
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

// 删除预设
const handleDelete = async (row) => {
  if (row.is_builtin) {
    ElMessage.warning('内置预设不允许删除')
    return
  }

  await ElMessageBox.confirm(
    `确定要删除预设"${row.display_name}"吗？`,
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

// 自动生成开关变化
const handleAutoGenerateChange = async (row) => {
  try {
    await updatePreset(row.id, { is_auto_generate: row.is_auto_generate })
    ElMessage.success('设置已更新')
  } catch (error) {
    ElMessage.error('设置失败')
    // 恢复开关状态
    row.is_auto_generate = row.is_auto_generate === 1 ? 0 : 1
  }
}

// 获取模式文本
const getModeText = (mode) => {
  const modes = {
    fit: '适应',
    fill: '填充',
    crop: '裁剪',
    exact: '精确'
  }
  return modes[mode] || mode
}

// 获取模式标签类型
const getModeTagType = (mode) => {
  const types = {
    fit: 'success',
    fill: 'primary',
    crop: 'warning',
    exact: 'danger'
  }
  return types[mode] || 'info'
}

onMounted(() => {
  loadPresets()
})
</script>

<style scoped>
.thumbnail-presets {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.form-tip {
  font-size: 12px;
  color: #909399;
  margin-top: 5px;
  line-height: 1.5;
}
</style>
