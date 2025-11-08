import request from '@/utils/request'

/**
 * 获取数据库信息
 */
export function getDatabaseInfo() {
  return request({
    url: '/database/info',
    method: 'get'
  })
}

/**
 * 获取所有表信息
 */
export function getTables() {
  return request({
    url: '/database/tables',
    method: 'get'
  })
}

/**
 * 完整备份
 */
export function backup(data) {
  return request({
    url: '/database/backup',
    method: 'post',
    data
  })
}

/**
 * 备份指定表
 */
export function backupTables(data) {
  return request({
    url: '/database/backup-tables',
    method: 'post',
    data
  })
}

/**
 * 获取备份列表
 */
export function getBackups(params) {
  return request({
    url: '/database/backups',
    method: 'get',
    params
  })
}

/**
 * 恢复数据库
 */
export function restore(data) {
  return request({
    url: '/database/restore',
    method: 'post',
    data
  })
}

/**
 * 验证备份文件
 */
export function validateBackup(data) {
  return request({
    url: '/database/validate-backup',
    method: 'post',
    data
  })
}

/**
 * 删除备份
 */
export function deleteBackup(id) {
  return request({
    url: `/database/backup/${id}`,
    method: 'delete'
  })
}

/**
 * 下载备份文件
 */
export function downloadBackup(params) {
  return request({
    url: '/database/download-backup',
    method: 'get',
    params,
    responseType: 'blob'
  })
}

/**
 * 优化表
 */
export function optimizeTables(data) {
  return request({
    url: '/database/optimize',
    method: 'post',
    data
  })
}

/**
 * 修复表
 */
export function repairTables(data) {
  return request({
    url: '/database/repair',
    method: 'post',
    data
  })
}

/**
 * 清理旧备份
 */
export function cleanOldBackups(data) {
  return request({
    url: '/database/clean-old-backups',
    method: 'post',
    data
  })
}
