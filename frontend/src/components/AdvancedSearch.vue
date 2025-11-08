<template>
  <div class="advanced-search">
    <el-dialog
      v-model="visible"
      title="高级搜索"
      width="800px"
      :close-on-click-modal="false"
      @close="handleClose"
    >
      <el-tabs v-model="activeTab" @tab-click="handleTabChange">
        <!-- 全文搜索 -->
        <el-tab-pane label="全文搜索" name="fulltext">
          <el-form :model="fulltextForm" label-width="100px">
            <el-form-item label="搜索关键词">
              <el-autocomplete
                v-model="fulltextForm.keyword"
                :fetch-suggestions="fetchSuggestions"
                placeholder="请输入搜索关键词"
                style="width: 100%"
                @select="handleSuggestionSelect"
                clearable
              >
                <template #default="{ item }">
                  <div class="suggestion-item">
                    <span>{{ item.title }}</span>
                    <span class="view-count">{{ item.view_count }} 次浏览</span>
                  </div>
                </template>
              </el-autocomplete>
            </el-form-item>

            <el-form-item label="搜索模式">
              <el-select v-model="fulltextForm.mode" placeholder="请选择搜索模式" style="width: 100%">
                <el-option label="自然语言模式（推荐）" value="natural">
                  <div>
                    <div>自然语言模式</div>
                    <div style="font-size: 12px; color: #999;">根据相关度排序，适合大多数搜索场景</div>
                  </div>
                </el-option>
                <el-option label="布尔模式（高级）" value="boolean">
                  <div>
                    <div>布尔模式</div>
                    <div style="font-size: 12px; color: #999;">支持 +word -word "phrase" 等操作符</div>
                  </div>
                </el-option>
                <el-option label="查询扩展模式" value="query_expansion">
                  <div>
                    <div>查询扩展模式</div>
                    <div style="font-size: 12px; color: #999;">自动扩展相关词，提高召回率</div>
                  </div>
                </el-option>
              </el-select>
            </el-form-item>

            <el-form-item label="分类筛选">
              <el-select v-model="fulltextForm.category_id" placeholder="请选择分类" clearable style="width: 100%">
                <el-option
                  v-for="category in categories"
                  :key="category.id"
                  :label="category.name"
                  :value="category.id"
                />
              </el-select>
            </el-form-item>

            <el-form-item label="状态筛选">
              <el-select v-model="fulltextForm.status" placeholder="请选择状态" clearable style="width: 100%">
                <el-option label="草稿" :value="0" />
                <el-option label="已发布" :value="1" />
                <el-option label="待审核" :value="2" />
                <el-option label="已下线" :value="3" />
              </el-select>
            </el-form-item>

            <el-form-item label="发布时间">
              <el-date-picker
                v-model="fulltextDateRange"
                type="daterange"
                range-separator="-"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                style="width: 100%"
                @change="handleFulltextDateChange"
              />
            </el-form-item>

            <el-form-item label="搜索历史">
              <el-tag
                v-for="(history, index) in searchHistory"
                :key="index"
                class="history-tag"
                @click="loadFromHistory(history)"
                closable
                @close="removeHistory(index)"
              >
                {{ history.keyword }}
              </el-tag>
              <el-button v-if="searchHistory.length > 0" type="text" size="small" @click="clearHistory">
                清空历史
              </el-button>
            </el-form-item>
          </el-form>
        </el-tab-pane>

        <!-- 高级搜索 -->
        <el-tab-pane label="高级搜索" name="advanced">
          <el-form :model="advancedForm" label-width="100px">
            <el-form-item label="标题">
              <el-input v-model="advancedForm.title" placeholder="请输入文章标题" clearable />
            </el-form-item>

            <el-form-item label="内容">
              <el-input v-model="advancedForm.content" placeholder="请输入内容关键词" clearable />
            </el-form-item>

            <el-form-item label="摘要">
              <el-input v-model="advancedForm.summary" placeholder="请输入摘要关键词" clearable />
            </el-form-item>

            <el-form-item label="作者">
              <el-input v-model="advancedForm.author" placeholder="请输入作者名称" clearable />
            </el-form-item>

            <el-form-item label="分类">
              <el-select v-model="advancedForm.category_id" placeholder="请选择分类" clearable style="width: 100%">
                <el-option
                  v-for="category in categories"
                  :key="category.id"
                  :label="category.name"
                  :value="category.id"
                />
              </el-select>
            </el-form-item>

            <el-form-item label="标签">
              <el-select
                v-model="advancedForm.tag_ids"
                placeholder="请选择标签"
                multiple
                clearable
                style="width: 100%"
              >
                <el-option v-for="tag in tags" :key="tag.id" :label="tag.name" :value="tag.id" />
              </el-select>
            </el-form-item>

            <el-form-item label="状态">
              <el-select v-model="advancedForm.status" placeholder="请选择状态" clearable style="width: 100%">
                <el-option label="草稿" :value="0" />
                <el-option label="已发布" :value="1" />
                <el-option label="待审核" :value="2" />
                <el-option label="已下线" :value="3" />
              </el-select>
            </el-form-item>

            <el-form-item label="文章属性">
              <el-checkbox v-model="advancedForm.is_top" :true-label="1" :false-label="''" style="margin-right: 20px">
                置顶
              </el-checkbox>
              <el-checkbox v-model="advancedForm.is_recommend" :true-label="1" :false-label="''" style="margin-right: 20px">
                推荐
              </el-checkbox>
              <el-checkbox v-model="advancedForm.is_hot" :true-label="1" :false-label="''">
                热门
              </el-checkbox>
            </el-form-item>

            <el-form-item label="发布时间">
              <el-date-picker
                v-model="advancedDateRange"
                type="daterange"
                range-separator="-"
                start-placeholder="开始日期"
                end-placeholder="结束日期"
                style="width: 100%"
                @change="handleAdvancedDateChange"
              />
            </el-form-item>

            <el-form-item label="浏览量">
              <el-row :gutter="10">
                <el-col :span="11">
                  <el-input-number
                    v-model="advancedForm.min_views"
                    :min="0"
                    placeholder="最小浏览量"
                    style="width: 100%"
                  />
                </el-col>
                <el-col :span="2" style="text-align: center">至</el-col>
                <el-col :span="11">
                  <el-input-number
                    v-model="advancedForm.max_views"
                    :min="0"
                    placeholder="最大浏览量"
                    style="width: 100%"
                  />
                </el-col>
              </el-row>
            </el-form-item>

            <el-form-item label="排序方式">
              <el-row :gutter="10">
                <el-col :span="12">
                  <el-select v-model="advancedForm.sort_by" placeholder="排序字段" style="width: 100%">
                    <el-option label="发布时间" value="publish_time" />
                    <el-option label="浏览量" value="view_count" />
                    <el-option label="点赞数" value="like_count" />
                    <el-option label="评论数" value="comment_count" />
                    <el-option label="创建时间" value="create_time" />
                    <el-option label="更新时间" value="update_time" />
                  </el-select>
                </el-col>
                <el-col :span="12">
                  <el-select v-model="advancedForm.sort_order" placeholder="排序方向" style="width: 100%">
                    <el-option label="降序" value="desc" />
                    <el-option label="升序" value="asc" />
                  </el-select>
                </el-col>
              </el-row>
            </el-form-item>
          </el-form>
        </el-tab-pane>
      </el-tabs>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="handleReset">重置</el-button>
          <el-button @click="handleClose">取消</el-button>
          <el-button type="primary" @click="handleSearch">搜索</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { searchSuggestions } from '@/api/article'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  categories: {
    type: Array,
    default: () => []
  },
  tags: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue', 'search'])

