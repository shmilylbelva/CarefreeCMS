-- ============================================
-- CMS 内容管理系统数据库设计
-- 数据库: cms_database
-- MySQL 8.0+
-- ============================================

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
-- Table structure for ad_positions
-- ----------------------------
DROP TABLE IF EXISTS `ad_positions`;
CREATE TABLE `ad_positions`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '广告位ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '广告位名称',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '广告位代码（唯一标识）',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '广告位描述',
  `width` int NULL DEFAULT NULL COMMENT '宽度（像素）',
  `height` int NULL DEFAULT NULL COMMENT '高度（像素）',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_code`(`code` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '广告位表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ad_positions
-- ----------------------------
INSERT INTO `ad_positions` VALUES (1, '首页顶部横幅', 'home_top_banner', '首页顶部横幅广告位', 1200, 120, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34');
INSERT INTO `ad_positions` VALUES (2, '首页右侧', 'home_right_sidebar', '首页右侧边栏广告位', 300, 250, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34');
INSERT INTO `ad_positions` VALUES (3, '文章页顶部', 'article_top', '文章页顶部广告位', 728, 90, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34');
INSERT INTO `ad_positions` VALUES (4, '文章页底部', 'article_bottom', '文章页底部广告位', 728, 90, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34');
INSERT INTO `ad_positions` VALUES (5, '全站浮动', 'site_float', '全站浮动广告位', 200, 200, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34');

-- ----------------------------
-- Table structure for admin_logs
-- ----------------------------
DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '用户ID',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '用户名',
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作动作',
  `module` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作模块',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '操作描述',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'IP地址',
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '用户代理',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '操作日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_logs
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
INSERT INTO `admin_roles` VALUES (1, '超级管理员', '拥有所有权限', '[\"*\"]', 1, 1, '2025-10-12 02:12:51', '2025-10-12 02:12:51');
INSERT INTO `admin_roles` VALUES (2, '管理员', '拥有大部分管理权限', '[\"article.*\", \"category.*\", \"tag.*\", \"page.*\", \"comment.*\", \"media.*\"]', 2, 1, '2025-10-12 02:12:51', '2025-10-12 02:12:51');
INSERT INTO `admin_roles` VALUES (3, '编辑', '可以管理文章、分类、标签', '[\"article.*\", \"category.view\", \"tag.*\", \"comment.*\", \"media.*\"]', 3, 1, '2025-10-12 02:12:51', '2025-10-12 02:12:51');
INSERT INTO `admin_roles` VALUES (4, '作者', '只能管理自己的文章', '[\"article.create\", \"article.edit_own\", \"media.upload\"]', 4, 1, '2025-10-12 02:12:51', '2025-10-12 02:12:51');

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
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理员用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_users
-- ----------------------------
INSERT INTO `admin_users` VALUES (1, 'admin', '$2y$10$xpuuHKDpthvJEOaVq9AOv.03eFimQqh4yHkpYIzdC55H6gm8.9QlS', '系统管理员', 'sinma@sinma.net', '13131313131', 'uploads/avatar/2025/10/14/avatar_1_20251014035910.png', 1, 1, '2025-11-06 21:02:28', '127.0.0.1', '2025-10-12 02:12:51', '2025-11-06 21:02:29');

-- ----------------------------
-- Table structure for ads
-- ----------------------------
DROP TABLE IF EXISTS `ads`;
CREATE TABLE `ads`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '广告ID',
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
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '广告表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ads
-- ----------------------------
INSERT INTO `ads` VALUES (1, 1, '春季促销活动', 'image', 'https://www.carefreecms.com/uploads/2025/10/21/20251021002334_68f66206c30e6.jpg', 'https://example.com/sale', '[]', '2025-02-28 08:00:00', '2027-06-10 08:00:00', 1, 1, 0, 0, '2025-10-19 00:58:34', '2025-10-21 00:23:37', NULL);
INSERT INTO `ads` VALUES (2, 2, '产品推荐', 'image', 'https://www.carefreecms.com/uploads/2025/10/21/20251021002344_68f6621032001.jpg', 'https://example.com/products', '[]', NULL, NULL, 1, 2, 0, 0, '2025-10-19 00:58:34', '2025-10-21 00:23:46', NULL);

-- ----------------------------
-- Table structure for article_categories
-- ----------------------------
DROP TABLE IF EXISTS `article_categories`;
CREATE TABLE `article_categories`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `article_id` int UNSIGNED NOT NULL COMMENT '文章ID',
  `category_id` int UNSIGNED NOT NULL COMMENT '分类ID',
  `is_main` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否为主分类：1=主分类，0=副分类',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `unique_article_category`(`article_id` ASC, `category_id` ASC) USING BTREE,
  INDEX `idx_article_id`(`article_id` ASC) USING BTREE,
  INDEX `idx_category_id`(`category_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 42 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '文章分类关联表（支持主分类+副分类）' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of article_categories
