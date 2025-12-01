<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\MediaWatermarkPreset;
use app\model\MediaWatermarkLog;
use app\service\MediaWatermarkService;
use think\App;
use think\Request;

/**
 * 媒体水印管理控制器
 */
class MediaWatermark extends BaseController
{
    protected $watermarkService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->watermarkService = new MediaWatermarkService();
    }

    /**
     * 水印预设列表
     */
    public function presets(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);
        $type = $request->get('type', '');
        $isActive = $request->get('is_active', '');

        $query = MediaWatermarkPreset::order('id', 'desc');

        if (!empty($type)) {
            $query->where('type', $type);
        }

        if ($isActive !== '') {
            $query->where('is_active', $isActive ? 1 : 0);
        }

        $list = $query->page($page, $pageSize)->select();
        $total = MediaWatermarkPreset::when(!empty($type), function ($q) use ($type) {
            $q->where('type', $type);
        })
        ->when($isActive !== '', function ($q) use ($isActive) {
            $q->where('is_active', $isActive ? 1 : 0);
        })
        ->count();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取单个预设
     */
    public function readPreset($id)
    {
        $preset = MediaWatermarkPreset::find($id);

        if (!$preset) {
            return Response::notFound('预设不存在');
        }

        return Response::success($preset);
    }

    /**
     * 创建水印预设
     */
    public function createPreset(Request $request)
    {
        try {
            $type = $request->post('type');

            if (empty($type)) {
                return Response::error('水印类型不能为空');
            }

            $data = [
                'name' => $request->post('name'),
                'type' => $type,
                'text_content' => $request->post('text_content'),
                'text_font' => $request->post('text_font'),
                'text_size' => $request->post('text_size'),
                'text_color' => $request->post('text_color'),
                'image_path' => $request->post('image_path'),
                'position' => $request->post('position', MediaWatermarkPreset::POS_BOTTOM_RIGHT),
                'offset_x' => $request->post('offset_x', 10),
                'offset_y' => $request->post('offset_y', 10),
                'opacity' => $request->post('opacity', 50),
                'scale' => $request->post('scale', 100),
                'tile_spacing' => $request->post('tile_spacing'),
                'is_default' => $request->post('is_default', 0),
                'is_active' => $request->post('is_active', 1),
            ];

            // 如果设置为默认，取消其他默认预设
            if ($data['is_default']) {
                MediaWatermarkPreset::where('is_default', 1)->update(['is_default' => 0]);
            }

            $preset = MediaWatermarkPreset::create($data);

            return Response::success($preset, '预设创建成功');

        } catch (\Exception $e) {
            return Response::error('预设创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新水印预设
     */
    public function updatePreset(Request $request, $id)
    {
        try {
            $preset = MediaWatermarkPreset::find($id);

            if (!$preset) {
                return Response::notFound('预设不存在');
            }

            $data = [];

            if ($request->has('name')) {
                $data['name'] = $request->post('name');
            }

            if ($request->has('type')) {
                $data['type'] = $request->post('type');
            }

            if ($request->has('text_content')) {
                $data['text_content'] = $request->post('text_content');
            }

            if ($request->has('text_font')) {
                $data['text_font'] = $request->post('text_font');
            }

            if ($request->has('text_size')) {
                $data['text_size'] = $request->post('text_size');
            }

            if ($request->has('text_color')) {
                $data['text_color'] = $request->post('text_color');
            }

            if ($request->has('image_path')) {
                $data['image_path'] = $request->post('image_path');
            }

            if ($request->has('position')) {
                $data['position'] = $request->post('position');
            }

            if ($request->has('offset_x')) {
                $data['offset_x'] = $request->post('offset_x');
            }

            if ($request->has('offset_y')) {
                $data['offset_y'] = $request->post('offset_y');
            }

            if ($request->has('opacity')) {
                $data['opacity'] = $request->post('opacity');
            }

            if ($request->has('scale')) {
                $data['scale'] = $request->post('scale');
            }

            if ($request->has('tile_spacing')) {
                $data['tile_spacing'] = $request->post('tile_spacing');
            }

            if ($request->has('is_default')) {
                $data['is_default'] = $request->post('is_default');

                // 如果设置为默认，取消其他默认预设
                if ($data['is_default']) {
                    MediaWatermarkPreset::where('id', '<>', $id)
                        ->where('is_default', 1)
                        ->update(['is_default' => 0]);
                }
            }

            if ($request->has('is_active')) {
                $data['is_active'] = $request->post('is_active');
            }

            // 使用Db更新避免批量更新bug
            $affected = \think\facade\Db::name('media_watermark_presets')
                ->where('id', '=', $id)
                ->limit(1)
                ->update($data);

            if ($affected === 0) {
                return Response::error('更新失败：未找到该记录或数据未改变');
            }

            // 重新获取更新后的数据
            $preset = MediaWatermarkPreset::find($id);

            return Response::success($preset, '预设更新成功');

        } catch (\Exception $e) {
            return Response::error('预设更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除水印预设
     */
    public function deletePreset($id)
    {
        try {
            $preset = MediaWatermarkPreset::find($id);

            if (!$preset) {
                return Response::notFound('预设不存在');
            }

            $presetId = $preset->id;

            // 使用Db类直接删除，确保WHERE条件精确
            $affected = \think\facade\Db::name('media_watermark_presets')
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
     * 添加水印
     */
    public function add(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $presetId = $request->post('preset_id');
            $customConfig = $request->post('custom_config', []);

            if (empty($mediaId)) {
                return Response::error('媒体ID不能为空');
            }

            // 添加水印
            $outputFile = $this->watermarkService->addWatermark(
                $mediaId,
                $presetId,
                $customConfig
            );

            return Response::success([
                'file_id' => $outputFile->id,
                'file_url' => $outputFile->file_url,
                'file_path' => $outputFile->file_path,
                'file_size' => $outputFile->file_size,
            ], '水印添加成功');

        } catch (\Exception $e) {
            return Response::error('水印添加失败：' . $e->getMessage());
        }
    }

    /**
     * 批量添加水印
     */
    public function batchAdd(Request $request)
    {
        try {
            $mediaIds = $request->post('media_ids', []);
            $presetId = $request->post('preset_id');

            if (empty($mediaIds)) {
                return Response::error('媒体ID不能为空');
            }

            $results = [];
            $successCount = 0;
            $failedCount = 0;

            foreach ($mediaIds as $mediaId) {
                try {
                    $outputFile = $this->watermarkService->addWatermark($mediaId, $presetId);

                    $results[] = [
                        'media_id' => $mediaId,
                        'success' => true,
                        'file' => [
                            'id' => $outputFile->id,
                            'url' => $outputFile->file_url,
                        ],
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
            ], "批量添加完成：成功 {$successCount} 个，失败 {$failedCount} 个");

        } catch (\Exception $e) {
            return Response::error('批量添加失败：' . $e->getMessage());
        }
    }

    /**
     * 水印处理日志
     */
    public function logs(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);
        $mediaId = $request->get('media_id', '');
        $status = $request->get('status', '');

        $query = MediaWatermarkLog::with(['media.file', 'preset', 'user'])
            ->order('created_at', 'desc');

        if (!empty($mediaId)) {
            $query->where('media_id', $mediaId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $list = $query->page($page, $pageSize)->select();

        // 处理返回数据，确保包含正确的图片信息
        $data = $list->map(function ($log) {
            $item = $log->toArray();

            // 确保 media 包含 file_url 和 file_name
            if (isset($item['media']) && $log->media && $log->media->file) {
                $item['media']['file_url'] = $log->media->file_url;
                $item['media']['file_name'] = $log->media->file->file_name;
            }

            return $item;
        });

        $total = MediaWatermarkLog::when(!empty($mediaId), function ($q) use ($mediaId) {
            $q->where('media_id', $mediaId);
        })
        ->when(!empty($status), function ($q) use ($status) {
            $q->where('status', $status);
        })
        ->count();

        return Response::paginate($data->toArray(), $total, $page, $pageSize);
    }
}
