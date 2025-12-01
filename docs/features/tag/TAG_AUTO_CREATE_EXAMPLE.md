# 标签自动创建功能 - 测试示例

## 快速测试

### 使用Postman/curl测试

#### 1. 创建文章（包含新标签）

**请求**:
```http
POST http://localhost:8000/api/articles
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "title": "Vue 3 完全指南",
  "content": "<p>这是一篇关于Vue 3的完整教程...</p>",
  "description": "学习Vue 3的最佳实践",
  "category_id": 1,
  "tags": [
    1,                    // 已存在的标签ID
    "Vue 3",              // 新标签（将自动创建）
    "Composition API",    // 新标签（将自动创建）
    "TypeScript",         // 新标签（将自动创建）
    2                     // 已存在的标签ID
  ],
  "status": 1
}
```

**响应**:
```json
{
  "code": 0,
  "message": "文章创建成功",
  "data": {
    "id": 123
  }
}
```

**验证**:
1. 访问 `GET /api/tags` 查看标签列表，应该能看到新创建的标签：
   - "Vue 3"
   - "Composition API"
   - "TypeScript"

2. 访问 `GET /api/articles/123` 查看文章详情，应该能看到5个关联的标签

#### 2. 更新文章（添加更多新标签）

**请求**:
```http
PUT http://localhost:8000/api/articles/123
Authorization: Bearer {your_token}
Content-Type: application/json

{
  "tags": [
    "Vue 3",           // 已存在（上次创建的）
    "Vite",            // 新标签
    "Pinia",           // 新标签
    3                  // 已存在的标签ID
  ]
}
```

**响应**:
```json
{
  "code": 0,
  "message": "文章更新成功",
  "data": {
    "id": 123
  }
}
```

**验证**:
1. 标签库中新增 "Vite" 和 "Pinia"
2. 文章123现在关联3个标签（旧标签已被替换）

## 前端集成示例

### Vue 3 完整示例

```vue
<template>
  <div class="article-form">
    <el-form :model="form" ref="formRef" :rules="rules" label-width="100px">
      <!-- 标题 -->
      <el-form-item label="标题" prop="title">
        <el-input v-model="form.title" placeholder="请输入文章标题" />
      </el-form-item>

      <!-- 分类 -->
      <el-form-item label="分类" prop="category_id">
        <el-select v-model="form.category_id" placeholder="请选择分类">
          <el-option
            v-for="cat in categories"
            :key="cat.id"
            :label="cat.name"
            :value="cat.id"
          />
        </el-select>
      </el-form-item>

      <!-- 标签（支持自动创建） -->
      <el-form-item label="标签">
        <el-select
          v-model="form.tags"
          multiple
          filterable
          allow-create
          default-first-option
          :reserve-keyword="false"
          placeholder="选择或输入新标签（按回车添加）"
          style="width: 100%"
          @change="handleTagChange"
        >
          <el-option
            v-for="tag in allTags"
            :key="tag.id"
            :label="tag.name"
            :value="tag.id"
          >
            <span style="float: left">{{ tag.name }}</span>
            <span style="float: right; color: #8492a6; font-size: 13px">
              {{ tag.article_count }} 篇文章
            </span>
          </el-option>
        </el-select>
        <div style="margin-top: 10px; color: #909399; font-size: 12px">
          提示: 选择已有标签或输入新标签名称后按回车添加
        </div>
      </el-form-item>

      <!-- 标签预览 -->
      <el-form-item label="已选标签">
        <el-tag
          v-for="(tag, index) in displayTags"
          :key="index"
          closable
          @close="removeTag(index)"
          style="margin-right: 10px; margin-bottom: 5px"
          :type="tag.isNew ? 'success' : ''"
        >
          {{ tag.name }}
          <span v-if="tag.isNew" style="font-size: 10px"> (新)</span>
        </el-tag>
        <el-text v-if="displayTags.length === 0" type="info">
          暂无标签
        </el-text>
      </el-form-item>

      <!-- 内容 -->
      <el-form-item label="内容" prop="content">
        <el-input
          v-model="form.content"
          type="textarea"
          :rows="10"
          placeholder="请输入文章内容"
        />
      </el-form-item>

      <!-- 按钮 -->
      <el-form-item>
        <el-button type="primary" @click="submitForm">保存</el-button>
        <el-button @click="resetForm">重置</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { createArticle, updateArticle } from '@/api/article'
import { getTags } from '@/api/tag'
import { getCategories } from '@/api/category'

// 表单数据
const form = reactive({
  title: '',
  content: '',
  category_id: null,
  tags: [], // 可以包含ID（数字）或名称（字符串）
  status: 1
})

// 验证规则
const rules = {
  title: [{ required: true, message: '请输入标题', trigger: 'blur' }],
  content: [{ required: true, message: '请输入内容', trigger: 'blur' }],
  category_id: [{ required: true, message: '请选择分类', trigger: 'change' }]
}

const formRef = ref(null)
const allTags = ref([]) // 所有已存在的标签
const categories = ref([])

// 计算显示的标签（用于预览）
const displayTags = computed(() => {
  return form.tags.map(tag => {
    if (typeof tag === 'number') {
      // 如果是ID，从allTags中查找
      const found = allTags.value.find(t => t.id === tag)
      return {
        name: found ? found.name : `标签#${tag}`,
        isNew: false
      }
    } else {
      // 如果是字符串，说明是新标签
      return {
        name: tag,
        isNew: true
      }
    }
  })
})

