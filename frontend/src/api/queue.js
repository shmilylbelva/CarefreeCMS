import request from '@/utils/request'

/**
 * 队列管理API
 */

// 获取队列统计信息
export function getQueueStats(params) {
  return request({
    url: '/queue/stats',
    method: 'get',
    params
  })
}

// 清空队列数据
export function clearQueueData(data) {
  return request({
    url: '/queue/clear',
    method: 'post',
    data
  })
}

// 获取AI图片任务列表
export function getAiImageTasks(params) {
  return request({
    url: '/queue/ai-image/tasks',
    method: 'get',
    params
  })
}

// 重试AI图片任务
export function retryAiImageTask(taskId) {
  return request({
    url: `/queue/ai-image/tasks/${taskId}/retry`,
    method: 'post'
  })
}

// 取消AI图片任务
export function cancelAiImageTask(taskId) {
  return request({
    url: `/queue/ai-image/tasks/${taskId}/cancel`,
    method: 'post'
  })
}

// 删除AI图片任务
export function deleteAiImageTask(taskId) {
  return request({
    url: `/queue/ai-image/tasks/${taskId}`,
    method: 'delete'
  })
}

// 获取视频转码任务列表
export function getVideoTranscodeTasks(params) {
  return request({
    url: '/queue/video/tasks',
    method: 'get',
    params
  })
}

// 重试视频转码任务
export function retryVideoTranscodeTask(taskId) {
  return request({
    url: `/queue/video/tasks/${taskId}/retry`,
    method: 'post'
  })
}

// 取消视频转码任务
export function cancelVideoTranscodeTask(taskId) {
  return request({
    url: `/queue/video/tasks/${taskId}/cancel`,
    method: 'post'
  })
}

// 删除视频转码任务
export function deleteVideoTranscodeTask(taskId) {
  return request({
    url: `/queue/video/tasks/${taskId}`,
    method: 'delete'
  })
}

// 获取队列日志
export function getQueueLogs(params) {
  return request({
    url: '/queue/logs',
    method: 'get',
    params
  })
}

// 清空队列日志
export function clearQueueLogs(data) {
  return request({
    url: '/queue/logs/clear',
    method: 'post',
    data
  })
}

// 导出队列日志
export function exportQueueLogs(params) {
  return request({
    url: '/queue/logs/export',
    method: 'get',
    params,
    responseType: 'blob'
  })
}

// 获取队列任务详情
export function getQueueTaskDetail(taskId) {
  return request({
    url: `/queue/tasks/${taskId}`,
    method: 'get'
  })
}

// 批量重试失败任务
export function batchRetryFailedTasks(data) {
  return request({
    url: '/queue/batch-retry',
    method: 'post',
    data
  })
}

// 批量删除任务
export function batchDeleteTasks(data) {
  return request({
    url: '/queue/batch-delete',
    method: 'post',
    data
  })
}

// 暂停队列
export function pauseQueue(queueName) {
  return request({
    url: `/queue/${queueName}/pause`,
    method: 'post'
  })
}

// 恢复队列
export function resumeQueue(queueName) {
  return request({
    url: `/queue/${queueName}/resume`,
    method: 'post'
  })
}
