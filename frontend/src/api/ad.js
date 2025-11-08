import request from '@/utils/request'

/**
 * 获取广告列表
 */
export function getAdList(params) {
  return request({
    url: '/ads',
    method: 'get',
    params
  })
}

/**
 * 获取广告详情
 */
export function getAdDetail(id) {
  return request({
    url: `/ads/${id}`,
    method: 'get'
  })
}

/**
 * 创建广告
 */
export function createAd(data) {
  return request({
    url: '/ads',
    method: 'post',
    data
  })
}

/**
 * 更新广告
 */
export function updateAd(id, data) {
  return request({
    url: `/ads/${id}`,
    method: 'put',
    data
  })
}

/**
 * 删除广告
 */
export function deleteAd(id) {
  return request({
    url: `/ads/${id}`,
    method: 'delete'
  })
}

/**
 * 获取广告统计
 */
export function getAdStatistics(id, params) {
  return request({
    url: `/ads/${id}/statistics`,
    method: 'get',
    params
  })
}

/**
 * 记录广告点击
 */
export function recordAdClick(id) {
  return request({
    url: `/ads/${id}/click`,
    method: 'post'
  })
}
