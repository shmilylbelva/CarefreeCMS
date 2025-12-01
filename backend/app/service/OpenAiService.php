<?php
declare (strict_types = 1);

namespace app\service;

/**
 * OpenAI服务
 */
class OpenAiService extends AiService
{
    /**
     * 测试连接
     */
    public function testConnection()
    {
        $url = $this->apiEndpoint ?: 'https://api.openai.com/v1/models';

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        try {
            $response = $this->httpRequest($url, [], 'GET', $headers);
            return [
                'success' => true,
                'models' => array_column($response['data'] ?? [], 'id'),
            ];
        } catch (\Exception $e) {
            throw new \Exception('OpenAI连接测试失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成文章
     */
    public function generateArticle($topic, $options = [])
    {
        // 检查是否使用原始提示词（模板已经包含完整提示词）
        $useRawPrompt = $options['use_raw_prompt'] ?? false;

        if ($useRawPrompt) {
            // 直接使用传入的prompt，不再调用buildArticlePrompt
            $prompt = $topic;
        } else {
            // 根据topic和options构建提示词（包含长度、风格等要求）
            $prompt = $this->buildArticlePrompt($topic, $options);
        }

        $url = ($this->apiEndpoint ?: 'https://api.openai.com/v1') . '/chat/completions';

        $data = [
            'model' => $this->model ?: 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => '你是一个专业的内容创作者，擅长撰写高质量的文章。',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ];

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        try {
            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (!isset($response['choices'][0]['message']['content'])) {
                throw new \Exception('OpenAI返回数据格式错误');
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
            throw new \Exception('OpenAI文章生成失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成图片 (DALL-E)
     */
    public function generateImage(array $options = []): array
    {
        $url = ($this->apiEndpoint ?: 'https://api.openai.com/v1') . '/images/generations';

        // 构建请求数据
        $data = [
            'model' => $options['model'] ?? 'dall-e-3',
            'prompt' => $options['prompt'],
            'n' => $options['n'] ?? 1,
            'size' => $options['size'] ?? '1024x1024',
            'quality' => $options['quality'] ?? 'standard',
            'response_format' => 'url',
        ];

        // DALL-E 3 支持 style 参数
        if (isset($options['style']) && $data['model'] === 'dall-e-3') {
            $data['style'] = $options['style']; // vivid 或 natural
        }

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];

        try {
            $response = $this->httpRequest($url, $data, 'POST', $headers);

            if (!isset($response['data']) || empty($response['data'])) {
                throw new \Exception('DALL-E返回数据格式错误');
            }

            $images = [];
            foreach ($response['data'] as $item) {
                $images[] = [
                    'url' => $item['url'],
                    'revised_prompt' => $item['revised_prompt'] ?? null,
                ];
            }

            return [
                'success' => true,
                'images' => $images,
            ];

        } catch (\Exception $e) {
            throw new \Exception('DALL-E图片生成失败: ' . $e->getMessage());
        }
    }

    /**
     * 编辑图片 (DALL-E)
     */
    public function editImage(string $imagePath, string $prompt, string $maskPath = null): array
    {
        $url = ($this->apiEndpoint ?: 'https://api.openai.com/v1') . '/images/edits';

        // 使用multipart/form-data
        $postFields = [
            'image' => new \CURLFile($imagePath),
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'response_format' => 'url',
        ];

        if ($maskPath) {
            $postFields['mask'] = new \CURLFile($maskPath);
        }

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
        ];

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 120,
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $response = json_decode($result, true);

            if ($httpCode !== 200 || isset($response['error'])) {
                throw new \Exception($response['error']['message'] ?? '请求失败');
            }

            $images = [];
            foreach ($response['data'] as $item) {
                $images[] = [
                    'url' => $item['url'],
                ];
            }

            return [
                'success' => true,
                'images' => $images,
            ];

        } catch (\Exception $e) {
            throw new \Exception('DALL-E图片编辑失败: ' . $e->getMessage());
        }
    }

    /**
     * 图片变体 (DALL-E)
     */
    public function createImageVariation(string $imagePath, int $n = 1, string $size = '1024x1024'): array
    {
        $url = ($this->apiEndpoint ?: 'https://api.openai.com/v1') . '/images/variations';

        $postFields = [
            'image' => new \CURLFile($imagePath),
            'n' => $n,
            'size' => $size,
            'response_format' => 'url',
        ];

        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
        ];

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postFields,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_TIMEOUT => 120,
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $response = json_decode($result, true);

            if ($httpCode !== 200 || isset($response['error'])) {
                throw new \Exception($response['error']['message'] ?? '请求失败');
            }

            $images = [];
            foreach ($response['data'] as $item) {
                $images[] = [
                    'url' => $item['url'],
                ];
            }

            return [
                'success' => true,
                'images' => $images,
            ];

        } catch (\Exception $e) {
            throw new \Exception('DALL-E图片变体生成失败: ' . $e->getMessage());
        }
    }
}
