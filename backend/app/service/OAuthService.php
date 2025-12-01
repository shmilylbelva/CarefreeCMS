<?php
declare (strict_types = 1);

namespace app\service;

use app\model\OAuthConfig;
use app\model\FrontUserOAuth;
use app\model\FrontUser;
use think\facade\Db;
use think\Exception;

/**
 * OAuth服务层
 */
class OAuthService
{
    /**
     * 获取授权登录URL
     */
    public function getAuthUrl($platform, $state = '')
    {
        $config = OAuthConfig::getByPlatform($platform);
        if (!$config) {
            throw new Exception('OAuth平台未配置或未启用');
        }

        if (!$config->isConfigComplete()) {
            throw new Exception('OAuth配置不完整，请联系管理员');
        }

        // 生成state参数用于防CSRF攻击
        if (empty($state)) {
            $state = md5(uniqid() . time());
        }

        // 根据不同平台生成授权URL
        switch ($platform) {
            case OAuthConfig::PLATFORM_WECHAT:
                return $this->getWechatAuthUrl($config, $state);
            case OAuthConfig::PLATFORM_QQ:
                return $this->getQQAuthUrl($config, $state);
            case OAuthConfig::PLATFORM_WEIBO:
                return $this->getWeiboAuthUrl($config, $state);
            case OAuthConfig::PLATFORM_GITHUB:
                return $this->getGithubAuthUrl($config, $state);
            default:
                throw new Exception('不支持的OAuth平台');
        }
    }

    /**
     * 处理OAuth回调
     */
    public function handleCallback($platform, $code, $state = '')
    {
        $config = OAuthConfig::getByPlatform($platform);
        if (!$config) {
            throw new Exception('OAuth平台未配置');
        }

        // 根据不同平台获取Access Token
        $tokenData = $this->getAccessToken($platform, $config, $code);

        // 获取用户信息
        $userInfo = $this->getUserInfo($platform, $config, $tokenData);

        // 查找或创建用户
        return $this->findOrCreateUser($platform, $userInfo, $tokenData);
    }

    /**
     * 微信授权URL
     */
    protected function getWechatAuthUrl($config, $state)
    {
        $params = [
            'appid' => $config->app_id,
            'redirect_uri' => urlencode($config->redirect_uri),
            'response_type' => 'code',
            'scope' => $config->scope ?: 'snsapi_login',
            'state' => $state,
        ];

        return 'https://open.weixin.qq.com/connect/qrconnect?' . http_build_query($params) . '#wechat_redirect';
    }

    /**
     * QQ授权URL
     */
    protected function getQQAuthUrl($config, $state)
    {
        $params = [
            'client_id' => $config->app_id,
            'redirect_uri' => urlencode($config->redirect_uri),
            'response_type' => 'code',
            'scope' => $config->scope ?: 'get_user_info',
            'state' => $state,
        ];

        return 'https://graph.qq.com/oauth2.0/authorize?' . http_build_query($params);
    }

    /**
     * 微博授权URL
     */
    protected function getWeiboAuthUrl($config, $state)
    {
        $params = [
            'client_id' => $config->app_id,
            'redirect_uri' => urlencode($config->redirect_uri),
            'response_type' => 'code',
            'scope' => $config->scope ?: 'email',
            'state' => $state,
        ];

        return 'https://api.weibo.com/oauth2/authorize?' . http_build_query($params);
    }

    /**
     * GitHub授权URL
     */
    protected function getGithubAuthUrl($config, $state)
    {
        $params = [
            'client_id' => $config->app_id,
            'redirect_uri' => urlencode($config->redirect_uri),
            'scope' => $config->scope ?: 'user:email',
            'state' => $state,
        ];

        return 'https://github.com/login/oauth/authorize?' . http_build_query($params);
    }

    /**
     * 获取Access Token
     */
    protected function getAccessToken($platform, $config, $code)
    {
        switch ($platform) {
            case OAuthConfig::PLATFORM_WECHAT:
                return $this->getWechatAccessToken($config, $code);
            case OAuthConfig::PLATFORM_QQ:
                return $this->getQQAccessToken($config, $code);
            case OAuthConfig::PLATFORM_WEIBO:
                return $this->getWeiboAccessToken($config, $code);
            case OAuthConfig::PLATFORM_GITHUB:
                return $this->getGithubAccessToken($config, $code);
            default:
                throw new Exception('不支持的OAuth平台');
        }
    }

