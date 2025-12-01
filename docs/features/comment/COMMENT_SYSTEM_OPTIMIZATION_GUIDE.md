# 评论系统优化功能使用指南

> CarefreeCMS 评论系统优化完整文档 - 敏感词过滤、用户通知、智能审核

**版本**: v1.7.0
**最后更新**: 2025-11-08

---

## 📋 功能概述

评论系统优化包含三大核心功能：

1. **敏感词过滤系统** - 基于DFA算法的高效敏感词检测，支持文章、评论、单页等所有内容
2. **用户通知系统** - 站内信+邮件双渠道通知，支持评论回复、点赞、文章审核等场景
3. **智能审核系统** - 基于用户信誉度的智能审核机制，自动识别和处理违规内容

---

## 🎯 核心特性

### 敏感词过滤
- ✅ **DFA算法** - 字典树实现，检测效率O(n)
- ✅ **三级处理** - 提示警告、自动替换、拒绝发布
- ✅ **分类管理** - 政治、色情、暴力、广告、辱骂等6大类
- ✅ **批量导入** - 支持批量导入敏感词库
- ✅ **全局应用** - 适用于文章、评论、单页等所有文本内容
- ✅ **实时缓存** - Redis缓存敏感词树，1小时TTL
- ✅ **命中统计** - 记录每个敏感词的命中次数

### 用户通知
- ✅ **站内信通知** - 实时站内消息推送
- ✅ **邮件通知** - 异步邮件队列发送
- ✅ **通知类型** - 评论回复、点赞、文章审核、系统通知
- ✅ **个性化设置** - 用户可自定义接收渠道
- ✅ **未读数统计** - 实时未读消息计数
- ✅ **批量操作** - 批量标记已读、批量删除

### 智能审核
- ✅ **信誉度系统** - 100分制用户信誉评分
- ✅ **自动审核** - 高信誉用户自动通过审核
- ✅ **违规记录** - 完整的违规内容追踪
- ✅ **分级处理** - 根据违规程度自动降低信誉度

---

## 📊 数据库表结构

### 1. 敏感词库表 (sensitive_words)

