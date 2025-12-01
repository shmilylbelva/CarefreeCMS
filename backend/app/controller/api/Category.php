<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\common\Logger;
use app\model\Category as CategoryModel;
use app\model\OperationLog;
use app\traits\QueryFilterTrait;
use think\Request;

/**
 * 分类管理控制器
 */
class Category extends BaseController
{
    use QueryFilterTrait;
    /**
     * 分类列表
     */
    public function index(Request $request)
    {
        // 构建查询 - 默认查询所有站点
        $query = CategoryModel::withoutSiteScope()->with(['parent', 'site']);

        // 定义过滤条件
        $filters = [
            'name' => ['operator' => 'like'],
            'parent_id' => ['operator' => '='],
            'status' => ['operator' => '='],
            'site_id' => [
                'operator' => '=',
                'field' => 'categories.site_id',
                'callback' => function($q, $value) {
                    // 跳过 'all' 值
                    if ($value === 'all') {
                        return $q;
                    }
                    return $q->where('categories.site_id', (int)$value);
                }
            ],
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
            $request->get('page_size', 50)
        );
    }

    /**
     * 分类树形结构
     */
    public function tree(Request $request)
    {
        $status = $request->get('status', 1);
        $siteId = $request->get('site_id', '');
        $siteIds = $request->get('site_ids', '');

        // 构建缓存键参数
        $cacheParams = [
            'status' => $status,
            'site_id' => $siteId,
            'site_ids' => $siteIds,
        ];

        // 获取所有分类 - 禁用自动站点过滤
        $query = CategoryModel::withoutSiteScope()
            ->with(['site'])
            ->order(['sort' => 'asc', 'id' => 'asc']);

        if ($status !== '') {
            $query->where('status', $status);
        }

        // 支持多站点筛选（site_ids 参数优先）
        if ($siteIds !== '') {
            // site_ids 是逗号分隔的字符串，如 "1,2,3"
            $siteIdArray = array_filter(array_map('intval', explode(',', $siteIds)));
            if (!empty($siteIdArray)) {
                $query->whereIn('categories.site_id', $siteIdArray);
            }
        } elseif ($siteId !== '') {
            // 兼容单个 site_id 参数
            $query->where('categories.site_id', $siteId);
        }

        $categories = $query->select()->toArray();

        // 构建树形结构
        $tree = $this->buildTree($categories);

        return Response::success($tree);
    }

    /**
     * 构建树形结构
     */
    private function buildTree($items, $parentId = 0)
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 分类详情
     */
    public function read($id)
    {
        $category = CategoryModel::withoutSiteScope()->with(['parent'])->find($id);

        if (!$category) {
            return Response::notFound('分类不存在');
        }

        return Response::success($category->toArray());
    }

