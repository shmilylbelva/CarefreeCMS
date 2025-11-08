import request from '@/utils/request'

/**
 * 分析文章SEO
 */
export function analyzeSeo(data) {
  return request({
    url: '/seo-analyzer/analyze',
    method: 'post',
    data
  })
}

/**
 * 分析指定文章SEO
 */
export function analyzeArticleSeo(id) {
  return request({
    url: `/seo-analyzer/analyze/${id}`,
    method: 'get'
  })
}

/**
 * 计算关键词密度
 */
export function calculateKeywordDensity(content, keywords) {
  return request({
    url: '/seo-analyzer/keyword-density',
    method: 'post',
    data: { content, keywords }
  })
}

/**
 * 自动生成SEO标题
 */
export function generateSeoTitle(title, keywords = '') {
  return request({
    url: '/seo-analyzer/generate-title',
    method: 'post',
    data: { title, keywords }
  })
}

/**
 * 自动生成SEO描述
 */
export function generateSeoDescription(content, keywords = '', max_length = 160) {
  return request({
    url: '/seo-analyzer/generate-description',
    method: 'post',
    data: { content, keywords, max_length }
  })
}

/**
 * 自动提取关键词
 */
export function extractKeywords(content, count = 5) {
  return request({
    url: '/seo-analyzer/extract-keywords',
    method: 'post',
    data: { content, count }
  })
}

/**
 * 获取SEO优化建议
 */
export function getSeoSuggestions(id) {
  return request({
    url: `/seo-analyzer/suggestions/${id}`,
    method: 'get'
  })
}

/**
 * 自动优化文章SEO
 */
export function autoOptimizeSeo(id) {
  return request({
    url: `/seo-analyzer/auto-optimize/${id}`,
    method: 'post'
  })
}

/**
 * 批量分析文章
 */
export function batchAnalyzeSeo(ids) {
  return request({
    url: '/seo-analyzer/batch-analyze',
    method: 'post',
    data: { ids }
  })
}

/**
 * 生成Sitemap
 */
export function generateSitemap(type = 'all') {
  return request({
    url: '/seo-sitemap/generate',
    method: 'post',
    data: { type }
  })
}

/**
 * Ping搜索引擎
 */
export function pingSitemap(sitemap_url = '') {
  return request({
    url: '/seo-sitemap/ping',
    method: 'post',
    data: { sitemap_url }
  })
}
