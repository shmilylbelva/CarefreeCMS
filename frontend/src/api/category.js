import request from './request'
import { cachedRequest, localCache } from '@/utils/localCache'

// 分类缓存键前缀（包含 localCache 使用的 cms_cache_ 前缀）
const CATEGORY_CACHE_PREFIX = 'cms_cache_request_/categories/tree'

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

// 清除所有分类缓存
export function clearCategoryCache() {
  try {
    // 遍历 localStorage 清除所有分类树缓存
    const keys = Object.keys(localStorage)
    keys.forEach(key => {
      if (key.includes(CATEGORY_CACHE_PREFIX)) {
        localStorage.removeItem(key)
      }
    })
    console.log('[CategoryCache] 已清除分类缓存')
  } catch (error) {
    console.error('[CategoryCache] 清除缓存失败:', error)
  }
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
