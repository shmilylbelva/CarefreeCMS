<?php
namespace app\service;

use app\model\TemplatePackage;
use app\model\Template;
use think\facade\Db;
use think\Exception;

/**
 * 模板包管理服务
 * 处理模板包的创建、安装、导入导出等完整流程
 */
class TemplatePackageService
{
    /**
     * 模板目录路径
     */
    protected $templateBasePath;

    public function __construct()
    {
        $this->templateBasePath = app()->getRootPath() . 'templates' . DIRECTORY_SEPARATOR;
    }

    /**
     * 创建完整的模板包
     * 包括：数据库记录、目录结构、配置文件、模板文件
     *
     * @param array $packageData 模板包数据
     * @param array $templates 模板列表
     * @return TemplatePackage
     */
    public function createPackage(array $packageData, array $templates = [])
    {
        Db::startTrans();
        try {
            // 1. 创建模板包数据库记录
            $package = TemplatePackage::create($packageData);

            // 2. 创建模板包目录
            $packageDir = $this->createPackageDirectory($packageData['code']);

            // 3. 生成模板配置文件
            $this->createTemplateConfig($packageDir, $packageData, $templates);

            // 4. 创建模板文件记录和文件
            $this->createTemplateFiles($package->id, $packageData['code'], $templates);

            // 5. 创建必要的资源目录
            $this->createResourceDirectories($packageDir);

            // 6. 生成README文件
            $this->createReadmeFile($packageDir, $packageData);

            Db::commit();
            return $package;

        } catch (\Exception $e) {
            Db::rollback();
            // 清理已创建的目录
            if (isset($packageDir) && is_dir($packageDir)) {
                $this->removeDirectory($packageDir);
            }
            throw new Exception('创建模板包失败：' . $e->getMessage());
        }
    }

    /**
     * 安装模板包
     * 从现有文件安装模板包到系统
     *
     * @param string $packageCode 模板包代码
     * @return bool
     */
    public function installPackage(string $packageCode)
    {
        $packageDir = $this->templateBasePath . $packageCode;

        if (!is_dir($packageDir)) {
            throw new Exception("模板包目录不存在: {$packageCode}");
        }

        // 读取配置文件
        $configFile = $packageDir . DIRECTORY_SEPARATOR . 'template.json';
        if (!file_exists($configFile)) {
            throw new Exception("模板配置文件不存在: template.json");
        }

        $config = json_decode(file_get_contents($configFile), true);

        Db::startTrans();
        try {
            // 1. 创建模板包记录
            $packageData = [
                'name' => $config['name'] ?? $packageCode,
                'code' => $packageCode,
                'description' => $config['description'] ?? '',
                'version' => $config['version'] ?? '1.0.0',
                'author' => $config['author'] ?? '',
                'author_url' => $config['author_url'] ?? '',
                'preview_image' => $config['screenshot'] ?? '',
                'is_system' => 0,
                'is_global' => 1,
                'status' => 1,
                'config_schema' => isset($config['settings']) ? json_encode($config['settings']) : null,
                'default_config' => isset($config['seo']) ? json_encode($config['seo']) : null,
            ];

            $package = TemplatePackage::create($packageData);

            // 2. 创建模板文件记录
            if (isset($config['templates'])) {
                foreach ($config['templates'] as $key => $template) {
                    $templateData = [
                        'site_id' => 0,
                        'package_id' => $package->id,
                        'name' => $template['name'],
                        'template_key' => $packageCode . '_' . $key,
                        'template_type' => $key,
                        'description' => $template['description'] ?? '',
                        'template_path' => $packageCode . '/' . $template['file'],
                        'is_default' => 0,
                        'status' => 1
                    ];

                    Template::create($templateData);
                }
            }

            Db::commit();
            return true;

        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception('安装模板包失败：' . $e->getMessage());
        }
    }

    /**
     * 创建模板包目录
     *
     * @param string $packageCode 模板包代码
     * @return string 目录路径
     */
    protected function createPackageDirectory(string $packageCode)
    {
        $packageDir = $this->templateBasePath . $packageCode;

        if (is_dir($packageDir)) {
            throw new Exception("模板包目录已存在: {$packageCode}");
        }

        if (!mkdir($packageDir, 0755, true)) {
            throw new Exception("无法创建模板包目录: {$packageDir}");
        }

        return $packageDir;
    }

    /**
     * 创建资源目录
     *
     * @param string $packageDir 模板包目录
     */
    protected function createResourceDirectories(string $packageDir)
    {
        $directories = ['css', 'js', 'images', 'fonts'];

        foreach ($directories as $dir) {
            $dirPath = $packageDir . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
        }
    }

