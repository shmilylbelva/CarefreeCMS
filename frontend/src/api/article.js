import request from './request'
import { cachedRequest } from '@/utils/localCache'

// 获取文章列表
export function getArticleList(params) {
  return request({
    url: '/articles',
    method: 'get',
    params
  })
}

// 获取热门文章（带前端缓存，10分钟）
export function getHotArticles(params = {}) {
  const cacheKey = cachedRequest.generateKey('/articles/hot', params)
  return cachedRequest.request(
    () => request({
      url: '/articles/hot',
      method: 'get',
      params
    }),
    cacheKey,
    { expire: 10 * 60 * 1000 } // 10分钟缓存
  )
}

// 获取推荐文章（带前端缓存，10分钟）
export function getRecommendArticles(params = {}) {
  const cacheKey = cachedRequest.generateKey('/articles/recommend', params)
  return cachedRequest.request(
    () => request({
      url: '/articles/recommend',
      method: 'get',
      params
    }),
    cacheKey,
    { expire: 10 * 60 * 1000 } // 10分钟缓存
  )
}

// AI生成文章内容
export function generateArticleContent(data) {
  return request({
    url: '/articles/generate-content',
    method: 'post',
    data
  })
}

// 获取文章详情
export function getArticleDetail(id) {
  return request({
    url: `/articles/${id}`,
    method: 'get'
  })
}

// 创建文章
export function createArticle(data) {
  return request({
    url: '/articles',
    method: 'post',
    data
  })
}

// 更新文章
export function updateArticle(id, data) {
  return request({
    url: `/articles/${id}`,
    method: 'put',
    data
  })
}

// 删除文章
export function deleteArticle(id, data = {}) {
  return request({
    url: `/articles/${id}`,
    method: 'delete',
    data
  })
}

// 发布文章 (使用RESTful PATCH方式)
export function publishArticle(id) {
  return request({
    url: `/articles/${id}`,
    method: 'patch',
    data: { status: 'published' }
  })
}

// 下线文章 (使用RESTful PATCH方式)
export function offlineArticle(id) {
  return request({
    url: `/articles/${id}`,
    method: 'patch',
    data: { status: 'offline' }
  })
}

// 部分更新文章 (通用PATCH方法，可用于任意字段更新)
export function patchArticle(id, data) {
  return request({
    url: `/articles/${id}`,
    method: 'patch',
    data
  })
}

// 全文搜索
export function fullTextSearch(params) {
  return request({
    url: '/articles/fulltext-search',
    method: 'get',
    params
  })
}

// 高级搜索
export function advancedSearch(params) {
  return request({
    url: '/articles/advanced-search',
    method: 'get',
    params
  })
}

// 搜索建议
export function searchSuggestions(keyword, limit = 10) {
  return request({
    url: '/articles/search-suggestions',
    method: 'get',
    params: { keyword, limit }
  })
}
