<?php
declare (strict_types = 1);

namespace app\service;

/**
 * 讯飞星火服务
 */
class SparkService extends AiService
{
    /**
     * 测试连接
     */
    public function testConnection()
    {
        try {
            // 星火使用HTTP API v2
            $url = $this->apiEndpoint ?: 'https://spark-api-open.xf-yun.com/v1/chat/completions';

            $data = [
                'model' => $this->model ?: 'generalv3.5',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => 'Hello',
                    ],
                ],
            ];

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ];

            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (isset($response['choices'])) {
                return [
                    'success' => true,
                    'message' => '讯飞星火连接测试成功',
                ];
            }

            throw new \Exception('返回数据格式错误');
        } catch (\Exception $e) {
            throw new \Exception('讯飞星火连接测试失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成文章
     */
    public function generateArticle($topic, $options = [])
    {
        $prompt = $this->buildArticlePrompt($topic, $options);
        $url = $this->apiEndpoint ?: 'https://spark-api-open.xf-yun.com/v1/chat/completions';

        $data = [
            'model' => $this->model ?: 'generalv3.5',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxTokens,
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey,
        ];

        try {
            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (!isset($response['choices'][0]['message']['content'])) {
                throw new \Exception('讯飞星火返回数据格式错误');
            }

            $content = $response['choices'][0]['message']['content'];
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
            throw new \Exception('讯飞星火文章生成失败: ' . $e->getMessage());
        }
    }
}
