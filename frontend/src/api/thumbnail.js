import request from './request'

// ========== 缩略图预设管理 ==========

// 获取缩略图预设列表
export function getPresets(params) {
  return request({ url: '/media-thumbnail/presets', method: 'get', params })
}

// 获取单个预设
export function getPreset(id) {
  return request({ url: `/media-thumbnail/presets/${id}`, method: 'get' })
}

// 创建预设
export function createPreset(data) {
  return request({ url: '/media-thumbnail/presets', method: 'post', data })
}

// 更新预设
export function updatePreset(id, data) {
  return request({ url: `/media-thumbnail/presets/${id}`, method: 'put', data })
}

// 删除预设
export function deletePreset(id) {
  return request({ url: `/media-thumbnail/presets/${id}`, method: 'delete' })
}

// ========== 缩略图生成 ==========

// 为单个媒体生成缩略图
export function generateThumbnail(data) {
  return request({ url: '/media-thumbnail/generate', method: 'post', data })
}

// 批量生成缩略图
export function batchGenerate(data) {
  return request({ url: '/media-thumbnail/batch-generate', method: 'post', data })
}

// 重新生成媒体的所有缩略图
export function regenerateThumbnails(mediaId) {
  return request({ url: '/media-thumbnail/regenerate', method: 'post', data: { media_id: mediaId } })
}

// 删除媒体的所有缩略图
export function deleteAllThumbnails(mediaId) {
  return request({ url: '/media-thumbnail/delete-all', method: 'post', data: { media_id: mediaId } })
}
