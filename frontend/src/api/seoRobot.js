import request from '@/utils/request'

export function getSeoRobotList(params) {
  return request({ url: '/seo-robots', method: 'get', params })
}

export function getSeoRobot(id) {
  return request({ url: `/seo-robots/${id}`, method: 'get' })
}

export function getActiveSeoRobot() {
  return request({ url: '/seo-robots/active', method: 'get' })
}

export function createSeoRobot(data) {
  return request({ url: '/seo-robots', method: 'post', data })
}

export function updateSeoRobot(id, data) {
  return request({ url: `/seo-robots/${id}`, method: 'put', data })
}

export function deleteSeoRobot(id) {
  return request({ url: `/seo-robots/${id}`, method: 'delete' })
}

export function activateSeoRobot(id) {
  return request({ url: `/seo-robots/${id}/activate`, method: 'post' })
}

export function validateRobotContent(content) {
  return request({ url: '/seo-robots/validate', method: 'post', data: { content } })
}

export function getRobotTemplates() {
  return request({ url: '/seo-robots/templates', method: 'get' })
}

export function applyRobotTemplate(template) {
  return request({ url: '/seo-robots/apply-template', method: 'post', data: { template } })
}

export function generateRobotFile() {
  return request({ url: '/seo-robots/generate', method: 'post' })
}

export function getCurrentRobotFile() {
  return request({ url: '/seo-robots/current', method: 'get' })
}
