# 自定义字段和内容模型功能文档

## 功能概述

自定义字段和内容模型功能为CMS系统提供了灵活的内容扩展能力，允许用户根据业务需求自定义字段类型和数据结构，无需修改代码即可实现复杂的内容管理需求。

## 功能特性

✅ **11种字段类型** - 支持文本、数字、日期、下拉、单选、多选、富文本、图片、文件等多种类型
✅ **内容模型管理** - 创建和管理自定义内容模型，扩展系统功能
✅ **字段组管理** - 通过字段组实现字段的逻辑分组展示
✅ **灵活配置** - 支持默认值、占位符、帮助文本、验证规则等丰富配置
✅ **系统模型支持** - 为文章、分类、标签、单页等系统模型添加自定义字段
✅ **动态表单渲染** - 自动根据字段定义生成表单控件
✅ **无缝集成** - 已集成到文章编辑页面，自动加载和保存字段值

---

## 数据库设计

### 1. 内容模型表（content_models）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 模型ID |
| name | varchar(50) | 模型名称 |
| table_name | varchar(50) | 数据表名 |
| icon | varchar(50) | 图标（Element Plus图标名） |
| description | varchar(255) | 模型描述 |
| template | varchar(100) | 默认模板 |
| is_system | tinyint | 是否系统模型：0=自定义，1=系统预设 |
| status | tinyint | 状态：0=禁用，1=启用 |
| sort | int | 排序 |
| create_time | datetime | 创建时间 |
| update_time | datetime | 更新时间 |

**系统预设模型**：
- 文章（articles）
- 分类（categories）
- 标签（tags）
- 单页（pages）

### 2. 自定义字段表（custom_fields）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 字段ID |
| name | varchar(50) | 字段名称 |
| field_key | varchar(50) | 字段键名（英文标识） |
| field_type | varchar(20) | 字段类型 |
| model_type | varchar(20) | 关联模型类型 |
| model_id | int | 内容模型ID |
| group_name | varchar(50) | 字段组名 |
| options | text | 字段选项（JSON格式） |
| default_value | varchar(255) | 默认值 |
| placeholder | varchar(100) | 占位符文本 |
| help_text | varchar(255) | 帮助说明 |
| validation_rules | varchar(255) | 验证规则（JSON格式） |
| is_required | tinyint | 是否必填 |
| is_searchable | tinyint | 是否可搜索 |
| is_show_in_list | tinyint | 是否在列表显示 |
| sort | int | 排序 |
| status | tinyint | 状态 |
| create_time | datetime | 创建时间 |
| update_time | datetime | 更新时间 |

### 3. 自定义字段值表（custom_field_values）

| 字段名 | 类型 | 说明 |
|--------|------|------|
| id | int | 值ID |
| field_id | int | 字段ID |
| entity_type | varchar(20) | 实体类型 |
| entity_id | int | 实体ID |
| field_value | text | 字段值 |
| create_time | datetime | 创建时间 |
| update_time | datetime | 更新时间 |

---

## 字段类型说明

### 1. 文本类型

**单行文本（text）**
- 适用于：标题、名称、链接等短文本
- 控件：`<el-input>`
- 存储：字符串

**多行文本（textarea）**
- 适用于：描述、备注等多行文本
- 控件：`<el-input type="textarea">`
- 存储：字符串

**富文本（richtext）**
- 适用于：详细内容、介绍等富文本内容
- 控件：TinyMCE编辑器
- 存储：HTML字符串

### 2. 数字类型

**数字（number）**
- 适用于：价格、数量、评分等数值
- 控件：`<el-input-number>`
- 存储：数字字符串

### 3. 日期类型

**日期（date）**
- 适用于：生日、截止日期等日期
- 控件：`<el-date-picker type="date">`
- 存储：YYYY-MM-DD格式

**日期时间（datetime）**
- 适用于：发布时间、活动时间等精确时间
- 控件：`<el-date-picker type="datetime">`
- 存储：YYYY-MM-DD HH:mm:ss格式

### 4. 选择类型

**下拉选择（select）**
- 适用于：分类选择、状态选择等单选
- 控件：`<el-select>`
- 存储：选项值（字符串）
- 配置：需配置选项列表

**单选按钮（radio）**
- 适用于：性别、类型等少量选项的单选
- 控件：`<el-radio-group>`
- 存储：选项值（字符串）
- 配置：需配置选项列表

**多选框（checkbox）**
- 适用于：标签选择、权限选择等多选
- 控件：`<el-checkbox-group>`
- 存储：JSON数组
- 配置：需配置选项列表

