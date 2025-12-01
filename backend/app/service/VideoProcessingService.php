<?php
declare (strict_types = 1);

namespace app\service;

use app\model\MediaLibrary;
use app\model\MediaFile;
use app\model\VideoTranscodeRecord;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;

/**
 * 视频处理服务
 * 使用FFmpeg进行视频转码、截图、信息提取等操作
 * 需要安装: composer require php-ffmpeg/php-ffmpeg
 */
class VideoProcessingService
{
    protected $ffmpeg;
    protected $mediaFileService;

    public function __construct()
    {
        // 初始化FFmpeg（需要先安装ffmpeg命令行工具）
        try {
            $this->ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => '/usr/bin/ffmpeg',  // Linux
                'ffprobe.binaries' => '/usr/bin/ffprobe', // Linux
                // Windows: 'C:\\ffmpeg\\bin\\ffmpeg.exe'
                'timeout'          => 3600, // 超时时间（秒）
                'ffmpeg.threads'   => 12,   // 线程数
            ]);
        } catch (\Exception $e) {
            // FFmpeg未安装或配置错误
            trace('FFmpeg初始化失败: ' . $e->getMessage(), 'error');
        }

        $this->mediaFileService = new MediaFileService();
    }

    /**
     * 获取视频信息
     *
     * @param string $filePath 视频文件路径
     * @return array
     */
    public function getVideoInfo(string $filePath): array
    {
        try {
            if (!$this->ffmpeg) {
                throw new \Exception('FFmpeg未初始化');
            }

            $video = $this->ffmpeg->open($filePath);
            $streams = $video->getStreams();

            $videoStream = $streams->videos()->first();
            $audioStream = $streams->audios()->first();

            $info = [
                'duration' => 0,
                'width' => 0,
                'height' => 0,
                'bitrate' => 0,
                'codec' => '',
                'fps' => 0,
                'has_audio' => false,
                'audio_codec' => '',
            ];

            if ($videoStream) {
                $dimensions = $videoStream->getDimensions();
                $info['width'] = $dimensions->getWidth();
                $info['height'] = $dimensions->getHeight();
                $info['codec'] = $videoStream->get('codec_name');
                $info['fps'] = (float)$videoStream->get('r_frame_rate', '0/1');
                $info['bitrate'] = (int)$videoStream->get('bit_rate', 0);

                // 获取时长
                $duration = $videoStream->get('duration');
                if ($duration) {
                    $info['duration'] = (int)$duration;
                }
            }

            if ($audioStream) {
                $info['has_audio'] = true;
                $info['audio_codec'] = $audioStream->get('codec_name');
            }

            return $info;

        } catch (\Exception $e) {
            throw new \Exception('获取视频信息失败: ' . $e->getMessage());
        }
    }

    /**
     * 视频转码
     *
     * @param int $mediaId 媒体ID
     * @param array $options 转码选项
     * @return MediaFile
     */
    public function transcodeVideo(int $mediaId, array $options = []): MediaFile
    {
        try {
            if (!$this->ffmpeg) {
                throw new \Exception('FFmpeg未初始化，无法进行视频转码');
            }

            $media = MediaLibrary::with('file')->find($mediaId);

            if (!$media || !$media->file) {
                throw new \Exception('媒体不存在');
            }

            if ($media->file->file_type !== MediaFile::TYPE_VIDEO) {
                throw new \Exception('只能转码视频文件');
            }

            // 创建转码记录
            $record = VideoTranscodeRecord::create([
                'media_id' => $mediaId,
                'original_file_id' => $media->file_id,
                'format' => $options['format'] ?? 'mp4',
                'quality' => $options['quality'] ?? 'medium',
                'resolution' => $options['resolution'] ?? null,
                'status' => 'processing',
            ]);

            // 更新媒体状态
            $media->status = 'processing';
            $media->save();

            // 获取源文件路径
            $sourcePath = $this->getLocalFilePath($media->file);

            // 打开视频
            $video = $this->ffmpeg->open($sourcePath);

            // 设置输出格式
            $format = new X264();
            $format->setAudioCodec('aac');

            // 设置质量
            $quality = $options['quality'] ?? 'medium';
            switch ($quality) {
                case 'low':
                    $format->setKiloBitrate(500);
                    break;
                case 'high':
                    $format->setKiloBitrate(2000);
                    break;
                default: // medium
                    $format->setKiloBitrate(1000);
            }

            // 设置分辨率
            if (!empty($options['resolution'])) {
                list($width, $height) = explode('x', $options['resolution']);
                $video->filters()->resize(new Dimension($width, $height));
            }

            // 输出文件路径
            $outputDir = dirname($sourcePath) . '/transcoded';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $outputFilename = pathinfo($sourcePath, PATHINFO_FILENAME) . '_transcoded.' . ($options['format'] ?? 'mp4');
            $outputPath = $outputDir . '/' . $outputFilename;

            // 执行转码
            $video->save($format, $outputPath);

            // 创建新的MediaFile记录
            $uploadedFile = new \think\file\UploadedFile(
                $outputPath,
                $outputFilename,
                'video/' . ($options['format'] ?? 'mp4'),
                filesize($outputPath),
                0
            );

            $newFile = $this->mediaFileService->uploadFile($uploadedFile, [
                'site_id' => $media->site_id,
            ]);

            // 更新转码记录
            $record->result_file_id = $newFile->id;
            $record->status = 'completed';
            $record->completed_at = date('Y-m-d H:i:s');
            $record->save();

            // 更新媒体状态
            $media->status = 'active';
            $media->save();

            return $newFile;

        } catch (\Exception $e) {
            // 更新失败状态
            if (isset($record)) {
                $record->status = 'failed';
                $record->error_message = $e->getMessage();
                $record->save();
            }

            if (isset($media)) {
                $media->status = 'failed';
                $media->save();
            }

            throw $e;
        }
    }

    /**
     * 生成视频封面/海报
     *
     * @param int $mediaId 媒体ID
     * @param int $timeInSeconds 截图时间点（秒）
     * @return MediaFile
     */
    public function generatePoster(int $mediaId, int $timeInSeconds = 1): MediaFile
    {
        try {
            if (!$this->ffmpeg) {
                throw new \Exception('FFmpeg未初始化，无法生成视频封面');
            }

            $media = MediaLibrary::with('file')->find($mediaId);

            if (!$media || !$media->file) {
                throw new \Exception('媒体不存在');
            }

            if ($media->file->file_type !== MediaFile::TYPE_VIDEO) {
                throw new \Exception('只能为视频文件生成封面');
            }

            // 获取源文件路径
            $sourcePath = $this->getLocalFilePath($media->file);

            // 打开视频
            $video = $this->ffmpeg->open($sourcePath);

            // 生成截图
            $frame = $video->frame(TimeCode::fromSeconds($timeInSeconds));

            // 输出文件路径
            $outputDir = dirname($sourcePath) . '/posters';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $outputFilename = pathinfo($sourcePath, PATHINFO_FILENAME) . '_poster.jpg';
            $outputPath = $outputDir . '/' . $outputFilename;

            // 保存截图
            $frame->save($outputPath);

            // 创建MediaFile记录
            $uploadedFile = new \think\file\UploadedFile(
                $outputPath,
                $outputFilename,
                'image/jpeg',
                filesize($outputPath),
                0
            );

            $posterFile = $this->mediaFileService->uploadFile($uploadedFile, [
                'site_id' => $media->site_id,
            ]);

            // 创建MediaLibrary记录
            MediaLibrary::create([
                'file_id' => $posterFile->id,
                'site_id' => $media->site_id,
                'user_id' => $media->user_id,
                'title' => $media->title . ' - 封面',
                'description' => '视频封面（自动生成）',
                'source' => 'video_poster',
                'source_id' => $mediaId,
                'status' => 'active',
            ]);

            return $posterFile;

        } catch (\Exception $e) {
            throw new \Exception('生成视频封面失败: ' . $e->getMessage());
        }
    }

    /**
     * 生成多帧预览图
     *
     * @param int $mediaId 媒体ID
     * @param int $frameCount 帧数
     * @return array
     */
    public function generateThumbnails(int $mediaId, int $frameCount = 9): array
    {
        try {
            if (!$this->ffmpeg) {
                throw new \Exception('FFmpeg未初始化');
            }

            $media = MediaLibrary::with('file')->find($mediaId);

            if (!$media || !$media->file) {
                throw new \Exception('媒体不存在');
            }

            // 获取视频信息
            $sourcePath = $this->getLocalFilePath($media->file);
            $videoInfo = $this->getVideoInfo($sourcePath);
            $duration = $videoInfo['duration'];

            if ($duration <= 0) {
                throw new \Exception('无法获取视频时长');
            }

            // 计算截图时间点
            $interval = $duration / ($frameCount + 1);
            $thumbnails = [];

            $outputDir = dirname($sourcePath) . '/thumbnails';
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $video = $this->ffmpeg->open($sourcePath);

            for ($i = 1; $i <= $frameCount; $i++) {
                $timeInSeconds = (int)($interval * $i);
                $frame = $video->frame(TimeCode::fromSeconds($timeInSeconds));

                $outputFilename = pathinfo($sourcePath, PATHINFO_FILENAME) . '_thumb_' . $i . '.jpg';
                $outputPath = $outputDir . '/' . $outputFilename;

                $frame->save($outputPath);

                $thumbnails[] = [
                    'time' => $timeInSeconds,
                    'path' => $outputPath,
                ];
            }

            return $thumbnails;

        } catch (\Exception $e) {
            throw new \Exception('生成缩略图失败: ' . $e->getMessage());
        }
    }

    /**
     * 获取本地文件路径（如果是云存储，先下载到本地）
     *
     * @param MediaFile $file
     * @return string
     */
    public function getLocalFilePath(MediaFile $file): string
    {
        // 如果是本地存储，直接返回路径
        if ($file->storage_type === 'local') {
            return app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . $file->file_path;
        }

        // 云存储需要先下载到临时目录
        $tempDir = sys_get_temp_dir() . '/video_processing';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempFile = $tempDir . '/' . basename($file->file_path);

        // 如果临时文件已存在且哈希匹配，直接使用
        if (file_exists($tempFile)) {
            if (MediaFile::calculateHash($tempFile) === $file->file_hash) {
                return $tempFile;
            }
        }

        // 从云存储下载
        $storage = \app\service\storage\StorageFactory::getInstance($file->storage_config_id);
        $storage->download($file->file_path, $tempFile);

        return $tempFile;
    }
}
