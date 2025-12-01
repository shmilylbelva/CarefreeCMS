<?php

namespace app\model;

/**
 * 通用关联模型
 * 替代：article_categories, article_tags, topic_articles
 */
class Relation extends SiteModel
{
    protected $name = 'relations';

    protected $autoWriteTimestamp = 'datetime';
    protected $createTime = 'created_at';
    protected $updateTime = false;

    protected $type = [
        'source_id' => 'integer',
        'target_id' => 'integer',
        'sort' => 'integer',
        'site_id' => 'integer',
        'extra' => 'json',
    ];

    // 关联类型常量
    const TYPE_ARTICLE_CATEGORY = 'article-category';
    const TYPE_ARTICLE_TAG = 'article-tag';
    const TYPE_TOPIC_ARTICLE = 'topic-article';

    // 关系类型常量（用于文章分类的主/副分类）
    const RELATION_MAIN = 'main';
    const RELATION_SUB = 'sub';
    const RELATION_DEFAULT = 'default';

    /**
     * 添加文章分类关联
     */
    public static function addArticleCategory($articleId, $categoryId, $isMain = 0, $siteId = 1)
    {
        // 确保ID都是整数类型,避免类型不一致导致的问题
        $articleId = (int)$articleId;
        $categoryId = (int)$categoryId;
        $siteId = (int)$siteId;

        // 检查是否已存在，避免重复插入
        $existing = self::where('source_type', 'article')
            ->where('source_id', $articleId)
            ->where('target_type', 'category')
            ->where('target_id', $categoryId)
            ->find();

        if ($existing) {
            // 如果已存在，更新relation_type
            $existing->relation_type = $isMain ? self::RELATION_MAIN : self::RELATION_SUB;
            $existing->save();
            return $existing;
        }

        return self::create([
            'source_type' => 'article',
            'source_id' => $articleId,
            'target_type' => 'category',
            'target_id' => $categoryId,
            'relation_type' => $isMain ? self::RELATION_MAIN : self::RELATION_SUB,
            'site_id' => $siteId,
        ]);
    }

    /**
     * 添加文章标签关联
     */
    public static function addArticleTag($articleId, $tagId, $siteId = 1)
    {
        // 检查是否已存在，避免重复插入
        $existing = self::where('source_type', 'article')
            ->where('source_id', $articleId)
            ->where('target_type', 'tag')
            ->where('target_id', $tagId)
            ->find();

        if ($existing) {
            return $existing;
        }

        return self::create([
            'source_type' => 'article',
            'source_id' => $articleId,
            'target_type' => 'tag',
            'target_id' => $tagId,
            'relation_type' => self::RELATION_DEFAULT,
            'site_id' => $siteId,
        ]);
    }

    /**
     * 添加专题文章关联
     */
    public static function addTopicArticle($topicId, $articleId, $sort = 0, $siteId = 1)
    {
        // 检查是否已存在，避免重复插入
        $existing = self::where('source_type', 'topic')
            ->where('source_id', $topicId)
            ->where('target_type', 'article')
            ->where('target_id', $articleId)
            ->find();

        if ($existing) {
            // 如果已存在，更新排序
            $existing->sort = $sort;
            $existing->save();
            return $existing;
        }

        return self::create([
            'source_type' => 'topic',
            'source_id' => $topicId,
            'target_type' => 'article',
            'target_id' => $articleId,
            'sort' => $sort,
            'relation_type' => self::RELATION_DEFAULT,
            'site_id' => $siteId,
        ]);
    }

    /**
     * 获取文章的所有分类ID
     */
    public static function getArticleCategoryIds($articleId)
    {
        return self::where('source_type', 'article')
            ->where('source_id', $articleId)
            ->where('target_type', 'category')
            ->column('target_id');
    }

    /**
     * 获取文章的主分类ID
     */
    public static function getArticleMainCategoryId($articleId)
    {
        $relation = self::where('source_type', 'article')
            ->where('source_id', $articleId)
            ->where('target_type', 'category')
            ->where('relation_type', self::RELATION_MAIN)
            ->find();

        return $relation ? $relation->target_id : null;
    }

