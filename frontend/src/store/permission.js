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
   * 支持通配符匹配,例如:
   * - 权限列表包含 "article.*",可以匹配 "article.view", "article.create" 等
   * - 权限列表包含 "*",可以匹配所有权限
   */
  function hasPermission(permission) {
    if (!permission) {
      return true
    }

    // 超级管理员拥有所有权限
    if (permissions.value.includes('*')) {
      return true
    }

    // 精确匹配
    if (permissions.value.includes(permission)) {
      return true
    }

    // 通配符匹配
    // 例如: article.view 可以被 article.* 匹配
    const parts = permission.split('.')
    if (parts.length >= 2) {
      const wildcardPermission = parts[0] + '.*'
      if (permissions.value.includes(wildcardPermission)) {
        return true
      }
    }

    return false
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
