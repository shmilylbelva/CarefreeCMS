# 标签自动创建功能说明

## 功能概述

在创建或编辑文章时，用户可以手动输入新标签名称，系统会自动创建这些标签并加入标签库。

## 实现细节

### 后端实现

**文件**: `backend/app/controller/api/Article.php`

#### 核心方法

**processTagsWithAutoCreate(array $tags): array**

该方法处理标签数组，支持以下两种格式：
1. **标签ID**（整数）- 直接使用已存在的标签
2. **标签名称**（字符串）- 自动查找或创建新标签

**特性**:
- 自动去重
- 长度限制（最大50个字符）
- 并发安全（捕获重复创建异常）
- 自动生成slug（英文转小写，空格替换为连字符）

#### 处理流程

```
用户输入标签
    ↓
[是数字ID?]
    ├─ 是 → 直接使用
    └─ 否 → 是标签名称
            ↓
        [标签已存在?]
            ├─ 是 → 使用现有标签ID
            └─ 否 → 创建新标签
                    ↓
                返回新标签ID
    ↓
返回标签ID数组
```

#### 代码示例

```php
// 在save和update方法中的使用
$tags = $data['tags'] ?? []; // 可以是ID或名称的混合数组

// 处理标签（自动创建新标签）
$tagIds = $this->processTagsWithAutoCreate($tags);

// 关联标签
foreach ($tagIds as $tagId) {
    ArticleTag::create([
        'article_id' => $article->id,
        'tag_id' => $tagId
    ]);
}
```

### 前端使用

#### API调用示例

**创建文章**:
```javascript
import { createArticle } from '@/api/article'

// 标签可以是ID（已存在的标签）或名称（新标签）的混合
const articleData = {
  title: '文章标题',
  content: '文章内容',
  category_id: 1,
  tags: [
    1,              // 已存在的标签ID
    'Vue.js',       // 新标签名称
    'JavaScript',   // 新标签名称
    3               // 已存在的标签ID
  ]
}

const result = await createArticle(articleData)
```

**更新文章**:
```javascript
import { updateArticle } from '@/api/article'

const articleData = {
  title: '更新后的标题',
  tags: [
    'React',        // 新标签
    'TypeScript',   // 新标签
    5               // 已存在的标签ID
  ]
}

const result = await updateArticle(articleId, articleData)
```

#### Vue组件示例

**使用Element Plus的标签输入组件**:

```vue
<template>
  <el-form>
    <el-form-item label="标签">
      <!-- 方式1: 使用el-select支持创建新选项 -->
      <el-select
        v-model="form.tags"
        multiple
        filterable
        allow-create
        default-first-option
        placeholder="选择或输入新标签"
        style="width: 100%"
      >
        <el-option
          v-for="tag in existingTags"
          :key="tag.id"
          :label="tag.name"
          :value="tag.id"
        />
      </el-select>

      <!-- 方式2: 使用el-tag动态输入 -->
      <div>
        <el-tag
          v-for="tag in form.tags"
          :key="tag"
          closable
          @close="handleRemoveTag(tag)"
          style="margin-right: 10px"
        >
          {{ getTagLabel(tag) }}
        </el-tag>
        <el-input
          v-if="inputVisible"
          ref="tagInput"
          v-model="inputValue"
          size="small"
          style="width: 120px"
          @keyup.enter="handleInputConfirm"
          @blur="handleInputConfirm"
        />
        <el-button
          v-else
          size="small"
          @click="showInput"
        >
          + 添加标签
        </el-button>
      </div>
    </el-form-item>
  </el-form>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { getTags } from '@/api/tag'

const form = reactive({
  title: '',
  content: '',
  category_id: null,
  tags: [] // 可以包含ID或名称
})

const existingTags = ref([])
const inputVisible = ref(false)
const inputValue = ref('')
const tagInput = ref(null)

// 加载已存在的标签
onMounted(async () => {
  const result = await getTags({ page: 1, page_size: 100 })
  if (result.code === 0) {
    existingTags.value = result.data.list
  }
})

// 方式2的辅助方法
const getTagLabel = (tag) => {
  if (typeof tag === 'number') {
    const found = existingTags.value.find(t => t.id === tag)
    return found ? found.name : tag
  }
  return tag
}

const handleRemoveTag = (tag) => {
  form.tags = form.tags.filter(t => t !== tag)
}

const showInput = () => {
  inputVisible.value = true
  nextTick(() => {
    tagInput.value?.focus()
  })
}

const handleInputConfirm = () => {
  if (inputValue.value) {
    // 检查是否已存在（按名称）
    const existingTag = existingTags.value.find(
      t => t.name.toLowerCase() === inputValue.value.toLowerCase()
    )

    if (existingTag) {
      // 使用已存在的标签ID
      if (!form.tags.includes(existingTag.id)) {
        form.tags.push(existingTag.id)
      }
    } else {
      // 添加新标签名称
      if (!form.tags.includes(inputValue.value)) {
        form.tags.push(inputValue.value)
      }
    }
  }

  inputVisible.value = false
  inputValue.value = ''
}
</script>
```

