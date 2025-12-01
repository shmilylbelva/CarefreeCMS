import request from '@/utils/request'

/**
 * 获取OAuth配置列表（后台管理）
 * @param {Object} params 查询参数
 * @returns {Promise}
 */
export function getOAuthConfigList(params) {
  return request({
    url: '/oauth-configs',
    method: 'get',
    params
  })
}

/**
 * 获取OAuth配置详情
 * @param {Number} id 配置ID
 * @returns {Promise}
 */
export function getOAuthConfigDetail(id) {
  return request({
    url: `/oauth-configs/${id}`,
    method: 'get'
  })
}

/**
 * 更新OAuth配置
 * @param {Number} id 配置ID
 * @param {Object} data 配置数据
 * @returns {Promise}
 */
export function updateOAuthConfig(id, data) {
  return request({
    url: `/oauth-configs/${id}`,
    method: 'put',
    data
  })
}

/**
 * 批量更新OAuth平台状态
 * @param {Array} ids 配置ID数组
 * @param {Number} is_enabled 启用状态 (0-禁用, 1-启用)
 * @returns {Promise}
 */
export function batchUpdateOAuthStatus(ids, is_enabled) {
  return request({
    url: '/oauth-configs/batch-update-status',
    method: 'post',
    data: { ids, is_enabled }
  })
}

/**
 * 获取平台选项
 * @returns {Promise}
 */
export function getPlatformOptions() {
  return request({
    url: '/oauth-configs/platform-options',
    method: 'get'
  })
}

/**
 * 测试OAuth配置
 * @param {Number} id 配置ID
 * @returns {Promise}
 */
export function testOAuthConfig(id) {
  return request({
    url: `/oauth-configs/${id}/test`,
    method: 'get'
  })
}

// ========== 前台OAuth登录 ==========

/**
 * 获取启用的OAuth平台列表
 * @returns {Promise}
 */
export function getEnabledPlatforms() {
  return request({
    url: '/oauth/enabled-platforms',
    method: 'get'
  })
}

/**
 * 获取OAuth授权登录URL
 * @param {String} platform 平台标识(wechat/qq/weibo/github)
 * @returns {Promise}
 */
export function getOAuthAuthUrl(platform) {
  return request({
    url: '/oauth/auth-url',
    method: 'get',
    params: { platform }
  })
}

/**
 * OAuth回调处理
 * @param {String} code 授权码
 * @param {String} state 状态参数
 * @returns {Promise}
 */
export function oauthCallback(code, state) {
  return request({
    url: '/oauth/callback',
    method: 'get',
    params: { code, state }
  })
}

/**
 * 获取用户的OAuth绑定列表
 * @returns {Promise}
 */
export function getUserOAuthBindings() {
  return request({
    url: '/oauth/user-bindings',
    method: 'get'
  })
}

/**
 * 绑定第三方账号
 * @param {String} platform 平台标识
 * @param {String} code 授权码
 * @returns {Promise}
 */
export function bindOAuthAccount(platform, code) {
  return request({
    url: '/oauth/bind',
    method: 'post',
    data: { platform, code }
  })
}

/**
 * 解绑第三方账号
 * @param {String} platform 平台标识
 * @returns {Promise}
 */
export function unbindOAuthAccount(platform) {
  return request({
    url: '/oauth/unbind',
    method: 'post',
    data: { platform }
  })
}
