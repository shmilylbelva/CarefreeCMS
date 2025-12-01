# 数据库表结构优化方案

## 当前问题分析

当前系统有 **74 张基础表**（不含站点独立表），存在以下问题：
1. 功能相似的表分散存储
2. 多对多关联表过多
3. 分组/分类表结构重复
4. 日志表分散，不便于统一管理

## 优化方案

### 一、关联表合并（减少 3 张表）

#### 当前状态
```
article_categories  - 文章分类关联
article_tags        - 文章标签关联
topic_articles      - 专题文章关联
```

#### 优化后：`relations` 表（通用多对多关联表）
```sql
CREATE TABLE `relations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `source_type` varchar(50) NOT NULL COMMENT '源类型：article, topic',
  `source_id` int unsigned NOT NULL COMMENT '源ID',
  `target_type` varchar(50) NOT NULL COMMENT '目标类型：category, tag, article',
  `target_id` int unsigned NOT NULL COMMENT '目标ID',
  `relation_type` varchar(20) DEFAULT NULL COMMENT '关联类型：main, sub',
  `sort` int DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_source` (`source_type`, `source_id`),
  KEY `idx_target` (`target_type`, `target_id`)
) COMMENT='通用关联表';
```

**减少：3 张表 → 1 张表**

---

### 二、分组/分类表合并（减少 3 张表）

#### 当前状态
```
link_groups           - 友情链接分组
slider_groups         - 幻灯片分组
point_shop_categories - 积分商品分类
ad_positions          - 广告位（本质也是分组）
```

#### 优化后：`groups` 表（通用分组表）
```sql
CREATE TABLE `groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL COMMENT '类型：link, slider, point_shop, ad',
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `parent_id` int unsigned DEFAULT NULL,
  `description` text,
  `sort` int DEFAULT 0,
  `status` tinyint DEFAULT 1,
  `config` json COMMENT '扩展配置',
  `site_id` int unsigned DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_site` (`site_id`)
) COMMENT='通用分组表';
```

**减少：4 张表 → 1 张表**

---

### 三、用户行为表合并（减少 4 张表）

#### 当前状态
```
comment_likes     - 评论点赞/踩
user_likes        - 文章点赞
user_favorites    - 用户收藏
user_follows      - 用户关注
```

#### 优化后：`user_actions` 表（通用用户行为表）
```sql
CREATE TABLE `user_actions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `target_type` varchar(50) NOT NULL COMMENT '目标类型：article, comment, user',
  `target_id` int unsigned NOT NULL COMMENT '目标ID',
  `action_type` varchar(20) NOT NULL COMMENT '行为类型：like, dislike, favorite, follow',
  `site_id` int unsigned DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_action` (`user_id`, `target_type`, `target_id`, `action_type`),
  KEY `idx_target` (`target_type`, `target_id`),
  KEY `idx_user` (`user_id`)
) COMMENT='用户行为表';
```

**减少：4 张表 → 1 张表**

---

### 四、日志表合并（减少 6 张表）

#### 当前状态
```
admin_logs        - 管理员日志
login_logs        - 登录日志
operation_logs    - 操作日志
security_logs     - 安全日志
system_logs       - 系统日志
seo_404_logs      - 404日志
```

#### 优化后：`logs` 表（统一日志表）
```sql
CREATE TABLE `logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL COMMENT '日志类型：admin, login, operation, security, system, seo_404',
  `level` varchar(20) DEFAULT 'info' COMMENT 'debug, info, warning, error',
  `user_id` int unsigned DEFAULT NULL,
  `user_type` varchar(20) DEFAULT NULL COMMENT 'admin, front_user',
  `action` varchar(100) DEFAULT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int unsigned DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `request_url` varchar(500) DEFAULT NULL,
  `message` text,
  `data` json COMMENT '详细数据',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`),
  KEY `idx_level` (`level`)
) COMMENT='统一日志表';
```

