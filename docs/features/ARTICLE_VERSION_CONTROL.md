# 文章版本控制功能文档

## 功能概述

文章版本控制功能允许系统自动记录文章的每次修改历史，支持版本对比和版本回滚，确保内容修改的可追溯性。

## 功能特性

✅ **自动版本保存** - 每次创建或更新文章时自动创建版本快照
✅ **版本列表** - 查看文章的所有历史版本
✅ **版本对比** - 对比任意两个版本的差异
✅ **版本回滚** - 恢复到指定的历史版本
✅ **版本删除** - 删除不需要的版本（保留至少一个版本）
✅ **修改说明** - 支持为每次修改添加说明文字

---

## 数据库设计

### article_versions 表结构

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 版本ID |
| article_id | int | 文章ID |
| version_number | int | 版本号（递增） |
| title | varchar | 文章标题 |
| content | longtext | 文章内容 |
| summary | varchar | 文章摘要 |
| cover_image | varchar | 封面图 |
| category_id | int | 分类ID |
| user_id | int | 作者ID |
| tags | text | 标签（JSON） |
| seo_* | varchar | SEO相关字段 |
| change_log | varchar | 修改说明 |
| created_by | int | 创建版本的用户ID |
| create_time | datetime | 创建时间 |

---

## API接口文档

### 1. 获取文章的版本列表

**请求**
```http
GET /backend/articles/{article_id}/versions?page=1&page_size=20
Authorization: Bearer {token}
```

**响应**
```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "list": [
            {
                "id": 5,
                "article_id": 1,
                "version_number": 5,
                "title": "文章标题",
                "change_log": "修改了标题和内容",
                "created_by": 1,
                "create_time": "2025-10-18 15:30:00",
                "creator": {
                    "id": 1,
                    "username": "admin"
                }
            }
        ],
        "pagination": {
            "total": 5,
            "page": 1,
            "page_size": 20,
            "total_pages": 1
        }
    }
}
```

### 2. 获取版本详情

**请求**
```http
GET /backend/article-versions/{id}
Authorization: Bearer {token}
```

**响应**
```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "id": 5,
        "article_id": 1,
        "version_number": 5,
        "title": "文章标题",
        "content": "文章内容...",
        "summary": "文章摘要",
        "cover_image": "/uploads/images/cover.jpg",
        "category_id": 2,
        "tags": {"1": "技术", "3": "开发"},
        "seo_title": "SEO标题",
        "seo_keywords": "关键词",
        "seo_description": "SEO描述",
        "change_log": "修改了标题和内容",
        "created_by": 1,
        "create_time": "2025-10-18 15:30:00",
        "article": { /* 文章信息 */ },
        "category": { /* 分类信息 */ },
        "user": { /* 作者信息 */ },
        "creator": { /* 创建版本的用户信息 */ }
    }
}
```

### 3. 对比两个版本

**请求**
```http
GET /backend/article-versions/compare?old_version_id=3&new_version_id=5
Authorization: Bearer {token}
```

**响应**
```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "old_version": {
            "id": 3,
            "version_number": 3,
            "create_time": "2025-10-18 14:00:00",
            "created_by": "admin",
            "change_log": "初始版本"
        },
        "new_version": {
            "id": 5,
            "version_number": 5,
            "create_time": "2025-10-18 15:30:00",
            "created_by": "admin",
            "change_log": "修改了标题和内容"
        },
        "diff": {
            "title": {
                "old": "旧标题",
                "new": "新标题"
            },
            "content": {
                "old": "旧内容...",
                "new": "新内容..."
            },
            "seo_keywords": {
                "old": "旧关键词",
                "new": "新关键词"
            }
        }
    }
}
```

### 4. 回滚到指定版本

**请求**
```http
POST /backend/article-versions/{id}/rollback
Authorization: Bearer {token}
Content-Type: application/json
```

**响应**
```json
{
    "code": 200,
    "msg": "版本回滚成功",
    "data": {
        "article_id": 1,
        "rollback_to_version": 3
    }
}
```

**说明**：
- 回滚前会自动备份当前版本
- 回滚后会创建一个新版本，标记为"回滚到版本 #N"
- 回滚操作会恢复文章的所有字段（标题、内容、分类、标签等）

### 5. 删除版本

**请求**
```http
DELETE /backend/article-versions/{id}
Authorization: Bearer {token}
```

**响应**
```json
{
    "code": 200,
    "msg": "版本删除成功",
    "data": []
}
```

**限制**：
- 不能删除文章的最后一个版本
- 已删除的版本无法恢复

