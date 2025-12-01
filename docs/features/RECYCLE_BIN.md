# 内容回收站功能文档

## 功能概述

内容回收站功能为CMS系统提供了软删除机制，允许用户在删除内容后能够恢复或彻底删除，确保数据的安全性和可恢复性。

## 功能特性

✅ **软删除机制** - 删除的内容不会立即从数据库中移除，而是标记为已删除
✅ **回收站列表** - 统一管理所有已删除的内容（文章、分类、标签、单页）
✅ **恢复功能** - 支持单个或批量恢复已删除的内容
✅ **彻底删除** - 支持单个或批量彻底删除，真正从数据库中移除数据
✅ **分类筛选** - 可按内容类型筛选回收站项目
✅ **关键词搜索** - 支持按标题搜索回收站内容
✅ **统计信息** - 显示各类型内容的删除数量
✅ **清空回收站** - 一键清空所有或指定类型的回收站内容

---

## 数据库设计

### 软删除字段

所有支持回收站的表都添加了 `deleted_at` 字段：

```sql
ALTER TABLE `articles` ADD COLUMN `deleted_at` datetime DEFAULT NULL;
ALTER TABLE `categories` ADD COLUMN `deleted_at` datetime DEFAULT NULL;
ALTER TABLE `tags` ADD COLUMN `deleted_at` datetime DEFAULT NULL;
ALTER TABLE `pages` ADD COLUMN `deleted_at` datetime DEFAULT NULL;
```

- `deleted_at` 为 NULL：表示数据未删除（正常状态）
- `deleted_at` 有值：表示数据已删除（软删除状态），值为删除时间

---

## 后端实现

### 模型更新

所有支持回收站的模型都使用了 ThinkPHP 的 `SoftDelete` trait：

**Article.php**（已有）
```php
use think\model\concern\SoftDelete;

class Article extends Model
{
    use SoftDelete;

    protected $deleteTime = 'deleted_at';
    protected $defaultSoftDelete = null;
}
```

**Category.php**、**Tag.php**、**Page.php**（新增）
```php
use think\model\concern\SoftDelete;

class Category extends Model
{
    use SoftDelete;

    protected $deleteTime = 'deleted_at';
    protected $defaultSoftDelete = null;
}
```

### 控制器

**RecycleBin.php** - 回收站管理控制器

主要方法：
- `index()` - 获取回收站列表（支持分类筛选和关键词搜索）
- `statistics()` - 获取回收站统计信息
- `restore()` - 恢复单个项目
- `batchRestore()` - 批量恢复
- `destroy()` - 彻底删除单个项目
- `batchDestroy()` - 批量彻底删除
- `clear()` - 清空回收站

### API路由

```php
// 回收站管理
Route::get('recycle-bin', 'app\controller\api\RecycleBin@index');
Route::get('recycle-bin/statistics', 'app\controller\api\RecycleBin@statistics');
Route::post('recycle-bin/restore', 'app\controller\api\RecycleBin@restore');
Route::post('recycle-bin/batch-restore', 'app\controller\api\RecycleBin@batchRestore');
Route::delete('recycle-bin/:type/:id', 'app\controller\api\RecycleBin@destroy');
Route::post('recycle-bin/batch-destroy', 'app\controller\api\RecycleBin@batchDestroy');
Route::post('recycle-bin/clear', 'app\controller\api\RecycleBin@clear');
```

---

## 前端实现

### API服务

**recycleBin.js** - 回收站API服务层

```javascript
import request from './request'

// 获取回收站列表
export function getRecycleBinList(params)

// 获取回收站统计
export function getRecycleBinStatistics()

// 恢复单个项目
export function restoreItem(data)

// 批量恢复
export function batchRestore(data)

// 彻底删除单个项目
export function destroyItem(type, id)

// 批量彻底删除
export function batchDestroy(data)

// 清空回收站
export function clearRecycleBin(data)
```

### 页面组件

**RecycleBin.vue** - 回收站管理页面

功能：
- 显示回收站统计信息
- 类型筛选（全部、文章、分类、标签、单页）
- 关键词搜索
- 列表展示（类型、标题、删除时间）
- 批量操作（批量恢复、批量删除）
- 单个操作（恢复、彻底删除）
- 清空回收站
- 分页功能

### 路由配置

```javascript
{
  path: 'recycle-bin',
  name: 'RecycleBin',
  component: () => import('@/views/RecycleBin.vue'),
  meta: { title: '回收站' }
}
```

### 菜单导航

在系统管理菜单下添加了"回收站"菜单项：

```html
<el-menu-item index="/recycle-bin">回收站</el-menu-item>
```

---

## API接口文档

### 1. 获取回收站列表

**请求**
```http
GET /backend/recycle-bin?type=all&keyword=&page=1&page_size=20
Authorization: Bearer {token}
```

**参数说明**
- `type`: 类型筛选（all, article, category, tag, page）
- `keyword`: 关键词搜索
- `page`: 页码
- `page_size`: 每页数量

