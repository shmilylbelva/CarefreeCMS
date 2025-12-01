<?php
declare (strict_types = 1);

namespace app\service;

/**
 * 阿里通义千问服务
 */
class TongyiService extends AiService
{
    /**
     * 测试连接
     */
    public function testConnection()
    {
        try {
            $url = $this->apiEndpoint ?: 'https://dashscope.aliyuncs.com/api/v1/services/aigc/text-generation/generation';

            $data = [
                'model' => $this->model ?: 'qwen-turbo',
                'input' => [
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => 'Hello',
                        ],
                    ],
                ],
                'parameters' => [
                    'result_format' => 'message',
                ],
            ];

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ];

            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (isset($response['output'])) {
                return [
                    'success' => true,
                    'message' => '通义千问连接测试成功',
                ];
            }

            throw new \Exception('返回数据格式错误');
        } catch (\Exception $e) {
            throw new \Exception('通义千问连接测试失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成文章
     */
    public function generateArticle($topic, $options = [])
    {
        $prompt = $this->buildArticlePrompt($topic, $options);
        $url = $this->apiEndpoint ?: 'https://dashscope.aliyuncs.com/api/v1/services/aigc/text-generation/generation';

        $data = [
            'model' => $this->model ?: 'qwen-turbo',
            'input' => [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ],
            'parameters' => [
                'result_format' => 'message',
                'temperature' => $this->temperature,
                'top_p' => 0.8,
                'max_tokens' => $this->maxTokens,
            ],
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        try {
            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (!isset($response['output']['choices'][0]['message']['content'])) {
                throw new \Exception('通义千问返回数据格式错误');
            }

            $content = $response['output']['choices'][0]['message']['content'];
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
            throw new \Exception('通义千问文章生成失败: ' . $e->getMessage());
        }
    }
}
