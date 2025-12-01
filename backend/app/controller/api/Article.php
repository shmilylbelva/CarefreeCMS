<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\common\Logger;
use app\model\Article as ArticleModel;
use app\model\Relation;
use app\model\ArticleVersion;
use app\model\Tag;
use app\model\OperationLog;
use app\service\SensitiveWordService;
use app\service\MediaUsageService;
use think\Request;
use think\facade\Db;
use think\facade\Log;

/**
 * 文章管理控制器
 */
class Article extends BaseController
{
    /**
     * 文章列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);

        // 构建查询 - 禁用自动站点过滤
        // 注意：不预加载category，因为Category模型会应用站点过滤，导致跨站点分类无法加载
        $query = ArticleModel::withoutSiteScope()
            ->with([
                'user',
                'site'
            ]);

        // 应用查询条件（使用统一的方法）
        $query = $this->applyArticleFilters($query, $request);

        // 排序：置顶优先，然后按发布时间倒序
        $query->order(['is_top' => 'desc', 'sort' => 'desc', 'publish_time' => 'desc']);

        // 使用 paginate() 自动处理分页和计数
        $result = $query->paginate([
            'list_rows' => $pageSize,
            'page' => $page,
        ]);

        $list = $result->items();
        $total = $result->total();

        // 优化：批量查询副分类，避免在应用层过滤
        // 确保$listData是纯数组
        if (is_array($list)) {
            // 如果已经是数组，检查第一个元素
            if (!empty($list) && is_object($list[0])) {
                // 元素是对象，转换为数组
                $listData = array_map(function($item) {
                    return is_object($item) && method_exists($item, 'toArray') ? $item->toArray() : (array)$item;
                }, $list);
            } else {
                $listData = $list;
            }
        } else {
            $listData = $list->toArray();
        }

        if (!empty($listData)) {
            $articleIds = array_column($listData, 'id');

            // 批量查询主分类（禁用站点过滤，允许跨站点查询）
            $categoryIds = array_filter(array_unique(array_column($listData, 'category_id')));
            if (!empty($categoryIds)) {
                $categories = \app\model\Category::withoutSiteScope()
                    ->whereIn('id', $categoryIds)
                    ->field('id,name,slug')
                    ->select()
                    ->toArray();

                // 构建分类映射表
                $categoryMap = array_column($categories, null, 'id');

                // 添加分类到文章数据中
                foreach ($listData as &$item) {
                    if (!empty($item['category_id']) && isset($categoryMap[$item['category_id']])) {
                        $item['category'] = $categoryMap[$item['category_id']];
                    } else {
                        $item['category'] = null;
                    }
                }
            } else {
                // 没有分类，添加null
                foreach ($listData as &$item) {
                    $item['category'] = null;
                }
            }

            // 批量查询所有文章的副分类关联
            $subCategoryRelations = Relation::withoutSiteScope()
                ->where('source_type', 'article')
                ->whereIn('source_id', $articleIds)
                ->where('target_type', 'category')
                ->where('relation_type', 'sub')
                ->select()
                ->toArray();

            // 如果有副分类，批量查询分类信息
            if (!empty($subCategoryRelations)) {
                $subCategoryIds = array_unique(array_column($subCategoryRelations, 'target_id'));
                $categories = \app\model\Category::withoutSiteScope()
                    ->whereIn('id', $subCategoryIds)
                    ->field('id,name,slug')
                    ->select()
                    ->toArray();

                // 构建分类映射表
                $categoryMap = array_column($categories, null, 'id');

                // 构建文章副分类映射表
                $articleSubCategories = [];
                foreach ($subCategoryRelations as $rel) {
                    $articleId = $rel['source_id'];
                    $categoryId = $rel['target_id'];
                    if (isset($categoryMap[$categoryId])) {
                        if (!isset($articleSubCategories[$articleId])) {
                            $articleSubCategories[$articleId] = [];
                        }
                        $articleSubCategories[$articleId][] = $categoryMap[$categoryId];
                    }
                }

                // 添加副分类到文章数据中
                foreach ($listData as &$item) {
                    $item['sub_categories'] = $articleSubCategories[$item['id']] ?? [];
                }
            } else {
                // 没有副分类关联，添加空数组
                foreach ($listData as &$item) {
                    $item['sub_categories'] = [];
                }
            }
        }

        return Response::paginate($listData, $total, $page, $pageSize);
    }

    /**
     * 文章详情
     */
    public function read($id)
    {
        // 禁用自动站点过滤，允许查看所有站点的文章
        // 优化N+1查询：只加载必要字段
        $article = ArticleModel::withoutSiteScope()
            ->with(['user'])
            ->find($id);

        if (!$article) {
            return Response::notFound('文章不存在');
        }

        $articleData = $article->toArray();

        // 手动查询主分类（禁用站点过滤）
        if (!empty($articleData['category_id'])) {
            $category = \app\model\Category::withoutSiteScope()
                ->where('id', $articleData['category_id'])
                ->field('id,name,slug')
                ->find();
            $articleData['category'] = $category ? $category->toArray() : null;
        } else {
            $articleData['category'] = null;
        }

        // 手动查询标签（禁用站点过滤）
        $tagRelations = \app\model\Relation::withoutSiteScope()
            ->where('source_type', 'article')
            ->where('source_id', $id)
            ->where('target_type', 'tag')
            ->column('target_id');
        if (!empty($tagRelations)) {
            $articleData['tags'] = \app\model\Tag::withoutSiteScope()
                ->whereIn('id', $tagRelations)
                ->select()
                ->toArray();
        } else {
            $articleData['tags'] = [];
        }

        // 手动查询分类（禁用站点过滤）
        $categoryRelations = \app\model\Relation::withoutSiteScope()
            ->where('source_type', 'article')
            ->where('source_id', $id)
            ->where('target_type', 'category')
            ->select()
            ->toArray();
        if (!empty($categoryRelations)) {
            $categoryIds = array_column($categoryRelations, 'target_id');
            $categories = \app\model\Category::withoutSiteScope()
                ->whereIn('id', $categoryIds)
                ->select()
                ->toArray();

            // 添加关联类型信息
            $categoryMap = [];
            foreach ($categoryRelations as $rel) {
                $categoryMap[$rel['target_id']] = $rel['relation_type'];
            }
            foreach ($categories as &$cat) {
                $cat['pivot'] = ['relation_type' => $categoryMap[$cat['id']] ?? 'main'];
            }

            $articleData['categories'] = $categories;

            // 提取副分类信息
            $articleData['sub_categories'] = array_filter($categories, function($cat) {
                return isset($cat['pivot']['relation_type']) && $cat['pivot']['relation_type'] == 'sub';
            });
            $articleData['sub_categories'] = array_values($articleData['sub_categories']);
        } else {
            $articleData['categories'] = [];
            $articleData['sub_categories'] = [];
        }

        // 手动查询专题（禁用站点过滤）
        $topicRelations = \app\model\Relation::withoutSiteScope()
            ->where('source_type', 'topic')
            ->where('target_type', 'article')
            ->where('target_id', $id)
            ->column('source_id');
        if (!empty($topicRelations)) {
            $articleData['topics'] = \app\model\Topic::withoutSiteScope()
                ->whereIn('id', $topicRelations)
                ->select()
                ->toArray();
        } else {
            $articleData['topics'] = [];
        }

        // 生成完整的封面图片URL
        if (!empty($articleData['cover_image'])) {
            if (!str_starts_with($articleData['cover_image'], 'http')) {
                // 使用当前站点的site_url字段
                $site = \app\service\SiteContextService::getSite();
                $siteUrl = $site ? $site->site_url : '';
                if (!empty($siteUrl)) {
                    $articleData['cover_image'] = rtrim($siteUrl, '/') . '/' . $articleData['cover_image'];
                } else {
                    $articleData['cover_image'] = request()->domain() . '/html/' . $articleData['cover_image'];
                }
            }
        }

        return Response::success($articleData);
    }

