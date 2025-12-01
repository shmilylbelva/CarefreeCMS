<?php
declare (strict_types = 1);

namespace app\service;

use app\model\MediaLibrary;
use app\model\MediaFile;
use think\file\UploadedFile;

/**
 * 媒体库业务服务
 * 处理媒体库的业务逻辑
 */
class MediaLibraryService
{
    protected $fileService;

    public function __construct()
    {
        $this->fileService = new MediaFileService();
    }

    /**
     * 上传媒体文件
     * @param UploadedFile $file 上传的文件
     * @param array $data 媒体信息
     * @return MediaLibrary
     */
    public function upload(UploadedFile $file, array $data = []): MediaLibrary
    {
        try {
            // 1. 上传文件到MediaFile（支持去重）
            $mediaFile = $this->fileService->uploadFile($file);

            // 2. 创建MediaLibrary记录
            $media = MediaLibrary::create([
                'file_id' => $mediaFile->id,
                'user_id' => $data['user_id'] ?? request()->user['id'] ?? 1,
                'site_id' => $data['site_id'] ?? SiteContextService::getSite()->id ?? 1,
                'title' => $data['title'] ?? $mediaFile->file_name,
                'description' => $data['description'] ?? null,
                'alt_text' => $data['alt_text'] ?? null,
                'source' => $data['source'] ?? MediaLibrary::SOURCE_UPLOAD,
                'source_id' => $data['source_id'] ?? null,
                'status' => MediaLibrary::STATUS_ACTIVE,
                'is_public' => $data['is_public'] ?? 1,
            ]);

            // 3. 自动生成缩略图（如果是图片）
            if ($mediaFile->file_type === MediaFile::TYPE_IMAGE) {
                $thumbnailService = new MediaThumbnailService();
                $thumbnailService->generateAutoThumbnails($media->id);
            }

            // 4. 提取元数据（EXIF等）
            if ($mediaFile->file_type === MediaFile::TYPE_IMAGE) {
                $this->extractImageMetadata($media->id);
            }

            return $media;

        } catch (\Exception $e) {
            throw new \Exception('媒体上传失败：' . $e->getMessage());
        }
    }

    /**
     * 更新媒体信息
     */
    public function update(int $mediaId, array $data): MediaLibrary
    {
        $media = MediaLibrary::find($mediaId);

        if (!$media) {
            throw new \Exception('媒体不存在');
        }

        // 更新基本信息
        if (isset($data['title'])) {
            $media->title = $data['title'];
        }

        if (isset($data['description'])) {
            $media->description = $data['description'];
        }

        if (isset($data['alt_text'])) {
            $media->alt_text = $data['alt_text'];
        }

        if (isset($data['is_public'])) {
            $media->is_public = $data['is_public'];
        }

        $media->save();

        return $media;
    }

    /**
     * 删除媒体
     */
    public function delete(int $mediaId, bool $permanent = false): bool
    {
        $media = MediaLibrary::find($mediaId);

        if (!$media) {
            throw new \Exception('媒体不存在');
        }

        // 检查回收站配置
        $site = \app\model\Site::find($media->site_id);
        $recycleBinEnabled = $site ? $site->recycle_bin_enable : 'open';

        if ($recycleBinEnabled === 'open' && !$permanent) {
            // 软删除 - 使用 Db 直接更新，避免批量删除 bug
            $affected = \think\facade\Db::name('media_library')
                ->where('id', '=', $mediaId)
                ->limit(1)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            return $affected > 0;
        } else {
            // 永久删除
            // 1. 删除缩略图
            $thumbnailService = new MediaThumbnailService();
            $thumbnailService->deleteAllThumbnails($mediaId);

            // 2. 删除元数据
            \app\model\MediaMetadata::where('media_id', $mediaId)->delete();

            // 3. 减少文件引用计数
            $file = $media->file;
            if ($file) {
                $file->decrementRefCount();

                // 如果引用计数为0，删除物理文件
                if ($file->canDelete()) {
                    $file->deletePhysicalFile();
                    // 使用 Db 直接删除文件记录
                    \think\facade\Db::name('media_files')
                        ->where('id', '=', $file->id)
                        ->limit(1)
                        ->delete();
                }
            }

            // 4. 删除媒体记录 - 使用 Db 直接删除，避免批量删除 bug
            $affected = \think\facade\Db::name('media_library')
                ->where('id', '=', $mediaId)
                ->limit(1)
                ->delete();

            return $affected > 0;
        }
    }

    /**
     * 提取图片EXIF元数据
     */
    protected function extractImageMetadata(int $mediaId): void
    {
        $media = MediaLibrary::find($mediaId);

        if (!$media || !$media->file) {
            return;
        }

        $filePath = $media->file->getFullPath();

        if (!$filePath || !file_exists($filePath)) {
            return;
        }

        // 读取EXIF数据
        $exif = @exif_read_data($filePath);

        if ($exif) {
            // 存储有用的EXIF信息
            $metadata = [];

            if (isset($exif['Make'])) {
                $metadata['camera_make'] = $exif['Make'];
            }

            if (isset($exif['Model'])) {
                $metadata['camera_model'] = $exif['Model'];
            }

            if (isset($exif['DateTimeOriginal'])) {
                $metadata['taken_at'] = $exif['DateTimeOriginal'];
            }

            if (isset($exif['ExposureTime'])) {
                $metadata['exposure_time'] = $exif['ExposureTime'];
            }

            if (isset($exif['FNumber'])) {
                $metadata['f_number'] = $exif['FNumber'];
            }

            if (isset($exif['ISOSpeedRatings'])) {
                $metadata['iso'] = $exif['ISOSpeedRatings'];
            }

            if (isset($exif['FocalLength'])) {
                $metadata['focal_length'] = $exif['FocalLength'];
            }

            if (!empty($metadata)) {
                \app\model\MediaMetadata::setMany($mediaId, $metadata);
            }
        }
    }

    /**
     * 批量导入媒体
     */
    public function batchImport(array $files, array $commonData = []): array
    {
        $results = [];

        foreach ($files as $file) {
            try {
                $media = $this->upload($file, $commonData);
                $results[] = [
                    'success' => true,
                    'media' => $media,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'file' => $file->getOriginalName(),
                ];
            }
        }

        return $results;
    }
}
