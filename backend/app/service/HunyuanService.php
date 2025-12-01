<?php
declare (strict_types = 1);

namespace app\service;

/**
 * 腾讯混元服务
 */
class HunyuanService extends AiService
{
    /**
     * 测试连接
     */
    public function testConnection()
    {
        try {
            $url = $this->apiEndpoint ?: 'https://hunyuan.tencentcloudapi.com';

            $data = [
                'Model' => $this->model ?: 'hunyuan-lite',
                'Messages' => [
                    [
                        'Role' => 'user',
                        'Content' => 'Hello',
                    ],
                ],
            ];

            $headers = [
                'Content-Type: application/json',
                'Authorization: ' . $this->apiKey,
            ];

            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (isset($response['Response']['Choices'])) {
                return [
                    'success' => true,
                    'message' => '腾讯混元连接测试成功',
                ];
            }

            throw new \Exception('返回数据格式错误');
        } catch (\Exception $e) {
            throw new \Exception('腾讯混元连接测试失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成文章
     */
    public function generateArticle($topic, $options = [])
    {
        $prompt = $this->buildArticlePrompt($topic, $options);
        $url = $this->apiEndpoint ?: 'https://hunyuan.tencentcloudapi.com';

        $data = [
            'Model' => $this->model ?: 'hunyuan-lite',
            'Messages' => [
                [
                    'Role' => 'user',
                    'Content' => $prompt,
                ],
            ],
            'Temperature' => $this->temperature,
            'TopP' => 0.8,
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: ' . $this->apiKey,
        ];

        try {
            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (!isset($response['Response']['Choices'][0]['Message']['Content'])) {
                throw new \Exception('腾讯混元返回数据格式错误');
            }

            $content = $response['Response']['Choices'][0]['Message']['Content'];
            $tokensUsed = $response['Response']['Usage']['TotalTokens'] ?? 0;

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
            throw new \Exception('腾讯混元文章生成失败: ' . $e->getMessage());
        }
    }
}
