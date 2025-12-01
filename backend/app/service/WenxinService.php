<?php
declare (strict_types = 1);

namespace app\service;

/**
 * 百度文心一言服务
 */
class WenxinService extends AiService
{
    private $accessToken;

    /**
     * 获取access_token
     */
    private function getAccessToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        // 文心一言使用API Key和Secret Key换取access_token
        $settings = $this->config->settings ?? [];
        $clientId = $this->apiKey; // API Key作为client_id
        $clientSecret = $settings['secret_key'] ?? ''; // Secret Key

        if (!$clientSecret) {
            throw new \Exception('百度文心一言需要配置Secret Key');
        }

        $url = 'https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id=' . $clientId . '&client_secret=' . $clientSecret;

        try {
            $response = $this->httpRequest($url, [], 'GET', []);
            $this->accessToken = $response['access_token'] ?? '';
            return $this->accessToken;
        } catch (\Exception $e) {
            throw new \Exception('获取百度文心一言Access Token失败: ' . $e->getMessage());
        }
    }

    /**
     * 测试连接
     */
    public function testConnection()
    {
        try {
            $token = $this->getAccessToken();
            return [
                'success' => true,
                'message' => 'Access Token获取成功',
            ];
        } catch (\Exception $e) {
            throw new \Exception('百度文心一言连接测试失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成文章
     */
    public function generateArticle($topic, $options = [])
    {
        $prompt = $this->buildArticlePrompt($topic, $options);
        $token = $this->getAccessToken();

        // 文心一言API endpoint
        $model = $this->model ?: 'completions_pro'; // 或 eb-instant, completions等
        $url = 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/' . $model . '?access_token=' . $token;

        $data = [
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => $this->temperature,
            'top_p' => 0.8,
        ];

        $headers = [
            'Content-Type: application/json',
        ];

        try {
            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (!isset($response['result'])) {
                throw new \Exception('百度文心一言返回数据格式错误');
            }

            $content = $response['result'];
            $tokensUsed = $response['usage']['total_tokens'] ?? 0;

            // 解析返回的JSON
            $result = $this->parseAiResponse($content);

            return [
                'title' => $result['title'] ?? '',
                'content' => $result['content'] ?? '',
                'summary' => $result['summary'] ?? '',
                'keywords' => $result['keywords'] ?? '',
                'tokens' => $tokensUsed,
            ];

        } catch (\Exception $e) {
            throw new \Exception('百度文心一言文章生成失败: ' . $e->getMessage());
        }
    }
}
