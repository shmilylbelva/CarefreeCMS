<template>
  <el-dialog
    v-model="visible"
    title="裁剪图片"
    width="900px"
    :close-on-click-modal="false"
    @close="handleClose"
  >
    <div class="image-cropper">
      <div class="cropper-container">
        <div class="preview-area">
          <img ref="imageRef" :src="imageSrc" alt="待裁剪图片" />
        </div>

        <div class="control-panel">
          <!-- 预览 -->
          <div class="preview-box">
            <div class="preview-title">预览</div>
            <div class="preview-content" :style="previewStyle"></div>
            <div class="preview-info">
              {{ cropData.width }} × {{ cropData.height }}
            </div>
          </div>

          <!-- 裁剪参数 -->
          <el-form :model="cropOptions" label-width="80px" size="small">
            <el-form-item label="宽高比">
              <el-select v-model="aspectRatio" @change="handleAspectRatioChange">
                <el-option label="自由" :value="NaN" />
                <el-option label="1:1 (正方形)" :value="1" />
                <el-option label="4:3" :value="4/3" />
                <el-option label="16:9" :value="16/9" />
                <el-option label="3:2" :value="3/2" />
                <el-option label="2:1" :value="2" />
              </el-select>
            </el-form-item>

            <el-form-item label="输出宽度">
              <el-input-number
                v-model="outputWidth"
                :min="1"
                :max="4096"
                controls-position="right"
              />
            </el-form-item>

            <el-form-item label="输出高度">
              <el-input-number
                v-model="outputHeight"
                :min="1"
                :max="4096"
                controls-position="right"
              />
            </el-form-item>

            <el-form-item label="输出质量">
              <el-slider v-model="quality" :min="1" :max="100" :marks="{ 100: '最佳' }" />
            </el-form-item>

            <el-form-item label="输出格式">
              <el-radio-group v-model="outputFormat">
                <el-radio value="jpeg">JPEG</el-radio>
                <el-radio value="png">PNG</el-radio>
                <el-radio value="webp">WEBP</el-radio>
              </el-radio-group>
            </el-form-item>
          </el-form>

          <!-- 操作按钮 -->
          <div class="actions">
            <el-button-group>
              <el-button :icon="ZoomIn" @click="zoom(0.1)">放大</el-button>
              <el-button :icon="ZoomOut" @click="zoom(-0.1)">缩小</el-button>
            </el-button-group>

            <el-button-group style="margin-left: 10px">
              <el-button :icon="RefreshLeft" @click="rotate(-90)">左转</el-button>
              <el-button :icon="RefreshRight" @click="rotate(90)">右转</el-button>
            </el-button-group>

            <el-button-group style="margin-left: 10px">
              <el-button @click="flipH">水平翻转</el-button>
              <el-button @click="flipV">垂直翻转</el-button>
            </el-button-group>

            <el-button style="margin-left: 10px" @click="reset">重置</el-button>
          </div>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="dialog-footer">
        <span class="tips">提示：拖动图片进行裁剪，滚轮缩放</span>
        <div>
          <el-button @click="handleClose">取消</el-button>
          <el-button type="primary" @click="handleCrop" :loading="cropping">
            {{ saveToServer ? '裁剪并保存' : '裁剪' }}
          </el-button>
        </div>
      </div>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { ElMessage } from 'element-plus'
import { ZoomIn, ZoomOut, RefreshLeft, RefreshRight } from '@element-plus/icons-vue'
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'
import { cropImage } from '@/api/mediaEdit'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  mediaId: {
    type: Number,
    default: null
  },
  imageSrc: {
    type: String,
    required: true
  },
  saveToServer: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['update:modelValue', 'success'])

const visible = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

// Cropper实例
const imageRef = ref(null)
let cropper = null

// 数据
const cropping = ref(false)
const aspectRatio = ref(NaN)
const outputWidth = ref(800)
const outputHeight = ref(600)
const quality = ref(90)
const outputFormat = ref('jpeg')

const cropOptions = ref({
  aspectRatio: NaN,
  viewMode: 1,
  dragMode: 'move',
  autoCropArea: 0.8,
  restore: false,
  guides: true,
  center: true,
  highlight: true,
  cropBoxMovable: true,
  cropBoxResizable: true,
  toggleDragModeOnDblclick: false
})

const cropData = ref({
  x: 0,
  y: 0,
  width: 0,
  height: 0
})

