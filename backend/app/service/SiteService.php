<?php
declare (strict_types = 1);

namespace app\service;

use app\model\Site;
use app\model\SiteAdmin;
use app\model\Article;
use app\model\FrontUser;
use think\facade\Db;
use think\Exception;

/**
 * 站点管理服务
 */
class SiteService
{
    /**
     * 获取站点列表
     * @param array $params 查询参数
     * @return array
     */
    public function getList($params = [])
    {
        $page = (int)($params['page'] ?? 1);
        $limit = (int)($params['limit'] ?? 15);

        // 过滤掉非搜索参数
        $searchParams = $params;
        unset($searchParams['page'], $searchParams['limit'], $searchParams['with_parent'], $searchParams['with_template']);

        $query = Site::withSearch(array_keys($searchParams), $searchParams);

        // 关联父站点
        if (isset($params['with_parent']) && $params['with_parent']) {
            $query->with(['parent']);
        }

        // 关联模板
        if (isset($params['with_template']) && $params['with_template']) {
            $query->with(['template']);
        }

        $total = $query->count();
        $list = $query->page($page, $limit)
            ->order('sort', 'asc')
            ->order('id', 'asc')
            ->select();

        return [
            'total' => $total,
            'list'  => $list,
            'page'  => $page,
            'limit' => $limit,
        ];
    }

    /**
     * 获取站点详情
     * @param int $id 站点ID
     * @return Site
     * @throws Exception
     */
    public function getDetail($id)
    {
        $site = Site::with(['parent', 'template'])->find($id);

        if (!$site) {
            throw new Exception('站点不存在');
        }

        return $site;
    }

    /**
     * 创建站点
     * @param array $data 站点数据
     * @return Site
     * @throws Exception
     */
    public function create($data)
    {
        // 验证站点代码唯一性
        if (Site::where('site_code', $data['site_code'])->count() > 0) {
            throw new Exception('站点代码已存在');
        }

        // 如果是子域名模式，验证子域名唯一性
        if (!empty($data['sub_domain']) && Site::where('sub_domain', $data['sub_domain'])->count() > 0) {
            throw new Exception('子域名已被使用');
        }

        // 验证静态生成目录唯一性
        if (!empty($data['static_output_dir'])) {
            // 规范化路径，去除首尾斜杠
            $data['static_output_dir'] = trim($data['static_output_dir'], '/\\');

            // 检查是否与其他站点重复
            if (Site::where('static_output_dir', $data['static_output_dir'])->count() > 0) {
                throw new Exception('静态生成目录已被其他站点使用，请更换目录');
            }
        }

        // 生成表前缀（如果需要）
        if (empty($data['db_prefix']) && $data['site_type'] != Site::TYPE_MAIN) {
            $data['db_prefix'] = 'site_' . $data['site_code'] . '_';
        }

        // 处理SEO配置字段
        $seoConfig = [];
        if (isset($data['seo_title'])) {
            $seoConfig['seo_title'] = $data['seo_title'];
            unset($data['seo_title']);
        }
        if (isset($data['seo_keywords'])) {
            $seoConfig['seo_keywords'] = $data['seo_keywords'];
            unset($data['seo_keywords']);
        }
        if (isset($data['seo_description'])) {
            $seoConfig['seo_description'] = $data['seo_description'];
            unset($data['seo_description']);
        }
        if (!empty($seoConfig)) {
            $data['seo_config'] = json_encode($seoConfig, JSON_UNESCAPED_UNICODE);
        }

        // 处理核心配置字段
        $coreConfig = [];
        if (isset($data['index_template'])) {
            $coreConfig['index_template'] = $data['index_template'];
            unset($data['index_template']);
        }
        if (isset($data['recycle_bin_enable'])) {
            $coreConfig['recycle_bin_enable'] = $data['recycle_bin_enable'];
            unset($data['recycle_bin_enable']);
        }
        if (isset($data['article_sub_category'])) {
            $coreConfig['article_sub_category'] = $data['article_sub_category'];
            unset($data['article_sub_category']);
        }
        if (!empty($coreConfig)) {
            $data['config'] = json_encode($coreConfig, JSON_UNESCAPED_UNICODE);
        }

        // 处理JSON字段（保留原有逻辑）
        if (isset($data['storage_config']) && is_array($data['storage_config'])) {
            $data['storage_config'] = json_encode($data['storage_config'], JSON_UNESCAPED_UNICODE);
        }

        Db::startTrans();
        try {
            $site = Site::create($data);

            // 如果指定了管理员，创建站点管理员关联
            if (!empty($data['admin_user_ids']) && is_array($data['admin_user_ids'])) {
                $this->assignAdmins($site->id, $data['admin_user_ids']);
            }

            // 为非主站创建独立表
            if ($site->site_type != Site::TYPE_MAIN && !empty($site->db_prefix)) {
                try {
                    $tableResult = SiteTableService::createSiteTables($site->site_code, $site->db_prefix);

                    // 记录创建表的结果
                    $successCount = count($tableResult['success']);
                    $failedCount = count($tableResult['failed']);

                    if ($failedCount > 0) {
                        // 有失败的表，但不影响站点创建
                        trace("站点表创建部分失败: 成功 {$successCount} 个，失败 {$failedCount} 个", 'warning');
                    }
                } catch (\Exception $e) {
                    // 创建表失败不影响站点创建，记录错误
                    trace('创建站点表失败: ' . $e->getMessage(), 'error');
                }
            }

            Db::commit();
            return $site;
        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception('创建站点失败：' . $e->getMessage());
        }
    }

