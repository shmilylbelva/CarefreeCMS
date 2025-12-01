import axios from 'axios'
import { ElMessage } from 'element-plus'
import { getToken, clearLoginData } from '@/utils/auth'
import router from '@/router'

// 创建 axios 实例
const service = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 30000
})

// 防止重复跳转登录页的标志
let isRedirectingToLogin = false

// 处理401未授权错误
function handle401Error(message) {
  // 如果已经在跳转中，不重复处理
  if (isRedirectingToLogin) {
    return
  }

  isRedirectingToLogin = true

  // 显示错误提示
  ElMessage.error(message || '登录已过期，请重新登录')

  // 清空所有登录相关缓存
  clearLoginData()

  // 跳转到登录页
  router.push('/login').finally(() => {
    // 跳转完成后重置标志（延迟重置，避免并发请求问题）
    setTimeout(() => {
      isRedirectingToLogin = false
    }, 1000)
  })
}

// 请求拦截器
service.interceptors.request.use(
  config => {
    const token = getToken()
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => {
    console.error('请求错误:', error)
    return Promise.reject(error)
  }
)

// 响应拦截器
service.interceptors.response.use(
  response => {
    const res = response.data

    if (res.code !== 200) {
      // 401: 令牌无效或已过期，清空登录缓存并跳转登录页
      if (res.code === 401) {
        handle401Error(res.message)
        return Promise.reject(new Error(res.message || '未授权'))
      }

      // 检查是否需要隐藏错误提示（由调用方自行处理）
      if (!response.config.hideErrorMessage) {
        // 400 错误通常是业务逻辑错误，使用 warning 类型提示
        const messageType = res.code === 400 ? 'warning' : 'error'
        ElMessage({
          message: res.message || '请求失败',
          type: messageType,
          duration: 3000
        })
      }

      return Promise.reject(new Error(res.message || 'Error'))
    }

    return res
  },
  error => {
    console.error('响应错误:', error)

    // 处理HTTP 401状态码（令牌无效或已过期）
    if (error.response?.status === 401) {
      const message = error.response?.data?.message || '登录已过期，请重新登录'
      handle401Error(message)
      return Promise.reject(error)
    }

    // 其他错误提示
    if (!error.config?.hideErrorMessage) {
      const message = error.response?.data?.message || error.message || '网络错误'
      ElMessage.error(message)
    }
    return Promise.reject(error)
  }
)

export default service
