<?php
declare(strict_types=1);

namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Cache;
use app\model\Category;
use app\model\Tag;
use app\model\Article;
use app\model\Site;
use app\model\Config;
use app\model\AdminUser;

/**
 * 缓存预热命令
 *
 * 用法:
 * php think cache:warmup                    // 预热所有缓存
 * php think cache:warmup --type=all         // 预热所有缓存
 * php think cache:warmup --type=config      // 预热系统配置
 * php think cache:warmup --type=sites       // 预热站点数据
 * php think cache:warmup --type=categories  // 预热分类数据
 * php think cache:warmup --type=tags        // 预热标签数据
 * php think cache:warmup --type=articles    // 预热文章数据
 * php think cache:warmup --type=permissions // 预热用户权限
 */
class CacheWarmup extends Command
{
    protected function configure()
    {
        $this->setName('cache:warmup')
            ->addOption('type', 't', Option::VALUE_OPTIONAL, '预热类型 (all, categories, tags, articles, sites, config, permissions)', 'all')
            ->setDescription('预热缓存，提前加载常用数据');
    }

    protected function execute(Input $input, Output $output)
    {
        $type = $input->getOption('type');

        $output->writeln('');
        $output->writeln('<info>开始缓存预热...</info>');
        $output->writeln('');

        $startTime = microtime(true);
        $warmed = [];

        try {
            // 预热系统配置缓存
            if ($type === 'all' || $type === 'config') {
                $output->write('预热系统配置缓存... ');
                $this->warmupConfig();
                $warmed[] = 'config';
                $output->writeln('<comment>完成</comment>');
            }

            // 预热站点缓存
            if ($type === 'all' || $type === 'sites') {
                $output->write('预热站点缓存... ');
                $this->warmupSites();
                $warmed[] = 'sites';
                $output->writeln('<comment>完成</comment>');
            }

            // 预热分类缓存
            if ($type === 'all' || $type === 'categories') {
                $output->write('预热分类缓存... ');
                $this->warmupCategories();
                $warmed[] = 'categories';
                $output->writeln('<comment>完成</comment>');
            }

            // 预热标签缓存
            if ($type === 'all' || $type === 'tags') {
                $output->write('预热标签缓存... ');
                $this->warmupTags();
                $warmed[] = 'tags';
                $output->writeln('<comment>完成</comment>');
            }

            // 预热文章缓存
            if ($type === 'all' || $type === 'articles') {
                $output->write('预热文章缓存... ');
                $this->warmupArticles();
                $warmed[] = 'articles';
                $output->writeln('<comment>完成</comment>');
            }

            // 预热用户权限缓存
            if ($type === 'all' || $type === 'permissions') {
                $output->write('预热用户权限缓存... ');
                $this->warmupPermissions();
                $warmed[] = 'permissions';
                $output->writeln('<comment>完成</comment>');
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $output->writeln('');
            $output->writeln("<info>✓ 缓存预热完成！</info>");
            $output->writeln("<comment>预热项目: " . implode(', ', $warmed) . "</comment>");
            $output->writeln("<comment>耗时: {$duration}秒</comment>");
            $output->writeln('');

            return 0;
        } catch (\Exception $e) {
            $output->writeln('');
            $output->writeln("<error>✗ 缓存预热失败: " . $e->getMessage() . "</error>");
            $output->writeln('');
            return 1;
        }
    }

    /**
     * 预热分类缓存
     */
    protected function warmupCategories()
    {
        // 预热分类树（状态=1）
        Category::getCachedList('tree', function () {
            $query = Category::withoutSiteScope()
                ->with(['site'])
                ->where('status', 1)
                ->order(['sort' => 'asc', 'id' => 'asc']);

            $categories = $query->select()->toArray();

            // 构建树形结构（简单版本）
            return $this->buildTree($categories);
        }, ['status' => 1]);

        // 预热所有站点的分类树
        $siteIds = \app\model\Site::column('id');
        foreach ($siteIds as $siteId) {
            Category::getCachedList('tree', function () use ($siteId) {
                $query = Category::withoutSiteScope()
                    ->with(['site'])
                    ->where('status', 1)
                    ->where('site_id', $siteId)
                    ->order(['sort' => 'asc', 'id' => 'asc']);

                $categories = $query->select()->toArray();

                return $this->buildTree($categories);
            }, ['status' => 1, 'site_id' => $siteId]);
        }
    }

    /**
     * 预热标签缓存
     */
    protected function warmupTags()
    {
        // 预热标签列表
        Tag::getCachedList('all', function () {
            return Tag::where('status', 1)
                ->order(['sort' => 'asc', 'id' => 'desc'])
                ->select();
        }, ['status' => 1]);
    }

    /**
     * 预热文章缓存
     */
    protected function warmupArticles()
    {
        // 预热热门文章
        Article::getCachedList('hot', function () {
            return Article::withoutSiteScope()
                ->with([
                    'category:id,name,slug',
                    'user:id,username,real_name',
                    'site:id,name'
                ])
                ->where('status', 1)
                ->order([
                    'view_count' => 'desc',
                    'like_count' => 'desc',
                    'comment_count' => 'desc',
                    'publish_time' => 'desc'
                ])
                ->limit(10)
                ->select();
        }, ['limit' => 10], 600);

        // 预热推荐文章
        Article::getCachedList('recommend', function () {
            return Article::withoutSiteScope()
                ->with([
                    'category:id,name,slug',
                    'user:id,username,real_name',
                    'site:id,name'
                ])
                ->where('status', 1)
                ->where('is_recommend', 1)
                ->order([
                    'is_top' => 'desc',
                    'sort' => 'desc',
                    'publish_time' => 'desc'
                ])
                ->limit(10)
                ->select();
        }, ['limit' => 10], 600);

        // 预热各站点的热门文章
        $siteIds = \app\model\Site::column('id');
        foreach ($siteIds as $siteId) {
            Article::getCachedList('hot', function () use ($siteId) {
                return Article::withoutSiteScope()
                    ->with([
                        'category:id,name,slug',
                        'user:id,username,real_name',
                        'site:id,name'
                    ])
                    ->where('status', 1)
                    ->where('site_id', $siteId)
                    ->order([
                        'view_count' => 'desc',
                        'like_count' => 'desc',
                        'comment_count' => 'desc',
                        'publish_time' => 'desc'
                    ])
                    ->limit(10)
                    ->select();
            }, ['limit' => 10, 'site_id' => $siteId], 600);
        }
    }

    /**
     * 预热系统配置缓存
     */
    protected function warmupConfig()
    {
        // 预热所有配置
        Config::getAllConfigsCached();
    }

    /**
     * 预热站点缓存
     */
    protected function warmupSites()
    {
        // 预热启用的站点列表
        Site::getEnabledSites();

        // 预热主站
        Site::getMainSite();

        // 预热站点选项
        \app\service\SiteContextService::getSiteOptions(true);
        \app\service\SiteContextService::getSiteOptions(false);

        // 预热各站点代码
        $siteCodes = Site::column('site_code');
        foreach ($siteCodes as $siteCode) {
            Site::getBySiteCode($siteCode);
        }
    }

    /**
     * 预热用户权限缓存
     */
    protected function warmupPermissions()
    {
        // 预热所有活跃用户的权限
        $activeUserIds = AdminUser::where('status', 1)->column('id');
        foreach ($activeUserIds as $userId) {
            AdminUser::getUserPermissions($userId);
        }
    }

    /**
     * 构建树形结构（简化版）
     */
    protected function buildTree($items, $parentId = 0)
    {
        $tree = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }
}
