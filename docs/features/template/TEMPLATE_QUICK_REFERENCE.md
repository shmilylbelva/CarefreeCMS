# CMS模板开发快速参考

## ⚠️ 关键注意事项

### 1. JavaScript箭头函数问题（最常见！）

```javascript
// ❌ 错误 - 会导致模板解析失败
array.forEach(item => console.log(item));
element.addEventListener('click', () => {...});

// ✅ 正确 - 使用传统函数
array.forEach(function(item) { console.log(item); });
element.addEventListener('click', function() {...});
```

**原因**: ThinkPHP模板引擎会将 `=>` 识别为PHP数组语法，导致解析错误。

**解决方案**:
1. 使用传统函数语法（推荐）
2. 将JS代码放到外部.js文件
3. 使用 `{literal}...{/literal}` 标签包裹

---

## 📁 必需文件结构

```
templates/your-template/
├── layout.html       # 布局框架（必需）
├── index.html        # 首页（必需）
├── article.html      # 文章详情（必需）
├── category.html     # 分类页（必需）
├── tag.html         # 标签页（必需）
├── page.html        # 单页（必需）
└── assets/          # 静态资源
    ├── css/
    ├── js/
    └── images/
```

---

## 🔤 常用模板语法

### 变量输出
```html
{$变量名}                           基本输出
{$user.username}                   对象属性
{$data['key']}                     数组元素
{$title ?: '默认值'}                默认值
{$status == 1 ? '是' : '否'}        三元运算
```

### 条件判断
```html
{if condition="$is_home"}
    首页内容
{else /}
    其他内容
{/if}
```

### 循环遍历
```html
{volist name="articles" id="article"}
    {$article.title}
{/volist}
```

### 模板继承
```html
<!-- 子模板 -->
{extend name="layout" /}

{block name="content"}
    页面内容
{/block}
```

---

## 🎯 系统变量速查

### 全局配置
```html
{$config.site_name}         # 网站名称
{$config.seo_title}         # SEO标题
{$config.seo_keywords}      # SEO关键词
{$config.seo_description}   # SEO描述
{$config.site_logo}         # 网站Logo
{$config.site_icp}          # ICP备案号
{$config.site_copyright}    # 版权信息

# 或使用 carefree:config 标签
{carefree:config name='site_name' /}
{carefree:config name='seo_title' /}
{carefree:config name='seo_keywords' /}
{carefree:config name='seo_description' /}
```

### 文章对象
```html
{$article.id}              # ID
{$article.title}           # 标题
{$article.content}         # 内容
{$article.cover_image}     # 封面图
{$article.publish_time}    # 发布时间
{$article.view_count}      # 浏览量
{$article.category.name}   # 分类名
{$article.tags}            # 标签数组
```

### 列表数据
```html
{$articles}                # 文章列表
{$categories}              # 分类列表
{$hot_articles}            # 热门文章
{$related_articles}        # 相关文章
{$prev_article}            # 上一篇
{$next_article}            # 下一篇
```

---

## 🐛 常见错误及解决

| 错误 | 原因 | 解决方案 |
|------|------|---------|
| `unexpected token "="` | 使用了箭头函数 | 改用传统函数语法 |
| 变量显示 `{$var}` | 变量未定义 | 使用 `{$var ?: '默认值'}` |
| 图片不显示 | 路径错误 | 使用绝对路径 `/assets/...` |
| 样式不生效 | CSS未加载 | 检查路径和缓存 |

---

## ✅ 开发流程

1. **复制现有模板**
   ```bash
   cp -r templates/default templates/your-template
   ```

2. **修改模板文件**
   - 不使用箭头函数
   - 使用正确的变量名
   - 测试所有页面类型

3. **在后台切换模板**
   - 登录后台 → 系统设置 → 模板管理
   - 选择新模板并保存

4. **生成静态页面**
   - 后台 → 内容管理 → 生成静态
   - 或使用命令: `php think build:static`

5. **测试验证**
   - 检查首页、文章页、分类页
   - 测试响应式布局
   - 验证SEO标签

---

## 🎨 布局示例

### layout.html 基础结构
```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title ?: $config.site_name}</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    {block name="style"}{/block}
</head>
<body>
    <header>
        <!-- 导航 -->
    </header>

    <main>
        {block name="content"}{/block}
    </main>

    <footer>
        <!-- 页脚 -->
    </footer>

    <script src="/assets/js/main.js"></script>
    {block name="script"}{/block}
</body>
</html>
```

### index.html 基础结构
```html
{extend name="layout" /}

{block name="content"}
<div class="container">
    <div class="articles">
        {volist name="articles" id="article"}
        <article>
            <h2>{$article.title}</h2>
            <p>{$article.summary}</p>
            <a href="/article-{$article.id}.html">阅读更多</a>
        </article>
        {/volist}
    </div>

    <aside>
        <!-- 侧边栏 -->
    </aside>
</div>
{/block}
```

---

## 📝 调试技巧

### 查看变量内容
```html
<!-- 开发环境调试 -->
{$article|dump}

<!-- 浏览器console -->
<script>
console.log({$article|json_encode});
</script>
```

### 显示条件调试信息
```html
{if condition="true"}  <!-- 开发时设为true -->
<div style="background:yellow;padding:10px;">
    调试: {$article.title}
</div>
{/if}
```

---

## 🚀 性能优化提示

1. **图片优化**
   - 使用WebP格式
   - 提供占位图
   - 实施懒加载

2. **CSS优化**
   - 关键CSS内联
   - 非关键CSS延迟加载
   - 压缩CSS文件

3. **JavaScript优化**
   - 使用 `defer` 属性
   - 放在body底部
   - 压缩JS文件

4. **缓存策略**
   - 静态资源添加版本号
   - 设置合理的缓存时间

---

## 📚 进一步学习

- 详细文档: `docs/TEMPLATE_DEVELOPMENT_GUIDE.md`
- ThinkPHP文档: https://www.kancloud.cn/manual/thinkphp6_0/1037637
- 参考模板: `templates/default/`, `templates/blog/`

---

**快速参考卡片** | v1.0 | 2025-10-28
