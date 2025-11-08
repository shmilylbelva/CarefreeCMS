import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src')
    }
  },
  server: {
    port: 3000,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true
      }
    }
  },
  build: {
    // 代码分割优化
    rollupOptions: {
      output: {
        // 手动配置代码分割
        manualChunks: {
          // Vue 核心库
          'vue-vendor': ['vue', 'vue-router', 'pinia'],
          // Element Plus UI 组件库
          'element-plus': ['element-plus'],
          // Element Plus 图标
          'element-icons': ['@element-plus/icons-vue'],
          // TinyMCE 富文本编辑器（较大的库）
          'tinymce': ['tinymce', '@tinymce/tinymce-vue'],
          // Monaco Editor 代码编辑器
          'monaco-editor': ['monaco-editor', '@guolao/vue-monaco-editor'],
          // 其他第三方库
          'vendor': ['axios']
        },
        // 优化文件命名
        chunkFileNames: 'js/[name]-[hash].js',
        entryFileNames: 'js/[name]-[hash].js',
        assetFileNames: '[ext]/[name]-[hash].[ext]'
      }
    },
    // 提高 chunk 大小警告的阈值到 1000kb
    chunkSizeWarningLimit: 1000,
    // 启用 CSS 代码分割
    cssCodeSplit: true,
    // 使用 esbuild 压缩（更快）
    minify: 'esbuild',
    // esbuild 压缩选项
    esbuild: {
      drop: ['console', 'debugger']
    }
  }
})
