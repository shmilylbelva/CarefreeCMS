import { defineStore } from 'pinia'
import { localCache, sessionCache, cachedRequest } from '@/utils/localCache'
import { getCategoryTree } from '@/api/category'
import { getAllTags } from '@/api/tag'
import { getSiteOptions } from '@/api/site'
import { getHotArticles, getRecommendArticles } from '@/api/article'

/**
 * 缓存管理 Store
 * 管理前端常用数据的缓存，减少网络请求
 */
export const useCacheStore = defineStore('cache', {
  state: () => ({
    // 缓存状态
    cacheStats: {
      local: null,
      session: null,
      lastUpdated: null
    },

    // 预加载状态
    preloadStatus: {
      categories: false,
      tags: false,
      sites: false,
      articles: false
    }
  }),

  getters: {
    /**
     * 是否已完成预加载
     */
    isPreloaded: (state) => {
      return Object.values(state.preloadStatus).every(status => status === true)
    },

    /**
     * 获取缓存统计信息
     */
    getCacheStats: (state) => {
      return state.cacheStats
    }
  },

  actions: {
    /**
     * 预加载常用数据到缓存
     * 在应用启动时调用，提前加载常用数据
     */
    async preloadCache() {
      console.log('[CacheStore] 开始预加载缓存数据...')

      try {
        // 并行预加载所有数据
        await Promise.all([
          this.preloadCategories(),
          this.preloadTags(),
          this.preloadSites(),
          this.preloadArticles()
        ])

        console.log('[CacheStore] 缓存数据预加载完成')
        this.updateCacheStats()
      } catch (error) {
        console.error('[CacheStore] 缓存预加载失败:', error)
      }
    },

    /**
     * 预加载分类数据
     */
    async preloadCategories() {
      try {
        await getCategoryTree({ status: 1 })
        this.preloadStatus.categories = true
        console.log('[CacheStore] 分类数据预加载完成')
      } catch (error) {
        console.error('[CacheStore] 分类数据预加载失败:', error)
      }
    },

    /**
     * 预加载标签数据
     */
    async preloadTags() {
      try {
        await getAllTags({ status: 1 })
        this.preloadStatus.tags = true
        console.log('[CacheStore] 标签数据预加载完成')
      } catch (error) {
        console.error('[CacheStore] 标签数据预加载失败:', error)
      }
    },

    /**
     * 预加载站点数据
     */
    async preloadSites() {
      try {
        await getSiteOptions()
        this.preloadStatus.sites = true
        console.log('[CacheStore] 站点数据预加载完成')
      } catch (error) {
        console.error('[CacheStore] 站点数据预加载失败:', error)
      }
    },

    /**
     * 预加载文章数据
     */
    async preloadArticles() {
      try {
        await Promise.all([
          getHotArticles({ limit: 10 }),
          getRecommendArticles({ limit: 10 })
        ])
        this.preloadStatus.articles = true
        console.log('[CacheStore] 文章数据预加载完成')
      } catch (error) {
        console.error('[CacheStore] 文章数据预加载失败:', error)
      }
    },

    /**
     * 更新缓存统计信息
     */
    updateCacheStats() {
      this.cacheStats.local = localCache.getStats()
      this.cacheStats.session = sessionCache.getStats()
      this.cacheStats.lastUpdated = new Date().toISOString()
    },

    /**
     * 清除所有缓存
     */
    clearAllCache() {
      localCache.clear()
      sessionCache.clear()
      cachedRequest.clearAll()

      // 重置预加载状态
      this.preloadStatus = {
        categories: false,
        tags: false,
        sites: false,
        articles: false
      }

      this.updateCacheStats()
      console.log('[CacheStore] 所有缓存已清除')
    },

    /**
     * 清除过期缓存
     */
    clearExpiredCache() {
      localCache.clearExpired()
      sessionCache.clearExpired()
      this.updateCacheStats()
      console.log('[CacheStore] 过期缓存已清除')
    },

    /**
     * 清除指定模块的缓存
     * @param {string} module - 模块名称 (categories, tags, sites, articles)
     */
    clearModuleCache(module) {
      const patterns = {
        categories: 'request_/categories',
        tags: 'request_/tags',
        sites: 'request_/sites',
        articles: 'request_/articles'
      }

      const pattern = patterns[module]
      if (!pattern) {
        console.warn(`[CacheStore] 未知的模块: ${module}`)
        return
      }

      // 清除localStorage中匹配的缓存
      const keys = Object.keys(localStorage).filter(key =>
        key.includes(pattern)
      )
      keys.forEach(key => localStorage.removeItem(key))

      // 重置预加载状态
      this.preloadStatus[module] = false

      this.updateCacheStats()
      console.log(`[CacheStore] ${module} 缓存已清除`)
    },

    /**
     * 刷新指定模块的缓存
     * @param {string} module - 模块名称
     */
    async refreshModuleCache(module) {
      // 先清除缓存
      this.clearModuleCache(module)

      // 重新加载数据
      const preloadMethods = {
        categories: this.preloadCategories,
        tags: this.preloadTags,
        sites: this.preloadSites,
        articles: this.preloadArticles
      }

      const method = preloadMethods[module]
      if (method) {
        await method.call(this)
        console.log(`[CacheStore] ${module} 缓存已刷新`)
      }
    },

    /**
     * 获取缓存命中率
     * 通过比较预加载状态判断
     */
    getCacheHitRate() {
      const total = Object.keys(this.preloadStatus).length
      const loaded = Object.values(this.preloadStatus).filter(v => v === true).length
      return total > 0 ? (loaded / total) * 100 : 0
    }
  }
})
