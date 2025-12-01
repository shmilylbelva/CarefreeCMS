<?php
declare(strict_types=1);

namespace app\common;

/**
 * 错误代码定义
 * 统一管理所有业务错误代码
 */
class ErrorCode
{
    // ==================== 通用错误 (1000-1099) ====================
    const SUCCESS = 0;
    const UNKNOWN_ERROR = 1000;
    const INVALID_PARAMS = 1001;
    const VALIDATION_FAILED = 1002;
    const OPERATION_FAILED = 1003;
    const RESOURCE_NOT_FOUND = 1004;
    const RESOURCE_ALREADY_EXISTS = 1005;
    const PERMISSION_DENIED = 1006;
    const SYSTEM_ERROR = 1007;
    const SERVICE_UNAVAILABLE = 1008;

    // ==================== 认证相关 (1100-1199) ====================
    const AUTH_FAILED = 1100;
    const TOKEN_INVALID = 1101;
    const TOKEN_EXPIRED = 1102;
    const LOGIN_REQUIRED = 1103;
    const USERNAME_PASSWORD_ERROR = 1104;
    const ACCOUNT_DISABLED = 1105;
    const ACCOUNT_LOCKED = 1106;
    const PASSWORD_WEAK = 1107;
    const PASSWORD_MISMATCH = 1108;

    // ==================== 用户相关 (1200-1299) ====================
    const USER_NOT_FOUND = 1200;
    const USER_ALREADY_EXISTS = 1201;
    const USER_STATUS_INVALID = 1202;
    const USER_ROLE_INVALID = 1203;
    const USER_EMAIL_EXISTS = 1204;
    const USER_PHONE_EXISTS = 1205;

    // ==================== 文章相关 (1300-1399) ====================
    const ARTICLE_NOT_FOUND = 1300;
    const ARTICLE_ALREADY_EXISTS = 1301;
    const ARTICLE_STATUS_INVALID = 1302;
    const ARTICLE_PUBLISH_FAILED = 1303;
    const ARTICLE_DELETE_FAILED = 1304;
    const ARTICLE_TITLE_REQUIRED = 1305;
    const ARTICLE_CONTENT_REQUIRED = 1306;

    // ==================== 分类相关 (1400-1499) ====================
    const CATEGORY_NOT_FOUND = 1400;
    const CATEGORY_ALREADY_EXISTS = 1401;
    const CATEGORY_HAS_CHILDREN = 1402;
    const CATEGORY_HAS_ARTICLES = 1403;
    const CATEGORY_PARENT_INVALID = 1404;
    const CATEGORY_NAME_REQUIRED = 1405;

    // ==================== 标签相关 (1500-1599) ====================
    const TAG_NOT_FOUND = 1500;
    const TAG_ALREADY_EXISTS = 1501;
    const TAG_NAME_REQUIRED = 1502;

    // ==================== 文件上传相关 (1600-1699) ====================
    const FILE_UPLOAD_FAILED = 1600;
    const FILE_TYPE_NOT_ALLOWED = 1601;
    const FILE_SIZE_EXCEEDED = 1602;
    const FILE_NOT_FOUND = 1603;
    const FILE_DELETE_FAILED = 1604;
    const UPLOAD_DIR_CREATE_FAILED = 1605;
    const FILE_SAVE_FAILED = 1606;

    // ==================== 站点相关 (1700-1799) ====================
    const SITE_NOT_FOUND = 1700;
    const SITE_ALREADY_EXISTS = 1701;
    const SITE_DISABLED = 1702;
    const SITE_SWITCH_FAILED = 1703;
    const SITE_CODE_REQUIRED = 1704;
    const SITE_NAME_REQUIRED = 1705;

    // ==================== 模板相关 (1800-1899) ====================
    const TEMPLATE_NOT_FOUND = 1800;
    const TEMPLATE_ALREADY_EXISTS = 1801;
    const TEMPLATE_DIR_NOT_FOUND = 1802;
    const TEMPLATE_FILE_CREATE_FAILED = 1803;
    const TEMPLATE_FILE_UPDATE_FAILED = 1804;

