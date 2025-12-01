# CMS系统权限配置方案

## 1. 核心内容管理

### 1.1 文章管理 (article)
- `article.view` - 查看文章列表
- `article.read` - 查看文章详情
- `article.create` - 创建文章
- `article.edit` - 编辑所有文章
- `article.edit_own` - 只能编辑自己的文章
- `article.delete` - 删除文章
- `article.publish` - 发布/下线文章
- `article.batch` - 批量操作（批量发布、删除、修改分类等）
- `article.export` - 导出文章
- `article.flag` - 管理文章标记（置顶、推荐、热门等）
- `article.version` - 版本管理（查看、回滚、对比）

### 1.2 分类管理 (category)
- `category.view` - 查看分类列表/树
- `category.read` - 查看分类详情
- `category.create` - 创建分类
- `category.edit` - 编辑分类
- `category.delete` - 删除分类
- `category.sort` - 调整分类排序

### 1.3 标签管理 (tag)
- `tag.view` - 查看标签列表
- `tag.read` - 查看标签详情
- `tag.create` - 创建标签
- `tag.edit` - 编辑标签
- `tag.delete` - 删除标签
- `tag.merge` - 合并标签

### 1.4 页面管理 (page)
- `page.view` - 查看单页列表
- `page.read` - 查看单页详情
- `page.create` - 创建单页
- `page.edit` - 编辑单页
- `page.delete` - 删除单页

### 1.5 专题管理 (topic)
- `topic.view` - 查看专题列表
- `topic.read` - 查看专题详情
- `topic.create` - 创建专题
- `topic.edit` - 编辑专题
- `topic.delete` - 删除专题
- `topic.article` - 管理专题文章关联

### 1.6 自定义字段 (custom_field)
- `custom_field.view` - 查看自定义字段
- `custom_field.create` - 创建自定义字段
- `custom_field.edit` - 编辑自定义字段
- `custom_field.delete` - 删除自定义字段

### 1.7 内容模型 (content_model)
- `content_model.view` - 查看内容模型
- `content_model.create` - 创建内容模型
- `content_model.edit` - 编辑内容模型
- `content_model.delete` - 删除内容模型

## 2. 媒体管理

### 2.1 媒体文件 (media)
- `media.view` - 查看媒体库
- `media.upload` - 上传文件
- `media.edit` - 编辑媒体（裁剪、旋转等）
- `media.delete` - 删除媒体
- `media.move` - 移动媒体
- `media.download` - 下载媒体

### 2.2 水印管理 (watermark)
- `watermark.view` - 查看水印预设
- `watermark.create` - 创建水印预设
- `watermark.edit` - 编辑水印预设
- `watermark.delete` - 删除水印预设

### 2.3 缩略图管理 (thumbnail)
- `thumbnail.view` - 查看缩略图预设
- `thumbnail.create` - 创建缩略图预设
- `thumbnail.edit` - 编辑缩略图预设
- `thumbnail.delete` - 删除缩略图预设

### 2.4 视频处理 (video)
- `video.view` - 查看视频处理记录
- `video.transcode` - 转码视频
- `video.poster` - 生成视频封面

## 3. 评论管理

### 3.1 评论管理 (comment)
- `comment.view` - 查看评论列表
- `comment.read` - 查看评论详情
- `comment.approve` - 审核评论
- `comment.delete` - 删除评论
- `comment.batch` - 批量操作评论

### 3.2 评论举报 (comment_report)
- `comment_report.view` - 查看举报列表
- `comment_report.handle` - 处理举报

### 3.3 违规内容 (violation)
- `violation.view` - 查看违规内容
- `violation.handle` - 处理违规内容

## 4. 用户管理

### 4.1 后台用户 (admin_user)
- `admin_user.view` - 查看管理员列表
- `admin_user.read` - 查看管理员详情
- `admin_user.create` - 创建管理员
- `admin_user.edit` - 编辑管理员
- `admin_user.delete` - 删除管理员
- `admin_user.reset_password` - 重置密码

### 4.2 角色管理 (role)
- `role.view` - 查看角色列表
- `role.read` - 查看角色详情
- `role.create` - 创建角色
- `role.edit` - 编辑角色
- `role.delete` - 删除角色
- `role.permission` - 管理角色权限

### 4.3 前台用户 (front_user)
- `front_user.view` - 查看前台用户列表
- `front_user.read` - 查看用户详情
- `front_user.edit` - 编辑用户
- `front_user.delete` - 删除用户
- `front_user.block` - 禁用/启用用户

### 4.4 会员等级 (member_level)
- `member_level.view` - 查看会员等级
- `member_level.create` - 创建会员等级
- `member_level.edit` - 编辑会员等级
- `member_level.delete` - 删除会员等级

## 5. 广告营销

