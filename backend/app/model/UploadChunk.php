<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 上传分片模型
 */
class UploadChunk extends Model
{
    protected $table = 'upload_chunks';

    protected $autoWriteTimestamp = false;

    // 状态常量
    const STATUS_UPLOADED = 'uploaded';
    const STATUS_VERIFIED = 'verified';
    const STATUS_FAILED = 'failed';

    /**
     * 关联会话
     */
    public function session()
    {
        return $this->belongsTo(ChunkedUploadSession::class, 'session_id', 'id');
    }

    /**
     * 验证分片哈希
     */
    public function verifyHash(): bool
    {
        if (empty($this->chunk_hash) || !file_exists($this->file_path)) {
            return false;
        }

        $actualHash = hash_file('md5', $this->file_path);

        if ($actualHash === $this->chunk_hash) {
            $this->status = self::STATUS_VERIFIED;
            $this->save();
            return true;
        }

        return false;
    }
}
