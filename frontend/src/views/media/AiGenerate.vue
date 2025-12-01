<template>
  <div class="ai-generate-page">
    <AiImageGenerator @generated="handleGenerated" />
  </div>
</template>

<script setup>
import { ElMessage } from 'element-plus'
import AiImageGenerator from '@/components/MediaLibrary/AiImageGenerator.vue'

const handleGenerated = (result) => {
  ElMessage.success('图片生成任务已创建')
}
</script>

<style scoped>
.ai-generate-page {
  padding: 20px;
}
</style>
