<template>
  <el-container class="layout-container">
    <!-- 侧边栏 -->
    <el-aside width="200px">
      <div class="logo">
        <h3>逍遥CMS</h3>
      </div>
      <el-menu
        :default-active="activeMenu"
        :router="true"
        background-color="#304156"
        text-color="#bfcbd9"
        active-text-color="#409EFF"
      >
        <el-menu-item index="/dashboard">
          <el-icon><Odometer /></el-icon>
          <span>仪表板</span>
        </el-menu-item>

        <el-sub-menu index="content">
          <template #title>
            <el-icon><Document /></el-icon>
            <span>内容管理</span>
          </template>
          <el-menu-item index="/articles">文章列表</el-menu-item>
          <el-menu-item index="/ai-tasks">批量生成文章</el-menu-item>
          <el-menu-item index="/categories">分类管理</el-menu-item>
          <el-menu-item index="/tags">标签管理</el-menu-item>
          <el-menu-item index="/article-flags">文章属性</el-menu-item>
          <el-menu-item index="/topics">专题管理</el-menu-item>
          <el-menu-item index="/contributions">投稿管理</el-menu-item>
          <el-menu-item index="/pages">单页管理</el-menu-item>
          <el-menu-item index="/links">友情链接</el-menu-item>
          <el-menu-item index="/ads">广告管理</el-menu-item>
          <el-menu-item index="/sliders">幻灯片管理</el-menu-item>
        </el-sub-menu>

        <el-menu-item index="/media">
          <el-icon><Picture /></el-icon>
          <span>媒体库</span>
        </el-menu-item>

        <el-sub-menu v-if="hasAnyAiPermission" index="ai">
          <template #title>
            <el-icon><MagicStick /></el-icon>
            <span>AI管理</span>
          </template>
          <el-menu-item v-if="hasPermission('ai_provider.view')" index="/ai-providers">AI厂商管理</el-menu-item>
          <el-menu-item v-if="hasPermission('ai_model.view')" index="/ai-models">AI模型管理</el-menu-item>
          <el-menu-item v-if="hasPermission('ai_prompt.view')" index="/ai-prompts">提示词模板</el-menu-item>
          <el-menu-item v-if="hasPermission('ai_config.view')" index="/ai-configs">AI配置管理</el-menu-item>
        </el-sub-menu>

        <el-sub-menu index="seo">
          <template #title>
            <el-icon><TrendCharts /></el-icon>
            <span>SEO管理</span>
          </template>
          <el-menu-item index="/build">静态生成</el-menu-item>
          <el-menu-item index="/sitemap">Sitemap生成</el-menu-item>
          <el-menu-item index="/seo-redirects">URL重定向</el-menu-item>
          <el-menu-item index="/seo-404-logs">404监控</el-menu-item>
          <el-menu-item index="/seo-robots">Robots.txt</el-menu-item>
          <el-menu-item index="/seo-tools">SEO分析工具</el-menu-item>
        </el-sub-menu>

        <el-sub-menu index="member">
          <template #title>
            <el-icon><User /></el-icon>
            <span>会员管理</span>
          </template>
          <el-menu-item index="/front-users">会员列表</el-menu-item>
          <el-menu-item index="/member-levels">会员等级</el-menu-item>
        </el-sub-menu>

        <el-sub-menu index="comment">
          <template #title>
            <el-icon><ChatDotRound /></el-icon>
            <span>评论管理</span>
          </template>
          <el-menu-item index="/comments">评论列表</el-menu-item>
          <el-menu-item index="/comment-statistics">评论统计</el-menu-item>
          <el-menu-item index="/comment-reports">举报管理</el-menu-item>
          <el-menu-item index="/comment-emojis">表情管理</el-menu-item>
        </el-sub-menu>

        <el-sub-menu index="template">
          <template #title>
            <el-icon><Files /></el-icon>
            <span>模板管理</span>
          </template>
          <el-menu-item index="/template-packages">模板包管理</el-menu-item>
          <el-menu-item index="/template-types">模板类型管理</el-menu-item>
          <el-menu-item index="/template-editor">模板编辑器</el-menu-item>
          <el-menu-item index="/template-tags">模板标签教程</el-menu-item>
        </el-sub-menu>

        <el-sub-menu index="system">
          <template #title>
            <el-icon><Setting /></el-icon>
            <span>系统管理</span>
          </template>
          <el-menu-item index="/sites">多站点管理</el-menu-item>
          <el-menu-item index="/users">用户权限</el-menu-item>
          <el-menu-item index="/notifications">消息通知</el-menu-item>
          <el-menu-item index="/sms-service">短信服务</el-menu-item>
          <el-menu-item index="/content-models">内容模型</el-menu-item>
          <el-menu-item index="/database">数据库管理</el-menu-item>
          <el-menu-item index="/cache">缓存管理</el-menu-item>
          <el-menu-item index="/system-logs">运行日志</el-menu-item>
          <el-menu-item index="/recycle-bin">回收站</el-menu-item>
        </el-sub-menu>
      </el-menu>
    </el-aside>

    <!-- 主内容区 -->
    <el-container>
      <!-- 顶部导航栏 -->
      <el-header>
        <div class="header-content">
          <div class="left"></div>
          <div class="right">
            <el-dropdown @command="handleCommand">
              <span class="user-info">
                <el-icon><User /></el-icon>
                {{ userStore.userInfo?.username || '用户' }}
                <el-icon><ArrowDown /></el-icon>
              </span>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="profile">个人信息</el-dropdown-item>
                  <el-dropdown-item command="logout" divided>退出登录</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </div>
        </div>
      </el-header>

      <!-- 主内容 -->
      <el-main>
        <router-view />
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ElMessageBox, ElMessage } from 'element-plus'
import {
  Odometer,
  Document,
  Picture,
  TrendCharts,
  Setting,
  Tools,
  Files,
  User,
  ArrowDown,
  ChatDotRound,
  MagicStick
} from '@element-plus/icons-vue'
import { useUserStore } from '@/store/user'
import { usePermissionStore } from '@/store/permission'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const permissionStore = usePermissionStore()

