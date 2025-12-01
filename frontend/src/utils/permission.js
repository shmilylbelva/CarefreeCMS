/**
 * 权限工具类
 *
 * 提供权限检查、权限指令等功能
 * 使用前需要先通过API加载用户权限
 */

import store from '@/store'

/**
 * 检查用户是否拥有指定权限
 *
 * @param {string|Array} permission - 权限标识或权限数组
 * @returns {boolean}
 */
export function hasPermission(permission) {
  const permissions = store.getters?.permissions || []

  // 如果没有权限数据，默认无权限
  if (!permissions || permissions.length === 0) {
    return false
  }

  // 超级管理员拥有所有权限
  if (permissions.includes('*')) {
    return true
  }

  // 如果是数组，检查是否拥有任一权限
  if (Array.isArray(permission)) {
    return permission.some(perm => checkSinglePermission(permissions, perm))
  }

  // 单个权限检查
  return checkSinglePermission(permissions, permission)
}

/**
 * 检查单个权限
 *
 * @param {Array} userPermissions - 用户权限列表
 * @param {string} required - 需要的权限
 * @returns {boolean}
 */
function checkSinglePermission(userPermissions, required) {
  // 完全匹配
  if (userPermissions.includes(required)) {
    return true
  }

  // 通配符匹配 (例如: article.* 匹配 article.create)
  const parts = required.split('.')
  if (parts.length >= 2) {
    const wildcardPerm = `${parts[0]}.*`
    if (userPermissions.includes(wildcardPerm)) {
      return true
    }
  }

  return false
}

/**
 * 检查是否拥有任一权限
 *
 * @param {Array} permissions - 权限数组
 * @returns {boolean}
 */
export function hasAnyPermission(permissions) {
  if (!Array.isArray(permissions)) {
    return false
  }

  return permissions.some(perm => hasPermission(perm))
}

/**
 * 检查是否拥有所有权限
 *
 * @param {Array} permissions - 权限数组
 * @returns {boolean}
 */
export function hasAllPermissions(permissions) {
  if (!Array.isArray(permissions)) {
    return false
  }

  return permissions.every(perm => hasPermission(perm))
}

/**
 * 检查是否是超级管理员
 *
 * @returns {boolean}
 */
export function isSuperAdmin() {
  const permissions = store.getters?.permissions || []
  return permissions.includes('*')
}

/**
 * 获取用户所有权限
 *
 * @returns {Array}
 */
export function getUserPermissions() {
  return store.getters?.permissions || []
}

/**
 * 权限过滤器
 * 用于过滤数组中的项，只保留有权限的项
 *
 * @param {Array} items - 要过滤的项数组
 * @param {Function} getPermission - 获取项权限的函数
 * @returns {Array}
 */
export function filterByPermission(items, getPermission) {
  if (!Array.isArray(items)) {
    return []
  }

  return items.filter(item => {
    const permission = getPermission(item)
    return hasPermission(permission)
  })
}

/**
 * 权限映射
 * 根据权限返回不同的值
 *
 * @param {Object} permissionMap - 权限映射对象 { permission: value }
 * @param {*} defaultValue - 默认值
 * @returns {*}
 */
export function mapByPermission(permissionMap, defaultValue = null) {
  for (const [permission, value] of Object.entries(permissionMap)) {
    if (hasPermission(permission)) {
      return value
    }
  }
  return defaultValue
}

/**
 * 根据权限禁用元素
 *
 * @param {string} permission - 需要的权限
 * @returns {boolean} - 是否禁用
 */
export function disableWithoutPermission(permission) {
  return !hasPermission(permission)
}

/**
 * Vue 3 权限指令
 * 使用方法: v-permission="'article.create'"
 */
export const permissionDirective = {
  mounted(el, binding) {
    const { value } = binding

    if (!hasPermission(value)) {
      // 移除元素
      el.parentNode?.removeChild(el)
    }
  }
}

/**
 * Vue 3 任一权限指令
 * 使用方法: v-permission-any="['article.edit', 'article.edit_own']"
 */
export const permissionAnyDirective = {
  mounted(el, binding) {
    const { value } = binding

    if (!hasAnyPermission(value)) {
      el.parentNode?.removeChild(el)
    }
  }
}

/**
 * Vue 3 所有权限指令
 * 使用方法: v-permission-all="['article.view', 'article.edit']"
 */
export const permissionAllDirective = {
  mounted(el, binding) {
    const { value } = binding

    if (!hasAllPermissions(value)) {
      el.parentNode?.removeChild(el)
    }
  }
}

/**
 * Vue 3 权限禁用指令
 * 使用方法: v-permission-disable="'article.delete'"
 * 没有权限时禁用元素而不是移除
 */
export const permissionDisableDirective = {
  mounted(el, binding) {
    const { value } = binding

    if (!hasPermission(value)) {
      el.disabled = true
      el.classList.add('is-disabled')
    }
  }
}

// 默认导出
export default {
  hasPermission,
  hasAnyPermission,
  hasAllPermissions,
  isSuperAdmin,
  getUserPermissions,
  filterByPermission,
  mapByPermission,
  disableWithoutPermission,
  // 指令
  permissionDirective,
  permissionAnyDirective,
  permissionAllDirective,
  permissionDisableDirective
}
