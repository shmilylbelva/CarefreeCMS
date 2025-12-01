<?php
declare (strict_types = 1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Option;
use think\facade\Db;
use app\model\MediaFile;
use app\model\MediaLibrary;
use app\service\MediaFileService;

/**
 * 媒体库数据迁移命令
 * 从旧的media表迁移数据到新的media_library系统
 *
 * 使用方法：
 * php think media:migrate                 # 迁移所有数据
 * php think media:migrate --limit=100     # 仅迁移100条
 * php think media:migrate --dry-run       # 试运行，不实际迁移
 */
class MediaMigrate extends Command
{
    protected $fileService;

    protected function configure()
    {
        $this->setName('media:migrate')
            ->setDescription('Migrate media data from legacy table to new media library system')
            ->addOption('limit', 'l', Option::VALUE_OPTIONAL, 'Limit number of records to migrate')
            ->addOption('dry-run', null, Option::VALUE_NONE, 'Run migration in dry-run mode (no actual changes)')
            ->addOption('force', 'f', Option::VALUE_NONE, 'Force migration even if records already exist');
    }

    protected function execute(Input $input, Output $output)
    {
        $this->fileService = new MediaFileService();

        $limit = $input->getOption('limit');
        $dryRun = $input->getOption('dry-run');
        $force = $input->getOption('force');

        if ($dryRun) {
            $output->writeln('<info>Running in DRY-RUN mode - no actual changes will be made</info>');
            $output->writeln('');
        }

        $output->writeln('<comment>Starting media migration from legacy table...</comment>');
        $output->writeln('');

        // 检查legacy表是否存在
        if (!$this->hasLegacyTable()) {
            $output->writeln('<error>Legacy media table (media_legacy) does not exist!</error>');
            return 1;
        }

        // 获取需要迁移的记录
        $query = Db::table('media_legacy');

        if (!$force) {
            // 只迁移未迁移的记录
            $migratedIds = MediaLibrary::whereNotNull('created_at')
                ->column('id');

            if (!empty($migratedIds)) {
                $query->whereNotIn('id', $migratedIds);
            }
        }

        if ($limit) {
            $query->limit((int)$limit);
        }

        $legacyRecords = $query->select();
        $total = count($legacyRecords);

        if ($total === 0) {
            $output->writeln('<info>No records to migrate.</info>');
            return 0;
        }

        $output->writeln("Found <info>{$total}</info> record(s) to migrate.");
        $output->writeln('');

        // 统计数据
        $stats = [
            'total' => $total,
            'success' => 0,
            'skipped' => 0,
            'failed' => 0,
            'deduped' => 0,
        ];

        $progressBar = 0;

        foreach ($legacyRecords as $old) {
            $progressBar++;

            try {
                $output->write("[$progressBar/$total] Migrating media ID {$old['id']}... ");

                // 检查文件是否存在
                $oldFilePath = $old['file_path'];
                $fullPath = app()->getRootPath() . 'html' . DIRECTORY_SEPARATOR . $oldFilePath;

                if (!file_exists($fullPath)) {
                    $output->writeln('<error>SKIPPED (file not found)</error>');
                    $stats['skipped']++;
                    continue;
                }

                if (!$dryRun) {
                    // 计算文件hash
                    $fileHash = MediaFile::calculateHash($fullPath);

                    // 检查文件是否已存在
                    $mediaFile = MediaFile::findByHash($fileHash);

                    if ($mediaFile) {
                        // 文件已存在，增加引用计数
                        $mediaFile->incrementRefCount();
                        $stats['deduped']++;
                        $isDuplicate = true;
                    } else {
                        // 创建新的MediaFile记录
                        $mediaFile = MediaFile::create([
                            'file_hash' => $fileHash,
                            'file_path' => $oldFilePath,
                            'file_name' => $old['file_name'],
                            'file_ext' => pathinfo($old['file_name'], PATHINFO_EXTENSION),
                            'file_size' => $old['file_size'],
                            'mime_type' => $old['mime_type'] ?? 'application/octet-stream',
                            'file_type' => $old['file_type'] ?? 'other',
                            'storage_type' => $old['storage_type'] ?? MediaFile::STORAGE_LOCAL,
                            'width' => $old['width'] ?? null,
                            'height' => $old['height'] ?? null,
                            'ref_count' => 1,
                        ]);
                        $isDuplicate = false;
                    }

                    // 创建MediaLibrary记录（使用原ID）
                    MediaLibrary::create([
                        'id' => $old['id'],
                        'file_id' => $mediaFile->id,
                        'site_id' => $old['site_id'] ?? 1,
                        'user_id' => $old['user_id'] ?? 1,
                        'title' => $old['file_name'],
                        'description' => null,
                        'alt_text' => null,
                        'source' => MediaLibrary::SOURCE_UPLOAD,
                        'status' => MediaLibrary::STATUS_ACTIVE,
                        'is_public' => 1,
                        'created_at' => $old['create_time'] ?? date('Y-m-d H:i:s'),
                        'updated_at' => $old['update_time'] ?? date('Y-m-d H:i:s'),
                    ]);

                    if ($isDuplicate) {
                        $output->writeln('<comment>OK (deduplicated)</comment>');
                    } else {
                        $output->writeln('<info>OK</info>');
                    }

                    $stats['success']++;
                } else {
                    $output->writeln('<comment>OK (dry-run)</comment>');
                    $stats['success']++;
                }

            } catch (\Exception $e) {
                $output->writeln('<error>FAILED: ' . $e->getMessage() . '</error>');
                $stats['failed']++;
            }
        }

        // 输出统计信息
        $output->writeln('');
        $output->writeln('<comment>Migration Summary:</comment>');
        $output->writeln('--------------------------------------------------');
        $output->writeln("Total records:       <info>{$stats['total']}</info>");
        $output->writeln("Successfully migrated: <info>{$stats['success']}</info>");
        $output->writeln("Deduplicated:        <comment>{$stats['deduped']}</comment>");
        $output->writeln("Skipped:             <comment>{$stats['skipped']}</comment>");
        $output->writeln("Failed:              <error>{$stats['failed']}</error>");
        $output->writeln('--------------------------------------------------');

        if ($dryRun) {
            $output->writeln('');
            $output->writeln('<info>This was a DRY-RUN. No actual changes were made.</info>');
            $output->writeln('<info>Run without --dry-run to perform actual migration.</info>');
        }

        $output->writeln('');
        $output->writeln('<info>Migration completed!</info>');

        return 0;
    }

    /**
     * 检查legacy表是否存在
     */
    protected function hasLegacyTable(): bool
    {
        try {
            $tables = Db::query('SHOW TABLES LIKE "media_legacy"');
            return !empty($tables);
        } catch (\Exception $e) {
            return false;
        }
    }
}