    /**
     * 更新站点
     * @param int $id 站点ID
     * @param array $data 站点数据
     * @return bool
     * @throws Exception
     */
    public function update($id, $data)
    {
        $site = Site::find($id);
        if (!$site) {
            throw new Exception('站点不存在');
        }

        // 如果修改了站点代码，验证唯一性
        if (isset($data['site_code']) && $data['site_code'] != $site->site_code) {
            if (Site::where('site_code', $data['site_code'])->where('id', '<>', $id)->count() > 0) {
                throw new Exception('站点代码已存在');
            }
        }

        // 如果修改了子域名，验证唯一性
        if (isset($data['sub_domain']) && $data['sub_domain'] != $site->sub_domain && !empty($data['sub_domain'])) {
            if (Site::where('sub_domain', $data['sub_domain'])->where('id', '<>', $id)->count() > 0) {
                throw new Exception('子域名已被使用');
            }
        }

        // 如果修改了静态生成目录，验证唯一性
        if (isset($data['static_output_dir'])) {
            // 规范化路径，去除首尾斜杠
            $data['static_output_dir'] = trim($data['static_output_dir'], '/\\');

            // 如果为空字符串，转为null
            if ($data['static_output_dir'] === '') {
                $data['static_output_dir'] = null;
            }

            // 如果不为空且与原值不同，检查是否与其他站点重复
            if (!empty($data['static_output_dir']) && $data['static_output_dir'] != $site->static_output_dir) {
                if (Site::where('static_output_dir', $data['static_output_dir'])->where('id', '<>', $id)->count() > 0) {
                    throw new Exception('静态生成目录已被其他站点使用，请更换目录');
                }
            }
        }

        // 处理SEO配置字段
        $seoConfig = [];
        if (isset($data['seo_title'])) {
            $seoConfig['seo_title'] = $data['seo_title'];
            unset($data['seo_title']);
        }
        if (isset($data['seo_keywords'])) {
            $seoConfig['seo_keywords'] = $data['seo_keywords'];
            unset($data['seo_keywords']);
        }
        if (isset($data['seo_description'])) {
            $seoConfig['seo_description'] = $data['seo_description'];
            unset($data['seo_description']);
        }
        if (!empty($seoConfig)) {
            // 如果站点已有SEO配置，合并
            // 注意：ThinkPHP的类型转换已将seo_config转为数组，无需json_decode
            $existingSeoConfig = is_array($site->seo_config) ? $site->seo_config : [];
            $mergedSeoConfig = array_merge($existingSeoConfig, $seoConfig);
            $data['seo_config'] = json_encode($mergedSeoConfig, JSON_UNESCAPED_UNICODE);
        }

        // 处理核心配置字段
        $coreConfig = [];
        if (isset($data['index_template'])) {
            $coreConfig['index_template'] = $data['index_template'];
            unset($data['index_template']);
        }
        if (isset($data['recycle_bin_enable'])) {
            $coreConfig['recycle_bin_enable'] = $data['recycle_bin_enable'];
            unset($data['recycle_bin_enable']);
        }
        if (isset($data['article_sub_category'])) {
            $coreConfig['article_sub_category'] = $data['article_sub_category'];
            unset($data['article_sub_category']);
        }
        if (!empty($coreConfig)) {
            // 如果站点已有核心配置，合并
            // 注意：ThinkPHP的类型转换已将config转为数组，无需json_decode
            $existingConfig = is_array($site->config) ? $site->config : [];
            $mergedConfig = array_merge($existingConfig, $coreConfig);
            $data['config'] = json_encode($mergedConfig, JSON_UNESCAPED_UNICODE);
        }

        // 处理JSON字段（保留原有逻辑）
        if (isset($data['storage_config']) && is_array($data['storage_config'])) {
            $data['storage_config'] = json_encode($data['storage_config'], JSON_UNESCAPED_UNICODE);
        }

        return $site->save($data);
    }

