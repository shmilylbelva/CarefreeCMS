<?php
// CORS跨域配置
return [
    // 允许的来源白名单（从.env读取，多个用逗号分隔）
    'allowed_origins' => !empty(env('cors.allowed_origins'))
        ? array_map('trim', explode(',', env('cors.allowed_origins')))
        : [
            'http://localhost:5173',
            'http://localhost:3000',
            'http://localhost:3001',
            'http://127.0.0.1:5173',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:3001',
        ],

    // 允许的HTTP方法
    'allowed_methods' => 'GET, POST, PUT, DELETE, OPTIONS, PATCH',

    // 允许的请求头
    'allowed_headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-CSRF-Token',

    // 预检请求的有效期（秒）
    'max_age' => 86400,

    // 允许暴露的响应头
    'expose_headers' => 'Content-Length, Content-Type',

    // 是否允许携带认证信息（cookies等）
    'allow_credentials' => true,
];
