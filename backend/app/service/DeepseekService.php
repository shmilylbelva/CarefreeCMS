<?php
declare (strict_types = 1);

namespace app\service;

/**
 * DeepSeek服务（兼容OpenAI接口）
 */
class DeepseekService extends OpenAiService
{
    /**
     * 重写API端点默认值
     */
    public function __construct($config)
    {
        parent::__construct($config);

        // DeepSeek使用自己的API端点
        if (!$this->apiEndpoint) {
            $this->apiEndpoint = 'https://api.deepseek.com/v1';
        }

        // DeepSeek默认模型
        if (!$this->model) {
            $this->model = 'deepseek-chat';
        }
    }
}
