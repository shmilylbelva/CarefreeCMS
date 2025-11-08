import request from '@/utils/request'

export function getSeoRedirectList(params) {
  return request({ url: '/seo-redirects', method: 'get', params })
}

export function getSeoRedirect(id) {
  return request({ url: `/seo-redirects/${id}`, method: 'get' })
}

export function createSeoRedirect(data) {
  return request({ url: '/seo-redirects', method: 'post', data })
}

export function updateSeoRedirect(id, data) {
  return request({ url: `/seo-redirects/${id}`, method: 'put', data })
}

export function deleteSeoRedirect(id) {
  return request({ url: `/seo-redirects/${id}`, method: 'delete' })
}

export function batchDeleteSeoRedirects(ids) {
  return request({ url: '/seo-redirects/batch-delete', method: 'post', data: { ids } })
}

export function batchToggleSeoRedirects(ids, is_enabled) {
  return request({ url: '/seo-redirects/batch-toggle', method: 'post', data: { ids, is_enabled } })
}

export function testSeoRedirect(url) {
  return request({ url: '/seo-redirects/test', method: 'post', data: { url } })
}

export function getSeoRedirectStatistics() {
  return request({ url: '/seo-redirects/statistics', method: 'get' })
}

export function getSeoRedirectOptions() {
  return request({ url: '/seo-redirects/options', method: 'get' })
}

export function importSeoRedirects(content) {
  return request({ url: '/seo-redirects/import', method: 'post', data: { content } })
}

export function exportSeoRedirects() {
  return request({ url: '/seo-redirects/export', method: 'get' })
}
