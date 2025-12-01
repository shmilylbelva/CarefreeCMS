<?php

use think\facade\Route;

// ============================================
// API路由配置
// ============================================

// 跨域中间件应用于所有API路由
Route::group('api', function () {

    // ========== 认证相关（不需要JWT认证） ==========
    Route::group('auth', function () {
        Route::post('login', 'app\controller\api\Auth@login');          // 登录
    });

    // ========== OAuth第三方登录（不需要JWT认证） ==========
    Route::group('oauth', function () {
        Route::get('enabled-platforms', 'app\controller\api\OAuthController@getEnabledPlatforms'); // 获取启用的平台
        Route::get('auth-url', 'app\controller\api\OAuthController@getAuthUrl');                  // 获取授权URL
        Route::get('callback', 'app\controller\api\OAuthController@callback');                     // OAuth回调
    });

    // ========== API文档（不需要JWT认证） ==========
    Route::group('api-doc', function () {
        Route::get('', 'app\controller\api\ApiDoc@index');                    // Swagger UI
        Route::get('json', 'app\controller\api\ApiDoc@json');                 // Swagger JSON
        Route::get('statistics', 'app\controller\api\ApiDoc@statistics');     // API统计
    });

    // ========== 需要JWT认证的接口 ==========
    Route::group(function () {

        // 用户信息与认证操作
        Route::get('auth/info', 'app\controller\api\Auth@info');                  // 获取当前用户信息
        Route::post('auth/logout', 'app\controller\api\Auth@logout');             // 退出登录（需要认证）
        Route::patch('auth/password', 'app\controller\api\Auth@updatePassword');  // 修改密码（RESTful）
        Route::post('auth/change-password', 'app\controller\api\Auth@changePassword'); // 修改密码（已废弃，使用PATCH）

        // OAuth账号绑定管理（需要认证）
        Route::get('oauth/user-bindings', 'app\controller\api\OAuthController@getUserBindings'); // 获取用户绑定列表
        Route::post('oauth/bind', 'app\controller\api\OAuthController@bind');                    // 绑定第三方账号
        Route::post('oauth/unbind', 'app\controller\api\OAuthController@unbind');                // 解绑第三方账号

        // 仪表板统计
        Route::get('dashboard/stats', 'app\controller\api\Dashboard@stats');           // 获取统计数据
        Route::get('dashboard/server-info', 'app\controller\api\Dashboard@serverInfo'); // 获取服务器信息
        Route::get('dashboard/system-info', 'app\controller\api\Dashboard@systemInfo'); // 获取系统信息

        // 文章管理
        Route::get('articles/fulltext-search', 'app\controller\api\Article@fullTextSearch');       // 全文搜索
        Route::get('articles/advanced-search', 'app\controller\api\Article@advancedSearch');       // 高级搜索
        Route::get('articles/search-suggestions', 'app\controller\api\Article@searchSuggestions'); // 搜索建议
        Route::get('articles/hot', 'app\controller\api\Article@hot');                              // 热门文章（带缓存）
        Route::get('articles/recommend', 'app\controller\api\Article@recommend');                  // 推荐文章（带缓存）
        Route::post('articles/generate-content', 'app\controller\api\Article@generateContent');    // AI生成文章内容
        Route::get('articles/:id/check-delete-media', 'app\controller\api\Article@checkDeleteMedia'); // 检查删除时的媒体使用
        Route::post('articles/:id/sync-media-usage', 'app\controller\api\Article@syncMediaUsage'); // 同步文章的媒体使用记录
        Route::resource('articles', 'app\controller\api\Article');                // RESTful文章资源
        Route::patch('articles/:id', 'app\controller\api\Article@patch');         // 部分更新文章（RESTful）
        Route::post('articles/:id/publish', 'app\controller\api\Article@publish');  // 发布文章（已废弃，使用PATCH）
        Route::post('articles/:id/offline', 'app\controller\api\Article@offline');  // 下线文章（已废弃，使用PATCH）
        Route::post('articles/batch-delete', 'app\controller\api\Article@batchDelete');  // 批量删除文章
        Route::post('articles/batch-publish', 'app\controller\api\Article@batchPublish');  // 批量发布文章
        Route::post('articles/batch-offline', 'app\controller\api\Article@batchOffline');  // 批量下线文章
        Route::post('articles/batch-update-category', 'app\controller\api\Article@batchUpdateCategory');  // 批量修改分类
        Route::get('articles/export', 'app\controller\api\Article@export');  // 导出文章

        // 文章版本管理
        Route::get('articles/:article_id/versions/statistics', 'app\controller\api\ArticleVersion@statistics'); // 获取版本统计
        Route::get('articles/:article_id/versions', 'app\controller\api\ArticleVersion@index');           // 获取文章的版本列表
        Route::get('article-versions/:id', 'app\controller\api\ArticleVersion@read');                     // 获取版本详情
        Route::get('article-versions/compare', 'app\controller\api\ArticleVersion@compare');              // 对比两个版本
        Route::post('article-versions/:id/rollback', 'app\controller\api\ArticleVersion@rollback');       // 回滚到指定版本
        Route::delete('article-versions/:id', 'app\controller\api\ArticleVersion@delete');                // 删除版本
        Route::post('article-versions/batch-delete', 'app\controller\api\ArticleVersion@batchDelete');    // 批量删除版本

        // 分类管理
        Route::get('categories/tree', 'app\controller\api\Category@tree');        // 分类树（需在resource之前）
        Route::resource('categories', 'app\controller\api\Category');             // RESTful分类资源

        // 标签管理
        Route::get('tags/all', 'app\controller\api\Tag@all');                     // 所有标签（不分页）
        Route::resource('tags', 'app\controller\api\Tag');                        // RESTful标签资源

        // 文章属性管理
        Route::get('article-flags/all', 'app\controller\api\ArticleFlag@all');   // 所有文章属性（不分页）
        Route::resource('article-flags', 'app\controller\api\ArticleFlag');       // RESTful文章属性资源

        // 页面管理
        Route::get('pages/all', 'app\controller\api\Page@all');                   // 所有单页（不分页）
        Route::resource('pages', 'app\controller\api\Page');                      // RESTful页面资源

        // 后台评论管理
        Route::get('admin/comments/statistics', 'app\controller\admin\CommentController@statistics');         // 评论统计
        Route::get('admin/comments/trend', 'app\controller\admin\CommentController@trend');                   // 评论趋势
        Route::get('admin/comments/active-users', 'app\controller\admin\CommentController@activeUsers');      // 活跃用户
        Route::get('admin/comments/hot', 'app\controller\admin\CommentController@hotComments');               // 热门评论
        Route::get('admin/comments/pending-count', 'app\controller\admin\CommentController@pendingCount');    // 待审核数量
        Route::post('admin/comments/batch-audit', 'app\controller\admin\CommentController@batchAudit');       // 批量审核
        Route::post('admin/comments/batch-delete', 'app\controller\admin\CommentController@batchDelete');     // 批量删除
        Route::post('admin/comments/batch-update-status', 'app\controller\admin\CommentController@batchUpdateStatus'); // 批量更新状态
        Route::post('admin/comments/:id/audit', 'app\controller\admin\CommentController@audit');              // 审核评论
        Route::post('admin/comments/:id/reply', 'app\controller\admin\CommentController@reply');              // 回复评论
        Route::post('admin/comments/:id/toggle-hot', 'app\controller\admin\CommentController@toggleHot');     // 切换热门标记
        Route::resource('admin/comments', 'app\controller\admin\CommentController');                           // RESTful评论资源

        // 后台评论举报管理
        Route::get('admin/comment-reports/statistics', 'app\controller\api\CommentReportController@statistics');   // 举报统计
        Route::post('admin/comment-reports/batch-handle', 'app\controller\api\CommentReportController@batchHandle'); // 批量处理
        Route::post('admin/comment-reports/:id/handle', 'app\controller\api\CommentReportController@handle');      // 处理举报
        Route::post('admin/comment-reports/:id/ignore', 'app\controller\api\CommentReportController@ignore');      // 忽略举报
        Route::resource('admin/comment-reports', 'app\controller\api\CommentReportController');                     // RESTful举报资源

        // 后台表情管理
        Route::get('admin/comment-emojis/categories', 'app\controller\admin\CommentEmojiController@categories');   // 表情分类
        Route::get('admin/comment-emojis/hot', 'app\controller\admin\CommentEmojiController@hotEmojis');           // 热门表情
        Route::get('admin/comment-emojis/statistics', 'app\controller\admin\CommentEmojiController@statistics');   // 表情统计
        Route::get('admin/comment-emojis/by-category', 'app\controller\admin\CommentEmojiController@getByCategory'); // 按分类获取
        Route::post('admin/comment-emojis/batch-import', 'app\controller\admin\CommentEmojiController@batchImport'); // 批量导入
        Route::post('admin/comment-emojis/batch-delete', 'app\controller\admin\CommentEmojiController@batchDelete'); // 批量删除
        Route::post('admin/comment-emojis/batch-toggle', 'app\controller\admin\CommentEmojiController@batchToggle'); // 批量启用/禁用
        Route::post('admin/comment-emojis/batch-reset-count', 'app\controller\admin\CommentEmojiController@batchResetUseCount'); // 批量重置使用次数
        Route::post('admin/comment-emojis/:id/update-sort', 'app\controller\admin\CommentEmojiController@updateSort'); // 更新排序
        Route::post('admin/comment-emojis/:id/reset-count', 'app\controller\admin\CommentEmojiController@resetUseCount'); // 重置使用次数
        Route::resource('admin/comment-emojis', 'app\controller\admin\CommentEmojiController');                      // RESTful表情资源

        // ========== 媒体库系统 ==========

        // 媒体文件管理
        Route::get('media/stats', 'app\controller\api\Media@stats');              // 存储统计
        Route::post('media/batch-upload', 'app\controller\api\Media@batchUpload'); // 批量上传
        Route::post('media/upload', 'app\controller\api\Media@upload');           // 上传文件
        Route::get('media/:id', 'app\controller\api\Media@read');                 // 获取详情
        Route::put('media/:id', 'app\controller\api\Media@update');               // 更新信息
        Route::delete('media/:id', 'app\controller\api\Media@delete');            // 删除文件
        Route::get('media', 'app\controller\api\Media@index');                    // 文件列表

        // 缩略图管理
        Route::get('media-thumbnail/presets', 'app\controller\api\MediaThumbnail@presets');           // 预设列表
        Route::get('media-thumbnail/presets/:id', 'app\controller\api\MediaThumbnail@readPreset');    // 获取预设
        Route::post('media-thumbnail/presets', 'app\controller\api\MediaThumbnail@createPreset');     // 创建预设
        Route::put('media-thumbnail/presets/:id', 'app\controller\api\MediaThumbnail@updatePreset');  // 更新预设
        Route::delete('media-thumbnail/presets/:id', 'app\controller\api\MediaThumbnail@deletePreset'); // 删除预设
        Route::post('media-thumbnail/generate', 'app\controller\api\MediaThumbnail@generate');         // 生成缩略图
        Route::post('media-thumbnail/batch-generate', 'app\controller\api\MediaThumbnail@batchGenerate'); // 批量生成
        Route::post('media-thumbnail/regenerate', 'app\controller\api\MediaThumbnail@regenerate');     // 重新生成
        Route::post('media-thumbnail/delete-all', 'app\controller\api\MediaThumbnail@deleteAll');      // 删除所有

        // 水印管理
        Route::get('media-watermark/presets', 'app\controller\api\MediaWatermark@presets');           // 预设列表
        Route::get('media-watermark/presets/:id', 'app\controller\api\MediaWatermark@readPreset');    // 获取预设
        Route::post('media-watermark/presets', 'app\controller\api\MediaWatermark@createPreset');     // 创建预设
        Route::put('media-watermark/presets/:id', 'app\controller\api\MediaWatermark@updatePreset');  // 更新预设
        Route::delete('media-watermark/presets/:id', 'app\controller\api\MediaWatermark@deletePreset'); // 删除预设
        Route::post('media-watermark/add', 'app\controller\api\MediaWatermark@add');                  // 添加水印
        Route::post('media-watermark/batch-add', 'app\controller\api\MediaWatermark@batchAdd');       // 批量添加
        Route::get('media-watermark/logs', 'app\controller\api\MediaWatermark@logs');                 // 处理日志

        // 媒体使用追踪
        Route::get('media-usage/:mediaId', 'app\controller\api\MediaUsage@getMediaUsage');            // 获取媒体使用情况
        Route::get('media-usage/used-media', 'app\controller\api\MediaUsage@getUsedMedia');           // 获取对象使用的媒体
        Route::get('media-usage/check-delete/:mediaId', 'app\controller\api\MediaUsage@checkSafeDelete'); // 检查是否可安全删除
        Route::get('media-usage/unused', 'app\controller\api\MediaUsage@getUnusedMedia');             // 获取未使用的媒体
        Route::post('media-usage/clean-unused', 'app\controller\api\MediaUsage@cleanUnused');         // 清理未使用的媒体
        Route::post('media-usage/record', 'app\controller\api\MediaUsage@recordUsage');               // 记录使用
        Route::post('media-usage/remove', 'app\controller\api\MediaUsage@removeUsage');               // 删除使用记录

        // 图片编辑
        Route::post('media-edit/resize', 'app\controller\api\MediaEdit@resize');           // 调整大小
        Route::post('media-edit/crop', 'app\controller\api\MediaEdit@crop');               // 裁剪
        Route::post('media-edit/rotate', 'app\controller\api\MediaEdit@rotate');           // 旋转
        Route::post('media-edit/flip', 'app\controller\api\MediaEdit@flip');               // 翻转
        Route::post('media-edit/brightness', 'app\controller\api\MediaEdit@brightness');   // 亮度
        Route::post('media-edit/contrast', 'app\controller\api\MediaEdit@contrast');       // 对比度
        Route::post('media-edit/grayscale', 'app\controller\api\MediaEdit@grayscale');     // 灰度化
        Route::post('media-edit/sharpen', 'app\controller\api\MediaEdit@sharpen');         // 锐化
        Route::post('media-edit/blur', 'app\controller\api\MediaEdit@blur');               // 模糊
        Route::post('media-edit/filter', 'app\controller\api\MediaEdit@filter');           // 滤镜
        Route::get('media-edit/filters', 'app\controller\api\MediaEdit@filters');          // 滤镜列表
        Route::get('media-edit/history', 'app\controller\api\MediaEdit@history');          // 编辑历史

        // AI图片生成
        Route::get('ai-image/models', 'app\controller\api\AiImageGeneration@models');      // AI模型列表
        Route::get('ai-image/tasks', 'app\controller\api\AiImageGeneration@tasks');        // 任务列表
        Route::get('ai-image/tasks/:id', 'app\controller\api\AiImageGeneration@taskDetail'); // 任务详情
        Route::post('ai-image/tasks', 'app\controller\api\AiImageGeneration@create');      // 创建任务
        Route::post('ai-image/execute', 'app\controller\api\AiImageGeneration@execute');   // 执行任务
        Route::post('ai-image/cancel', 'app\controller\api\AiImageGeneration@cancel');     // 取消任务
        Route::post('ai-image/retry', 'app\controller\api\AiImageGeneration@retry');       // 重试任务
        Route::get('ai-image/stats', 'app\controller\api\AiImageGeneration@stats');        // 任务统计

        // AI图片提示词模板
        Route::get('ai-image/prompt-templates', 'app\controller\api\AiImageGeneration@templates');        // 模板列表
        Route::get('ai-image/prompt-templates/popular', 'app\controller\api\AiImageGeneration@popularTemplates'); // 热门模板
        Route::post('ai-image/prompt-templates', 'app\controller\api\AiImageGeneration@createTemplate');  // 创建模板
        Route::put('ai-image/prompt-templates/:id', 'app\controller\api\AiImageGeneration@updateTemplate'); // 更新模板
        Route::delete('ai-image/prompt-templates/:id', 'app\controller\api\AiImageGeneration@deleteTemplate'); // 删除模板

        // 存储配置管理
        Route::get('storage-config/drivers', 'app\controller\api\StorageConfig@drivers');          // 支持的驱动
        Route::get('storage-config/driver-template/:driver', 'app\controller\api\StorageConfig@driverTemplate'); // 驱动配置模板
        Route::post('storage-config/test', 'app\controller\api\StorageConfig@test');               // 测试连接
        Route::post('storage-config/sort', 'app\controller\api\StorageConfig@sort');               // 批量排序
        Route::get('storage-config', 'app\controller\api\StorageConfig@index');                    // 配置列表
        Route::post('storage-config', 'app\controller\api\StorageConfig@create');                  // 创建配置
        Route::get('storage-config/:id', 'app\controller\api\StorageConfig@read');                 // 配置详情
        Route::put('storage-config/:id', 'app\controller\api\StorageConfig@update');               // 更新配置
        Route::delete('storage-config/:id', 'app\controller\api\StorageConfig@delete');            // 删除配置
        Route::post('storage-config/:id/set-default', 'app\controller\api\StorageConfig@setDefault'); // 设为默认

        // 队列管理
        Route::post('queue/ai-image', 'app\controller\api\QueueManage@pushAiImageJob');            // 推送AI图片生成任务
        Route::post('queue/batch-thumbnail', 'app\controller\api\QueueManage@pushBatchThumbnailJob'); // 推送批量缩略图任务
        Route::post('queue/batch-watermark', 'app\controller\api\QueueManage@pushBatchWatermarkJob'); // 推送批量水印任务
        Route::post('queue/video-transcode', 'app\controller\api\QueueManage@pushVideoTranscodeJob'); // 推送视频转码任务
        Route::post('queue/later', 'app\controller\api\QueueManage@pushLater');                    // 延迟推送任务
        Route::get('queue/stats', 'app\controller\api\QueueManage@stats');                         // 队列统计
        Route::post('queue/clear', 'app\controller\api\QueueManage@clear');                        // 清空队列
        Route::get('queue/ai-image/tasks', 'app\controller\api\QueueManage@getAiImageTasks');      // 获取AI图片任务列表
        Route::post('queue/ai-image/tasks/:taskId/retry', 'app\controller\api\QueueManage@retryAiImageTask'); // 重试AI图片任务
        Route::post('queue/ai-image/tasks/:taskId/cancel', 'app\controller\api\QueueManage@cancelAiImageTask'); // 取消AI图片任务
        Route::delete('queue/ai-image/tasks/:taskId', 'app\controller\api\QueueManage@deleteAiImageTask'); // 删除AI图片任务
        Route::get('queue/video/tasks', 'app\controller\api\QueueManage@getVideoTranscodeTasks'); // 获取视频转码任务列表
        Route::post('queue/video/tasks/:taskId/retry', 'app\controller\api\QueueManage@retryVideoTranscodeTask'); // 重试视频转码任务
        Route::post('queue/video/tasks/:taskId/cancel', 'app\controller\api\QueueManage@cancelVideoTranscodeTask'); // 取消视频转码任务
        Route::delete('queue/video/tasks/:taskId', 'app\controller\api\QueueManage@deleteVideoTranscodeTask'); // 删除视频转码任务
        Route::get('queue/logs', 'app\controller\api\QueueManage@getLogs');                        // 获取队列日志

        // 视频处理
        Route::get('video/info', 'app\controller\api\VideoProcessing@info');                       // 获取视频信息
        Route::post('video/transcode', 'app\controller\api\VideoProcessing@transcode');            // 视频转码
        Route::post('video/generate-poster', 'app\controller\api\VideoProcessing@generatePoster'); // 生成封面
        Route::post('video/generate-thumbnails', 'app\controller\api\VideoProcessing@generateThumbnails'); // 生成预览图
        Route::get('video/transcode-records', 'app\controller\api\VideoProcessing@transcodeRecords'); // 转码记录列表
        Route::get('video/transcode-records/:id', 'app\controller\api\VideoProcessing@transcodeRecordDetail'); // 转码记录详情
        Route::get('video/transcode-stats', 'app\controller\api\VideoProcessing@transcodeStats');  // 转码统计

        // 分片上传
        Route::post('chunked-upload/init', 'app\controller\api\ChunkedUpload@init');               // 初始化上传会话
        Route::post('chunked-upload/chunk', 'app\controller\api\ChunkedUpload@uploadChunk');       // 上传分片
        Route::post('chunked-upload/merge', 'app\controller\api\ChunkedUpload@merge');             // 合并分片
        Route::get('chunked-upload/progress', 'app\controller\api\ChunkedUpload@progress');        // 获取进度
        Route::post('chunked-upload/cancel', 'app\controller\api\ChunkedUpload@cancel');           // 取消上传
        Route::post('chunked-upload/cleanup', 'app\controller\api\ChunkedUpload@cleanup');         // 清理过期会话

        // 用户管理
        Route::post('users/:id/reset-password', 'app\controller\api\User@resetPassword'); // 重置密码（需在resource之前）
        Route::resource('users', 'app\controller\api\User');                      // RESTful用户资源

        // 角色管理
        Route::get('roles/all', 'app\controller\api\Role@all');                   // 所有角色（不分页）
        Route::resource('roles', 'app\controller\api\Role');                      // RESTful角色资源

        // 个人信息
        Route::get('profile/permissions', 'app\controller\api\Profile@permissions'); // 获取当前用户权限
        Route::get('profile/password', function() { return \app\common\Response::error('不支持的方法', 405); }); // 占位路由，防止匹配到updatePassword
        Route::get('profile/avatar', function() { return \app\common\Response::error('不支持的方法', 405); }); // 占位路由
        Route::get('profile', 'app\controller\api\Profile@index');                // 获取个人信息
        Route::put('profile', 'app\controller\api\Profile@update');               // 更新个人信息
        Route::patch('profile/password', 'app\controller\api\Profile@updatePassword'); // 修改密码（RESTful）
        Route::post('profile/password', 'app\controller\api\Profile@updatePassword'); // 修改密码（已废弃，使用PATCH）
        Route::post('profile/avatar', 'app\controller\api\Profile@uploadAvatar'); // 上传头像

        // 操作日志
        Route::get('operation-logs', 'app\controller\api\OperationLog@index');    // 日志列表
        Route::get('operation-logs/modules', 'app\controller\api\OperationLog@modules'); // 模块列表
        Route::get('operation-logs/actions', 'app\controller\api\OperationLog@actions'); // 操作类型列表
        Route::get('operation-logs/:id', 'app\controller\api\OperationLog@read'); // 日志详情
        Route::post('operation-logs/clear', 'app\controller\api\OperationLog@clear'); // 清空日志
        Route::post('operation-log/batch-delete', 'app\controller\api\OperationLog@batchDelete'); // 批量删除日志

        // 模板管理（注意：更具体的路由要放在前面）
        // 在线模板编辑（必须放在最前面，避免被 templates 通用路由匹配）
        Route::get('templates/file-tree', 'app\controller\api\Template@getFileTree');     // 获取文件树
        Route::get('templates/read-file', 'app\controller\api\Template@readFile');        // 读取文件
        Route::post('templates/save-file', 'app\controller\api\Template@saveFile');       // 保存文件
        Route::post('templates/create-file', 'app\controller\api\Template@createFile');   // 创建文件
        Route::post('templates/delete-file', 'app\controller\api\Template@deleteFile');   // 删除文件
        Route::get('templates/backups', 'app\controller\api\Template@getBackups');        // 获取历史记录列表
        Route::get('templates/history-content', 'app\controller\api\Template@getHistoryContent'); // 获取历史版本内容
        Route::post('templates/restore-history', 'app\controller\api\Template@restoreHistory');   // 恢复历史版本

        // 模板套装管理
        Route::get('templates/current-theme', 'app\controller\api\Template@getCurrentTheme'); // 获取当前模板套装
        Route::get('templates/themes', 'app\controller\api\Template@scanThemes');  // 扫描所有模板套装
        Route::get('templates/scan', 'app\controller\api\Template@scanTemplates'); // 扫描模板文件
        Route::post('templates/switch-theme', 'app\controller\api\Template@switchTheme'); // 切换模板套装
        Route::get('templates', 'app\controller\api\Template@scanTemplates');     // 获取当前套装的模板文件列表

        // 模板包管理（多站点模板系统）
        Route::get('template-packages/all', 'app\controller\api\TemplatePackageController@all');                    // 获取所有可用模板包（下拉框）
        Route::get('template-packages/:id/templates', 'app\controller\api\TemplatePackageController@templates');    // 获取模板包的模板列表
        Route::post('template-packages/:id/copy', 'app\controller\api\TemplatePackageController@copy');             // 复制模板包
        Route::get('template-packages/:id/export', 'app\controller\api\TemplatePackageController@export');          // 导出模板包
        Route::post('template-packages/import', 'app\controller\api\TemplatePackageController@import');             // 导入模板包
        Route::resource('template-packages', 'app\controller\api\TemplatePackageController');                       // RESTful模板包资源

        // 模板包内模板管理
        Route::get('template/index', 'app\controller\api\TemplateController@index');                               // 获取模板列表
        Route::post('template/save', 'app\controller\api\TemplateController@save');                                // 创建模板
        Route::put('template/update/:id', 'app\controller\api\TemplateController@update');                         // 更新模板
        Route::delete('template/delete/:id', 'app\controller\api\TemplateController@delete');                      // 删除模板
        Route::get('template/read/:id', 'app\controller\api\TemplateController@read');                             // 获取模板详情
        Route::post('template/save-content', 'app\controller\api\TemplateController@saveContent');                 // 保存模板内容
        Route::post('template/copy', 'app\controller\api\TemplateController@copy');                                // 复制模板
        Route::post('template/batch', 'app\controller\api\TemplateController@batch');                              // 批量操作
        Route::get('template/package-files', 'app\controller\api\TemplateController@getPackageFiles');             // 获取模板包文件列表
        Route::get('template/check-file', 'app\controller\api\TemplateController@checkFileExists');                // 检查模板文件是否存在

        // 模板类型管理
        Route::get('template-type/index', 'app\controller\api\TemplateTypeController@index');                      // 获取模板类型列表
        Route::get('template-type/options', 'app\controller\api\TemplateTypeController@options');                  // 获取模板类型选项
        Route::get('template-type/detail/:id', 'app\controller\api\TemplateTypeController@detail');                // 获取模板类型详情
        Route::post('template-type/save', 'app\controller\api\TemplateTypeController@save');                       // 创建模板类型
        Route::put('template-type/update/:id', 'app\controller\api\TemplateTypeController@update');                // 更新模板类型
        Route::delete('template-type/delete/:id', 'app\controller\api\TemplateTypeController@delete');             // 删除模板类型
        Route::post('template-type/batch-delete', 'app\controller\api\TemplateTypeController@batchDelete');        // 批量删除
        Route::post('template-type/update-sort', 'app\controller\api\TemplateTypeController@updateSort');          // 更新排序
        Route::post('template-type/toggle-status/:id', 'app\controller\api\TemplateTypeController@toggleStatus');  // 切换状态

        // 静态页面生成
        Route::post('build/all-sites', 'app\controller\api\Build@buildAllSites'); // 生成所有站点（新）
        Route::post('build/all', 'app\controller\api\Build@all');                 // 生成所有静态页（支持site_id参数）
        Route::post('build/index', 'app\controller\api\Build@index');             // 生成首页
        Route::post('build/articles', 'app\controller\api\Build@articles');       // 生成文章列表页
        Route::post('build/article/:id', 'app\controller\api\Build@article');     // 生成文章详情页
        Route::post('build/categories', 'app\controller\api\Build@categories');   // 生成所有分类页
        Route::post('build/category/:id', 'app\controller\api\Build@category');   // 生成单个分类页
        Route::post('build/tags', 'app\controller\api\Build@tags');               // 生成所有标签页
        Route::post('build/tag/:id', 'app\controller\api\Build@tag');             // 生成单个标签页
        Route::post('build/topics', 'app\controller\api\Build@topics');           // 生成所有专题页
        Route::post('build/topic/:id', 'app\controller\api\Build@topic');         // 生成单个专题页
        Route::post('build/pages', 'app\controller\api\Build@pages');             // 生成所有单页面
        Route::post('build/page/:id', 'app\controller\api\Build@page');           // 生成单个单页面
        Route::get('build/logs', 'app\controller\api\Build@logs');                // 生成日志

        // 模板资源管理
        Route::post('build/sync-assets', 'app\controller\api\Build@syncAssets');  // 同步模板资源到静态目录
        Route::post('build/clean-assets', 'app\controller\api\Build@cleanAssets'); // 清理旧资源
        Route::get('build/assets-list', 'app\controller\api\Build@getAssetsList'); // 获取资源列表

        // Sitemap生成
        Route::post('sitemap/all', 'app\controller\api\Sitemap@generateAll');     // 生成所有格式sitemap
        Route::post('sitemap/txt', 'app\controller\api\Sitemap@generateTxt');     // 生成TXT格式
        Route::post('sitemap/xml', 'app\controller\api\Sitemap@generateXml');     // 生成XML格式
        Route::post('sitemap/html', 'app\controller\api\Sitemap@generateHtml');   // 生成HTML格式

        // 回收站管理
        Route::get('recycle-bin/statistics', 'app\controller\api\RecycleBin@statistics');    // 回收站统计
        Route::post('recycle-bin/restore', 'app\controller\api\RecycleBin@restore');         // 恢复单个
        Route::post('recycle-bin/batch-restore', 'app\controller\api\RecycleBin@batchRestore'); // 批量恢复
        Route::post('recycle-bin/batch-destroy', 'app\controller\api\RecycleBin@batchDestroy'); // 批量彻底删除
        Route::post('recycle-bin/clear', 'app\controller\api\RecycleBin@clear');             // 清空回收站
        Route::delete('recycle-bin/:type/:id', 'app\controller\api\RecycleBin@destroy');     // 彻底删除
        Route::get('recycle-bin', 'app\controller\api\RecycleBin@index');                    // 回收站列表

        // 内容模型管理
        Route::get('content-models/all', 'app\controller\api\ContentModelController@all');   // 获取所有模型（不分页）
        Route::resource('content-models', 'app\controller\api\ContentModelController');      // RESTful内容模型资源

        // 自定义字段管理
        Route::get('custom-fields/field-types', 'app\controller\api\CustomFieldController@getFieldTypes');  // 字段类型列表
        Route::get('custom-fields/model-types', 'app\controller\api\CustomFieldController@getModelTypes');  // 模型类型列表
        Route::get('custom-fields/by-model', 'app\controller\api\CustomFieldController@getByModel');        // 根据模型获取字段
        Route::get('custom-fields/entity-values', 'app\controller\api\CustomFieldController@getEntityValues'); // 获取实体字段值
        Route::post('custom-fields/entity-values', 'app\controller\api\CustomFieldController@saveEntityValues'); // 保存实体字段值
        Route::resource('custom-fields', 'app\controller\api\CustomFieldController');        // RESTful自定义字段资源

        // 专题管理
        Route::get('topics/all', 'app\controller\api\TopicController@all');                         // 获取所有专题（不分页）
        Route::get('topics/:id/articles', 'app\controller\api\TopicController@articles');           // 获取专题的文章列表
        Route::post('topics/:id/articles', 'app\controller\api\TopicController@addArticle');        // 添加文章到专题
        Route::delete('topics/:id/articles/:article_id', 'app\controller\api\TopicController@removeArticle'); // 从专题移除文章
        Route::post('topics/:id/articles/batch', 'app\controller\api\TopicController@setArticles'); // 批量设置专题文章
        Route::put('topics/:id/articles/:article_id/sort', 'app\controller\api\TopicController@updateArticleSort'); // 更新文章排序
        Route::put('topics/:id/articles/:article_id/featured', 'app\controller\api\TopicController@setArticleFeatured'); // 设置精选文章
        Route::resource('topics', 'app\controller\api\TopicController');                             // RESTful专题资源

        // 友链分组管理
        Route::get('link-groups/all', 'app\controller\api\LinkGroupController@all');                // 获取所有分组（不分页）
        Route::resource('link-groups', 'app\controller\api\LinkGroupController');                    // RESTful分组资源

        // 友情链接管理
        Route::post('links/:id/audit', 'app\controller\api\LinkController@audit');                  // 审核链接
        Route::resource('links', 'app\controller\api\LinkController');                               // RESTful链接资源

        // 广告位管理
        Route::get('ad-positions/all', 'app\controller\api\AdPositionController@all');              // 获取所有广告位（不分页）
        Route::resource('ad-positions', 'app\controller\api\AdPositionController');                  // RESTful广告位资源

        // 广告管理
        Route::get('ads/:id/statistics', 'app\controller\api\AdController@statistics');             // 获取广告统计
        Route::post('ads/:id/click', 'app\controller\api\AdController@click');                      // 记录广告点击
        Route::resource('ads', 'app\controller\api\AdController');                                   // RESTful广告资源

        // 幻灯片组管理
        Route::get('slider-groups/all', 'app\controller\api\SliderGroupController@all');            // 获取所有分组（不分页）
        Route::resource('slider-groups', 'app\controller\api\SliderGroupController');                // RESTful幻灯片组资源

        // 幻灯片管理
        Route::get('sliders/group/:code', 'app\controller\api\SliderController@getByGroupCode');    // 按分组代码获取幻灯片
        Route::post('sliders/:id/click', 'app\controller\api\SliderController@click');              // 记录幻灯片点击
        Route::post('sliders/:id/view', 'app\controller\api\SliderController@view');                // 记录幻灯片展示
        Route::resource('sliders', 'app\controller\api\SliderController');                           // RESTful幻灯片资源

        // SEO重定向管理
        Route::get('seo-redirects/statistics', 'app\controller\api\SeoRedirectController@statistics');  // 重定向统计
        Route::post('seo-redirects/test', 'app\controller\api\SeoRedirectController@test');             // 测试重定向
        Route::post('seo-redirects/import', 'app\controller\api\SeoRedirectController@import');         // 导入规则
        Route::get('seo-redirects/export', 'app\controller\api\SeoRedirectController@export');          // 导出规则
        Route::get('seo-redirects/options', 'app\controller\api\SeoRedirectController@options');        // 获取配置选项
        Route::post('seo-redirects/batch-delete', 'app\controller\api\SeoRedirectController@batchDelete');  // 批量删除
        Route::post('seo-redirects/batch-toggle', 'app\controller\api\SeoRedirectController@batchToggle');  // 批量启用/禁用
        Route::resource('seo-redirects', 'app\controller\api\SeoRedirectController');                  // RESTful重定向资源

        // SEO 404日志管理
        Route::get('seo-404-logs/statistics', 'app\controller\api\Seo404LogController@statistics');    // 404统计
        Route::get('seo-404-logs/top-errors', 'app\controller\api\Seo404LogController@topErrors');     // 高频404
        Route::get('seo-404-logs/recent-errors', 'app\controller\api\Seo404LogController@recentErrors'); // 最近404
        Route::get('seo-404-logs/export', 'app\controller\api\Seo404LogController@export');            // 导出日志
        Route::post('seo-404-logs/:id/mark-fixed', 'app\controller\api\Seo404LogController@markFixed'); // 标记已修复
        Route::post('seo-404-logs/:id/ignore', 'app\controller\api\Seo404LogController@ignore');       // 忽略
        Route::post('seo-404-logs/:id/create-redirect', 'app\controller\api\Seo404LogController@createRedirect'); // 创建重定向
        Route::post('seo-404-logs/batch-mark-fixed', 'app\controller\api\Seo404LogController@batchMarkFixed'); // 批量标记
        Route::post('seo-404-logs/batch-delete', 'app\controller\api\Seo404LogController@batchDelete'); // 批量删除
        Route::post('seo-404-logs/clean', 'app\controller\api\Seo404LogController@clean');            // 清理旧日志
        Route::resource('seo-404-logs', 'app\controller\api\Seo404LogController');                     // RESTful 404日志资源

        // SEO Robots.txt管理
        Route::get('seo-robots/active', 'app\controller\api\SeoRobotController@active');               // 获取启用的配置
        Route::get('seo-robots/templates', 'app\controller\api\SeoRobotController@templates');         // 获取模板列表
        Route::get('seo-robots/current', 'app\controller\api\SeoRobotController@current');             // 查看当前文件
        Route::post('seo-robots/validate', 'app\controller\api\SeoRobotController@validateContent');    // 验证内容
        Route::post('seo-robots/apply-template', 'app\controller\api\SeoRobotController@applyTemplate'); // 应用模板
        Route::post('seo-robots/generate', 'app\controller\api\SeoRobotController@generate');          // 生成文件
        Route::post('seo-robots/:id/activate', 'app\controller\api\SeoRobotController@activate');      // 启用配置
        Route::resource('seo-robots', 'app\controller\api\SeoRobotController');                         // RESTful Robots资源

        // SEO分析工具
        Route::post('seo-analyzer/analyze', 'app\controller\api\SeoAnalyzerController@analyzeArticle');        // 分析文章SEO
        Route::get('seo-analyzer/analyze/:id', 'app\controller\api\SeoAnalyzerController@analyzeArticle');     // 分析指定文章
        Route::post('seo-analyzer/keyword-density', 'app\controller\api\SeoAnalyzerController@keywordDensity'); // 关键词密度
        Route::post('seo-analyzer/generate-title', 'app\controller\api\SeoAnalyzerController@generateTitle');  // 生成标题
        Route::post('seo-analyzer/generate-description', 'app\controller\api\SeoAnalyzerController@generateDescription'); // 生成描述
        Route::post('seo-analyzer/extract-keywords', 'app\controller\api\SeoAnalyzerController@extractKeywords'); // 提取关键词
        Route::get('seo-analyzer/suggestions/:id', 'app\controller\api\SeoAnalyzerController@getSuggestions'); // 获取建议
        Route::post('seo-analyzer/auto-optimize/:id', 'app\controller\api\SeoAnalyzerController@autoOptimize'); // 自动优化
        Route::post('seo-analyzer/batch-analyze', 'app\controller\api\SeoAnalyzerController@batchAnalyze');    // 批量分析

        // 增强Sitemap生成
        Route::post('seo-sitemap/generate', 'app\controller\api\SeoAnalyzerController@generateSitemap');       // 生成sitemap
        Route::post('seo-sitemap/ping', 'app\controller\api\SeoAnalyzerController@pingSitemap');               // Ping搜索引擎

        // 数据库管理
        Route::get('database/info', 'app\controller\api\DatabaseController@getInfo');                          // 获取数据库信息
        Route::get('database/tables', 'app\controller\api\DatabaseController@getTables');                      // 获取表信息
        Route::post('database/backup', 'app\controller\api\DatabaseController@backup');                        // 完整备份
        Route::post('database/backup-tables', 'app\controller\api\DatabaseController@backupTables');           // 备份指定表
        Route::get('database/backups', 'app\controller\api\DatabaseController@getBackups');                    // 备份列表
        Route::post('database/restore', 'app\controller\api\DatabaseController@restore');                      // 恢复数据库
        Route::post('database/validate-backup', 'app\controller\api\DatabaseController@validateBackup');       // 验证备份文件
        Route::delete('database/backup/:id', 'app\controller\api\DatabaseController@deleteBackup');            // 删除备份
        Route::get('database/download-backup', 'app\controller\api\DatabaseController@downloadBackup');        // 下载备份
        Route::post('database/optimize', 'app\controller\api\DatabaseController@optimize');                    // 优化表
        Route::post('database/repair', 'app\controller\api\DatabaseController@repair');                        // 修复表
        Route::post('database/clean-old-backups', 'app\controller\api\DatabaseController@cleanOldBackups');    // 清理旧备份

        // 缓存管理
        Route::get('cache/info', 'app\controller\api\CacheController@getInfo');                                // 获取缓存信息
        Route::get('cache/driver', 'app\controller\api\CacheController@getDriver');                            // 获取当前驱动
        Route::post('cache/switch-driver', 'app\controller\api\CacheController@switchDriver');                 // 切换缓存驱动
        Route::post('cache/test-redis', 'app\controller\api\CacheController@testRedis');                       // 测试Redis连接
        Route::post('cache/clear-all', 'app\controller\api\CacheController@clearAll');                         // 清空所有缓存
        Route::post('cache/clear-tag', 'app\controller\api\CacheController@clearTag');                         // 清除标签缓存
        Route::delete('cache', 'app\controller\api\CacheController@delete');                                   // 删除指定缓存
        Route::post('cache/clear-template', 'app\controller\api\CacheController@clearTemplate');               // 清除模板缓存
        Route::post('cache/clear-logs', 'app\controller\api\CacheController@clearLogs');                       // 清除日志文件
        Route::get('cache/keys', 'app\controller\api\CacheController@getKeys');                                // 获取缓存键列表
        Route::get('cache/get', 'app\controller\api\CacheController@get');                                     // 获取缓存值
        Route::post('cache/set', 'app\controller\api\CacheController@set');                                    // 设置缓存值
        Route::post('cache/warmup', 'app\controller\api\CacheController@warmup');                              // 缓存预热
        Route::post('cache/test-performance', 'app\controller\api\CacheController@testPerformance');           // 测试性能

        // 查询监控
        Route::get('query-monitor/summary', 'app\controller\api\QueryMonitor@summary');                       // 获取查询统计摘要
        Route::get('query-monitor/slow-queries', 'app\controller\api\QueryMonitor@slowQueries');               // 获取慢查询列表
        Route::get('query-monitor/nplus1-issues', 'app\controller\api\QueryMonitor@nplus1Issues');             // 获取N+1查询问题
        Route::get('query-monitor/route', 'app\controller\api\QueryMonitor@routeQueries');                     // 获取路由查询详情
        Route::get('query-monitor/report', 'app\controller\api\QueryMonitor@report');                          // 生成优化报告
        Route::get('query-monitor/trend', 'app\controller\api\QueryMonitor@trend');                            // 查询趋势（7天）
        Route::get('query-monitor/realtime', 'app\controller\api\QueryMonitor@realtime');                      // 实时监控
        Route::get('query-monitor/config', 'app\controller\api\QueryMonitor@config');                          // 获取配置
        Route::post('query-monitor/config', 'app\controller\api\QueryMonitor@updateConfig');                   // 更新配置
        Route::post('query-monitor/clear', 'app\controller\api\QueryMonitor@clear');                           // 清除日志
        Route::get('query-monitor/export', 'app\controller\api\QueryMonitor@export');                          // 导出报告

        // 定时任务管理
        Route::get('cron-jobs/presets', 'app\controller\api\CronJobController@presets');                       // 预设任务列表
        Route::get('cron-jobs/cron-expressions', 'app\controller\api\CronJobController@cronExpressions');      // 常用Cron表达式
        Route::post('cron-jobs/validate-cron', 'app\controller\api\CronJobController@validateCron');           // 验证Cron表达式
        Route::get('cron-jobs/logs', 'app\controller\api\CronJobController@logs');                             // 任务日志列表
        Route::post('cron-jobs/clean-logs', 'app\controller\api\CronJobController@cleanLogs');                 // 清理日志
        Route::get('cron-jobs/:id/logs', 'app\controller\api\CronJobController@jobLogs');                      // 获取指定任务的日志
        Route::post('cron-jobs/:id/run', 'app\controller\api\CronJobController@run');                          // 手动执行任务
        Route::put('cron-jobs/:id/status', 'app\controller\api\CronJobController@updateStatus');               // 更新任务状态
        Route::post('cron-jobs/batch-delete', 'app\controller\api\CronJobController@batchDelete');             // 批量删除
        Route::resource('cron-jobs', 'app\controller\api\CronJobController');                                  // RESTful定时任务资源

        // OAuth配置管理（后台）
        Route::get('oauth-configs/platform-options', 'app\controller\api\OAuthConfigController@getPlatformOptions'); // 平台选项
        Route::get('oauth-configs/enabled-platforms', 'app\controller\api\OAuthConfigController@getEnabledPlatforms'); // 启用的平台
        Route::post('oauth-configs/batch-update-status', 'app\controller\api\OAuthConfigController@batchUpdateStatus'); // 批量更新状态
        Route::get('oauth-configs/:id/test', 'app\controller\api\OAuthConfigController@testConfig');          // 测试配置
        Route::resource('oauth-configs', 'app\controller\api\OAuthConfigController')->except(['save', 'delete']); // RESTful OAuth配置资源（不允许新增和删除）

        // 日志管理
        Route::get('logs/system', 'app\controller\api\LogController@getSystemLogs');                           // 获取系统日志
        Route::get('logs/login', 'app\controller\api\LogController@getLoginLogs');                             // 获取登录日志
        Route::get('logs/security', 'app\controller\api\LogController@getSecurityLogs');                       // 获取安全日志
        Route::get('logs/system/stats', 'app\controller\api\LogController@getSystemLogStats');                 // 系统日志统计
        Route::get('logs/login/stats', 'app\controller\api\LogController@getLoginLogStats');                   // 登录日志统计
        Route::get('logs/security/high-risk-ips', 'app\controller\api\LogController@getHighRiskIps');          // 高危IP列表
        Route::delete('logs/system/:id', 'app\controller\api\LogController@deleteSystemLog');                  // 删除系统日志
        Route::delete('logs/login/:id', 'app\controller\api\LogController@deleteLoginLog');                    // 删除登录日志
        Route::delete('logs/security/:id', 'app\controller\api\LogController@deleteSecurityLog');              // 删除安全日志
        Route::post('logs/system/batch-delete', 'app\controller\api\LogController@batchDeleteSystemLogs');     // 批量删除系统日志
        Route::post('logs/login/batch-delete', 'app\controller\api\LogController@batchDeleteLoginLogs');       // 批量删除登录日志
        Route::post('logs/security/batch-delete', 'app\controller\api\LogController@batchDeleteSecurityLogs'); // 批量删除安全日志
        Route::post('logs/clean-old', 'app\controller\api\LogController@cleanOldLogs');                        // 清理旧日志
        Route::get('logs/export', 'app\controller\api\LogController@exportLogs');                              // 导出日志

        // ========== 扩展功能管理 ==========
        // 前台用户管理
        Route::get('front-user-manage/index', 'app\controller\api\FrontUserManage@index');                     // 用户列表
        Route::get('front-user-manage/statistics', 'app\controller\api\FrontUserManage@statistics');           // 用户统计
        Route::post('front-user-manage/create', 'app\controller\api\FrontUserManage@create');                  // 创建用户
        Route::post('front-user-manage/adjust-points/:id', 'app\controller\api\FrontUserManage@adjustPoints'); // 调整积分
        Route::post('front-user-manage/set-level/:id', 'app\controller\api\FrontUserManage@setLevel');         // 设置等级
        Route::post('front-user-manage/set-vip/:id', 'app\controller\api\FrontUserManage@setVip');             // 设置VIP
        Route::post('front-user-manage/change-status/:id', 'app\controller\api\FrontUserManage@changeStatus'); // 修改状态

        // 消息通知管理
        Route::get('notification-manage/template-index', 'app\controller\api\NotificationManage@templateIndex'); // 模板列表
        Route::get('notification-manage/template-read/:id', 'app\controller\api\NotificationManage@templateRead'); // 模板详情
        Route::post('notification-manage/template-create', 'app\controller\api\NotificationManage@templateCreate'); // 创建模板
        Route::put('notification-manage/template-update/:id', 'app\controller\api\NotificationManage@templateUpdate'); // 更新模板
        Route::delete('notification-manage/template-delete/:id', 'app\controller\api\NotificationManage@templateDelete'); // 删除模板
        Route::get('notification-manage/notification-index', 'app\controller\api\NotificationManage@notificationIndex'); // 通知记录列表
        Route::post('notification-manage/send-system-notification', 'app\controller\api\NotificationManage@sendSystemNotification'); // 发送系统通知

        // 短信服务管理
        Route::get('sms-manage/config-index', 'app\controller\api\SmsManage@configIndex');                     // 配置列表
        Route::post('sms-manage/config-create', 'app\controller\api\SmsManage@configCreate');                  // 创建配置
        Route::put('sms-manage/config-update/:id', 'app\controller\api\SmsManage@configUpdate');               // 更新配置
        Route::delete('sms-manage/config-delete/:id', 'app\controller\api\SmsManage@configDelete');            // 删除配置
        Route::get('sms-manage/log-index', 'app\controller\api\SmsManage@logIndex');                           // 短信日志
        Route::get('sms-manage/statistics', 'app\controller\api\SmsManage@statistics');                        // 发送统计

        // 积分商城管理
        Route::get('point-shop-manage/goods-index', 'app\controller\api\PointShopManage@goodsIndex');          // 商品列表
        Route::post('point-shop-manage/goods-create', 'app\controller\api\PointShopManage@goodsCreate');       // 创建商品
        Route::put('point-shop-manage/goods-update/:id', 'app\controller\api\PointShopManage@goodsUpdate');    // 更新商品
        Route::delete('point-shop-manage/goods-delete/:id', 'app\controller\api\PointShopManage@goodsDelete'); // 删除商品
        Route::get('point-shop-manage/order-index', 'app\controller\api\PointShopManage@orderIndex');          // 订单列表
        Route::post('point-shop-manage/order-deliver/:id', 'app\controller\api\PointShopManage@orderDeliver'); // 订单发货
        Route::post('point-shop-manage/order-complete/:id', 'app\controller\api\PointShopManage@orderComplete'); // 完成订单
        Route::post('point-shop-manage/order-cancel/:id', 'app\controller\api\PointShopManage@orderCancel');   // 取消订单
        Route::get('point-shop-manage/statistics', 'app\controller\api\PointShopManage@statistics');           // 商城统计

        // 投稿管理
        Route::get('contribute-manage/index', 'app\controller\api\ContributeManage@index');                    // 投稿列表
        Route::get('contribute-manage/read/:id', 'app\controller\api\ContributeManage@read');                  // 投稿详情
        Route::post('contribute-manage/audit-pass/:id', 'app\controller\api\ContributeManage@auditPass');      // 审核通过
        Route::post('contribute-manage/audit-reject/:id', 'app\controller\api\ContributeManage@auditReject');  // 审核拒绝
        Route::get('contribute-manage/config-index', 'app\controller\api\ContributeManage@configIndex');       // 配置列表
        Route::post('contribute-manage/config-create', 'app\controller\api\ContributeManage@configCreate');    // 创建配置
        Route::put('contribute-manage/config-update/:id', 'app\controller\api\ContributeManage@configUpdate'); // 更新配置
        Route::get('contribute-manage/statistics', 'app\controller\api\ContributeManage@statistics');          // 投稿统计

        // 会员等级管理
        Route::get('member-level-manage/index', 'app\controller\api\MemberLevelManage@index');                 // 等级配置列表
        Route::get('member-level-manage/read/:id', 'app\controller\api\MemberLevelManage@read');               // 等级配置详情
        Route::post('member-level-manage/create', 'app\controller\api\MemberLevelManage@create');              // 创建等级配置
        Route::put('member-level-manage/update/:id', 'app\controller\api\MemberLevelManage@update');           // 更新等级配置
        Route::delete('member-level-manage/delete/:id', 'app\controller\api\MemberLevelManage@delete');        // 删除等级配置
        Route::get('member-level-manage/log-index', 'app\controller\api\MemberLevelManage@logIndex');          // 升级日志列表
        Route::post('member-level-manage/batch-upgrade', 'app\controller\api\MemberLevelManage@batchUpgrade'); // 批量升级
        Route::post('member-level-manage/check-user/:id', 'app\controller\api\MemberLevelManage@checkUser');   // 检查用户等级
        Route::get('member-level-manage/user-progress/:id', 'app\controller\api\MemberLevelManage@userProgress'); // 用户等级进度
        Route::get('member-level-manage/statistics', 'app\controller\api\MemberLevelManage@statistics');       // 统计信息

        // ========== 多站点管理 ==========
        // 站点管理（特殊路由需要在resource之前定义）
        Route::get('sites/options', 'app\controller\api\SiteController@options');                              // 获取站点选项（下拉框）
        Route::get('sites/current', 'app\controller\api\SiteController@current');                              // 获取当前站点
        Route::post('sites/switch', 'app\controller\api\SiteController@switch');                               // 切换站点
        Route::post('sites/batch-delete', 'app\controller\api\SiteController@batchDelete');                    // 批量删除站点
        Route::put('sites/:id/status', 'app\controller\api\SiteController@updateStatus');                      // 更新站点状态
        Route::post('sites/:id/admins', 'app\controller\api\SiteController@assignAdmins');                     // 分配管理员
        Route::get('sites/:id/admins', 'app\controller\api\SiteController@admins');                            // 获取管理员列表
        Route::put('sites/:id/stats', 'app\controller\api\SiteController@updateStats');                        // 更新统计数据
        Route::post('sites/copy-config', 'app\controller\api\SiteController@copyConfig');                      // 复制配置
        Route::post('sites/clear-cache', 'app\controller\api\SiteController@clearCache');                      // 清除缓存

        // 站点表管理
        Route::post('sites/:id/create-tables', 'app\controller\api\SiteController@createTables');              // 创建站点表
        Route::get('sites/:id/check-tables', 'app\controller\api\SiteController@checkTables');                 // 检查站点表状态
        Route::post('sites/:id/migrate-data', 'app\controller\api\SiteController@migrateData');                // 迁移数据到站点表
        Route::post('sites/:id/truncate-tables', 'app\controller\api\SiteController@truncateTables');          // 清空站点表数据

        // 站点模板配置
        Route::get('sites/:id/template-config', 'app\controller\api\SiteController@getTemplateConfig');        // 获取站点的模板配置
        Route::post('sites/:id/template-package', 'app\controller\api\SiteController@setTemplatePackage');     // 设置站点的模板包
        Route::put('sites/:id/template-config', 'app\controller\api\SiteController@updateTemplateConfig');     // 更新站点的模板自定义配置
        Route::get('sites/:id/template-overrides', 'app\controller\api\SiteController@getTemplateOverrides');  // 获取站点的模板覆盖列表
        Route::post('sites/:id/template-override', 'app\controller\api\SiteController@setTemplateOverride');   // 设置站点的模板覆盖
        Route::delete('sites/:id/template-override', 'app\controller\api\SiteController@removeTemplateOverride'); // 移除站点的模板覆盖

        Route::resource('sites', 'app\controller\api\SiteController');                                          // RESTful站点资源

        // ========== 评论系统优化 ==========
        // 敏感词管理（后台管理员）
        Route::get('sensitive-words/categories', 'app\controller\api\SensitiveWordController@categories');                // 获取分类选项
        Route::get('sensitive-words/levels', 'app\controller\api\SensitiveWordController@levels');                        // 获取级别选项
        Route::get('sensitive-words/statistics', 'app\controller\api\SensitiveWordController@statistics');                // 获取统计信息
        Route::post('sensitive-words/test-check', 'app\controller\api\SensitiveWordController@testCheck');                // 测试检测
        Route::post('sensitive-words/batch-import', 'app\controller\api\SensitiveWordController@batchImport');            // 批量导入
        Route::post('sensitive-words/batch-delete', 'app\controller\api\SensitiveWordController@batchDelete');            // 批量删除
        Route::post('sensitive-words/batch-update-status', 'app\controller\api\SensitiveWordController@batchUpdateStatus'); // 批量更新状态
        Route::resource('sensitive-words', 'app\controller\api\SensitiveWordController');                                  // RESTful敏感词资源

        // 违规内容记录管理（后台管理员）
        Route::get('content-violations/statistics', 'app\controller\api\ContentViolationController@statistics');          // 获取统计信息
        Route::post('content-violations/:id/mark-reviewed', 'app\controller\api\ContentViolationController@markAsReviewed'); // 标记已审核
        Route::post('content-violations/:id/mark-ignored', 'app\controller\api\ContentViolationController@markAsIgnored'); // 标记已忽略
        Route::post('content-violations/batch-review', 'app\controller\api\ContentViolationController@batchReview');      // 批量审核
        Route::resource('content-violations', 'app\controller\api\ContentViolationController')->except(['save', 'update']); // RESTful违规记录资源

        // ========== AI文章生成管理 ==========
        // AI厂商管理
        Route::get('ai-providers/all', 'app\controller\api\AiProviderController@all');                             // 获取所有厂商（下拉框）
        Route::get('ai-providers/:id/models', 'app\controller\api\AiProviderController@models');                   // 获取厂商的模型列表
        Route::resource('ai-providers', 'app\controller\api\AiProviderController');                                // RESTful AI厂商资源

        // AI模型管理
        Route::get('ai-models/all', 'app\controller\api\AiModelController@all');                                   // 获取所有模型（按厂商分组）
        Route::post('ai-models/batch-import', 'app\controller\api\AiModelController@batchImport');                 // 批量导入模型
        Route::resource('ai-models', 'app\controller\api\AiModelController');                                      // RESTful AI模型资源

        // AI配置管理
        Route::get('ai-configs/all', 'app\controller\api\AiConfigController@all');                                 // 获取所有配置（下拉框）
        Route::get('ai-configs/providers', 'app\controller\api\AiConfigController@providers');                     // 获取AI提供商列表
        Route::get('ai-configs/provider-models', 'app\controller\api\AiConfigController@providerModels');          // 获取提供商模型列表
        Route::get('ai-configs/provider-config-guide', 'app\controller\api\AiConfigController@providerConfigGuide'); // 获取提供商配置指南
        Route::post('ai-configs/:id/test', 'app\controller\api\AiConfigController@test');                          // 测试AI连接
        Route::post('ai-configs/:id/set-default', 'app\controller\api\AiConfigController@setDefault');             // 设置为默认配置
        Route::resource('ai-configs', 'app\controller\api\AiConfigController');                                    // RESTful AI配置资源

        // AI文章生成任务管理
        Route::get('ai-article-tasks/statistics', 'app\controller\api\AiArticleTaskController@statistics');        // 统计信息
        Route::get('ai-article-tasks/statuses', 'app\controller\api\AiArticleTaskController@statuses');            // 获取状态列表
        Route::get('ai-article-tasks/:id/generated-articles', 'app\controller\api\AiArticleTaskController@generatedArticles'); // 获取生成记录
        Route::post('ai-article-tasks/:id/start', 'app\controller\api\AiArticleTaskController@start');             // 启动任务
        Route::post('ai-article-tasks/:id/stop', 'app\controller\api\AiArticleTaskController@stop');               // 停止任务
        Route::resource('ai-article-tasks', 'app\controller\api\AiArticleTaskController');                         // RESTful任务资源

        // AI提示词模板管理
        Route::get('ai-prompt-templates/all', 'app\controller\api\AiPromptTemplateController@all');                // 获取所有模板（下拉选择）
        Route::get('ai-prompt-templates/categories', 'app\controller\api\AiPromptTemplateController@categories');  // 获取分类列表
        Route::resource('ai-prompt-templates', 'app\controller\api\AiPromptTemplateController');                   // RESTful提示词模板资源

    })->middleware(\app\middleware\Auth::class);  // 应用JWT认证中间件

    // ========== 前台用户系统（不需要JWT认证） ==========
    Route::group('front', function () {
        // 前台用户认证
        Route::post('auth/register', 'app\controller\api\FrontAuth@register');         // 用户注册
        Route::post('auth/login', 'app\controller\api\FrontAuth@login');               // 用户登录
        Route::post('auth/send-reset-email', 'app\controller\api\FrontAuth@sendResetEmail'); // 发送密码重置邮件
        Route::post('auth/reset-password', 'app\controller\api\FrontAuth@resetPassword');    // 重置密码
        Route::get('auth/verify-email', 'app\controller\api\FrontAuth@verifyEmail');    // 验证邮箱

        // 前台评论（公开接口，游客可访问）
        Route::get('comments', 'app\controller\api\FrontComment@index');                // 获取文章评论列表
        Route::get('comments/:id', 'app\controller\api\FrontComment@read');             // 获取评论详情
        Route::post('comments', 'app\controller\api\FrontComment@create');              // 发表评论（支持游客）

        // 评论点赞/点踩（游客和登录用户都可用）
        Route::post('comments/like', 'app\controller\api\CommentLikeController@like');         // 点赞评论
        Route::post('comments/dislike', 'app\controller\api\CommentLikeController@dislike');   // 点踩评论
        Route::get('comments/like-status', 'app\controller\api\CommentLikeController@getStatus');  // 获取点赞状态
        Route::post('comments/batch-like-status', 'app\controller\api\CommentLikeController@getBatchStatus'); // 批量获取状态

        // 评论举报（游客和登录用户都可用）
        Route::post('comments/report', 'app\controller\api\CommentReportController@report');   // 举报评论

        // 表情列表（公开）
        Route::get('comment-emojis', 'app\controller\admin\CommentEmojiController@getByCategory'); // 获取启用的表情
    });

    // ========== 前台用户系统（需要JWT认证） ==========
    Route::group('front', function () {
        // 前台用户认证操作
        Route::post('auth/logout', 'app\controller\api\FrontAuth@logout');              // 退出登录
        Route::get('auth/info', 'app\controller\api\FrontAuth@info');                   // 获取当前用户信息
        Route::post('auth/change-password', 'app\controller\api\FrontAuth@changePassword'); // 修改密码
        Route::post('auth/send-verify-email', 'app\controller\api\FrontAuth@sendVerifyEmail'); // 发送邮箱验证邮件

        // 前台用户资料管理
        Route::get('profile', 'app\controller\api\FrontProfile@index');                 // 获取用户资料
        Route::put('profile', 'app\controller\api\FrontProfile@update');                // 更新用户资料
        Route::post('profile/avatar', 'app\controller\api\FrontProfile@uploadAvatar');  // 上传头像

        // 收藏管理
        Route::get('favorites', 'app\controller\api\FrontProfile@favorites');           // 收藏列表
        Route::post('favorites', 'app\controller\api\FrontProfile@addFavorite');        // 添加收藏
        Route::delete('favorites', 'app\controller\api\FrontProfile@removeFavorite');   // 取消收藏

        // 点赞管理
        Route::post('likes', 'app\controller\api\FrontProfile@addLike');                // 点赞
        Route::delete('likes', 'app\controller\api\FrontProfile@removeLike');           // 取消点赞

        // 阅读历史
        Route::get('read-history', 'app\controller\api\FrontProfile@readHistory');      // 阅读历史列表
        Route::post('read-history', 'app\controller\api\FrontProfile@addReadHistory');  // 记录阅读历史

        // 积分管理
        Route::get('point-logs', 'app\controller\api\FrontProfile@pointLogs');          // 积分日志

        // 关注管理
        Route::post('follow', 'app\controller\api\FrontProfile@follow');                // 关注用户
        Route::delete('follow', 'app\controller\api\FrontProfile@unfollow');            // 取消关注
        Route::get('following', 'app\controller\api\FrontProfile@followingList');       // 关注列表
        Route::get('followers', 'app\controller\api\FrontProfile@followerList');        // 粉丝列表

        // 前台评论（需要登录）- 已移至公开接口区域，支持游客访问

        // 用户通知（前台用户）
        Route::get('notifications', 'app\controller\api\UserNotificationController@index');                          // 通知列表
        Route::get('notifications/unread-count', 'app\controller\api\UserNotificationController@unreadCount');       // 未读数量
        Route::post('notifications/:id/mark-as-read', 'app\controller\api\UserNotificationController@markAsRead');  // 标记已读
        Route::post('notifications/batch-mark-as-read', 'app\controller\api\UserNotificationController@batchMarkAsRead'); // 批量标记已读
        Route::post('notifications/mark-all-as-read', 'app\controller\api\UserNotificationController@markAllAsRead'); // 全部标记已读
        Route::delete('notifications/:id', 'app\controller\api\UserNotificationController@delete');                  // 删除通知
        Route::delete('notifications/clear-read', 'app\controller\api\UserNotificationController@clearRead');        // 清空已读
        Route::get('notifications/settings', 'app\controller\api\UserNotificationController@settings');              // 获取通知设置
        Route::post('notifications/settings', 'app\controller\api\UserNotificationController@updateSettings');       // 更新通知设置
    })->middleware(\app\middleware\Auth::class);  // 应用JWT认证中间件

})->middleware([\app\middleware\Cors::class, \app\middleware\SystemLog::class]);  // 应用跨域中间件和系统日志中间件
