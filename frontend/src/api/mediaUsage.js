import request from './request'

// ========== 媒体使用追踪 ==========

// 获取媒体的使用情况
export function getMediaUsage(mediaId) {
  return request({ url: `/media-usage/${mediaId}`, method: 'get' })
}

// 获取对象使用的媒体列表
export function getUsedMedia(usableType, usableId) {
  return request({
    url: '/media-usage/used-media',
    method: 'get',
    params: { usable_type: usableType, usable_id: usableId }
  })
}

// 检查媒体是否可以安全删除
export function checkSafeDelete(mediaId) {
  return request({ url: `/media-usage/check-delete/${mediaId}`, method: 'get' })
}

// 获取未使用的媒体列表
export function getUnusedMedia(params) {
  return request({ url: '/media-usage/unused', method: 'get', params })
}

// 清理未使用的媒体
export function cleanUnusedMedia(data) {
  return request({ url: '/media-usage/clean-unused', method: 'post', data })
}

// 记录媒体使用
export function recordUsage(data) {
  return request({ url: '/media-usage/record', method: 'post', data })
}

// 删除使用记录
export function removeUsage(data) {
  return request({ url: '/media-usage/remove', method: 'post', data })
}

// ========== 文章相关 ==========

// 检查文章删除时的媒体使用情况
export function checkArticleDeleteMedia(articleId) {
  return request({ url: `/articles/${articleId}/check-delete-media`, method: 'get' })
}
