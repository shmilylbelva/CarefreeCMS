import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const usePermissionStore = defineStore('permission', () => {
  // 用户拥有的权限列表
  const permissions = ref([])

  /**
   * 设置权限列表
   */
  function setPermissions(perms) {
    permissions.value = Array.isArray(perms) ? perms : []
  }

  /**
   * 检查是否有指定权限
   */
  function hasPermission(permission) {
    // 超级管理员拥有所有权限
    if (permissions.value.includes('*')) {
      return true
    }
    return permissions.value.includes(permission)
  }

  /**
   * 检查是否有任一权限
   */
  function hasAnyPermission(perms) {
    if (!Array.isArray(perms)) {
      return false
    }
    // 超级管理员拥有所有权限
    if (permissions.value.includes('*')) {
      return true
    }
    return perms.some(perm => permissions.value.includes(perm))
  }

  /**
   * 检查是否有所有权限
   */
  function hasAllPermissions(perms) {
    if (!Array.isArray(perms)) {
      return false
    }
    // 超级管理员拥有所有权限
    if (permissions.value.includes('*')) {
      return true
    }
    return perms.every(perm => permissions.value.includes(perm))
  }

  /**
   * 清空权限
   */
  function clearPermissions() {
    permissions.value = []
  }

  return {
    permissions,
    setPermissions,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    clearPermissions
  }
})
