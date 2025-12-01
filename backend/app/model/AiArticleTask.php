<?php
declare (strict_types = 1);

namespace app\model;

/**
 * AI文章生成任务模型
 */
class AiArticleTask extends SiteModel
{
    protected $name = 'ai_article_tasks';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'category_id' => 'integer',
        'ai_config_id' => 'integer',
        'prompt_template_id' => 'integer',
        'total_count' => 'integer',
        'generated_count' => 'integer',
        'success_count' => 'integer',
        'failed_count' => 'integer',
        'settings' => 'json',
        'prompt_variables' => 'json',
    ];

    // 任务状态常量
    const STATUS_PENDING = 'pending';       // 待处理
    const STATUS_PROCESSING = 'processing'; // 处理中
    const STATUS_COMPLETED = 'completed';   // 已完成
    const STATUS_FAILED = 'failed';         // 失败
    const STATUS_STOPPED = 'stopped';       // 已停止

    /**
     * 关联AI配置
     */
    public function aiConfig()
    {
        return $this->belongsTo(AiConfig::class, 'ai_config_id', 'id');
    }

    /**
     * 关联分类
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    /**
     * 关联生成的文章记录
     */
    public function generatedArticles()
    {
        return $this->hasMany(AiGeneratedArticle::class, 'task_id', 'id');
    }

    /**
     * 关联提示词模板
     */
    public function promptTemplate()
    {
        return $this->belongsTo(AiPromptTemplate::class, 'prompt_template_id', 'id');
    }

    /**
     * 获取最终的提示词（变量替换后）
     */
    public function getFinalPrompt()
    {
        // 如果有模板，使用模板进行变量替换
        if ($this->prompt_template_id && $this->promptTemplate) {
            $prompt = $this->promptTemplate->prompt;
            $userVariables = $this->prompt_variables ?? [];

            // 将 topic 也加入变量中（topic 是必填的核心内容）
            $userVariables['topic'] = $this->topic;

            // 获取模板定义的变量及其默认值
            $templateVariables = [];
            if ($this->promptTemplate->variables) {
                try {
                    // variables 可能已经是数组（ThinkPHP自动转换）或者是字符串
                    $varDefs = is_array($this->promptTemplate->variables)
                        ? $this->promptTemplate->variables
                        : json_decode($this->promptTemplate->variables, true);

                    if (is_array($varDefs)) {
                        foreach ($varDefs as $varDef) {
                            // 如果用户没有填写且有默认值，使用默认值
                            $varName = $varDef['name'];
                            if (!isset($userVariables[$varName]) || $userVariables[$varName] === '') {
                                if (isset($varDef['default'])) {
                                    $templateVariables[$varName] = $varDef['default'];
                                }
                            } else {
                                $templateVariables[$varName] = $userVariables[$varName];
                            }
                        }
                    } else {
                        $templateVariables = $userVariables;
                    }
                } catch (\Exception $e) {
                    // 解析失败，使用用户提供的变量
                    $templateVariables = $userVariables;
                }
            } else {
                $templateVariables = $userVariables;
            }

            // 确保 topic 始终存在
            $templateVariables['topic'] = $this->topic;

            // 替换变量（确保所有值都转换为字符串）
            foreach ($templateVariables as $key => $value) {
                // 将值转换为字符串，处理各种类型
                $stringValue = $value === null ? '' : (string)$value;
                $prompt = str_replace('{' . $key . '}', $stringValue, $prompt);
            }

            return $prompt;
        }

        // 否则返回原始 topic
        return $this->topic;
    }

    /**
     * 获取所有任务状态
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING => '待处理',
            self::STATUS_PROCESSING => '处理中',
            self::STATUS_COMPLETED => '已完成',
            self::STATUS_FAILED => '失败',
            self::STATUS_STOPPED => '已停止',
        ];
    }

    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $statuses = self::getStatuses();
        return $statuses[$data['status']] ?? '未知';
    }

    /**
     * 获取进度百分比
     */
    public function getProgressAttr($value, $data)
    {
        if ($data['total_count'] <= 0) {
            return 0;
        }
        return round(($data['generated_count'] / $data['total_count']) * 100, 2);
    }

    /**
     * 开始任务
     */
    public function start()
    {
        if ($this->status !== self::STATUS_PENDING && $this->status !== self::STATUS_STOPPED) {
            throw new \Exception('只有待处理或已停止的任务可以启动');
        }

        $this->status = self::STATUS_PROCESSING;
        $this->started_at = date('Y-m-d H:i:s');
        $this->save();

        return true;
    }

    /**
     * 停止任务
     */
    public function stop()
    {
        if ($this->status !== self::STATUS_PROCESSING) {
            throw new \Exception('只有处理中的任务可以停止');
        }

        $this->status = self::STATUS_STOPPED;
        $this->save();

        return true;
    }

    /**
     * 完成任务
     */
    public function complete()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = date('Y-m-d H:i:s');
        $this->save();

        return true;
    }

    /**
     * 标记为失败
     */
    public function markAsFailed($errorMessage = '')
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->completed_at = date('Y-m-d H:i:s');
        $this->save();

        return true;
    }

    /**
     * 更新生成计数
     */
    public function updateCounts($success = true)
    {
        $this->generated_count++;

        if ($success) {
            $this->success_count++;
        } else {
            $this->failed_count++;
        }

        // 检查是否完成
        if ($this->generated_count >= $this->total_count) {
            $this->complete();
        } else {
            $this->save();
        }

        return true;
    }

    /**
     * 搜索器：状态
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value) {
            $query->where('status', $value);
        }
    }

    /**
     * 搜索器：分类
     */
    public function searchCategoryIdAttr($query, $value)
    {
        if ($value) {
            $query->where('category_id', $value);
        }
    }
}
