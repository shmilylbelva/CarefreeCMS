# 前端管理页面修改 - 移除全局配置，改用站点配置（完整版）

## 修改说明

配合后端API的修改，前端管理页面进行了全面调整：
- ✅ **完全移除**所有对 `/api/config` 接口的调用
- ✅ 将配置读取改为从站点级别获取
- ✅ 添加**空值安全检查**，防止配置值不存在时报错
- ✅ 添加清晰的提示引导用户使用站点管理
- ✅ 保留旧页面作为提示和引导
- ✅ 删除不再使用的 `api/config.js` 文件

## 检查结果

经过全面检查，前端已**完全不再调用** `/api/config` 接口：
- ✅ 搜索所有 `.vue` 和 `.js` 文件
- ✅ 确认没有任何文件导入 `@/api/config`
- ✅ 确认没有任何文件调用 `getConfig` 或 `saveConfig`（除了本地定义的同名函数）
- ✅ 删除了 `frontend/src/api/config.js` 文件

## 修改的文件

### 1. article/Edit.vue - 文章编辑页面 ⭐ 新增
**位置**: `frontend/src/views/article/Edit.vue`

#### 问题
编辑文章时调用 `/api/config` 接口获取 `article_sub_category` 配置导致错误。

#### 修改内容

**1. 修改API导入** (line 409):
```javascript
// 修改前
import { getConfig } from '@/api/config'
import { getSiteOptions } from '@/api/site'

// 修改后
import { getSiteOptions, getSiteDetail } from '@/api/site'
```

**2. 修改配置加载函数** (line 551-562):
```javascript
// 修改前
const loadSystemConfig = async () => {
  try {
    const res = await getConfig()
    subCategoryEnabled.value = res.data.article_sub_category === 'open'
  } catch (error) {
    console.error('加载系统配置失败', error)
  }
}

// 修改后
const loadSiteConfig = async (siteId) => {
  if (!siteId) return

  try {
    const res = await getSiteDetail(siteId)
    // 添加空值检查，防止配置值不存在时报错
    subCategoryEnabled.value = res.data?.article_sub_category === 'open'
  } catch (error) {
    console.error('加载站点配置失败', error)
    subCategoryEnabled.value = false // 默认关闭
  }
}
```

**重要改进**：
- 使用可选链操作符（`?.`）进行空值检查
- 确保即使站点配置中没有 `article_sub_category` 字段也不会报错
- 如果配置不存在，会自动返回 `false`（关闭副分类）

**3. 修改onMounted和添加watch** (line 997-1024):
```javascript
onMounted(async () => {
  if (isEdit.value) {
    await loadOptions(form.site_id || null)
    await loadCustomFields(form.site_id || null)
    await loadArticle()
    await loadSiteConfig(form.site_id) // 根据文章站点ID加载配置
  } else {
    // ...
    await loadSiteConfig(form.site_id || 1) // 加载默认站点配置
  }
})

// 监听站点ID变化
watch(() => form.site_id, (newSiteId) => {
  if (newSiteId) {
    loadSiteConfig(newSiteId)
  }
})
```

### 2. RecycleBin.vue - 回收站页面 ⭐ 新增
**位置**: `frontend/src/views/RecycleBin.vue`

#### 问题
调用 `/api/config` 检查回收站是否启用，但配置已迁移到站点级别。

#### 修改内容

**1. 移除API导入和变量**:
```javascript
// 删除
import { getConfig } from '@/api/config'
const recycleBinEnabled = ref(true)
```

**2. 删除检查函数**:
```javascript
// 删除整个函数
const checkRecycleBinStatus = async () => { ... }
```

**3. 修改页面提示**:
```vue
<el-alert type="info" title="回收站配置说明">
  回收站功能现已调整为站点级别配置，每个站点可以独立设置。
  请前往：系统管理 > 站点管理 > 编辑站点 > 核心设置
</el-alert>
```

**4. 移除所有 `v-if="recycleBinEnabled"` 判断**

**5. 简化onMounted**:
```javascript
// 修改前
onMounted(async () => {
  await checkRecycleBinStatus()
  loadData()
})

// 修改后
onMounted(() => {
  loadData()
})
```

### 3. seo/Settings.vue - SEO设置页面 ⭐ 新增
**位置**: `frontend/src/views/seo/Settings.vue`

#### 问题
调用 `/api/config` 获取和保存SEO配置，但配置已迁移到站点级别。

#### 修改内容

