const TOKEN_KEY = import.meta.env.VITE_TOKEN_KEY || 'cms_token'

export function getToken() {
  return localStorage.getItem(TOKEN_KEY)
}

export function setToken(token) {
  localStorage.setItem(TOKEN_KEY, token)
}

export function removeToken() {
  localStorage.removeItem(TOKEN_KEY)
}

/**
 * 清空所有登录相关缓存
 * 用于401错误时彻底清理登录状态
 */
export function clearLoginData() {
  // 清除token
  localStorage.removeItem(TOKEN_KEY)

  // 清除可能存在的用户信息（如果有的话）
  localStorage.removeItem('user_info')
  localStorage.removeItem('user')
  localStorage.removeItem('userInfo')

  // 清除站点信息（登出后需要重新选择）
  localStorage.removeItem('current_site')
  localStorage.removeItem('site_id')

  // 也可以选择清空sessionStorage
  sessionStorage.clear()
}
