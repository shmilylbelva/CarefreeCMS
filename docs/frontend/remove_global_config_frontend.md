# 前端管理页面修改 - 移除全局配置，改用站点配置

## 修改说明

配合后端API的修改，前端管理页面也进行了相应调整：
- 全局配置页面改为系统级配置，添加提示引导用户使用站点管理
- 站点管理页面添加评论相关配置项
- 保留全局配置页面用于系统级配置（如模板套装管理）

## 修改的文件

### 1. site/List.vue - 站点管理页面
**位置**: `frontend/src/views/site/List.vue`

#### 修改内容

**1. 在"核心设置"标签页添加评论配置项** (line 298-349):

```vue
<el-tab-pane label="核心设置" name="core">
  <el-divider>模板设置</el-divider>

  <el-form-item label="首页模板">
    <el-input v-model="form.index_template" placeholder="默认: index" />
    <div class="form-tip">用于生成首页的模板文件名（不含扩展名）</div>
  </el-form-item>

  <el-divider>系统功能</el-divider>

  <el-form-item label="回收站">
    <el-radio-group v-model="form.recycle_bin_enable">
      <el-radio value="open">开启</el-radio>
      <el-radio value="close">关闭</el-radio>
    </el-radio-group>
    <div class="form-tip">开启后，删除内容将进入回收站；关闭则直接物理删除</div>
  </el-form-item>

  <el-form-item label="文档副栏目">
    <el-radio-group v-model="form.article_sub_category">
      <el-radio value="open">开启</el-radio>
      <el-radio value="close">关闭</el-radio>
    </el-radio-group>
    <div class="form-tip">开启后，一篇文章可以同时属于多个分类（一个主分类+多个副分类）</div>
  </el-form-item>

  <el-divider>评论设置</el-divider>

  <el-form-item label="允许游客评论">
    <el-radio-group v-model="form.enable_guest_comment">
      <el-radio :value="true">允许</el-radio>
      <el-radio :value="false">禁止</el-radio>
    </el-radio-group>
    <div class="form-tip">允许后，未登录用户也可以发表评论（需提供昵称和邮箱）</div>
  </el-form-item>

  <el-form-item label="评论自动审核">
    <el-radio-group v-model="form.auto_approve">
      <el-radio :value="true">开启</el-radio>
      <el-radio :value="false">关闭</el-radio>
    </el-radio-group>
    <div class="form-tip">开启后，评论将自动审核通过；关闭则需要人工审核</div>
  </el-form-item>

  <el-form-item label="敏感词过滤">
    <el-radio-group v-model="form.enable_sensitive_filter">
      <el-radio :value="true">开启</el-radio>
      <el-radio :value="false">关闭</el-radio>
    </el-radio-group>
    <div class="form-tip">开启后，评论内容将进行敏感词过滤和替换</div>
  </el-form-item>
</el-tab-pane>
```

**2. 在表单数据中添加评论配置字段** (line 489-492):

```javascript
const form = reactive({
  // ... 其他字段
  // 核心设置
  index_template: 'index',
  recycle_bin_enable: 'open',
  article_sub_category: 'close',
  // 评论设置
  enable_guest_comment: true,
  auto_approve: false,
  enable_sensitive_filter: true,
  status: 1,
  sort: 0
})
```

### 2. config/Index.vue - 全局配置页面
**位置**: `frontend/src/views/config/Index.vue`

#### 修改内容

**1. 在页面顶部添加警告提示** (line 3-24):

```vue
<el-alert
  type="warning"
  :closable="false"
  style="margin-bottom: 20px;"
  title="功能调整提示"
>
  <template #default>
    <div style="line-height: 1.8;">
      <p style="margin: 0 0 10px 0;"><strong>多站点配置已迁移至站点管理</strong></p>
      <p style="margin: 0 0 5px 0;">以下配置已废弃，请前往 <strong>站点管理 → 编辑站点 → 核心设置</strong> 进行配置：</p>
      <ul style="margin: 5px 0; padding-left: 20px;">
        <li>回收站开关</li>
        <li>文档副栏目开关</li>
        <li>评论相关设置（允许游客评论、自动审核、敏感词过滤）</li>
      </ul>
      <p style="margin: 10px 0 0 0; color: #E6A23C;">
        <el-icon><InfoFilled /></el-icon>
        每个站点可以独立配置，实现多站点差异化管理
      </p>
    </div>
  </template>
</el-alert>
```

**2. 修改卡片标题** (line 27-29):

```vue
<el-card>
  <template #header>
    <h3>系统级配置</h3>
  </template>
```

**3. 简化"核心设置"标签页，改名为"模板管理"** (line 138-170):

**修改前**:
- 标签名: "核心设置"
- 包含: 模板套装、首页模板、回收站、文档副栏目

**修改后**:
- 标签名: "模板管理"
- 仅包含: 模板套装管理
- 移除了回收站和文档副栏目配置

