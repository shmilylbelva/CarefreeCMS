# 前端缓存使用示例

## 1. 在应用启动时预加载缓存

在 `src/main.js` 或 `src/App.vue` 中：

```javascript
// main.js
import { createApp } from 'vue'
import App from './App.vue'
import pinia from './store'
import { useCacheStore } from './store/cache'

const app = createApp(App)
app.use(pinia)

// 应用启动后预加载缓存
const cacheStore = useCacheStore()
cacheStore.preloadCache().then(() => {
  console.log('缓存预加载完成')
})

app.mount('#app')
```

或在 `App.vue` 中：

```vue
<script setup>
import { onMounted } from 'vue'
import { useCacheStore } from '@/store/cache'

const cacheStore = useCacheStore()

onMounted(() => {
  // 后台预加载，不阻塞页面渲染
  cacheStore.preloadCache()
})
</script>
```

---

## 2. 在组件中使用缓存的API

### 示例：显示热门文章列表

```vue
<template>
  <div class="hot-articles">
    <h3>热门文章</h3>
    <div v-if="loading">加载中...</div>
    <div v-else>
      <article v-for="article in articles" :key="article.id">
        <h4>{{ article.title }}</h4>
        <p>浏览量: {{ article.view_count }}</p>
      </article>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getHotArticles } from '@/api/article'

const articles = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    // 首次从后端获取，后续10分钟内从localStorage读取
    const response = await getHotArticles({ limit: 10 })
    articles.value = response.data
  } catch (error) {
    console.error('获取热门文章失败:', error)
  } finally {
    loading.value = false
  }
})
</script>
```

### 示例：显示分类树

```vue
<template>
  <div class="category-tree">
    <el-tree
      :data="categoryTree"
      :props="{ label: 'name', children: 'children' }"
      node-key="id"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getCategoryTree } from '@/api/category'

const categoryTree = ref([])

onMounted(async () => {
  // 从缓存获取（30分钟有效期）
  const response = await getCategoryTree({ status: 1 })
  categoryTree.value = response.data
})
</script>
```

---

## 3. 手动清除缓存

### 示例：编辑分类后清除缓存

```vue
<script setup>
import { useCacheStore } from '@/store/cache'
import { updateCategory } from '@/api/category'
import { ElMessage } from 'element-plus'

const cacheStore = useCacheStore()

async function handleUpdateCategory(id, data) {
  try {
    await updateCategory(id, data)

    // 更新成功后清除分类缓存
    cacheStore.clearModuleCache('categories')

    ElMessage.success('分类更新成功')
  } catch (error) {
    ElMessage.error('更新失败: ' + error.message)
  }
}
</script>
```

### 示例：刷新缓存按钮

```vue
<template>
  <div>
    <el-button @click="refreshCache">刷新缓存</el-button>
    <el-button @click="clearCache" type="danger">清除所有缓存</el-button>
  </div>
</template>

<script setup>
import { useCacheStore } from '@/store/cache'
import { ElMessage } from 'element-plus'

const cacheStore = useCacheStore()

async function refreshCache() {
  try {
    await cacheStore.preloadCache()
    ElMessage.success('缓存刷新成功')
  } catch (error) {
    ElMessage.error('刷新失败')
  }
}

function clearCache() {
  cacheStore.clearAllCache()
  ElMessage.success('缓存已清除')
}
</script>
```

---

## 4. 强制更新缓存

### 示例：强制从后端获取最新数据

```vue
<script setup>
import { ref } from 'vue'
import { getHotArticles } from '@/api/article'
import { cachedRequest } from '@/utils/localCache'

const articles = ref([])

// 普通调用（使用缓存）
async function loadArticles() {
  const response = await getHotArticles({ limit: 10 })
  articles.value = response.data
}

// 强制刷新（忽略缓存）
async function forceRefresh() {
  // 先清除缓存
  const cacheKey = cachedRequest.generateKey('/articles/hot', { limit: 10 })
  cachedRequest.clearCache(cacheKey)

  // 重新请求
  const response = await getHotArticles({ limit: 10 })
  articles.value = response.data
}
</script>
```

---

## 5. 监控缓存状态

### 示例：缓存统计页面

```vue
<template>
  <div class="cache-stats">
    <h3>缓存统计</h3>

    <el-card>
      <h4>LocalStorage 缓存</h4>
      <p>总键数: {{ cacheStats.local?.totalKeys }}</p>
      <p>有效数据: {{ cacheStats.local?.validCount }}</p>
      <p>过期数据: {{ cacheStats.local?.expiredCount }}</p>
      <p>总大小: {{ cacheStats.local?.totalSizeKB }} KB</p>
    </el-card>

    <el-card>
      <h4>预加载状态</h4>
      <el-tag v-if="cacheStore.isPreloaded" type="success">已完成</el-tag>
      <el-tag v-else type="warning">进行中</el-tag>
      <p>缓存命中率: {{ cacheStore.getCacheHitRate().toFixed(1) }}%</p>
    </el-card>

    <el-button @click="updateStats">更新统计</el-button>
    <el-button @click="cleanExpired">清理过期缓存</el-button>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useCacheStore } from '@/store/cache'

const cacheStore = useCacheStore()

const cacheStats = computed(() => cacheStore.cacheStats)

function updateStats() {
  cacheStore.updateCacheStats()
}

function cleanExpired() {
  cacheStore.clearExpiredCache()
}
</script>
```

