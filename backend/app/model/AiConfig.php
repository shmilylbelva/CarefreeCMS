<?php
declare (strict_types = 1);

namespace app\model;

/**
 * AI配置模型
 */
class AiConfig extends SiteModel
{
    protected $name = 'ai_configs';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'max_tokens' => 'integer',
        'temperature' => 'float',
        'is_default' => 'integer',
        'status' => 'integer',
        'settings' => 'json',
    ];

    // AI提供商常量
    const PROVIDER_OPENAI = 'openai';
    const PROVIDER_CLAUDE = 'claude';
    const PROVIDER_GEMINI = 'gemini';      // Google Gemini
    const PROVIDER_WENXIN = 'wenxin';      // 百度文心一言
    const PROVIDER_TONGYI = 'tongyi';      // 阿里通义千问
    const PROVIDER_CHATGLM = 'chatglm';    // 智谱ChatGLM
    const PROVIDER_DEEPSEEK = 'deepseek';  // DeepSeek
    const PROVIDER_KIMI = 'kimi';          // 月之暗面Kimi
    const PROVIDER_DOUBAO = 'doubao';      // 字节跳动豆包
    const PROVIDER_SPARK = 'spark';        // 讯飞星火
    const PROVIDER_HUNYUAN = 'hunyuan';    // 腾讯混元
    const PROVIDER_MINIMAX = 'minimax';    // MiniMax
    const PROVIDER_CUSTOM = 'custom';      // 自定义兼容OpenAI API

    /**
     * 获取所有支持的AI提供商（从数据库读取）
     */
    public static function getProviders()
    {
        $providers = AiProvider::where('status', 1)
            ->order('sort_order', 'asc')
            ->order('id', 'asc')
            ->select();

        $result = [];
        foreach ($providers as $provider) {
            $result[$provider->code] = $provider->name;
        }

        return $result;
    }

    /**
     * 获取默认配置
     */
    public static function getDefault()
    {
        return self::where('is_default', 1)
            ->where('status', 1)
            ->find();
    }

    /**
     * 设置为默认配置
     */
    public function setAsDefault()
    {
        // 先取消其他默认配置
        self::where('is_default', 1)->update(['is_default' => 0]);

        // 设置当前为默认
        $this->is_default = 1;
        $this->save();
    }

    /**
     * 测试AI连接
     * @return array
     */
    public function testConnection()
    {
        try {
            $service = \app\service\AiService::createFromConfig($this);
            $result = $service->testConnection();

            return [
                'success' => true,
                'message' => 'AI连接测试成功',
                'data' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'AI连接测试失败：' . $e->getMessage()
            ];
        }
    }

    /**
     * 搜索器：提供商
     */
    public function searchProviderAttr($query, $value)
    {
        if ($value) {
            $query->where('provider', $value);
        }
    }

    /**
     * 搜索器：状态
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== null && $value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * 获取各平台支持的模型列表（从数据库读取）
     */
    public static function getProviderModels()
    {
        return AiModel::getAllModelsGrouped();
    }

    /**
     * 获取提供商配置说明
     */
    public static function getProviderConfigGuide()
    {
        return [
            self::PROVIDER_OPENAI => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入OpenAI API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => 'https://api.openai.com/v1 (可选)', 'required' => false],
                'extra_fields' => [],
            ],
            self::PROVIDER_CLAUDE => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入Claude API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => 'https://api.anthropic.com/v1 (可选)', 'required' => false],
                'extra_fields' => [],
            ],
            self::PROVIDER_GEMINI => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入Google AI API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => '默认使用Google官方端点 (可选)', 'required' => false],
                'extra_fields' => [],
            ],
            self::PROVIDER_WENXIN => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入百度API Key (作为Client ID)', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => '默认使用百度官方端点 (可选)', 'required' => false],
                'extra_fields' => [
                    'secret_key' => ['label' => 'Secret Key', 'placeholder' => '输入百度Secret Key', 'required' => true, 'type' => 'password'],
                ],
            ],
            self::PROVIDER_TONGYI => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入阿里云DashScope API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => 'https://dashscope.aliyuncs.com (可选)', 'required' => false],
                'extra_fields' => [],
            ],
            self::PROVIDER_CHATGLM => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入智谱AI API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => 'https://open.bigmodel.cn (可选)', 'required' => false],
                'extra_fields' => [],
            ],
            self::PROVIDER_DEEPSEEK => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入DeepSeek API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => 'https://api.deepseek.com/v1 (可选)', 'required' => false],
                'extra_fields' => [],
            ],
            self::PROVIDER_KIMI => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入Kimi (Moonshot) API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => 'https://api.moonshot.cn/v1 (可选)', 'required' => false],
                'extra_fields' => [],
            ],
            self::PROVIDER_DOUBAO => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入火山引擎API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => 'https://ark.cn-beijing.volces.com/api/v3 (可选)', 'required' => false],
                'extra_fields' => [
                    'endpoint_id' => ['label' => 'Endpoint ID', 'placeholder' => '输入模型推理接入点ID', 'required' => false, 'type' => 'text'],
                ],
            ],
            self::PROVIDER_SPARK => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入讯飞星火API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => 'https://spark-api-open.xf-yun.com (可选)', 'required' => false],
                'extra_fields' => [],
            ],
            self::PROVIDER_HUNYUAN => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入腾讯云API密钥', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => 'https://hunyuan.tencentcloudapi.com (可选)', 'required' => false],
                'extra_fields' => [],
            ],
            self::PROVIDER_MINIMAX => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入MiniMax API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => 'https://api.minimax.chat (可选)', 'required' => false],
                'extra_fields' => [],
            ],
            self::PROVIDER_CUSTOM => [
                'api_key' => ['label' => 'API Key', 'placeholder' => '输入自定义API Key', 'required' => true],
                'api_endpoint' => ['label' => 'API端点', 'placeholder' => '输入兼容OpenAI格式的API端点 (必填)', 'required' => true],
                'extra_fields' => [],
            ],
        ];
    }
}
