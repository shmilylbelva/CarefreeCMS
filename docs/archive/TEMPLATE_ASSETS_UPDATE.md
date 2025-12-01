# 模板资源管理功能更新说明

## 更新时间
2025-10-23

## 更新内容

### 新增功能：模板资源自动同步

现在你可以在模板中使用独立的CSS、JS、图片文件，系统会在生成静态页面时自动同步这些资源文件。

### 主要特性

1. **自动资源同步**
   - 生成静态页面时自动同步 `assets` 目录
   - 增量同步，只复制变更的文件
   - 支持CSS、JS、图片等所有类型的资源

2. **智能同步机制**
   - 检测文件修改时间和大小
   - 跳过未变更的文件，提高效率
   - 详细的同步日志

3. **独立资源管理API**
   - `POST /backend/build/sync-assets` - 手动同步资源
   - `POST /backend/build/clean-assets` - 清理旧资源
   - `GET /backend/build/assets-list` - 获取资源列表

## 新增文件

### 后端
- `backend/app/service/TemplateAssetManager.php` - 资源管理服务类

### 前端资源
- `backend/templates/official/assets/css/common.css` - 示例公共CSS文件

### 文档
- `docs/TEMPLATE_ASSETS.md` - 完整使用文档
- `docs/QUICK_START_ASSETS.md` - 快速入门指南

## 修改文件

### 后端
- `backend/app/controller/backend/Build.php`
  - 添加 TemplateAssetManager 引用
  - 新增 `syncAssets()` 方法
  - 新增 `cleanAssets()` 方法
  - 新增 `getAssetsList()` 方法
  - 修改 `all()` 方法，添加自动资源同步

### 路由
- `backend/route/api.php`
  - 添加资源管理相关路由

## 使用方法

### 1. 创建资源文件

在模板套装目录下创建 `assets` 目录：

```
backend/templates/official/
└── assets/
    ├── css/
    │   └── common.css
    ├── js/
    └── images/
```

### 2. 在模板中引用

```html
<link rel="stylesheet" href="/assets/css/common.css">
<script src="/assets/js/main.js"></script>
<img src="/assets/images/logo.png" alt="Logo">
```

### 3. 生成静态页面

点击"生成全部"按钮，系统会：
1. 生成所有HTML静态页面
2. **自动同步资源文件到 `backend/html/assets/` 目录**

## 兼容性

- 向后兼容：现有模板继续使用内联CSS也完全正常
- 可以混合使用内联CSS和外部CSS文件
- 不影响现有功能

## 性能优化

- 增量同步，只复制变更的文件
- 避免不必要的文件操作
- 支持大量资源文件

## 后续规划

- [ ] 前端添加资源管理界面
- [ ] 支持资源文件压缩和优化
- [ ] 支持资源版本管理
- [ ] 添加资源CDN配置

## 文档

详细使用说明请参考：
- [完整文档](docs/TEMPLATE_ASSETS.md)
- [快速上手](docs/QUICK_START_ASSETS.md)

## 技术支持

如有问题，请查看：
1. 文档：`docs/TEMPLATE_ASSETS.md`
2. 快速上手：`docs/QUICK_START_ASSETS.md`
3. Issue: https://github.com/carefree-code/CarefreeCMS/issues

---

**版本**: 1.1.0
**开发团队**: CareFree Team