---

## 6. 使用SessionStorage临时缓存

### 示例：会话级别的表单数据缓存

```vue
<script setup>
import { ref, watch } from 'vue'
import { sessionCache } from '@/utils/localCache'

const formData = ref({
  title: '',
  content: ''
})

// 从缓存恢复表单数据
const cachedForm = sessionCache.get('draft_form')
if (cachedForm) {
  formData.value = cachedForm
}

// 监听表单变化，自动保存到会话缓存
watch(formData, (newData) => {
  sessionCache.set('draft_form', newData, 30 * 60 * 1000) // 30分钟
}, { deep: true })

function submitForm() {
  // 提交成功后清除缓存
  sessionCache.delete('draft_form')
}
</script>
```

---

## 7. 与后端缓存预热集成

### 示例：调用后端缓存预热

```vue
<template>
  <div>
    <el-button @click="warmupBackendCache">后端缓存预热</el-button>
    <el-button @click="warmupAllCache">全部缓存预热</el-button>
  </div>
</template>

<script setup>
import { warmup } from '@/api/cache'
import { useCacheStore } from '@/store/cache'
import { ElMessage } from 'element-plus'

const cacheStore = useCacheStore()

async function warmupBackendCache() {
  try {
    await warmup({ type: 'all' })
    ElMessage.success('后端缓存预热完成')
  } catch (error) {
    ElMessage.error('预热失败')
  }
}

async function warmupAllCache() {
  try {
    // 后端预热
    await warmup({ type: 'all' })

    // 前端预热
    await cacheStore.preloadCache()

    ElMessage.success('所有缓存预热完成')
  } catch (error) {
    ElMessage.error('预热失败')
  }
}
</script>
```

---

## 8. 自定义缓存策略

### 示例：根据用户角色设置不同缓存时间

```javascript
import { cachedRequest } from '@/utils/localCache'
import { request } from '@/utils/request'

export function getArticleList(params) {
  const user = JSON.parse(localStorage.getItem('user') || '{}')

  // 管理员使用较短缓存时间（5分钟）
  // 普通用户使用较长缓存时间（30分钟）
  const expire = user.role === 'admin'
    ? 5 * 60 * 1000
    : 30 * 60 * 1000

  const cacheKey = cachedRequest.generateKey('/articles', params)

  return cachedRequest.request(
    () => request({ url: '/articles', method: 'get', params }),
    cacheKey,
    { expire }
  )
}
```

---

## 9. 错误处理和降级

### 示例：缓存失败时的降级策略

```vue
<script setup>
import { ref } from 'vue'
import { getHotArticles } from '@/api/article'
import { localCache } from '@/utils/localCache'

const articles = ref([])
const usedFallback = ref(false)

async function loadArticles() {
  try {
    const response = await getHotArticles({ limit: 10 })
    articles.value = response.data
    usedFallback.value = false
  } catch (error) {
    console.error('加载失败，尝试从缓存获取旧数据:', error)

    // 尝试获取可能已过期的缓存数据
    const cacheKey = 'request_/articles/hot_{"limit":10}'
    const cached = localCache.get(cacheKey)

    if (cached) {
      articles.value = cached.data
      usedFallback.value = true
    } else {
      // 显示默认数据或错误提示
      articles.value = []
    }
  }
}
</script>
```

---

## 10. 开发调试技巧

### 在控制台查看缓存

```javascript
// 在浏览器控制台执行

// 查看所有缓存键
Object.keys(localStorage).filter(k => k.startsWith('cms_cache_'))

// 查看具体缓存
const cache = JSON.parse(localStorage.getItem('cms_cache_site_options'))
console.log(cache)

// 清除所有缓存
Object.keys(localStorage)
  .filter(k => k.startsWith('cms_cache_'))
  .forEach(k => localStorage.removeItem(k))

// 查看缓存统计
import { localCache } from '@/utils/localCache'
console.log(localCache.getStats())
```

### 测试缓存性能

```javascript
// 测试缓存命中性能
console.time('缓存读取')
const data = localCache.get('site_options')
console.timeEnd('缓存读取')
// 输出: 缓存读取: 2.5ms

// 测试网络请求性能
console.time('网络请求')
const response = await getSiteOptions()
console.timeEnd('网络请求')
// 输出: 网络请求: 150ms
```

---

## 常见问题

### Q1: 如何判断数据是从缓存还是网络获取的？

```javascript
import { localCache } from '@/utils/localCache'

const cacheKey = 'site_options'
const fromCache = localCache.has(cacheKey)

if (fromCache) {
  console.log('数据来自缓存')
} else {
  console.log('数据来自网络')
}
```

### Q2: 如何设置不同环境的缓存策略？

```javascript
const isDev = import.meta.env.DEV

// 开发环境使用较短缓存
const expire = isDev ? 60 * 1000 : 30 * 60 * 1000

localCache.set('data', value, expire)
```

### Q3: 缓存数据被修改了怎么办？

```javascript
// 使用深拷贝避免修改缓存数据
const cachedData = localCache.get('data')
const data = JSON.parse(JSON.stringify(cachedData))
```

---

以上示例覆盖了前端缓存的主要使用场景，可根据实际需求灵活调整。