**1. 添加顶部警告提示**:
```vue
<el-alert type="warning" title="SEO配置已迁移至站点管理">
  首页SEO（TDK）配置已调整为站点级别
  请前往：系统管理 > 站点管理 > 编辑站点 > SEO设置
</el-alert>
```

**2. 修改卡片标题**:
```vue
<h3>SEO设置（已废弃）</h3>
```

**3. 移除API导入**:
```javascript
// 删除
import { getConfig, saveConfig } from '@/api/config'
```

**4. 修改配置函数**:
```javascript
const fetchConfig = async () => {
  // 不再从全局配置获取
  form.seo_title = ''
  form.seo_keywords = ''
  form.seo_description = ''
}

const handleSave = async () => {
  ElMessage.warning('SEO配置已迁移到站点管理，请前往站点管理页面进行配置')
}
```

### 4. site/List.vue - 站点管理页面
**位置**: `frontend/src/views/site/List.vue`

#### 修改内容

**在"核心设置"标签页添加评论配置项** (line 298-349):

```vue
<el-tab-pane label="核心设置" name="core">
  <el-divider>模板设置</el-divider>
  <el-form-item label="首页模板">...</el-form-item>

  <el-divider>系统功能</el-divider>
  <el-form-item label="回收站">...</el-form-item>
  <el-form-item label="文档副栏目">...</el-form-item>

  <el-divider>评论设置</el-divider>

  <el-form-item label="允许游客评论">
    <el-radio-group v-model="form.enable_guest_comment">
      <el-radio :value="true">允许</el-radio>
      <el-radio :value="false">禁止</el-radio>
    </el-radio-group>
  </el-form-item>

  <el-form-item label="评论自动审核">
    <el-radio-group v-model="form.auto_approve">
      <el-radio :value="true">开启</el-radio>
      <el-radio :value="false">关闭</el-radio>
    </el-radio-group>
  </el-form-item>

  <el-form-item label="敏感词过滤">
    <el-radio-group v-model="form.enable_sensitive_filter">
      <el-radio :value="true">开启</el-radio>
      <el-radio :value="false">关闭</el-radio>
    </el-radio-group>
  </el-form-item>
</el-tab-pane>
```

**添加表单字段默认值** (line 489-492):
```javascript
const form = reactive({
  // ...
  // 评论设置
  enable_guest_comment: true,
  auto_approve: false,
  enable_sensitive_filter: true,
})
```

### 5. config/Index.vue - 全局配置页面 ⭐ 完全重构
**位置**: `frontend/src/views/config/Index.vue`

#### 问题
此页面调用已删除的 `/api/config` 接口，并且管理的很多配置已迁移到站点级别。

#### 修改内容

**1. 移除 API 导入**:
```javascript
// 删除
import { getConfig, saveConfig } from '@/api/config'
```

**2. 移除表单数据和相关函数**:
```javascript
// 删除
const form = reactive({ ... }) // 所有网站信息和附件扩展配置
const imageFeatures = ref([])
const fetchConfig = async () => { ... }
const handleSave = async () => { ... }
const beforeUpload = (file) => { ... }
const handleUpload = async ({ file }, field) => { ... }
```

**3. 删除标签页**:
- ❌ 删除"网站信息"标签页（已迁移到站点管理）
- ❌ 删除"附件扩展"标签页（系统级配置，暂无后端支持）
- ✅ 保留"模板管理"标签页（使用正确的模板管理接口）

**4. 移除保存按钮**:
- 模板切换已有自己的保存机制，不需要额外的保存按钮

**5. 修改默认标签页**:
```javascript
// 修改
const activeTab = ref('core') // 默认显示模板管理
```

**6. 简化 onMounted**:
```javascript
// 修改前
onMounted(() => {
  fetchConfig()
  fetchTemplates()
  fetchThemes()
  fetchCurrentTheme()
})

// 修改后
onMounted(() => {
  fetchTemplates()
  fetchThemes()
  fetchCurrentTheme()
})
```

**7. 简化导入**:
```javascript
// 修改后
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { InfoFilled } from '@element-plus/icons-vue'
import { getTemplates, scanThemes, getCurrentTheme, switchTheme } from '@/api/template'
```

### 6. api/config.js - API 文件 ⭐ 已删除
**位置**: `frontend/src/api/config.js`

#### 修改内容
完全删除此文件，因为：
- 后端 `/api/config` 接口已删除
- 前端没有任何地方再使用此文件
- 配置已迁移到站点级别

## 修改汇总表

