<template>
  <div class="template-editor-pro">
    <el-row :gutter="20">
      <!-- 左侧：文件树 -->
      <el-col :span="5">
        <el-card shadow="never" class="file-tree-card">
          <template #header>
            <div class="card-header">
              <span>文件管理</span>
              <el-dropdown @command="handleFileAction">
                <el-button type="primary" size="small" :icon="Plus">
                  新建<el-icon class="el-icon--right"><arrow-down /></el-icon>
                </el-button>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item command="newFile">新建文件</el-dropdown-item>
                    <el-dropdown-item command="newFolder">新建文件夹</el-dropdown-item>
                    <el-dropdown-item command="upload">上传文件</el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>
          </template>

          <!-- 模板套装选择 -->
          <div class="theme-selector">
            <el-select
              v-model="currentTheme"
              placeholder="选择模板套装"
              @change="handleThemeChange"
              size="default"
            >
              <el-option
                v-for="theme in themes"
                :key="theme.key"
                :label="theme.name"
                :value="theme.key"
              >
                <span style="float: left">{{ theme.name }}</span>
                <span style="float: right; color: #8492a6; font-size: 13px">
                  {{ theme.version || 'v1.0' }}
                </span>
              </el-option>
            </el-select>
          </div>

          <!-- 搜索框 -->
          <el-input
            v-model="fileSearchKeyword"
            placeholder="搜索文件..."
            :prefix-icon="Search"
            size="small"
            clearable
            style="margin-bottom: 10px;"
          />

          <!-- 文件树 -->
          <el-tree
            ref="treeRef"
            :data="filteredFileTree"
            :props="treeProps"
            node-key="path"
            highlight-current
            :expand-on-click-node="false"
            :filter-node-method="filterNode"
            default-expand-all
            @node-click="handleNodeClick"
            @node-contextmenu="handleContextMenu"
          >
            <template #default="{ node, data }">
              <span class="custom-tree-node">
                <el-icon v-if="data.type === 'directory'" class="folder-icon">
                  <folder />
                </el-icon>
                <el-icon v-else class="file-icon" :style="{ color: getFileIconColor(data.name) }">
                  <document />
                </el-icon>
                <span class="node-label">{{ node.label }}</span>
                <span v-if="data.size" class="file-size">{{ formatFileSize(data.size) }}</span>
              </span>
            </template>
          </el-tree>
        </el-card>
      </el-col>

      <!-- 右侧：编辑器 -->
      <el-col :span="19">
        <el-card shadow="never" class="editor-card">
          <!-- 工具栏 -->
          <template #header>
            <div class="editor-toolbar">
              <div class="toolbar-left">
                <el-button-group>
                  <el-button
                    size="small"
                    :icon="DocumentCopy"
                    @click="newTab"
                    title="新建标签页"
                  >
                    新建
                  </el-button>
                  <el-button
                    size="small"
                    :icon="FolderOpened"
                    @click="showOpenFileDialog"
                    title="打开文件"
                  >
                    打开
                  </el-button>
                  <el-button
                    size="small"
                    :icon="Check"
                    @click="saveCurrentFile"
                    :disabled="!activeTab || !activeTab.modified"
                    :loading="saving"
                    title="保存 (Ctrl+S)"
                  >
                    保存
                  </el-button>
                  <el-button
                    size="small"
                    :icon="CopyDocument"
                    @click="saveAllFiles"
                    :disabled="!hasModifiedTabs"
                    title="保存全部"
                  >
                    全部保存
                  </el-button>
                </el-button-group>

                <el-divider direction="vertical" />

                <el-button-group>
                  <el-button
                    size="small"
                    :icon="MagicStick"
                    @click="formatCode"
                    :disabled="!activeTab"
                    title="格式化代码"
                  >
                    格式化
                  </el-button>
                  <el-button
                    size="small"
                    :icon="Operation"
                    @click="showDiffView"
                    :disabled="openTabs.length < 2"
                    title="文件对比"
                  >
                    对比
                  </el-button>
                  <el-button
                    size="small"
                    :icon="Aim"
                    @click="toggleMinimap"
                    :disabled="!activeTab"
                    title="切换缩略图"
                  >
                    缩略图
                  </el-button>
                </el-button-group>

                <el-divider direction="vertical" />

                <el-button-group>
                  <el-button
                    size="small"
                    :icon="RefreshRight"
                    @click="reloadCurrentFile"
                    :disabled="!activeTab"
                    title="重新加载"
                  >
                    重载
                  </el-button>
                  <el-button
                    size="small"
                    :icon="Clock"
                    @click="showFileHistory"
                    :disabled="!activeTab"
                    title="查看历史"
                  >
                    历史
                  </el-button>
                </el-button-group>
              </div>

              <div class="toolbar-right">
                <el-tag size="small" v-if="activeTab">
                  {{ getLanguageLabel(activeTab.language) }}
                </el-tag>
                <el-tag type="info" size="small" v-if="activeTab">
                  行: {{ cursorPosition.line }} | 列: {{ cursorPosition.column }}
                </el-tag>
                <el-switch
                  v-model="editorSettings.wordWrap"
                  active-text="自动换行"
                  size="small"
                  @change="updateEditorOptions"
                />
              </div>
            </div>
          </template>

          <!-- 标签页 -->
          <div v-if="openTabs.length > 0" class="tabs-container">
            <div class="tabs-wrapper">
              <div
                v-for="tab in openTabs"
                :key="tab.id"
                :class="['tab-item', { active: tab.id === activeTabId, modified: tab.modified }]"
                @click="switchTab(tab.id)"
                @contextmenu.prevent="showTabContextMenu($event, tab)"
              >
                <el-icon class="tab-icon" :style="{ color: getFileIconColor(tab.name) }">
                  <document />
                </el-icon>
                <span class="tab-label">{{ tab.name }}</span>
                <span v-if="tab.modified" class="modified-dot">●</span>
                <el-icon class="tab-close" @click.stop="closeTab(tab.id)">
                  <close />
                </el-icon>
              </div>
            </div>
            <el-dropdown @command="handleTabsAction" trigger="click">
              <el-button size="small" text :icon="MoreFilled" />
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="closeOthers">关闭其他</el-dropdown-item>
                  <el-dropdown-item command="closeRight">关闭右侧</el-dropdown-item>
                  <el-dropdown-item command="closeAll">关闭全部</el-dropdown-item>
                  <el-dropdown-item command="closeUnmodified">关闭未修改</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>

          <!-- Monaco 编辑器 -->
          <div class="monaco-container">
            <VueMonacoEditor
              v-if="activeTab"
              :key="activeTab.id"
              :value="activeTab.content"
              :language="activeTab.language"
              :theme="editorSettings.theme"
              :options="editorOptions"
              @editorDidMount="handleEditorMount"
              @update:value="handleEditorChange"
            />
            <el-empty
              v-else
              description="请从左侧选择文件或新建文件"
              :image-size="200"
            >
              <template #extra>
                <el-button type="primary" @click="newTab">新建文件</el-button>
                <el-button @click="showOpenFileDialog">打开文件</el-button>
              </template>
            </el-empty>
          </div>
        </el-card>
      </el-col>
    </el-row>

    <!-- 新建文件对话框 -->
    <el-dialog v-model="createDialogVisible" title="新建文件" width="500px">
      <el-form :model="createForm" label-width="100px">
        <el-form-item label="文件名">
          <el-input
            v-model="createForm.fileName"
            placeholder="例如: custom.html"
            @keyup.enter="handleCreate"
          >
            <template #append>
              <el-select v-model="createForm.fileExt" style="width: 100px">
                <el-option label=".html" value="html" />
                <el-option label=".css" value="css" />
                <el-option label=".js" value="js" />
                <el-option label=".json" value="json" />
                <el-option label=".md" value="md" />
              </el-select>
            </template>
          </el-input>
        </el-form-item>
        <el-form-item label="文件路径">
          <el-input v-model="createForm.filePath" placeholder="留空创建在根目录" />
        </el-form-item>
        <el-form-item label="模板">
          <el-select v-model="createForm.template" placeholder="选择模板（可选）">
            <el-option label="空白文件" value="" />
            <el-option label="HTML5 模板" value="html5" />
            <el-option label="CSS 重置" value="css-reset" />
            <el-option label="JavaScript 模块" value="js-module" />
          </el-select>
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="createDialogVisible = false">取消</el-button>
        <el-button type="primary" @click="handleCreate">创建</el-button>
      </template>
    </el-dialog>

    <!-- 文件对比对话框 -->
    <el-dialog
      v-model="diffDialogVisible"
      title="文件对比"
      width="90%"
      top="5vh"
      :close-on-click-modal="false"
    >
      <div class="diff-selector">
        <el-select v-model="diffLeft" placeholder="选择左侧文件" style="width: 300px">
          <el-option
            v-for="tab in openTabs"
            :key="'left-' + tab.id"
            :label="tab.name"
            :value="tab.id"
          />
        </el-select>
        <el-icon style="margin: 0 20px"><right /></el-icon>
        <el-select v-model="diffRight" placeholder="选择右侧文件" style="width: 300px">
          <el-option
            v-for="tab in openTabs"
            :key="'right-' + tab.id"
            :label="tab.name"
            :value="tab.id"
          />
        </el-select>
      </div>
      <div class="diff-editor" style="height: 600px; margin-top: 20px;">
        <VueMonacoDiffEditor
          v-if="diffLeft && diffRight"
          :original="getDiffContent(diffLeft)"
          :modified="getDiffContent(diffRight)"
          :language="getDiffLanguage()"
          :theme="editorSettings.theme"
        />
      </div>
    </el-dialog>

    <!-- 文件历史对话框 -->
    <el-dialog v-model="historyDialogVisible" title="文件历史" width="900px">
      <el-table :data="fileHistory" stripe>
        <el-table-column prop="version" label="版本" width="80" />
        <el-table-column prop="file_name" label="文件名" width="150" />
        <el-table-column prop="description" label="修改描述" min-width="200" />
        <el-table-column prop="size" label="大小" width="100">
          <template #default="{ row }">
            {{ formatFileSize(row.size) }}
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="180" />
        <el-table-column label="操作" width="160" fixed="right">
          <template #default="{ row }">
            <el-button size="small" type="primary" @click="restoreBackup(row)">
              恢复
            </el-button>
            <el-button size="small" @click="previewBackup(row)">预览</el-button>
          </template>
        </el-table-column>
      </el-table>
      <el-empty v-if="fileHistory.length === 0" description="暂无历史记录" />
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  Plus,
  ArrowDown,
  Search,
  Folder,
  Document,
  DocumentCopy,
  FolderOpened,
  Check,
  CopyDocument,
  MagicStick,
  Operation,
  Aim,
  RefreshRight,
  Clock,
  Close,
  MoreFilled,
  Right
} from '@element-plus/icons-vue'
import { VueMonacoEditor, VueMonacoDiffEditor } from '@guolao/vue-monaco-editor'
import {
  scanThemes,
  getCurrentTheme,
  getFileTree,
  readFile,
  saveFile as saveFileApi,
  createFile as createFileApi,
  deleteFile as deleteFileApi,
  getBackups,
  getHistoryContent,
  restoreHistory as restoreHistoryApi
} from '@/api/template'