    /**
     * 删除站点
     * @param int $id 站点ID
     * @return bool
     * @throws Exception
     */
    public function delete($id)
    {
        $site = Site::find($id);
        if (!$site) {
            throw new Exception('站点不存在');
        }

        // 不允许删除主站
        if ($site->site_type == Site::TYPE_MAIN) {
            throw new Exception('不允许删除主站');
        }

        // 检查是否有子站点
        if ($site->children()->count() > 0) {
            throw new Exception('该站点下还有子站点，无法删除');
        }

        // 检查是否有内容
        $articleCount = Article::where('site_id', $id)->count();
        if ($articleCount > 0) {
            throw new Exception('该站点下还有 ' . $articleCount . ' 篇文章，无法删除');
        }

        Db::startTrans();
        try {
            // 删除站点记录
            $site->delete();

            // 删除站点的独立表
            if (!empty($site->db_prefix)) {
                try {
                    $tableCount = SiteTableService::dropSiteTables($site->db_prefix);
                    trace("已删除站点 {$site->site_name} 的 {$tableCount} 张独立表", 'info');
                } catch (\Exception $e) {
                    // 删除表失败，记录日志但不影响站点删除
                    trace('删除站点表失败: ' . $e->getMessage(), 'error');
                }
            }

            Db::commit();
            return true;
        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception('删除站点失败: ' . $e->getMessage());
        }
    }

    /**
     * 批量删除站点
     * @param array $ids 站点ID数组
     * @return int 删除数量
     */
    public function batchDelete($ids)
    {
        $count = 0;
        foreach ($ids as $id) {
            try {
                $this->delete($id);
                $count++;
            } catch (\Exception $e) {
                // 记录错误，继续删除其他站点
                continue;
            }
        }
        return $count;
    }

    /**
     * 更新站点状态
     * @param int $id 站点ID
     * @param int $status 状态
     * @return bool
     * @throws Exception
     */
    public function updateStatus($id, $status)
    {
        $site = Site::find($id);
        if (!$site) {
            throw new Exception('站点不存在');
        }

        return $site->save(['status' => $status]);
    }

