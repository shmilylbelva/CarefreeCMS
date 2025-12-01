# 评论系统使用文档

## 概述

评论系统支持注册用户和游客评论，包含评论发表、审核、回复、点赞、举报等完整功能，并提供敏感词过滤机制。

## 功能特性

### 1. 核心功能
- ✅ 注册用户评论
- ✅ 游客评论（可配置是否允许）
- ✅ 嵌套评论/回复功能
- ✅ 评论审核机制（可配置自动审核或人工审核）
- ✅ 敏感词过滤
- ✅ 评论点赞
- ✅ 评论举报
- ✅ 评论统计

### 2. 管理功能
- ✅ 后台评论列表（支持多条件筛选）
- ✅ 批量审核
- ✅ 批量删除
- ✅ 管理员回复
- ✅ 评论编辑
- ✅ 评论统计分析

## 数据库表结构

### comments 表（评论表）
```sql
CREATE TABLE `comments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `article_id` int unsigned NOT NULL COMMENT '文章ID',
  `user_id` int unsigned DEFAULT NULL COMMENT '前台用户ID（注册用户）',
  `is_guest` tinyint NOT NULL DEFAULT '0' COMMENT '是否游客评论：0=注册用户，1=游客',
  `parent_id` int unsigned NOT NULL DEFAULT '0' COMMENT '父评论ID，0表示顶级评论',
  `user_name` varchar(50) DEFAULT NULL COMMENT '评论者名称（游客）',
  `user_email` varchar(100) DEFAULT NULL COMMENT '评论者邮箱（游客）',
  `user_ip` varchar(50) DEFAULT NULL COMMENT '评论者IP',
  `content` text NOT NULL COMMENT '评论内容',
  `like_count` int unsigned NOT NULL DEFAULT '0' COMMENT '点赞数',
  `is_admin` tinyint NOT NULL DEFAULT '0' COMMENT '是否管理员：0=否，1=是',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '状态：0=待审核，1=已通过，2=已拒绝',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_article_id` (`article_id`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_status` (`status`),
  KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='评论表';
```

## 前台接口API

### 1. 获取文章评论列表
**接口**: `GET /backend/front/comments?article_id={id}&page=1&limit=20`
**认证**: 不需要
**参数**:
- article_id: 文章ID（必填）
- page: 页码（默认1）
- limit: 每页数量（默认20）

**返回**: 评论树形结构（包含子评论）

**示例**:
```bash
curl "http://localhost:8000/backend/front/comments?article_id=1&page=1&limit=10"
```

### 2. 发表评论
**接口**: `POST /backend/front/comments`
**认证**: 不需要（支持游客和注册用户）
**参数**:
- article_id: 文章ID（必填）
- content: 评论内容（必填，5-500字符）
- parent_id: 父评论ID（可选，回复评论时填写）
- user_name: 昵称（游客必填）
- user_email: 邮箱（游客必填）

**说明**:
- 注册用户需在请求头中携带Token
- 游客评论需提供昵称和邮箱
- 评论内容会经过敏感词过滤
- 根据配置决定是否需要审核

**示例**:

游客评论：
```bash
curl -X POST http://localhost:8000/backend/front/comments \
  -H "Content-Type: application/json" \
  -d '{
    "article_id": 1,
    "content": "这是一条游客评论",
    "user_name": "游客张三",
    "user_email": "guest@example.com"
  }'
```

注册用户评论：
```bash
curl -X POST http://localhost:8000/backend/front/comments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "article_id": 1,
    "content": "这是一条注册用户评论"
  }'
```

### 3. 点赞评论
**接口**: `POST /backend/front/comments/like`
**认证**: 需要Token
**参数**:
- comment_id: 评论ID

### 4. 取消点赞
**接口**: `POST /backend/front/comments/unlike`
**认证**: 需要Token
**参数**:
- comment_id: 评论ID

### 5. 举报评论
**接口**: `POST /backend/front/comments/report`
**认证**: 不需要
**参数**:
- comment_id: 评论ID
- reason: 举报原因

### 6. 获取评论详情
**接口**: `GET /backend/front/comments/{id}`
**认证**: 不需要

## 后台管理接口API

### 1. 评论列表
**接口**: `GET /backend/comments?page=1&limit=20`
**认证**: 需要管理员Token
**搜索参数**:
- article_id: 文章ID
- user_id: 用户ID
- status: 状态（0=待审核，1=已通过，2=已拒绝）
- is_guest: 是否游客（0=注册用户，1=游客）
- keyword: 关键词（搜索评论内容）
- start_time: 开始时间
- end_time: 结束时间

### 2. 审核评论
**接口**: `POST /backend/comments/{id}/audit`
**认证**: 需要管理员Token
**参数**:
- status: 状态（1=通过，2=拒绝）

### 3. 批量审核
**接口**: `POST /backend/comments/batch-audit`
**认证**: 需要管理员Token
**参数**:
- ids: 评论ID数组
- status: 状态（1=通过，2=拒绝）

### 4. 删除评论
**接口**: `DELETE /backend/comments/{id}`
**认证**: 需要管理员Token
**说明**: 会同时删除所有子评论

### 5. 批量删除
**接口**: `POST /backend/comments/batch-delete`
**认证**: 需要管理员Token
**参数**:
- ids: 评论ID数组

### 6. 管理员回复
**接口**: `POST /backend/comments/{id}/reply`
**认证**: 需要管理员Token
**参数**:
- content: 回复内容

### 7. 编辑评论
**接口**: `PUT /backend/comments/{id}`
**认证**: 需要管理员Token
**参数**:
- content: 评论内容

### 8. 评论统计
**接口**: `GET /backend/comments/statistics`
**认证**: 需要管理员Token
**返回**:
- 总评论数
- 各状态评论数
- 今日/本周/本月评论数
- 注册用户/游客评论数
- 最近7天趋势
- 评论最多的文章Top 10

## 敏感词过滤

### 敏感词过滤服务 (SensitiveWordFilter)

**类路径**: `app\service\SensitiveWordFilter`

**主要方法**:

```php
// 检测是否包含敏感词
SensitiveWordFilter::check($text); // 返回 bool

