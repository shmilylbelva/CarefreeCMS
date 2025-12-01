<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\common\Logger;
use app\model\AiPromptTemplate;
use app\model\OperationLog;
use think\Request;

/**
 * AI提示词模板管理控制器
 */
class AiPromptTemplateController extends BaseController
{
    /**
     * 模板列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $category = $request->get('category', '');
        $status = $request->get('status', '');
        $keyword = $request->get('keyword', '');

        $query = AiPromptTemplate::order('sort_order', 'asc')
            ->order('id', 'desc');

        if ($category) {
            $query->where('category', $category);
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereLike('name', "%{$keyword}%")
                  ->whereOr('description', 'like', "%{$keyword}%");
            });
        }

        // 先统计总数
        $total = $query->count();

        // 再获取分页数据
        $list = $query->page($page, $pageSize)->select();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取所有启用的模板（下拉选择）
     */
    public function all(Request $request)
    {
        $category = $request->get('category', '');

        $query = AiPromptTemplate::where('status', 1)
            ->order('sort_order', 'asc')
            ->order('id', 'asc')
            ->field('id,name,category,prompt,description,variables');

        if ($category) {
            $query->where('category', $category);
        }

        $list = $query->select();

        return Response::success($list->toArray());
    }

    /**
     * 模板详情
     */
    public function read($id)
    {
        $template = AiPromptTemplate::find($id);

        if (!$template) {
            return Response::notFound('模板不存在');
        }

        return Response::success($template->toArray());
    }

    /**
     * 创建模板
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['name'])) {
            return Response::error('模板名称不能为空');
        }

        if (empty($data['prompt'])) {
            return Response::error('提示词内容不能为空');
        }

        try {
            $template = AiPromptTemplate::create($data);

            Logger::create(OperationLog::MODULE_SYSTEM, 'AI提示词模板', $template->id);
            return Response::success(['id' => $template->id], '模板创建成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_CREATE, '创建AI提示词模板失败', false, $e->getMessage());
            return Response::error('模板创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新模板
     */
    public function update(Request $request, $id)
    {
        $template = AiPromptTemplate::find($id);
        if (!$template) {
            return Response::notFound('模板不存在');
        }

        // 允许修改所有字段（包括系统模板）
        $data = $request->post();

        try {
            $affected = \think\facade\Db::name('ai_prompt_templates')
                ->where('id', '=', $id)
                ->limit(1)
                ->update($data);

            if ($affected === 0) {
                return Response::error('模板更新失败：未找到该模板或数据未改变');
            }

            Logger::update(OperationLog::MODULE_SYSTEM, 'AI提示词模板', $id);
            return Response::success([], '模板更新成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_UPDATE, "更新AI提示词模板失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('模板更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除模板
     */
    public function delete($id)
    {
        $template = AiPromptTemplate::find($id);
        if (!$template) {
            return Response::notFound('模板不存在');
        }

        // 系统预置模板不允许删除
        if ($template->is_system) {
            return Response::error('系统预置模板不允许删除');
        }

        try {
            $templateId = $template->id;
            $templateName = $template->name;

            // 使用Db类直接删除，确保WHERE条件精确
            $affected = \think\facade\Db::name('ai_prompt_templates')
                ->where('id', '=', $templateId)
                ->limit(1)
                ->delete();

            if ($affected === 0) {
                throw new \Exception('模板删除失败：未找到该模板');
            }

            Logger::delete(OperationLog::MODULE_SYSTEM, "AI提示词模板[{$templateName}]", $templateId);
            return Response::success([], '模板删除成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_SYSTEM, OperationLog::ACTION_DELETE, "删除AI提示词模板失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('模板删除失败：' . $e->getMessage());
        }
    }

    /**
     * 获取分类列表
     */
    public function categories()
    {
        return Response::success(AiPromptTemplate::getCategories());
    }
}
