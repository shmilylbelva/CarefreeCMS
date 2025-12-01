import request from './request'

// ==================== AI配置管理 ====================

// 获取AI提供商列表
export function getAiProviders() {
  return request({
    url: '/ai-configs/providers',
    method: 'get'
  })
}

// 获取所有AI配置（下拉选择）
// params.text_generation_only: 是否只获取支持文本生成的配置（用于批量文章生成）
export function getAllAiConfigs(params = {}) {
  return request({
    url: '/ai-configs/all',
    method: 'get',
    params
  })
}

// 获取AI配置列表
export function getAiConfigList(params) {
  return request({
    url: '/ai-configs',
    method: 'get',
    params
  })
}

// 获取AI配置详情
export function getAiConfigDetail(id) {
  return request({
    url: `/ai-configs/${id}`,
    method: 'get'
  })
}

// 创建AI配置
export function createAiConfig(data) {
  return request({
    url: '/ai-configs',
    method: 'post',
    data
  })
}

// 更新AI配置
export function updateAiConfig(id, data) {
  return request({
    url: `/ai-configs/${id}`,
    method: 'put',
    data
  })
}

// 删除AI配置
export function deleteAiConfig(id) {
  return request({
    url: `/ai-configs/${id}`,
    method: 'delete'
  })
}

// 测试AI连接
export function testAiConfig(id) {
  return request({
    url: `/ai-configs/${id}/test`,
    method: 'post'
  })
}

// 设置为默认配置
export function setDefaultAiConfig(id) {
  return request({
    url: `/ai-configs/${id}/set-default`,
    method: 'post'
  })
}

// 获取提供商支持的模型列表
export function getProviderModels(provider) {
  return request({
    url: '/ai-configs/provider-models',
    method: 'get',
    params: { provider }
  })
}

// 获取提供商配置说明
export function getProviderConfigGuide(provider) {
  return request({
    url: '/ai-configs/provider-config-guide',
    method: 'get',
    params: { provider }
  })
}

// ==================== AI厂商管理 ====================

// 获取所有AI厂商（下拉选择）
export function getAllAiProviders() {
  return request({
    url: '/ai-providers/all',
    method: 'get'
  })
}

// 获取AI厂商列表
export function getAiProviderList(params) {
  return request({
    url: '/ai-providers',
    method: 'get',
    params
  })
}

// 获取AI厂商详情
export function getAiProviderDetail(id) {
  return request({
    url: `/ai-providers/${id}`,
    method: 'get'
  })
}

// 创建AI厂商
export function createAiProvider(data) {
  return request({
    url: '/ai-providers',
    method: 'post',
    data
  })
}

// 更新AI厂商
export function updateAiProvider(id, data) {
  return request({
    url: `/ai-providers/${id}`,
    method: 'put',
    data
  })
}

// 删除AI厂商
export function deleteAiProvider(id) {
  return request({
    url: `/ai-providers/${id}`,
    method: 'delete'
  })
}

// 获取厂商的模型列表
export function getAiProviderModels(id) {
  return request({
    url: `/ai-providers/${id}/models`,
    method: 'get'
  })
}

// ==================== AI模型管理 ====================

// 获取所有AI模型（按厂商分组）
export function getAllAiModels() {
  return request({
    url: '/ai-models/all',
    method: 'get'
  })
}

// 获取AI模型列表
export function getAiModelList(params) {
  return request({
    url: '/ai-models',
    method: 'get',
    params
  })
}

// 获取AI模型详情
export function getAiModelDetail(id) {
  return request({
    url: `/ai-models/${id}`,
    method: 'get'
  })
}

// 创建AI模型
export function createAiModel(data) {
  return request({
    url: '/ai-models',
    method: 'post',
    data
  })
}

// 更新AI模型
export function updateAiModel(id, data) {
  return request({
    url: `/ai-models/${id}`,
    method: 'put',
    data
  })
}

// 删除AI模型
export function deleteAiModel(id) {
  return request({
    url: `/ai-models/${id}`,
    method: 'delete'
  })
}

// 批量导入模型
export function batchImportModels(data) {
  return request({
    url: '/ai-models/batch-import',
    method: 'post',
    data
  })
}

// ==================== AI提示词模板管理 ====================

// 获取所有提示词模板（下拉选择）
export function getAllPromptTemplates(category) {
  return request({
    url: '/ai-prompt-templates/all',
    method: 'get',
    params: { category }
  })
}

// 获取提示词模板列表
export function getPromptTemplateList(params) {
  return request({
    url: '/ai-prompt-templates',
    method: 'get',
    params
  })
}

// 获取提示词模板详情
export function getPromptTemplateDetail(id) {
  return request({
    url: `/ai-prompt-templates/${id}`,
    method: 'get'
  })
}

// 创建提示词模板
export function createPromptTemplate(data) {
  return request({
    url: '/ai-prompt-templates',
    method: 'post',
    data
  })
}

// 更新提示词模板
export function updatePromptTemplate(id, data) {
  return request({
    url: `/ai-prompt-templates/${id}`,
    method: 'put',
    data
  })
}

// 删除提示词模板
export function deletePromptTemplate(id) {
  return request({
    url: `/ai-prompt-templates/${id}`,
    method: 'delete'
  })
}

// 获取模板分类列表
export function getPromptTemplateCategories() {
  return request({
    url: '/ai-prompt-templates/categories',
    method: 'get'
  })
}

// ==================== AI文章生成任务管理 ====================

// 获取任务统计信息
export function getTaskStatistics() {
  return request({
    url: '/ai-article-tasks/statistics',
    method: 'get'
  })
}

// 获取任务状态列表
export function getTaskStatuses() {
  return request({
    url: '/ai-article-tasks/statuses',
    method: 'get'
  })
}

// 获取任务列表
export function getTaskList(params) {
  return request({
    url: '/ai-article-tasks',
    method: 'get',
    params
  })
}

// 获取任务详情
export function getTaskDetail(id) {
  return request({
    url: `/ai-article-tasks/${id}`,
    method: 'get'
  })
}

// 创建任务
export function createTask(data) {
  return request({
    url: '/ai-article-tasks',
    method: 'post',
    data
  })
}

// 更新任务
export function updateTask(id, data) {
  return request({
    url: `/ai-article-tasks/${id}`,
    method: 'put',
    data
  })
}

// 删除任务
export function deleteTask(id) {
  return request({
    url: `/ai-article-tasks/${id}`,
    method: 'delete'
  })
}

// 启动任务
export function startTask(id) {
  return request({
    url: `/ai-article-tasks/${id}/start`,
    method: 'post'
  })
}

// 停止任务
export function stopTask(id) {
  return request({
    url: `/ai-article-tasks/${id}/stop`,
    method: 'post'
  })
}

// 获取任务的生成记录
export function getTaskGeneratedArticles(id, params) {
  return request({
    url: `/ai-article-tasks/${id}/generated-articles`,
    method: 'get',
    params
  })
}
