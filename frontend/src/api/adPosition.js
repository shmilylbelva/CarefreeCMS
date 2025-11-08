import request from '@/utils/request'

/**
 * 获取广告位列表
 */
export function getAdPositionList(params) {
  return request({
    url: '/ad-positions',
    method: 'get',
    params
  })
}

/**
 * 获取所有广告位（不分页）
 */
export function getAllAdPositions() {
  return request({
    url: '/ad-positions/all',
    method: 'get'
  })
}

/**
 * 获取广告位详情
 */
export function getAdPositionDetail(id) {
  return request({
    url: `/ad-positions/${id}`,
    method: 'get'
  })
}

/**
 * 创建广告位
 */
export function createAdPosition(data) {
  return request({
    url: '/ad-positions',
    method: 'post',
    data
  })
}

/**
 * 更新广告位
 */
export function updateAdPosition(id, data) {
  return request({
    url: `/ad-positions/${id}`,
    method: 'put',
    data
  })
}

/**
 * 删除广告位
 */
export function deleteAdPosition(id) {
  return request({
    url: `/ad-positions/${id}`,
    method: 'delete'
  })
}