```sql
CREATE TABLE `sensitive_words` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `word` varchar(100) NOT NULL COMMENT '敏感词',
  `level` tinyint(1) NOT NULL DEFAULT '2' COMMENT '处理级别(1-提示 2-替换 3-拒绝)',
  `replacement` varchar(10) DEFAULT '***' COMMENT '替换词',
  `category` varchar(50) DEFAULT 'general' COMMENT '分类',
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT '启用状态',
  `hit_count` int(11) DEFAULT '0' COMMENT '命中次数',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注说明',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_word` (`word`),
  KEY `idx_category` (`category`),
  KEY `idx_enabled` (`is_enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**分类说明**:
- `politics` - 政治敏感
- `porn` - 色情内容
- `violence` - 暴力内容
- `ad` - 广告内容
- `abuse` - 辱骂内容
- `general` - 其他

**处理级别**:
- `1` - 提示警告（允许发布，但记录违规）
- `2` - 自动替换（替换为***后发布）
- `3` - 拒绝发布（直接拒绝，不允许发布）

### 2. 违规内容记录表 (content_violations)

```sql
CREATE TABLE `content_violations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `content_type` varchar(20) NOT NULL COMMENT '内容类型(article/comment/page)',
  `content_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `matched_words` text COMMENT '匹配到的敏感词(JSON)',
  `original_content` text COMMENT '原始内容片段',
  `filtered_content` text COMMENT '过滤后内容',
  `action` varchar(20) NOT NULL COMMENT '处理动作(warn/replace/reject)',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT '状态',
  `reviewed_by` int(11) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_content` (`content_type`, `content_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 3. 用户通知表 (user_notifications)

```sql
CREATE TABLE `user_notifications` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '接收用户ID',
  `type` varchar(50) NOT NULL COMMENT '通知类型',
  `title` varchar(200) NOT NULL,
  `content` text,
  `related_type` varchar(20) DEFAULT NULL,
  `related_id` int(11) UNSIGNED DEFAULT NULL,
  `sender_id` int(11) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`, `is_read`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**通知类型**:
- `comment_reply` - 评论回复
- `comment_like` - 评论点赞
- `article_approved` - 文章审核通过
- `article_rejected` - 文章被拒绝
- `system` - 系统通知

### 4. 用户通知设置表 (user_notification_settings)

### 5. 用户信誉度表 (user_reputation)

---

## 💻 后端API使用

### 1. 敏感词管理API

#### 1.1 获取敏感词列表
```http
GET /api/sensitive-words?page=1&page_size=20&category=politics
```

**响应**:
```json
{
  "code": 0,
  "message": "success",
  "data": {
    "total": 100,
    "list": [
      {
        "id": 1,
        "word": "反动",
        "level": 3,
        "category": "politics",
        "hit_count": 5
      }
    ]
  }
}
```

#### 1.2 创建敏感词
```http
POST /api/sensitive-words
Content-Type: application/json

{
  "word": "测试敏感词",
  "level": 2,
  "category": "general",
  "replacement": "***",
  "is_enabled": 1
}
```

#### 1.3 批量导入
```http
POST /api/sensitive-words/batch-import
Content-Type: application/json

{
  "words": "敏感词1\n敏感词2\n敏感词3",
  "level": 2,
  "category": "general",
  "replacement": "***"
}
```

#### 1.4 测试检测
```http
POST /api/sensitive-words/test-check
Content-Type: application/json

{
  "content": "这是一段包含敏感词的测试文本"
}
```

**响应**:
```json
{
  "code": 0,
  "data": {
    "has_sensitive": true,
    "matched_words": ["敏感词"],
    "matched_count": 1,
    "filtered_content": "这是一段包含***的测试文本",
    "matched_details": [
      {
        "word": "敏感词",
        "level": 2,
        "category": "general",
        "replacement": "***"
      }
    ]
  }
}
```

### 2. 集成到文章/评论发布

#### 2.1 在文章控制器中集成

```php
use app\service\SensitiveWordService;

public function save(Request $request): Response
{
    $data = $request->only(['title', 'content', ...]);
    $userId = $request->userId ?? 0;

    // 敏感词检测
    $sensitiveService = new SensitiveWordService();

    // 检测标题
    $titleResult = $sensitiveService->checkAndHandle(
        'article',
        0, // 新文章ID为0
        $userId,
        $data['title'],
        true // 自动替换
    );

    if (!$titleResult['allowed']) {
        return json([
            'code' => 400,
            'message' => $titleResult['message'],
            'data' => ['matched_words' => $titleResult['matched_words']]
        ]);
    }

    // 检测内容
    $contentResult = $sensitiveService->checkAndHandle(
        'article',
        0,
        $userId,
        $data['content'],
        true
    );

    if (!$contentResult['allowed']) {
        return json([
            'code' => 400,
            'message' => $contentResult['message']
        ]);
    }

    // 使用过滤后的内容
    $data['title'] = $titleResult['content'];
    $data['content'] = $contentResult['content'];

    // 保存文章...
    $article = Article::create($data);

    return json(['code' => 0, 'message' => '发布成功']);
}
```

#### 2.2 在评论控制器中集成

```php
public function save(Request $request): Response
{
    $data = $request->only(['article_id', 'content', 'parent_id']);
    $userId = $request->userId ?? 0;

    // 敏感词检测
    $sensitiveService = new SensitiveWordService();
    $result = $sensitiveService->checkAndHandle(
        'comment',
        0,
        $userId,
        $data['content'],
        true // 自动替换
    );

    if (!$result['allowed']) {
        return json([
            'code' => 400,
            'message' => $result['message']
        ]);
    }

    $data['content'] = $result['content'];

    // 保存评论
    $comment = Comment::create($data);

    // 发送通知（如果是回复）
    if ($data['parent_id']) {
        $parentComment = Comment::find($data['parent_id']);
        if ($parentComment && $parentComment->user_id != $userId) {
            $notificationService = new NotificationService();
            $notificationService->sendCommentReplyNotification(
                $comment->id,
                $parentComment->user_id,
                $userId,
                $data['article_id'],
                $article->title,
                $data['content']
            );
        }
    }

    return json(['code' => 0, 'message' => '评论成功']);
}
```

### 3. 用户通知API

#### 3.1 获取通知列表
```http
GET /api/user-notifications?page=1&page_size=20&is_read=0
Authorization: Bearer {token}
```

#### 3.2 获取未读数量
```http
GET /api/user-notifications/unread-count
Authorization: Bearer {token}
```

#### 3.3 标记为已读
```http
POST /api/user-notifications/{id}/mark-as-read
Authorization: Bearer {token}
```

#### 3.4 全部标记为已读
```http
POST /api/user-notifications/mark-all-as-read
Authorization: Bearer {token}
```

#### 3.5 获取/更新通知设置
```http
GET /api/user-notifications/settings
POST /api/user-notifications/settings

{
  "settings": {
    "comment_reply": {
      "site_enabled": 1,
      "email_enabled": 1
    },
    "comment_like": {
      "site_enabled": 1,
      "email_enabled": 0
    }
  }
}
```

---

## 🎨 前端集成

### 1. 敏感词管理界面

创建 `frontend/src/views/system/SensitiveWords.vue`:

```vue
<template>
  <div class="sensitive-words-container">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>敏感词管理</span>
          <div>
            <el-button type="primary" @click="handleAdd">添加敏感词</el-button>
            <el-button @click="handleBatchImport">批量导入</el-button>
            <el-button type="success" @click="handleTest">测试检测</el-button>
          </div>
        </div>
      </template>

      <!-- 筛选条件 -->
      <el-form :inline="true" :model="searchForm">
        <el-form-item label="分类">
          <el-select v-model="searchForm.category" clearable>
            <el-option label="全部" value="" />
            <el-option v-for="(label, value) in categories" :key="value"
              :label="label" :value="value" />
          </el-select>
        </el-form-item>
        <el-form-item label="级别">
          <el-select v-model="searchForm.level" clearable>
            <el-option label="全部" value="" />
            <el-option v-for="(label, value) in levels" :key="value"
              :label="label" :value="value" />
          </el-select>
        </el-form-item>
        <el-form-item label="关键词">
          <el-input v-model="searchForm.keyword" placeholder="搜索敏感词" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="loadList">查询</el-button>
          <el-button @click="handleReset">重置</el-button>
        </el-form-item>
      </el-form>

      <!-- 数据表格 -->
      <el-table :data="list" v-loading="loading">
        <el-table-column type="selection" width="55" />
        <el-table-column prop="word" label="敏感词" />
        <el-table-column prop="category" label="分类">
          <template #default="{row}">
            <el-tag>{{ categories[row.category] }}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="level" label="处理级别">
          <template #default="{row}">
            <el-tag :type="getLevelType(row.level)">
              {{ levels[row.level] }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="hit_count" label="命中次数" width="100" />
        <el-table-column prop="is_enabled" label="状态" width="80">
          <template #default="{row}">
            <el-switch v-model="row.is_enabled" :active-value="1" :inactive-value="0"
              @change="handleStatusChange(row)" />
          </template>
        </el-table-column>
        <el-table-column label="操作" width="180" fixed="right">
          <template #default="{row}">
            <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
            <el-button type="danger" link @click="handleDelete(row.id)">删除</el-button>
          </template>
        </el-table-column>
      </el-table>

      <!-- 分页 -->
      <el-pagination
        v-model:current-page="page"
        v-model:page-size="pageSize"
        :total="total"
        @current-change="loadList"
        layout="total, prev, pager, next, jumper"
      />
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import {
  getSensitiveWords,
  createSensitiveWord,
  updateSensitiveWord,
  deleteSensitiveWord,
  getSensitiveWordCategories,
  getSensitiveWordLevels
} from '@/api/sensitiveWord'

const loading = ref(false)
const list = ref([])
const total = ref(0)
const page = ref(1)
const pageSize = ref(20)
const categories = ref({})
const levels = ref({})

const searchForm = reactive({
  category: '',
  level: '',
  keyword: ''
})

const loadList = async () => {
  loading.value = true
  try {
    const res = await getSensitiveWords({
      page: page.value,
      page_size: pageSize.value,
      ...searchForm
    })
    list.value = res.data.list
    total.value = res.data.total
  } finally {
    loading.value = false
  }
}

const loadOptions = async () => {
  const [catRes, levelRes] = await Promise.all([
    getSensitiveWordCategories(),
    getSensitiveWordLevels()
  ])
  categories.value = catRes.data
  levels.value = levelRes.data
}

const getLevelType = (level) => {
  const types = { 1: 'info', 2: 'warning', 3: 'danger' }
  return types[level] || ''
}

onMounted(() => {
  loadOptions()
  loadList()
})
</script>
```

### 2. API封装

创建 `frontend/src/api/sensitiveWord.js`:

```javascript
import request from '@/utils/request'

export function getSensitiveWords(params) {
  return request({
    url: '/sensitive-words',
    method: 'get',
    params
  })
}

export function createSensitiveWord(data) {
  return request({
    url: '/sensitive-words',
    method: 'post',
    data
  })
}

export function updateSensitiveWord(id, data) {
  return request({
    url: `/sensitive-words/${id}`,
    method: 'put',
    data
  })
}

export function deleteSensitiveWord(id) {
  return request({
    url: `/sensitive-words/${id}`,
    method: 'delete'
  })
}

export function batchImportSensitiveWords(data) {
  return request({
    url: '/sensitive-words/batch-import',
    method: 'post',
    data
  })
}

export function testSensitiveWord(data) {
  return request({
    url: '/sensitive-words/test-check',
    method: 'post',
    data
  })
}

export function getSensitiveWordCategories() {
  return request({
    url: '/sensitive-words/categories',
    method: 'get'
  })
}

export function getSensitiveWordLevels() {
  return request({
    url: '/sensitive-words/levels',
    method: 'get'
  })
}
```

---

## 🔧 配置说明

### 1. 添加路由配置

在 `backend/route/api.php` 中添加：

```php
// 敏感词管理（需要管理员权限）
Route::resource('sensitive-words', 'app\controller\api\SensitiveWordController');
Route::post('sensitive-words/batch-import', 'app\controller\api\SensitiveWordController@batchImport');
Route::post('sensitive-words/batch-delete', 'app\controller\api\SensitiveWordController@batchDelete');
Route::post('sensitive-words/batch-update-status', 'app\controller\api\SensitiveWordController@batchUpdateStatus');
Route::get('sensitive-words/categories', 'app\controller\api\SensitiveWordController@categories');
Route::get('sensitive-words/levels', 'app\controller\api\SensitiveWordController@levels');
Route::get('sensitive-words/statistics', 'app\controller\api\SensitiveWordController@statistics');
Route::post('sensitive-words/test-check', 'app\controller\api\SensitiveWordController@testCheck');

// 违规记录管理
Route::resource('content-violations', 'app\controller\api\ContentViolationController')->except(['save', 'update']);
Route::post('content-violations/{id}/mark-reviewed', 'app\controller\api\ContentViolationController@markAsReviewed');
Route::post('content-violations/{id}/mark-ignored', 'app\controller\api\ContentViolationController@markAsIgnored');
Route::post('content-violations/batch-review', 'app\controller\api\ContentViolationController@batchReview');

// 用户通知（前台）
Route::get('user-notifications', 'app\controller\api\UserNotificationController@index');
Route::get('user-notifications/unread-count', 'app\controller\api\UserNotificationController@unreadCount');
Route::post('user-notifications/{id}/mark-as-read', 'app\controller\api\UserNotificationController@markAsRead');
Route::post('user-notifications/batch-mark-as-read', 'app\controller\api\UserNotificationController@batchMarkAsRead');
Route::post('user-notifications/mark-all-as-read', 'app\controller\api\UserNotificationController@markAllAsRead');
Route::delete('user-notifications/{id}', 'app\controller\api\UserNotificationController@delete');
Route::delete('user-notifications/clear-read', 'app\controller\api\UserNotificationController@clearRead');
Route::get('user-notifications/settings', 'app\controller\api\UserNotificationController@settings');
Route::post('user-notifications/settings', 'app\controller\api\UserNotificationController@updateSettings');
```

### 2. 前端路由配置

在 `frontend/src/router/index.js` 中添加：

```javascript
{
  path: 'sensitive-words',
  name: 'SensitiveWords',
  component: () => import('@/views/system/SensitiveWords.vue'),
  meta: { title: '敏感词管理' }
},
{
  path: 'content-violations',
  name: 'ContentViolations',
  component: () => import('@/views/system/ContentViolations.vue'),
  meta: { title: '违规记录' }
}
```

---

## 📈 性能优化

### 1. 敏感词检测性能

- **DFA算法**: 时间复杂度O(n)，n为文本长度
- **Redis缓存**: 敏感词树缓存1小时，避免频繁读取数据库
- **批量检测**: 支持一次检测多个字段

### 2. 缓存策略

```php
// 清除敏感词缓存（添加/更新/删除敏感词后自动调用）
SensitiveWordService::clearCache();

// 手动重新加载
$service = new SensitiveWordService();
$service->reload();
```

---

## 🛡️ 安全建议

1. **敏感词库保护**: 敏感词管理接口需要管理员权限
2. **内容记录**: 所有违规内容都有完整记录，便于审核和追溯
3. **用户信誉**: 通过信誉度系统自动识别恶意用户
4. **频率限制**: 建议对评论发布添加频率限制（如1分钟内最多3条）

---

## 📞 技术支持

- **文档**: [docs/COMMENT_SYSTEM_OPTIMIZATION_GUIDE.md](./COMMENT_SYSTEM_OPTIMIZATION_GUIDE.md)
- **问题反馈**: https://gitee.com/carefreeteam/issues
- **邮箱**: sinma@qq.com
- **QQ群**: 113572201

---

**Made with ❤️ by CarefreeCMS Team © 2025**
