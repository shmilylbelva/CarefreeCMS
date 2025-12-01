# 媒体库系统增强功能文档

## 概述

本文档描述了对媒体库系统的重大增强功能，包括云存储支持、队列系统、视频处理和分片上传等生产级特性。

---

## 一、云存储支持

### 1.1 功能特性

- **多云存储适配器**：支持本地存储、阿里云OSS、腾讯云COS、七牛云
- **统一接口**：所有存储提供商使用相同的API接口
- **自动切换**：可按站点配置不同的存储提供商
- **CDN加速**：支持配置CDN域名加速访问
- **文件迁移**：支持在不同存储之间迁移文件

### 1.2 架构设计

```
StorageInterface (接口)
    ├── LocalStorage (本地存储)
    ├── AliyunOssStorage (阿里云OSS)
    ├── TencentCosStorage (腾讯云COS)
    └── QiniuStorage (七牛云)

StorageFactory (工厂类)
    └── 负责创建和管理存储实例
```

### 1.3 存储配置

#### 创建存储配置

```bash
POST /api/storage-config
{
  "name": "阿里云OSS-主存储",
  "driver": "aliyun_oss",
  "config_data": {
    "access_key_id": "YOUR_ACCESS_KEY",
    "access_key_secret": "YOUR_SECRET",
    "bucket": "your-bucket-name",
    "endpoint": "oss-cn-hangzhou.aliyuncs.com",
    "cdn_domain": "https://cdn.example.com"
  },
  "is_enabled": 1,
  "is_default": 1
}
```

#### 测试连接

```bash
POST /api/storage-config/test
{
  "driver": "aliyun_oss",
  "config_data": { ... }
}
```

#### 支持的驱动

- **local** - 本地存储（默认）
- **aliyun_oss** - 阿里云对象存储
- **tencent_cos** - 腾讯云对象存储
- **qiniu** - 七牛云存储

### 1.4 依赖安装

```bash
# 阿里云OSS
composer require aliyuncs/oss-sdk-php

# 腾讯云COS
composer require qcloud/cos-sdk-v5

# 七牛云
composer require qiniu/php-sdk
```

### 1.5 数据库表

执行SQL脚本创建相关表：

```bash
mysql -u root -p cms_database < backend/database/storage_config_schema.sql
mysql -u root -p cms_database < backend/database/storage_upgrade.sql
```

---

## 二、队列系统

### 2.1 功能特性

- **异步处理**：避免长时间操作阻塞请求
- **自动重试**：失败任务自动重试（可配置次数）
- **进度跟踪**：实时查看任务执行进度
- **队列分组**：不同类型任务使用不同队列

### 2.2 支持的队列任务

#### AI图片生成任务

```php
// 自动推送到队列
POST /api/ai-image/execute
{
  "task_id": 123,
  "async": true  // 设置为true使用队列
}

// 手动推送
POST /api/queue/ai-image
{
  "task_id": 123
}
```

#### 批量缩略图生成

```php
POST /api/queue/batch-thumbnail
{
  "media_ids": [1, 2, 3, 4, 5],
  "preset_name": "medium"  // 可选，不填则生成所有自动预设
}
```

#### 批量水印处理

```php
POST /api/queue/batch-watermark
{
  "media_ids": [1, 2, 3, 4, 5],
  "preset_id": 1  // 或使用 "config" 自定义配置
}
```

#### 视频转码

```php
POST /api/queue/video-transcode
{
  "media_id": 123,
  "format": "mp4",
  "quality": "high",
  "resolution": "1920x1080"
}
```

### 2.3 队列配置

ThinkPHP 8默认使用sync（同步）驱动，生产环境建议使用redis或database驱动。

编辑 `config/queue.php`:

```php
return [
    'default' => 'redis',  // 使用redis驱动

    'connections' => [
        'redis' => [
            'type' => 'redis',
            'queue' => 'default',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '',
            'select' => 0,
            'timeout' => 0,
            'persistent' => false,
        ],
    ],
];
```

### 2.4 启动队列消费者

```bash
# 启动队列监听（开发环境）
php think queue:listen

# 启动队列守护进程（生产环境）
php think queue:work --daemon

# 监听特定队列
php think queue:listen --queue ai-image,thumbnail,watermark,video
```

### 2.5 队列任务类

所有队列任务类位于 `app/queue/` 目录：

- `AiImageGenerationJob.php` - AI图片生成
- `BatchThumbnailJob.php` - 批量缩略图
- `BatchWatermarkJob.php` - 批量水印
- `VideoTranscodeJob.php` - 视频转码

---

## 三、视频处理

### 3.1 功能特性

- **视频信息提取**：自动获取时长、分辨率、编码等信息
- **视频转码**：支持多种格式和质量转换
- **封面生成**：从视频中截取封面图
- **多帧预览**：生成多张预览图用于进度条预览
- **异步处理**：支持队列异步转码

### 3.2 前置要求

安装FFmpeg和PHP-FFMpeg库：

