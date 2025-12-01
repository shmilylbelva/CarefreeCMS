# 模板指定功能实现总结

## 已完成的工作

### 1. 数据库更新 ✅
- 为 `categories` 表添加了 `template` 字段
- 在 `site_config` 表中添加了 `index_template` 配置项
- 向 `templates` 表插入了默认模板记录

**执行的 SQL 文件**: `database_update_template.sql`

### 2. 后端 API 更新 ✅

#### Build.php 控制器修改
- **`loadConfig()`** - 添加了 `index_template` 配置项加载
- **`index()`** - 支持使用自定义首页模板
- **`category()`** - 支持使用分类自定义模板
- **`page()`** - 支持使用单页自定义模板

#### 新增 Template 控制器
**文件**: `backend/app/controller/backend/Template.php`
- `list()` - 获取数据库中的模板列表
- `scan()` - 扫描 templates 目录中的实际模板文件

#### 路由更新
**文件**: `backend/route/api.php`
- `GET /backend/templates` - 获取模板列表
- `GET /backend/templates/scan` - 扫描模板文件

### 3. 前端更新 ✅

#### API 调用
**文件**: `frontend/src/backend/template.js`
```javascript
- getTemplates() - 获取模板列表
- scanTemplates() - 扫描模板文件
```

#### 分类管理界面更新
**文件**: `frontend/src/views/category/List.vue`
- 添加了模板选择下拉框
- 在表单中添加 `template` 字段
- 加载模板列表

## 待完成的工作

### 4. 单页管理界面更新 🔄
**需要修改**: `frontend/src/views/page/Edit.vue`

添加模板选择功能：
```vue
<el-form-item label="模板">
  <el-select v-model="form.template" placeholder="请选择模板">
    <el-option label="默认模板(page)" value="page" />
    <el-option
      v-for="tpl in templates"
      :key="tpl.template_key"
      :label="tpl.name"
      :value="tpl.template_key"
    />
  </el-select>
</el-form-item>
```

### 5. 系统配置界面更新 🔄
**需要修改**: `frontend/src/views/config/Index.vue`

在"模板配置"分组中添加首页模板选择：
```vue
<el-form-item label="首页模板">
  <el-select v-model="config.index_template" placeholder="请选择首页模板">
    <el-option label="默认首页模板(index)" value="index" />
    <el-option
      v-for="tpl in templates"
      :key="tpl.template_key"
      :label="tpl.name"
      :value="tpl.template_key"
    />
  </el-select>
</el-form-item>
```

## 使用说明

### 如何为分类指定模板
1. 进入"分类管理"
2. 点击"编辑"按钮
3. 在"模板"下拉框中选择想要使用的模板
4. 保存后重新生成该分类的静态页面

### 如何为单页指定模板
1. 进入"单页管理"
2. 编辑某个单页
3. 在"模板"字段选择想要使用的模板
4. 保存后重新生成该单页

### 如何为首页指定模板
1. 进入"系统配置"
2. 找到"首页模板"设置项
3. 选择想要使用的模板
4. 保存后重新生成首页

### 模板文件位置
所有模板文件存放在：`backend/templates/` 目录

例如：
- `backend/templates/index.html` - 首页模板
- `backend/templates/category.html` - 分类模板
- `backend/templates/page.html` - 单页模板
- `backend/templates/tag.html` - 标签模板

### 创建自定义模板
1. 在 `backend/templates/` 目录创建新的 `.html` 文件
2. 在数据库 `templates` 表中添加记录
3. 模板就会出现在管理界面的下拉列表中

## 技术细节

### 模板渲染逻辑
```php
// 首页
$template = $this->config['index_template'] ?? 'index';
$content = View::fetch('/' . $template, $data);

// 分类
$template = $category->template ?? 'category';
$content = View::fetch('/' . $template, $data);

// 单页
$template = $page->template ?? 'page';
$content = View::fetch('/' . $template, $data);
```

### 默认模板
如果没有指定模板，系统会使用以下默认值：
- 首页: `index`
- 分类: `category`
- 单页: `page`
- 标签: `tag`
- 文章详情: `article`
- 文章列表: `articles`
