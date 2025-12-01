# CMS 系统数据库设计文档

## 📚 目录

- [数据库架构](#数据库架构)
- [表设计详解](#表设计详解)
- [字段说明](#字段说明)
- [关系设计](#关系设计)
- [索引优化](#索引优化)
- [数据规范](#数据规范)

---

## 数据库架构

### 数据库设计原则

1. **规范化**：遵循第三范式（3NF）
2. **可扩展性**：预留字段和表空间
3. **性能**：合理使用索引和分区
4. **安全性**：字段加密、访问控制
5. **可维护性**：清晰的命名和注释

### 数据库总体结构

```
cms_database
├── 用户认证模块
│   ├── admin_users      (管理员用户)
│   ├── admin_roles      (角色)
│   └── admin_permissions (权限)
├── 内容管理模块
│   ├── articles         (文章)
│   ├── categories       (分类)
│   ├── tags            (标签)
│   ├── pages           (单页)
│   ├── comments        (评论)
│   └── article_tags    (文章标签关联)
├── 媒体管理模块
│   ├── media           (媒体库)
│   └── media_groups    (媒体分组)
├── SEO 模块
│   ├── redirect        (重定向规则)
│   └── sitemap_log     (网站地图日志)
└── 系统模块
    ├── site_config     (网站配置)
    ├── templates       (模板)
    ├── admin_logs      (操作日志)
    └── system_jobs     (系统任务)
```

---

## 表设计详解

### 1. 用户表 (admin_users)

#### 表结构

| 字段 | 类型 | 长度 | 默认值 | 说明 |
|------|------|------|--------|------|
| id | int | - | AUTO_INCREMENT | 用户 ID |
| username | varchar | 50 | - | 用户名（唯一） |
| password | varchar | 255 | - | 密码（bcrypt 哈希） |
| real_name | varchar | 50 | NULL | 真实姓名 |
| email | varchar | 100 | NULL | 邮箱（唯一） |
| phone | varchar | 20 | NULL | 手机号 |
| avatar | varchar | 255 | NULL | 头像 URL |
| role_id | int | - | 3 | 角色 ID |
| status | tinyint | - | 1 | 状态 (0=禁用, 1=启用) |
| last_login_time | datetime | - | NULL | 最后登录时间 |
| last_login_ip | varchar | 50 | NULL | 最后登录 IP |
| create_time | datetime | - | CURRENT_TIMESTAMP | 创建时间 |
| update_time | datetime | - | CURRENT_TIMESTAMP | 更新时间 |
| deleted_at | datetime | - | NULL | 删除时间（软删除） |

#### 索引

```sql
PRIMARY KEY (id)
UNIQUE KEY uk_username (username)
UNIQUE KEY uk_email (email)
KEY idx_role_id (role_id)
KEY idx_status (status)
```

#### SQL

```sql
CREATE TABLE `admin_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码（bcrypt哈希）',
  `real_name` varchar(50) DEFAULT NULL COMMENT '真实姓名',
  `email` varchar(100) DEFAULT NULL COMMENT '邮箱',
  `phone` varchar(20) DEFAULT NULL COMMENT '手机号',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像URL',
  `role_id` int unsigned NOT NULL DEFAULT '3' COMMENT '角色ID',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '状态：0=禁用，1=启用',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT NULL COMMENT '最后登录IP',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime DEFAULT NULL COMMENT '删除时间（软删除）',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_username` (`username`),
  UNIQUE KEY `uk_email` (`email`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='管理员用户表';
```

### 2. 文章表 (articles)

#### 表结构

| 字段 | 类型 | 长度 | 说明 |
|------|------|------|------|
| id | int | - | 文章 ID |
| category_id | int | - | 分类 ID |
| user_id | int | - | 作者 ID |
| title | varchar | 200 | 文章标题 |
| slug | varchar | 200 | URL 别名 |
| summary | varchar | 500 | 文章摘要 |
| content | longtext | - | 文章内容（HTML） |
| cover_image | varchar | 255 | 封面图片 URL |
| images | json | - | 文章图片集（JSON 数组） |
| author | varchar | 50 | 显示作者名（可能与 user_id 不同） |
| source | varchar | 100 | 文章来源 |
| source_url | varchar | 255 | 来源链接 |
| view_count | int | - | 浏览次数 |
| like_count | int | - | 点赞数 |
| comment_count | int | - | 评论数 |
| is_top | tinyint | - | 是否置顶 |
| is_recommend | tinyint | - | 是否推荐 |
| is_hot | tinyint | - | 是否热门 |
| flags | json | - | 文章属性标签（JSON） |
| publish_time | datetime | - | 发布时间 |
| seo_title | varchar | 100 | SEO 标题 |
| seo_keywords | varchar | 255 | SEO 关键词 |
| seo_description | varchar | 500 | SEO 描述 |
| sort | int | - | 排序权重 |
| status | tinyint | - | 状态（0=草稿, 1=已发布等） |
| create_time | datetime | - | 创建时间 |
| update_time | datetime | - | 更新时间 |
| deleted_at | datetime | - | 删除时间 |

#### 字段值说明

**status 字段**：
- `0` 草稿
- `1` 已发布
- `2` 待审核
- `3` 已下线

**flags 字段示例**：
```json
{
  "original": 1,           // 是否原创
  "reprinted": 0,          // 是否转载
  "sticky": 0,             // 是否置顶
  "comment_allowed": 1     // 是否允许评论
}
```

#### 索引

```sql
PRIMARY KEY (id)
UNIQUE KEY uk_slug (slug)
KEY idx_category_id (category_id)
KEY idx_user_id (user_id)
KEY idx_status_publish (status, publish_time)
KEY idx_is_top (is_top)
KEY idx_is_recommend (is_recommend)
FULLTEXT KEY ft_title_content (title, content)
```

### 3. 分类表 (categories)

#### 表结构

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 分类 ID |
| parent_id | int | 父分类 ID（0 为顶级） |
| name | varchar | 分类名称 |
| slug | varchar | URL 别名 |
| description | text | 分类描述 |
| cover_image | varchar | 分类封面 |
| seo_title | varchar | SEO 标题 |
| seo_keywords | varchar | SEO 关键词 |
| seo_description | varchar | SEO 描述 |
| sort | int | 排序权重 |
| status | tinyint | 状态 |
| create_time | datetime | 创建时间 |
| update_time | datetime | 更新时间 |

#### 层级关系示例

```
根分类
├── 技术 (id=1)
│   ├── PHP (id=2, parent_id=1)
│   ├── JavaScript (id=3, parent_id=1)
│   └── Python (id=4, parent_id=1)
├── 生活 (id=5)
│   ├── 美食 (id=6, parent_id=5)
│   └── 旅游 (id=7, parent_id=5)
```

### 4. 标签表 (tags)

#### 表结构

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 标签 ID |
| name | varchar | 标签名称 |
| slug | varchar | URL 别名 |
| description | varchar | 标签描述 |
| article_count | int | 关联文章数 |
| sort | int | 排序权重 |
| status | tinyint | 状态 |
| create_time | datetime | 创建时间 |
| update_time | datetime | 更新时间 |

### 5. 文章标签关联表 (article_tags)

#### 表结构

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 关联 ID |
| article_id | int | 文章 ID |
| tag_id | int | 标签 ID |
| create_time | datetime | 关联时间 |

#### 关系示例

```
article_tags 表：
article_id  tag_id  create_time
1           1       2024-01-01 10:00
1           2       2024-01-01 10:00
2           2       2024-01-02 10:00
2           3       2024-01-02 10:00

表示：
- 文章 1 拥有标签 1, 2
- 文章 2 拥有标签 2, 3
```

### 6. 评论表 (comments)

#### 表结构

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 评论 ID |
| article_id | int | 文章 ID |
| parent_id | int | 父评论 ID |
| user_name | varchar | 评论者名称 |
| user_email | varchar | 评论者邮箱 |
| user_ip | varchar | 评论者 IP |
| content | text | 评论内容 |
| like_count | int | 点赞数 |
| is_admin | tinyint | 是否管理员评论 |
| status | tinyint | 状态（0=待审, 1=通过, 2=拒绝） |
| create_time | datetime | 创建时间 |
| update_time | datetime | 更新时间 |

#### 嵌套评论示例

```
评论 1 (parent_id=0)          - 顶级评论
├── 评论 2 (parent_id=1)      - 回复评论 1
├── 评论 3 (parent_id=1)      - 回复评论 1
│   └── 评论 4 (parent_id=3)  - 回复评论 3
```

### 7. 媒体表 (media)

#### 表结构

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 媒体 ID |
| user_id | int | 上传者 ID |
| file_name | varchar | 原始文件名 |
| file_path | varchar | 服务器存储路径 |
| file_url | varchar | 外部访问 URL |
| file_type | varchar | 文件类型（image/video/audio/document） |
| mime_type | varchar | MIME 类型 |
| file_size | bigint | 文件大小（字节） |
| width | int | 图片/视频宽度（像素） |
| height | int | 图片/视频高度（像素） |
| storage_type | varchar | 存储方式（local/qiniu/aliyun） |
| create_time | datetime | 创建时间 |

### 8. 网站配置表 (site_config)

#### 表结构

| 字段 | 类型 | 说明 |
|------|------|------|
| id | int | 配置 ID |
| config_key | varchar | 配置键（唯一） |
| config_value | text | 配置值 |
| config_type | varchar | 配置类型（text/number/json/image） |
| group_name | varchar | 分组名称 |
| description | varchar | 配置描述 |
| sort | int | 排序 |
| update_time | datetime | 更新时间 |

#### 配置示例

| config_key | config_value | config_type | group_name |
|---|---|---|---|
| site_name | CMS 内容管理系统 | text | basic |
| site_logo | /upload/logo.png | image | basic |
| site_keywords | CMS, 内容管理 | text | seo |
| upload_max_size | 10 | number | upload |

### 9. 操作日志表 (admin_logs)

#### 表结构

| 字段 | 类型 | 说明 |
|------|------|------|
| id | bigint | 日志 ID |
| user_id | int | 操作用户 ID |
| username | varchar | 操作用户名 |
| action | varchar | 操作动作（create/update/delete等） |
| module | varchar | 操作模块（article/category等） |
| description | varchar | 操作描述 |
| ip | varchar | 操作者 IP |
| user_agent | varchar | 用户代理 |
| create_time | datetime | 操作时间 |

#### 示例记录

```
user_id  username  action  module      description
1        admin     create  article     创建文章 "Hello World"
1        admin     update  article     更新文章 ID 1
2        editor    delete  article     删除文章 ID 2
```

---

## 关系设计

### E-R 图

```
admin_users (用户)
    ├── 1:N --> admin_roles (多个用户可有一个角色)
    ├── 1:N --> articles (用户发布多篇文章)
    ├── 1:N --> comments (用户可发表多条评论)
    └── 1:N --> media (用户可上传多个媒体)

categories (分类)
    ├── 1:N --> articles (分类包含多篇文章)
    └── 1:N --> categories (自关联：分类可有子分类)

tags (标签)
    └── N:N --> articles (标签和文章多对多关系)
         通过 article_tags 表关联

articles (文章)
    ├── 1:N --> comments (文章有多条评论)
    ├── N:1 --> admin_users (文章属于一个用户)
    ├── N:1 --> categories (文章属于一个分类)
    └── N:N --> tags (文章可有多个标签)
```

### 关键关系查询

**获取文章及其关联数据**：

```sql
SELECT
    a.*,
    c.name AS category_name,
    u.username AS author_username,
    GROUP_CONCAT(t.name) AS tag_names
FROM articles a
LEFT JOIN categories c ON a.category_id = c.id
LEFT JOIN admin_users u ON a.user_id = u.id
LEFT JOIN article_tags at ON a.id = at.article_id
LEFT JOIN tags t ON at.tag_id = t.id
WHERE a.id = 1
GROUP BY a.id;
```

---

## 数据规范

### 字符编码

所有表使用 `utf8mb4` 字符集，支持表情符号：

```sql
CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

### 时间字段规范

- 所有时间字段使用 `datetime` 类型
- 格式：`YYYY-MM-DD HH:MM:SS`
- 服务器时区：Asia/Shanghai (UTC+8)
- 时间戳操作：使用 `CURRENT_TIMESTAMP`

### JSON 字段规范

MySQL 5.7+ 支持原生 JSON 类型，用于存储结构化数据：

**images 字段示例**：
```json
[
  {"url": "https://example.com/img1.jpg", "title": "标题1"},
  {"url": "https://example.com/img2.jpg", "title": "标题2"}
]
```

**flags 字段示例**：
```json
{
  "original": 1,
  "featured": 0,
  "allow_comment": 1
}
```

### 命名规范

| 类型 | 规范 | 示例 |
|------|------|------|
| 表名 | 小写 + 下划线 | admin_users |
| 字段名 | 小写 + 下划线 | last_login_time |
| 索引名 | 前缀 + 字段名 | idx_user_id, uk_username |
| 主键 | id | id |
| 外键 | 表名_id | user_id, article_id |
| 时间戳 | _time | create_time, update_time |
| 删除标记 | deleted_at | deleted_at |

### 数据验证规则

| 字段 | 验证规则 |
|------|---------|
| email | 符合 RFC 5322 |
| username | 3-50 字符，字母数字下划线 |
| password | 至少 8 字符，bcrypt 哈希 |
| status | 0 或 1 |
| tinyint | 0-255 |
| varchar | 长度限制 |

---

## 索引优化

### 常用查询优化

**查询1：按分类获取发布的文章**

```sql
-- 使用索引
SELECT * FROM articles
WHERE category_id = 1 AND status = 1 AND publish_time IS NOT NULL
ORDER BY publish_time DESC
LIMIT 20;

-- 索引：idx_category_status_publish
```

**查询2：获取用户的评论**

```sql
SELECT * FROM comments
WHERE user_name = '用户名'
ORDER BY create_time DESC
LIMIT 50;

-- 索引：idx_user_name_time
```

**查询3：搜索文章**

```sql
SELECT * FROM articles
WHERE MATCH(title, content) AGAINST('搜索词' IN BOOLEAN MODE);

-- 索引：FULLTEXT ft_search
```

### 索引建议

参见 [DATABASE_INDEX_OPTIMIZATION.md](./DATABASE_INDEX_OPTIMIZATION.md)

---

## 备份和恢复

### 完整备份

```bash
mysqldump -u root -p --all-databases > full_backup.sql
```

### 单库备份

```bash
mysqldump -u root -p cms_database > cms_backup.sql
```

### 恢复

```bash
mysql -u root -p cms_database < cms_backup.sql
```

---

## 相关文档

- [数据库索引优化](./DATABASE_INDEX_OPTIMIZATION.md)
- [开发指南](./DEVELOPER_GUIDE.md)
- [API 文档](./API_DOCUMENTATION.md)

---

**数据库版本**: 1.0.0
**最后更新**: 2025-10-24
**CMS 版本**: 1.2.0