```vue
<el-tab-pane label="模板管理" name="core">
  <el-alert type="info" :closable="false" style="margin-bottom: 20px;">
    模板套装配置用于后台管理，站点使用的模板请到站点管理中配置
  </el-alert>

  <el-form :model="form" label-width="140px" style="max-width: 800px;">
    <el-divider>模板套装管理</el-divider>

    <el-form-item label="当前模板套装">
      <!-- 模板套装选择器 -->
    </el-form-item>
  </el-form>
</el-tab-pane>
```

**4. 添加InfoFilled图标导入** (line 257):

```javascript
import { InfoFilled } from '@element-plus/icons-vue'
```

## 配置项说明

### 站点管理 - 核心设置标签页

| 配置项 | 类型 | 默认值 | 说明 |
|--------|------|--------|------|
| 首页模板 | String | 'index' | 用于生成首页的模板文件名 |
| 回收站 | String | 'open' | 'open' = 开启, 'close' = 关闭 |
| 文档副栏目 | String | 'close' | 'open' = 开启, 'close' = 关闭 |
| 允许游客评论 | Boolean | true | true = 允许, false = 禁止 |
| 评论自动审核 | Boolean | false | true = 开启, false = 关闭 |
| 敏感词过滤 | Boolean | true | true = 开启, false = 关闭 |

### 数据类型说明

注意评论配置使用的是布尔值（Boolean），而不是字符串：
- `enable_guest_comment`: Boolean (true/false)
- `auto_approve`: Boolean (true/false)
- `enable_sensitive_filter`: Boolean (true/false)

与之对比，系统功能配置使用字符串：
- `recycle_bin_enable`: String ('open'/'close')
- `article_sub_category`: String ('open'/'close')

## 用户界面效果

### 全局配置页面
- ⚠️ 顶部显示黄色警告提示框
- 📝 提示用户配置已迁移到站点管理
- 🔗 明确指引操作路径："站点管理 → 编辑站点 → 核心设置"
- 🎯 列出已废弃的具体配置项
- 💡 说明多站点差异化管理的优势

### 站点管理页面
- ➕ 核心设置标签页新增"评论设置"分组
- 📋 三个评论相关配置项（允许游客评论、评论自动审核、敏感词过滤）
- 📝 每个配置项都有清晰的说明文字
- 🔧 使用Radio单选组，界面清晰易用

## 操作指引

### 如何配置站点设置

1. **进入站点管理**
   - 导航：系统管理 → 站点管理

2. **编辑站点**
   - 点击站点列表中的"编辑"按钮

3. **配置核心设置**
   - 切换到"核心设置"标签页
   - 配置模板、系统功能、评论设置
   - 点击"确定"保存

### 配置示例

**示例1：开启游客评论但需人工审核**
```
允许游客评论: 允许
评论自动审核: 关闭
敏感词过滤: 开启
```

**示例2：仅允许注册用户评论**
```
允许游客评论: 禁止
评论自动审核: 开启
敏感词过滤: 开启
```

**示例3：完全开放的评论系统**
```
允许游客评论: 允许
评论自动审核: 开启
敏感词过滤: 开启
```

## 向后兼容

- ✅ 全局配置页面保留，用于系统级配置
- ✅ 原有的"网站信息"和"附件扩展"标签页保持不变
- ✅ 路由未改变，所有链接仍然有效
- ✅ 提供明确的迁移引导，用户体验平滑

## 数据流程

### 保存站点配置
```
用户在站点管理页面编辑
    ↓
修改form中的配置字段
    ↓
提交到后端 PUT /api/sites/{id}
    ↓
后端SiteService处理配置
    ↓
存储到sites表的config字段（JSON）
```

### 读取站点配置
```
打开站点编辑对话框
    ↓
调用后端 GET /api/sites/{id}
    ↓
获取站点数据包括config字段
    ↓
解析并填充到form
    ↓
显示在核心设置标签页
```

## 注意事项

1. **数据类型一致性**
   - 评论配置使用布尔值（Boolean）
   - 系统功能使用字符串（'open'/'close'）
   - 前后端保持一致

2. **默认值设置**
   - 确保form中设置了合理的默认值
   - 新建站点时会使用这些默认值

3. **表单验证**
   - 当前未对评论配置项添加验证规则
   - 如需要可在rules中添加

4. **后端兼容**
   - 确保后端SiteService正确处理这些字段
   - 确保后端在创建/更新站点时保存到config字段

## 测试建议

### 1. 测试站点编辑
```
1. 打开站点管理页面
2. 点击"编辑"按钮编辑站点
3. 切换到"核心设置"标签页
4. 修改评论配置
5. 保存并验证
```

### 2. 测试新建站点
```
1. 点击"添加站点"按钮
2. 填写基本信息
3. 切换到"核心设置"标签页
4. 查看默认值是否正确
5. 修改配置并保存
6. 重新打开编辑，验证是否保存成功
```

### 3. 测试全局配置页面
```
1. 打开全局配置页面
2. 查看顶部是否显示警告提示
3. 验证提示内容是否清晰
4. 验证"模板管理"标签页功能正常
```

## 相关文档

- [后端API修改文档](../../backend/docs/remove_global_config_controller.md)
- [站点管理API文档](../../backend/docs/site_management.md)
- [多站点支持文档](../../backend/docs/multisite_support.md)
