import request from './request'

// ==================== 后台评论管理 ====================

// 获取评论列表
export function getCommentList(params) {
  return request({
    url: '/admin/comments',
    method: 'get',
    params
  })
}

// 获取评论详情
export function getCommentDetail(id) {
  return request({
    url: `/admin/comments/${id}`,
    method: 'get'
  })
}

// 更新评论
export function updateComment(id, data) {
  return request({
    url: `/admin/comments/${id}`,
    method: 'put',
    data
  })
}

// 删除评论
export function deleteComment(id) {
  return request({
    url: `/admin/comments/${id}`,
    method: 'delete'
  })
}

// 批量删除评论
export function batchDeleteComments(data) {
  return request({
    url: '/admin/comments/batch-delete',
    method: 'post',
    data
  })
}

// 审核评论
export function auditComment(id, data) {
  return request({
    url: `/admin/comments/${id}/audit`,
    method: 'post',
    data
  })
}

// 批量审核评论
export function batchAuditComments(data) {
  return request({
    url: '/admin/comments/batch-audit',
    method: 'post',
    data
  })
}

// 批量更新评论状态
export function batchUpdateCommentStatus(data) {
  return request({
    url: '/admin/comments/batch-update-status',
    method: 'post',
    data
  })
}

// 回复评论（管理员）
export function replyComment(id, data) {
  return request({
    url: `/admin/comments/${id}/reply`,
    method: 'post',
    data
  })
}

// 切换热门标记
export function toggleHotComment(id) {
  return request({
    url: `/admin/comments/${id}/toggle-hot`,
    method: 'post'
  })
}

// 获取评论统计
export function getCommentStatistics() {
  return request({
    url: '/admin/comments/statistics',
    method: 'get'
  })
}

// 获取评论趋势数据
export function getCommentTrend() {
  return request({
    url: '/admin/comments/trend',
    method: 'get'
  })
}

// 获取活跃用户
export function getActiveUsers(params) {
  return request({
    url: '/admin/comments/active-users',
    method: 'get',
    params
  })
}

// 获取热门评论
export function getHotComments(params) {
  return request({
    url: '/admin/comments/hot',
    method: 'get',
    params
  })
}

// 获取待审核评论数量
export function getPendingCommentCount() {
  return request({
    url: '/admin/comments/pending-count',
    method: 'get'
  })
}

// ==================== 评论举报管理 ====================

// 获取举报列表
export function getReportList(params) {
  return request({
    url: '/admin/comment-reports',
    method: 'get',
    params
  })
}

// 获取举报详情
export function getReportDetail(id) {
  return request({
    url: `/admin/comment-reports/${id}`,
    method: 'get'
  })
}

// 处理举报
export function handleReport(id, data) {
  return request({
    url: `/admin/comment-reports/${id}/handle`,
    method: 'post',
    data
  })
}

// 忽略举报
export function ignoreReport(id) {
  return request({
    url: `/admin/comment-reports/${id}/ignore`,
    method: 'post'
  })
}

// 批量处理举报
export function batchHandleReports(data) {
  return request({
    url: '/admin/comment-reports/batch-handle',
    method: 'post',
    data
  })
}

// 删除举报记录
export function deleteReport(id) {
  return request({
    url: `/admin/comment-reports/${id}`,
    method: 'delete'
  })
}

// 获取举报统计
export function getReportStatistics() {
  return request({
    url: '/admin/comment-reports/statistics',
    method: 'get'
  })
}

// ==================== 表情管理 ====================

// 获取表情列表
export function getEmojiList(params) {
  return request({
    url: '/admin/comment-emojis',
    method: 'get',
    params
  })
}

// 获取表情详情
export function getEmojiDetail(id) {
  return request({
    url: `/admin/comment-emojis/${id}`,
    method: 'get'
  })
}

// 创建表情
export function createEmoji(data) {
  return request({
    url: '/admin/comment-emojis',
    method: 'post',
    data
  })
}

// 更新表情
export function updateEmoji(id, data) {
  return request({
    url: `/admin/comment-emojis/${id}`,
    method: 'put',
    data
  })
}

// 删除表情
export function deleteEmoji(id) {
  return request({
    url: `/admin/comment-emojis/${id}`,
    method: 'delete'
  })
}

// 批量删除表情
export function batchDeleteEmojis(data) {
  return request({
    url: '/admin/comment-emojis/batch-delete',
    method: 'post',
    data
  })
}

// 批量启用/禁用表情
export function batchToggleEmojis(data) {
  return request({
    url: '/admin/comment-emojis/batch-toggle',
    method: 'post',
    data
  })
}

// 更新表情排序
export function updateEmojiSort(id, data) {
  return request({
    url: `/admin/comment-emojis/${id}/update-sort`,
    method: 'post',
    data
  })
}

// 重置表情使用次数
export function resetEmojiUseCount(id) {
  return request({
    url: `/admin/comment-emojis/${id}/reset-count`,
    method: 'post'
  })
}

// 批量重置使用次数
export function batchResetEmojiUseCount(data) {
  return request({
    url: '/admin/comment-emojis/batch-reset-count',
    method: 'post',
    data
  })
}

// 批量导入表情
export function batchImportEmojis(data) {
  return request({
    url: '/admin/comment-emojis/batch-import',
    method: 'post',
    data
  })
}

// 获取表情分类
export function getEmojiCategories() {
  return request({
    url: '/admin/comment-emojis/categories',
    method: 'get'
  })
}

// 获取热门表情
export function getHotEmojis(params) {
  return request({
    url: '/admin/comment-emojis/hot',
    method: 'get',
    params
  })
}

// 获取表情统计
export function getEmojiStatistics() {
  return request({
    url: '/admin/comment-emojis/statistics',
    method: 'get'
  })
}

// 按分类获取表情
export function getEmojisByCategory(params) {
  return request({
    url: '/admin/comment-emojis/by-category',
    method: 'get',
    params
  })
}
