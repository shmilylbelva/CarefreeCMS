import request from '@/utils/request'

/**
 * 获取友情链接列表
 */
export function getLinkList(params) {
  return request({
    url: '/links',
    method: 'get',
    params
  })
}

/**
 * 获取友情链接详情
 */
export function getLinkDetail(id) {
  return request({
    url: `/links/${id}`,
    method: 'get'
  })
}

/**
 * 创建友情链接
 */
export function createLink(data) {
  return request({
    url: '/links',
    method: 'post',
    data
  })
}

/**
 * 更新友情链接
 */
export function updateLink(id, data) {
  return request({
    url: `/links/${id}`,
    method: 'put',
    data
  })
}

/**
 * 删除友情链接
 */
export function deleteLink(id) {
  return request({
    url: `/links/${id}`,
    method: 'delete'
  })
}

/**
 * 审核友情链接
 */
export function auditLink(id, action, note = '') {
  return request({
    url: `/links/${id}/audit`,
    method: 'post',
    data: {
      action,
      note
    }
  })
}
