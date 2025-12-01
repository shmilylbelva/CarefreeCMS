# 多站点模板系统迁移指南

## 一、系统概述

本次升级实现了完整的多站点模板系统，将原先的单站点模板功能扩展为支持模板包的多站点模板管理系统。

### 主要变更

1. **新增表结构**
   - `template_packages` - 模板包管理表
   - `site_template_config` - 站点模板配置表
   - `site_template_overrides` - 站点模板覆盖表

2. **修改表结构**
   - `templates` 表新增字段：`package_id`, `template_type`, `is_package_default`, `parent_template_id` 等

3. **新增功能模块**
   - 模板包管理（CRUD）
   - 站点模板配置
   - 模板覆盖机制

## 二、数据迁移

### 2.1 数据库迁移步骤

1. **执行数据库脚本**

   ```bash
   # 1. 创建新表
   mysql -uroot -p cms_database < docs/database_template_packages.sql

   # 2. 升级 templates 表
   mysql -uroot -p cms_database < docs/database_templates_upgrade.sql
   ```

2. **验证迁移结果**

   ```sql
   -- 检查默认模板包是否创建
   SELECT * FROM template_packages WHERE id = 1;

   -- 检查模板是否已关联到默认包
   SELECT id, template_name, package_id, template_type
   FROM templates
   WHERE package_id = 1;

   -- 应该看到 7 条模板记录
   ```

### 2.2 系统配置迁移

#### 现状分析

当前系统配置存储在 `system_config` 表中（site_id=0 表示全局配置），包含以下配置项：

- 网站基本信息（logo, favicon, 网站名称等）
- SEO配置（title, keywords, description）
- 联系方式
- 第三方代码
- 备案信息

#### 迁移建议

**方案1：保持双轨制（推荐用于过渡期）**

```sql
-- 系统配置表保留作为默认值
-- 站点可以选择使用系统配置或自定义配置

-- 站点表已包含必要字段：
-- logo, favicon, description, keywords, copyright,
-- icp_no, police_no, contact_email, contact_phone, etc.
```

**方案2：完全迁移到站点配置**

```sql
-- 将全局配置迁移到主站点
UPDATE sites
SET
  logo = (SELECT config_value FROM system_config WHERE config_key = 'site_logo' AND site_id = 0),
  favicon = (SELECT config_value FROM system_config WHERE config_key = 'site_favicon' AND site_id = 0),
  seo_title = (SELECT config_value FROM system_config WHERE config_key = 'seo_title' AND site_id = 0),
  seo_keywords = (SELECT config_value FROM system_config WHERE config_key = 'seo_keywords' AND site_id = 0),
  seo_description = (SELECT config_value FROM system_config WHERE config_key = 'seo_description' AND site_id = 0),
  copyright = (SELECT config_value FROM system_config WHERE config_key = 'web_copyright' AND site_id = 0),
  icp_no = (SELECT config_value FROM system_config WHERE config_key = 'web_recordnum' AND site_id = 0)
WHERE site_type = 1; -- 主站点

-- 其他站点可以复制主站配置或单独设置
```

## 三、模板包使用指南

### 3.1 创建模板包

1. 访问后台 **模板包管理** 页面
2. 点击"添加模板包"
3. 填写以下信息：
   - 代码：唯一标识（如：`theme_blog_2024`）
   - 名称：显示名称（如：博客主题 2024）
   - 描述：模板包说明
   - 默认配置：JSON格式的可配置项

示例默认配置：
```json
{
  "primary_color": "#409EFF",
  "font_family": "Microsoft YaHei",
  "font_size": "14px",
  "header_height": "60px",
  "footer_text": "© 2024 版权所有"
}
```

### 3.2 为站点配置模板包

1. 进入 **多站点管理** 页面
2. 编辑目标站点
3. 切换到"模板配置"标签
4. 选择模板包
5. 可选：自定义配置（覆盖默认值）

### 3.3 模板解析优先级

系统按以下优先级解析模板：

```
站点模板覆盖 > 站点模板包 > 全局默认模板包
```

#### 使用场景示例

**场景1：多站点使用相同模板**
- 创建一个模板包"企业主题"
- 所有子站点都配置使用该模板包
- 需要修改模板时，只需更新模板包

