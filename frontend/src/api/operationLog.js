import request from '@/utils/request'

// 获取操作日志列表
export function getOperationLogs(params) {
  return request({
    url: '/operation-logs',
    method: 'get',
    params
  })
}

// 获取日志详情
export function getOperationLogDetail(id) {
  return request({
    url: `/operation-logs/${id}`,
    method: 'get'
  })
}

// 获取模块列表
export function getModules() {
  return request({
    url: '/operation-logs/modules',
    method: 'get'
  })
}

// 获取操作类型列表
export function getActions() {
  return request({
    url: '/operation-logs/actions',
    method: 'get'
  })
}

// 清空日志
export function clearLogs(days) {
  return request({
    url: '/operation-logs/clear',
    method: 'post',
    data: { days }
  })
}
