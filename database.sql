
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ad_clicks
-- ----------------------------
DROP TABLE IF EXISTS `ad_clicks`;
CREATE TABLE `ad_clicks`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `ad_id` int UNSIGNED NOT NULL COMMENT '广告ID',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '访客IP',
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '用户代理',
  `referer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '来源页面',
  `click_time` datetime NULL DEFAULT NULL COMMENT '点击时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_ad_id`(`ad_id` ASC) USING BTREE,
  INDEX `idx_click_time`(`click_time` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '广告点击统计表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ad_clicks
-- ----------------------------

-- ----------------------------
-- Table structure for admin_roles
-- ----------------------------
DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE `admin_roles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '角色描述',
  `permissions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '权限列表（JSON格式）',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '角色表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_roles
-- ----------------------------
INSERT INTO `admin_roles` VALUES (1, '超级管理员', '拥有系统所有权限，包括系统配置、用户管理、内容管理等全部功能', '[\"*\"]', 1, 1, '2025-10-12 02:12:51', '2025-11-30 17:35:17');
INSERT INTO `admin_roles` VALUES (2, '管理员', '管理员拥有系统大部分功能的完整管理权限，包括内容管理、AI功能、站点配置、模板管理、定时任务等，但不包括系统核心配置（system_config.edit）、角色权限管理（role.*）、后台用户管理（admin_user.*）、数据库还原（database.restore）等核心权限', '[\"dashboard.*\", \"article.*\", \"category.*\", \"tag.*\", \"page.*\", \"topic.*\", \"custom_field.*\", \"content_model.*\", \"media.*\", \"watermark.*\", \"thumbnail.*\", \"video.*\", \"comment.*\", \"comment_report.*\", \"violation.*\", \"front_user.view\", \"front_user.read\", \"front_user.edit\", \"front_user.block\", \"member_level.*\", \"ad.*\", \"ad_position.*\", \"slider.*\", \"link.*\", \"ai_config.*\", \"ai_provider.*\", \"ai_model.*\", \"ai_prompt.*\", \"ai_article.*\", \"ai_image.*\", \"site.*\", \"site_config.*\", \"template_package.*\", \"template.view\", \"template.edit\", \"template.check\", \"template_type.*\", \"build.*\", \"seo_analyzer.*\", \"seo_404.*\", \"seo_redirect.*\", \"seo_robot.*\", \"sitemap.*\", \"system_config.view\", \"storage.*\", \"email.*\", \"sms.*\", \"oauth.*\", \"sensitive_word.*\", \"ip_filter.*\", \"cron_job.*\", \"queue.*\", \"operation_log.*\", \"system_log.*\", \"query_monitor.*\", \"database.view\", \"database.backup\", \"database.download\", \"database.delete\", \"database_optimize.*\", \"cache.*\", \"notification_template.*\", \"notification.*\", \"contribute_config.*\", \"contribute.*\", \"point_shop_goods.*\", \"point_shop_order.*\", \"recycle_bin.*\", \"api_doc.view\", \"profile.*\"]', 2, 1, '2025-10-12 02:12:51', '2025-11-30 20:46:29');
INSERT INTO `admin_roles` VALUES (3, '编辑', '可以管理文章、分类、标签、页面、专题、评论等内容相关功能，但无法管理用户和系统设置', '[\n        \"dashboard.view\",\n        \"article.view\",\n        \"article.read\",\n        \"article.create\",\n        \"article.edit\",\n        \"article.delete\",\n        \"article.publish\",\n        \"article.batch\",\n        \"article.flag\",\n        \"article.version\",\n        \"category.view\",\n        \"category.read\",\n        \"category.create\",\n        \"category.edit\",\n        \"tag.view\",\n        \"tag.read\",\n        \"tag.create\",\n        \"tag.edit\",\n        \"tag.delete\",\n        \"tag.merge\",\n        \"page.view\",\n        \"page.read\",\n        \"page.create\",\n        \"page.edit\",\n        \"page.delete\",\n        \"topic.view\",\n        \"topic.read\",\n        \"topic.create\",\n        \"topic.edit\",\n        \"topic.delete\",\n        \"topic.article\",\n        \"custom_field.view\",\n        \"media.view\",\n        \"media.upload\",\n        \"media.edit\",\n        \"media.delete\",\n        \"media.move\",\n        \"comment.view\",\n        \"comment.read\",\n        \"comment.approve\",\n        \"comment.delete\",\n        \"comment.batch\",\n        \"comment_report.view\",\n        \"comment_report.handle\",\n        \"violation.view\",\n        \"violation.handle\",\n        \"ai_prompt.view\",\n        \"ai_article.view\",\n        \"ai_article.create\",\n        \"ai_image.view\",\n        \"ai_image.create\",\n        \"template.view\",\n        \"build.index\",\n        \"build.article\",\n        \"build.category\",\n        \"build.tag\",\n        \"build.page\",\n        \"seo_analyzer.view\",\n        \"seo_404.view\",\n        \"sitemap.view\",\n        \"sitemap.generate\",\n        \"contribute.view\",\n        \"contribute.read\",\n        \"contribute.approve\",\n        \"contribute.reject\",\n        \"recycle_bin.view\",\n        \"recycle_bin.restore\",\n        \"profile.*\"\n    ]', 3, 1, '2025-10-12 02:12:51', '2025-11-30 17:35:17');
INSERT INTO `admin_roles` VALUES (4, '作者', '只能创建和编辑自己的文章，上传媒体文件，查看分类和标签', '[\n        \"dashboard.view\",\n        \"article.view\",\n        \"article.read\",\n        \"article.create\",\n        \"article.edit_own\",\n        \"article.version\",\n        \"category.view\",\n        \"category.read\",\n        \"tag.view\",\n        \"tag.read\",\n        \"tag.create\",\n        \"media.view\",\n        \"media.upload\",\n        \"media.edit\",\n        \"ai_article.view\",\n        \"ai_article.create\",\n        \"ai_image.view\",\n        \"ai_image.create\",\n        \"profile.*\"\n    ]', 4, 1, '2025-10-12 02:12:51', '2025-11-30 17:35:17');

-- ----------------------------
-- Table structure for admin_users
-- ----------------------------
DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE `admin_users`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码（哈希）',
  `real_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '真实姓名',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邮箱',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '手机号',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '头像',
  `role_id` int UNSIGNED NOT NULL DEFAULT 3 COMMENT '角色ID：1=超管，2=管理员，3=编辑，4=作者',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `last_login_time` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '最后登录IP',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_username`(`username` ASC) USING BTREE,
  INDEX `idx_role_id`(`role_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理员用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_users
-- ----------------------------
INSERT INTO `admin_users` VALUES (1, 'admin', '$2y$10$xpuuHKDpthvJEOaVq9AOv.03eFimQqh4yHkpYIzdC55H6gm8.9QlS', '系统管理员', 'sinma@sinma.net', '13131313131', 'uploads/avatar/2025/11/28/avatar_1_20251128111803.png', 1, 1, '2025-12-01 00:48:08', '127.0.0.1', '2025-10-12 02:12:51', '2025-12-01 00:48:08');
INSERT INTO `admin_users` VALUES (2, 'testadmin', '$2y$10$fweJRsiu2nD47mHZrFzGgehsMGDME8Wkx6MJx.Ox/3JBWWWUPibye', 'Test Admin', 'testadmin@test.com', NULL, NULL, 1, 1, '2025-10-28 11:53:46', '127.0.0.1', '2025-10-28 11:50:19', '2025-10-28 11:53:46');
INSERT INTO `admin_users` VALUES (3, 'manager', '$2y$10$W8Cn.fIHjGG5FWIUJoZSn.NQ/TFK.yqQ3vJCUu0ph7zg4KTBPjMgK', '管理员测试', 'manager@test.com', NULL, NULL, 2, 1, '2025-11-30 20:53:29', '127.0.0.1', '2025-11-30 19:05:11', '2025-11-30 20:53:30');
INSERT INTO `admin_users` VALUES (4, 'editor', '$2y$10$gYNKrEhwHpAR2X9BXz1K8.2VpcQ69UdeT6GE8LULUMLFzOLWXAc7.', '编辑测试', 'editor@test.com', NULL, NULL, 3, 1, '2025-11-30 19:06:12', '127.0.0.1', '2025-11-30 19:05:58', '2025-11-30 19:06:13');
INSERT INTO `admin_users` VALUES (5, 'author', '$2y$10$.9MJ6YHGRGMSLLncGTkkw.zbhuzWZHI2NWwmv.j9JwNokoOf41NPq', '作者测试', 'author@test.com', NULL, NULL, 4, 1, '2025-11-30 19:07:57', '127.0.0.1', '2025-11-30 19:07:08', '2025-11-30 19:07:58');

-- ----------------------------
-- Table structure for ads
-- ----------------------------
DROP TABLE IF EXISTS `ads`;
CREATE TABLE `ads`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '广告ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `position_id` int UNSIGNED NOT NULL COMMENT '广告位ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '广告名称',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'image' COMMENT '广告类型：image=图片，code=代码，carousel=轮播',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '广告内容（图片URL或HTML代码）',
  `link_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '链接地址',
  `images` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '轮播图片（JSON数组）',
  `start_time` datetime NULL DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime NULL DEFAULT NULL COMMENT '结束时间',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `click_count` int NOT NULL DEFAULT 0 COMMENT '点击次数',
  `view_count` int NOT NULL DEFAULT 0 COMMENT '展示次数',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间（软删除）',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_position_id`(`position_id` ASC) USING BTREE,
  INDEX `idx_type`(`type` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_time`(`start_time` ASC, `end_time` ASC) USING BTREE,
  INDEX `idx_sort`(`sort` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '广告表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ads
-- ----------------------------
INSERT INTO `ads` VALUES (1, 1, 1, '春季促销活动', 'image', 'https://www.carefreecms.com/uploads/2025/10/21/20251021002334_68f66206c30e6.jpg', 'https://example.com/sale', '[]', '2025-10-31 16:00:00', '2025-11-29 16:00:00', 1, 1, 0, 0, '2025-10-19 00:58:34', '2025-11-23 04:45:05', NULL);
INSERT INTO `ads` VALUES (2, 1, 2, '产品推荐', 'image', 'https://www.carefreecms.com/uploads/2025/10/21/20251021002344_68f6621032001.jpg', 'https://example.com/products', '[]', '2025-10-31 16:00:00', '2025-11-29 16:00:00', 1, 2, 0, 0, '2025-10-19 00:58:34', '2025-11-23 04:45:05', NULL);

-- ----------------------------
-- Table structure for ai_article_tasks
-- ----------------------------
DROP TABLE IF EXISTS `ai_article_tasks`;
CREATE TABLE `ai_article_tasks`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '任务名称',
  `topic` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '生成主题（或最终生成的完整提示词）',
  `prompt_template_id` int UNSIGNED NULL DEFAULT NULL COMMENT '提示词模板ID',
  `prompt_variables` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '提示词变量值（JSON）',
  `category_id` int UNSIGNED NULL DEFAULT NULL COMMENT '目标分类ID',
  `ai_config_id` int UNSIGNED NOT NULL COMMENT 'AI配置ID',
  `total_count` int NOT NULL DEFAULT 1 COMMENT '计划生成数量',
  `generated_count` int NOT NULL DEFAULT 0 COMMENT '已生成数量',
  `success_count` int NOT NULL DEFAULT 0 COMMENT '成功数量',
  `failed_count` int NOT NULL DEFAULT 0 COMMENT '失败数量',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'pending' COMMENT '状态：pending待处理/processing处理中/completed已完成/failed失败/stopped已停止',
  `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '生成设置（JSON）：文章长度、风格、是否自动发布等',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '错误信息',
  `started_at` datetime NULL DEFAULT NULL COMMENT '开始时间',
  `completed_at` datetime NULL DEFAULT NULL COMMENT '完成时间',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_category_id`(`category_id` ASC) USING BTREE,
  INDEX `idx_ai_config_id`(`ai_config_id` ASC) USING BTREE,
  INDEX `idx_prompt_template_id`(`prompt_template_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'AI文章生成任务表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ai_article_tasks
-- ----------------------------

-- ----------------------------
-- Table structure for ai_configs
-- ----------------------------
DROP TABLE IF EXISTS `ai_configs`;
CREATE TABLE `ai_configs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置名称',
  `provider` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AI提供商：openai/claude/wenxin/tongyi/chatglm',
  `api_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'API密钥',
  `api_endpoint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'API端点（可选）',
  `model` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '模型名称',
  `max_tokens` int NULL DEFAULT 2000 COMMENT '最大token数',
  `temperature` decimal(3, 2) NULL DEFAULT 0.70 COMMENT '温度参数 0-1',
  `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '其他配置（JSON格式）',
  `is_default` tinyint(1) NULL DEFAULT 0 COMMENT '是否默认配置',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_provider`(`provider` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'AI配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ai_configs
-- ----------------------------
INSERT INTO `ai_configs` VALUES (3, 1, '逍遥免费AI', 'carefreeai', 'sk-VDnLLud4H4QLfNXh3460630fA7324b32B94311B64751E2F0', '', 'hunyuan-lite', 2000, 0.70, '[]', 0, 1, '2025-12-01 01:32:04', '2025-12-01 01:32:04');

-- ----------------------------
-- Table structure for ai_generated_articles
-- ----------------------------
DROP TABLE IF EXISTS `ai_generated_articles`;
CREATE TABLE `ai_generated_articles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `task_id` int UNSIGNED NOT NULL COMMENT '任务ID',
  `article_id` int UNSIGNED NULL DEFAULT NULL COMMENT '文章ID（生成后关联）',
  `prompt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '使用的提示词',
  `request_prompt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '实际发送给AI的提示词',
  `generated_title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '生成的标题',
  `generated_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '生成的内容',
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT 'AI原始返回结果（JSON）',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'pending' COMMENT '状态：pending待处理/success成功/failed失败',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '错误信息',
  `tokens_used` int NULL DEFAULT 0 COMMENT '使用的token数',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_task_id`(`task_id` ASC) USING BTREE,
  INDEX `idx_article_id`(`article_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'AI生成文章记录表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for ai_image_prompt_templates
-- ----------------------------
DROP TABLE IF EXISTS `ai_image_prompt_templates`;
CREATE TABLE `ai_image_prompt_templates`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `site_id` int NOT NULL COMMENT '站点ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板名称',
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '分类：人物/风景/抽象/商业等',
  `prompt_template` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '提示词模板',
  `negative_prompt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '负面提示词',
  `variables` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '变量定义（JSON）',
  `default_width` int NULL DEFAULT NULL COMMENT '默认宽度',
  `default_height` int NULL DEFAULT NULL COMMENT '默认高度',
  `default_style` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '默认风格',
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '模板缩略图',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '模板描述',
  `usage_count` int NOT NULL DEFAULT 0 COMMENT '使用次数',
  `is_public` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否公开',
  `is_builtin` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否内置',
  `sort_order` int NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_category`(`category` ASC) USING BTREE,
  INDEX `idx_is_public`(`is_public` ASC) USING BTREE,
  INDEX `idx_usage_count`(`usage_count` ASC) USING BTREE,
  INDEX `idx_sort_order`(`sort_order` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'AI图片提示词模板表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ai_image_prompt_templates
-- ----------------------------

-- ----------------------------
-- Table structure for ai_image_tasks
-- ----------------------------
DROP TABLE IF EXISTS `ai_image_tasks`;
CREATE TABLE `ai_image_tasks`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `site_id` int NOT NULL COMMENT '站点ID',
  `user_id` int NOT NULL COMMENT '用户ID',
  `ai_model_id` int NOT NULL COMMENT 'AI模型ID',
  `prompt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '生成提示词',
  `negative_prompt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '负面提示词',
  `template_id` int NULL DEFAULT NULL COMMENT '提示词模板ID',
  `template_variables` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '模板变量（JSON）',
  `image_count` int NOT NULL DEFAULT 1 COMMENT '生成图片数量',
  `width` int NULL DEFAULT NULL COMMENT '图片宽度',
  `height` int NULL DEFAULT NULL COMMENT '图片高度',
  `style` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '风格',
  `quality` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '质量',
  `extra_params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '其他参数（JSON）',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '状态：pending/processing/completed/failed',
  `progress` int NOT NULL DEFAULT 0 COMMENT '进度0-100',
  `generated_count` int NOT NULL DEFAULT 0 COMMENT '已生成数量',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '错误信息',
  `cost_tokens` int NULL DEFAULT NULL COMMENT '消耗token数',
  `cost_amount` decimal(10, 4) NULL DEFAULT NULL COMMENT '消耗金额',
  `started_at` datetime NULL DEFAULT NULL COMMENT '开始时间',
  `completed_at` datetime NULL DEFAULT NULL COMMENT '完成时间',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_ai_model_id`(`ai_model_id` ASC) USING BTREE,
  INDEX `idx_template_id`(`template_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_created_at`(`created_at` ASC) USING BTREE,
  CONSTRAINT `fk_ai_image_tasks_model` FOREIGN KEY (`ai_model_id`) REFERENCES `ai_models` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'AI图片生成任务表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ai_image_tasks
-- ----------------------------

-- ----------------------------
-- Table structure for ai_models
-- ----------------------------
DROP TABLE IF EXISTS `ai_models`;
CREATE TABLE `ai_models`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `provider_id` int NOT NULL COMMENT '所属厂商ID',
  `model_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模型代码',
  `model_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模型名称',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '模型描述',
  `context_window` int NULL DEFAULT NULL COMMENT '上下文窗口大小',
  `max_output_tokens` int NULL DEFAULT NULL COMMENT '最大输出tokens',
  `supports_functions` tinyint(1) NULL DEFAULT 0 COMMENT '是否支持函数调用',
  `supports_text_generation` tinyint(1) NULL DEFAULT 1 COMMENT '支持文本生成',
  `supports_vision` tinyint(1) NULL DEFAULT 0 COMMENT '是否支持视觉',
  `supports_image_input` tinyint(1) NULL DEFAULT 0 COMMENT '支持图像输入/理解',
  `supports_image_generation` tinyint(1) NULL DEFAULT 0 COMMENT '支持图像生成(如DALL-E)',
  `supports_audio_input` tinyint(1) NULL DEFAULT 0 COMMENT '支持音频输入/语音识别(STT)',
  `supports_audio_output` tinyint(1) NULL DEFAULT 0 COMMENT '支持音频输出/文本转语音(TTS)',
  `supports_audio_generation` tinyint(1) NULL DEFAULT 0 COMMENT '支持音频生成(如音乐、音效)',
  `supports_video_input` tinyint(1) NULL DEFAULT 0 COMMENT '支持视频输入/理解',
  `supports_video_generation` tinyint(1) NULL DEFAULT 0 COMMENT '支持视频生成',
  `supports_document_parsing` tinyint(1) NULL DEFAULT 0 COMMENT '支持文档解析(PDF/Word/Excel等)',
  `supports_code_generation` tinyint(1) NULL DEFAULT 0 COMMENT '支持代码生成',
  `supports_code_interpreter` tinyint(1) NULL DEFAULT 0 COMMENT '支持代码解释器/执行',
  `supports_realtime_voice` tinyint(1) NULL DEFAULT 0 COMMENT '支持实时语音对话',
  `supports_streaming` tinyint(1) NULL DEFAULT 1 COMMENT '支持流式输出',
  `supports_embeddings` tinyint(1) NULL DEFAULT 0 COMMENT '支持嵌入向量生成',
  `supports_web_search` tinyint(1) NULL DEFAULT 0 COMMENT '支持网络搜索集成',
  `multimodal_capabilities` json NULL COMMENT '多模态能力详细配置(JSON)',
  `is_custom` tinyint(1) NULL DEFAULT 0 COMMENT '是否自定义模型',
  `is_builtin` tinyint(1) NULL DEFAULT 0 COMMENT '是否内置',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态',
  `sort_order` int NULL DEFAULT 0 COMMENT '排序',
  `pricing_info` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '价格信息JSON',
  `extra_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '额外配置JSON',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_provider_id`(`provider_id` ASC) USING BTREE,
  INDEX `idx_model_code`(`model_code` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_sort_order`(`sort_order` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 194 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'AI模型表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ai_models
-- ----------------------------
INSERT INTO `ai_models` VALUES (1, 1, 'gpt-4-turbo-2024-04-09', 'GPT-4 Turbo (最新)', 'GPT-4 Turbo最新版本，支持视觉', 128000, NULL, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 1, 0, 1, '{\"vision_quality\": \"good\"}', 0, 1, 0, 5, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (2, 1, 'gpt-4', 'GPT-4 (8K)', 'GPT-4标准版，8K上下文', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 1, 0, 1, '{\"vision_quality\": \"good\"}', 0, 1, 0, 2, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (3, 1, 'gpt-4-32k', 'GPT-4 (32K)', 'GPT-4扩展版，32K上下文', 32768, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 1, 0, 1, '{\"vision_quality\": \"good\"}', 0, 1, 0, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (4, 1, 'gpt-3.5-turbo', 'GPT-3.5 Turbo (16K)', '快速且经济的模型', 16384, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (5, 1, 'gpt-3.5-turbo-16k', 'GPT-3.5 Turbo (16K)', '扩展上下文版本', 16384, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 0, 5, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (6, 2, 'claude-3-opus-20240229', 'Claude 3 Opus', '最强大的Claude模型', 200000, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, '{\"vision_quality\": \"good\"}', 0, 1, 0, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (7, 2, 'claude-3-sonnet-20240229', 'Claude 3 Sonnet', '平衡性能和成本', 200000, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, '{\"vision_quality\": \"good\"}', 0, 1, 0, 2, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (8, 2, 'claude-3-haiku-20240307', 'Claude 3 Haiku', '快速且经济的模型', 200000, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, '{\"vision_quality\": \"good\"}', 0, 1, 0, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (9, 2, 'claude-2.1', 'Claude 2.1', '上一代Claude模型', 100000, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 0, 4, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (10, 2, 'claude-2.0', 'Claude 2.0', 'Claude 2基础版', 100000, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 0, 5, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (11, 3, 'gemini-1.5-pro', 'Gemini 1.5 Pro', 'Gemini 1.5 Pro，支持超长上下文和多模态', 2000000, NULL, 1, 1, 1, 1, 0, 1, 0, 0, 1, 0, 1, 1, 0, 0, 1, 0, 1, '{\"audio_quality\": \"good\", \"vision_quality\": \"good\", \"native_multimodal\": true}', 0, 1, 0, 2, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (12, 3, 'gemini-1.5-flash', 'Gemini 1.5 Flash', 'Gemini 1.5 Flash，快速高效的多模态模型', 1000000, NULL, 1, 1, 1, 1, 0, 1, 0, 0, 1, 0, 1, 1, 0, 0, 1, 0, 1, '{\"audio_quality\": \"good\", \"vision_quality\": \"good\", \"native_multimodal\": true}', 0, 1, 0, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (13, 3, 'gemini-pro', 'Gemini Pro', '专业版模型', 30720, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 0, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (14, 3, 'gemini-pro-vision', 'Gemini Pro Vision', '支持视觉理解', 30720, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 0, 4, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (16, 4, 'ernie-3.5-8k', '文心大模型 3.5 (8K)', '文心3.5标准版', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 1, 1, 2, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (17, 4, 'ernie-3.5-128k', '文心大模型 3.5 (128K)', '超长上下文版本', 131072, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 1, 1, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (18, 4, 'ernie-speed-8k', '文心极速版 (8K)', '快速响应，经济实惠', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 1, 1, 4, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (19, 4, 'ernie-lite-8k', '文心轻量版 (8K)', '轻量级模型', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 1, 1, 5, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (20, 5, 'qwen-turbo', '通义千问 Turbo', '快速响应，性价比高', 8192, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"long_context\": true}', 0, 1, 1, 1, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (21, 5, 'qwen-plus', '通义千问 Plus', '增强版本，效果更好', 32768, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"long_context\": true}', 0, 1, 1, 2, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (22, 5, 'qwen-max', '通义千问 Max', '最强大的通义千问模型', 8192, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"long_context\": true}', 0, 1, 1, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (23, 5, 'qwen-max-longcontext', '通义千问 Max (长文本)', '支持超长上下文', 30000, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"long_context\": true}', 0, 1, 1, 4, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (24, 6, 'glm-4', 'GLM-4', 'GLM-4基础模型，支持长文本和函数调用', 128000, NULL, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (25, 6, 'glm-4v', 'GLM-4V', 'GLM-4视觉版本', 2048, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"vision_focus\": true}', 0, 1, 1, 2, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (27, 6, 'chatglm_turbo', 'ChatGLM Turbo', 'ChatGLM快速版', 8192, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (28, 7, 'deepseek-chat', 'DeepSeek Chat', 'DeepSeek对话模型', 32768, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"code_focus\": true}', 0, 1, 1, 5, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (29, 7, 'deepseek-coder', 'DeepSeek Coder', '专业代码生成模型', 32768, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"code_focus\": true}', 0, 1, 1, 6, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (36, 10, 'generalv3.5', '星火3.5', '讯飞星火3.5版本', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 1, 1, 1, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (37, 10, 'generalv3', '星火3.0', '讯飞星火3.0版本', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 1, 1, 2, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (38, 10, 'generalv2', '星火2.0', '讯飞星火2.0版本', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 1, 1, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (39, 11, 'hunyuan-lite', '混元 Lite', '轻量版本，快速响应', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 1, 1, 1, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (40, 11, 'hunyuan-standard', '混元 Standard', '标准版本', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 1, 1, 2, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (41, 11, 'hunyuan-pro', '混元 Pro', '专业版本，效果更好', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 1, 1, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (42, 12, 'abab6-chat', 'ABAB 6', 'MiniMax ABAB 6对话模型', 8192, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (43, 12, 'abab5.5-chat', 'ABAB 5.5', 'MiniMax ABAB 5.5对话模型', 8192, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (44, 12, 'abab5-chat', 'ABAB 5', 'MiniMax ABAB 5对话模型', 8192, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (45, 13, 'gpt-3.5-turbo', 'GPT-3.5 Turbo', 'OpenAI兼容接口常用模型', 16384, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (46, 13, 'gpt-4', 'GPT-4', 'OpenAI兼容接口高级模型', 8192, NULL, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 1, 0, 1, '{\"vision_quality\": \"good\"}', 0, 1, 0, 2, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (47, 13, 'claude-2', 'Claude-2', 'Claude兼容接口', 100000, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (48, 13, 'llama-2-70b', 'Llama 2 70B', 'Meta Llama模型', 4096, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (49, 13, 'mistral-7b', 'Mistral 7B', 'Mistral AI模型', 8192, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 5, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (50, 13, 'custom-model', '其他自定义模型', '可手动输入任意模型名称', 4096, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 6, NULL, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_models` VALUES (51, 14, 'MiniMax-M2', 'MiniMax-M2', 'MiniMax-M2', NULL, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 0, 1, 0, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (52, 14, 'hunyuan-lite', 'hunyuan-lite', 'hunyuan-lite', NULL, NULL, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, '{\"chinese_optimized\": true}', 0, 0, 1, 0, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (62, 7, 'deepseek-r1', 'DeepSeek-R1', 'DeepSeek推理增强模型，擅长数学和逻辑推理', 64000, 16384, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 1, 0, 0, '{\"complex_tasks\": true, \"reasoning_focus\": true}', 0, 1, 1, 4, NULL, NULL, '2025-11-13 23:44:34', '2025-11-13 23:44:34');
INSERT INTO `ai_models` VALUES (97, 2, 'claude-opus-4-5', 'Claude Opus 4.5', 'Anthropic最新旗舰模型，代码能力世界第一，SWE-bench达80.9%准确率', 200000, 16384, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (98, 2, 'claude-sonnet-4-5', 'Claude Sonnet 4.5', 'Claude 4.5系列平衡版，推理和数学能力大幅提升', 200000, 16384, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (99, 2, 'claude-haiku-4-5', 'Claude Haiku 4.5', 'Claude 4.5系列快速版，低延迟优化，性价比极高', 200000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (100, 1, 'gpt-5', 'GPT-5', 'GPT-5旗舰模型，包含DALL-E 3图像生成能力', 200000, 32768, 1, 1, 1, 0, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (101, 1, 'gpt-5-1', 'GPT-5.1', 'GPT-5升级版，更温暖、更强大，可自定义语气和风格', 200000, 32768, 1, 1, 1, 0, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (102, 1, 'gpt-4-1', 'GPT-4.1', 'GPT-4升级版，代码和指令遵循能力大幅提升', 128000, 16384, 1, 1, 1, 0, 0, 1, 1, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (103, 1, 'gpt-4-1-mini', 'GPT-4.1 Mini', 'GPT-4.1轻量版，性价比高', 128000, 16384, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (104, 1, 'gpt-4-1-nano', 'GPT-4.1 Nano', 'GPT-4.1超轻量版，速度最快', 64000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 5, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (105, 1, 'o3', 'O3', 'OpenAI最新推理模型，深度思考能力', 200000, 65536, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 6, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (106, 1, 'o4-mini', 'O4 Mini', 'O4推理模型轻量版', 128000, 32768, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 7, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (107, 3, 'gemini-3', 'Gemini 3', 'Google最新智能模型，推理深度空前，19/20基准测试领先', 2000000, 8192, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (108, 3, 'gemini-3-pro', 'Gemini 3 Pro', 'Gemini 3 Pro版本，跨文本、图像、视频、音频、代码无缝整合', 2000000, 8192, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (109, 3, 'gemini-3-deep-think', 'Gemini 3 Deep Think', 'Gemini 3深度思考模式，Ultra订阅用户专享', 2000000, 16384, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (110, 3, 'gemini-2-5-pro', 'Gemini 2.5 Pro', 'Gemini 2.5系列旗舰，支持自适应思考', 2000000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (111, 3, 'gemini-2-5-flash', 'Gemini 2.5 Flash', 'Gemini 2.5快速版，高性能低成本', 1000000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 5, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (112, 3, 'gemini-2-5-flash-lite', 'Gemini 2.5 Flash Lite', 'Gemini 2.5超轻量版', 1000000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 6, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (113, 7, 'deepseek-v3-2-exp', 'DeepSeek V3.2 Exp', 'DeepSeek最新实验版，引入DSA稀疏注意力，成本降低50%', 64000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (114, 7, 'deepseek-v3-1', 'DeepSeek V3.1', 'DeepSeek混合架构，思考/非思考双模式，SWE-bench提升40%', 64000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (115, 7, 'deepseek-r2', 'DeepSeek R2', 'DeepSeek第二代推理模型', 64000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (116, 5, 'qwen3-max', 'Qwen3 Max', 'Qwen3系列旗舰，MoE架构，预训练数据超20T tokens', 128000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (117, 5, 'qwen3-coder-plus', 'Qwen3 Coder Plus', 'Qwen3代码增强版，强大Coding Agent能力', 128000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (118, 5, 'qwen2-5-max', 'Qwen2.5 Max', 'Qwen2.5系列旗舰', 128000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (119, 5, 'qwen2-5-vl', 'Qwen2.5 VL', 'Qwen2.5视觉理解升级版', 128000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (120, 5, 'qwen2-5-1m', 'Qwen2.5 1M', 'Qwen2.5百万token长文本处理，Dual Chunk Attention机制', 1000000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 5, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (121, 5, 'qwen2-5-omni', 'Qwen2.5 Omni', 'Qwen2.5多模态模型', 128000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 6, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (122, 4, 'ernie-4.5-moe-47b', 'ERNIE 4.5 MoE 47B', '百度文心4.5系列MoE模型，47B激活参数，424B总参数，已开源Apache 2.0', 200000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (123, 4, 'ernie-4.5-moe-3b', 'ERNIE 4.5 MoE 3B', '百度文心4.5系列轻量MoE模型，3B激活参数', 128000, 4096, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (124, 4, 'ernie-4.5-0.3b', 'ERNIE 4.5 0.3B', '百度文心4.5系列超轻量Dense模型，0.3B参数', 64000, 4096, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (125, 4, 'ernie-5.0-preview', 'ERNIE 5.0 Preview', '百度文心5.0预览版，原生全模态，可处理文本/图像/音频/视频，性能超越GPT-5和Gemini 2.5 Pro', 200000, 16384, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (126, 6, 'glm-4.5', 'GLM-4.5', '智谱AI旗舰Agent基础模型，355B总参数32B激活，全球第三、开源第一，MIT开源', 128000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (127, 6, 'glm-realtime', 'GLM-Realtime', 'GLM实时多模态模型，端到端视频理解和语音交互，支持唱歌功能', 128000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (128, 9, 'doubao-seed-1.6', 'Doubao Seed 1.6', '豆包1.6全功能模型，256K上下文，自适应思考，综合能力提升32%', 256000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (129, 9, 'doubao-seed-1.6-thinking', 'Doubao Seed 1.6 Thinking', '豆包1.6深度推理版，编码+58%、数学+43%，支持复杂Agent', 256000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (130, 9, 'doubao-seed-1.6-flash', 'Doubao Seed 1.6 Flash', '豆包1.6极速版，适用于实时交互场景', 256000, 4096, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (131, 9, 'doubao-seed-code', 'Doubao Seed Code', '豆包编程模型，专为Agentic Coding优化，256K上下文+视觉理解', 256000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (132, 8, 'kimi-k1.5', 'Kimi K1.5', 'Kimi K1.5模型，数学/编码/多模态推理达到o1级别', 128000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (133, 8, 'kimi-k2', 'Kimi K2', 'Kimi K2模型，1T总参数32B激活MoE架构，256K上下文', 256000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (134, 8, 'kimi-k2-thinking', 'Kimi K2 Thinking', 'Kimi K2 Thinking推理模型，1T参数32B激活，支持200-300工具调用，开源SOTA推理模型', 256000, 16384, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (135, 8, 'kimi-linear', 'Kimi Linear', 'Kimi Linear架构，线性注意力机制，推理速度6倍提升，KV缓存减少75%', 256000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (136, 10, 'spark-x1.5', 'Spark X1.5', '讯飞星火X1.5深度推理模型，全国产算力训练，MoE架构，推理效率提升100%', 128000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (137, 10, 'spark-x1', 'Spark X1', '讯飞星火X1推理模型，对标o1和DeepSeek R1，数学和知识问答优异', 128000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (138, 10, 'spark-4.0-ultra', 'Spark 4.0 Ultra', '讯飞星火4.0 Ultra，图文识别能力升级，文档识别准确率+40%', 128000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (139, 10, 'spark-4.0-turbo', 'Spark 4.0 Turbo', '讯飞星火4.0 Turbo，高性能版本', 128000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (140, 12, 'minimax-text-01', 'MiniMax Text 01', 'MiniMax-01文本模型，456B参数45.9B激活，4M上下文（GPT-4o的32倍），线性注意力机制', 4000000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (141, 12, 'minimax-vl-01', 'MiniMax VL 01', 'MiniMax-01视觉多模态模型，456B参数，4M上下文', 4000000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (142, 12, 'minimax-m1', 'MiniMax M1', 'MiniMax-M1混合注意力推理模型，1M上下文，80K生成长度，Lightning Attention机制', 1000000, 80000, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (143, 12, 'minimax-m2', 'MiniMax M2', 'MiniMax-M2稀疏MoE模型，230B总参数10B激活，开源第一，性能接近Claude 4.5 Sonnet', 256000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (144, 16, 'llama-4-scout', 'Llama 4 Scout', 'Meta Llama 4 Scout，多模态MoE模型，16专家17B激活参数，10M上下文，单H100可运行', 10000000, 8192, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (145, 16, 'llama-4-maverick', 'Llama 4 Maverick', 'Meta Llama 4 Maverick，128专家17B激活参数，400B总参数', 10000000, 8192, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (146, 16, 'llama-4-behemoth', 'Llama 4 Behemoth', 'Meta Llama 4 Behemoth，史上最强Llama模型，2T总参数（训练中）', 10000000, 8192, 1, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 0, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (147, 17, 'mistral-large-2.1', 'Mistral Large 2.1', 'Mistral Large 2.1（2024年11月），长上下文理解提升，函数调用更准确', 128000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (148, 17, 'mistral-medium-3', 'Mistral Medium 3', 'Mistral Medium 3，平衡前沿性能和成本，比Large便宜10倍', 128000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (149, 17, 'mistral-small-3.1', 'Mistral Small 3.1', 'Mistral Small 3.1（2025年3月），24B参数多模态模型，128K上下文，150 tokens/s', 128000, 8192, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (150, 17, 'codestral-2501', 'Codestral 2501', 'Codestral 2501（2025年1月），专业编程模型', 128000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (151, 18, 'grok-4.1', 'Grok 4.1', 'xAI Grok 4.1（2025年11月18日），LMArena排名#2（1465 Elo），非推理模式', 2000000, 16384, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (152, 18, 'grok-4.1-thinking', 'Grok 4.1 Thinking', 'xAI Grok 4.1 Thinking，LMArena排名#1（1483 Elo），领先第二名31分', 2000000, 16384, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (153, 18, 'grok-4-fast', 'Grok 4 Fast', 'xAI Grok 4 Fast，高性价比推理模型，2M上下文，统一推理/非推理架构', 2000000, 16384, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 3, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (154, 18, 'grok-4', 'Grok 4', 'xAI Grok 4（2025年7月9日），旗舰模型', 2000000, 16384, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 4, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (155, 18, 'grok-4-heavy', 'Grok 4 Heavy', 'xAI Grok 4 Heavy，重量级版本', 2000000, 16384, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 5, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (156, 19, 'command-a-03-2025', 'Command A', 'Cohere Command A（2025年3月），111B参数，256K上下文，吞吐量比Command R+提升150%，擅长工具使用/RAG/Agent/多语言', 256000, 8192, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 1, NULL, NULL, NULL, NULL);
INSERT INTO `ai_models` VALUES (157, 19, 'command-r-plus-08-2024', 'Command R+ 08-2024', 'Cohere Command R+（2024年8月），104B参数，企业级模型', 128000, 4096, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, NULL, 0, 1, 1, 2, NULL, NULL, NULL, NULL);

-- ----------------------------
-- Table structure for ai_prompt_templates
-- ----------------------------
DROP TABLE IF EXISTS `ai_prompt_templates`;
CREATE TABLE `ai_prompt_templates`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板名称',
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '分类：article/product/news/custom',
  `prompt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '提示词内容',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '描述',
  `variables` json NULL COMMENT '可用变量说明',
  `is_system` tinyint(1) NULL DEFAULT 0 COMMENT '是否系统预置',
  `sort_order` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `usage_count` int NULL DEFAULT 0 COMMENT '使用次数',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_category`(`category` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'AI提示词模板表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ai_prompt_templates
-- ----------------------------
INSERT INTO `ai_prompt_templates` VALUES (1, 1, '科技文章模板', '科技文章', '请根据以下主题撰写一篇专业的科技文章：\n\n【主题】{topic}\n\n【要求】\n- 字数：{length}字左右\n- 文章风格：{style}\n- 关键词：{keywords}\n- 文章结构完整，包含标题、引言、正文、总结\n- 内容专业、准确、易读\n\n请直接输出文章内容。', '适用于科技行业的专业文章撰写，可指定字数、风格和关键词', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"文章字数\", \"default\": 1000, \"required\": false}, {\"name\": \"style\", \"type\": \"select\", \"label\": \"文章风格\", \"default\": \"专业\", \"options\": [\"专业\", \"通俗易懂\", \"学术\"], \"required\": false}, {\"name\": \"keywords\", \"type\": \"text\", \"label\": \"关键词\", \"default\": \"\", \"required\": false}]', 1, 1, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (2, 1, '产品介绍模板', '产品介绍', '请根据以下主题撰写一篇产品介绍文章：\n\n【主题】{topic}\n\n【要求】\n- 字数：{length}字左右\n- 产品类型：{product_type}\n- 核心卖点：{key_features}\n- 突出产品优势和特色\n- 语言生动、具有说服力\n- 包含使用场景描述\n\n请直接输出文章内容。', '用于产品宣传和介绍的文章模板', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"文章字数\", \"default\": 800, \"required\": false}, {\"name\": \"product_type\", \"type\": \"text\", \"label\": \"产品类型\", \"default\": \"软件产品\", \"required\": false}, {\"name\": \"key_features\", \"type\": \"textarea\", \"label\": \"核心卖点\", \"default\": \"功能强大、易于使用\", \"required\": false}]', 1, 2, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (3, 1, '新闻报道模板', '新闻报道', '请根据以下主题撰写一篇新闻报道：\n\n【主题】{topic}\n\n【要求】\n- 字数：{length}字左右\n- 新闻时间：{news_time}\n- 新闻地点：{news_location}\n- 采用新闻写作风格，客观、准确\n- 结构：标题、导语、正文、结尾\n- 突出新闻要素：5W1H\n\n请直接输出新闻内容。', '用于撰写新闻报道类文章', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"文章字数\", \"default\": 600, \"required\": false}, {\"name\": \"news_time\", \"type\": \"text\", \"label\": \"新闻时间\", \"default\": \"今日\", \"required\": false}, {\"name\": \"news_location\", \"type\": \"text\", \"label\": \"新闻地点\", \"default\": \"\", \"required\": false}]', 1, 3, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (4, 1, '教程指南模板', '教程指南', '请根据以下主题撰写一篇教程指南：\n\n【主题】{topic}\n\n【要求】\n- 字数：{length}字左右\n- 目标读者：{target_level}\n- 步骤清晰、易于理解\n- 包含具体操作步骤\n- 结构：简介、准备工作、详细步骤、注意事项、总结\n\n请直接输出教程内容。', '用于撰写操作指南和教程类文章', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"文章字数\", \"default\": 1500, \"required\": false}, {\"name\": \"target_level\", \"type\": \"select\", \"label\": \"目标读者水平\", \"default\": \"初学者\", \"options\": [\"初学者\", \"进阶用户\", \"专业人士\"], \"required\": false}]', 1, 4, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (5, 1, '行业分析模板', '行业分析', '请根据以下主题撰写一篇行业分析文章：\n\n【主题】{topic}\n\n【要求】\n- 字数：{length}字左右\n- 分析角度：{analysis_focus}\n- 时间范围：{time_range}\n- 包含数据和案例支撑\n- 结构：行业概述、市场现状、发展趋势、机遇与挑战、总结\n- 分析深入、观点明确\n\n请直接输出分析文章。', '用于撰写行业分析和市场研究类文章', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"文章字数\", \"default\": 2000, \"required\": false}, {\"name\": \"analysis_focus\", \"type\": \"select\", \"label\": \"分析角度\", \"default\": \"整体发展\", \"options\": [\"整体发展\", \"技术创新\", \"市场竞争\", \"政策影响\"], \"required\": false}, {\"name\": \"time_range\", \"type\": \"text\", \"label\": \"时间范围\", \"default\": \"2024-2025\", \"required\": false}]', 1, 5, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (6, 1, 'SEO优化文章模板', 'SEO文章', '请撰写一篇SEO优化的文章：\n\n【主题】{topic}\n\n【SEO要求】\n- 字数：{length}字左右\n- 目标关键词：{keywords}\n- 长尾关键词：{long_tail_keywords}\n- 内容深度：{depth}\n- 关键词密度：2-3%，自然融入，避免堆砌\n- 标题包含主关键词\n- 每个段落都有小标题（H2/H3）\n- 结构清晰，利于搜索引擎抓取\n\n【内容要求】\n- 满足用户搜索意图\n- 提供实用价值\n- 包含数据和案例\n- 适当内链建议\n\n请直接输出文章内容。', '专门用于SEO优化的文章撰写，提升搜索引擎排名', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"文章字数\", \"default\": 1500, \"required\": false}, {\"name\": \"keywords\", \"type\": \"text\", \"label\": \"目标关键词\", \"default\": \"\", \"required\": false, \"placeholder\": \"主要优化的关键词\"}, {\"name\": \"long_tail_keywords\", \"type\": \"text\", \"label\": \"长尾关键词\", \"default\": \"\", \"required\": false, \"placeholder\": \"相关长尾词，逗号分隔\"}, {\"name\": \"depth\", \"type\": \"select\", \"label\": \"内容深度\", \"default\": \"深入\", \"options\": [\"基础介绍\", \"中等深度\", \"深入分析\"], \"required\": false}]', 1, 6, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (7, 1, '营销软文模板', '营销文案', '请撰写一篇营销软文：\n\n【主题】{topic}\n\n【营销要求】\n- 字数：{length}字左右\n- 产品/服务：{product}\n- 目标人群：{target_audience}\n- 营销角度：{marketing_angle}\n- 转化目标：{conversion_goal}\n\n【写作要求】\n- 不要硬广，自然植入\n- 讲故事，引发共鸣\n- 突出痛点和解决方案\n- 建立信任感\n- 软性引导行动\n\n请直接输出软文内容。', '适用于产品推广、品牌宣传的营销软文', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"文章字数\", \"default\": 1000, \"required\": false}, {\"name\": \"product\", \"type\": \"text\", \"label\": \"产品/服务\", \"default\": \"\", \"required\": false, \"placeholder\": \"推广的产品或服务\"}, {\"name\": \"target_audience\", \"type\": \"text\", \"label\": \"目标人群\", \"default\": \"年轻白领\", \"required\": false, \"placeholder\": \"如：年轻白领、宝妈、企业主\"}, {\"name\": \"marketing_angle\", \"type\": \"select\", \"label\": \"营销角度\", \"default\": \"痛点解决\", \"options\": [\"痛点解决\", \"场景化\", \"对比优势\", \"用户案例\", \"情感共鸣\"], \"required\": false}, {\"name\": \"conversion_goal\", \"type\": \"text\", \"label\": \"转化目标\", \"default\": \"了解产品\", \"required\": false, \"placeholder\": \"如：咨询、下单、注册\"}]', 1, 7, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (8, 1, '技术博客模板', '技术博客', '请撰写一篇技术博客文章：\n\n【主题】{topic}\n\n【技术要求】\n- 字数：{length}字左右\n- 技术难度：{difficulty}\n- 是否包含代码：{include_code}\n- 技术栈：{tech_stack}\n\n【内容结构】\n- 背景介绍：为什么需要这个技术\n- 核心概念：关键原理讲解\n- 实现步骤：详细操作指南\n- 代码示例：实际应用代码（如需要）\n- 注意事项：常见问题和解决方案\n- 总结：最佳实践建议\n\n请直接输出博客内容。', '适用于技术分享、开发经验总结的博客文章', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"文章字数\", \"default\": 2000, \"required\": false}, {\"name\": \"difficulty\", \"type\": \"select\", \"label\": \"技术难度\", \"default\": \"中级\", \"options\": [\"入门级\", \"中级\", \"高级\"], \"required\": false}, {\"name\": \"include_code\", \"type\": \"select\", \"label\": \"是否包含代码\", \"default\": \"是\", \"options\": [\"是\", \"否\"], \"required\": false}, {\"name\": \"tech_stack\", \"type\": \"text\", \"label\": \"技术栈\", \"default\": \"\", \"required\": false, \"placeholder\": \"如：Vue.js、Python、Docker等\"}]', 1, 8, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (9, 1, '电商产品描述模板', '电商文案', '请为以下产品撰写吸引人的产品描述：\n\n【产品】{topic}\n\n【产品信息】\n- 产品类型：{product_type}\n- 核心卖点：{selling_points}\n- 目标用户：{target_users}\n- 价格区间：{price_range}\n\n【描述要求】\n- 字数：{length}字左右\n- 开头吸引眼球\n- 突出核心优势（3-5个）\n- 解决用户痛点\n- 使用场景描述\n- 包含购买理由\n- 添加紧迫感（限时优惠等）\n- 结尾引导下单\n\n请直接输出产品描述。', '专门用于电商平台的产品详情页文案', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"文案字数\", \"default\": 500, \"required\": false}, {\"name\": \"product_type\", \"type\": \"text\", \"label\": \"产品类型\", \"default\": \"\", \"required\": false, \"placeholder\": \"如：服饰、电子产品、食品\"}, {\"name\": \"selling_points\", \"type\": \"textarea\", \"label\": \"核心卖点\", \"default\": \"\", \"required\": false, \"placeholder\": \"产品的主要优势，一行一个\"}, {\"name\": \"target_users\", \"type\": \"text\", \"label\": \"目标用户\", \"default\": \"大众用户\", \"required\": false, \"placeholder\": \"如：年轻女性、科技爱好者\"}, {\"name\": \"price_range\", \"type\": \"text\", \"label\": \"价格区间\", \"default\": \"中等价位\", \"required\": false, \"placeholder\": \"如：高端、中等、实惠\"}]', 1, 9, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (10, 1, '社交媒体文案模板', '社交媒体', '请创作一条社交媒体文案：\n\n【主题】{topic}\n\n【平台要求】\n- 目标平台：{platform}\n- 文案长度：{length}字左右\n- 内容风格：{style}\n- 互动目标：{engagement_goal}\n\n【创作要求】\n- 开头3秒抓住注意力\n- 语言轻松、口语化\n- 适当使用emoji表情\n- 包含话题标签\n- 引导互动（点赞、评论、转发）\n- 添加行动号召\n\n请直接输出文案内容。', '适用于微博、小红书、抖音等社交平台的内容创作', '[{\"name\": \"platform\", \"type\": \"select\", \"label\": \"目标平台\", \"default\": \"通用\", \"options\": [\"通用\", \"微信公众号\", \"微博\", \"小红书\", \"抖音\", \"快手\"], \"required\": false}, {\"name\": \"length\", \"type\": \"number\", \"label\": \"文案长度\", \"default\": 200, \"required\": false}, {\"name\": \"style\", \"type\": \"select\", \"label\": \"内容风格\", \"default\": \"轻松幽默\", \"options\": [\"轻松幽默\", \"专业干货\", \"情感共鸣\", \"励志鸡汤\", \"种草推荐\"], \"required\": false}, {\"name\": \"engagement_goal\", \"type\": \"select\", \"label\": \"互动目标\", \"default\": \"点赞收藏\", \"options\": [\"点赞收藏\", \"评论互动\", \"转发分享\", \"引流私信\"], \"required\": false}]', 1, 10, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (11, 1, '问答知识模板', '问答Q&A', '请针对以下问题撰写详细解答：\n\n【问题】{topic}\n\n【回答要求】\n- 字数：{length}字左右\n- 回答风格：{style}\n- 专业程度：{expertise_level}\n\n【内容结构】\n- 直接回答：开门见山给出答案\n- 详细解释：为什么是这样\n- 举例说明：实际案例辅助理解\n- 注意事项：相关的补充提醒\n- 总结建议：实用的行动建议\n\n请直接输出回答内容。', '适用于知乎、百度知道等问答平台的内容创作', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"回答字数\", \"default\": 600, \"required\": false}, {\"name\": \"style\", \"type\": \"select\", \"label\": \"回答风格\", \"default\": \"通俗易懂\", \"options\": [\"通俗易懂\", \"专业严谨\", \"幽默风趣\", \"简洁明了\"], \"required\": false}, {\"name\": \"expertise_level\", \"type\": \"select\", \"label\": \"专业程度\", \"default\": \"中等\", \"options\": [\"基础科普\", \"中等专业\", \"高度专业\"], \"required\": false}]', 1, 11, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (12, 1, '故事创作模板', '故事创作', '请根据以下主题创作一个故事：\n\n【主题】{topic}\n\n【故事要求】\n- 字数：{length}字左右\n- 故事类型：{story_type}\n- 叙事视角：{perspective}\n- 情节节奏：{pacing}\n\n【创作要求】\n- 开头引人入胜\n- 人物形象鲜明\n- 情节合理有冲突\n- 细节描写生动\n- 结尾有回味\n\n请直接输出故事内容。', '适用于创意写作、小说片段、营销故事等', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"故事字数\", \"default\": 1200, \"required\": false}, {\"name\": \"story_type\", \"type\": \"select\", \"label\": \"故事类型\", \"default\": \"现实主义\", \"options\": [\"现实主义\", \"悬疑推理\", \"奇幻冒险\", \"爱情故事\", \"励志成长\", \"科幻未来\"], \"required\": false}, {\"name\": \"perspective\", \"type\": \"select\", \"label\": \"叙事视角\", \"default\": \"第三人称\", \"options\": [\"第一人称\", \"第三人称\", \"全知视角\"], \"required\": false}, {\"name\": \"pacing\", \"type\": \"select\", \"label\": \"情节节奏\", \"default\": \"适中\", \"options\": [\"快节奏\", \"适中\", \"慢节奏\"], \"required\": false}]', 1, 12, 1, 9, NULL, '2025-11-25 14:47:03');
INSERT INTO `ai_prompt_templates` VALUES (13, 1, '演讲稿模板', '演讲稿', '请撰写一篇演讲稿：\n\n【主题】{topic}\n\n【演讲信息】\n- 字数：{length}字左右（约{speech_duration}分钟）\n- 听众对象：{audience}\n- 演讲场合：{occasion}\n- 演讲风格：{style}\n\n【演讲结构】\n- 开场白：吸引听众注意\n- 主题引入：点明演讲主题\n- 核心内容：分点阐述（2-3个要点）\n- 案例故事：增强说服力\n- 号召行动：激发听众行动\n- 结束语：升华主题\n\n【表达要求】\n- 语言口语化，适合朗读\n- 使用排比、反问等修辞\n- 情感充沛，富有感染力\n- 结构清晰，逻辑严密\n\n请直接输出演讲稿内容。', '适用于各类演讲、发言、讲话稿撰写', '[{\"name\": \"length\", \"type\": \"number\", \"label\": \"演讲字数\", \"default\": 800, \"required\": false}, {\"name\": \"speech_duration\", \"type\": \"select\", \"label\": \"演讲时长\", \"default\": \"5\", \"options\": [\"3\", \"5\", \"10\", \"15\", \"20\"], \"required\": false}, {\"name\": \"audience\", \"type\": \"text\", \"label\": \"听众对象\", \"default\": \"公司员工\", \"required\": false, \"placeholder\": \"如：公司员工、学生、行业专家\"}, {\"name\": \"occasion\", \"type\": \"text\", \"label\": \"演讲场合\", \"default\": \"内部会议\", \"required\": false, \"placeholder\": \"如：年会、培训、论坛\"}, {\"name\": \"style\", \"type\": \"select\", \"label\": \"演讲风格\", \"default\": \"激励鼓舞\", \"options\": [\"激励鼓舞\", \"专业严谨\", \"轻松幽默\", \"深刻感人\"], \"required\": false}]', 1, 13, 1, 9, NULL, '2025-11-25 14:47:03');

-- ----------------------------
-- Table structure for ai_providers
-- ----------------------------
DROP TABLE IF EXISTS `ai_providers`;
CREATE TABLE `ai_providers`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '厂商代码',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '厂商名称',
  `name_en` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '厂商英文名称',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '厂商描述',
  `logo_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Logo URL',
  `website` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '官网地址',
  `api_doc_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'API文档地址',
  `is_custom` tinyint(1) NULL DEFAULT 0 COMMENT '是否自定义厂商',
  `is_builtin` tinyint(1) NULL DEFAULT 0 COMMENT '是否内置',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态',
  `sort_order` int NULL DEFAULT 0 COMMENT '排序',
  `config_fields` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '配置字段定义JSON',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_sort_order`(`sort_order` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'AI厂商表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ai_providers
-- ----------------------------
INSERT INTO `ai_providers` VALUES (1, 'openai', 'OpenAI (GPT-3.5/4)', 'OpenAI', 'OpenAI的GPT系列模型，包括GPT-3.5和GPT-4', NULL, NULL, NULL, 0, 1, 1, 1, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (2, 'claude', 'Claude (Anthropic)', 'Claude', 'Anthropic的Claude系列模型', NULL, NULL, NULL, 0, 1, 1, 2, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (3, 'gemini', 'Google Gemini', 'Google Gemini', 'Google的Gemini系列模型', NULL, NULL, NULL, 0, 1, 1, 3, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (4, 'wenxin', '百度文心一言', 'Baidu Wenxin', '百度的文心一言大模型', NULL, NULL, NULL, 0, 1, 1, 4, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (5, 'tongyi', '阿里通义千问', 'Alibaba Tongyi', '阿里巴巴的通义千问大模型', NULL, NULL, NULL, 0, 1, 1, 5, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (6, 'chatglm', '智谱ChatGLM', 'Zhipu ChatGLM', '智谱AI的ChatGLM系列模型', NULL, NULL, NULL, 0, 1, 1, 6, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (7, 'deepseek', 'DeepSeek', 'DeepSeek', 'DeepSeek系列模型', NULL, NULL, NULL, 0, 1, 1, 7, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (8, 'kimi', '月之暗面 Kimi', 'Moonshot Kimi', '月之暗面的Kimi模型', NULL, NULL, NULL, 0, 1, 1, 8, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (9, 'doubao', '字节跳动 豆包', 'ByteDance Doubao', '字节跳动的豆包模型', NULL, NULL, NULL, 0, 1, 1, 9, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (10, 'spark', '讯飞星火', 'iFlytek Spark', '科大讯飞的星火认知大模型', NULL, NULL, NULL, 0, 1, 1, 10, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (11, 'hunyuan', '腾讯混元', 'Tencent Hunyuan', '腾讯的混元大模型', NULL, NULL, NULL, 0, 1, 1, 11, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (12, 'minimax', 'MiniMax', 'MiniMax', 'MiniMax的ABAB系列模型', NULL, NULL, NULL, 0, 1, 1, 12, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (13, 'custom', '自定义(兼容OpenAI API)', 'Custom (OpenAI Compatible)', '自定义API服务，兼容OpenAI接口格式', NULL, NULL, NULL, 0, 1, 1, 99, NULL, '2025-11-09 20:55:03', '2025-11-09 20:55:03');
INSERT INTO `ai_providers` VALUES (14, 'carefreeai', '逍遥免费AI', 'Carefree', '逍遥免费AI', NULL, 'https://www.carefreecode.com', 'https://www.carefreecode.com', 0, 0, 1, 0, NULL, NULL, NULL);
INSERT INTO `ai_providers` VALUES (15, 'stability', 'Stability AI', NULL, 'Stable Diffusion图像生成', NULL, NULL, NULL, 0, 1, 1, 0, NULL, NULL, NULL);
INSERT INTO `ai_providers` VALUES (16, 'meta', 'Meta (Llama)', NULL, 'Meta开源大语言模型', NULL, NULL, NULL, 0, 1, 1, 0, NULL, NULL, NULL);
INSERT INTO `ai_providers` VALUES (17, 'mistral', 'Mistral AI', NULL, '法国开源AI公司', NULL, NULL, NULL, 0, 1, 1, 0, NULL, NULL, NULL);
INSERT INTO `ai_providers` VALUES (18, 'xai', 'xAI (Grok)', NULL, 'Elon Musk的AI公司', NULL, NULL, NULL, 0, 1, 1, 0, NULL, NULL, NULL);
INSERT INTO `ai_providers` VALUES (19, 'cohere', 'Cohere', NULL, '企业级AI模型提供商', NULL, NULL, NULL, 0, 1, 1, 0, NULL, NULL, NULL);

-- ----------------------------
-- Table structure for article_flags
-- ----------------------------
DROP TABLE IF EXISTS `article_flags`;
CREATE TABLE `article_flags`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '属性名称',
  `flag_value` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '属性值',
  `is_show` tinyint(1) NULL DEFAULT 1 COMMENT '是否显示 1-是 0-否',
  `sort_order` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态 1-启用 0-禁用',
  `create_time` datetime NULL DEFAULT NULL,
  `update_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `flag_value`(`flag_value` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '文章属性表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of article_flags
-- ----------------------------
INSERT INTO `article_flags` VALUES (1, '头条', 'h', 1, 1, 1, '2025-10-14 20:57:52', '2025-10-14 20:57:52');
INSERT INTO `article_flags` VALUES (2, '推荐', 'c', 1, 2, 1, '2025-10-14 20:57:52', '2025-10-14 20:57:52');
INSERT INTO `article_flags` VALUES (3, '加推', 'a', 0, 3, 1, '2025-10-14 20:57:52', '2025-10-14 20:57:52');
INSERT INTO `article_flags` VALUES (4, '标题', 'b', 0, 4, 1, '2025-10-14 20:57:52', '2025-10-14 20:57:52');
INSERT INTO `article_flags` VALUES (5, '有图', 'p', 1, 5, 1, '2025-10-14 20:57:52', '2025-10-14 20:57:52');
INSERT INTO `article_flags` VALUES (6, '外链', 'j', 1, 6, 1, '2025-10-14 20:57:52', '2025-10-14 20:57:52');
INSERT INTO `article_flags` VALUES (7, '轮播', 's', 0, 7, 1, '2025-10-14 20:57:52', '2025-10-14 20:57:52');
INSERT INTO `article_flags` VALUES (8, '滚动', 'r', 0, 8, 1, '2025-10-14 20:57:52', '2025-10-14 20:57:52');
INSERT INTO `article_flags` VALUES (9, '热文', 'd', 0, 9, 1, '2025-10-14 20:57:52', '2025-10-14 20:57:52');

-- ----------------------------
-- Table structure for article_versions
-- ----------------------------
DROP TABLE IF EXISTS `article_versions`;
CREATE TABLE `article_versions`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '版本ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `article_id` int UNSIGNED NOT NULL COMMENT '文章ID',
  `version_number` int UNSIGNED NOT NULL COMMENT '版本号',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '文章标题',
  `slug` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'URL别名',
  `summary` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '文章摘要',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '文章内容',
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '封面图',
  `images` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '文章图片集',
  `category_id` int UNSIGNED NOT NULL COMMENT '分类ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '作者ID',
  `tags` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '标签JSON',
  `author` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '作者名称',
  `source` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '文章来源',
  `source_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '来源URL',
  `view_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览量',
  `like_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数',
  `comment_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论数',
  `is_top` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否置顶',
  `is_recommend` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否推荐',
  `is_hot` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否热门',
  `publish_time` datetime NULL DEFAULT NULL COMMENT '发布时间',
  `seo_title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'SEO关键词',
  `seo_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'SEO描述',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态:0-草稿,1-已发布,2-待审核',
  `flags` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '标记',
  `change_log` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '修改说明',
  `created_by` int UNSIGNED NULL DEFAULT NULL COMMENT '创建版本的用户ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_article_id`(`article_id` ASC) USING BTREE,
  INDEX `idx_version_number`(`version_number` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '文章版本表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for articles
-- ----------------------------
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文章ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `source_id` int UNSIGNED NULL DEFAULT NULL COMMENT '源记录ID（用于标识复制关系）',
  `category_id` int UNSIGNED NOT NULL COMMENT '分类ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '作者ID',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章标题',
  `slug` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'URL别名',
  `summary` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '文章摘要',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文章内容',
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '封面图片',
  `images` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '文章图片集（JSON格式）',
  `author` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '作者名称（显示用）',
  `source` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '文章来源',
  `source_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '来源链接',
  `view_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `like_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数',
  `comment_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论数',
  `is_top` tinyint NOT NULL DEFAULT 0 COMMENT '是否置顶：0=否，1=是',
  `is_recommend` tinyint NOT NULL DEFAULT 0 COMMENT '是否推荐：0=否，1=是',
  `is_hot` tinyint NOT NULL DEFAULT 0 COMMENT '是否热门：0=否，1=是',
  `publish_time` datetime NULL DEFAULT NULL COMMENT '发布时间',
  `seo_title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SEO关键词',
  `seo_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SEO描述',
  `og_title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Open Graph标题',
  `og_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Open Graph描述',
  `og_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Open Graph图片',
  `twitter_card` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'summary' COMMENT 'Twitter卡片类型：summary, summary_large_image, app, player',
  `canonical_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '规范链接',
  `schema_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'Article' COMMENT 'Schema.org类型',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0=草稿，1=已发布，2=待审核，3=已下线',
  `flags` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '文章属性（多个属性值组合，如hcp）',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL,
  `is_contribute` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否投稿',
  `audit_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '审核状态（0待审核/1已通过/2已拒绝）',
  `audit_user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '审核人ID',
  `audit_time` datetime NULL DEFAULT NULL COMMENT '审核时间',
  `audit_remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '审核备注',
  `reward_points` int NOT NULL DEFAULT 0 COMMENT '奖励积分',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_site_slug`(`site_id` ASC, `slug` ASC) USING BTREE,
  INDEX `idx_category_id`(`category_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_status_publish`(`status` ASC, `publish_time` ASC) USING BTREE,
  INDEX `idx_is_top`(`is_top` ASC) USING BTREE,
  INDEX `idx_is_recommend`(`is_recommend` ASC) USING BTREE,
  INDEX `idx_is_hot`(`is_hot` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_source_id`(`source_id` ASC) USING BTREE,
  FULLTEXT INDEX `ft_title_content`(`title`, `content`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '文章表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `source_id` int UNSIGNED NULL DEFAULT NULL COMMENT '源记录ID（用于标识复制关系）',
  `parent_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父分类ID，0表示顶级分类',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分类名称',
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'URL别名',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '分类描述',
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '封面图片',
  `template` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'category' COMMENT '分类模板名称',
  `seo_title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SEO关键词',
  `seo_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SEO描述',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间（软删除）',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_site_slug`(`site_id` ASC, `slug` ASC) USING BTREE,
  INDEX `idx_parent_id`(`parent_id` ASC) USING BTREE,
  INDEX `idx_status_sort`(`status` ASC, `sort` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_source_id`(`source_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '分类表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for chunked_upload_sessions
-- ----------------------------
DROP TABLE IF EXISTS `chunked_upload_sessions`;
CREATE TABLE `chunked_upload_sessions`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `site_id` int UNSIGNED NULL DEFAULT NULL COMMENT '站点ID',
  `upload_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '上传标识（唯一）',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '文件名',
  `file_size` bigint NOT NULL COMMENT '文件总大小（字节）',
  `file_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '文件哈希（完成后计算）',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'MIME类型',
  `chunk_size` int NOT NULL DEFAULT 2097152 COMMENT '分片大小（字节，默认2MB）',
  `total_chunks` int NOT NULL COMMENT '总分片数',
  `uploaded_chunks` int NOT NULL DEFAULT 0 COMMENT '已上传分片数',
  `temp_dir` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '临时目录路径',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'uploading' COMMENT '状态：uploading/merging/completed/failed',
  `media_id` int UNSIGNED NULL DEFAULT NULL COMMENT '完成后的媒体ID',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '错误信息',
  `expires_at` datetime NOT NULL COMMENT '过期时间',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `completed_at` datetime NULL DEFAULT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_upload_id`(`upload_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_expires_at`(`expires_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '分片上传会话表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for comment_emojis
-- ----------------------------
DROP TABLE IF EXISTS `comment_emojis`;
CREATE TABLE `comment_emojis`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '表情名称',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '表情代码(如 :smile:)',
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '表情图片URL',
  `unicode` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'Unicode字符(如 ?)',
  `category` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'default' COMMENT '分类(default-默认 custom-自定义)',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用(0-否 1-是)',
  `use_count` int NULL DEFAULT 0 COMMENT '使用次数',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_code`(`code` ASC) USING BTREE,
  INDEX `idx_category`(`category` ASC) USING BTREE,
  INDEX `idx_enabled`(`is_enabled` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 87 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '评论表情表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of comment_emojis
-- ----------------------------
INSERT INTO `comment_emojis` VALUES (1, '微笑', ':smile:', NULL, '😀', '表情', 1, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (2, '大笑', ':laugh:', NULL, '😂', '表情', 2, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (3, '眨眼', ':wink:', NULL, '😉', '表情', 3, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (4, '喜欢', ':heart_eyes:', NULL, '😍', '表情', 4, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (5, '亲吻', ':kiss:', NULL, '😘', '表情', 5, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (6, '思考', ':thinking:', NULL, '🤔', '表情', 6, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (7, '无语', ':neutral:', NULL, '😐', '表情', 7, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (8, '流汗', ':sweat:', NULL, '😅', '表情', 8, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (9, '尴尬', ':flushed:', NULL, '😳', '表情', 9, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (10, '哭泣', ':cry:', NULL, '😢', '表情', 10, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (11, '大哭', ':sob:', NULL, '😭', '表情', 11, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (12, '生气', ':angry:', NULL, '😠', '表情', 12, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (13, '惊讶', ':surprised:', NULL, '😮', '表情', 13, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (14, '害怕', ':fearful:', NULL, '😨', '表情', 14, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (15, '困倦', ':sleepy:', NULL, '😴', '表情', 15, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (16, '点赞', ':thumbsup:', NULL, '👍', '手势', 20, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (17, '点踩', ':thumbsdown:', NULL, '👎', '手势', 21, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (18, '好的', ':ok:', NULL, '👌', '手势', 22, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (19, '胜利', ':victory:', NULL, '✌️', '手势', 23, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (20, '鼓掌', ':clap:', NULL, '👏', '手势', 24, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (21, '祈祷', ':pray:', NULL, '🙏', '手势', 25, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (22, '强壮', ':muscle:', NULL, '💪', '手势', 26, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (23, '握手', ':handshake:', NULL, '🤝', '手势', 27, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (24, '红心', ':heart:', NULL, '❤️', '心形', 30, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (25, '心碎', ':broken_heart:', NULL, '💔', '心形', 31, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (26, '橙心', ':orange_heart:', NULL, '🧡', '心形', 32, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (27, '黄心', ':yellow_heart:', NULL, '💛', '心形', 33, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (28, '绿心', ':green_heart:', NULL, '💚', '心形', 34, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (29, '蓝心', ':blue_heart:', NULL, '💙', '心形', 35, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (30, '紫心', ':purple_heart:', NULL, '💜', '心形', 36, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (31, '黑心', ':black_heart:', NULL, '🖤', '心形', 37, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (32, '火焰', ':fire:', NULL, '🔥', '心形', 38, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (33, '星星', ':star:', NULL, '⭐', '心形', 39, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (34, '对勾', ':check:', NULL, '✅', '符号', 40, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (35, '叉号', ':x:', NULL, '❌', '符号', 41, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (36, '感叹', ':exclamation:', NULL, '❗', '符号', 42, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (37, '疑问', ':question:', NULL, '❓', '符号', 43, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (38, '灯泡', ':bulb:', NULL, '💡', '符号', 44, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (39, '礼物', ':gift:', NULL, '🎁', '符号', 45, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (40, '蛋糕', ':cake:', NULL, '🎂', '符号', 46, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (41, '派对', ':party:', NULL, '🎉', '符号', 47, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (42, '音乐', ':music:', NULL, '🎵', '符号', 48, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (43, '警告', ':warning:', NULL, '⚠️', '符号', 49, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (44, '狗', ':dog:', NULL, '🐶', '动物', 50, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (45, '猫', ':cat:', NULL, '🐱', '动物', 51, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (46, '老虎', ':tiger:', NULL, '🐯', '动物', 52, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (47, '兔子', ':rabbit:', NULL, '🐰', '动物', 53, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (48, '熊猫', ':panda:', NULL, '🐼', '动物', 54, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (49, '猴子', ':monkey:', NULL, '🐵', '动物', 55, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (50, '鸡', ':chicken:', NULL, '🐔', '动物', 56, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (51, '企鹅', ':penguin:', NULL, '🐧', '动物', 57, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (52, '苹果', ':apple:', NULL, '🍎', '食物', 60, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (53, '香蕉', ':banana:', NULL, '🍌', '食物', 61, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (54, '西瓜', ':watermelon:', NULL, '🍉', '食物', 62, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (55, '汉堡', ':hamburger:', NULL, '🍔', '食物', 63, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (56, '披萨', ':pizza:', NULL, '🍕', '食物', 64, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (57, '咖啡', ':coffee:', NULL, '☕', '食物', 65, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (58, '啤酒', ':beer:', NULL, '🍺', '食物', 66, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (59, '蛋糕', ':birthday:', NULL, '🎂', '食物', 67, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (60, '足球', ':soccer:', NULL, '⚽', '运动', 70, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (61, '篮球', ':basketball:', NULL, '🏀', '运动', 71, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (62, '网球', ':tennis:', NULL, '🎾', '运动', 72, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (63, '奖杯', ':trophy:', NULL, '🏆', '运动', 73, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (64, '奖牌', ':medal:', NULL, '🏅', '运动', 74, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (65, '太阳', ':sunny:', NULL, '☀️', '天气', 80, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (66, '月亮', ':moon:', NULL, '🌙', '天气', 81, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (67, '星星', ':stars:', NULL, '✨', '天气', 82, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (68, '云朵', ':cloud:', NULL, '☁️', '天气', 83, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (69, '雨', ':rain:', NULL, '🌧️', '天气', 84, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (70, '雪', ':snow:', NULL, '❄️', '天气', 85, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (71, '闪电', ':lightning:', NULL, '⚡', '天气', 86, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (72, '彩虹', ':rainbow:', NULL, '🌈', '天气', 87, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (73, '飞机', ':airplane:', NULL, '✈️', '旅行', 90, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (74, '火车', ':train:', NULL, '🚄', '旅行', 91, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (75, '汽车', ':car:', NULL, '🚗', '旅行', 92, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (76, '自行车', ':bike:', NULL, '🚲', '旅行', 93, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (77, '房子', ':house:', NULL, '🏠', '旅行', 94, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (78, '城市', ':city:', NULL, '🏙️', '旅行', 95, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (79, '电脑', ':computer:', NULL, '💻', '物品', 100, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (80, '手机', ':phone:', NULL, '📱', '物品', 101, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (81, '相机', ':camera:', NULL, '📷', '物品', 102, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (82, '书籍', ':book:', NULL, '📚', '物品', 103, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (83, '信封', ':email:', NULL, '✉️', '物品', 104, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (84, '铅笔', ':pencil:', NULL, '✏️', '物品', 105, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (85, '剪刀', ':scissors:', NULL, '✂️', '物品', 106, 1, 0, NULL, NULL);
INSERT INTO `comment_emojis` VALUES (86, '钥匙', ':key:', NULL, '🔑', '物品', 107, 1, 0, NULL, NULL);

-- ----------------------------
-- Table structure for comment_likes
-- ----------------------------
DROP TABLE IF EXISTS `comment_likes`;
CREATE TABLE `comment_likes`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `comment_id` int UNSIGNED NOT NULL COMMENT '评论ID',
  `user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '用户ID（注册用户）',
  `user_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '用户IP（游客）',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '类型(1-点赞 2-点踩)',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_comment_user`(`comment_id` ASC, `user_id` ASC) USING BTREE,
  UNIQUE INDEX `uk_comment_ip`(`comment_id` ASC, `user_ip` ASC) USING BTREE,
  INDEX `idx_comment_id`(`comment_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_user_ip`(`user_ip` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '评论点赞/点踩表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of comment_likes
-- ----------------------------

-- ----------------------------
-- Table structure for comment_reports
-- ----------------------------
DROP TABLE IF EXISTS `comment_reports`;
CREATE TABLE `comment_reports`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `comment_id` int UNSIGNED NOT NULL COMMENT '评论ID',
  `reporter_id` int UNSIGNED NULL DEFAULT NULL COMMENT '举报人ID（注册用户）',
  `reporter_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '举报人IP（游客）',
  `reporter_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '举报人邮箱（游客）',
  `reason` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'spam' COMMENT '举报原因(spam-垃圾 abuse-辱骂 porn-色情 ad-广告 other-其他)',
  `reason_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '详细说明',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '处理状态(0-待处理 1-已处理 2-已忽略)',
  `handle_result` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '处理结果(deleted-已删除 approved-误报)',
  `handler_id` int UNSIGNED NULL DEFAULT NULL COMMENT '处理人ID',
  `handle_time` datetime NULL DEFAULT NULL COMMENT '处理时间',
  `handle_remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '处理备注',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_comment_id`(`comment_id` ASC) USING BTREE,
  INDEX `idx_reporter_id`(`reporter_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '评论举报表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of comment_reports
-- ----------------------------

-- ----------------------------
-- Table structure for comments
-- ----------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `article_id` int UNSIGNED NOT NULL COMMENT '文章ID',
  `user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '前台用户ID（注册用户）',
  `is_guest` tinyint NOT NULL DEFAULT 0 COMMENT '是否游客评论：0=注册用户，1=游客',
  `parent_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父评论ID，0表示顶级评论',
  `user_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '评论者名称（游客）',
  `user_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '评论者邮箱（游客）',
  `user_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '评论者IP',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '评论内容',
  `like_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数',
  `dislike_count` int NULL DEFAULT 0 COMMENT '点踩数',
  `report_count` int NULL DEFAULT 0 COMMENT '被举报次数',
  `is_admin` tinyint NOT NULL DEFAULT 0 COMMENT '是否管理员：0=否，1=是',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0=待审核，1=已通过，2=已拒绝',
  `is_hot` tinyint(1) NULL DEFAULT 0 COMMENT '是否热门评论',
  `hot_score` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '热度分数',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_article_id`(`article_id` ASC) USING BTREE,
  INDEX `idx_parent_id`(`parent_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '评论表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of comments
-- ----------------------------
INSERT INTO `comments` VALUES (1, 1, 1, NULL, 1, 0, '游客张三', 'guest@example.com', '127.0.0.1', '这是编辑后的评论内容', 0, 0, 0, 0, 1, 0, 0.00, '2025-10-28 11:43:42', '2025-10-28 11:55:41');
INSERT INTO `comments` VALUES (2, 1, 1, 2, 0, 0, NULL, NULL, '127.0.0.1', '这是一条注册用户的评论，测试用户评论功能！', 0, 0, 0, 0, 2, 0, 0.00, '2025-10-28 11:47:14', '2025-10-28 11:54:15');
INSERT INTO `comments` VALUES (3, 1, 1, NULL, 1, 0, '测试用户3', 'test3@test.com', '127.0.0.1', '这是第三条测试评论', 0, 0, 0, 0, 1, 0, 0.00, '2025-10-28 11:54:24', '2025-10-28 11:54:34');
INSERT INTO `comments` VALUES (4, 1, 1, NULL, 1, 0, '测试用户4', 'test4@test.com', '127.0.0.1', '这是第四条测试评论', 0, 0, 0, 0, 1, 0, 0.00, '2025-10-28 11:54:24', '2025-10-28 11:54:34');
INSERT INTO `comments` VALUES (5, 1, 1, NULL, 0, 1, NULL, NULL, '127.0.0.1', '感谢您的评论！这是管理员的回复。', 0, 0, 0, 1, 1, 0, 0.00, '2025-10-28 11:54:44', '2025-10-28 11:54:44');

-- ----------------------------
-- Table structure for content_models
-- ----------------------------
DROP TABLE IF EXISTS `content_models`;
CREATE TABLE `content_models`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '模型ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模型名称',
  `table_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '数据表名',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '图标',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '模型描述',
  `template` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '默认模板',
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否系统模型：0=自定义，1=系统预设',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_table_name`(`table_name` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容模型表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of content_models
-- ----------------------------
INSERT INTO `content_models` VALUES (1, 1, '文章', 'articles', 'Document', '系统内置文章模型', NULL, 1, 1, 100, '2025-10-18 22:36:49', '2025-10-18 22:36:49');
INSERT INTO `content_models` VALUES (2, 1, '分类', 'categories', 'FolderOpened', '系统内置分类模型', NULL, 1, 1, 90, '2025-10-18 22:36:49', '2025-10-18 22:36:49');
INSERT INTO `content_models` VALUES (3, 1, '标签', 'tags', 'CollectionTag', '系统内置标签模型', NULL, 1, 1, 80, '2025-10-18 22:36:49', '2025-10-18 22:36:49');
INSERT INTO `content_models` VALUES (4, 1, '单页', 'pages', 'Files', '系统内置单页模型', NULL, 1, 1, 70, '2025-10-18 22:36:49', '2025-10-18 22:36:49');

-- ----------------------------
-- Table structure for content_violations
-- ----------------------------
DROP TABLE IF EXISTS `content_violations`;
CREATE TABLE `content_violations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `content_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '内容类型(article-文章 comment-评论 page-单页)',
  `content_id` int UNSIGNED NOT NULL COMMENT '内容ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '发布用户ID',
  `matched_words` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '匹配到的敏感词(JSON格式)',
  `original_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '原始内容片段',
  `filtered_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '过滤后内容',
  `action` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '处理动作(warn-警告 replace-替换 reject-拒绝)',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'pending' COMMENT '状态(pending-待处理 reviewed-已审核 ignored-已忽略)',
  `reviewed_by` int UNSIGNED NULL DEFAULT NULL COMMENT '审核人ID',
  `reviewed_at` timestamp NULL DEFAULT NULL COMMENT '审核时间',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_content`(`content_type` ASC, `content_id` ASC) USING BTREE,
  INDEX `idx_user`(`user_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_created`(`created_at` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '违规内容记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of content_violations
-- ----------------------------

-- ----------------------------
-- Table structure for contribute_config
-- ----------------------------
DROP TABLE IF EXISTS `contribute_config`;
CREATE TABLE `contribute_config`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category_id` int UNSIGNED NOT NULL COMMENT '分类ID',
  `allow_contribute` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否允许投稿',
  `need_audit` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否需要审核',
  `reward_points` int NOT NULL DEFAULT 0 COMMENT '投稿通过奖励积分',
  `min_words` int NOT NULL DEFAULT 0 COMMENT '最少字数要求',
  `max_per_day` int NOT NULL DEFAULT 0 COMMENT '每日最多投稿数（0不限）',
  `level_required` tinyint NOT NULL DEFAULT 0 COMMENT '所需等级',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `category_id`(`category_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '投稿配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of contribute_config
-- ----------------------------
INSERT INTO `contribute_config` VALUES (1, 5, 1, 1, 10, 200, 0, 0, '2025-11-04 16:16:13', '2025-11-04 16:16:13');
INSERT INTO `contribute_config` VALUES (2, 1, 1, 1, 10, 200, 0, 0, '2025-11-04 16:16:29', '2025-11-04 16:16:29');
INSERT INTO `contribute_config` VALUES (3, 2, 1, 1, 10, 200, 0, 0, '2025-11-04 16:16:39', '2025-11-04 16:16:39');
INSERT INTO `contribute_config` VALUES (4, 3, 1, 1, 10, 200, 0, 0, '2025-11-04 16:16:50', '2025-11-04 16:16:50');

-- ----------------------------
-- Table structure for cron_job_logs
-- ----------------------------
DROP TABLE IF EXISTS `cron_job_logs`;
CREATE TABLE `cron_job_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `job_id` int NOT NULL COMMENT '任务ID',
  `job_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '任务名称',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '状态:success-成功,failed-失败,running-运行中',
  `start_time` datetime NOT NULL COMMENT '开始时间',
  `end_time` datetime NULL DEFAULT NULL COMMENT '结束时间',
  `duration` int NULL DEFAULT NULL COMMENT '运行时长(秒)',
  `output` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '输出信息',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '错误信息',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_job_id`(`job_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_start_time`(`start_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '定时任务日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cron_job_logs
-- ----------------------------
INSERT INTO `cron_job_logs` VALUES (1, 4, 'test_job', 'running', '2025-11-08 13:12:15', NULL, NULL, NULL, NULL, '2025-11-08 13:12:16');

-- ----------------------------
-- Table structure for cron_jobs
-- ----------------------------
DROP TABLE IF EXISTS `cron_jobs`;
CREATE TABLE `cron_jobs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '任务名称',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '任务标题',
  `cron_expression` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Cron表达式',
  `command` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '执行命令',
  `params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '命令参数(JSON)',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否系统任务',
  `run_count` int NOT NULL DEFAULT 0 COMMENT '运行次数',
  `last_run_time` datetime NULL DEFAULT NULL COMMENT '最后运行时间',
  `last_run_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '最后运行状态',
  `last_run_duration` int NULL DEFAULT NULL COMMENT '最后运行时长(秒)',
  `next_run_time` datetime NULL DEFAULT NULL COMMENT '下次运行时间',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '任务描述',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_name`(`name` ASC) USING BTREE,
  INDEX `idx_is_enabled`(`is_enabled` ASC) USING BTREE,
  INDEX `idx_next_run_time`(`next_run_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '定时任务' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cron_jobs
-- ----------------------------
INSERT INTO `cron_jobs` VALUES (1, 'database_backup', '数据库自动备份', '0 2 * * *', 'database:backup', '[]', 1, 1, 0, NULL, NULL, NULL, '2025-11-09 02:00:00', '每天凌晨2点自动备份数据库', '2025-11-08 13:09:50', '2025-11-08 13:11:36');
INSERT INTO `cron_jobs` VALUES (2, 'cache_clear', '定时清理缓存', '0 3 * * 0', 'cache:clear', '{\"type\":\"all\"}', 0, 1, 0, NULL, NULL, NULL, NULL, '每周日凌晨3点清理所有缓存', '2025-11-08 13:09:50', '2025-11-08 13:09:50');
INSERT INTO `cron_jobs` VALUES (3, 'log_clean', '清理旧日志', '0 4 * * 0', 'log:clean', '{\"days\":30}', 0, 1, 0, NULL, NULL, NULL, NULL, '每周日凌晨4点清理30天前的日志', '2025-11-08 13:09:50', '2025-11-08 13:09:50');
INSERT INTO `cron_jobs` VALUES (4, 'test_job', '测试任务', '*/5 * * * *', 'log:clean', '{\"days\":7}', 1, 0, 1, '2025-11-08 13:12:15', 'success', 0, '2025-11-08 13:15:00', '每5分钟清理7天前的日志', '2025-11-08 13:11:58', '2025-11-08 13:12:16');

-- ----------------------------
-- Table structure for custom_field_values
-- ----------------------------
DROP TABLE IF EXISTS `custom_field_values`;
CREATE TABLE `custom_field_values`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '值ID',
  `field_id` int UNSIGNED NOT NULL COMMENT '字段ID',
  `entity_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '实体类型：article,category,tag,page,custom',
  `entity_id` int UNSIGNED NOT NULL COMMENT '实体ID',
  `field_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '字段值',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_field_entity`(`field_id` ASC, `entity_type` ASC, `entity_id` ASC) USING BTREE,
  INDEX `idx_entity`(`entity_type` ASC, `entity_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '自定义字段值存储表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of custom_field_values
-- ----------------------------
INSERT INTO `custom_field_values` VALUES (1, 1, 'article', 18, '111', '2025-11-13 05:25:28', '2025-11-13 05:25:28');
INSERT INTO `custom_field_values` VALUES (2, 2, 'article', 18, '222', '2025-11-13 05:25:28', '2025-11-13 05:25:28');
INSERT INTO `custom_field_values` VALUES (3, 1, 'article', 15, '测试', '2025-11-28 11:17:22', '2025-11-28 11:17:22');
INSERT INTO `custom_field_values` VALUES (4, 2, 'article', 15, '测试一下', '2025-11-28 11:17:22', '2025-11-28 11:17:22');

-- ----------------------------
-- Table structure for custom_fields
-- ----------------------------
DROP TABLE IF EXISTS `custom_fields`;
CREATE TABLE `custom_fields`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '字段ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字段名称',
  `field_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字段键名（英文）',
  `field_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字段类型：text,number,date,datetime,select,radio,checkbox,textarea,richtext,image,file',
  `model_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关联模型：article,category,tag,page,custom',
  `model_id` int UNSIGNED NULL DEFAULT NULL COMMENT '内容模型ID（model_type为custom时有效）',
  `group_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '字段组名',
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '字段选项（JSON格式，用于select/radio/checkbox）',
  `default_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '默认值',
  `placeholder` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '占位符文本',
  `help_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '帮助说明',
  `validation_rules` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '验证规则（JSON格式）',
  `is_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否必填：0=否，1=是',
  `is_searchable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否可搜索：0=否，1=是',
  `is_show_in_list` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否在列表显示：0=否，1=是',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_field_key_model`(`field_key` ASC, `model_type` ASC, `model_id` ASC) USING BTREE,
  INDEX `idx_model_type`(`model_type` ASC) USING BTREE,
  INDEX `idx_model_id`(`model_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '自定义字段定义表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of custom_fields
-- ----------------------------
INSERT INTO `custom_fields` VALUES (1, 1, '测试', 'article_test', 'text', 'article', NULL, '测试信息', NULL, '', '请输入测试内容', '这里是帮助说明，你看看就行', NULL, 0, 1, 1, 0, 1, '2025-11-04 14:54:46', '2025-11-04 14:54:46');
INSERT INTO `custom_fields` VALUES (2, 1, '测试', 'article_test1', 'text', 'article', NULL, '测试信息', NULL, '', '请输入测试内容', '这里是帮助说明，你看看就行', NULL, 0, 1, 1, 0, 1, '2025-11-04 14:55:10', '2025-11-04 14:55:10');

-- ----------------------------
-- Table structure for database_backups
-- ----------------------------
DROP TABLE IF EXISTS `database_backups`;
CREATE TABLE `database_backups`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '备份文件名',
  `filepath` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '文件路径',
  `filesize` bigint NOT NULL DEFAULT 0 COMMENT '文件大小(字节)',
  `tables_count` int NOT NULL DEFAULT 0 COMMENT '表数量',
  `backup_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'full' COMMENT '备份类型:full-完整,tables-指定表',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '备份描述',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态:1-成功,0-失败',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '错误信息',
  `create_time` datetime NULL DEFAULT NULL COMMENT '备份时间',
  `create_user_id` int NULL DEFAULT NULL COMMENT '备份人ID',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '数据库备份记录' ROW_FORMAT = Dynamic;



-- ----------------------------
-- Table structure for email_logs
-- ----------------------------
DROP TABLE IF EXISTS `email_logs`;
CREATE TABLE `email_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `to_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '收件人邮箱',
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '邮件主题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '邮件内容',
  `template_id` int NULL DEFAULT NULL COMMENT '模板ID',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '状态:pending-待发送,sent-已发送,failed-失败',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '错误信息',
  `send_time` datetime NULL DEFAULT NULL COMMENT '发送时间',
  `retry_count` int NOT NULL DEFAULT 0 COMMENT '重试次数',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_send_time`(`send_time` ASC) USING BTREE,
  INDEX `idx_template_id`(`template_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '邮件发送日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of email_logs
-- ----------------------------

-- ----------------------------
-- Table structure for email_templates
-- ----------------------------
DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE `email_templates`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '模板标识',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '模板标题',
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '邮件主题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '邮件内容',
  `variables` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '可用变量说明',
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否系统模板',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '模板描述',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_name`(`name` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '邮件模板' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of email_templates
-- ----------------------------

-- ----------------------------
-- Table structure for front_user_oauth
-- ----------------------------
DROP TABLE IF EXISTS `front_user_oauth`;
CREATE TABLE `front_user_oauth`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '平台标识(wechat/qq/weibo/github)',
  `openid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '第三方平台用户唯一标识',
  `unionid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '开放平台统一标识(微信)',
  `nickname` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '第三方平台昵称',
  `avatar` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '第三方平台头像',
  `access_token` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '访问令牌',
  `refresh_token` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '刷新令牌',
  `expires_in` int NULL DEFAULT NULL COMMENT '过期时间(秒)',
  `token_expires_at` datetime NULL DEFAULT NULL COMMENT 'Token过期时间',
  `extra_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '额外数据(JSON格式)',
  `bind_time` datetime NULL DEFAULT NULL COMMENT '绑定时间',
  `last_login_time` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  `login_count` int NOT NULL DEFAULT 0 COMMENT '登录次数',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '绑定状态(0-已解绑 1-已绑定)',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_platform_openid`(`platform` ASC, `openid` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_platform`(`platform` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '用户第三方账号绑定表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of front_user_oauth
-- ----------------------------

-- ----------------------------
-- Table structure for front_users
-- ----------------------------
DROP TABLE IF EXISTS `front_users`;
CREATE TABLE `front_users`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID（用户所属站点）',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码（哈希）',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '昵称',
  `real_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '真实姓名',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邮箱',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '手机号',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '头像',
  `gender` tinyint NULL DEFAULT 0 COMMENT '性别：0=保密，1=男，2=女',
  `birthday` date NULL DEFAULT NULL COMMENT '生日',
  `province` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '省份',
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '城市',
  `signature` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '个性签名',
  `bio` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '个人简介',
  `points` int NOT NULL DEFAULT 0 COMMENT '积分',
  `level` tinyint NOT NULL DEFAULT 1 COMMENT '等级',
  `article_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '发布文章数',
  `comment_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '评论数',
  `favorite_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '收藏数',
  `follower_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '粉丝数',
  `following_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '关注数',
  `email_verified` tinyint NOT NULL DEFAULT 0 COMMENT '邮箱是否已验证：0=未验证，1=已验证',
  `phone_verified` tinyint NOT NULL DEFAULT 0 COMMENT '手机是否已验证：0=未验证，1=已验证',
  `email_verify_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '邮箱验证令牌',
  `email_verify_expire` datetime NULL DEFAULT NULL COMMENT '邮箱验证令牌过期时间',
  `reset_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '密码重置令牌',
  `reset_token_expire` datetime NULL DEFAULT NULL COMMENT '密码重置令牌过期时间',
  `last_login_time` datetime NULL DEFAULT NULL COMMENT '最后登录时间',
  `last_login_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '最后登录IP',
  `login_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '登录次数',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=正常，2=待验证',
  `is_vip` tinyint NOT NULL DEFAULT 0 COMMENT '是否VIP：0=否，1=是',
  `vip_expire_time` datetime NULL DEFAULT NULL COMMENT 'VIP过期时间',
  `wechat_openid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '微信OpenID',
  `qq_openid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'QQ OpenID',
  `weibo_uid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '微博UID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '注册时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '软删除时间',
  `github_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'GitHub ID',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_username`(`username` ASC) USING BTREE,
  UNIQUE INDEX `uk_email`(`email` ASC) USING BTREE,
  UNIQUE INDEX `uk_phone`(`phone` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_level`(`level` ASC) USING BTREE,
  INDEX `idx_points`(`points` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE,
  INDEX `idx_wechat_openid`(`wechat_openid` ASC) USING BTREE,
  INDEX `idx_qq_openid`(`qq_openid` ASC) USING BTREE,
  INDEX `idx_weibo_uid`(`weibo_uid` ASC) USING BTREE,
  INDEX `idx_github_id`(`github_id` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '前台用户表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for groups
-- ----------------------------
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '类型：link, slider, point_shop, ad',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '分组名称',
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '别名',
  `parent_id` int UNSIGNED NULL DEFAULT NULL COMMENT '父级ID',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '描述',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '图片',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `config` json NULL COMMENT '扩展配置（广告位的宽度、高度等）',
  `site_id` int UNSIGNED NULL DEFAULT 1 COMMENT '站点ID',
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_type`(`type` ASC) USING BTREE,
  INDEX `idx_parent`(`parent_id` ASC) USING BTREE,
  INDEX `idx_site`(`site_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '通用分组表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of groups
-- ----------------------------
INSERT INTO `groups` VALUES (1, 'link', '合作伙伴', NULL, NULL, '战略合作伙伴网站', NULL, 1, 1, NULL, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);
INSERT INTO `groups` VALUES (2, 'link', '友情链接', NULL, NULL, '友好互链网站', NULL, 2, 1, NULL, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);
INSERT INTO `groups` VALUES (3, 'link', '推荐网站', NULL, NULL, '优质推荐网站', NULL, 3, 1, NULL, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);
INSERT INTO `groups` VALUES (4, 'slider', '首页轮播', 'home_slider', NULL, '首页顶部轮播图', NULL, 0, 1, '{\"width\": 1920, \"height\": 600, \"animation\": \"slide\", \"auto_play\": 1, \"play_interval\": 5000}', 1, '2025-10-19 01:29:18', '2025-10-19 01:29:18', NULL);
INSERT INTO `groups` VALUES (5, 'slider', '产品展示', 'product_slider', NULL, '产品页轮播展示', NULL, 0, 1, '{\"width\": 800, \"height\": 400, \"animation\": \"fade\", \"auto_play\": 1, \"play_interval\": 3000}', 1, '2025-10-19 01:29:18', '2025-10-19 01:29:18', NULL);
INSERT INTO `groups` VALUES (6, 'slider', '客户案例', 'case_slider', NULL, '客户案例轮播', NULL, 0, 1, '{\"width\": 600, \"height\": 400, \"animation\": \"slide\", \"auto_play\": 0, \"play_interval\": 3000}', 1, '2025-10-19 01:29:18', '2025-10-19 01:29:18', NULL);
INSERT INTO `groups` VALUES (7, 'point_shop', '虚拟商品', NULL, NULL, NULL, 'virtual', 1, 1, NULL, 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59', NULL);
INSERT INTO `groups` VALUES (8, 'point_shop', '实物商品', NULL, NULL, NULL, 'physical', 2, 1, NULL, 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59', NULL);
INSERT INTO `groups` VALUES (9, 'point_shop', '优惠券', NULL, NULL, NULL, 'coupon', 3, 1, NULL, 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59', NULL);
INSERT INTO `groups` VALUES (10, 'point_shop', '会员特权', NULL, NULL, NULL, 'vip', 4, 1, NULL, 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59', NULL);
INSERT INTO `groups` VALUES (14, 'ad', '首页顶部横幅', 'home_top_banner', NULL, '首页顶部横幅广告位', NULL, 0, 1, '{\"width\": 1200, \"height\": 120}', 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);
INSERT INTO `groups` VALUES (15, 'ad', '首页右侧', 'home_right_sidebar', NULL, '首页右侧边栏广告位', NULL, 0, 1, '{\"width\": 300, \"height\": 250}', 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);
INSERT INTO `groups` VALUES (16, 'ad', '文章页顶部', 'article_top', NULL, '文章页顶部广告位', NULL, 0, 1, '{\"width\": 728, \"height\": 90}', 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);
INSERT INTO `groups` VALUES (17, 'ad', '文章页底部', 'article_bottom', NULL, '文章页底部广告位', NULL, 0, 1, '{\"width\": 728, \"height\": 90}', 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);
INSERT INTO `groups` VALUES (18, 'ad', '全站浮动', 'site_float', NULL, '全站浮动广告位', NULL, 0, 1, '{\"width\": 200, \"height\": 200}', 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);

-- ----------------------------
-- Table structure for ip_blacklist
-- ----------------------------
DROP TABLE IF EXISTS `ip_blacklist`;
CREATE TABLE `ip_blacklist`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'IP地址',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'manual' COMMENT '类型:manual-手动,auto-自动',
  `reason` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '原因',
  `block_count` int NOT NULL DEFAULT 0 COMMENT '拦截次数',
  `last_block_time` datetime NULL DEFAULT NULL COMMENT '最后拦截时间',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `expire_time` datetime NULL DEFAULT NULL COMMENT '过期时间',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_ip`(`ip` ASC) USING BTREE,
  INDEX `idx_is_enabled`(`is_enabled` ASC) USING BTREE,
  INDEX `idx_expire_time`(`expire_time` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'IP黑名单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ip_blacklist
-- ----------------------------

-- ----------------------------
-- Table structure for ip_whitelist
-- ----------------------------
DROP TABLE IF EXISTS `ip_whitelist`;
CREATE TABLE `ip_whitelist`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'IP地址',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '描述',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_ip`(`ip` ASC) USING BTREE,
  INDEX `idx_is_enabled`(`is_enabled` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'IP白名单' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ip_whitelist
-- ----------------------------

-- ----------------------------
-- Table structure for links
-- ----------------------------
DROP TABLE IF EXISTS `links`;
CREATE TABLE `links`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '链接ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `group_id` int UNSIGNED NULL DEFAULT NULL COMMENT '分组ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '网站名称',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '网站URL',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'Logo图片',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '网站描述',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '联系邮箱',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序权重',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态：0=待审核，1=已通过，2=已拒绝',
  `is_home` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否首页显示：0=否，1=是',
  `view_count` int NOT NULL DEFAULT 0 COMMENT '点击次数',
  `audit_time` datetime NULL DEFAULT NULL COMMENT '审核时间',
  `audit_user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '审核人ID',
  `audit_note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '审核备注',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间（软删除）',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_group_id`(`group_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_sort`(`sort` ASC) USING BTREE,
  INDEX `idx_is_home`(`is_home` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '友情链接表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of links
-- ----------------------------
INSERT INTO `links` VALUES (1, 1, 1, '百度', 'https://www.baidu.com', NULL, '百度1', NULL, 1, 1, 1, 0, NULL, NULL, NULL, '2025-10-19 00:58:34', '2025-11-23 04:40:13', NULL);
INSERT INTO `links` VALUES (2, 1, 1, '腾讯', 'https://www.qq.com', NULL, '百度1', NULL, 2, 1, 1, 0, NULL, NULL, NULL, '2025-10-19 00:58:34', '2025-11-23 04:40:13', NULL);
INSERT INTO `links` VALUES (3, 1, 2, 'GitHub', 'https://github.com', NULL, '百度1', NULL, 3, 1, 0, 0, NULL, NULL, NULL, '2025-10-19 00:58:34', '2025-11-23 04:40:13', NULL);
INSERT INTO `links` VALUES (4, 1, 3, 'Gitee', 'https://www.gitee.com', '', '百度1', '', 0, 1, 1, 0, NULL, NULL, NULL, '2025-10-21 08:44:11', '2025-11-23 04:40:13', NULL);

-- ----------------------------
-- Table structure for media_edit_history
-- ----------------------------
DROP TABLE IF EXISTS `media_edit_history`;
CREATE TABLE `media_edit_history`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `media_id` int UNSIGNED NOT NULL COMMENT '媒体ID',
  `user_id` int NOT NULL COMMENT '操作用户ID',
  `operation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作类型：resize/crop/rotate/filter等',
  `operation_params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '操作参数（JSON）',
  `original_file_id` int NULL DEFAULT NULL COMMENT '原始文件ID',
  `result_file_id` int NULL DEFAULT NULL COMMENT '结果文件ID',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success' COMMENT '状态：success/failed',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '错误信息',
  `processing_time` int NULL DEFAULT NULL COMMENT '处理耗时（毫秒）',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_media_id`(`media_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_operation`(`operation` ASC) USING BTREE,
  INDEX `idx_created_at`(`created_at` ASC) USING BTREE,
  CONSTRAINT `fk_edit_history_media` FOREIGN KEY (`media_id`) REFERENCES `media_library` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体编辑历史表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of media_edit_history
-- ----------------------------

-- ----------------------------
-- Table structure for media_files
-- ----------------------------
DROP TABLE IF EXISTS `media_files`;
CREATE TABLE `media_files`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `file_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA256文件哈希（唯一）',
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件相对路径',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原始文件名',
  `file_ext` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件扩展名',
  `file_size` bigint NOT NULL COMMENT '文件大小（字节）',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'MIME类型',
  `file_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件类型：image/video/audio/document/other',
  `storage_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '存储类型：local/oss/cos/qiniu等',
  `storage_config_id` int UNSIGNED NULL DEFAULT NULL COMMENT '存储配置ID',
  `file_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '文件访问URL（云存储的完整URL）',
  `width` int NULL DEFAULT NULL COMMENT '图片/视频宽度',
  `height` int NULL DEFAULT NULL COMMENT '图片/视频高度',
  `duration` int NULL DEFAULT NULL COMMENT '音视频时长（秒）',
  `ref_count` int NOT NULL DEFAULT 0 COMMENT '引用计数',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_file_hash`(`file_hash` ASC) USING BTREE,
  INDEX `idx_file_type`(`file_type` ASC) USING BTREE,
  INDEX `idx_storage_type`(`storage_type` ASC) USING BTREE,
  INDEX `idx_created_at`(`created_at` ASC) USING BTREE,
  INDEX `idx_storage_config_id`(`storage_config_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体文件物理存储表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for media_legacy
-- ----------------------------
DROP TABLE IF EXISTS `media_legacy`;
CREATE TABLE `media_legacy`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '媒体ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '上传者ID',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件名',
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件路径',
  `file_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `file_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件类型：image/video/audio/document',
  `mime_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'MIME类型',
  `file_size` bigint UNSIGNED NOT NULL COMMENT '文件大小（字节）',
  `width` int UNSIGNED NULL DEFAULT NULL COMMENT '图片/视频宽度',
  `height` int UNSIGNED NULL DEFAULT NULL COMMENT '图片/视频高度',
  `storage_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '存储类型：local/qiniu/aliyun',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `deleted_at` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_file_type`(`file_type` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体库表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for media_library
-- ----------------------------
DROP TABLE IF EXISTS `media_library`;
CREATE TABLE `media_library`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `file_id` int UNSIGNED NOT NULL COMMENT '关联media_files.id',
  `site_id` int NOT NULL COMMENT '站点ID',
  `user_id` int NOT NULL COMMENT '上传用户ID',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '媒体标题',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '媒体描述',
  `alt_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Alt文本（SEO）',
  `source` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upload' COMMENT '来源：upload/ai_generate/external',
  `source_id` int NULL DEFAULT NULL COMMENT '来源ID（如AI任务ID）',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT '状态：active/processing/failed/deleted',
  `is_public` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否公开：0-私有 1-公开',
  `view_count` int NOT NULL DEFAULT 0 COMMENT '查看次数',
  `download_count` int NOT NULL DEFAULT 0 COMMENT '下载次数',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '软删除时间',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_file_id`(`file_id` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_source`(`source` ASC, `source_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_created_at`(`created_at` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE,
  CONSTRAINT `fk_media_library_file` FOREIGN KEY (`file_id`) REFERENCES `media_files` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体库业务管理表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for media_metadata
-- ----------------------------
DROP TABLE IF EXISTS `media_metadata`;
CREATE TABLE `media_metadata`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `media_id` int UNSIGNED NOT NULL COMMENT '关联media_library.id',
  `meta_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '元数据键',
  `meta_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '元数据值（JSON）',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_media_meta`(`media_id` ASC, `meta_key` ASC) USING BTREE,
  INDEX `idx_media_id`(`media_id` ASC) USING BTREE,
  INDEX `idx_meta_key`(`meta_key` ASC) USING BTREE,
  CONSTRAINT `fk_media_metadata` FOREIGN KEY (`media_id`) REFERENCES `media_library` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体元数据表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of media_metadata
-- ----------------------------

-- ----------------------------
-- Table structure for media_thumbnail_presets
-- ----------------------------
DROP TABLE IF EXISTS `media_thumbnail_presets`;
CREATE TABLE `media_thumbnail_presets`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `site_id` int NOT NULL COMMENT '站点ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '预设名称',
  `display_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '显示名称',
  `width` int NULL DEFAULT NULL COMMENT '宽度（null为自适应）',
  `height` int NULL DEFAULT NULL COMMENT '高度（null为自适应）',
  `mode` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fit' COMMENT '模式：fit/fill/crop/exact',
  `quality` int NOT NULL DEFAULT 85 COMMENT '图片质量1-100',
  `format` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '输出格式：jpg/png/webp，null为原格式',
  `is_builtin` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否内置预设',
  `is_auto_generate` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否自动生成',
  `sort_order` int NOT NULL DEFAULT 0,
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '描述',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_site_name`(`site_id` ASC, `name` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_name`(`name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '缩略图预设配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of media_thumbnail_presets
-- ----------------------------
INSERT INTO `media_thumbnail_presets` VALUES (1, 1, 'small', '小图(缩略图)', 200, 200, 'fit', 85, NULL, 1, 1, 1, '适用于列表缩略图、图标等场景，200x200像素', '2025-11-29 06:17:22', '2025-11-29 06:17:22');
INSERT INTO `media_thumbnail_presets` VALUES (2, 1, 'medium', '中图(卡片)', 600, 600, 'fit', 90, NULL, 1, 1, 2, '适用于卡片、网格布局等场景，600x600像素', '2025-11-29 06:17:22', '2025-11-29 06:17:22');
INSERT INTO `media_thumbnail_presets` VALUES (3, 1, 'large', '大图(详情)', 1200, 1200, 'fit', 95, NULL, 1, 0, 3, '适用于详情页、查看大图等场景，1200x1200像素', '2025-11-29 06:17:22', '2025-11-29 06:17:22');

-- ----------------------------
-- Table structure for media_thumbnails
-- ----------------------------
DROP TABLE IF EXISTS `media_thumbnails`;
CREATE TABLE `media_thumbnails`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `media_id` int UNSIGNED NOT NULL COMMENT '关联media_library.id',
  `preset_id` int NULL DEFAULT NULL COMMENT '缩略图预设ID',
  `preset_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规格名称：thumbnail/small/medium/large等',
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '缩略图路径',
  `width` int NOT NULL COMMENT '宽度',
  `height` int NOT NULL COMMENT '高度',
  `file_size` bigint NOT NULL COMMENT '文件大小',
  `storage_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local' COMMENT '存储类型',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_media_preset`(`media_id` ASC, `preset_name` ASC) USING BTREE,
  INDEX `idx_media_id`(`media_id` ASC) USING BTREE,
  INDEX `idx_preset_name`(`preset_name` ASC) USING BTREE,
  CONSTRAINT `fk_media_thumbnails` FOREIGN KEY (`media_id`) REFERENCES `media_library` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体缩略图表' ROW_FORMAT = Dynamic;



-- ----------------------------
-- Table structure for media_usage
-- ----------------------------
DROP TABLE IF EXISTS `media_usage`;
CREATE TABLE `media_usage`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `media_id` int UNSIGNED NOT NULL COMMENT '媒体ID',
  `usable_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '使用类型(article, page, comment等)',
  `usable_id` int UNSIGNED NOT NULL COMMENT '使用对象ID',
  `field_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '字段名称(content, thumb等)',
  `usage_count` int UNSIGNED NULL DEFAULT 1 COMMENT '引用次数',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_media_usable`(`media_id` ASC, `usable_type` ASC, `usable_id` ASC) USING BTREE,
  INDEX `idx_media_id`(`media_id` ASC) USING BTREE,
  INDEX `idx_usable`(`usable_type` ASC, `usable_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体使用追踪表' ROW_FORMAT = Dynamic;



-- ----------------------------
-- Table structure for media_video_transcodes
-- ----------------------------
DROP TABLE IF EXISTS `media_video_transcodes`;
CREATE TABLE `media_video_transcodes`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `media_id` int UNSIGNED NOT NULL COMMENT '媒体ID',
  `original_file_id` int UNSIGNED NOT NULL COMMENT '原始文件ID',
  `preset` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '转码预设：480p/720p/1080p等',
  `format` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '输出格式：mp4/webm/hls等',
  `codec` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '编码器',
  `resolution` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '分辨率',
  `bitrate` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '码率',
  `output_file_id` int NULL DEFAULT NULL COMMENT '输出文件ID',
  `poster_file_id` int NULL DEFAULT NULL COMMENT '海报图片ID',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT '状态：pending/processing/completed/failed',
  `progress` int NOT NULL DEFAULT 0 COMMENT '进度0-100',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '错误信息',
  `processing_time` int NULL DEFAULT NULL COMMENT '处理耗时（秒）',
  `started_at` datetime NULL DEFAULT NULL COMMENT '开始时间',
  `completed_at` datetime NULL DEFAULT NULL COMMENT '完成时间',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_media_id`(`media_id` ASC) USING BTREE,
  INDEX `idx_original_file_id`(`original_file_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_created_at`(`created_at` ASC) USING BTREE,
  CONSTRAINT `fk_video_transcodes_media` FOREIGN KEY (`media_id`) REFERENCES `media_library` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体视频转码任务表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of media_video_transcodes
-- ----------------------------

-- ----------------------------
-- Table structure for media_watermark_log
-- ----------------------------
DROP TABLE IF EXISTS `media_watermark_log`;
CREATE TABLE `media_watermark_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `media_id` int UNSIGNED NOT NULL COMMENT '媒体ID',
  `preset_id` int NULL DEFAULT NULL COMMENT '水印预设ID',
  `user_id` int NOT NULL COMMENT '操作用户ID',
  `watermark_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '水印类型',
  `watermark_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '水印配置（JSON）',
  `output_file_id` int NULL DEFAULT NULL COMMENT '输出文件ID',
  `backup_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'success' COMMENT '状态：success/failed',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '错误信息',
  `processing_time` int NULL DEFAULT NULL COMMENT '处理耗时（毫秒）',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_media_id`(`media_id` ASC) USING BTREE,
  INDEX `idx_preset_id`(`preset_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_created_at`(`created_at` ASC) USING BTREE,
  CONSTRAINT `fk_watermark_log_media` FOREIGN KEY (`media_id`) REFERENCES `media_library` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体水印处理日志表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for media_watermark_presets
-- ----------------------------
DROP TABLE IF EXISTS `media_watermark_presets`;
CREATE TABLE `media_watermark_presets`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `site_id` int NOT NULL COMMENT '站点ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '预设名称',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '水印类型：text/image/tiled',
  `text_content` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '文字内容',
  `text_font` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '字体',
  `text_size` int NULL DEFAULT NULL COMMENT '字体大小',
  `text_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '文字颜色',
  `image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '水印图片路径',
  `position` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'bottom-right' COMMENT '位置：top-left/top-right/bottom-left/bottom-right/center等',
  `offset_x` int NOT NULL DEFAULT 10 COMMENT 'X偏移',
  `offset_y` int NOT NULL DEFAULT 10 COMMENT 'Y偏移',
  `opacity` int NOT NULL DEFAULT 50 COMMENT '透明度0-100',
  `scale` int NOT NULL DEFAULT 100 COMMENT '缩放比例',
  `tile_spacing` int NULL DEFAULT NULL COMMENT '平铺间距（仅平铺模式）',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认预设',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_type`(`type` ASC) USING BTREE,
  INDEX `idx_is_default`(`is_default` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体水印预设表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of media_watermark_presets
-- ----------------------------
INSERT INTO `media_watermark_presets` VALUES (1, 1, '逍遥CMS', 'text', '逍遥CMS', 'C:/Windows/Fonts/simhei.ttf', 20, 'rgb(164, 164, 164)', '', 'bottom-right', 10, 10, 50, 100, 100, 1, 1, '2025-11-21 00:02:41', '2025-11-21 00:02:41');

-- ----------------------------
-- Table structure for member_level_logs
-- ----------------------------
DROP TABLE IF EXISTS `member_level_logs`;
CREATE TABLE `member_level_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `old_level` int NOT NULL COMMENT '原等级',
  `new_level` int NOT NULL COMMENT '新等级',
  `upgrade_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'auto' COMMENT '升级类型：auto自动 manual手动',
  `reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '升级原因',
  `operator_id` int NULL DEFAULT NULL COMMENT '操作人ID（手动升级时）',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id` ASC) USING BTREE,
  INDEX `create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '会员等级升级日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of member_level_logs
-- ----------------------------

-- ----------------------------
-- Table structure for member_levels
-- ----------------------------
DROP TABLE IF EXISTS `member_levels`;
CREATE TABLE `member_levels`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `level` int NOT NULL COMMENT '等级',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '等级名称',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '等级图标',
  `points_required` int NOT NULL DEFAULT 0 COMMENT '所需积分',
  `articles_required` int NOT NULL DEFAULT 0 COMMENT '所需文章数',
  `comments_required` int NOT NULL DEFAULT 0 COMMENT '所需评论数',
  `days_required` int NOT NULL DEFAULT 0 COMMENT '所需注册天数',
  `privileges` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '等级权益JSON',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '等级描述',
  `color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '等级颜色',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `level`(`level` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '会员等级配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of member_levels
-- ----------------------------
INSERT INTO `member_levels` VALUES (1, 0, '新手', NULL, 0, 0, 0, 0, '{\"max_articles_per_day\":10,\"max_comments_per_day\":50,\"max_upload_size\":5,\"can_set_top\":false,\"can_recommend\":false}', '刚注册的新用户', '#909399', 0, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (2, 1, '见习', NULL, 100, 1, 5, 7, '{\"max_articles_per_day\":15,\"max_comments_per_day\":60,\"max_upload_size\":7,\"can_set_top\":false,\"can_recommend\":false}', '开始熟悉平台', '#67C23A', 1, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (3, 2, '学徒', NULL, 300, 5, 20, 15, '{\"max_articles_per_day\":20,\"max_comments_per_day\":70,\"max_upload_size\":9,\"can_set_top\":false,\"can_recommend\":false}', '积极参与的用户', '#409EFF', 2, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (4, 3, '能手', NULL, 800, 15, 50, 30, '{\"max_articles_per_day\":25,\"max_comments_per_day\":80,\"max_upload_size\":11,\"can_set_top\":false,\"can_recommend\":true}', '经验丰富的用户', '#E6A23C', 3, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (5, 4, '高手', NULL, 2000, 30, 100, 60, '{\"max_articles_per_day\":30,\"max_comments_per_day\":90,\"max_upload_size\":13,\"can_set_top\":false,\"can_recommend\":true}', '资深活跃用户', '#F56C6C', 4, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (6, 5, '专家', NULL, 5000, 60, 200, 90, '{\"max_articles_per_day\":35,\"max_comments_per_day\":100,\"max_upload_size\":15,\"can_set_top\":true,\"can_recommend\":true}', '领域专家级用户', '#C71585', 5, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (7, 6, '大师', NULL, 10000, 100, 500, 180, '{\"max_articles_per_day\":40,\"max_comments_per_day\":110,\"max_upload_size\":17,\"can_set_top\":true,\"can_recommend\":true}', '大师级用户', '#8B008B', 6, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (8, 7, '宗师', NULL, 20000, 200, 1000, 365, '{\"max_articles_per_day\":45,\"max_comments_per_day\":120,\"max_upload_size\":19,\"can_set_top\":true,\"can_recommend\":true}', '平台核心用户', '#4B0082', 7, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (9, 8, '传说', NULL, 50000, 500, 2000, 730, '{\"max_articles_per_day\":50,\"max_comments_per_day\":130,\"max_upload_size\":21,\"can_set_top\":true,\"can_recommend\":true}', '传说级用户', '#FFD700', 8, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (10, 9, '神话', NULL, 100000, 1000, 5000, 1095, '{\"max_articles_per_day\":60,\"max_comments_per_day\":150,\"max_upload_size\":25,\"can_set_top\":true,\"can_recommend\":true}', '神话级用户', '#FF6347', 9, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (11, 10, '至尊', NULL, 200000, 2000, 10000, 1825, '{\"max_articles_per_day\":100,\"max_comments_per_day\":200,\"max_upload_size\":30,\"can_set_top\":true,\"can_recommend\":true}', '至尊级用户', '#FF0000', 10, 1, '2025-11-02 05:01:54', '2025-11-02 05:01:54', NULL);
INSERT INTO `member_levels` VALUES (12, 11, '超级至尊', '', 1111111, 11111111, 111, 30, '[]', '', '#dd1c1c', 11, 1, '2025-11-03 01:51:52', '2025-11-28 21:43:20', '2025-11-28 21:43:19');

-- ----------------------------
-- Table structure for notification_templates
-- ----------------------------
DROP TABLE IF EXISTS `notification_templates`;
CREATE TABLE `notification_templates`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '模板代码',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '模板名称',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '类型',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '标题模板',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '内容模板',
  `channels` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '发送渠道（site/email/sms）',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '消息模板表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notification_templates
-- ----------------------------
INSERT INTO `notification_templates` VALUES (1, 'system_notice', '系统通知', 'system', '系统通知', '{content}', 'site,email', 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59');
INSERT INTO `notification_templates` VALUES (2, 'comment_reply', '评论回复', 'reply', '您收到了新的评论回复', '{from_user}回复了您的评论：{content}', 'site,email', 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59');
INSERT INTO `notification_templates` VALUES (3, 'article_like', '文章点赞', 'like', '您的文章被点赞了', '{from_user}赞了您的文章：{article_title}', 'site', 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59');
INSERT INTO `notification_templates` VALUES (4, 'new_follower', '新增粉丝', 'follow', '您有新的粉丝', '{from_user}关注了您', 'site,email', 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59');
INSERT INTO `notification_templates` VALUES (5, 'article_audit', '文章审核', 'system', '投稿审核通知', '您的投稿《{article_title}》审核{result}', 'site,email', 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59');
INSERT INTO `notification_templates` VALUES (6, 'user_register', '用户注册欢迎通知', 'system', '欢迎您，{username}', '感谢您注册本站用户！', 'site,email', 1, '2025-11-02 05:03:00', '2025-11-02 05:03:00');
INSERT INTO `notification_templates` VALUES (7, 'tsss', 'ces', 'system', '111', '222', 'site,email', 1, '2025-11-03 17:07:37', '2025-11-03 17:07:37');

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '类型（system/comment/like/follow/reply）',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '标题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '内容',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '链接',
  `from_user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '来源用户ID',
  `related_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '关联类型',
  `related_id` int UNSIGNED NULL DEFAULT NULL COMMENT '关联ID',
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已读',
  `read_time` datetime NULL DEFAULT NULL COMMENT '阅读时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_is_read`(`is_read` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '站内消息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notifications
-- ----------------------------
INSERT INTO `notifications` VALUES (1, 3, 'system', '系统测试通知', '这是一条系统测试通知，用于验证通知系统是否正常工作。', NULL, NULL, NULL, NULL, 1, '2025-11-01 13:04:54', '2025-11-01 13:04:54');
INSERT INTO `notifications` VALUES (2, 3, 'system', '系统通知', '模板通知测试内容', NULL, NULL, NULL, NULL, 0, NULL, '2025-11-01 13:04:54');
INSERT INTO `notifications` VALUES (3, 3, 'order', '订单已发货', '您的积分兑换订单（PS202511011316377096）已发货', '/point-shop/order/', NULL, 'point_shop_order', NULL, 0, NULL, '2025-11-01 13:16:38');


-- ----------------------------
-- Table structure for oauth_configs
-- ----------------------------
DROP TABLE IF EXISTS `oauth_configs`;
CREATE TABLE `oauth_configs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `platform` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '平台标识(wechat/qq/weibo/github)',
  `platform_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '平台名称',
  `app_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '应用ID/AppKey',
  `app_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '应用密钥/AppSecret',
  `redirect_uri` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '回调地址',
  `scope` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '授权范围',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '启用状态(0-禁用 1-启用)',
  `sort_order` int NOT NULL DEFAULT 0 COMMENT '排序权重',
  `extra_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '额外配置(JSON格式)',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '备注说明',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_platform`(`platform` ASC) USING BTREE,
  INDEX `idx_enabled`(`is_enabled` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'OAuth平台配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of oauth_configs
-- ----------------------------
INSERT INTO `oauth_configs` VALUES (1, 'wechat', '微信登录', '', '', '', 'snsapi_login', 0, 1, NULL, '微信开放平台登录，需配置AppID和AppSecret', '2025-11-08 13:26:18', '2025-11-08 13:26:18');
INSERT INTO `oauth_configs` VALUES (2, 'qq', 'QQ登录', '', '', '', 'get_user_info', 0, 2, NULL, 'QQ互联登录，需配置AppID和AppKey', '2025-11-08 13:26:18', '2025-11-08 13:26:18');
INSERT INTO `oauth_configs` VALUES (3, 'weibo', '微博登录', '', '', '', 'email', 0, 3, NULL, '新浪微博登录，需配置AppKey和AppSecret', '2025-11-08 13:26:18', '2025-11-08 13:26:18');
INSERT INTO `oauth_configs` VALUES (4, 'github', 'GitHub登录', '', '', '', 'user:email', 0, 4, NULL, 'GitHub OAuth登录，需配置Client ID和Client Secret', '2025-11-08 13:26:18', '2025-11-08 13:26:18');

-- ----------------------------
-- Table structure for operation_logs
-- ----------------------------
DROP TABLE IF EXISTS `operation_logs`;
CREATE TABLE `operation_logs`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NULL DEFAULT NULL COMMENT '操作用户ID',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '操作用户名',
  `module` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '模块名称',
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '操作类型',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '操作描述',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'IP地址',
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '用户代理',
  `request_method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '请求方法',
  `request_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '请求URL',
  `request_params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '请求参数',
  `old_values` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '修改前的值（JSON格式）',
  `new_values` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '修改后的值（JSON格式）',
  `changed_fields` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '变更字段列表（逗号分隔）',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态 1成功 0失败',
  `error_msg` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '错误信息',
  `execute_time` int NULL DEFAULT 0 COMMENT '执行时间(毫秒)',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_module`(`module` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE,
  INDEX `idx_module_action`(`module` ASC, `action` ASC) USING BTREE,
  INDEX `idx_user_id_create_time`(`user_id` ASC, `create_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '操作日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for pages
-- ----------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '页面ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '页面标题',
  `slug` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'URL别名',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '页面内容',
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '封面图片',
  `template` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'default' COMMENT '模板名称',
  `seo_title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SEO关键词',
  `seo_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'SEO描述',
  `og_title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Open Graph标题',
  `og_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Open Graph描述',
  `og_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'Open Graph图片',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间（软删除）',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_site_slug`(`site_id` ASC, `slug` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '单页面表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pages
-- ----------------------------
INSERT INTO `pages` VALUES (1, 1, '关于我们', 'about', '<div>\n<div>逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。</div>\n</div>', NULL, 'page', '关于我们', '关于我们', '逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。', NULL, NULL, NULL, 1, 1, '2025-10-12 02:12:51', '2025-11-23 02:53:42', NULL);
INSERT INTO `pages` VALUES (2, 1, '联系我们', 'contact', '<p>联系方式</p>\n<p>QQ: 42033223</p>\n<p>Email: <a href=\"mailto:sinma@qq.com\">sinma@qq.com</a></p>\n<p>官方QQ交流群：113572201</p>', NULL, 'page', '联系我们', '联系我们', '联系方式 QQ: 42033223 Email: sinma@qq.com 官方QQ交流群：113572201', NULL, NULL, NULL, 2, 1, '2025-10-12 02:12:51', '2025-11-23 02:53:42', NULL);
INSERT INTO `pages` VALUES (3, 1, '产品功能', 'features', '<div>\n<div>核心功能模块</div>\n<br>\n<div>1. 文章管理</div>\n<div>- 文章的增删改查</div>\n<div>- 文章分类、标签管理</div>\n<div>- 文章置顶、推荐、热门标记</div>\n<div>- 富文本编辑器</div>\n<div>- 图片上传和管理</div>\n<div>- 文章搜索和筛选</div>\n<div>- SEO设置</div>\n<br>\n<div>2. 分类管理</div>\n<div>- 多级分类支持</div>\n<div>- 分类排序</div>\n<div>- 分类SEO设置</div>\n<br>\n<div>3. 标签管理</div>\n<div>- 标签增删改查</div>\n<div>- 标签关联统计</div>\n<br>\n<div>4. 页面管理</div>\n<div>- 单页面管理（关于我们、联系我们等）</div>\n<div>- 自定义模板选择</div>\n<br>\n<div>5. 用户管理（多角色）</div>\n<div>- **超级管理员**: 拥有所有权限</div>\n<div>- **管理员**: 拥有大部分管理权限</div>\n<div>- **编辑**: 可以管理文章、分类、标签</div>\n<div>- **作者**: 只能管理自己的文章</div>\n<br>\n<div>6. 评论管理</div>\n<div>- 评论审核</div>\n<div>- 评论回复</div>\n<div>- 评论删除</div>\n<br>\n<div>7. 媒体库</div>\n<div>- 图片、文件上传</div>\n<div>- 媒体文件管理</div>\n<div>- 多种存储方式支持</div>\n<br>\n<div>8. SEO设置</div>\n<div>- 每篇文章独立SEO设置</div>\n<div>- 全站SEO配置</div>\n<br>\n<div>9. 站点配置</div>\n<div>- 网站基础信息</div>\n<div>- 上传配置</div>\n<div>- 模板配置</div>\n<br>\n<div>10. 模板管理</div>\n<div>- 多套模板支持</div>\n<div>- 模板切换</div>\n<br>\n<div>11. 静态页面生成</div>\n<div>- **手动生成**: 后台按钮点击生成</div>\n<div>- **自动生成**: 文章发布/更新时自动生成</div>\n<div>- **定时生成**: 定时任务批量生成</div>\n<div>- **生成范围**: 首页、列表页、详情页、栏目页、标签聚合页</div>\n<div>- **生成日志**: 记录每次生成的详细信息</div>\n</div>', '', 'page', '产品功能', '产品功能', '核心功能模块 1. 文章管理 - 文章的增删改查 - 文章分类、标签管理 - 文章置顶、推荐、热门标记 - 富文本编辑器 - 图片上传和管理 - 文章搜索和筛选 - SEO设置 2. 分类管理 - 多级分类支持 - 分类排序 - 分类SEO设置 3. 标签管理 - 标签增删改查 - 标签关联统计 4. 页面管理 - 单页面管理（关于我们、联系我们等） - 自定义模板选择 5. 用户管理（多角色） ', NULL, NULL, NULL, 0, 1, '2025-10-12 02:12:51', '2025-11-23 02:53:42', NULL);
INSERT INTO `pages` VALUES (4, 1, '会员中心', 'members', '<p>会员中心</p>', '', 'page', '会员中心', '会员中心', '会员中心', NULL, NULL, NULL, 0, 1, '2025-10-12 02:12:51', '2025-11-29 03:17:14', NULL);
INSERT INTO `pages` VALUES (5, 1, '投稿中心', 'contribute', '<div>\n<div>投稿中心</div>\n</div>', '', 'page', '投稿中心', '投稿中心', '投稿中心', NULL, NULL, NULL, 0, 1, '2025-10-12 02:12:51', '2025-11-29 03:17:14', NULL);
INSERT INTO `pages` VALUES (6, 1, '我的投稿', 'contributions', '<div>\n<div>我的投稿</div>\n</div>', '', 'page', '我的投稿', '我的投稿', '我的投稿', NULL, NULL, NULL, 0, 1, '2025-10-12 02:12:51', '2025-11-29 03:17:14', NULL);
INSERT INTO `pages` VALUES (7, 1, '个人中心', 'profile', '<p>个人中心</p>', '', 'page', '个人中心', '个人中心', '个人中心', NULL, NULL, NULL, 0, 1, '2025-10-12 02:12:51', '2025-11-29 03:17:14', NULL);
INSERT INTO `pages` VALUES (8, 1, '我的通知', 'notifications', '<p>我的通知</p>', '', 'page', '我的通知', '我的通知', '我的通知', NULL, NULL, NULL, 0, 1, '2025-10-12 02:12:51', '2025-11-29 03:17:14', NULL);
INSERT INTO `pages` VALUES (9, 1, '注册', 'register', '<p>注册</p>', '', 'page', '注册', '注册', '注册', NULL, NULL, NULL, 0, 1, '2025-10-12 02:12:51', '2025-11-29 03:17:14', NULL);
INSERT INTO `pages` VALUES (10, 1, '登录', 'login', '<p>登录</p>', '', 'page', '登录', '登录', '登录', NULL, NULL, NULL, 0, 1, '2025-10-12 02:12:51', '2025-11-29 03:17:14', NULL);

-- ----------------------------
-- Table structure for point_shop_goods
-- ----------------------------
DROP TABLE IF EXISTS `point_shop_goods`;
CREATE TABLE `point_shop_goods`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '分类ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '商品名称',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '商品描述',
  `images` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '商品图片（JSON）',
  `price` int NOT NULL COMMENT '所需积分',
  `stock` int NOT NULL DEFAULT 0 COMMENT '库存（-1为无限）',
  `sales` int NOT NULL DEFAULT 0 COMMENT '销量',
  `limit_per_user` int NOT NULL DEFAULT -1 COMMENT '每人限兑（-1为不限）',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'virtual' COMMENT '类型（virtual虚拟/physical实物）',
  `virtual_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '虚拟商品内容',
  `level_required` tinyint NOT NULL DEFAULT 0 COMMENT '所需等级',
  `vip_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否需要VIP',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态（0下架/1上架）',
  `start_time` datetime NULL DEFAULT NULL COMMENT '上架时间',
  `end_time` datetime NULL DEFAULT NULL COMMENT '下架时间',
  `sort_order` int NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_category_id`(`category_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '积分商城商品表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of point_shop_goods
-- ----------------------------
INSERT INTO `point_shop_goods` VALUES (1, 1, '测试虚拟商品', '这是一个测试虚拟商品', NULL, 100, -1, 1, -1, 'virtual', '这是虚拟商品的内容', 0, 0, 1, NULL, NULL, 0, '2025-11-01 13:16:08', '2025-11-01 13:16:38');

-- ----------------------------
-- Table structure for point_shop_orders
-- ----------------------------
DROP TABLE IF EXISTS `point_shop_orders`;
CREATE TABLE `point_shop_orders`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `order_no` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '订单号',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `goods_id` int UNSIGNED NOT NULL COMMENT '商品ID',
  `goods_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '商品名称',
  `goods_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '商品图片',
  `points` int NOT NULL COMMENT '消耗积分',
  `quantity` int NOT NULL DEFAULT 1 COMMENT '数量',
  `total_points` int NOT NULL COMMENT '总积分',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态（0待发货/1已发货/2已完成/3已取消）',
  `contact_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '收货人',
  `contact_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '联系电话',
  `contact_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '收货地址',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '备注',
  `admin_remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '管理员备注',
  `virtual_content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '虚拟商品内容',
  `deliver_time` datetime NULL DEFAULT NULL COMMENT '发货时间',
  `complete_time` datetime NULL DEFAULT NULL COMMENT '完成时间',
  `cancel_time` datetime NULL DEFAULT NULL COMMENT '取消时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '下单时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `order_no`(`order_no` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_goods_id`(`goods_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '积分兑换订单表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of point_shop_orders
-- ----------------------------
INSERT INTO `point_shop_orders` VALUES (1, 'PS202511011316377096', 3, 1, '测试虚拟商品', NULL, 100, 1, 100, 0, NULL, NULL, NULL, NULL, NULL, '这是虚拟商品的内容', NULL, NULL, NULL, '2025-11-01 13:16:38');

-- ----------------------------
-- Table structure for relations
-- ----------------------------
DROP TABLE IF EXISTS `relations`;
CREATE TABLE `relations`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `source_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '源类型：article, topic',
  `source_id` int UNSIGNED NOT NULL COMMENT '源ID',
  `target_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '目标类型：category, tag, article',
  `target_id` int UNSIGNED NOT NULL COMMENT '目标ID',
  `relation_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'default' COMMENT '关联类型：main主分类, sub副分类, default默认',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `extra` json NULL,
  `site_id` int UNSIGNED NULL DEFAULT 1 COMMENT '站点ID',
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_relation`(`source_type` ASC, `source_id` ASC, `target_type` ASC, `target_id` ASC, `relation_type` ASC) USING BTREE,
  INDEX `idx_source`(`source_type` ASC, `source_id` ASC) USING BTREE,
  INDEX `idx_target`(`target_type` ASC, `target_id` ASC) USING BTREE,
  INDEX `idx_site`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '通用关联表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for sensitive_words
-- ----------------------------
DROP TABLE IF EXISTS `sensitive_words`;
CREATE TABLE `sensitive_words`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `word` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '敏感词',
  `level` tinyint(1) NOT NULL DEFAULT 2 COMMENT '处理级别(1-提示 2-替换 3-拒绝)',
  `replacement` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '***' COMMENT '替换词',
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'general' COMMENT '分类(politics-政治 porn-色情 violence-暴力 ad-广告 abuse-辱骂 general-其他)',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '启用状态(0-禁用 1-启用)',
  `hit_count` int NULL DEFAULT 0 COMMENT '命中次数',
  `remark` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '备注说明',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_word`(`word` ASC) USING BTREE,
  INDEX `idx_category`(`category` ASC) USING BTREE,
  INDEX `idx_enabled`(`is_enabled` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '敏感词库表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sensitive_words
-- ----------------------------
INSERT INTO `sensitive_words` VALUES (1, '反动', 3, '***', 'politics', 1, 0, '政治敏感词', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (2, '暴乱', 3, '***', 'politics', 1, 0, '政治敏感词', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (3, '颠覆', 3, '***', 'politics', 1, 0, '政治敏感词', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (4, '色情', 3, '***', 'porn', 1, 0, '色情内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (5, '淫秽', 3, '***', 'porn', 1, 0, '色情内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (6, '裸聊', 3, '***', 'porn', 1, 0, '色情内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (7, '杀人', 2, '***', 'violence', 1, 0, '暴力内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (8, '自杀', 2, '***', 'violence', 1, 0, '暴力内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (9, '恐怖', 2, '***', 'violence', 1, 0, '暴力内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (10, '代开发票', 3, '***', 'ad', 1, 0, '广告内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (11, '办证', 3, '***', 'ad', 1, 0, '广告内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (12, '贷款', 2, '***', 'ad', 1, 0, '广告内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (13, '兼职', 2, '***', 'ad', 1, 0, '广告内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (14, '傻逼', 2, '***', 'abuse', 1, 0, '辱骂内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (15, '垃圾', 2, '***', 'abuse', 1, 0, '辱骂内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (16, '废物', 2, '***', 'abuse', 1, 0, '辱骂内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');
INSERT INTO `sensitive_words` VALUES (17, '白痴', 2, '***', 'abuse', 1, 0, '辱骂内容', '2025-11-08 13:55:41', '2025-11-08 13:55:41');

-- ----------------------------
-- Table structure for seo_404_logs
-- ----------------------------
DROP TABLE IF EXISTS `seo_404_logs`;
CREATE TABLE `seo_404_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '404 URL',
  `referer` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '来源页面',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'IP地址',
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '用户代理',
  `hit_count` int NOT NULL DEFAULT 1 COMMENT '出现次数',
  `first_hit_time` datetime NULL DEFAULT NULL COMMENT '首次出现时间',
  `last_hit_time` datetime NULL DEFAULT NULL COMMENT '最后出现时间',
  `is_fixed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已修复：0=未修复，1=已修复',
  `fixed_time` datetime NULL DEFAULT NULL COMMENT '修复时间',
  `fixed_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '修复方式：redirect=重定向，deleted=已删除，ignored=忽略',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_url`(`url`(255) ASC) USING BTREE,
  INDEX `idx_hit_count`(`hit_count` ASC) USING BTREE,
  INDEX `idx_is_fixed`(`is_fixed` ASC) USING BTREE,
  INDEX `idx_last_hit_time`(`last_hit_time` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '404错误日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of seo_404_logs
-- ----------------------------

-- ----------------------------
-- Table structure for seo_keyword_rankings
-- ----------------------------
DROP TABLE IF EXISTS `seo_keyword_rankings`;
CREATE TABLE `seo_keyword_rankings`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `keyword` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '关键词',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '目标URL',
  `search_engine` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'baidu' COMMENT '搜索引擎：baidu, google, bing等',
  `ranking` int NULL DEFAULT NULL COMMENT '排名位置（1-100，NULL表示100名之外）',
  `check_date` date NOT NULL COMMENT '检查日期',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_keyword_engine_date`(`keyword` ASC, `search_engine` ASC, `check_date` ASC) USING BTREE,
  INDEX `idx_keyword`(`keyword` ASC) USING BTREE,
  INDEX `idx_check_date`(`check_date` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'SEO关键词排名追踪表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of seo_keyword_rankings
-- ----------------------------

-- ----------------------------
-- Table structure for seo_redirects
-- ----------------------------
DROP TABLE IF EXISTS `seo_redirects`;
CREATE TABLE `seo_redirects`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '重定向ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `from_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '源URL（支持通配符*）',
  `to_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '目标URL',
  `redirect_type` int NOT NULL DEFAULT 301 COMMENT '重定向类型：301=永久重定向，302=临时重定向',
  `match_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'exact' COMMENT '匹配类型：exact=精确匹配，wildcard=通配符，regex=正则表达式',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用：0=禁用，1=启用',
  `hit_count` int NOT NULL DEFAULT 0 COMMENT '命中次数',
  `last_hit_time` datetime NULL DEFAULT NULL COMMENT '最后命中时间',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '规则描述',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_from_url`(`from_url`(255) ASC) USING BTREE,
  INDEX `idx_enabled`(`is_enabled` ASC) USING BTREE,
  INDEX `idx_hit_count`(`hit_count` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'URL重定向规则表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of seo_redirects
-- ----------------------------
INSERT INTO `seo_redirects` VALUES (1, 1, '/old-page', '/new-page', 301, 'exact', 1, 0, NULL, '旧页面迁移到新页面', '2025-10-19 02:20:29', '2025-10-19 02:20:29');
INSERT INTO `seo_redirects` VALUES (2, 1, '/blog/*', '/articles/*', 301, 'wildcard', 1, 0, NULL, '博客路径调整', '2025-10-19 02:20:29', '2025-10-19 02:20:29');

-- ----------------------------
-- Table structure for seo_robots
-- ----------------------------
DROP TABLE IF EXISTS `seo_robots`;
CREATE TABLE `seo_robots`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '配置名称',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'robots.txt内容',
  `is_active` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否启用：0=否，1=是',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '描述',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_active`(`is_active` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Robots.txt配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of seo_robots
-- ----------------------------
INSERT INTO `seo_robots` VALUES (1, '默认配置', 'User-agent: *\nDisallow: /admin/\nDisallow: /api/\nDisallow: *.json$\nDisallow: *.xml$\n\nSitemap: /sitemap.xml', 1, '默认的robots.txt配置', '2025-10-19 02:20:29', '2025-10-19 04:44:18');
INSERT INTO `seo_robots` VALUES (2, '全部允许', 'User-agent: *\nDisallow:\n\nSitemap: /sitemap.xml', 0, '允许所有搜索引擎抓取', '2025-10-19 02:20:29', '2025-10-19 02:20:29');
INSERT INTO `seo_robots` VALUES (3, '全部禁止', 'User-agent: *\nDisallow: /', 0, '禁止所有搜索引擎抓取（开发环境）', '2025-10-19 02:20:29', '2025-10-19 02:20:29');

-- ----------------------------
-- Table structure for site_admins
-- ----------------------------
DROP TABLE IF EXISTS `site_admins`;
CREATE TABLE `site_admins`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `site_id` int UNSIGNED NOT NULL COMMENT '站点ID',
  `admin_user_id` int UNSIGNED NOT NULL COMMENT '管理员用户ID',
  `role_type` tinyint NOT NULL DEFAULT 1 COMMENT '角色类型：1=站点管理员 2=站点编辑 3=站点审核员',
  `permissions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '站点权限（JSON格式，为空则继承系统角色权限）',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=禁用 1=启用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_site_admin`(`site_id` ASC, `admin_user_id` ASC) USING BTREE,
  INDEX `idx_admin_user_id`(`admin_user_id` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '站点管理员关联表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of site_admins
-- ----------------------------

-- ----------------------------
-- Table structure for site_config
-- ----------------------------
DROP TABLE IF EXISTS `site_config`;
CREATE TABLE `site_config`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `config_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置键',
  `config_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '配置值',
  `config_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text' COMMENT '配置类型：text/number/json/image',
  `group_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'basic' COMMENT '配置分组：basic/seo/upload/template',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '配置描述',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_config_key`(`config_key` ASC) USING BTREE,
  INDEX `idx_group_name`(`group_name` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 32 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '站点配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of site_config
-- ----------------------------
INSERT INTO `site_config` VALUES (1, 'site_name', '逍遥内容管理系统 CarefreeCMS', 'text', 'basic', '网站名称', 1, '2025-10-21 08:12:15');
INSERT INTO `site_config` VALUES (2, 'site_logo', '', 'image', 'basic', '网站Logo', 2, '2025-10-12 02:12:51');
INSERT INTO `site_config` VALUES (3, 'site_keywords', 'CMS,内容管理,ThinkPHP,Vue', 'text', 'seo', 'SEO关键词', 3, '2025-10-12 02:12:51');
INSERT INTO `site_config` VALUES (4, 'site_description', '基于ThinkPHP8和Vue3的内容管理系统', 'text', 'seo', 'SEO描述', 4, '2025-10-12 02:12:51');
INSERT INTO `site_config` VALUES (5, 'site_icp', '', 'text', 'basic', 'ICP备案号', 5, '2025-10-12 02:12:51');
INSERT INTO `site_config` VALUES (6, 'site_copyright', '© 2025 逍遥内容管理系统 CarefreeCMS  All rights reserved.', 'text', 'basic', '版权信息', 6, '2025-10-21 08:12:15');
INSERT INTO `site_config` VALUES (7, 'upload_max_size', '10', 'number', 'upload', '最大上传大小(MB)', 7, '2025-10-12 02:12:51');
INSERT INTO `site_config` VALUES (8, 'upload_allowed_ext', 'jpg,jpeg,png,gif,webp,pdf,doc,docx,zip', 'text', 'upload', '允许的文件扩展名', 8, '2025-10-12 02:12:51');
INSERT INTO `site_config` VALUES (9, 'default_template', 'default', 'text', 'template', '默认模板', 9, '2025-10-12 02:12:51');
INSERT INTO `site_config` VALUES (10, 'article_page_size', '20', 'number', 'basic', '文章列表每页数量', 10, '2025-10-12 02:12:51');
INSERT INTO `site_config` VALUES (11, 'comment_need_audit', '1', 'number', 'basic', '评论是否需要审核：0=否，1=是', 11, '2025-10-12 02:12:51');
INSERT INTO `site_config` VALUES (12, 'index_template', 'index', 'text', 'template', '首页模板（相对于当前模板套装目录）', 100, '2025-10-16 02:01:28');
INSERT INTO `site_config` VALUES (13, 'current_template_theme', 'linux_nbxx', 'text', 'template', '当前模板套装', 101, '2025-11-29 03:17:14');
INSERT INTO `site_config` VALUES (14, 'site_favicon', '', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (15, 'site_url', 'https://www.carefreecms.com', 'text', 'basic', NULL, 0, '2025-10-20 21:41:58');
INSERT INTO `site_config` VALUES (16, 'site_police', '', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (17, 'seo_title', '逍遥内容管理系统 CarefreeCMS SEO', 'text', 'basic', NULL, 0, '2025-11-06 19:40:11');
INSERT INTO `site_config` VALUES (18, 'seo_keywords', '逍遥,逍遥码,内容管理系统,cms,carefree,carefreecms,seo', 'text', 'basic', NULL, 0, '2025-11-06 19:40:11');
INSERT INTO `site_config` VALUES (19, 'seo_description', '这里是逍遥内容管理系统 CarefreeCMS的官方网站，欢迎您的访问。', 'text', 'basic', NULL, 0, '2025-10-23 11:16:43');
INSERT INTO `site_config` VALUES (20, 'thirdparty_code_pc', '', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (21, 'recycle_bin_enable', 'open', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (22, 'article_sub_category', 'open', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (23, 'breadcrumb_home', '首页', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (24, 'breadcrumb_separator', '>', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (25, 'upload_image_ext', 'jpg|gif|png|bmp|jpeg|ico|webp', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (26, 'upload_file_ext', 'zip|gz|rar|iso|doc|xls|ppt|wps|docx|xlsx|pptx', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (27, 'upload_video_ext', 'swf|mpg|mp3|rm|rmvb|wmv|wma|wav|mid|mov|mp4', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (28, 'upload_rename', 'random', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (29, 'content_image_features', 'wap_adapt,add_title,add_alt', 'text', 'basic', NULL, 0, '2025-10-21 08:57:33');
INSERT INTO `site_config` VALUES (30, 'article_default_views', '500|1000', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');
INSERT INTO `site_config` VALUES (31, 'article_default_downloads', '100|500', 'text', 'basic', NULL, 0, '2025-10-18 12:15:45');

-- ----------------------------
-- Table structure for site_content_share
-- ----------------------------
DROP TABLE IF EXISTS `site_content_share`;
CREATE TABLE `site_content_share`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '共享ID',
  `source_site_id` int UNSIGNED NOT NULL COMMENT '源站点ID',
  `target_site_id` int UNSIGNED NOT NULL COMMENT '目标站点ID',
  `content_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内容类型：article=文章 category=分类 tag=标签',
  `content_id` int UNSIGNED NOT NULL COMMENT '内容ID',
  `share_mode` tinyint NOT NULL DEFAULT 1 COMMENT '共享模式：1=引用（不复制） 2=复制（独立副本）',
  `sync_update` tinyint NOT NULL DEFAULT 1 COMMENT '是否同步更新：0=否 1=是（仅引用模式有效）',
  `target_content_id` int UNSIGNED NULL DEFAULT NULL COMMENT '目标站点内容ID（复制模式时使用）',
  `share_status` tinyint NOT NULL DEFAULT 1 COMMENT '共享状态：0=已取消 1=共享中',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_share`(`source_site_id` ASC, `target_site_id` ASC, `content_type` ASC, `content_id` ASC) USING BTREE,
  INDEX `idx_source_site`(`source_site_id` ASC, `content_type` ASC, `content_id` ASC) USING BTREE,
  INDEX `idx_target_site`(`target_site_id` ASC, `content_type` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '站点内容共享表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of site_content_share
-- ----------------------------

-- ----------------------------
-- Table structure for site_template_config
-- ----------------------------
DROP TABLE IF EXISTS `site_template_config`;
CREATE TABLE `site_template_config`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `site_id` int UNSIGNED NOT NULL COMMENT '站点ID',
  `package_id` int UNSIGNED NOT NULL COMMENT '模板包ID',
  `custom_config` json NULL COMMENT '个性化配置（主题色、字体等）',
  `is_active` tinyint NOT NULL DEFAULT 1 COMMENT '是否激活（0否1是）',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_site_package`(`site_id` ASC, `package_id` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_package_id`(`package_id` ASC) USING BTREE,
  INDEX `idx_is_active`(`is_active` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '站点模板配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of site_template_config
-- ----------------------------
INSERT INTO `site_template_config` VALUES (1, 1, 1, NULL, 0, '2025-11-17 01:03:37', '2025-11-30 14:31:46');


-- ----------------------------
-- Table structure for site_template_overrides
-- ----------------------------
DROP TABLE IF EXISTS `site_template_overrides`;
CREATE TABLE `site_template_overrides`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `site_id` int UNSIGNED NOT NULL COMMENT '站点ID',
  `template_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板类型（index/category/article/page/tag等）',
  `template_id` int UNSIGNED NOT NULL COMMENT '覆盖使用的模板ID',
  `priority` int NOT NULL DEFAULT 0 COMMENT '优先级（数字越大优先级越高）',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_site_type`(`site_id` ASC, `template_type` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_template_id`(`template_id` ASC) USING BTREE,
  INDEX `idx_template_type`(`template_type` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '站点模板覆盖表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of site_template_overrides
-- ----------------------------

-- ----------------------------
-- Table structure for sites
-- ----------------------------
DROP TABLE IF EXISTS `sites`;
CREATE TABLE `sites`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '站点ID',
  `site_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '站点代码（唯一标识）',
  `site_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '站点名称',
  `site_type` tinyint NOT NULL DEFAULT 2 COMMENT '站点类型：1=主站 2=子站 3=独立站',
  `parent_site_id` int UNSIGNED NULL DEFAULT NULL COMMENT '父站点ID（用于站点层级关系）',
  `static_output_dir` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '静态文件输出目录，相对于html目录',
  `sub_domain` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '子域名前缀',
  `domain_bind_type` tinyint NOT NULL DEFAULT 1 COMMENT '域名绑定类型：1=独立域名 2=子域名 3=目录',
  `db_prefix` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '表前缀（如 site1_）',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '站点Logo',
  `favicon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '站点图标',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '站点描述',
  `keywords` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '站点关键词',
  `copyright` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '版权信息',
  `icp_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'ICP备案号',
  `police_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '公安备案号',
  `thirdparty_code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '第三方代码',
  `contact_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '联系邮箱',
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '联系电话',
  `contact_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '联系地址',
  `site_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '网站网址',
  `region_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '地域代码（如城市代码）',
  `region_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '地域名称（如城市名称）',
  `province` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '省份',
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '城市',
  `district` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '区县',
  `template_id` int UNSIGNED NULL DEFAULT NULL COMMENT '默认模板ID',
  `template_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '模板路径',
  `theme_color` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '主题色',
  `config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '站点配置（JSON格式）',
  `seo_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT 'SEO配置（JSON格式）',
  `analytics_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '统计配置（JSON格式）',
  `static_enable` tinyint NOT NULL DEFAULT 0 COMMENT '是否启用静态化：0=否 1=是',
  `static_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '静态文件路径',
  `static_domain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '静态文件域名',
  `storage_type` tinyint NOT NULL DEFAULT 1 COMMENT '存储类型：1=本地 2=OSS 3=COS',
  `storage_config` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '存储配置（JSON格式）',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=禁用 1=启用 2=维护中',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `visit_count` bigint UNSIGNED NOT NULL DEFAULT 0 COMMENT '访问量',
  `article_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '文章数',
  `user_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户数',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `delete_time` datetime NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_site_code`(`site_code` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_region_code`(`region_code` ASC) USING BTREE,
  INDEX `idx_parent_site_id`(`parent_site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '多站点配置表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sites
-- ----------------------------
INSERT INTO `sites` VALUES (1, 'main', '逍遥CMS', 1, NULL, NULL, NULL, 1, NULL, 'http://localhost:8000/uploads/2025/11/21/20251121234400_692088c09524e.jpg', 'http://localhost:8000/uploads/2025/11/22/20251122002551_6920928fc4183.ico', '主站点', '内容管理系统', 'Copyright @ 2025 CarefreeCms', '湘ICP备100010号', '湘GA备100011号', '', NULL, NULL, NULL, 'http://democmstest.sinma.net/', NULL, NULL, NULL, NULL, NULL, 1, 'default', NULL, '{\"index_template\":\"index\",\"recycle_bin_enable\":\"open\",\"article_sub_category\":\"close\"}', '{\"seo_title\":\"\\u900d\\u9065CMS\",\"seo_keywords\":\"\\u900d\\u9065CMS\",\"seo_description\":\"\\u900d\\u9065CMS\"}', NULL, 0, NULL, NULL, 1, NULL, 1, 0, 0, 11, 4, '2025-11-08 12:27:14', '2025-11-30 14:31:48', NULL);

-- ----------------------------
-- Table structure for sliders
-- ----------------------------
DROP TABLE IF EXISTS `sliders`;
CREATE TABLE `sliders`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '幻灯片ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `group_id` int UNSIGNED NOT NULL COMMENT '分组ID',
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '标题',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '图片URL',
  `link_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '链接地址',
  `link_target` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '_blank' COMMENT '链接打开方式：_blank=新窗口，_self=当前窗口',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '描述文字',
  `button_text` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '按钮文字',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `start_time` datetime NULL DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime NULL DEFAULT NULL COMMENT '结束时间',
  `view_count` int NOT NULL DEFAULT 0 COMMENT '展示次数',
  `click_count` int NOT NULL DEFAULT 0 COMMENT '点击次数',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间（软删除）',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_group_id`(`group_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_sort`(`sort` ASC) USING BTREE,
  INDEX `idx_time`(`start_time` ASC, `end_time` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '幻灯片表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sliders
-- ----------------------------
INSERT INTO `sliders` VALUES (1, 1, 1, '欢迎使用CMS系统', 'https://www.carefreecms.com/uploads/2025/10/21/20251021002240_68f661d06afdb.jpg', '/about', '_blank', '功能强大、易于使用的内容管理系统', '了解更多', 1, 1, NULL, NULL, 0, 0, '2025-10-19 01:29:18', '2025-10-21 00:22:53', NULL);
INSERT INTO `sliders` VALUES (2, 1, 1, '专业的技术支持', 'https://www.carefreecms.com/uploads/2025/10/21/20251021002300_68f661e417f0f.jpg', '/support', '_blank', '7x24小时技术支持服务', '联系我们', 2, 1, NULL, NULL, 0, 0, '2025-10-19 01:29:18', '2025-10-21 00:23:03', NULL);
INSERT INTO `sliders` VALUES (3, 1, 1, '丰富的模板资源', 'https://www.carefreecms.com/uploads/2025/10/21/20251021002309_68f661eda7e00.jpg', '/templates', '_blank', '海量精美模板任您选择', '查看模板', 3, 1, NULL, NULL, 0, 0, '2025-10-19 01:29:18', '2025-10-21 00:23:13', NULL);

-- ----------------------------
-- Table structure for sms_config
-- ----------------------------
DROP TABLE IF EXISTS `sms_config`;
CREATE TABLE `sms_config`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `provider` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '服务商（aliyun/tencent/yunpian）',
  `access_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'AccessKey',
  `access_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'AccessSecret',
  `sign_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '签名',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '短信配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sms_config
-- ----------------------------
INSERT INTO `sms_config` VALUES (1, 'mock', 'test_key', 'test_secret', '测试签名', 1, 1, '2025-11-01 13:08:54', '2025-11-01 13:08:54');

-- ----------------------------
-- Table structure for sms_logs
-- ----------------------------
DROP TABLE IF EXISTS `sms_logs`;
CREATE TABLE `sms_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '手机号',
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '验证码',
  `content` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '短信内容',
  `template_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '模板代码',
  `provider` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '服务商',
  `response` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '返回结果',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态（0失败/1成功）',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'IP地址',
  `send_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '发送时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_phone`(`phone` ASC) USING BTREE,
  INDEX `idx_send_time`(`send_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '短信发送日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sms_logs
-- ----------------------------
INSERT INTO `sms_logs` VALUES (1, '13800138000', '123456', '您的验证码是：123456，5分钟内有效。', NULL, 'mock', '模拟发送成功', 1, '0.0.0.0', '2025-11-01 13:08:54');
INSERT INTO `sms_logs` VALUES (2, '13800138000', '123456', '您的验证码是：123456，5分钟内有效。', NULL, 'mock', '模拟发送成功', 1, '0.0.0.0', '2025-11-01 13:09:50');
INSERT INTO `sms_logs` VALUES (3, '13900139000', '049052', '您的验证码是：049052，5分钟内有效。', NULL, 'mock', '模拟发送成功', 1, '0.0.0.0', '2025-11-01 13:09:52');
INSERT INTO `sms_logs` VALUES (4, '13800138000', '123456', '您的验证码是：123456，5分钟内有效。', NULL, 'mock', '模拟发送成功', 1, '0.0.0.0', '2025-11-01 13:10:30');

-- ----------------------------
-- Table structure for sms_templates
-- ----------------------------
DROP TABLE IF EXISTS `sms_templates`;
CREATE TABLE `sms_templates`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '模板代码',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '模板名称',
  `provider` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '服务商',
  `template_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '模板ID',
  `content` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '模板内容',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '类型（verify/login/reset）',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '短信模板表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sms_templates
-- ----------------------------

-- ----------------------------
-- Table structure for storage_configs
-- ----------------------------
DROP TABLE IF EXISTS `storage_configs`;
CREATE TABLE `storage_configs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `site_id` int UNSIGNED NULL DEFAULT NULL COMMENT '站点ID，NULL表示全局配置',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '配置名称',
  `driver` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '存储驱动 (local/aliyun_oss/tencent_cos/qiniu)',
  `config_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '配置数据（JSON格式）',
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否启用',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否为默认存储',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '描述',
  `sort_order` int NOT NULL DEFAULT 0 COMMENT '排序',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_driver`(`driver` ASC) USING BTREE,
  INDEX `idx_is_default`(`is_default` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '存储配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of storage_configs
-- ----------------------------
INSERT INTO `storage_configs` VALUES (1, NULL, '本地存储', 'local', '{\"root_path\":\"html/uploads\",\"url_prefix\":\"/uploads\"}', 1, 1, '默认本地存储', 0, NULL, NULL);

-- ----------------------------
-- Table structure for storage_stats
-- ----------------------------
DROP TABLE IF EXISTS `storage_stats`;
CREATE TABLE `storage_stats`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `storage_config_id` int UNSIGNED NOT NULL COMMENT '存储配置ID',
  `site_id` int UNSIGNED NULL DEFAULT NULL COMMENT '站点ID',
  `total_files` int NOT NULL DEFAULT 0 COMMENT '文件总数',
  `total_size` bigint NOT NULL DEFAULT 0 COMMENT '总大小（字节）',
  `image_count` int NOT NULL DEFAULT 0 COMMENT '图片数量',
  `video_count` int NOT NULL DEFAULT 0 COMMENT '视频数量',
  `audio_count` int NOT NULL DEFAULT 0 COMMENT '音频数量',
  `document_count` int NOT NULL DEFAULT 0 COMMENT '文档数量',
  `other_count` int NOT NULL DEFAULT 0 COMMENT '其他文件数量',
  `stat_date` date NOT NULL COMMENT '统计日期',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_storage_site_date`(`storage_config_id` ASC, `site_id` ASC, `stat_date` ASC) USING BTREE,
  INDEX `idx_stat_date`(`stat_date` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '存储使用统计表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of storage_stats
-- ----------------------------

-- ----------------------------
-- Table structure for system_config
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `site_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '站点ID（0=全局配置，>0=站点配置）',
  `config_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '配置键',
  `config_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '配置值',
  `create_time` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `config_key`(`config_key` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 29 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '系统配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of system_config
-- ----------------------------
INSERT INTO `system_config` VALUES (1, 0, 'site_status', 'open', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (2, 0, 'site_name', '国产CMS', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (3, 0, 'site_logo', 'http://localhost:8000/html/uploads/2025/10/14/20251014062422_68ed7c163fa4f.png', '2025-10-13 10:30:18', '2025-10-14 06:24:26');
INSERT INTO `system_config` VALUES (4, 0, 'site_favicon', '', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (5, 0, 'site_url', '', '2025-10-13 10:30:18', '2025-10-13 22:02:05');
INSERT INTO `system_config` VALUES (6, 0, 'site_copyright', 'Copyright @ 2025 sinma.net', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (7, 0, 'site_icp', '湘ICP备100010号', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (8, 0, 'site_police', '湘GA备100011号', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (9, 0, 'seo_title', '国产CMS-title', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (10, 0, 'seo_keywords', '国产CMS,cms,cms中国', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (11, 0, 'seo_description', '国产CMS描述文件', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (12, 0, 'thirdparty_code_pc', '', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (13, 0, 'thirdparty_code_mobile', '', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (14, 0, 'mobile_domain_enable', 'open', '2025-10-13 10:30:18', '2025-10-13 10:30:52');
INSERT INTO `system_config` VALUES (15, 0, 'https_enable', 'open', '2025-10-13 10:30:18', '2025-10-13 10:30:52');
INSERT INTO `system_config` VALUES (16, 0, 'recycle_bin_enable', 'open', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (17, 0, 'pc_to_mobile_js', 'append', '2025-10-13 10:30:18', '2025-10-13 10:30:52');
INSERT INTO `system_config` VALUES (18, 0, 'article_sub_category', 'open', '2025-10-13 10:30:18', '2025-10-13 10:30:52');
INSERT INTO `system_config` VALUES (19, 0, 'breadcrumb_home', '首页', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (20, 0, 'breadcrumb_separator', '>', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (21, 0, 'upload_image_ext', 'jpg|gif|png|bmp|jpeg|ico|webp', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (22, 0, 'upload_file_ext', 'zip|gz|rar|iso|doc|xls|ppt|wps|docx|xlsx|pptx', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (23, 0, 'upload_video_ext', 'swf|mpg|mp3|rm|rmvb|wmv|wma|wav|mid|mov|mp4', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (24, 0, 'upload_max_size', '2', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (25, 0, 'upload_rename', 'random', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (26, 0, 'content_image_features', '', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (27, 0, 'article_default_views', '500|1000', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (28, 0, 'article_default_downloads', '100|500', '2025-10-13 10:30:18', '2025-10-13 10:30:18');

-- ----------------------------
-- Table structure for system_logs
-- ----------------------------
DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `level` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '日志级别:debug,info,warning,error,critical',
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '日志分类',
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '日志消息',
  `context` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '上下文数据(JSON)',
  `user_id` int NULL DEFAULT NULL COMMENT '用户ID',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'IP地址',
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '用户代理',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '请求URL',
  `method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '请求方法',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_level`(`level` ASC) USING BTREE,
  INDEX `idx_category`(`category` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '系统日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tags
-- ----------------------------
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标签ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `source_id` int UNSIGNED NULL DEFAULT NULL COMMENT '源记录ID（用于标识复制关系）',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标签名称',
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'URL别名',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '标签描述',
  `article_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联文章数',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间（软删除）',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_site_name`(`site_id` ASC, `name` ASC) USING BTREE,
  UNIQUE INDEX `uk_site_slug`(`site_id` ASC, `slug` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_source_id`(`source_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '标签表' ROW_FORMAT = Dynamic;



-- ----------------------------
-- Table structure for template_history
-- ----------------------------
DROP TABLE IF EXISTS `template_history`;
CREATE TABLE `template_history`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `theme_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '模板套装key',
  `file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '文件路径',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '文件名',
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '文件内容',
  `file_size` int NOT NULL DEFAULT 0 COMMENT '文件大小(字节)',
  `version` int NOT NULL DEFAULT 1 COMMENT '版本号',
  `change_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '修改描述',
  `user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '操作用户ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_theme_file`(`theme_key` ASC, `file_path` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '模板历史记录表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for template_packages
-- ----------------------------
DROP TABLE IF EXISTS `template_packages`;
CREATE TABLE `template_packages`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '模板包ID',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板包名称',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '唯一标识（如：modern_theme）',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '描述',
  `preview_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '预览图',
  `version` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0.0' COMMENT '版本号',
  `author` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '作者',
  `author_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '作者网站',
  `is_system` tinyint NOT NULL DEFAULT 0 COMMENT '是否系统内置（0否1是）',
  `is_global` tinyint NOT NULL DEFAULT 1 COMMENT '是否全局可用（0否1是）',
  `allowed_sites` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '允许使用的站点ID（JSON数组，仅当is_global=0时有效）',
  `config_schema` json NULL COMMENT '配置项定义（颜色、字体、布局等）',
  `default_config` json NULL COMMENT '默认配置值',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态（0禁用1启用）',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `install_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '安装时间',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_code`(`code` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_sort`(`sort` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '模板包表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of template_packages
-- ----------------------------
INSERT INTO `template_packages` VALUES (1, '默认模板包', 'default', 'CarefreeCMS系统默认模板包，包含基础的页面模板', NULL, '1.0.0', 'CarefreeCMS', NULL, 1, 1, NULL, NULL, NULL, 1, 0, '2025-11-16 23:11:52', '2025-11-16 23:11:52', '2025-11-24 19:00:00');
INSERT INTO `template_packages` VALUES (2, '官方模板包', 'official', '官方模板包', NULL, '1.0.0', 'carefreecms', NULL, 0, 1, NULL, NULL, '[]', 1, 1, '2025-11-17 01:46:04', '2025-11-17 01:46:04', '2025-11-24 19:00:00');

-- ----------------------------
-- Table structure for template_types
-- ----------------------------
DROP TABLE IF EXISTS `template_types`;
CREATE TABLE `template_types`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '类型名称',
  `code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '类型代码',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '类型描述',
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '图标',
  `file_naming` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '文件命名规则',
  `params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '支持的参数(JSON)',
  `template_vars` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '模板变量说明(JSON)',
  `example_code` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '示例代码',
  `is_system` tinyint(1) NULL DEFAULT 0 COMMENT '是否系统内置',
  `allow_multiple` tinyint(1) NULL DEFAULT 0 COMMENT '是否允许多个模板',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态：1启用 0禁用',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idx_code`(`code` ASC) USING BTREE,
  INDEX `idx_sort`(`sort` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '模板类型表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of template_types
-- ----------------------------
INSERT INTO `template_types` VALUES (1, '首页模板', 'index', '网站首页模板，用于展示网站主页内容', 'el-icon-house', 'index.html', '{\"site_id\":\"站点ID\",\"page\":\"页码\"}', '{\"articles\":\"文章列表\",\"categories\":\"分类列表\",\"sliders\":\"幻灯片\",\"hot_articles\":\"热门文章\",\"recommended\":\"推荐内容\"}', '{% extends \"layout.html\" %}\n{% block content %}\n    {carefree:slider position=\"index\" limit=\"5\" id=\"slide\"}\n    <!-- 幻灯片内容 -->\n    {/carefree:slider}\n\n    {carefree:article flag=\"recommend\" limit=\"6\" cache=\"600\" id=\"rec\"}\n    <!-- 推荐文章 -->\n    {/carefree:article}\n{% endblock %}', 1, 0, 0, 1, '2025-11-24 05:11:08', '2025-11-24 15:34:35');
INSERT INTO `template_types` VALUES (2, '分类模板', 'category', '分类列表页模板，用于展示某个分类下的文章列表', 'el-icon-folder', 'category.html 或 category_*.html', '{\"category_id\":\"分类ID\",\"page\":\"页码\",\"pagesize\":\"每页条数\"}', '{\"category\":\"当前分类信息\",\"articles\":\"文章列表\",\"subcategories\":\"子分类列表\",\"breadcrumb\":\"面包屑导航\",\"pager\":\"分页信息\"}', '{carefree:article typeid=\"$category.id\" page=\"$page\" pagesize=\"20\" id=\"article\"}\n    <article>\n        <h2>{$article.title}</h2>\n        <p>{$article.description}</p>\n    </article>\n{/carefree:article}', 1, 1, 2, 1, '2025-11-24 05:11:08', '2025-11-24 15:34:35');
INSERT INTO `template_types` VALUES (3, '文章模板', 'article', '文章详情页模板，用于展示文章具体内容', 'el-icon-document', 'article.html 或 article_*.html', '{\"article_id\":\"文章ID\",\"category_id\":\"分类ID\"}', '{\"article\":\"文章详细信息\",\"category\":\"所属分类\",\"tags\":\"文章标签\",\"prev_article\":\"上一篇文章\",\"next_article\":\"下一篇文章\",\"related\":\"相关文章\",\"comments\":\"评论列表\"}', '{carefree:arcinfo id=\"$article.id\" id=\"arc\"}\n    <h1>{$arc.title}</h1>\n    <div class=\"content\">{$arc.content|raw}</div>\n{/carefree:arcinfo}', 1, 1, 3, 1, '2025-11-24 05:11:08', '2025-11-24 15:34:35');
INSERT INTO `template_types` VALUES (4, '标签模板', 'tag', '标签页模板，用于展示标签相关的文章', 'el-icon-price-tag', 'tag.html', '{\"tag_id\":\"标签ID\",\"page\":\"页码\"}', '{\"tag\":\"标签信息\",\"articles\":\"相关文章列表\",\"related_tags\":\"相关标签\",\"pager\":\"分页信息\"}', '{carefree:article tag=\"$tag.id\" page=\"$page\" pagesize=\"20\" id=\"article\"}\n    <!-- 标签相关文章 -->\n{/carefree:article}', 1, 0, 4, 1, '2025-11-24 05:11:08', '2025-11-24 15:34:35');
INSERT INTO `template_types` VALUES (5, '搜索模板', 'search', '搜索结果页模板，用于展示搜索结果', 'el-icon-search', 'search.html', '{\"keyword\":\"搜索关键词\",\"page\":\"页码\",\"type\":\"搜索类型\"}', '{\"keyword\":\"搜索关键词\",\"results\":\"搜索结果列表\",\"total\":\"结果总数\",\"pager\":\"分页信息\",\"hot_searches\":\"热门搜索\"}', '{carefree:search keyword=\"$keyword\" page=\"$page\" pagesize=\"20\" id=\"result\"}\n    <!-- 搜索结果 -->\n{/carefree:search}', 1, 0, 5, 1, '2025-11-24 05:11:08', '2025-11-24 15:34:35');
INSERT INTO `template_types` VALUES (6, '单页模板', 'page', '单页面模板，用于关于我们、联系方式等独立页面', 'el-icon-document-copy', 'page.html 或 page_*.html', '{\"page_id\":\"页面ID\",\"slug\":\"页面别名\"}', '{\"page\":\"页面信息\",\"content\":\"页面内容\"}', '{carefree:pageinfo id=\"$page.id\" id=\"pagedata\"}\n    <h1>{$pagedata.title}</h1>\n    <div>{$pagedata.content|raw}</div>\n{/carefree:pageinfo}', 1, 1, 6, 1, '2025-11-24 05:11:08', '2025-11-24 15:34:35');
INSERT INTO `template_types` VALUES (7, '专题模板', 'topic', '专题页面模板，用于展示专题内容', 'el-icon-collection', 'topic.html 或 topic_*.html', '{\"topic_id\":\"专题ID\",\"page\":\"页码\"}', '{\"topic\":\"专题信息\",\"articles\":\"专题文章\",\"pager\":\"分页信息\"}', '{carefree:topic id=\"$topic.id\" id=\"topicinfo\"}\n    <h1>{$topicinfo.name}</h1>\n    {carefree:article topic=\"$topic.id\" limit=\"20\" id=\"article\"}\n        <!-- 专题文章 -->\n    {/carefree:article}\n{/carefree:topic}', 0, 1, 7, 1, '2025-11-24 05:11:08', '2025-11-24 15:34:35');
INSERT INTO `template_types` VALUES (8, '404错误页', 'error404', '404错误页面模板', 'el-icon-warning', '404.html', '{\"url\":\"请求的URL\"}', '{\"error_message\":\"错误信息\",\"suggestions\":\"建议内容\"}', '<div class=\"error-404\">\n    <h1>404</h1>\n    <p>页面未找到</p>\n    <a href=\"{{ base_url }}/\">返回首页</a>\n</div>', 0, 0, 8, 1, '2025-11-24 05:11:08', '2025-11-24 15:34:35');
INSERT INTO `template_types` VALUES (9, '站点地图', 'sitemap', 'HTML格式的站点地图模板', 'el-icon-map-location', 'sitemap.html', '{}', '{\"categories\":\"所有分类\",\"pages\":\"所有页面\",\"recent_articles\":\"最新文章\"}', '{carefree:category limit=\"100\" id=\"cat\"}\n    <a href=\"{{ base_url }}/category/{$cat.id}.html\">{$cat.name}</a>\n{/carefree:category}', 0, 0, 9, 1, '2025-11-24 05:11:08', '2025-11-24 15:34:35');
INSERT INTO `template_types` VALUES (10, '模板框架', 'layout', '页面布局框架模板，定义网站的整体结构和公共部分', 'el-icon-grid', 'layout.html', '{\"site_id\":\"站点ID\"}', '{\"site\":\"站点信息\",\"nav_categories\":\"导航分类\",\"content\":\"页面主内容区域\"}', '<!DOCTYPE html>\n<html>\n<head>\n    <title>{% block title %}{{ site.name }}{% endblock %}</title>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    {% block head %}{% endblock %}\n</head>\n<body>\n    <header>\n        <!-- 网站头部 -->\n        {carefree:include file=\"header.html\"}\n    </header>\n\n    <main>\n        {% block content %}\n        <!-- 页面主要内容 -->\n        {% endblock %}\n    </main>\n\n    <footer>\n        <!-- 网站底部 -->\n        {carefree:include file=\"footer.html\"}\n    </footer>\n    {% block scripts %}{% endblock %}\n</body>\n</html>', 1, 0, 0, 1, '2025-11-24 20:06:09', '2025-11-24 20:06:09');
INSERT INTO `template_types` VALUES (11, '头部模板', 'header', '网站头部区域模板，包含导航菜单、Logo等', 'el-icon-top', 'header.html', NULL, NULL, NULL, 0, 0, 10, 1, '2025-11-24 20:11:13', '2025-11-24 20:11:13');
INSERT INTO `template_types` VALUES (12, '底部模板', 'footer', '网站底部区域模板，包含版权信息、友情链接等', 'el-icon-bottom', 'footer.html', NULL, NULL, NULL, 0, 0, 11, 1, '2025-11-24 20:12:38', '2025-11-24 20:12:38');
INSERT INTO `template_types` VALUES (13, '侧边栏模板', 'sidebar', '网站侧边栏模板，包含小工具、热门文章等', 'el-icon-menu', 'sidebar.html', NULL, NULL, NULL, 0, 0, 12, 1, '2025-11-24 20:22:06', '2025-11-24 20:22:06');

-- ----------------------------
-- Table structure for templates
-- ----------------------------
DROP TABLE IF EXISTS `templates`;
CREATE TABLE `templates`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '模板ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '站点ID（废弃，改用package_id）',
  `package_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '所属模板包ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板名称',
  `template_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板标识',
  `template_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '模板类型（index/category/article/page/tag/search等）',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '模板描述',
  `preview_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '预览图',
  `template_path` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板路径',
  `variables` json NULL COMMENT '模板变量定义',
  `config_schema` json NULL COMMENT '模板配置项定义',
  `is_default` tinyint NOT NULL DEFAULT 0 COMMENT '是否默认模板（全局级别，废弃）',
  `is_package_default` tinyint NOT NULL DEFAULT 0 COMMENT '是否包内默认模板（0否1是）',
  `parent_template_id` int UNSIGNED NULL DEFAULT NULL COMMENT '继承自哪个模板（模板继承功能）',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_template_key`(`template_key` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_package_id`(`package_id` ASC) USING BTREE,
  INDEX `idx_template_type`(`template_type` ASC) USING BTREE,
  INDEX `idx_parent_template_id`(`parent_template_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 29 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '模板管理表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of templates
-- ----------------------------
INSERT INTO `templates` VALUES (1, 0, 1, '布局模板', 'default', 'layout', '定义网站整体布局结构', NULL, 'default/layout.html', NULL, NULL, 0, 1, NULL, 1, '2025-10-12 02:12:51', '2025-11-24 20:58:32');
INSERT INTO `templates` VALUES (2, 0, 1, '首页模板', 'index', 'index', '网站首页默认模板', NULL, 'default/index.html', NULL, NULL, 0, 1, NULL, 1, '2025-10-15 21:45:51', '2025-11-24 20:00:37');
INSERT INTO `templates` VALUES (3, 0, 1, '分类页模板', 'category', 'category', '分类列表页默认模板', NULL, 'default/category.html', NULL, NULL, 0, 1, NULL, 1, '2025-10-15 21:45:51', '2025-11-24 20:00:38');
INSERT INTO `templates` VALUES (4, 0, 1, '单页模板', 'page', 'page', '单页面默认模板', NULL, 'default/page.html', NULL, NULL, 0, 1, NULL, 1, '2025-10-15 21:45:51', '2025-11-24 20:00:39');
INSERT INTO `templates` VALUES (5, 0, 1, '文章列表模板', 'articles', 'articles', '文章列表页模板', NULL, 'default/articles.html', NULL, NULL, 0, 1, NULL, 1, '2025-10-15 21:45:51', '2025-11-24 20:00:42');
INSERT INTO `templates` VALUES (6, 0, 1, '文章详情模板', 'article', 'article', '文章详情页模板', NULL, 'default/article.html', NULL, NULL, 0, 1, NULL, 1, '2025-10-15 21:45:51', '2025-11-24 20:00:43');
INSERT INTO `templates` VALUES (7, 0, 1, '标签页模板', 'tag', 'tag', '标签页模板', NULL, 'default/tag.html', NULL, NULL, 0, 1, NULL, 1, '2025-10-15 21:45:51', '2025-11-24 20:00:44');
INSERT INTO `templates` VALUES (8, 0, 1, '搜索页模板', 'search', 'search', '搜索结果页面模板', NULL, 'default/search.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-17 02:05:53', '2025-11-24 20:00:45');
INSERT INTO `templates` VALUES (9, 0, 1, '专题页模板', 'topic', 'topic', '专题文章列表页面模板', NULL, 'default/topic.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-17 02:05:53', '2025-11-24 20:00:46');
INSERT INTO `templates` VALUES (10, 0, 1, '归档页模板', 'archive', 'archive', '文章归档页面模板', NULL, 'default/archive.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-17 02:05:53', '2025-11-24 20:00:47');
INSERT INTO `templates` VALUES (11, 0, 1, '404页面模板', '404', '404', '页面未找到提示模板', NULL, 'default/404.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-17 02:05:53', '2025-11-24 20:00:48');
INSERT INTO `templates` VALUES (12, 0, 1, '侧边栏模板', 'sidebar', 'sidebar', '侧边栏组件模板', NULL, 'default/sidebar.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-17 02:05:53', '2025-11-24 20:00:49');
INSERT INTO `templates` VALUES (21, 0, 2, '官方首页', 'official_index', 'index', NULL, NULL, 'official/index.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-23 12:39:21', '2025-11-23 12:39:21');
INSERT INTO `templates` VALUES (22, 0, 2, '官方分类页', 'official_category', 'category', NULL, NULL, 'official/category.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-23 12:39:21', '2025-11-23 12:39:21');
INSERT INTO `templates` VALUES (23, 0, 2, '官方文章页', 'official_article', 'article', NULL, NULL, 'official/article.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-23 12:39:21', '2025-11-23 12:39:21');
INSERT INTO `templates` VALUES (24, 0, 2, '官方列表页', 'official_articles', 'articles', NULL, NULL, 'official/articles.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-23 12:39:21', '2025-11-23 12:39:21');
INSERT INTO `templates` VALUES (25, 0, 2, '官方标签页', 'official_tag', 'tag', NULL, NULL, 'official/tag.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-23 12:39:21', '2025-11-23 12:39:21');
INSERT INTO `templates` VALUES (26, 0, 2, '官方专题页', 'official_topic', 'topic', NULL, NULL, 'official/topic.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-23 12:39:21', '2025-11-23 12:39:21');
INSERT INTO `templates` VALUES (27, 0, 2, '官方单页', 'official_page', 'page', NULL, NULL, 'official/page.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-23 12:39:21', '2025-11-23 12:39:21');
INSERT INTO `templates` VALUES (28, 0, 2, '官方布局', 'official_layout', 'layout', NULL, NULL, 'official/layout.html', NULL, NULL, 0, 1, NULL, 1, '2025-11-23 12:39:21', '2025-11-23 12:39:21');


-- ----------------------------
-- Table structure for topic_articles
-- ----------------------------
DROP TABLE IF EXISTS `topic_articles`;
CREATE TABLE `topic_articles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `topic_id` int UNSIGNED NOT NULL COMMENT '专题ID',
  `article_id` int UNSIGNED NOT NULL COMMENT '文章ID',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序权重，数值越大越靠前',
  `create_time` datetime NULL DEFAULT NULL COMMENT '关联创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unique_topic_article`(`topic_id` ASC, `article_id` ASC) USING BTREE,
  INDEX `idx_topic_id`(`topic_id` ASC) USING BTREE,
  INDEX `idx_article_id`(`article_id` ASC) USING BTREE,
  INDEX `idx_sort`(`sort` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '专题文章关联表' ROW_FORMAT = Dynamic;



-- ----------------------------
-- Table structure for topics
-- ----------------------------
DROP TABLE IF EXISTS `topics`;
CREATE TABLE `topics`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '专题ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `source_id` int UNSIGNED NULL DEFAULT NULL COMMENT '源记录ID（用于标识复制关系）',
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '专题名称',
  `slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'URL别名',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '专题描述',
  `cover_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '专题封面图',
  `template` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'topic_default' COMMENT '专题模板',
  `seo_title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'SEO标题',
  `seo_keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'SEO关键词',
  `seo_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'SEO描述',
  `is_recommended` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否推荐：0=否，1=是',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `view_count` int NOT NULL DEFAULT 0 COMMENT '浏览次数',
  `article_count` int NOT NULL DEFAULT 0 COMMENT '文章数量',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  `deleted_at` datetime NULL DEFAULT NULL COMMENT '删除时间（软删除）',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_site_slug`(`site_id` ASC, `slug` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_recommended`(`is_recommended` ASC) USING BTREE,
  INDEX `idx_sort`(`sort` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE,
  INDEX `idx_source_id`(`source_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '专题表' ROW_FORMAT = Dynamic;



-- ----------------------------
-- Table structure for upload_chunks
-- ----------------------------
DROP TABLE IF EXISTS `upload_chunks`;
CREATE TABLE `upload_chunks`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `session_id` int UNSIGNED NOT NULL COMMENT '会话ID',
  `chunk_index` int NOT NULL COMMENT '分片序号（从0开始）',
  `chunk_size` int NOT NULL COMMENT '分片大小',
  `chunk_hash` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '分片哈希',
  `file_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '分片文件路径',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'uploaded' COMMENT '状态：uploaded/verified/failed',
  `uploaded_at` datetime NULL DEFAULT NULL COMMENT '上传时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_session_chunk`(`session_id` ASC, `chunk_index` ASC) USING BTREE,
  INDEX `idx_session_id`(`session_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '分片记录表' ROW_FORMAT = Dynamic;



-- ----------------------------
-- Table structure for user_actions
-- ----------------------------
DROP TABLE IF EXISTS `user_actions`;
CREATE TABLE `user_actions`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `target_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '目标类型：article, comment, user',
  `target_id` int UNSIGNED NOT NULL COMMENT '目标ID',
  `action_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '行为类型：like点赞, dislike踩, favorite收藏, follow关注',
  `site_id` int UNSIGNED NULL DEFAULT 1 COMMENT '站点ID',
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_user_action`(`user_id` ASC, `target_type` ASC, `target_id` ASC, `action_type` ASC) USING BTREE,
  INDEX `idx_user`(`user_id` ASC) USING BTREE,
  INDEX `idx_target`(`target_type` ASC, `target_id` ASC) USING BTREE,
  INDEX `idx_action`(`action_type` ASC) USING BTREE,
  INDEX `idx_site`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '用户行为表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_actions
-- ----------------------------

-- ----------------------------
-- Table structure for user_point_logs
-- ----------------------------
DROP TABLE IF EXISTS `user_point_logs`;
CREATE TABLE `user_point_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `points` int NOT NULL COMMENT '积分变动（正数为增加，负数为减少）',
  `balance` int NOT NULL COMMENT '变动后余额',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '类型：register=注册，login=登录，post=发帖，comment=评论，like=点赞，reward=奖励，consume=消费',
  `description` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '描述',
  `related_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '关联类型：article/comment等',
  `related_id` int UNSIGNED NULL DEFAULT NULL COMMENT '关联ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_type`(`type` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户积分记录表' ROW_FORMAT = Dynamic;



-- ----------------------------
-- Table structure for user_read_history
-- ----------------------------
DROP TABLE IF EXISTS `user_read_history`;
CREATE TABLE `user_read_history`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '历史ID',
  `site_id` int UNSIGNED NOT NULL DEFAULT 1 COMMENT '站点ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `article_id` int UNSIGNED NOT NULL COMMENT '文章ID',
  `read_progress` int NOT NULL DEFAULT 0 COMMENT '阅读进度（百分比）',
  `read_time` int NOT NULL DEFAULT 0 COMMENT '阅读时长（秒）',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_user_article`(`user_id` ASC, `article_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_article_id`(`article_id` ASC) USING BTREE,
  INDEX `idx_update_time`(`update_time` ASC) USING BTREE,
  INDEX `idx_site_id`(`site_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户阅读历史表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_read_history
-- ----------------------------

-- ----------------------------
-- Table structure for user_reputation
-- ----------------------------
DROP TABLE IF EXISTS `user_reputation`;
CREATE TABLE `user_reputation`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `score` int NOT NULL DEFAULT 100 COMMENT '信誉分(0-100)',
  `violation_count` int NOT NULL DEFAULT 0 COMMENT '违规次数',
  `approved_count` int NOT NULL DEFAULT 0 COMMENT '通过审核次数',
  `rejected_count` int NOT NULL DEFAULT 0 COMMENT '拒绝次数',
  `last_violation_at` timestamp NULL DEFAULT NULL COMMENT '最后违规时间',
  `auto_approve` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否自动通过(1-是 0-否)',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_user`(`user_id` ASC) USING BTREE,
  INDEX `idx_score`(`score` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '用户信誉度表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_reputation
-- ----------------------------
INSERT INTO `user_reputation` VALUES (1, 1, 95, 1, 0, 0, '2025-11-29 05:17:46', 0, '2025-11-29 05:17:46', '2025-11-29 05:17:46');

-- ----------------------------
-- Table structure for verify_codes
-- ----------------------------
DROP TABLE IF EXISTS `verify_codes`;
CREATE TABLE `verify_codes`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '类型（phone/email）',
  `account` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '账号（手机号或邮箱）',
  `code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '验证码',
  `scene` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '场景（register/login/reset/bind）',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态（0未使用/1已使用/2已过期）',
  `expire_time` datetime NOT NULL COMMENT '过期时间',
  `use_time` datetime NULL DEFAULT NULL COMMENT '使用时间',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'IP地址',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_account_code`(`account` ASC, `code` ASC) USING BTREE,
  INDEX `idx_expire_time`(`expire_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '验证码表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of verify_codes
-- ----------------------------
INSERT INTO `verify_codes` VALUES (1, 'phone', '13800138000', '171693', 'register', 2, '2025-11-01 13:13:53', NULL, '0.0.0.0', '2025-11-01 13:08:54');
INSERT INTO `verify_codes` VALUES (2, 'phone', '13800138000', '857742', 'register', 2, '2025-11-01 13:14:50', NULL, '0.0.0.0', '2025-11-01 13:09:50');
INSERT INTO `verify_codes` VALUES (3, 'phone', '13900139000', '049052', 'login', 0, '2025-11-01 13:14:51', NULL, '0.0.0.0', '2025-11-01 13:09:52');
INSERT INTO `verify_codes` VALUES (4, 'phone', '13800138000', '372199', 'register', 0, '2025-11-01 13:15:29', NULL, '0.0.0.0', '2025-11-01 13:10:30');

-- ----------------------------
-- Table structure for video_posters
-- ----------------------------
DROP TABLE IF EXISTS `video_posters`;
CREATE TABLE `video_posters`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `media_id` int UNSIGNED NOT NULL COMMENT '视频媒体ID',
  `poster_media_id` int UNSIGNED NOT NULL COMMENT '海报媒体ID',
  `time_in_seconds` int NOT NULL DEFAULT 0 COMMENT '截图时间点（秒）',
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否默认海报',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_media_id`(`media_id` ASC) USING BTREE,
  INDEX `idx_is_default`(`is_default` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '视频海报记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of video_posters
-- ----------------------------

-- ----------------------------
-- Table structure for video_transcode_records
-- ----------------------------
DROP TABLE IF EXISTS `video_transcode_records`;
CREATE TABLE `video_transcode_records`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `media_id` int UNSIGNED NOT NULL COMMENT '媒体ID',
  `original_file_id` int UNSIGNED NOT NULL COMMENT '原始文件ID',
  `result_file_id` int UNSIGNED NULL DEFAULT NULL COMMENT '结果文件ID',
  `format` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '输出格式 (mp4/webm/avi等)',
  `quality` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'medium' COMMENT '质量 (low/medium/high)',
  `resolution` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '分辨率 (1920x1080/1280x720等)',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'pending' COMMENT '状态：pending/processing/completed/failed',
  `progress` int NOT NULL DEFAULT 0 COMMENT '进度（0-100）',
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '错误信息',
  `started_at` datetime NULL DEFAULT NULL COMMENT '开始时间',
  `completed_at` datetime NULL DEFAULT NULL COMMENT '完成时间',
  `created_at` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_media_id`(`media_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_created_at`(`created_at` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '视频转码记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of video_transcode_records
-- ----------------------------

-- ----------------------------
-- View structure for v_ai_model_capabilities
-- ----------------------------
DROP VIEW IF EXISTS `v_ai_model_capabilities`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_ai_model_capabilities` AS select `m`.`id` AS `id`,`p`.`name` AS `provider_name`,`m`.`model_code` AS `model_code`,`m`.`model_name` AS `model_name`,`m`.`description` AS `description`,`m`.`context_window` AS `context_window`,`m`.`max_output_tokens` AS `max_output_tokens`,((((((((((coalesce(`m`.`supports_functions`,0) + coalesce(`m`.`supports_image_input`,0)) + coalesce(`m`.`supports_image_generation`,0)) + coalesce(`m`.`supports_audio_input`,0)) + coalesce(`m`.`supports_audio_output`,0)) + coalesce(`m`.`supports_video_input`,0)) + coalesce(`m`.`supports_document_parsing`,0)) + coalesce(`m`.`supports_code_generation`,0)) + coalesce(`m`.`supports_code_interpreter`,0)) + coalesce(`m`.`supports_realtime_voice`,0)) + coalesce(`m`.`supports_web_search`,0)) AS `total_capabilities`,`m`.`supports_functions` AS `func`,`m`.`supports_image_input` AS `img_in`,`m`.`supports_image_generation` AS `img_gen`,`m`.`supports_audio_input` AS `aud_in`,`m`.`supports_audio_output` AS `aud_out`,`m`.`supports_video_input` AS `vid_in`,`m`.`supports_document_parsing` AS `doc`,`m`.`supports_code_generation` AS `code_gen`,`m`.`supports_code_interpreter` AS `code_exec`,`m`.`supports_realtime_voice` AS `realtime`,`m`.`supports_streaming` AS `stream`,`m`.`supports_web_search` AS `web`,`m`.`multimodal_capabilities` AS `multimodal_capabilities`,`m`.`status` AS `status` from (`ai_models` `m` join `ai_providers` `p` on((`m`.`provider_id` = `p`.`id`))) where (`m`.`status` = 1) order by ((((((((((coalesce(`m`.`supports_functions`,0) + coalesce(`m`.`supports_image_input`,0)) + coalesce(`m`.`supports_image_generation`,0)) + coalesce(`m`.`supports_audio_input`,0)) + coalesce(`m`.`supports_audio_output`,0)) + coalesce(`m`.`supports_video_input`,0)) + coalesce(`m`.`supports_document_parsing`,0)) + coalesce(`m`.`supports_code_generation`,0)) + coalesce(`m`.`supports_code_interpreter`,0)) + coalesce(`m`.`supports_realtime_voice`,0)) + coalesce(`m`.`supports_web_search`,0)) desc,`m`.`context_window` desc;

-- ----------------------------
-- View structure for v_top_multimodal_models
-- ----------------------------
DROP VIEW IF EXISTS `v_top_multimodal_models`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `v_top_multimodal_models` AS select `v_ai_model_capabilities`.`provider_name` AS `provider_name`,`v_ai_model_capabilities`.`model_name` AS `model_name`,`v_ai_model_capabilities`.`context_window` AS `context_window`,`v_ai_model_capabilities`.`total_capabilities` AS `total_capabilities`,`v_ai_model_capabilities`.`img_in` AS `img_in`,`v_ai_model_capabilities`.`aud_in` AS `aud_in`,`v_ai_model_capabilities`.`vid_in` AS `vid_in`,`v_ai_model_capabilities`.`doc` AS `doc`,`v_ai_model_capabilities`.`code_gen` AS `code_gen`,`v_ai_model_capabilities`.`realtime` AS `realtime`,`v_ai_model_capabilities`.`web` AS `web`,`v_ai_model_capabilities`.`multimodal_capabilities` AS `multimodal_capabilities` from `v_ai_model_capabilities` where (`v_ai_model_capabilities`.`total_capabilities` >= 5) order by `v_ai_model_capabilities`.`total_capabilities` desc,`v_ai_model_capabilities`.`context_window` desc;

-- ----------------------------
-- Function structure for get_model_capability_tags
-- ----------------------------
DROP FUNCTION IF EXISTS `get_model_capability_tags`;
delimiter ;;
CREATE FUNCTION `get_model_capability_tags`(model_id INT)
 RETURNS varchar(500) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci
  DETERMINISTIC
BEGIN
    DECLARE tags VARCHAR(500) DEFAULT '';
    DECLARE has_img, has_aud, has_vid, has_doc, has_code, has_web TINYINT(1);

    SELECT
        supports_image_input,
        supports_audio_input,
        supports_video_input,
        supports_document_parsing,
        supports_code_generation,
        supports_web_search
    INTO has_img, has_aud, has_vid, has_doc, has_code, has_web
    FROM ai_models
    WHERE id = model_id;

    IF has_img THEN SET tags = CONCAT(tags, '[图像] '); END IF;
    IF has_aud THEN SET tags = CONCAT(tags, '[音频] '); END IF;
    IF has_vid THEN SET tags = CONCAT(tags, '[视频] '); END IF;
    IF has_doc THEN SET tags = CONCAT(tags, '[文档] '); END IF;
    IF has_code THEN SET tags = CONCAT(tags, '[代码] '); END IF;
    IF has_web THEN SET tags = CONCAT(tags, '[联网] '); END IF;

    RETURN TRIM(tags);
END
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;
