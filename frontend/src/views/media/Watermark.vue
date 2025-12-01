<template>
  <div class="watermark-page">
    <el-tabs v-model="activeTab" type="border-card">
      <el-tab-pane label="应用水印" name="apply">
        <WatermarkApply @applied="handleApplied" />
      </el-tab-pane>

      <el-tab-pane label="预设管理" name="config">
        <WatermarkConfig @change="handlePresetChange" />
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { ElMessage } from 'element-plus'
import WatermarkApply from '@/components/MediaLibrary/WatermarkApply.vue'
import WatermarkConfig from '@/components/MediaLibrary/WatermarkConfig.vue'

const activeTab = ref('apply')

const handleApplied = () => {
  ElMessage.success('水印应用成功')
}

const handlePresetChange = () => {
  // 预设变更时可以通知应用组件刷新
}
</script>

<style scoped>
.watermark-page {
  padding: 20px;
}
</style>