const visible = ref(false)
const activeTab = ref('fulltext')
const fulltextDateRange = ref(null)
const advancedDateRange = ref(null)

// 全文搜索表单
const fulltextForm = reactive({
  keyword: '',
  mode: 'natural',
  category_id: '',
  status: '',
  start_time: '',
  end_time: ''
})

// 高级搜索表单
const advancedForm = reactive({
  title: '',
  content: '',
  summary: '',
  author: '',
  category_id: '',
  tag_ids: [],
  user_id: '',
  status: '',
  is_top: '',
  is_recommend: '',
  is_hot: '',
  start_time: '',
  end_time: '',
  min_views: '',
  max_views: '',
  sort_by: 'publish_time',
  sort_order: 'desc'
})

// 搜索历史
const searchHistory = ref([])

// 加载搜索历史
const loadSearchHistory = () => {
  try {
    const history = localStorage.getItem('article_search_history')
    if (history) {
      searchHistory.value = JSON.parse(history)
    }
  } catch (error) {
    console.error('加载搜索历史失败', error)
  }
}

// 保存搜索历史
const saveSearchHistory = (keyword) => {
  if (!keyword) return

  // 避免重复
  const index = searchHistory.value.findIndex(item => item.keyword === keyword)
  if (index > -1) {
    searchHistory.value.splice(index, 1)
  }

  // 添加到开头
  searchHistory.value.unshift({
    keyword,
    timestamp: Date.now()
  })

  // 限制历史记录数量
  if (searchHistory.value.length > 10) {
    searchHistory.value = searchHistory.value.slice(0, 10)
  }

  // 保存到 localStorage
  try {
    localStorage.setItem('article_search_history', JSON.stringify(searchHistory.value))
  } catch (error) {
    console.error('保存搜索历史失败', error)
  }
}

