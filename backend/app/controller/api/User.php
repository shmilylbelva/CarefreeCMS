<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\AdminUser;
use app\model\AdminRole;
use app\traits\QueryFilterTrait;
use think\Request;

/**
 * 用户管理控制器
 */
class User extends BaseController
{
    use QueryFilterTrait;
    /**
     * 用户列表
     */
    public function index(Request $request)
    {
        // 构建查询
        $query = AdminUser::with(['role']);

        // 定义过滤条件
        $filters = [
            'username' => ['operator' => 'like'],
            'role_id' => ['operator' => '='],
            'status' => ['operator' => '='],
        ];

        // 定义排序
        $order = ['id' => 'desc'];

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
     * 用户详情
     */
    public function read($id)
    {
        $user = AdminUser::with(['role'])->find($id);

        if (!$user) {
            return Response::notFound('用户不存在');
        }

        return Response::success($user->toArray());
    }

    /**
     * 创建用户
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['username'])) {
            return Response::error('用户名不能为空');
        }
        if (empty($data['password'])) {
            return Response::error('密码不能为空');
        }
        if (empty($data['role_id'])) {
            return Response::error('请选择角色');
        }

        // 检查用户名是否已存在
        $exists = AdminUser::where('username', $data['username'])->find();
        if ($exists) {
            return Response::error('用户名已存在');
        }

        // 检查角色是否存在
        $role = AdminRole::find($data['role_id']);
        if (!$role) {
            return Response::error('角色不存在');
        }

        // 设置默认值
        if (!isset($data['status'])) {
            $data['status'] = 1;
        }

        try {
            $user = AdminUser::create($data);
            return Response::success(['id' => $user->id], '用户创建成功');
        } catch (\Exception $e) {
            return Response::error('用户创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新用户
     */
    public function update(Request $request, $id)
    {
        $user = AdminUser::find($id);
        if (!$user) {
            return Response::notFound('用户不存在');
        }

        $data = $request->post();

        // 如果修改角色，检查角色是否存在
        if (isset($data['role_id'])) {
            $role = AdminRole::find($data['role_id']);
            if (!$role) {
                return Response::error('角色不存在');
            }
        }

        // 如果密码为空，则不更新密码
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        }

        try {
            $user->save($data);
            return Response::success([], '用户更新成功');
        } catch (\Exception $e) {
            return Response::error('用户更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除用户
     */
    public function delete($id)
    {
        $user = AdminUser::find($id);
        if (!$user) {
            return Response::notFound('用户不存在');
        }

        // 不能删除ID为1的超级管理员
        if ((int)$id === 1) {
            return Response::error('不能删除超级管理员');
        }

        try {
            $user->delete();
            return Response::success([], '用户删除成功');
        } catch (\Exception $e) {
            return Response::error('用户删除失败：' . $e->getMessage());
        }
    }

    /**
     * 重置密码
     */
    public function resetPassword(Request $request, $id)
    {
        $user = AdminUser::find($id);
        if (!$user) {
            return Response::notFound('用户不存在');
        }

        $password = $request->post('password');
        if (empty($password)) {
            return Response::error('新密码不能为空');
        }

        try {
            $user->password = $password;
            $user->save();
            return Response::success([], '密码重置成功');
        } catch (\Exception $e) {
            return Response::error('密码重置失败：' . $e->getMessage());
        }
    }
}
