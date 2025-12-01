import request from '@/utils/request'

/**
 * 分片上传API
 */

// 初始化分片上传会话
export function initChunkedUpload(data) {
  return request({
    url: '/chunked-upload/init',
    method: 'post',
    data
  })
}

// 上传分片
export function uploadChunk(uploadId, chunkIndex, chunk, chunkHash) {
  const formData = new FormData()
  formData.append('upload_id', uploadId)
  formData.append('chunk_index', chunkIndex)
  formData.append('chunk', chunk)
  if (chunkHash) {
    formData.append('chunk_hash', chunkHash)
  }

  return request({
    url: '/chunked-upload/chunk',
    method: 'post',
    data: formData,
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

// 合并分片
export function mergeChunks(uploadId, data = {}) {
  return request({
    url: '/chunked-upload/merge',
    method: 'post',
    data: {
      upload_id: uploadId,
      ...data
    }
  })
}

// 获取上传进度
export function getUploadProgress(uploadId) {
  return request({
    url: '/chunked-upload/progress',
    method: 'get',
    params: { upload_id: uploadId }
  })
}

// 取消上传
export function cancelUpload(uploadId) {
  return request({
    url: '/chunked-upload/cancel',
    method: 'post',
    data: { upload_id: uploadId }
  })
}