| 文件 | 修改内容 | 状态 |
|------|---------|------|
| article/Edit.vue | 从站点配置获取article_sub_category，添加空值检查 | ✅ 已修改 |
| RecycleBin.vue | 移除全局回收站检查，改为站点级别说明 | ✅ 已修改 |
| seo/Settings.vue | 添加提示，废弃保存功能 | ✅ 已修改 |
| site/List.vue | 添加评论配置项 | ✅ 已修改 |
| config/Index.vue | 完全重构，移除/api/config调用，删除已迁移的配置 | ✅ 已修改 |
| api/config.js | 删除整个文件 | ✅ 已删除 |

## 受影响的接口

| 接口 | 状态 | 替代方案 |
|------|------|----------|
| GET /api/config | ❌ 已删除 | GET /api/sites/{id} |
| POST /api/config | ❌ 已删除 | PUT /api/sites/{id} |

## 配置迁移对照表

| 配置项 | 原位置 | 新位置 | 访问方式 |
|--------|--------|--------|----------|
| article_sub_category | site_config表 | sites.config | $site->article_sub_category |
| recycle_bin_enable | site_config表 | sites.config | $site->recycle_bin_enable |
| enable_guest_comment | 无 | sites.config | $site->config['enable_guest_comment'] |
| auto_approve | 无 | sites.config | $site->config['auto_approve'] |
| enable_sensitive_filter | 无 | sites.config | $site->config['enable_sensitive_filter'] |
| seo_title | site_config表 | sites.seo_config | $site->seo_title |
| seo_keywords | site_config表 | sites.seo_config | $site->seo_keywords |
| seo_description | site_config表 | sites.seo_config | $site->seo_description |

## 用户操作指引

### 配置文章副分类
1. 进入：系统管理 → 站点管理
2. 点击"编辑"按钮
3. 切换到"核心设置"标签页
4. 设置"文档副栏目"为"开启"

### 配置回收站
1. 进入：系统管理 → 站点管理
2. 点击"编辑"按钮
3. 切换到"核心设置"标签页
4. 设置"回收站"为"开启"或"关闭"

### 配置评论功能
1. 进入：系统管理 → 站点管理
2. 点击"编辑"按钮
3. 切换到"核心设置"标签页
4. 设置评论相关配置

### 配置SEO信息
1. 进入：系统管理 → 站点管理
2. 点击"编辑"按钮
3. 切换到"SEO设置"标签页
4. 填写SEO标题、关键词、描述

## 测试验证

### 1. 测试文章编辑
- ✅ 打开文章编辑页面不再报错
- ✅ 根据站点配置正确显示/隐藏副分类选择器
- ✅ 切换站点时副分类选择器状态正确更新

### 2. 测试回收站
- ✅ 回收站页面正常加载
- ✅ 显示站点级别配置提示
- ✅ 回收站功能正常工作

### 3. 测试SEO设置
- ✅ SEO设置页面正常加载
- ✅ 显示迁移提示
- ✅ 保存按钮显示提示信息

### 4. 测试站点管理
- ✅ 评论配置项正常显示
- ✅ 配置值正确保存
- ✅ 配置值正确读取

## 空值安全处理

### 问题描述
在从站点配置获取信息时，如果直接访问嵌套属性而不进行空值检查，当配置值不存在时会导致运行时错误。

### 解决方案
所有从站点配置中读取的属性都使用了可选链操作符（`?.`）进行空值检查：

**示例 - article/Edit.vue**:
```javascript
// ❌ 错误做法（可能报错）
subCategoryEnabled.value = res.data.article_sub_category === 'open'

// ✅ 正确做法（安全访问）
subCategoryEnabled.value = res.data?.article_sub_category === 'open'
```

**示例 - site/List.vue**:
```javascript
// 使用 undefined 检查和默认值
Object.keys(form).forEach(key => {
  form[key] = row[key] !== undefined ? row[key] : form[key]
})
```

### 最佳实践
1. **使用可选链操作符**：`res.data?.config?.key`
2. **提供默认值**：使用 `??` 或 `||` 操作符
3. **try-catch 错误处理**：在 catch 块中设置安全的默认值
4. **后端保证**：后端使用 `??` 操作符提供默认值

## 向后兼容性

- ✅ 旧页面保留，添加提示引导
- ✅ 路由未改变
- ✅ 所有链接仍然有效
- ✅ 用户体验平滑过渡

## 已知问题

无

## 相关文档

- [后端API修改文档](../../backend/docs/remove_global_config_controller.md)
- [站点管理API文档](../../backend/docs/site_management.md)
- [多站点支持文档](../../backend/docs/multisite_support.md)