    /**
     * 创建文章
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['title']) || empty($data['content']) || empty($data['category_id'])) {
            return Response::error('标题、内容和分类不能为空');
        }

        // 验证标题长度
        if (mb_strlen($data['title'], 'utf-8') > 200) {
            return Response::error('标题不能超过200个字符');
        }

        // 验证内容长度（防止过大的内容）
        if (mb_strlen($data['content'], 'utf-8') > 100000) {
            return Response::error('内容不能超过10万个字符');
        }

        // 验证分类ID格式
        if (!is_numeric($data['category_id']) || (int)$data['category_id'] <= 0) {
            return Response::error('分类ID无效');
        }
        $data['category_id'] = (int)$data['category_id'];

        // 敏感词检测
        $sensitiveService = new SensitiveWordService();
        $userId = $request->user['id'] ?? 0;

        // 检测标题
        $titleResult = $sensitiveService->checkAndHandle(
            'article',
            0,  // 新文章，ID为0
            $userId,
            $data['title'],
            true  // 自动替换
        );

        if (!$titleResult['allowed']) {
            return Response::error($titleResult['message']);
        }
        $data['title'] = $titleResult['content'];

        // 检测内容
        $contentResult = $sensitiveService->checkAndHandle(
            'article',
            0,
            $userId,
            $data['content'],
            true  // 自动替换
        );

        if (!$contentResult['allowed']) {
            return Response::error($contentResult['message']);
        }
        $data['content'] = $contentResult['content'];

        // 检测摘要（如果有）
        if (!empty($data['description'])) {
            $descResult = $sensitiveService->checkAndHandle(
                'article',
                0,
                $userId,
                $data['description'],
                true
            );

            if (!$descResult['allowed']) {
                return Response::error($descResult['message']);
            }
            $data['description'] = $descResult['content'];
        }

        // 获取标签数据（支持ID或名称）
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        // 获取专题数据
        $topics = $data['topics'] ?? [];
        unset($data['topics']);

        // 获取副分类数据,确保是数组且元素为整数
        $subCategories = $data['sub_categories'] ?? [];
        if (!is_array($subCategories)) {
            $subCategories = [];
        } else {
            // 确保所有副分类ID都是整数
            $subCategories = array_map('intval', array_filter($subCategories));
        }
        unset($data['sub_categories']);

        // 主分类ID(确保是整数类型)
        $mainCategoryId = (int)$data['category_id'];

        // 添加作者信息
        $data['user_id'] = $request->user['id'];

        // 如果是发布状态，设置发布时间
        if (isset($data['status']) && $data['status'] == 1 && empty($data['publish_time'])) {
            $data['publish_time'] = date('Y-m-d H:i:s');
        }

        // 自动提取SEO信息
        $this->autoExtractSeoInfo($data);

        // 多站点支持：获取站点IDs（数组或单个值）
        $siteIds = [];
        if (isset($data['site_ids']) && is_array($data['site_ids']) && !empty($data['site_ids'])) {
            // 前端传递的是多选站点数组
            $siteIds = $data['site_ids'];
            unset($data['site_ids']);
            unset($data['site_id']); // 移除单选字段
        } elseif (isset($data['site_id'])) {
            // 向后兼容：单站点
            $siteIds = [$data['site_id']];
        } else {
            // 默认站点
            $siteIds = [1];
        }

        // 预先查询分类、标签、专题的站点归属信息，用于多站点创建时过滤
        $categorySiteMap = [];
        $tagSiteMap = [];
        $topicSiteMap = [];

        if (count($siteIds) > 1) {
            // 查询主分类和副分类的站点归属
            $allCategoryIds = array_merge([$mainCategoryId], $subCategories);
            // 转换为整数并去重，避免类型不一致导致的重复
            $allCategoryIds = array_filter(array_unique(array_map('intval', $allCategoryIds)));
            if (!empty($allCategoryIds)) {
                $categoryRecords = \app\model\Category::withoutSiteScope()->whereIn('id', $allCategoryIds)->select();
                foreach ($categoryRecords as $cat) {
                    $categorySiteMap[$cat->id] = $cat->site_id;
                }
            }

            // 查询标签的站点归属
            if (!empty($tags) && is_array($tags)) {
                $tagIds = array_filter($tags, 'is_numeric');
                if (!empty($tagIds)) {
                    $tagRecords = \app\model\Tag::withoutSiteScope()->whereIn('id', $tagIds)->select();
                    foreach ($tagRecords as $tag) {
                        $tagSiteMap[$tag->id] = $tag->site_id;
                    }
                }
            }

            // 查询专题的站点归属
            if (!empty($topics) && is_array($topics)) {
                $topicRecords = \app\model\Topic::withoutSiteScope()->whereIn('id', $topics)->select();
                foreach ($topicRecords as $topic) {
                    $topicSiteMap[$topic->id] = $topic->site_id;
                }
            }
        }

        Db::startTrans();
        try {
            $createdArticles = [];
            $sourceId = null;

            // 为每个站点创建文章副本
            foreach ($siteIds as $index => $siteId) {
                $articleData = $data;
                $articleData['site_id'] = $siteId;

                // 第一个是主记录，后续记录设置 source_id
                if ($index > 0 && $sourceId) {
                    $articleData['source_id'] = $sourceId;
                }

                // 创建文章
                $article = ArticleModel::create($articleData);

                // 第一个记录作为源记录
                if ($index === 0) {
                    $sourceId = $article->id;
                }

                $createdArticles[] = $article;

                // 过滤并关联标签（只关联属于当前站点的标签）
                if (!empty($tags) && is_array($tags)) {
                    $tagIds = $this->processTagsWithAutoCreate($tags, $siteId);
                    // 如果是多站点创建，过滤出属于当前站点的标签
                    if (count($siteIds) > 1) {
                        $tagIds = array_filter($tagIds, function($tagId) use ($tagSiteMap, $siteId) {
                            return isset($tagSiteMap[$tagId]) && $tagSiteMap[$tagId] == $siteId;
                        });
                    }
                    if (!empty($tagIds)) {
                        Relation::saveArticleTags($article->id, $tagIds, $siteId);
                    }
                }

                // 过滤并关联专题（只关联属于当前站点的专题）
                if (!empty($topics) && is_array($topics)) {
                    $filteredTopics = $topics;
                    // 如果是多站点创建，过滤出属于当前站点的专题
                    if (count($siteIds) > 1) {
                        $filteredTopics = array_filter($topics, function($topicId) use ($topicSiteMap, $siteId) {
                            return isset($topicSiteMap[$topicId]) && $topicSiteMap[$topicId] == $siteId;
                        });
                    }
                    // 专题关联（目前只支持单个专题）
                    if (!empty($filteredTopics)) {
                        Relation::saveTopicArticles($filteredTopics[0] ?? 0, [$article->id], $siteId);
                    }
                }

                // 检查副分类功能是否开启（从站点配置中读取）
                $site = \app\model\Site::find($siteId);
                $subCategoryEnabled = $site ? $site->article_sub_category : 'close';

                // 过滤主分类和副分类（只使用属于当前站点的分类）
                $filteredMainCategoryId = null;
                $filteredSubCategories = [];

                if (count($siteIds) > 1) {
                    // 多站点创建：过滤分类
                    // 检查主分类是否属于当前站点
                    if (isset($categorySiteMap[$mainCategoryId]) && $categorySiteMap[$mainCategoryId] == $siteId) {
                        $filteredMainCategoryId = $mainCategoryId;
                    }
                    // 过滤副分类
                    $filteredSubCategories = array_filter($subCategories, function($catId) use ($categorySiteMap, $siteId) {
                        return isset($categorySiteMap[$catId]) && $categorySiteMap[$catId] == $siteId;
                    });
                } else {
                    // 单站点创建：使用全部分类
                    $filteredMainCategoryId = $mainCategoryId;
                    $filteredSubCategories = $subCategories;
                }

                // 如果有主分类，则保存分类关联
                if ($filteredMainCategoryId) {
                    if ($subCategoryEnabled === 'open') {
                        // 合并主分类和副分类
                        $allCategories = array_merge([$filteredMainCategoryId], $filteredSubCategories);
                        // 转换为整数并去重，避免类型不一致导致的重复
                        $allCategories = array_values(array_unique(array_map('intval', $allCategories)));
                        Relation::saveArticleCategories($article->id, $allCategories, $filteredMainCategoryId, $siteId);
                    } else {
                        // 仅保存主分类
                        Relation::saveArticleCategories($article->id, [$filteredMainCategoryId], $filteredMainCategoryId, $siteId);
                    }
                }
                // 注意：如果当前站点没有匹配的主分类，则该站点的文章不会有分类关联

                // 创建初始版本（标签关系会自动延迟加载）
                ArticleVersion::createFromArticle($article, $request->user['id'], '初始版本');

                // 记录日志
                Logger::create(OperationLog::MODULE_ARTICLE, '文章', $article->id);
            }

            Db::commit();

            // 记录媒体使用情况
            $usageService = new MediaUsageService();
            foreach ($createdArticles as $article) {
                try {
                    // 从内容中提取并记录媒体使用
                    $usageService->recordUsageFromContent(
                        $article->content,
                        'article',
                        $article->id,
                        'content'
                    );

                    // 记录缩略图的使用
                    if (!empty($article->thumb)) {
                        $thumbMediaIds = $usageService->extractMediaIds($article->thumb);
                        if (!empty($thumbMediaIds)) {
                            $usageService->recordUsageFromMediaIds(
                                $thumbMediaIds,
                                'article',
                                $article->id,
                                'thumb'
                            );
                        }
                    }

                    // 记录封面图片的使用
                    if (!empty($article->cover_image)) {
                        $coverMediaIds = $usageService->extractMediaIds($article->cover_image);
                        if (!empty($coverMediaIds)) {
                            $usageService->recordUsageFromMediaIds(
                                $coverMediaIds,
                                'article',
                                $article->id,
                                'cover_image'
                            );
                        }
                    }

                    // 记录图片集合的使用
                    if (!empty($article->images)) {
                        // images可能是JSON数组
                        $imagesContent = is_string($article->images) ? $article->images : json_encode($article->images);
                        $imageMediaIds = $usageService->extractMediaIds($imagesContent);
                        if (!empty($imageMediaIds)) {
                            $usageService->recordUsageFromMediaIds(
                                $imageMediaIds,
                                'article',
                                $article->id,
                                'images'
                            );
                        }
                    }

                    // 记录OG图片的使用
                    if (!empty($article->og_image)) {
                        $ogMediaIds = $usageService->extractMediaIds($article->og_image);
                        if (!empty($ogMediaIds)) {
                            $usageService->recordUsageFromMediaIds(
                                $ogMediaIds,
                                'article',
                                $article->id,
                                'og_image'
                            );
                        }
                    }
                } catch (\Exception $e) {
                    // 记录失败不影响文章创建
                    Log::error('记录文章媒体使用失败: ' . $e->getMessage());
                }
            }

            $message = count($createdArticles) > 1
                ? "文章创建成功，已为 " . count($createdArticles) . " 个站点创建副本"
                : '文章创建成功';

            return Response::success([
                'id' => $createdArticles[0]->id,
                'count' => count($createdArticles),
                'ids' => array_map(fn($a) => $a->id, $createdArticles)
            ], $message);

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('文章创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新文章
     */
    public function update(Request $request, $id)
    {
        // 禁用自动站点过滤
        $article = ArticleModel::withoutSiteScope()->find($id);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        // 审计日志：保存修改前的值
        $oldValues = [
            'title' => $article->title,
            'category_id' => $article->category_id,
            'status' => $article->status,
            'is_top' => $article->is_top,
            'is_recommend' => $article->is_recommend,
            'is_hot' => $article->is_hot,
            'summary' => $article->summary,
            'seo_keywords' => $article->seo_keywords,
            'seo_description' => $article->seo_description,
        ];

        // 关键修复：确保模型实例也禁用站点过滤
        // 否则 save() 内部调用 db() 时会重新应用站点过滤，导致误更新
        $reflection = new \ReflectionObject($article);
        if ($reflection->hasProperty('multiSiteEnabled')) {
            $property = $reflection->getProperty('multiSiteEnabled');
            $property->setAccessible(true);
            $property->setValue($article, false);
        }

        $data = $request->post();

        // 验证标题长度（如果有更新）
        if (isset($data['title'])) {
            if (empty($data['title'])) {
                return Response::error('标题不能为空');
            }
            if (mb_strlen($data['title'], 'utf-8') > 200) {
                return Response::error('标题不能超过200个字符');
            }
        }

        // 验证内容长度（如果有更新）
        if (isset($data['content'])) {
            if (empty($data['content'])) {
                return Response::error('内容不能为空');
            }
            if (mb_strlen($data['content'], 'utf-8') > 100000) {
                return Response::error('内容不能超过10万个字符');
            }
        }

        // 验证分类ID格式（如果有更新）
        if (isset($data['category_id'])) {
            if (!is_numeric($data['category_id']) || (int)$data['category_id'] <= 0) {
                return Response::error('分类ID无效');
            }
            $data['category_id'] = (int)$data['category_id'];
        }

        // 敏感词检测
        $sensitiveService = new SensitiveWordService();
        $userId = $request->user['id'] ?? 0;

        // 检测标题（如果有更新）
        if (isset($data['title'])) {
            $titleResult = $sensitiveService->checkAndHandle(
                'article',
                $id,
                $userId,
                $data['title'],
                true  // 自动替换
            );

            if (!$titleResult['allowed']) {
                return Response::error($titleResult['message']);
            }
            $data['title'] = $titleResult['content'];
        }

        // 检测内容（如果有更新）
        if (isset($data['content'])) {
            $contentResult = $sensitiveService->checkAndHandle(
                'article',
                $id,
                $userId,
                $data['content'],
                true  // 自动替换
            );

            if (!$contentResult['allowed']) {
                return Response::error($contentResult['message']);
            }
            $data['content'] = $contentResult['content'];
        }

        // 检测摘要（如果有更新）
        if (isset($data['description']) && !empty($data['description'])) {
            $descResult = $sensitiveService->checkAndHandle(
                'article',
                $id,
                $userId,
                $data['description'],
                true
            );

            if (!$descResult['allowed']) {
                return Response::error($descResult['message']);
            }
            $data['description'] = $descResult['content'];
        }

        // 获取标签数据
        $tags = $data['tags'] ?? null;
        unset($data['tags']);

        // 获取专题数据
        $topics = $data['topics'] ?? null;
        unset($data['topics']);

        // 获取副分类数据
        $subCategories = $data['sub_categories'] ?? null;
        unset($data['sub_categories']);

        // 获取版本修改说明
        $changeLog = $data['change_log'] ?? null;
        unset($data['change_log']);

        // 主分类ID(确保是整数类型)
        $mainCategoryId = (int)($data['category_id'] ?? $article->category_id);

        // 如果从草稿变为发布状态，设置发布时间
        if (isset($data['status']) && $data['status'] == 1 && $article->status == 0 && empty($article->publish_time)) {
            $data['publish_time'] = date('Y-m-d H:i:s');
        }

        // 自动提取SEO信息
        $this->autoExtractSeoInfo($data);

        Db::startTrans();
        try {
            // 更新文章
            $article->save($data);

            // 更新标签关联（支持自动创建新标签）
            if ($tags !== null && is_array($tags)) {
                // 获取文章的站点ID
                $siteId = $article->site_id ?? 1;
                // 处理标签（自动创建新标签，传递站点ID）
                $tagIds = $this->processTagsWithAutoCreate($tags, $siteId);

                // 验证标签是否属于当前站点（过滤掉不属于当前站点的标签）
                if (!empty($tagIds)) {
                    $validTagIds = Tag::withoutSiteScope()
                        ->whereIn('id', $tagIds)
                        ->where('site_id', $siteId)
                        ->column('id');
                    $tagIds = array_values(array_intersect($tagIds, $validTagIds));
                }

                Relation::saveArticleTags($id, $tagIds, $siteId);
            }

            // 更新专题关联
            if ($topics !== null && is_array($topics)) {
                // 先删除文章的所有专题关联（不受站点限制）
                Relation::withoutSiteScope()
                    ->where('source_type', 'topic')
                    ->where('target_type', 'article')
                    ->where('target_id', $id)
                    ->delete();

                // 获取文章的站点ID
                $siteId = $article->site_id ?? 1;

                // 验证专题是否属于当前站点（过滤掉不属于当前站点的专题）
                if (!empty($topics)) {
                    $validTopicIds = \app\model\Topic::withoutSiteScope()
                        ->whereIn('id', $topics)
                        ->where('site_id', $siteId)
                        ->column('id');

                    // 只添加属于当前站点的专题
                    foreach ($validTopicIds as $topicId) {
                        Relation::addTopicArticle($topicId, $id, 0, $siteId);
                    }
                }
            }

            // 检查副分类功能是否开启（从站点配置中读取）
            $siteId = $article->site_id ?? 1;
            $site = \app\model\Site::find($siteId);
            $subCategoryEnabled = $site ? $site->article_sub_category : 'close';

            // 更新分类关联
            // 如果传递了 sub_categories 参数,或者主分类发生了变化,则更新分类关联
            $categoryChanged = isset($data['category_id']) && $data['category_id'] != $article->getData('category_id');
            if ($subCategories !== null || $categoryChanged) {
                // 如果没有传递 sub_categories,使用当前的副分类
                if ($subCategories === null) {
                    // 获取当前文章的副分类（不受站点限制）
                    $currentSubCategories = Relation::withoutSiteScope()
                        ->where('source_type', 'article')
                        ->where('source_id', $id)
                        ->where('target_type', 'category')
                        ->where('relation_type', Relation::RELATION_SUB)
                        ->column('target_id');
                    // 确保都是整数类型
                    $subCategories = array_map('intval', $currentSubCategories);
                } else {
                    // 确保传入的副分类ID都是整数类型
                    $subCategories = is_array($subCategories) ? array_map('intval', array_filter($subCategories)) : [];
                }

                if ($subCategoryEnabled === 'open') {
                    // 合并主分类和副分类
                    $allCategories = array_merge([$mainCategoryId], is_array($subCategories) ? $subCategories : []);
                    // 转换为整数并去重,避免类型不一致导致的重复
                    $allCategories = array_values(array_unique(array_map('intval', $allCategories)));

                    // 验证分类是否属于当前站点（过滤掉不属于当前站点的分类）
                    $validCategoryIds = \app\model\Category::withoutSiteScope()
                        ->whereIn('id', $allCategories)
                        ->where('site_id', $siteId)
                        ->column('id');
                    $allCategories = array_values(array_intersect($allCategories, $validCategoryIds));

                    // 验证主分类是否属于当前站点
                    $validMainCategoryId = in_array($mainCategoryId, $validCategoryIds) ? $mainCategoryId : null;

                    if ($validMainCategoryId && !empty($allCategories)) {
                        Relation::saveArticleCategories($id, $allCategories, $validMainCategoryId, $siteId);
                    }
                } else {
                    // 仅保存主分类，但要验证主分类是否属于当前站点
                    $validMainCategory = \app\model\Category::withoutSiteScope()
                        ->where('id', $mainCategoryId)
                        ->where('site_id', $siteId)
                        ->find();

                    if ($validMainCategory) {
                        Relation::saveArticleCategories($id, [(int)$mainCategoryId], (int)$mainCategoryId, $siteId);
                    }
                }
            }

            // 创建新版本（更新后，标签关系会自动延迟加载）
            ArticleVersion::createFromArticle($article, $request->user['id'], $changeLog);

            Db::commit();

            // 更新媒体使用情况
            $usageService = new MediaUsageService();
            try {
                // 从内容中提取并记录媒体使用
                if (isset($data['content'])) {
                    $usageService->recordUsageFromContent(
                        $article->content,
                        'article',
                        $article->id,
                        'content'
                    );
                }

                // 更新缩略图的使用
                if (isset($data['thumb'])) {
                    if (!empty($article->thumb)) {
                        $thumbMediaIds = $usageService->extractMediaIds($article->thumb);
                        if (!empty($thumbMediaIds)) {
                            $usageService->recordUsageFromMediaIds(
                                $thumbMediaIds,
                                'article',
                                $article->id,
                                'thumb'
                            );
                        }
                    } else {
                        // 如果移除了缩略图，删除使用记录
                        $usageService->removeUsage('article', $article->id, 'thumb');
                    }
                }

                // 更新封面图片的使用
                if (isset($data['cover_image'])) {
                    if (!empty($article->cover_image)) {
                        $coverMediaIds = $usageService->extractMediaIds($article->cover_image);
                        if (!empty($coverMediaIds)) {
                            $usageService->recordUsageFromMediaIds(
                                $coverMediaIds,
                                'article',
                                $article->id,
                                'cover_image'
                            );
                        }
                    } else {
                        // 如果移除了封面图片，删除使用记录
                        $usageService->removeUsage('article', $article->id, 'cover_image');
                    }
                }

                // 更新图片集合的使用
                if (isset($data['images'])) {
                    if (!empty($article->images)) {
                        // images可能是JSON数组
                        $imagesContent = is_string($article->images) ? $article->images : json_encode($article->images);
                        $imageMediaIds = $usageService->extractMediaIds($imagesContent);
                        if (!empty($imageMediaIds)) {
                            $usageService->recordUsageFromMediaIds(
                                $imageMediaIds,
                                'article',
                                $article->id,
                                'images'
                            );
                        }
                    } else {
                        // 如果移除了图片集合，删除使用记录
                        $usageService->removeUsage('article', $article->id, 'images');
                    }
                }

                // 更新OG图片的使用
                if (isset($data['og_image'])) {
                    if (!empty($article->og_image)) {
                        $ogMediaIds = $usageService->extractMediaIds($article->og_image);
                        if (!empty($ogMediaIds)) {
                            $usageService->recordUsageFromMediaIds(
                                $ogMediaIds,
                                'article',
                                $article->id,
                                'og_image'
                            );
                        }
                    } else {
                        // 如果移除了OG图片，删除使用记录
                        $usageService->removeUsage('article', $article->id, 'og_image');
                    }
                }
            } catch (\Exception $e) {
                // 记录失败不影响文章更新
                Log::error('更新文章媒体使用失败: ' . $e->getMessage());
            }

            // 审计日志：收集修改后的值
            $newValues = [
                'title' => $article->title,
                'category_id' => $article->category_id,
                'status' => $article->status,
                'is_top' => $article->is_top,
                'is_recommend' => $article->is_recommend,
                'is_hot' => $article->is_hot,
                'summary' => $article->summary,
                'seo_keywords' => $article->seo_keywords,
                'seo_description' => $article->seo_description,
            ];

            // 记录日志（包含修改前后的值对比）
            Logger::update(OperationLog::MODULE_ARTICLE, '文章', $id, $oldValues, $newValues);

            // 如果文章已发布，自动生成静态页面
            if ($article->status == 1) {  // 1 = 已发布
                $this->autoGenerateStatic($id);
            }

            return Response::success([], '文章更新成功');

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('文章更新失败：' . $e->getMessage());
        }
    }