### 6. 批量删除版本

**请求**
```http
POST /backend/article-versions/batch-delete
Authorization: Bearer {token}
Content-Type: application/json

{
    "ids": [2, 3, 4]
}
```

**响应**
```json
{
    "code": 200,
    "msg": "批量删除成功",
    "data": []
}
```

### 7. 获取版本统计信息

**请求**
```http
GET /backend/articles/{article_id}/versions/statistics
Authorization: Bearer {token}
```

**响应**
```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "article_id": 1,
        "article_title": "文章标题",
        "version_count": 5,
        "latest_version": {
            "id": 5,
            "version_number": 5,
            "create_time": "2025-10-18 15:30:00",
            "change_log": "修改了标题和内容"
        },
        "first_version": {
            "id": 1,
            "version_number": 1,
            "create_time": "2025-10-15 10:00:00"
        }
    }
}
```

---

## 使用场景

### 场景1：创建文章时自动创建版本

```http
POST /backend/articles
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "我的第一篇文章",
    "content": "文章内容...",
    "category_id": 1,
    "tags": [1, 2],
    "status": 1
}
```

**系统行为**：
1. 创建文章
2. 自动创建版本号为 1 的初始版本
3. 修改说明默认为 "初始版本"

### 场景2：更新文章时创建新版本

```http
PUT /backend/articles/1
Authorization: Bearer {token}
Content-Type: application/json

{
    "title": "修改后的标题",
    "content": "修改后的内容...",
    "change_log": "修正了错别字，优化了排版"
}
```

**系统行为**：
1. 更新文章内容
2. 自动创建新版本（版本号递增）
3. 保存修改说明

### 场景3：查看修改历史

前端可以：
1. 展示版本列表，显示每次修改的时间、修改人、修改说明
2. 点击版本查看该版本的完整内容
3. 选择两个版本进行对比，高亮显示差异部分

### 场景4：回滚到历史版本

当发现最新修改有问题时：
1. 选择一个历史版本
2. 点击"回滚"按钮
3. 系统自动恢复到该版本的内容
4. 当前版本会被备份为新版本

---

## 前端集成建议

### 版本列表页面

```vue
<template>
  <div class="version-list">
    <el-table :data="versions">
      <el-table-column prop="version_number" label="版本号" width="80" />
      <el-table-column prop="change_log" label="修改说明" />
      <el-table-column prop="creator.username" label="修改人" width="120" />
      <el-table-column prop="create_time" label="修改时间" width="180" />
      <el-table-column label="操作" width="200">
        <template #default="scope">
          <el-button size="small" @click="viewVersion(scope.row)">查看</el-button>
          <el-button size="small" @click="compareVersion(scope.row)">对比</el-button>
          <el-button size="small" type="warning" @click="rollback(scope.row)">回滚</el-button>
          <el-button size="small" type="danger" @click="deleteVersion(scope.row)">删除</el-button>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>
```

### 版本对比页面

```vue
<template>
  <div class="version-compare">
    <div class="diff-view">
      <div class="diff-item" v-for="(value, key) in diff" :key="key">
        <h4>{{ fieldLabels[key] }}</h4>
        <div class="old-value">
          <label>旧值：</label>
          <span>{{ value.old }}</span>
        </div>
        <div class="new-value">
          <label>新值：</label>
          <span>{{ value.new }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
```

---

## 注意事项

1. **性能考虑**
   - 每次修改都会创建完整的版本快照，会占用一定存储空间
   - 建议定期清理过期的版本（保留最近 N 个版本）
   - 对于非常活跃的文章，可以考虑限制版本数量

2. **版本保护**
   - 系统会阻止删除文章的最后一个版本
   - 重要版本可以考虑添加"锁定"功能防止误删

3. **回滚安全**
   - 回滚前会自动备份当前版本
   - 回滚操作会记录在操作日志中
   - 建议在回滚前向用户确认

4. **并发控制**
   - 如果多人同时编辑同一篇文章，可能导致版本冲突
   - 建议添加编辑锁或冲突检测机制

---

## 扩展功能建议

- [ ] 版本锁定功能（防止重要版本被删除）
- [ ] 版本标签（为重要版本添加标记）
- [ ] 定期清理策略（自动删除过期版本）
- [ ] 版本对比可视化（富文本内容的视觉对比）
- [ ] 版本审核工作流（重要修改需要审核）
- [ ] 版本导出（导出为文件）
- [ ] 版本合并（合并多个版本的修改）

---

**更新时间**: 2025-10-18
**版本**: 1.0
