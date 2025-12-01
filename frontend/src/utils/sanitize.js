/**
 * HTML 内容安全清理工具
 * 用于防止 XSS 攻击
 */
import DOMPurify from 'dompurify'

/**
 * 清理 HTML 内容，移除危险的标签和属性
 * @param {string} html - 原始 HTML 内容
 * @param {Object} config - DOMPurify 配置选项
 * @returns {string} 清理后的安全 HTML
 */
export function sanitizeHtml(html, config = {}) {
  if (!html || typeof html !== 'string') {
    return ''
  }

  // 默认配置：允许常见的格式化标签，但禁用脚本
  const defaultConfig = {
    ALLOWED_TAGS: [
      'p', 'br', 'span', 'div', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
      'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'a', 'img', 'table', 'thead', 'tbody',
      'tr', 'th', 'td', 'mark', 'del', 'ins', 'sub', 'sup', 'hr'
    ],
    ALLOWED_ATTR: [
      'href', 'title', 'src', 'alt', 'width', 'height', 'class', 'style', 'target',
      'rel', 'data-*'
    ],
    ALLOW_DATA_ATTR: true,
    KEEP_CONTENT: true,
    // 只允许 http/https 协议
    ALLOWED_URI_REGEXP: /^(?:(?:(?:f|ht)tps?|mailto|tel|callto|sms|cid|xmpp):|[^a-z]|[a-z+.\-]+(?:[^a-z+.\-:]|$))/i
  }

  // 合并自定义配置
  const finalConfig = { ...defaultConfig, ...config }

  return DOMPurify.sanitize(html, finalConfig)
}

/**
 * 清理搜索高亮内容（更严格的配置）
 * @param {string} html - 带高亮的 HTML 内容
 * @returns {string} 清理后的安全 HTML
 */
export function sanitizeHighlightedContent(html) {
  return sanitizeHtml(html, {
    ALLOWED_TAGS: ['p', 'span', 'div', 'strong', 'em', 'mark', 'br'],
    ALLOWED_ATTR: ['class', 'style']
  })
}

/**
 * 清理富文本编辑器内容（允许更多标签）
 * @param {string} html - 编辑器生成的 HTML 内容
 * @returns {string} 清理后的安全 HTML
 */
export function sanitizeEditorContent(html) {
  return sanitizeHtml(html, {
    // 使用默认配置，允许更多富文本标签
    ADD_TAGS: ['iframe'], // 如果需要嵌入视频
    ADD_ATTR: ['allowfullscreen', 'frameborder', 'scrolling']
  })
}

/**
 * 移除所有 HTML 标签，只保留纯文本
 * @param {string} html - HTML 内容
 * @returns {string} 纯文本内容
 */
export function stripHtml(html) {
  if (!html || typeof html !== 'string') {
    return ''
  }

  return DOMPurify.sanitize(html, {
    ALLOWED_TAGS: [],
    KEEP_CONTENT: true
  })
}

/**
 * Vue 3 指令：v-safe-html
 * 用法: <div v-safe-html="content"></div>
 */
export const vSafeHtml = {
  mounted(el, binding) {
    const sanitized = sanitizeHtml(binding.value)
    el.innerHTML = sanitized
  },
  updated(el, binding) {
    const sanitized = sanitizeHtml(binding.value)
    el.innerHTML = sanitized
  }
}

/**
 * Vue 3 指令：v-safe-highlight（用于搜索高亮）
 * 用法: <div v-safe-highlight="highlightedContent"></div>
 */
export const vSafeHighlight = {
  mounted(el, binding) {
    const sanitized = sanitizeHighlightedContent(binding.value)
    el.innerHTML = sanitized
  },
  updated(el, binding) {
    const sanitized = sanitizeHighlightedContent(binding.value)
    el.innerHTML = sanitized
  }
}

export default {
  sanitizeHtml,
  sanitizeHighlightedContent,
  sanitizeEditorContent,
  stripHtml,
  vSafeHtml,
  vSafeHighlight
}