// 主题列表
const themes = ref([])
const currentTheme = ref('default')

// 文件树
const fileTree = ref([])
const fileSearchKeyword = ref('')
const treeRef = ref(null)
const treeProps = {
  children: 'children',
  label: 'name'
}

// 标签页管理
const openTabs = ref([])
const activeTabId = ref(null)
let tabIdCounter = 0

const activeTab = computed(() => {
  return openTabs.value.find(tab => tab.id === activeTabId.value)
})

const hasModifiedTabs = computed(() => {
  return openTabs.value.some(tab => tab.modified)
})

// 编辑器设置
const editorSettings = reactive({
  theme: 'vs-dark',
  fontSize: 14,
  wordWrap: 'on',
  minimap: true
})

const editorOptions = computed(() => ({
  fontSize: editorSettings.fontSize,
  wordWrap: editorSettings.wordWrap,
  minimap: { enabled: editorSettings.minimap },
  automaticLayout: true,
  scrollBeyondLastLine: false,
  readOnly: false,
  tabSize: 2,
  formatOnPaste: true,
  formatOnType: true,
  suggestOnTriggerCharacters: true,
  acceptSuggestionOnEnter: 'on',
  quickSuggestions: true,
  folding: true,
  foldingStrategy: 'indentation',
  showFoldingControls: 'always'
}))

