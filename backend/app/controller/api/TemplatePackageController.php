<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\TemplatePackage;
use app\model\Template;
use app\model\SiteTemplateConfig;
use think\Request;

/**
 * 模板包管理控制器
 */
class TemplatePackageController extends BaseController
{
    /**
     * 模板包列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $name = $request->get('name', '');
        $code = $request->get('code', '');
        $status = $request->get('status', '');
        $isSystem = $request->get('is_system', '');

        $query = TemplatePackage::withSearch(['name', 'code', 'status', 'is_system'], [
            'name' => $name,
            'code' => $code,
            'status' => $status,
            'is_system' => $isSystem,
        ])->order('sort', 'asc');

        $total = $query->count();
        $list = $query->page($page, $pageSize)->select();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取所有可用的模板包（下拉选择）
     */
    public function all(Request $request)
    {
        $siteId = $request->get('site_id', null);

        $packages = TemplatePackage::getAvailablePackages($siteId);

        return Response::success($packages);
    }

    /**
     * 模板包详情
     */
    public function read($id)
    {
        $package = TemplatePackage::find($id);

        if (!$package) {
            return Response::notFound('模板包不存在');
        }

        $data = $package->toArray();

        // 获取模板列表（不使用站点过滤）
        $templates = Template::getByPackage($id);
        $data['templates'] = $templates->toArray();

        // 统计信息
        $data['template_count'] = $templates->count();
        $data['using_site_count'] = SiteTemplateConfig::where('package_id', $id)
            ->where('is_active', 1)
            ->count();

        return Response::success($data);
    }

    /**
     * 创建模板包
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['name'])) {
            return Response::error('模板包名称不能为空');
        }

        if (empty($data['code'])) {
            return Response::error('模板包代码不能为空');
        }

        // 检查代码是否重复
        $exists = TemplatePackage::where('code', $data['code'])->find();
        if ($exists) {
            return Response::error('模板包代码已存在');
        }

        // 创建模板包
        $package = TemplatePackage::create($data);

        return Response::success($package, '模板包创建成功');
    }

    /**
     * 更新模板包
     */
    public function update(Request $request, $id)
    {
        $package = TemplatePackage::find($id);

        if (!$package) {
            return Response::notFound('模板包不存在');
        }

        // 系统内置模板包限制修改
        if ($package->is_system && !$request->user->isSuperAdmin()) {
            return Response::error('系统内置模板包不允许修改');
        }

        $data = $request->post();

        // 如果修改了代码，检查是否重复
        if (isset($data['code']) && $data['code'] != $package->code) {
            $exists = TemplatePackage::where('code', $data['code'])->find();
            if ($exists) {
                return Response::error('模板包代码已存在');
            }
        }

        $package->save($data);

        return Response::success($package, '模板包更新成功');
    }

    /**
     * 删除模板包
     */
    public function delete($id)
    {
        $package = TemplatePackage::find($id);

        if (!$package) {
            return Response::notFound('模板包不存在');
        }

        // 系统内置模板包不允许删除
        if ($package->is_system) {
            return Response::error('系统内置模板包不允许删除');
        }

        // 检查是否有站点在使用
        $usingCount = SiteTemplateConfig::where('package_id', $id)
            ->where('is_active', 1)
            ->count();

        if ($usingCount > 0) {
            return Response::error("该模板包正在被 {$usingCount} 个站点使用，无法删除");
        }

        // 删除模板包及其模板（模板是全局共享的，需要禁用站点过滤）
        Template::withoutSiteScope()->where('package_id', $id)->delete();
        $package->delete();

        return Response::success(null, '模板包删除成功');
    }

    /**
     * 获取模板包的模板列表
     */
    public function templates($id, Request $request)
    {
        $package = TemplatePackage::find($id);

        if (!$package) {
            return Response::notFound('模板包不存在');
        }

        $templateType = $request->get('template_type', '');

        $templates = Template::getByPackage($id, $templateType ?: null);

        return Response::success($templates);
    }

    /**
     * 复制模板包
     */
    public function copy($id, Request $request)
    {
        $package = TemplatePackage::find($id);

        if (!$package) {
            return Response::notFound('模板包不存在');
        }

        $newName = $request->post('name', $package->name . '_副本');
        $newCode = $request->post('code', $package->code . '_copy_' . time());

        // 检查代码是否重复
        $exists = TemplatePackage::where('code', $newCode)->find();
        if ($exists) {
            return Response::error('模板包代码已存在');
        }

        // 复制模板包
        $newPackage = $package->toArray();
        unset($newPackage['id'], $newPackage['create_time'], $newPackage['update_time'], $newPackage['install_time']);
        $newPackage['name'] = $newName;
        $newPackage['code'] = $newCode;
        $newPackage['is_system'] = 0;

        $newPackageModel = TemplatePackage::create($newPackage);

        // 复制模板文件（模板是全局共享的，需要禁用站点过滤）
        $templates = Template::withoutSiteScope()->where('package_id', $id)->select();
        foreach ($templates as $template) {
            $newTemplate = $template->toArray();
            unset($newTemplate['id'], $newTemplate['create_time'], $newTemplate['update_time']);
            $newTemplate['package_id'] = $newPackageModel->id;
            Template::create($newTemplate);
        }

        return Response::success($newPackageModel, '模板包复制成功');
    }

    /**
     * 导出模板包（预留接口）
     */
    public function export($id)
    {
        $package = TemplatePackage::with(['templates'])->find($id);

        if (!$package) {
            return Response::notFound('模板包不存在');
        }

        // TODO: 实现模板包导出为ZIP功能

        return Response::success([
            'package' => $package,
            'message' => '导出功能开发中...'
        ]);
    }

    /**
     * 导入模板包（预留接口）
     */
    public function import(Request $request)
    {
        // TODO: 实现从ZIP导入模板包功能

        return Response::success(null, '导入功能开发中...');
    }
}
