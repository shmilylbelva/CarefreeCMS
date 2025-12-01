<?php
declare (strict_types = 1);

namespace app\service;

/**
 * MiniMax服务
 */
class MinimaxService extends AiService
{
    /**
     * 测试连接
     */
    public function testConnection()
    {
        try {
            $url = $this->apiEndpoint ?: 'https://api.minimax.chat/v1/text/chatcompletion_v2';

            $data = [
                'model' => $this->model ?: 'abab6-chat',
                'messages' => [
                    [
                        'sender_type' => 'USER',
                        'sender_name' => '用户',
                        'text' => 'Hello',
                    ],
                ],
                'bot_setting' => [
                    [
                        'bot_name' => '助手',
                        'content' => '你是一个AI助手',
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
                    'message' => 'MiniMax连接测试成功',
                ];
            }

            throw new \Exception('返回数据格式错误');
        } catch (\Exception $e) {
            throw new \Exception('MiniMax连接测试失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成文章
     */
    public function generateArticle($topic, $options = [])
    {
        $prompt = $this->buildArticlePrompt($topic, $options);
        $url = $this->apiEndpoint ?: 'https://api.minimax.chat/v1/text/chatcompletion_v2';

        $data = [
            'model' => $this->model ?: 'abab6-chat',
            'messages' => [
                [
                    'sender_type' => 'USER',
                    'sender_name' => '用户',
                    'text' => $prompt,
                ],
            ],
            'bot_setting' => [
                [
                    'bot_name' => '文章生成助手',
                    'content' => '你是一个专业的文章生成助手，善于根据主题创作高质量的内容。',
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

            if (!isset($response['choices'][0]['text'])) {
                throw new \Exception('MiniMax返回数据格式错误');
            }

            $content = $response['choices'][0]['text'];
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
            throw new \Exception('MiniMax文章生成失败: ' . $e->getMessage());
        }
    }
}
