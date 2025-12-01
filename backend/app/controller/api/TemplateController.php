<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\Template as TemplateModel;
use app\model\TemplatePackage;
use app\service\TemplatePackageService;
use think\Request;
use think\facade\Db;

/**
 * 模板管理控制器
 * 管理模板包中的模板列表（CRUD）
 */
class TemplateController extends BaseController
{
    /**
     * 获取模板包的模板列表
     */
    public function index(Request $request)
    {
        $packageId = $request->get('package_id', 0);
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $templateType = $request->get('template_type', '');
        $name = $request->get('name', '');

        if (!$packageId) {
            return Response::error('请指定模板包');
        }

        // 检查模板包是否存在
        $package = TemplatePackage::find($packageId);
        if (!$package) {
            return Response::notFound('模板包不存在');
        }

        // 模板包的模板是全局共享的（site_id=0），需要禁用站点过滤
        $query = TemplateModel::withoutSiteScope()
            ->where('package_id', $packageId);

        if ($templateType) {
            $query->where('template_type', $templateType);
        }

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        $total = $query->count();
        $list = $query->order('id', 'asc')
            ->page($page, $pageSize)
            ->select();

        // 补充文件存在状态
        $templatesPath = app()->getRootPath() . 'templates' . DIRECTORY_SEPARATOR;
        foreach ($list as &$template) {
            $filePath = $templatesPath . $template['template_path'];
            $template['file_exists'] = file_exists($filePath);
            $template['file_size'] = $template['file_exists'] ? filesize($filePath) : 0;
            $template['file_modified'] = $template['file_exists'] ? date('Y-m-d H:i:s', filemtime($filePath)) : null;
        }

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 创建新模板
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['package_id'])) {
            return Response::error('请指定模板包');
        }

        if (empty($data['name'])) {
            return Response::error('模板名称不能为空');
        }

        // 如果没有提供template_key，自动生成
        if (empty($data['template_key'])) {
            $data['template_key'] = $data['package_id'] . '_' . ($data['type'] ?? $data['template_type']);
        }

        // 兼容前端传来的type字段
        if (!empty($data['type']) && empty($data['template_type'])) {
            $data['template_type'] = $data['type'];
        }

        if (empty($data['template_type'])) {
            return Response::error('模板类型不能为空');
        }

        // 兼容前端传来的file字段
        if (!empty($data['file']) && empty($data['file_name'])) {
            $data['file_name'] = $data['file'];
        }

        // 检查模板包
        $package = TemplatePackage::find($data['package_id']);
        if (!$package) {
            return Response::notFound('模板包不存在');
        }

        // 检查模板标识是否重复（模板是全局共享的，需要禁用站点过滤）
        $exists = TemplateModel::withoutSiteScope()->where('template_key', $data['template_key'])->find();
        if ($exists) {
            return Response::error('模板标识已存在');
        }

        // 生成模板文件路径
        $fileName = $data['file_name'] ?? $data['template_type'] . '.html';
        $templatePath = $package->code . '/' . $fileName;

        Db::startTrans();
        try {
            // 如果设置为包内默认，先取消同类型其他模板的默认状态（模板是全局共享的，需要禁用站点过滤）
            if (!empty($data['is_package_default'])) {
                TemplateModel::withoutSiteScope()->where('package_id', $data['package_id'])
                    ->where('template_type', $data['template_type'])
                    ->update(['is_package_default' => 0]);
            }

            // 创建数据库记录
            $template = TemplateModel::create([
                'site_id' => $data['site_id'] ?? 0,
                'package_id' => $data['package_id'],
                'name' => $data['name'],
                'template_key' => $data['template_key'],
                'template_type' => $data['template_type'],
                'description' => $data['description'] ?? '',
                'template_path' => $templatePath,
                'variables' => $data['variables'] ?? null,
                'config_schema' => $data['config_schema'] ?? null,
                'is_package_default' => $data['is_package_default'] ?? 0,
                'status' => $data['status'] ?? 1
            ]);

            // 创建模板文件
            $this->createTemplateFile($package->code, $fileName, $data['template_type'], $data['content'] ?? null);

            Db::commit();
            return Response::success($template, '模板创建成功');

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('创建模板失败：' . $e->getMessage());
        }
    }

    /**
     * 更新模板信息
     */
    public function update(Request $request, $id)
    {
        $template = TemplateModel::withoutSiteScope()->find($id);

        if (!$template) {
            return Response::notFound('模板不存在');
        }

        $data = $request->post();

        // 兼容前端传来的type字段
        if (!empty($data['type']) && empty($data['template_type'])) {
            $data['template_type'] = $data['type'];
        }

        // 如果修改了模板标识，检查是否重复（模板是全局共享的，需要禁用站点过滤）
        if (isset($data['template_key']) && $data['template_key'] != $template->template_key) {
            $exists = TemplateModel::withoutSiteScope()->where('template_key', $data['template_key'])->find();
            if ($exists) {
                return Response::error('模板标识已存在');
            }
        }

        // 如果设置为包内默认，取消同类型其他模板的默认状态（模板是全局共享的，需要禁用站点过滤）
        if (isset($data['is_package_default']) && $data['is_package_default'] == 1) {
            // 获取要设置的模板类型（可能更新了类型）
            $templateType = $data['template_type'] ?? $template->template_type;

            TemplateModel::withoutSiteScope()->where('package_id', $template->package_id)
                ->where('template_type', $templateType)
                ->where('id', '<>', $id)
                ->update(['is_package_default' => 0]);
        }

        // 如果更新了文件名，更新template_path
        if (isset($data['file']) && !empty($data['file'])) {
            $package = TemplatePackage::find($template->package_id);
            if ($package) {
                $fileName = $data['file'];
                if (!str_ends_with($fileName, '.html')) {
                    $fileName .= '.html';
                }
                $data['template_path'] = $package->code . '/' . $fileName;
            }
        }

        // 更新数据库记录
        $template->save($data);

        // 只有明确提供了内容且内容不为null时才更新文件
        // 避免编辑基本信息时清空文件内容
        if (isset($data['content']) && $data['content'] !== null && $data['content'] !== '') {
            $this->updateTemplateFile($template->template_path, $data['content']);
        }

        return Response::success($template, '模板更新成功');
    }

    /**
     * 删除模板
     */
    public function delete($id)
    {
        $template = TemplateModel::withoutSiteScope()->find($id);

        if (!$template) {
            return Response::notFound('模板不存在');
        }

        // 检查是否为系统模板或默认模板
        if ($template->is_default) {
            return Response::error('默认模板不能删除');
        }

        // 检查是否有站点在使用
        $usingCount = Db::name('site_template_config')
            ->where('template_id', $id)
            ->count();

        if ($usingCount > 0) {
            return Response::error("该模板正在被 {$usingCount} 个站点使用，无法删除");
        }

        Db::startTrans();
        try {
            $templateId = $template->id;
            $templatePath = $template->template_path;

            // 使用Db类直接删除，确保WHERE条件精确
            $affected = Db::name('templates')
                ->where('id', '=', $templateId)
                ->limit(1)
                ->delete();

            if ($affected === 0) {
                throw new \Exception('模板删除失败：未找到该模板');
            }

            // 询问是否删除文件（可选）
            if ($request->post('delete_file', false)) {
                $this->deleteTemplateFile($templatePath);
            }

            Db::commit();
            return Response::success(null, '模板删除成功');

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('删除模板失败：' . $e->getMessage());
        }
    }

    /**
     * 读取模板内容
     */
    public function read($id)
    {
        $template = TemplateModel::withoutSiteScope()->find($id);

        if (!$template) {
            return Response::notFound('模板不存在');
        }

        $templatesPath = app()->getRootPath() . 'templates' . DIRECTORY_SEPARATOR;
        $filePath = $templatesPath . str_replace('/', DIRECTORY_SEPARATOR, $template->template_path);

        $data = $template->toArray();

        // 读取文件内容
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);

            // 检测并转换编码为UTF-8
            $encoding = mb_detect_encoding($content, ['UTF-8', 'GBK', 'GB2312', 'ISO-8859-1', 'ASCII'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }

            // 移除可能的BOM
            $bom = pack('H*','EFBBBF');
            $content = preg_replace("/^$bom/", '', $content);

            $data['content'] = $content;
            $data['file_exists'] = true;
            $data['file_size'] = filesize($filePath);
            $data['file_modified'] = date('Y-m-d H:i:s', filemtime($filePath));
        } else {
            $data['content'] = '';
            $data['file_exists'] = false;
            $data['file_size'] = 0;
            $data['file_modified'] = null;
        }

        // 获取模板包信息
        $data['package'] = TemplatePackage::find($template->package_id);

        return Response::success($data);
    }

    /**
     * 保存模板内容
     */
    public function saveContent(Request $request, $id)
    {
        $template = TemplateModel::withoutSiteScope()->find($id);

        if (!$template) {
            return Response::notFound('模板不存在');
        }

        $content = $request->post('content', '');
        $description = $request->post('description', ''); // 修改说明

        try {
            // 保存到文件
            $this->updateTemplateFile($template->template_path, $content);

            // 记录历史（如果需要）
            if (class_exists('\app\model\TemplateHistory')) {
                $package = TemplatePackage::find($template->package_id);
                \app\model\TemplateHistory::createHistory(
                    $package->code,
                    str_replace($package->code . '/', '', $template->template_path),
                    $content,
                    $request->user['id'] ?? null,
                    $description ?: '更新模板内容'
                );
            }

            return Response::success([
                'path' => $template->template_path,
                'modified' => date('Y-m-d H:i:s')
            ], '模板内容保存成功');

        } catch (\Exception $e) {
            return Response::error('保存模板失败：' . $e->getMessage());
        }
    }

    /**
     * 复制模板
     */
    public function copy($id, Request $request)
    {
        $template = TemplateModel::withoutSiteScope()->find($id);

        if (!$template) {
            return Response::notFound('模板不存在');
        }

        $newName = $request->post('name', $template->name . '_副本');
        $newKey = $request->post('template_key', $template->template_key . '_copy_' . time());
        $newFileName = $request->post('file_name', '');

        // 检查新标识是否重复（模板是全局共享的，需要禁用站点过滤）
        $exists = TemplateModel::withoutSiteScope()->where('template_key', $newKey)->find();
        if ($exists) {
            return Response::error('模板标识已存在');
        }

        Db::startTrans();
        try {
            // 复制数据库记录
            $newTemplate = $template->toArray();
            unset($newTemplate['id'], $newTemplate['create_time'], $newTemplate['update_time']);
            $newTemplate['name'] = $newName;
            $newTemplate['template_key'] = $newKey;
            $newTemplate['is_default'] = 0;

            // 生成新文件名
            if (empty($newFileName)) {
                $oldFileName = basename($template->template_path);
                $newFileName = str_replace('.html', '_copy.html', $oldFileName);
            }

            $package = TemplatePackage::find($template->package_id);
            $newTemplate['template_path'] = $package->code . '/' . $newFileName;

            $newTemplateModel = TemplateModel::create($newTemplate);

            // 复制文件内容
            $templatesPath = app()->getRootPath() . 'templates' . DIRECTORY_SEPARATOR;
            $oldFile = $templatesPath . str_replace('/', DIRECTORY_SEPARATOR, $template->template_path);
            $newFile = $templatesPath . str_replace('/', DIRECTORY_SEPARATOR, $newTemplate['template_path']);

            if (file_exists($oldFile)) {
                $content = file_get_contents($oldFile);
                file_put_contents($newFile, $content);
            }

            Db::commit();
            return Response::success($newTemplateModel, '模板复制成功');

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('复制模板失败：' . $e->getMessage());
        }
    }

    /**
     * 批量操作
     */
    public function batch(Request $request)
    {
        $action = $request->post('action');
        $ids = $request->post('ids', []);

        if (empty($ids)) {
            return Response::error('请选择要操作的模板');
        }

        switch ($action) {
            case 'enable':
                TemplateModel::withoutSiteScope()->whereIn('id', $ids)->update(['status' => 1]);
                return Response::success(null, '批量启用成功');

            case 'disable':
                TemplateModel::withoutSiteScope()->whereIn('id', $ids)->update(['status' => 0]);
                return Response::success(null, '批量禁用成功');

            case 'delete':
                // 检查是否有默认模板（模板是全局共享的，需要禁用站点过滤）
                $hasDefault = TemplateModel::withoutSiteScope()->whereIn('id', $ids)
                    ->where('is_default', 1)
                    ->count();

                if ($hasDefault > 0) {
                    return Response::error('选中的模板包含默认模板，无法删除');
                }

                TemplateModel::withoutSiteScope()->whereIn('id', $ids)->delete();
                return Response::success(null, '批量删除成功');

            default:
                return Response::error('未知操作');
        }
    }

    /**
     * 获取模板类型列表
     */
    public function getTypes()
    {
        $types = [
            ['value' => 'layout', 'label' => '布局模板'],
            ['value' => 'index', 'label' => '首页模板'],
            ['value' => 'category', 'label' => '分类页模板'],
            ['value' => 'article', 'label' => '文章页模板'],
            ['value' => 'articles', 'label' => '文章列表模板'],
            ['value' => 'page', 'label' => '单页模板'],
            ['value' => 'search', 'label' => '搜索页模板'],
            ['value' => 'tag', 'label' => '标签页模板'],
            ['value' => 'topic', 'label' => '专题页模板'],
            ['value' => 'archive', 'label' => '归档页模板'],
            ['value' => '404', 'label' => '404页面模板'],
            ['value' => 'custom', 'label' => '自定义模板']
        ];

        return Response::success($types);
    }

    /**
     * 创建模板文件
     */
    private function createTemplateFile($packageCode, $fileName, $type, $content = null)
    {
        $templatesPath = app()->getRootPath() . 'templates' . DIRECTORY_SEPARATOR;
        $packageDir = $templatesPath . $packageCode . DIRECTORY_SEPARATOR;

        if (!is_dir($packageDir)) {
            throw new \Exception("模板包目录不存在: {$packageCode}");
        }

        $filePath = $packageDir . $fileName;

        // 如果没有提供内容，使用默认模板
        if ($content === null) {
            $service = new TemplatePackageService();
            $content = $this->getDefaultTemplateContent($type, $packageCode);
        }

        if (file_put_contents($filePath, $content) === false) {
            throw new \Exception("无法创建模板文件: {$fileName}");
        }
    }

    /**
     * 更新模板文件内容
     */
    private function updateTemplateFile($templatePath, $content)
    {
        $templatesPath = app()->getRootPath() . 'templates' . DIRECTORY_SEPARATOR;
        $filePath = $templatesPath . str_replace('/', DIRECTORY_SEPARATOR, $templatePath);

        // 确保目录存在
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_put_contents($filePath, $content) === false) {
            throw new \Exception("无法更新模板文件");
        }
    }

    /**
     * 删除模板文件
     */
    private function deleteTemplateFile($templatePath)
    {
        $templatesPath = app()->getRootPath() . 'templates' . DIRECTORY_SEPARATOR;
        $filePath = $templatesPath . str_replace('/', DIRECTORY_SEPARATOR, $templatePath);

        if (file_exists($filePath)) {
            // 备份到回收站
            $trashPath = $templatesPath . '.trash' . DIRECTORY_SEPARATOR;
            if (!is_dir($trashPath)) {
                mkdir($trashPath, 0755, true);
            }

            $backupFile = $trashPath . str_replace('/', '_', $templatePath) . '.' . date('YmdHis');
            copy($filePath, $backupFile);

            // 删除文件
            unlink($filePath);
        }
    }

    /**
     * 获取模板包的文件列表
     */
    public function getPackageFiles(Request $request)
    {
        $packageId = $request->get('package_id');
        if (!$packageId) {
            return Response::error('请提供模板包ID');
        }

        $package = TemplatePackage::find($packageId);
        if (!$package) {
            return Response::notFound('模板包不存在');
        }

        $templatesPath = app()->getRootPath() . 'templates' . DIRECTORY_SEPARATOR;
        $packageDir = $templatesPath . $package->code . DIRECTORY_SEPARATOR;

        if (!is_dir($packageDir)) {
            return Response::success([]);
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($packageDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match('/\.(html|htm|twig|tpl)$/i', $file->getFilename())) {
                $relativePath = str_replace($packageDir, '', $file->getPathname());
                $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

                // 获取已登记的模板信息（模板是全局共享的，需要禁用站点过滤）
                $template = TemplateModel::withoutSiteScope()->where('package_id', $packageId)
                    ->where('template_path', $package->code . '/' . $relativePath)
                    ->find();

                $files[] = [
                    'path' => $relativePath,
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    'registered' => $template ? true : false,
                    'template_id' => $template ? $template->id : null,
                    'template_name' => $template ? $template->name : null
                ];
            }
        }

        return Response::success($files);
    }

    /**
     * 检查模板文件是否存在
     */
    public function checkFileExists(Request $request)
    {
        $packageId = $request->get('package_id');
        $fileName = $request->get('file_name');

        if (!$packageId || !$fileName) {
            return Response::error('参数不完整');
        }

        $package = TemplatePackage::find($packageId);
        if (!$package) {
            return Response::notFound('模板包不存在');
        }

        $templatesPath = app()->getRootPath() . 'templates' . DIRECTORY_SEPARATOR;
        $filePath = $templatesPath . $package->code . DIRECTORY_SEPARATOR . $fileName;

        $exists = file_exists($filePath);
        $content = '';

        if ($exists) {
            $content = file_get_contents($filePath);
        }

        return Response::success([
            'exists' => $exists,
            'content' => $content,
            'path' => $package->code . '/' . $fileName
        ]);
    }

    /**
     * 获取默认模板内容
     */
    private function getDefaultTemplateContent($type, $packageCode)
    {
        $templates = [
            'layout' => "<!DOCTYPE html>\n<html>\n<head>\n    <title>{% block title %}{{ site_name }}{% endblock %}</title>\n</head>\n<body>\n    {% block content %}{% endblock %}\n</body>\n</html>",
            'index' => "{% extends \"{$packageCode}/layout.html\" %}\n\n{% block content %}\n<h1>首页</h1>\n{% endblock %}",
            'category' => "{% extends \"{$packageCode}/layout.html\" %}\n\n{% block content %}\n<h1>{{ category.name }}</h1>\n{% endblock %}",
            'article' => "{% extends \"{$packageCode}/layout.html\" %}\n\n{% block content %}\n<h1>{{ article.title }}</h1>\n<div>{{ article.content|raw }}</div>\n{% endblock %}",
            'page' => "{% extends \"{$packageCode}/layout.html\" %}\n\n{% block content %}\n<h1>{{ page.title }}</h1>\n<div>{{ page.content|raw }}</div>\n{% endblock %}",
            'search' => "{% extends \"{$packageCode}/layout.html\" %}\n\n{% block content %}\n<h1>搜索：{{ keyword }}</h1>\n{% endblock %}",
            'tag' => "{% extends \"{$packageCode}/layout.html\" %}\n\n{% block content %}\n<h1>标签：{{ tag.name }}</h1>\n{% endblock %}"
        ];

        return $templates[$type] ?? "{% extends \"{$packageCode}/layout.html\" %}\n\n{% block content %}\n<!-- {$type} 模板 -->\n{% endblock %}";
    }
}