### 5. 文件类型

**图片上传（image）**
- 适用于：产品图片、轮播图等图片
- 控件：`<el-upload>` + 图片预览
- 存储：图片URL路径
- 限制：默认10MB以内

**文件上传（file）**
- 适用于：附件、文档等文件
- 控件：`<el-upload>` + 文件链接
- 存储：文件URL路径
- 限制：默认10MB以内

---

## 后端实现

### 模型类

**ContentModel.php** - 内容模型模型
```php
class ContentModel extends Model
{
    // 关联自定义字段
    public function customFields()
    {
        return $this->hasMany(CustomField::class, 'model_id', 'id')
            ->where('model_type', 'custom')
            ->order('sort', 'asc');
    }
}
```

**CustomField.php** - 自定义字段模型
```php
class CustomField extends Model
{
    // 字段类型常量
    const TYPE_TEXT = 'text';
    const TYPE_NUMBER = 'number';
    const TYPE_DATE = 'date';
    // ... 其他类型

    // 获取所有字段类型
    public static function getFieldTypes()
    {
        return [
            ['value' => 'text', 'label' => '单行文本'],
            // ... 其他类型
        ];
    }
}
```

**CustomFieldValue.php** - 字段值模型
```php
class CustomFieldValue extends Model
{
    // 获取实体的所有字段值
    public static function getEntityValues($entityType, $entityId)
    {
        // 返回键值对数组
    }

    // 保存实体的字段值
    public static function saveEntityValues($entityType, $entityId, $fieldValues)
    {
        // 批量保存或更新字段值
    }
}
```

### 控制器

**ContentModelController.php** - 内容模型管理
- `index()` - 获取模型列表
- `all()` - 获取所有模型（不分页）
- `read($id)` - 获取模型详情
- `save()` - 创建模型
- `update($id)` - 更新模型
- `delete($id)` - 删除模型

**CustomFieldController.php** - 自定义字段管理
- `index()` - 获取字段列表
- `getByModel()` - 根据模型获取字段
- `read($id)` - 获取字段详情
- `save()` - 创建字段
- `update($id)` - 更新字段
- `delete($id)` - 删除字段
- `getFieldTypes()` - 获取字段类型列表
- `getModelTypes()` - 获取模型类型列表
- `getEntityValues()` - 获取实体字段值
- `saveEntityValues()` - 保存实体字段值

### API路由

```php
// 内容模型管理
Route::get('content-models/all', 'ContentModelController@all');
Route::resource('content-models', 'ContentModelController');

// 自定义字段管理
Route::get('custom-fields/field-types', 'CustomFieldController@getFieldTypes');
Route::get('custom-fields/model-types', 'CustomFieldController@getModelTypes');
Route::get('custom-fields/by-model', 'CustomFieldController@getByModel');
Route::get('custom-fields/entity-values', 'CustomFieldController@getEntityValues');
Route::post('custom-fields/entity-values', 'CustomFieldController@saveEntityValues');
Route::resource('custom-fields', 'CustomFieldController');
```

---

## 前端实现

### API服务

**contentModel.js** - 内容模型API
```javascript
export function getContentModelList(params)
export function getAllContentModels()
export function getContentModelDetail(id)
export function createContentModel(data)
export function updateContentModel(id, data)
export function deleteContentModel(id)
```

**customField.js** - 自定义字段API
```javascript
export function getCustomFieldList(params)
export function getFieldsByModel(modelType, modelId)
export function getCustomFieldDetail(id)
export function createCustomField(data)
export function updateCustomField(id, data)
export function deleteCustomField(id)
export function getFieldTypes()
export function getModelTypes()
export function getEntityValues(entityType, entityId)
export function saveEntityValues(entityType, entityId, fieldValues)
```

### 页面组件

**ContentModel/List.vue** - 内容模型列表
- 模型列表展示
- 创建/编辑模型对话框
- 系统模型不可编辑/删除
- 跳转到字段管理

**CustomField/List.vue** - 自定义字段列表
- 字段列表展示
- 按模型类型筛选
- 创建/编辑字段对话框
- 字段选项配置（select/radio/checkbox）
- 字段属性配置（必填、可搜索、列表显示等）

**CustomFieldRenderer.vue** - 动态字段渲染组件
- 根据字段类型动态渲染表单控件
- 支持所有11种字段类型
- 自动处理字段验证
- 自动处理文件上传
- 显示帮助文本

### 集成示例（文章编辑）

