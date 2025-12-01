<?php
declare (strict_types = 1);

namespace app\service;

use app\model\AiConfig;

/**
 * AI服务基类
 */
abstract class AiService
{
    protected $config;
    protected $apiKey;
    protected $apiEndpoint;
    protected $model;
    protected $maxTokens;
    protected $temperature;

    public function __construct(AiConfig $config)
    {
        $this->config = $config;
        $this->apiKey = $config->api_key;
        $this->apiEndpoint = $config->api_endpoint;
        $this->model = $config->model;
        $this->maxTokens = $config->max_tokens ?? 2000;
        $this->temperature = $config->temperature ?? 0.7;
    }

    /**
     * 根据配置创建对应的AI服务实例
     */
    public static function createFromConfig(AiConfig $config)
    {
        switch ($config->provider) {
            case AiConfig::PROVIDER_OPENAI:
                return new OpenAiService($config);
            case AiConfig::PROVIDER_CLAUDE:
                return new ClaudeService($config);
            case AiConfig::PROVIDER_GEMINI:
                return new GeminiService($config);
            case AiConfig::PROVIDER_WENXIN:
                return new WenxinService($config);
            case AiConfig::PROVIDER_TONGYI:
                return new TongyiService($config);
            case AiConfig::PROVIDER_CHATGLM:
                return new ChatglmService($config);
            case AiConfig::PROVIDER_DEEPSEEK:
                return new DeepseekService($config);
            case AiConfig::PROVIDER_KIMI:
                return new KimiService($config);
            case AiConfig::PROVIDER_DOUBAO:
                return new DoubaoService($config);
            case AiConfig::PROVIDER_SPARK:
                return new SparkService($config);
            case AiConfig::PROVIDER_HUNYUAN:
                return new HunyuanService($config);
            case AiConfig::PROVIDER_MINIMAX:
                return new MinimaxService($config);
            case AiConfig::PROVIDER_CUSTOM:
                return new CustomAiService($config);
            default:
                // 所有其他厂商（包括自定义厂商）都使用CustomAiService
                // CustomAiService支持OpenAI兼容的API
                return new CustomAiService($config);
        }
    }

    /**
     * 测试连接
     */
    abstract public function testConnection();

    /**
     * 生成文章
     * @param string $topic 主题
     * @param array $options 选项：长度、风格等
     * @return array ['title' => '', 'content' => '', 'summary' => '', 'keywords' => '', 'tokens' => 0]
     */
    abstract public function generateArticle($topic, $options = []);

    /**
     * 发送HTTP请求
     */
    protected function httpRequest($url, $data = [], $method = 'POST', $headers = [])
    {
        $ch = curl_init();

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } else {
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            throw new \Exception('HTTP请求失败: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception('HTTP请求返回错误代码: ' . $httpCode . ', 响应: ' . $response);
        }

        return json_decode($response, true);
    }

    /**
     * 构建文章生成提示词
     */
    protected function buildArticlePrompt($topic, $options = [])
    {
        $length = $options['length'] ?? 'medium'; // short/medium/long
        $style = $options['style'] ?? 'professional'; // professional/casual/creative
        $includeImages = $options['include_images'] ?? false;

        $lengthGuide = [
            'short' => '500-800字',
            'medium' => '1000-1500字',
            'long' => '2000-3000字',
        ];

        $styleGuide = [
            'professional' => '专业、严谨的写作风格',
            'casual' => '轻松、口语化的写作风格',
            'creative' => '创意、富有想象力的写作风格',
        ];

        $prompt = "请根据以下主题生成一篇文章：\n\n";
        $prompt .= "主题: {$topic}\n\n";
        $prompt .= "要求:\n";
        $prompt .= "1. 文章长度: " . ($lengthGuide[$length] ?? '1000-1500字') . "\n";
        $prompt .= "2. 写作风格: " . ($styleGuide[$style] ?? '专业、严谨的写作风格') . "\n";
        $prompt .= "3. 内容要求: 内容充实、逻辑清晰、观点明确、有实用价值\n";
        $prompt .= "4. 结构要求: 包含引言、正文（分段）、结尾\n";
        $prompt .= "5. SEO优化: 自然融入关键词，但不要过度堆砌\n";
        $prompt .= "6. 格式要求: 使用HTML格式，包含<p>、<h2>、<h3>等标签\n\n";
        $prompt .= "请返回JSON格式，包含以下字段:\n";
        $prompt .= "{\n";
        $prompt .= "  \"title\": \"文章标题（吸引人、包含关键词）\",\n";
        $prompt .= "  \"content\": \"文章正文（HTML格式）\",\n";
        $prompt .= "  \"summary\": \"文章摘要（100-200字）\",\n";
        $prompt .= "  \"keywords\": \"SEO关键词（逗号分隔）\"\n";
        $prompt .= "}\n";

        return $prompt;
    }

