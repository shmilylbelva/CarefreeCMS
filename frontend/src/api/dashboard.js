import request from './request'

// 获取仪表板统计数据
export function getDashboardStats() {
  return request({ url: '/dashboard/stats', method: 'get' })
}

// 获取服务器信息
export function getServerInfo() {
  return request({ url: '/dashboard/server-info', method: 'get' })
}

// 获取系统信息
export function getSystemInfo() {
  return request({ url: '/dashboard/system-info', method: 'get' })
}