    /**
     * 检查文章删除前的媒体使用情况
     */
    public function checkDeleteMedia($id)
    {
        $article = ArticleModel::withoutSiteScope()->find($id);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        try {
            $usageService = new MediaUsageService();
            $mediaList = $usageService->getUsedMedia('article', (int)$id);

            return Response::success([
                'has_media' => count($mediaList) > 0,
                'media_count' => count($mediaList),
                'media_list' => $mediaList,
            ]);
        } catch (\Exception $e) {
            return Response::error('检查媒体使用失败：' . $e->getMessage());
        }
    }

    /**
     * 同步文章的媒体使用记录
     */
    public function syncMediaUsage($id)
    {
        $article = ArticleModel::withoutSiteScope()->find($id);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        try {
            $usageService = new MediaUsageService();
            $synced = $usageService->syncArticleMediaUsage((int)$id);

            return Response::success($synced, '媒体使用记录已同步');
        } catch (\Exception $e) {
            return Response::error('同步失败：' . $e->getMessage());
        }
    }

    /**
     * 删除文章
     */
    public function delete(Request $request, $id)
    {
        // 禁用自动站点过滤，允许删除所有站点的文章
        $article = ArticleModel::withoutSiteScope()->find($id);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        // 获取是否要删除关联媒体的参数
        $deleteMedia = $request->post('delete_media', false);
        $mediaIds = $request->post('media_ids', []);

        // 检查回收站是否开启（从站点配置中读取）
        $siteId = $article->site_id ?? 1;
        $site = \app\model\Site::find($siteId);
        $recycleBinEnabled = $site ? $site->recycle_bin_enable : 'open';

        Db::startTrans();
        try {
            if ($recycleBinEnabled === 'open') {
                // 软删除：使用Db类直接执行，确保只删除指定ID的记录
                $affected = Db::name('articles')
                    ->where('id', '=', $id)
                    ->limit(1)
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);

                if ($affected === 0) {
                    throw new \Exception('文章删除失败：未找到该文章');
                }
                $message = '文章已移入回收站';
            } else {
                // 物理删除
                // 删除所有关联（标签、分类、专题）- 不受站点限制
                Relation::withoutSiteScope()
                    ->where('source_type', 'article')
                    ->where('source_id', $id)
                    ->delete();

                // 删除作为目标的关联（如专题-文章关联）- 不受站点限制
                Relation::withoutSiteScope()
                    ->where('target_type', 'article')
                    ->where('target_id', $id)
                    ->delete();

                // 物理删除文章：使用Db类直接删除，确保只删除指定ID的记录
                $affected = Db::name('articles')
                    ->where('id', '=', $id)
                    ->limit(1)
                    ->delete();

                if ($affected === 0) {
                    throw new \Exception('文章删除失败：未找到该文章');
                }
                $message = '文章删除成功';
            }

            Db::commit();

            // 删除媒体使用记录
            $usageService = new MediaUsageService();
            try {
                $usageService->removeUsage('article', (int)$id);

                // 如果用户选择删除关联的媒体
                if ($deleteMedia && !empty($mediaIds) && is_array($mediaIds)) {
                    $deletedCount = 0;
                    foreach ($mediaIds as $mediaId) {
                        try {
                            $media = \app\model\MediaLibrary::find($mediaId);
                            if ($media) {
                                $media->delete();
                                $deletedCount++;
                            }
                        } catch (\Exception $e) {
                            Log::error("删除媒体 {$mediaId} 失败: " . $e->getMessage());
                        }
                    }

                    if ($deletedCount > 0) {
                        $message .= "，已删除 {$deletedCount} 个关联媒体";
                    }
                }
            } catch (\Exception $e) {
                Log::error('清理媒体使用记录失败: ' . $e->getMessage());
            }

            // 记录日志
            Logger::delete(OperationLog::MODULE_ARTICLE, '文章', $id);

            return Response::success([], $message);

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('文章删除失败：' . $e->getMessage());
        }
    }

    /**
     * 部分更新文章（PATCH方法，符合RESTful规范）
     * 用于更新文章的部分字段，如状态、标记等
     */
    public function patch(Request $request, $id)
    {
        $article = ArticleModel::withoutSiteScope()->find($id);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        // 禁用站点过滤
        $reflection = new \ReflectionObject($article);
        if ($reflection->hasProperty('multiSiteEnabled')) {
            $property = $reflection->getProperty('multiSiteEnabled');
            $property->setAccessible(true);
            $property->setValue($article, false);
        }

        $data = $request->patch();

        // 保存修改前的值用于审计
        $oldValues = [];
        $allowedFields = ['status', 'is_top', 'is_recommend', 'is_hot', 'sort', 'view_count'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $oldValues[$field] = $article->$field;
            }
        }

        // 状态转换
        if (isset($data['status'])) {
            $statusMap = [
                'draft' => 0,
                'published' => 1,
                'pending' => 2,
                'offline' => 3
            ];

            // 支持字符串状态或数字状态
            if (is_string($data['status']) && isset($statusMap[$data['status']])) {
                $data['status'] = $statusMap[$data['status']];
            }

            // 如果状态变更为已发布，设置发布时间
            if ($data['status'] == 1 && empty($article->publish_time)) {
                $data['publish_time'] = date('Y-m-d H:i:s');
            }
        }

        // 只更新允许的字段
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $article->$field = $data[$field];
            }
        }

        $article->save();

        // 收集修改后的值
        $newValues = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $newValues[$field] = $article->$field;
            }
        }

        // 记录日志
        Logger::update(OperationLog::MODULE_ARTICLE, '文章', $id, $oldValues, $newValues);

        // 如果状态改为已发布，生成静态页面
        if (isset($data['status']) && $data['status'] == 1) {
            $this->autoGenerateStatic($id);
        }

        return Response::success($article->toArray(), '文章更新成功');
    }

    /**
     * 发布文章（草稿或已下线 -> 已发布）
     * @deprecated 使用 PATCH /articles/:id 替代
     */
    public function publish($id)
    {
        // 禁用自动站点过滤
        $article = ArticleModel::withoutSiteScope()->find($id);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        // 关键修复：确保模型实例也禁用站点过滤
        $reflection = new \ReflectionObject($article);
        if ($reflection->hasProperty('multiSiteEnabled')) {
            $property = $reflection->getProperty('multiSiteEnabled');
            $property->setAccessible(true);
            $property->setValue($article, false);
        }

        $article->status = 1; // 已发布
        if (empty($article->publish_time)) {
            $article->publish_time = date('Y-m-d H:i:s');
        }
        $article->save();

        // 记录日志
        Logger::publish(OperationLog::MODULE_ARTICLE, '文章', $id);

        // 自动生成静态页面
        $this->autoGenerateStatic($id);

        return Response::success([], '文章发布成功');
    }

    /**
     * 下线文章（已发布 -> 已下线）
     */
    public function offline($id)
    {
        // 禁用自动站点过滤
        $article = ArticleModel::withoutSiteScope()->find($id);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        // 关键修复：确保模型实例也禁用站点过滤
        $reflection = new \ReflectionObject($article);
        if ($reflection->hasProperty('multiSiteEnabled')) {
            $property = $reflection->getProperty('multiSiteEnabled');
            $property->setAccessible(true);
            $property->setValue($article, false);
        }

        $article->status = 3; // 已下线
        $article->save();

        // 记录日志
        Logger::offline(OperationLog::MODULE_ARTICLE, '文章', $id);

        return Response::success([], '文章已下线');
    }

    /**
     * 批量删除文章
     */
    public function batchDelete(Request $request)
    {
        $ids = $request->post('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要删除的文章');
        }

        Db::startTrans();
        try {
            $successCount = 0;
            $failCount = 0;

            foreach ($ids as $id) {
                $article = ArticleModel::withoutSiteScope()->find($id);
                if (!$article) {
                    $failCount++;
                    continue;
                }

                // 禁用站点过滤
                $reflection = new \ReflectionObject($article);
                if ($reflection->hasProperty('multiSiteEnabled')) {
                    $property = $reflection->getProperty('multiSiteEnabled');
                    $property->setAccessible(true);
                    $property->setValue($article, false);
                }

                // 检查回收站是否开启
                $siteId = $article->site_id ?? 1;
                $site = \app\model\Site::find($siteId);
                $recycleBinEnabled = $site ? $site->recycle_bin_enable : 'open';

                if ($recycleBinEnabled === 'open') {
                    // 软删除
                    $article->delete();
                } else {
                    // 物理删除关联和文章
                    Relation::withoutSiteScope()
                        ->where('source_type', 'article')
                        ->where('source_id', $id)
                        ->delete();
                    Relation::withoutSiteScope()
                        ->where('target_type', 'article')
                        ->where('target_id', $id)
                        ->delete();
                    $article->force()->delete();
                }

                $successCount++;
            }

            Db::commit();

            // 记录日志
            Logger::batchDelete(OperationLog::MODULE_ARTICLE, '文章', $ids);

            if ($failCount > 0) {
                return Response::success([], "成功删除{$successCount}篇文章，{$failCount}篇失败");
            }

            return Response::success([], "成功删除{$successCount}篇文章");

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('批量删除失败：' . $e->getMessage());
        }
    }

    /**
     * 批量发布文章
     */
    public function batchPublish(Request $request)
    {
        $ids = $request->post('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要发布的文章');
        }

        Db::startTrans();
        try {
            $successCount = 0;

            foreach ($ids as $id) {
                $article = ArticleModel::withoutSiteScope()->find($id);
                if (!$article) {
                    continue;
                }

                // 禁用站点过滤
                $reflection = new \ReflectionObject($article);
                if ($reflection->hasProperty('multiSiteEnabled')) {
                    $property = $reflection->getProperty('multiSiteEnabled');
                    $property->setAccessible(true);
                    $property->setValue($article, false);
                }

                $article->status = 1; // 已发布
                if (empty($article->publish_time)) {
                    $article->publish_time = date('Y-m-d H:i:s');
                }
                $article->save();

                // 自动生成静态页面
                $this->autoGenerateStatic($id);

                $successCount++;
            }

            Db::commit();

            // 记录日志
            Logger::log(
                OperationLog::MODULE_ARTICLE,
                OperationLog::ACTION_PUBLISH,
                "批量发布文章，数量: {$successCount}，ID: " . implode(',', $ids)
            );

            return Response::success([], "成功发布{$successCount}篇文章");

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('批量发布失败：' . $e->getMessage());
        }
    }

    /**
     * 批量下线文章
     */
    public function batchOffline(Request $request)
    {
        $ids = $request->post('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要下线的文章');
        }

        Db::startTrans();
        try {
            $successCount = 0;

            foreach ($ids as $id) {
                $article = ArticleModel::withoutSiteScope()->find($id);
                if (!$article) {
                    continue;
                }

                // 禁用站点过滤
                $reflection = new \ReflectionObject($article);
                if ($reflection->hasProperty('multiSiteEnabled')) {
                    $property = $reflection->getProperty('multiSiteEnabled');
                    $property->setAccessible(true);
                    $property->setValue($article, false);
                }

                $article->status = 3; // 已下线
                $article->save();

                $successCount++;
            }

            Db::commit();

            // 记录日志
            Logger::log(
                OperationLog::MODULE_ARTICLE,
                OperationLog::ACTION_OFFLINE,
                "批量下线文章，数量: {$successCount}，ID: " . implode(',', $ids)
            );

            return Response::success([], "成功下线{$successCount}篇文章");

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('批量下线失败：' . $e->getMessage());
        }
    }

    /**
     * 批量修改文章分类
     */
    public function batchUpdateCategory(Request $request)
    {
        $ids = $request->post('ids', []);
        $categoryId = $request->post('category_id');

        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要修改的文章');
        }

        if (empty($categoryId)) {
            return Response::error('请选择分类');
        }

        Db::startTrans();
        try {
            $successCount = 0;

            foreach ($ids as $id) {
                $article = ArticleModel::withoutSiteScope()->find($id);
                if (!$article) {
                    continue;
                }

                // 禁用站点过滤
                $reflection = new \ReflectionObject($article);
                if ($reflection->hasProperty('multiSiteEnabled')) {
                    $property = $reflection->getProperty('multiSiteEnabled');
                    $property->setAccessible(true);
                    $property->setValue($article, false);
                }

                $article->category_id = $categoryId;
                $article->save();

                // 更新关联表
                $siteId = $article->site_id ?? 1;
                Relation::saveArticleCategories($id, [$categoryId], $categoryId, $siteId);

                $successCount++;
            }

            Db::commit();

            // 记录日志
            Logger::log(
                OperationLog::MODULE_ARTICLE,
                OperationLog::ACTION_UPDATE,
                "批量修改文章分类，数量: {$successCount}，目标分类: {$categoryId}，ID: " . implode(',', $ids)
            );

            return Response::success([], "成功修改{$successCount}篇文章的分类");

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('批量修改分类失败：' . $e->getMessage());
        }
    }

    /**
     * 自动提取SEO信息（摘要、关键词、描述）
     */
    private function autoExtractSeoInfo(&$data)
    {
        $title = $data['title'] ?? '';
        $content = $data['content'] ?? '';

        // 去除HTML标签
        $plainText = strip_tags($content);

        // 解码HTML实体（如&nbsp; &amp; &lt; &gt; &quot;等）
        $plainText = html_entity_decode($plainText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // 去除多余空白（包括不可见字符）
        $plainText = preg_replace('/\s+/u', ' ', $plainText);
        $plainText = trim($plainText);

        // 去除零宽字符和其他特殊不可见字符
        $plainText = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $plainText);

        // 自动生成摘要（如果为空）
        if (empty($data['summary']) && !empty($plainText)) {
            $data['summary'] = mb_substr($plainText, 0, 200, 'UTF-8');
            if (mb_strlen($plainText, 'UTF-8') > 200) {
                $data['summary'] .= '...';
            }
        }

        // 自动生成SEO描述（如果为空）
        if (empty($data['seo_description']) && !empty($plainText)) {
            $data['seo_description'] = mb_substr($plainText, 0, 150, 'UTF-8');
            if (mb_strlen($plainText, 'UTF-8') > 150) {
                $data['seo_description'] .= '...';
            }
        }

        // 自动生成SEO关键词（如果为空）
        if (empty($data['seo_keywords'])) {
            $keywords = [];

            // 从标题中提取关键词
            if (!empty($title)) {
                // 解码标题中的HTML实体
                $cleanTitle = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                // 分词（简单实现：按空格、标点分割）
                $titleWords = preg_split('/[\s,，.。;；:：!！?？、]+/u', $cleanTitle, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($titleWords as $word) {
                    $word = trim($word);
                    if (mb_strlen($word, 'UTF-8') >= 2 && mb_strlen($word, 'UTF-8') <= 10) {
                        $keywords[] = $word;
                    }
                }
            }

            // 从内容中提取（取前100字符）
            $contentSample = mb_substr($plainText, 0, 100, 'UTF-8');
            $contentWords = preg_split('/[\s,，.。;；:：!！?？、]+/u', $contentSample, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($contentWords as $word) {
                $word = trim($word);
                if (mb_strlen($word, 'UTF-8') >= 2 && mb_strlen($word, 'UTF-8') <= 10) {
                    $keywords[] = $word;
                }
            }

            // 去重并限制数量
            $keywords = array_unique($keywords);
            $keywords = array_slice($keywords, 0, 8);

            if (!empty($keywords)) {
                $data['seo_keywords'] = implode(',', $keywords);
            }
        }
    }

    /**
     * 自动生成静态页面
     */
    private function autoGenerateStatic($id)
    {
        try {
            // 使用 app() 辅助函数实例化控制器
            $staticBuild = app(Build::class);
            $staticBuild->article($id);
        } catch (\Exception $e) {
            // 静态生成失败不影响主流程
            trace('自动生成静态页面失败: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * 全文搜索
     * 使用 MySQL FULLTEXT INDEX 进行搜索
     */
    public function fullTextSearch(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $mode = $request->get('mode', 'natural'); // natural, boolean, query_expansion
        $categoryId = $request->get('category_id', '');
        $status = $request->get('status', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');

        if (empty($keyword)) {
            return Response::error('搜索关键词不能为空');
        }

        // 构建基础查询 - 优化N+1查询
        // 注意：不预加载category，因为Category模型会应用站点过滤，导致跨站点分类无法加载
        $query = ArticleModel::withoutSiteScope()->with(['user']);

        // 根据模式选择搜索方式
        switch ($mode) {
            case 'boolean':
                // 布尔模式：支持 +word -word "phrase" 等操作符
                $query->whereRaw(
                    "MATCH(title, content) AGAINST(? IN BOOLEAN MODE)",
                    [$keyword]
                );
                break;
            case 'query_expansion':
                // 查询扩展模式：自动扩展相关词
                $query->whereRaw(
                    "MATCH(title, content) AGAINST(? WITH QUERY EXPANSION)",
                    [$keyword]
                );
                break;
            default:
                // 自然语言模式（默认）
                $query->whereRaw(
                    "MATCH(title, content) AGAINST(?)",
                    [$keyword]
                );
                break;
        }

        // 添加额外过滤条件
        if ($categoryId !== '') {
            $query->where(function($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->whereOr('id', 'in', function($subQuery) use ($categoryId) {
                      $subQuery->table('article_categories')
                               ->where('category_id', $categoryId)
                               ->field('article_id');
                  });
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if (!empty($startTime)) {
            $query->where('publish_time', '>=', $startTime);
        }

        if (!empty($endTime)) {
            $query->where('publish_time', '<=', $endTime . ' 23:59:59');
        }

        // 计算相关度得分并排序
        if ($mode === 'natural' || $mode === 'query_expansion') {
            $query->field([
                '*',
                'MATCH(title, content) AGAINST(?) as relevance_score'
            ], [$keyword]);
            $query->order('relevance_score', 'desc');
        } else {
            $query->order(['is_top' => 'desc', 'publish_time' => 'desc']);
        }

        // 获取总数
        $total = $query->count();

        // 分页查询
        $list = $query->page($page, $pageSize)->select();

        // 处理数据
        $listData = $list->toArray();

        // 优化：批量查询分类
        if (!empty($listData)) {
            $articleIds = array_column($listData, 'id');

            // 批量查询主分类（禁用站点过滤，允许跨站点查询）
            $categoryIds = array_filter(array_unique(array_column($listData, 'category_id')));
            if (!empty($categoryIds)) {
                $categories = \app\model\Category::withoutSiteScope()
                    ->whereIn('id', $categoryIds)
                    ->field('id,name,slug')
                    ->select()
                    ->toArray();

                // 构建分类映射表
                $categoryMap = array_column($categories, null, 'id');

                // 添加分类到文章数据中
                foreach ($listData as &$item) {
                    if (!empty($item['category_id']) && isset($categoryMap[$item['category_id']])) {
                        $item['category'] = $categoryMap[$item['category_id']];
                    } else {
                        $item['category'] = null;
                    }
                }
            } else {
                // 没有分类，添加null
                foreach ($listData as &$item) {
                    $item['category'] = null;
                }
            }

            $subCategoryRelations = Relation::withoutSiteScope()
                ->where('source_type', 'article')
                ->whereIn('source_id', $articleIds)
                ->where('target_type', 'category')
                ->where('relation_type', 'sub')
                ->select()
                ->toArray();

            if (!empty($subCategoryRelations)) {
                $subCategoryIds = array_unique(array_column($subCategoryRelations, 'target_id'));
                $categories = \app\model\Category::withoutSiteScope()
                    ->whereIn('id', $subCategoryIds)
                    ->field('id,name,slug')
                    ->select()
                    ->toArray();

                $categoryMap = array_column($categories, null, 'id');
                $articleSubCategories = [];

                foreach ($subCategoryRelations as $rel) {
                    $articleId = $rel['source_id'];
                    $categoryId = $rel['target_id'];
                    if (isset($categoryMap[$categoryId])) {
                        if (!isset($articleSubCategories[$articleId])) {
                            $articleSubCategories[$articleId] = [];
                        }
                        $articleSubCategories[$articleId][] = $categoryMap[$categoryId];
                    }
                }

                foreach ($listData as &$item) {
                    $item['sub_categories'] = $articleSubCategories[$item['id']] ?? [];
                }
            } else {
                foreach ($listData as &$item) {
                    $item['sub_categories'] = [];
                }
            }
        }

        // 高亮关键词
        foreach ($listData as &$item) {
            $item['highlighted_title'] = $this->highlightKeyword($item['title'], $keyword);
            $item['highlighted_summary'] = $this->highlightKeyword(
                $item['summary'] ?? mb_substr(strip_tags($item['content']), 0, 200),
                $keyword
            );
        }

        return Response::paginate($listData, $total, $page, $pageSize);
    }

    /**
     * 高级搜索
     * 支持多字段组合搜索
     */
    public function advancedSearch(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);

        // 搜索条件
        $title = $request->get('title', '');
        $content = $request->get('content', '');
        $summary = $request->get('summary', '');
        $author = $request->get('author', '');
        $categoryId = $request->get('category_id', '');
        $tagIds = $request->get('tag_ids', ''); // 逗号分隔的标签ID
        $userId = $request->get('user_id', '');
        $status = $request->get('status', '');
        $isTop = $request->get('is_top', '');
        $isRecommend = $request->get('is_recommend', '');
        $isHot = $request->get('is_hot', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');
        $minViews = $request->get('min_views', '');
        $maxViews = $request->get('max_views', '');
        $sortBy = $request->get('sort_by', 'publish_time'); // publish_time, view_count, like_count
        $sortOrder = $request->get('sort_order', 'desc'); // asc, desc

        // 构建查询 - 优化N+1查询
        // 注意：不预加载category，因为Category模型会应用站点过滤，导致跨站点分类无法加载
        $query = ArticleModel::withoutSiteScope()->with(['user']);

        // 标题搜索
        if (!empty($title)) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        // 内容搜索
        if (!empty($content)) {
            $query->where('content', 'like', '%' . $content . '%');
        }

        // 摘要搜索
        if (!empty($summary)) {
            $query->where('summary', 'like', '%' . $summary . '%');
        }

        // 作者名搜索
        if (!empty($author)) {
            $query->where('id', 'in', function($subQuery) use ($author) {
                $subQuery->table('admin_users')
                         ->where('username', 'like', '%' . $author . '%')
                         ->whereOr('real_name', 'like', '%' . $author . '%')
                         ->field('id');
            });
        }

        // 分类筛选
        if ($categoryId !== '') {
            $query->where(function($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->whereOr('id', 'in', function($subQuery) use ($categoryId) {
                      $subQuery->table('article_categories')
                               ->where('category_id', $categoryId)
                               ->field('article_id');
                  });
            });
        }

        // 标签筛选（支持多个标签）
        if (!empty($tagIds)) {
            $tagIdArray = explode(',', $tagIds);
            $query->where('id', 'in', function($subQuery) use ($tagIdArray) {
                $subQuery->table('article_tags')
                         ->whereIn('tag_id', $tagIdArray)
                         ->field('article_id');
            });
        }

        // 用户筛选
        if ($userId !== '') {
            $query->where('user_id', $userId);
        }

        // 状态筛选
        if ($status !== '') {
            $query->where('status', $status);
        }

        // 置顶筛选
        if ($isTop !== '') {
            $query->where('is_top', $isTop);
        }

        // 推荐筛选
        if ($isRecommend !== '') {
            $query->where('is_recommend', $isRecommend);
        }

        // 热门筛选
        if ($isHot !== '') {
            $query->where('is_hot', $isHot);
        }

        // 时间范围筛选
        if (!empty($startTime)) {
            $query->where('publish_time', '>=', $startTime);
        }
        if (!empty($endTime)) {
            $query->where('publish_time', '<=', $endTime . ' 23:59:59');
        }

        // 浏览量范围筛选
        if ($minViews !== '') {
            $query->where('view_count', '>=', $minViews);
        }
        if ($maxViews !== '') {
            $query->where('view_count', '<=', $maxViews);
        }

        // 排序
        $allowedSortFields = ['publish_time', 'view_count', 'like_count', 'comment_count', 'create_time', 'update_time'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->order($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->order(['is_top' => 'desc', 'publish_time' => 'desc']);
        }

        // 获取总数
        $total = $query->count();

        // 分页查询
        $list = $query->page($page, $pageSize)->select();

        // 处理数据 - 优化批量查询副分类
        $listData = $list->toArray();

        if (!empty($listData)) {
            $articleIds = array_column($listData, 'id');

            // 批量查询主分类（禁用站点过滤，允许跨站点查询）
            $categoryIds = array_filter(array_unique(array_column($listData, 'category_id')));
            if (!empty($categoryIds)) {
                $categories = \app\model\Category::withoutSiteScope()
                    ->whereIn('id', $categoryIds)
                    ->field('id,name,slug')
                    ->select()
                    ->toArray();

                // 构建分类映射表
                $categoryMap = array_column($categories, null, 'id');

                // 添加分类到文章数据中
                foreach ($listData as &$item) {
                    if (!empty($item['category_id']) && isset($categoryMap[$item['category_id']])) {
                        $item['category'] = $categoryMap[$item['category_id']];
                    } else {
                        $item['category'] = null;
                    }
                }
            } else {
                // 没有分类，添加null
                foreach ($listData as &$item) {
                    $item['category'] = null;
                }
            }

            $subCategoryRelations = Relation::withoutSiteScope()
                ->where('source_type', 'article')
                ->whereIn('source_id', $articleIds)
                ->where('target_type', 'category')
                ->where('relation_type', 'sub')
                ->select()
                ->toArray();

            if (!empty($subCategoryRelations)) {
                $subCategoryIds = array_unique(array_column($subCategoryRelations, 'target_id'));
                $categories = \app\model\Category::withoutSiteScope()
                    ->whereIn('id', $subCategoryIds)
                    ->field('id,name,slug')
                    ->select()
                    ->toArray();

                $categoryMap = array_column($categories, null, 'id');
                $articleSubCategories = [];

                foreach ($subCategoryRelations as $rel) {
                    $articleId = $rel['source_id'];
                    $categoryId = $rel['target_id'];
                    if (isset($categoryMap[$categoryId])) {
                        if (!isset($articleSubCategories[$articleId])) {
                            $articleSubCategories[$articleId] = [];
                        }
                        $articleSubCategories[$articleId][] = $categoryMap[$categoryId];
                    }
                }

                foreach ($listData as &$item) {
                    $item['sub_categories'] = $articleSubCategories[$item['id']] ?? [];
                }
            } else {
                foreach ($listData as &$item) {
                    $item['sub_categories'] = [];
                }
            }
        }

        return Response::paginate($listData, $total, $page, $pageSize);
    }

    /**
     * 搜索建议（自动完成）
     * 基于文章标题提供搜索建议
     */
    public function searchSuggestions(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $limit = $request->get('limit', 10);

        if (empty($keyword)) {
            return Response::success([]);
        }

        // 搜索匹配的文章标题
        $articles = ArticleModel::withoutSiteScope()
            ->where('title', 'like', '%' . $keyword . '%')
            ->where('status', 1) // 只搜索已发布的文章
            ->field(['id', 'title', 'view_count'])
            ->order('view_count', 'desc')
            ->limit($limit)
            ->select();

        $suggestions = [];
        foreach ($articles as $article) {
            $suggestions[] = [
                'id' => $article->id,
                'title' => $article->title,
                'view_count' => $article->view_count
            ];
        }

        return Response::success($suggestions);
    }

    /**
     * 高亮关键词
     */
    private function highlightKeyword($text, $keyword)
    {
        if (empty($text) || empty($keyword)) {
            return $text;
        }

        // 转义特殊字符
        $keyword = preg_quote($keyword, '/');

        // 高亮匹配的关键词（不区分大小写）
        return preg_replace(
            '/(' . $keyword . ')/iu',
            '<mark>$1</mark>',
            $text
        );
    }

    /**
     * 处理标签（支持自动创建新标签）
     *
     * @param array $tags 标签数组，可以是ID（整数）或名称（字符串）的混合
     * @param int $siteId 站点ID
     * @return array 标签ID数组
     */
    private function processTagsWithAutoCreate(array $tags, $siteId = 1): array
    {
        $tagIds = [];

        foreach ($tags as $tag) {
            // 如果是数字ID，直接使用
            if (is_numeric($tag) && intval($tag) > 0) {
                $tagIds[] = intval($tag);
            }
            // 如果是字符串名称，查找或创建
            elseif (is_string($tag) && !empty(trim($tag))) {
                $tagName = trim($tag);

                // 检查标签长度
                if (mb_strlen($tagName, 'utf-8') > 50) {
                    continue; // 跳过过长的标签
                }

                // 查找已存在的标签（在指定站点下查找）
                $existingTag = Tag::withoutSiteScope()
                    ->where('name', $tagName)
                    ->where('site_id', $siteId)
                    ->find();

                if ($existingTag) {
                    // 标签已存在，使用现有标签ID
                    $tagIds[] = $existingTag->id;
                } else {
                    // 标签不存在，创建新标签（指定站点ID）
                    try {
                        $newTag = Tag::create([
                            'name' => $tagName,
                            'slug' => $this->generateTagSlug($tagName),
                            'site_id' => $siteId, // 设置站点ID
                            'status' => 1, // 默认启用
                            'article_count' => 0,
                            'sort' => 0
                        ]);
                        $tagIds[] = $newTag->id;
                    } catch (\Exception $e) {
                        // 创建失败（可能是并发导致的重复），尝试再次查找
                        $existingTag = Tag::withoutSiteScope()
                            ->where('name', $tagName)
                            ->where('site_id', $siteId)
                            ->find();
                        if ($existingTag) {
                            $tagIds[] = $existingTag->id;
                        }
                    }
                }
            }
        }

        // 去重，确保类型一致
        return array_values(array_unique(array_map('intval', $tagIds)));
    }

    /**
     * 生成标签slug
     *
     * @param string $name 标签名称
     * @return string slug
     */
    private function generateTagSlug(string $name): string
    {
        // 如果是英文，转为小写并替换空格为连字符
        if (preg_match('/^[a-zA-Z0-9\s-]+$/', $name)) {
            return strtolower(str_replace(' ', '-', trim($name)));
        }

        // 如果是中文或混合，使用拼音或原名称
        // 这里简单处理，直接使用名称
        return $name;
    }

    /**
     * AI生成文章内容
     */
    public function generateContent(Request $request)
    {
        $title = $request->post('title', '');
        $aiConfigId = $request->post('ai_config_id', '');
        $promptTemplateId = $request->post('prompt_template_id', '');
        $templateVariables = $request->post('template_variables', []);
        $length = $request->post('length', 'medium'); // short, medium, long
        $style = $request->post('style', 'professional'); // professional, casual, technical

        // 调试：记录接收到的参数
        Log::info('AI生成接收参数 - title: ' . var_export($title, true) . ', ai_config_id: ' . $aiConfigId . ', length: ' . $length . ', style: ' . $style . ', template_id: ' . $promptTemplateId . ', template_vars: ' . json_encode($templateVariables, JSON_UNESCAPED_UNICODE));

        // 验证标题
        if (empty($title)) {
            return Response::error('请先填写文章标题');
        }

        // 获取AI配置
        if (empty($aiConfigId)) {
            // 如果没有指定，使用默认配置
            $aiConfig = \app\model\AiConfig::where('is_default', 1)
                ->where('status', 1)
                ->find();

            if (!$aiConfig) {
                // 如果没有默认配置，使用第一个启用的配置
                $aiConfig = \app\model\AiConfig::where('status', 1)->find();
            }
        } else {
            $aiConfig = \app\model\AiConfig::find($aiConfigId);
        }

        if (!$aiConfig) {
            return Response::error('未找到可用的AI配置，请先在系统设置中配置AI服务');
        }

        if ($aiConfig->status != 1) {
            return Response::error('所选AI配置未启用');
        }

        try {
            // 创建AI服务实例
            $aiService = \app\service\AiService::createFromConfig($aiConfig);

            // 是否启用详细日志（开发环境或调试模式下启用）
            $enableDebugLog = env('app_debug', false);

            if ($enableDebugLog) {
                Log::info('=== AI文章生成开始 === 标题: ' . $title . ', AI配置: ' . $aiConfig->name . ', 提供商: ' . $aiConfig->provider . ', 使用模板: ' . (!empty($promptTemplateId) ? '是' : '否'));
            }

            // 构建提示词
            if (!empty($promptTemplateId)) {
                // 使用提示词模板
                $template = \app\model\AiPromptTemplate::find($promptTemplateId);
                if (!$template) {
                    return Response::error('提示词模板不存在');
                }

                if ($template->status != 1) {
                    return Response::error('提示词模板未启用');
                }

                // 替换模板变量
                // 确保文章标题作为 topic 变量传入模板
                if (!isset($templateVariables['topic'])) {
                    $templateVariables['topic'] = $title;
                }

                $prompt = $this->buildPromptFromTemplate($template, $templateVariables);

                if ($enableDebugLog) {
                    Log::info('使用提示词模板: ' . $template->name . ' (ID: ' . $promptTemplateId . '), 变量: ' . json_encode($templateVariables, JSON_UNESCAPED_UNICODE) . ', 提示词长度: ' . mb_strlen($prompt));
                }

                // 增加模板使用次数
                $template->incrementUsage();

                $options = ['use_raw_prompt' => true];
            } else {
                // 使用默认提示词
                $prompt = $this->buildArticlePrompt($title, $length, $style);

                if ($enableDebugLog) {
                    Log::info('使用默认提示词, 标题: ' . $title . ', 长度: ' . $length . ', 风格: ' . $style . ', 提示词长度: ' . mb_strlen($prompt));
                }

                $options = [
                    'use_raw_prompt' => true, // 修复：已经构建了完整提示词，应该使用原始提示词
                    'length' => $length,
                    'style' => $style,
                ];
            }

            // 生成文章
            $result = $aiService->generateArticle($prompt, $options);

            if ($enableDebugLog) {
                Log::info('AI生成成功, 内容长度: ' . mb_strlen($result['content'] ?? '') . ', 有摘要: ' . (!empty($result['summary']) ? '是' : '否'));
            }

            // 检测并转换Markdown为HTML
            $content = $result['content'] ?? '';
            $content = $this->convertMarkdownToHtml($content);

            // 记录日志
            OperationLog::record([
                'module' => 'article',
                'action' => 'ai_generate_content',
                'description' => "使用AI生成文章内容: {$title}",
                'request_params' => json_encode([
                    'title' => $title,
                    'ai_config' => $aiConfig->name,
                    'template_id' => $promptTemplateId ?? null,
                    'use_template' => !empty($promptTemplateId)
                ])
            ]);

            return Response::success([
                'content' => $content,
                'summary' => $result['summary'] ?? '',
                'ai_config_name' => $aiConfig->name,
            ], 'AI内容生成成功');

        } catch (\Exception $e) {
            Log::error('AI生成文章内容失败, 标题: ' . $title . ', 错误: ' . $e->getMessage());
            Log::error('错误堆栈: ' . $e->getTraceAsString());
            return Response::error('AI生成失败: ' . $e->getMessage());
        }
    }

    /**
     * 从模板构建提示词
     */
    private function buildPromptFromTemplate($template, $userVariables)
    {
        $prompt = $template->prompt;

        // 获取模板定义的变量
        $templateVariables = $template->variables ?? [];

        // 合并系统变量和用户变量
        $variables = [];
        foreach ($templateVariables as $varDef) {
            $varName = $varDef['name'] ?? '';
            if (empty($varName)) {
                continue;
            }

            // 优先使用用户输入的值，其次使用默认值
            if (isset($userVariables[$varName])) {
                $variables[$varName] = $userVariables[$varName];
            } elseif (isset($varDef['default'])) {
                $variables[$varName] = $varDef['default'];
            } else {
                $variables[$varName] = '';
            }
        }

        // 添加用户传入的但模板未定义的变量（如 topic）
        foreach ($userVariables as $varName => $varValue) {
            if (!isset($variables[$varName])) {
                $variables[$varName] = $varValue;
            }
        }

        // 替换变量
        foreach ($variables as $name => $value) {
            // 确保值是字符串，如果是数组则转换为JSON
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            $prompt = str_replace('{' . $name . '}', $value, $prompt);
        }

        return $prompt;
    }

    /**
     * 构建文章生成提示词
     */
    private function buildArticlePrompt($title, $length, $style)
    {
        $lengthMap = [
            'short' => '500-800字',
            'medium' => '1000-1500字',
            'long' => '2000-3000字'
        ];

        $styleMap = [
            'professional' => '专业严谨',
            'casual' => '轻松随意',
            'technical' => '技术深度'
        ];

        $lengthDesc = $lengthMap[$length] ?? '1000-1500字';
        $styleDesc = $styleMap[$style] ?? '专业严谨';

        $prompt = <<<PROMPT
请根据以下标题撰写一篇文章：

标题：{$title}

要求：
1. 文章长度：{$lengthDesc}
2. 写作风格：{$styleDesc}
3. 内容结构清晰，层次分明，包含引言、正文和结尾
4. 使用HTML格式输出，包含适当的段落（<p>）、标题（<h2>、<h3>）等标签
5. 内容要有深度和价值，避免空洞和重复
6. 语言流畅自然，符合中文表达习惯

请直接输出文章内容，不要包含任何说明性文字。
PROMPT;

        return $prompt;
    }

    /**
     * 检测内容是否为Markdown格式并转换为HTML
     *
     * @param string $content 原始内容
     * @return string 处理后的内容（如果是Markdown则转换为HTML，否则返回原内容）
     */
    private function convertMarkdownToHtml($content)
    {
        if (empty($content)) {
            return $content;
        }

        $enableDebugLog = env('app_debug', false);

        // 先解码可能被HTML转义的内容（AI有时会返回转义的HTML）
        $originalContent = $content;
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($enableDebugLog && $content !== $originalContent) {
            Log::info('HTML实体解码: 原始长度 ' . mb_strlen($originalContent) . ' -> 解码后长度 ' . mb_strlen($content));
        }

        // 第一步：检测是否已经是HTML格式
        $htmlPatterns = [
            '/<p[\s>]/i',              // <p> 段落标签
            '/<div[\s>]/i',            // <div> 标签
            '/<h[1-6][\s>]/i',         // <h1> - <h6> 标题标签
            '/<ul[\s>]/i',             // <ul> 无序列表
            '/<ol[\s>]/i',             // <ol> 有序列表
            '/<li[\s>]/i',             // <li> 列表项
            '/<br[\s\/>]/i',           // <br> 换行
            '/<strong[\s>]/i',         // <strong> 粗体
            '/<em[\s>]/i',             // <em> 斜体
            '/<blockquote[\s>]/i',     // <blockquote> 引用
            '/<pre[\s>]/i',            // <pre> 预格式化
            '/<code[\s>]/i',           // <code> 代码
            '/<a[\s>]/i',              // <a> 链接
            '/<img[\s>]/i',            // <img> 图片
        ];

        $isHtml = false;
        foreach ($htmlPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $isHtml = true;
                if ($enableDebugLog) {
                    Log::info('检测到HTML格式（匹配: ' . $pattern . '），直接返回HTML内容');
                }
                break;
            }
        }

        // 如果已经是HTML格式，直接返回
        if ($isHtml) {
            return $content;
        }

        // 第二步：检测是否为Markdown格式的特征
        $markdownPatterns = [
            '/^#{1,6}\s+/m',           // 标题 # ## ###
            '/\*\*.*?\*\*/s',          // 粗体 **text**
            '/__.*?__/s',              // 粗体 __text__
            '/\*[^*]+\*/s',            // 斜体 *text* (改进：避免匹配空格)
            '/_[^_]+_/s',              // 斜体 _text_ (改进：避免匹配空格)
            '/```[\s\S]*?```/m',       // 代码块
            '/`[^`]+`/',               // 行内代码
            '/^\s*[-*+]\s+/m',         // 无序列表
            '/^\s*\d+\.\s+/m',         // 有序列表
            '/\[.+?\]\(.+?\)/',        // 链接
            '/!\[.*?\]\(.+?\)/',       // 图片
            '/^\s*>\s+/m',             // 引用
        ];

        $isMarkdown = false;
        foreach ($markdownPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $isMarkdown = true;
                if ($enableDebugLog) {
                    Log::info('检测到Markdown格式（匹配: ' . $pattern . '）');
                }
                break;
            }
        }

        // 如果不是Markdown格式，直接返回原内容
        if (!$isMarkdown) {
            if ($enableDebugLog) {
                Log::info('既不是HTML也不是Markdown格式，返回纯文本内容');
            }
            return $content;
        }

        // 第三步：使用Parsedown转换Markdown为HTML
        try {
            $parsedown = new \Parsedown();
            $parsedown->setSafeMode(true); // 启用安全模式，防止XSS
            $html = $parsedown->text($content);

            if ($enableDebugLog) {
                Log::info('Markdown转HTML成功，原始长度: ' . mb_strlen($content) . '，转换后长度: ' . mb_strlen($html));
            }

            return $html;
        } catch (\Exception $e) {
            Log::error('Markdown转HTML失败: ' . $e->getMessage());
            return $content; // 转换失败则返回原内容
        }
    }

    /**
     * 导出文章数据
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx'); // xlsx or csv
        $exportType = $request->get('type', 'all'); // all, selected, filtered

        // 验证格式
        if (!in_array($format, ['xlsx', 'csv'])) {
            return Response::error('不支持的导出格式');
        }

        try {
            // 获取要导出的数据
            if ($exportType === 'selected') {
                // 导出选中的文章
                $ids = $request->get('ids', []);
                if (empty($ids)) {
                    return Response::error('请选择要导出的文章');
                }

                $articles = ArticleModel::withoutSiteScope()
                    ->with(['user:id,username'])
                    ->whereIn('id', $ids)
                    ->select()
                    ->toArray();
            } else {
                // 导出筛选的或全部文章
                $query = ArticleModel::withoutSiteScope()
                    ->with(['user:id,username']);

                // 应用筛选条件（使用公共方法）
                $query = $this->applyArticleFilters($query, $request);

                $articles = $query->select()->toArray();
            }

            if (empty($articles)) {
                return Response::error('没有可导出的数据');
            }

            // 批量查询主分类（禁用站点过滤，允许跨站点查询）
            $categoryIds = array_filter(array_unique(array_column($articles, 'category_id')));
            if (!empty($categoryIds)) {
                $categories = \app\model\Category::withoutSiteScope()
                    ->whereIn('id', $categoryIds)
                    ->field('id,name')
                    ->select()
                    ->toArray();

                // 构建分类映射表
                $categoryMap = array_column($categories, null, 'id');

                // 添加分类到文章数据中
                foreach ($articles as &$item) {
                    if (!empty($item['category_id']) && isset($categoryMap[$item['category_id']])) {
                        $item['category'] = $categoryMap[$item['category_id']];
                    } else {
                        $item['category'] = null;
                    }
                }
            } else {
                // 没有分类，添加null
                foreach ($articles as &$item) {
                    $item['category'] = null;
                }
            }

            // 使用Export服务导出
            $filePath = \app\service\ExportService::exportArticles($articles, $format);

            // 记录日志
            Logger::export(OperationLog::MODULE_ARTICLE, '文章', count($articles));

            // 返回文件下载
            $downloadName = 'articles_' . date('YmdHis') . '.' . $format;
            \app\service\ExportService::download($filePath, $downloadName);

        } catch (\Exception $e) {
            Log::error('文章导出失败: ' . $e->getMessage());
            return Response::error('导出失败：' . $e->getMessage());
        }
    }

    /**
     * 获取热门文章列表（带缓存）
     *
     * @param Request $request
     * @return \think\Response
     */
    public function hot(Request $request)
    {
        $limit = $request->get('limit', 10);
        $siteId = $request->get('site_id', '');

        // 构建缓存键参数
        $cacheParams = [
            'limit' => $limit,
            'site_id' => $siteId,
        ];

        // 使用缓存获取热门文章
        $articles = ArticleModel::getCachedList('hot', function () use ($limit, $siteId) {
            $query = ArticleModel::withoutSiteScope()
                ->with([
                    'user',
                    'site:id,name'
                ])
                ->where('status', 1); // 只显示已发布

            // 站点筛选
            if ($siteId !== '') {
                $query->where('articles.site_id', $siteId);
            }

            // 按浏览量、点赞数、评论数综合排序
            $query->order([
                'view_count' => 'desc',
                'like_count' => 'desc',
                'comment_count' => 'desc',
                'publish_time' => 'desc'
            ]);

            return $query->limit($limit)->select();
        }, $cacheParams, 600); // 10分钟缓存

        // 转换为数组并添加主分类信息
        $articlesData = $articles->toArray();
        if (!empty($articlesData)) {
            // 批量查询主分类（禁用站点过滤，允许跨站点查询）
            $categoryIds = array_filter(array_unique(array_column($articlesData, 'category_id')));
            if (!empty($categoryIds)) {
                $categories = \app\model\Category::withoutSiteScope()
                    ->whereIn('id', $categoryIds)
                    ->field('id,name,slug')
                    ->select()
                    ->toArray();

                // 构建分类映射表
                $categoryMap = array_column($categories, null, 'id');

                // 添加分类到文章数据中
                foreach ($articlesData as &$item) {
                    if (!empty($item['category_id']) && isset($categoryMap[$item['category_id']])) {
                        $item['category'] = $categoryMap[$item['category_id']];
                    } else {
                        $item['category'] = null;
                    }
                }
            } else {
                // 没有分类，添加null
                foreach ($articlesData as &$item) {
                    $item['category'] = null;
                }
            }
        }

        return Response::success($articlesData);
    }

    /**
     * 获取推荐文章列表（带缓存）
     *
     * @param Request $request
     * @return \think\Response
     */
    public function recommend(Request $request)
    {
        $limit = $request->get('limit', 10);
        $siteId = $request->get('site_id', '');

        // 构建缓存键参数
        $cacheParams = [
            'limit' => $limit,
            'site_id' => $siteId,
        ];

        // 使用缓存获取推荐文章
        $articles = ArticleModel::getCachedList('recommend', function () use ($limit, $siteId) {
            $query = ArticleModel::withoutSiteScope()
                ->with([
                    'user',
                    'site:id,name'
                ])
                ->where('status', 1) // 只显示已发布
                ->where('is_recommend', 1); // 推荐文章

            // 站点筛选
            if ($siteId !== '') {
                $query->where('articles.site_id', $siteId);
            }

            $query->order([
                'is_top' => 'desc',
                'sort' => 'desc',
                'publish_time' => 'desc'
            ]);

            return $query->limit($limit)->select();
        }, $cacheParams, 600); // 10分钟缓存

        // 转换为数组并添加主分类信息
        $articlesData = $articles->toArray();
        if (!empty($articlesData)) {
            // 批量查询主分类（禁用站点过滤，允许跨站点查询）
            $categoryIds = array_filter(array_unique(array_column($articlesData, 'category_id')));
            if (!empty($categoryIds)) {
                $categories = \app\model\Category::withoutSiteScope()
                    ->whereIn('id', $categoryIds)
                    ->field('id,name,slug')
                    ->select()
                    ->toArray();

                // 构建分类映射表
                $categoryMap = array_column($categories, null, 'id');

                // 添加分类到文章数据中
                foreach ($articlesData as &$item) {
                    if (!empty($item['category_id']) && isset($categoryMap[$item['category_id']])) {
                        $item['category'] = $categoryMap[$item['category_id']];
                    } else {
                        $item['category'] = null;
                    }
                }
            } else {
                // 没有分类，添加null
                foreach ($articlesData as &$item) {
                    $item['category'] = null;
                }
            }
        }

        return Response::success($articlesData);
    }

    /**
     * 应用文章查询过滤条件（提取公共方法，避免代码重复）
     *
     * @param mixed $query 查询构造器
     * @param Request $request 请求对象
     * @return mixed 返回查询构造器
     */
    private function applyArticleFilters($query, Request $request)
    {
        $title = $request->get('title', '');
        $categoryId = $request->get('category_id', '');
        $userId = $request->get('user_id', '');
        $status = $request->get('status', '');
        $isTop = $request->get('is_top', '');
        $isRecommend = $request->get('is_recommend', '');
        $flag = $request->get('flag', '');
        $startTime = $request->get('start_time', '');
        $endTime = $request->get('end_time', '');
        $siteId = $request->get('site_id', '');

        // 标题搜索
        if (!empty($title)) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        // 站点筛选
        if ($siteId !== '') {
            $query->where('articles.site_id', $siteId);
        }

        // 分类筛选（支持主分类和副分类）
        if ($categoryId !== '') {
            $query->where(function($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->whereOr('id', 'in', function($subQuery) use ($categoryId) {
                      $subQuery->table('relations')
                               ->where('source_type', 'article')
                               ->where('target_type', 'category')
                               ->where('target_id', $categoryId)
                               ->field('source_id');
                  });
            });
        }

        // 作者筛选
        if ($userId !== '') {
            $query->where('user_id', $userId);
        }

        // 状态筛选
        if ($status !== '') {
            $query->where('status', $status);
        }

        // 置顶筛选
        if ($isTop !== '') {
            $query->where('is_top', $isTop);
        }

        // 推荐筛选
        if ($isRecommend !== '') {
            $query->where('is_recommend', $isRecommend);
        }

        // 文章属性筛选
        if (!empty($flag)) {
            $query->where('flags', 'like', '%' . $flag . '%');
        }

        // 时间范围筛选
        if (!empty($startTime)) {
            $query->where('publish_time', '>=', $startTime);
        }
        if (!empty($endTime)) {
            $query->where('publish_time', '<=', $endTime . ' 23:59:59');
        }

        return $query;
    }
}