    /**
     * 获取文章的所有标签ID
     */
    public static function getArticleTagIds($articleId)
    {
        return self::where('source_type', 'article')
            ->where('source_id', $articleId)
            ->where('target_type', 'tag')
            ->column('target_id');
    }

    /**
     * 获取专题的所有文章ID
     */
    public static function getTopicArticleIds($topicId)
    {
        return self::where('source_type', 'topic')
            ->where('source_id', $topicId)
            ->where('target_type', 'article')
            ->order('sort', 'asc')
            ->column('target_id');
    }

    /**
     * 删除文章的所有分类关联
     * 注意：删除所有站点的关联记录，不受当前站点限制
     */
    public static function deleteArticleCategories($articleId)
    {
        return self::withoutSiteScope()
            ->where('source_type', 'article')
            ->where('source_id', $articleId)
            ->where('target_type', 'category')
            ->delete();
    }

    /**
     * 删除文章的所有标签关联
     * 注意：删除所有站点的关联记录，不受当前站点限制
     */
    public static function deleteArticleTags($articleId)
    {
        return self::withoutSiteScope()
            ->where('source_type', 'article')
            ->where('source_id', $articleId)
            ->where('target_type', 'tag')
            ->delete();
    }

    /**
     * 删除专题的所有文章关联
     * 注意：删除所有站点的关联记录，不受当前站点限制
     */
    public static function deleteTopicArticles($topicId)
    {
        return self::withoutSiteScope()
            ->where('source_type', 'topic')
            ->where('source_id', $topicId)
            ->where('target_type', 'article')
            ->delete();
    }

    /**
     * 更新文章在专题中的排序
     */
    public static function updateTopicArticleSort($topicId, $articleId, $sort)
    {
        return self::where('source_type', 'topic')
            ->where('source_id', $topicId)
            ->where('target_type', 'article')
            ->where('target_id', $articleId)
            ->update(['sort' => $sort]);
    }

    /**
     * 获取文章在专题中的排序
     */
    public static function getTopicArticleSort($topicId, $articleId)
    {
        $relation = self::where('source_type', 'topic')
            ->where('source_id', $topicId)
            ->where('target_type', 'article')
            ->where('target_id', $articleId)
            ->find();

        return $relation ? $relation->sort : 0;
    }

    /**
     * 批量保存文章分类（先删除旧的，再添加新的）
     */
    public static function saveArticleCategories($articleId, $categoryIds, $mainCategoryId = null, $siteId = 1)
    {
        // 确保ID都是整数类型
        $articleId = (int)$articleId;
        $mainCategoryId = $mainCategoryId !== null ? (int)$mainCategoryId : null;
        $siteId = (int)$siteId;

        // 确保分类ID数组中的值都是整数
        $categoryIds = array_map('intval', $categoryIds);

        // 删除旧关联
        self::deleteArticleCategories($articleId);

        // 添加新关联
        foreach ($categoryIds as $categoryId) {
            // 使用严格比较(===)确保类型一致
            $isMain = ($categoryId === $mainCategoryId) ? 1 : 0;
            self::addArticleCategory($articleId, $categoryId, $isMain, $siteId);
        }

        return true;
    }

    /**
     * 批量保存文章标签（先删除旧的，再添加新的）
     */
    public static function saveArticleTags($articleId, $tagIds, $siteId = 1)
    {
        // 删除旧关联
        self::deleteArticleTags($articleId);

        // 添加新关联
        foreach ($tagIds as $tagId) {
            self::addArticleTag($articleId, $tagId, $siteId);
        }

        return true;
    }

    /**
     * 批量保存专题文章（先删除旧的，再添加新的）
     */
    public static function saveTopicArticles($topicId, $articleIds, $siteId = 1)
    {
        // 删除旧关联
        self::deleteTopicArticles($topicId);

        // 添加新关联
        $sort = 0;
        foreach ($articleIds as $articleId) {
            self::addTopicArticle($topicId, $articleId, $sort++, $siteId);
        }

        return true;
    }
}
