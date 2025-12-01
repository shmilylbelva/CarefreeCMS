# 本地资源配置指南

## 重要规则

**所有模板不允许使用 CDN 资源，所有资源必须在本地！**

## 需要的本地资源

### 1. Bootstrap 5.3.3

**下载地址**：https://getbootstrap.com/docs/5.3/getting-started/download/

**需要的文件**：
- `bootstrap.min.css` → 放置到 `templates/{模板名}/assets/css/`
- `bootstrap.bundle.min.js` → 放置到 `templates/{模板名}/assets/js/`

**下载步骤**：
```bash
# 方法1：从官网下载编译版本
1. 访问 https://getbootstrap.com/docs/5.3/getting-started/download/
2. 下载 "Compiled CSS and JS"
3. 解压后复制以下文件：
   - dist/css/bootstrap.min.css
   - dist/js/bootstrap.bundle.min.js

# 方法2：使用 npm 下载（如果有 Node.js）
npm install bootstrap@5.3.3
# 文件位于 node_modules/bootstrap/dist/
```

### 2. Bootstrap Icons 1.11.3

**下载地址**：https://icons.getbootstrap.com/

**需要的文件**：
- `bootstrap-icons.min.css` → 放置到 `templates/{模板名}/assets/css/`
- `fonts/` 目录 → 放置到 `templates/{模板名}/assets/fonts/`

**下载步骤**：
```bash
# 方法1：从官网下载
1. 访问 https://github.com/twbs/icons/releases/latest
2. 下载 bootstrap-icons-{version}.zip
3. 解压后复制：
   - font/bootstrap-icons.min.css → assets/css/
   - font/fonts/ → assets/fonts/

# 方法2：使用 npm
npm install bootstrap-icons@1.11.3
# 文件位于 node_modules/bootstrap-icons/font/
```

**注意**：Bootstrap Icons CSS 文件中引用字体的路径需要修改

```css
/* 修改 bootstrap-icons.min.css 中的字体路径 */
@font-face {
  font-family: "bootstrap-icons";
  src: url("../fonts/bootstrap-icons.woff2?dd67030699838ea613ee6dbda90effa6") format("woff2"),
       url("../fonts/bootstrap-icons.woff?dd67030699838ea613ee6dbda90effa6") format("woff");
}
```

## 文件放置结构

### Official 模板

```
templates/official/assets/
├── css/
│   ├── bootstrap.min.css           ← Bootstrap CSS
│   ├── bootstrap-icons.min.css     ← Bootstrap Icons CSS
│   ├── common.css
│   ├── layout.css
│   └── comment.css
├── js/
│   ├── bootstrap.bundle.min.js     ← Bootstrap JS
│   ├── main.js
│   ├── auth.js
│   └── comment.js
├── fonts/
│   ├── bootstrap-icons.woff        ← Bootstrap Icons 字体
│   └── bootstrap-icons.woff2       ← Bootstrap Icons 字体
└── images/
    └── (项目图片)
```

### Blog 模板

```
templates/blog/assets/
├── css/
│   ├── bootstrap-icons.min.css     ← Bootstrap Icons CSS
│   ├── common.css
│   ├── layout.css
│   ├── index.css
│   └── article.css
├── js/
│   └── main.js
├── fonts/
│   ├── bootstrap-icons.woff        ← Bootstrap Icons 字体
│   └── bootstrap-icons.woff2       ← Bootstrap Icons 字体
└── images/
    └── (项目图片)
```

## 快速安装脚本

### Windows (PowerShell)

