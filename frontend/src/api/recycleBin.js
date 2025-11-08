import request from './request'

// 获取回收站列表
export function getRecycleBinList(params) {
  return request({
    url: '/recycle-bin',
    method: 'get',
    params
  })
}

// 获取回收站统计
export function getRecycleBinStatistics() {
  return request({
    url: '/recycle-bin/statistics',
    method: 'get'
  })
}

// 恢复单个项目
export function restoreItem(data) {
  return request({
    url: '/recycle-bin/restore',
    method: 'post',
    data
  })
}

// 批量恢复
export function batchRestore(data) {
  return request({
    url: '/recycle-bin/batch-restore',
    method: 'post',
    data
  })
}

// 彻底删除单个项目
export function destroyItem(type, id) {
  return request({
    url: `/recycle-bin/${type}/${id}`,
    method: 'delete'
  })
}

// 批量彻底删除
export function batchDestroy(data) {
  return request({
    url: '/recycle-bin/batch-destroy',
    method: 'post',
    data
  })
}

// 清空回收站
export function clearRecycleBin(data) {
  return request({
    url: '/recycle-bin/clear',
    method: 'post',
    data
  })
}
