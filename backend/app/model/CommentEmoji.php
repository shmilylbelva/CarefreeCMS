<?php

namespace app\model;

use think\Model;

/**
 * 评论表情模型
 */
class CommentEmoji extends Model
{
    protected $name = 'comment_emojis';
    protected $autoWriteTimestamp = true;

    protected $type = [
        'sort'       => 'integer',
        'is_enabled' => 'integer',
        'use_count'  => 'integer',
    ];

    /**
     * 获取所有启用的表情
     *
     * @param string|null $category 分类筛选
     * @return array
     */
    public static function getEnabled(?string $category = null): array
    {
        $query = self::where('is_enabled', 1)
            ->order('sort', 'asc')
            ->order('id', 'asc');

        if ($category) {
            $query->where('category', $category);
        }

        return $query->select()->toArray();
    }

    /**
     * 通过code获取表情
     *
     * @param string $code 表情代码
     * @return Model|null
     */
    public static function getByCode(string $code): ?Model
    {
        return self::where('code', $code)->find();
    }

    /**
     * 增加使用次数
     *
     * @param int|string $codeOrId 表情ID或代码
     * @return bool
     */
    public static function incrementUseCount($codeOrId): bool
    {
        if (is_numeric($codeOrId)) {
            $emoji = self::find($codeOrId);
        } else {
            $emoji = self::where('code', $codeOrId)->find();
        }

        if ($emoji) {
            $emoji->use_count += 1;
            return $emoji->save();
        }

        return false;
    }

    /**
     * 替换文本中的表情代码为Unicode
     *
     * @param string $content 内容
     * @return string
     */
    public static function replaceEmojis(string $content): string
    {
        $emojis = self::where('is_enabled', 1)->select();

        foreach ($emojis as $emoji) {
            if ($emoji->unicode) {
                $content = str_replace($emoji->code, $emoji->unicode, $content);
            } elseif ($emoji->image_url) {
                $img = '<img src="' . $emoji->image_url . '" alt="' . $emoji->name . '" class="emoji" />';
                $content = str_replace($emoji->code, $img, $content);
            }
        }

        return $content;
    }

    /**
     * 获取表情分类列表
     *
     * @return array
     */
    public static function getCategories(): array
    {
        return self::where('is_enabled', 1)
            ->group('category')
            ->column('category');
    }

    /**
     * 获取热门表情（使用次数最多）
     *
     * @param int $limit 数量限制
     * @return array
     */
    public static function getHotEmojis(int $limit = 10): array
    {
        return self::where('is_enabled', 1)
            ->where('use_count', '>', 0)
            ->order('use_count', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }
}