    // ==================== 评论相关 (1900-1999) ====================
    const COMMENT_NOT_FOUND = 1900;
    const COMMENT_DISABLED = 1901;
    const COMMENT_AUDIT_REQUIRED = 1902;
    const COMMENT_CONTENT_REQUIRED = 1903;

    // ==================== 专题相关 (2000-2099) ====================
    const TOPIC_NOT_FOUND = 2000;
    const TOPIC_ALREADY_EXISTS = 2001;
    const TOPIC_SLUG_EXISTS = 2002;

    // ==================== 回收站相关 (2100-2199) ====================
    const RECYCLE_BIN_NOT_FOUND = 2100;
    const RECYCLE_BIN_TYPE_INVALID = 2101;
    const RESTORE_FAILED = 2102;
    const PERMANENT_DELETE_FAILED = 2103;

    // ==================== 数据库相关 (2200-2299) ====================
    const DB_QUERY_ERROR = 2200;
    const DB_INSERT_FAILED = 2201;
    const DB_UPDATE_FAILED = 2202;
    const DB_DELETE_FAILED = 2203;
    const DB_CONNECTION_FAILED = 2204;

    // ==================== 缓存相关 (2300-2399) ====================
    const CACHE_ERROR = 2300;
    const CACHE_SET_FAILED = 2301;
    const CACHE_GET_FAILED = 2302;
    const CACHE_DELETE_FAILED = 2303;

