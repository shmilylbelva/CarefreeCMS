<template>
  <div class="tinymce-editor">
    <Editor
      :id="editorId"
      v-model="content"
      :init="editorConfig"
      :disabled="disabled"
      @onInit="handleInit"
    />

    <!-- 媒体库选择器 -->
    <MediaSelector
      v-model="mediaSelectorVisible"
      :file-type="mediaSelectorType"
      @select="handleMediaSelect"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import Editor from '@tinymce/tinymce-vue'
import tinymce from 'tinymce/tinymce'
import request from '@/api/request'
import MediaSelector from './MediaSelector.vue'

// TinyMCE 核心
import 'tinymce/models/dom'
import 'tinymce/themes/silver'
import 'tinymce/icons/default'

// 插件
import 'tinymce/plugins/advlist'
import 'tinymce/plugins/autolink'
import 'tinymce/plugins/lists'
import 'tinymce/plugins/link'
import 'tinymce/plugins/image'
import 'tinymce/plugins/charmap'
import 'tinymce/plugins/preview'
import 'tinymce/plugins/anchor'
import 'tinymce/plugins/searchreplace'
import 'tinymce/plugins/visualblocks'
import 'tinymce/plugins/code'
import 'tinymce/plugins/fullscreen'
import 'tinymce/plugins/insertdatetime'
import 'tinymce/plugins/media'
import 'tinymce/plugins/table'
import 'tinymce/plugins/wordcount'
import 'tinymce/plugins/codesample'
import 'tinymce/plugins/emoticons'
import 'tinymce/plugins/directionality'
import 'tinymce/plugins/pagebreak'
import 'tinymce/plugins/nonbreaking'
import 'tinymce/plugins/visualchars'
import 'tinymce/plugins/quickbars'

// 表情符号数据库
import 'tinymce/plugins/emoticons/js/emojis'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  height: {
    type: Number,
    default: 500
  },
  plugins: {
    type: String,
    default: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks visualchars code fullscreen insertdatetime media table wordcount codesample emoticons directionality pagebreak nonbreaking quickbars'
  },
  toolbar: {
    type: [String, Array],
    default: [
      'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough subscript superscript | forecolor backcolor | alignleft aligncenter alignright alignjustify',
      'outdent indent | bullist numlist | link image mediaLibrary media table emoticons charmap | codesample code visualblocks | hr pagebreak anchor | searchreplace visualchars | insertdatetime | ltr rtl | removeformat | preview fullscreen'
    ]
  }
})

const emit = defineEmits(['update:modelValue'])

const editorId = ref('tinymce-' + Math.random().toString(36).substring(7))
const content = ref(props.modelValue)
const mediaSelectorVisible = ref(false)
const mediaSelectorType = ref('all')
let editorInstance = null

// 监听内容变化
watch(() => props.modelValue, (newVal) => {
  if (newVal !== content.value) {
    content.value = newVal
  }
})

watch(content, (newVal) => {
  emit('update:modelValue', newVal)
})

// 打开媒体库选择器
const openMediaSelector = (type = 'all') => {
  mediaSelectorType.value = type
  mediaSelectorVisible.value = true
}

// 处理媒体库文件选择
const handleMediaSelect = (file) => {
  if (!editorInstance) return

  if (file.file_type === 'image') {
    // 插入图片
    editorInstance.insertContent(`<img src="${file.file_url}" alt="${file.file_name}" style="max-width: 100%;" />`)
  } else if (file.file_type === 'video') {
    // 插入视频
    editorInstance.insertContent(`<video controls style="max-width: 100%;"><source src="${file.file_url}" /></video>`)
  } else {
    // 插入文件链接
    editorInstance.insertContent(`<a href="${file.file_url}" target="_blank">${file.file_name}</a>`)
  }
}

// 编辑器配置
const editorConfig = {
  // GPL 开源许可证 - 同意开源许可条款
  license_key: 'gpl',

  // 指定皮肤路径
  skin_url: '/tinymce/skins/ui/oxide',
  content_css: '/tinymce/skins/content/default/content.min.css',

  language: 'zh_CN',
  height: props.height,
  menubar: true,
  plugins: props.plugins,
  toolbar: props.toolbar,

  // 中文语言包配置
  language_url: '/tinymce/langs/zh_CN.js',

  // 编辑器初始化设置
  setup: (editor) => {
    editorInstance = editor

    // 注册"从媒体库选择"按钮
    editor.ui.registry.addButton('mediaLibrary', {
      text: '媒体库',
      icon: 'gallery',
      tooltip: '从媒体库选择文件',
      onAction: () => {
        openMediaSelector('all')
      }
    })

    // 添加菜单项到插入菜单
    editor.ui.registry.addMenuItem('mediaLibrary', {
      text: '从媒体库选择',
      icon: 'gallery',
      onAction: () => {
        openMediaSelector('all')
      }
    })
  },

  // 内容样式
  content_style: `
    body {
      font-family: 'Microsoft YaHei', '微软雅黑', Arial, sans-serif;
      font-size: 14px;
      line-height: 1.6;
    }
    img {
      max-width: 100%;
      height: auto;
    }
    pre {
      background-color: #f4f4f4;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 10px;
      overflow-x: auto;
    }
  `,

  // 图片上传
  images_upload_handler: async (blobInfo, progress) => {
    return new Promise(async (resolve, reject) => {
      try {
        const formData = new FormData()
        formData.append('file', blobInfo.blob(), blobInfo.filename())

        const res = await request({
          url: '/media/upload',
          method: 'post',
          data: formData,
          headers: { 'Content-Type': 'multipart/form-data' },
          onUploadProgress: (progressEvent) => {
            const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
            progress(percentCompleted)
          }
        })

        if (res && res.data && res.data.file_url) {
          resolve(res.data.file_url)
        } else {
          console.error('上传响应数据:', res)
          reject('图片上传失败：未返回图片URL')
        }
      } catch (error) {
        console.error('图片上传错误:', error)
        ElMessage.error('图片上传失败')
        reject('图片上传失败: ' + (error.message || '未知错误'))
      }
    })
  },

  // 自动保存
  auto_save_interval: '30s',

  // 粘贴配置
  paste_data_images: true,
  paste_as_text: false,

  // 代码高亮
  codesample_languages: [
    { text: 'HTML/XML', value: 'markup' },
    { text: 'JavaScript', value: 'javascript' },
    { text: 'CSS', value: 'css' },
    { text: 'PHP', value: 'php' },
    { text: 'Python', value: 'python' },
    { text: 'Java', value: 'java' },
    { text: 'C', value: 'c' },
    { text: 'C++', value: 'cpp' },
    { text: 'C#', value: 'csharp' },
    { text: 'SQL', value: 'sql' }
  ],

  // 移动端响应式
  mobile: {
    menubar: true
  },

  // 快速工具栏
  quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
  quickbars_insert_toolbar: false,

  // 其他配置
  branding: false,
  promotion: false,
  statusbar: true,
  elementpath: false,
  resize: true
}

const handleInit = (evt, editor) => {
  editorInstance = editor
  console.log('TinyMCE 编辑器初始化完成')
}
</script>

<style scoped>
.tinymce-editor {
  width: 100%;
}
</style>
