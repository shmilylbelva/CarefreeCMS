<?php
declare (strict_types = 1);

namespace app\service;

/**
 * Claude服务
 */
class ClaudeService extends AiService
{
    /**
     * 测试连接
     */
    public function testConnection()
    {
        // Claude API没有专门的测试端点，我们发送一个简单的请求来测试
        try {
            $this->generateSimpleText('测试连接');
            return [
                'success' => true,
                'message' => 'Claude连接正常',
            ];
        } catch (\Exception $e) {
            throw new \Exception('Claude连接测试失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成简单文本（用于测试）
     */
    private function generateSimpleText($prompt)
    {
        $url = ($this->apiEndpoint ?: 'https://api.anthropic.com/v1') . '/messages';

        $data = [
            'model' => $this->model ?: 'claude-3-sonnet-20240229',
            'max_tokens' => 100,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ];

        $headers = [
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: 2023-06-01',
            'Content-Type: application/json',
        ];

        return $this->httpRequest($url, $data, 'POST', $headers);
    }

    /**
     * 生成文章
     */
    public function generateArticle($topic, $options = [])
    {
        $prompt = $this->buildArticlePrompt($topic, $options);

        $url = ($this->apiEndpoint ?: 'https://api.anthropic.com/v1') . '/messages';

        $data = [
            'model' => $this->model ?: 'claude-3-sonnet-20240229',
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
        ];

        $headers = [
            'x-api-key: ' . $this->apiKey,
            'anthropic-version: 2023-06-01',
            'Content-Type: application/json',
        ];

        try {
            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (!isset($response['content'][0]['text'])) {
                throw new \Exception('Claude返回数据格式错误');
            }

            $content = $response['content'][0]['text'];
            $tokensUsed = ($response['usage']['input_tokens'] ?? 0) + ($response['usage']['output_tokens'] ?? 0);

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
            throw new \Exception('Claude文章生成失败: ' . $e->getMessage());
        }
    }
}
