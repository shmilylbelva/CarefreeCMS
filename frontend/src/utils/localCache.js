/**
 * 前端本地缓存工具
 * 支持localStorage和sessionStorage
 * 带有过期时间管理和自动清理功能
 */

const DEFAULT_EXPIRE = 3600000 // 默认过期时间 1小时（毫秒）
const PREFIX = 'cms_cache_' // 缓存键前缀

/**
 * 缓存类型
 */
export const CacheType = {
  LOCAL: 'local', // localStorage - 持久化存储
  SESSION: 'session' // sessionStorage - 会话存储
}

/**
 * 缓存数据结构
 */
class CacheItem {
  constructor(value, expire) {
    this.value = value
    this.timestamp = Date.now()
    this.expire = expire || DEFAULT_EXPIRE
  }

  isExpired() {
    return Date.now() - this.timestamp > this.expire
  }
}

/**
 * 本地缓存管理器
 */
class LocalCache {
  constructor(type = CacheType.LOCAL) {
    this.storage = type === CacheType.LOCAL ? localStorage : sessionStorage
    this.type = type
  }

  /**
   * 生成完整的缓存键
   */
  getFullKey(key) {
    return `${PREFIX}${key}`
  }

  /**
   * 设置缓存
   * @param {string} key - 缓存键
   * @param {*} value - 缓存值（会自动JSON序列化）
   * @param {number} expire - 过期时间（毫秒），默认1小时
   */
  set(key, value, expire = DEFAULT_EXPIRE) {
    try {
      const fullKey = this.getFullKey(key)
      const cacheItem = new CacheItem(value, expire)
      this.storage.setItem(fullKey, JSON.stringify(cacheItem))
      return true
    } catch (error) {
      console.error('[LocalCache] 设置缓存失败:', error)
      // 可能是存储空间已满，尝试清理过期缓存
      this.clearExpired()
      return false
    }
  }

  /**
   * 获取缓存
   * @param {string} key - 缓存键
   * @param {*} defaultValue - 默认值（缓存不存在或已过期时返回）
   * @returns {*} 缓存值
   */
  get(key, defaultValue = null) {
    try {
      const fullKey = this.getFullKey(key)
      const item = this.storage.getItem(fullKey)

      if (!item) {
        return defaultValue
      }

      const cacheItem = JSON.parse(item)

      // 检查是否过期
      if (new Date().getTime() - cacheItem.timestamp > cacheItem.expire) {
        this.delete(key)
        return defaultValue
      }

      return cacheItem.value
    } catch (error) {
      console.error('[LocalCache] 获取缓存失败:', error)
      return defaultValue
    }
  }

  /**
   * 删除缓存
   * @param {string} key - 缓存键
   */
  delete(key) {
    try {
      const fullKey = this.getFullKey(key)
      this.storage.removeItem(fullKey)
      return true
    } catch (error) {
      console.error('[LocalCache] 删除缓存失败:', error)
      return false
    }
  }

  /**
   * 检查缓存是否存在且未过期
   * @param {string} key - 缓存键
   * @returns {boolean}
   */
  has(key) {
    const value = this.get(key, undefined)
    return value !== undefined
  }

  /**
   * 清除所有缓存（只清除带前缀的）
   */
  clear() {
    try {
      const keys = Object.keys(this.storage)
      keys.forEach(key => {
        if (key.startsWith(PREFIX)) {
          this.storage.removeItem(key)
        }
      })
      return true
    } catch (error) {
      console.error('[LocalCache] 清除缓存失败:', error)
      return false
    }
  }

  /**
   * 清除过期缓存
   */
  clearExpired() {
    try {
      const keys = Object.keys(this.storage)
      keys.forEach(key => {
        if (key.startsWith(PREFIX)) {
          try {
            const item = this.storage.getItem(key)
            if (item) {
              const cacheItem = JSON.parse(item)
              if (new Date().getTime() - cacheItem.timestamp > cacheItem.expire) {
                this.storage.removeItem(key)
              }
            }
          } catch (e) {
            // 解析失败，删除该项
            this.storage.removeItem(key)
          }
        }
      })
      return true
    } catch (error) {
      console.error('[LocalCache] 清除过期缓存失败:', error)
      return false
    }
  }

  /**
   * 获取缓存统计信息
   */
  getStats() {
    const keys = Object.keys(this.storage).filter(key => key.startsWith(PREFIX))
    let totalSize = 0
    let expiredCount = 0
    let validCount = 0

    keys.forEach(key => {
      const item = this.storage.getItem(key)
      if (item) {
        totalSize += item.length
        try {
          const cacheItem = JSON.parse(item)
          if (new Date().getTime() - cacheItem.timestamp > cacheItem.expire) {
            expiredCount++
          } else {
            validCount++
          }
        } catch (e) {
          expiredCount++
        }
      }
    })

    return {
      totalKeys: keys.length,
      validCount,
      expiredCount,
      totalSize,
      totalSizeKB: (totalSize / 1024).toFixed(2)
    }
  }
}

/**
 * 带缓存的请求包装器
 * 自动管理请求结果的缓存
 */
export class CachedRequest {
  constructor(cacheType = CacheType.LOCAL) {
    this.cache = new LocalCache(cacheType)
  }

  /**
   * 生成请求的缓存键
   * @param {string} url - 请求URL
   * @param {object} params - 请求参数
   */
  generateKey(url, params = {}) {
    const paramStr = JSON.stringify(params)
    return `request_${url}_${paramStr}`
  }

  /**
   * 带缓存的请求
   * @param {Function} requestFn - 请求函数
   * @param {string} cacheKey - 缓存键
   * @param {object} options - 选项
   * @returns {Promise}
   */
  async request(requestFn, cacheKey, options = {}) {
    const {
      expire = DEFAULT_EXPIRE,
      forceUpdate = false,
      useCache = true
    } = options

    // 如果不使用缓存或强制更新，直接请求
    if (!useCache || forceUpdate) {
      const result = await requestFn()
      if (useCache) {
        this.cache.set(cacheKey, result, expire)
      }
      return result
    }

    // 尝试从缓存获取
    const cached = this.cache.get(cacheKey)
    if (cached !== null) {
      return cached
    }

    // 缓存未命中，发起请求
    const result = await requestFn()
    this.cache.set(cacheKey, result, expire)
    return result
  }

  /**
   * 清除指定缓存
   */
  clearCache(cacheKey) {
    return this.cache.delete(cacheKey)
  }

  /**
   * 清除所有请求缓存
   */
  clearAll() {
    return this.cache.clear()
  }
}

// 导出默认实例
export const localCache = new LocalCache(CacheType.LOCAL)
export const sessionCache = new LocalCache(CacheType.SESSION)
export const cachedRequest = new CachedRequest(CacheType.LOCAL)

// 定期清理过期缓存（每10分钟）
if (typeof window !== 'undefined') {
  setInterval(() => {
    localCache.clearExpired()
    sessionCache.clearExpired()
  }, 10 * 60 * 1000)
}

export default {
  localCache,
  sessionCache,
  cachedRequest,
  CacheType
}
