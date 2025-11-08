import request from '@/utils/request'

/**
 * 获取友链分组列表
 */
export function getLinkGroupList(params) {
  return request({
    url: '/link-groups',
    method: 'get',
    params
  })
}

/**
 * 获取所有友链分组（不分页）
 */
export function getAllLinkGroups() {
  return request({
    url: '/link-groups/all',
    method: 'get'
  })
}

/**
 * 获取友链分组详情
 */
export function getLinkGroupDetail(id) {
  return request({
    url: `/link-groups/${id}`,
    method: 'get'
  })
}

/**
 * 创建友链分组
 */
export function createLinkGroup(data) {
  return request({
    url: '/link-groups',
    method: 'post',
    data
  })
}

/**
 * 更新友链分组
 */
export function updateLinkGroup(id, data) {
  return request({
    url: `/link-groups/${id}`,
    method: 'put',
    data
  })
}

/**
 * 删除友链分组
 */
export function deleteLinkGroup(id) {
  return request({
    url: `/link-groups/${id}`,
    method: 'delete'
  })
}
