import request from './request'

// 生成所有格式的sitemap
export function generateAllSitemaps() {
  return request({ url: '/sitemap/all', method: 'post' })
}

// 生成TXT格式sitemap
export function generateTxtSitemap() {
  return request({ url: '/sitemap/txt', method: 'post' })
}

// 生成XML格式sitemap
export function generateXmlSitemap() {
  return request({ url: '/sitemap/xml', method: 'post' })
}

// 生成HTML格式sitemap
export function generateHtmlSitemap() {
  return request({ url: '/sitemap/html', method: 'post' })
}

// Ping搜索引擎
export function pingSitemap(sitemapUrl = '') {
  return request({
    url: '/seo-sitemap/ping',
    method: 'post',
    data: { sitemap_url: sitemapUrl }
  })
}
