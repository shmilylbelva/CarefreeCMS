<?php
declare (strict_types = 1);

namespace app\model;

/**
 * AI图片生成任务模型
 */
class AiImageTask extends SiteModel
{
    protected $name = 'ai_image_tasks';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'site_id' => 'integer',
        'user_id' => 'integer',
        'ai_model_id' => 'integer',
        'template_id' => 'integer',
        'image_count' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'progress' => 'integer',
        'generated_count' => 'integer',
        'cost_tokens' => 'integer',
        'cost_amount' => 'float',
    ];

    // 状态常量
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * 关联AI模型
     */
    public function aiModel()
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id', 'id');
    }

    /**
     * 关联用户
     */
    public function user()
    {
        return $this->belongsTo(AdminUser::class, 'user_id', 'id');
    }

    /**
     * 关联提示词模板
     */
    public function template()
    {
        return $this->belongsTo(AiImagePromptTemplate::class, 'template_id', 'id');
    }

    /**
     * 关联生成的媒体（通过source_id）
     */
    public function generatedMedia()
    {
        return MediaLibrary::where('source', MediaLibrary::SOURCE_AI_GENERATE)
            ->where('source_id', $this->id)
            ->select();
    }

    /**
     * 更新进度
     */
    public function updateProgress(int $progress, int $generatedCount = null)
    {
        $this->progress = $progress;

        if ($generatedCount !== null) {
            $this->generated_count = $generatedCount;
        }

        return $this->save();
    }

    /**
     * 标记为处理中
     */
    public function markAsProcessing()
    {
        $this->status = self::STATUS_PROCESSING;
        $this->started_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * 标记为完成
     */
    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->progress = 100;
        $this->completed_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * 标记为失败
     */
    public function markAsFailed(string $errorMessage)
    {
        $this->status = self::STATUS_FAILED;
        $this->error_message = $errorMessage;
        $this->completed_at = date('Y-m-d H:i:s');
        return $this->save();
    }

    /**
     * 获取模板变量（JSON解析）
     */
    public function getTemplateVariablesAttr($value, $data)
    {
        if (isset($data['template_variables'])) {
            return json_decode($data['template_variables'], true);
        }
        return [];
    }

    /**
     * 获取额外参数（JSON解析）
     */
    public function getExtraParamsAttr($value, $data)
    {
        if (isset($data['extra_params'])) {
            return json_decode($data['extra_params'], true);
        }
        return [];
    }
}
