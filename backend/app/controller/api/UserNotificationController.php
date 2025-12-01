<?php

namespace app\controller\api;

use app\model\UserNotification;
use app\model\UserNotificationSetting;
use think\Request;
use think\Response;

/**
 * 用户通知控制器（前台）
 */
class UserNotificationController extends BaseController
{
    /**
     * 获取当前用户通知列表
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $userId = $request->userId ?? 0;
        $page = $request->param('page', 1);
        $pageSize = $request->param('page_size', 20);
        $type = $request->param('type', '');
        $isRead = $request->param('is_read', '');

        if (!$userId) {
            return json(['code' => 401, 'message' => '未登录']);
        }

        $filters = [];
        if ($type) {
            $filters['type'] = $type;
        }
        if ($isRead !== '') {
            $filters['is_read'] = $isRead;
        }

        $result = UserNotification::getUserNotifications($userId, $page, $pageSize, $filters);

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => $result
        ]);
    }

    /**
     * 获取未读数量
     * @param Request $request
     * @return Response
     */
    public function unreadCount(Request $request): Response
    {
        $userId = $request->userId ?? 0;

        if (!$userId) {
            return json(['code' => 401, 'message' => '未登录']);
        }

        $count = UserNotification::getUnreadCount($userId);

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => ['count' => $count]
        ]);
    }

    /**
     * 标记为已读
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function markAsRead(Request $request, int $id): Response
    {
        $userId = $request->userId ?? 0;

        if (!$userId) {
            return json(['code' => 401, 'message' => '未登录']);
        }

        $result = UserNotification::markAsRead($id, $userId);

        if (!$result) {
            return json(['code' => 400, 'message' => '操作失败']);
        }

        return json([
            'code' => 0,
            'message' => '已标记为已读'
        ]);
    }

    /**
     * 批量标记为已读
     * @param Request $request
     * @return Response
     */
    public function batchMarkAsRead(Request $request): Response
    {
        $userId = $request->userId ?? 0;
        $ids = $request->param('ids', []);

        if (!$userId) {
            return json(['code' => 401, 'message' => '未登录']);
        }

        if (empty($ids)) {
            return json(['code' => 400, 'message' => 'IDs不能为空']);
        }

        $count = UserNotification::batchMarkAsRead($ids, $userId);

        return json([
            'code' => 0,
            'message' => "已标记 {$count} 条通知为已读"
        ]);
    }

    /**
     * 全部标记为已读
     * @param Request $request
     * @return Response
     */
    public function markAllAsRead(Request $request): Response
    {
        $userId = $request->userId ?? 0;

        if (!$userId) {
            return json(['code' => 401, 'message' => '未登录']);
        }

        $count = UserNotification::markAllAsRead($userId);

        return json([
            'code' => 0,
            'message' => "已标记 {$count} 条通知为已读"
        ]);
    }

    /**
     * 删除通知
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function delete(Request $request, int $id): Response
    {
        $userId = $request->userId ?? 0;

        if (!$userId) {
            return json(['code' => 401, 'message' => '未登录']);
        }

        $result = UserNotification::deleteNotification($id, $userId);

        if (!$result) {
            return json(['code' => 400, 'message' => '操作失败']);
        }

        return json([
            'code' => 0,
            'message' => '删除成功'
        ]);
    }

    /**
     * 清空已读通知
     * @param Request $request
     * @return Response
     */
    public function clearRead(Request $request): Response
    {
        $userId = $request->userId ?? 0;

        if (!$userId) {
            return json(['code' => 401, 'message' => '未登录']);
        }

        $count = UserNotification::clearReadNotifications($userId);

        return json([
            'code' => 0,
            'message' => "已清空 {$count} 条已读通知"
        ]);
    }

    /**
     * 获取通知设置
     * @param Request $request
     * @return Response
     */
    public function settings(Request $request): Response
    {
        $userId = $request->userId ?? 0;

        if (!$userId) {
            return json(['code' => 401, 'message' => '未登录']);
        }

        $settings = UserNotificationSetting::getAllSettings($userId);

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => $settings
        ]);
    }

    /**
     * 更新通知设置
     * @param Request $request
     * @return Response
     */
    public function updateSettings(Request $request): Response
    {
        $userId = $request->userId ?? 0;
        $settings = $request->param('settings', []);

        if (!$userId) {
            return json(['code' => 401, 'message' => '未登录']);
        }

        if (empty($settings)) {
            return json(['code' => 400, 'message' => '设置不能为空']);
        }

        UserNotificationSetting::updateUserSettings($userId, $settings);

        return json([
            'code' => 0,
            'message' => '设置已更新'
        ]);
    }
}
