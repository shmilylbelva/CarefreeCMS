<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 模板类型模型
 */
class TemplateType extends Model
{
    // 设置表名
    protected $table = 'template_types';

    // 设置字段类型
    protected $type = [
        'id' => 'integer',
        'sort' => 'integer',
        'is_system' => 'integer',
        'allow_multiple' => 'integer',
        'status' => 'integer',
        'params' => 'json',
        'template_vars' => 'json',
    ];

    // 自动时间戳
    protected $autoWriteTimestamp = true;

    /**
     * 获取器：格式化参数
     */
    public function getParamsAttr($value, $data)
    {
        // 处理 ThinkPHP 8 的 Json 对象
        if ($value instanceof \think\model\type\Json) {
            return $value->value();
        }
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }
        if (is_array($value)) {
            return $value;
        }
        return [];
    }

    /**
     * 获取器：格式化模板变量
     */
    public function getTemplateVarsAttr($value, $data)
    {
        // 处理 ThinkPHP 8 的 Json 对象
        if ($value instanceof \think\model\type\Json) {
            return $value->value();
        }
        if (is_string($value)) {
            return json_decode($value, true) ?: [];
        }
        if (is_array($value)) {
            return $value;
        }
        return [];
    }

    /**
     * 修改器：参数
     */
    public function setParamsAttr($value)
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return $value;
    }

    /**
     * 修改器：模板变量
     */
    public function setTemplateVarsAttr($value)
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return $value;
    }

    /**
     * 关联模板
     */
    public function templates()
    {
        return $this->hasMany(Template::class, 'template_type', 'code');
    }

    /**
     * 获取启用的模板类型列表
     */
    public static function getActiveTypes()
    {
        return self::where('status', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();
    }

    /**
     * 根据代码获取模板类型
     */
    public static function getByCode($code)
    {
        return self::where('code', $code)
            ->where('status', 1)
            ->find();
    }

    /**
     * 获取系统内置类型
     */
    public static function getSystemTypes()
    {
        return self::where('is_system', 1)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select();
    }

    /**
     * 获取用于选择器的选项
     */
    public static function getSelectOptions()
    {
        $types = self::getActiveTypes();
        $options = [];

        foreach ($types as $type) {
            $options[] = [
                'value' => $type->code,
                'label' => $type->name,
                'description' => $type->description,
                'icon' => $type->icon,
                'allow_multiple' => $type->allow_multiple
            ];
        }

        return $options;
    }

    /**
     * 检查是否可以删除
     */
    public function canDelete()
    {
        // 系统内置类型不能删除
        if ($this->is_system) {
            return false;
        }

        // 检查是否有模板在使用此类型（模板是全局共享的，需要禁用站点过滤）
        $count = Template::withoutSiteScope()->where('template_type', $this->code)->count();
        if ($count > 0) {
            return false;
        }

        return true;
    }

    /**
     * 获取文件命名示例
     */
    public function getFileNamingExamples()
    {
        $examples = [];

        switch ($this->code) {
            case 'index':
                $examples = ['index.html'];
                break;
            case 'category':
                $examples = ['category.html', 'category_news.html', 'category_product.html'];
                break;
            case 'article':
                $examples = ['article.html', 'article_news.html', 'article_blog.html'];
                break;
            case 'page':
                $examples = ['page.html', 'page_about.html', 'page_contact.html'];
                break;
            case 'topic':
                $examples = ['topic.html', 'topic_special.html'];
                break;
            default:
                $examples = [$this->file_naming];
        }

        return $examples;
    }

    /**
     * 获取参数说明（格式化）
     */
    public function getFormattedParams()
    {
        $params = $this->params ?: [];
        $formatted = [];

        foreach ($params as $key => $description) {
            $formatted[] = [
                'name' => $key,
                'description' => $description,
                'example' => $this->getParamExample($key)
            ];
        }

        return $formatted;
    }

    /**
     * 获取参数示例值
     */
    private function getParamExample($paramName)
    {
        $examples = [
            'site_id' => '1',
            'page' => '1',
            'pagesize' => '20',
            'category_id' => '5',
            'article_id' => '123',
            'tag_id' => '10',
            'topic_id' => '3',
            'page_id' => '2',
            'keyword' => '搜索词',
            'slug' => 'about-us',
            'type' => 'news',
            'url' => '/not-found-page'
        ];

        return $examples[$paramName] ?? '';
    }

    /**
     * 获取模板变量说明（格式化）
     */
    public function getFormattedTemplateVars()
    {
        $vars = $this->template_vars ?: [];
        $formatted = [];

        foreach ($vars as $key => $description) {
            $formatted[] = [
                'name' => $key,
                'description' => $description,
                'type' => $this->getVarType($key)
            ];
        }

        return $formatted;
    }

    /**
     * 获取变量类型
     */
    private function getVarType($varName)
    {
        $types = [
            'articles' => 'array',
            'article' => 'object',
            'categories' => 'array',
            'category' => 'object',
            'sliders' => 'array',
            'tags' => 'array',
            'tag' => 'object',
            'comments' => 'array',
            'pager' => 'object',
            'total' => 'integer',
            'keyword' => 'string',
            'content' => 'string',
            'breadcrumb' => 'array'
        ];

        return $types[$varName] ?? 'mixed';
    }
}