// Monaco编辑器实例
let editorInstance = null
const cursorPosition = reactive({ line: 1, column: 1 })

// 对话框
const createDialogVisible = ref(false)
const diffDialogVisible = ref(false)
const historyDialogVisible = ref(false)

const createForm = reactive({
  fileName: '',
  fileExt: 'html',
  filePath: '',
  template: ''
})

const diffLeft = ref(null)
const diffRight = ref(null)
const fileHistory = ref([])
const saving = ref(false)

// 过滤后的文件树
const filteredFileTree = computed(() => {
  if (!fileSearchKeyword.value) {
    return fileTree.value
  }
  return filterTree(fileTree.value, fileSearchKeyword.value)
})

// 初始化
onMounted(async () => {
  await loadThemes()
  await loadCurrentTheme()

  // 验证 localStorage 中的主题是否存在
  const savedTheme = localStorage.getItem('template_editor_current_theme')
  if (savedTheme && !themes.value.find(t => t.key === savedTheme)) {
    // 如果保存的主题不存在，清除 localStorage 并重新加载
    localStorage.removeItem('template_editor_current_theme')
    await loadCurrentTheme()
  }

  await loadFileTree()
  setupKeyboardShortcuts()
})

onBeforeUnmount(() => {
  removeKeyboardShortcuts()
})

