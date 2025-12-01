# 模板包完整管理指南

## 概述

本文档详细说明了CMS系统中模板包的完整创建和管理流程。模板包不仅仅是数据库记录，而是一个完整的文件系统结构，包含模板文件、资源文件和配置信息。

## 模板包架构

### 1. 文件系统结构

```
templates/
└── [package_code]/          # 模板包目录
    ├── template.json        # 配置文件（必需）
    ├── README.md           # 说明文档
    ├── layout.html         # 布局模板
    ├── index.html          # 首页模板
    ├── category.html       # 分类页模板
    ├── article.html        # 文章页模板
    ├── page.html           # 单页模板
    ├── search.html         # 搜索页模板
    ├── tag.html           # 标签页模板
    ├── css/               # 样式文件目录
    │   └── style.css
    ├── js/                # JavaScript目录
    │   └── main.js
    ├── images/            # 图片资源
    └── fonts/             # 字体文件
```

### 2. 数据库结构

- **template_packages** - 模板包信息表
- **templates** - 模板文件记录表
- **site_template_config** - 站点模板配置表

## 创建模板包的三种方式

### 方式一：使用TemplatePackageService（推荐）

```php
use app\service\TemplatePackageService;

$service = new TemplatePackageService();

// 模板包信息
$packageData = [
    'name' => '模板包名称',
    'code' => 'template_code',
    'description' => '模板包描述',
    'version' => '1.0.0',
    'author' => '作者名',
    'status' => 1
];

// 模板文件列表
$templates = [
    [
        'name' => '布局模板',
        'type' => 'layout',
        'file' => 'layout.html',
        'description' => '基础布局'
    ],
    // ... 其他模板
];

// 创建模板包
$package = $service->createPackage($packageData, $templates);
```

**此方式会自动：**
- ✅ 创建数据库记录
- ✅ 创建目录结构
- ✅ 生成配置文件
- ✅ 创建模板文件
- ✅ 创建资源目录
- ✅ 生成README文档

### 方式二：使用命令行工具

```bash
# 创建新模板包
php think template:install create my_template --name="我的模板" --author="张三" --description="自定义模板包"

# 从现有文件安装
php think template:install install existing_template

# 更新模板包配置
php think template:install update my_template
```

### 方式三：手动创建（不推荐）

1. 在templates目录创建文件夹
2. 创建template.json配置文件
3. 编写模板文件
4. 在数据库中插入记录

## 模板配置文件（template.json）

```json
{
    "name": "模板包名称",
    "version": "1.0.0",
    "author": "作者",
    "description": "模板包描述",
    "screenshot": "screenshot.jpg",
    "templates": {
        "index": {
            "file": "index.html",
            "name": "首页模板",
            "description": "网站首页"
        },
        "category": {
            "file": "category.html",
            "name": "分类页",
            "description": "分类列表页"
        }
        // ... 其他模板
    },
    "assets": {
        "css": ["css/style.css"],
        "js": ["js/main.js"],
        "images": ["images/"]
    },
    "settings": {
        // 自定义设置
    }
}
```

## 模板文件编写规范

### 1. 继承布局

```twig
{% extends "[package_code]/layout.html" %}
```

### 2. 定义区块

```twig
{% block title %}页面标题{% endblock %}
{% block content %}
    <!-- 页面内容 -->
{% endblock %}
```

### 3. 使用变量

```twig
{{ base_url }}          # 站点根URL
{{ site_name }}         # 站点名称
{{ article.title }}     # 文章标题
{{ article.content|raw }}  # 文章内容
```

## 后台管理流程

### 1. 创建模板包

在后台管理系统中：

1. 进入【模板管理】→【模板包】
2. 点击【新建模板包】
3. 填写基本信息
4. **系统自动创建：**
   - 目录结构
   - 配置文件
   - 模板文件
   - 数据库记录

### 2. 分配模板

1. 进入【站点管理】
2. 选择站点
3. 在【模板设置】中选择模板包
4. 为不同页面类型分配对应模板

### 3. 编辑模板

1. 通过FTP/文件管理器访问 `templates/[package_code]/`
2. 编辑对应的HTML文件
3. 修改CSS/JS文件
4. 刷新页面查看效果

## 实际案例

### Linux系统下载网模板包

已创建的完整模板包示例：

```
templates/linux_nbxx/
├── template.json        # 配置文件
├── layout.html         # 布局（头部、导航、底部）
├── index.html          # 首页（15个Linux发行版板块）
├── category.html       # 分类页（带侧边排行榜）
├── article.html        # 详情页（下载链接展示）
├── page.html          # 单页
├── search.html        # 搜索页（关键词高亮）
├── tag.html           # 标签页（标签云）
├── css/
│   └── style.css      # 11KB完整样式
├── js/
│   └── main.js        # 9KB交互功能
└── images/
    └── README.md      # 图片说明
```

**数据库记录：**
- template_packages表：ID=3
- templates表：7条记录
- 状态：已启用，可在后台选择使用

## API接口

### 获取可用模板包

```
GET /api/template-package/all?site_id=1
```

### 获取模板包详情

```
GET /api/template-package/read/3
```

### 获取模板列表

```
GET /api/template-package/templates/3?template_type=index
```

## 注意事项

1. **模板包代码（code）必须唯一**
2. **模板文件路径使用正斜杠（/）**
3. **继承时使用完整路径：`package_code/layout.html`**
4. **资源文件使用绝对路径：`{{ base_url }}/templates/package_code/css/style.css`**
5. **系统模板包（is_system=1）不允许删除**

## 故障排除

### 问题1：模板不显示在后台

**检查：**
- template_packages表的status字段是否为1
- templates表中是否有对应记录
- 文件是否存在于templates目录

### 问题2：模板渲染错误

**检查：**
- extends路径是否正确
- 变量名是否拼写正确
- Twig语法是否正确

### 问题3：样式不生效

**检查：**
- CSS文件路径是否正确
- base_url变量是否正常输出
- 浏览器缓存是否清理

## 总结

完整的模板包创建流程包括：

1. **数据库记录** - 在template_packages和templates表中创建记录
2. **目录结构** - 在templates目录创建模板包文件夹
3. **配置文件** - 创建template.json
4. **模板文件** - 创建各类型模板HTML文件
5. **资源文件** - 创建CSS、JS、图片等资源
6. **文档说明** - 创建README.md

使用TemplatePackageService可以自动完成以上所有步骤，确保模板包的完整性和可用性。

---

*文档更新日期：2024-11-24*