    /**
     * 错误消息映射表
     * 将错误代码映射到用户友好的错误消息
     */
    private static $messages = [
        // 通用错误
        self::SUCCESS => '操作成功',
        self::UNKNOWN_ERROR => '未知错误，请稍后重试',
        self::INVALID_PARAMS => '请求参数不正确',
        self::VALIDATION_FAILED => '数据验证失败',
        self::OPERATION_FAILED => '操作失败，请稍后重试',
        self::RESOURCE_NOT_FOUND => '请求的资源不存在',
        self::RESOURCE_ALREADY_EXISTS => '资源已存在',
        self::PERMISSION_DENIED => '您没有权限执行此操作',
        self::SYSTEM_ERROR => '系统错误，请联系管理员',
        self::SERVICE_UNAVAILABLE => '服务暂时不可用',

        // 认证相关
        self::AUTH_FAILED => '身份验证失败',
        self::TOKEN_INVALID => '登录凭证无效，请重新登录',
        self::TOKEN_EXPIRED => '登录已过期，请重新登录',
        self::LOGIN_REQUIRED => '请先登录',
        self::USERNAME_PASSWORD_ERROR => '用户名或密码错误',
        self::ACCOUNT_DISABLED => '账号已被禁用',
        self::ACCOUNT_LOCKED => '账号已被锁定',
        self::PASSWORD_WEAK => '密码强度不够，请使用更强的密码',
        self::PASSWORD_MISMATCH => '两次输入的密码不一致',

        // 用户相关
        self::USER_NOT_FOUND => '用户不存在',
        self::USER_ALREADY_EXISTS => '用户已存在',
        self::USER_STATUS_INVALID => '用户状态无效',
        self::USER_ROLE_INVALID => '用户角色无效',
        self::USER_EMAIL_EXISTS => '该邮箱已被使用',
        self::USER_PHONE_EXISTS => '该手机号已被使用',

        // 文章相关
        self::ARTICLE_NOT_FOUND => '文章不存在',
        self::ARTICLE_ALREADY_EXISTS => '文章已存在',
        self::ARTICLE_STATUS_INVALID => '文章状态无效',
        self::ARTICLE_PUBLISH_FAILED => '文章发布失败',
        self::ARTICLE_DELETE_FAILED => '文章删除失败',
        self::ARTICLE_TITLE_REQUIRED => '文章标题不能为空',
        self::ARTICLE_CONTENT_REQUIRED => '文章内容不能为空',

        // 分类相关
        self::CATEGORY_NOT_FOUND => '分类不存在',
        self::CATEGORY_ALREADY_EXISTS => '分类已存在',
        self::CATEGORY_HAS_CHILDREN => '该分类下还有子分类，无法删除',
        self::CATEGORY_HAS_ARTICLES => '该分类下还有文章，无法删除',
        self::CATEGORY_PARENT_INVALID => '父分类无效',
        self::CATEGORY_NAME_REQUIRED => '分类名称不能为空',

        // 标签相关
        self::TAG_NOT_FOUND => '标签不存在',
        self::TAG_ALREADY_EXISTS => '标签已存在',
        self::TAG_NAME_REQUIRED => '标签名称不能为空',

        // 文件上传相关
        self::FILE_UPLOAD_FAILED => '文件上传失败',
        self::FILE_TYPE_NOT_ALLOWED => '不支持该文件类型',
        self::FILE_SIZE_EXCEEDED => '文件大小超出限制',
        self::FILE_NOT_FOUND => '文件不存在',
        self::FILE_DELETE_FAILED => '文件删除失败',
        self::UPLOAD_DIR_CREATE_FAILED => '无法创建上传目录',
        self::FILE_SAVE_FAILED => '文件保存失败',

        // 站点相关
        self::SITE_NOT_FOUND => '站点不存在',
        self::SITE_ALREADY_EXISTS => '站点已存在',
        self::SITE_DISABLED => '站点已禁用',
        self::SITE_SWITCH_FAILED => '站点切换失败',
        self::SITE_CODE_REQUIRED => '站点代码不能为空',
        self::SITE_NAME_REQUIRED => '站点名称不能为空',

        // 模板相关
        self::TEMPLATE_NOT_FOUND => '模板不存在',
        self::TEMPLATE_ALREADY_EXISTS => '模板已存在',
        self::TEMPLATE_DIR_NOT_FOUND => '模板目录不存在',
        self::TEMPLATE_FILE_CREATE_FAILED => '模板文件创建失败',
        self::TEMPLATE_FILE_UPDATE_FAILED => '模板文件更新失败',

        // 评论相关
        self::COMMENT_NOT_FOUND => '评论不存在',
        self::COMMENT_DISABLED => '评论功能已关闭',
        self::COMMENT_AUDIT_REQUIRED => '评论需要审核',
        self::COMMENT_CONTENT_REQUIRED => '评论内容不能为空',

        // 专题相关
        self::TOPIC_NOT_FOUND => '专题不存在',
        self::TOPIC_ALREADY_EXISTS => '专题已存在',
        self::TOPIC_SLUG_EXISTS => '该URL别名已被使用',

        // 回收站相关
        self::RECYCLE_BIN_NOT_FOUND => '回收站项目不存在',
        self::RECYCLE_BIN_TYPE_INVALID => '不支持的类型',
        self::RESTORE_FAILED => '恢复失败',
        self::PERMANENT_DELETE_FAILED => '永久删除失败',

        // 数据库相关
        self::DB_QUERY_ERROR => '数据查询失败',
        self::DB_INSERT_FAILED => '数据插入失败',
        self::DB_UPDATE_FAILED => '数据更新失败',
        self::DB_DELETE_FAILED => '数据删除失败',
        self::DB_CONNECTION_FAILED => '数据库连接失败',

        // 缓存相关
        self::CACHE_ERROR => '缓存操作失败',
        self::CACHE_SET_FAILED => '缓存设置失败',
        self::CACHE_GET_FAILED => '缓存获取失败',
        self::CACHE_DELETE_FAILED => '缓存删除失败',
    ];

    /**
     * 获取错误消息
     *
     * @param int $code 错误代码
     * @param string|null $defaultMessage 默认消息（如果代码不存在）
     * @return string
     */
    public static function getMessage(int $code, ?string $defaultMessage = null): string
    {
        return self::$messages[$code] ?? $defaultMessage ?? '操作失败';
    }

    /**
     * 检查错误代码是否存在
     *
     * @param int $code
     * @return bool
     */
    public static function exists(int $code): bool
    {
        return isset(self::$messages[$code]);
    }

    /**
     * 获取所有错误代码和消息
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$messages;
    }
}
