<template>
  <div v-if="hasPermission">
    <slot></slot>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { hasPermission as checkPermission } from '@/utils/permission'

/**
 * 权限组件
 *
 * 使用方法：
 * <Permission permission="article.create">
 *   <el-button>创建文章</el-button>
 * </Permission>
 */
const props = defineProps({
  // 需要的权限（单个或多个）
  permission: {
    type: [String, Array],
    required: true
  },
  // 检查模式：'any' 任一权限, 'all' 所有权限
  mode: {
    type: String,
    default: 'any',
    validator: (value) => ['any', 'all'].includes(value)
  }
})

const hasPermission = computed(() => {
  if (Array.isArray(props.permission)) {
    if (props.mode === 'all') {
      // 需要所有权限
      return props.permission.every(perm => checkPermission(perm))
    } else {
      // 任一权限即可
      return props.permission.some(perm => checkPermission(perm))
    }
  } else {
    return checkPermission(props.permission)
  }
})
</script>
