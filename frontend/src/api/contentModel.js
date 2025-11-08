import request from './request'

// 获取内容模型列表
export function getContentModelList(params) {
  return request({
    url: '/content-models',
    method: 'get',
    params
  })
}

// 获取所有内容模型（不分页）
export function getAllContentModels() {
  return request({
    url: '/content-models/all',
    method: 'get'
  })
}

// 获取内容模型详情
export function getContentModelDetail(id) {
  return request({
    url: `/content-models/${id}`,
    method: 'get'
  })
}

// 创建内容模型
export function createContentModel(data) {
  return request({
    url: '/content-models',
    method: 'post',
    data
  })
}

// 更新内容模型
export function updateContentModel(id, data) {
  return request({
    url: `/content-models/${id}`,
    method: 'put',
    data
  })
}

// 删除内容模型
export function deleteContentModel(id) {
  return request({
    url: `/content-models/${id}`,
    method: 'delete'
  })
}
