import request from '@/utils/request'

// 获取文章属性列表
export function getArticleFlags(params) {
  return request({
    url: '/article-flags',
    method: 'get',
    params
  })
}

// 获取所有启用的文章属性（不分页）
export function getAllArticleFlags(params) {
  return request({
    url: '/article-flags/all',
    method: 'get',
    params
  })
}

// 获取文章属性详情
export function getArticleFlagDetail(id) {
  return request({
    url: `/article-flags/${id}`,
    method: 'get'
  })
}

// 创建文章属性
export function createArticleFlag(data) {
  return request({
    url: '/article-flags',
    method: 'post',
    data
  })
}

// 更新文章属性
export function updateArticleFlag(id, data) {
  return request({
    url: `/article-flags/${id}`,
    method: 'put',
    data
  })
}

// 删除文章属性
export function deleteArticleFlag(id) {
  return request({
    url: `/article-flags/${id}`,
    method: 'delete'
  })
}
