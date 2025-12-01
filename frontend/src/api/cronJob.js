import request from '@/utils/request'

/**
 * 获取定时任务列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export function getCronJobList(params) {
  return request({
    url: '/cron-jobs',
    method: 'get',
    params
  })
}

/**
 * 获取定时任务详情
 * @param {Number} id 任务ID
 * @returns {Promise}
 */
export function getCronJobDetail(id) {
  return request({
    url: `/cron-jobs/${id}`,
    method: 'get'
  })
}

/**
 * 创建定时任务
 * @param {Object} data 任务数据
 * @returns {Promise}
 */
export function createCronJob(data) {
  return request({
    url: '/cron-jobs',
    method: 'post',
    data
  })
}

/**
 * 更新定时任务
 * @param {Number} id 任务ID
 * @param {Object} data 任务数据
 * @returns {Promise}
 */
export function updateCronJob(id, data) {
  return request({
    url: `/cron-jobs/${id}`,
    method: 'put',
    data
  })
}

/**
 * 删除定时任务
 * @param {Number} id 任务ID
 * @returns {Promise}
 */
export function deleteCronJob(id) {
  return request({
    url: `/cron-jobs/${id}`,
    method: 'delete'
  })
}

/**
 * 批量删除定时任务
 * @param {Array} ids 任务ID数组
 * @returns {Promise}
 */
export function batchDeleteCronJobs(ids) {
  return request({
    url: '/cron-jobs/batch-delete',
    method: 'post',
    data: { ids }
  })
}

/**
 * 更新任务状态
 * @param {Number} id 任务ID
 * @param {Number} is_enabled 启用状态 (0-禁用, 1-启用)
 * @returns {Promise}
 */
export function updateCronJobStatus(id, is_enabled) {
  return request({
    url: `/cron-jobs/${id}/status`,
    method: 'put',
    data: { is_enabled }
  })
}

/**
 * 手动执行任务
 * @param {Number} id 任务ID
 * @returns {Promise}
 */
export function runCronJob(id) {
  return request({
    url: `/cron-jobs/${id}/run`,
    method: 'post'
  })
}

/**
 * 获取任务日志列表
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export function getCronJobLogList(params) {
  return request({
    url: '/cron-jobs/logs',
    method: 'get',
    params
  })
}

/**
 * 获取指定任务的日志
 * @param {Number} id 任务ID
 * @param {Number} limit 数量限制
 * @returns {Promise}
 */
export function getJobLogs(id, limit = 50) {
  return request({
    url: `/cron-jobs/${id}/logs`,
    method: 'get',
    params: { limit }
  })
}

/**
 * 清理日志
 * @param {Number} days 保留天数
 * @returns {Promise}
 */
export function cleanCronJobLogs(days = 30) {
  return request({
    url: '/cron-jobs/clean-logs',
    method: 'post',
    data: { days }
  })
}

/**
 * 验证Cron表达式
 * @param {String} expression Cron表达式
 * @returns {Promise}
 */
export function validateCronExpression(expression) {
  return request({
    url: '/cron-jobs/validate-cron',
    method: 'post',
    data: { expression }
  })
}

/**
 * 获取预设任务列表
 * @returns {Promise}
 */
export function getCronJobPresets() {
  return request({
    url: '/cron-jobs/presets',
    method: 'get'
  })
}

/**
 * 获取常用Cron表达式
 * @returns {Promise}
 */
export function getCronExpressions() {
  return request({
    url: '/cron-jobs/cron-expressions',
    method: 'get'
  })
}
