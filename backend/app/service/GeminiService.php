<?php
declare (strict_types = 1);

namespace app\service;

/**
 * Google Gemini服务
 */
class GeminiService extends AiService
{
    /**
     * 测试连接
     */
    public function testConnection()
    {
        try {
            $model = $this->model ?: 'gemini-pro';
            $url = $this->apiEndpoint ?: "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent";
            
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Hello'],
                        ],
                    ],
                ],
            ];

            // Gemini使用URL参数传递API Key
            $url .= '?key=' . $this->apiKey;

            $headers = [
                'Content-Type: application/json',
            ];

            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (isset($response['candidates'])) {
                return [
                    'success' => true,
                    'message' => 'Gemini连接测试成功',
                ];
            }

            throw new \Exception('返回数据格式错误');
        } catch (\Exception $e) {
            throw new \Exception('Gemini连接测试失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成文章
     */
    public function generateArticle($topic, $options = [])
    {
        $prompt = $this->buildArticlePrompt($topic, $options);
        $model = $this->model ?: 'gemini-pro';
        $url = $this->apiEndpoint ?: "https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent";
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => $this->temperature,
                'topP' => 0.8,
                'maxOutputTokens' => $this->maxTokens,
            ],
        ];

        // Gemini使用URL参数传递API Key
        $url .= '?key=' . $this->apiKey;

        $headers = [
            'Content-Type: application/json',
        ];

        try {
            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Gemini返回数据格式错误');
            }

            $content = $response['candidates'][0]['content']['parts'][0]['text'];
            $tokensUsed = $response['usageMetadata']['totalTokenCount'] ?? 0;

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
            throw new \Exception('Gemini文章生成失败: ' . $e->getMessage());
        }
    }
}