### 5.1 广告管理 (ad)
- `ad.view` - 查看广告列表
- `ad.read` - 查看广告详情
- `ad.create` - 创建广告
- `ad.edit` - 编辑广告
- `ad.delete` - 删除广告
- `ad.stats` - 查看广告统计

### 5.2 广告位管理 (ad_position)
- `ad_position.view` - 查看广告位
- `ad_position.create` - 创建广告位
- `ad_position.edit` - 编辑广告位
- `ad_position.delete` - 删除广告位

### 5.3 轮播图管理 (slider)
- `slider.view` - 查看轮播图
- `slider.create` - 创建轮播图
- `slider.edit` - 编辑轮播图
- `slider.delete` - 删除轮播图
- `slider.sort` - 调整轮播图排序

### 5.4 友情链接 (link)
- `link.view` - 查看友链列表
- `link.create` - 创建友链
- `link.edit` - 编辑友链
- `link.delete` - 删除友链
- `link.group` - 管理友链分组

## 6. AI功能

### 6.1 AI配置 (ai_config)
- `ai_config.view` - 查看AI配置
- `ai_config.edit` - 编辑AI配置

### 6.2 AI供应商 (ai_provider)
- `ai_provider.view` - 查看AI供应商
- `ai_provider.create` - 创建AI供应商
- `ai_provider.edit` - 编辑AI供应商
- `ai_provider.delete` - 删除AI供应商

### 6.3 AI模型 (ai_model)
- `ai_model.view` - 查看AI模型
- `ai_model.create` - 创建AI模型
- `ai_model.edit` - 编辑AI模型
- `ai_model.delete` - 删除AI模型

### 6.4 提示词模板 (ai_prompt)
- `ai_prompt.view` - 查看提示词模板
- `ai_prompt.create` - 创建提示词模板
- `ai_prompt.edit` - 编辑提示词模板
- `ai_prompt.delete` - 删除提示词模板

### 6.5 AI文章任务 (ai_article)
- `ai_article.view` - 查看AI文章任务
- `ai_article.create` - 创建AI文章任务
- `ai_article.cancel` - 取消AI文章任务

### 6.6 AI图像生成 (ai_image)
- `ai_image.view` - 查看AI图像任务
- `ai_image.create` - 创建AI图像任务
- `ai_image.cancel` - 取消AI图像任务

## 7. 站点管理

### 7.1 多站点管理 (site)
- `site.view` - 查看站点列表
- `site.read` - 查看站点详情
- `site.create` - 创建站点
- `site.edit` - 编辑站点
- `site.delete` - 删除站点
- `site.switch` - 切换站点

### 7.2 站点配置 (site_config)
- `site_config.view` - 查看站点配置
- `site_config.edit` - 编辑站点配置

## 8. 模板管理

### 8.1 模板包管理 (template_package)
- `template_package.view` - 查看模板包
- `template_package.create` - 创建模板包
- `template_package.edit` - 编辑模板包
- `template_package.delete` - 删除模板包
- `template_package.install` - 安装模板包

### 8.2 模板文件管理 (template)
- `template.view` - 查看模板文件
- `template.edit` - 编辑模板文件
- `template.check` - 检测模板语法

### 8.3 模板类型 (template_type)
- `template_type.view` - 查看模板类型
- `template_type.create` - 创建模板类型
- `template_type.edit` - 编辑模板类型
- `template_type.delete` - 删除模板类型

### 8.4 静态生成 (build)
- `build.index` - 生成首页
- `build.article` - 生成文章页
- `build.category` - 生成分类页
- `build.tag` - 生成标签页
- `build.page` - 生成单页
- `build.all` - 全站生成

## 9. SEO管理

### 9.1 SEO分析 (seo_analyzer)
- `seo_analyzer.view` - 查看SEO分析

### 9.2 404日志 (seo_404)
- `seo_404.view` - 查看404日志
- `seo_404.delete` - 删除404日志

### 9.3 重定向管理 (seo_redirect)
- `seo_redirect.view` - 查看重定向规则
- `seo_redirect.create` - 创建重定向
- `seo_redirect.edit` - 编辑重定向
- `seo_redirect.delete` - 删除重定向

### 9.4 Robots管理 (seo_robot)
- `seo_robot.view` - 查看robots配置
- `seo_robot.edit` - 编辑robots配置

### 9.5 Sitemap管理 (sitemap)
- `sitemap.view` - 查看sitemap
- `sitemap.generate` - 生成sitemap

## 10. 系统设置

### 10.1 系统配置 (system_config)
- `system_config.view` - 查看系统配置
- `system_config.edit` - 编辑系统配置

### 10.2 存储配置 (storage)
- `storage.view` - 查看存储配置
- `storage.create` - 创建存储配置
- `storage.edit` - 编辑存储配置
- `storage.delete` - 删除存储配置
- `storage.test` - 测试存储连接

