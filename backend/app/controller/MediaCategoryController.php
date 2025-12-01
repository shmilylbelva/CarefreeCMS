<?php
declare(strict_types=1);

namespace app\controller;

use app\service\MediaCategoryService;
use think\Request;

/**
 * 媒体分类控制器
 */
class MediaCategoryController extends BaseController
{
    protected MediaCategoryService $service;

    public function __construct(MediaCategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * 获取分类列表
     */
    public function index(Request $request)
    {
        try {
            $params = $request->get();
            $result = $this->service->getList($params);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取分类树
     */
    public function tree(Request $request)
    {
        try {
            $parentId = (int)$request->get('parent_id', 0);
            $onlyVisible = (bool)$request->get('only_visible', false);
            $tree = $this->service->getTree($parentId, $onlyVisible);
            return $this->success($tree);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取分类详情
     */
    public function read(int $id)
    {
        try {
            $category = $this->service->getDetail($id);
            if (!$category) {
                return $this->error('分类不存在', 404);
            }
            return $this->success($category);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 创建分类
     */
    public function save(Request $request)
    {
        try {
            $data = $request->post();

            // 验证
            $this->validate($data, [
                'name' => 'require|max:100',
                'parent_id' => 'integer|egt:0',
                'sort_order' => 'integer|egt:0',
            ]);

            $category = $this->service->create($data);
            return $this->success($category, '创建成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 更新分类
     */
    public function update(Request $request, int $id)
    {
        try {
            $data = $request->put();

            // 验证
            $this->validate($data, [
                'name' => 'max:100',
                'parent_id' => 'integer|egt:0',
                'sort_order' => 'integer|egt:0',
            ]);

            $category = $this->service->update($id, $data);
            return $this->success($category, '更新成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 删除分类
     */
    public function delete(Request $request, int $id)
    {
        try {
            $force = (bool)$request->post('force', false);
            $this->service->delete($id, $force);
            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 批量删除
     */
    public function batchDelete(Request $request)
    {
        try {
            $ids = $request->post('ids', []);
            $force = (bool)$request->post('force', false);

            if (empty($ids)) {
                return $this->error('请选择要删除的分类');
            }

            $result = $this->service->batchDelete($ids, $force);
            return $this->success($result, "成功删除 {$result['success']} 个分类");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 移动分类
     */
    public function move(Request $request, int $id)
    {
        try {
            $targetParentId = (int)$request->post('target_parent_id', 0);
            $category = $this->service->move($id, $targetParentId);
            return $this->success($category, '移动成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 批量排序
     */
    public function sort(Request $request)
    {
        try {
            $sorts = $request->post('sorts', []);

            if (empty($sorts)) {
                return $this->error('请提供排序数据');
            }

            $this->service->sort($sorts);
            return $this->success(null, '排序成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取分类路径
     */
    public function path(int $id)
    {
        try {
            $path = $this->service->getPath($id);
            return $this->success($path);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 设置媒体分类
     */
    public function setMedia(Request $request)
    {
        try {
            $mediaId = (int)$request->post('media_id');
            $categoryId = (int)$request->post('category_id');

            if (!$mediaId) {
                return $this->error('请提供媒体ID');
            }

            $this->service->setMediaCategory($mediaId, $categoryId);
            return $this->success(null, '设置成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 批量设置分类
     */
    public function batchSetMedia(Request $request)
    {
        try {
            $mediaIds = $request->post('media_ids', []);
            $categoryId = (int)$request->post('category_id');

            if (empty($mediaIds)) {
                return $this->error('请选择媒体文件');
            }

            $count = $this->service->batchSetCategory($mediaIds, $categoryId);
            return $this->success(['count' => $count], "成功设置 {$count} 个媒体的分类");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取分类下的媒体
     */
    public function media(Request $request, int $id)
    {
        try {
            $params = $request->get();
            $result = $this->service->getMediaByCategory($id, $params);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 合并分类
     */
    public function merge(Request $request)
    {
        try {
            $sourceId = (int)$request->post('source_id');
            $targetId = (int)$request->post('target_id');

            if (!$sourceId || !$targetId) {
                return $this->error('请提供源分类和目标分类');
            }

            $this->service->merge($sourceId, $targetId);
            return $this->success(null, '合并成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 重新计算媒体数量
     */
    public function recalculate()
    {
        try {
            $count = $this->service->recalculateAllCounts();
            return $this->success(['count' => $count], "已重新计算 {$count} 个分类的媒体数量");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
