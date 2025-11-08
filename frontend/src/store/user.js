import { defineStore } from 'pinia'
import { login, getUserInfo, logout } from '@/api/auth'
import { getToken, setToken, removeToken } from '@/utils/auth'
import { usePermissionStore } from './permission'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: getToken() || '',  // 从 localStorage 初始化 token
    userInfo: null
  }),

  getters: {
    isLoggedIn: (state) => !!state.token
  },

  actions: {
    // 设置 token
    setToken(token) {
      this.token = token
      setToken(token)
    },

    // 登录
    async login(loginForm) {
      try {
        const res = await login(loginForm)
        this.token = res.data.token
        setToken(res.data.token)
        return res
      } catch (error) {
        return Promise.reject(error)
      }
    },

    // 获取用户信息
    async getUserInfo() {
      try {
        const res = await getUserInfo()
        this.userInfo = res.data

        // 加载用户权限
        const permissionStore = usePermissionStore()
        if (res.data.role && res.data.role.permissions) {
          let permissions = []
          try {
            permissions = typeof res.data.role.permissions === 'string'
              ? JSON.parse(res.data.role.permissions)
              : res.data.role.permissions
          } catch (e) {
            permissions = []
          }
          // 超级管理员（ID=1）拥有所有权限
          if (res.data.role_id === 1) {
            permissions = ['*']
          }
          permissionStore.setPermissions(permissions)
        }

        return res
      } catch (error) {
        return Promise.reject(error)
      }
    },

    // 退出登录
    async logout() {
      try {
        // 只有在有 token 的情况下才调用后端 API
        if (this.token) {
          await logout().catch(err => {
            // 忽略退出登录时的错误（比如 token 已过期）
            console.log('退出登录API调用失败，但继续清理本地状态')
          })
        }
      } catch (error) {
        console.error('退出登录失败:', error)
      } finally {
        // 无论如何都要清空本地状态
        this.token = ''
        this.userInfo = null
        removeToken()

        // 清空权限
        const permissionStore = usePermissionStore()
        permissionStore.clearPermissions()
      }
    }
  }
})
