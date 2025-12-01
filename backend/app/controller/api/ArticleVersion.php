<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\common\Logger;
use app\model\ArticleVersion as ArticleVersionModel;
use app\model\Article as ArticleModel;
use app\model\ArticleTag;
use app\model\OperationLog;
use think\Request;
use think\facade\Db;

/**
 * 文章版本管理控制器
 */
class ArticleVersion extends BaseController
{
    /**
     * 获取文章的版本列表
     */
    public function index(Request $request, $articleId)
    {
        // 查询文章，不受站点限制
        $article = ArticleModel::withoutSiteScope()->find($articleId);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        $page = (int) $request->get('page', 1);
        $pageSize = (int) $request->get('page_size', 20);

        // 查询版本列表，使用文章所属站点
        $query = ArticleVersionModel::forSite($article->site_id)
            ->with(['creator'])
            ->where('article_id', $articleId)
            ->order('version_number', 'desc');

        $total = $query->count();
        $list = $query->page($page, $pageSize)->select()->toArray();

        return Response::paginate($list, $total, $page, $pageSize);
    }

    /**
     * 获取版本详情
     */
    public function read($id)
    {
        // 查询版本，不受站点限制
        $version = ArticleVersionModel::withoutSiteScope()
            ->with(['article', 'category', 'user', 'creator'])
            ->find($id);

        if (!$version) {
            return Response::notFound('版本不存在');
        }

        return Response::success($version);
    }

    /**
     * 对比两个版本
     */
    public function compare(Request $request)
    {
        $oldVersionId = $request->get('old_version_id');
        $newVersionId = $request->get('new_version_id');

        if (!$oldVersionId || !$newVersionId) {
            return Response::error('请提供要对比的版本ID');
        }

        // 查询版本，不受站点限制
        $oldVersion = ArticleVersionModel::withoutSiteScope()->find($oldVersionId);
        $newVersion = ArticleVersionModel::withoutSiteScope()->find($newVersionId);

        if (!$oldVersion || !$newVersion) {
            return Response::notFound('版本不存在');
        }

        if ($oldVersion->article_id !== $newVersion->article_id) {
            return Response::error('不能对比不同文章的版本');
        }

        // 获取差异
        $diff = ArticleVersionModel::compareVersions($newVersion, $oldVersion);

        return Response::success([
            'old_version' => [
                'id' => $oldVersion->id,
                'version_number' => $oldVersion->version_number,
                'create_time' => $oldVersion->create_time,
                'created_by' => $oldVersion->creator ? $oldVersion->creator->username : null,
                'change_log' => $oldVersion->change_log,
            ],
            'new_version' => [
                'id' => $newVersion->id,
                'version_number' => $newVersion->version_number,
                'create_time' => $newVersion->create_time,
                'created_by' => $newVersion->creator ? $newVersion->creator->username : null,
                'change_log' => $newVersion->change_log,
            ],
            'diff' => $diff,
        ]);
    }

    /**
     * 回滚到指定版本
     */
    public function rollback(Request $request, $id)
    {
        // 查询版本，不受站点限制
        $version = ArticleVersionModel::withoutSiteScope()->find($id);
        if (!$version) {
            return Response::notFound('版本不存在');
        }

        // 查询文章，不受站点限制
        $article = ArticleModel::withoutSiteScope()->find($version->article_id);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        Db::startTrans();
        try {
            // 备份当前版本
            $currentVersion = ArticleVersionModel::createFromArticle(
                $article,
                $request->user['id'],
                '回滚前备份'
            );

            // 恢复到指定版本的数据
            $article->title           = $version->title;
            $article->slug            = $version->slug;
            $article->summary         = $version->summary;
            $article->content         = $version->content;
            $article->cover_image     = $version->cover_image;
            $article->images          = $version->images;
            $article->category_id     = $version->category_id;
            $article->author          = $version->author;
            $article->source          = $version->source;
            $article->source_url      = $version->source_url;
            $article->is_top          = $version->is_top;
            $article->is_recommend    = $version->is_recommend;
            $article->is_hot          = $version->is_hot;
            $article->seo_title       = $version->seo_title;
            $article->seo_keywords    = $version->seo_keywords;
            $article->seo_description = $version->seo_description;
            $article->sort            = $version->sort;
            $article->flags           = $version->flags;
            $article->save();

            // 恢复标签
            if ($version->tags && is_array($version->tags)) {
                // 删除旧的标签关联
                ArticleTag::where('article_id', $article->id)->delete();

                // 添加版本中的标签
                foreach ($version->tags as $tagId => $tagName) {
                    ArticleTag::create([
                        'article_id' => $article->id,
                        'tag_id' => $tagId
                    ]);
                }
            }

            // 创建回滚版本记录（标签关系会自动延迟加载）
            ArticleVersionModel::createFromArticle(
                $article,
                $request->user['id'],
                "回滚到版本 #{$version->version_number}"
            );

            Db::commit();

            // 记录日志
            Logger::update(OperationLog::MODULE_ARTICLE, '文章版本回滚', $article->id);

            return Response::success([
                'article_id' => $article->id,
                'rollback_to_version' => $version->version_number,
            ], '版本回滚成功');

        } catch (\Exception $e) {
            Db::rollback();
            return Response::error('版本回滚失败：' . $e->getMessage());
        }
    }

