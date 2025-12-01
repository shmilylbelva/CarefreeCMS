import request from './request'

/**
 * 生成所有静态页面
 * @param {object} params 参数对象，包含 site_id
 */
export function buildAll(params) {
  return request({
    url: '/build/all',
    method: 'post',
    params
  })
}

/**
 * 生成首页
 * @param {object} params 参数对象，包含 site_id
 */
export function buildIndex(params) {
  return request({
    url: '/build/index',
    method: 'post',
    params
  })
}

/**
 * 生成文章列表页
 * @param {object} params 参数对象，包含 site_id
 */
export function buildArticles(params) {
  return request({
    url: '/build/articles',
    method: 'post',
    params
  })
}

/**
 * 生成文章详情页
 * @param {number} id 文章ID
 * @param {object} params 参数对象，包含 site_id
 */
export function buildArticle(id, params) {
  return request({
    url: `/build/article/${id}`,
    method: 'post',
    params
  })
}

/**
 * 生成所有分类页
 * @param {object} params 参数对象，包含 site_id
 */
export function buildCategories(params) {
  return request({
    url: '/build/categories',
    method: 'post',
    params
  })
}

/**
 * 生成单个分类页
 * @param {number} id 分类ID
 * @param {object} params 参数对象，包含 site_id
 */
export function buildCategory(id, params) {
  return request({
    url: `/build/category/${id}`,
    method: 'post',
    params
  })
}

/**
 * 生成所有标签页
 * @param {object} params 参数对象，包含 site_id
 */
export function buildTags(params) {
  return request({
    url: '/build/tags',
    method: 'post',
    params
  })
}

/**
 * 生成单个标签页
 * @param {number} id 标签ID
 * @param {object} params 参数对象，包含 site_id
 */
export function buildTag(id, params) {
  return request({
    url: `/build/tag/${id}`,
    method: 'post',
    params
  })
}

/**
 * 生成所有专题页
 * @param {object} params 参数对象，包含 site_id
 */
export function buildTopics(params) {
  return request({
    url: '/build/topics',
    method: 'post',
    params
  })
}

/**
 * 生成单个专题页
 * @param {number} id 专题ID
 * @param {object} params 参数对象，包含 site_id
 */
export function buildTopic(id, params) {
  return request({
    url: `/build/topic/${id}`,
    method: 'post',
    params
  })
}

/**
 * 生成所有单页
 * @param {object} params 参数对象，包含 site_id
 */
export function buildPages(params) {
  return request({
    url: '/build/pages',
    method: 'post',
    params
  })
}

/**
 * 生成单个单页
 * @param {number} id 页面ID
 * @param {object} params 参数对象，包含 site_id
 */
export function buildPage(id, params) {
  return request({
    url: `/build/page/${id}`,
    method: 'post',
    params
  })
}

/**
 * 同步模板资源到静态目录
 */
export function syncAssets() {
  return request({
    url: '/build/sync-assets',
    method: 'post'
  })
}

/**
 * 清理旧资源
 */
export function cleanAssets() {
  return request({
    url: '/build/clean-assets',
    method: 'post'
  })
}

/**
 * 获取资源列表
 */
export function getAssetsList() {
  return request({
    url: '/build/assets-list',
    method: 'get'
  })
}

/**
 * 获取生成日志
 * @param {object} params 查询参数
 */
export function getBuildLogs(params) {
  return request({
    url: '/build/logs',
    method: 'get',
    params
  })
}