**响应**
```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "list": [
            {
                "id": 1,
                "item_type": "article",
                "item_type_text": "文章",
                "item_title": "测试文章",
                "deleted_at": "2025-10-18 15:30:00"
            }
        ],
        "pagination": {
            "total": 10,
            "page": 1,
            "page_size": 20
        }
    }
}
```

### 2. 获取回收站统计

**请求**
```http
GET /backend/recycle-bin/statistics
Authorization: Bearer {token}
```

**响应**
```json
{
    "code": 200,
    "msg": "success",
    "data": {
        "article_count": 5,
        "category_count": 2,
        "tag_count": 3,
        "page_count": 1,
        "total_count": 11
    }
}
```

### 3. 恢复单个项目

**请求**
```http
POST /backend/recycle-bin/restore
Authorization: Bearer {token}
Content-Type: application/json

{
    "type": "article",
    "id": 1
}
```

**响应**
```json
{
    "code": 200,
    "msg": "恢复成功",
    "data": []
}
```

### 4. 批量恢复

**请求**
```http
POST /backend/recycle-bin/batch-restore
Authorization: Bearer {token}
Content-Type: application/json

{
    "items": [
        {"type": "article", "id": 1},
        {"type": "category", "id": 2}
    ]
}
```

**响应**
```json
{
    "code": 200,
    "msg": "成功恢复 2 个项目",
    "data": {
        "success_count": 2
    }
}
```

### 5. 彻底删除单个项目

**请求**
```http
DELETE /backend/recycle-bin/{type}/{id}
Authorization: Bearer {token}
```

**响应**
```json
{
    "code": 200,
    "msg": "彻底删除成功",
    "data": []
}
```

### 6. 批量彻底删除

**请求**
```http
POST /backend/recycle-bin/batch-destroy
Authorization: Bearer {token}
Content-Type: application/json

{
    "items": [
        {"type": "article", "id": 1},
        {"type": "category", "id": 2}
    ]
}
```

**响应**
```json
{
    "code": 200,
    "msg": "成功删除 2 个项目",
    "data": {
        "success_count": 2
    }
}
```

### 7. 清空回收站

**请求**
```http
POST /backend/recycle-bin/clear
Authorization: Bearer {token}
Content-Type: application/json

{
    "type": "all"
}
```

**参数说明**
- `type`: all（清空所有）、article、category、tag、page

**响应**
```json
{
    "code": 200,
    "msg": "成功清空 10 个项目",
    "data": {
        "deleted_count": 10
    }
}
```

---

## 使用说明

### 1. 删除内容

在文章、分类、标签、单页的管理页面，点击"删除"按钮：
- 内容会被移到回收站，不会立即删除
- 删除后内容在列表中不再显示
- 可以通过回收站恢复

### 2. 查看回收站

在系统管理 → 回收站页面：
- 查看所有已删除的内容
- 按类型筛选（文章、分类、标签、单页）
- 按关键词搜索
- 查看统计信息

### 3. 恢复内容

**单个恢复**：
- 在回收站列表中找到要恢复的内容
- 点击"恢复"按钮
- 确认后内容将恢复到原列表

**批量恢复**：
- 勾选要恢复的多个内容
- 点击"批量恢复"按钮
- 确认后所有选中内容将被恢复

### 4. 彻底删除

**单个删除**：
- 在回收站列表中找到要删除的内容
- 点击"彻底删除"按钮
- 确认后内容将从数据库中永久删除，无法恢复

**批量删除**：
- 勾选要删除的多个内容
- 点击"批量彻底删除"按钮
- 确认后所有选中内容将被永久删除

### 5. 清空回收站

- 点击页面右上角"清空回收站"按钮
- 可选择清空所有类型或仅清空当前筛选类型
- 确认后回收站将被清空，所有内容永久删除

---

## 注意事项

1. **软删除机制**
   - 普通删除操作会将内容移到回收站（软删除）
   - 只有在回收站中执行"彻底删除"才会真正删除数据
   - 软删除的内容不会在前台显示

2. **数据安全**
   - 彻底删除操作不可恢复，请谨慎操作
   - 清空回收站会永久删除所有内容
   - 建议定期检查回收站，及时处理不需要的内容

3. **关联数据**
   - 删除分类时，该分类下的文章不会被删除
   - 删除标签时，文章与标签的关联会被移除
   - 恢复内容时会恢复到删除前的状态

4. **权限控制**
   - 回收站功能需要登录后才能访问
   - 建议只给管理员权限访问回收站
   - 普通用户删除的内容也会进入回收站

5. **性能优化**
   - 定期清理回收站可以减少数据库存储
   - 建议每月清理一次超过30天的回收站内容
   - 可以通过定时任务自动清理（待开发）

---

## 扩展功能建议

- [ ] **自动清理** - 定期自动清理超过指定天数的回收站内容
- [ ] **删除原因** - 记录删除原因，方便追溯
- [ ] **删除人** - 记录删除操作的用户
- [ ] **批量导出** - 导出回收站内容到文件
- [ ] **预览功能** - 在回收站中预览内容详情
- [ ] **高级筛选** - 按删除时间范围、删除人筛选

---

**更新时间**: 2025-10-18
**版本**: 1.0
