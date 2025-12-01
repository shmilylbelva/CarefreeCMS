import request from './request'
import { cachedRequest } from '@/utils/localCache'

export function getCategoryList(params) {
  return request({ url: '/categories', method: 'get', params })
}

// 获取分类树（带前端缓存，30分钟）
// 后端已有缓存，前端再加一层减少网络请求
export function getCategoryTree(params = {}) {
  const cacheKey = cachedRequest.generateKey('/categories/tree', params)
  return cachedRequest.request(
    () => request({ url: '/categories/tree', method: 'get', params }),
    cacheKey,
    { expire: 30 * 60 * 1000 } // 30分钟缓存
  )
}

export function getCategoryDetail(id) {
  return request({ url: `/categories/${id}`, method: 'get' })
}

export function createCategory(data) {
  return request({ url: '/categories', method: 'post', data })
}

export function updateCategory(id, data) {
  return request({ url: `/categories/${id}`, method: 'put', data })
}

export function deleteCategory(id) {
  return request({ url: `/categories/${id}`, method: 'delete' })
}
