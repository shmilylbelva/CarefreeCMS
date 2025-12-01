# 模板资源管理方案

## 一、问题描述

在使用逍遥CMS生成静态页面时，如果模板文件中的CSS是内联的，会导致代码重复、难以维护。如果将CSS独立出来作为单独的文件，又会遇到生成静态页面后CSS文件不会同步的问题。

## 二、解决方案

我们实现了一个**自动资源同步系统**，可以：
1. 允许你在模板套装中使用独立的CSS、JS、图片文件
2. 在生成静态页面时自动将这些资源复制到静态目录
3. 支持增量同步，只复制变更的文件

## 三、目录结构

### 1. 模板套装资源目录

在每个模板套装目录下创建 `assets` 文件夹：

```
backend/templates/official/
├── assets/
│   ├── css/
│   │   ├── common.css      # 公共样式
│   │   ├── index.css       # 首页样式
│   │   ├── article.css     # 文章页样式
│   │   └── layout.css      # 布局样式
│   ├── js/
│   │   ├── main.js         # 主JS文件
│   │   └── utils.js        # 工具函数
│   └── images/
│       ├── logo.png
│       └── banner.jpg
├── index.html
├── article.html
├── layout.html
└── theme.json
```

### 2. 静态输出目录

资源文件会被自动复制到：

```
backend/html/
├── assets/
│   ├── css/
│   │   ├── common.css
│   │   ├── index.css
│   │   └── ...
│   ├── js/
│   │   └── ...
│   └── images/
│       └── ...
├── index.html
├── article/
│   └── 1.html
└── ...
```

## 四、如何使用

### 1. 在模板中引用独立的CSS文件

修改你的模板文件（如 `layout.html`）：

**之前（内联CSS）：**
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { background: #333; }
        /* 很多CSS代码... */
    </style>
</head>
<body>
    ...
</body>
</html>
```

**现在（使用外部CSS）：**
```html
<!DOCTYPE html>
<html>
<head>
    <title>{$title}</title>
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/layout.css">
</head>
<body>
    ...
</body>
</html>
```

### 2. 创建CSS文件

在 `backend/templates/official/assets/css/` 目录下创建 `common.css`：

```css
/* common.css - 公共样式 */
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: #333;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
}
```

### 3. 资源路径说明

**重要：** 在模板中使用相对路径引用资源：

```html
<!-- CSS -->
<link rel="stylesheet" href="assets/css/common.css">

<!-- JavaScript -->
<script src="assets/js/main.js"></script>

<!-- 图片 -->
<img src="assets/images/logo.png" alt="Logo">
```

**为什么使用相对路径？**
- 首页 `/index.html` 访问资源：`assets/css/common.css` → `/assets/css/common.css`
- 文章页 `/article/123.html` 访问资源：`../assets/css/common.css` → `/assets/css/common.css`

**最佳实践：**
使用绝对路径（推荐）：
```html
<link rel="stylesheet" href="/assets/css/common.css">
<script src="/assets/js/main.js"></script>
<img src="/assets/images/logo.png" alt="Logo">
```

## 五、自动同步机制

### 1. 生成所有静态页面时自动同步

当你点击"生成全部"按钮时，系统会：
1. 生成所有HTML静态页面
2. **自动同步** `assets` 目录到静态输出目录
3. 在生成结果中显示同步的文件数量

### 2. 手动同步资源

如果你只修改了CSS/JS文件，不想重新生成所有HTML，可以单独同步资源：

**API接口：**
```bash
POST /backend/build/sync-assets
```

**返回示例：**
```json
{
    "code": 200,
    "message": "资源同步成功",
    "data": {
        "success": true,
        "total_files": 15,
        "log": [
            {
                "file": "common.css",
                "path": "assets/css/common.css",
                "size": 3456,
                "action": "copied",
                "reason": "源文件更新"
            }
        ]
    }
}
```

### 3. 增量同步机制

系统会智能检测文件是否需要复制：

| 情况 | 操作 | 说明 |
|------|------|------|
| 目标文件不存在 | 复制 | 新文件 |
| 源文件更新时间更晚 | 复制 | 文件已修改 |
| 文件大小不同 | 复制 | 内容变化 |
| 文件完全相同 | 跳过 | 避免不必要的复制 |

## 六、API接口说明

### 1. 同步资源文件

```
POST /backend/build/sync-assets
```

将模板套装的 `assets` 目录同步到静态输出目录。

**返回参数：**
- `success`: 是否成功
- `total_files`: 同步的文件总数
- `log`: 详细的同步日志（每个文件的操作）

### 2. 清理旧资源

```
POST /backend/build/clean-assets
```

删除静态输出目录中的所有资源文件（清空 `backend/html/assets/` 目录）。

**使用场景：**
- 切换模板套装前清理旧资源
- 资源文件混乱时重新同步

### 3. 获取资源列表

```
GET /backend/build/assets-list
```

获取当前模板套装的所有资源文件列表。

**返回示例：**
```json
{
    "code": 200,
    "data": {
        "css": [
            {
                "name": "common.css",
                "path": "assets/css/common.css",
                "size": 3456,
                "modified": "2025-10-23 10:30:15"
            }
        ],
        "js": [...],
        "images": [...],
        "other": [...]
    }
}
```

## 七、最佳实践

### 1. CSS文件组织建议

```
assets/css/
├── common.css      # 全局公共样式（重置、字体、颜色变量等）
├── layout.css      # 布局样式（头部、底部、导航等）
├── components.css  # 组件样式（按钮、表单、卡片等）
├── index.css       # 首页特定样式
├── article.css     # 文章页特定样式
└── responsive.css  # 响应式样式
```

### 2. 在layout.html中统一引入公共资源

```html
{__NOLAYOUT__}
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{block name="title"}{carefree:config name='site_name' /}{/block}</title>

    <!-- 公共CSS -->
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/components.css">

    <!-- 页面特定CSS -->
    {block name="style"}{/block}
