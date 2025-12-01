<?php

namespace app\model;

use think\Model;

/**
 * 敏感词模型
 */
class SensitiveWord extends Model
{
    protected $name = 'sensitive_words';

    // 处理级别常量
    const LEVEL_WARN = 1;       // 提示警告
    const LEVEL_REPLACE = 2;    // 替换
    const LEVEL_REJECT = 3;     // 拒绝发布

    // 分类常量
    const CATEGORY_POLITICS = 'politics';  // 政治
    const CATEGORY_PORN = 'porn';          // 色情
    const CATEGORY_VIOLENCE = 'violence';  // 暴力
    const CATEGORY_AD = 'ad';              // 广告
    const CATEGORY_ABUSE = 'abuse';        // 辱骂
    const CATEGORY_GENERAL = 'general';    // 其他

    /**
     * 获取启用的敏感词列表
     * @return array
     */
    public static function getEnabledWords(): array
    {
        return self::where('is_enabled', 1)
            ->order('level desc, word')
            ->column('word,level,replacement,category', 'id');
    }

    /**
     * 按分类获取敏感词
     * @param string $category
     * @return array
     */
    public static function getByCategory(string $category): array
    {
        return self::where('is_enabled', 1)
            ->where('category', $category)
            ->order('level desc')
            ->select()
            ->toArray();
    }

    /**
     * 批量导入敏感词
     * @param array $words
     * @param array $options
     * @return int 成功导入数量
     */
    public static function batchImport(array $words, array $options = []): int
    {
        $level = $options['level'] ?? self::LEVEL_REPLACE;
        $category = $options['category'] ?? self::CATEGORY_GENERAL;
        $replacement = $options['replacement'] ?? '***';

        $count = 0;
        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word)) {
                continue;
            }

            try {
                self::create([
                    'word' => $word,
                    'level' => $level,
                    'category' => $category,
                    'replacement' => $replacement,
                    'is_enabled' => 1
                ]);
                $count++;
            } catch (\Exception $e) {
                // 忽略重复的词
                continue;
            }
        }

        return $count;
    }

    /**
     * 增加命中次数
     * @param int $id
     * @return void
     */
    public static function incrementHitCount(int $id): void
    {
        self::where('id', $id)->inc('hit_count')->update();
    }

    /**
     * 获取分类选项
     * @return array
     */
    public static function getCategoryOptions(): array
    {
        return [
            self::CATEGORY_POLITICS => '政治敏感',
            self::CATEGORY_PORN => '色情内容',
            self::CATEGORY_VIOLENCE => '暴力内容',
            self::CATEGORY_AD => '广告内容',
            self::CATEGORY_ABUSE => '辱骂内容',
            self::CATEGORY_GENERAL => '其他'
        ];
    }

    /**
     * 获取级别选项
     * @return array
     */
    public static function getLevelOptions(): array
    {
        return [
            self::LEVEL_WARN => '提示警告',
            self::LEVEL_REPLACE => '替换处理',
            self::LEVEL_REJECT => '拒绝发布'
        ];
    }

    /**
     * 获取统计信息
     * @return array
     */
    public static function getStatistics(): array
    {
        $total = self::count();
        $enabled = self::where('is_enabled', 1)->count();
        $byCategory = self::where('is_enabled', 1)
            ->group('category')
            ->column('COUNT(*) as count', 'category');

        return [
            'total' => $total,
            'enabled' => $enabled,
            'disabled' => $total - $enabled,
            'by_category' => $byCategory
        ];
    }
}