// 过滤敏感词（替换为*）
SensitiveWordFilter::filter($text, '*'); // 返回过滤后的文本

// 获取敏感词列表
SensitiveWordFilter::getWords($text); // 返回数组

// 添加敏感词
SensitiveWordFilter::addWords(['新敏感词']);

// 移除敏感词
SensitiveWordFilter::removeWords(['某敏感词']);

// 获取所有敏感词
SensitiveWordFilter::getAllWords(); // 返回数组
```

**敏感词配置**:

可以在 `config/sensitive_words.php` 中配置敏感词列表（文件需手动创建）：

```php
<?php
return [
    // 政治相关
    '敏感词1',
    '敏感词2',

    // 色情相关
    '敏感词3',

    // 其他
    '敏感词4',
];
```

**算法**: 使用DFA（确定有限状态自动机）算法，高效匹配敏感词。

## 系统配置

评论系统支持以下配置项（在系统配置中设置）：

```php
$commentConfig = [
    'enable_guest_comment' => true,   // 是否允许游客评论
    'auto_approve'         => false,  // 是否自动审核通过
    'enable_sensitive_filter' => true, // 是否启用敏感词过滤
];
```

## 前端集成示例

### Vue.js示例

```javascript
// 获取评论列表
async function getComments(articleId, page = 1) {
  const response = await fetch(
    `http://localhost:8000/backend/front/comments?article_id=${articleId}&page=${page}&limit=10`
  );
  return await response.json();
}

// 发表评论（注册用户）
async function postComment(articleId, content, parentId = 0) {
  const token = localStorage.getItem('token');
  const response = await fetch('http://localhost:8000/backend/front/comments', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      article_id: articleId,
      content: content,
      parent_id: parentId
    })
  });
  return await response.json();
}

// 发表评论（游客）
async function postGuestComment(articleId, content, userName, userEmail) {
  const response = await fetch('http://localhost:8000/backend/front/comments', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      article_id: articleId,
      content: content,
      user_name: userName,
      user_email: userEmail
    })
  });
  return await response.json();
}

