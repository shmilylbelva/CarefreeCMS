import request from '@/utils/request'

/**
 * 获取专题列表
 */
export function getTopicList(params) {
  return request({
    url: '/topics',
    method: 'get',
    params
  })
}

/**
 * 获取所有专题（不分页）
 */
export function getAllTopics(params) {
  return request({
    url: '/topics/all',
    method: 'get',
    params
  })
}

/**
 * 获取专题详情
 */
export function getTopicDetail(id) {
  return request({
    url: `/topics/${id}`,
    method: 'get'
  })
}

/**
 * 创建专题
 */
export function createTopic(data) {
  return request({
    url: '/topics',
    method: 'post',
    data
  })
}

/**
 * 更新专题
 */
export function updateTopic(id, data) {
  return request({
    url: `/topics/${id}`,
    method: 'put',
    data
  })
}

/**
 * 删除专题
 */
export function deleteTopic(id) {
  return request({
    url: `/topics/${id}`,
    method: 'delete'
  })
}

/**
 * 获取专题的文章列表
 */
export function getTopicArticles(id, params) {
  return request({
    url: `/topics/${id}/articles`,
    method: 'get',
    params
  })
}

/**
 * 添加文章到专题
 */
export function addArticleToTopic(topicId, articleId, sort = 0, isFeatured = 0) {
  return request({
    url: `/topics/${topicId}/articles`,
    method: 'post',
    data: {
      article_id: articleId,
      sort,
      is_featured: isFeatured
    }
  })
}

/**
 * 从专题移除文章
 */
export function removeArticleFromTopic(topicId, articleId) {
  return request({
    url: `/topics/${topicId}/articles/${articleId}`,
    method: 'delete'
  })
}

/**
 * 批量设置专题文章
 */
export function setTopicArticles(topicId, articleIds) {
  return request({
    url: `/topics/${topicId}/articles/batch`,
    method: 'post',
    data: {
      article_ids: articleIds
    }
  })
}

/**
 * 更新文章在专题中的排序
 */
export function updateArticleSort(topicId, articleId, sort) {
  return request({
    url: `/topics/${topicId}/articles/${articleId}/sort`,
    method: 'put',
    data: {
      sort
    }
  })
}

/**
 * 设置文章为精选
 */
export function setArticleFeatured(topicId, articleId, isFeatured) {
  return request({
    url: `/topics/${topicId}/articles/${articleId}/featured`,
    method: 'put',
    data: {
      is_featured: isFeatured
    }
  })
}