// 加载主题列表
const loadThemes = async () => {
  try {
    const res = await scanThemes()
    themes.value = res.data || []
  } catch (error) {
    ElMessage.error('加载模板套装失败')
  }
}

// 加载当前主题
const loadCurrentTheme = async () => {
  try {
    // 优先从 localStorage 读取用户上次选择的模板
    const savedTheme = localStorage.getItem('template_editor_current_theme')
    if (savedTheme) {
      currentTheme.value = savedTheme
      return
    }

    // 如果没有保存的选择，则获取主站点的模板套装
    const res = await getCurrentTheme()
    currentTheme.value = res.data.key || 'default'
  } catch (error) {
    console.error('加载当前模板套装失败', error)
    currentTheme.value = 'default'
  }
}

// 加载文件树
const loadFileTree = async () => {
  try {
    const res = await getFileTree(currentTheme.value)
    fileTree.value = res.data || []
  } catch (error) {
    ElMessage.error('加载文件树失败')
  }
}

// 切换主题
const handleThemeChange = async () => {
  if (hasModifiedTabs.value) {
    try {
      await ElMessageBox.confirm(
        '当前有未保存的文件，切换主题将关闭所有标签页，是否继续？',
        '提示',
        {
          confirmButtonText: '继续',
          cancelButtonText: '取消',
          type: 'warning'
        }
      )
    } catch {
      currentTheme.value = themes.value.find(t => t.key === currentTheme.value)?.key || 'default'
      return
    }
  }

  // 保存用户的选择到 localStorage
  localStorage.setItem('template_editor_current_theme', currentTheme.value)

  openTabs.value = []
  activeTabId.value = null
  await loadFileTree()
}

// 文件树节点点击
const handleNodeClick = async (data) => {
  if (data.type === 'directory') {
    return
  }

  await openFileInTab(data.path)
}

