<template>
  <div class="thumbnail-page">
    <el-tabs v-model="activeTab" type="border-card">
      <el-tab-pane label="生成缩略图" name="generator">
        <ThumbnailGenerator />
      </el-tab-pane>

      <el-tab-pane label="预设管理" name="presets">
        <ThumbnailPresets />
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ThumbnailGenerator from '@/components/MediaLibrary/ThumbnailGenerator.vue'
import ThumbnailPresets from '@/components/MediaLibrary/ThumbnailPresets.vue'

const activeTab = ref('generator')
</script>

<style scoped>
.thumbnail-page {
  padding: 20px;
}
</style>
