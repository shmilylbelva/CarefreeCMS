<?php
declare (strict_types = 1);

namespace app\service;

use app\model\MediaLibrary;
use app\model\MediaThumbnail;
use app\model\MediaThumbnailPreset;

/**
 * 媒体缩略图服务
 * 处理缩略图生成、管理等操作
 *
 * 注意：实际的图片处理需要安装 intervention/image 库
 * composer require intervention/image
 */
class MediaThumbnailService
{
    /**
     * 为媒体自动生成所有启用的缩略图
     */
    public function generateAutoThumbnails(int $mediaId): array
    {
        $media = MediaLibrary::find($mediaId);

        if (!$media || !$media->file) {
            throw new \Exception('媒体不存在');
        }

        // 只为图片生成缩略图
        if ($media->file->file_type !== \app\model\MediaFile::TYPE_IMAGE) {
            return [];
        }

        // 获取自动生成的预设
        $presets = MediaThumbnailPreset::getAutoGeneratePresets($media->site_id);

        $results = [];

        foreach ($presets as $preset) {
            try {
                $thumbnail = $this->generateThumbnail($mediaId, $preset->name);
                $results[] = [
                    'preset' => $preset->name,
                    'success' => true,
                    'thumbnail' => $thumbnail,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'preset' => $preset->name,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * 生成指定规格的缩略图
     */
    public function generateThumbnail(int $mediaId, string $presetName): MediaThumbnail
    {
        $media = MediaLibrary::find($mediaId);

        if (!$media || !$media->file) {
            throw new \Exception('媒体不存在');
        }

        // 获取预设配置
        $preset = MediaThumbnailPreset::getByName($presetName, $media->site_id);

        if (!$preset) {
            throw new \Exception('缩略图预设不存在：' . $presetName);
        }

        // 检查是否已存在该规格的缩略图
        $existing = MediaThumbnail::where('media_id', $mediaId)
            ->where('preset_name', $presetName)
            ->find();

        if ($existing) {
            // 删除旧的缩略图文件
            $existing->deletePhysicalFile();
            $existing->delete();
        }

        // 生成缩略图
        $originalPath = $media->file->getFullPath();

        if (!$originalPath || !file_exists($originalPath)) {
            throw new \Exception('原始文件不存在');
        }

        // 生成缩略图文件路径
        $thumbDir = 'uploads/thumbnails/' . date('Y/m/d');
        $thumbFileName = pathinfo($media->file->file_name, PATHINFO_FILENAME)
            . '_' . $presetName
            . '.' . ($preset->format ?: $media->file->file_ext);

        $thumbPath = $thumbDir . '/' . $thumbFileName;
        $thumbFullPath = app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . $thumbPath;

        // 创建目录
        $thumbFullDir = dirname($thumbFullPath);
        if (!is_dir($thumbFullDir)) {
            mkdir($thumbFullDir, 0755, true);
        }

        // 使用GD库生成缩略图（如果安装了intervention/image可以使用更强大的功能）
        list($origWidth, $origHeight) = getimagesize($originalPath);

        // 计算缩略图尺寸
        list($thumbWidth, $thumbHeight) = $this->calculateThumbnailSize(
            $origWidth,
            $origHeight,
            $preset->width,
            $preset->height,
            $preset->mode
        );

        // 生成缩略图
        $this->resizeImage(
            $originalPath,
            $thumbFullPath,
            $thumbWidth,
            $thumbHeight,
            $preset->mode,
            $preset->quality
        );

        // 创建缩略图记录
        $thumbnail = MediaThumbnail::create([
            'media_id' => $mediaId,
            'preset_id' => $preset->id,
            'preset_name' => $presetName,
            'file_path' => $thumbPath,
            'width' => $thumbWidth,
            'height' => $thumbHeight,
            'file_size' => filesize($thumbFullPath),
            'storage_type' => 'local',
        ]);

        return $thumbnail;
    }

    /**
     * 计算缩略图尺寸
     */
    protected function calculateThumbnailSize(
        int $origWidth,
        int $origHeight,
        ?int $targetWidth,
        ?int $targetHeight,
        string $mode
    ): array {
        // 如果没有指定目标尺寸，返回原始尺寸
        if (!$targetWidth && !$targetHeight) {
            return [$origWidth, $origHeight];
        }

        // 如果只指定了宽度
        if ($targetWidth && !$targetHeight) {
            $ratio = $targetWidth / $origWidth;
            return [$targetWidth, (int)($origHeight * $ratio)];
        }

        // 如果只指定了高度
        if (!$targetWidth && $targetHeight) {
            $ratio = $targetHeight / $origHeight;
            return [(int)($origWidth * $ratio), $targetHeight];
        }

        // 两个尺寸都指定了
        switch ($mode) {
            case MediaThumbnailPreset::MODE_FIT:
                // 等比例缩放，适应尺寸（不裁剪）
                $ratio = min($targetWidth / $origWidth, $targetHeight / $origHeight);
                return [(int)($origWidth * $ratio), (int)($origHeight * $ratio)];

            case MediaThumbnailPreset::MODE_FILL:
                // 等比例缩放，填充尺寸（可能裁剪）
                $ratio = max($targetWidth / $origWidth, $targetHeight / $origHeight);
                return [(int)($origWidth * $ratio), (int)($origHeight * $ratio)];

            case MediaThumbnailPreset::MODE_CROP:
                // 裁剪到指定尺寸
                return [$targetWidth, $targetHeight];

            case MediaThumbnailPreset::MODE_EXACT:
                // 强制缩放到指定尺寸（可能变形）
                return [$targetWidth, $targetHeight];

            default:
                return [$targetWidth, $targetHeight];
        }
    }

    /**
     * 使用GD库调整图片大小
     */
    protected function resizeImage(
        string $sourcePath,
        string $destPath,
        int $width,
        int $height,
        string $mode,
        int $quality
    ): void {
        // 获取原图信息
        $info = getimagesize($sourcePath);
        $mime = $info['mime'];

        // 创建原图资源
        switch ($mime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \Exception('不支持的图片格式：' . $mime);
        }

        // 创建目标图像
        $dest = imagecreatetruecolor($width, $height);

        // 保持透明度
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
            imagefilledrectangle($dest, 0, 0, $width, $height, $transparent);
        }

        // 根据模式调整图片
        if ($mode === MediaThumbnailPreset::MODE_CROP) {
            // 裁剪模式：从中心裁剪
            $origWidth = imagesx($source);
            $origHeight = imagesy($source);

            $ratio = max($width / $origWidth, $height / $origHeight);
            $tempWidth = (int)($origWidth * $ratio);
            $tempHeight = (int)($origHeight * $ratio);

            $temp = imagecreatetruecolor($tempWidth, $tempHeight);

            imagecopyresampled(
                $temp, $source,
                0, 0, 0, 0,
                $tempWidth, $tempHeight,
                $origWidth, $origHeight
            );

            // 从中心裁剪
            $cropX = (int)(($tempWidth - $width) / 2);
            $cropY = (int)(($tempHeight - $height) / 2);

            imagecopy(
                $dest, $temp,
                0, 0, $cropX, $cropY,
                $width, $height
            );

            imagedestroy($temp);
        } else {
            // 其他模式：直接缩放
            imagecopyresampled(
                $dest, $source,
                0, 0, 0, 0,
                $width, $height,
                imagesx($source), imagesy($source)
            );
        }

        // 保存图片
        $ext = pathinfo($destPath, PATHINFO_EXTENSION);

        switch (strtolower($ext)) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($dest, $destPath, $quality);
                break;
            case 'png':
                $pngQuality = (int)((100 - $quality) / 11);
                imagepng($dest, $destPath, $pngQuality);
                break;
            case 'gif':
                imagegif($dest, $destPath);
                break;
            case 'webp':
                imagewebp($dest, $destPath, $quality);
                break;
            default:
                imagejpeg($dest, $destPath, $quality);
        }

        // 释放资源
        imagedestroy($source);
        imagedestroy($dest);
    }

    /**
     * 删除媒体的所有缩略图
     */
    public function deleteAllThumbnails(int $mediaId): int
    {
        $thumbnails = MediaThumbnail::where('media_id', $mediaId)->select();
        $count = 0;

        foreach ($thumbnails as $thumbnail) {
            if ($thumbnail->deletePhysicalFile()) {
                $thumbnail->delete();
                $count++;
            }
        }

        return $count;
    }

    /**
     * 重新生成媒体的所有缩略图
     */
    public function regenerateAllThumbnails(int $mediaId): array
    {
        // 删除现有缩略图
        $this->deleteAllThumbnails($mediaId);

        // 重新生成
        return $this->generateAutoThumbnails($mediaId);
    }
}