    /**
     * 创建分类
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['name'])) {
            return Response::error('分类名称不能为空');
        }

        // 检查父分类是否存在
        if (isset($data['parent_id']) && $data['parent_id'] > 0) {
            $parent = CategoryModel::withoutSiteScope()->find($data['parent_id']);
            if (!$parent) {
                return Response::error('父分类不存在');
            }
        }

        // 处理parent_id：null转换为0（顶级分类）
        if (!isset($data['parent_id']) || $data['parent_id'] === null || $data['parent_id'] === '') {
            $data['parent_id'] = 0;
        }

        // 多站点支持：获取站点IDs（数组或单个值）
        $siteIds = [];
        if (isset($data['site_ids']) && is_array($data['site_ids']) && !empty($data['site_ids'])) {
            $siteIds = $data['site_ids'];
            unset($data['site_ids']);
            unset($data['site_id']);
        } elseif (isset($data['site_id'])) {
            $siteIds = [$data['site_id']];
        } else {
            $siteIds = [1];
        }

        try {
            $createdCategories = [];
            $sourceId = null;

            // 为每个站点创建分类副本
            foreach ($siteIds as $index => $siteId) {
                $categoryData = $data;
                $categoryData['site_id'] = $siteId;

                // 检查同名分类
                $exists = CategoryModel::where('name', $categoryData['name'])
                    ->where('parent_id', $categoryData['parent_id'])
                    ->where('site_id', $siteId)
                    ->find();
                if ($exists) {
                    throw new \Exception("站点ID {$siteId} 下已存在同名分类");
                }

                // 第一个是主记录，后续记录设置 source_id
                if ($index > 0 && $sourceId) {
                    $categoryData['source_id'] = $sourceId;
                }

                $category = CategoryModel::create($categoryData);

                // 第一个记录作为源记录
                if ($index === 0) {
                    $sourceId = $category->id;
                }

                $createdCategories[] = $category;
                Logger::create(OperationLog::MODULE_CATEGORY, '分类', $category->id);
            }

            $message = count($createdCategories) > 1
                ? "分类创建成功，已为 " . count($createdCategories) . " 个站点创建副本"
                : '分类创建成功';

            return Response::success([
                'id' => $createdCategories[0]->id,
                'count' => count($createdCategories),
                'ids' => array_map(fn($c) => $c->id, $createdCategories)
            ], $message);
        } catch (\Exception $e) {
            Logger::log(
                OperationLog::MODULE_CATEGORY,
                OperationLog::ACTION_CREATE,
                '创建分类失败',
                false,
                $e->getMessage()
            );
            return Response::error('分类创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新分类
     */
    public function update(Request $request, $id)
    {
        $category = CategoryModel::withoutSiteScope()->find($id);
        if (!$category) {
            return Response::notFound('分类不存在');
        }

        $postData = $request->post();

        // 只允许更新这些字段，过滤掉其他字段（如children、articles_count等）
        $allowedFields = ['site_id', 'parent_id', 'name', 'slug', 'sort', 'template', 'description', 'status'];
        $data = [];
        foreach ($allowedFields as $field) {
            if (isset($postData[$field])) {
                $data[$field] = $postData[$field];
            }
        }

        // 验证父分类
        if (isset($data['parent_id']) && $data['parent_id'] > 0) {
            // 不能设置自己为父分类
            if ($data['parent_id'] == $id) {
                return Response::error('不能将自己设置为父分类');
            }

            // 检查父分类是否存在
            $parent = CategoryModel::withoutSiteScope()->find($data['parent_id']);
            if (!$parent) {
                return Response::error('父分类不存在');
            }

            // 防止循环引用（简单检查，父分类的父分类不能是当前分类）
            if ($parent->parent_id == $id) {
                return Response::error('不能创建循环引用的分类结构');
            }
        }

        // 检查同名分类（跨站点检查）
        if (isset($data['name'])) {
            $exists = CategoryModel::withoutSiteScope()
                ->where('name', $data['name'])
                ->where('parent_id', $data['parent_id'] ?? $category->parent_id)
                ->where('id', '<>', $id)
                ->find();
            if ($exists) {
                return Response::error('同名分类已存在');
            }
        }

        try {
            // 使用Db类直接更新，绕过模型的复杂逻辑，确保WHERE条件精确
            $affected = \think\facade\Db::name('categories')
                ->where('id', '=', $id)
                ->limit(1)  // 额外保险：限制只更新1条
                ->update($data);

            // 记录日志以便调试
            error_log("更新分类 ID: {$id}, 影响行数: {$affected}");
            error_log("更新数据: " . json_encode($data));

            if ($affected === 0) {
                return Response::error('分类更新失败：未找到该分类或数据未改变');
            }

            Logger::update(OperationLog::MODULE_CATEGORY, '分类', $id);
            return Response::success(['affected' => $affected], '分类更新成功');
        } catch (\Exception $e) {
            error_log("更新异常: " . $e->getMessage());
            Logger::log(
                OperationLog::MODULE_CATEGORY,
                OperationLog::ACTION_UPDATE,
                "更新分类失败 (ID: {$id})",
                false,
                $e->getMessage()
            );
            return Response::error('分类更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除分类
     */
    public function delete($id)
    {
        $category = CategoryModel::withoutSiteScope()->find($id);
        if (!$category) {
            return Response::notFound('分类不存在');
        }

        // 检查是否有子分类
        $children = CategoryModel::withoutSiteScope()->where('parent_id', $id)->count();
        if ($children > 0) {
            return Response::error('该分类下有子分类，无法删除');
        }

        // 检查是否有关联文章
        $articleCount = $category->articles()->count();
        if ($articleCount > 0) {
            return Response::error('该分类下有文章，无法删除');
        }

        try {
            $categoryName = $category->name;

            // 使用Db类直接执行软删除，确保只删除指定ID的记录
            $affected = \think\facade\Db::name('categories')
                ->where('id', '=', $id)
                ->limit(1)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            if ($affected === 0) {
                return Response::error('分类删除失败：未找到该分类');
            }

            // 清除缓存
            CategoryModel::clearCacheTag();

            Logger::delete(OperationLog::MODULE_CATEGORY, "分类[{$categoryName}]", $id);
            return Response::success([], '分类删除成功');
        } catch (\Exception $e) {
            Logger::log(
                OperationLog::MODULE_CATEGORY,
                OperationLog::ACTION_DELETE,
                "删除分类失败 (ID: {$id})",
                false,
                $e->getMessage()
            );
            return Response::error('分类删除失败：' . $e->getMessage());
        }
    }
}
