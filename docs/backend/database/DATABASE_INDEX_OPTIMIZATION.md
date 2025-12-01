# 数据库索引优化方案

## 1. 当前索引分析

通过分析数据库设计文件，发现以下表需要优化索引：

### 1.1 关键发现

| 表名 | 缺失的索引 | 原因 |
|------|---------|------|
| articles | `idx_create_time`, `idx_update_time` | 列表排序查询频繁 |
| articles | `idx_category_status_publish` | 联合查询优化 |
| admin_logs | `idx_action_module` | 操作日志查询优化 |
| media | `idx_create_time` | 媒体库列表排序 |
| comments | `idx_create_time` | 评论列表排序 |
| categories | `idx_create_time` | 分类列表排序 |
| tags | `idx_article_count` | 热门标签查询 |

### 1.2 现有索引评估

✅ **设计合理的索引**：
- `articles.uk_slug` - 唯一索引
- `articles.idx_status_publish` - 复合索引
- `article_tags.uk_article_tag` - 复合唯一索引
- `admin_users.uk_username` - 唯一索引

⚠️ **需要优化的地方**：
- 缺少创建时间排序索引
- 缺少某些频繁查询字段的组合索引
- 全文索引可以优化

## 2. 优化建议

### 2.1 文章表优化

```sql
-- 添加创建时间索引（用于排序）
ALTER TABLE articles ADD INDEX idx_create_time (create_time DESC);
ALTER TABLE articles ADD INDEX idx_update_time (update_time DESC);

-- 添加复合索引（分类+状态+发布时间）
ALTER TABLE articles ADD INDEX idx_category_status_publish (category_id, status, publish_time DESC);

-- 优化全文索引查询性能
ALTER TABLE articles ADD FULLTEXT INDEX ft_search (title, summary, content);
```

### 2.2 日志表优化

```sql
-- 操作日志复合索引
ALTER TABLE admin_logs ADD INDEX idx_action_module (action, module);
ALTER TABLE admin_logs ADD INDEX idx_user_create (user_id, create_time DESC);

-- 登录日志索引
ALTER TABLE admin_logs ADD INDEX idx_ip_time (ip, create_time DESC);
```

### 2.3 媒体库优化

```sql
-- 媒体库时间排序
ALTER TABLE media ADD INDEX idx_create_time (create_time DESC);
ALTER TABLE media ADD INDEX idx_user_type (user_id, file_type);
```

### 2.4 评论表优化

```sql
-- 评论表排序和审核
ALTER TABLE comments ADD INDEX idx_create_time (create_time DESC);
ALTER TABLE comments ADD INDEX idx_article_status (article_id, status);
```

### 2.5 分类标签优化

```sql
-- 分类
ALTER TABLE categories ADD INDEX idx_create_time (create_time DESC);

-- 标签热度查询
ALTER TABLE tags ADD INDEX idx_article_count (article_count DESC);
ALTER TABLE tags ADD INDEX idx_create_time (create_time DESC);
```

### 2.6 分页查询优化

```sql
-- 站点配置（分组查询）
ALTER TABLE site_config ADD INDEX idx_group_sort (group_name, sort);

-- 模板
ALTER TABLE templates ADD INDEX idx_status (status);
```

## 3. 索引性能基准测试

### 3.1 常见查询场景

```sql
-- 场景1：按状态发布时间获取文章列表（使用idx_status_publish）
SELECT * FROM articles
WHERE status = 1 AND publish_time IS NOT NULL
ORDER BY publish_time DESC
LIMIT 20;

-- 场景2：按分类获取发布的文章（使用idx_category_status_publish）
SELECT * FROM articles
WHERE category_id = 1 AND status = 1
ORDER BY publish_time DESC
LIMIT 20;

-- 场景3：按创建时间排序文章（使用idx_create_time）
SELECT * FROM articles
ORDER BY create_time DESC
LIMIT 20;

-- 场景4：操作日志查询（使用idx_action_module）
SELECT * FROM admin_logs
WHERE action = 'edit' AND module = 'article'
ORDER BY create_time DESC;

-- 场景5：全文搜索（使用ft_search）
SELECT * FROM articles
WHERE MATCH(title, summary, content) AGAINST('关键词' IN BOOLEAN MODE);
```

## 4. 实施方案

### 4.1 第一阶段：关键索引

1. ✅ 添加文章表时间排序索引
2. ✅ 添加文章复合索引
3. ✅ 添加日志表复合索引

### 4.2 第二阶段：性能优化

4. ✅ 添加媒体库索引
5. ✅ 添加评论表索引
6. ✅ 添加分类标签索引

### 4.3 第三阶段：监控维护

- 使用 EXPLAIN 分析查询计划
- 定期统计索引使用情况
- 删除未使用的索引

## 5. 索引维护脚本

### 5.1 查看索引使用情况

```sql
SELECT
    object_schema,
    object_name,
    count_read,
    count_write,
    count_delete,
    count_insert,
    count_update
FROM performance_schema.table_io_waits_summary_by_index_usage
WHERE object_schema != 'mysql'
ORDER BY count_read DESC;
```

### 5.2 查找未使用的索引

```sql
SELECT
    object_schema,
    object_name,
    index_name
FROM performance_schema.table_io_waits_summary_by_index_usage
WHERE index_name != 'PRIMARY'
    AND count_read = 0
    AND count_write = 0
ORDER BY object_schema, object_name;
```

## 6. 预期收益

- 文章列表查询性能提升 50-70%
- 日志查询性能提升 40-60%
- 减少全表扫描
- 改善用户体验

## 7. 注意事项

⚠️ **实施前**：
- 备份数据库
- 在测试环境验证
- 使用 EXPLAIN 分析执行计划

⚠️ **实施中**：
- 在非业务高峰期执行
- 监控查询性能变化
- 记录实施时间

⚠️ **实施后**：
- 验证查询性能
- 更新 ORM 查询策略
- 定期监控索引效率

---

**更新时间**：2025-10-24
**优先级**：HIGH
**预计工作量**：2-3小时