```bash
# 安装FFmpeg（Ubuntu/Debian）
sudo apt-get install ffmpeg

# 安装FFmpeg（CentOS/RHEL）
sudo yum install ffmpeg

# 安装FFmpeg（macOS）
brew install ffmpeg

# 安装PHP库
composer require php-ffmpeg/php-ffmpeg
```

配置FFmpeg路径（`VideoProcessingService.php`）：

```php
$this->ffmpeg = FFMpeg::create([
    'ffmpeg.binaries'  => '/usr/bin/ffmpeg',  // Linux
    'ffprobe.binaries' => '/usr/bin/ffprobe', // Linux
    // Windows: 'C:\\ffmpeg\\bin\\ffmpeg.exe'
]);
```

### 3.3 API接口

#### 获取视频信息

```bash
GET /api/video/info?media_id=123

# 响应
{
  "duration": 120,
  "width": 1920,
  "height": 1080,
  "bitrate": 2000000,
  "codec": "h264",
  "fps": 30,
  "has_audio": true,
  "audio_codec": "aac"
}
```

#### 视频转码

```bash
POST /api/video/transcode
{
  "media_id": 123,
  "format": "mp4",
  "quality": "high",  // low/medium/high
  "resolution": "1280x720",
  "async": true  // 推荐异步执行
}
```

#### 生成封面

```bash
POST /api/video/generate-poster
{
  "media_id": 123,
  "time": 5  // 第5秒截图
}
```

#### 生成多帧预览

```bash
POST /api/video/generate-thumbnails
{
  "media_id": 123,
  "frame_count": 9  // 生成9张预览图
}
```

### 3.4 数据库表

```bash
mysql -u root -p cms_database < backend/database/video_processing_schema.sql
```

创建的表：
- `video_transcode_records` - 转码记录
- `video_posters` - 视频封面记录

---

## 四、分片上传

### 4.1 功能特性

- **大文件支持**：支持GB级大文件上传
- **断点续传**：网络中断后可继续上传
- **并发上传**：支持多个分片并发上传
- **哈希校验**：每个分片可单独校验
- **自动清理**：过期会话自动清理
- **进度追踪**：实时查看上传进度

### 4.2 上传流程

```
1. 初始化会话
   ↓
2. 分片上传（可并发）
   ↓
3. 合并分片
   ↓
4. 创建媒体记录
```

### 4.3 完整示例

#### 步骤1：初始化上传会话

```javascript
// 前端代码
const file = document.querySelector('input[type="file"]').files[0];
const chunkSize = 2 * 1024 * 1024; // 2MB

const initRes = await fetch('/api/chunked-upload/init', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    file_name: file.name,
    file_size: file.size,
    mime_type: file.type,
    chunk_size: chunkSize
  })
});

const { upload_id, total_chunks } = await initRes.json();
```

#### 步骤2：分片上传

```javascript
for (let i = 0; i < total_chunks; i++) {
  const start = i * chunkSize;
  const end = Math.min(start + chunkSize, file.size);
  const chunk = file.slice(start, end);

  // 计算分片哈希（可选，用于验证）
  const chunkHash = await calculateMD5(chunk);

  const formData = new FormData();
  formData.append('upload_id', upload_id);
  formData.append('chunk_index', i);
  formData.append('chunk', chunk);
  formData.append('chunk_hash', chunkHash);

  await fetch('/api/chunked-upload/chunk', {
    method: 'POST',
    body: formData
  });

  // 更新进度
  console.log(`进度: ${((i + 1) / total_chunks * 100).toFixed(2)}%`);
}
```

#### 步骤3：合并分片

```javascript
const mergeRes = await fetch('/api/chunked-upload/merge', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    upload_id: upload_id,
    title: '我的视频',
    description: '描述信息',
    category_ids: [1, 2],
    tags: ['标签1', '标签2']
  })
});

const { media, file } = await mergeRes.json();
console.log('上传完成！', media);
```

### 4.4 API接口

#### 初始化会话

```bash
POST /api/chunked-upload/init
{
  "file_name": "large_video.mp4",
  "file_size": 1073741824,  # 1GB
  "mime_type": "video/mp4",
  "chunk_size": 2097152,    # 2MB
  "expiry_hours": 24
}
```

#### 上传分片

```bash
POST /api/chunked-upload/chunk
Content-Type: multipart/form-data

upload_id: "abc123..."
chunk_index: 0
chunk: [binary data]
chunk_hash: "md5_hash_of_chunk"
```

#### 查询进度

```bash
GET /api/chunked-upload/progress?upload_id=abc123

# 响应
{
  "upload_id": "abc123",
  "status": "uploading",
  "total_chunks": 512,
  "uploaded_chunks": 256,
  "progress": 50.0,
  "is_completed": false
}
```

#### 取消上传

```bash
POST /api/chunked-upload/cancel
{
  "upload_id": "abc123"
}
```

### 4.5 数据库表

```bash
mysql -u root -p cms_database < backend/database/chunked_upload_schema.sql
```

创建的表：
- `chunked_upload_sessions` - 上传会话
- `upload_chunks` - 分片记录

