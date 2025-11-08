import request from '@/utils/request'

/**
 * 获取幻灯片列表（分页）
 */
export function getSliderList(params) {
  return request({
    url: '/sliders',
    method: 'get',
    params
  })
}

/**
 * 获取幻灯片详情
 */
export function getSlider(id) {
  return request({
    url: `/sliders/${id}`,
    method: 'get'
  })
}

/**
 * 创建幻灯片
 */
export function createSlider(data) {
  return request({
    url: '/sliders',
    method: 'post',
    data
  })
}

/**
 * 更新幻灯片
 */
export function updateSlider(id, data) {
  return request({
    url: `/sliders/${id}`,
    method: 'put',
    data
  })
}

/**
 * 删除幻灯片
 */
export function deleteSlider(id) {
  return request({
    url: `/sliders/${id}`,
    method: 'delete'
  })
}

/**
 * 记录幻灯片点击
 */
export function recordSliderClick(id) {
  return request({
    url: `/sliders/${id}/click`,
    method: 'post'
  })
}

/**
 * 记录幻灯片展示
 */
export function recordSliderView(id) {
  return request({
    url: `/sliders/${id}/view`,
    method: 'post'
  })
}

/**
 * 按分组代码获取幻灯片
 */
export function getSlidersByGroupCode(code) {
  return request({
    url: `/sliders/group/${code}`,
    method: 'get'
  })
}
