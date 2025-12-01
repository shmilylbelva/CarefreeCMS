<?php
declare (strict_types = 1);

namespace app\model;

/**
 * AI提示词模板模型
 */
class AiPromptTemplate extends SiteModel
{
    protected $name = 'ai_prompt_templates';

    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    protected $type = [
        'sort_order' => 'integer',
        'is_system' => 'integer',
        'status' => 'integer',
        'usage_count' => 'integer',
        'variables' => 'json',
    ];

    /**
     * 获取分类列表
     */
    public static function getCategories()
    {
        return [
            '科技文章' => '科技文章',
            '产品介绍' => '产品介绍',
            '新闻报道' => '新闻报道',
            '教程指南' => '教程指南',
            '行业分析' => '行业分析',
            'SEO文章' => 'SEO文章',
            '营销文案' => '营销文案',
            '技术博客' => '技术博客',
            '电商文案' => '电商文案',
            '社交媒体' => '社交媒体',
            '问答Q&A' => '问答Q&A',
            '故事创作' => '故事创作',
            '演讲稿' => '演讲稿',
            '自定义' => '自定义',
        ];
    }

    /**
     * 增加使用次数
     */
    public function incrementUsage()
    {
        $this->usage_count++;
        $this->save();
    }

    /**
     * 搜索器：分类
     */
    public function searchCategoryAttr($query, $value)
    {
        if ($value) {
            $query->where('category', $value);
        }
    }

    /**
     * 搜索器：状态
     */
    public function searchStatusAttr($query, $value)
    {
        if ($value !== '') {
            $query->where('status', $value);
        }
    }

    /**
     * 搜索器：关键词
     */
    public function searchKeywordAttr($query, $value)
    {
        if ($value) {
            $query->where(function ($q) use ($value) {
                $q->whereLike('name', "%{$value}%")
                  ->whereOr('description', 'like', "%{$value}%");
            });
        }
    }
}
