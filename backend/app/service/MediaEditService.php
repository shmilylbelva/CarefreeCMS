<?php
declare (strict_types = 1);

namespace app\service;

use app\model\MediaLibrary;
use app\model\MediaFile;
use app\model\MediaEditHistory;

/**
 * 媒体编辑服务
 * 支持图片的各种编辑操作
 */
class MediaEditService
{
    protected $fileService;

    public function __construct()
    {
        $this->fileService = new MediaFileService();
    }

    /**
     * 调整图片大小
     */
    public function resize(int $mediaId, int $width, int $height, string $mode = 'fit'): MediaFile
    {
        return $this->editImage($mediaId, 'resize', [
            'width' => $width,
            'height' => $height,
            'mode' => $mode,
        ], function ($image, $params) {
            $newWidth = $params['width'];
            $newHeight = $params['height'];
            $mode = $params['mode'];

            $origWidth = imagesx($image);
            $origHeight = imagesy($image);

            // 计算新尺寸
            if ($mode === 'fit') {
                $ratio = min($newWidth / $origWidth, $newHeight / $origHeight);
                $newWidth = (int)($origWidth * $ratio);
                $newHeight = (int)($origHeight * $ratio);
            }

            // 创建新图片
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // 保持透明度
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);

            imagecopyresampled(
                $newImage, $image,
                0, 0, 0, 0,
                $newWidth, $newHeight,
                $origWidth, $origHeight
            );

            return $newImage;
        });
    }

    /**
     * 裁剪图片
     */
    public function crop(int $mediaId, int $x, int $y, int $width, int $height): MediaFile
    {
        return $this->editImage($mediaId, 'crop', [
            'x' => $x,
            'y' => $y,
            'width' => $width,
            'height' => $height,
        ], function ($image, $params) {
            $newImage = imagecreatetruecolor($params['width'], $params['height']);

            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);

            imagecopy(
                $newImage, $image,
                0, 0,
                $params['x'], $params['y'],
                $params['width'], $params['height']
            );

            return $newImage;
        });
    }

    /**
     * 旋转图片
     */
    public function rotate(int $mediaId, int $angle, string $bgColor = '#FFFFFF'): MediaFile
    {
        return $this->editImage($mediaId, 'rotate', [
            'angle' => $angle,
            'bg_color' => $bgColor,
        ], function ($image, $params) {
            $color = $this->parseColor($params['bg_color']);
            $bgColor = imagecolorallocate($image, $color['r'], $color['g'], $color['b']);

            $rotated = imagerotate($image, -$params['angle'], $bgColor);

            imagealphablending($rotated, false);
            imagesavealpha($rotated, true);

            return $rotated;
        });
    }

    /**
     * 翻转图片
     */
    public function flip(int $mediaId, string $direction = 'horizontal'): MediaFile
    {
        return $this->editImage($mediaId, 'flip', [
            'direction' => $direction,
        ], function ($image, $params) {
            $mode = $params['direction'] === 'horizontal' ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL;
            imageflip($image, $mode);
            return $image;
        });
    }

    /**
     * 调整亮度
     */
    public function brightness(int $mediaId, int $level): MediaFile
    {
        return $this->editImage($mediaId, 'brightness', [
            'level' => $level,
        ], function ($image, $params) {
            imagefilter($image, IMG_FILTER_BRIGHTNESS, $params['level']);
            return $image;
        });
    }

    /**
     * 调整对比度
     */
    public function contrast(int $mediaId, int $level): MediaFile
    {
        return $this->editImage($mediaId, 'contrast', [
            'level' => $level,
        ], function ($image, $params) {
            imagefilter($image, IMG_FILTER_CONTRAST, -$params['level']);
            return $image;
        });
    }

    /**
     * 灰度化
     */
    public function grayscale(int $mediaId): MediaFile
    {
        return $this->editImage($mediaId, 'grayscale', [], function ($image, $params) {
            imagefilter($image, IMG_FILTER_GRAYSCALE);
            return $image;
        });
    }

    /**
     * 锐化
     */
    public function sharpen(int $mediaId): MediaFile
    {
        return $this->editImage($mediaId, 'sharpen', [], function ($image, $params) {
            $matrix = [
                [-1, -1, -1],
                [-1, 16, -1],
                [-1, -1, -1],
            ];
            $divisor = 8;
            $offset = 0;
            imageconvolution($image, $matrix, $divisor, $offset);
            return $image;
        });
    }

    /**
     * 模糊
     */
    public function blur(int $mediaId, int $level = 1): MediaFile
    {
        return $this->editImage($mediaId, 'blur', [
            'level' => $level,
        ], function ($image, $params) {
            for ($i = 0; $i < $params['level']; $i++) {
                imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
            }
            return $image;
        });
    }

    /**
     * 应用滤镜
     */
    public function filter(int $mediaId, string $filterName): MediaFile
    {
        $filters = [
            'sepia' => function ($image) {
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                imagefilter($image, IMG_FILTER_COLORIZE, 100, 50, 0);
                return $image;
            },
            'negative' => function ($image) {
                imagefilter($image, IMG_FILTER_NEGATE);
                return $image;
            },
            'emboss' => function ($image) {
                imagefilter($image, IMG_FILTER_EMBOSS);
                return $image;
            },
            'edge' => function ($image) {
                imagefilter($image, IMG_FILTER_EDGEDETECT);
                return $image;
            },
            'sketch' => function ($image) {
                imagefilter($image, IMG_FILTER_MEAN_REMOVAL);
                return $image;
            },
        ];

        if (!isset($filters[$filterName])) {
            throw new \Exception('不支持的滤镜：' . $filterName);
        }

        return $this->editImage($mediaId, 'filter', [
            'filter_name' => $filterName,
        ], $filters[$filterName]);
    }

    /**
     * 通用图片编辑方法
     */
    protected function editImage(int $mediaId, string $operation, array $params, callable $processor): MediaFile
    {
        $startTime = microtime(true);

        try {
            // 获取媒体
            $media = MediaLibrary::with(['file'])->find($mediaId);

            if (!$media || !$media->file) {
                throw new \Exception('媒体不存在');
            }

            // 只支持图片
            if ($media->file->file_type !== MediaFile::TYPE_IMAGE) {
                throw new \Exception('只支持图片类型');
            }

            // 加载原图
            $sourcePath = $media->file->getFullPath();

            if (!$sourcePath || !file_exists($sourcePath)) {
                throw new \Exception('原始文件不存在');
            }

            $image = $this->loadImage($sourcePath);

            // 执行编辑操作
            $processedImage = $processor($image, $params);

            // 生成输出文件路径
            $outputDir = 'uploads/edited/' . date('Y/m/d');
            $outputFileName = pathinfo($media->file->file_name, PATHINFO_FILENAME)
                . '_' . $operation . '_' . time()
                . '.' . $media->file->file_ext;

            $outputPath = $outputDir . '/' . $outputFileName;
            $outputFullPath = app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . $outputPath;

            // 创建输出目录
            $outputFullDir = dirname($outputFullPath);
            if (!is_dir($outputFullDir)) {
                mkdir($outputFullDir, 0755, true);
            }

            // 保存图片
            $this->saveImage($processedImage, $outputFullPath, $media->file->file_ext);

            // 释放资源
            if ($processedImage !== $image) {
                imagedestroy($image);
            }
            imagedestroy($processedImage);

            // 计算文件hash并创建MediaFile记录
            $fileHash = MediaFile::calculateHash($outputFullPath);
            $existingFile = MediaFile::findByHash($fileHash);

            if ($existingFile) {
                @unlink($outputFullPath);
                $existingFile->incrementRefCount();
                $outputFile = $existingFile;
            } else {
                list($width, $height) = getimagesize($outputFullPath);

                $outputFile = MediaFile::create([
                    'file_hash' => $fileHash,
                    'file_path' => $outputPath,
                    'file_name' => $outputFileName,
                    'file_ext' => $media->file->file_ext,
                    'file_size' => filesize($outputFullPath),
                    'mime_type' => $media->file->mime_type,
                    'file_type' => MediaFile::TYPE_IMAGE,
                    'storage_type' => MediaFile::STORAGE_LOCAL,
                    'width' => $width,
                    'height' => $height,
                    'ref_count' => 1,
                ]);
            }

            // 记录编辑历史
            $processingTime = (int)((microtime(true) - $startTime) * 1000);

            MediaEditHistory::create([
                'media_id' => $mediaId,
                'user_id' => request()->user['id'] ?? 1,
                'operation' => $operation,
                'operation_params' => json_encode($params),
                'original_file_id' => $media->file->id,
                'result_file_id' => $outputFile->id,
                'status' => MediaEditHistory::STATUS_SUCCESS,
                'processing_time' => $processingTime,
            ]);

            return $outputFile;

        } catch (\Exception $e) {
            // 记录失败日志
            $processingTime = (int)((microtime(true) - $startTime) * 1000);

            MediaEditHistory::create([
                'media_id' => $mediaId,
                'user_id' => request()->user['id'] ?? 1,
                'operation' => $operation,
                'operation_params' => json_encode($params),
                'original_file_id' => $media->file->id ?? null,
                'status' => MediaEditHistory::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'processing_time' => $processingTime,
            ]);

            throw $e;
        }
    }

    /**
     * 加载图片
     */
    protected function loadImage(string $path)
    {
        $info = getimagesize($path);
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            case 'image/webp':
                return imagecreatefromwebp($path);
            default:
                throw new \Exception('不支持的图片格式');
        }
    }

    /**
     * 保存图片
     */
    protected function saveImage($image, string $path, string $ext): void
    {
        $ext = strtolower($ext);

        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, $path, 90);
                break;
            case 'png':
                imagepng($image, $path, 8);
                break;
            case 'gif':
                imagegif($image, $path);
                break;
            case 'webp':
                imagewebp($image, $path, 90);
                break;
            default:
                imagejpeg($image, $path, 90);
        }
    }

    /**
     * 解析颜色
     */
    protected function parseColor(string $color): array
    {
        $color = ltrim($color, '#');

        if (strlen($color) === 3) {
            $r = hexdec($color[0] . $color[0]);
            $g = hexdec($color[1] . $color[1]);
            $b = hexdec($color[2] . $color[2]);
        } else {
            $r = hexdec(substr($color, 0, 2));
            $g = hexdec(substr($color, 2, 2));
            $b = hexdec(substr($color, 4, 2));
        }

        return ['r' => $r, 'g' => $g, 'b' => $b];
    }
}