## 使用场景

### 场景1: 全部使用已存在的标签
```javascript
tags: [1, 2, 3] // 标签ID数组
```

### 场景2: 全部创建新标签
```javascript
tags: ['Vue 3', 'Composition API', 'TypeScript'] // 标签名称数组
```

### 场景3: 混合使用（推荐）
```javascript
tags: [
  1,              // 已存在的标签
  'Vue 3',        // 新标签
  2,              // 已存在的标签
  'Pinia'         // 新标签
]
```

## 标签创建规则

1. **长度限制**: 标签名称最长50个字符（UTF-8）
2. **自动去重**: 相同的标签只会创建一次
3. **大小写不敏感**: 查找已存在标签时不区分大小写
4. **默认状态**: 新创建的标签默认启用（status = 1）
5. **Slug生成**:
   - 英文标签: 转小写，空格替换为连字符（如 "Vue JS" → "vue-js"）
   - 中文标签: 使用原名称

## 并发处理

系统处理了并发创建相同标签的情况：
```php
try {
    $newTag = Tag::create([...]);
    $tagIds[] = $newTag->id;
} catch (\Exception $e) {
    // 如果创建失败（可能是并发导致的重复）
    // 尝试再次查找
    $existingTag = Tag::where('name', $tagName)->find();
    if ($existingTag) {
        $tagIds[] = $existingTag->id;
    }
}
```

## API响应

创建或更新文章成功后，系统会正常返回文章信息。新创建的标签会自动出现在标签库中。

**成功响应示例**:
```json
{
  "code": 0,
  "message": "文章创建成功",
  "data": {
    "id": 123
  }
}
```

## 数据库变化

创建新标签时，会在`tags`表中插入新记录：

```sql
INSERT INTO tags (name, slug, status, article_count, sort, create_time, update_time)
VALUES ('Vue 3', 'vue-3', 1, 0, 0, NOW(), NOW());
```

同时在`article_tags`中建立关联：

```sql
INSERT INTO article_tags (article_id, tag_id)
VALUES (123, 456);
```

## 后续管理

新创建的标签会立即出现在后台标签管理页面，管理员可以：
- 修改标签名称
- 修改标签slug
- 调整排序
- 启用/禁用标签
- 删除标签

## 注意事项

1. **标签验证**: 前端应该对标签名称进行基本验证（长度、特殊字符等）
2. **性能考虑**: 如果单次提交大量新标签，可能会影响性能
3. **重复检查**: 建议前端在用户输入时提示是否已存在相似标签
4. **权限控制**: 如需限制用户创建标签的权限，可在`processTagsWithAutoCreate`方法中添加权限检查

## 扩展建议

### 1. 添加标签推荐
基于文章内容自动推荐标签：
```javascript
// 调用智能推荐API
const suggestedTags = await getSuggestedTags(articleContent)
```

### 2. 标签合并
当发现相似标签时提示合并：
```javascript
// 检测相似标签
if (isSimilar(newTag, existingTag)) {
  confirm('发现相似标签，是否使用已存在的标签？')
}
```

### 3. 标签分类
为标签添加分类功能：
```javascript
tags: [
  { name: 'Vue 3', category: '前端框架' },
  { name: 'TypeScript', category: '编程语言' }
]
```

## 总结

标签自动创建功能为用户提供了更灵活的文章标注方式，无需预先在标签库中创建标签，极大提升了内容创作效率。系统会智能处理标签的查找、创建和关联，确保数据一致性和并发安全。
