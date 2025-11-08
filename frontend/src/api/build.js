import request from './request'

// 生成所有静态页面
export function buildAll() {
  return request({ url: '/build/all', method: 'post' })
}

// 生成首页
export function buildIndex() {
  return request({ url: '/build/index', method: 'post' })
}

// 生成文章列表页
export function buildArticles() {
  return request({ url: '/build/articles', method: 'post' })
}

// 生成文章详情页
export function buildArticle(id) {
  return request({ url: `/build/article/${id}`, method: 'post' })
}

// 生成所有分类页
export function buildCategories() {
  return request({ url: '/build/categories', method: 'post' })
}

// 生成分类页
export function buildCategory(id) {
  return request({ url: `/build/category/${id}`, method: 'post' })
}

// 生成所有标签页
export function buildTags() {
  return request({ url: '/build/tags', method: 'post' })
}

// 生成标签页
export function buildTag(id) {
  return request({ url: `/build/tag/${id}`, method: 'post' })
}

// 生成所有专题页
export function buildTopics() {
  return request({ url: '/build/topics', method: 'post' })
}

// 生成专题页
export function buildTopic(id) {
  return request({ url: `/build/topic/${id}`, method: 'post' })
}

// 生成所有单页面
export function buildPages() {
  return request({ url: '/build/pages', method: 'post' })
}

// 生成单个单页面
export function buildPage(id) {
  return request({ url: `/build/page/${id}`, method: 'post' })
}

// 获取生成日志
export function getBuildLogs(params) {
  return request({ url: '/build/logs', method: 'get', params })
}
