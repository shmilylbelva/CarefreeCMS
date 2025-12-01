<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 用户第三方账号绑定模型
 */
class FrontUserOAuth extends Model
{
    protected $name = 'front_user_oauth';

    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    // 类型转换
    protected $type = [
        'user_id' => 'integer',
        'expires_in' => 'integer',
        'login_count' => 'integer',
        'status' => 'boolean',
        'bind_time' => 'datetime',
        'last_login_time' => 'datetime',
        'token_expires_at' => 'datetime',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
        'extra_data' => 'json',
    ];

    // 允许批量赋值的字段
    protected $field = [
        'id', 'user_id', 'platform', 'openid', 'unionid', 'nickname', 'avatar',
        'access_token', 'refresh_token', 'expires_in', 'token_expires_at',
        'extra_data', 'bind_time', 'last_login_time', 'login_count', 'status',
        'create_time', 'update_time'
    ];

    // 状态常量
    const STATUS_UNBOUND = 0;
    const STATUS_BOUND = 1;

    /**
     * 关联前台用户
     */
    public function frontUser()
    {
        return $this->belongsTo(FrontUser::class, 'user_id', 'id');
    }

    /**
     * 关联OAuth配置
     */
    public function oauthConfig()
    {
        return $this->belongsTo(OAuthConfig::class, 'platform', 'platform');
    }

    /**
     * 搜索器 - 用户ID
     */
    public function searchUserIdAttr($query, $value)
    {
        if ($value) {
            $query->where('user_id', $value);
        }
    }

    /**
     * 搜索器 - 平台
     */
    public function searchPlatformAttr($query, $value)
    {
        if ($value) {
            $query->where('platform', $value);
        }
    }

    /**
     * 搜索器 - 绑定状态
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '' && $value !== null) {
            $query->where('status', $value);
        }
    }

    /**
     * 根据平台和openid查找绑定记录
     */
    public static function findByPlatformOpenid($platform, $openid)
    {
        return self::where('platform', $platform)
            ->where('openid', $openid)
            ->where('status', self::STATUS_BOUND)
            ->find();
    }

    /**
     * 根据用户ID和平台查找绑定记录
     */
    public static function findByUserPlatform($userId, $platform)
    {
        return self::where('user_id', $userId)
            ->where('platform', $platform)
            ->where('status', self::STATUS_BOUND)
            ->find();
    }

    /**
     * 获取用户的所有绑定账号
     */
    public static function getUserBindings($userId)
    {
        return self::where('user_id', $userId)
            ->where('status', self::STATUS_BOUND)
            ->with(['oauthConfig'])
            ->select();
    }

    /**
     * 更新登录信息
     */
    public function updateLoginInfo()
    {
        $this->login_count = $this->login_count + 1;
        $this->last_login_time = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * 更新Token信息
     */
    public function updateToken($accessToken, $refreshToken = null, $expiresIn = null)
    {
        $this->access_token = $accessToken;
        if ($refreshToken) {
            $this->refresh_token = $refreshToken;
        }
        if ($expiresIn) {
            $this->expires_in = $expiresIn;
            $this->token_expires_at = date('Y-m-d H:i:s', time() + $expiresIn);
        }
        $this->save();
    }

    /**
     * 检查Token是否过期
     */
    public function isTokenExpired()
    {
        if (!$this->token_expires_at) {
            return true;
        }
        return strtotime($this->token_expires_at) < time();
    }

    /**
     * 解绑账号
     */
    public function unbind()
    {
        $this->status = self::STATUS_UNBOUND;
        return $this->save();
    }
}
