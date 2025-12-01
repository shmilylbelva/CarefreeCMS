import request from '@/utils/request'

/**
 * 获取缓存信息
 */
export function getCacheInfo() {
  return request({
    url: '/cache/info',
    method: 'get'
  })
}

/**
 * 清空所有缓存
 */
export function clearAll() {
  return request({
    url: '/cache/clear-all',
    method: 'post'
  })
}

/**
 * 清除标签缓存
 */
export function clearTag(data) {
  return request({
    url: '/cache/clear-tag',
    method: 'post',
    data
  })
}

/**
 * 删除指定缓存
 */
export function deleteCache(params) {
  return request({
    url: '/cache',
    method: 'delete',
    params
  })
}

/**
 * 清除模板缓存
 */
export function clearTemplate() {
  return request({
    url: '/cache/clear-template',
    method: 'post'
  })
}

/**
 * 清除日志文件
 */
export function clearLogs(data) {
  return request({
    url: '/cache/clear-logs',
    method: 'post',
    data
  })
}

/**
 * 获取缓存键列表
 */
export function getKeys(params) {
  return request({
    url: '/cache/keys',
    method: 'get',
    params
  })
}

/**
 * 获取缓存值
 */
export function get(params) {
  return request({
    url: '/cache/get',
    method: 'get',
    params
  })
}

/**
 * 设置缓存值
 */
export function set(data) {
  return request({
    url: '/cache/set',
    method: 'post',
    data
  })
}

/**
 * 缓存预热
 * @param {object} data - 预热选项 { type: 'all|config|sites|categories|tags|articles|permissions' }
 */
export function warmup(data = {}) {
  return request({
    url: '/cache/warmup',
    method: 'post',
    data
  })
}

/**
 * 测试缓存性能
 */
export function testPerformance(data) {
  return request({
    url: '/cache/test-performance',
    method: 'post',
    data
  })
}

/**
 * 获取当前缓存驱动
 */
export function getDriver() {
  return request({
    url: '/cache/driver',
    method: 'get'
  })
}

/**
 * 切换缓存驱动
 */
export function switchDriver(data) {
  return request({
    url: '/cache/switch-driver',
    method: 'post',
    data
  })
}

/**
 * 测试Redis连接
 */
export function testRedis(data) {
  return request({
    url: '/cache/test-redis',
    method: 'post',
    data
  })
}
