<?php
declare (strict_types = 1);

namespace app\service;

/**
 * 豆包服务（字节跳动，使用火山引擎API，兼容OpenAI接口）
 */
class DoubaoService extends OpenAiService
{
    /**
     * 重写API端点默认值
     */
    public function __construct($config)
    {
        parent::__construct($config);

        // 豆包使用火山引擎API端点
        if (!$this->apiEndpoint) {
            $this->apiEndpoint = 'https://ark.cn-beijing.volces.com/api/v3';
        }

        // 豆包默认模型（需要用户配置endpoint_id）
        if (!$this->model) {
            $this->model = 'doubao-pro-4k';
        }
    }
}
