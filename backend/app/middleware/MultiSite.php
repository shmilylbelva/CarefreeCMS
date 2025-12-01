<?php

namespace app\middleware;

use app\service\SiteContextService;
use Closure;
use think\Request;

/**
 * 多站点中间件
 * 负责识别当前请求对应的站点，并设置站点上下文
 */
class MultiSite
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 识别当前站点
        $site = SiteContextService::identifySite();

        // 将站点信息存储到请求对象中，方便后续使用
        if ($site) {
            $request->site = $site;
            $request->siteId = $site->id;
            $request->siteCode = $site->site_code;
            $request->siteName = $site->site_name;

            // 重要：将站点ID注册到应用容器，供全局查询作用域使用
            app()->bind('current_site_id', $site->id);
            app()->bind('current_site', $site);

            // 记录日志（调试用）
            if (config('app.debug', false)) {
                trace("MultiSite middleware: Current site set to {$site->id} ({$site->site_code})", 'info');
            }
        }

        // 执行请求
        $response = $next($request);

        // 在响应头中添加站点信息（可选，用于调试）
        if ($site && config('app.debug', false)) {
            $response->header([
                'X-Site-Id'   => $site->id,
                'X-Site-Code' => $site->site_code,
                'X-Site-Name' => urlencode($site->site_name),
            ]);
        }

        return $response;
    }
}
