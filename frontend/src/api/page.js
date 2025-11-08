import request from '@/utils/request'

/**
 * 获取单页列表
 */
export function getPages(params) {
  return request({
    url: '/pages',
    method: 'get',
    params
  })
}

/**
 * 获取单页详情
 */
export function getPageDetail(id) {
  return request({
    url: `/pages/${id}`,
    method: 'get'
  })
}

/**
 * 创建单页
 */
export function createPage(data) {
  return request({
    url: '/pages',
    method: 'post',
    data
  })
}

/**
 * 更新单页
 */
export function updatePage(id, data) {
  return request({
    url: `/pages/${id}`,
    method: 'put',
    data
  })
}

/**
 * 删除单页
 */
export function deletePage(id) {
  return request({
    url: `/pages/${id}`,
    method: 'delete'
  })
}

/**
 * 获取所有已发布的单页
 */
export function getAllPages() {
  return request({
    url: '/pages/all',
    method: 'get'
  })
}