// 在标签页中打开文件
const openFileInTab = async (filePath) => {
  // 检查是否已经打开
  const existingTab = openTabs.value.find(tab => tab.path === filePath)
  if (existingTab) {
    activeTabId.value = existingTab.id
    return
  }

  // 加载文件内容
  try {
    const res = await readFile(currentTheme.value, filePath)
    console.log('API response:', res)

    // request.js 拦截器已经返回了 {code, message, data, timestamp}
    // 所以 res.data 就是我们需要的文件数据
    const fileData = res.data
    console.log('File data:', fileData)
    console.log('File content length:', fileData?.content?.length || 0)

    if (!fileData || fileData.content === undefined) {
      ElMessage.error('文件数据格式错误')
      console.error('Invalid file data:', fileData)
      return
    }

    const newTab = {
      id: ++tabIdCounter,
      name: filePath.split('/').pop(),
      path: filePath,
      content: fileData.content || '',
      originalContent: fileData.content || '',
      modified: false,
      language: detectLanguage(filePath),
      size: fileData.size || 0,
      modifiedTime: fileData.modified || ''
    }

    console.log('Creating new tab:', newTab)
    openTabs.value.push(newTab)
    activeTabId.value = newTab.id

    // 验证标签页已正确添加
    console.log('Active tab ID:', activeTabId.value)
    console.log('Open tabs count:', openTabs.value.length)
    console.log('Active tab content length:', activeTab.value?.content?.length || 0)
  } catch (error) {
    console.error('Error opening file:', error)
    ElMessage.error('读取文件失败：' + (error.message || ''))
  }
}

// 检测文件语言
const detectLanguage = (fileName) => {
  const ext = fileName.split('.').pop().toLowerCase()
  const languageMap = {
    html: 'html',
    htm: 'html',
    css: 'css',
    scss: 'scss',
    less: 'less',
    js: 'javascript',
    json: 'json',
    md: 'markdown',
    php: 'php',
    xml: 'xml',
    svg: 'xml'
  }
  return languageMap[ext] || 'plaintext'
}

// 获取语言标签
const getLanguageLabel = (language) => {
  const labels = {
    html: 'HTML',
    css: 'CSS',
    scss: 'SCSS',
    less: 'LESS',
    javascript: 'JavaScript',
    json: 'JSON',
    markdown: 'Markdown',
    php: 'PHP',
    xml: 'XML',
    plaintext: 'Text'
  }
  return labels[language] || language.toUpperCase()
}

// 获取文件图标颜色
const getFileIconColor = (fileName) => {
  const ext = fileName.split('.').pop().toLowerCase()
  const colors = {
    html: '#e34c26',
    css: '#563d7c',
    js: '#f1e05a',
    json: '#cbcb41',
    md: '#083fa1',
    php: '#4F5D95',
    xml: '#0060ac'
  }
  return colors[ext] || '#909399'
}

// 编辑器挂载
const handleEditorMount = (editor) => {
  console.log('Monaco Editor mounted, instance:', editor)
  console.log('Current active tab:', activeTab.value)
  console.log('Editor model value:', editor.getValue())

  editorInstance = editor

  // 监听光标位置变化
  editor.onDidChangeCursorPosition((e) => {
    cursorPosition.line = e.position.lineNumber
    cursorPosition.column = e.position.column
  })

  // 验证编辑器内容
  console.log('Editor content after mount:', editor.getValue()?.substring(0, 100))
}

// 编辑器内容变化
const handleEditorChange = (value) => {
  console.log('Editor content changed, new value length:', value?.length || 0)

  if (!activeTab.value) return

  // 更新标签页内容
  activeTab.value.content = value
  // 标记为已修改
  activeTab.value.modified = activeTab.value.content !== activeTab.value.originalContent
}

// 切换标签页
const switchTab = (tabId) => {
  activeTabId.value = tabId
}

// 关闭标签页
const closeTab = async (tabId) => {
  const tab = openTabs.value.find(t => t.id === tabId)
  if (!tab) return

  if (tab.modified) {
    try {
      await ElMessageBox.confirm(
        `文件"${tab.name}"有未保存的修改，是否保存？`,
        '提示',
        {
          confirmButtonText: '保存',
          cancelButtonText: '不保存',
          distinguishCancelAndClose: true,
          type: 'warning'
        }
      )
      await saveTab(tab)
    } catch (action) {
      if (action === 'cancel') {
        // 用户选择不保存，继续关闭
      } else {
        // 用户取消关闭
        return
      }
    }
  }

  const index = openTabs.value.findIndex(t => t.id === tabId)
  openTabs.value.splice(index, 1)

  // 如果关闭的是当前标签页，切换到下一个
  if (tabId === activeTabId.value) {
    if (openTabs.value.length > 0) {
      activeTabId.value = openTabs.value[Math.max(0, index - 1)].id
    } else {
      activeTabId.value = null
    }
  }
}