// 加载标签列表
const loadTags = async () => {
  try {
    const result = await getTags({ page: 1, page_size: 200, status: 1 })
    if (result.code === 0) {
      allTags.value = result.data.list || []
    }
  } catch (error) {
    console.error('加载标签失败:', error)
  }
}

// 加载分类列表
const loadCategories = async () => {
  try {
    const result = await getCategories({ page: 1, page_size: 100 })
    if (result.code === 0) {
      categories.value = result.data.list || []
    }
  } catch (error) {
    console.error('加载分类失败:', error)
  }
}

// 标签变化处理
const handleTagChange = (values) => {
  console.log('当前标签:', values)
  console.log('新标签:', values.filter(v => typeof v === 'string'))
  console.log('已存在标签:', values.filter(v => typeof v === 'number'))
}

// 移除标签
const removeTag = (index) => {
  form.tags.splice(index, 1)
}

// 提交表单
const submitForm = async () => {
  const valid = await formRef.value.validate().catch(() => false)
  if (!valid) return

  try {
    const result = await createArticle(form)
    if (result.code === 0) {
      ElMessage.success('文章创建成功！新标签已自动加入标签库')
      resetForm()
      // 重新加载标签列表，以显示新创建的标签
      await loadTags()
    } else {
      ElMessage.error(result.message || '创建失败')
    }
  } catch (error) {
    console.error('创建文章失败:', error)
    ElMessage.error('创建失败')
  }
}

// 重置表单
const resetForm = () => {
  formRef.value?.resetFields()
  form.tags = []
}

// 组件挂载时加载数据
onMounted(() => {
  loadTags()
  loadCategories()
})
</script>

<style scoped>
.article-form {
  max-width: 800px;
  margin: 20px auto;
}
</style>
```

## 高级用法示例

### 1. 标签输入时智能提示

```vue
<script setup>
import { ref, watch } from 'vue'

const tagInput = ref('')
const suggestedTags = ref([])

