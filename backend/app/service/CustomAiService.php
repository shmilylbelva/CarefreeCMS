<?php
declare (strict_types = 1);

namespace app\service;

/**
 * 自定义AI服务（兼容OpenAI接口）
 */
class CustomAiService extends OpenAiService
{
    /**
     * 构造函数
     * 使用用户配置的endpoint和model
     */
    public function __construct($config)
    {
        parent::__construct($config);
        
        // 自定义服务允许用户完全自定义endpoint和model
        // 不设置默认值，完全由用户配置
    }
}
