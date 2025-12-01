# 模板资源管理 - 5分钟快速上手

## 快速开始

### 第一步：创建CSS文件

在 `backend/templates/official/assets/css/` 目录下已有示例文件：
- `common.css` - 公共样式

你可以创建更多CSS文件，例如：

```bash
backend/templates/official/assets/css/
├── common.css      # 已创建
├── article.css     # 你可以创建
└── index.css       # 你可以创建
```

### 第二步：在模板中引用CSS

修改 `backend/templates/official/layout.html`，在 `<head>` 中添加：

```html
<link rel="stylesheet" href="/assets/css/common.css">
```

### 第三步：生成静态页面

访问后台管理：
1. 进入"静态生成"页面
2. 点击"生成全部"按钮
3. **系统会自动同步CSS文件到 `backend/html/assets/` 目录**

### 第四步：查看效果

访问生成的静态页面，CSS样式已生效！

## 关键点

1. **使用绝对路径引用资源**：`/assets/css/common.css`
2. **生成静态页面时会自动同步资源文件**
3. **只修改CSS不需要重新生成HTML时**，调用：`POST /backend/build/sync-assets`

## 目录结构一览

```
backend/
├── templates/official/          # 模板源文件
│   ├── assets/                  # 资源目录
│   │   ├── css/                 # CSS文件
│   │   ├── js/                  # JS文件
│   │   └── images/              # 图片文件
│   ├── layout.html
│   └── index.html
│
└── html/                        # 静态输出目录
    ├── assets/                  # 自动同步的资源
    │   ├── css/
    │   ├── js/
    │   └── images/
    └── index.html
```

## 常用命令

```bash
# 同步资源文件（单独）
curl -X POST http://localhost:8000/backend/build/sync-assets

# 清理旧资源
curl -X POST http://localhost:8000/backend/build/clean-assets

# 获取资源列表
curl -X GET http://localhost:8000/backend/build/assets-list
```

## 完整文档

详细说明请参考：[TEMPLATE_ASSETS.md](./TEMPLATE_ASSETS.md)
