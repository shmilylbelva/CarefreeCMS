import request from '@/utils/request'

/**
 * 视频处理API
 */

// 获取视频信息
export function getVideoInfo(id) {
  return request({
    url: `/video/${id}/info`,
    method: 'get'
  })
}

// 转码视频
export function transcodeVideo(data) {
  return request({
    url: '/video/transcode',
    method: 'post',
    data
  })
}

// 批量转码
export function batchTranscode(data) {
  return request({
    url: '/video/batch-transcode',
    method: 'post',
    data
  })
}

// 获取转码进度
export function getTranscodeProgress(taskId) {
  return request({
    url: `/video/transcode/progress/${taskId}`,
    method: 'get'
  })
}

// 取消转码
export function cancelTranscode(taskId) {
  return request({
    url: `/video/transcode/cancel/${taskId}`,
    method: 'post'
  })
}

// 截取视频片段
export function clipVideo(data) {
  return request({
    url: '/video/clip',
    method: 'post',
    data
  })
}

// 提取视频封面
export function extractCover(data) {
  return request({
    url: '/video/extract-cover',
    method: 'post',
    data
  })
}

// 批量提取封面
export function batchExtractCover(data) {
  return request({
    url: '/video/batch-extract-cover',
    method: 'post',
    data
  })
}

// 生成视频缩略图
export function generateVideoThumbnail(data) {
  return request({
    url: '/video/thumbnail',
    method: 'post',
    data
  })
}

// 添加视频水印
export function addVideoWatermark(data) {
  return request({
    url: '/video/watermark',
    method: 'post',
    data
  })
}

// 合并视频
export function mergeVideos(data) {
  return request({
    url: '/video/merge',
    method: 'post',
    data
  })
}

// 视频转GIF
export function videoToGif(data) {
  return request({
    url: '/video/to-gif',
    method: 'post',
    data
  })
}

// 调整视频速度
export function adjustSpeed(data) {
  return request({
    url: '/video/adjust-speed',
    method: 'post',
    data
  })
}

// 旋转视频
export function rotateVideo(data) {
  return request({
    url: '/video/rotate',
    method: 'post',
    data
  })
}

// 调整视频分辨率
export function resizeVideo(data) {
  return request({
    url: '/video/resize',
    method: 'post',
    data
  })
}

// 获取转码配置列表
export function getTranscodeConfigs(params) {
  return request({
    url: '/video-transcode-config',
    method: 'get',
    params
  })
}

// 创建转码配置
export function createTranscodeConfig(data) {
  return request({
    url: '/video-transcode-config',
    method: 'post',
    data
  })
}

// 更新转码配置
export function updateTranscodeConfig(id, data) {
  return request({
    url: `/video-transcode-config/${id}`,
    method: 'put',
    data
  })
}

// 删除转码配置
export function deleteTranscodeConfig(id) {
  return request({
    url: `/video-transcode-config/${id}`,
    method: 'delete'
  })
}

// 获取转码预设列表
export function getTranscodePresets() {
  return request({
    url: '/video/transcode-presets',
    method: 'get'
  })
}

// 获取支持的视频格式
export function getSupportedFormats() {
  return request({
    url: '/video/supported-formats',
    method: 'get'
  })
}

// 获取支持的编解码器
export function getSupportedCodecs() {
  return request({
    url: '/video/supported-codecs',
    method: 'get'
  })
}

// 分析视频质量
export function analyzeQuality(id) {
  return request({
    url: `/video/${id}/analyze-quality`,
    method: 'get'
  })
}

// 添加字幕
export function addSubtitle(data) {
  return request({
    url: '/video/add-subtitle',
    method: 'post',
    data
  })
}

// 提取音频
export function extractAudio(data) {
  return request({
    url: '/video/extract-audio',
    method: 'post',
    data
  })
}

// 替换音频
export function replaceAudio(data) {
  return request({
    url: '/video/replace-audio',
    method: 'post',
    data
  })
}

// 调整音量
export function adjustVolume(data) {
  return request({
    url: '/video/adjust-volume',
    method: 'post',
    data
  })
}

// 获取视频处理历史
export function getVideoProcessHistory(id, params) {
  return request({
    url: `/video/${id}/process-history`,
    method: 'get',
    params
  })
}
