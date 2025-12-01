<?php

namespace app\controller\api;

use OpenApi\Attributes as OA;

#[OA\OpenApi(
    openapi: '3.0.0',
    info: new OA\Info(
        title: '逍遥CMS API文档',
        version: '2.0.0',
        description: '逍遥内容管理系统 RESTful API 文档',
        contact: new OA\Contact(
            name: 'Sinma',
            email: 'sinma@qq.com',
            url: 'https://www.sinma.net'
        )
    ),
    servers: [
        new OA\Server(
            url: '/api',
            description: 'API服务器'
        )
    ]
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'JWT认证，格式：Bearer {token}',
    name: 'Authorization',
    in: 'header',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
#[OA\Tag(
    name: '文章管理',
    description: '文章的增删改查、发布、下线、导出等操作'
)]
#[OA\Tag(
    name: '分类管理',
    description: '文章分类的管理'
)]
#[OA\Tag(
    name: '标签管理',
    description: '文章标签的管理'
)]
#[OA\Tag(
    name: '用户管理',
    description: '用户和权限管理'
)]
#[OA\Tag(
    name: '系统管理',
    description: '系统配置和日志管理'
)]
class OpenApiSpec
{
}
