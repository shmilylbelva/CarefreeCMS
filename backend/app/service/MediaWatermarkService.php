<?php
declare (strict_types = 1);

namespace app\service;

use app\model\MediaLibrary;
use app\model\MediaWatermarkPreset;
use app\model\MediaWatermarkLog;
use app\model\MediaFile;

/**
 * 媒体水印服务
 * 处理图片水印添加
 *
 * 注意：使用GD库实现基本水印功能
 * 如需更强大功能可安装 intervention/image
 */
class MediaWatermarkService
{
    protected $fileService;

    public function __construct()
    {
        $this->fileService = new MediaFileService();
    }

    /**
     * 为媒体添加水印
     * @param int $mediaId 媒体ID
     * @param int|null $presetId 水印预设ID，null则使用默认预设
     * @param array $customConfig 自定义水印配置
     * @return MediaFile 添加水印后的新文件
     */
    public function addWatermark(int $mediaId, ?int $presetId = null, array $customConfig = []): MediaFile
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

            // 获取水印配置
            if (!empty($customConfig)) {
                $config = $customConfig;
            } else {
                if ($presetId) {
                    $preset = MediaWatermarkPreset::find($presetId);
                } else {
                    $preset = MediaWatermarkPreset::getDefault($media->site_id);
                }

                if (!$preset) {
                    throw new \Exception('水印预设不存在');
                }

                $config = [
                    'type' => $preset->type,
                    'text_content' => $preset->text_content,
                    'text_font' => $preset->text_font,
                    'text_size' => $preset->text_size,
                    'text_color' => $preset->text_color,
                    'image_path' => $preset->image_path,
                    'position' => $preset->position,
                    'offset_x' => $preset->offset_x,
                    'offset_y' => $preset->offset_y,
                    'opacity' => $preset->opacity,
                    'scale' => $preset->scale,
                    'tile_spacing' => $preset->tile_spacing,
                ];
            }

            // 获取原图路径
            $sourcePath = $media->file->getFullPath();

            if (!$sourcePath || !file_exists($sourcePath)) {
                throw new \Exception('原始文件不存在');
            }

            // 备份原图
            $backupDir = 'backups/watermark/' . date('Y/m/d');
            $backupFullDir = app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $backupDir;

            if (!is_dir($backupFullDir)) {
                mkdir($backupFullDir, 0755, true);
            }

            $backupFileName = pathinfo($media->file->file_name, PATHINFO_FILENAME)
                . '_backup_' . time()
                . '.' . $media->file->file_ext;
            $backupFullPath = $backupFullDir . DIRECTORY_SEPARATOR . $backupFileName;

            // 复制原图到备份目录
            if (!copy($sourcePath, $backupFullPath)) {
                throw new \Exception('备份原图失败');
            }

            // 使用临时文件生成水印图片
            $tempPath = $sourcePath . '.tmp';

            // 根据水印类型处理
            switch ($config['type']) {
                case MediaWatermarkPreset::TYPE_TEXT:
                    $this->addTextWatermark($sourcePath, $tempPath, $config);
                    break;

                case MediaWatermarkPreset::TYPE_IMAGE:
                    $this->addImageWatermark($sourcePath, $tempPath, $config);
                    break;

                case MediaWatermarkPreset::TYPE_TILED:
                    $this->addTiledWatermark($sourcePath, $tempPath, $config);
                    break;

                default:
                    throw new \Exception('不支持的水印类型');
            }

            // 用水印图片替换原图
            if (!rename($tempPath, $sourcePath)) {
                @unlink($tempPath);
                throw new \Exception('替换原图失败');
            }

            // 更新 MediaFile 的文件信息
            $newFileSize = filesize($sourcePath);
            $newFileHash = MediaFile::calculateHash($sourcePath);

            $media->file->file_size = $newFileSize;
            $media->file->file_hash = $newFileHash;
            $media->file->save();

            $outputFile = $media->file;

            // 记录日志（含备份文件路径）
            $processingTime = (int)((microtime(true) - $startTime) * 1000);
            $backupPath = $backupDir . '/' . $backupFileName;

            MediaWatermarkLog::create([
                'media_id' => $mediaId,
                'preset_id' => $presetId,
                'user_id' => request()->user['id'] ?? 1,
                'watermark_type' => $config['type'],
                'watermark_config' => json_encode($config),
                'output_file_id' => $outputFile->id,
                'backup_path' => $backupPath, // 记录备份文件路径
                'status' => MediaWatermarkLog::STATUS_SUCCESS,
                'processing_time' => $processingTime,
            ]);