    /**
     * 创建模板配置文件
     *
     * @param string $packageDir 模板包目录
     * @param array $packageData 模板包数据
     * @param array $templates 模板列表
     */
    protected function createTemplateConfig(string $packageDir, array $packageData, array $templates)
    {
        $config = [
            'name' => $packageData['name'],
            'version' => $packageData['version'] ?? '1.0.0',
            'author' => $packageData['author'] ?? '',
            'description' => $packageData['description'] ?? '',
            'screenshot' => $packageData['preview_image'] ?? '',
            'templates' => [],
            'assets' => [
                'css' => ['css/style.css'],
                'js' => ['js/main.js'],
                'images' => ['images/']
            ],
            'settings' => $packageData['config_schema'] ?? [],
            'seo' => $packageData['default_config'] ?? []
        ];

        // 添加模板信息
        foreach ($templates as $template) {
            $config['templates'][$template['type']] = [
                'file' => $template['file'],
                'name' => $template['name'],
                'description' => $template['description'] ?? ''
            ];
        }

        $configFile = $packageDir . DIRECTORY_SEPARATOR . 'template.json';
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * 创建模板文件和数据库记录
     *
     * @param int $packageId 模板包ID
     * @param string $packageCode 模板包代码
     * @param array $templates 模板列表
     */
    protected function createTemplateFiles(int $packageId, string $packageCode, array $templates)
    {
        $packageDir = $this->templateBasePath . $packageCode;

        foreach ($templates as $template) {
            // 创建数据库记录
            $templateData = [
                'site_id' => 0,
                'package_id' => $packageId,
                'name' => $template['name'],
                'template_key' => $packageCode . '_' . $template['type'],
                'template_type' => $template['type'],
                'description' => $template['description'] ?? '',
                'template_path' => $packageCode . '/' . $template['file'],
                'is_default' => 0,
                'status' => 1
            ];

            Template::create($templateData);

            // 创建模板文件
            $templateFile = $packageDir . DIRECTORY_SEPARATOR . $template['file'];
            $templateContent = $this->generateTemplateContent($template['type'], $packageCode);
            file_put_contents($templateFile, $templateContent);
        }

        // 创建CSS文件
        $cssDir = $packageDir . DIRECTORY_SEPARATOR . 'css';
        if (!is_dir($cssDir)) {
            mkdir($cssDir, 0755, true);
        }
        $cssFile = $cssDir . DIRECTORY_SEPARATOR . 'style.css';
        file_put_contents($cssFile, $this->getDefaultCss());

        // 创建JS文件
        $jsDir = $packageDir . DIRECTORY_SEPARATOR . 'js';
        if (!is_dir($jsDir)) {
            mkdir($jsDir, 0755, true);
        }
        $jsFile = $jsDir . DIRECTORY_SEPARATOR . 'main.js';
        file_put_contents($jsFile, $this->getDefaultJs());
    }

    /**
     * 生成模板内容
     *
     * @param string $type 模板类型
     * @param string $packageCode 模板包代码
     * @return string
     */
    protected function generateTemplateContent(string $type, string $packageCode)
    {
        $templates = [
            'layout' => $this->getLayoutTemplate($packageCode),
            'index' => $this->getIndexTemplate($packageCode),
            'category' => $this->getCategoryTemplate($packageCode),
            'article' => $this->getArticleTemplate($packageCode),
            'page' => $this->getPageTemplate($packageCode),
            'search' => $this->getSearchTemplate($packageCode),
            'tag' => $this->getTagTemplate($packageCode)
        ];

        return $templates[$type] ?? $this->getDefaultTemplate($type, $packageCode);
    }

    /**
     * 获取布局模板
     */
    protected function getLayoutTemplate($packageCode)
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}{{ site_name }}{% endblock %}</title>
    <meta name="keywords" content="{% block keywords %}{{ site_keywords }}{% endblock %}">
    <meta name="description" content="{% block description %}{{ site_description }}{% endblock %}">
    <link rel="stylesheet" href="{{ base_url }}/templates/{$packageCode}/css/style.css">
    {% block extra_css %}{% endblock %}
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="{{ base_url }}/">{{ site_name }}</a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="{{ base_url }}/">首页</a></li>
                    {% for category in categories %}
                    <li><a href="{{ base_url }}/category/{{ category.slug }}.html">{{ category.name }}</a></li>
                    {% endfor %}
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            {% block content %}{% endblock %}
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; {{ "now"|date("Y") }} {{ site_name }}. All rights reserved.</p>
        </div>
    </footer>