    /**
     * 微信获取Access Token
     */
    protected function getWechatAccessToken($config, $code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token';
        $params = [
            'appid' => $config->app_id,
            'secret' => $config->getRawAppSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

        $response = $this->httpGet($url . '?' . http_build_query($params));
        $data = json_decode($response, true);

        if (isset($data['errcode'])) {
            throw new Exception('微信OAuth错误: ' . ($data['errmsg'] ?? '未知错误'));
        }

        return $data;
    }

    /**
     * QQ获取Access Token
     */
    protected function getQQAccessToken($config, $code)
    {
        $url = 'https://graph.qq.com/oauth2.0/token';
        $params = [
            'client_id' => $config->app_id,
            'client_secret' => $config->getRawAppSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $config->redirect_uri,
        ];

        $response = $this->httpGet($url . '?' . http_build_query($params));
        parse_str($response, $data);

        if (isset($data['error'])) {
            throw new Exception('QQ OAuth错误: ' . ($data['error_description'] ?? '未知错误'));
        }

        return $data;
    }

    /**
     * 微博获取Access Token
     */
    protected function getWeiboAccessToken($config, $code)
    {
        $url = 'https://api.weibo.com/oauth2/access_token';
        $params = [
            'client_id' => $config->app_id,
            'client_secret' => $config->getRawAppSecret(),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $config->redirect_uri,
        ];

        $response = $this->httpPost($url, $params);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception('微博OAuth错误: ' . ($data['error_description'] ?? '未知错误'));
        }

        return $data;
    }

    /**
     * GitHub获取Access Token
     */
    protected function getGithubAccessToken($config, $code)
    {
        $url = 'https://github.com/login/oauth/access_token';
        $params = [
            'client_id' => $config->app_id,
            'client_secret' => $config->getRawAppSecret(),
            'code' => $code,
            'redirect_uri' => $config->redirect_uri,
        ];

        $response = $this->httpPost($url, $params, [
            'Accept: application/json'
        ]);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception('GitHub OAuth错误: ' . ($data['error_description'] ?? '未知错误'));
        }