// 保存当前文件
const saveCurrentFile = async () => {
  if (!activeTab.value) return
  await saveTab(activeTab.value)
}

// 保存标签页
const saveTab = async (tab) => {
  saving.value = true
  try {
    await saveFileApi(
      currentTheme.value,
      tab.path,
      tab.content
    )

    tab.originalContent = tab.content
    tab.modified = false
    ElMessage.success(`文件"${tab.name}"保存成功`)
  } catch (error) {
    ElMessage.error('保存失败：' + (error.message || ''))
    throw error
  } finally {
    saving.value = false
  }
}

// 保存所有文件
const saveAllFiles = async () => {
  const modifiedTabs = openTabs.value.filter(tab => tab.modified)
  let successCount = 0
  let failCount = 0

  for (const tab of modifiedTabs) {
    try {
      await saveTab(tab)
      successCount++
    } catch {
      failCount++
    }
  }

  if (failCount === 0) {
    ElMessage.success(`成功保存${successCount}个文件`)
  } else {
    ElMessage.warning(`保存完成：成功${successCount}个，失败${failCount}个`)
  }
}

// 格式化代码
const formatCode = () => {
  if (editorInstance) {
    editorInstance.trigger('editor', 'editor.action.formatDocument')
  }
}

// 切换缩略图
const toggleMinimap = () => {
  editorSettings.minimap = !editorSettings.minimap
}

// 更新编辑器选项
const updateEditorOptions = () => {
  // 编辑器选项会通过computed自动更新
}

// 重新加载当前文件
const reloadCurrentFile = async () => {
  if (!activeTab.value) return

  if (activeTab.value.modified) {
    try {
      await ElMessageBox.confirm(
        '当前文件有未保存的修改，是否放弃修改并重新加载？',
        '提示',
        {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }
      )
    } catch {
      return
    }
  }

  const filePath = activeTab.value.path
  const tabId = activeTab.value.id
  const index = openTabs.value.findIndex(t => t.id === tabId)

  openTabs.value.splice(index, 1)
  await openFileInTab(filePath)
  ElMessage.success('文件已重新加载')
}

// 新建标签页
const newTab = () => {
  createDialogVisible.value = true
  createForm.fileName = ''
  createForm.fileExt = 'html'
  createForm.filePath = ''
  createForm.template = ''
}

// 创建文件
const handleCreate = async () => {
  if (!createForm.fileName) {
    ElMessage.warning('请输入文件名')
    return
  }

  const fullName = `${createForm.fileName}.${createForm.fileExt}`
  const path = createForm.filePath ? `${createForm.filePath}/${fullName}` : fullName

  try {
    await createFileApi(
      currentTheme.value,
      path,
      createForm.fileExt
    )

    ElMessage.success('文件创建成功')
    createDialogVisible.value = false

    await loadFileTree()
    await openFileInTab(path)
  } catch (error) {
    ElMessage.error('创建失败：' + (error.message || ''))
  }
}

// 显示文件对比
const showDiffView = () => {
  if (openTabs.value.length >= 2) {
    diffLeft.value = openTabs.value[0].id
    diffRight.value = openTabs.value[1].id
    diffDialogVisible.value = true
  }
}

// 获取对比内容
const getDiffContent = (tabId) => {
  const tab = openTabs.value.find(t => t.id === tabId)
  return tab ? tab.content : ''
}

// 获取对比语言
const getDiffLanguage = () => {
  const leftTab = openTabs.value.find(t => t.id === diffLeft.value)
  return leftTab ? leftTab.language : 'plaintext'
}

