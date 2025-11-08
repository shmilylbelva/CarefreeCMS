import request from './request'

// 获取系统配置
export function getConfig() {
  return request({ url: '/config', method: 'get' })
}

// 保存系统配置
export function saveConfig(data) {
  return request({ url: '/config', method: 'post', data })
}
