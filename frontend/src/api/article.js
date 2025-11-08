import request from './request'

// 获取文章列表
export function getArticleList(params) {
  return request({
    url: '/articles',
    method: 'get',
    params
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
export function deleteArticle(id) {
  return request({
    url: `/articles/${id}`,
    method: 'delete'
  })
}

// 发布文章
export function publishArticle(id) {
  return request({
    url: `/articles/${id}/publish`,
    method: 'post'
  })
}

// 下线文章
export function offlineArticle(id) {
  return request({
    url: `/articles/${id}/offline`,
    method: 'post'
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
