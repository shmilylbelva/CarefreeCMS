<?php
declare(strict_types=1);

namespace app\controller\api;

use OpenApi\Generator;
use think\facade\View;
use app\common\Response;

/**
 * API文档控制器
 */
class ApiDoc
{
    /**
     * 生成Swagger JSON
     */
    public function json()
    {
        $openapi = Generator::scan([
            app_path('controller/api')
        ]);

        header('Content-Type: application/json');
        echo $openapi->toJson();
        exit;
    }

    /**
     * 显示Swagger UI
     */
    public function index()
    {
        $html = <<<'HTML'
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API文档 - 逍遥CMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.10.5/swagger-ui.css">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        .topbar {
            display: none;
        }
        .swagger-ui .info {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.10.5/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5.10.5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "/api/api-doc/json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                defaultModelsExpandDepth: -1,
                docExpansion: "list",
                filter: true,
                tryItOutEnabled: true,
                persistAuthorization: true,
                onComplete: function() {
                    // 自动从localStorage恢复token
                    const token = localStorage.getItem('cms_token');
                    if (token) {
                        ui.preauthorizeApiKey('bearerAuth', token);
                    }
                }
            });
        }
    </script>
</body>
</html>
HTML;

        echo $html;
        exit;
    }

    /**
     * 获取API统计信息
     */
    public function statistics()
    {
        try {
            $openapi = Generator::scan([
                app_path('controller/api')
            ]);

            $paths = $openapi->paths ?? [];
            $totalEndpoints = 0;
            $methods = [
                'GET' => 0,
                'POST' => 0,
                'PUT' => 0,
                'DELETE' => 0,
                'PATCH' => 0
            ];

            foreach ($paths as $path) {
                foreach (['get', 'post', 'put', 'delete', 'patch'] as $method) {
                    if (isset($path->$method)) {
                        $totalEndpoints++;
                        $methods[strtoupper($method)]++;
                    }
                }
            }

            return Response::success([
                'total_endpoints' => $totalEndpoints,
                'methods' => $methods,
                'version' => $openapi->info->version ?? '1.0.0',
                'title' => $openapi->info->title ?? 'API文档'
            ]);

        } catch (\Exception $e) {
            return Response::error('获取统计信息失败：' . $e->getMessage());
        }
    }
}
