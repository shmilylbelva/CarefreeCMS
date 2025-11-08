import { createApp } from 'vue'
import ElementPlus from 'element-plus'
import 'element-plus/dist/index.css'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import zhCn from 'element-plus/es/locale/lang/zh-cn'

import App from './App.vue'
import router from './router'
import pinia from './store'
import permissionDirective from './directives/permission'

const app = createApp(App)

// 注册权限指令
app.directive('permission', permissionDirective)

// 注册所有 Element Plus 图标
for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
  app.component(key, component)
}

// 使用插件
app.use(ElementPlus, { locale: zhCn })
app.use(router)
app.use(pinia)

// 挂载应用
app.mount('#app')
