<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\MediaThumbnailPreset;
use app\service\MediaThumbnailService;
use think\App;
use think\Request;

/**
 * 媒体缩略图管理控制器
 */
class MediaThumbnail extends BaseController
{
    protected $thumbnailService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->thumbnailService = new MediaThumbnailService();
    }

    /**
     * 缩略图预设列表
     */
    public function presets(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);
        $autoGenerate = $request->get('auto_generate', ''); // 筛选自动生成的预设

        $query = MediaThumbnailPreset::order('sort_order', 'asc')
            ->order('id', 'asc');

        if ($autoGenerate !== '') {
            $query->where('is_auto_generate', $autoGenerate ? 1 : 0);
        }

        $list = $query->page($page, $pageSize)->select();
        $total = MediaThumbnailPreset::when($autoGenerate !== '', function ($q) use ($autoGenerate) {
            $q->where('is_auto_generate', $autoGenerate ? 1 : 0);
        })->count();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取单个预设
     */
    public function readPreset($id)
    {
        $preset = MediaThumbnailPreset::find($id);

        if (!$preset) {
            return Response::notFound('预设不存在');
        }

        return Response::success($preset);
    }

    /**
     * 创建缩略图预设
     */
    public function createPreset(Request $request)
    {
        try {
            $name = $request->post('name');

            if (empty($name)) {
                return Response::error('预设名称不能为空');
            }

            // 检查名称是否已存在
            $existing = MediaThumbnailPreset::where('name', $name)->find();

            if ($existing) {
                return Response::error('预设名称已存在');
            }

            $data = [
                'name' => $name,
                'display_name' => $request->post('display_name', $name),
                'width' => $request->post('width'),
                'height' => $request->post('height'),
                'mode' => $request->post('mode', MediaThumbnailPreset::MODE_FIT),
                'quality' => $request->post('quality', 85),
                'format' => $request->post('format'),
                'is_builtin' => 0,
                'is_auto_generate' => $request->post('is_auto_generate', 1),
                'description' => $request->post('description', ''),
            ];

            $preset = MediaThumbnailPreset::create($data);

            return Response::success($preset, '预设创建成功');

        } catch (\Exception $e) {
            return Response::error('预设创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新缩略图预设
     */
    public function updatePreset(Request $request, $id)
    {
        try {
            $preset = MediaThumbnailPreset::find($id);

            if (!$preset) {
                return Response::notFound('预设不存在');
            }

            // 内置预设不允许修改部分字段
            if ($preset->is_builtin) {
                return Response::error('内置预设不允许修改');
            }

            $data = [];

            if ($request->has('name')) {
                $name = $request->post('name');

                // 检查名称是否已被使用
                $existing = MediaThumbnailPreset::where('name', $name)
                    ->where('id', '<>', $id)
                    ->find();

                if ($existing) {
                    return Response::error('预设名称已被使用');
                }

                $data['name'] = $name;
            }

            if ($request->has('display_name')) {
                $data['display_name'] = $request->post('display_name');
            }

            if ($request->has('width')) {
                $data['width'] = $request->post('width');
            }

            if ($request->has('height')) {
                $data['height'] = $request->post('height');
            }

            if ($request->has('mode')) {
                $data['mode'] = $request->post('mode');
            }

            if ($request->has('quality')) {
                $data['quality'] = $request->post('quality');
            }

            if ($request->has('format')) {
                $data['format'] = $request->post('format');
            }

            if ($request->has('is_auto_generate')) {
                $data['is_auto_generate'] = $request->post('is_auto_generate');
            }

            if ($request->has('description')) {
                $data['description'] = $request->post('description');
            }

            // 使用Db更新避免批量更新bug
            $affected = \think\facade\Db::name('media_thumbnail_presets')
                ->where('id', '=', $id)
                ->limit(1)
                ->update($data);

            if ($affected === 0) {
                return Response::error('更新失败：未找到该记录或数据未改变');
            }

            // 重新获取更新后的数据
            $preset = MediaThumbnailPreset::find($id);

            return Response::success($preset, '预设更新成功');

        } catch (\Exception $e) {
            return Response::error('预设更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除缩略图预设
     */
    public function deletePreset($id)
    {
        try {
            $preset = MediaThumbnailPreset::find($id);

            if (!$preset) {
                return Response::notFound('预设不存在');
            }

            // 内置预设不允许删除
            if ($preset->is_builtin) {
                return Response::error('内置预设不允许删除');
            }

            $presetId = $preset->id;

            // 使用Db类直接删除，确保WHERE条件精确
            $affected = \think\facade\Db::name('media_thumbnail_presets')
                ->where('id', '=', $presetId)
                ->limit(1)
                ->delete();

            if ($affected === 0) {
                throw new \Exception('预设删除失败：未找到该预设');
            }

            return Response::success([], '预设删除成功');

        } catch (\Exception $e) {
            return Response::error('预设删除失败：' . $e->getMessage());
        }
    }

    /**
     * 为单个媒体生成缩略图
     */
    public function generate(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $presetName = $request->post('preset_name');

            if (empty($mediaId)) {
                return Response::error('媒体ID不能为空');
            }

            if (empty($presetName)) {
                // 生成所有自动生成的预设
                $results = $this->thumbnailService->generateAutoThumbnails($mediaId);

                return Response::success($results, '缩略图生成完成');
            } else {
                // 生成指定预设
                $thumbnail = $this->thumbnailService->generateThumbnail($mediaId, $presetName);

                return Response::success($thumbnail, '缩略图生成成功');
            }

        } catch (\Exception $e) {
            return Response::error('缩略图生成失败：' . $e->getMessage());
        }
    }

    /**
     * 批量生成缩略图
     */
    public function batchGenerate(Request $request)
    {
        try {
            $mediaIds = $request->post('media_ids', []);
            $presetName = $request->post('preset_name', '');

            if (empty($mediaIds)) {
                return Response::error('媒体ID不能为空');
            }

            $results = [];
            $successCount = 0;
            $failedCount = 0;

            foreach ($mediaIds as $mediaId) {
                try {
                    if (empty($presetName)) {
                        $result = $this->thumbnailService->generateAutoThumbnails($mediaId);
                    } else {
                        $result = $this->thumbnailService->generateThumbnail($mediaId, $presetName);
                    }

                    $results[] = [
                        'media_id' => $mediaId,
                        'success' => true,
                        'result' => $result,
                    ];

                    $successCount++;

                } catch (\Exception $e) {
                    $results[] = [
                        'media_id' => $mediaId,
                        'success' => false,
                        'error' => $e->getMessage(),
                    ];

                    $failedCount++;
                }
            }

            return Response::success([
                'total' => count($mediaIds),
                'success' => $successCount,
                'failed' => $failedCount,
                'results' => $results,
            ], "批量生成完成：成功 {$successCount} 个，失败 {$failedCount} 个");

        } catch (\Exception $e) {
            return Response::error('批量生成失败：' . $e->getMessage());
        }
    }

    /**
     * 重新生成媒体的所有缩略图
     */
    public function regenerate(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');

            if (empty($mediaId)) {
                return Response::error('媒体ID不能为空');
            }

            $results = $this->thumbnailService->regenerateAllThumbnails($mediaId);

            return Response::success($results, '缩略图重新生成完成');

        } catch (\Exception $e) {
            return Response::error('缩略图重新生成失败：' . $e->getMessage());
        }
    }

    /**
     * 删除媒体的所有缩略图
     */
    public function deleteAll(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');

            if (empty($mediaId)) {
                return Response::error('媒体ID不能为空');
            }

            $count = $this->thumbnailService->deleteAllThumbnails($mediaId);

            return Response::success(['count' => $count], "成功删除 {$count} 个缩略图");

        } catch (\Exception $e) {
            return Response::error('删除缩略图失败：' . $e->getMessage());
        }
    }
}