            return $outputFile;

        } catch (\Exception $e) {
            // 记录失败日志
            $processingTime = (int)((microtime(true) - $startTime) * 1000);

            MediaWatermarkLog::create([
                'media_id' => $mediaId,
                'preset_id' => $presetId ?? null,
                'user_id' => request()->user['id'] ?? 1,
                'watermark_type' => $config['type'] ?? 'unknown',
                'watermark_config' => isset($config) ? json_encode($config) : null,
                'status' => MediaWatermarkLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'processing_time' => $processingTime,
            ]);

            throw $e;
        }
    }

    /**
     * 添加文字水印
     */
    protected function addTextWatermark(string $sourcePath, string $outputPath, array $config): void
    {
        // 加载原图
        $image = $this->loadImage($sourcePath);

        $width = imagesx($image);
        $height = imagesy($image);

        // 解析颜色
        $color = $this->parseColor($config['text_color'] ?? '#000000');
        $alpha = (int)((100 - ($config['opacity'] ?? 50)) * 1.27);

        $textColor = imagecolorallocatealpha(
            $image,
            $color['r'],
            $color['g'],
            $color['b'],
            $alpha
        );

        // 计算文字位置
        $text = $config['text_content'] ?? '';
        $fontSize = $config['text_size'] ?? 20;
        $fontPath = $config['text_font'] ?? '';

        // 检测是否包含多字节字符（中文等）
        $hasMultibyteChars = strlen($text) != mb_strlen($text, 'UTF-8');

        if ($hasMultibyteChars || !empty($fontPath)) {
            // 使用 TrueType 字体
            if (empty($fontPath)) {
                throw new \Exception('渲染中文或特殊字符需要指定字体文件，请在水印预设中上传 TrueType 字体文件（.ttf）');
            }

            // 解析字体路径
            $fullFontPath = $this->parseFontPath($fontPath);

            if (!file_exists($fullFontPath)) {
                throw new \Exception('字体文件不存在：' . $fontPath);
            }

            // 计算文字边界框
            $bbox = imagettfbbox($fontSize, 0, $fullFontPath, $text);
            $textWidth = abs($bbox[4] - $bbox[0]);
            $textHeight = abs($bbox[5] - $bbox[1]);

            list($x, $y) = $this->calculatePosition(
                $width,
                $height,
                $textWidth,
                $textHeight,
                $config['position'] ?? MediaWatermarkPreset::POS_BOTTOM_RIGHT,
                $config['offset_x'] ?? 10,
                $config['offset_y'] ?? 10
            );

            // 绘制 TrueType 文字 (y 需要调整为基线位置)
            imagettftext($image, $fontSize, 0, $x, $y + $textHeight, $textColor, $fullFontPath, $text);
        } else {
            // 使用内置字体（仅支持 ASCII）
            $textWidth = imagefontwidth(5) * strlen($text);
            $textHeight = imagefontheight(5);

            list($x, $y) = $this->calculatePosition(
                $width,
                $height,
                $textWidth,
                $textHeight,
                $config['position'] ?? MediaWatermarkPreset::POS_BOTTOM_RIGHT,
                $config['offset_x'] ?? 10,
                $config['offset_y'] ?? 10
            );

            // 绘制内置字体文字
            imagestring($image, 5, $x, $y, $text, $textColor);
        }

        // 保存图片
        $this->saveImage($image, $outputPath);
        imagedestroy($image);
    }

    /**
     * 添加图片水印
     */
    protected function addImageWatermark(string $sourcePath, string $outputPath, array $config): void
    {
        // 加载原图
        $image = $this->loadImage($sourcePath);

        $width = imagesx($image);
        $height = imagesy($image);

        // 加载水印图片
        $watermarkPath = app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . $config['image_path'];

        if (!file_exists($watermarkPath)) {
            throw new \Exception('水印图片不存在');
        }

        $watermark = $this->loadImage($watermarkPath);

        $wmWidth = imagesx($watermark);
        $wmHeight = imagesy($watermark);

        // 缩放水印
        $scale = ($config['scale'] ?? 100) / 100;
        $newWmWidth = (int)($wmWidth * $scale);
        $newWmHeight = (int)($wmHeight * $scale);

        // 创建缩放后的水印
        $scaledWatermark = imagecreatetruecolor($newWmWidth, $newWmHeight);
        imagealphablending($scaledWatermark, false);
        imagesavealpha($scaledWatermark, true);

        imagecopyresampled(
            $scaledWatermark,
            $watermark,
            0, 0, 0, 0,
            $newWmWidth, $newWmHeight,
            $wmWidth, $wmHeight
        );

        // 计算水印位置
        list($x, $y) = $this->calculatePosition(
            $width,
            $height,
            $newWmWidth,
            $newWmHeight,
            $config['position'] ?? MediaWatermarkPreset::POS_BOTTOM_RIGHT,
            $config['offset_x'] ?? 10,
            $config['offset_y'] ?? 10
        );

        // 合并水印
        $opacity = $config['opacity'] ?? 50;
        imagecopymerge(
            $image,
            $scaledWatermark,
            $x, $y,
            0, 0,
            $newWmWidth, $newWmHeight,
            $opacity
        );

        // 保存图片
        $this->saveImage($image, $outputPath);

        imagedestroy($image);
        imagedestroy($watermark);
        imagedestroy($scaledWatermark);
    }

    /**
     * 添加平铺水印
     */
    protected function addTiledWatermark(string $sourcePath, string $outputPath, array $config): void
    {
        // 加载原图
        $image = $this->loadImage($sourcePath);

        $width = imagesx($image);
        $height = imagesy($image);

        // 解析颜色和透明度
        $color = $this->parseColor($config['text_color'] ?? '#000000');
        $alpha = (int)((100 - ($config['opacity'] ?? 20)) * 1.27);

        $textColor = imagecolorallocatealpha(
            $image,
            $color['r'],
            $color['g'],
            $color['b'],
            $alpha
        );

        // 平铺文字
        $text = $config['text_content'] ?? '';
        $fontSize = $config['text_size'] ?? 20;
        $fontPath = $config['text_font'] ?? '';
        $spacing = $config['tile_spacing'] ?? 100;

        // 检测是否包含多字节字符（中文等）
        $hasMultibyteChars = strlen($text) != mb_strlen($text, 'UTF-8');

        if ($hasMultibyteChars || !empty($fontPath)) {
            // 使用 TrueType 字体
            if (empty($fontPath)) {
                throw new \Exception('渲染中文或特殊字符需要指定字体文件，请在水印预设中上传 TrueType 字体文件（.ttf）');
            }

            $fullFontPath = $this->parseFontPath($fontPath);

            if (!file_exists($fullFontPath)) {
                throw new \Exception('字体文件不存在：' . $fontPath);
            }

            // 计算文字边界框
            $bbox = imagettfbbox($fontSize, 0, $fullFontPath, $text);
            $textWidth = abs($bbox[4] - $bbox[0]);
            $textHeight = abs($bbox[5] - $bbox[1]);

            // 平铺
            for ($ty = 0; $ty < $height; $ty += $textHeight + $spacing) {
                for ($tx = 0; $tx < $width; $tx += $textWidth + $spacing) {
                    imagettftext($image, $fontSize, 0, $tx, $ty + $textHeight, $textColor, $fullFontPath, $text);
                }
            }
        } else {
            // 使用内置字体（仅支持 ASCII）
            $textWidth = imagefontwidth(5) * strlen($text);
            $textHeight = imagefontheight(5);

            // 平铺
            for ($ty = 0; $ty < $height; $ty += $textHeight + $spacing) {
                for ($tx = 0; $tx < $width; $tx += $textWidth + $spacing) {
                    imagestring($image, 5, $tx, $ty, $text, $textColor);
                }
            }
        }

        // 保存图片
        $this->saveImage($image, $outputPath);
        imagedestroy($image);
    }

    /**
     * 加载图片
     */
    protected function loadImage(string $path)
    {
        $info = getimagesize($path);
        $mime = $info['mime'];

        $image = null;

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($path);
                // 启用 alpha 混合和保存 alpha 通道
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($path);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($path);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                break;
            default:
                throw new \Exception('不支持的图片格式');
        }

        return $image;
    }

    /**
     * 保存图片
     */
    protected function saveImage($image, string $path): void
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, $path, 90);
                break;
            case 'png':
                // 确保保存 alpha 通道
                imagesavealpha($image, true);
                imagepng($image, $path, 6); // 压缩级别 6 (0-9, 9最高)
                break;
            case 'gif':
                imagegif($image, $path);
                break;
            case 'webp':
                imagesavealpha($image, true);
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
        // 支持 rgb(r, g, b) 格式
        if (preg_match('/rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i', $color, $matches)) {
            return [
                'r' => (int)$matches[1],
                'g' => (int)$matches[2],
                'b' => (int)$matches[3],
            ];
        }

        // 支持十六进制格式
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

    /**
     * 解析字体路径
     */
    protected function parseFontPath(string $fontPath): string
    {
        // 如果是绝对路径，直接返回
        if (file_exists($fontPath)) {
            return $fontPath;
        }

        // 如果是相对路径，转换为绝对路径
        $fullPath = app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . ltrim($fontPath, '/\\');

        return $fullPath;
    }

    /**
     * 计算水印位置
     */
    protected function calculatePosition(
        int $imgWidth,
        int $imgHeight,
        int $wmWidth,
        int $wmHeight,
        string $position,
        int $offsetX,
        int $offsetY
    ): array {
        switch ($position) {
            case MediaWatermarkPreset::POS_TOP_LEFT:
                return [$offsetX, $offsetY];

            case MediaWatermarkPreset::POS_TOP_RIGHT:
                return [$imgWidth - $wmWidth - $offsetX, $offsetY];

            case MediaWatermarkPreset::POS_BOTTOM_LEFT:
                return [$offsetX, $imgHeight - $wmHeight - $offsetY];

            case MediaWatermarkPreset::POS_BOTTOM_RIGHT:
                return [$imgWidth - $wmWidth - $offsetX, $imgHeight - $wmHeight - $offsetY];

            case MediaWatermarkPreset::POS_CENTER:
                return [
                    ($imgWidth - $wmWidth) / 2,
                    ($imgHeight - $wmHeight) / 2
                ];

            default:
                return [$offsetX, $offsetY];
        }
    }
}
