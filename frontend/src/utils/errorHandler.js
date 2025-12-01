/**
 * 统一错误处理工具
 * 提供更友好的错误提示
 */
import { ElMessage, ElNotification } from 'element-plus'

/**
 * 从错误对象中提取可读的错误消息
 * @param {Error} error - 错误对象
 * @param {string} defaultMessage - 默认错误消息
 * @returns {string} 错误消息
 */
export function getErrorMessage(error, defaultMessage = '操作失败') {
  if (!error) {
    return defaultMessage
  }

  // HTTP 响应错误
  if (error.response) {
    const { status, data } = error.response

    // 根据状态码提供更友好的提示
    switch (status) {
      case 400:
        return data?.message || '请求参数错误'
      case 401:
        return '登录已过期，请重新登录'
      case 403:
        return data?.message || '没有权限执行此操作'
      case 404:
        return data?.message || '请求的资源不存在'
      case 409:
        return data?.message || '操作冲突，请刷新后重试'
      case 422:
        return data?.message || '数据验证失败'
      case 429:
        return '操作过于频繁，请稍后重试'
      case 500:
        return '服务器内部错误，请稍后重试'
      case 502:
        return '网关错误，请稍后重试'
      case 503:
        return '服务暂时不可用，请稍后重试'
      case 504:
        return '请求超时，请稍后重试'
      default:
        // 优先使用后端返回的错误消息
        if (data?.message) {
          return data.message
        }
        if (data?.error) {
          return data.error
        }
        return defaultMessage
    }
  }

  // 网络错误
  if (error.message === 'Network Error') {
    return '网络连接失败，请检查网络设置'
  }

  // 请求超时
  if (error.code === 'ECONNABORTED') {
    return '请求超时，请稍后重试'
  }

  // 请求取消
  if (error.message && error.message.includes('cancel')) {
    return '请求已取消'
  }

  // 其他错误
  return error.message || defaultMessage
}

/**
 * 处理错误并显示消息提示
 * @param {Error} error - 错误对象
 * @param {Object} options - 配置选项
 * @param {string} options.defaultMessage - 默认错误消息
 * @param {string} options.type - 提示类型：'message' | 'notification'
 * @param {boolean} options.showConsoleError - 是否在控制台打印错误
 * @param {Function} options.onError - 错误回调函数
 */
export function handleError(error, options = {}) {
  const {
    defaultMessage = '操作失败',
    type = 'message',
    showConsoleError = true,
    onError
  } = options

  // 在控制台打印详细错误
  if (showConsoleError) {
    console.error('Error occurred:', error)
    if (error.response) {
      console.error('Response data:', error.response.data)
      console.error('Response status:', error.response.status)
    }
  }

  // 提取错误消息
  const message = getErrorMessage(error, defaultMessage)

  // 显示错误提示
  if (type === 'notification') {
    ElNotification.error({
      title: '错误',
      message,
      duration: 5000
    })
  } else {
    ElMessage.error(message)
  }

  // 执行错误回调
  if (typeof onError === 'function') {
    onError(error, message)
  }

  // 401 错误特殊处理：跳转到登录页
  if (error.response?.status === 401) {
    setTimeout(() => {
      // 清除登录信息
      localStorage.removeItem('token')
      localStorage.removeItem('userInfo')
      // 跳转到登录页
      if (window.location.pathname !== '/login') {
        window.location.href = '/login'
      }
    }, 1500)
  }

  return message
}

/**
 * 创建一个带错误处理的异步函数包装器
 * @param {Function} asyncFn - 异步函数
 * @param {Object} errorOptions - 错误处理选项
 * @returns {Function} 包装后的函数
 */
export function withErrorHandler(asyncFn, errorOptions = {}) {
  return async (...args) => {
    try {
      return await asyncFn(...args)
    } catch (error) {
      handleError(error, errorOptions)
      throw error
    }
  }
}

/**
 * 批量验证参数
 * @param {Object} params - 参数对象
 * @param {Array} rules - 验证规则数组
 * @returns {Object} { valid: boolean, message: string }
 */
export function validateParams(params, rules) {
  for (const rule of rules) {
    const { field, required, type, min, max, pattern, message } = rule
    const value = params[field]

    // 必填验证
    if (required && (value === undefined || value === null || value === '')) {
      return {
        valid: false,
        message: message || `${field} 不能为空`
      }
    }

    // 如果值为空且非必填，跳过后续验证
    if (!required && (value === undefined || value === null || value === '')) {
      continue
    }

    // 类型验证
    if (type) {
      const actualType = Array.isArray(value) ? 'array' : typeof value
      if (actualType !== type) {
        return {
          valid: false,
          message: message || `${field} 类型错误，期望 ${type}`
        }
      }
    }

    // 最小值/最小长度验证
    if (min !== undefined) {
      const length = typeof value === 'string' ? value.length : value
      if (length < min) {
        return {
          valid: false,
          message: message || `${field} 不能少于 ${min}`
        }
      }
    }

    // 最大值/最大长度验证
    if (max !== undefined) {
      const length = typeof value === 'string' ? value.length : value
      if (length > max) {
        return {
          valid: false,
          message: message || `${field} 不能超过 ${max}`
        }
      }
    }

    // 正则验证
    if (pattern && typeof value === 'string') {
      if (!pattern.test(value)) {
        return {
          valid: false,
          message: message || `${field} 格式不正确`
        }
      }
    }
  }

  return { valid: true, message: '' }
}

/**
 * 安全的分页参数处理
 * @param {number} page - 页码
 * @param {number} pageSize - 每页数量
 * @param {Object} options - 配置选项
 * @returns {Object} { page, pageSize }
 */
export function sanitizePaginationParams(page, pageSize, options = {}) {
  const {
    minPage = 1,
    maxPage = 10000,
    minPageSize = 1,
    maxPageSize = 100,
    defaultPage = 1,
    defaultPageSize = 10
  } = options

  // 转换为整数
  let safePage = parseInt(page) || defaultPage
  let safePageSize = parseInt(pageSize) || defaultPageSize

  // 限制范围
  safePage = Math.max(minPage, Math.min(maxPage, safePage))
  safePageSize = Math.max(minPageSize, Math.min(maxPageSize, safePageSize))

  return {
    page: safePage,
    pageSize: safePageSize
  }
}

export default {
  getErrorMessage,
  handleError,
  withErrorHandler,
  validateParams,
  sanitizePaginationParams
}
