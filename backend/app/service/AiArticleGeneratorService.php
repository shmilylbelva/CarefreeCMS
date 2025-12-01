<?php
declare (strict_types = 1);

namespace app\service;

use app\model\AiArticleTask;
use app\model\AiGeneratedArticle;
use app\model\Article;

/**
 * AI文章生成服务
 */
class AiArticleGeneratorService
{
    /**
     * 处理任务
     */
    public function processTask(AiArticleTask $task)
    {
        try {
            // 获取AI配置
            $aiConfig = $task->aiConfig;
            if (!$aiConfig) {
                throw new \Exception('AI配置不存在');
            }

            // 创建AI服务实例
            $aiService = AiService::createFromConfig($aiConfig);

            // 获取任务设置
            $settings = $task->settings ?? [];
            $autoPublish = $settings['auto_publish'] ?? false;

            // 解析所有主题（topic字段可能包含多个主题，用换行符分隔）
            $topics = $this->parseTopics($task->topic);

            if (empty($topics)) {
                throw new \Exception('主题不能为空');
            }

            // 判断是否使用了提示词模板
            $useTemplate = !empty($task->prompt_template_id);

            // 计算需要生成的数量
            $remaining = $task->total_count - $task->generated_count;

            // 确保不超过主题数量
            if ($remaining > count($topics)) {
                $remaining = count($topics);
            }

            // 计算起始索引（已生成的数量即为下一个要生成的主题索引）
            $startIndex = $task->generated_count;

            // 生成文章
            for ($i = 0; $i < $remaining; $i++) {
                // 检查任务是否被停止
                $task->refresh();
                if ($task->status === AiArticleTask::STATUS_STOPPED) {
                    break;
                }

                // 获取当前主题
                $currentTopicIndex = $startIndex + $i;
                $currentTopic = $topics[$currentTopicIndex] ?? $topics[0]; // 防止索引越界

                try {
                    // 根据是否使用模板，构建提示词
                    if ($useTemplate) {
                        // 使用模板：需要为当前主题进行变量替换
                        $prompt = $this->buildPromptFromTemplate($task, $currentTopic);
                        $options = ['use_raw_prompt' => true];
                    } else {
                        // 不使用模板：直接使用主题，传递长度和风格参数
                        $prompt = $currentTopic;
                        $options = [
                            'length' => $settings['length'] ?? 'medium',
                            'style' => $settings['style'] ?? 'professional',
                        ];
                    }

                    // 调用AI生成
                    $result = $aiService->generateArticle($prompt, $options);

                    // 检测并转换Markdown为HTML
                    if (!empty($result['content'])) {
                        $result['content'] = $this->convertMarkdownToHtml($result['content']);
                    }

                    // 保存生成记录
                    $generatedArticle = $this->saveGeneratedArticle($task, $currentTopic, $prompt, $result);

                    // 如果设置了自动发布，创建文章
                    if ($autoPublish) {
                        $this->publishArticle($generatedArticle, $task, $settings);
                    }

                    // 更新任务计数
                    $task->updateCounts(true);

                } catch (\Exception $e) {
                    // 记录失败
                    $this->saveFailedArticle($task, $currentTopic, $prompt ?? $currentTopic, $e->getMessage());
                    $task->updateCounts(false);

                    // 如果连续失败次数过多，停止任务
                    if ($task->failed_count >= 5) {
                        $task->markAsFailed('连续失败次数过多，任务已停止');
                        break;
                    }
                }

                // 添加延迟，避免请求过快
                if ($i < $remaining - 1) {
                    sleep(2);
                }
            }

            return true;

        } catch (\Exception $e) {
            $task->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * 解析主题（支持多行）
     */
    private function parseTopics($topicString)
    {
        // 按换行符分隔
        $topics = preg_split('/\r\n|\r|\n/', trim($topicString));

        // 去除空行和首尾空格
        $topics = array_map('trim', $topics);
        $topics = array_filter($topics, function($topic) {
            return !empty($topic);
        });

        return array_values($topics);
    }

    /**
     * 从模板构建提示词（为特定主题进行变量替换）
     */
    private function buildPromptFromTemplate(AiArticleTask $task, $currentTopic)
    {
        $template = $task->promptTemplate;
        if (!$template) {
            return $currentTopic;
        }

        $prompt = $template->prompt;
        $userVariables = $task->prompt_variables ?? [];

        // 将当前主题作为topic变量
        $userVariables['topic'] = $currentTopic;

        // 获取模板定义的变量及其默认值
        $templateVariables = [];
        if ($template->variables) {
            try {
                $varDefs = is_array($template->variables)
                    ? $template->variables
                    : json_decode($template->variables, true);

                if (is_array($varDefs)) {
                    foreach ($varDefs as $varDef) {
                        $varName = $varDef['name'];
                        if (!isset($userVariables[$varName]) || $userVariables[$varName] === '') {
                            if (isset($varDef['default'])) {
                                $templateVariables[$varName] = $varDef['default'];
                            }
                        } else {
                            $templateVariables[$varName] = $userVariables[$varName];
                        }
                    }
                }
            } catch (\Exception $e) {
                // 解析失败，使用用户提供的变量
                $templateVariables = $userVariables;
            }
        } else {
            $templateVariables = $userVariables;
        }

        // 确保topic始终存在
        $templateVariables['topic'] = $currentTopic;

        // 替换变量
        foreach ($templateVariables as $key => $value) {
            $stringValue = $value === null ? '' : (string)$value;
            $prompt = str_replace('{' . $key . '}', $stringValue, $prompt);
        }

        return $prompt;
    }

    /**
     * 保存生成的文章记录
     */
    private function saveGeneratedArticle(AiArticleTask $task, $currentTopic, $requestPrompt, $result)
    {
        return AiGeneratedArticle::create([
            'task_id' => $task->id,
            'prompt' => $currentTopic, // 当前主题
            'request_prompt' => $requestPrompt, // 实际发送的提示词（变量替换后或构建后）
            'generated_title' => $result['title'] ?? '',
            'generated_content' => $result['content'] ?? '',
            'raw_response' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), // 原始AI响应
            'status' => AiGeneratedArticle::STATUS_SUCCESS,
            'tokens_used' => $result['tokens'] ?? 0,
        ]);
    }

    /**
     * 保存失败记录
     */
    private function saveFailedArticle(AiArticleTask $task, $currentTopic, $requestPrompt, $errorMessage)
    {
        return AiGeneratedArticle::create([
            'task_id' => $task->id,
            'prompt' => $currentTopic, // 当前主题
            'request_prompt' => $requestPrompt, // 实际发送的提示词
            'status' => AiGeneratedArticle::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * 发布文章
     */
    private function publishArticle(AiGeneratedArticle $generatedArticle, AiArticleTask $task, $settings)
    {
        // 获取当前登录用户ID，如果没有则使用默认管理员ID
        $userId = 1; // 默认使用ID为1的管理员
        try {
            // 尝试从JWT获取当前用户ID
            $request = request();
            if ($request && $request->user) {
                $userId = $request->user->id ?? 1;
            }
        } catch (\Exception $e) {
            // 如果获取失败，使用默认值
        }

        // 准备文章数据
        $articleData = [
            'category_id' => $task->category_id,
            'user_id' => $userId, // 设置作者ID
            'title' => $generatedArticle->generated_title,
            'content' => $generatedArticle->generated_content,
            'summary' => mb_substr(strip_tags($generatedArticle->generated_content), 0, 200),
            'status' => $settings['publish_status'] ?? 0, // 0草稿 1已发布
            'author' => 'AI生成', // 标记为AI生成
            'source' => 'AI文章生成器',
        ];

        // 如果生成了SEO关键词，提取并设置
        if (!empty($generatedArticle->generated_content)) {
            // 尝试从内容中提取关键词（这里简化处理）
            $articleData['seo_keywords'] = $generatedArticle->generated_title;
        }

        // 创建文章
        $article = $generatedArticle->createAsArticle($articleData);

        return $article;
    }

    /**
     * 生成单篇文章（供手动调用）
     */
    public function generateSingle($aiConfigId, $topic, $categoryId = null, $options = [])
    {
        // 获取AI配置
        $aiConfig = \app\model\AiConfig::find($aiConfigId);
        if (!$aiConfig) {
            throw new \Exception('AI配置不存在');
        }

        // 创建AI服务实例
        $aiService = AiService::createFromConfig($aiConfig);

        // 生成文章
        $result = $aiService->generateArticle($topic, $options);

        // 检测并转换Markdown为HTML
        if (!empty($result['content'])) {
            $result['content'] = $this->convertMarkdownToHtml($result['content']);
        }

        // 创建文章
        $articleData = [
            'title' => $result['title'],
            'content' => $result['content'],
            'summary' => $result['summary'] ?? mb_substr(strip_tags($result['content']), 0, 200),
            'seo_keywords' => $result['keywords'] ?? '',
            'status' => $options['publish_status'] ?? 0,
        ];

        if ($categoryId) {
            $articleData['category_id'] = $categoryId;
        }

        $article = Article::create($articleData);

        return [
            'article' => $article,
            'tokens_used' => $result['tokens'],
        ];
    }

    /**
     * 检测内容是否为Markdown格式并转换为HTML
     *
     * @param string $content 原始内容
     * @return string 处理后的内容（如果是Markdown则转换为HTML，否则返回原内容）
     */
    private function convertMarkdownToHtml($content)
    {
        if (empty($content)) {
            return $content;
        }

        // 检测是否为Markdown格式的特征
        $markdownPatterns = [
            '/^#{1,6}\s+/m',           // 标题 # ## ###
            '/\*\*.*?\*\*/s',          // 粗体 **text**
            '/__.*?__/s',              // 粗体 __text__
            '/\*.*?\*/s',              // 斜体 *text*
            '/_.*?_/s',                // 斜体 _text_
            '/```[\s\S]*?```/m',       // 代码块
            '/`[^`]+`/',               // 行内代码
            '/^\s*[-*+]\s+/m',         // 无序列表
            '/^\s*\d+\.\s+/m',         // 有序列表
            '/\[.*?\]\(.*?\)/',        // 链接
            '/!\[.*?\]\(.*?\)/',       // 图片
            '/^\s*>\s+/m',             // 引用
        ];

        $isMarkdown = false;
        foreach ($markdownPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $isMarkdown = true;
                break;
            }
        }

        // 如果不是Markdown格式，直接返回原内容
        if (!$isMarkdown) {
            return $content;
        }

        // 使用Parsedown转换Markdown为HTML
        try {
            $parsedown = new \Parsedown();
            $parsedown->setSafeMode(true); // 启用安全模式，防止XSS
            $html = $parsedown->text($content);

            // 记录日志（如果启用调试）
            if (env('app_debug', false)) {
                trace('批量生成: 检测到Markdown格式，已转换为HTML，原始长度: ' . mb_strlen($content) . '，转换后长度: ' . mb_strlen($html), 'info');
            }

            return $html;
        } catch (\Exception $e) {
            trace('批量生成: Markdown转HTML失败: ' . $e->getMessage(), 'error');
            return $content; // 转换失败则返回原内容
        }
    }
}
