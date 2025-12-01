<?php
declare(strict_types=1);

namespace app\traits;

use think\Request;

/**
 * 查询过滤Trait
 * 用于消除列表查询中的代码重复
 */
trait QueryFilterTrait
{
    /**
     * 应用通用的查询过滤条件
     *
     * @param mixed $query 查询构造器
     * @param array $filters 过滤条件配置
     * @param Request $request 请求对象
     * @return mixed 返回查询构造器
     *
     * @example
     * $filters = [
     *     'title' => ['operator' => 'like'],
     *     'status' => ['operator' => '='],
     *     'user_id' => ['operator' => '=', 'field' => 'user_id'],
     *     'start_time' => ['operator' => '>=', 'field' => 'create_time'],
     *     'end_time' => ['operator' => '<=', 'field' => 'create_time', 'suffix' => ' 23:59:59'],
     * ];
     * $query = $this->applyFilters($query, $filters, $request);
     */
    protected function applyFilters($query, array $filters, Request $request)
    {
        foreach ($filters as $param => $config) {
            $value = $request->get($param, '');

            // 跳过空值（但保留0和'0'）
            if ($value === '' || $value === null) {
                continue;
            }

            $operator = $config['operator'] ?? '=';
            $field = $config['field'] ?? $param;
            $callback = $config['callback'] ?? null;

            // 如果提供了自定义回调，使用回调处理
            if ($callback && is_callable($callback)) {
                $query = $callback($query, $value, $param);
                continue;
            }

            // 处理like操作符
            if ($operator === 'like') {
                $value = '%' . $value . '%';
            }

            // 添加后缀（如时间的23:59:59）
            if (isset($config['suffix'])) {
                $value .= $config['suffix'];
            }

            // 应用过滤条件
            $query->where($field, $operator, $value);
        }

        return $query;
    }

    /**
     * 应用排序条件
     *
     * @param mixed $query 查询构造器
     * @param array $defaultOrder 默认排序 ['field' => 'desc']
     * @param Request $request 请求对象
     * @return mixed 返回查询构造器
     */
    protected function applyOrder($query, array $defaultOrder = [], Request $request = null)
    {
        if ($request) {
            $sortBy = $request->get('sort', '');
            $sortOrder = $request->get('order', 'desc');

            if ($sortBy) {
                return $query->order([$sortBy => $sortOrder]);
            }
        }

        if (!empty($defaultOrder)) {
            return $query->order($defaultOrder);
        }

        return $query;
    }

    /**
     * 执行分页查询并返回标准格式
     *
     * @param mixed $query 查询构造器
     * @param int|string $page 页码
     * @param int|string $pageSize 每页数量
     * @return array 包含list和total的数组
     */
    protected function paginateQuery($query, $page = 1, $pageSize = 20): array
    {
        // 转换为int类型
        $page = (int)$page;
        $pageSize = (int)$pageSize;

        $result = $query->paginate([
            'list_rows' => $pageSize,
            'page' => $page,
        ]);

        return [
            'list' => $result->items(),
            'total' => $result->total(),
        ];
    }

    /**
     * 快速构建列表查询（一站式方法）
     *
     * @param mixed $query 初始查询构造器
     * @param array $filters 过滤条件配置
     * @param array $order 排序配置
     * @param Request $request 请求对象
     * @return array 包含list和total的数组
     */
    protected function buildListQuery($query, array $filters, array $order, Request $request): array
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);

        // 应用过滤条件
        $query = $this->applyFilters($query, $filters, $request);

        // 应用排序
        $query = $this->applyOrder($query, $order, $request);

        // 执行分页查询
        return $this->paginateQuery($query, $page, $pageSize);
    }
}
