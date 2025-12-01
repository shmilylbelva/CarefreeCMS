import request from '@/utils/request'

/**
 * 媒体编辑API
 */

// 裁剪图片
export function cropImage(data) {
  return request({
    url: '/media/crop',
    method: 'post',
    data
  })
}

// 调整图片大小
export function resizeImage(data) {
  return request({
    url: '/media/resize',
    method: 'post',
    data
  })
}

// 旋转图片
export function rotateImage(data) {
  return request({
    url: '/media/rotate',
    method: 'post',
    data
  })
}

// 翻转图片
export function flipImage(data) {
  return request({
    url: '/media/flip',
    method: 'post',
    data
  })
}

// 添加水印
export function addWatermark(data) {
  return request({
    url: '/media/watermark',
    method: 'post',
    data
  })
}

// 批量添加水印
export function batchWatermark(data) {
  return request({
    url: '/media/batch-watermark',
    method: 'post',
    data
  })
}

// 生成缩略图
export function generateThumbnail(data) {
  return request({
    url: '/media/thumbnail',
    method: 'post',
    data
  })
}

// 批量生成缩略图
export function batchThumbnail(data) {
  return request({
    url: '/media/batch-thumbnail',
    method: 'post',
    data
  })
}

// 压缩图片
export function compressImage(data) {
  return request({
    url: '/media/compress',
    method: 'post',
    data
  })
}

// 批量压缩图片
export function batchCompress(data) {
  return request({
    url: '/media/batch-compress',
    method: 'post',
    data
  })
}

// 转换格式
export function convertFormat(data) {
  return request({
    url: '/media/convert-format',
    method: 'post',
    data
  })
}

// 调整亮度/对比度/饱和度
export function adjustImage(data) {
  return request({
    url: '/media/adjust',
    method: 'post',
    data
  })
}

// 应用滤镜
export function applyFilter(data) {
  return request({
    url: '/media/filter',
    method: 'post',
    data
  })
}

// 获取可用滤镜列表
export function getFilters() {
  return request({
    url: '/media/filters',
    method: 'get'
  })
}

// 获取水印模板列表
export function getWatermarkTemplates(params) {
  return request({
    url: '/watermark-template',
    method: 'get',
    params
  })
}

// 创建水印模板
export function createWatermarkTemplate(data) {
  return request({
    url: '/watermark-template',
    method: 'post',
    data
  })
}

// 更新水印模板
export function updateWatermarkTemplate(id, data) {
  return request({
    url: `/watermark-template/${id}`,
    method: 'put',
    data
  })
}

// 删除水印模板
export function deleteWatermarkTemplate(id) {
  return request({
    url: `/watermark-template/${id}`,
    method: 'delete'
  })
}

// 获取缩略图配置
export function getThumbnailConfigs(params) {
  return request({
    url: '/thumbnail-config',
    method: 'get',
    params
  })
}

// 创建缩略图配置
export function createThumbnailConfig(data) {
  return request({
    url: '/thumbnail-config',
    method: 'post',
    data
  })
}

// 更新缩略图配置
export function updateThumbnailConfig(id, data) {
  return request({
    url: `/thumbnail-config/${id}`,
    method: 'put',
    data
  })
}

// 删除缩略图配置
export function deleteThumbnailConfig(id) {
  return request({
    url: `/thumbnail-config/${id}`,
    method: 'delete'
  })
}

// 获取编辑历史
export function getEditHistory(mediaId, params) {
  return request({
    url: `/media/${mediaId}/edit-history`,
    method: 'get',
    params
  })
}

// 恢复到历史版本
export function restoreVersion(mediaId, versionId) {
  return request({
    url: `/media/${mediaId}/restore/${versionId}`,
    method: 'post'
  })
}

// 删除历史版本
export function deleteVersion(mediaId, versionId) {
  return request({
    url: `/media/${mediaId}/version/${versionId}`,
    method: 'delete'
  })
}
