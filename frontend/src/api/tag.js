import request from './request'
import { cachedRequest } from '@/utils/localCache'

export function getTagList(params) {
  return request({ url: '/tags', method: 'get', params })
}

// 获取所有标签（带前端缓存，30分钟）
// 后端已有缓存，前端再加一层减少网络请求
export function getAllTags(params = {}) {
  const cacheKey = cachedRequest.generateKey('/tags/all', params)
  return cachedRequest.request(
    () => request({ url: '/tags/all', method: 'get', params }),
    cacheKey,
    { expire: 30 * 60 * 1000 } // 30分钟缓存
  )
}

export function createTag(data) {
  return request({ url: '/tags', method: 'post', data })
}

export function updateTag(id, data) {
  return request({ url: `/tags/${id}`, method: 'put', data })
}

export function deleteTag(id) {
  return request({ url: `/tags/${id}`, method: 'delete' })
}
