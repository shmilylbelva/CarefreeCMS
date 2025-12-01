# CarefreeCMS 后台管理系统

![Version](https://img.shields.io/badge/version-1.3.0-blue.svg)
![Vue](https://img.shields.io/badge/vue-3.5-brightgreen.svg)
![Vite](https://img.shields.io/badge/vite-7.1-646CFF.svg)
![Element Plus](https://img.shields.io/badge/element--plus-2.11-409EFF.svg)

CarefreeCMS 的前端后台管理界面，基于 Vue 3 + Vite + Element Plus 构建。

## 技术栈

- **Vue 3.5** - 渐进式 JavaScript 框架（Composition API）
- **Vite 7.1** - 新一代前端构建工具
- **Element Plus 2.11** - 基于 Vue 3 的组件库
- **Vue Router 4** - Vue.js 官方路由管理器
- **Pinia 3** - Vue 的状态管理库
- **Axios** - HTTP 客户端
- **TinyMCE 6** - 富文本编辑器

## 环境要求

- Node.js >= 16.0
- npm >= 8.0 或 yarn >= 1.22

## 项目结构

```
frontend/
├── public/                    # 静态资源目录
│   ├── tinymce/              # TinyMCE 编辑器资源
│   │   ├── langs/            # 语言包
│   │   └── skins/            # 皮肤文件
│   └── favicon.ico           # 网站图标
├── src/                       # 源代码目录
│   ├── api/                  # API 接口封装
│   │   ├── admin.js         # 管理员接口
│   │   ├── article.js       # 文章接口
│   │   ├── auth.js          # 认证接口
│   │   ├── cache.js         # 缓存接口
│   │   ├── category.js      # 分类接口
│   │   ├── config.js        # 配置接口
│   │   ├── database.js      # 数据库接口
│   │   ├── log.js           # 日志接口
│   │   ├── media.js         # 媒体接口
│   │   ├── page.js          # 单页接口
│   │   ├── request.js       # 请求封装
│   │   ├── role.js          # 角色接口
│   │   ├── sitemap.js       # 网站地图接口
│   │   ├── static.js        # 静态生成接口
│   │   └── tag.js           # 标签接口
│   ├── assets/               # 资源文件
│   │   └── logo.svg         # Logo 图片
│   ├── components/           # 公共组件
│   │   ├── TinyMCE.vue      # TinyMCE 编辑器组件
│   │   ├── AdvancedSearch.vue  # 高级搜索对话框
│   │   ├── ArticleVersionList.vue  # 文章版本列表
│   │   ├── ArticleVersionCompare.vue  # 版本对比
│   │   ├── CustomFieldRenderer.vue  # 自定义字段渲染器
│   │   └── MediaSelector.vue  # 媒体选择器
│   ├── router/               # 路由配置
│   │   └── index.js         # 路由定义
│   ├── store/                # Pinia 状态管理
│   │   ├── index.js         # Store 入口
│   │   └── modules/         # 模块化 Store
│   │       └── user.js      # 用户状态
│   ├── utils/                # 工具函数
│   │   └── request.js       # Axios 请求封装
│   ├── views/                # 页面组件
│   │   ├── Dashboard.vue    # 仪表板
│   │   ├── Login.vue        # 登录页
│   │   ├── article/         # 文章管理
│   │   ├── category/        # 分类管理
│   │   ├── tag/             # 标签管理
│   │   ├── page/            # 单页管理
│   │   ├── media/           # 媒体库
│   │   ├── static/          # 静态生成
│   │   ├── sitemap/         # 网站地图
│   │   └── system/          # 系统管理
│   ├── App.vue              # 根组件
│   ├── main.js              # 入口文件
│   └── style.css            # 全局样式
├── .gitignore               # Git 忽略配置
├── index.html               # HTML 模板
├── package.json             # 项目配置
├── vite.config.js           # Vite 配置
└── README.md                # 项目说明

```

## 快速开始

### 1. 安装依赖

```bash
npm install
```

或使用 yarn：

```bash
yarn install
```

### 2. 配置后端 API 地址

编辑 `src/utils/request.js` 文件，修改 `baseURL`：

```javascript
const request = axios.create({
  baseURL: 'http://localhost:8000/api', // 修改为你的后端API地址
  timeout: 10000
})
```

### 3. 启动开发服务器

```bash
npm run dev
```

访问 `http://localhost:3000` 查看效果。

### 4. 构建生产版本

```bash
npm run build
```

构建后的文件位于 `dist/` 目录。

## 开发指南

### API 接口调用

所有 API 接口都封装在 `src/api/` 目录下，使用时直接导入：

```javascript
import { getArticleList, createArticle, fullTextSearch, advancedSearch } from '@/api/article'

// 获取文章列表
const { data } = await getArticleList({ page: 1, limit: 10 })

// 创建文章
await createArticle({
  title: '文章标题',
  content: '文章内容',
  category_id: 1
})

// 全文搜索
const searchResult = await fullTextSearch({
  keyword: 'Vue 教程',
  mode: 'natural',  // 自然语言模式
  page: 1,
  page_size: 20
})

// 高级搜索
const advancedResult = await advancedSearch({
  title: 'Vue',
  status: 1,
  min_views: 100,
  sort_by: 'view_count',
  sort_order: 'desc'
})
```

### 状态管理

使用 Pinia 进行状态管理，Store 定义在 `src/store/` 目录：

```javascript
import { useUserStore } from '@/store/modules/user'

const userStore = useUserStore()

// 获取用户信息
const userInfo = userStore.userInfo

// 调用 action
await userStore.getUserInfo()
```

### 路由配置

路由配置在 `src/router/index.js`，支持：

- 路由懒加载
- 路由元信息
- 导航守卫
- 动态路由

添加新路由：

```javascript
{
  path: '/example',
  name: 'Example',
  component: () => import('@/views/Example.vue'),
  meta: {
    title: '示例页面',
    requiresAuth: true
  }
}
```

### 组件开发

#### TinyMCE 富文本编辑器

```vue
<template>
  <TinyMCE v-model="content" :height="500" />
</template>

<script setup>
import { ref } from 'vue'
import TinyMCE from '@/components/TinyMCE.vue'

const content = ref('')
</script>
```

支持的配置项：
- `height`: 编辑器高度（默认 500）
- `disabled`: 是否禁用（默认 false）
- `plugins`: 插件列表
- `toolbar`: 工具栏配置（支持2行布局）

#### 高级搜索组件

```vue
<template>
  <div>
    <el-button @click="showAdvancedSearch = true">
      高级搜索
    </el-button>

    <AdvancedSearch
      v-model="showAdvancedSearch"
      :categories="categories"
      :tags="tags"
      @search="handleSearch"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import AdvancedSearch from '@/components/AdvancedSearch.vue'

const showAdvancedSearch = ref(false)
const categories = ref([])
const tags = ref([])

const handleSearch = ({ type, params }) => {
  if (type === 'fulltext') {
    // 处理全文搜索
    console.log('全文搜索参数:', params)
  } else {
    // 处理高级搜索
    console.log('高级搜索参数:', params)
  }
}
</script>
```

功能特性：
- 支持全文搜索（自然语言、布尔、查询扩展三种模式）
- 支持高级搜索（多字段组合查询）
- 实时搜索建议/自动完成
- 搜索历史记录（localStorage）
- 搜索结果关键词高亮

### 搜索功能使用

系统提供了强大的搜索功能，包括全文搜索和高级搜索。

#### 全文搜索

支持三种搜索模式：

1. **自然语言模式**（推荐）
   - 适合大多数搜索场景
   - 按相关度自动排序
   - 示例：搜索 "Vue 开发教程"

2. **布尔模式**（高级）
   - 支持操作符：`+word`（必须包含）、`-word`（不包含）、`"phrase"`（精确匹配）
   - 示例：`+Vue -React "前端开发"`

3. **查询扩展模式**
   - 自动扩展相关词汇
   - 提高搜索召回率

#### 高级搜索

支持的筛选条件：
- **文本字段**：标题、内容、摘要、作者名称
- **分类和标签**：按分类筛选、多标签筛选
- **文章属性**：状态、置顶、推荐、热门
- **数值范围**：发布时间范围、浏览量范围
- **排序选项**：发布时间、浏览量、点赞数、评论数等

#### 使用示例

```vue
<template>
  <div>
    <!-- 高级搜索按钮 -->
    <el-button @click="showAdvancedSearch = true">
      <el-icon><search /></el-icon>
      高级搜索
    </el-button>

    <!-- 搜索结果显示 -->
    <el-alert v-if="currentSearchType" type="info" closable @close="clearSearch">
      {{ getSearchTypeText() }}
    </el-alert>

    <!-- 文章列表（带高亮） -->
    <el-table :data="articleList">
      <el-table-column label="标题">
        <template #default="{ row }">
          <div v-if="row.highlighted_title" v-html="row.highlighted_title"></div>
          <div v-else>{{ row.title }}</div>
        </template>
      </el-table-column>
    </el-table>

    <!-- 高级搜索组件 -->
    <AdvancedSearch
      v-model="showAdvancedSearch"
      :categories="categories"
      :tags="tags"
      @search="handleAdvancedSearch"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { fullTextSearch, advancedSearch } from '@/api/article'
import AdvancedSearch from '@/components/AdvancedSearch.vue'

const showAdvancedSearch = ref(false)
const articleList = ref([])
const currentSearchType = ref('')

const handleAdvancedSearch = async ({ type, params }) => {
  if (type === 'fulltext') {
    const res = await fullTextSearch(params)
    articleList.value = res.data.list
  } else {
    const res = await advancedSearch(params)
    articleList.value = res.data.list
  }
  currentSearchType.value = type
}
</script>

<style scoped>
/* 高亮关键词样式 */
:deep(mark) {
  background-color: #ffeb3b;
  color: #000;
  padding: 2px 4px;
  border-radius: 2px;
  font-weight: 500;
}
</style>
```

#### 搜索历史

搜索历史自动保存在浏览器 localStorage 中：
- 最多保存 10 条记录
- 点击历史记录可快速重新搜索
- 支持删除单条或清空全部历史

### 请求拦截器

在 `src/utils/request.js` 中配置了请求和响应拦截器：

**请求拦截器**
- 自动添加 JWT Token
- 处理请求配置

**响应拦截器**
- 统一错误处理
- Token 过期自动跳转登录
- 错误消息提示

## 构建部署

### 开发环境

```bash
npm run dev
```

### 生产构建

```bash
npm run build
```

### 预览生产构建

```bash
npm run preview
```

### 部署到服务器

1. 执行 `npm run build` 生成 `dist/` 目录
2. 将 `dist/` 目录上传到服务器
3. 配置 Nginx：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;
    }

    # API 代理（可选）
    location /api/ {
        proxy_pass http://localhost:8000/api/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

## 配置说明

### Vite 配置 (vite.config.js)

```javascript
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true
      }
    }
  }
})
```

### 环境变量

创建 `.env.development` 和 `.env.production` 文件：

```bash
# .env.development
VITE_API_BASE_URL=http://localhost:8000/api

# .env.production
VITE_API_BASE_URL=https://api.your-domain.com/api
```

在代码中使用：

```javascript
const apiBaseUrl = import.meta.env.VITE_API_BASE_URL
```

## 目录说明

### /api 接口层
封装所有后端 API 调用，统一管理接口。

### /components 组件层
存放可复用的公共组件。

### /views 视图层
存放页面级组件，对应路由。

### /router 路由层
定义前端路由配置。

### /store 状态层
使用 Pinia 管理全局状态。

### /utils 工具层
存放工具函数和公共方法。

## 代码规范

### 命名规范

- **文件名**: 使用 PascalCase（如：`ArticleList.vue`）
- **组件名**: 使用 PascalCase
- **变量名**: 使用 camelCase
- **常量名**: 使用 UPPER_SNAKE_CASE

### Vue 3 Composition API

推荐使用 `<script setup>` 语法：

```vue
<script setup>
import { ref, computed, onMounted } from 'vue'

const count = ref(0)
const double = computed(() => count.value * 2)

onMounted(() => {
  console.log('组件已挂载')
})
</script>
```

### 样式规范

推荐使用 scoped 样式：

```vue
<style scoped>
.article-list {
  padding: 20px;
}
</style>
```

## 常见问题

### 1. 端口被占用

修改 `vite.config.js` 中的端口配置：

```javascript
server: {
  port: 3001 // 更换其他端口
}
```

### 2. API 请求跨域

在 `vite.config.js` 中配置代理：

```javascript
server: {
  proxy: {
    '/api': {
      target: 'http://localhost:8000',
      changeOrigin: true
    }
  }
}
```

### 3. TinyMCE 加载失败

确保 `public/tinymce/` 目录下有完整的 TinyMCE 资源文件。

### 4. 图片上传失败

检查：
- 后端 API 是否正常
- 文件大小是否超限
- 上传目录是否有写入权限

### 5. 打包后白屏

确保：
- 路由模式使用 `createWebHashHistory` 或配置服务器 rewrite
- 检查浏览器控制台错误信息
- 确认静态资源路径正确

### 6. 全文搜索无结果

检查：
- 确保后端数据库已创建 FULLTEXT INDEX
- 搜索关键词长度（英文词至少4个字符）
- 确认有已发布的文章
- 查看浏览器控制台网络请求是否成功

### 7. 搜索历史丢失

这是正常行为：
- 搜索历史存储在浏览器 localStorage
- 清除浏览器缓存会导致历史丢失
- 隐私模式不保存历史记录

## 性能优化

### 1. 路由懒加载

```javascript
component: () => import('@/views/Article/List.vue')
```

### 2. 组件按需引入

Element Plus 已配置自动按需引入。

### 3. 图片懒加载

使用 Element Plus 的 `el-image` 组件自带懒加载。

### 4. 代码分割

Vite 自动进行代码分割，无需额外配置。

## 更新日志

### v1.3.0 (2025-11-04)

**重大更新：系统稳定性和用户体验全面提升** 🎉

**界面优化：**
- ✨ 投稿配置：修复分类下拉列表加载问题
- ✨ 广告管理：新增快捷调用代码功能，一键复制Carefree标签
- ✨ 幻灯片管理：新增分组快捷调用代码，包含完整HTML示例
- ✨ 媒体库：添加全选/取消全选按钮，批量操作更便捷
- ✨ 会员管理：会员列表新增VIP到期时间列，支持永久VIP标识
- ✨ 消息通知：修复通知记录不显示问题，添加自动加载机制
- ✨ 短信服务：修复统计数据显示错误，支持嵌套对象访问
- ✨ 缓存管理：修复切换Redis驱动后信息显示问题，优化自动刷新

**权限系统增强：**
- ✨ 角色权限管理：补充所有新增功能的权限定义
  - 新增内容管理权限：文章属性、专题、友情链接、内容模型、自定义字段、回收站
  - 新增SEO管理权限：SEO设置、URL重定向、404监控、Robots.txt、SEO工具
  - 新增系统管理权限：数据库管理、缓存管理、系统日志、操作日志
  - 新增扩展功能权限：广告、幻灯片、会员、投稿、通知、短信、积分商城等
  - 新增模板管理权限：模板编辑器、模板标签教程

**日志功能完善：**
- ✨ 系统日志：显示所有API请求记录
- ✨ 登录日志：显示用户登录/登出记录
- ✨ 安全日志：显示失败登录等安全事件

**修复问题：**
- 🐛 修复多个组件数据加载时机问题
- 🐛 修复部分统计数据显示错误
- 🐛 改进组件响应式数据处理
- 🐛 优化Vue watch监听器使用

**技术改进：**
- 使用可选链操作符`?.`处理嵌套对象访问
- 添加localStorage状态同步机制
- 改进组件生命周期钩子使用
- 优化数据加载和刷新逻辑

**升级说明：**
本次更新主要是界面优化和bug修复，无需特殊升级操作。建议所有用户升级到此版本。

---

### v1.2.0 (2025-10-28)

**新增功能：**
- ✨ 全文搜索功能
  - 支持自然语言模式（按相关度排序）
  - 支持布尔模式（+word -word "phrase"）
  - 支持查询扩展模式（自动扩展相关词）
  - 搜索结果关键词高亮显示
- ✨ 高级搜索功能
  - 支持15+个搜索字段和筛选条件
  - 支持多条件组合查询（标题、内容、摘要、作者等）
  - 支持分类、标签、状态筛选
  - 支持浏览量范围筛选
  - 支持多种排序方式
- ✨ 搜索建议/自动完成
  - 实时搜索建议（基于文章标题）
  - 显示浏览量统计
  - 按热门程度排序
- ✨ 搜索历史功能
  - 自动保存最近10次搜索
  - 一键重新执行历史搜索
  - 支持删除和清空操作

**组件更新：**
- 新增 `AdvancedSearch.vue` 高级搜索对话框组件
- 更新 `List.vue` 文章列表页面，集成高级搜索

**API 更新：**
- 新增 `fullTextSearch` 全文搜索接口
- 新增 `advancedSearch` 高级搜索接口
- 新增 `searchSuggestions` 搜索建议接口

### v1.1.0 (2025-10-21)
- 优化 TinyMCE 编辑器：移除帮助功能，工具栏改为2行布局
- 新增缓存管理页面，支持缓存驱动切换
- 优化 Sitemap 生成界面，合并为单页布局
- 改进错误提示和用户体验

### v1.0.0 (2025-10-15)
- 首个正式版本发布
- 完整的后台管理功能
- 基于 Vue 3 + Vite 构建
- Element Plus 组件库集成
- TinyMCE 富文本编辑器

## 贡献指南

欢迎提交 Issue 和 Pull Request！

1. Fork 本仓库
2. 创建特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 许可证

本项目采用 MIT 开源协议。

## 联系我们

- 邮箱: sinma@qq.com
- 官网: https://www.carefreecms.com

---

Made with ❤️ by CarefreeCMS Team © 2025

![QQ群](qqqun.jpg)