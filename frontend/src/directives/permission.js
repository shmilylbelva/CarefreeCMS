import { usePermissionStore } from '@/store/permission'

/**
 * 权限指令（改进版：支持异步权限加载）
 * 用法：
 * v-permission="'articles.create'"  检查单个权限
 * v-permission="['articles.create', 'articles.edit']"  检查是否有任一权限
 * v-permission.all="['articles.create', 'articles.edit']"  检查是否有所有权限
 */

// 检查权限的通用函数
function checkPermission(value, modifiers, permissionStore) {
  if (!value) return true

  let hasPermission = false

  if (Array.isArray(value)) {
    // 数组：检查是否有任一权限（默认）或所有权限
    if (modifiers.all) {
      hasPermission = permissionStore.hasAllPermissions(value)
    } else {
      hasPermission = permissionStore.hasAnyPermission(value)
    }
  } else {
    // 字符串：检查单个权限
    hasPermission = permissionStore.hasPermission(value)
  }

  return hasPermission
}

// 更新元素显示状态
function updateElementVisibility(el, hasPermission) {
  // 使用 display 而不是移除元素，这样权限加载后可以显示
  if (!hasPermission) {
    // 保存原始 display 值（首次）
    if (!el._vPermissionOriginalDisplay) {
      el._vPermissionOriginalDisplay = el.style.display || ''
    }
    el.style.display = 'none'
  } else {
    // 恢复原始 display 值
    el.style.display = el._vPermissionOriginalDisplay || ''
  }
}

export default {
  mounted(el, binding) {
    const { value, modifiers } = binding
    const permissionStore = usePermissionStore()

    const hasPermission = checkPermission(value, modifiers, permissionStore)
    updateElementVisibility(el, hasPermission)
  },

  updated(el, binding) {
    const { value, modifiers } = binding
    const permissionStore = usePermissionStore()

    const hasPermission = checkPermission(value, modifiers, permissionStore)
    updateElementVisibility(el, hasPermission)
  }
}
