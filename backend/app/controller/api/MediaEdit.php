<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\MediaEditHistory;
use app\service\MediaEditService;
use think\App;
use think\Request;

/**
 * 媒体编辑控制器
 */
class MediaEdit extends BaseController
{
    protected $editService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->editService = new MediaEditService();
    }

    /**
     * 调整大小
     */
    public function resize(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $width = $request->post('width');
            $height = $request->post('height');
            $mode = $request->post('mode', 'fit');

            if (empty($mediaId) || empty($width) || empty($height)) {
                return Response::error('参数错误');
            }

            $file = $this->editService->resize($mediaId, $width, $height, $mode);

            return Response::success([
                'file_id' => $file->id,
                'file_url' => $file->file_url,
                'width' => $file->width,
                'height' => $file->height,
            ], '调整大小成功');

        } catch (\Exception $e) {
            return Response::error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 裁剪
     */
    public function crop(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $x = $request->post('x');
            $y = $request->post('y');
            $width = $request->post('width');
            $height = $request->post('height');

            if (empty($mediaId) || !isset($x) || !isset($y) || empty($width) || empty($height)) {
                return Response::error('参数错误');
            }

            $file = $this->editService->crop($mediaId, $x, $y, $width, $height);

            return Response::success([
                'file_id' => $file->id,
                'file_url' => $file->file_url,
                'width' => $file->width,
                'height' => $file->height,
            ], '裁剪成功');

        } catch (\Exception $e) {
            return Response::error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 旋转
     */
    public function rotate(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $angle = $request->post('angle');
            $bgColor = $request->post('bg_color', '#FFFFFF');

            if (empty($mediaId) || !isset($angle)) {
                return Response::error('参数错误');
            }

            $file = $this->editService->rotate($mediaId, $angle, $bgColor);

            return Response::success([
                'file_id' => $file->id,
                'file_url' => $file->file_url,
            ], '旋转成功');

        } catch (\Exception $e) {
            return Response::error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 翻转
     */
    public function flip(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $direction = $request->post('direction', 'horizontal');

            if (empty($mediaId)) {
                return Response::error('参数错误');
            }

            $file = $this->editService->flip($mediaId, $direction);

            return Response::success([
                'file_id' => $file->id,
                'file_url' => $file->file_url,
            ], '翻转成功');

        } catch (\Exception $e) {
            return Response::error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 调整亮度
     */
    public function brightness(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $level = $request->post('level');

            if (empty($mediaId) || !isset($level)) {
                return Response::error('参数错误');
            }

            $file = $this->editService->brightness($mediaId, $level);

            return Response::success([
                'file_id' => $file->id,
                'file_url' => $file->file_url,
            ], '亮度调整成功');

        } catch (\Exception $e) {
            return Response::error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 调整对比度
     */
    public function contrast(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $level = $request->post('level');

            if (empty($mediaId) || !isset($level)) {
                return Response::error('参数错误');
            }

            $file = $this->editService->contrast($mediaId, $level);

            return Response::success([
                'file_id' => $file->id,
                'file_url' => $file->file_url,
            ], '对比度调整成功');

        } catch (\Exception $e) {
            return Response::error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 灰度化
     */
    public function grayscale(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');

            if (empty($mediaId)) {
                return Response::error('参数错误');
            }

            $file = $this->editService->grayscale($mediaId);

            return Response::success([
                'file_id' => $file->id,
                'file_url' => $file->file_url,
            ], '灰度化成功');

        } catch (\Exception $e) {
            return Response::error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 锐化
     */
    public function sharpen(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');

            if (empty($mediaId)) {
                return Response::error('参数错误');
            }

            $file = $this->editService->sharpen($mediaId);

            return Response::success([
                'file_id' => $file->id,
                'file_url' => $file->file_url,
            ], '锐化成功');

        } catch (\Exception $e) {
            return Response::error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 模糊
     */
    public function blur(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $level = $request->post('level', 1);

            if (empty($mediaId)) {
                return Response::error('参数错误');
            }

            $file = $this->editService->blur($mediaId, $level);

            return Response::success([
                'file_id' => $file->id,
                'file_url' => $file->file_url,
            ], '模糊处理成功');

        } catch (\Exception $e) {
            return Response::error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 应用滤镜
     */
    public function filter(Request $request)
    {
        try {
            $mediaId = $request->post('media_id');
            $filterName = $request->post('filter_name');

            if (empty($mediaId) || empty($filterName)) {
                return Response::error('参数错误');
            }

            $file = $this->editService->filter($mediaId, $filterName);

            return Response::success([
                'file_id' => $file->id,
                'file_url' => $file->file_url,
            ], '滤镜应用成功');

        } catch (\Exception $e) {
            return Response::error('操作失败：' . $e->getMessage());
        }
    }

    /**
     * 获取编辑历史
     */
    public function history(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);
        $mediaId = $request->get('media_id', '');

        $query = MediaEditHistory::with(['media', 'user', 'originalFile', 'resultFile'])
            ->order('created_at', 'desc');

        if (!empty($mediaId)) {
            $query->where('media_id', $mediaId);
        }

        $list = $query->page($page, $pageSize)->select();
        $total = MediaEditHistory::when(!empty($mediaId), function ($q) use ($mediaId) {
            $q->where('media_id', $mediaId);
        })->count();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取支持的滤镜列表
     */
    public function filters()
    {
        $filters = [
            ['name' => 'sepia', 'label' => '怀旧', 'description' => '复古棕褐色调'],
            ['name' => 'negative', 'label' => '反色', 'description' => '颜色反转'],
            ['name' => 'emboss', 'label' => '浮雕', 'description' => '浮雕效果'],
            ['name' => 'edge', 'label' => '边缘检测', 'description' => '检测并突出边缘'],
            ['name' => 'sketch', 'label' => '素描', 'description' => '素描画效果'],
        ];

        return Response::success($filters);
    }
}
