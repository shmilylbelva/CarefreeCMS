import request from './request'

// 登录
export function login(data) {
  return request({
    url: '/auth/login',
    method: 'post',
    data
  })
}

// 退出登录
export function logout() {
  return request({
    url: '/auth/logout',
    method: 'post'
  })
}

// 获取用户信息
export function getUserInfo() {
  return request({
    url: '/auth/info',
    method: 'get'
  })
}

// 修改密码 (使用RESTful PATCH方式)
export function changePassword(data) {
  return request({
    url: '/auth/password',
    method: 'patch',
    data
  })
}
