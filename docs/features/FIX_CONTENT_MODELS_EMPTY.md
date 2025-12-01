# 内容模型列表为空问题修复

## 问题描述

用户反馈内容模型页面显示为空，但实际上数据库中已经有4个系统内置模型（文章、分类、标签、单页）。

## 问题原因

### 1. 搜索器逻辑缺陷

**文件**：`D:\work\cms\api\app\model\ContentModel.php`

**原代码**：
```php
public function searchNameAttr($query, $value)
{
    $query->where('name', 'like', '%' . $value . '%');
}

public function searchStatusAttr($query, $value)
{
    $query->where('status', $value);
}
```

**问题**：
- 当搜索参数为空字符串时，搜索器仍然会执行查询条件
- `where('status', '')` 会导致SQL查询 `status = ''`，无法匹配任何数据
- `where('name', 'like', '%%')` 虽然能匹配所有，但与status的空条件冲突

### 2. 缺少附加字段

控制器返回的数据缺少前端需要的 `status_text` 和 `is_system_text` 字段。

## 解决方案

### 1. 修复搜索器（ContentModel.php）

**修改后代码**：
```php
public function searchNameAttr($query, $value)
{
    if ($value !== '' && $value !== null) {
        $query->where('name', 'like', '%' . $value . '%');
    }
}

public function searchStatusAttr($query, $value)
{
    if ($value !== '' && $value !== null) {
        $query->where('status', $value);
    }
}
```

**改进点**：
- 添加了空值检查
- 只有在参数有值时才添加查询条件
- 避免了空字符串导致的查询错误

### 2. 添加附加字段（ContentModelController.php）

**修改后代码**：
```php
public function index(Request $request)
{
    // ... 前面的代码

    $list = $query->page($page, $pageSize)
        ->append(['status_text', 'is_system_text'])
        ->select()
        ->toArray();

    return Response::paginate($list, $total, $page, $pageSize);
}
```

**改进点**：
- 使用 `append()` 方法附加获取器字段
- 确保前端可以正确显示状态文本和系统模型标识

## 验证结果

修复后API返回数据：

```json
{
  "code": 200,
  "message": "success",
  "data": {
    "list": [
      {
        "id": 1,
        "name": "文章",
        "table_name": "articles",
        "icon": "Document",
        "description": "系统内置文章模型",
        "template": null,
        "is_system": 1,
        "status": 1,
        "sort": 100,
        "create_time": "2025-10-18 22:36:49",
        "update_time": "2025-10-18 22:36:49",
        "status_text": "启用",
        "is_system_text": "是"
      },
      {
        "id": 2,
        "name": "分类",
        "table_name": "categories",
        "icon": "FolderOpened",
        "description": "系统内置分类模型",
        "template": null,
        "is_system": 1,
        "status": 1,
        "sort": 90,
        "create_time": "2025-10-18 22:36:49",
        "update_time": "2025-10-18 22:36:49",
        "status_text": "启用",
        "is_system_text": "是"
      },
      {
        "id": 3,
        "name": "标签",
        "table_name": "tags",
        "icon": "CollectionTag",
        "description": "系统内置标签模型",
        "template": null,
        "is_system": 1,
        "status": 1,
        "sort": 80,
        "create_time": "2025-10-18 22:36:49",
        "update_time": "2025-10-18 22:36:49",
        "status_text": "启用",
        "is_system_text": "是"
      },
      {
        "id": 4,
        "name": "单页",
        "table_name": "pages",
        "icon": "Files",
        "description": "系统内置单页模型",
        "template": null,
        "is_system": 1,
        "status": 1,
        "sort": 70,
        "create_time": "2025-10-18 22:36:49",
        "update_time": "2025-10-18 22:36:49",
        "status_text": "启用",
        "is_system_text": "是"
      }
    ],
    "total": 4,
    "page": 1,
    "page_size": 20,
    "total_pages": 1
  },
  "timestamp": 1760962167
}
```

## 系统内置模型说明

| ID | 名称 | 表名 | 图标 | 描述 |
|----|------|------|------|------|
| 1 | 文章 | articles | Document | 系统内置文章模型 |
| 2 | 分类 | categories | FolderOpened | 系统内置分类模型 |
| 3 | 标签 | tags | CollectionTag | 系统内置标签模型 |
| 4 | 单页 | pages | Files | 系统内置单页模型 |

## 经验总结

1. **搜索器设计原则**：
   - 必须处理空值情况
   - 避免空字符串作为查询条件
   - 使用严格的条件判断（!== '' && !== null）

2. **ThinkPHP模型获取器**：
   - 需要使用 `append()` 方法显式附加
   - 在toArray()之前调用append()
   - 确保前端能获取到计算字段

3. **调试技巧**：
   - 先检查数据库是否有数据
   - 再检查API是否返回正确
   - 最后检查前端渲染逻辑
   - 使用curl直接测试API接口

## 相关文件

- `backend/app/model/ContentModel.php` - 内容模型类
- `backend/app/controller/backend/ContentModelController.php` - 内容模型控制器
- `frontend/src/views/contentModel/List.vue` - 前端列表页面
- `docs/database_custom_fields_and_models.sql` - 初始数据脚本
