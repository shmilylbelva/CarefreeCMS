<?php
declare(strict_types=1);

namespace app\service;

use app\model\Site;
use app\model\Template;
use app\model\TemplatePackage;
use app\model\SiteTemplateConfig;
use app\model\SiteTemplateOverride;

/**
 * 模板解析服务
 * 负责根据站点和模板类型解析出正确的模板文件路径和配置
 */
class TemplateResolver
{
    /**
     * 站点ID
     * @var int
     */
    protected $siteId;

    /**
     * 站点信息
     * @var Site|null
     */
    protected $site;

    /**
     * 站点模板包ID
     * @var int
     */
    protected $packageId;

    /**
     * 模板包信息
     * @var TemplatePackage|null
     */
    protected $package;

    /**
     * 合并后的配置
     * @var array
     */
    protected $mergedConfig = [];

    /**
     * 构造函数
     * @param int $siteId 站点ID，0表示全局/默认站点
     */
    public function __construct(int $siteId = 0)
    {
        $this->siteId = $siteId;
        $this->loadSite();
        $this->loadPackage();
        $this->mergeConfig();
    }

    /**
     * 加载站点信息
     */
    protected function loadSite()
    {
        if ($this->siteId > 0) {
            $this->site = Site::find($this->siteId);
            if (!$this->site) {
                throw new \Exception("站点不存在: {$this->siteId}");
            }
        } else {
            // 获取主站点（使用缓存方法）
            $this->site = Site::getMainSite();
            if ($this->site) {
                $this->siteId = $this->site->id;
            }
        }
    }

    /**
     * 加载模板包信息
     */
    protected function loadPackage()
    {
        // 获取站点配置的模板包ID
        if ($this->site) {
            $siteConfig = SiteTemplateConfig::where('site_id', $this->site->id)->find();
            $this->packageId = $siteConfig ? $siteConfig->package_id : 1;
        } else {
            $this->packageId = 1; // 默认使用ID为1的模板包
        }

        // 加载模板包
        $this->package = TemplatePackage::find($this->packageId);
        if (!$this->package) {
            // 如果指定的包不存在，回退到默认包
            $this->package = TemplatePackage::where('code', 'default')->find();
            if (!$this->package) {
                throw new \Exception("默认模板包不存在");
            }
            $this->packageId = $this->package->id;
        }
    }

    /**
     * 合并配置
     * 优先级：站点自定义配置 > 模板包默认配置
     */
    protected function mergeConfig()
    {
        // 模板包默认配置
        $defaultConfig = $this->package->default_config ?? [];

        // 站点自定义配置
        $customConfig = [];
        if ($this->site) {
            $siteConfig = SiteTemplateConfig::where('site_id', $this->site->id)->find();
            if ($siteConfig) {
                $customConfig = $siteConfig->custom_config ?? [];
            }
        }

        // 合并配置（自定义配置覆盖默认配置）
        $this->mergedConfig = array_merge($defaultConfig, $customConfig);
    }

    /**
     * 解析模板路径
     * 优先级：站点覆盖模板 > 站点包模板 > 默认包模板
     *
     * @param string $templateType 模板类型（如 index, article, category等）
     * @return string 模板文件的完整路径
     */
    public function resolveTemplatePath(string $templateType): string
    {
        // 1. 检查站点是否有覆盖模板
        if ($this->site) {
            $override = SiteTemplateOverride::where('site_id', $this->site->id)
                ->where('template_type', $templateType)
                ->find();

            if ($override) {
                $templatePath = root_path() . 'templates' . DIRECTORY_SEPARATOR .
                               'sites' . DIRECTORY_SEPARATOR .
                               $this->site->id . DIRECTORY_SEPARATOR .
                               $override->override_path;
                if (file_exists($templatePath)) {
                    return $templatePath;
                }
            }
        }

        // 2. 查找站点包模板（禁用站点过滤，因为模板是全局共享的）
        $template = Template::withoutSiteScope()
            ->where('package_id', $this->packageId)
            ->where('template_type', $templateType)
            ->where('is_package_default', 1)
            ->where('status', 1)
            ->find();

        if ($template) {
            $templatePath = root_path() . 'templates' . DIRECTORY_SEPARATOR . $template->template_path;
            if (file_exists($templatePath)) {
                return $templatePath;
            }
        }

        // 3. 回退到默认包模板
        if ($this->packageId != 1) {
            $defaultTemplate = Template::withoutSiteScope()
                ->where('package_id', 1)
                ->where('template_type', $templateType)
                ->where('is_package_default', 1)
                ->where('status', 1)
                ->find();

            if ($defaultTemplate) {
                $templatePath = root_path() . 'templates' . DIRECTORY_SEPARATOR . $defaultTemplate->template_path;
                if (file_exists($templatePath)) {
                    return $templatePath;
                }
            }
        }

        throw new \Exception("模板不存在: {$templateType}");
    }

    /**
     * 获取模板文件路径（用于View渲染）
     * @param string $templateType 模板类型
     * @return string 相对路径（相对于templates目录）
     */
    public function getTemplateViewPath(string $templateType): string
    {
        // 1. 检查站点覆盖
        if ($this->site) {
            $override = SiteTemplateOverride::where('site_id', $this->site->id)
                ->where('template_type', $templateType)
                ->find();

            if ($override) {
                return 'sites/' . $this->site->id . '/' . $override->override_path;
            }
        }

        // 2. 查找站点包模板（禁用站点过滤，因为模板是全局共享的）
        $template = Template::withoutSiteScope()
            ->where('package_id', $this->packageId)
            ->where('template_type', $templateType)
            ->where('is_package_default', 1)
            ->where('status', 1)
            ->find();

        if ($template) {
            return $template->template_path;
        }

        // 3. 回退到默认包
        if ($this->packageId != 1) {
            $defaultTemplate = Template::withoutSiteScope()
                ->where('package_id', 1)
                ->where('template_type', $templateType)
                ->where('is_package_default', 1)
                ->where('status', 1)
                ->find();

            if ($defaultTemplate) {
                return $defaultTemplate->template_path;
            }
        }

        throw new \Exception("模板不存在: {$templateType}");
    }

    /**
     * 获取合并后的配置
     * @return array
     */
    public function getConfig(): array
    {
        return $this->mergedConfig;
    }

    /**
     * 获取站点信息
     * @return Site|null
     */
    public function getSite(): ?Site
    {
        return $this->site;
    }

    /**
     * 获取模板包信息
     * @return TemplatePackage|null
     */
    public function getPackage(): ?TemplatePackage
    {
        return $this->package;
    }

    /**
     * 获取站点ID
     * @return int
     */
    public function getSiteId(): int
    {
        return $this->siteId;
    }

    /**
     * 获取模板包代码
     * @return string
     */
    public function getPackageCode(): string
    {
        return $this->package ? $this->package->code : 'default';
    }

    /**
     * 准备模板数据
     * 返回传递给模板的通用数据
     *
     * @return array
     */
    public function prepareTemplateData(): array
    {
        $data = [
            'site' => $this->site ? $this->site->toArray() : [],
            'config' => $this->mergedConfig,
            'package' => $this->package ? [
                'code' => $this->package->code,
                'name' => $this->package->name,
                'version' => $this->package->version,
            ] : null,
        ];

        // 确保SEO配置可访问
        if ($this->site && $this->site->seo_config) {
            $data['site']['seo_config'] = $this->site->seo_config;
        }

        return $data;
    }
}
