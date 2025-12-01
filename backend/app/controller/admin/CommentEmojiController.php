<?php

namespace app\controller\admin;

use app\BaseController;
use app\common\Response;
use app\model\CommentEmoji;
use think\Request;

/**
 * 评论表情管理控制器（后台）
 */
class CommentEmojiController extends BaseController
{
    /**
     * 获取表情列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 50);
        $category = $request->get('category', '');
        $isEnabled = $request->get('is_enabled', '');
        $keyword = $request->get('keyword', '');

        $query = CommentEmoji::order('sort', 'asc')->order('id', 'asc');

        // 分类筛选
        if (!empty($category)) {
            $query->where('category', $category);
        }

        // 启用状态筛选
        if ($isEnabled !== '') {
            $query->where('is_enabled', $isEnabled);
        }

        // 关键词搜索
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                  ->whereOr('code', 'like', '%' . $keyword . '%');
            });
        }

        $emojis = $query->paginate([
            'list_rows' => $limit,
            'page' => $page
        ]);

        return Response::success($emojis);
    }

    /**
     * 获取所有分类
     */
    public function categories(Request $request)
    {
        $categories = CommentEmoji::getCategories();
        return Response::success($categories);
    }

    /**
     * 获取表情详情
     */
    public function read(Request $request, $id)
    {
        $emoji = CommentEmoji::find($id);

        if (!$emoji) {
            return Response::notFound('表情不存在');
        }

        return Response::success($emoji);
    }

    /**
     * 创建表情
     */
    public function save(Request $request)
    {
        $data = $request->only([
            'name',
            'code',
            'unicode',
            'image_url',
            'category',
            'sort',
            'is_enabled'
        ]);

        // 验证必填字段
        if (empty($data['name']) || empty($data['code'])) {
            return Response::error('表情名称和代码不能为空');
        }

        // 检查代码是否已存在
        $existing = CommentEmoji::where('code', $data['code'])->find();
        if ($existing) {
            return Response::error('表情代码已存在');
        }

        // 必须提供 unicode 或 image_url 其中之一
        if (empty($data['unicode']) && empty($data['image_url'])) {
            return Response::error('必须提供Unicode字符或图片URL');
        }

        // 设置默认值
        $data['category'] = $data['category'] ?? 'default';
        $data['sort'] = $data['sort'] ?? 0;
        $data['is_enabled'] = $data['is_enabled'] ?? 1;
        $data['use_count'] = 0;

        $emoji = CommentEmoji::create($data);

        if ($emoji) {
            return Response::success($emoji, '创建成功');
        }

        return Response::error('创建失败');
    }

    /**
     * 更新表情
     */
    public function update(Request $request, $id)
    {
        $emoji = CommentEmoji::find($id);
        if (!$emoji) {
            return Response::notFound('表情不存在');
        }

        $data = $request->only([
            'name',
            'code',
            'unicode',
            'image_url',
            'category',
            'sort',
            'is_enabled'
        ]);

        // 验证必填字段
        if (isset($data['name']) && empty($data['name'])) {
            return Response::error('表情名称不能为空');
        }

        if (isset($data['code']) && empty($data['code'])) {
            return Response::error('表情代码不能为空');
        }

        // 如果修改了代码，检查是否已存在
        if (isset($data['code']) && $data['code'] != $emoji->code) {
            $existing = CommentEmoji::where('code', $data['code'])->find();
            if ($existing) {
                return Response::error('表情代码已存在');
            }
        }

        // 更新数据
        foreach ($data as $key => $value) {
            $emoji->$key = $value;
        }

        if ($emoji->save()) {
            return Response::success($emoji, '更新成功');
        }

        return Response::error('更新失败');
    }

    /**
     * 删除表情
     */
    public function delete(Request $request, $id)
    {
        $emoji = CommentEmoji::find($id);
        if (!$emoji) {
            return Response::notFound('表情不存在');
        }

        if ($emoji->delete()) {
            return Response::success([], '删除成功');
        }

        return Response::error('删除失败');
    }

    /**
     * 批量删除表情
     */
    public function batchDelete(Request $request)
    {
        $ids = $request->param('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要删除的表情');
        }

        $count = CommentEmoji::whereIn('id', $ids)->delete();