-- ----------------------------
INSERT INTO `article_categories` VALUES (14, 3, 1, 1, '2025-10-13 21:11:03');
INSERT INTO `article_categories` VALUES (15, 3, 6, 0, '2025-10-13 21:11:03');
INSERT INTO `article_categories` VALUES (16, 3, 5, 0, '2025-10-13 21:11:03');
INSERT INTO `article_categories` VALUES (17, 3, 4, 0, '2025-10-13 21:11:03');
INSERT INTO `article_categories` VALUES (18, 3, 3, 0, '2025-10-13 21:11:03');
INSERT INTO `article_categories` VALUES (19, 3, 2, 0, '2025-10-13 21:11:03');
INSERT INTO `article_categories` VALUES (32, 1, 3, 1, '2025-10-23 11:32:25');
INSERT INTO `article_categories` VALUES (33, 1, 4, 0, '2025-10-23 11:32:25');
INSERT INTO `article_categories` VALUES (39, 2, 5, 1, '2025-11-06 15:41:01');
INSERT INTO `article_categories` VALUES (40, 4, 5, 1, '2025-11-06 15:42:11');
INSERT INTO `article_categories` VALUES (41, 4, 1, 0, '2025-11-06 15:42:11');

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
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '文章属性表' ROW_FORMAT = Dynamic;

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
-- Table structure for article_tags
-- ----------------------------
DROP TABLE IF EXISTS `article_tags`;
CREATE TABLE `article_tags`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `article_id` int UNSIGNED NOT NULL COMMENT '文章ID',
  `tag_id` int UNSIGNED NOT NULL COMMENT '标签ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_article_tag`(`article_id` ASC, `tag_id` ASC) USING BTREE,
  INDEX `idx_tag_id`(`tag_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '文章标签关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of article_tags
-- ----------------------------
INSERT INTO `article_tags` VALUES (12, 3, 1, '2025-10-13 21:11:03');
INSERT INTO `article_tags` VALUES (20, 1, 1, '2025-10-23 11:32:26');

-- ----------------------------
-- Table structure for article_versions
-- ----------------------------
DROP TABLE IF EXISTS `article_versions`;
CREATE TABLE `article_versions`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '版本ID',
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
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '文章版本表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of article_versions
-- ----------------------------
INSERT INTO `article_versions` VALUES (1, 1, 1, '逍遥内容管理系统简介', NULL, '逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。 核心特性 - 🎨 **模板套装系统** - 支持多套模板自由切换，快速定制网站风格 - ⚡ **静态页面生成** - 一键生成纯静态HTML页面，访问速度快，SEO友好 - 📝 **文章管理** - 支持富文本编辑、草稿保存...', '<p>逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。</p>\n<p>&nbsp;</p>\n<div>\n<div><span style=\"font-size: 12pt;\"><strong>核心特性</strong></span></div>\n<br>\n<div>- 🎨 **模板套装系统** - 支持多套模板自由切换，快速定制网站风格</div>\n<div>- ⚡ **静态页面生成** - 一键生成纯静态HTML页面，访问速度快，SEO友好</div>\n<div>- 📝 **文章管理** - 支持富文本编辑、草稿保存、文章属性标记、自动提取SEO</div>\n<div>- 📂 **分类管理** - 树形结构分类，支持自定义模板</div>\n<div>- 🏷️ **标签系统** - 灵活的标签体系，方便内容组织</div>\n<div>- 📄 **单页管理** - 独立页面管理，支持封面图和SEO自动提取</div>\n<div>- 🖼️ **媒体库** - 统一媒体文件管理，支持按类型和日期查询</div>\n<div>- 🔐 **权限管理** - 基于角色的访问控制（RBAC）</div>\n<div>- 👥 **用户管理** - 多用户系统，支持用户角色分配</div>\n<div>- 🔍 **SEO优化** - 自动提取TDK、Sitemap生成</div>\n<div>- 📊 **操作日志** - 详细的用户操作审计记录，支持批量删除</div>\n<div>- 🎨 **现代化UI** - 基于 Element Plus的美观界面</div>\n<div>&nbsp;</div>\n<div>\n<div>后端采用技术：</div>\n<div>- PHP 8.2+</div>\n<div>- ThinkPHP 8.0</div>\n<div>- MySQL 8.0</div>\n<div>- JWT 认证</div>\n<div>- ThinkORM</div>\n<br>\n<div>前端采用技术：</div>\n<div>- Vue 3 (Composition API)</div>\n<div>- Vite 7</div>\n<div>- Element Plus</div>\n<div>- Vue Router 4</div>\n<div>- Pinia</div>\n<div>- Axios</div>\n<div>- TinyMCE (富文本编辑器)</div>\n<br>\n<div>后端环境要求</div>\n<br>\n<div>- PHP &gt;= 8.0</div>\n<div>- MySQL &gt;= 5.7</div>\n<div>- Composer</div>\n<div>&nbsp;</div>\n<div>前端环境要求</div>\n<div>- Node.js &gt;= 16.0</div>\n<div>- npm 或 yarn</div>\n<br>\n<div>&nbsp;<span style=\"font-size: 14pt;\">✨ 核心功能模块</span></div>\n<br>\n<div><strong>1. 文章管理</strong></div>\n<div>- 文章的增删改查</div>\n<div>- 文章分类、标签管理</div>\n<div>- 文章置顶、推荐、热门标记</div>\n<div>- 富文本编辑器</div>\n<div>- 图片上传和管理</div>\n<div>- 文章搜索和筛选</div>\n<div>- SEO设置</div>\n<br>\n<div><strong>&nbsp;2. 分类管理</strong></div>\n<div>- 多级分类支持</div>\n<div>- 分类排序</div>\n<div>- 分类SEO设置</div>\n<br>\n<div><strong>3. 标签管理</strong></div>\n<div>- 标签增删改查</div>\n<div>- 标签关联统计</div>\n<br>\n<div><strong>4. 页面管理</strong></div>\n<div>- 单页面管理（关于我们、联系我们等）</div>\n<div>- 自定义模板选择</div>\n<br>\n<div><strong>5. 用户管理（多角色）</strong></div>\n<div>- **超级管理员**: 拥有所有权限</div>\n<div>- **管理员**: 拥有大部分管理权限</div>\n<div>- **编辑**: 可以管理文章、分类、标签</div>\n<div>- **作者**: 只能管理自己的文章</div>\n<br>\n<div><strong>6. 评论管理</strong></div>\n<div>- 评论审核</div>\n<div>- 评论回复</div>\n<div>- 评论删除</div>\n<br>\n<div><strong>7. 媒体库</strong></div>\n<div>- 图片、文件上传</div>\n<div>- 媒体文件管理</div>\n<div>- 多种存储方式支持</div>\n<br>\n<div><strong>8. SEO设置</strong></div>\n<div>- 每篇文章独立SEO设置</div>\n<div>- 全站SEO配置</div>\n<br>\n<div><strong>9. 站点配置</strong></div>\n<div>- 网站基础信息</div>\n<div>- 上传配置</div>\n<div>- 模板配置</div>\n<br>\n<div><strong>10. 模板管理</strong></div>\n<div>- 多套模板支持</div>\n<div>- 模板切换</div>\n<br>\n<div><strong>11. 静态页面生成</strong></div>\n<div>- **手动生成**: 后台按钮点击生成</div>\n<div>- **自动生成**: 文章发布/更新时自动生成</div>\n<div>- **定时生成**: 定时任务批量生成</div>\n<div>- **生成范围**: 首页、列表页、详情页、栏目页、标签聚合页</div>\n<div>- **生成日志**: 记录每次生成的详细信息</div>\n</div>\n</div>', NULL, NULL, 3, 1, '{\"\\u6d4b\\u8bd5\\u6807\\u7b7e1\":1}', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-13 07:18:59', '测试文章标题1', '逍遥内容管理系统简介,采用前后端分离架构,支持静态页面生成,适用于个人博客,企业网站,核心特性,**模', '逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。 核心特性 - 🎨 **模板套装系统** - 支持多套模板自由切换，快速定制网站风格 - ⚡ **静态页面生成** - 一键生...', 0, 1, 'hcabp', '', 1, '2025-10-21 08:39:11');
INSERT INTO `article_versions` VALUES (2, 1, 2, '逍遥内容管理系统简介', NULL, '逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。 ', '<p>逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。</p>\n<p>&nbsp;</p>\n<div>\n<div><span style=\"font-size: 12pt;\"><strong>核心特性</strong></span></div>\n<br>\n<div>- 🎨 **模板套装系统** - 支持多套模板自由切换，快速定制网站风格</div>\n<div>- ⚡ **静态页面生成** - 一键生成纯静态HTML页面，访问速度快，SEO友好</div>\n<div>- 📝 **文章管理** - 支持富文本编辑、草稿保存、文章属性标记、自动提取SEO</div>\n<div>- 📂 **分类管理** - 树形结构分类，支持自定义模板</div>\n<div>- 🏷️ **标签系统** - 灵活的标签体系，方便内容组织</div>\n<div>- 📄 **单页管理** - 独立页面管理，支持封面图和SEO自动提取</div>\n<div>- 🖼️ **媒体库** - 统一媒体文件管理，支持按类型和日期查询</div>\n<div>- 🔐 **权限管理** - 基于角色的访问控制（RBAC）</div>\n<div>- 👥 **用户管理** - 多用户系统，支持用户角色分配</div>\n<div>- 🔍 **SEO优化** - 自动提取TDK、Sitemap生成</div>\n<div>- 📊 **操作日志** - 详细的用户操作审计记录，支持批量删除</div>\n<div>- 🎨 **现代化UI** - 基于 Element Plus的美观界面</div>\n<div>&nbsp;</div>\n<div>\n<div>后端采用技术：</div>\n<div>- PHP 8.2+</div>\n<div>- ThinkPHP 8.0</div>\n<div>- MySQL 8.0</div>\n<div>- JWT 认证</div>\n<div>- ThinkORM</div>\n<br>\n<div>前端采用技术：</div>\n<div>- Vue 3 (Composition API)</div>\n<div>- Vite 7</div>\n<div>- Element Plus</div>\n<div>- Vue Router 4</div>\n<div>- Pinia</div>\n<div>- Axios</div>\n<div>- TinyMCE (富文本编辑器)</div>\n<br>\n<div>后端环境要求</div>\n<br>\n<div>- PHP &gt;= 8.0</div>\n<div>- MySQL &gt;= 5.7</div>\n<div>- Composer</div>\n<div>&nbsp;</div>\n<div>前端环境要求</div>\n<div>- Node.js &gt;= 16.0</div>\n<div>- npm 或 yarn</div>\n<br>\n<div>&nbsp;<span style=\"font-size: 14pt;\">✨ 核心功能模块</span></div>\n<br>\n<div><strong>1. 文章管理</strong></div>\n<div>- 文章的增删改查</div>\n<div>- 文章分类、标签管理</div>\n<div>- 文章置顶、推荐、热门标记</div>\n<div>- 富文本编辑器</div>\n<div>- 图片上传和管理</div>\n<div>- 文章搜索和筛选</div>\n<div>- SEO设置</div>\n<br>\n<div><strong>&nbsp;2. 分类管理</strong></div>\n<div>- 多级分类支持</div>\n<div>- 分类排序</div>\n<div>- 分类SEO设置</div>\n<br>\n<div><strong>3. 标签管理</strong></div>\n<div>- 标签增删改查</div>\n<div>- 标签关联统计</div>\n<br>\n<div><strong>4. 页面管理</strong></div>\n<div>- 单页面管理（关于我们、联系我们等）</div>\n<div>- 自定义模板选择</div>\n<br>\n<div><strong>5. 用户管理（多角色）</strong></div>\n<div>- **超级管理员**: 拥有所有权限</div>\n<div>- **管理员**: 拥有大部分管理权限</div>\n<div>- **编辑**: 可以管理文章、分类、标签</div>\n<div>- **作者**: 只能管理自己的文章</div>\n<br>\n<div><strong>6. 评论管理</strong></div>\n<div>- 评论审核</div>\n<div>- 评论回复</div>\n<div>- 评论删除</div>\n<br>\n<div><strong>7. 媒体库</strong></div>\n<div>- 图片、文件上传</div>\n<div>- 媒体文件管理</div>\n<div>- 多种存储方式支持</div>\n<br>\n<div><strong>8. SEO设置</strong></div>\n<div>- 每篇文章独立SEO设置</div>\n<div>- 全站SEO配置</div>\n<br>\n<div><strong>9. 站点配置</strong></div>\n<div>- 网站基础信息</div>\n<div>- 上传配置</div>\n<div>- 模板配置</div>\n<br>\n<div><strong>10. 模板管理</strong></div>\n<div>- 多套模板支持</div>\n<div>- 模板切换</div>\n<br>\n<div><strong>11. 静态页面生成</strong></div>\n<div>- 手动生成: 后台按钮点击生成</div>\n<div>- 自动生成: 文章发布/更新时自动生成</div>\n<div>- 定时生成: 定时任务批量生成</div>\n<div>- 生成范围: 首页、列表页、详情页、栏目页、标签聚合页</div>\n<div>- 生成日志: 记录每次生成的详细信息</div>\n</div>\n</div>', NULL, NULL, 3, 1, '{\"\\u6d4b\\u8bd5\\u6807\\u7b7e1\":1}', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-13 07:18:59', '测试文章标题1', '逍遥内容管理系统简介,采用前后端分离架构,支持静态页面生成,适用于个人博客,企业网站,核心特性', '逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。 核心特性', 0, 1, 'hcabp', '', 1, '2025-10-21 11:41:23');
INSERT INTO `article_versions` VALUES (3, 1, 3, '逍遥内容管理系统简介', NULL, '逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。 ', '<p>逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。</p>\n<p>&nbsp;</p>\n<div>\n<div><span style=\"font-size: 12pt;\"><strong>核心特性</strong></span></div>\n<br>\n<div>- 🎨 **模板套装系统** - 支持多套模板自由切换，快速定制网站风格</div>\n<div>- ⚡ **静态页面生成** - 一键生成纯静态HTML页面，访问速度快，SEO友好</div>\n<div>- 📝 **文章管理** - 支持富文本编辑、草稿保存、文章属性标记、自动提取SEO</div>\n<div>- 📂 **分类管理** - 树形结构分类，支持自定义模板</div>\n<div>- 🏷️ **标签系统** - 灵活的标签体系，方便内容组织</div>\n<div>- 📄 **单页管理** - 独立页面管理，支持封面图和SEO自动提取</div>\n<div>- 🖼️ **媒体库** - 统一媒体文件管理，支持按类型和日期查询</div>\n<div>- 🔐 **权限管理** - 基于角色的访问控制（RBAC）</div>\n<div>- 👥 **用户管理** - 多用户系统，支持用户角色分配</div>\n<div>- 🔍 **SEO优化** - 自动提取TDK、Sitemap生成</div>\n<div>- 📊 **操作日志** - 详细的用户操作审计记录，支持批量删除</div>\n<div>- 🎨 **现代化UI** - 基于 Element Plus的美观界面</div>\n<div>&nbsp;</div>\n<div>\n<div>后端采用技术：</div>\n<div>- PHP 8.2+</div>\n<div>- ThinkPHP 8.0</div>\n<div>- MySQL 8.0</div>\n<div>- JWT 认证</div>\n<div>- ThinkORM</div>\n<br>\n<div>前端采用技术：</div>\n<div>- Vue 3 (Composition API)</div>\n<div>- Vite 7</div>\n<div>- Element Plus</div>\n<div>- Vue Router 4</div>\n<div>- Pinia</div>\n<div>- Axios</div>\n<div>- TinyMCE (富文本编辑器)</div>\n<br>\n<div>后端环境要求</div>\n<br>\n<div>- PHP &gt;= 8.0</div>\n<div>- MySQL &gt;= 5.7</div>\n<div>- Composer</div>\n<div>&nbsp;</div>\n<div>前端环境要求</div>\n<div>- Node.js &gt;= 16.0</div>\n<div>- npm 或 yarn</div>\n<br>\n<div>&nbsp;<span style=\"font-size: 14pt;\">✨ 核心功能模块</span></div>\n<br>\n<div><strong>1. 文章管理</strong></div>\n<div>- 文章的增删改查</div>\n<div>- 文章分类、标签管理</div>\n<div>- 文章置顶、推荐、热门标记</div>\n<div>- 富文本编辑器</div>\n<div>- 图片上传和管理</div>\n<div>- 文章搜索和筛选</div>\n<div>- SEO设置</div>\n<br>\n<div><strong>&nbsp;2. 分类管理</strong></div>\n<div>- 多级分类支持</div>\n<div>- 分类排序</div>\n<div>- 分类SEO设置</div>\n<br>\n<div><strong>3. 标签管理</strong></div>\n<div>- 标签增删改查</div>\n<div>- 标签关联统计</div>\n<br>\n<div><strong>4. 页面管理</strong></div>\n<div>- 单页面管理（关于我们、联系我们等）</div>\n<div>- 自定义模板选择</div>\n<br>\n<div><strong>5. 用户管理（多角色）</strong></div>\n<div>- **超级管理员**: 拥有所有权限</div>\n<div>- **管理员**: 拥有大部分管理权限</div>\n<div>- **编辑**: 可以管理文章、分类、标签</div>\n<div>- **作者**: 只能管理自己的文章</div>\n<br>\n<div><strong>6. 评论管理</strong></div>\n<div>- 评论审核</div>\n<div>- 评论回复</div>\n<div>- 评论删除</div>\n<br>\n<div><strong>7. 媒体库</strong></div>\n<div>- 图片、文件上传</div>\n<div>- 媒体文件管理</div>\n<div>- 多种存储方式支持</div>\n<br>\n<div><strong>8. SEO设置</strong></div>\n<div>- 每篇文章独立SEO设置</div>\n<div>- 全站SEO配置</div>\n<br>\n<div><strong>9. 站点配置</strong></div>\n<div>- 网站基础信息</div>\n<div>- 上传配置</div>\n<div>- 模板配置</div>\n<br>\n<div><strong>10. 模板管理</strong></div>\n<div>- 多套模板支持</div>\n<div>- 模板切换</div>\n<br>\n<div><strong>11. 静态页面生成</strong></div>\n<div>- 手动生成: 后台按钮点击生成</div>\n<div>- 自动生成: 文章发布/更新时自动生成</div>\n<div>- 定时生成: 定时任务批量生成</div>\n<div>- 生成范围: 首页、列表页、详情页、栏目页、标签聚合页</div>\n<div>- 生成日志: 记录每次生成的详细信息</div>\n</div>\n</div>', 'https://www.carefreecms.com/uploads/2025/10/23/20251023113216_68f9a1c05cc04.png', NULL, 3, 1, '{\"\\u6d4b\\u8bd5\\u6807\\u7b7e1\":1}', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-13 07:18:59', '测试文章标题1', '逍遥内容管理系统简介,采用前后端分离架构,支持静态页面生成,适用于个人博客,企业网站,核心特性', '逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。 核心特性', 0, 1, 'hcabp', '', 1, '2025-10-23 11:32:26');
INSERT INTO `article_versions` VALUES (4, 4, 1, '112', NULL, '41242412', '<p>323232323</p>\n<p><img style=\"max-width: 100%; display: block; margin-left: auto; margin-right: auto;\" src=\"https://www.carefreecms.com/uploads/2025/10/23/20251023113216_68f9a1c05cc04.png\" alt=\"fAZ492Eva.png\"></p>\n<p>&nbsp;</p>\n<p>sdfsfsfsdf</p>', NULL, NULL, 5, 1, '{\"\\u6d4b\\u8bd5\\u6807\\u7b7e1\":1}', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-14 22:43:52', NULL, '32323', '23232323', 0, 1, 'ch', '', 1, '2025-10-23 12:02:08');
INSERT INTO `article_versions` VALUES (5, 4, 2, '112', NULL, '41242412', '<p>323232323</p>\n<p><img style=\"max-width: 100%; display: block; margin-left: auto; margin-right: auto;\" src=\"https://www.carefreecms.com/uploads/2025/10/23/20251023113216_68f9a1c05cc04.png\" alt=\"fAZ492Eva.png\"></p>\n<p>&nbsp;</p>\n<p>sdfsfsfsdf</p>', 'https://www.carefreecms.com/uploads/2025/11/04/20251104143129_69099dc15c060.png', NULL, 5, 1, '{\"\\u6d4b\\u8bd5\\u6807\\u7b7e1\":1}', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-14 22:43:52', NULL, '32323', '23232323', 0, 1, 'ch', '', 1, '2025-11-04 14:31:44');
INSERT INTO `article_versions` VALUES (6, 2, 1, '[推荐]本地开发环境使用教程', NULL, '本地开发环境使用教程 确认本地环境正常：推荐php 8.2+ node 20.19+ npm 10.8+ mysql 8+ 从chinaz.com下载整体包：逍遥内容管理系统-chinaz下载地址 解压后有两个文件夹，一个是后端程序：carefreecms-master 一个是后台管理界面：carefreecms-frontend-master 在数据库中创建数据库，用户，密码，导入carefre...', '<p>本地开发环境使用教程</p>\n<ol>\n<li>确认本地环境正常：推荐php 8.2+&nbsp; &nbsp;node 20.19+&nbsp; npm 10.8+ mysql 8+</li>\n<li>从chinaz.com下载整体包：<a title=\"逍遥内容管理系统-chinaz下载地址\" href=\"https://down.chinaz.com/soft/51898.htm\" target=\"_blank\" rel=\"noopener\">逍遥内容管理系统-chinaz下载地址</a></li>\n<li>解压后有两个文件夹，一个是后端程序：carefreecms-master&nbsp; 一个是后台管理界面：carefreecms-frontend-master</li>\n<li>在数据库中创建数据库，用户，密码，导入carefreecms-master文件夹内的database.sql</li>\n<li>将数据库的信息写入 carefreecms-master 目录下的 .env文件中&nbsp; 或者写入 carefreecms-master 目录下 config目录下的database.php文件中。</li>\n<li>进入后端程序文件夹carefreecms-master后，运行命令提示符 cmd : php think run</li>\n<li>进入后台管理界面文件夹carefreecms-frontend-master后，运行命令提示符 cmd： 先运行 npm install 安装依赖包，成功后运行 npm run dev 启动后台界面</li>\n<li>打开后台界面的网址，就能看到登录界面，输入默认用户名 admin 和密码 admin123</li>\n<li>&nbsp;能成功登录，表示系统正常运行了。</li>\n<li>&nbsp;</li>\n</ol>', NULL, NULL, 5, 1, '[]', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-13 07:21:01', NULL, '本地开发环境使用教程,确认本地环境正常,推荐php,2+,node,20,19+,npm', '本地开发环境使用教程 确认本地环境正常：推荐php 8.2+ node 20.19+ npm 10.8+ mysql 8+ 从chinaz.com下载整体包：逍遥内容管理系统-chinaz下载地址 解压后有两个文件夹，一个是后端程序：carefreecms-master 一个是后台管理界面：care...', 0, 0, '', '', 1, '2025-11-06 15:37:41');
INSERT INTO `article_versions` VALUES (7, 2, 2, '[推荐]本地开发环境使用教程', NULL, '本地开发环境使用教程 确认本地环境正常：推荐php 8.2+ node 20.19+ npm 10.8+ mysql 8+ 从chinaz.com下载整体包：逍遥内容管理系统-chinaz下载地址 解压后有两个文件夹，一个是后端程序：carefreecms-master 一个是后台管理界面：carefreecms-frontend-master 在数据库中创建数据库，用户，密码，导入carefre...', '<p>本地开发环境使用教程</p>\n<ol>\n<li>确认本地环境正常：推荐php 8.2+&nbsp; &nbsp;node 20.19+&nbsp; npm 10.8+ mysql 8+</li>\n<li>从chinaz.com下载整体包：<a title=\"逍遥内容管理系统-chinaz下载地址\" href=\"https://down.chinaz.com/soft/51898.htm\" target=\"_blank\" rel=\"noopener\">逍遥内容管理系统-chinaz下载地址</a></li>\n<li>解压后有两个文件夹，一个是后端程序：carefreecms-master&nbsp; 一个是后台管理界面：carefreecms-frontend-master</li>\n<li>在数据库中创建数据库，用户，密码，导入carefreecms-master文件夹内的database.sql</li>\n<li>将数据库的信息写入 carefreecms-master 目录下的 .env文件中&nbsp; 或者写入 carefreecms-master 目录下 config目录下的database.php文件中。</li>\n<li>进入后端程序文件夹carefreecms-master后，运行命令提示符 cmd : php think run</li>\n<li>进入后台管理界面文件夹carefreecms-frontend-master后，运行命令提示符 cmd： 先运行 npm install 安装依赖包，成功后运行 npm run dev 启动后台界面</li>\n<li>打开后台界面的网址，就能看到登录界面，输入默认用户名 admin 和密码 admin123</li>\n<li>&nbsp;能成功登录，表示系统正常运行了。</li>\n<li>首先修改系统管理中的基本信息，尤其是 前端网站网址，这个网址是你要把网站展示到互联网的网址</li>\n<li>然后修改你喜欢的模板，保存模板修改后，在seo管理中，生成全站html</li>\n<li>进入后端程序文件夹carefreecms-master，把html文件夹里的内容全部传到你服务器上网站目录下。大功告成！</li>\n</ol>', NULL, NULL, 5, 1, '[]', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-13 07:21:01', NULL, '本地开发环境使用教程,确认本地环境正常,推荐php,2+,node,20,19+,npm', '本地开发环境使用教程 确认本地环境正常：推荐php 8.2+ node 20.19+ npm 10.8+ mysql 8+ 从chinaz.com下载整体包：逍遥内容管理系统-chinaz下载地址 解压后有两个文件夹，一个是后端程序：carefreecms-master 一个是后台管理界面：care...', 0, 1, '', '', 1, '2025-11-06 15:41:01');
INSERT INTO `article_versions` VALUES (8, 4, 3, '宝塔环境使用教程', NULL, '本地开发环境使用教程 待完善', '<p>本地开发环境使用教程 待完善</p>', 'https://www.carefreecms.com/uploads/2025/11/04/20251104143129_69099dc15c060.png', NULL, 5, 1, '[]', NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-14 22:43:52', NULL, '宝塔环境使用教程,本地开发环境使用教程,待完善', '本地开发环境使用教程 待完善', 0, 0, 'ch', '', 1, '2025-11-06 15:42:11');

-- ----------------------------
-- Table structure for articles
-- ----------------------------
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文章ID',
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
  UNIQUE INDEX `uk_slug`(`slug` ASC) USING BTREE,
  INDEX `idx_category_id`(`category_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_status_publish`(`status` ASC, `publish_time` ASC) USING BTREE,
  INDEX `idx_is_top`(`is_top` ASC) USING BTREE,
  INDEX `idx_is_recommend`(`is_recommend` ASC) USING BTREE,
  INDEX `idx_is_hot`(`is_hot` ASC) USING BTREE,
  FULLTEXT INDEX `ft_title_content`(`title`, `content`)
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '文章表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of articles
-- ----------------------------
INSERT INTO `articles` VALUES (1, 3, 1, '逍遥内容管理系统简介', NULL, '逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。 ', '<p>逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。</p>\n<p>&nbsp;</p>\n<div>\n<div><span style=\"font-size: 12pt;\"><strong>核心特性</strong></span></div>\n<br>\n<div>- 🎨 **模板套装系统** - 支持多套模板自由切换，快速定制网站风格</div>\n<div>- ⚡ **静态页面生成** - 一键生成纯静态HTML页面，访问速度快，SEO友好</div>\n<div>- 📝 **文章管理** - 支持富文本编辑、草稿保存、文章属性标记、自动提取SEO</div>\n<div>- 📂 **分类管理** - 树形结构分类，支持自定义模板</div>\n<div>- 🏷️ **标签系统** - 灵活的标签体系，方便内容组织</div>\n<div>- 📄 **单页管理** - 独立页面管理，支持封面图和SEO自动提取</div>\n<div>- 🖼️ **媒体库** - 统一媒体文件管理，支持按类型和日期查询</div>\n<div>- 🔐 **权限管理** - 基于角色的访问控制（RBAC）</div>\n<div>- 👥 **用户管理** - 多用户系统，支持用户角色分配</div>\n<div>- 🔍 **SEO优化** - 自动提取TDK、Sitemap生成</div>\n<div>- 📊 **操作日志** - 详细的用户操作审计记录，支持批量删除</div>\n<div>- 🎨 **现代化UI** - 基于 Element Plus的美观界面</div>\n<div>&nbsp;</div>\n<div>\n<div>后端采用技术：</div>\n<div>- PHP 8.2+</div>\n<div>- ThinkPHP 8.0</div>\n<div>- MySQL 8.0</div>\n<div>- JWT 认证</div>\n<div>- ThinkORM</div>\n<br>\n<div>前端采用技术：</div>\n<div>- Vue 3 (Composition API)</div>\n<div>- Vite 7</div>\n<div>- Element Plus</div>\n<div>- Vue Router 4</div>\n<div>- Pinia</div>\n<div>- Axios</div>\n<div>- TinyMCE (富文本编辑器)</div>\n<br>\n<div>后端环境要求</div>\n<br>\n<div>- PHP &gt;= 8.0</div>\n<div>- MySQL &gt;= 5.7</div>\n<div>- Composer</div>\n<div>&nbsp;</div>\n<div>前端环境要求</div>\n<div>- Node.js &gt;= 16.0</div>\n<div>- npm 或 yarn</div>\n<br>\n<div>&nbsp;<span style=\"font-size: 14pt;\">✨ 核心功能模块</span></div>\n<br>\n<div><strong>1. 文章管理</strong></div>\n<div>- 文章的增删改查</div>\n<div>- 文章分类、标签管理</div>\n<div>- 文章置顶、推荐、热门标记</div>\n<div>- 富文本编辑器</div>\n<div>- 图片上传和管理</div>\n<div>- 文章搜索和筛选</div>\n<div>- SEO设置</div>\n<br>\n<div><strong>&nbsp;2. 分类管理</strong></div>\n<div>- 多级分类支持</div>\n<div>- 分类排序</div>\n<div>- 分类SEO设置</div>\n<br>\n<div><strong>3. 标签管理</strong></div>\n<div>- 标签增删改查</div>\n<div>- 标签关联统计</div>\n<br>\n<div><strong>4. 页面管理</strong></div>\n<div>- 单页面管理（关于我们、联系我们等）</div>\n<div>- 自定义模板选择</div>\n<br>\n<div><strong>5. 用户管理（多角色）</strong></div>\n<div>- **超级管理员**: 拥有所有权限</div>\n<div>- **管理员**: 拥有大部分管理权限</div>\n<div>- **编辑**: 可以管理文章、分类、标签</div>\n<div>- **作者**: 只能管理自己的文章</div>\n<br>\n<div><strong>6. 评论管理</strong></div>\n<div>- 评论审核</div>\n<div>- 评论回复</div>\n<div>- 评论删除</div>\n<br>\n<div><strong>7. 媒体库</strong></div>\n<div>- 图片、文件上传</div>\n<div>- 媒体文件管理</div>\n<div>- 多种存储方式支持</div>\n<br>\n<div><strong>8. SEO设置</strong></div>\n<div>- 每篇文章独立SEO设置</div>\n<div>- 全站SEO配置</div>\n<br>\n<div><strong>9. 站点配置</strong></div>\n<div>- 网站基础信息</div>\n<div>- 上传配置</div>\n<div>- 模板配置</div>\n<br>\n<div><strong>10. 模板管理</strong></div>\n<div>- 多套模板支持</div>\n<div>- 模板切换</div>\n<br>\n<div><strong>11. 静态页面生成</strong></div>\n<div>- 手动生成: 后台按钮点击生成</div>\n<div>- 自动生成: 文章发布/更新时自动生成</div>\n<div>- 定时生成: 定时任务批量生成</div>\n<div>- 生成范围: 首页、列表页、详情页、栏目页、标签聚合页</div>\n<div>- 生成日志: 记录每次生成的详细信息</div>\n</div>\n</div>', 'https://www.carefreecms.com/uploads/2025/10/23/20251023113216_68f9a1c05cc04.png', NULL, NULL, NULL, NULL, 0, 0, 4, 0, 0, 0, '2025-10-13 07:18:59', '测试文章标题1', '逍遥内容管理系统简介,采用前后端分离架构,支持静态页面生成,适用于个人博客,企业网站,核心特性', '逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。 核心特性', NULL, NULL, NULL, 'summary', NULL, 'Article', 0, 1, 'hcabp', '2025-10-12 07:24:49', '2025-10-28 11:54:44', NULL, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `articles` VALUES (2, 5, 1, '[推荐]本地开发环境使用教程', NULL, '本地开发环境使用教程 确认本地环境正常：推荐php 8.2+ node 20.19+ npm 10.8+ mysql 8+ 从chinaz.com下载整体包：逍遥内容管理系统-chinaz下载地址 解压后有两个文件夹，一个是后端程序：carefreecms-master 一个是后台管理界面：carefreecms-frontend-master 在数据库中创建数据库，用户，密码，导入carefre...', '<p>本地开发环境使用教程</p>\n<ol>\n<li>确认本地环境正常：推荐php 8.2+&nbsp; &nbsp;node 20.19+&nbsp; npm 10.8+ mysql 8+</li>\n<li>从chinaz.com下载整体包：<a title=\"逍遥内容管理系统-chinaz下载地址\" href=\"https://down.chinaz.com/soft/51898.htm\" target=\"_blank\" rel=\"noopener\">逍遥内容管理系统-chinaz下载地址</a></li>\n<li>解压后有两个文件夹，一个是后端程序：carefreecms-master&nbsp; 一个是后台管理界面：carefreecms-frontend-master</li>\n<li>在数据库中创建数据库，用户，密码，导入carefreecms-master文件夹内的database.sql</li>\n<li>将数据库的信息写入 carefreecms-master 目录下的 .env文件中&nbsp; 或者写入 carefreecms-master 目录下 config目录下的database.php文件中。</li>\n<li>进入后端程序文件夹carefreecms-master后，运行命令提示符 cmd : php think run</li>\n<li>进入后台管理界面文件夹carefreecms-frontend-master后，运行命令提示符 cmd： 先运行 npm install 安装依赖包，成功后运行 npm run dev 启动后台界面</li>\n<li>打开后台界面的网址，就能看到登录界面，输入默认用户名 admin 和密码 admin123</li>\n<li>&nbsp;能成功登录，表示系统正常运行了。</li>\n<li>首先修改系统管理中的基本信息，尤其是 前端网站网址，这个网址是你要把网站展示到互联网的网址</li>\n<li>然后修改你喜欢的模板，保存模板修改后，在seo管理中，生成全站html</li>\n<li>进入后端程序文件夹carefreecms-master，把html文件夹里的内容全部传到你服务器上网站目录下。大功告成！</li>\n</ol>', NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-13 07:21:01', NULL, '本地开发环境使用教程,确认本地环境正常,推荐php,2+,node,20,19+,npm', '本地开发环境使用教程 确认本地环境正常：推荐php 8.2+ node 20.19+ npm 10.8+ mysql 8+ 从chinaz.com下载整体包：逍遥内容管理系统-chinaz下载地址 解压后有两个文件夹，一个是后端程序：carefreecms-master 一个是后台管理界面：care...', NULL, NULL, NULL, 'summary', NULL, 'Article', 0, 1, '', '2025-10-13 07:05:23', '2025-10-14 22:25:05', NULL, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `articles` VALUES (3, 1, 1, '1', NULL, '111', '<p>111111</p>\n<p>&nbsp;</p>\n<p><img style=\"display: block; margin-left: auto; margin-right: auto;\" src=\"http://localhost:8000/uploads/2025/10/13/20251013211027_68ecfa4324c42.png\" alt=\"\" width=\"150\" height=\"150\"></p>', NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-13 19:46:47', NULL, '2222', '3333', NULL, NULL, NULL, 'summary', NULL, 'Article', 0, 1, '', '2025-10-13 19:30:20', '2025-10-19 05:17:24', NULL, 0, 0, NULL, NULL, NULL, 0);
INSERT INTO `articles` VALUES (4, 5, 1, '宝塔环境使用教程', NULL, '本地开发环境使用教程 待完善', '<p>本地开发环境使用教程 待完善</p>', 'https://www.carefreecms.com/uploads/2025/11/04/20251104143129_69099dc15c060.png', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, '2025-10-14 22:43:52', NULL, '宝塔环境使用教程,本地开发环境使用教程,待完善', '本地开发环境使用教程 待完善', NULL, NULL, NULL, 'summary', NULL, 'Article', 0, 1, 'ch', '2025-10-14 22:43:49', '2025-11-06 16:36:52', NULL, 0, 0, NULL, NULL, NULL, 0);

-- ----------------------------
-- Table structure for categories
-- ----------------------------
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
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
  UNIQUE INDEX `uk_slug`(`slug` ASC) USING BTREE,
  INDEX `idx_parent_id`(`parent_id` ASC) USING BTREE,
  INDEX `idx_status_sort`(`status` ASC, `sort` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '分类表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of categories
-- ----------------------------
INSERT INTO `categories` VALUES (1, 0, '公司新闻', 'company-news', '公司最新动态', NULL, 'category', NULL, NULL, NULL, 1, 1, '2025-10-12 02:12:51', '2025-10-12 02:12:51', NULL);
INSERT INTO `categories` VALUES (2, 0, '行业资讯', 'industry-news', '行业相关资讯', NULL, 'category', NULL, NULL, NULL, 2, 1, '2025-10-12 02:12:51', '2025-10-12 02:12:51', NULL);
INSERT INTO `categories` VALUES (3, 0, '产品介绍', 'products', '产品相关介绍', NULL, 'category', NULL, NULL, NULL, 3, 1, '2025-10-12 02:12:51', '2025-10-12 02:12:51', NULL);
INSERT INTO `categories` VALUES (4, 0, '技术文档', 'tech-docs', '技术相关文档', NULL, 'category', NULL, NULL, NULL, 4, 1, '2025-10-12 02:12:51', '2025-10-12 02:12:51', NULL);
INSERT INTO `categories` VALUES (5, 0, '技术文章', 'tech', '技术类文章分类', NULL, 'category', NULL, NULL, NULL, 0, 1, '2025-10-12 07:23:45', '2025-10-12 07:23:45', NULL);
INSERT INTO `categories` VALUES (6, 0, '测试', 'test', '测试分类', NULL, 'category', NULL, NULL, NULL, 14, 1, '2025-10-13 07:06:18', '2025-10-13 07:06:18', NULL);

-- ----------------------------
-- Table structure for comments
-- ----------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '评论ID',
  `article_id` int UNSIGNED NOT NULL COMMENT '文章ID',
  `user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '前台用户ID（注册用户）',
  `is_guest` tinyint NOT NULL DEFAULT 0 COMMENT '是否游客评论：0=注册用户，1=游客',
  `parent_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '父评论ID，0表示顶级评论',
  `user_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '评论者名称（游客）',
  `user_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '评论者邮箱（游客）',
  `user_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '评论者IP',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '评论内容',
  `like_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '点赞数',
  `is_admin` tinyint NOT NULL DEFAULT 0 COMMENT '是否管理员：0=否，1=是',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT '状态：0=待审核，1=已通过，2=已拒绝',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_article_id`(`article_id` ASC) USING BTREE,
  INDEX `idx_parent_id`(`parent_id` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '评论表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of comments
-- ----------------------------
INSERT INTO `comments` VALUES (1, 1, NULL, 1, 0, '游客张三', 'guest@example.com', '127.0.0.1', '这是编辑后的评论内容', 0, 0, 1, '2025-10-28 11:43:42', '2025-10-28 11:55:41');
INSERT INTO `comments` VALUES (2, 1, 2, 0, 0, NULL, NULL, '127.0.0.1', '这是一条注册用户的评论，测试用户评论功能！', 0, 0, 2, '2025-10-28 11:47:14', '2025-10-28 11:54:15');
INSERT INTO `comments` VALUES (3, 1, NULL, 1, 0, '测试用户3', 'test3@test.com', '127.0.0.1', '这是第三条测试评论', 0, 0, 1, '2025-10-28 11:54:24', '2025-10-28 11:54:34');
INSERT INTO `comments` VALUES (4, 1, NULL, 1, 0, '测试用户4', 'test4@test.com', '127.0.0.1', '这是第四条测试评论', 0, 0, 1, '2025-10-28 11:54:24', '2025-10-28 11:54:34');
INSERT INTO `comments` VALUES (5, 1, NULL, 0, 1, NULL, NULL, '127.0.0.1', '感谢您的评论！这是管理员的回复。', 0, 1, 1, '2025-10-28 11:54:44', '2025-10-28 11:54:44');

-- ----------------------------
-- Table structure for content_models
-- ----------------------------
DROP TABLE IF EXISTS `content_models`;
CREATE TABLE `content_models`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '模型ID',
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
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容模型表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of content_models
-- ----------------------------
INSERT INTO `content_models` VALUES (1, '文章', 'articles', 'Document', '系统内置文章模型', NULL, 1, 1, 100, '2025-10-18 22:36:49', '2025-10-18 22:36:49');
INSERT INTO `content_models` VALUES (2, '分类', 'categories', 'FolderOpened', '系统内置分类模型', NULL, 1, 1, 90, '2025-10-18 22:36:49', '2025-10-18 22:36:49');
INSERT INTO `content_models` VALUES (3, '标签', 'tags', 'CollectionTag', '系统内置标签模型', NULL, 1, 1, 80, '2025-10-18 22:36:49', '2025-10-18 22:36:49');
INSERT INTO `content_models` VALUES (4, '单页', 'pages', 'Files', '系统内置单页模型', NULL, 1, 1, 70, '2025-10-18 22:36:49', '2025-10-18 22:36:49');

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
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '定时任务日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cron_job_logs
-- ----------------------------

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
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '定时任务' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cron_jobs
-- ----------------------------

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
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '自定义字段值存储表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of custom_field_values
-- ----------------------------

-- ----------------------------
-- Table structure for custom_fields
-- ----------------------------
DROP TABLE IF EXISTS `custom_fields`;
CREATE TABLE `custom_fields`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '字段ID',
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
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '自定义字段定义表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of custom_fields
-- ----------------------------
INSERT INTO `custom_fields` VALUES (1, '测试', 'article_test', 'text', 'article', NULL, '测试信息', NULL, '', '请输入测试内容', '这里是帮助说明，你看看就行', NULL, 0, 1, 1, 0, 1, '2025-11-04 14:54:46', '2025-11-04 14:54:46');
INSERT INTO `custom_fields` VALUES (2, '测试', 'article_test1', 'text', 'article', NULL, '测试信息', NULL, '', '请输入测试内容', '这里是帮助说明，你看看就行', NULL, 0, 1, 1, 0, 1, '2025-11-04 14:55:10', '2025-11-04 14:55:10');

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
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '数据库备份记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of database_backups
-- ----------------------------

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
-- Table structure for front_users
-- ----------------------------
DROP TABLE IF EXISTS `front_users`;
CREATE TABLE `front_users`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
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
  INDEX `idx_github_id`(`github_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '前台用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of front_users
-- ----------------------------
INSERT INTO `front_users` VALUES (1, 'testuser', '$2y$10$3QptEgodMb17Sv2tcYjQu.P2IT3aXCiax4ayfRI3YNfh4zT5/uRmC', '测试用户', NULL, 'test@example.com', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 10, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, NULL, NULL, NULL, NULL, '2025-10-28 11:36:15', '2025-10-28 11:36:15', NULL, NULL);
INSERT INTO `front_users` VALUES (2, 'newuser2025', '$2y$10$lH0rHXblbAOH8Cl1oRzPFOHhKfE7rfbAZAFEBXY3LUQL7Vg16KcDW', '新用户2025', NULL, 'newuser2025@example.com', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 10, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, '2025-10-28 11:44:53', '127.0.0.1', 2, 1, 0, NULL, NULL, NULL, NULL, '2025-10-28 11:36:57', '2025-10-28 11:44:53', NULL, NULL);
INSERT INTO `front_users` VALUES (3, 'test_user_001', '$2y$10$IJNUbz7CCRdaQt1coTCCU.BBCJZInxVp0gFPoxH6nq7V.GP3LasRC', '测试用户001', NULL, 'test001@example.com', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 105, 1, 0, 0, 0, 0, 0, 0, 0, '521d7aa277e9020259ab9c766c85e20d', '2025-11-02 12:48:53', NULL, NULL, NULL, NULL, 0, 1, 0, NULL, NULL, NULL, NULL, '2025-11-01 12:48:53', '2025-11-01 13:16:38', NULL, NULL);
INSERT INTO `front_users` VALUES (4, 'sinma', '$2y$10$Y8GLg06G4QP.CLZqgazzl.XQvi.WZYz1ymbRw1dUnFKK7VeQPUMGe', '', '', '', '', NULL, 0, NULL, NULL, NULL, NULL, NULL, 101, 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 1, NULL, NULL, NULL, NULL, '2025-11-02 05:38:08', '2025-11-04 14:47:35', NULL, NULL);

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
-- Table structure for link_groups
-- ----------------------------
DROP TABLE IF EXISTS `link_groups`;
CREATE TABLE `link_groups`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分组ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '分组名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '分组描述',
  `sort` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_sort`(`sort` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '友链分组表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of link_groups
-- ----------------------------
INSERT INTO `link_groups` VALUES (1, '合作伙伴', '战略合作伙伴网站', 1, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34');
INSERT INTO `link_groups` VALUES (2, '友情链接', '友好互链网站', 2, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34');
INSERT INTO `link_groups` VALUES (3, '推荐网站', '优质推荐网站', 3, 1, '2025-10-19 00:58:34', '2025-10-19 00:58:34');

-- ----------------------------
-- Table structure for links
-- ----------------------------
DROP TABLE IF EXISTS `links`;
CREATE TABLE `links`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '链接ID',
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
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '友情链接表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of links
-- ----------------------------
INSERT INTO `links` VALUES (1, 1, '百度', 'https://www.baidu.com', NULL, '全球最大的中文搜索引擎', NULL, 1, 1, 1, 0, NULL, NULL, NULL, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);
INSERT INTO `links` VALUES (2, 1, '腾讯', 'https://www.qq.com', NULL, '腾讯官方网站', NULL, 2, 1, 1, 0, NULL, NULL, NULL, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);
INSERT INTO `links` VALUES (3, 2, 'GitHub', 'https://github.com', NULL, '全球最大的代码托管平台', NULL, 3, 1, 0, 0, NULL, NULL, NULL, '2025-10-19 00:58:34', '2025-10-19 00:58:34', NULL);
INSERT INTO `links` VALUES (4, 3, 'Gitee', 'https://www.gitee.com', '', '', '', 0, 1, 1, 0, NULL, NULL, NULL, '2025-10-21 08:44:11', '2025-10-21 08:44:11', NULL);

-- ----------------------------
-- Table structure for login_logs
-- ----------------------------
DROP TABLE IF EXISTS `login_logs`;
CREATE TABLE `login_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NULL DEFAULT NULL COMMENT '用户ID',
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '用户名',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'IP地址',
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '用户代理',
  `login_time` datetime NOT NULL COMMENT '登录时间',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '状态:success-成功,failed-失败',
  `fail_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '失败原因',
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '地理位置',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_ip`(`ip` ASC) USING BTREE,
  INDEX `idx_login_time`(`login_time` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '登录日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of login_logs
-- ----------------------------
INSERT INTO `login_logs` VALUES (1, 1, 'admin', '127.0.0.1', 'curl/8.15.0', '2025-11-04 15:27:42', 'success', '', '内网IP');
INSERT INTO `login_logs` VALUES (2, NULL, 'testuser', '127.0.0.1', 'curl/8.15.0', '2025-11-04 15:29:18', 'failed', '用户不存在', '内网IP');
INSERT INTO `login_logs` VALUES (3, 1, 'admin', '127.0.0.1', 'curl/8.15.0', '2025-11-04 15:30:25', 'success', '', '内网IP');
INSERT INTO `login_logs` VALUES (4, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 03:24:54', 'success', '', '内网IP');
INSERT INTO `login_logs` VALUES (5, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 15:20:55', 'success', '', '内网IP');
INSERT INTO `login_logs` VALUES (6, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 15:21:17', 'success', '', '内网IP');
INSERT INTO `login_logs` VALUES (7, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 15:22:53', 'success', '', '内网IP');
INSERT INTO `login_logs` VALUES (8, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 18:40:35', 'success', '', '内网IP');
INSERT INTO `login_logs` VALUES (9, 1, 'admin', '127.0.0.1', 'curl/8.15.0', '2025-11-06 19:50:46', 'success', '', '内网IP');
INSERT INTO `login_logs` VALUES (10, 1, 'admin', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 Edg/142.0.0.0', '2025-11-06 21:02:28', 'success', '', '内网IP');

-- ----------------------------
-- Table structure for media
-- ----------------------------
DROP TABLE IF EXISTS `media`;
CREATE TABLE `media`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '媒体ID',
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
  INDEX `idx_file_type`(`file_type` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '媒体库表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of media
-- ----------------------------
INSERT INTO `media` VALUES (1, 1, '1.png', 'uploads/2025/10/13/20251013070046_68ec331eb77b8.png', 'http://localhost:8000/uploads/2025/10/13/20251013070046_68ec331eb77b8.png', 'image', 'image/png', 30608, 150, 150, 'local', '2025-10-13 07:00:47', '2025-10-14 03:11:15');
INSERT INTO `media` VALUES (2, 1, '021.jpg', 'uploads/2025/10/13/20251013070825_68ec34e9e4da0.jpg', 'http://localhost:8000/uploads/2025/10/13/20251013070825_68ec34e9e4da0.jpg', 'image', 'image/jpeg', 82462, 1920, 1080, 'local', '2025-10-13 07:08:26', '2025-10-14 03:11:12');
INSERT INTO `media` VALUES (3, 1, '1.png', 'uploads/2025/10/13/20251013075016_68ec3eb873348.png', 'http://localhost:8000/uploads/2025/10/13/20251013075016_68ec3eb873348.png', 'image', 'image/png', 30608, 150, 150, 'local', '2025-10-13 07:50:17', '2025-10-14 03:11:10');
INSERT INTO `media` VALUES (4, 1, '280.png', 'uploads/2025/10/13/20251013102806_68ec63b696e39.png', 'http://localhost:8000/uploads/2025/10/13/20251013102806_68ec63b696e39.png', 'image', 'image/png', 6193, 280, 280, 'local', '2025-10-13 10:28:07', '2025-10-14 03:11:05');
INSERT INTO `media` VALUES (5, 1, '1.png', 'uploads/2025/10/13/20251013211027_68ecfa4324c42.png', 'http://localhost:8000/uploads/2025/10/13/20251013211027_68ecfa4324c42.png', 'image', 'image/png', 30608, 150, 150, 'local', '2025-10-13 21:10:27', '2025-10-14 03:11:08');
INSERT INTO `media` VALUES (6, 1, '1 (1).jpg', 'uploads/2025/10/14/20251014020323_68ed3eeb4be48.jpg', NULL, 'image', 'image/jpeg', 63167, 900, 828, 'local', '2025-10-14 02:03:23', '2025-10-14 23:15:17');
INSERT INTO `media` VALUES (7, 1, '200.png', 'uploads/2025/10/14/20251014062422_68ed7c163fa4f.png', NULL, 'image', 'image/png', 8377, 200, 200, 'local', '2025-10-14 06:24:22', '2025-10-21 11:41:54');
INSERT INTO `media` VALUES (8, 1, '1 (2).jpg', 'uploads/2025/10/14/20251014204459_68ee45cb09f3b.jpg', NULL, 'image', 'image/jpeg', 42502, 500, 332, 'local', '2025-10-14 20:44:59', NULL);
INSERT INTO `media` VALUES (9, 1, '1 (1).jpg', 'uploads/2025/10/14/20251014221653_68ee5b5527950.jpg', NULL, 'image', 'image/jpeg', 63167, 900, 828, 'local', '2025-10-14 22:16:53', NULL);
INSERT INTO `media` VALUES (10, 1, '1.png', 'uploads/2025/10/14/20251014222455_68ee5d3793c97.png', NULL, 'image', 'image/png', 30608, 150, 150, 'local', '2025-10-14 22:24:56', NULL);
INSERT INTO `media` VALUES (11, 1, '1 (2).jpg', 'uploads/2025/10/19/20251019040836_68f3f3c46cfca.jpg', NULL, 'image', 'image/jpeg', 42502, 500, 332, 'local', '2025-10-19 04:08:36', NULL);
INSERT INTO `media` VALUES (12, 1, '1 (2).jpg', 'uploads/2025/10/19/20251019041349_68f3f4fdc14c5.jpg', NULL, 'image', 'image/jpeg', 42502, 500, 332, 'local', '2025-10-19 04:13:50', NULL);
INSERT INTO `media` VALUES (13, 1, '11.jpg', 'uploads/2025/10/20/20251020231550_68f6522676d09.jpg', NULL, 'image', 'image/jpeg', 258755, 5924, 2000, 'local', '2025-10-20 23:15:51', '2025-10-21 08:45:30');
INSERT INTO `media` VALUES (14, 1, '11.jpg', 'uploads/2025/10/20/20251020231615_68f6523f4de91.jpg', NULL, 'image', 'image/jpeg', 258755, 5924, 2000, 'local', '2025-10-20 23:16:15', '2025-10-21 08:45:33');
INSERT INTO `media` VALUES (15, 1, '12.jpg', 'uploads/2025/10/20/20251020231643_68f6525b5585b.jpg', NULL, 'image', 'image/jpeg', 324066, 4800, 1500, 'local', '2025-10-20 23:16:43', NULL);
INSERT INTO `media` VALUES (16, 1, '11.jpg', 'uploads/2025/10/20/20251020235433_68f65b39e5169.jpg', NULL, 'image', 'image/jpeg', 258755, 5924, 2000, 'local', '2025-10-20 23:54:34', '2025-10-21 08:45:05');
INSERT INTO `media` VALUES (17, 1, '11.jpg', 'uploads/2025/10/21/20251021000708_68f65e2c30546.jpg', NULL, 'image', 'image/jpeg', 258755, 5924, 2000, 'local', '2025-10-21 00:07:08', '2025-10-21 08:45:08');
INSERT INTO `media` VALUES (18, 1, '11.jpg', 'uploads/2025/10/21/20251021001911_68f660ff5da9c.jpg', NULL, 'image', 'image/jpeg', 258755, 5924, 2000, 'local', '2025-10-21 00:19:11', '2025-10-21 08:45:14');
INSERT INTO `media` VALUES (19, 1, '11.jpg', 'uploads/2025/10/21/20251021002240_68f661d06afdb.jpg', NULL, 'image', 'image/jpeg', 258755, 5924, 2000, 'local', '2025-10-21 00:22:40', '2025-10-21 08:45:17');
INSERT INTO `media` VALUES (20, 1, '12.jpg', 'uploads/2025/10/21/20251021002300_68f661e417f0f.jpg', NULL, 'image', 'image/jpeg', 324066, 4800, 1500, 'local', '2025-10-21 00:23:00', NULL);
INSERT INTO `media` VALUES (21, 1, '13.jpg', 'uploads/2025/10/21/20251021002309_68f661eda7e00.jpg', NULL, 'image', 'image/jpeg', 271621, 6592, 2000, 'local', '2025-10-21 00:23:10', NULL);
INSERT INTO `media` VALUES (22, 1, '1 (2).jpg', 'uploads/2025/10/21/20251021002334_68f66206c30e6.jpg', NULL, 'image', 'image/jpeg', 42502, 500, 332, 'local', '2025-10-21 00:23:35', NULL);
INSERT INTO `media` VALUES (23, 1, '1 (1).jpg', 'uploads/2025/10/21/20251021002344_68f6621032001.jpg', NULL, 'image', 'image/jpeg', 63167, 900, 828, 'local', '2025-10-21 00:23:44', NULL);
INSERT INTO `media` VALUES (24, 1, 'fAZ492Eva.png', 'uploads/2025/10/23/20251023113216_68f9a1c05cc04.png', NULL, 'image', 'image/png', 386999, 1024, 1024, 'local', '2025-10-23 11:32:16', NULL);
INSERT INTO `media` VALUES (25, 1, '280.png', 'uploads/2025/11/04/20251104143129_69099dc15c060.png', NULL, 'image', 'image/png', 6193, 280, 280, 'local', '2025-11-04 14:31:29', NULL);

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
INSERT INTO `member_levels` VALUES (12, 11, '超级至尊', '', 1111111, 11111111, 111, 30, '[]', '', '#dd1c1c', 11, 1, '2025-11-03 01:51:52', '2025-11-03 01:51:52', NULL);

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
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '站内消息表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of notifications
-- ----------------------------
INSERT INTO `notifications` VALUES (1, 3, 'system', '系统测试通知', '这是一条系统测试通知，用于验证通知系统是否正常工作。', NULL, NULL, NULL, NULL, 1, '2025-11-01 13:04:54', '2025-11-01 13:04:54');
INSERT INTO `notifications` VALUES (2, 3, 'system', '系统通知', '模板通知测试内容', NULL, NULL, NULL, NULL, 0, NULL, '2025-11-01 13:04:54');
INSERT INTO `notifications` VALUES (3, 3, 'order', '订单已发货', '您的积分兑换订单（PS202511011316377096）已发货', '/point-shop/order/', NULL, 'point_shop_order', NULL, 0, NULL, '2025-11-01 13:16:38');
INSERT INTO `notifications` VALUES (4, 1, 'system', '111', '222', NULL, NULL, NULL, NULL, 0, NULL, '2025-11-02 04:01:27');
INSERT INTO `notifications` VALUES (5, 2, 'system', '111', '222', NULL, NULL, NULL, NULL, 0, NULL, '2025-11-02 04:01:27');
INSERT INTO `notifications` VALUES (6, 3, 'system', '111', '222', NULL, NULL, NULL, NULL, 0, NULL, '2025-11-02 04:01:27');
INSERT INTO `notifications` VALUES (7, 1, 'system', '111', '222', NULL, NULL, NULL, NULL, 0, NULL, '2025-11-04 14:51:56');
INSERT INTO `notifications` VALUES (8, 2, 'system', '111', '222', NULL, NULL, NULL, NULL, 0, NULL, '2025-11-04 14:51:56');
INSERT INTO `notifications` VALUES (9, 3, 'system', '111', '222', NULL, NULL, NULL, NULL, 0, NULL, '2025-11-04 14:51:56');
INSERT INTO `notifications` VALUES (10, 4, 'system', '111', '222', NULL, NULL, NULL, NULL, 0, NULL, '2025-11-04 14:51:56');

-- ----------------------------
-- Table structure for oauth_config
-- ----------------------------
DROP TABLE IF EXISTS `oauth_config`;
CREATE TABLE `oauth_config`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `platform` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '平台（wechat/qq/weibo/github）',
  `app_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '应用ID',
  `app_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '应用密钥',
  `app_secret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '应用密钥',
  `callback_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '回调地址',
  `scope` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '授权范围',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态（0禁用/1启用）',
  `sort_order` int NOT NULL DEFAULT 0 COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `platform`(`platform` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'OAuth配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of oauth_config
-- ----------------------------

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
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态 1成功 0失败',
  `error_msg` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '错误信息',
  `execute_time` int NULL DEFAULT 0 COMMENT '执行时间(毫秒)',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_module`(`module` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 104 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '操作日志表' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for pages
-- ----------------------------
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '页面ID',
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
  UNIQUE INDEX `uk_slug`(`slug` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '单页面表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pages
-- ----------------------------
INSERT INTO `pages` VALUES (1, '关于我们', 'about', '<div>\n<div>逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。</div>\n</div>', NULL, 'page', '关于我们', '关于我们', '逍遥内容管理系统（CarefreeCMS）是一款功能强大、易于使用的内容管理平台，采用前后端分离架构，支持静态页面生成，适用于个人博客、企业网站、新闻媒体等各类内容发布场景。', NULL, NULL, NULL, 1, 1, '2025-10-12 02:12:51', '2025-10-16 02:52:05', NULL);
INSERT INTO `pages` VALUES (2, '联系我们', 'contact', '<p>联系方式</p>\n<p>QQ: 42033223</p>\n<p>Email: <a href=\"mailto:sinma@qq.com\">sinma@qq.com</a></p>\n<p>官方QQ交流群：113572201</p>', NULL, 'page', '联系我们', '联系我们', '联系方式 QQ: 42033223 Email: sinma@qq.com 官方QQ交流群：113572201', NULL, NULL, NULL, 2, 1, '2025-10-12 02:12:51', '2025-10-16 02:52:05', NULL);
INSERT INTO `pages` VALUES (3, '产品功能', 'features', '<div>\n<div>核心功能模块</div>\n<br>\n<div>1. 文章管理</div>\n<div>- 文章的增删改查</div>\n<div>- 文章分类、标签管理</div>\n<div>- 文章置顶、推荐、热门标记</div>\n<div>- 富文本编辑器</div>\n<div>- 图片上传和管理</div>\n<div>- 文章搜索和筛选</div>\n<div>- SEO设置</div>\n<br>\n<div>2. 分类管理</div>\n<div>- 多级分类支持</div>\n<div>- 分类排序</div>\n<div>- 分类SEO设置</div>\n<br>\n<div>3. 标签管理</div>\n<div>- 标签增删改查</div>\n<div>- 标签关联统计</div>\n<br>\n<div>4. 页面管理</div>\n<div>- 单页面管理（关于我们、联系我们等）</div>\n<div>- 自定义模板选择</div>\n<br>\n<div>5. 用户管理（多角色）</div>\n<div>- **超级管理员**: 拥有所有权限</div>\n<div>- **管理员**: 拥有大部分管理权限</div>\n<div>- **编辑**: 可以管理文章、分类、标签</div>\n<div>- **作者**: 只能管理自己的文章</div>\n<br>\n<div>6. 评论管理</div>\n<div>- 评论审核</div>\n<div>- 评论回复</div>\n<div>- 评论删除</div>\n<br>\n<div>7. 媒体库</div>\n<div>- 图片、文件上传</div>\n<div>- 媒体文件管理</div>\n<div>- 多种存储方式支持</div>\n<br>\n<div>8. SEO设置</div>\n<div>- 每篇文章独立SEO设置</div>\n<div>- 全站SEO配置</div>\n<br>\n<div>9. 站点配置</div>\n<div>- 网站基础信息</div>\n<div>- 上传配置</div>\n<div>- 模板配置</div>\n<br>\n<div>10. 模板管理</div>\n<div>- 多套模板支持</div>\n<div>- 模板切换</div>\n<br>\n<div>11. 静态页面生成</div>\n<div>- **手动生成**: 后台按钮点击生成</div>\n<div>- **自动生成**: 文章发布/更新时自动生成</div>\n<div>- **定时生成**: 定时任务批量生成</div>\n<div>- **生成范围**: 首页、列表页、详情页、栏目页、标签聚合页</div>\n<div>- **生成日志**: 记录每次生成的详细信息</div>\n</div>', '', 'page', '产品功能', '产品功能', '核心功能模块 1. 文章管理 - 文章的增删改查 - 文章分类、标签管理 - 文章置顶、推荐、热门标记 - 富文本编辑器 - 图片上传和管理 - 文章搜索和筛选 - SEO设置 2. 分类管理 - 多级分类支持 - 分类排序 - 分类SEO设置 3. 标签管理 - 标签增删改查 - 标签关联统计 4. 页面管理 - 单页面管理（关于我们、联系我们等） - 自定义模板选择 5. 用户管理（多角色） ', NULL, NULL, NULL, 0, 1, '2025-10-23 01:30:12', '2025-10-23 01:46:45', NULL);
INSERT INTO `pages` VALUES (4, '会员中心', 'members', '<p>会员中心</p>', '', 'members', '会员中心', '会员中心', '会员中心', NULL, NULL, NULL, 0, 1, '2025-11-06 19:54:15', '2025-11-06 19:54:15', NULL);
INSERT INTO `pages` VALUES (5, '投稿中心', 'contribute', '<div>\n<div>投稿中心</div>\n</div>', '', 'contribute', '投稿中心', '投稿中心', '投稿中心', NULL, NULL, NULL, 0, 1, '2025-11-06 19:55:30', '2025-11-06 19:55:30', NULL);
INSERT INTO `pages` VALUES (6, '我的投稿', 'contributions', '<div>\n<div>我的投稿</div>\n</div>', '', 'contributions', '我的投稿', '我的投稿', '我的投稿', NULL, NULL, NULL, 0, 1, '2025-11-06 19:56:07', '2025-11-06 19:56:07', NULL);
INSERT INTO `pages` VALUES (7, '个人中心', 'profile', '<p>个人中心</p>', '', 'profile', '个人中心', '个人中心', '个人中心', NULL, NULL, NULL, 0, 1, '2025-11-06 19:57:17', '2025-11-06 19:57:17', NULL);
INSERT INTO `pages` VALUES (8, '我的通知', 'notifications', '<p>我的通知</p>', '', 'notifications', '我的通知', '我的通知', '我的通知', NULL, NULL, NULL, 0, 1, '2025-11-06 19:57:50', '2025-11-06 19:57:50', NULL);
INSERT INTO `pages` VALUES (9, '注册', 'register', '<p>注册</p>', '', 'register', '注册', '注册', '注册', NULL, NULL, NULL, 0, 1, '2025-11-06 19:58:24', '2025-11-06 19:58:24', NULL);
INSERT INTO `pages` VALUES (10, '登录', 'login', '<p>登录</p>', '', 'login', '登录', '登录', '登录', NULL, NULL, NULL, 0, 1, '2025-11-06 19:58:48', '2025-11-06 19:58:48', NULL);

-- ----------------------------
-- Table structure for point_shop_categories
-- ----------------------------
DROP TABLE IF EXISTS `point_shop_categories`;
CREATE TABLE `point_shop_categories`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '分类名称',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '图标',
  `sort_order` int NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '积分商城分类表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of point_shop_categories
-- ----------------------------
INSERT INTO `point_shop_categories` VALUES (1, '虚拟商品', 'virtual', 1, 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59');
INSERT INTO `point_shop_categories` VALUES (2, '实物商品', 'physical', 2, 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59');
INSERT INTO `point_shop_categories` VALUES (3, '优惠券', 'coupon', 3, 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59');
INSERT INTO `point_shop_categories` VALUES (4, '会员特权', 'vip', 4, 1, '2025-11-01 12:58:59', '2025-11-01 12:58:59');

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
-- Table structure for security_logs
-- ----------------------------
DROP TABLE IF EXISTS `security_logs`;
CREATE TABLE `security_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '类型:sql_injection,xss_attack,csrf_attack,brute_force等',
  `level` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '级别:low,medium,high,critical',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'IP地址',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '请求URL',
  `method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '请求方法',
  `user_agent` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '用户代理',
  `request_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '请求数据',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '描述',
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否已拦截',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_type`(`type` ASC) USING BTREE,
  INDEX `idx_level`(`level` ASC) USING BTREE,
  INDEX `idx_ip`(`ip` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '安全日志' ROW_FORMAT = Dynamic;


-- ----------------------------
-- Table structure for seo_404_logs
-- ----------------------------
DROP TABLE IF EXISTS `seo_404_logs`;
CREATE TABLE `seo_404_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
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
  INDEX `idx_last_hit_time`(`last_hit_time` ASC) USING BTREE
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
  `keyword` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '关键词',
  `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '目标URL',
  `search_engine` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'baidu' COMMENT '搜索引擎：baidu, google, bing等',
  `ranking` int NULL DEFAULT NULL COMMENT '排名位置（1-100，NULL表示100名之外）',
  `check_date` date NOT NULL COMMENT '检查日期',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_keyword_engine_date`(`keyword` ASC, `search_engine` ASC, `check_date` ASC) USING BTREE,
  INDEX `idx_keyword`(`keyword` ASC) USING BTREE,
  INDEX `idx_check_date`(`check_date` ASC) USING BTREE
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
  INDEX `idx_hit_count`(`hit_count` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'URL重定向规则表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of seo_redirects
-- ----------------------------
INSERT INTO `seo_redirects` VALUES (1, '/old-page', '/new-page', 301, 'exact', 1, 0, NULL, '旧页面迁移到新页面', '2025-10-19 02:20:29', '2025-10-19 02:20:29');
INSERT INTO `seo_redirects` VALUES (2, '/blog/*', '/articles/*', 301, 'wildcard', 1, 0, NULL, '博客路径调整', '2025-10-19 02:20:29', '2025-10-19 02:20:29');

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
INSERT INTO `site_config` VALUES (13, 'current_template_theme', 'official', 'text', 'template', '当前模板套装', 101, '2025-11-03 20:45:18');
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
-- Table structure for slider_groups
-- ----------------------------
DROP TABLE IF EXISTS `slider_groups`;
CREATE TABLE `slider_groups`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分组ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '分组名称',
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '分组代码（唯一标识）',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '分组描述',
  `width` int NULL DEFAULT NULL COMMENT '图片宽度（像素）',
  `height` int NULL DEFAULT NULL COMMENT '图片高度（像素）',
  `auto_play` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否自动播放：0=否，1=是',
  `play_interval` int NOT NULL DEFAULT 3000 COMMENT '播放间隔（毫秒）',
  `animation` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'slide' COMMENT '动画效果：slide=滑动，fade=淡入淡出',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_code`(`code` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '幻灯片组表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of slider_groups
-- ----------------------------
INSERT INTO `slider_groups` VALUES (1, '首页轮播', 'home_slider', '首页顶部轮播图', 1920, 600, 1, 5000, 'slide', 1, '2025-10-19 01:29:18', '2025-10-19 01:29:18');
INSERT INTO `slider_groups` VALUES (2, '产品展示', 'product_slider', '产品页轮播展示', 800, 400, 1, 3000, 'fade', 1, '2025-10-19 01:29:18', '2025-10-19 01:29:18');
INSERT INTO `slider_groups` VALUES (3, '客户案例', 'case_slider', '客户案例轮播', 600, 400, 0, 3000, 'slide', 1, '2025-10-19 01:29:18', '2025-10-19 01:29:18');

-- ----------------------------
-- Table structure for sliders
-- ----------------------------
DROP TABLE IF EXISTS `sliders`;
CREATE TABLE `sliders`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '幻灯片ID',
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
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '幻灯片表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of sliders
-- ----------------------------
INSERT INTO `sliders` VALUES (1, 1, '欢迎使用CMS系统', 'https://www.carefreecms.com/uploads/2025/10/21/20251021002240_68f661d06afdb.jpg', '/about', '_blank', '功能强大、易于使用的内容管理系统', '了解更多', 1, 1, NULL, NULL, 0, 0, '2025-10-19 01:29:18', '2025-10-21 00:22:53', NULL);
INSERT INTO `sliders` VALUES (2, 1, '专业的技术支持', 'https://www.carefreecms.com/uploads/2025/10/21/20251021002300_68f661e417f0f.jpg', '/support', '_blank', '7x24小时技术支持服务', '联系我们', 2, 1, NULL, NULL, 0, 0, '2025-10-19 01:29:18', '2025-10-21 00:23:03', NULL);
INSERT INTO `sliders` VALUES (3, 1, '丰富的模板资源', 'https://www.carefreecms.com/uploads/2025/10/21/20251021002309_68f661eda7e00.jpg', '/templates', '_blank', '海量精美模板任您选择', '查看模板', 3, 1, NULL, NULL, 0, 0, '2025-10-19 01:29:18', '2025-10-21 00:23:13', NULL);

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
-- Table structure for static_build_log
-- ----------------------------
DROP TABLE IF EXISTS `static_build_log`;
CREATE TABLE `static_build_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '日志ID',
  `build_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '生成类型：manual/auto/schedule',
  `build_scope` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '生成范围：all/article/category/page/index',
  `target_id` int UNSIGNED NULL DEFAULT NULL COMMENT '目标ID（文章ID/分类ID等）',
  `file_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '生成文件数量',
  `success_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '成功数量',
  `fail_count` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '失败数量',
  `build_time` decimal(10, 2) NULL DEFAULT NULL COMMENT '生成耗时（秒）',
  `error_msg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '错误信息',
  `user_id` int UNSIGNED NULL DEFAULT NULL COMMENT '操作者ID',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=失败，1=成功，2=部分成功',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_build_type`(`build_type` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1305 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '静态页面生成日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for system_config
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `config_key` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '配置键',
  `config_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT '配置值',
  `create_time` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `config_key`(`config_key` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 29 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '系统配置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of system_config
-- ----------------------------
INSERT INTO `system_config` VALUES (1, 'site_status', 'open', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (2, 'site_name', '国产CMS', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (3, 'site_logo', 'http://localhost:8000/html/uploads/2025/10/14/20251014062422_68ed7c163fa4f.png', '2025-10-13 10:30:18', '2025-10-14 06:24:26');
INSERT INTO `system_config` VALUES (4, 'site_favicon', '', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (5, 'site_url', '', '2025-10-13 10:30:18', '2025-10-13 22:02:05');
INSERT INTO `system_config` VALUES (6, 'site_copyright', 'Copyright @ 2025 sinma.net', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (7, 'site_icp', '湘ICP备100010号', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (8, 'site_police', '湘GA备100011号', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (9, 'seo_title', '国产CMS-title', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (10, 'seo_keywords', '国产CMS,cms,cms中国', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (11, 'seo_description', '国产CMS描述文件', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (12, 'thirdparty_code_pc', '', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (13, 'thirdparty_code_mobile', '', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (14, 'mobile_domain_enable', 'open', '2025-10-13 10:30:18', '2025-10-13 10:30:52');
INSERT INTO `system_config` VALUES (15, 'https_enable', 'open', '2025-10-13 10:30:18', '2025-10-13 10:30:52');
INSERT INTO `system_config` VALUES (16, 'recycle_bin_enable', 'open', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (17, 'pc_to_mobile_js', 'append', '2025-10-13 10:30:18', '2025-10-13 10:30:52');
INSERT INTO `system_config` VALUES (18, 'article_sub_category', 'open', '2025-10-13 10:30:18', '2025-10-13 10:30:52');
INSERT INTO `system_config` VALUES (19, 'breadcrumb_home', '首页', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (20, 'breadcrumb_separator', '>', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (21, 'upload_image_ext', 'jpg|gif|png|bmp|jpeg|ico|webp', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (22, 'upload_file_ext', 'zip|gz|rar|iso|doc|xls|ppt|wps|docx|xlsx|pptx', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (23, 'upload_video_ext', 'swf|mpg|mp3|rm|rmvb|wmv|wma|wav|mid|mov|mp4', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (24, 'upload_max_size', '2', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (25, 'upload_rename', 'random', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (26, 'content_image_features', '', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (27, 'article_default_views', '500|1000', '2025-10-13 10:30:18', '2025-10-13 10:30:18');
INSERT INTO `system_config` VALUES (28, 'article_default_downloads', '100|500', '2025-10-13 10:30:18', '2025-10-13 10:30:18');

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
) ENGINE = InnoDB AUTO_INCREMENT = 460 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '系统日志' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tags
-- ----------------------------
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标签ID',
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
  UNIQUE INDEX `uk_name`(`name` ASC) USING BTREE,
  UNIQUE INDEX `uk_slug`(`slug` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '标签表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tags
-- ----------------------------
INSERT INTO `tags` VALUES (1, '测试标签1', 'test1', '测试标签测试标签测试标签1', 0, 0, 1, '2025-10-13 04:28:06', '2025-10-13 04:28:06', NULL);
INSERT INTO `tags` VALUES (3, '系统介绍', 'jieshao', '系统介绍', 0, 0, 1, '2025-10-21 08:41:30', '2025-10-21 08:41:30', NULL);

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
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '模板历史记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for templates
-- ----------------------------
DROP TABLE IF EXISTS `templates`;
CREATE TABLE `templates`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '模板ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板名称',
  `template_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板标识',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '模板描述',
  `preview_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '预览图',
  `template_path` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模板路径',
  `is_default` tinyint NOT NULL DEFAULT 0 COMMENT '是否默认：0=否，1=是',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态：0=禁用，1=启用',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_template_key`(`template_key` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '模板管理表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of templates
-- ----------------------------
INSERT INTO `templates` VALUES (1, '默认模板', 'default', '系统默认模板', NULL, 'default', 1, 1, '2025-10-12 02:12:51', '2025-10-12 02:12:51');
INSERT INTO `templates` VALUES (2, '首页模板', 'index', '网站首页默认模板', NULL, 'index', 0, 1, '2025-10-15 21:45:51', '2025-10-15 21:45:51');
INSERT INTO `templates` VALUES (3, '分类页模板', 'category', '分类列表页默认模板', NULL, 'category', 0, 1, '2025-10-15 21:45:51', '2025-10-15 21:45:51');
INSERT INTO `templates` VALUES (4, '单页模板', 'page', '单页面默认模板', NULL, 'page', 0, 1, '2025-10-15 21:45:51', '2025-10-15 21:45:51');
INSERT INTO `templates` VALUES (5, '文章列表模板', 'articles', '文章列表页模板', NULL, 'articles', 0, 1, '2025-10-15 21:45:51', '2025-10-15 21:45:51');
INSERT INTO `templates` VALUES (6, '文章详情模板', 'article', '文章详情页模板', NULL, 'article', 0, 1, '2025-10-15 21:45:51', '2025-10-15 21:45:51');
INSERT INTO `templates` VALUES (7, '标签页模板', 'tag', '标签页模板', NULL, 'tag', 0, 1, '2025-10-15 21:45:51', '2025-10-15 21:45:51');

-- ----------------------------
-- Table structure for topic_articles
-- ----------------------------
DROP TABLE IF EXISTS `topic_articles`;
CREATE TABLE `topic_articles`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `topic_id` int UNSIGNED NOT NULL COMMENT '专题ID',
  `article_id` int UNSIGNED NOT NULL COMMENT '文章ID',
  `sort` int NOT NULL DEFAULT 0 COMMENT '在专题中的排序',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否精选：0=否，1=是',
  `create_time` datetime NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_topic_article`(`topic_id` ASC, `article_id` ASC) USING BTREE,
  INDEX `idx_topic_id`(`topic_id` ASC) USING BTREE,
  INDEX `idx_article_id`(`article_id` ASC) USING BTREE,
  INDEX `idx_sort`(`sort` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '专题-文章关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of topic_articles
-- ----------------------------
INSERT INTO `topic_articles` VALUES (5, 1, 1, 0, 0, '2025-10-23 11:32:26');
INSERT INTO `topic_articles` VALUES (9, 2, 2, 0, 0, '2025-11-06 15:41:01');

-- ----------------------------
-- Table structure for topics
-- ----------------------------
DROP TABLE IF EXISTS `topics`;
CREATE TABLE `topics`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '专题ID',
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
  UNIQUE INDEX `uk_slug`(`slug` ASC) USING BTREE,
  INDEX `idx_status`(`status` ASC) USING BTREE,
  INDEX `idx_recommended`(`is_recommended` ASC) USING BTREE,
  INDEX `idx_sort`(`sort` ASC) USING BTREE,
  INDEX `idx_deleted_at`(`deleted_at` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '专题表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of topics
-- ----------------------------
INSERT INTO `topics` VALUES (1, '热门推荐', 'hot-recommend', '精选热门文章推荐专题', NULL, 'topic', NULL, NULL, NULL, 1, 1, 1, 0, 0, '2025-10-18 23:35:33', '2025-10-18 23:35:33', NULL);
INSERT INTO `topics` VALUES (2, '技术分享', 'tech-share', '技术文章分享专题', NULL, 'topic', NULL, NULL, NULL, 1, 1, 2, 0, 0, '2025-10-18 23:35:33', '2025-10-18 23:35:33', NULL);
INSERT INTO `topics` VALUES (3, '行业资讯', 'industry-news', '最新行业动态资讯', NULL, 'topic', NULL, NULL, NULL, 0, 1, 3, 0, 0, '2025-10-18 23:35:33', '2025-10-18 23:35:33', NULL);
INSERT INTO `topics` VALUES (4, '测试专题', 'testzhuanti', '这是个用于测试的专题', '', 'topic', '', '', '', 0, 1, 5, 0, 0, '2025-10-21 08:42:45', '2025-10-21 08:42:45', NULL);

-- ----------------------------
-- Table structure for user_favorites
-- ----------------------------
DROP TABLE IF EXISTS `user_favorites`;
CREATE TABLE `user_favorites`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '收藏ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `article_id` int UNSIGNED NOT NULL COMMENT '文章ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '收藏时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_user_article`(`user_id` ASC, `article_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_article_id`(`article_id` ASC) USING BTREE,
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户收藏表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_favorites
-- ----------------------------

-- ----------------------------
-- Table structure for user_follows
-- ----------------------------
DROP TABLE IF EXISTS `user_follows`;
CREATE TABLE `user_follows`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '关注ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '关注者ID',
  `follow_user_id` int UNSIGNED NOT NULL COMMENT '被关注者ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '关注时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_user_follow`(`user_id` ASC, `follow_user_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_follow_user_id`(`follow_user_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户关注表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_follows
-- ----------------------------

-- ----------------------------
-- Table structure for user_likes
-- ----------------------------
DROP TABLE IF EXISTS `user_likes`;
CREATE TABLE `user_likes`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '点赞ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `target_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '目标类型：article=文章，comment=评论',
  `target_id` int UNSIGNED NOT NULL COMMENT '目标ID',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '点赞时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_user_target`(`user_id` ASC, `target_type` ASC, `target_id` ASC) USING BTREE,
  INDEX `idx_user_id`(`user_id` ASC) USING BTREE,
  INDEX `idx_target`(`target_type` ASC, `target_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户点赞表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_likes
-- ----------------------------

-- ----------------------------
-- Table structure for user_notification_settings
-- ----------------------------
DROP TABLE IF EXISTS `user_notification_settings`;
CREATE TABLE `user_notification_settings`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int UNSIGNED NOT NULL COMMENT '用户ID',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '通知类型',
  `site_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '站内消息',
  `email_enabled` tinyint(1) NOT NULL DEFAULT 1 COMMENT '邮件通知',
  `sms_enabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '短信通知',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `user_type`(`user_id` ASC, `type` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '用户消息设置表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_notification_settings
-- ----------------------------
INSERT INTO `user_notification_settings` VALUES (1, 3, 'system', 1, 1, 0, '2025-11-01 13:04:54', '2025-11-01 13:04:54');

-- ----------------------------
-- Table structure for user_point_logs
-- ----------------------------
DROP TABLE IF EXISTS `user_point_logs`;
CREATE TABLE `user_point_logs`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '记录ID',
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
  INDEX `idx_create_time`(`create_time` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户积分记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_point_logs
-- ----------------------------
INSERT INTO `user_point_logs` VALUES (1, 1, 10, 10, 'register', '注册奖励', NULL, NULL, '2025-10-28 11:36:15');
INSERT INTO `user_point_logs` VALUES (2, 2, 10, 10, 'register', '注册奖励', NULL, NULL, '2025-10-28 11:36:57');
INSERT INTO `user_point_logs` VALUES (3, 3, 10, 10, 'test', '测试增加积分', NULL, NULL, '2025-11-01 12:48:53');
INSERT INTO `user_point_logs` VALUES (4, 3, -5, 5, 'test', '测试扣除积分', NULL, NULL, '2025-11-01 12:48:53');
INSERT INTO `user_point_logs` VALUES (5, 3, 200, 205, 'test', '测试添加积分', NULL, NULL, '2025-11-01 13:16:38');
INSERT INTO `user_point_logs` VALUES (6, 3, -100, 105, 'point_shop', '兑换商品：测试虚拟商品', 'order', NULL, '2025-11-01 13:16:38');
INSERT INTO `user_point_logs` VALUES (7, 4, 100, 100, 'admin_add', '管理员调整', NULL, NULL, '2025-11-02 05:48:45');
INSERT INTO `user_point_logs` VALUES (8, 4, 1, 101, 'admin_add', '管理员调整', NULL, NULL, '2025-11-04 14:47:05');

-- ----------------------------
-- Table structure for user_read_history
-- ----------------------------
DROP TABLE IF EXISTS `user_read_history`;
CREATE TABLE `user_read_history`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '历史ID',
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
  INDEX `idx_update_time`(`update_time` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户阅读历史表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_read_history
-- ----------------------------

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

SET FOREIGN_KEY_CHECKS = 1;