### 4.6 定期清理

建议设置定时任务清理过期会话：

```bash
# 添加到crontab
0 2 * * * cd /path/to/cms && php think command ChunkedUploadCleanup
```

创建清理命令（`app/command/ChunkedUploadCleanup.php`）：

```php
<?php
namespace app\command;

use app\service\ChunkedUploadService;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class ChunkedUploadCleanup extends Command
{
    protected function configure()
    {
        $this->setName('chunked:cleanup')
            ->setDescription('清理过期的分片上传会话');
    }

    protected function execute(Input $input, Output $output)
    {
        $service = new ChunkedUploadService();
        $count = $service->cleanupExpiredSessions();

        $output->writeln("清理完成，共清理 {$count} 个过期会话");
    }
}
```

---

## 五、整合使用示例

### 5.1 完整的视频上传与处理流程

```javascript
// 1. 使用分片上传大视频文件
const uploadResult = await uploadLargeFile(videoFile);

// 2. 异步转码为多种格式
await fetch('/api/video/transcode', {
  method: 'POST',
  body: JSON.stringify({
    media_id: uploadResult.media.id,
    format: 'mp4',
    quality: 'high',
    async: true
  })
});

// 3. 生成封面
await fetch('/api/video/generate-poster', {
  method: 'POST',
  body: JSON.stringify({
    media_id: uploadResult.media.id,
    time: 3
  })
});

// 4. 生成多帧预览
await fetch('/api/video/generate-thumbnails', {
  method: 'POST',
  body: JSON.stringify({
    media_id: uploadResult.media.id,
    frame_count: 9
  })
});
```

### 5.2 配置云存储并使用

```javascript
// 1. 配置阿里云OSS
await fetch('/api/storage-config', {
  method: 'POST',
  body: JSON.stringify({
    name: '阿里云OSS',
    driver: 'aliyun_oss',
    config_data: { /* OSS配置 */ },
    is_default: true
  })
});

// 2. 上传文件时自动使用配置的云存储
const formData = new FormData();
formData.append('file', file);
formData.append('storage_config_id', ossConfigId);  // 可选，不填使用默认

await fetch('/api/media/upload', {
  method: 'POST',
  body: formData
});
```

---

## 六、性能优化建议

### 6.1 队列优化

- 使用Redis作为队列驱动（比数据库快）
- 部署多个队列消费者进程
- 根据业务量调整队列优先级

### 6.2 视频处理优化

- 转码任务必须使用队列异步执行
- 配置足够的服务器资源（CPU、内存）
- 考虑使用专门的转码服务器
- 开启FFmpeg硬件加速（GPU）

### 6.3 分片上传优化

- 调整分片大小（2-5MB最佳）
- 实现前端并发上传（3-5个并发）
- 定期清理过期会话
- 使用CDN加速上传

### 6.4 云存储优化

- 配置CDN加速访问
- 启用跨域访问（CORS）
- 使用对象存储的图片处理服务
- 配置生命周期规则自动删除旧文件

---

## 七、故障排查

### 7.1 队列任务不执行

```bash
# 检查队列配置
php think queue:info

# 检查Redis连接
redis-cli ping

# 手动执行一次
php think queue:work --once
```

### 7.2 视频转码失败

```bash
# 检查FFmpeg安装
ffmpeg -version

# 检查文件权限
ls -la /path/to/video/files

# 查看错误日志
tail -f runtime/log/error.log
```

### 7.3 分片上传失败

```bash
# 检查临时目录权限
ls -la runtime/chunked_uploads/

# 检查磁盘空间
df -h

# 清理临时文件
php think chunked:cleanup
```

### 7.4 云存储连接失败

```bash
# 测试连接
POST /api/storage-config/test

# 检查网络
ping oss-cn-hangzhou.aliyuncs.com

# 验证密钥
# 登录云服务控制台检查AccessKey是否正确
```

---

## 八、安全建议

1. **存储配置加密**：敏感信息（AccessKey）应加密存储
2. **上传限制**：设置文件大小、类型限制
3. **权限控制**：验证用户权限后再允许操作
4. **临时文件清理**：定期清理临时文件
5. **CDN防盗链**：配置Referer白名单
6. **签名URL**：私有文件使用带过期时间的签名URL

---

## 九、总结

本次增强为媒体库系统添加了以下生产级功能：

✅ **云存储支持** - 4个云存储适配器 + 统一接口
✅ **队列系统** - 4种队列任务 + 自动重试
✅ **视频处理** - 转码 + 封面生成 + 多帧预览
✅ **分片上传** - 大文件支持 + 断点续传

**新增文件统计**：
- 存储适配器：5个类
- 队列任务：4个类
- 视频处理：3个类
- 分片上传：3个类
- 控制器：4个
- 模型：5个
- 数据库表：9张
- API路由：40+个

**依赖包**：
- aliyuncs/oss-sdk-php
- qcloud/cos-sdk-v5
- qiniu/php-sdk
- php-ffmpeg/php-ffmpeg

系统已具备完整的生产级媒体处理能力！
