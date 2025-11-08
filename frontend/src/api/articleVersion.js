import request from './request'

// 获取文章的版本列表
export function getArticleVersions(articleId, params) {
  return request({
    url: `/articles/${articleId}/versions`,
    method: 'get',
    params
  })
}

// 获取版本统计信息
export function getVersionStatistics(articleId) {
  return request({
    url: `/articles/${articleId}/versions/statistics`,
    method: 'get'
  })
}

// 获取版本详情
export function getVersionDetail(id) {
  return request({
    url: `/article-versions/${id}`,
    method: 'get'
  })
}

// 对比两个版本
export function compareVersions(oldVersionId, newVersionId) {
  return request({
    url: '/article-versions/compare',
    method: 'get',
    params: {
      old_version_id: oldVersionId,
      new_version_id: newVersionId
    }
  })
}

// 回滚到指定版本
export function rollbackToVersion(id) {
  return request({
    url: `/article-versions/${id}/rollback`,
    method: 'post'
  })
}

// 删除版本
export function deleteVersion(id) {
  return request({
    url: `/article-versions/${id}`,
    method: 'delete'
  })
}

// 批量删除版本
export function batchDeleteVersions(ids) {
  return request({
    url: '/article-versions/batch-delete',
    method: 'post',
    data: { ids }
  })
}
