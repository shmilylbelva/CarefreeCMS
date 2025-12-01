<?php
declare(strict_types=1);

namespace app\controller\api;

use app\BaseController;
use app\model\TemplateType;
use app\model\Template;
use think\Request;
use think\facade\Db;

class TemplateTypeController extends BaseController
{
    /**
     * 获取模板类型列表
     */
    public function index(Request $request)
    {
        try {
            $page = $request->param('page', 1);
            $pageSize = $request->param('page_size', 20);
            $keyword = $request->param('keyword', '');
            $status = $request->param('status');
            $isSystem = $request->param('is_system');

            $query = TemplateType::order('sort', 'asc')->order('id', 'asc');

            // 搜索条件
            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->whereOr('code', 'like', "%{$keyword}%")
                      ->whereOr('description', 'like', "%{$keyword}%");
                });
            }

            if ($status !== null && $status !== '') {
                $query->where('status', $status);
            }

            if ($isSystem !== null && $isSystem !== '') {
                $query->where('is_system', $isSystem);
            }

            // 分页查询
            $list = $query->paginate([
                'page' => $page,
                'list_rows' => $pageSize
            ]);

            // 添加额外信息
            $items = $list->items();
            foreach ($items as &$item) {
                // 获取使用此类型的模板数量（模板是全局共享的，需要禁用站点过滤）
                $item['template_count'] = Template::withoutSiteScope()->where('template_type', $item['code'])->count();
                // 获取格式化的参数和变量
                $item['formatted_params'] = $item->getFormattedParams();
                $item['formatted_vars'] = $item->getFormattedTemplateVars();
                // 获取文件命名示例
                $item['naming_examples'] = $item->getFileNamingExamples();
                // 是否可删除
                $item['can_delete'] = $item->canDelete();
            }

            return json([
                'code' => 200,
                'msg' => '获取成功',
                'data' => [
                    'list' => $items,
                    'total' => $list->total(),
                    'page' => $list->currentPage(),
                    'page_size' => $pageSize
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '获取失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取模板类型选项
     */
    public function options()
    {
        try {
            $options = TemplateType::getSelectOptions();

            return json([
                'code' => 200,
                'msg' => '获取成功',
                'data' => $options
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '获取失败：' . $e->getMessage()]);
        }
    }

    /**
     * 获取模板类型详情
     */
    public function detail($id)
    {
        try {
            $type = TemplateType::find($id);

            if (!$type) {
                return json(['code' => 404, 'msg' => '模板类型不存在']);
            }

            // 添加额外信息（模板是全局共享的，需要禁用站点过滤）
            $type['template_count'] = Template::withoutSiteScope()->where('template_type', $type->code)->count();
            $type['formatted_params'] = $type->getFormattedParams();
            $type['formatted_vars'] = $type->getFormattedTemplateVars();
            $type['naming_examples'] = $type->getFileNamingExamples();
            $type['can_delete'] = $type->canDelete();

            return json([
                'code' => 200,
                'msg' => '获取成功',
                'data' => $type
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '获取失败：' . $e->getMessage()]);
        }
    }

    /**
     * 创建模板类型
     */
    public function save(Request $request)
    {
        try {
            $data = $request->param();

            // 验证必填字段
            $validate = \think\facade\Validate::rule([
                'name' => 'require|length:1,50',
                'code' => 'require|length:1,30|unique:template_types',
                'file_naming' => 'require|length:1,100',
                'sort' => 'integer'
            ])->message([
                'name.require' => '类型名称不能为空',
                'name.length' => '类型名称长度必须在1-50之间',
                'code.require' => '类型代码不能为空',
                'code.length' => '类型代码长度必须在1-30之间',
                'code.unique' => '类型代码已存在',
                'file_naming.require' => '文件命名规则不能为空',
                'file_naming.length' => '文件命名规则长度必须在1-100之间',
                'sort.integer' => '排序必须是整数'
            ]);

            if (!$validate->check($data)) {
                return json(['code' => 422, 'msg' => $validate->getError()]);
            }

            // 处理参数和变量字段
            if (isset($data['params']) && is_array($data['params'])) {
                // 将数组转换为键值对
                $params = [];
                foreach ($data['params'] as $param) {
                    if (!empty($param['name'])) {
                        $params[$param['name']] = $param['description'] ?? '';
                    }
                }
                $data['params'] = $params;
            }

            if (isset($data['template_vars']) && is_array($data['template_vars'])) {
                // 将数组转换为键值对
                $vars = [];
                foreach ($data['template_vars'] as $var) {
                    if (!empty($var['name'])) {
                        $vars[$var['name']] = $var['description'] ?? '';
                    }
                }
                $data['template_vars'] = $vars;
            }

            // 设置默认值
            $data['is_system'] = $data['is_system'] ?? false;
            $data['allow_multiple'] = $data['allow_multiple'] ?? false;
            $data['status'] = $data['status'] ?? true;
            $data['sort'] = $data['sort'] ?? 0;

            $type = TemplateType::create($data);

            return json([
                'code' => 200,
                'msg' => '创建成功',
                'data' => $type
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '创建失败：' . $e->getMessage()]);
        }
    }

    /**
     * 更新模板类型
     */
    public function update(Request $request, $id)
    {
        try {
            $type = TemplateType::find($id);

            if (!$type) {
                return json(['code' => 404, 'msg' => '模板类型不存在']);
            }

            // 系统内置类型限制修改
            if ($type->is_system) {
                $allowedFields = ['description', 'icon', 'params', 'template_vars', 'example_code', 'sort', 'status'];
                $data = array_intersect_key($request->param(), array_flip($allowedFields));
            } else {
                $data = $request->param();
            }

            // 验证字段
            $rules = [
                'name' => 'length:1,50',
                'file_naming' => 'length:1,100',
                'sort' => 'integer'
            ];

            // code字段验证（排除自己）
            if (isset($data['code']) && $data['code'] !== $type->code) {
                $rules['code'] = 'length:1,30|unique:template_types';
            }

            $validate = \think\facade\Validate::rule($rules)->message([
                'name.length' => '类型名称长度必须在1-50之间',
                'code.length' => '类型代码长度必须在1-30之间',
                'code.unique' => '类型代码已存在',
                'file_naming.length' => '文件命名规则长度必须在1-100之间',
                'sort.integer' => '排序必须是整数'
            ]);

            if (!$validate->check($data)) {
                return json(['code' => 422, 'msg' => $validate->getError()]);
            }

            // 处理参数和变量字段
            if (isset($data['params']) && is_array($data['params'])) {
                $params = [];
                foreach ($data['params'] as $param) {
                    if (is_array($param) && !empty($param['name'])) {
                        $params[$param['name']] = $param['description'] ?? '';
                    } elseif (is_string($param)) {
                        // 兼容旧格式
                        $params[] = $param;
                    }
                }
                $data['params'] = $params;
            }

            if (isset($data['template_vars']) && is_array($data['template_vars'])) {
                $vars = [];
                foreach ($data['template_vars'] as $var) {
                    if (is_array($var) && !empty($var['name'])) {
                        $vars[$var['name']] = $var['description'] ?? '';
                    } elseif (is_string($var)) {
                        // 兼容旧格式
                        $vars[] = $var;
                    }
                }
                $data['template_vars'] = $vars;
            }

            // 如果修改了code，需要更新相关模板
            $oldCode = $type->code;
            $type->save($data);

            if (isset($data['code']) && $data['code'] !== $oldCode) {
                // 更新所有使用此类型的模板（模板是全局共享的，需要禁用站点过滤）
                Template::withoutSiteScope()->where('template_type', $oldCode)
                    ->update(['template_type' => $data['code']]);
            }

            return json([
                'code' => 200,
                'msg' => '更新成功',
                'data' => $type
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '更新失败：' . $e->getMessage()]);
        }
    }

    /**
     * 删除模板类型
     */
    public function delete($id)
    {
        try {
            $type = TemplateType::find($id);

            if (!$type) {
                return json(['code' => 404, 'msg' => '模板类型不存在']);
            }

            // 检查是否可以删除
            if (!$type->canDelete()) {
                if ($type->is_system) {
                    return json(['code' => 403, 'msg' => '系统内置类型不能删除']);
                } else {
                    // 模板是全局共享的，需要禁用站点过滤
                    $count = Template::withoutSiteScope()->where('template_type', $type->code)->count();
                    return json(['code' => 403, 'msg' => "该类型下还有 {$count} 个模板在使用，不能删除"]);
                }
            }

            $type->delete();

            return json([
                'code' => 200,
                'msg' => '删除成功'
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '删除失败：' . $e->getMessage()]);
        }
    }

    /**
     * 批量删除
     */
    public function batchDelete(Request $request)
    {
        try {
            $ids = $request->param('ids', []);

            if (empty($ids)) {
                return json(['code' => 422, 'msg' => '请选择要删除的项']);
            }

            $types = TemplateType::whereIn('id', $ids)->select();
            $deleted = 0;
            $errors = [];

            foreach ($types as $type) {
                if ($type->canDelete()) {
                    $type->delete();
                    $deleted++;
                } else {
                    if ($type->is_system) {
                        $errors[] = "{$type->name} 是系统内置类型";
                    } else {
                        // 模板是全局共享的，需要禁用站点过滤
                        $count = Template::withoutSiteScope()->where('template_type', $type->code)->count();
                        $errors[] = "{$type->name} 下有 {$count} 个模板在使用";
                    }
                }
            }

            $msg = "成功删除 {$deleted} 个模板类型";
            if (!empty($errors)) {
                $msg .= "，失败：" . implode('；', $errors);
            }

            return json([
                'code' => 200,
                'msg' => $msg,
                'data' => [
                    'deleted' => $deleted,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '批量删除失败：' . $e->getMessage()]);
        }
    }

    /**
     * 更新排序
     */
    public function updateSort(Request $request)
    {
        try {
            $items = $request->param('items', []);

            if (empty($items)) {
                return json(['code' => 422, 'msg' => '没有要更新的数据']);
            }

            Db::startTrans();
            try {
                foreach ($items as $item) {
                    if (isset($item['id']) && isset($item['sort'])) {
                        TemplateType::where('id', $item['id'])
                            ->update(['sort' => $item['sort']]);
                    }
                }
                Db::commit();

                return json([
                    'code' => 200,
                    'msg' => '排序更新成功'
                ]);
            } catch (\Exception $e) {
                Db::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '更新排序失败：' . $e->getMessage()]);
        }
    }

    /**
     * 切换状态
     */
    public function toggleStatus($id)
    {
        try {
            $type = TemplateType::find($id);

            if (!$type) {
                return json(['code' => 404, 'msg' => '模板类型不存在']);
            }

            $type->status = !$type->status;
            $type->save();

            return json([
                'code' => 200,
                'msg' => $type->status ? '已启用' : '已禁用',
                'data' => ['status' => $type->status]
            ]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => '操作失败：' . $e->getMessage()]);
        }
    }
}