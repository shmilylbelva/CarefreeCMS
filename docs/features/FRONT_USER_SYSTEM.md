# 前台用户系统使用文档

## 概述

前台用户系统提供了完整的用户注册、登录、资料管理、收藏、点赞、阅读历史、积分系统、关注等功能。

## 功能列表

### 1. 用户认证

#### 1.1 用户注册
- **接口**: `POST /backend/front/auth/register`
- **参数**:
  - username: 用户名（3-20字符，只能包含字母和数字）
  - password: 密码（6-20字符）
  - email: 邮箱（必填）
  - nickname: 昵称（可选，默认使用用户名）
- **返回**: 用户ID、用户名、昵称
- **说明**: 注册成功后自动奖励10积分

#### 1.2 用户登录
- **接口**: `POST /backend/front/auth/login`
- **参数**:
  - username: 用户名或邮箱
  - password: 密码
- **返回**: Token和用户信息
- **说明**: 支持用户名或邮箱登录，每日首次登录奖励5积分

#### 1.3 退出登录
- **接口**: `POST /backend/front/auth/logout`
- **认证**: 需要Token
- **说明**: JWT无状态，前端删除Token即可

#### 1.4 获取用户信息
- **接口**: `GET /backend/front/auth/info`
- **认证**: 需要Token
- **返回**: 完整的用户信息

#### 1.5 修改密码
- **接口**: `POST /backend/front/auth/change-password`
- **认证**: 需要Token
- **参数**:
  - old_password: 旧密码
  - new_password: 新密码（至少6位）
  - confirm_password: 确认密码

#### 1.6 找回密码
- **发送重置邮件**: `POST /backend/front/auth/send-reset-email`
  - 参数: email（邮箱）
- **重置密码**: `POST /backend/front/auth/reset-password`
  - 参数: token、new_password、confirm_password

#### 1.7 邮箱验证
- **发送验证邮件**: `POST /backend/front/auth/send-verify-email`（需要Token）
- **验证邮箱**: `GET /backend/front/auth/verify-email?token={token}`

### 2. 用户资料管理

#### 2.1 获取资料
- **接口**: `GET /backend/front/profile`
- **认证**: 需要Token

#### 2.2 更新资料
- **接口**: `PUT /backend/front/profile`
- **认证**: 需要Token
- **可更新字段**:
  - nickname: 昵称
  - real_name: 真实姓名
  - phone: 手机号
  - gender: 性别（0=保密，1=男，2=女）
  - birthday: 生日
  - province: 省份
  - city: 城市
  - signature: 个性签名
  - bio: 个人简介

#### 2.3 上传头像
- **接口**: `POST /backend/front/profile/avatar`
- **认证**: 需要Token
- **参数**: avatar（文件，最大2MB，支持jpg/jpeg/png/gif）

### 3. 收藏管理

#### 3.1 收藏列表
- **接口**: `GET /backend/front/favorites?page=1&limit=20`
- **认证**: 需要Token

#### 3.2 添加收藏
- **接口**: `POST /backend/front/favorites`
- **认证**: 需要Token
- **参数**: article_id（文章ID）

#### 3.3 取消收藏
- **接口**: `DELETE /backend/front/favorites`
- **认证**: 需要Token
- **参数**: article_id（文章ID）

### 4. 点赞管理

#### 4.1 点赞
- **接口**: `POST /backend/front/likes`
- **认证**: 需要Token
- **参数**:
  - target_type: 目标类型（article=文章，comment=评论）
  - target_id: 目标ID

#### 4.2 取消点赞
- **接口**: `DELETE /backend/front/likes`
- **认证**: 需要Token
- **参数**:
  - target_type: 目标类型
  - target_id: 目标ID

### 5. 阅读历史

#### 5.1 阅读历史列表
- **接口**: `GET /backend/front/read-history?page=1&limit=20`
- **认证**: 需要Token

#### 5.2 记录阅读历史
- **接口**: `POST /backend/front/read-history`
- **认证**: 需要Token
- **参数**:
  - article_id: 文章ID
  - read_progress: 阅读进度（百分比）
  - read_time: 阅读时长（秒）

### 6. 积分管理

#### 6.1 积分日志
- **接口**: `GET /backend/front/point-logs?page=1&limit=20`
- **认证**: 需要Token

