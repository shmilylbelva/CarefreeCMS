<?php
declare(strict_types=1);

namespace app\controller;

use app\service\MediaTagService;
use think\Request;

/**
 * 媒体标签控制器
 */
class MediaTagController extends BaseController
{
    protected MediaTagService $service;

    public function __construct(MediaTagService $service)
    {
        $this->service = $service;
    }

    /**
     * 获取标签列表
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
     * 获取热门标签
     */
    public function popular(Request $request)
    {
        try {
            $limit = (int)$request->get('limit', 20);
            $tags = $this->service->getPopular($limit);
            return $this->success($tags);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取标签云
     */
    public function cloud(Request $request)
    {
        try {
            $limit = (int)$request->get('limit', 50);
            $cloud = $this->service->getTagCloud($limit);
            return $this->success($cloud);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 搜索标签
     */
    public function search(Request $request)
    {
        try {
            $keyword = $request->get('keyword', '');
            $limit = (int)$request->get('limit', 10);

            if (empty($keyword)) {
                return $this->success([]);
            }

            $tags = $this->service->search($keyword, $limit);
            return $this->success($tags);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取标签详情
     */
    public function read(int $id)
    {
        try {
            $tag = $this->service->getDetail($id);
            if (!$tag) {
                return $this->error('标签不存在', 404);
            }
            return $this->success($tag);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 创建标签
     */
    public function save(Request $request)
    {
        try {
            $data = $request->post();

            // 验证
            $this->validate($data, [
                'name' => 'require|max:50',
                'slug' => 'max:100',
                'color' => 'max:20',
            ]);

            $tag = $this->service->create($data);
            return $this->success($tag, '创建成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 更新标签
     */
    public function update(Request $request, int $id)
    {
        try {
            $data = $request->put();

            // 验证
            $this->validate($data, [
                'name' => 'max:50',
                'slug' => 'max:100',
                'color' => 'max:20',
            ]);

            $tag = $this->service->update($id, $data);
            return $this->success($tag, '更新成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 删除标签
     */
    public function delete(int $id)
    {
        try {
            $this->service->delete($id);
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

            if (empty($ids)) {
                return $this->error('请选择要删除的标签');
            }

            $result = $this->service->batchDelete($ids);
            return $this->success($result, "成功删除 {$result['success']} 个标签");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 设置媒体标签
     */
    public function setMedia(Request $request)
    {
        try {
            $mediaId = (int)$request->post('media_id');
            $tagIds = $request->post('tag_ids', []);

            if (!$mediaId) {
                return $this->error('请提供媒体ID');
            }

            $this->service->setMediaTags($mediaId, $tagIds);
            return $this->success(null, '设置成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 按名称添加标签
     */
    public function addByNames(Request $request)
    {
        try {
            $mediaId = (int)$request->post('media_id');
            $tagNames = $request->post('tag_names', []);

            if (!$mediaId) {
                return $this->error('请提供媒体ID');
            }

            if (empty($tagNames)) {
                return $this->error('请提供标签名称');
            }

            $tags = $this->service->addTagsByNames($mediaId, $tagNames);
            return $this->success($tags, '添加成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 批量设置标签
     */
    public function batchSetMedia(Request $request)
    {
        try {
            $mediaIds = $request->post('media_ids', []);
            $tagIds = $request->post('tag_ids', []);

            if (empty($mediaIds)) {
                return $this->error('请选择媒体文件');
            }

            $count = $this->service->batchSetTags($mediaIds, $tagIds);
            return $this->success(['count' => $count], "成功设置 {$count} 个媒体的标签");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取媒体的标签
     */
    public function mediaTags(int $mediaId)
    {
        try {
            $tags = $this->service->getMediaTags($mediaId);
            return $this->success($tags);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 获取标签下的媒体
     */
    public function media(Request $request, int $id)
    {
        try {
            $params = $request->get();
            $result = $this->service->getMediaByTag($id, $params);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 合并标签
     */
    public function merge(Request $request)
    {
        try {
            $sourceId = (int)$request->post('source_id');
            $targetId = (int)$request->post('target_id');

            if (!$sourceId || !$targetId) {
                return $this->error('请提供源标签和目标标签');
            }

            $this->service->merge($sourceId, $targetId);
            return $this->success(null, '合并成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 清理未使用的标签
     */
    public function cleanUnused()
    {
        try {
            $count = $this->service->cleanUnused();
            return $this->success(['count' => $count], "已清理 {$count} 个未使用的标签");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 重新计算使用次数
     */
    public function recalculate()
    {
        try {
            $count = $this->service->recalculateAllCounts();
            return $this->success(['count' => $count], "已重新计算 {$count} 个标签的使用次数");
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
