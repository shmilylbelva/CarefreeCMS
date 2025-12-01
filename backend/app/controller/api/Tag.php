<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\common\Logger;
use app\model\Tag as TagModel;
use app\model\Relation;
use app\model\OperationLog;
use app\traits\QueryFilterTrait;
use think\Request;

/**
 * 标签管理控制器
 */
class Tag extends BaseController
{
    use QueryFilterTrait;
    /**
     * 标签列表
     */
    public function index(Request $request)
    {
        // 构建查询 - 禁用自动站点过滤
        $query = TagModel::withoutSiteScope()->with(['site']);

        // 定义过滤条件
        $filters = [
            'name' => ['operator' => 'like'],
            'status' => ['operator' => '='],
            'site_id' => ['operator' => '=', 'field' => 'tags.site_id'],
        ];

        // 定义排序
        $order = ['sort' => 'asc', 'id' => 'desc'];

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
     * 获取所有标签（不分页）
     */
    public function all(Request $request)
    {
        $status = $request->get('status', 1);
        $siteId = $request->get('site_id', '');
        $siteIds = $request->get('site_ids', '');

        $query = TagModel::withoutSiteScope()
            ->order(['sort' => 'asc', 'id' => 'desc']);

        if ($status !== '') {
            $query->where('status', $status);
        }

        // 支持多站点筛选（site_ids 参数优先）
        if ($siteIds !== '') {
            // site_ids 是逗号分隔的字符串，如 "1,2,3"
            $siteIdArray = array_filter(array_map('intval', explode(',', $siteIds)));
            if (!empty($siteIdArray)) {
                $query->whereIn('tags.site_id', $siteIdArray);
            }
        } elseif ($siteId !== '') {
            // 兼容单个 site_id 参数
            $query->where('tags.site_id', $siteId);
        }

        $list = $query->select();

        return Response::success($list->toArray());
    }

    /**
     * 标签详情
     */
    public function read($id)
    {
        $tag = TagModel::withoutSiteScope()->find($id);

        if (!$tag) {
            return Response::notFound('标签不存在');
        }

        return Response::success($tag->toArray());
    }

    /**
     * 创建标签
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['name'])) {
            return Response::error('标签名称不能为空');
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
            $createdTags = [];
            $sourceId = null;

            // 为每个站点创建标签副本
            foreach ($siteIds as $index => $siteId) {
                $tagData = $data;
                $tagData['site_id'] = $siteId;

                // 检查同名标签
                $exists = TagModel::where('name', $tagData['name'])
                    ->where('site_id', $siteId)
                    ->find();
                if ($exists) {
                    throw new \Exception("站点ID {$siteId} 下已存在同名标签");
                }

                // 第一个是主记录，后续记录设置 source_id
                if ($index > 0 && $sourceId) {
                    $tagData['source_id'] = $sourceId;
                }

                $tag = TagModel::create($tagData);

                // 第一个记录作为源记录
                if ($index === 0) {
                    $sourceId = $tag->id;
                }

                $createdTags[] = $tag;
                Logger::create(OperationLog::MODULE_TAG, '标签', $tag->id);
            }

            $message = count($createdTags) > 1
                ? "标签创建成功，已为 " . count($createdTags) . " 个站点创建副本"
                : '标签创建成功';

            return Response::success([
                'id' => $createdTags[0]->id,
                'count' => count($createdTags),
                'ids' => array_map(fn($t) => $t->id, $createdTags)
            ], $message);
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_TAG, OperationLog::ACTION_CREATE, '创建标签失败', false, $e->getMessage());
            return Response::error('标签创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新标签
     */
    public function update(Request $request, $id)
    {
        $tag = TagModel::withoutSiteScope()->find($id);
        if (!$tag) {
            return Response::notFound('标签不存在');
        }

        $postData = $request->post();

        // 只允许更新这些字段
        $allowedFields = ['site_id', 'name', 'slug', 'description', 'color', 'sort', 'status'];
        $data = [];
        foreach ($allowedFields as $field) {
            if (isset($postData[$field])) {
                $data[$field] = $postData[$field];
            }
        }

        // 检查同名标签（跨站点检查）
        if (isset($data['name'])) {
            $exists = TagModel::withoutSiteScope()
                ->where('name', $data['name'])
                ->where('id', '<>', $id)
                ->find();
            if ($exists) {
                return Response::error('标签名称已存在');
            }
        }

        try {
            // 使用Db类直接更新，确保WHERE条件精确
            $affected = \think\facade\Db::name('tags')
                ->where('id', '=', $id)
                ->limit(1)
                ->update($data);

            error_log("更新标签 ID: {$id}, 影响行数: {$affected}");
            error_log("更新数据: " . json_encode($data));

            if ($affected === 0) {
                return Response::error('标签更新失败：未找到该标签或数据未改变');
            }

            Logger::update(OperationLog::MODULE_TAG, '标签', $id);
            return Response::success(['affected' => $affected], '标签更新成功');
        } catch (\Exception $e) {
            error_log("更新标签异常: " . $e->getMessage());
            Logger::log(OperationLog::MODULE_TAG, OperationLog::ACTION_UPDATE, "更新标签失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('标签更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除标签
     */
    public function delete($id)
    {
        $tag = TagModel::withoutSiteScope()->find($id);
        if (!$tag) {
            return Response::notFound('标签不存在');
        }

        // 检查是否有关联文章
        $articleCount = Relation::where('source_type', 'article')
            ->where('target_type', 'tag')
            ->where('target_id', $id)
            ->count();
        if ($articleCount > 0) {
            return Response::error('该标签下有关联文章，无法删除');
        }

        try {
            $tagName = $tag->name;

            // 使用Db类直接执行软删除，确保只删除指定ID的记录
            $affected = \think\facade\Db::name('tags')
                ->where('id', '=', $id)
                ->limit(1)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            if ($affected === 0) {
                return Response::error('标签删除失败：未找到该标签');
            }

            // 清除缓存
            TagModel::clearCacheTag();

            Logger::delete(OperationLog::MODULE_TAG, "标签[{$tagName}]", $id);
            return Response::success([], '标签删除成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_TAG, OperationLog::ACTION_DELETE, "删除标签失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('标签删除失败：' . $e->getMessage());
        }
    }
}