// 显示文件历史
const showFileHistory = async () => {
  if (!activeTab.value) return

  try {
    const res = await getBackups(currentTheme.value, activeTab.value.path)
    fileHistory.value = res.data || []
    historyDialogVisible.value = true
  } catch (error) {
    ElMessage.error('获取历史记录失败')
  }
}

// 恢复历史版本
const restoreBackup = async (history) => {
  try {
    await ElMessageBox.confirm(
      `确定要恢复到版本 ${history.version} 吗？当前内容将被覆盖。`,
      '确认恢复',
      {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      }
    )

    const res = await restoreHistoryApi(history.id)
    ElMessage.success(res.message || '恢复成功')

    // 关闭历史对话框
    historyDialogVisible.value = false

    // 重新加载文件内容
    if (activeTab.value) {
      const fileRes = await readFile(currentTheme.value, activeTab.value.path)
      const fileData = fileRes.data

      activeTab.value.content = fileData.content || ''
      activeTab.value.originalContent = fileData.content || ''
      activeTab.value.modified = false
      activeTab.value.size = fileData.size || 0
      activeTab.value.modifiedTime = fileData.modified || ''
    }
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('恢复失败：' + (error.message || ''))
    }
  }
}

// 预览历史版本
const previewBackup = async (history) => {
  try {
    const res = await getHistoryContent(history.id)
    const historyData = res.data

    if (!historyData || !historyData.content) {
      ElMessage.error('获取历史内容失败')
      return
    }

    // 在新标签页中打开历史版本（只读预览）
    const previewTab = {
      id: ++tabIdCounter,
      name: `${history.file_name} [v${history.version}]`,
      path: historyData.file_path,
      content: historyData.content,
      originalContent: historyData.content,
      modified: false,
      language: detectLanguage(historyData.file_path),
      size: historyData.size || 0,
      modifiedTime: historyData.create_time,
      isPreview: true // 标记为预览模式
    }

    openTabs.value.push(previewTab)
    activeTabId.value = previewTab.id

    // 关闭历史对话框
    historyDialogVisible.value = false
  } catch (error) {
    ElMessage.error('预览失败：' + (error.message || ''))
  }
}

// 文件操作菜单
const handleFileAction = (command) => {
  switch (command) {
    case 'newFile':
      newTab()
      break
    case 'newFolder':
      ElMessage.info('新建文件夹功能开发中')
      break
    case 'upload':
      ElMessage.info('上传文件功能开发中')
      break
  }
}

// 标签页操作
const handleTabsAction = (command) => {
  switch (command) {
    case 'closeOthers':
      if (activeTabId.value) {
        openTabs.value = openTabs.value.filter(t => t.id === activeTabId.value)
      }
      break
    case 'closeRight':
      if (activeTabId.value) {
        const index = openTabs.value.findIndex(t => t.id === activeTabId.value)
        openTabs.value = openTabs.value.slice(0, index + 1)
      }
      break
    case 'closeAll':
      openTabs.value = []
      activeTabId.value = null
      break
    case 'closeUnmodified':
      openTabs.value = openTabs.value.filter(t => t.modified)
      if (!openTabs.value.find(t => t.id === activeTabId.value)) {
        activeTabId.value = openTabs.value[0]?.id || null
      }
      break
  }
}

// 显示打开文件对话框
const showOpenFileDialog = () => {
  ElMessage.info('请从左侧文件树选择文件')
}

// 右键菜单
const handleContextMenu = (event, node, data) => {
  // TODO: 实现右键菜单
  console.log('Context menu', data)
}

const showTabContextMenu = (event, tab) => {
  // TODO: 实现标签页右键菜单
  console.log('Tab context menu', tab)
}

// 文件树过滤
const filterNode = (value, data) => {
  if (!value) return true
  return data.name.toLowerCase().includes(value.toLowerCase())
}

const filterTree = (tree, keyword) => {
  const result = []
  for (const node of tree) {
    if (node.type === 'file' && node.name.toLowerCase().includes(keyword.toLowerCase())) {
      result.push(node)
    } else if (node.children) {
      const filteredChildren = filterTree(node.children, keyword)
      if (filteredChildren.length > 0) {
        result.push({
          ...node,
          children: filteredChildren
        })
      }
    }
  }
  return result
}

