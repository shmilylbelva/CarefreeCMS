<?php
declare(strict_types=1);

namespace app\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * API版本控制中间件
 */
class ApiVersion
{
    /**
     * 支持的API版本列表
     */
    private const SUPPORTED_VERSIONS = ['v1', 'v2'];

    /**
     * 默认API版本
     */
    private const DEFAULT_VERSION = 'v1';

    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next)
    {
        // 从请求头获取版本
        $version = $request->header('API-Version', self::DEFAULT_VERSION);

        // 也支持从URL路径获取版本
        $path = $request->pathinfo();
        if (preg_match('/^api\/(v\d+)\//', $path, $matches)) {
            $version = $matches[1];
        }

        // 验证版本
        if (!in_array($version, self::SUPPORTED_VERSIONS)) {
            return json([
                'code' => 400,
                'message' => "不支持的API版本: {$version}，支持的版本: " . implode(', ', self::SUPPORTED_VERSIONS)
            ], 400);
        }

        // 注入版本信息到请求对象
        $request->apiVersion = $version;

        $response = $next($request);

        // 在响应头中添加版本信息
        $response->header(['API-Version' => $version]);

        return $response;
    }
}