// 从历史记录加载
const loadFromHistory = (history) => {
  fulltextForm.keyword = history.keyword
}

// 删除历史记录
const removeHistory = (index) => {
  searchHistory.value.splice(index, 1)
  try {
    localStorage.setItem('article_search_history', JSON.stringify(searchHistory.value))
  } catch (error) {
    console.error('保存搜索历史失败', error)
  }
}

// 清空历史记录
const clearHistory = () => {
  searchHistory.value = []
  try {
    localStorage.removeItem('article_search_history')
  } catch (error) {
    console.error('清空搜索历史失败', error)
  }
}

// 获取搜索建议
const fetchSuggestions = async (queryString, callback) => {
  if (!queryString) {
    callback([])
    return
  }

  try {
    const res = await searchSuggestions(queryString, 10)
    callback(res.data || [])
  } catch (error) {
    console.error('获取搜索建议失败', error)
    callback([])
  }
}

// 选择建议
const handleSuggestionSelect = (item) => {
  fulltextForm.keyword = item.title
}

// 处理全文搜索日期变化
const handleFulltextDateChange = (dates) => {
  if (dates && dates.length === 2) {
    fulltextForm.start_time = dates[0].toISOString().split('T')[0]
    fulltextForm.end_time = dates[1].toISOString().split('T')[0]
  } else {
    fulltextForm.start_time = ''
    fulltextForm.end_time = ''
  }
}

// 处理高级搜索日期变化
const handleAdvancedDateChange = (dates) => {
  if (dates && dates.length === 2) {
    advancedForm.start_time = dates[0].toISOString().split('T')[0]
    advancedForm.end_time = dates[1].toISOString().split('T')[0]
  } else {
    advancedForm.start_time = ''
    advancedForm.end_time = ''
  }
}

// 标签页切换
const handleTabChange = () => {
  // 可以在这里添加切换逻辑
}

// 搜索
const handleSearch = () => {
  if (activeTab.value === 'fulltext') {
    if (!fulltextForm.keyword) {
      ElMessage.warning('请输入搜索关键词')
      return
    }

    // 保存搜索历史
    saveSearchHistory(fulltextForm.keyword)

    // 触发搜索事件
    emit('search', {
      type: 'fulltext',
      params: { ...fulltextForm }
    })
  } else {
    // 高级搜索至少要有一个搜索条件
    const hasCondition = Object.entries(advancedForm).some(([key, value]) => {
      if (key === 'sort_by' || key === 'sort_order') return false
      return value !== '' && value !== null && (Array.isArray(value) ? value.length > 0 : true)
    })

    if (!hasCondition) {
      ElMessage.warning('请至少填写一个搜索条件')
      return
    }

    // 处理标签 ID（转换为逗号分隔的字符串）
    const params = { ...advancedForm }
    if (params.tag_ids && params.tag_ids.length > 0) {
      params.tag_ids = params.tag_ids.join(',')
    } else {
      delete params.tag_ids
    }

    emit('search', {
      type: 'advanced',
      params
    })
  }

  handleClose()
}

// 重置
const handleReset = () => {
  if (activeTab.value === 'fulltext') {
    Object.assign(fulltextForm, {
      keyword: '',
      mode: 'natural',
      category_id: '',
      status: '',
      start_time: '',
      end_time: ''
    })
    fulltextDateRange.value = null
  } else {
    Object.assign(advancedForm, {
      title: '',
      content: '',
      summary: '',
      author: '',
      category_id: '',
      tag_ids: [],
      user_id: '',
      status: '',
      is_top: '',
      is_recommend: '',
      is_hot: '',
      start_time: '',
      end_time: '',
      min_views: '',
      max_views: '',
      sort_by: 'publish_time',
      sort_order: 'desc'
    })
    advancedDateRange.value = null
  }
}

// 关闭对话框
const handleClose = () => {
  visible.value = false
  emit('update:modelValue', false)
}

// 监听外部 visible 变化
watch(
  () => props.modelValue,
  (newVal) => {
    visible.value = newVal
    if (newVal) {
      loadSearchHistory()
    }
  },
  { immediate: true }
)
</script>

<style scoped>
.suggestion-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.view-count {
  font-size: 12px;
  color: #999;
}

.history-tag {
  margin-right: 8px;
  margin-bottom: 8px;
  cursor: pointer;
}

.history-tag:hover {
  opacity: 0.8;
}
</style>
