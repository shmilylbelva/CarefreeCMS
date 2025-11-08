/**
 * 系统权限定义
 * 权限结构：菜单 -> 页面 -> 按钮/操作
 */

export const permissions = [
  {
    id: 'dashboard',
    name: '仪表板',
    icon: 'Odometer',
    type: 'menu',
    children: [
      {
        id: 'dashboard.view',
        name: '查看仪表板',
        type: 'page'
      }
    ]
  },
  {
    id: 'content',
    name: '内容管理',
    icon: 'Document',
    type: 'menu',
    children: [
      {
        id: 'articles',
        name: '文章管理',
        type: 'page',
        children: [
          { id: 'articles.view', name: '查看文章列表', type: 'action' },
          { id: 'articles.create', name: '创建文章', type: 'action' },
          { id: 'articles.edit', name: '编辑文章', type: 'action' },
          { id: 'articles.delete', name: '删除文章', type: 'action' },
          { id: 'articles.publish', name: '发布文章', type: 'action' },
          { id: 'articles.offline', name: '下线文章', type: 'action' }
        ]
      },
      {
        id: 'categories',
        name: '分类管理',
        type: 'page',
        children: [
          { id: 'categories.view', name: '查看分类列表', type: 'action' },
          { id: 'categories.create', name: '创建分类', type: 'action' },
          { id: 'categories.edit', name: '编辑分类', type: 'action' },
          { id: 'categories.delete', name: '删除分类', type: 'action' }
        ]
      },
      {
        id: 'tags',
        name: '标签管理',
        type: 'page',
        children: [
          { id: 'tags.view', name: '查看标签列表', type: 'action' },
          { id: 'tags.create', name: '创建标签', type: 'action' },
          { id: 'tags.edit', name: '编辑标签', type: 'action' },
          { id: 'tags.delete', name: '删除标签', type: 'action' }
        ]
      },
      {
        id: 'pages',
        name: '单页管理',
        type: 'page',
        children: [
          { id: 'pages.view', name: '查看单页列表', type: 'action' },
          { id: 'pages.create', name: '创建单页', type: 'action' },
          { id: 'pages.edit', name: '编辑单页', type: 'action' },
          { id: 'pages.delete', name: '删除单页', type: 'action' }
        ]
      },
      {
        id: 'article-flags',
        name: '文章属性管理',
        type: 'page',
        children: [
          { id: 'article-flags.view', name: '查看属性列表', type: 'action' },
          { id: 'article-flags.create', name: '创建属性', type: 'action' },
          { id: 'article-flags.edit', name: '编辑属性', type: 'action' },
          { id: 'article-flags.delete', name: '删除属性', type: 'action' }
        ]
      },
      {
        id: 'topics',
        name: '专题管理',
        type: 'page',
        children: [
          { id: 'topics.view', name: '查看专题列表', type: 'action' },
          { id: 'topics.create', name: '创建专题', type: 'action' },
          { id: 'topics.edit', name: '编辑专题', type: 'action' },
          { id: 'topics.delete', name: '删除专题', type: 'action' }
        ]
      },
      {
        id: 'links',
        name: '友情链接',
        type: 'page',
        children: [
          { id: 'links.view', name: '查看链接列表', type: 'action' },
          { id: 'links.create', name: '创建链接', type: 'action' },
          { id: 'links.edit', name: '编辑链接', type: 'action' },
          { id: 'links.delete', name: '删除链接', type: 'action' }
        ]
      },
      {
        id: 'content-models',
        name: '内容模型',
        type: 'page',
        children: [
          { id: 'content-models.view', name: '查看模型列表', type: 'action' },
          { id: 'content-models.create', name: '创建模型', type: 'action' },
          { id: 'content-models.edit', name: '编辑模型', type: 'action' },
          { id: 'content-models.delete', name: '删除模型', type: 'action' }
        ]
      },
      {
        id: 'custom-fields',
        name: '自定义字段',
        type: 'page',
        children: [
          { id: 'custom-fields.view', name: '查看字段列表', type: 'action' },
          { id: 'custom-fields.create', name: '创建字段', type: 'action' },
          { id: 'custom-fields.edit', name: '编辑字段', type: 'action' },
          { id: 'custom-fields.delete', name: '删除字段', type: 'action' }
        ]
      },
      {
        id: 'recycle-bin',
        name: '回收站',
        type: 'page',
        children: [
          { id: 'recycle-bin.view', name: '查看回收站', type: 'action' },
          { id: 'recycle-bin.restore', name: '恢复内容', type: 'action' },
          { id: 'recycle-bin.delete', name: '彻底删除', type: 'action' }
        ]
      }
    ]
  },
  {
    id: 'media',
    name: '媒体库',
    icon: 'Picture',
    type: 'menu',
    children: [
      {
        id: 'media.view',
        name: '查看媒体文件',
        type: 'page'
      },
      {
        id: 'media.upload',
        name: '上传文件',
        type: 'action'
      },
      {
        id: 'media.delete',
        name: '删除文件',
        type: 'action'
      },
      {
        id: 'media.batch_delete',
        name: '批量删除',
        type: 'action'
      }
    ]
  },
  {
    id: 'seo',
    name: 'SEO管理',
    icon: 'TrendCharts',
    type: 'menu',
    children: [
      {
        id: 'build',
        name: '静态生成',
        type: 'page',
        children: [
          { id: 'build.view', name: '查看生成页面', type: 'action' },
          { id: 'build.all', name: '生成所有页面', type: 'action' },
          { id: 'build.index', name: '生成首页', type: 'action' },
          { id: 'build.articles', name: '生成文章列表', type: 'action' },
          { id: 'build.article', name: '生成文章详情', type: 'action' },
          { id: 'build.category', name: '生成分类页', type: 'action' },
          { id: 'build.page', name: '生成单页面', type: 'action' },
          { id: 'build.logs', name: '查看生成日志', type: 'action' }
        ]
      },
      {
        id: 'sitemap',
        name: 'Sitemap生成',
        type: 'page',
        children: [
          { id: 'sitemap.view', name: '查看Sitemap页面', type: 'action' },
          { id: 'sitemap.generate_all', name: '生成所有格式', type: 'action' },
          { id: 'sitemap.generate_txt', name: '生成TXT', type: 'action' },
          { id: 'sitemap.generate_xml', name: '生成XML', type: 'action' },
          { id: 'sitemap.generate_html', name: '生成HTML', type: 'action' }
        ]
      },
      {
        id: 'seo-settings',
        name: 'SEO设置',
        type: 'page',
        children: [
          { id: 'seo-settings.view', name: '查看SEO设置', type: 'action' },
          { id: 'seo-settings.edit', name: '修改SEO设置', type: 'action' }
        ]
      },
      {
        id: 'seo-redirects',
        name: 'URL重定向',
        type: 'page',
        children: [
          { id: 'seo-redirects.view', name: '查看重定向规则', type: 'action' },
          { id: 'seo-redirects.create', name: '创建重定向', type: 'action' },
          { id: 'seo-redirects.edit', name: '编辑重定向', type: 'action' },
          { id: 'seo-redirects.delete', name: '删除重定向', type: 'action' }
        ]
      },
      {
        id: 'seo-404-logs',
        name: '404错误监控',
        type: 'page',
        children: [
          { id: 'seo-404-logs.view', name: '查看404日志', type: 'action' },
          { id: 'seo-404-logs.delete', name: '删除日志', type: 'action' }
        ]
      },
      {
        id: 'seo-robots',
        name: 'Robots.txt',
        type: 'page',
        children: [
          { id: 'seo-robots.view', name: '查看Robots配置', type: 'action' },
          { id: 'seo-robots.edit', name: '编辑Robots', type: 'action' }
        ]
      },
      {
        id: 'seo-tools',
        name: 'SEO工具',
        type: 'page',
        children: [
          { id: 'seo-tools.view', name: '查看SEO工具', type: 'action' },
          { id: 'seo-tools.use', name: '使用工具', type: 'action' }
        ]
      }
    ]
  },
  {
    id: 'system',
    name: '系统管理',
    icon: 'Setting',
    type: 'menu',
    children: [
      {
        id: 'config',
        name: '基本信息',
        type: 'page',
        children: [
          { id: 'config.view', name: '查看配置', type: 'action' },
          { id: 'config.edit', name: '修改配置', type: 'action' }
        ]
      },
      {
        id: 'users',
        name: '用户管理',
        type: 'page',
        children: [
          { id: 'users.view', name: '查看用户列表', type: 'action' },
          { id: 'users.create', name: '创建用户', type: 'action' },
          { id: 'users.edit', name: '编辑用户', type: 'action' },
          { id: 'users.delete', name: '删除用户', type: 'action' },
          { id: 'users.reset_password', name: '重置密码', type: 'action' }
        ]
      },
      {
        id: 'roles',
        name: '角色管理',
        type: 'page',
        children: [
          { id: 'roles.view', name: '查看角色列表', type: 'action' },
          { id: 'roles.create', name: '创建角色', type: 'action' },
          { id: 'roles.edit', name: '编辑角色', type: 'action' },
          { id: 'roles.delete', name: '删除角色', type: 'action' },
          { id: 'roles.set_permissions', name: '设置权限', type: 'action' }
        ]
      },
      {
        id: 'database',
        name: '数据库管理',
        type: 'page',
        children: [
          { id: 'database.view', name: '查看数据库信息', type: 'action' },
          { id: 'database.backup', name: '备份数据库', type: 'action' },
          { id: 'database.restore', name: '恢复数据库', type: 'action' },
          { id: 'database.optimize', name: '优化数据库', type: 'action' }
        ]
      },
      {
        id: 'cache',
        name: '缓存管理',
        type: 'page',
        children: [
          { id: 'cache.view', name: '查看缓存信息', type: 'action' },
          { id: 'cache.clear', name: '清理缓存', type: 'action' }
        ]
      },
      {
        id: 'system-logs',
        name: '系统日志',
        type: 'page',
        children: [
          { id: 'system-logs.view', name: '查看系统日志', type: 'action' },
          { id: 'system-logs.delete', name: '删除日志', type: 'action' }
        ]
      },
      {
        id: 'operation-logs',
        name: '操作日志',
        type: 'page',
        children: [
          { id: 'operation-logs.view', name: '查看操作日志', type: 'action' },
          { id: 'operation-logs.delete', name: '删除日志', type: 'action' }
        ]
      }
    ]
  },
  {
    id: 'template',
    name: '模板管理',
    icon: 'DocumentCopy',
    type: 'menu',
    children: [
      {
        id: 'template-editor',
        name: '模板编辑器',
        type: 'page',
        children: [
          { id: 'template-editor.view', name: '查看模板', type: 'action' },
          { id: 'template-editor.edit', name: '编辑模板', type: 'action' }
        ]
      },
      {
        id: 'template-tags',
        name: '模板标签教程',
        type: 'page',
        children: [
          { id: 'template-tags.view', name: '查看标签教程', type: 'action' }
        ]
      }
    ]
  },
  {
    id: 'extensions',
    name: '扩展功能',
    icon: 'Grid',
    type: 'menu',
    children: [
      {
        id: 'ads',
        name: '广告管理',
        type: 'page',
        children: [
          { id: 'ads.view', name: '查看广告列表', type: 'action' },
          { id: 'ads.create', name: '创建广告', type: 'action' },
          { id: 'ads.edit', name: '编辑广告', type: 'action' },
          { id: 'ads.delete', name: '删除广告', type: 'action' }
        ]
      },
      {
        id: 'sliders',
        name: '幻灯片管理',
        type: 'page',
        children: [
          { id: 'sliders.view', name: '查看幻灯片列表', type: 'action' },
          { id: 'sliders.create', name: '创建幻灯片', type: 'action' },
          { id: 'sliders.edit', name: '编辑幻灯片', type: 'action' },
          { id: 'sliders.delete', name: '删除幻灯片', type: 'action' }
        ]
      },
      {
        id: 'front-users',
        name: '会员列表',
        type: 'page',
        children: [
          { id: 'front-users.view', name: '查看会员列表', type: 'action' },
          { id: 'front-users.edit', name: '编辑会员', type: 'action' },
          { id: 'front-users.delete', name: '删除会员', type: 'action' },
          { id: 'front-users.set_vip', name: '设置VIP', type: 'action' },
          { id: 'front-users.adjust_points', name: '调整积分', type: 'action' }
        ]
      },
      {
        id: 'member-levels',
        name: '会员等级',
        type: 'page',
        children: [
          { id: 'member-levels.view', name: '查看等级列表', type: 'action' },
          { id: 'member-levels.create', name: '创建等级', type: 'action' },
          { id: 'member-levels.edit', name: '编辑等级', type: 'action' },
          { id: 'member-levels.delete', name: '删除等级', type: 'action' }
        ]
      },
      {
        id: 'contributions',
        name: '投稿管理',
        type: 'page',
        children: [
          { id: 'contributions.view', name: '查看投稿列表', type: 'action' },
          { id: 'contributions.audit', name: '审核投稿', type: 'action' },
          { id: 'contributions.delete', name: '删除投稿', type: 'action' }
        ]
      },
      {
        id: 'notifications',
        name: '消息通知管理',
        type: 'page',
        children: [
          { id: 'notifications.view', name: '查看通知列表', type: 'action' },
          { id: 'notifications.send', name: '发送通知', type: 'action' },
          { id: 'notifications.template', name: '管理模板', type: 'action' },
          { id: 'notifications.delete', name: '删除通知', type: 'action' }
        ]
      },
      {
        id: 'sms-service',
        name: '短信服务管理',
        type: 'page',
        children: [
          { id: 'sms-service.view', name: '查看短信日志', type: 'action' },
          { id: 'sms-service.config', name: '配置短信服务', type: 'action' },
          { id: 'sms-service.send', name: '发送短信', type: 'action' }
        ]
      },
      {
        id: 'point-shop',
        name: '积分商城管理',
        type: 'page',
        children: [
          { id: 'point-shop.view', name: '查看商品列表', type: 'action' },
          { id: 'point-shop.create', name: '创建商品', type: 'action' },
          { id: 'point-shop.edit', name: '编辑商品', type: 'action' },
          { id: 'point-shop.delete', name: '删除商品', type: 'action' },
          { id: 'point-shop.orders', name: '管理订单', type: 'action' }
        ]
      }
    ]
  }
]

/**
 * 获取所有权限ID
 */
export function getAllPermissionIds() {
  const ids = []

  function traverse(items) {
    items.forEach(item => {
      ids.push(item.id)
      if (item.children) {
        traverse(item.children)
      }
    })
  }

  traverse(permissions)
  return ids
}

/**
 * 获取权限树（用于权限选择器）
 */
export function getPermissionTree() {
  return permissions
}

/**
 * 扁平化权限列表
 */
export function getFlatPermissions() {
  const flat = []

  function traverse(items, parent = null) {
    items.forEach(item => {
      flat.push({
        ...item,
        parent: parent?.id || null
      })
      if (item.children) {
        traverse(item.children, item)
      }
    })
  }

  traverse(permissions)
  return flat
}