// 格式化文件大小
const formatFileSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

// 键盘快捷键
const handleKeydown = (e) => {
  // Ctrl+S 保存
  if (e.ctrlKey && e.key === 's') {
    e.preventDefault()
    saveCurrentFile()
  }
  // Ctrl+Shift+S 保存全部
  else if (e.ctrlKey && e.shiftKey && e.key === 's') {
    e.preventDefault()
    saveAllFiles()
  }
  // Ctrl+W 关闭标签页
  else if (e.ctrlKey && e.key === 'w') {
    e.preventDefault()
    if (activeTabId.value) {
      closeTab(activeTabId.value)
    }
  }
  // Ctrl+Shift+F 格式化
  else if (e.ctrlKey && e.shiftKey && e.key === 'f') {
    e.preventDefault()
    formatCode()
  }
}

const setupKeyboardShortcuts = () => {
  window.addEventListener('keydown', handleKeydown)
}

const removeKeyboardShortcuts = () => {
  window.removeEventListener('keydown', handleKeydown)
}

// 监听文件搜索关键词
watch(fileSearchKeyword, (val) => {
  if (treeRef.value) {
    treeRef.value.filter(val)
  }
})
</script>

<style scoped>
.template-editor-pro {
  padding: 20px;
  height: calc(100vh - 60px);
}

.file-tree-card,
.editor-card {
  height: calc(100vh - 100px);
}

.file-tree-card :deep(.el-card__body) {
  padding: 15px;
  overflow-y: auto;
  height: calc(100% - 56px);
}

.editor-card {
  display: flex;
  flex-direction: column;
}

.editor-card :deep(.el-card__header) {
  padding: 10px 20px;
}

.editor-card :deep(.el-card__body) {
  flex: 1;
  padding: 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.theme-selector {
  margin-bottom: 15px;
}

.custom-tree-node {
  display: flex;
  align-items: center;
  flex: 1;
  padding-right: 8px;
}

.folder-icon,
.file-icon {
  margin-right: 5px;
  font-size: 16px;
}

.node-label {
  flex: 1;
  font-size: 13px;
}

.file-size {
  font-size: 11px;
  color: #909399;
  margin-left: 8px;
}

.editor-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 10px;
}

.toolbar-left,
.toolbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.tabs-container {
  display: flex;
  align-items: center;
  border-bottom: 1px solid #e4e7ed;
  background: #f5f7fa;
  padding: 0 10px;
}

.tabs-wrapper {
  flex: 1;
  display: flex;
  overflow-x: auto;
  overflow-y: hidden;
}

.tabs-wrapper::-webkit-scrollbar {
  height: 0;
}

.tab-item {
  display: flex;
  align-items: center;
  padding: 8px 12px;
  cursor: pointer;
  border-right: 1px solid #e4e7ed;
  background: #fff;
  transition: all 0.3s;
  white-space: nowrap;
  user-select: none;
}

.tab-item:hover {
  background: #ecf5ff;
}

.tab-item.active {
  background: #409eff;
  color: #fff;
}

.tab-item.modified .tab-label {
  font-style: italic;
}

.tab-icon {
  margin-right: 5px;
  font-size: 14px;
}

.tab-label {
  margin-right: 8px;
  font-size: 13px;
}

.modified-dot {
  margin-right: 8px;
  color: #f56c6c;
  font-weight: bold;
}

.tab-item.active .modified-dot {
  color: #fff;
}

.tab-close {
  font-size: 12px;
  padding: 2px;
  border-radius: 2px;
  transition: all 0.2s;
}

.tab-close:hover {
  background: rgba(0, 0, 0, 0.1);
}

.monaco-container {
  flex: 1;
  overflow: hidden;
}

.diff-selector {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px 0;
}

.diff-editor {
  border: 1px solid #e4e7ed;
  border-radius: 4px;
  overflow: hidden;
}
</style>
