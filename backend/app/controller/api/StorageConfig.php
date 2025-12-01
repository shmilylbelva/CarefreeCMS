<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\model\StorageConfig as StorageConfigModel;
use app\service\storage\StorageFactory;
use think\Request;

/**
 * 存储配置控制器
 */
class StorageConfig extends BaseController
{
    /**
     * 配置列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 20);
        $driver = $request->get('driver', '');
        $siteId = $request->get('site_id', '');

        $query = StorageConfigModel::order('sort_order', 'asc')
            ->order('created_at', 'desc');

        if (!empty($driver)) {
            $query->where('driver', $driver);
        }

        if ($siteId !== '') {
            if ($siteId == '0') {
                $query->whereNull('site_id');
            } else {
                $query->where('site_id', $siteId);
            }
        }

        $list = $query->page($page, $pageSize)->select();
        $total = StorageConfigModel::when(!empty($driver), function ($q) use ($driver) {
            $q->where('driver', $driver);
        })
        ->when($siteId !== '', function ($q) use ($siteId) {
            if ($siteId == '0') {
                $q->whereNull('site_id');
            } else {
                $q->where('site_id', $siteId);
            }
        })
        ->count();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取配置详情
     */
    public function read($id)
    {
        $config = StorageConfigModel::find($id);

        if (!$config) {
            return Response::notFound('配置不存在');
        }

        return Response::success($config);
    }

    /**
     * 创建配置
     */
    public function create(Request $request)
    {
        try {
            $data = [
                'site_id' => $request->post('site_id'),
                'name' => $request->post('name'),
                'driver' => $request->post('driver'),
                'config_data' => $request->post('config_data', []),
                'is_enabled' => $request->post('is_enabled', 1),
                'is_default' => $request->post('is_default', 0),
                'description' => $request->post('description'),
                'sort_order' => $request->post('sort_order', 0),
            ];

            if (empty($data['name']) || empty($data['driver'])) {
                return Response::error('配置名称和驱动不能为空');
            }

            // 验证驱动是否支持
            $supportedDrivers = StorageFactory::getSupportedDrivers();
            if (!in_array($data['driver'], $supportedDrivers)) {
                return Response::error('不支持的存储驱动');
            }

            $config = StorageConfigModel::create($data);

            // 如果设置为默认，更新其他配置
            if ($data['is_default']) {
                $config->setAsDefault();
            }

            return Response::success($config, '配置创建成功');

        } catch (\Exception $e) {
            return Response::error('创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新配置
     */
    public function update(Request $request, $id)
    {
        try {
            $config = StorageConfigModel::find($id);

            if (!$config) {
                return Response::notFound('配置不存在');
            }

            $data = [];

            if ($request->has('name')) {
                $data['name'] = $request->post('name');
            }

            if ($request->has('config_data')) {
                $data['config_data'] = $request->post('config_data');
            }

            if ($request->has('is_enabled')) {
                $data['is_enabled'] = $request->post('is_enabled');
            }

            if ($request->has('description')) {
                $data['description'] = $request->post('description');
            }

            if ($request->has('sort_order')) {
                $data['sort_order'] = $request->post('sort_order');
            }

            $config->save($data);

            // 如果设置为默认
            if ($request->has('is_default') && $request->post('is_default')) {
                $config->setAsDefault();
            }

            // 清除工厂缓存
            StorageFactory::clearCache($id);

            return Response::success($config, '配置更新成功');

        } catch (\Exception $e) {
            return Response::error('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除配置
     */
    public function delete($id)
    {
        try {
            $config = StorageConfigModel::find($id);

            if (!$config) {
                return Response::notFound('配置不存在');
            }

            if ($config->is_default) {
                return Response::error('默认配置不能删除');
            }

            $config->delete();

            // 清除工厂缓存
            StorageFactory::clearCache($id);

            return Response::success([], '配置删除成功');

        } catch (\Exception $e) {
            return Response::error('删除失败：' . $e->getMessage());
        }
    }

    /**
     * 测试配置连接
     */
    public function test(Request $request)
    {
        try {
            $driver = $request->post('driver');
            $configData = $request->post('config_data', []);

            if (empty($driver)) {
                return Response::error('请选择存储驱动');
            }

            $result = StorageFactory::testConnection($driver, $configData);

            if ($result['success']) {
                return Response::success($result, '连接测试成功');
            } else {
                return Response::error($result['message']);
            }

        } catch (\Exception $e) {
            return Response::error('测试失败：' . $e->getMessage());
        }
    }

    /**
     * 设置为默认配置
     */
    public function setDefault($id)
    {
        try {
            $config = StorageConfigModel::find($id);

            if (!$config) {
                return Response::notFound('配置不存在');
            }

            if (!$config->is_enabled) {
                return Response::error('配置未启用，无法设为默认');
            }

            $config->setAsDefault();

            return Response::success($config, '设置成功');

        } catch (\Exception $e) {
            return Response::error('设置失败：' . $e->getMessage());
        }
    }

    /**
     * 获取支持的存储驱动列表
     */
    public function drivers()
    {
        $drivers = [
            [
                'value' => 'local',
                'label' => '本地存储',
                'description' => '文件存储在服务器本地',
                'icon' => 'folder',
            ],
            [
                'value' => 'aliyun_oss',
                'label' => '阿里云OSS',
                'description' => '阿里云对象存储服务',
                'icon' => 'aliyun',
            ],
            [
                'value' => 'tencent_cos',
                'label' => '腾讯云COS',
                'description' => '腾讯云对象存储服务',
                'icon' => 'tencent',
            ],
            [
                'value' => 'qiniu',
                'label' => '七牛云',
                'description' => '七牛云存储服务',
                'icon' => 'qiniu',
            ],
        ];

        return Response::success($drivers);
    }

    /**
     * 获取驱动配置模板
     */
    public function driverTemplate($driver)
    {
        $template = StorageConfigModel::getConfigTemplate($driver);

        if (empty($template)) {
            return Response::notFound('不支持的驱动类型');
        }

        return Response::success($template);
    }

    /**
     * 批量排序
     */
    public function sort(Request $request)
    {
        try {
            $sorts = $request->post('sorts', []);

            foreach ($sorts as $item) {
                if (isset($item['id']) && isset($item['sort_order'])) {
                    StorageConfigModel::where('id', $item['id'])
                        ->update(['sort_order' => $item['sort_order']]);
                }
            }

            return Response::success([], '排序成功');

        } catch (\Exception $e) {
            return Response::error('排序失败：' . $e->getMessage());
        }
    }
}