// 预览样式
const previewStyle = computed(() => {
  if (!cropper) return {}

  const data = cropper.getData(true)
  const containerData = cropper.getContainerData()
  const imageData = cropper.getImageData()

  const ratio = containerData.width / imageData.width

  return {
    width: `${Math.round(data.width * ratio)}px`,
    height: `${Math.round(data.height * ratio)}px`,
    overflow: 'hidden',
    backgroundImage: `url(${props.imageSrc})`,
    backgroundSize: `${Math.round(imageData.width * ratio)}px ${Math.round(imageData.height * ratio)}px`,
    backgroundPosition: `${-Math.round(data.x * ratio)}px ${-Math.round(data.y * ratio)}px`
  }
})

// 初始化Cropper
const initCropper = () => {
  if (cropper) {
    cropper.destroy()
  }

  nextTick(() => {
    if (!imageRef.value) return

    cropper = new Cropper(imageRef.value, {
      ...cropOptions.value,
      crop(event) {
        cropData.value = {
          x: Math.round(event.detail.x),
          y: Math.round(event.detail.y),
          width: Math.round(event.detail.width),
          height: Math.round(event.detail.height)
        }

        // 同步输出尺寸
        outputWidth.value = cropData.value.width
        outputHeight.value = cropData.value.height
      }
    })
  })
}

// 宽高比改变
const handleAspectRatioChange = (value) => {
  if (cropper) {
    cropper.setAspectRatio(value)
  }
}

// 缩放
const zoom = (ratio) => {
  if (cropper) {
    cropper.zoom(ratio)
  }
}

// 旋转
const rotate = (degree) => {
  if (cropper) {
    cropper.rotate(degree)
  }
}

// 水平翻转
const flipH = () => {
  if (cropper) {
    const data = cropper.getData()
    cropper.scaleX(data.scaleX === 1 ? -1 : 1)
  }
}

// 垂直翻转
const flipV = () => {
  if (cropper) {
    const data = cropper.getData()
    cropper.scaleY(data.scaleY === 1 ? -1 : 1)
  }
}

// 重置
const reset = () => {
  if (cropper) {
    cropper.reset()
  }
}

// 裁剪
const handleCrop = async () => {
  if (!cropper) return

  try {
    cropping.value = true

    if (props.saveToServer && props.mediaId) {
      // 保存到服务器
      const data = cropper.getData(true)
      const result = await cropImage({
        media_id: props.mediaId,
        x: data.x,
        y: data.y,
        width: data.width,
        height: data.height
      })

      ElMessage.success('裁剪成功')
      emit('success', result.data)
    } else {
      // 返回裁剪后的图片
      const canvas = cropper.getCroppedCanvas({
        width: outputWidth.value,
        height: outputHeight.value,
        imageSmoothingQuality: 'high'
      })

      canvas.toBlob(
        (blob) => {
          const url = URL.createObjectURL(blob)
          emit('success', { blob, url })
          ElMessage.success('裁剪完成')
        },
        `image/${outputFormat.value}`,
        quality.value / 100
      )
    }

    handleClose()
  } catch (error) {
    ElMessage.error('裁剪失败：' + error.message)
  } finally {
    cropping.value = false
  }
}

// 关闭
const handleClose = () => {
  if (cropper) {
    cropper.destroy()
    cropper = null
  }
  visible.value = false
}

// 监听对话框显示
watch(visible, (val) => {
  if (val) {
    nextTick(() => {
      initCropper()
    })
  }
})
</script>

<style scoped lang="scss">
.image-cropper {
  .cropper-container {
    display: flex;
    gap: 20px;

    .preview-area {
      flex: 1;
      height: 500px;
      background-color: #f5f7fa;
      border-radius: 8px;
      overflow: hidden;

      img {
        display: block;
        max-width: 100%;
      }
    }

    .control-panel {
      width: 280px;

      .preview-box {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #e4e7ed;
        border-radius: 8px;

        .preview-title {
          font-size: 14px;
          font-weight: 600;
          margin-bottom: 10px;
        }

        .preview-content {
          margin: 0 auto;
          border: 1px solid #e4e7ed;
          border-radius: 4px;
        }

        .preview-info {
          margin-top: 8px;
          text-align: center;
          font-size: 12px;
          color: #909399;
        }
      }

      .actions {
        margin-top: 15px;

        .el-button-group {
          display: flex;
        }
      }
    }
  }
}

.dialog-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;

  .tips {
    font-size: 12px;
    color: #909399;
  }
}

:deep(.cropper-view-box) {
  outline: 2px solid #409eff;
  outline-color: rgba(64, 158, 255, 0.75);
}

:deep(.cropper-face) {
  background-color: rgba(64, 158, 255, 0.1);
}
</style>
