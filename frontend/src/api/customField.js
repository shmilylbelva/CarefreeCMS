import request from './request'

// 获取自定义字段列表
export function getCustomFieldList(params) {
  return request({
    url: '/custom-fields',
    method: 'get',
    params
  })
}

// 根据模型获取字段（用于表单）
export function getFieldsByModel(modelType, modelId = null, siteId = null) {
  const params = {
    model_type: modelType,
    model_id: modelId
  }

  if (siteId !== null) {
    params.site_id = siteId
  }

  return request({
    url: '/custom-fields/by-model',
    method: 'get',
    params
  })
}

// 获取自定义字段详情
export function getCustomFieldDetail(id) {
  return request({
    url: `/custom-fields/${id}`,
    method: 'get'
  })
}

// 创建自定义字段
export function createCustomField(data) {
  return request({
    url: '/custom-fields',
    method: 'post',
    data
  })
}

// 更新自定义字段
export function updateCustomField(id, data) {
  return request({
    url: `/custom-fields/${id}`,
    method: 'put',
    data
  })
}

// 删除自定义字段
export function deleteCustomField(id) {
  return request({
    url: `/custom-fields/${id}`,
    method: 'delete'
  })
}

// 获取字段类型列表
export function getFieldTypes() {
  return request({
    url: '/custom-fields/field-types',
    method: 'get'
  })
}

// 获取模型类型列表
export function getModelTypes() {
  return request({
    url: '/custom-fields/model-types',
    method: 'get'
  })
}

// 获取实体的字段值
export function getEntityValues(entityType, entityId) {
  return request({
    url: '/custom-fields/entity-values',
    method: 'get',
    params: {
      entity_type: entityType,
      entity_id: entityId
    }
  })
}

// 保存实体的字段值
export function saveEntityValues(entityType, entityId, fieldValues) {
  return request({
    url: '/custom-fields/entity-values',
    method: 'post',
    data: {
      entity_type: entityType,
      entity_id: entityId,
      field_values: fieldValues
    }
  })
}
