# 第三方登录(OAuth)功能使用指南

> CarefreeCMS OAuth登录系统完整文档

**版本**: v1.5.0
**最后更新**: 2025-11-08

---

## 📋 功能概述

第三方登录系统允许用户使用微信、QQ、微博、GitHub等第三方平台账号快速登录，无需注册即可使用系统功能。管理员可以在后台灵活配置各平台的OAuth参数。

### 核心特性

- ✅ **4大平台支持** - 微信、QQ、微博、GitHub
- ✅ **OAuth 2.0标准** - 完整的授权流程
- ✅ **自动账号创建** - 首次登录自动创建用户
- ✅ **账号绑定管理** - 支持绑定/解绑操作
- ✅ **安全防护** - State参数防CSRF攻击
- ✅ **配置管理** - 后台可视化配置界面
- ✅ **Token管理** - 自动刷新和过期检测

---

## 🚀 快速开始

### 1. 后台配置OAuth平台

登录后台 → 系统管理 → OAuth配置 (`/system/oauth-config`)

#### 1.1 微信登录配置

1. 访问 [微信开放平台](https://open.weixin.qq.com/)
2. 创建网站应用，获取AppID和AppSecret
3. 在后台填写配置：
   - **AppID**: 你的微信AppID
   - **AppSecret**: 你的微信AppSecret
   - **回调地址**: `https://your-domain.com/oauth/callback?platform=wechat`
   - **授权范围**: `snsapi_login`
4. 启用微信登录

#### 1.2 QQ登录配置

1. 访问 [QQ互联](https://connect.qq.com/)
2. 创建网站应用，获取AppID和AppKey
3. 在后台填写配置：
   - **AppID**: 你的QQ AppID
   - **AppSecret**: 你的QQ AppKey
   - **回调地址**: `https://your-domain.com/oauth/callback?platform=qq`
   - **授权范围**: `get_user_info`
4. 启用QQ登录

#### 1.3 微博登录配置

1. 访问 [微博开放平台](https://open.weibo.com/)
2. 创建网站应用，获取App Key和App Secret
3. 在后台填写配置：
   - **AppID**: 你的微博App Key
   - **AppSecret**: 你的微博App Secret
   - **回调地址**: `https://your-domain.com/oauth/callback?platform=weibo`
   - **授权范围**: `email`
4. 启用微博登录

#### 1.4 GitHub登录配置

1. 访问 [GitHub OAuth Apps](https://github.com/settings/developers)
2. 创建OAuth App，获取Client ID和Client Secret
3. 在后台填写配置：
   - **AppID**: 你的GitHub Client ID
   - **AppSecret**: 你的GitHub Client Secret
   - **回调地址**: `https://your-domain.com/oauth/callback?platform=github`
   - **授权范围**: `user:email`
4. 启用GitHub登录

---

## 💻 前端集成

### API接口说明

#### 1. 获取启用的平台列表

```javascript
import { getEnabledPlatforms } from '@/api/oauth'

// 获取已启用的OAuth平台
const platforms = await getEnabledPlatforms()
console.log(platforms.data) // [{ platform: 'wechat', platform_name: '微信登录', ... }]
```

#### 2. 获取授权登录URL

```javascript
import { getOAuthAuthUrl } from '@/api/oauth'

// 点击微信登录按钮
const handleWechatLogin = async () => {
  const res = await getOAuthAuthUrl('wechat')
  window.location.href = res.data.auth_url // 跳转到微信授权页面
}
```

#### 3. 处理OAuth回调

```javascript
import { oauthCallback } from '@/api/oauth'
import { useRouter, useRoute } from 'vue-router'

// 在回调页面
const route = useRoute()
const router = useRouter()

onMounted(async () => {
  const code = route.query.code
  const state = route.query.state

  if (code && state) {
    try {
      const res = await oauthCallback(code, state)

      // 保存token
      localStorage.setItem('token', res.data.token)

      // 跳转到首页
      router.push('/')
    } catch (error) {
      console.error('OAuth登录失败:', error)
    }
  }
})
```

#### 4. 账号绑定管理

```javascript
import {
  getUserOAuthBindings,
  bindOAuthAccount,
  unbindOAuthAccount
} from '@/api/oauth'

// 获取用户绑定列表
const bindings = await getUserOAuthBindings()
console.log(bindings.data)

// 绑定微信账号
const handleBind = async (code) => {
  await bindOAuthAccount('wechat', code)
}

// 解绑微信账号
const handleUnbind = async () => {
  await unbindOAuthAccount('wechat')
}
```

---

## 🎨 前端登录页面示例

```vue
<template>
  <div class="login-page">
    <el-card class="login-card">
      <h2>用户登录</h2>

      <!-- 传统登录表单 -->
      <el-form :model="form">
        <el-form-item>
          <el-input v-model="form.username" placeholder="用户名" />
        </el-form-item>
        <el-form-item>
          <el-input v-model="form.password" type="password" placeholder="密码" />
        </el-form-item>
        <el-button type="primary" @click="handleLogin">登录</el-button>
      </el-form>

      <!-- OAuth第三方登录 -->
      <div class="oauth-login" v-if="oauthPlatforms.length > 0">
        <el-divider>第三方登录</el-divider>
        <div class="oauth-buttons">
          <el-button
            v-for="platform in oauthPlatforms"
            :key="platform.platform"
            @click="handleOAuthLogin(platform.platform)">
            {{ platform.platform_name }}
          </el-button>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { getEnabledPlatforms, getOAuthAuthUrl } from '@/api/oauth'

const oauthPlatforms = ref([])

const loadOAuthPlatforms = async () => {
  try {
    const res = await getEnabledPlatforms()
    oauthPlatforms.value = res.data
  } catch (error) {
    console.error('获取OAuth平台失败', error)
  }
}

const handleOAuthLogin = async (platform) => {
  try {
    const res = await getOAuthAuthUrl(platform)
    window.location.href = res.data.auth_url
  } catch (error) {
    console.error('获取授权URL失败', error)
  }
}

onMounted(() => {
  loadOAuthPlatforms()
})
</script>
```

---

## 🔐 后端API文档

### 公开接口（无需认证）

#### 1. 获取启用的平台列表

**接口**: `GET /api/oauth/enabled-platforms`

**响应示例**:
```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "platform": "wechat",
      "platform_name": "微信登录",
      "sort_order": 1
    }
  ]
}
```

#### 2. 获取授权URL

**接口**: `GET /api/oauth/auth-url?platform=wechat`

**响应示例**:
```json
{
  "code": 0,
  "message": "success",
  "data": {
    "auth_url": "https://open.weixin.qq.com/connect/qrconnect?appid=xxx...",
    "state": "abc123def456"
  }
}
```

#### 3. OAuth回调处理

**接口**: `GET /api/oauth/callback?code=xxx&state=xxx`

**响应示例**:
```json
{
  "code": 0,
  "message": "登录成功",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": 1,
      "username": "wechat_1234567890",
      "nickname": "微信用户",
      "avatar": "https://..."
    }
  }
}
```

### 需要认证的接口

#### 4. 获取用户绑定列表

**接口**: `GET /api/oauth/user-bindings`

**响应示例**:
```json
{
  "code": 0,
  "message": "success",
  "data": [
    {
      "platform": "wechat",
      "platform_name": "微信登录",
      "is_bound": true,
      "binding_info": {
        "nickname": "微信用户",
        "avatar": "https://...",
        "bind_time": "2025-11-08 12:00:00",
        "last_login_time": "2025-11-08 13:00:00",
        "login_count": 5
      }
    }
  ]
}
```

#### 5. 绑定第三方账号

**接口**: `POST /api/oauth/bind`

**请求体**:
```json
{
  "platform": "wechat",
  "code": "授权码"
}
```

#### 6. 解绑第三方账号

**接口**: `POST /api/oauth/unbind`

**请求体**:
```json
{
  "platform": "wechat"
}
```

---

## 🛡️ 安全说明

### 1. State参数

系统使用State参数防止CSRF攻击：
- 每次授权请求生成唯一state
- State缓存10分钟自动过期
- 回调时验证state有效性

### 2. Token管理

- Access Token自动存储和刷新
- Token过期时间自动计算
- 支持Refresh Token机制

### 3. 配置安全

- AppSecret在列表页自动隐藏（显示为****）
- 编辑时不修改密钥可留空
- 支持测试配置完整性

---

## 📊 数据库表结构

### oauth_configs（OAuth配置表）

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| platform | varchar(50) | 平台标识（唯一） |
| platform_name | varchar(100) | 平台名称 |
| app_id | varchar(255) | 应用ID |
| app_secret | varchar(255) | 应用密钥 |
| redirect_uri | varchar(500) | 回调地址 |
| scope | varchar(500) | 授权范围 |
| is_enabled | tinyint | 启用状态 |
| sort_order | int | 排序权重 |

### front_user_oauth（用户绑定表）

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 主键 |
| user_id | int | 用户ID |
| platform | varchar(50) | 平台标识 |
| openid | varchar(255) | 第三方OpenID（唯一） |
| unionid | varchar(255) | UnionID（微信） |
| nickname | varchar(100) | 第三方昵称 |
| avatar | varchar(500) | 第三方头像 |
| access_token | varchar(500) | 访问令牌 |
| refresh_token | varchar(500) | 刷新令牌 |
| login_count | int | 登录次数 |
| status | tinyint | 绑定状态 |

---

## 🔧 故障排除

### 问题1: 授权后跳转失败

**症状**: 点击OAuth登录后无法跳转或报错

**排查**:
1. 检查redirect_uri是否正确配置
2. 检查回调地址是否添加到第三方平台白名单
3. 查看浏览器控制台错误信息

### 问题2: State参数无效

**症状**: 回调时提示"state参数无效或已过期"

**排查**:
1. 检查Redis缓存是否正常运行
2. State缓存时间为10分钟，超时需重新授权
3. 检查服务器时间是否正确

### 问题3: 获取用户信息失败

**症状**: Access Token获取成功，但无法获取用户信息

**排查**:
1. 检查授权scope是否包含必要权限
2. 检查第三方平台API是否正常
3. 查看后端错误日志

---

## 📝 最佳实践

### 1. 配置管理

- 生产环境和测试环境使用不同的AppID
- 定期检查AppSecret是否泄露
- 建议将配置存储在环境变量中

### 2. 用户体验

- 首次OAuth登录提示用户绑定手机号/邮箱
- 提供账号解绑功能入口
- 显示用户当前绑定的第三方账号

### 3. 安全建议

- 启用HTTPS避免Token泄露
- 限制OAuth回调域名白名单
- 定期清理过期Token

---

## 📞 技术支持

- **文档**: [docs/OAUTH_GUIDE.md](./OAUTH_GUIDE.md)
- **问题反馈**: https://gitee.com/carefreeteam/issues
- **邮箱**: sinma@qq.com
- **QQ群**: 113572201

---

**Made with ❤️ by CarefreeCMS Team © 2025**
