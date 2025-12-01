import request from '@/utils/request'

/**
 * 存储配置API
 */

// 获取存储配置列表
export function getStorageConfigs(params) {
  return request({
    url: '/storage-config',
    method: 'get',
    params
  })
}

// 获取存储配置详情
export function getStorageConfig(id) {
  return request({
    url: `/storage-config/${id}`,
    method: 'get'
  })
}

// 创建存储配置
export function createStorageConfig(data) {
  return request({
    url: '/storage-config',
    method: 'post',
    data
  })
}

// 更新存储配置
export function updateStorageConfig(id, data) {
  return request({
    url: `/storage-config/${id}`,
    method: 'put',
    data
  })
}

// 删除存储配置
export function deleteStorageConfig(id) {
  return request({
    url: `/storage-config/${id}`,
    method: 'delete'
  })
}

// 测试存储配置
export function testStorageConfig(data) {
  return request({
    url: '/storage-config/test',
    method: 'post',
    data
  })
}

// 设为默认存储
export function setDefaultStorage(id) {
  return request({
    url: `/storage-config/${id}/set-default`,
    method: 'post'
  })
}

// 获取支持的驱动列表
export function getDrivers() {
  return request({
    url: '/storage-config/drivers',
    method: 'get'
  })
}

// 获取驱动配置模板
export function getDriverTemplate(driver) {
  return request({
    url: `/storage-config/driver-template/${driver}`,
    method: 'get'
  })
}

// 批量排序
export function sortStorageConfigs(sorts) {
  return request({
    url: '/storage-config/sort',
    method: 'post',
    data: { sorts }
  })
}