    <script src="{{ base_url }}/templates/{$packageCode}/js/main.js"></script>
    {% block extra_js %}{% endblock %}
</body>
</html>
HTML;
    }

    /**
     * 获取首页模板
     */
    protected function getIndexTemplate($packageCode)
    {
        return <<<HTML
{% extends "{$packageCode}/layout.html" %}

{% block title %}{{ site_name }} - 首页{% endblock %}

{% block content %}
<div class="home-content">
    <h1>欢迎访问{{ site_name }}</h1>

    <div class="article-list">
        {% for article in articles %}
        <article class="article-item">
            <h2><a href="{{ base_url }}/article/{{ article.id }}.html">{{ article.title }}</a></h2>
            <div class="article-meta">
                <span>发布时间：{{ article.created_at|date('Y-m-d') }}</span>
                <span>分类：{{ article.category.name }}</span>
            </div>
            <div class="article-summary">
                {{ article.summary|default(article.content|striptags|truncate(200)) }}
            </div>
        </article>
        {% endfor %}
    </div>
</div>
{% endblock %}
HTML;
    }

    /**
     * 获取分类模板
     */
    protected function getCategoryTemplate($packageCode)
    {
        return <<<HTML
{% extends "{$packageCode}/layout.html" %}

{% block title %}{{ category.name }} - {{ site_name }}{% endblock %}

{% block content %}
<div class="category-content">
    <h1>{{ category.name }}</h1>

    <div class="article-list">
        {% for article in articles %}
        <article class="article-item">
            <h2><a href="{{ base_url }}/article/{{ article.id }}.html">{{ article.title }}</a></h2>
            <div class="article-meta">
                <span>{{ article.created_at|date('Y-m-d') }}</span>
            </div>
            <div class="article-summary">
                {{ article.summary|default(article.content|striptags|truncate(200)) }}
            </div>
        </article>
        {% endfor %}
    </div>

    {% if total_pages > 1 %}
    <div class="pagination">
        <!-- 分页代码 -->
    </div>
    {% endif %}
</div>
{% endblock %}
HTML;
    }

    /**
     * 获取文章模板
     */
    protected function getArticleTemplate($packageCode)
    {
        return <<<HTML
{% extends "{$packageCode}/layout.html" %}

{% block title %}{{ article.title }} - {{ site_name }}{% endblock %}

{% block content %}
<article class="article-detail">
    <header class="article-header">
        <h1>{{ article.title }}</h1>
        <div class="article-meta">
            <span>发布时间：{{ article.created_at|date('Y-m-d H:i:s') }}</span>
            <span>分类：{{ article.category.name }}</span>
            <span>浏览：{{ article.views }} 次</span>
        </div>
    </header>

    <div class="article-content">
        {{ article.content|raw }}
    </div>

    {% if article.tags %}
    <div class="article-tags">
        标签：
        {% for tag in article.tags %}
        <a href="{{ base_url }}/tag/{{ tag.slug }}.html">{{ tag.name }}</a>
        {% endfor %}
    </div>
    {% endif %}
</article>
{% endblock %}
HTML;
    }

    /**
     * 获取单页模板
     */
    protected function getPageTemplate($packageCode)
    {
        return <<<HTML
{% extends "{$packageCode}/layout.html" %}

{% block title %}{{ page.title }} - {{ site_name }}{% endblock %}

{% block content %}
<div class="page-content">
    <h1>{{ page.title }}</h1>
    <div class="page-body">
        {{ page.content|raw }}
    </div>
</div>
{% endblock %}
HTML;
    }

    /**
     * 获取搜索模板
     */
    protected function getSearchTemplate($packageCode)
    {
        return <<<HTML
{% extends "{$packageCode}/layout.html" %}

{% block title %}搜索：{{ keyword }} - {{ site_name }}{% endblock %}

{% block content %}
<div class="search-content">
    <h1>搜索结果</h1>
    <p>关键词："{{ keyword }}"，共找到 {{ total }} 个结果</p>

    <div class="search-results">
        {% for article in articles %}
        <div class="search-item">
            <h2><a href="{{ base_url }}/article/{{ article.id }}.html">{{ article.title }}</a></h2>
            <p>{{ article.summary|default(article.content|striptags|truncate(200)) }}</p>
        </div>
        {% endfor %}
    </div>
</div>
{% endblock %}
HTML;
    }

    /**
     * 获取标签模板
     */
    protected function getTagTemplate($packageCode)
    {
        return <<<HTML
{% extends "{$packageCode}/layout.html" %}

{% block title %}标签：{{ tag.name }} - {{ site_name }}{% endblock %}

{% block content %}
<div class="tag-content">
    <h1>标签：{{ tag.name }}</h1>

    <div class="article-list">
        {% for article in articles %}
        <article class="article-item">
            <h2><a href="{{ base_url }}/article/{{ article.id }}.html">{{ article.title }}</a></h2>
            <div class="article-summary">
                {{ article.summary|default(article.content|striptags|truncate(200)) }}
            </div>
        </article>
        {% endfor %}
    </div>
</div>
{% endblock %}
HTML;
    }

    /**
     * 获取默认模板
     */
    protected function getDefaultTemplate($type, $packageCode)
    {
        return <<<HTML
{% extends "{$packageCode}/layout.html" %}

{% block title %}{{ site_name }}{% endblock %}

{% block content %}
<div class="content">
    <h1>{$type} 模板</h1>
    <p>这是 {$type} 模板的默认内容。</p>
</div>
{% endblock %}
HTML;
    }

    /**
     * 获取默认CSS
     */
    protected function getDefaultCss()
    {
        return <<<CSS
/* 基础样式 */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
    line-height: 1.6;
    color: #333;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* 头部样式 */
.site-header {
    background: #fff;
    border-bottom: 1px solid #eee;
    padding: 20px 0;
}

.logo {
    display: inline-block;
    font-size: 24px;
    font-weight: bold;
}

.main-nav {
    float: right;
}

.main-nav ul {
    list-style: none;
}

.main-nav li {
    display: inline-block;
    margin-left: 20px;
}

/* 内容区域 */
.main-content {
    padding: 40px 0;
    min-height: 400px;
}

/* 底部样式 */
.site-footer {
    background: #333;
    color: #fff;
    padding: 20px 0;
    text-align: center;
}

/* 文章列表 */
.article-item {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.article-item h2 {
    margin-bottom: 10px;
}

.article-meta {
    color: #999;
    font-size: 14px;
    margin-bottom: 10px;
}

.article-meta span {
    margin-right: 15px;
}

/* 响应式 */
@media (max-width: 768px) {
    .main-nav {
        float: none;
        margin-top: 20px;
    }

    .main-nav li {
        display: block;
        margin: 10px 0;
    }
}
CSS;
    }

    /**
     * 获取默认JS
     */
    protected function getDefaultJs()
    {
        return <<<JS
// 默认JavaScript代码
document.addEventListener('DOMContentLoaded', function() {
    console.log('模板包JS已加载');

    // 在这里添加您的JavaScript代码
});
JS;
    }

    /**
     * 创建README文件
     */
    protected function createReadmeFile($packageDir, $packageData)
    {
        $content = <<<MD
# {$packageData['name']}

## 简介
{$packageData['description']}

## 版本
{$packageData['version']}

## 作者
{$packageData['author']}

## 安装说明
1. 将此模板包放置在 templates 目录下
2. 在后台管理系统中激活此模板包
3. 为站点选择使用此模板包

## 模板文件说明
- layout.html - 基础布局模板
- index.html - 首页模板
- category.html - 分类页模板
- article.html - 文章页模板
- page.html - 单页模板
- search.html - 搜索页模板
- tag.html - 标签页模板

## 目录结构
```
{$packageData['code']}/
├── template.json    # 配置文件
├── layout.html      # 布局模板
├── index.html       # 首页
├── category.html    # 分类页
├── article.html     # 文章页
├── page.html        # 单页
├── search.html      # 搜索页
├── tag.html         # 标签页
├── css/            # 样式文件
│   └── style.css
├── js/             # JavaScript文件
│   └── main.js
└── images/         # 图片资源
```

## 更新日志
- {$packageData['version']} - 初始版本
MD;

        file_put_contents($packageDir . DIRECTORY_SEPARATOR . 'README.md', $content);
    }

    /**
     * 删除目录（递归）
     */
    protected function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }

    /**
     * 更新现有模板包（用于Linux下载网模板）
     */
    public function updateExistingPackage(string $packageCode)
    {
        // 检查模板包是否已在数据库中
        $package = TemplatePackage::where('code', $packageCode)->find();

        if (!$package) {
            throw new Exception("模板包不存在: {$packageCode}");
        }

        $packageDir = $this->templateBasePath . $packageCode;

        if (!is_dir($packageDir)) {
            throw new Exception("模板包目录不存在: {$packageDir}");
        }

        // 如果template.json不存在，创建它
        $configFile = $packageDir . DIRECTORY_SEPARATOR . 'template.json';
        if (file_exists($configFile)) {
            // 已存在，更新就完成了
            return true;
        }

        // 读取现有模板文件（模板是全局共享的，需要禁用站点过滤）
        $templates = Template::withoutSiteScope()->where('package_id', $package->id)->select();

        $config = [
            'name' => $package->name,
            'version' => $package->version,
            'author' => $package->author,
            'description' => $package->description,
            'templates' => [],
            'assets' => [
                'css' => ['css/style.css'],
                'js' => ['js/main.js'],
                'images' => ['images/']
            ]
        ];

        foreach ($templates as $template) {
            $type = str_replace('linux_nbxx_', '', $template->template_key);
            $file = str_replace('linux_nbxx/', '', $template->template_path);

            $config['templates'][$type] = [
                'file' => $file,
                'name' => $template->name,
                'description' => $template->description
            ];
        }

        // 保存配置文件
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return true;
    }
}