```powershell
# 下载 Bootstrap
Invoke-WebRequest -Uri "https://github.com/twbs/bootstrap/releases/download/v5.3.3/bootstrap-5.3.3-dist.zip" -OutFile "bootstrap.zip"
Expand-Archive bootstrap.zip -DestinationPath "."

# 下载 Bootstrap Icons
Invoke-WebRequest -Uri "https://github.com/twbs/icons/releases/download/v1.11.3/bootstrap-icons-1.11.3.zip" -OutFile "bootstrap-icons.zip"
Expand-Archive bootstrap-icons.zip -DestinationPath "."

# 复制到 official 模板
Copy-Item "bootstrap-5.3.3-dist/css/bootstrap.min.css" "templates/official/assets/css/"
Copy-Item "bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js" "templates/official/assets/js/"
Copy-Item "bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" "templates/official/assets/css/"
Copy-Item "bootstrap-icons-1.11.3/font/fonts" "templates/official/assets/" -Recurse

# 复制到 blog 模板
Copy-Item "bootstrap-icons-1.11.3/font/bootstrap-icons.min.css" "templates/blog/assets/css/"
Copy-Item "bootstrap-icons-1.11.3/font/fonts" "templates/blog/assets/" -Recurse
```

### Linux/Mac (Bash)

```bash
#!/bin/bash

# 下载 Bootstrap
wget https://github.com/twbs/bootstrap/releases/download/v5.3.3/bootstrap-5.3.3-dist.zip
unzip bootstrap-5.3.3-dist.zip

# 下载 Bootstrap Icons
wget https://github.com/twbs/icons/releases/download/v1.11.3/bootstrap-icons-1.11.3.zip
unzip bootstrap-icons-1.11.3.zip

# 复制到 official 模板
cp bootstrap-5.3.3-dist/css/bootstrap.min.css templates/official/assets/css/
cp bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js templates/official/assets/js/
cp bootstrap-icons-1.11.3/font/bootstrap-icons.min.css templates/official/assets/css/
cp -r bootstrap-icons-1.11.3/font/fonts templates/official/assets/

# 复制到 blog 模板
cp bootstrap-icons-1.11.3/font/bootstrap-icons.min.css templates/blog/assets/css/
cp -r bootstrap-icons-1.11.3/font/fonts templates/blog/assets/

# 清理
rm -rf bootstrap-5.3.3-dist bootstrap-icons-1.11.3 *.zip
```

## 验证安装

### 检查文件是否存在

```bash
# Official 模板
ls templates/official/assets/css/bootstrap.min.css
ls templates/official/assets/css/bootstrap-icons.min.css
ls templates/official/assets/js/bootstrap.bundle.min.js
ls templates/official/assets/fonts/bootstrap-icons.woff2

# Blog 模板
ls templates/blog/assets/css/bootstrap-icons.min.css
ls templates/blog/assets/fonts/bootstrap-icons.woff2
```

### 重新生成静态页面

```bash
# 同步资源到 html 目录
curl -X POST http://localhost:8000/backend/build/sync-assets \
  -H "Authorization: Bearer YOUR_TOKEN"

# 重新生成所有页面
curl -X POST http://localhost:8000/backend/build/all \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 常见问题

### Q1: Bootstrap Icons 图标不显示？

**A**: 检查以下几点：
1. 字体文件是否在正确位置：`assets/fonts/bootstrap-icons.woff2`
2. CSS 文件中的字体路径是否正确：`url("../fonts/...")`
3. 静态资源是否已同步到 `html/` 目录

### Q2: 页面样式错乱？

**A**:
1. 检查 Bootstrap CSS 是否正确加载
2. 清除浏览器缓存
3. 重新同步资源：`/backend/build/sync-assets`

### Q3: 是否可以使用其他版本的 Bootstrap？

**A**: 可以，但需要确保：
1. 版本兼容性（推荐 Bootstrap 5.x）
2. 修改模板中的引用路径
3. 测试所有页面功能正常

## 禁用的 CDN 资源

以下 CDN 已从模板中移除：

- ❌ `https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/...`
- ❌ `https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/...`
- ❌ `https://cdn.jsdelivr.net/npm/aos@2.3.4/...` (已完全移除)
- ❌ `https://unpkg.com/...`
- ❌ `https://cdnjs.cloudflare.com/...`

## 移除的库

### AOS 动画库

**状态**：已完全移除

**原因**：
- 非必需的装饰性功能
- 增加额外依赖
- 所有 `data-aos` 属性保留（不影响功能）

**如需启用**：
1. 下载 AOS 库到本地
2. 在 layout.html 中引入本地文件
3. 在 main.js 中初始化 AOS

---

**最后更新**: 2025-10-28
**维护者**: Claude AI Assistant
