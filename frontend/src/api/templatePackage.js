import request from './request'

export function getTemplatePackageList(params) {
  return request({ url: '/template-packages', method: 'get', params })
}

export function getAllTemplatePackages(params) {
  return request({ url: '/template-packages/all', method: 'get', params })
}

export function getTemplatePackageDetail(id) {
  return request({ url: `/template-packages/${id}`, method: 'get' })
}

export function createTemplatePackage(data) {
  return request({ url: '/template-packages', method: 'post', data })
}

export function updateTemplatePackage(id, data) {
  return request({ url: `/template-packages/${id}`, method: 'put', data })
}

export function deleteTemplatePackage(id) {
  return request({ url: `/template-packages/${id}`, method: 'delete' })
}

export function getPackageTemplates(id, params) {
  return request({ url: `/template-packages/${id}/templates`, method: 'get', params })
}

export function copyTemplatePackage(id, name, code) {
  return request({
    url: `/template-packages/${id}/copy`,
    method: 'post',
    data: { name, code }
  })
}

export function exportTemplatePackage(id) {
  return request({ url: `/template-packages/${id}/export`, method: 'get' })
}

export function importTemplatePackage(data) {
  return request({ url: '/template-packages/import', method: 'post', data })
}
