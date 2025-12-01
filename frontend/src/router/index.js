import { createRouter, createWebHistory } from 'vue-router'
import { getToken } from '@/utils/auth'
import { useUserStore } from '@/store/user'
import { usePermissionStore } from '@/store/permission'
import { ElMessage } from 'element-plus'

// 路由配置
const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/Login.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/',
    component: () => import('@/layouts/MainLayout.vue'),
    redirect: '/dashboard',
    meta: { requiresAuth: true },
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: () => import('@/views/Dashboard.vue'),
        meta: { title: '仪表板' }
      },
      {
        path: 'articles',
        name: 'ArticleList',
        component: () => import('@/views/article/List.vue'),
        meta: { title: '文章列表', permission: 'article.view' }
      },
      {
        path: 'articles/create',
        name: 'ArticleCreate',
        component: () => import('@/views/article/Edit.vue'),
        meta: { title: '创建文章', permission: 'article.create' }
      },
      {
        path: 'articles/edit/:id',
        name: 'ArticleEdit',
        component: () => import('@/views/article/Edit.vue'),
        meta: { title: '编辑文章', permission: 'article.edit' }
      },
      {
        path: 'categories',
        name: 'CategoryList',
        component: () => import('@/views/category/List.vue'),
        meta: { title: '分类管理', permission: 'category.view' }
      },
      {
        path: 'tags',
        name: 'TagList',
        component: () => import('@/views/tag/List.vue'),
        meta: { title: '标签管理', permission: 'tag.view' }
      },
      {
        path: 'article-flags',
        name: 'ArticleFlagList',
        component: () => import('@/views/articleFlag/List.vue'),
        meta: { title: '文章属性管理', permission: 'article.view' }
      },
      {
        path: 'pages',
        name: 'PageList',
        component: () => import('@/views/page/List.vue'),
        meta: { title: '单页管理', permission: 'page.view' }
      },
      {
        path: 'pages/create',
        name: 'PageCreate',
        component: () => import('@/views/page/Edit.vue'),
        meta: { title: '创建单页', permission: 'page.create' }
      },
      {
        path: 'pages/edit/:id',
        name: 'PageEdit',
        component: () => import('@/views/page/Edit.vue'),
        meta: { title: '编辑单页', permission: 'page.edit' }
      },
      {
        path: 'media',
        name: 'MediaList',
        component: () => import('@/views/media/List.vue'),
        meta: { title: '媒体库', permission: 'media.view' }
      },
      {
        path: 'media/storage',
        name: 'StorageConfig',
        component: () => import('@/components/MediaLibrary/StorageConfig.vue'),
        meta: { title: '存储配置', permission: 'media.storage' }
      },
      {
        path: 'media/queue',
        name: 'QueueMonitor',
        component: () => import('@/components/MediaLibrary/QueueMonitor.vue'),
        meta: { title: '队列监控', permission: 'media.queue' }
      },
      {
        path: 'media/ai-generate',
        name: 'AiImageGenerate',
        component: () => import('@/views/media/AiGenerate.vue'),
        meta: { title: 'AI图片生成', permission: 'media.ai' }
      },
      {
        path: 'media/watermark',
        name: 'MediaWatermark',
        component: () => import('@/views/media/Watermark.vue'),
        meta: { title: '水印管理', permission: 'media.watermark' }
      },
      {
        path: 'media/thumbnail',
        name: 'MediaThumbnail',
        component: () => import('@/views/media/Thumbnail.vue'),
        meta: { title: '缩略图管理', permission: 'media.thumbnail' }
      },
      {
        path: 'build',
        name: 'StaticBuild',
        component: () => import('@/views/build/Index.vue'),
        meta: { title: '静态生成', permission: 'build.view' }
      },
      {
        path: 'sitemap',
        name: 'SitemapIndex',
        component: () => import('@/views/sitemap/Index.vue'),
        meta: { title: 'Sitemap生成', permission: 'sitemap.view' }
      },
      {
        path: 'sites',
        name: 'SiteList',
        component: () => import('@/views/site/List.vue'),
        meta: { title: '多站点管理', permission: 'site.view' }
      },
      {
        path: 'template-packages',
        name: 'TemplatePackageList',
        component: () => import('@/views/templatePackage/List.vue'),
        meta: { title: '模板包管理', permission: 'template.view' }
      },
      {
        path: 'template-types',
        name: 'TemplateTypeList',
        component: () => import('@/views/templateType/index.vue'),
        meta: { title: '模板类型管理', permission: 'template.view' }
      },
      {
        path: 'users',
        name: 'UserPermission',
        component: () => import('@/views/user/Permission.vue'),
        meta: { title: '用户权限', permission: 'user.view' }
      },
      {
        path: 'profile',
        name: 'Profile',
        component: () => import('@/views/Profile.vue'),
        meta: { title: '个人信息' }
      },
      {
        path: 'recycle-bin',
        name: 'RecycleBin',
        component: () => import('@/views/RecycleBin.vue'),
        meta: { title: '回收站' }
      },
      {
        path: 'content-models',
        name: 'ContentModelList',
        component: () => import('@/views/contentModel/List.vue'),
        meta: { title: '内容模型' }
      },
      {
        path: 'custom-fields',
        name: 'CustomFieldList',
        component: () => import('@/views/customField/List.vue'),
        meta: { title: '自定义字段' }
      },
      {
        path: 'topics',
        name: 'TopicList',
        component: () => import('@/views/topic/List.vue'),
        meta: { title: '专题管理' }
      },
      // ========== 评论管理 ==========
      {
        path: 'comments',
        name: 'CommentList',
        component: () => import('@/views/comment/List.vue'),
        meta: { title: '评论管理' }
      },
      {
        path: 'comment-statistics',
        name: 'CommentStatistics',
        component: () => import('@/views/comment/Statistics.vue'),
        meta: { title: '评论统计' }
      },
      {
        path: 'comment-reports',
        name: 'CommentReports',
        component: () => import('@/views/comment/Reports.vue'),
        meta: { title: '举报管理' }
      },
      {
        path: 'comment-emojis',
        name: 'CommentEmojis',
        component: () => import('@/views/comment/Emojis.vue'),
        meta: { title: '表情管理' }
      },
      {
        path: 'links',
        name: 'LinkList',
        component: () => import('@/views/link/List.vue'),
        meta: { title: '友情链接' }
      },
      {
        path: 'ads',
        name: 'AdList',
        component: () => import('@/views/ad/List.vue'),
        meta: { title: '广告管理' }
      },
      {
        path: 'sliders',
        name: 'SliderList',
        component: () => import('@/views/slider/List.vue'),
        meta: { title: '幻灯片管理' }
      },
      {
        path: 'seo-settings',
        name: 'SeoSettings',
        component: () => import('@/views/seo/Settings.vue'),
        meta: { title: 'SEO设置' }
      },
      {
        path: 'seo-redirects',
        name: 'SeoRedirect',
        component: () => import('@/views/seo/Redirect.vue'),
        meta: { title: 'URL重定向' }
      },
      {
        path: 'seo-404-logs',
        name: 'Seo404Log',
        component: () => import('@/views/seo/Log404.vue'),
        meta: { title: '404错误监控' }
      },
      {
        path: 'seo-robots',
        name: 'SeoRobots',
        component: () => import('@/views/seo/Robots.vue'),
        meta: { title: 'Robots.txt' }
      },
      {
        path: 'seo-tools',
        name: 'SeoTools',
        component: () => import('@/views/seo/Tools.vue'),
        meta: { title: 'SEO工具' }
      },
      // AI文章生成
      {
        path: 'ai-providers',
        name: 'AiProviderList',
        component: () => import('@/views/ai/ProviderList.vue'),
        meta: { title: 'AI厂商管理', permission: 'ai_provider.view' }
      },
      {
        path: 'ai-models',
        name: 'AiModelList',
        component: () => import('@/views/ai/ModelList.vue'),
        meta: { title: 'AI模型管理', permission: 'ai_model.view' }
      },
      {
        path: 'ai-prompts',
        name: 'AiPromptTemplateList',
        component: () => import('@/views/ai/PromptTemplateList.vue'),
        meta: { title: '提示词模板', permission: 'ai_prompt.view' }
      },
      {
        path: 'ai-configs',
        name: 'AiConfigList',
        component: () => import('@/views/ai/ConfigList.vue'),
        meta: { title: 'AI配置管理', permission: 'ai_config.view' }
      },
      {
        path: 'ai-tasks',
        name: 'AiTaskList',
        component: () => import('@/views/ai/TaskList.vue'),
        meta: { title: 'AI文章生成', permission: 'ai_article.view' }
      },
      {
        path: 'database',
        name: 'Database',
        component: () => import('@/views/system/Database.vue'),
        meta: { title: '数据库管理' }
      },
      {
        path: 'cache',
        name: 'Cache',
        component: () => import('@/views/system/Cache.vue'),
        meta: { title: '缓存管理' }
      },
      {
        path: 'system-logs',
        name: 'SystemLogs',
        component: () => import('@/views/system/Logs.vue'),
        meta: { title: '系统日志' }
      },
      {
        path: 'cron-jobs',
        name: 'CronJobs',
        component: () => import('@/views/system/CronJob.vue'),
        meta: { title: '定时任务' }
      },
      {
        path: 'oauth-config',
        name: 'OAuthConfig',
        component: () => import('@/views/system/OAuthConfig.vue'),
        meta: { title: 'OAuth配置' }
      },
      {
        path: 'sensitive-words',
        name: 'SensitiveWords',
        component: () => import('@/views/system/SensitiveWords.vue'),
        meta: { title: '敏感词管理' }
      },
      {
        path: 'content-violations',
        name: 'ContentViolations',
        component: () => import('@/views/system/ContentViolations.vue'),
        meta: { title: '违规记录' }
      },
      {
        path: 'template-editor',
        name: 'TemplateEditor',
        component: () => import('@/views/template/Editor.vue'),
        meta: { title: '模板编辑器' }
      },
      {
        path: 'template-tags',
        name: 'TemplateTags',
        component: () => import('@/views/template/TagGuide.vue'),
        meta: { title: '模板标签教程' }
      },
      // 会员管理
      {
        path: 'front-users',
        name: 'FrontUserList',
        component: () => import('@/views/extensions/FrontUsers.vue'),
        meta: { title: '会员列表' }
      },
      {
        path: 'member-levels',
        name: 'MemberLevelManage',
        component: () => import('@/views/extensions/MemberLevels.vue'),
        meta: { title: '会员等级' }
      },
      // 内容管理 - 投稿
      {
        path: 'contributions',
        name: 'ContributionManage',
        component: () => import('@/views/extensions/Contributions.vue'),
        meta: { title: '投稿管理' }
      },
      // 系统管理 - 通知和服务
      {
        path: 'notifications',
        name: 'NotificationManage',
        component: () => import('@/views/extensions/Notifications.vue'),
        meta: { title: '消息通知管理' }
      },
      {
        path: 'sms-service',
        name: 'SmsService',
        component: () => import('@/views/extensions/SmsService.vue'),
        meta: { title: '短信服务管理' }
      },
      // 扩展功能 - 预留
      {
        path: 'point-shop',
        name: 'PointShopManage',
        component: () => import('@/views/extensions/PointShop.vue'),
        meta: { title: '积分商城管理' }
      }
    ]
  }
]

