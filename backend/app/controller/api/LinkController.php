<?php
declare (strict_types = 1);

namespace app\controller\api;

use app\model\Link;
use think\Request;
use think\facade\Validate;

/**
 * 友情链接管理控制器
 */
class LinkController extends BaseController
{
    /**
     * 获取链接列表
     */
    public function index(Request $request)
    {
        $page = (int) $request->param('page', 1);
        $pageSize = (int) $request->param('page_size', 10);
        $keyword = $request->param('keyword', '');
        $groupId = $request->param('group_id', '');
        $status = $request->param('status', '');
        $isHome = $request->param('is_home', '');
        $siteId = $request->param('site_id', '');

        $query = Link::withoutSiteScope()->with(['group', 'site'])
            ->order('sort', 'asc')
            ->order('id', 'desc');

        // 站点筛选
        if ($siteId !== '') {
            $query->where('links.site_id', (int) $siteId);
        }

        // 关键词搜索
        if (!empty($keyword)) {
            $query->where('name|url', 'like', '%' . $keyword . '%');
        }

        // 分组筛选
        if ($groupId !== '') {
            $query->where('group_id', (int) $groupId);
        }

        // 状态筛选
        if ($status !== '') {
            $query->where('status', (int) $status);
        }

        // 首页显示筛选
        if ($isHome !== '') {
            $query->where('is_home', (int) $isHome);
        }

        $list = $query->paginate([
            'list_rows' => $pageSize,
            'page' => $page,
        ]);

        return $this->success([
            'list' => $list->items(),
            'total' => $list->total(),
            'page' => $page,
            'page_size' => $pageSize,
        ]);
    }

    /**
     * 获取链接详情
     */
    public function read($id)
    {
        $link = Link::withoutSiteScope()->with(['group'])->find($id);

        if (!$link) {
            return $this->error('链接不存在');
        }

        return $this->success($link);
    }

    /**
     * 创建链接
     */
    public function save(Request $request)
    {
        $data = $request->only([
            'group_id',
            'name',
            'url',
            'logo',
            'description',
            'email',
            'sort',
            'status',
            'is_home',
        ]);

        // 数据验证
        $validate = Validate::rule([
            'name' => 'require|max:100',
            'url' => 'require|url|max:255',
            'email' => 'email',
        ])->message([
            'name.require' => '网站名称不能为空',
            'name.max' => '网站名称最多100个字符',
            'url.require' => '网站URL不能为空',
            'url.url' => 'URL格式不正确',
            'url.max' => 'URL最多255个字符',
            'email.email' => '邮箱格式不正确',
        ]);

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        // 设置默认值
        if (!isset($data['status'])) {
            $data['status'] = Link::STATUS_PENDING;
        }
        if (!isset($data['sort'])) {
            $data['sort'] = 0;
        }
        if (!isset($data['is_home'])) {
            $data['is_home'] = 0;
        }

        try {
            $link = new Link();
            $link->save($data);
            return $this->success($link, '创建成功');
        } catch (\Exception $e) {
            return $this->error('创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新链接
     */
    public function update(Request $request, $id)
    {
        $link = Link::withoutSiteScope()->find($id);

        if (!$link) {
            return $this->error('链接不存在');
        }

        $data = $request->only([
            'group_id',
            'name',
            'url',
            'logo',
            'description',
            'email',
            'sort',
            'status',
            'is_home',
        ]);

        // 数据验证
        $validate = Validate::rule([
            'name' => 'require|max:100',
            'url' => 'require|url|max:255',
            'email' => 'email',
        ])->message([
            'name.require' => '网站名称不能为空',
            'name.max' => '网站名称最多100个字符',
            'url.require' => '网站URL不能为空',
            'url.url' => 'URL格式不正确',
            'url.max' => 'URL最多255个字符',
            'email.email' => '邮箱格式不正确',
        ]);

        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        try {
            // 使用Db类直接更新，确保WHERE条件精确，只更新指定ID的记录
            $affected = \think\facade\Db::name('links')
                ->where('id', '=', $id)
                ->limit(1)
                ->update($data);

            if ($affected === 0) {
                return $this->error('更新失败：未找到该链接或数据未改变');
            }

            return $this->success(['affected' => $affected], '更新成功');
        } catch (\Exception $e) {
            return $this->error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除链接
     */
    public function delete($id)
    {
        $link = Link::withoutSiteScope()->find($id);

        if (!$link) {
            return $this->error('链接不存在');
        }

        try {
            // 使用Db类直接执行软删除，确保只删除指定ID的记录
            $affected = \think\facade\Db::name('links')
                ->where('id', '=', $id)
                ->limit(1)
                ->update(['deleted_at' => date('Y-m-d H:i:s')]);

            if ($affected === 0) {
                return $this->error('链接删除失败：未找到该链接');
            }

            return $this->success(null, '删除成功');
        } catch (\Exception $e) {
            return $this->error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 审核链接
     */
    public function audit(Request $request, $id)
    {
        $link = Link::withoutSiteScope()->find($id);

        if (!$link) {
            return $this->error('链接不存在');
        }

        $action = $request->param('action'); // approve 或 reject
        $note = $request->param('note', '');
        $userId = $request->user['id'];

        if ($action === 'approve') {
            $link->approve($userId, $note);
            return $this->success(null, '审核通过');
        } elseif ($action === 'reject') {
            $link->reject($userId, $note);
            return $this->success(null, '已拒绝');
        } else {
            return $this->error('无效的操作');
        }
    }
}