// 点赞评论
async function likeComment(commentId) {
  const token = localStorage.getItem('token');
  const response = await fetch('http://localhost:8000/backend/front/comments/like', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ comment_id: commentId })
  });
  return await response.json();
}
```

## 注意事项

1. **评论状态**:
   - 0: 待审核（默认）
   - 1: 已通过
   - 2: 已拒绝

2. **游客评论**: 需要提供昵称和邮箱，邮箱格式会自动验证

3. **注册用户评论**: 需要在请求头中携带有效的JWT Token

4. **敏感词过滤**: 如果评论内容包含敏感词，会直接拒绝提交

5. **防刷机制**: 同一内容在1分钟内不能重复提交

6. **评论树结构**: 支持无限级嵌套回复，前端展示时可根据需要限制层级

7. **删除机制**: 删除评论会同时删除所有子评论

8. **计数更新**: 审核通过评论后会自动更新文章的评论数和用户的评论数

## 测试记录

### 前台功能测试
- ✅ 游客评论功能正常
- ✅ 注册用户评论功能正常
- ✅ 评论列表获取正常（树形结构）
- ✅ Token解析正常（支持游客和登录用户）
- ✅ 敏感词过滤正常（自动拒绝含敏感词的评论）
- ✅ 防刷机制正常（1分钟内不能提交相同内容）
- ✅ 评论举报功能正常
- ✅ 评论详情获取正常

### 后台管理功能测试
- ✅ 评论列表获取正常（支持多条件筛选）
- ✅ 单条评论审核正常（通过/拒绝）
- ✅ 批量审核功能正常
- ✅ 单条评论删除正常
- ✅ 批量删除功能正常
- ✅ 管理员回复功能正常（自动标记is_admin=1）
- ✅ 评论编辑功能正常
- ✅ 评论统计功能正常（包含趋势、Top文章等）
- ✅ 评论嵌套结构正常（子评论正确显示）
- ✅ 评论计数更新正常（审核通过后自动更新文章评论数）

### 测试数据统计
- 总评论数：5条（测试后）
- 审核通过：4条
- 审核拒绝：1条
- 游客评论：3条
- 注册用户评论：2条
- 管理员回复：1条

### Bug修复记录
1. ✅ 修复了评论审核接口参数获取问题（从POST改为路由参数）
2. ✅ 修复了管理员回复接口参数获取问题（从POST改为路由参数）
3. ✅ 修复了路由冲突问题（调整report路由顺序）

## 后续扩展建议

1. **评论通知**: 实现邮件或站内信通知功能
2. **评论表情**: 支持表情符号
3. **评论图片**: 允许在评论中上传图片
4. **评论编辑**: 允许用户编辑自己的评论（时间限制）
5. **评论删除**: 允许用户删除自己的评论
6. **评论投票**: 支持踩/反对功能
7. **热门评论**: 根据点赞数显示热门评论
8. **评论排序**: 支持按时间、热度等方式排序
9. **@提及功能**: 支持@某用户
10. **评论搜索**: 全站评论搜索功能

## 详细测试记录

### 1. 游客评论测试
```bash
curl -X POST http://localhost:8000/backend/front/comments \
  -H "Content-Type: application/json" \
  -d '{
    "article_id": 1,
    "content": "这是一条游客评论",
    "user_name": "游客张三",
    "user_email": "guest@example.com"
  }'
```
**结果**: ✅ 成功创建评论，状态为待审核

### 2. 注册用户评论测试
```bash
curl -X POST http://localhost:8000/backend/front/comments \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "article_id": 1,
    "content": "这是一条注册用户评论"
  }'
```
**结果**: ✅ 成功创建评论，自动关联用户ID

### 3. 敏感词过滤测试
```bash
curl -X POST http://localhost:8000/backend/front/comments \
  -H "Content-Type: application/json" \
  -d '{
    "article_id": 1,
    "content": "这里有敏感词：赌博和色情内容",
    "user_name": "测试用户",
    "user_email": "test@test.com"
  }'
```
**结果**: ✅ 返回错误 "评论内容包含敏感词，请修改后重试"

### 4. 评论审核测试
```bash
# 审核通过
curl -X POST http://localhost:8000/backend/comments/1/audit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{"status": 1}'

# 审核拒绝
curl -X POST http://localhost:8000/backend/comments/2/audit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{"status": 2}'
```
**结果**: ✅ 审核成功，评论状态正确更新

### 5. 批量审核测试
```bash
curl -X POST http://localhost:8000/backend/comments/batch-audit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{"ids": [3, 4], "status": 1}'
```
**结果**: ✅ 成功审核2条评论

### 6. 管理员回复测试
```bash
curl -X POST http://localhost:8000/backend/comments/1/reply \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{"content": "感谢您的评论！这是管理员的回复。"}'
```
**结果**: ✅ 创建回复成功，is_admin标记为1，自动审核通过

### 7. 评论树形结构测试
```bash
curl "http://localhost:8000/backend/front/comments?article_id=1&page=1&limit=20"
```
**结果**: ✅ 正确返回树形结构，子评论嵌套在父评论的children数组中

### 8. 评论统计测试
```bash
curl http://localhost:8000/backend/comments/statistics \
  -H "Authorization: Bearer {admin_token}"
```
**结果**: ✅ 返回完整统计数据，包括：
- 总评论数、各状态评论数
- 今日/本周/本月评论数
- 最近7天趋势
- Top 10热门文章

### 9. 评论删除测试
```bash
# 单条删除
curl -X DELETE http://localhost:8000/backend/comments/6 \
  -H "Authorization: Bearer {admin_token}"

# 批量删除
curl -X POST http://localhost:8000/backend/comments/batch-delete \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{"ids": [7, 8]}'
```
**结果**: ✅ 删除成功，同时删除所有子评论

### 10. 评论编辑测试
```bash
curl -X PUT http://localhost:8000/backend/comments/1 \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {admin_token}" \
  -d '{"content": "这是编辑后的评论内容"}'
```
**结果**: ✅ 更新成功

### 11. 评论举报测试
```bash
curl -X POST http://localhost:8000/backend/front/comments/report \
  -H "Content-Type: application/json" \
  -d '{
    "comment_id": 3,
    "reason": "测试举报：评论包含不当内容"
  }'
```
**结果**: ✅ 举报成功

---

**开发完成时间**: 2025-10-28
**开发状态**: ✅ 已完成并通过全面测试
**版本**: v1.0.0
**测试状态**: ✅ 全部功能测试通过
