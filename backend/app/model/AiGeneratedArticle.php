<?php
declare (strict_types = 1);

namespace app\model;

/**
 * AI生成文章记录模型
 */
class AiGeneratedArticle extends SiteModel
{
    protected $name = 'ai_generated_articles';

    protected $autoWriteTimestamp = 'create_time';
    protected $createTime = 'create_time';
    protected $updateTime = false;

    protected $type = [
        'task_id' => 'integer',
        'article_id' => 'integer',
        'tokens_used' => 'integer',
    ];

    // 状态常量
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    /**
     * 关联任务
     */
    public function task()
    {
        return $this->belongsTo(AiArticleTask::class, 'task_id', 'id');
    }

    /**
     * 关联文章
     */
    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id', 'id');
    }

    /**
     * 创建为文章
     * @param array $data 额外的文章数据
     * @return Article
     */
    public function createAsArticle($data = [])
    {
        if ($this->article_id) {
            throw new \Exception('该记录已关联文章');
        }

        if ($this->status !== self::STATUS_SUCCESS) {
            throw new \Exception('只有成功生成的内容才能创建为文章');
        }

        // 准备文章数据
        $articleData = array_merge([
            'title' => $this->generated_title,
            'content' => $this->generated_content,
            'summary' => mb_substr(strip_tags($this->generated_content), 0, 200),
        ], $data);

        // 创建文章
        $article = Article::create($articleData);

        // 更新关联
        $this->article_id = $article->id;
        $this->save();

        return $article;
    }

    /**
     * 搜索器：任务ID
     */
    public function searchTaskIdAttr($query, $value)
    {
        if ($value) {
            $query->where('task_id', $value);
        }
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
}