```vue
<template>
  <!-- 自定义字段 -->
  <el-divider v-if="customFields.length > 0">
    自定义字段
  </el-divider>
  <CustomFieldRenderer
    v-if="customFields.length > 0"
    :fields="customFields"
    v-model="customFieldValues"
  />
</template>

<script setup>
import { getFieldsByModel, getEntityValues, saveEntityValues } from '@/backend/customField'
import CustomFieldRenderer from '@/components/CustomFieldRenderer.vue'

const customFields = ref([])
const customFieldValues = ref({})

// 加载字段定义
const loadCustomFields = async () => {
  const res = await getFieldsByModel('article')
  customFields.value = res.data.fields || []
}

// 加载字段值
const loadCustomFieldValues = async () => {
  const res = await getEntityValues('article', articleId)
  customFieldValues.value = res.data || {}
}

// 保存字段值
const saveCustomFields = async (articleId) => {
  await saveEntityValues('article', articleId, customFieldValues.value)
}
</script>
```

---

## 使用场景

### 场景1：为文章添加自定义字段

**需求**：文章需要添加"产品价格"、"产品规格"、"推荐等级"等字段

**步骤**：
1. 进入"自定义字段"管理页面
2. 点击"新建字段"
3. 配置字段信息：
   - 字段名称：产品价格
   - 字段键名：product_price
   - 字段类型：数字
   - 模型类型：文章
   - 是否必填：是
4. 保存后，在文章编辑页面即可看到该字段

### 场景2：创建自定义内容模型

**需求**：需要管理"产品"内容，包含产品名称、价格、库存等信息

**步骤**：
1. 进入"内容模型"管理页面
2. 点击"新建模型"
3. 配置模型信息：
   - 模型名称：产品
   - 数据表名：custom_products
   - 图标：Box
   - 描述：产品管理模型
4. 保存后，进入"自定义字段"管理
5. 为产品模型添加字段：
   - 产品名称（文本）
   - 产品价格（数字）
   - 产品库存（数字）
   - 产品图片（图片）
   - 产品详情（富文本）

### 场景3：字段分组展示

**需求**：字段较多时，希望按功能分组展示

**步骤**：
1. 在创建/编辑字段时，填写"字段组"名称
2. 例如：
   - 基本信息：产品名称、产品价格
   - 库存信息：库存数量、预警数量
   - 详细信息：产品详情、产品规格
3. 系统会自动按字段组分组展示

### 场景4：下拉选择字段配置

**需求**：需要一个"产品状态"下拉字段

**步骤**：
1. 创建字段，字段类型选择"下拉选择"
2. 在"字段选项"区域配置选项：
   - 值：on_sale，显示文本：在售
   - 值：off_sale，显示文本：下架
   - 值：pre_sale，显示文本：预售
3. 保存后，用户可以从下拉框选择产品状态

---

## 注意事项

### 1. 字段键名规范
- 只能包含字母、数字和下划线
- 不能以数字开头
- 建议使用小写字母和下划线，如：product_price
- 同一模型下键名不能重复

### 2. 字段类型选择
- 字段类型创建后不能修改
- 选择合适的字段类型很重要
- 数字类型用于需要计算的字段
- 富文本用于需要格式化的内容

### 3. 系统模型限制
- 系统预设模型不能编辑和删除
- 可以为系统模型添加自定义字段
- 系统模型包括：文章、分类、标签、单页

### 4. 数据存储
- 所有字段值都以文本形式存储
- 多选框（checkbox）存储为JSON数组
- 图片/文件存储URL路径
- 需要数值计算时需要转换类型

### 5. 性能考虑
- 字段值存储在独立表中
- 查询时需要JOIN关联
- 建议根据实际需求控制字段数量
- 可以通过索引优化查询性能

### 6. 删除操作
- 删除字段会同时删除该字段的所有值
- 删除模型会删除该模型下的所有字段定义
- 删除操作不可恢复，请谨慎操作

---

## 扩展功能建议

- [ ] **字段验证规则** - 支持更复杂的验证规则配置
- [ ] **字段权限控制** - 控制不同角色对字段的查看/编辑权限
- [ ] **字段条件显示** - 根据其他字段值决定字段是否显示
- [ ] **字段导入导出** - 批量导入导出字段配置
- [ ] **字段模板** - 预设常用字段组合模板
- [ ] **关联字段** - 支持关联其他模型的数据
- [ ] **计算字段** - 根据其他字段自动计算值
- [ ] **字段历史记录** - 记录字段值的修改历史

---

**更新时间**: 2025-10-18
**版本**: 1.0