    /**
     * 删除版本
     */
    public function delete($id)
    {
        // 查询版本，不受站点限制
        $version = ArticleVersionModel::withoutSiteScope()->find($id);
        if (!$version) {
            return Response::notFound('版本不存在');
        }

        // 检查是否是唯一版本（防止删除最后一个版本）
        $versionCount = ArticleVersionModel::forSite($version->site_id)
            ->where('article_id', $version->article_id)
            ->count();
        if ($versionCount <= 1) {
            return Response::error('不能删除文章的最后一个版本');
        }

        try {
            $versionId = $version->id;

            // 使用Db类直接删除，确保WHERE条件精确
            $affected = \think\facade\Db::name('article_versions')
                ->where('id', '=', $versionId)
                ->limit(1)
                ->delete();

            if ($affected === 0) {
                throw new \Exception('版本删除失败：未找到该版本');
            }

            // 记录日志
            Logger::delete(OperationLog::MODULE_ARTICLE, '文章版本', $id);

            return Response::success([], '版本删除成功');
        } catch (\Exception $e) {
            return Response::error('版本删除失败：' . $e->getMessage());
        }
    }

    /**
     * 批量删除版本
     */
    public function batchDelete(Request $request)
    {
        $ids = $request->post('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return Response::error('请选择要删除的版本');
        }

        try {
            // 检查每个版本是否可以删除
            foreach ($ids as $id) {
                $version = ArticleVersionModel::withoutSiteScope()->find($id);
                if ($version) {
                    $versionCount = ArticleVersionModel::forSite($version->site_id)
                        ->where('article_id', $version->article_id)
                        ->count();
                    if ($versionCount <= 1) {
                        return Response::error("文章「{$version->title}」只有一个版本，不能删除");
                    }
                }
            }

            // 执行删除
            ArticleVersionModel::destroy($ids);

            // 记录日志
            Logger::delete(OperationLog::MODULE_ARTICLE, '批量删除文章版本', 0);

            return Response::success([], '批量删除成功');
        } catch (\Exception $e) {
            return Response::error('批量删除失败：' . $e->getMessage());
        }
    }

    /**
     * 获取版本统计信息
     */
    public function statistics($articleId)
    {
        // 查询文章，不受站点限制
        $article = ArticleModel::withoutSiteScope()->find($articleId);
        if (!$article) {
            return Response::notFound('文章不存在');
        }

        // 查询版本统计，使用文章所属站点
        $versionCount = ArticleVersionModel::forSite($article->site_id)
            ->where('article_id', $articleId)
            ->count();

        $latestVersion = ArticleVersionModel::forSite($article->site_id)
            ->where('article_id', $articleId)
            ->order('version_number', 'desc')
            ->find();

        $firstVersion = ArticleVersionModel::forSite($article->site_id)
            ->where('article_id', $articleId)
            ->order('version_number', 'asc')
            ->find();

        return Response::success([
            'article_id' => $articleId,
            'article_title' => $article->title,
            'version_count' => $versionCount,
            'latest_version' => $latestVersion ? [
                'id' => $latestVersion->id,
                'version_number' => $latestVersion->version_number,
                'create_time' => $latestVersion->create_time,
                'change_log' => $latestVersion->change_log,
            ] : null,
            'first_version' => $firstVersion ? [
                'id' => $firstVersion->id,
                'version_number' => $firstVersion->version_number,
                'create_time' => $firstVersion->create_time,
            ] : null,
        ]);
    }
}
