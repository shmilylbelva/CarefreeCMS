<?php
declare (strict_types = 1);

namespace app\model;

/**
 * AI图片提示词模板模型
 */
class AiImagePromptTemplate extends SiteModel
{
    protected $name = 'ai_image_prompt_templates';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';

    protected $type = [
        'site_id' => 'integer',
        'default_width' => 'integer',
        'default_height' => 'integer',
        'usage_count' => 'integer',
        'is_public' => 'integer',
        'is_builtin' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * 获取变量定义（JSON解析）
     */
    public function getVariablesAttr($value, $data)
    {
        if (isset($data['variables'])) {
            return json_decode($data['variables'], true);
        }
        return [];
    }

    /**
     * 增加使用次数
     */
    public function incrementUsageCount()
    {
        return $this->inc('usage_count')->update();
    }

    /**
     * 渲染提示词（替换变量）
     */
    public function renderPrompt(array $variables = []): string
    {
        $prompt = $this->prompt_template;

        foreach ($variables as $key => $value) {
            $prompt = str_replace('{{' . $key . '}}', $value, $prompt);
        }

        return $prompt;
    }

    /**
     * 获取按分类分组的模板
     */
    public static function getByCategory(string $category = null)
    {
        $query = self::where('is_public', 1);

        if ($category) {
            $query->where('category', $category);
        }

        return $query->order('sort_order', 'asc')
            ->order('usage_count', 'desc')
            ->select();
    }

    /**
     * 获取热门模板
     */
    public static function getPopular(int $limit = 10)
    {
        return self::where('is_public', 1)
            ->where('usage_count', '>', 0)
            ->order('usage_count', 'desc')
            ->limit($limit)
            ->select();
    }
}