    /**
     * 根据站点代码获取站点
     * @param string $siteCode 站点代码
     * @return Site|null
     */
    public function getBySiteCode($siteCode)
    {
        return Site::getBySiteCode($siteCode);
    }

    /**
     * 根据地域代码获取站点
     * @param string $regionCode 地域代码
     * @return Site|null
     */
    public function getByRegionCode($regionCode)
    {
        return Site::getByRegionCode($regionCode);
    }

    /**
     * 获取所有启用的站点
     * @return array
     */
    public function getEnabledSites()
    {
        return Site::getEnabledSites()->toArray();
    }

    /**
     * 为站点分配管理员
     * @param int $siteId 站点ID
     * @param array $adminUserIds 管理员用户ID数组
     * @return bool
     */
    public function assignAdmins($siteId, $adminUserIds)
    {
        // 删除现有的管理员关联
        SiteAdmin::where('site_id', $siteId)->delete();

        // 创建新的管理员关联
        foreach ($adminUserIds as $adminUserId) {
            SiteAdmin::create([
                'site_id'       => $siteId,
                'admin_user_id' => $adminUserId,
                'role_type'     => SiteAdmin::ROLE_ADMIN,
                'status'        => SiteAdmin::STATUS_ENABLED,
            ]);
        }

        return true;
    }

    /**
     * 获取站点的管理员列表
     * @param int $siteId 站点ID
     * @return array
     */
    public function getSiteAdmins($siteId)
    {
        return SiteAdmin::getSiteAdmins($siteId)->toArray();
    }

    /**
     * 检查管理员是否有站点访问权限
     * @param int $adminUserId 管理员用户ID
     * @param int $siteId 站点ID
     * @return bool
     */
    public function hasAccess($adminUserId, $siteId)
    {
        // 超级管理员有所有站点的访问权限
        // 这里需要根据实际的权限系统进行判断

        return SiteAdmin::hasAccess($adminUserId, $siteId);
    }

    /**
     * 获取管理员可访问的站点列表
     * @param int $adminUserId 管理员用户ID
     * @return array
     */
    public function getAdminSites($adminUserId)
    {
        $siteAdmins = SiteAdmin::getAdminSites($adminUserId);
        $sites = [];

        foreach ($siteAdmins as $siteAdmin) {
            if ($siteAdmin->site) {
                $sites[] = $siteAdmin->site;
            }
        }

        return $sites;
    }

    /**
     * 更新站点统计数据
     * @param int $siteId 站点ID
     * @return bool
     */
    public function updateStats($siteId)
    {
        $site = Site::find($siteId);
        if (!$site) {
            return false;
        }

        $site->updateArticleCount();
        $site->updateUserCount();

        return true;
    }

    /**
     * 增加站点访问量
     * @param int $siteId 站点ID
     * @param int $count 增加数量
     * @return bool
     */
    public function incrementVisitCount($siteId, $count = 1)
    {
        $site = Site::find($siteId);
        if (!$site) {
            return false;
        }

        return $site->incrementVisitCount($count);
    }

    /**
     * 复制站点配置到新站点
     * @param int $fromSiteId 源站点ID
     * @param int $toSiteId 目标站点ID
     * @return bool
     * @throws Exception
     */
    public function copyConfig($fromSiteId, $toSiteId)
    {
        $fromSite = Site::find($fromSiteId);
        $toSite = Site::find($toSiteId);

        if (!$fromSite || !$toSite) {
            throw new Exception('站点不存在');
        }

        // 复制配置字段
        $toSite->save([
            'config'           => $fromSite->config,
            'seo_config'       => $fromSite->seo_config,
            'analytics_config' => $fromSite->analytics_config,
            'template_id'      => $fromSite->template_id,
            'template_path'    => $fromSite->template_path,
            'theme_color'      => $fromSite->theme_color,
            'storage_type'     => $fromSite->storage_type,
            'storage_config'   => $fromSite->storage_config,
        ]);

        return true;
    }
}