        return Response::success([
            'total' => count($ids),
            'success' => $count
        ], "成功删除 {$count} 个表情");
    }

    /**
     * 批量启用/禁用表情
     */
    public function batchToggle(Request $request)
    {
        $ids = $request->param('ids', []);
        $isEnabled = $request->param('is_enabled', 1);

        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要操作的表情');
        }

        $count = CommentEmoji::whereIn('id', $ids)->update(['is_enabled' => $isEnabled]);

        $action = $isEnabled ? '启用' : '禁用';
        return Response::success([
            'total' => count($ids),
            'success' => $count
        ], "成功{$action} {$count} 个表情");
    }

    /**
     * 更新排序
     */
    public function updateSort(Request $request, $id)
    {
        $sort = $request->param('sort', 0);

        $emoji = CommentEmoji::find($id);
        if (!$emoji) {
            return Response::notFound('表情不存在');
        }

        $emoji->sort = $sort;
        if ($emoji->save()) {
            return Response::success($emoji, '排序更新成功');
        }

        return Response::error('更新失败');
    }

    /**
     * 批量导入表情
     */
    public function batchImport(Request $request)
    {
        $emojis = $request->param('emojis', []);

        if (empty($emojis) || !is_array($emojis)) {
            return Response::error('请提供表情数据');
        }

        $successCount = 0;
        $errors = [];

        foreach ($emojis as $index => $data) {
            // 验证必填字段
            if (empty($data['name']) || empty($data['code'])) {
                $errors[] = "第 {$index} 项：名称和代码不能为空";
                continue;
            }

            // 检查代码是否已存在
            $existing = CommentEmoji::where('code', $data['code'])->find();
            if ($existing) {
                $errors[] = "第 {$index} 项：代码 {$data['code']} 已存在";
                continue;
            }

            // 必须提供 unicode 或 image_url 其中之一
            if (empty($data['unicode']) && empty($data['image_url'])) {
                $errors[] = "第 {$index} 项：必须提供Unicode字符或图片URL";
                continue;
            }

            // 设置默认值
            $data['category'] = $data['category'] ?? 'default';
            $data['sort'] = $data['sort'] ?? 0;
            $data['is_enabled'] = $data['is_enabled'] ?? 1;
            $data['use_count'] = 0;

            try {
                CommentEmoji::create($data);
                $successCount++;
            } catch (\Exception $e) {
                $errors[] = "第 {$index} 项：{$e->getMessage()}";
            }
        }

        return Response::success([
            'total' => count($emojis),
            'success' => $successCount,
            'errors' => $errors
        ], "成功导入 {$successCount} 个表情");
    }

    /**
     * 获取热门表情（使用次数最多）
     */
    public function hotEmojis(Request $request)
    {
        $limit = $request->get('limit', 20);
        $emojis = CommentEmoji::getHotEmojis($limit);
        return Response::success($emojis);
    }

    /**
     * 重置使用次数
     */
    public function resetUseCount(Request $request, $id)
    {
        $emoji = CommentEmoji::find($id);
        if (!$emoji) {
            return Response::notFound('表情不存在');
        }

        $emoji->use_count = 0;
        if ($emoji->save()) {
            return Response::success($emoji, '使用次数已重置');
        }

        return Response::error('重置失败');
    }

    /**
     * 批量重置使用次数
     */
    public function batchResetUseCount(Request $request)
    {
        $ids = $request->param('ids', []);

        if (empty($ids) || !is_array($ids)) {
            // 如果没有提供IDs，重置所有表情的使用次数
            $count = CommentEmoji::where('id', '>', 0)->update(['use_count' => 0]);
        } else {
            $count = CommentEmoji::whereIn('id', $ids)->update(['use_count' => 0]);
        }

        return Response::success([
            'success' => $count
        ], "成功重置 {$count} 个表情的使用次数");
    }

    /**
     * 获取表情库统计
     */
    public function statistics(Request $request)
    {
        $stats = [
            'total' => CommentEmoji::count(),
            'enabled' => CommentEmoji::where('is_enabled', 1)->count(),
            'disabled' => CommentEmoji::where('is_enabled', 0)->count(),
            'categories' => CommentEmoji::group('category')->count(),
            'total_use_count' => CommentEmoji::sum('use_count'),
            'avg_use_count' => CommentEmoji::avg('use_count'),
        ];

        return Response::success($stats);
    }

    /**
     * 根据分类获取表情（不分页）
     */
    public function getByCategory(Request $request)
    {
        $category = $request->get('category', '');
        $emojis = CommentEmoji::getEnabled($category);
        return Response::success($emojis);
    }
}
