<?php
declare (strict_types = 1);

namespace app\controller\api;

use app\BaseController;
use app\service\OAuthService;
use app\model\FrontUserOAuth;
use app\model\OAuthConfig;
use think\Request;
use think\Response;
use think\facade\Cache;

/**
 * OAuth登录控制器（前台）
 */
class OAuthController extends BaseController
{
    protected $oauthService;

    public function initialize()
    {
        parent::initialize();
        $this->oauthService = new OAuthService();
    }

    /**
     * 获取授权登录URL
     */
    public function getAuthUrl(Request $request): Response
    {
        $platform = $request->param('platform');

        if (!$platform) {
            return json(['code' => 400, 'message' => '请指定OAuth平台']);
        }

        try {
            // 生成并缓存state
            $state = md5(uniqid() . time());
            Cache::set('oauth_state_' . $state, $platform, 600); // 10分钟有效期

            $url = $this->oauthService->getAuthUrl($platform, $state);

            return json([
                'code' => 0,
                'message' => 'success',
                'data' => [
                    'auth_url' => $url,
                    'state' => $state
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'message' => $e->getMessage()]);
        }
    }

    /**
     * OAuth回调处理
     */
    public function callback(Request $request): Response
    {
        $code = $request->param('code');
        $state = $request->param('state');

        if (!$code) {
            return json(['code' => 400, 'message' => '授权失败，缺少code参数']);
        }

        try {
            // 验证state（防CSRF）
            $platform = Cache::get('oauth_state_' . $state);
            if (!$platform) {
                return json(['code' => 400, 'message' => 'state参数无效或已过期']);
            }

            // 清除state缓存
            Cache::delete('oauth_state_' . $state);

            // 处理OAuth回调
            $user = $this->oauthService->handleCallback($platform, $code, $state);

            // 生成JWT Token（这里需要根据实际的登录逻辑调整）
            $token = $this->generateToken($user);

            return json([
                'code' => 0,
                'message' => '登录成功',
                'data' => [
                    'token' => $token,
                    'user' => $user
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'message' => '登录失败: ' . $e->getMessage()]);
        }
    }

    /**
     * 绑定第三方账号
     */
    public function bind(Request $request): Response
    {
        // 需要用户已登录
        $userId = $request->userId; // 从中间件或JWT获取
        if (!$userId) {
            return json(['code' => 401, 'message' => '请先登录']);
        }

        $platform = $request->param('platform');
        $code = $request->param('code');

        if (!$platform || !$code) {
            return json(['code' => 400, 'message' => '参数不完整']);
        }

        try {
            $this->oauthService->bindAccount($userId, $platform, $code);

            return json([
                'code' => 0,
                'message' => '绑定成功'
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'message' => '绑定失败: ' . $e->getMessage()]);
        }
    }

    /**
     * 解绑第三方账号
     */
    public function unbind(Request $request): Response
    {
        // 需要用户已登录
        $userId = $request->userId;
        if (!$userId) {
            return json(['code' => 401, 'message' => '请先登录']);
        }

        $platform = $request->param('platform');
        if (!$platform) {
            return json(['code' => 400, 'message' => '请指定要解绑的平台']);
        }

        try {
            $this->oauthService->unbindAccount($userId, $platform);

            return json([
                'code' => 0,
                'message' => '解绑成功'
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'message' => '解绑失败: ' . $e->getMessage()]);
        }
    }

    /**
     * 获取用户的绑定列表
     */
    public function getUserBindings(Request $request): Response
    {
        $userId = $request->userId;
        if (!$userId) {
            return json(['code' => 401, 'message' => '请先登录']);
        }

        try {
            $bindings = FrontUserOAuth::getUserBindings($userId);

            // 获取所有平台
            $allPlatforms = OAuthConfig::getPlatformOptions();

            // 标记哪些平台已绑定
            $result = [];
            foreach ($allPlatforms as $platform => $name) {
                $binding = $bindings->where('platform', $platform)->first();
                $result[] = [
                    'platform' => $platform,
                    'platform_name' => $name,
                    'is_bound' => !empty($binding),
                    'binding_info' => $binding ? [
                        'nickname' => $binding->nickname,
                        'avatar' => $binding->avatar,
                        'bind_time' => $binding->bind_time,
                        'last_login_time' => $binding->last_login_time,
                        'login_count' => $binding->login_count,
                    ] : null
                ];
            }

            return json([
                'code' => 0,
                'message' => 'success',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 获取启用的OAuth平台列表（公开接口）
     */
    public function getEnabledPlatforms(): Response
    {
        try {
            $platforms = OAuthConfig::getEnabledPlatforms();

            return json([
                'code' => 0,
                'message' => 'success',
                'data' => $platforms->map(function($item) {
                    return [
                        'platform' => $item->platform,
                        'platform_name' => $item->platform_name,
                        'sort_order' => $item->sort_order,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 生成JWT Token（示例，需要根据实际项目调整）
     */
    private function generateToken($user)
    {
        $payload = [
            'iss' => 'cms_system',
            'aud' => 'cms_user',
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 7200, // 2小时过期
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
            ]
        ];

        // 这里需要使用JWT库生成token
        // 暂时返回一个模拟token
        $key = config('app.jwt_key', 'your-secret-key');
        return base64_encode(json_encode($payload));
    }
}
