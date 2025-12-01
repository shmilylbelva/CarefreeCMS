import request from './request'

// 获取水印预设列表
export function getPresets(params) {
  return request({ url: '/media-watermark/presets', method: 'get', params })
}

// 获取水印预设详情
export function getPresetDetail(id) {
  return request({ url: `/media-watermark/presets/${id}`, method: 'get' })
}

// 创建水印预设
export function createPreset(data) {
  return request({ url: '/media-watermark/presets', method: 'post', data })
}

// 更新水印预设
export function updatePreset(id, data) {
  return request({ url: `/media-watermark/presets/${id}`, method: 'put', data })
}

// 删除水印预设
export function deletePreset(id) {
  return request({ url: `/media-watermark/presets/${id}`, method: 'delete' })
}

// 添加水印
export function addWatermark(data) {
  return request({ url: '/media-watermark/add', method: 'post', data })
}

// 批量添加水印
export function batchAddWatermark(data) {
  return request({ url: '/media-watermark/batch-add', method: 'post', data })
}

// 获取水印处理日志
export function getWatermarkLogs(params) {
  return request({ url: '/media-watermark/logs', method: 'get', params })
}

// 推送批量水印任务到队列
export function pushBatchWatermarkJob(data) {
  return request({ url: '/queue/batch-watermark', method: 'post', data })
}
