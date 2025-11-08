import request from '@/utils/request'

/**
 * 获取系统日志列表
 */
export function getSystemLogs(params) {
  return request({
    url: '/logs/system',
    method: 'get',
    params
  })
}

/**
 * 获取登录日志列表
 */
export function getLoginLogs(params) {
  return request({
    url: '/logs/login',
    method: 'get',
    params
  })
}

/**
 * 获取安全日志列表
 */
export function getSecurityLogs(params) {
  return request({
    url: '/logs/security',
    method: 'get',
    params
  })
}

/**
 * 获取系统日志统计
 */
export function getSystemLogStats(params) {
  return request({
    url: '/logs/system/stats',
    method: 'get',
    params
  })
}

/**
 * 获取登录日志统计
 */
export function getLoginLogStats(params) {
  return request({
    url: '/logs/login/stats',
    method: 'get',
    params
  })
}

/**
 * 获取高危IP列表
 */
export function getHighRiskIps(params) {
  return request({
    url: '/logs/security/high-risk-ips',
    method: 'get',
    params
  })
}

/**
 * 删除系统日志
 */
export function deleteSystemLog(id) {
  return request({
    url: `/logs/system/${id}`,
    method: 'delete'
  })
}

/**
 * 删除登录日志
 */
export function deleteLoginLog(id) {
  return request({
    url: `/logs/login/${id}`,
    method: 'delete'
  })
}

/**
 * 删除安全日志
 */
export function deleteSecurityLog(id) {
  return request({
    url: `/logs/security/${id}`,
    method: 'delete'
  })
}

/**
 * 批量删除系统日志
 */
export function batchDeleteSystemLogs(data) {
  return request({
    url: '/logs/system/batch-delete',
    method: 'post',
    data
  })
}

/**
 * 批量删除登录日志
 */
export function batchDeleteLoginLogs(data) {
  return request({
    url: '/logs/login/batch-delete',
    method: 'post',
    data
  })
}

/**
 * 批量删除安全日志
 */
export function batchDeleteSecurityLogs(data) {
  return request({
    url: '/logs/security/batch-delete',
    method: 'post',
    data
  })
}

/**
 * 清理旧日志
 */
export function cleanOldLogs(data) {
  return request({
    url: '/logs/clean-old',
    method: 'post',
    data
  })
}

/**
 * 导出日志
 */
export function exportLogs(params) {
  return request({
    url: '/logs/export',
    method: 'get',
    params,
    responseType: 'blob'
  })
}