        return $data;
    }

    /**
     * 获取用户信息
     */
    protected function getUserInfo($platform, $config, $tokenData)
    {
        switch ($platform) {
            case OAuthConfig::PLATFORM_WECHAT:
                return $this->getWechatUserInfo($tokenData);
            case OAuthConfig::PLATFORM_QQ:
                return $this->getQQUserInfo($tokenData);
            case OAuthConfig::PLATFORM_WEIBO:
                return $this->getWeiboUserInfo($tokenData);
            case OAuthConfig::PLATFORM_GITHUB:
                return $this->getGithubUserInfo($tokenData);
            default:
                throw new Exception('不支持的OAuth平台');
        }
    }

    /**
     * 微信获取用户信息
     */
    protected function getWechatUserInfo($tokenData)
    {
        $url = 'https://api.weixin.qq.com/sns/userinfo';
        $params = [
            'access_token' => $tokenData['access_token'],
            'openid' => $tokenData['openid'],
        ];

        $response = $this->httpGet($url . '?' . http_build_query($params));
        $data = json_decode($response, true);

        if (isset($data['errcode'])) {
            throw new Exception('获取微信用户信息失败: ' . ($data['errmsg'] ?? '未知错误'));
        }

        return [
            'openid' => $data['openid'],
            'unionid' => $data['unionid'] ?? null,
            'nickname' => $data['nickname'] ?? '',
            'avatar' => $data['headimgurl'] ?? '',
            'extra_data' => $data,
        ];
    }

    /**
     * QQ获取用户信息
     */
    protected function getQQUserInfo($tokenData)
    {
        // 先获取OpenID
        $url = 'https://graph.qq.com/oauth2.0/me?access_token=' . $tokenData['access_token'];
        $response = $this->httpGet($url);

        // QQ返回的是JSONP格式，需要处理
        preg_match('/callback\((.*)\)/i', $response, $matches);
        $meData = json_decode($matches[1], true);
        $openid = $meData['openid'];

        // 获取用户信息
        $userUrl = 'https://graph.qq.com/user/get_user_info';
        $params = [
            'access_token' => $tokenData['access_token'],
            'oauth_consumer_key' => $meData['client_id'],
            'openid' => $openid,
        ];

        $response = $this->httpGet($userUrl . '?' . http_build_query($params));
        $data = json_decode($response, true);

        return [
            'openid' => $openid,
            'unionid' => null,
            'nickname' => $data['nickname'] ?? '',
            'avatar' => $data['figureurl_qq_2'] ?? ($data['figureurl_qq_1'] ?? ''),
            'extra_data' => $data,
        ];
    }

    /**
     * 微博获取用户信息
     */
    protected function getWeiboUserInfo($tokenData)
    {
        $url = 'https://api.weibo.com/2/users/show.json';
        $params = [
            'access_token' => $tokenData['access_token'],
            'uid' => $tokenData['uid'],
        ];

        $response = $this->httpGet($url . '?' . http_build_query($params));
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception('获取微博用户信息失败: ' . ($data['error'] ?? '未知错误'));
        }

        return [
            'openid' => (string)$data['id'],
            'unionid' => null,
            'nickname' => $data['screen_name'] ?? '',
            'avatar' => $data['avatar_large'] ?? '',
            'extra_data' => $data,
        ];
    }

    /**
     * GitHub获取用户信息
     */
    protected function getGithubUserInfo($tokenData)
    {
        $url = 'https://api.github.com/user';
        $response = $this->httpGet($url, [
            'Authorization: token ' . $tokenData['access_token'],
            'User-Agent: CarefreeCMS'
        ]);
        $data = json_decode($response, true);

        if (isset($data['message'])) {
            throw new Exception('获取GitHub用户信息失败: ' . $data['message']);
        }

        return [
            'openid' => (string)$data['id'],
            'unionid' => null,
            'nickname' => $data['login'] ?? '',
            'avatar' => $data['avatar_url'] ?? '',
            'extra_data' => $data,
        ];
    }

    /**
     * 查找或创建用户
     */
    protected function findOrCreateUser($platform, $userInfo, $tokenData)
    {
        Db::startTrans();
        try {
            // 查找已绑定的OAuth记录
            $oauth = FrontUserOAuth::findByPlatformOpenid($platform, $userInfo['openid']);

            if ($oauth) {
                // 已绑定，更新Token和登录信息
                $oauth->updateToken(
                    $tokenData['access_token'],
                    $tokenData['refresh_token'] ?? null,
                    $tokenData['expires_in'] ?? null
                );
                $oauth->updateLoginInfo();

                $user = FrontUser::find($oauth->user_id);
            } else {
                // 未绑定，创建新用户
                $user = FrontUser::create([
                    'username' => $platform . '_' . substr($userInfo['openid'], 0, 10),
                    'nickname' => $userInfo['nickname'],
                    'avatar' => $userInfo['avatar'],
                    'status' => 1,
                    'register_ip' => request()->ip(),
                    'last_login_ip' => request()->ip(),
                    'last_login_time' => date('Y-m-d H:i:s'),
                ]);

                // 创建OAuth绑定记录
                $oauth = FrontUserOAuth::create([
                    'user_id' => $user->id,
                    'platform' => $platform,
                    'openid' => $userInfo['openid'],
                    'unionid' => $userInfo['unionid'],
                    'nickname' => $userInfo['nickname'],
                    'avatar' => $userInfo['avatar'],
                    'access_token' => $tokenData['access_token'],
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'expires_in' => $tokenData['expires_in'] ?? null,
                    'token_expires_at' => isset($tokenData['expires_in']) ?
                        date('Y-m-d H:i:s', time() + $tokenData['expires_in']) : null,
                    'extra_data' => $userInfo['extra_data'],
                    'bind_time' => date('Y-m-d H:i:s'),
                    'last_login_time' => date('Y-m-d H:i:s'),
                    'login_count' => 1,
                    'status' => FrontUserOAuth::STATUS_BOUND,
                ]);
            }

            Db::commit();
            return $user;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

    /**
     * 绑定第三方账号到已有用户
     */
    public function bindAccount($userId, $platform, $code)
    {
        $config = OAuthConfig::getByPlatform($platform);
        if (!$config) {
            throw new Exception('OAuth平台未配置');
        }

        // 检查用户是否存在
        $user = FrontUser::find($userId);
        if (!$user) {
            throw new Exception('用户不存在');
        }

        // 检查是否已绑定该平台
        $existingBind = FrontUserOAuth::findByUserPlatform($userId, $platform);
        if ($existingBind) {
            throw new Exception('该平台账号已绑定');
        }

        // 获取OAuth用户信息
        $tokenData = $this->getAccessToken($platform, $config, $code);
        $userInfo = $this->getUserInfo($platform, $config, $tokenData);

        // 检查该第三方账号是否已被其他用户绑定
        $otherBind = FrontUserOAuth::findByPlatformOpenid($platform, $userInfo['openid']);
        if ($otherBind) {
            throw new Exception('该第三方账号已被其他用户绑定');
        }

        // 创建绑定记录
        FrontUserOAuth::create([
            'user_id' => $userId,
            'platform' => $platform,
            'openid' => $userInfo['openid'],
            'unionid' => $userInfo['unionid'],
            'nickname' => $userInfo['nickname'],
            'avatar' => $userInfo['avatar'],
            'access_token' => $tokenData['access_token'],
            'refresh_token' => $tokenData['refresh_token'] ?? null,
            'expires_in' => $tokenData['expires_in'] ?? null,
            'token_expires_at' => isset($tokenData['expires_in']) ?
                date('Y-m-d H:i:s', time() + $tokenData['expires_in']) : null,
            'extra_data' => $userInfo['extra_data'],
            'bind_time' => date('Y-m-d H:i:s'),
            'status' => FrontUserOAuth::STATUS_BOUND,
        ]);

        return true;
    }

    /**
     * 解绑第三方账号
     */
    public function unbindAccount($userId, $platform)
    {
        $oauth = FrontUserOAuth::findByUserPlatform($userId, $platform);
        if (!$oauth) {
            throw new Exception('未绑定该平台账号');
        }

        return $oauth->unbind();
    }

    /**
     * HTTP GET请求
     */
    protected function httpGet($url, $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * HTTP POST请求
     */
    protected function httpPost($url, $data, $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