// 创建路由实例
const router = createRouter({
  history: createWebHistory(),
  routes
})

// 防止重复显示错误消息的标志
let isShowingAuthError = false

// 全局路由守卫 - 登录检查
router.beforeEach(async (to, from, next) => {
  // 设置页面标题
  document.title = to.meta.title ? `${to.meta.title} - 逍遥内容管理系统` : '逍遥内容管理系统'

  const token = getToken()
  const userStore = useUserStore()

  // 白名单路由（不需要登录即可访问）
  const whiteList = ['/login']

  if (token) {
    // 已登录
    if (to.path === '/login') {
      // 如果已登录，访问登录页则跳转到首页
      next('/')
    } else {
      // 检查是否已获取用户信息
      if (!userStore.userInfo) {
        try {
          // 获取用户信息
          await userStore.getUserInfo()
          next()
        } catch (error) {
          // 获取用户信息失败，可能 token 已失效
          console.error('获取用户信息失败:', error)

          // 防止重复显示错误消息
          if (!isShowingAuthError) {
            isShowingAuthError = true
            ElMessage.error('登录已过期，请重新登录')
            setTimeout(() => {
              isShowingAuthError = false
            }, 3000)
          }

          await userStore.logout()
          next(`/login?redirect=${to.path}`)
        }
      } else {
        // 检查页面权限
        if (to.meta.permission) {
          const permissionStore = usePermissionStore()
          const hasPermission = permissionStore.hasPermission(to.meta.permission)

          if (!hasPermission) {
            ElMessage.error('您没有权限访问此页面')
            next('/dashboard')
            return
          }
        }
        next()
      }
    }
  } else {
    // 未登录
    if (whiteList.includes(to.path)) {
      // 在白名单中，直接放行
      next()
    } else {
      // 不在白名单中，跳转到登录页，并保存原始路径用于登录后跳转
      // 只在不是来自登录页的情况下显示提示
      if (from.path !== '/login' && !isShowingAuthError) {
        isShowingAuthError = true
        ElMessage.warning('请先登录')
        setTimeout(() => {
          isShowingAuthError = false
        }, 3000)
      }
      next(`/login?redirect=${to.path}`)
    }
  }
})

export default router
