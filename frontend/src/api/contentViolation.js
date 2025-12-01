import request from '@/utils/request'

/**
 * 获取违规记录列表
 * @param {Object} params
 * @returns {Promise}
 */
export function getContentViolations(params) {
  return request({
    url: '/content-violations',
    method: 'get',
    params
  })
}

/**
 * 获取违规记录详情
 * @param {Number} id
 * @returns {Promise}
 */
export function getContentViolation(id) {
  return request({
    url: `/content-violations/${id}`,
    method: 'get'
  })
}

/**
 * 标记为已审核
 * @param {Number} id
 * @returns {Promise}
 */
export function markViolationAsReviewed(id) {
  return request({
    url: `/content-violations/${id}/mark-reviewed`,
    method: 'post'
  })
}

/**
 * 标记为已忽略
 * @param {Number} id
 * @returns {Promise}
 */
export function markViolationAsIgnored(id) {
  return request({
    url: `/content-violations/${id}/mark-ignored`,
    method: 'post'
  })
}

/**
 * 批量审核
 * @param {Array} ids
 * @param {String} status - 'reviewed' 或 'ignored'
 * @returns {Promise}
 */
export function batchReviewViolations(ids, status) {
  return request({
    url: '/content-violations/batch-review',
    method: 'post',
    data: { ids, status }
  })
}

/**
 * 删除违规记录
 * @param {Number} id
 * @returns {Promise}
 */
export function deleteContentViolation(id) {
  return request({
    url: `/content-violations/${id}`,
    method: 'delete'
  })
}

/**
 * 获取统计信息
 * @returns {Promise}
 */
export function getContentViolationStatistics() {
  return request({
    url: '/content-violations/statistics',
    method: 'get'
  })
}
