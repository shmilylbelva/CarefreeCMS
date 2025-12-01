/**
 * 系统权限定义
 * 基于后端权限配置 backend/database/permissions_config.md
 * 更新时间: 2025-11-30
 */

export const permissions = [
  // 1. 仪表盘
  {
    id: 'dashboard',
    name: '仪表盘',
    icon: 'Odometer',
    type: 'menu',
    children: [
      { id: 'dashboard.view', name: '查看仪表盘', type: 'action' },
      { id: 'dashboard.stats', name: '查看统计数据', type: 'action' }
    ]
  },

  // 2. 内容管理
  {
    id: 'content',
    name: '内容管理',
    icon: 'Document',
    type: 'menu',
    children: [
      {
        id: 'article',
        name: '文章管理',
        type: 'page',
        children: [
          { id: 'article.view', name: '查看文章列表', type: 'action' },
          { id: 'article.read', name: '查看文章详情', type: 'action' },
          { id: 'article.create', name: '创建文章', type: 'action' },
          { id: 'article.edit', name: '编辑所有文章', type: 'action' },
          { id: 'article.edit_own', name: '只能编辑自己的文章', type: 'action' },
          { id: 'article.delete', name: '删除文章', type: 'action' },
          { id: 'article.publish', name: '发布/下线文章', type: 'action' },
          { id: 'article.batch', name: '批量操作', type: 'action' },
          { id: 'article.export', name: '导出文章', type: 'action' },
          { id: 'article.flag', name: '管理文章标记', type: 'action' },
          { id: 'article.version', name: '版本管理', type: 'action' }
        ]
      },
      {
        id: 'category',
        name: '分类管理',
        type: 'page',
        children: [
          { id: 'category.view', name: '查看分类列表', type: 'action' },
          { id: 'category.read', name: '查看分类详情', type: 'action' },
          { id: 'category.create', name: '创建分类', type: 'action' },
          { id: 'category.edit', name: '编辑分类', type: 'action' },
          { id: 'category.delete', name: '删除分类', type: 'action' },
          { id: 'category.sort', name: '调整分类排序', type: 'action' }
        ]
      },
      {
        id: 'tag',
        name: '标签管理',
        type: 'page',
        children: [
          { id: 'tag.view', name: '查看标签列表', type: 'action' },
          { id: 'tag.read', name: '查看标签详情', type: 'action' },
          { id: 'tag.create', name: '创建标签', type: 'action' },
          { id: 'tag.edit', name: '编辑标签', type: 'action' },
          { id: 'tag.delete', name: '删除标签', type: 'action' },
          { id: 'tag.merge', name: '合并标签', type: 'action' }
        ]
      },
      {
        id: 'page',
        name: '单页管理',
        type: 'page',
        children: [
          { id: 'page.view', name: '查看单页列表', type: 'action' },
          { id: 'page.read', name: '查看单页详情', type: 'action' },
          { id: 'page.create', name: '创建单页', type: 'action' },
          { id: 'page.edit', name: '编辑单页', type: 'action' },
          { id: 'page.delete', name: '删除单页', type: 'action' }
        ]
      },
      {
        id: 'topic',
        name: '专题管理',
        type: 'page',
        children: [
          { id: 'topic.view', name: '查看专题列表', type: 'action' },
          { id: 'topic.read', name: '查看专题详情', type: 'action' },
          { id: 'topic.create', name: '创建专题', type: 'action' },
          { id: 'topic.edit', name: '编辑专题', type: 'action' },
          { id: 'topic.delete', name: '删除专题', type: 'action' },
          { id: 'topic.article', name: '管理专题文章关联', type: 'action' }
        ]
      },
      {
        id: 'custom_field',
        name: '自定义字段',
        type: 'page',
        children: [
          { id: 'custom_field.view', name: '查看自定义字段', type: 'action' },
          { id: 'custom_field.create', name: '创建自定义字段', type: 'action' },
          { id: 'custom_field.edit', name: '编辑自定义字段', type: 'action' },
          { id: 'custom_field.delete', name: '删除自定义字段', type: 'action' }
        ]
      },
      {
        id: 'content_model',
        name: '内容模型',
        type: 'page',
        children: [
          { id: 'content_model.view', name: '查看内容模型', type: 'action' },
          { id: 'content_model.create', name: '创建内容模型', type: 'action' },
          { id: 'content_model.edit', name: '编辑内容模型', type: 'action' },
          { id: 'content_model.delete', name: '删除内容模型', type: 'action' }
        ]
      }
    ]
  },

  // 3. 媒体管理
  {
    id: 'media',
    name: '媒体库',
    icon: 'Picture',
    type: 'menu',
    children: [
      {
        id: 'media-library',
        name: '媒体文件',
        type: 'page',
        children: [
          { id: 'media.view', name: '查看媒体库', type: 'action' },
          { id: 'media.upload', name: '上传文件', type: 'action' },
          { id: 'media.edit', name: '编辑媒体', type: 'action' },
          { id: 'media.delete', name: '删除媒体', type: 'action' },
          { id: 'media.move', name: '移动媒体', type: 'action' },
          { id: 'media.download', name: '下载媒体', type: 'action' }
        ]
      },
      {
        id: 'watermark',
        name: '水印管理',
        type: 'page',
        children: [
          { id: 'watermark.view', name: '查看水印预设', type: 'action' },
          { id: 'watermark.create', name: '创建水印预设', type: 'action' },
          { id: 'watermark.edit', name: '编辑水印预设', type: 'action' },
          { id: 'watermark.delete', name: '删除水印预设', type: 'action' }
        ]
      },
      {
        id: 'thumbnail',
        name: '缩略图管理',
        type: 'page',
        children: [
          { id: 'thumbnail.view', name: '查看缩略图预设', type: 'action' },
          { id: 'thumbnail.create', name: '创建缩略图预设', type: 'action' },
          { id: 'thumbnail.edit', name: '编辑缩略图预设', type: 'action' },
          { id: 'thumbnail.delete', name: '删除缩略图预设', type: 'action' }
        ]
      },
      {
        id: 'video',
        name: '视频处理',
        type: 'page',
        children: [
          { id: 'video.view', name: '查看视频处理记录', type: 'action' },
          { id: 'video.transcode', name: '转码视频', type: 'action' },
          { id: 'video.poster', name: '生成视频封面', type: 'action' }
        ]
      }
    ]
  },

  // 4. AI管理
  {
    id: 'ai',
    name: 'AI管理',
    icon: 'MagicStick',
    type: 'menu',
    children: [
      {
        id: 'ai-config',
        name: 'AI配置管理',
        type: 'page',
        children: [
          { id: 'ai_config.view', name: '查看AI配置', type: 'action' },
          { id: 'ai_config.edit', name: '编辑AI配置', type: 'action' }
        ]
      },
      {
        id: 'ai-provider',
        name: 'AI供应商管理',
        type: 'page',
        children: [
          { id: 'ai_provider.view', name: '查看供应商列表', type: 'action' },
          { id: 'ai_provider.create', name: '创建供应商', type: 'action' },
          { id: 'ai_provider.edit', name: '编辑供应商', type: 'action' },
          { id: 'ai_provider.delete', name: '删除供应商', type: 'action' }
        ]
      },
      {
        id: 'ai-model',
        name: 'AI模型管理',
        type: 'page',
        children: [
          { id: 'ai_model.view', name: '查看模型列表', type: 'action' },
          { id: 'ai_model.create', name: '创建模型', type: 'action' },
          { id: 'ai_model.edit', name: '编辑模型', type: 'action' },
          { id: 'ai_model.delete', name: '删除模型', type: 'action' }
        ]
      },
      {
        id: 'ai-prompt',
        name: '提示词模板',
        type: 'page',
        children: [
          { id: 'ai_prompt.view', name: '查看提示词模板', type: 'action' },
          { id: 'ai_prompt.create', name: '创建模板', type: 'action' },
          { id: 'ai_prompt.edit', name: '编辑模板', type: 'action' },
          { id: 'ai_prompt.delete', name: '删除模板', type: 'action' }
        ]
      },
      {
        id: 'ai-article',
        name: 'AI文章生成',
        type: 'page',
        children: [
          { id: 'ai_article.view', name: '查看生成任务', type: 'action' },
          { id: 'ai_article.create', name: '创建生成任务', type: 'action' },
          { id: 'ai_article.cancel', name: '取消任务', type: 'action' }
        ]
      },
      {
        id: 'ai-image',
        name: 'AI图片生成',
        type: 'page',
        children: [
          { id: 'ai_image.view', name: '查看图片生成', type: 'action' },
          { id: 'ai_image.create', name: '创建生成任务', type: 'action' },
          { id: 'ai_image.cancel', name: '取消任务', type: 'action' }
        ]
      }
    ]
  },

  // 5. SEO管理
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
          { id: 'build.index', name: '生成首页', type: 'action' },
          { id: 'build.article', name: '生成文章页', type: 'action' },
          { id: 'build.category', name: '生成分类页', type: 'action' },
          { id: 'build.tag', name: '生成标签页', type: 'action' },
          { id: 'build.page', name: '生成单页', type: 'action' },
          { id: 'build.all', name: '全站生成', type: 'action' }
        ]
      },
      {
        id: 'sitemap',
        name: 'Sitemap生成',
        type: 'page',
        children: [
          { id: 'sitemap.view', name: '查看Sitemap', type: 'action' },
          { id: 'sitemap.generate', name: '生成Sitemap', type: 'action' }
        ]
      },
      {
        id: 'seo-redirect',
        name: 'URL重定向',
        type: 'page',
        children: [
          { id: 'seo_redirect.view', name: '查看重定向规则', type: 'action' },
          { id: 'seo_redirect.create', name: '创建重定向', type: 'action' },
          { id: 'seo_redirect.edit', name: '编辑重定向', type: 'action' },
          { id: 'seo_redirect.delete', name: '删除重定向', type: 'action' }
        ]
      },
      {
        id: 'seo-404',
        name: '404错误监控',
        type: 'page',
        children: [
          { id: 'seo_404.view', name: '查看404日志', type: 'action' },
          { id: 'seo_404.delete', name: '删除日志', type: 'action' }
        ]
      },
      {
        id: 'seo-robot',
        name: 'Robots.txt',
        type: 'page',
        children: [
          { id: 'seo_robot.view', name: '查看Robots配置', type: 'action' },
          { id: 'seo_robot.edit', name: '编辑Robots', type: 'action' }
        ]
      },
      {
        id: 'seo-analyzer',
        name: 'SEO分析工具',
        type: 'page',
        children: [
          { id: 'seo_analyzer.view', name: '查看SEO分析', type: 'action' }
        ]
      }
    ]
  },

  // 6. 会员管理
  {
    id: 'member',
    name: '会员管理',
    icon: 'User',
    type: 'menu',
    children: [
      {
        id: 'front-user',
        name: '会员列表',
        type: 'page',
        children: [
          { id: 'front_user.view', name: '查看会员列表', type: 'action' },
          { id: 'front_user.read', name: '查看用户详情', type: 'action' },
          { id: 'front_user.edit', name: '编辑用户', type: 'action' },
          { id: 'front_user.delete', name: '删除用户', type: 'action' },
          { id: 'front_user.block', name: '禁用/启用用户', type: 'action' }
        ]
      },
      {
        id: 'member-level',
        name: '会员等级',
        type: 'page',
        children: [
          { id: 'member_level.view', name: '查看会员等级', type: 'action' },
          { id: 'member_level.create', name: '创建会员等级', type: 'action' },
          { id: 'member_level.edit', name: '编辑会员等级', type: 'action' },
          { id: 'member_level.delete', name: '删除会员等级', type: 'action' }
        ]
      }
    ]
  },

  // 7. 评论管理
  {
    id: 'comment',
    name: '评论管理',
    icon: 'ChatDotRound',
    type: 'menu',
    children: [
      {
        id: 'comment-list',
        name: '评论列表',
        type: 'page',
        children: [
          { id: 'comment.view', name: '查看评论列表', type: 'action' },
          { id: 'comment.read', name: '查看评论详情', type: 'action' },
          { id: 'comment.approve', name: '审核评论', type: 'action' },
          { id: 'comment.delete', name: '删除评论', type: 'action' },
          { id: 'comment.batch', name: '批量操作评论', type: 'action' }
        ]
      },
      {
        id: 'comment-report',
        name: '举报管理',
        type: 'page',
        children: [
          { id: 'comment_report.view', name: '查看举报列表', type: 'action' },
          { id: 'comment_report.handle', name: '处理举报', type: 'action' }
        ]
      },
      {
        id: 'violation',
        name: '违规记录',
        type: 'page',
        children: [
          { id: 'violation.view', name: '查看违规内容', type: 'action' },
          { id: 'violation.handle', name: '处理违规内容', type: 'action' }
        ]
      }
    ]
  },

  // 8. 模板管理
  {
    id: 'template',
    name: '模板管理',
    icon: 'Files',
    type: 'menu',
    children: [
      {
        id: 'template-package',
        name: '模板包管理',
        type: 'page',
        children: [
          { id: 'template_package.view', name: '查看模板包', type: 'action' },
          { id: 'template_package.create', name: '创建模板包', type: 'action' },
          { id: 'template_package.edit', name: '编辑模板包', type: 'action' },
          { id: 'template_package.delete', name: '删除模板包', type: 'action' },
          { id: 'template_package.install', name: '安装模板包', type: 'action' }
        ]
      },
      {
        id: 'template-type',
        name: '模板类型管理',
        type: 'page',
        children: [
          { id: 'template_type.view', name: '查看模板类型', type: 'action' },
          { id: 'template_type.create', name: '创建模板类型', type: 'action' },
          { id: 'template_type.edit', name: '编辑模板类型', type: 'action' },
          { id: 'template_type.delete', name: '删除模板类型', type: 'action' }
        ]
      },
      {
        id: 'template-editor',
        name: '模板编辑器',
        type: 'page',
        children: [
          { id: 'template.view', name: '查看模板文件', type: 'action' },
          { id: 'template.edit', name: '编辑模板文件', type: 'action' },
          { id: 'template.check', name: '检测模板语法', type: 'action' }
        ]
      }
    ]
  },

  // 9. 系统管理
  {
    id: 'system',
    name: '系统管理',
    icon: 'Setting',
    type: 'menu',
    children: [
      {
        id: 'site',
        name: '多站点管理',
        type: 'page',
        children: [
          { id: 'site.view', name: '查看站点列表', type: 'action' },
          { id: 'site.read', name: '查看站点详情', type: 'action' },
          { id: 'site.create', name: '创建站点', type: 'action' },
          { id: 'site.edit', name: '编辑站点', type: 'action' },
          { id: 'site.delete', name: '删除站点', type: 'action' },
          { id: 'site.switch', name: '切换站点', type: 'action' }
        ]
      },
      {
        id: 'site-config',
        name: '站点配置',
        type: 'page',
        children: [
          { id: 'site_config.view', name: '查看站点配置', type: 'action' },
          { id: 'site_config.edit', name: '编辑站点配置', type: 'action' }
        ]
      },
      {
        id: 'admin-user',
        name: '后台用户管理',
        type: 'page',
        children: [
          { id: 'admin_user.view', name: '查看管理员列表', type: 'action' },
          { id: 'admin_user.read', name: '查看管理员详情', type: 'action' },
          { id: 'admin_user.create', name: '创建管理员', type: 'action' },
          { id: 'admin_user.edit', name: '编辑管理员', type: 'action' },
          { id: 'admin_user.delete', name: '删除管理员', type: 'action' },
          { id: 'admin_user.reset_password', name: '重置密码', type: 'action' }
        ]
      },
      {
        id: 'role',
        name: '角色管理',
        type: 'page',
        children: [
          { id: 'role.view', name: '查看角色列表', type: 'action' },
          { id: 'role.read', name: '查看角色详情', type: 'action' },
          { id: 'role.create', name: '创建角色', type: 'action' },
          { id: 'role.edit', name: '编辑角色', type: 'action' },
          { id: 'role.delete', name: '删除角色', type: 'action' },
          { id: 'role.permission', name: '管理角色权限', type: 'action' }
        ]
      },
      {
        id: 'system-config',
        name: '系统配置',
        type: 'page',
        children: [
          { id: 'system_config.view', name: '查看系统配置', type: 'action' },
          { id: 'system_config.edit', name: '编辑系统配置', type: 'action' }
        ]
      },
      {
        id: 'storage',
        name: '存储配置',
        type: 'page',
        children: [
          { id: 'storage.view', name: '查看存储配置', type: 'action' },
          { id: 'storage.create', name: '创建存储配置', type: 'action' },
          { id: 'storage.edit', name: '编辑存储配置', type: 'action' },
          { id: 'storage.delete', name: '删除存储配置', type: 'action' },
          { id: 'storage.test', name: '测试存储连接', type: 'action' }
        ]
      },
      {
        id: 'email',
        name: '邮件配置',
        type: 'page',
        children: [
          { id: 'email.view', name: '查看邮件配置', type: 'action' },
          { id: 'email.edit', name: '编辑邮件配置', type: 'action' },
          { id: 'email.test', name: '测试邮件发送', type: 'action' },
          { id: 'email.template', name: '管理邮件模板', type: 'action' },
          { id: 'email.log', name: '查看邮件日志', type: 'action' }
        ]
      },
      {
        id: 'sms',
        name: '短信服务',
        type: 'page',
        children: [
          { id: 'sms.view', name: '查看短信配置', type: 'action' },
          { id: 'sms.edit', name: '编辑短信配置', type: 'action' },
          { id: 'sms.test', name: '测试短信发送', type: 'action' },
          { id: 'sms.template', name: '管理短信模板', type: 'action' },
          { id: 'sms.log', name: '查看短信日志', type: 'action' }
        ]
      },
      {
        id: 'oauth',
        name: 'OAuth配置',
        type: 'page',
        children: [
          { id: 'oauth.view', name: '查看OAuth配置', type: 'action' },
          { id: 'oauth.create', name: '创建OAuth配置', type: 'action' },
          { id: 'oauth.edit', name: '编辑OAuth配置', type: 'action' },
          { id: 'oauth.delete', name: '删除OAuth配置', type: 'action' }
        ]
      },
      {
        id: 'sensitive-word',
        name: '敏感词管理',
        type: 'page',
        children: [
          { id: 'sensitive_word.view', name: '查看敏感词', type: 'action' },
          { id: 'sensitive_word.create', name: '创建敏感词', type: 'action' },
          { id: 'sensitive_word.edit', name: '编辑敏感词', type: 'action' },
          { id: 'sensitive_word.delete', name: '删除敏感词', type: 'action' },
          { id: 'sensitive_word.import', name: '导入敏感词', type: 'action' }
        ]
      },
      {
        id: 'ip-filter',
        name: 'IP黑白名单',
        type: 'page',
        children: [
          { id: 'ip_filter.view', name: '查看IP过滤', type: 'action' },
          { id: 'ip_filter.create', name: '创建IP规则', type: 'action' },
          { id: 'ip_filter.delete', name: '删除IP规则', type: 'action' }
        ]
      },
      {
        id: 'cron-job',
        name: '定时任务',
        type: 'page',
        children: [
          { id: 'cron_job.view', name: '查看定时任务', type: 'action' },
          { id: 'cron_job.create', name: '创建定时任务', type: 'action' },
          { id: 'cron_job.edit', name: '编辑定时任务', type: 'action' },
          { id: 'cron_job.delete', name: '删除定时任务', type: 'action' },
          { id: 'cron_job.execute', name: '手动执行任务', type: 'action' },
          { id: 'cron_job.log', name: '查看任务日志', type: 'action' }
        ]
      },
      {
        id: 'queue',
        name: '队列管理',
        type: 'page',
        children: [
          { id: 'queue.view', name: '查看队列状态', type: 'action' },
          { id: 'queue.retry', name: '重试失败任务', type: 'action' },
          { id: 'queue.clear', name: '清空队列', type: 'action' }
        ]
      },
      {
        id: 'database',
        name: '数据库管理',
        type: 'page',
        children: [
          { id: 'database.view', name: '查看备份列表', type: 'action' },
          { id: 'database.backup', name: '创建备份', type: 'action' },
          { id: 'database.restore', name: '恢复备份', type: 'action' },
          { id: 'database.delete', name: '删除备份', type: 'action' },
          { id: 'database.download', name: '下载备份', type: 'action' }
        ]
      },
      {
        id: 'database-optimize',
        name: '数据库优化',
        type: 'page',
        children: [
          { id: 'database_optimize.view', name: '查看数据库状态', type: 'action' },
          { id: 'database_optimize.execute', name: '执行优化', type: 'action' }
        ]
      },
      {
        id: 'cache',
        name: '缓存管理',
        type: 'page',
        children: [
          { id: 'cache.view', name: '查看缓存信息', type: 'action' },
          { id: 'cache.clear', name: '清空缓存', type: 'action' },
          { id: 'cache.clear_tag', name: '清空指定标签缓存', type: 'action' }
        ]
      },
      {
        id: 'operation-log',
        name: '操作日志',
        type: 'page',
        children: [
          { id: 'operation_log.view', name: '查看操作日志', type: 'action' },
          { id: 'operation_log.delete', name: '删除操作日志', type: 'action' }
        ]
      },
      {
        id: 'system-log',
        name: '系统日志',
        type: 'page',
        children: [
          { id: 'system_log.view', name: '查看系统日志', type: 'action' },
          { id: 'system_log.delete', name: '删除系统日志', type: 'action' },
          { id: 'system_log.download', name: '下载日志文件', type: 'action' }
        ]
      },
      {
        id: 'query-monitor',
        name: 'SQL监控',
        type: 'page',
        children: [
          { id: 'query_monitor.view', name: '查看SQL监控', type: 'action' },
          { id: 'query_monitor.clear', name: '清空监控记录', type: 'action' }
        ]
      },
      {
        id: 'notification',
        name: '消息通知管理',
        type: 'page',
        children: [
          { id: 'notification.view', name: '查看通知列表', type: 'action' },
          { id: 'notification.send', name: '发送通知', type: 'action' },
          { id: 'notification.delete', name: '删除通知', type: 'action' }
        ]
      },
      {
        id: 'notification-template',
        name: '通知模板',
        type: 'page',
        children: [
          { id: 'notification_template.view', name: '查看通知模板', type: 'action' },
          { id: 'notification_template.edit', name: '编辑通知模板', type: 'action' }
        ]
      }
    ]
  },

  // 10. 扩展功能
  {
    id: 'extensions',
    name: '扩展功能',
    icon: 'Grid',
    type: 'menu',
    children: [
      {
        id: 'ad',
        name: '广告管理',
        type: 'page',
        children: [
          { id: 'ad.view', name: '查看广告列表', type: 'action' },
          { id: 'ad.read', name: '查看广告详情', type: 'action' },
          { id: 'ad.create', name: '创建广告', type: 'action' },
          { id: 'ad.edit', name: '编辑广告', type: 'action' },
          { id: 'ad.delete', name: '删除广告', type: 'action' },
          { id: 'ad.stats', name: '查看广告统计', type: 'action' }
        ]
      },
      {
        id: 'ad-position',
        name: '广告位管理',
        type: 'page',
        children: [
          { id: 'ad_position.view', name: '查看广告位', type: 'action' },
          { id: 'ad_position.create', name: '创建广告位', type: 'action' },
          { id: 'ad_position.edit', name: '编辑广告位', type: 'action' },
          { id: 'ad_position.delete', name: '删除广告位', type: 'action' }
        ]
      },
      {
        id: 'slider',
        name: '幻灯片管理',
        type: 'page',
        children: [
          { id: 'slider.view', name: '查看幻灯片列表', type: 'action' },
          { id: 'slider.create', name: '创建幻灯片', type: 'action' },
          { id: 'slider.edit', name: '编辑幻灯片', type: 'action' },
          { id: 'slider.delete', name: '删除幻灯片', type: 'action' },
          { id: 'slider.sort', name: '调整排序', type: 'action' }
        ]
      },
      {
        id: 'link',
        name: '友情链接',
        type: 'page',
        children: [
          { id: 'link.view', name: '查看友链列表', type: 'action' },
          { id: 'link.create', name: '创建友链', type: 'action' },
          { id: 'link.edit', name: '编辑友链', type: 'action' },
          { id: 'link.delete', name: '删除友链', type: 'action' },
          { id: 'link.group', name: '管理友链分组', type: 'action' }
        ]
      },
      {
        id: 'contribute',
        name: '投稿管理',
        type: 'page',
        children: [
          { id: 'contribute.view', name: '查看投稿列表', type: 'action' },
          { id: 'contribute.read', name: '查看投稿详情', type: 'action' },
          { id: 'contribute.approve', name: '审核投稿', type: 'action' },
          { id: 'contribute.reject', name: '拒绝投稿', type: 'action' },
          { id: 'contribute.delete', name: '删除投稿', type: 'action' }
        ]
      },
      {
        id: 'contribute-config',
        name: '投稿配置',
        type: 'page',
        children: [
          { id: 'contribute_config.view', name: '查看投稿配置', type: 'action' },
          { id: 'contribute_config.edit', name: '编辑投稿配置', type: 'action' }
        ]
      },
      {
        id: 'point-shop-goods',
        name: '积分商品管理',
        type: 'page',
        children: [
          { id: 'point_shop_goods.view', name: '查看商品列表', type: 'action' },
          { id: 'point_shop_goods.create', name: '创建商品', type: 'action' },
          { id: 'point_shop_goods.edit', name: '编辑商品', type: 'action' },
          { id: 'point_shop_goods.delete', name: '删除商品', type: 'action' }
        ]
      },
      {
        id: 'point-shop-order',
        name: '积分订单管理',
        type: 'page',
        children: [
          { id: 'point_shop_order.view', name: '查看订单列表', type: 'action' },
          { id: 'point_shop_order.read', name: '查看订单详情', type: 'action' },
          { id: 'point_shop_order.process', name: '处理订单', type: 'action' },
          { id: 'point_shop_order.cancel', name: '取消订单', type: 'action' }
        ]
      }
    ]
  },

  // 11. 回收站
  {
    id: 'recycle-bin',
    name: '回收站',
    icon: 'Delete',
    type: 'menu',
    children: [
      { id: 'recycle_bin.view', name: '查看回收站', type: 'action' },
      { id: 'recycle_bin.restore', name: '恢复内容', type: 'action' },
      { id: 'recycle_bin.delete', name: '彻底删除', type: 'action' },
      { id: 'recycle_bin.clear', name: '清空回收站', type: 'action' }
    ]
  },

  // 12. 其他
  {
    id: 'profile',
    name: '个人中心',
    icon: 'User',
    type: 'menu',
    children: [
      { id: 'profile.view', name: '查看个人信息', type: 'action' },
      { id: 'profile.edit', name: '编辑个人信息', type: 'action' },
      { id: 'profile.change_password', name: '修改密码', type: 'action' }
    ]
  },

  {
    id: 'api-doc',
    name: 'API文档',
    icon: 'Document',
    type: 'menu',
    children: [
      { id: 'api_doc.view', name: '查看API文档', type: 'action' }
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
