<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\MediaLibrary;
use app\service\MediaLibraryService;
use think\App;
use think\Request;

/**
 * 媒体库控制器
 * 使用新的媒体库系统（MediaLibrary + MediaFile）
 */
class Media extends BaseController
{
    protected $mediaService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->mediaService = new MediaLibraryService();
    }

    /**
     * 媒体文件列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);
        $type = $request->get('type', '');
        $filename = $request->get('filename', '');
        $startDate = $request->get('start_date', '');
        $endDate = $request->get('end_date', '');
        $categoryId = $request->get('category_id', '');
        $tagId = $request->get('tag_id', '');

        // 构建查询
        $query = MediaLibrary::with(['file', 'user'])
            ->order('created_at', 'desc');

        // 搜索条件
        if (!empty($type)) {
            // 通过关联的file表查询文件类型
            $query->hasWhere('file', function ($q) use ($type) {
                $q->where('file_type', $type);
            });
        }

        if (!empty($filename)) {
            $query->where('title', 'like', '%' . $filename . '%');
        }

        // 日期范围查询
        if (!empty($startDate)) {
            $query->whereTime('created_at', '>=', $startDate . ' 00:00:00');
        }

        if (!empty($endDate)) {
            $query->whereTime('created_at', '<=', $endDate . ' 23:59:59');
        }

        // 分类筛选
        if (!empty($categoryId)) {
            $query->hasWhere('categories', function ($q) use ($categoryId) {
                $q->where('id', $categoryId);
            });
        }

        // 标签筛选
        if (!empty($tagId)) {
            $query->hasWhere('tags', function ($q) use ($tagId) {
                $q->where('id', $tagId);
            });
        }

        // 分页
        $list = $query->page($page, $pageSize)->select();

        // 处理返回数据
        $data = $list->map(function ($media) {
            return [
                'id' => $media->id,
                'title' => $media->title,
                'file_name' => $media->file ? $media->file->file_name : '',
                'file_url' => $media->file_url,
                'file_type' => $media->file ? $media->file->file_type : '',
                'file_size' => $media->file ? $media->file->file_size : 0,
                'width' => $media->file ? $media->file->width : null,
                'height' => $media->file ? $media->file->height : null,
                'mime_type' => $media->file ? $media->file->mime_type : '',
                'storage_type' => $media->file ? $media->file->storage_type : 'local',
                'description' => $media->description,
                'alt_text' => $media->alt_text,
                'is_public' => $media->is_public,
                'view_count' => $media->view_count,
                'download_count' => $media->download_count,
                'source' => $media->source,
                'user' => $media->user ? [
                    'id' => $media->user->id,
                    'username' => $media->user->username,
                ] : null,
                'thumbnails' => $media->getAllThumbnails(),
                'created_at' => $media->created_at,
                'updated_at' => $media->updated_at,
            ];
        });

        // 计算总数
        $total = MediaLibrary::when(!empty($type), function ($query) use ($type) {
            $query->hasWhere('file', function ($q) use ($type) {
                $q->where('file_type', $type);
            });
        })
        ->when(!empty($filename), function ($query) use ($filename) {
            $query->where('title', 'like', '%' . $filename . '%');
        })
        ->when(!empty($startDate), function ($query) use ($startDate) {
            $query->whereTime('created_at', '>=', $startDate . ' 00:00:00');
        })
        ->when(!empty($endDate), function ($query) use ($endDate) {
            $query->whereTime('created_at', '<=', $endDate . ' 23:59:59');
        })
        ->when(!empty($categoryId), function ($query) use ($categoryId) {
            $query->hasWhere('categories', function ($q) use ($categoryId) {
                $q->where('id', $categoryId);
            });
        })
        ->when(!empty($tagId), function ($query) use ($tagId) {
            $query->hasWhere('tags', function ($q) use ($tagId) {
                $q->where('id', $tagId);
            });
        })
        ->count();

        return Response::paginate($data->toArray(), $total, $page, $pageSize);
    }

    /**
     * 文件上传
     */
    public function upload(Request $request)
    {
        // 获取上传的文件
        $file = $request->file('file');

        if (!$file) {
            return Response::error('请选择要上传的文件');
        }

        // 验证文件
        try {
            validate([
                'file' => [
                    'fileSize' => 10 * 1024 * 1024, // 10MB
                    'fileExt'  => 'jpg,jpeg,png,gif,webp,ico,pdf,doc,docx,xls,xlsx,zip,rar',
                ]
            ])->check(['file' => $file]);
        } catch (\think\exception\ValidateException $e) {
            return Response::error($e->getMessage());
        }

        try {
            // 准备媒体数据
            $data = [
                'user_id' => $request->user['id'],
                'title' => $request->post('title', $file->getOriginalName()),
                'description' => $request->post('description', ''),
                'alt_text' => $request->post('alt_text', ''),
                'is_public' => $request->post('is_public', 1),
            ];

            // 分类和标签
            $categoryIds = $request->post('category_ids', []);
            $tagNames = $request->post('tag_names', []);

            if (!empty($categoryIds)) {
                $data['category_ids'] = is_array($categoryIds) ? $categoryIds : explode(',', $categoryIds);
            }

            if (!empty($tagNames)) {
                $data['tag_names'] = is_array($tagNames) ? $tagNames : explode(',', $tagNames);
            }

            // 使用服务上传媒体
            $media = $this->mediaService->upload($file, $data);

            return Response::success([
                'id' => $media->id,
                'title' => $media->title,
                'file_name' => $media->file->file_name,
                'file_url' => $media->file_url,
                'file_type' => $media->file->file_type,
                'file_size' => $media->file->file_size,
                'width' => $media->file->width,
                'height' => $media->file->height,
                'thumbnails' => $media->getAllThumbnails(),
            ], '文件上传成功');

        } catch (\Exception $e) {
            return Response::error('文件上传失败：' . $e->getMessage());
        }
    }

    /**
     * 获取媒体详情
     */
    public function read($id)
    {
        $media = MediaLibrary::with(['file', 'user', 'thumbnails'])
            ->find($id);

        if (!$media) {
            return Response::notFound('媒体不存在');
        }

        // 增加查看次数
        $media->incrementViewCount();

        return Response::success([
            'id' => $media->id,
            'title' => $media->title,
            'description' => $media->description,
            'alt_text' => $media->alt_text,
            'file_name' => $media->file->file_name,
            'file_url' => $media->file_url,
            'file_type' => $media->file->file_type,
            'file_size' => $media->file->file_size,
            'file_ext' => $media->file->file_ext,
            'width' => $media->file->width,
            'height' => $media->file->height,
            'mime_type' => $media->file->mime_type,
            'storage_type' => $media->file->storage_type,
            'is_public' => $media->is_public,
            'view_count' => $media->view_count,
            'download_count' => $media->download_count,
            'source' => $media->source,
            'status' => $media->status,
            'categories' => $media->categories,
            'tags' => $media->tags,
            'thumbnails' => $media->getAllThumbnails(),
            'user' => $media->user ? [
                'id' => $media->user->id,
                'username' => $media->user->username,
            ] : null,
            'created_at' => $media->created_at,
            'updated_at' => $media->updated_at,
        ]);
    }

    /**
     * 更新媒体信息
     */
    public function update(Request $request, $id)
    {
        try {
            $data = [
                'title' => $request->post('title'),
                'description' => $request->post('description'),
                'alt_text' => $request->post('alt_text'),
                'is_public' => $request->post('is_public'),
            ];

            // 分类和标签
            if ($request->has('category_ids')) {
                $categoryIds = $request->post('category_ids', []);
                $data['category_ids'] = is_array($categoryIds) ? $categoryIds : explode(',', $categoryIds);
            }

            if ($request->has('tag_names')) {
                $tagNames = $request->post('tag_names', []);
                $data['tag_names'] = is_array($tagNames) ? $tagNames : explode(',', $tagNames);
            }

            $media = $this->mediaService->update($id, $data);

            return Response::success([
                'id' => $media->id,
                'title' => $media->title,
                'description' => $media->description,
                'alt_text' => $media->alt_text,
                'is_public' => $media->is_public,
            ], '更新成功');

        } catch (\Exception $e) {
            return Response::error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除文件
     */
    public function delete($id)
    {
        try {
            $permanent = request()->post('permanent', false);
            $this->mediaService->delete($id, $permanent);

            $message = $permanent ? '文件已永久删除' : '文件已移入回收站';
            return Response::success([], $message);

        } catch (\Exception $e) {
            return Response::error('文件删除失败：' . $e->getMessage());
        }
    }

    /**
     * 批量上传
     */
    public function batchUpload(Request $request)
    {
        $files = $request->file('files');

        if (empty($files)) {
            return Response::error('请选择要上传的文件');
        }

        try {
            $commonData = [
                'user_id' => $request->user['id'],
                'is_public' => $request->post('is_public', 1),
            ];

            $results = $this->mediaService->batchImport($files, $commonData);

            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $failedCount = count($results) - $successCount;

            return Response::success([
                'total' => count($results),
                'success' => $successCount,
                'failed' => $failedCount,
                'results' => $results,
            ], "批量上传完成：成功 {$successCount} 个，失败 {$failedCount} 个");

        } catch (\Exception $e) {
            return Response::error('批量上传失败：' . $e->getMessage());
        }
    }

    /**
     * 获取存储统计信息
     */
    public function stats()
    {
        try {
            $fileService = new \app\service\MediaFileService();
            $stats = $fileService->getStorageStats();

            return Response::success($stats);

        } catch (\Exception $e) {
            return Response::error('获取统计信息失败：' . $e->getMessage());
        }
    }
}