**场景2：多站点使用不同模板**
- 创建多个模板包（如：新闻主题、博客主题、电商主题）
- 不同站点选择不同的模板包
- 每个站点可自定义配色等配置

**场景3：特定页面使用不同模板**
- 站点整体使用模板包A
- 但首页需要特殊设计
- 使用模板覆盖功能，单独为首页指定模板

## 四、API 接口说明

### 4.1 模板包管理 API

```http
# 获取模板包列表
GET /api/template-packages?page=1&page_size=20

# 获取所有可用模板包（下拉选择）
GET /api/template-packages/all?site_id=1

# 创建模板包
POST /api/template-packages
{
  "code": "theme_news",
  "name": "新闻主题",
  "description": "适用于新闻资讯类网站",
  "default_config": {...}
}

# 更新模板包
PUT /api/template-packages/{id}

# 删除模板包
DELETE /api/template-packages/{id}

# 复制模板包
POST /api/template-packages/{id}/copy
{
  "name": "新闻主题_副本",
  "code": "theme_news_copy"
}
```

### 4.2 站点模板配置 API

```http
# 获取站点的模板配置
GET /api/sites/{id}/template-config

# 设置站点的模板包
POST /api/sites/{id}/template-package
{
  "package_id": 1
}

# 更新站点的自定义配置
PUT /api/sites/{id}/template-config
{
  "custom_config": {
    "primary_color": "#FF0000"
  }
}

# 获取站点的模板覆盖列表
GET /api/sites/{id}/template-overrides

# 设置模板覆盖
POST /api/sites/{id}/template-override
{
  "template_type": "index",
  "template_id": 5,
  "priority": 10
}

# 移除模板覆盖
DELETE /api/sites/{id}/template-override
{
  "template_type": "index"
}
```

## 五、前端路由

新增路由：

```javascript
{
  path: 'template-packages',
  name: 'TemplatePackageList',
  component: () => import('@/views/templatePackage/List.vue'),
  meta: { title: '模板包管理' }
}
```

站点管理页面新增"模板配置"标签页（仅编辑时显示）

## 六、注意事项

### 6.1 兼容性

- 原有模板功能保持兼容
- 未配置模板包的站点将使用默认模板包（ID=1）
- 系统会自动将现有模板归入默认包

### 6.2 性能优化建议

- 模板包配置建议使用缓存
- 站点数量较多时，建议按需加载模板配置
- 定期清理无用的模板包

### 6.3 安全注意

- 系统内置模板包（`is_system=1`）受保护，不允许删除或修改
- 删除模板包前会检查是否有站点在使用
- 模板自定义配置需验证 JSON 格式

## 七、后续规划

### 7.1 模板市场（预留）

- 模板包导入/导出功能
- 在线模板市场
- 一键安装模板包

### 7.2 可视化配置

- 模板配置可视化编辑器
- 实时预览模板效果
- 拖拽式布局配置

### 7.3 模板继承

- 支持模板继承机制
- 子模板可继承父模板并覆盖部分内容
- 提高模板复用性

## 八、故障排除

### 8.1 模板包列表为空

**问题**：访问模板包管理页面，列表为空

**解决**：
```sql
-- 检查默认模板包是否存在
SELECT * FROM template_packages WHERE id = 1;

-- 如不存在，手动插入
INSERT INTO template_packages (id, code, name, description, is_system, status, sort)
VALUES (1, 'default', '默认模板包', '系统默认模板包，包含所有基础模板', 1, 1, 0);
```

### 8.2 站点模板配置不显示

**问题**：编辑站点时，看不到"模板配置"标签

**原因**：模板配置标签仅在编辑现有站点时显示（`v-if="isEdit"`）

**解决**：新建站点后，重新编辑该站点即可看到模板配置选项

### 8.3 模板覆盖不生效

**问题**：设置了模板覆盖，但前台仍使用默认模板

**检查步骤**：
1. 确认覆盖的模板ID存在且状态为启用
2. 确认模板类型（template_type）正确
3. 检查站点是否正确配置了模板包
4. 清除站点缓存：`POST /api/sites/clear-cache`

## 九、联系支持

如遇到问题，请检查：

1. 数据库迁移脚本是否全部执行成功
2. 后端模型和控制器是否部署正确
3. 前端路由是否正确配置
4. API 路由是否正确注册

技术支持：请在系统issue中提交问题
