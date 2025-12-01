<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\service\ChunkedUploadService;
use think\App;
use think\Request;

/**
 * 分片上传控制器
 * 支持大文件的分片上传和断点续传
 */
class ChunkedUpload extends BaseController
{
    protected $chunkedService;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->chunkedService = new ChunkedUploadService();
    }

    /**
     * 初始化上传会话
     */
    public function init(Request $request)
    {
        try {
            $data = [
                'user_id' => $request->user['id'] ?? 1,
                'site_id' => $request->site['id'] ?? null,
                'file_name' => $request->post('file_name'),
                'file_size' => $request->post('file_size'),
                'mime_type' => $request->post('mime_type'),
                'chunk_size' => $request->post('chunk_size'),
                'expiry_hours' => $request->post('expiry_hours'),
            ];

            if (empty($data['file_name']) || empty($data['file_size']) || empty($data['mime_type'])) {
                return Response::error('文件名、大小和类型不能为空');
            }

            $session = $this->chunkedService->initSession($data);

            return Response::success([
                'upload_id' => $session->upload_id,
                'chunk_size' => $session->chunk_size,
                'total_chunks' => $session->total_chunks,
                'expires_at' => $session->expires_at,
            ], '上传会话创建成功');

        } catch (\Exception $e) {
            return Response::error('初始化失败：' . $e->getMessage());
        }
    }

    /**
     * 上传单个分片
     */
    public function uploadChunk(Request $request)
    {
        try {
            $uploadId = $request->post('upload_id');
            $chunkIndex = $request->post('chunk_index');
            $chunkHash = $request->post('chunk_hash');
            $file = $request->file('chunk');

            if (empty($uploadId) || !isset($chunkIndex) || !$file) {
                return Response::error('参数错误');
            }

            $chunk = $this->chunkedService->uploadChunk(
                $uploadId,
                (int)$chunkIndex,
                $file,
                $chunkHash
            );

            // 获取进度
            $progress = $this->chunkedService->getProgress($uploadId);

            return Response::success([
                'chunk_index' => $chunk->chunk_index,
                'chunk_size' => $chunk->chunk_size,
                'status' => $chunk->status,
                'progress' => $progress['progress'],
                'uploaded_chunks' => $progress['uploaded_chunks'],
                'total_chunks' => $progress['total_chunks'],
            ], '分片上传成功');

        } catch (\Exception $e) {
            return Response::error('上传失败：' . $e->getMessage());
        }
    }

    /**
     * 合并分片
     */
    public function merge(Request $request)
    {
        try {
            $uploadId = $request->post('upload_id');
            $title = $request->post('title');
            $description = $request->post('description');
            $categoryIds = $request->post('category_ids', []);
            $tags = $request->post('tags', []);
            $isPublic = $request->post('is_public', 1);
            $storageConfigId = $request->post('storage_config_id');

            if (empty($uploadId)) {
                return Response::error('上传ID不能为空');
            }

            $result = $this->chunkedService->mergeChunks($uploadId, [
                'title' => $title,
                'description' => $description,
                'category_ids' => $categoryIds,
                'tags' => $tags,
                'is_public' => $isPublic,
                'storage_config_id' => $storageConfigId,
            ]);

            return Response::success([
                'media' => $result['media'],
                'file' => $result['file'],
            ], '文件上传完成');

        } catch (\Exception $e) {
            return Response::error('合并失败：' . $e->getMessage());
        }
    }

    /**
     * 获取上传进度
     */
    public function progress(Request $request)
    {
        try {
            $uploadId = $request->get('upload_id');

            if (empty($uploadId)) {
                return Response::error('上传ID不能为空');
            }

            $progress = $this->chunkedService->getProgress($uploadId);

            return Response::success($progress);

        } catch (\Exception $e) {
            return Response::error('获取进度失败：' . $e->getMessage());
        }
    }

    /**
     * 取消上传
     */
    public function cancel(Request $request)
    {
        try {
            $uploadId = $request->post('upload_id');

            if (empty($uploadId)) {
                return Response::error('上传ID不能为空');
            }

            $this->chunkedService->cancelUpload($uploadId);

            return Response::success([], '上传已取消');

        } catch (\Exception $e) {
            return Response::error('取消失败：' . $e->getMessage());
        }
    }

    /**
     * 清理过期会话（管理员功能）
     */
    public function cleanup()
    {
        try {
            $count = $this->chunkedService->cleanupExpiredSessions();

            return Response::success([
                'cleaned_count' => $count,
            ], '清理完成');

        } catch (\Exception $e) {
            return Response::error('清理失败：' . $e->getMessage());
        }
    }
}