    /**
     * 解析AI返回的JSON
     */
    protected function parseAiResponse($response)
    {
        // 如果已经是数组，直接返回
        if (is_array($response)) {
            return $response;
        }

        if (!is_string($response)) {
            throw new \Exception('无效的响应类型');
        }

        // 方法1：移除markdown代码块标记后直接解析
        $cleaned = preg_replace('/```json\s*|\s*```/', '', $response);
        $cleaned = trim($cleaned);
        $decoded = json_decode($cleaned, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // 方法2：查找JSON对象（使用正则提取{...}之间的内容）
        if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            $jsonStr = $matches[0];
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // 方法3：查找JSON数组（使用正则提取[...]之间的内容）
        if (preg_match('/\[[\s\S]*\]/', $response, $matches)) {
            $jsonStr = $matches[0];
            $decoded = json_decode($jsonStr, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // 方法4：尝试从纯文本中提取标题和内容
        $result = $this->parseTextResponse($response);
        if (!empty($result)) {
            return $result;
        }

        throw new \Exception('无法解析AI返回的响应，响应内容：' . mb_substr($response, 0, 200));
    }

    /**
     * 从纯文本中提取文章信息
     */
    protected function parseTextResponse($text)
    {
        $result = [
            'title' => '',
            'content' => '',
            'summary' => '',
            'keywords' => '',
        ];

        // 尝试提取标题（第一行或带有"标题"字样的行）
        $lines = explode("\n", $text);
        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // 如果包含"标题"关键字
            if (preg_match('/(?:标题|title)[：:]\s*(.+)/iu', $line, $matches)) {
                $result['title'] = trim($matches[1]);
                unset($lines[$index]);
                break;
            }

            // 或者第一个非空行作为标题
            if (empty($result['title']) && !empty($line)) {
                // 去除markdown标题符号
                $result['title'] = preg_replace('/^#+\s*/', '', $line);
                unset($lines[$index]);
                break;
            }
        }

        // 剩余内容作为正文
        $content = implode("\n", $lines);
        $content = trim($content);

        // 移除"正文"、"内容"等标签
        $content = preg_replace('/^(?:正文|内容|文章)[：:]\s*/iu', '', $content);

        // 将换行转换为HTML段落
        $paragraphs = array_filter(explode("\n\n", $content));
        $htmlContent = '';
        foreach ($paragraphs as $para) {
            $para = trim($para);
            if (empty($para)) {
                continue;
            }
            // 处理小标题（以#开头）
            if (preg_match('/^#+\s*(.+)/', $para, $matches)) {
                $htmlContent .= '<h2>' . trim($matches[1]) . '</h2>';
            } else {
                $htmlContent .= '<p>' . nl2br(htmlspecialchars($para)) . '</p>';
            }
        }

        $result['content'] = $htmlContent ?: $content;

        // 生成简单摘要（前200字）
        $result['summary'] = mb_substr(strip_tags($result['content']), 0, 200);

        return $result;
    }
}
