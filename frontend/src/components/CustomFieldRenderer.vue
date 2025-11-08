<template>
  <div class="custom-field-renderer">
    <el-form-item
      v-for="field in fields"
      :key="field.id"
      :label="field.name"
      :prop="`custom_fields.${field.field_key}`"
      :rules="getFieldRules(field)"
    >
      <!-- 单行文本 -->
      <el-input
        v-if="field.field_type === 'text'"
        v-model="modelValue[field.field_key]"
        :placeholder="field.placeholder || `请输入${field.name}`"
        @input="handleInput"
      />

      <!-- 数字 -->
      <el-input-number
        v-else-if="field.field_type === 'number'"
        v-model="modelValue[field.field_key]"
        :placeholder="field.placeholder || `请输入${field.name}`"
        @change="handleInput"
      />

      <!-- 日期 -->
      <el-date-picker
        v-else-if="field.field_type === 'date'"
        v-model="modelValue[field.field_key]"
        type="date"
        :placeholder="field.placeholder || `请选择${field.name}`"
        format="YYYY-MM-DD"
        value-format="YYYY-MM-DD"
        @change="handleInput"
        style="width: 100%;"
      />

      <!-- 日期时间 -->
      <el-date-picker
        v-else-if="field.field_type === 'datetime'"
        v-model="modelValue[field.field_key]"
        type="datetime"
        :placeholder="field.placeholder || `请选择${field.name}`"
        format="YYYY-MM-DD HH:mm:ss"
        value-format="YYYY-MM-DD HH:mm:ss"
        @change="handleInput"
        style="width: 100%;"
      />

      <!-- 下拉选择 -->
      <el-select
        v-else-if="field.field_type === 'select'"
        v-model="modelValue[field.field_key]"
        :placeholder="field.placeholder || `请选择${field.name}`"
        @change="handleInput"
        style="width: 100%;"
      >
        <el-option
          v-for="(label, value) in field.options"
          :key="value"
          :label="label"
          :value="value"
        />
      </el-select>

      <!-- 单选按钮 -->
      <el-radio-group
        v-else-if="field.field_type === 'radio'"
        v-model="modelValue[field.field_key]"
        @change="handleInput"
      >
        <el-radio
          v-for="(label, value) in field.options"
          :key="value"
          :label="value"
        >
          {{ label }}
        </el-radio>
      </el-radio-group>

      <!-- 多选框 -->
      <el-checkbox-group
        v-else-if="field.field_type === 'checkbox'"
        v-model="modelValue[field.field_key]"
        @change="handleInput"
      >
        <el-checkbox
          v-for="(label, value) in field.options"
          :key="value"
          :label="value"
        >
          {{ label }}
        </el-checkbox>
      </el-checkbox-group>

      <!-- 多行文本 -->
      <el-input
        v-else-if="field.field_type === 'textarea'"
        v-model="modelValue[field.field_key]"
        type="textarea"
        :rows="3"
        :placeholder="field.placeholder || `请输入${field.name}`"
        @input="handleInput"
      />

      <!-- 富文本编辑器 -->
      <TinyMCE
        v-else-if="field.field_type === 'richtext'"
        v-model="modelValue[field.field_key]"
        :height="300"
        @update:modelValue="handleInput"
      />

      <!-- 图片上传 -->
      <div v-else-if="field.field_type === 'image'">
        <el-upload
          class="image-uploader"
          :action="uploadAction"
          :headers="uploadHeaders"
          :show-file-list="false"
          :on-success="(response) => handleImageSuccess(response, field.field_key)"
          :before-upload="beforeUpload"
          name="file"
        >
          <img v-if="modelValue[field.field_key]" :src="modelValue[field.field_key]" class="uploaded-image" />
          <el-icon v-else class="uploader-icon"><Plus /></el-icon>
        </el-upload>
        <el-button
          v-if="modelValue[field.field_key]"
          size="small"
          type="danger"
          @click="handleRemoveImage(field.field_key)"
          style="margin-top: 10px;"
        >
          删除图片
        </el-button>
      </div>

      <!-- 文件上传 -->
      <div v-else-if="field.field_type === 'file'">
        <el-upload
          :action="uploadAction"
          :headers="uploadHeaders"
          :on-success="(response) => handleFileSuccess(response, field.field_key)"
          :before-upload="beforeUpload"
          name="file"
        >
          <el-button size="small" type="primary">选择文件</el-button>
        </el-upload>
        <div v-if="modelValue[field.field_key]" style="margin-top: 10px;">
          <el-link :href="modelValue[field.field_key]" target="_blank">{{ modelValue[field.field_key] }}</el-link>
          <el-button
            size="small"
            type="danger"
            @click="handleRemoveFile(field.field_key)"
            style="margin-left: 10px;"
          >
            删除
          </el-button>
        </div>
      </div>

      <!-- 帮助文本 -->
      <div v-if="field.help_text" class="help-text">
        {{ field.help_text }}
      </div>
    </el-form-item>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Plus } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { getToken } from '@/utils/auth'
import TinyMCE from './TinyMCE.vue'

const props = defineProps({
  fields: {
    type: Array,
    default: () => []
  },
  modelValue: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['update:modelValue'])

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

// 处理输入
const handleInput = () => {
  emit('update:modelValue', props.modelValue)
}

// 获取字段验证规则
const getFieldRules = (field) => {
  const rules = []
  if (field.is_required) {
    rules.push({
      required: true,
      message: `请输入${field.name}`,
      trigger: ['blur', 'change']
    })
  }
  return rules
}

// 图片上传成功
const handleImageSuccess = (response, fieldKey) => {
  if (response.code === 200) {
    props.modelValue[fieldKey] = response.data.file_url || response.data.file_path
    handleInput()
    ElMessage.success('上传成功')
  } else {
    ElMessage.error(response.message || '上传失败')
  }
}

// 文件上传成功
const handleFileSuccess = (response, fieldKey) => {
  if (response.code === 200) {
    props.modelValue[fieldKey] = response.data.file_url || response.data.file_path
    handleInput()
    ElMessage.success('上传成功')
  } else {
    ElMessage.error(response.message || '上传失败')
  }
}

// 删除图片
const handleRemoveImage = (fieldKey) => {
  props.modelValue[fieldKey] = ''
  handleInput()
}

// 删除文件
const handleRemoveFile = (fieldKey) => {
  props.modelValue[fieldKey] = ''
  handleInput()
}

// 上传前校验
const beforeUpload = (file) => {
  const isLt10M = file.size / 1024 / 1024 < 10
  if (!isLt10M) {
    ElMessage.error('文件大小不能超过 10MB!')
    return false
  }
  return true
}
</script>

<style scoped>
.custom-field-renderer {
  width: 100%;
}

.help-text {
  margin-top: 5px;
  color: #909399;
  font-size: 12px;
  line-height: 1.5;
}

.image-uploader :deep(.el-upload) {
  border: 1px dashed #d9d9d9;
  border-radius: 4px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: all 0.3s;
  width: 178px;
  height: 178px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #fafafa;
}

.image-uploader :deep(.el-upload:hover) {
  border-color: #409eff;
}

.uploader-icon {
  font-size: 28px;
  color: #8c939d;
}

.uploaded-image {
  width: 178px;
  height: 178px;
  display: block;
  object-fit: cover;
}
</style>
