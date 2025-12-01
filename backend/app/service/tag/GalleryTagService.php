<?php
namespace app\service\tag;

use think\facade\Db;

/**
 * 相册图库标签服务类
 * 处理相册图库标签的数据查询
 */
class GalleryTagService
{
    /**
     * 获取相册图片列表
     *
     * @param array $params 查询参数
     *   - albumid: 相册ID
     *   - limit: 数量限制
     *   - orderby: 排序方式
     *   - columns: 每行列数
     * @return array
     */
    public static function getList($params = [])
    {
        $albumid = $params['albumid'] ?? 0;
        $limit = $params['limit'] ?? 12;
        $orderby = $params['orderby'] ?? 'sort asc';
        $columns = $params['columns'] ?? 4;

        try {
            // 构建查询
            $query = Db::table('gallery_photos')
                ->alias('p')
                ->leftJoin('gallery_albums a', 'p.album_id = a.id')
                ->field('p.*, a.name as album_name')
                ->where('p.status', 1);

            // 按相册筛选
            if ($albumid > 0) {
                $query->where('p.album_id', $albumid);
            }

            // 排序
            $orderArr = explode(' ', $orderby);
            $orderField = $orderArr[0] ?? 'sort';
            $orderType = $orderArr[1] ?? 'asc';
            $query->order($orderField, $orderType);

            // 限制数量
            if ($limit > 0) {
                $query->limit($limit);
            }

            $photos = $query->select()->toArray();

            // 添加列计算和URL处理
            foreach ($photos as $key => &$photo) {
                $photo['col'] = ($key % $columns) + 1;
                $photo['row'] = floor($key / $columns) + 1;

                // 确保图片路径完整
                if (!empty($photo['image']) && !str_starts_with($photo['image'], 'http')) {
                    $photo['image'] = request()->domain() . $photo['image'];
                }

                // 处理缩略图
                if (!empty($photo['thumb']) && !str_starts_with($photo['thumb'], 'http')) {
                    $photo['thumb'] = request()->domain() . $photo['thumb'];
                } else if (empty($photo['thumb'])) {
                    $photo['thumb'] = $photo['image'];
                }
            }

            return $photos;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取相册信息
     *
     * @param int $albumid 相册ID
     * @return array|null
     */
    public static function getAlbumInfo($albumid)
    {
        try {
            return Db::table('gallery_albums')
                ->where('id', $albumid)
                ->where('status', 1)
                ->find();
        } catch (\Exception $e) {
            return null;
        }
    }
}