// 当前激活的菜单
const activeMenu = computed(() => {
  const { path } = route
  return path
})

// 权限检查方法
const hasPermission = (permission) => {
  return permissionStore.hasPermission(permission)
}

// 检查是否有任意AI权限
const hasAnyAiPermission = computed(() => {
  return hasPermission('ai_config.view') ||
         hasPermission('ai_provider.view') ||
         hasPermission('ai_model.view') ||
         hasPermission('ai_prompt.view') ||
         hasPermission('ai_article.view') ||
         hasPermission('ai_image.view')
})

// 下拉菜单命令处理
const handleCommand = async (command) => {
  if (command === 'logout') {
    try {
      await ElMessageBox.confirm('确定要退出登录吗？', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
      })
      await userStore.logout()
      ElMessage.success('退出登录成功')
      router.push('/login')
    } catch (error) {
      // 用户取消操作
    }
  } else if (command === 'profile') {
    router.push('/profile')
  }
}
</script>

<style scoped>
.layout-container {
  height: 100vh;
}

.el-aside {
  background-color: #304156;
  color: #bfcbd9;
}

.logo {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 18px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo h3 {
  margin: 0;
}

.el-menu {
  border: none;
}

.el-header {
  background-color: #fff;
  box-shadow: 0 1px 4px rgba(0, 21, 41, 0.08);
  display: flex;
  align-items: center;
  padding: 0 20px;
}

.header-content {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.user-info {
  display: flex;
  align-items: center;
  cursor: pointer;
  padding: 0 10px;
}

.user-info:hover {
  background-color: #f5f5f5;
  border-radius: 4px;
}

.user-info .el-icon {
  margin: 0 5px;
}

.el-main {
  background-color: #f0f2f5;
  padding: 20px;
}
</style>