**减少：6 张表 → 1 张表**

---

### 五、OAuth配置合并（减少 1 张表）

#### 当前状态
```
oauth_config   - OAuth配置（疑似废弃）
oauth_configs  - OAuth配置
```

#### 优化：删除重复表
直接删除 `oauth_config`，保留 `oauth_configs`

**减少：2 张表 → 1 张表**

---

### 六、模板表优化（减少 2 张表）

#### 当前状态
```
template          - 模板（疑似废弃）
templates         - 模板列表
template_history  - 模板历史
```

#### 优化方案 1：合并到 templates
在 `templates` 表中添加 `version` 和 `is_current` 字段，通过版本控制管理历史

#### 优化方案 2：保留独立历史表
保留 `templates` 和 `template_history`，删除 `template`

**推荐：方案 2，减少：3 张表 → 2 张表**

---

### 七、通知表优化（减少 1 张表）

#### 当前状态
```
notifications              - 通知记录
user_notifications         - 用户通知
notification_templates     - 通知模板
```

#### 优化后：合并 notifications 和 user_notifications
```sql
CREATE TABLE `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned DEFAULT NULL COMMENT 'NULL=系统通知',
  `template_id` int unsigned DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text,
  `data` json,
  `is_read` tinyint DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `is_global` tinyint DEFAULT 0 COMMENT '是否全局通知',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_read` (`is_read`)
) COMMENT='通知表';
```

**减少：2 张表 → 1 张表**

---

### 八、其他优化建议

#### 1. 删除空表/未使用的表
```
ad_clicks              - 0 行（如不需要点击统计可删除）
email_logs             - 0 行
email_templates        - 0 行（可以用 notification_templates 替代）
sms_config             - 0 行
sms_templates          - 0 行
content_violations     - 0 行
member_level_logs      - 0 行
user_notification_settings - 0 行
user_reputation        - 0 行
```

#### 2. 文章属性优化
`article_flags` 可以改为 JSON 字段存储在 articles 表中

#### 3. 文章版本可选
`article_versions` 如果不常用，可以考虑删除或改为按需开启

---

## 优化效果总结

### 表数量变化
```
优化前：74 张表
优化后：54 张表（最激进方案）
        或 60 张表（保守方案）

减少：14-20 张表 (19%-27%)
```

### 具体减少
1. 关联表：3 → 1（减少 2 张）
2. 分组表：4 → 1（减少 3 张）
3. 用户行为表：4 → 1（减少 3 张）
4. 日志表：6 → 1（减少 5 张）
5. OAuth：2 → 1（减少 1 张）
6. 模板：3 → 2（减少 1 张）
7. 通知：2 → 1（减少 1 张）
8. 删除空表：约 8-10 张

### 优势
✅ **减少表数量**：便于维护和管理
✅ **统一数据结构**：相似功能使用相同表结构
✅ **降低复杂度**：减少 JOIN 操作
✅ **提高扩展性**：新增类型只需加配置，不需建表
✅ **便于备份**：表少了备份更快
✅ **减少站点表**：独立表模式下每个站点少创建 10+ 张表

### 注意事项
⚠️ **数据迁移**：需要编写数据迁移脚本
⚠️ **代码改动**：需要修改相关模型和服务
⚠️ **索引优化**：合并后的表需要合理设计索引
⚠️ **查询性能**：需要测试合并后的查询性能

---

## 实施建议

### 第一阶段（低风险）
1. 删除空表和废弃表
2. 合并 OAuth 配置
3. 优化模板表

### 第二阶段（中风险）
1. 合并关联表
2. 合并分组表
3. 合并通知表

### 第三阶段（高风险）
1. 合并用户行为表
2. 合并日志表

---

## 实施工具

建议使用数据库迁移工具分步实施：
1. 创建新表
2. 迁移数据
3. 验证数据完整性
4. 更新代码
5. 删除旧表
