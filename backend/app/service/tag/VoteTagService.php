<?php
namespace app\service\tag;

use think\facade\Db;

/**
 * 投票标签服务类
 * 处理投票标签的数据查询
 */
class VoteTagService
{
    /**
     * 获取投票信息
     *
     * @param array $params 查询参数
     *   - voteid: 投票ID
     *   - showresult: 是否显示结果（1-显示，0-不显示）
     * @return array|null
     */
    public static function getInfo($params = [])
    {
        $voteid = $params['voteid'] ?? 0;
        $showresult = $params['showresult'] ?? 0;

        if (empty($voteid)) {
            return null;
        }

        try {
            // 获取投票基本信息
            $vote = Db::table('votes')
                ->where('id', $voteid)
                ->where('status', 1)
                ->find();

            if (!$vote) {
                return null;
            }

            // 获取投票选项
            $options = Db::table('vote_options')
                ->where('vote_id', $voteid)
                ->order('sort', 'asc')
                ->select()
                ->toArray();

            // 计算总投票数
            $totalVotes = array_sum(array_column($options, 'vote_count'));

            // 处理选项数据
            foreach ($options as &$option) {
                // 计算百分比
                if ($totalVotes > 0) {
                    $option['percent'] = round(($option['vote_count'] / $totalVotes) * 100, 2);
                } else {
                    $option['percent'] = 0;
                }

                // 格式化投票数
                $option['vote_count_formatted'] = self::formatVoteCount($option['vote_count'] ?? 0);
            }

            $vote['options'] = $options;
            $vote['total_votes'] = $totalVotes;
            $vote['total_votes_formatted'] = self::formatVoteCount($totalVotes);

            // 检查投票状态
            $now = time();
            $startTime = is_numeric($vote['start_time']) ? $vote['start_time'] : strtotime($vote['start_time']);
            $endTime = is_numeric($vote['end_time']) ? $vote['end_time'] : strtotime($vote['end_time']);

            if ($now < $startTime) {
                $vote['vote_status'] = 'not_started';
                $vote['vote_status_text'] = '未开始';
            } elseif ($now > $endTime) {
                $vote['vote_status'] = 'ended';
                $vote['vote_status_text'] = '已结束';
            } else {
                $vote['vote_status'] = 'ongoing';
                $vote['vote_status_text'] = '进行中';
            }

            // 格式化时间
            $vote['start_time_formatted'] = date('Y-m-d H:i', $startTime);
            $vote['end_time_formatted'] = date('Y-m-d H:i', $endTime);

            // 是否显示结果
            $vote['show_result'] = $showresult == 1 || $vote['vote_status'] == 'ended';

            return $vote;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 格式化投票数
     *
     * @param int $count 投票数
     * @return string
     */
    private static function formatVoteCount($count)
    {
        if ($count >= 10000) {
            return round($count / 10000, 1) . '万';
        } elseif ($count >= 1000) {
            return round($count / 1000, 1) . 'k';
        }
        return (string)$count;
    }

    /**
     * 获取投票列表
     *
     * @param int $limit 数量限制
     * @return array
     */
    public static function getList($limit = 10)
    {
        try {
            $votes = Db::table('votes')
                ->where('status', 1)
                ->order('create_time', 'desc')
                ->limit($limit)
                ->select()
                ->toArray();

            foreach ($votes as &$vote) {
                // 获取投票总数
                $totalVotes = Db::table('vote_options')
                    ->where('vote_id', $vote['id'])
                    ->sum('vote_count');

                $vote['total_votes'] = $totalVotes;
                $vote['total_votes_formatted'] = self::formatVoteCount($totalVotes);

                // 检查投票状态
                $now = time();
                $startTime = is_numeric($vote['start_time']) ? $vote['start_time'] : strtotime($vote['start_time']);
                $endTime = is_numeric($vote['end_time']) ? $vote['end_time'] : strtotime($vote['end_time']);

                if ($now < $startTime) {
                    $vote['vote_status'] = 'not_started';
                    $vote['vote_status_text'] = '未开始';
                } elseif ($now > $endTime) {
                    $vote['vote_status'] = 'ended';
                    $vote['vote_status_text'] = '已结束';
                } else {
                    $vote['vote_status'] = 'ongoing';
                    $vote['vote_status_text'] = '进行中';
                }
            }

            return $votes;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 提交投票
     *
     * @param int $voteId 投票ID
     * @param array $optionIds 选项ID数组
     * @param string $userId 用户ID（可选）
     * @param string $ip IP地址
     * @return array
     */
    public static function submitVote($voteId, $optionIds, $userId = '', $ip = '')
    {
        try {
            // 检查投票是否存在且有效
            $vote = Db::table('votes')
                ->where('id', $voteId)
                ->where('status', 1)
                ->find();

            if (!$vote) {
                return ['success' => false, 'message' => '投票不存在'];
            }

            // 检查投票时间
            $now = time();
            $startTime = is_numeric($vote['start_time']) ? $vote['start_time'] : strtotime($vote['start_time']);
            $endTime = is_numeric($vote['end_time']) ? $vote['end_time'] : strtotime($vote['end_time']);

            if ($now < $startTime) {
                return ['success' => false, 'message' => '投票尚未开始'];
            }

            if ($now > $endTime) {
                return ['success' => false, 'message' => '投票已结束'];
            }

            // 检查是否已投票（根据IP或用户ID）
            $checkCondition = ['vote_id' => $voteId];
            if (!empty($userId)) {
                $checkCondition['user_id'] = $userId;
            } else {
                $checkCondition['ip'] = $ip;
            }

            $hasVoted = Db::table('vote_records')
                ->where($checkCondition)
                ->find();

            if ($hasVoted) {
                return ['success' => false, 'message' => '您已经投过票了'];
            }

            // 检查选项数量是否符合要求
            if ($vote['is_multiple'] == 1) {
                if (count($optionIds) > $vote['max_choices']) {
                    return ['success' => false, 'message' => '最多只能选择' . $vote['max_choices'] . '个选项'];
                }
            } else {
                if (count($optionIds) != 1) {
                    return ['success' => false, 'message' => '只能选择一个选项'];
                }
            }

            // 开启事务
            Db::startTrans();

            try {
                // 增加选项投票数
                foreach ($optionIds as $optionId) {
                    Db::table('vote_options')
                        ->where('id', $optionId)
                        ->where('vote_id', $voteId)
                        ->inc('vote_count')
                        ->update();
                }

                // 记录投票
                Db::table('vote_records')->insert([
                    'vote_id' => $voteId,
                    'user_id' => $userId ?: null,
                    'ip' => $ip,
                    'option_ids' => implode(',', $optionIds),
                    'create_time' => date('Y-m-d H:i:s')
                ]);

                Db::commit();

                return ['success' => true, 'message' => '投票成功'];
            } catch (\Exception $e) {
                Db::rollback();
                return ['success' => false, 'message' => '投票失败：' . $e->getMessage()];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '投票失败：' . $e->getMessage()];
        }
    }
}
