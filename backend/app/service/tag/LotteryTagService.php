<?php
namespace app\service\tag;

use think\facade\Db;

/**
 * 抽奖标签服务类
 * 处理抽奖标签的数据查询
 */
class LotteryTagService
{
    /**
     * 获取抽奖信息
     *
     * @param array $params 查询参数
     *   - lotteryid: 抽奖ID
     * @return array|null
     */
    public static function getInfo($params = [])
    {
        $lotteryid = $params['lotteryid'] ?? 0;

        if (empty($lotteryid)) {
            return null;
        }

        try {
            // 获取抽奖基本信息
            $lottery = Db::table('lotteries')
                ->where('id', $lotteryid)
                ->where('status', 1)
                ->find();

            if (!$lottery) {
                return null;
            }

            // 获取奖品列表
            $prizes = Db::table('lottery_prizes')
                ->where('lottery_id', $lotteryid)
                ->where('status', 1)
                ->order('sort', 'asc')
                ->select()
                ->toArray();

            // 处理奖品数据
            $totalProbability = 0;
            foreach ($prizes as &$prize) {
                // 计算剩余数量
                $prize['remaining'] = max(0, $prize['total_count'] - $prize['won_count']);

                // 是否已抽完
                $prize['is_out_of_stock'] = $prize['remaining'] <= 0;

                // 格式化概率
                $prize['probability_formatted'] = $prize['probability'] . '%';

                // 确保图片路径完整
                if (!empty($prize['image']) && !str_starts_with($prize['image'], 'http')) {
                    $prize['image'] = request()->domain() . $prize['image'];
                }

                $totalProbability += $prize['probability'];
            }

            $lottery['prizes'] = $prizes;
            $lottery['total_probability'] = $totalProbability;

            // 获取统计信息
            $lottery['total_participants'] = Db::table('lottery_records')
                ->where('lottery_id', $lotteryid)
                ->count('DISTINCT user_id');

            $lottery['total_draws'] = Db::table('lottery_records')
                ->where('lottery_id', $lotteryid)
                ->count();

            // 检查活动状态
            $now = time();
            $startTime = is_numeric($lottery['start_time']) ? $lottery['start_time'] : strtotime($lottery['start_time']);
            $endTime = is_numeric($lottery['end_time']) ? $lottery['end_time'] : strtotime($lottery['end_time']);

            if ($now < $startTime) {
                $lottery['activity_status'] = 'not_started';
                $lottery['activity_status_text'] = '未开始';
            } elseif ($now > $endTime) {
                $lottery['activity_status'] = 'ended';
                $lottery['activity_status_text'] = '已结束';
            } else {
                $lottery['activity_status'] = 'ongoing';
                $lottery['activity_status_text'] = '进行中';
            }

            // 格式化时间
            $lottery['start_time_formatted'] = date('Y-m-d H:i', $startTime);
            $lottery['end_time_formatted'] = date('Y-m-d H:i', $endTime);

            // 剩余抽奖次数（如果设置了总次数限制）
            if (!empty($lottery['max_draws'])) {
                $lottery['remaining_draws'] = max(0, $lottery['max_draws'] - $lottery['total_draws']);
            }

            return $lottery;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取抽奖列表
     *
     * @param int $limit 数量限制
     * @return array
     */
    public static function getList($limit = 10)
    {
        try {
            $lotteries = Db::table('lotteries')
                ->where('status', 1)
                ->order('create_time', 'desc')
                ->limit($limit)
                ->select()
                ->toArray();

            foreach ($lotteries as &$lottery) {
                // 获取参与人数
                $lottery['total_participants'] = Db::table('lottery_records')
                    ->where('lottery_id', $lottery['id'])
                    ->count('DISTINCT user_id');

                // 检查活动状态
                $now = time();
                $startTime = is_numeric($lottery['start_time']) ? $lottery['start_time'] : strtotime($lottery['start_time']);
                $endTime = is_numeric($lottery['end_time']) ? $lottery['end_time'] : strtotime($lottery['end_time']);

                if ($now < $startTime) {
                    $lottery['activity_status'] = 'not_started';
                    $lottery['activity_status_text'] = '未开始';
                } elseif ($now > $endTime) {
                    $lottery['activity_status'] = 'ended';
                    $lottery['activity_status_text'] = '已结束';
                } else {
                    $lottery['activity_status'] = 'ongoing';
                    $lottery['activity_status_text'] = '进行中';
                }
            }

            return $lotteries;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 执行抽奖
     *
     * @param int $lotteryId 抽奖ID
     * @param string $userId 用户ID
     * @return array
     */
    public static function draw($lotteryId, $userId)
    {
        try {
            // 检查抽奖活动是否存在且有效
            $lottery = Db::table('lotteries')
                ->where('id', $lotteryId)
                ->where('status', 1)
                ->find();

            if (!$lottery) {
                return ['success' => false, 'message' => '抽奖活动不存在'];
            }

            // 检查活动时间
            $now = time();
            $startTime = is_numeric($lottery['start_time']) ? $lottery['start_time'] : strtotime($lottery['start_time']);
            $endTime = is_numeric($lottery['end_time']) ? $lottery['end_time'] : strtotime($lottery['end_time']);

            if ($now < $startTime) {
                return ['success' => false, 'message' => '活动尚未开始'];
            }

            if ($now > $endTime) {
                return ['success' => false, 'message' => '活动已结束'];
            }

            // 检查用户今日抽奖次数
            if (!empty($lottery['daily_limit'])) {
                $todayCount = Db::table('lottery_records')
                    ->where('lottery_id', $lotteryId)
                    ->where('user_id', $userId)
                    ->whereTime('create_time', 'today')
                    ->count();

                if ($todayCount >= $lottery['daily_limit']) {
                    return ['success' => false, 'message' => '今日抽奖次数已用完'];
                }
            }

            // 检查用户总抽奖次数
            if (!empty($lottery['user_limit'])) {
                $userTotalCount = Db::table('lottery_records')
                    ->where('lottery_id', $lotteryId)
                    ->where('user_id', $userId)
                    ->count();

                if ($userTotalCount >= $lottery['user_limit']) {
                    return ['success' => false, 'message' => '抽奖次数已用完'];
                }
            }

            // 获取可用奖品
            $prizes = Db::table('lottery_prizes')
                ->where('lottery_id', $lotteryId)
                ->where('status', 1)
                ->whereRaw('won_count < total_count')
                ->order('sort', 'asc')
                ->select()
                ->toArray();

            if (empty($prizes)) {
                return ['success' => false, 'message' => '奖品已抽完'];
            }

            // 执行抽奖算法
            $wonPrize = self::drawPrize($prizes);

            if (!$wonPrize) {
                // 未中奖
                Db::table('lottery_records')->insert([
                    'lottery_id' => $lotteryId,
                    'user_id' => $userId,
                    'prize_id' => 0,
                    'prize_name' => '未中奖',
                    'is_won' => 0,
                    'create_time' => date('Y-m-d H:i:s')
                ]);

                return [
                    'success' => true,
                    'is_won' => false,
                    'message' => '很遗憾，未中奖'
                ];
            }

            // 中奖处理
            Db::startTrans();

            try {
                // 增加奖品中奖次数
                Db::table('lottery_prizes')
                    ->where('id', $wonPrize['id'])
                    ->inc('won_count')
                    ->update();

                // 记录中奖
                Db::table('lottery_records')->insert([
                    'lottery_id' => $lotteryId,
                    'user_id' => $userId,
                    'prize_id' => $wonPrize['id'],
                    'prize_name' => $wonPrize['name'],
                    'is_won' => 1,
                    'create_time' => date('Y-m-d H:i:s')
                ]);

                Db::commit();

                return [
                    'success' => true,
                    'is_won' => true,
                    'message' => '恭喜中奖！',
                    'prize' => [
                        'id' => $wonPrize['id'],
                        'name' => $wonPrize['name'],
                        'image' => $wonPrize['image'],
                        'description' => $wonPrize['description']
                    ]
                ];
            } catch (\Exception $e) {
                Db::rollback();
                return ['success' => false, 'message' => '抽奖失败：' . $e->getMessage()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '抽奖失败：' . $e->getMessage()];
        }
    }

    /**
     * 抽奖算法（概率抽奖）
     *
     * @param array $prizes 奖品列表
     * @return array|null
     */
    private static function drawPrize($prizes)
    {
        // 计算总概率
        $totalProbability = array_sum(array_column($prizes, 'probability'));

        // 生成随机数
        $random = mt_rand(1, $totalProbability * 100) / 100;

        // 计算中奖
        $currentProbability = 0;
        foreach ($prizes as $prize) {
            $currentProbability += $prize['probability'];
            if ($random <= $currentProbability) {
                return $prize;
            }
        }

        return null;
    }

    /**
     * 获取中奖记录
     *
     * @param int $lotteryId 抽奖ID
     * @param int $limit 数量限制
     * @return array
     */
    public static function getWinRecords($lotteryId, $limit = 20)
    {
        try {
            return Db::table('lottery_records')
                ->alias('r')
                ->leftJoin('admin_users u', 'r.user_id = u.id')
                ->field('r.*, u.username, u.nickname')
                ->where('r.lottery_id', $lotteryId)
                ->where('r.is_won', 1)
                ->order('r.create_time', 'desc')
                ->limit($limit)
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