</head>
<body>
    <!-- 头部 -->
    <header>...</header>

    <!-- 主内容 -->
    <main>
        {block name="content"}{/block}
    </main>

    <!-- 底部 -->
    <footer>...</footer>

    <!-- 公共JS -->
    <script src="/assets/js/main.js"></script>

    <!-- 页面特定JS -->
    {block name="script"}{/block}
</body>
</html>
```

### 3. 在子模板中引入特定样式

```html
{extend name="layout" /}

{block name="style"}
<link rel="stylesheet" href="/assets/css/article.css">
{/block}

{block name="content"}
<article class="article-detail">
    <h1>{$article.title}</h1>
    <div class="article-content">
        {$article.content|raw}
    </div>
</article>
{/block}
```

### 4. 使用CSS变量统一样式

在 `common.css` 中定义CSS变量：

```css
:root {
    /* 主题颜色 */
    --primary-color: #409EFF;
    --success-color: #67C23A;
    --warning-color: #E6A23C;
    --danger-color: #F56C6C;

    /* 文字颜色 */
    --text-primary: #303133;
    --text-regular: #606266;
    --text-secondary: #909399;

    /* 边框颜色 */
    --border-color: #DCDFE6;

    /* 背景颜色 */
    --bg-color: #F5F5F5;
    --bg-white: #FFFFFF;
}

body {
    color: var(--text-primary);
    background-color: var(--bg-color);
}

a {
    color: var(--primary-color);
}
```

### 5. 压缩资源文件（可选）

生产环境建议压缩CSS和JS：

```bash
# 使用在线工具或命令行工具压缩
# CSS压缩
csso common.css -o common.min.css

# JS压缩
uglifyjs main.js -o main.min.js
```

然后在模板中引用压缩版本：
```html
<link rel="stylesheet" href="/assets/css/common.min.css">
```

## 八、工作流程

### 开发流程

1. **编辑CSS文件**
   ```bash
   编辑 backend/templates/official/assets/css/common.css
   ```

2. **方式一：重新生成静态页面**
   - 访问后台 → 静态生成 → 生成全部
   - 系统会自动同步资源文件

3. **方式二：只同步资源文件**
   - 使用API：`POST /backend/build/sync-assets`
   - 或添加前端按钮调用此接口

4. **预览效果**
   - 访问静态页面查看效果

### 切换模板套装

1. **清理旧资源**
   ```bash
   POST /backend/build/clean-assets
   ```

2. **切换模板**
   ```bash
   POST /backend/templates/switch-theme
   ```

3. **重新生成**
   ```bash
   POST /backend/build/all
   ```

## 九、常见问题

### Q1: 修改了CSS但静态页面没有更新？

**A:** 有几种可能：

1. 浏览器缓存，尝试强制刷新（Ctrl+F5）
2. 未同步资源，执行：`POST /backend/build/sync-assets`
3. 路径错误，检查CSS引用路径

### Q2: CSS文件路径404？

**A:** 检查以下几点：

1. 确保资源已同步：访问 `GET /backend/build/assets-list` 查看
2. 检查路径拼写，注意大小写
3. 使用绝对路径 `/assets/css/common.css`

### Q3: 如何知道哪些资源文件被同步了？

**A:** 查看同步日志：

```bash
POST /backend/build/sync-assets
```

返回的 `log` 字段包含每个文件的操作详情。

### Q4: 资源文件很多，同步很慢？

**A:** 优化建议：

1. 系统使用增量同步，只复制变更的文件
2. 删除不用的旧文件
3. 压缩CSS和JS文件减小体积

### Q5: 可以使用外部CDN吗？

**A:** 可以！直接在模板中引用CDN链接：

```html
<!-- 使用CDN -->
<link rel="stylesheet" href="https://cdn.example.com/common.css">

<!-- 或本地资源 -->
<link rel="stylesheet" href="/assets/css/common.css">
```

## 十、技术实现

### 核心类

- **TemplateAssetManager** (`backend/app/service/TemplateAssetManager.php`)
  - 负责资源文件的同步、清理和列表获取
  - 实现增量同步逻辑

- **Build Controller** (`backend/app/controller/backend/Build.php`)
  - 在 `all()` 方法中自动调用资源同步
  - 提供资源管理API接口

### 同步逻辑

```php
// 伪代码
foreach (源文件 as $file) {
    if (!目标文件存在) {
        复制文件;
    } elseif (源文件更新时间 > 目标文件更新时间) {
        复制文件;
    } elseif (源文件大小 != 目标文件大小) {
        复制文件;
    } else {
        跳过文件;
    }
}
```

## 十一、示例文件

系统已经为你创建了示例CSS文件：

```
backend/templates/official/assets/css/common.css
```

你可以在此基础上进行修改和扩展。

---

**版本**: 1.1.0
**最后更新**: 2025-10-23
**作者**: Xiaoyao Team
