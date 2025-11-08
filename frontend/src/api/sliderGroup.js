import request from '@/utils/request'

/**
 * 获取幻灯片组列表（分页）
 */
export function getSliderGroupList(params) {
  return request({
    url: '/slider-groups',
    method: 'get',
    params
  })
}

/**
 * 获取所有幻灯片组（不分页）
 */
export function getAllSliderGroups(params) {
  return request({
    url: '/slider-groups/all',
    method: 'get',
    params
  })
}

/**
 * 获取幻灯片组详情
 */
export function getSliderGroup(id) {
  return request({
    url: `/slider-groups/${id}`,
    method: 'get'
  })
}

/**
 * 创建幻灯片组
 */
export function createSliderGroup(data) {
  return request({
    url: '/slider-groups',
    method: 'post',
    data
  })
}

/**
 * 更新幻灯片组
 */
export function updateSliderGroup(id, data) {
  return request({
    url: `/slider-groups/${id}`,
    method: 'put',
    data
  })
}

/**
 * 删除幻灯片组
 */
export function deleteSliderGroup(id) {
  return request({
    url: `/slider-groups/${id}`,
    method: 'delete'
  })
}