#### 6.2 积分规则
- 注册奖励：10积分
- 每日首次登录：5积分
- 发布文章：可配置
- 发表评论：可配置
- 其他操作：可自定义

### 7. 关注管理

#### 7.1 关注用户
- **接口**: `POST /backend/front/follow`
- **认证**: 需要Token
- **参数**: follow_user_id（被关注用户ID）

#### 7.2 取消关注
- **接口**: `DELETE /backend/front/follow`
- **认证**: 需要Token
- **参数**: follow_user_id（被关注用户ID）

#### 7.3 关注列表（我关注的人）
- **接口**: `GET /backend/front/following?page=1&limit=20`
- **认证**: 需要Token

#### 7.4 粉丝列表（关注我的人）
- **接口**: `GET /backend/front/followers?page=1&limit=20`
- **认证**: 需要Token

## 数据库表结构

### front_users 表（前台用户表）
- 基本信息：username、nickname、email、phone、avatar等
- 统计数据：article_count、comment_count、favorite_count等
- 积分等级：points、level
- 认证状态：email_verified、phone_verified
- VIP信息：is_vip、vip_expire_time
- 第三方登录：wechat_openid、qq_openid、weibo_uid

### 关联表
- user_favorites: 用户收藏表
- user_likes: 用户点赞表
- user_read_history: 用户阅读历史表
- user_point_logs: 用户积分日志表
- user_follows: 用户关注表

## 使用示例

### 前端使用示例（JavaScript）

```javascript
// 1. 用户注册
const register = async (username, password, email) => {
  const response = await fetch('http://localhost:8000/backend/front/auth/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password, email })
  });
  return await response.json();
};

// 2. 用户登录
const login = async (username, password) => {
  const response = await fetch('http://localhost:8000/backend/front/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
  });
  const data = await response.json();
  if (data.code === 200) {
    // 保存Token到localStorage
    localStorage.setItem('token', data.data.token);
    localStorage.setItem('user', JSON.stringify(data.data.user_info));
  }
  return data;
};

// 3. 获取用户信息（需要Token）
const getUserInfo = async () => {
  const token = localStorage.getItem('token');
  const response = await fetch('http://localhost:8000/backend/front/auth/info', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  return await response.json();
};

// 4. 收藏文章
const addFavorite = async (articleId) => {
  const token = localStorage.getItem('token');
  const response = await fetch('http://localhost:8000/backend/front/favorites', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({ article_id: articleId })
  });
  return await response.json();
};

// 5. 点赞文章
const likeArticle = async (articleId) => {
  const token = localStorage.getItem('token');
  const response = await fetch('http://localhost:8000/backend/front/likes', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`
    },
    body: JSON.stringify({
      target_type: 'article',
      target_id: articleId
    })
  });
  return await response.json();
};
```

## 注意事项

1. **Token认证**: 除了注册、登录、找回密码等公开接口，其他接口都需要在请求头中携带Token：
   ```
   Authorization: Bearer {token}
   ```

2. **Token类型识别**: 前台用户Token中包含 `type: "front_user"` 字段，用于区分后台管理员和前台用户。

3. **邮箱验证**: 目前邮箱验证功能仅返回Token，需要配置邮件服务才能真正发送邮件。

4. **密码安全**: 所有密码都使用 `password_hash()` 加密存储，不可逆。

5. **积分系统**: 积分变动会自动记录到 `user_point_logs` 表中。

6. **软删除**: 用户表支持软删除（deleted_at字段）。

7. **用户等级**: 系统预设了5个等级（新手、初级会员、中级会员、高级会员、VIP会员），可根据积分自动升级。

## 后续开发建议

1. **邮件服务**: 配置SMTP服务，实现真正的邮箱验证和密码重置邮件发送
2. **第三方登录**: 实现微信、QQ、微博等第三方登录
3. **用户投稿**: 允许前台用户发布文章
4. **评论系统**: 基于用户系统开发评论功能
5. **会员系统**: 实现VIP会员功能和权限管理
6. **积分商城**: 开发积分兑换功能
7. **等级系统**: 完善用户等级升级规则

## 测试记录

- ✅ 用户注册功能正常
- ✅ 用户登录功能正常
- ✅ Token认证功能正常
- ✅ 获取用户信息功能正常
- ✅ 积分奖励功能正常

**开发完成时间**: 2025-10-28
**开发状态**: 已完成并测试通过