### 10.3 邮件配置 (email)
- `email.view` - 查看邮件配置
- `email.edit` - 编辑邮件配置
- `email.test` - 测试邮件发送
- `email.template` - 管理邮件模板
- `email.log` - 查看邮件日志

### 10.4 短信配置 (sms)
- `sms.view` - 查看短信配置
- `sms.edit` - 编辑短信配置
- `sms.test` - 测试短信发送
- `sms.template` - 管理短信模板
- `sms.log` - 查看短信日志

### 10.5 OAuth配置 (oauth)
- `oauth.view` - 查看OAuth配置
- `oauth.create` - 创建OAuth配置
- `oauth.edit` - 编辑OAuth配置
- `oauth.delete` - 删除OAuth配置

### 10.6 敏感词管理 (sensitive_word)
- `sensitive_word.view` - 查看敏感词
- `sensitive_word.create` - 创建敏感词
- `sensitive_word.edit` - 编辑敏感词
- `sensitive_word.delete` - 删除敏感词
- `sensitive_word.import` - 导入敏感词

### 10.7 IP黑白名单 (ip_filter)
- `ip_filter.view` - 查看IP过滤
- `ip_filter.create` - 创建IP规则
- `ip_filter.delete` - 删除IP规则

## 11. 定时任务

### 11.1 任务管理 (cron_job)
- `cron_job.view` - 查看定时任务
- `cron_job.create` - 创建定时任务
- `cron_job.edit` - 编辑定时任务
- `cron_job.delete` - 删除定时任务
- `cron_job.execute` - 手动执行任务
- `cron_job.log` - 查看任务日志

### 11.2 队列管理 (queue)
- `queue.view` - 查看队列状态
- `queue.retry` - 重试失败任务
- `queue.clear` - 清空队列

## 12. 日志管理

### 12.1 操作日志 (operation_log)
- `operation_log.view` - 查看操作日志
- `operation_log.delete` - 删除操作日志

### 12.2 系统日志 (system_log)
- `system_log.view` - 查看系统日志
- `system_log.delete` - 删除系统日志
- `system_log.download` - 下载日志文件

### 12.3 SQL监控 (query_monitor)
- `query_monitor.view` - 查看SQL监控
- `query_monitor.clear` - 清空监控记录

## 13. 数据库管理

### 13.1 数据库备份 (database)
- `database.view` - 查看备份列表
- `database.backup` - 创建备份
- `database.restore` - 恢复备份
- `database.delete` - 删除备份
- `database.download` - 下载备份

### 13.2 数据库优化 (database_optimize)
- `database_optimize.view` - 查看数据库状态
- `database_optimize.execute` - 执行优化

## 14. 缓存管理

### 14.1 缓存管理 (cache)
- `cache.view` - 查看缓存信息
- `cache.clear` - 清空缓存
- `cache.clear_tag` - 清空指定标签缓存

## 15. 通知管理

### 15.1 通知模板 (notification_template)
- `notification_template.view` - 查看通知模板
- `notification_template.edit` - 编辑通知模板

### 15.2 通知管理 (notification)
- `notification.view` - 查看通知列表
- `notification.send` - 发送通知
- `notification.delete` - 删除通知

## 16. 投稿管理

### 16.1 投稿配置 (contribute_config)
- `contribute_config.view` - 查看投稿配置
- `contribute_config.edit` - 编辑投稿配置

### 16.2 投稿管理 (contribute)
- `contribute.view` - 查看投稿列表
- `contribute.read` - 查看投稿详情
- `contribute.approve` - 审核投稿
- `contribute.reject` - 拒绝投稿
- `contribute.delete` - 删除投稿

## 17. 积分商城

### 17.1 商品管理 (point_shop_goods)
- `point_shop_goods.view` - 查看商品列表
- `point_shop_goods.create` - 创建商品
- `point_shop_goods.edit` - 编辑商品
- `point_shop_goods.delete` - 删除商品

### 17.2 订单管理 (point_shop_order)
- `point_shop_order.view` - 查看订单列表
- `point_shop_order.read` - 查看订单详情
- `point_shop_order.process` - 处理订单
- `point_shop_order.cancel` - 取消订单

## 18. 回收站

### 18.1 回收站 (recycle_bin)
- `recycle_bin.view` - 查看回收站
- `recycle_bin.restore` - 恢复内容
- `recycle_bin.delete` - 彻底删除
- `recycle_bin.clear` - 清空回收站

## 19. 仪表盘

### 19.1 仪表盘 (dashboard)
- `dashboard.view` - 查看仪表盘
- `dashboard.stats` - 查看统计数据

## 20. 其他

### 20.1 API文档 (api_doc)
- `api_doc.view` - 查看API文档

### 20.2 个人中心 (profile)
- `profile.view` - 查看个人信息
- `profile.edit` - 编辑个人信息
- `profile.change_password` - 修改密码
