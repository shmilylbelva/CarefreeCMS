import request from './request'

// 获取模板列表
export function getTemplates() {
  return request({ url: '/templates', method: 'get' })
}

// 扫描模板文件
export function scanTemplates(themeKey = '') {
  return request({
    url: '/templates/scan',
    method: 'get',
    params: { theme_key: themeKey }
  })
}

// 扫描所有模板套装
export function scanThemes() {
  return request({ url: '/templates/themes', method: 'get' })
}

// 获取当前模板套装
export function getCurrentTheme() {
  return request({ url: '/templates/current-theme', method: 'get' })
}

// 切换模板套装
export function switchTheme(themeKey) {
  return request({
    url: '/templates/switch-theme',
    method: 'post',
    data: { theme_key: themeKey }
  })
}

/**
 * ========== 在线模板编辑 ==========
 */

// 获取文件树
export function getFileTree(themeKey = '') {
  return request({
    url: '/templates/file-tree',
    method: 'get',
    params: { theme_key: themeKey }
  })
}

// 读取文件内容
export function readFile(themeKey, filePath) {
  console.log('readFile API call:', { themeKey, filePath })
  return request({
    url: '/templates/read-file',
    method: 'get',
    params: {
      theme_key: themeKey,
      file_path: filePath
    }
  }).then(res => {
    console.log('readFile API response:', res)
    return res
  }).catch(err => {
    console.error('readFile API error:', err)
    throw err
  })
}

// 保存文件
export function saveFile(themeKey, filePath, content) {
  return request({
    url: '/templates/save-file',
    method: 'post',
    data: {
      theme_key: themeKey,
      file_path: filePath,
      content
    }
  })
}

// 创建新文件
export function createFile(themeKey, fileName, fileType = 'html') {
  return request({
    url: '/templates/create-file',
    method: 'post',
    data: {
      theme_key: themeKey,
      file_name: fileName,
      file_type: fileType
    }
  })
}

// 删除文件
export function deleteFile(themeKey, filePath) {
  return request({
    url: '/templates/delete-file',
    method: 'post',
    data: {
      theme_key: themeKey,
      file_path: filePath
    }
  })
}

// 获取文件历史记录列表
export function getBackups(themeKey, filePath) {
  return request({
    url: '/templates/backups',
    method: 'get',
    params: {
      theme_key: themeKey,
      file_path: filePath
    }
  })
}

// 获取历史版本内容
export function getHistoryContent(historyId) {
  return request({
    url: '/templates/history-content',
    method: 'get',
    params: {
      history_id: historyId
    }
  })
}

// 恢复历史版本
export function restoreHistory(historyId) {
  return request({
    url: '/templates/restore-history',
    method: 'post',
    data: {
      history_id: historyId
    }
  })
}
