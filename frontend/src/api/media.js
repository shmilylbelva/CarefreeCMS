import request from '@/utils/request'

/**
 * 媒体库API
 */

// 获取媒体列表
export function getMediaList(params) {
  return request({
    url: '/media',
    method: 'get',
    params
  })
}

// 获取媒体详情
export function getMediaDetail(id) {
  return request({
    url: `/media/${id}`,
    method: 'get'
  })
}

// 上传媒体文件
export function uploadMedia(data) {
  return request({
    url: '/media/upload',
    method: 'post',
    data,
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

// 更新媒体信息
export function updateMedia(id, data) {
  return request({
    url: `/media/${id}`,
    method: 'put',
    data
  })
}

// 删除媒体
export function deleteMedia(id, data) {
  return request({
    url: `/media/${id}`,
    method: 'delete',
    data
  })
}

// 批量上传
export function batchUpload(data) {
  return request({
    url: '/media/batch-upload',
    method: 'post',
    data,
    headers: { 'Content-Type': 'multipart/form-data' }
  })
}

// 获取分类列表
export function getCategories() {
  return request({
    url: '/media-category',
    method: 'get'
  })
}

// 创建分类
export function createCategory(data) {
  return request({
    url: '/media-category',
    method: 'post',
    data
  })
}

// 更新分类
export function updateCategory(id, data) {
  return request({
    url: `/media-category/${id}`,
    method: 'put',
    data
  })
}

// 删除分类
export function deleteCategory(id) {
  return request({
    url: `/media-category/${id}`,
    method: 'delete'
  })
}

// 获取标签列表
export function getTags(params) {
  return request({
    url: '/media-tag',
    method: 'get',
    params
  })
}

// 创建标签
export function createTag(data) {
  return request({
    url: '/media-tag',
    method: 'post',
    data
  })
}

// 获取统计信息
export function getMediaStats() {
  return request({
    url: '/media/stats',
    method: 'get'
  })
}
