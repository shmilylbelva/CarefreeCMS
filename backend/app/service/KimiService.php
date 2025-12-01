<?php
declare (strict_types = 1);

namespace app\service;

/**
 * Kimi服务（月之暗面，兼容OpenAI接口）
 */
class KimiService extends OpenAiService
{
    /**
     * 重写API端点默认值
     */
    public function __construct($config)
    {
        parent::__construct($config);

        // Kimi使用自己的API端点
        if (!$this->apiEndpoint) {
            $this->apiEndpoint = 'https://api.moonshot.cn/v1';
        }

        // Kimi默认模型
        if (!$this->model) {
            $this->model = 'moonshot-v1-8k';
        }
    }
}
