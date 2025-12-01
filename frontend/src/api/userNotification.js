import request from '@/utils/request'

/**
 * 获取通知列表
 * @param {Object} params
 * @returns {Promise}
 */
export function getUserNotifications(params) {
  return request({
    url: '/front/notifications',
    method: 'get',
    params
  })
}

/**
 * 获取未读数量
 * @returns {Promise}
 */
export function getUnreadCount() {
  return request({
    url: '/front/notifications/unread-count',
    method: 'get'
  })
}

/**
 * 标记为已读
 * @param {Number} id
 * @returns {Promise}
 */
export function markNotificationAsRead(id) {
  return request({
    url: `/front/notifications/${id}/mark-as-read`,
    method: 'post'
  })
}

/**
 * 批量标记为已读
 * @param {Array} ids
 * @returns {Promise}
 */
export function batchMarkNotificationsAsRead(ids) {
  return request({
    url: '/front/notifications/batch-mark-as-read',
    method: 'post',
    data: { ids }
  })
}

/**
 * 全部标记为已读
 * @returns {Promise}
 */
export function markAllNotificationsAsRead() {
  return request({
    url: '/front/notifications/mark-all-as-read',
    method: 'post'
  })
}

/**
 * 删除通知
 * @param {Number} id
 * @returns {Promise}
 */
export function deleteNotification(id) {
  return request({
    url: `/front/notifications/${id}`,
    method: 'delete'
  })
}

/**
 * 清空已读通知
 * @returns {Promise}
 */
export function clearReadNotifications() {
  return request({
    url: '/front/notifications/clear-read',
    method: 'delete'
  })
}

/**
 * 获取通知设置
 * @returns {Promise}
 */
export function getNotificationSettings() {
  return request({
    url: '/front/notifications/settings',
    method: 'get'
  })
}

/**
 * 更新通知设置
 * @param {Object} settings
 * @returns {Promise}
 */
export function updateNotificationSettings(settings) {
  return request({
    url: '/front/notifications/settings',
    method: 'post',
    data: { settings }
  })
}
