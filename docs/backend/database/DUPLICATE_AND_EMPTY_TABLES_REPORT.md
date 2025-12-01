# 数据库重复表和空表分析报告

## 一、重复/功能重叠的表

### 1. OAuth配置表（重复）⚠️

| 表名 | 行数 | 大小 | 状态 | 建议 |
|------|------|------|------|------|
| `oauth_config` | 0 | 32 KB | 空表，结构较旧 | **删除** |
| `oauth_configs` | 4 | - | 正在使用 | **保留** |

**分析**：
- 两个表功能完全相同，都是存储OAuth配置
- `oauth_configs` 字段更完善（有 platform_name, extra_config 等）
- `oauth_config` 是空表，疑似废弃版本

**结论**：保留 `oauth_configs`，删除 `oauth_config`

---

### 2. 模板管理表（部分重复）⚠️

| 表名 | 行数 | 大小 | 用途 | 建议 |
|------|------|------|------|------|
| `template` | 0 | 48 KB | 未知，空表 | **删除** |
| `templates` | 7 | - | 模板主表 | **保留** |
| `template_history` | 3 | - | 模板历史版本 | **保留** |

**分析**：
- `template` 单数形式，完全空表，疑似早期废弃版本
- `templates` 和 `template_history` 是配套使用的正常表

**结论**：删除 `template`，保留 `templates` 和 `template_history`

---

### 3. 通知表（重复）⚠️

| 表名 | 行数 | 大小 | 用途 | 建议 |
|------|------|------|------|------|
| `notifications` | 10 | - | 通知主表 | **保留** |
| `user_notifications` | 0 | 80 KB | 用户通知（空表） | **删除或合并** |
| `user_notification_settings` | 0 | 32 KB | 用户通知设置（空表） | **删除** |

**分析**：
- `notifications` 已包含 `user_id` 字段，可以存储用户通知
- `user_notifications` 是空表，功能与 `notifications` 重复
- `user_notification_settings` 空表，功能未使用

**结论**：
- 保留 `notifications`
- 删除 `user_notifications` 和 `user_notification_settings`

---

### 4. 模板类型表（功能可合并）📋

| 表名 | 行数 | 功能 |
|------|------|------|
| `email_templates` | 0 | 邮件模板（空） |
| `sms_templates` | 0 | 短信模板（空） |
| `notification_templates` | 7 | 通知模板（使用中） |

**分析**：
- 三个表结构相似，功能重叠
- 只有 `notification_templates` 在使用
- 可以合并为一个统一的模板表，通过 `type` 字段区分

**结论**：
- **短期**：删除 `email_templates` 和 `sms_templates`（空表）
- **长期**：考虑重命名 `notification_templates` 为 `templates_unified`

---

## 二、完全空表（30张）

### A. 功能未启用的表（可安全删除）✅

| 表名 | 大小 | 功能说明 | 删除影响 |
|------|------|----------|----------|
| `admin_logs` | 48 KB | 管理员日志 | 无（有 operation_logs） |
| `admin_users` | 64 KB | 管理员用户 | ⚠️ 核心表，不能删 |
| `email_logs` | 64 KB | 邮件发送日志 | 无影响 |
| `email_templates` | 32 KB | 邮件模板 | 无影响 |
| `sms_config` | 16 KB | 短信配置 | 无影响 |
| `sms_templates` | 32 KB | 短信模板 | 无影响 |
| `security_logs` | 80 KB | 安全日志 | 无（可用 logs） |
| `ip_blacklist` | 64 KB | IP黑名单 | 功能未用 |
| `ip_whitelist` | 48 KB | IP白名单 | 功能未用 |
| `content_violations` | 80 KB | 内容违规 | 功能未用 |
| `member_level_logs` | 48 KB | 会员等级日志 | 功能未用 |

**建议删除**：9张（保留 admin_users 和 admin_logs）

---

### B. 功能相关的空表（需保留）🔒

| 表名 | 大小 | 功能说明 | 是否保留 |
|------|------|----------|----------|
| `ad_clicks` | 48 KB | 广告点击统计 | ✅ 保留（未来可用） |
| `comment_likes` | 96 KB | 评论点赞 | ✅ 保留（核心功能） |
| `comment_reports` | 80 KB | 评论举报 | ✅ 保留（核心功能） |
| `front_user_oauth` | 80 KB | 用户OAuth | ✅ 保留（核心功能） |
| `custom_field_values` | 48 KB | 自定义字段值 | ✅ 保留（核心功能） |
| `cron_job_logs` | 64 KB | 定时任务日志 | ✅ 保留（核心功能） |
| `point_shop_goods` | 48 KB | 积分商品 | ✅ 保留（业务功能） |
| `point_shop_orders` | 96 KB | 积分订单 | ✅ 保留（业务功能） |
| `seo_404_logs` | 80 KB | 404日志 | ✅ 保留（SEO功能） |
| `seo_keyword_rankings` | 64 KB | 关键词排名 | ✅ 保留（SEO功能） |

**建议**：保留，这些是核心业务功能表

---

### C. 用户行为相关空表（需保留）🔒

| 表名 | 大小 | 功能说明 |
|------|------|----------|
| `user_favorites` | 80 KB | 用户收藏 |
| `user_follows` | 64 KB | 用户关注 |
| `user_likes` | 64 KB | 用户点赞 |
| `user_read_history` | 80 KB | 阅读历史 |
| `user_reputation` | 48 KB | 用户声望 |

