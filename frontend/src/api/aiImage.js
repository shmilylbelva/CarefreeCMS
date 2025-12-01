import request from '@/utils/request'

/**
 * AI图片生成API
 */

// 生成AI图片
export function generateAiImage(data) {
  return request({
    url: '/ai-image/generate',
    method: 'post',
    data
  })
}

// 批量生成AI图片
export function batchGenerateAiImage(data) {
  return request({
    url: '/ai-image/batch-generate',
    method: 'post',
    data
  })
}

// 获取生成进度
export function getGenerationProgress(taskId) {
  return request({
    url: `/ai-image/progress/${taskId}`,
    method: 'get'
  })
}

// 取消生成
export function cancelGeneration(taskId) {
  return request({
    url: `/ai-image/cancel/${taskId}`,
    method: 'post'
  })
}

// 获取AI图片列表
export function getAiImageList(params) {
  return request({
    url: '/ai-image',
    method: 'get',
    params
  })
}

// 删除AI图片
export function deleteAiImage(id) {
  return request({
    url: `/ai-image/${id}`,
    method: 'delete'
  })
}

// 重新生成
export function regenerateAiImage(id, data) {
  return request({
    url: `/ai-image/${id}/regenerate`,
    method: 'post',
    data
  })
}

// 图生图（以图片为基础生成）
export function imageToImage(data) {
  return request({
    url: '/ai-image/image-to-image',
    method: 'post',
    data,
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

// 图片放大/超分辨率
export function upscaleImage(data) {
  return request({
    url: '/ai-image/upscale',
    method: 'post',
    data
  })
}

// 图片修复/inpainting
export function inpaintImage(data) {
  return request({
    url: '/ai-image/inpaint',
    method: 'post',
    data,
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

// 图片扩展/outpainting
export function outpaintImage(data) {
  return request({
    url: '/ai-image/outpaint',
    method: 'post',
    data,
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

// 移除背景
export function removeBackground(data) {
  return request({
    url: '/ai-image/remove-background',
    method: 'post',
    data,
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

// 风格转换
export function styleTransfer(data) {
  return request({
    url: '/ai-image/style-transfer',
    method: 'post',
    data,
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

// 获取AI模型列表
export function getAiModels(params) {
  return request({
    url: '/ai-image/models',
    method: 'get',
    params
  })
}

// 获取模型详情
export function getAiModelDetail(id) {
  return request({
    url: `/ai-image/models/${id}`,
    method: 'get'
  })
}

// 获取风格列表
export function getStyles(params) {
  return request({
    url: '/ai-image/styles',
    method: 'get',
    params
  })
}

// 创建风格
export function createStyle(data) {
  return request({
    url: '/ai-image/styles',
    method: 'post',
    data
  })
}

// 更新风格
export function updateStyle(id, data) {
  return request({
    url: `/ai-image/styles/${id}`,
    method: 'put',
    data
  })
}

// 删除风格
export function deleteStyle(id) {
  return request({
    url: `/ai-image/styles/${id}`,
    method: 'delete'
  })
}

// 获取提示词模板
export function getPromptTemplates(params) {
  return request({
    url: '/ai-image/prompt-templates',
    method: 'get',
    params
  })
}

// 创建提示词模板
export function createPromptTemplate(data) {
  return request({
    url: '/ai-image/prompt-templates',
    method: 'post',
    data
  })
}

// 更新提示词模板
export function updatePromptTemplate(id, data) {
  return request({
    url: `/ai-image/prompt-templates/${id}`,
    method: 'put',
    data
  })
}

// 删除提示词模板
export function deletePromptTemplate(id) {
  return request({
    url: `/ai-image/prompt-templates/${id}`,
    method: 'delete'
  })
}

// 优化提示词
export function optimizePrompt(data) {
  return request({
    url: '/ai-image/optimize-prompt',
    method: 'post',
    data
  })
}

// 获取生成参数预设
export function getGenerationPresets() {
  return request({
    url: '/ai-image/generation-presets',
    method: 'get'
  })
}

// 保存生成参数预设
export function saveGenerationPreset(data) {
  return request({
    url: '/ai-image/generation-presets',
    method: 'post',
    data
  })
}

// 获取生成历史
export function getGenerationHistory(params) {
  return request({
    url: '/ai-image/history',
    method: 'get',
    params
  })
}

// 收藏AI图片
export function favoriteAiImage(id) {
  return request({
    url: `/ai-image/${id}/favorite`,
    method: 'post'
  })
}

// 取消收藏
export function unfavoriteAiImage(id) {
  return request({
    url: `/ai-image/${id}/unfavorite`,
    method: 'post'
  })
}

// 获取配额信息
export function getQuotaInfo() {
  return request({
    url: '/ai-image/quota',
    method: 'get'
  })
}

// 获取费用统计
export function getCostStats(params) {
  return request({
    url: '/ai-image/cost-stats',
    method: 'get',
    params
  })
}
