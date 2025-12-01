import request from '@/utils/request'

/**
 * 获取敏感词列表
 * @param {Object} params
 * @returns {Promise}
 */
export function getSensitiveWords(params) {
  return request({
    url: '/sensitive-words',
    method: 'get',
    params
  })
}

/**
 * 获取敏感词详情
 * @param {Number} id
 * @returns {Promise}
 */
export function getSensitiveWord(id) {
  return request({
    url: `/sensitive-words/${id}`,
    method: 'get'
  })
}

/**
 * 创建敏感词
 * @param {Object} data
 * @returns {Promise}
 */
export function createSensitiveWord(data) {
  return request({
    url: '/sensitive-words',
    method: 'post',
    data
  })
}

/**
 * 更新敏感词
 * @param {Number} id
 * @param {Object} data
 * @returns {Promise}
 */
export function updateSensitiveWord(id, data) {
  return request({
    url: `/sensitive-words/${id}`,
    method: 'put',
    data
  })
}

/**
 * 删除敏感词
 * @param {Number} id
 * @returns {Promise}
 */
export function deleteSensitiveWord(id) {
  return request({
    url: `/sensitive-words/${id}`,
    method: 'delete'
  })
}

/**
 * 批量删除敏感词
 * @param {Array} ids
 * @returns {Promise}
 */
export function batchDeleteSensitiveWords(ids) {
  return request({
    url: '/sensitive-words/batch-delete',
    method: 'post',
    data: { ids }
  })
}

/**
 * 批量导入敏感词
 * @param {Object} data
 * @returns {Promise}
 */
export function batchImportSensitiveWords(data) {
  return request({
    url: '/sensitive-words/batch-import',
    method: 'post',
    data
  })
}

/**
 * 批量更新状态
 * @param {Array} ids
 * @param {Number} isEnabled
 * @returns {Promise}
 */
export function batchUpdateSensitiveWordsStatus(ids, isEnabled) {
  return request({
    url: '/sensitive-words/batch-update-status',
    method: 'post',
    data: { ids, is_enabled: isEnabled }
  })
}

/**
 * 获取分类选项
 * @returns {Promise}
 */
export function getSensitiveWordCategories() {
  return request({
    url: '/sensitive-words/categories',
    method: 'get'
  })
}

/**
 * 获取级别选项
 * @returns {Promise}
 */
export function getSensitiveWordLevels() {
  return request({
    url: '/sensitive-words/levels',
    method: 'get'
  })
}

/**
 * 获取统计信息
 * @returns {Promise}
 */
export function getSensitiveWordStatistics() {
  return request({
    url: '/sensitive-words/statistics',
    method: 'get'
  })
}

/**
 * 测试敏感词检测
 * @param {String} content
 * @returns {Promise}
 */
export function testSensitiveWord(content) {
  return request({
    url: '/sensitive-words/test-check',
    method: 'post',
    data: { content }
  })
}
