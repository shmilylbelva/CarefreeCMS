import request from './request'
import { cachedRequest } from '@/utils/localCache'

export function getSiteList(params) {
  return request({ url: '/sites', method: 'get', params })
}

export function getSiteDetail(id) {
  return request({ url: `/sites/${id}`, method: 'get' })
}

export function createSite(data) {
  return request({ url: '/sites', method: 'post', data })
}

export function updateSite(id, data) {
  return request({ url: `/sites/${id}`, method: 'put', data })
}

export function deleteSite(id) {
  return request({ url: `/sites/${id}`, method: 'delete' })
}

export function batchDeleteSites(ids) {
  return request({ url: '/sites/batch-delete', method: 'post', data: { ids } })
}

export function updateSiteStatus(id, status) {
  return request({ url: `/sites/${id}/status`, method: 'put', data: { status } })
}

// 获取站点选项列表（带前端缓存，30分钟）
// 后端已有缓存，前端再加一层减少网络请求
export function getSiteOptions() {
  const cacheKey = 'site_options'
  return cachedRequest.request(
    () => request({ url: '/sites/options', method: 'get' }),
    cacheKey,
    { expire: 30 * 60 * 1000 } // 30分钟缓存
  )
}

// 获取当前站点信息（带前端缓存，10分钟）
export function getCurrentSite() {
  const cacheKey = 'current_site'
  return cachedRequest.request(
    () => request({ url: '/sites/current', method: 'get' }),
    cacheKey,
    { expire: 10 * 60 * 1000 } // 10分钟缓存
  )
}

export function switchSite(siteId) {
  return request({ url: '/sites/switch', method: 'post', data: { site_id: siteId } })
}

export function assignSiteAdmins(id, adminUserIds) {
  return request({ url: `/sites/${id}/admins`, method: 'post', data: { admin_user_ids: adminUserIds } })
}

export function getSiteAdmins(id) {
  return request({ url: `/sites/${id}/admins`, method: 'get' })
}

export function updateSiteStats(id) {
  return request({ url: `/sites/${id}/stats`, method: 'put' })
}

export function copySiteConfig(fromSiteId, toSiteId) {
  return request({ url: '/sites/copy-config', method: 'post', data: { from_site_id: fromSiteId, to_site_id: toSiteId } })
}

export function clearSiteCache(siteId = null) {
  return request({ url: '/sites/clear-cache', method: 'post', data: { site_id: siteId } })
}

// ==================== 站点表管理 ====================

export function createSiteTables(id) {
  return request({ url: `/sites/${id}/create-tables`, method: 'post' })
}

export function checkSiteTables(id) {
  return request({ url: `/sites/${id}/check-tables`, method: 'get' })
}

export function migrateSiteData(id, tables = []) {
  return request({ url: `/sites/${id}/migrate-data`, method: 'post', data: { tables } })
}

export function truncateSiteTables(id) {
  return request({ url: `/sites/${id}/truncate-tables`, method: 'post' })
}

// ==================== 站点模板配置 ====================

export function getSiteTemplateConfig(id) {
  return request({ url: `/sites/${id}/template-config`, method: 'get' })
}

export function setSiteTemplatePackage(id, packageId) {
  return request({ url: `/sites/${id}/template-package`, method: 'post', data: { package_id: packageId } })
}

export function updateSiteTemplateConfig(id, customConfig) {
  return request({ url: `/sites/${id}/template-config`, method: 'put', data: { custom_config: customConfig } })
}

export function getSiteTemplateOverrides(id) {
  return request({ url: `/sites/${id}/template-overrides`, method: 'get' })
}

export function setSiteTemplateOverride(id, templateType, templateId, priority = 0) {
  return request({
    url: `/sites/${id}/template-override`,
    method: 'post',
    data: { template_type: templateType, template_id: templateId, priority }
  })
}

export function removeSiteTemplateOverride(id, templateType) {
  return request({
    url: `/sites/${id}/template-override`,
    method: 'delete',
    data: { template_type: templateType }
  })
}
