import request from '@/utils/request'

// 获取个人信息
export function getProfile() {
  return request({
    url: '/profile',
    method: 'get'
  })
}

// 更新个人信息
export function updateProfile(data) {
  return request({
    url: '/profile',
    method: 'put',
    data
  })
}

// 修改密码 (使用RESTful PATCH方式)
export function updatePassword(data) {
  return request({
    url: '/profile/password',
    method: 'patch',
    data
  })
}

// 上传头像
export function uploadAvatar(data) {
  return request({
    url: '/profile/avatar',
    method: 'post',
    data,
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  })
}

// 获取当前用户权限列表
export function getPermissions() {
  return request({
    url: '/profile/permissions',
    method: 'get'
  })
}