// 监听输入，提供智能提示
watch(tagInput, (newValue) => {
  if (newValue.length >= 2) {
    // 从已有标签中筛选匹配的
    suggestedTags.value = allTags.value
      .filter(tag =>
        tag.name.toLowerCase().includes(newValue.toLowerCase())
      )
      .slice(0, 5) // 最多显示5个建议
  } else {
    suggestedTags.value = []
  }
})
</script>
```

### 2. 防止重复添加

```javascript
const addTag = (tagValue) => {
  // 检查是否已经添加过
  const isDuplicate = form.tags.some(existingTag => {
    if (typeof existingTag === 'number' && typeof tagValue === 'number') {
      return existingTag === tagValue
    }
    if (typeof existingTag === 'string' && typeof tagValue === 'string') {
      return existingTag.toLowerCase() === tagValue.toLowerCase()
    }
    // 检查ID对应的名称是否与新标签名称相同
    if (typeof existingTag === 'number' && typeof tagValue === 'string') {
      const tag = allTags.value.find(t => t.id === existingTag)
      return tag && tag.name.toLowerCase() === tagValue.toLowerCase()
    }
    return false
  })

  if (isDuplicate) {
    ElMessage.warning('该标签已添加')
    return
  }

  form.tags.push(tagValue)
}
```

### 3. 批量导入标签

```vue
<template>
  <el-button @click="showBatchImport">批量导入标签</el-button>

  <el-dialog v-model="batchImportVisible" title="批量导入标签">
    <el-input
      v-model="batchTagsText"
      type="textarea"
      :rows="8"
      placeholder="每行一个标签名称，如：&#10;Vue 3&#10;React&#10;Angular"
    />
    <template #footer>
      <el-button @click="batchImportVisible = false">取消</el-button>
      <el-button type="primary" @click="importBatchTags">确定</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
const batchImportVisible = ref(false)
const batchTagsText = ref('')

const showBatchImport = () => {
  batchImportVisible.value = true
}

const importBatchTags = () => {
  const lines = batchTagsText.value.split('\n')
  const validTags = lines
    .map(line => line.trim())
    .filter(line => line.length > 0 && line.length <= 50)

  // 添加到表单（避免重复）
  validTags.forEach(tagName => {
    if (!form.tags.some(t =>
      (typeof t === 'string' && t.toLowerCase() === tagName.toLowerCase()) ||
      (typeof t === 'number' && allTags.value.find(tag =>
        tag.id === t && tag.name.toLowerCase() === tagName.toLowerCase()
      ))
    )) {
      form.tags.push(tagName)
    }
  })

  ElMessage.success(`成功导入 ${validTags.length} 个标签`)
  batchImportVisible.value = false
  batchTagsText.value = ''
}
</script>
```

## 测试检查清单

- [ ] 可以创建全新的标签
- [ ] 可以使用已存在的标签（通过ID）
- [ ] 可以混合使用新标签和已存在的标签
- [ ] 新创建的标签出现在标签库中
- [ ] 重复的标签名称不会创建多个记录
- [ ] 标签名称的大小写处理正确
- [ ] 过长的标签名称被正确拒绝
- [ ] 更新文章时标签处理正确
- [ ] 并发创建相同标签不会出错

## 常见问题

**Q: 为什么我输入的新标签没有创建？**
A: 检查以下几点：
1. 标签名称是否超过50个字符
2. 网络请求是否成功
3. 后端日志是否有错误信息

**Q: 如何禁止用户创建新标签？**
A: 在前端不使用 `allow-create` 属性，只提供已有标签选择

**Q: 新创建的标签slug是怎么生成的？**
A:
- 英文标签：转小写，空格替换为连字符
- 中文标签：使用原名称

**Q: 如何让某些用户无法创建新标签？**
A: 在 `processTagsWithAutoCreate` 方法开头添加权限检查：
```php
// 检查用户是否有创建标签权限
if (!$this->request->user['can_create_tag']) {
    // 过滤掉字符串类型的标签
    $tags = array_filter($tags, fn($tag) => is_numeric($tag));
}
```

## 性能建议

1. **前端缓存**: 将已加载的标签列表缓存在本地
2. **懒加载**: 标签数量多时使用虚拟滚动
3. **防抖**: 标签输入时添加防抖延迟
4. **批量创建**: 避免在一次请求中创建过多新标签（建议<20个）