**建议**：保留，这些是用户功能核心表

---

### D. 废弃/重复的空表（可删除）❌

| 表名 | 大小 | 原因 |
|------|------|------|
| `oauth_config` | 32 KB | 重复（有 oauth_configs） |
| `template` | 48 KB | 重复（有 templates） |
| `user_notifications` | 80 KB | 重复（有 notifications） |
| `user_notification_settings` | 32 KB | 功能未用 |

---

## 三、汇总统计

### 可安全删除的表（15张）

#### 重复表（4张）
1. `oauth_config` - 已有 oauth_configs
2. `template` - 已有 templates
3. `user_notifications` - 已有 notifications
4. `user_notification_settings` - 功能未使用

#### 功能未启用的空表（9张）
5. `email_logs`
6. `email_templates`
7. `sms_config`
8. `sms_templates`
9. `security_logs`
10. `ip_blacklist`
11. `ip_whitelist`
12. `content_violations`
13. `member_level_logs`

#### 特殊情况（2张）
14. `admin_logs` - 可选删除（已有 operation_logs）
15. `admin_users` - **不能删除**（但是空的，需要初始化）

---

### 空表但需保留（16张）

**核心功能表**：
- comment_likes, comment_reports
- front_user_oauth
- custom_field_values
- cron_job_logs

**业务功能表**：
- ad_clicks
- point_shop_goods, point_shop_orders
- seo_404_logs, seo_keyword_rankings

**用户功能表**：
- user_favorites, user_follows, user_likes
- user_read_history, user_reputation

---

## 四、删除建议优先级

### 🟢 优先级1（无风险，可立即删除）

```sql
-- 重复表
DROP TABLE IF EXISTS `oauth_config`;
DROP TABLE IF EXISTS `template`;
DROP TABLE IF EXISTS `user_notifications`;
DROP TABLE IF EXISTS `user_notification_settings`;

-- 未使用的功能表
DROP TABLE IF EXISTS `email_logs`;
DROP TABLE IF EXISTS `email_templates`;
DROP TABLE IF EXISTS `sms_config`;
DROP TABLE IF EXISTS `sms_templates`;
```

**影响**：无影响，这些表完全重复或未使用

---

### 🟡 优先级2（低风险，建议删除）

```sql
-- 功能未启用的表
DROP TABLE IF EXISTS `ip_blacklist`;
DROP TABLE IF EXISTS `ip_whitelist`;
DROP TABLE IF EXISTS `content_violations`;
DROP TABLE IF EXISTS `member_level_logs`;
DROP TABLE IF EXISTS `security_logs`;
```

**影响**：删除后相关功能将无法使用，但当前这些功能未开启

---

### 🔴 优先级3（需评估）

```sql
-- 管理员日志（已有 operation_logs）
DROP TABLE IF EXISTS `admin_logs`;
```

**影响**：需要确认 `operation_logs` 是否完全覆盖了 `admin_logs` 的功能

---

## 五、执行建议

### 方案A：保守方案（删除8张表）
只删除明确重复和完全未使用的表
```
- 重复表：4张
- 邮件/短信相关：4张
```
**节省空间**：约 256 KB（数据库层面）

### 方案B：推荐方案（删除13张表）
删除重复表和功能未启用的表
```
- 重复表：4张
- 未使用功能：9张
```
**节省空间**：约 544 KB

### 方案C：激进方案（删除14张表）
包括 admin_logs
```
- 重复表：4张
- 未使用功能：10张
```
**节省空间**：约 592 KB

---

## 六、特别提醒

### ⚠️ admin_users 表为空但不能删除
这是管理员用户核心表，当前为空说明：
1. 可能是初始化未完成
2. 管理员数据在其他表中

**需要检查**：
- 是否有其他管理员用户表
- 系统登录使用的是哪个用户表

### ⚠️ 删除前的准备工作
1. **备份数据库**
2. 检查代码中是否有引用这些表
3. 在测试环境先执行
4. 确认无影响后再到生产环境

---

## 七、执行SQL脚本

### 方案B（推荐）删除脚本

```sql
-- 备份提醒
-- 请先执行: mysqldump -u root -p cms_database > backup_before_delete_$(date +%Y%m%d).sql

-- 开始删除
USE cms_database;

-- 1. 删除重复表
DROP TABLE IF EXISTS `oauth_config`;
DROP TABLE IF EXISTS `template`;
DROP TABLE IF EXISTS `user_notifications`;
DROP TABLE IF EXISTS `user_notification_settings`;

-- 2. 删除邮件/短信相关空表
DROP TABLE IF EXISTS `email_logs`;
DROP TABLE IF EXISTS `email_templates`;
DROP TABLE IF EXISTS `sms_config`;
DROP TABLE IF EXISTS `sms_templates`;

-- 3. 删除未使用功能表
DROP TABLE IF EXISTS `ip_blacklist`;
DROP TABLE IF EXISTS `ip_whitelist`;
DROP TABLE IF EXISTS `content_violations`;
DROP TABLE IF EXISTS `member_level_logs`;
DROP TABLE IF EXISTS `security_logs`;

-- 验证
SHOW TABLES;
```
