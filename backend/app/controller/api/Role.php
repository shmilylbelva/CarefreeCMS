<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\AdminRole;
use app\model\AdminUser;
use app\traits\QueryFilterTrait;
use think\Request;

/**
 * 角色管理控制器
 */
class Role extends BaseController
{
    use QueryFilterTrait;
    /**
     * 角色列表
     */
    public function index(Request $request)
    {
        // 构建查询
        $query = AdminRole::withCount(['users']);

        // 定义过滤条件
        $filters = [
            'name' => ['operator' => 'like'],
            'status' => ['operator' => '='],
        ];

        // 定义排序
        $order = ['sort' => 'asc', 'id' => 'asc'];

        // 使用Trait的快速构建方法
        $result = $this->buildListQuery($query, $filters, $order, $request);

        // 确保list是数组
        $list = is_array($result['list']) ? $result['list'] : $result['list']->toArray();

        return Response::paginate(
            $list,
            $result['total'],
            $request->get('page', 1),
            $request->get('pageSize', 10)
        );
    }

    /**
     * 获取所有角色（不分页）
     */
    public function all(Request $request)
    {
        $status = $request->get('status', 1);

        $query = AdminRole::order(['sort' => 'asc', 'id' => 'asc']);

        if ($status !== '') {
            $query->where('status', $status);
        }

        $list = $query->select();

        return Response::success($list->toArray());
    }

    /**
     * 角色详情
     */
    public function read($id)
    {
        $role = AdminRole::withCount(['users'])->find($id);

        if (!$role) {
            return Response::notFound('角色不存在');
        }

        return Response::success($role->toArray());
    }

    /**
     * 创建角色
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['name'])) {
            return Response::error('角色名称不能为空');
        }

        // 检查角色名称是否已存在
        $exists = AdminRole::where('name', $data['name'])->find();
        if ($exists) {
            return Response::error('角色名称已存在');
        }

        // 设置默认值
        if (!isset($data['status'])) {
            $data['status'] = 1;
        }
        if (!isset($data['sort'])) {
            $data['sort'] = 0;
        }

        try {
            $role = AdminRole::create($data);
            return Response::success(['id' => $role->id], '角色创建成功');
        } catch (\Exception $e) {
            return Response::error('角色创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新角色
     */
    public function update(Request $request, $id)
    {
        $role = AdminRole::find($id);
        if (!$role) {
            return Response::notFound('角色不存在');
        }

        // 不能修改ID为1的超级管理员角色
        if ((int)$id === 1) {
            return Response::error('不能修改超级管理员角色');
        }

        $data = $request->post();

        // 检查角色名称是否与其他角色重复
        if (isset($data['name'])) {
            $exists = AdminRole::where('name', $data['name'])
                ->where('id', '<>', $id)
                ->find();
            if ($exists) {
                return Response::error('角色名称已存在');
            }
        }

        try {
            // 记录权限变更前的数据
            $oldPermissions = $role->permissions ?? [];
            $newPermissions = $data['permissions'] ?? $oldPermissions;

            // 保存角色数据
            $role->save($data);

            // 如果权限发生变更，记录日志
            if (isset($data['permissions']) && $oldPermissions != $newPermissions) {
                $this->logPermissionChange($role, $oldPermissions, $newPermissions, $request->user['id']);
            }

            // 清空该角色下所有用户的权限缓存
            if (isset($data['permissions'])) {
                $users = AdminUser::where('role_id', $id)->select();
                foreach ($users as $user) {
                    AdminUser::clearUserPermissionsCache($user->id);
                }
            }

            return Response::success([], '角色更新成功');
        } catch (\Exception $e) {
            return Response::error('角色更新失败：' . $e->getMessage());
        }
    }

    /**
     * 记录权限变更日志
     */
    protected function logPermissionChange($role, $oldPermissions, $newPermissions, $operatorId)
    {
        // 计算权限差异
        $added = is_array($newPermissions) && is_array($oldPermissions)
            ? array_diff($newPermissions, $oldPermissions)
            : [];
        $removed = is_array($newPermissions) && is_array($oldPermissions)
            ? array_diff($oldPermissions, $newPermissions)
            : [];

        // 构建日志内容
        $changes = [];
        if (!empty($added)) {
            $changes[] = '新增权限: ' . implode(', ', $added);
        }
        if (!empty($removed)) {
            $changes[] = '移除权限: ' . implode(', ', $removed);
        }

        $changeLog = implode('; ', $changes);

        // 记录操作日志
        \app\common\Logger::update(
            \app\model\OperationLog::MODULE_ROLE,
            "修改角色权限: {$role->name} - {$changeLog}",
            $role->id,
            json_encode([
                'old_permissions' => $oldPermissions,
                'new_permissions' => $newPermissions,
                'added' => array_values($added),
                'removed' => array_values($removed)
            ])
        );
    }

    /**
     * 删除角色
     */
    public function delete($id)
    {
        $role = AdminRole::find($id);
        if (!$role) {
            return Response::notFound('角色不存在');
        }

        // 不能删除ID为1的超级管理员角色
        if ((int)$id === 1) {
            return Response::error('不能删除超级管理员角色');
        }

        // 检查是否有用户关联此角色
        $userCount = AdminUser::where('role_id', $id)->count();
        if ($userCount > 0) {
            return Response::error('该角色下有 ' . $userCount . ' 个用户，无法删除');
        }

        try {
            $role->delete();
            return Response::success([], '角色删除成功');
        } catch (\Exception $e) {
            return Response::error('角色删除失败：' . $e->getMessage());
        }
    }
}
