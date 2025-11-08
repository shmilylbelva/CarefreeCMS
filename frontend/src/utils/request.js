import axios from 'axios'
import { ElMessage } from 'element-plus'
import { useUserStore } from '@/store/user'
import router from '@/router'

// 创建 axios 实例
const service = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json'
  }
})

// 请求拦截器
service.interceptors.request.use(
  config => {
    const userStore = useUserStore()
    if (userStore.token) {
      config.headers['Authorization'] = `Bearer ${userStore.token}`
    }
    return config
  },
  error => {
    console.error('Request error:', error)
    return Promise.reject(error)
  }
)

// 响应拦截器
service.interceptors.response.use(
  response => {
    const res = response.data

    // 检查响应头中是否有新的 token，如果有则自动更新
    // 后端在 token 即将过期时会返回新 token（Auth 中间件）
    const newToken = response.headers['x-new-token']
    if (newToken) {
      try {
        const userStore = useUserStore()
        userStore.setToken(newToken)
        console.debug('[Token] Token 已自动刷新')
      } catch (error) {
        console.error('[Token] 刷新 token 失败:', error)
      }
    }

    // 如果响应中有 code 字段，检查状态
    if (res.code !== undefined && res.code !== 200) {
      ElMessage({
        message: res.message || 'Error',
        type: 'error',
        duration: 3000
      })

      // 401: 未授权 - 只有关键接口才触发自动登出
      if (res.code === 401 && response.config.url && response.config.url.includes('/auth/info')) {
        const userStore = useUserStore()
        userStore.logout()
        router.push('/login')
      }

      return Promise.reject(new Error(res.message || 'Error'))
    }

    return res
  },
  error => {
    console.error('Response error:', error)

    // 忽略 logout 和 getUserInfo 请求的错误（由调用方处理）
    const isAuthRequest = error.config && (
      error.config.url.includes('/auth/logout') ||
      error.config.url.includes('/auth/info')
    )

    let message = error.message || 'Request failed'

    if (error.response) {
      const status = error.response.status
      const data = error.response.data

      switch (status) {
        case 400:
          message = data.message || '请求参数错误'
          break
        case 401:
          message = data.message || '未授权，请重新登录'
          // 不在这里处理 401，由 router guard 统一处理
          break
        case 403:
          message = '拒绝访问'
          break
        case 404:
          message = '请求的资源不存在'
          break
        case 500:
          message = '服务器内部错误'
          break
        default:
          message = data.message || `请求失败 (${status})`
      }
    } else if (error.code === 'ECONNABORTED') {
      message = '请求超时'
    } else if (error.message.includes('Network Error')) {
      message = '网络错误，请检查网络连接'
    }

    // 认证相关的错误不显示 toast，由页面或 router guard 处理
    if (!isAuthRequest) {
      ElMessage({
        message: message,
        type: 'error',
        duration: 3000
      })
    }

    return Promise.reject(error)
  }
)

export default service
