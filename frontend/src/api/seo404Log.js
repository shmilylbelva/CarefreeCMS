import request from '@/utils/request'

export function get404LogList(params) {
  return request({ url: '/seo-404-logs', method: 'get', params })
}

export function get404Log(id) {
  return request({ url: `/seo-404-logs/${id}`, method: 'get' })
}

export function delete404Log(id) {
  return request({ url: `/seo-404-logs/${id}`, method: 'delete' })
}

export function batchDelete404Logs(ids) {
  return request({ url: '/seo-404-logs/batch-delete', method: 'post', data: { ids } })
}

export function mark404Fixed(id, method, notes = '') {
  return request({ url: `/seo-404-logs/${id}/mark-fixed`, method: 'post', data: { method, notes } })
}

export function batchMark404Fixed(ids, method, notes = '') {
  return request({ url: '/seo-404-logs/batch-mark-fixed', method: 'post', data: { ids, method, notes } })
}

export function ignore404(id, notes = '') {
  return request({ url: `/seo-404-logs/${id}/ignore`, method: 'post', data: { notes } })
}

export function createRedirectFrom404(id, to_url, redirect_type = 301) {
  return request({ url: `/seo-404-logs/${id}/create-redirect`, method: 'post', data: { to_url, redirect_type } })
}

export function clean404Logs(days = 90) {
  return request({ url: '/seo-404-logs/clean', method: 'post', data: { days } })
}

export function get404Statistics() {
  return request({ url: '/seo-404-logs/statistics', method: 'get' })
}

export function getTop404Errors(limit = 20) {
  return request({ url: '/seo-404-logs/top-errors', method: 'get', params: { limit } })
}

export function getRecent404Errors(limit = 20) {
  return request({ url: '/seo-404-logs/recent-errors', method: 'get', params: { limit } })
}

export function export404Logs(is_fixed) {
  return request({ url: '/seo-404-logs/export', method: 'get', params: { is_fixed } })
}
