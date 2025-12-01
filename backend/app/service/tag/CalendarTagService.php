<?php
namespace app\service\tag;

use think\facade\Db;

/**
 * 日历标签服务类
 * 处理日历标签的数据查询
 */
class CalendarTagService
{
    /**
     * 获取日历数据
     *
     * @param array $params 查询参数
     *   - year: 年份
     *   - month: 月份
     *   - events: 是否包含事件（1-包含，0-不包含）
     * @return array
     */
    public static function getCalendar($params = [])
    {
        $year = $params['year'] ?? date('Y');
        $month = $params['month'] ?? date('m');
        $includeEvents = $params['events'] ?? 1;

        try {
            // 获取当月第一天和最后一天
            $firstDay = date('Y-m-01', strtotime("{$year}-{$month}-01"));
            $lastDay = date('Y-m-t', strtotime("{$year}-{$month}-01"));

            // 获取当月第一天是星期几（0-周日，1-周一，...，6-周六）
            $firstDayOfWeek = date('w', strtotime($firstDay));

            // 获取当月天数
            $daysInMonth = date('t', strtotime($firstDay));

            // 构建日历数据
            $calendar = [
                'year' => (int)$year,
                'month' => (int)$month,
                'month_name' => self::getMonthName($month),
                'first_day' => $firstDay,
                'last_day' => $lastDay,
                'first_day_of_week' => $firstDayOfWeek,
                'days_in_month' => $daysInMonth,
                'weeks' => []
            ];

            // 获取事件数据
            $events = [];
            if ($includeEvents == 1) {
                $eventList = Db::table('events')
                    ->where('status', 1)
                    ->where(function($query) use ($firstDay, $lastDay) {
                        $query->whereBetweenTime('start_time', $firstDay, $lastDay)
                              ->whereOr(function($query) use ($firstDay, $lastDay) {
                                  $query->whereBetweenTime('end_time', $firstDay, $lastDay);
                              });
                    })
                    ->select()
                    ->toArray();

                // 按日期分组事件
                foreach ($eventList as $event) {
                    $eventDate = date('Y-m-d', is_numeric($event['start_time']) ? $event['start_time'] : strtotime($event['start_time']));
                    if (!isset($events[$eventDate])) {
                        $events[$eventDate] = [];
                    }
                    $events[$eventDate][] = $event;
                }
            }

            // 构建周数据
            $dayCount = 1;
            $weekCount = 0;

            // 第一周（可能包含上月的日期）
            $calendar['weeks'][$weekCount] = [];

            // 填充上月的日期
            if ($firstDayOfWeek > 0) {
                $prevMonthLastDay = date('t', strtotime('-1 month', strtotime($firstDay)));
                $prevMonthDays = $prevMonthLastDay - $firstDayOfWeek + 1;

                for ($i = 0; $i < $firstDayOfWeek; $i++) {
                    $calendar['weeks'][$weekCount][] = [
                        'day' => $prevMonthDays + $i,
                        'date' => date('Y-m-d', strtotime('-' . ($firstDayOfWeek - $i) . ' days', strtotime($firstDay))),
                        'is_current_month' => false,
                        'is_today' => false,
                        'events' => []
                    ];
                }
            }

            // 填充当月的日期
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = date('Y-m-d', strtotime("{$year}-{$month}-{$day}"));
                $isToday = $date == date('Y-m-d');

                $dayData = [
                    'day' => $day,
                    'date' => $date,
                    'is_current_month' => true,
                    'is_today' => $isToday,
                    'events' => $events[$date] ?? [],
                    'event_count' => count($events[$date] ?? [])
                ];

                $calendar['weeks'][$weekCount][] = $dayData;

                // 如果是周六，开始新的一周
                if (($firstDayOfWeek + $day) % 7 == 0 && $day < $daysInMonth) {
                    $weekCount++;
                    $calendar['weeks'][$weekCount] = [];
                }
            }

            // 填充下月的日期（如果最后一周不满7天）
            $lastWeekDayCount = count($calendar['weeks'][$weekCount]);
            if ($lastWeekDayCount < 7) {
                $nextMonthDay = 1;
                for ($i = $lastWeekDayCount; $i < 7; $i++) {
                    $calendar['weeks'][$weekCount][] = [
                        'day' => $nextMonthDay,
                        'date' => date('Y-m-d', strtotime('+' . $nextMonthDay . ' days', strtotime($lastDay))),
                        'is_current_month' => false,
                        'is_today' => false,
                        'events' => []
                    ];
                    $nextMonthDay++;
                }
            }

            // 添加导航信息
            $calendar['prev_month'] = date('Y-m', strtotime('-1 month', strtotime($firstDay)));
            $calendar['next_month'] = date('Y-m', strtotime('+1 month', strtotime($firstDay)));

            return $calendar;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 获取月份名称
     *
     * @param int $month 月份
     * @return string
     */
    private static function getMonthName($month)
    {
        $months = [
            1 => '一月', 2 => '二月', 3 => '三月', 4 => '四月',
            5 => '五月', 6 => '六月', 7 => '七月', 8 => '八月',
            9 => '九月', 10 => '十月', 11 => '十一月', 12 => '十二月'
        ];

        return $months[(int)$month] ?? '';
    }

    /**
     * 获取事件列表
     *
     * @param string $startDate 开始日期
     * @param string $endDate 结束日期
     * @param int $catid 分类ID
     * @return array
     */
    public static function getEvents($startDate = '', $endDate = '', $catid = 0)
    {
        try {
            $query = Db::table('events')
                ->where('status', 1);

            // 日期范围筛选
            if (!empty($startDate)) {
                $query->where('start_time', '>=', $startDate);
            }

            if (!empty($endDate)) {
                $query->where('end_time', '<=', $endDate);
            }

            // 分类筛选
            if ($catid > 0) {
                $query->where('category_id', $catid);
            }

            $events = $query->order('start_time', 'asc')
                ->select()
                ->toArray();

            // 处理事件数据
            foreach ($events as &$event) {
                // 格式化时间
                $event['start_time_formatted'] = date('Y-m-d H:i', is_numeric($event['start_time']) ? $event['start_time'] : strtotime($event['start_time']));
                $event['end_time_formatted'] = date('Y-m-d H:i', is_numeric($event['end_time']) ? $event['end_time'] : strtotime($event['end_time']));

                // 计算事件时长
                $startTimestamp = is_numeric($event['start_time']) ? $event['start_time'] : strtotime($event['start_time']);
                $endTimestamp = is_numeric($event['end_time']) ? $event['end_time'] : strtotime($event['end_time']);
                $duration = $endTimestamp - $startTimestamp;

                $event['duration'] = $duration;
                $event['duration_formatted'] = self::formatDuration($duration);

                // 判断事件状态
                $now = time();
                if ($now < $startTimestamp) {
                    $event['event_status'] = 'upcoming';
                    $event['event_status_text'] = '即将开始';
                } elseif ($now > $endTimestamp) {
                    $event['event_status'] = 'past';
                    $event['event_status_text'] = '已结束';
                } else {
                    $event['event_status'] = 'ongoing';
                    $event['event_status_text'] = '进行中';
                }

                // 处理地点
                if (!empty($event['location'])) {
                    $event['has_location'] = true;
                }
            }

            return $events;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 格式化时长
     *
     * @param int $seconds 秒数
     * @return string
     */
    private static function formatDuration($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];

        if ($days > 0) {
            $parts[] = $days . '天';
        }

        if ($hours > 0) {
            $parts[] = $hours . '小时';
        }

        if ($minutes > 0 && $days == 0) {
            $parts[] = $minutes . '分钟';
        }

        return !empty($parts) ? implode('', $parts) : '0分钟';
    }

    /**
     * 获取今日事件
     *
     * @return array
     */
    public static function getTodayEvents()
    {
        $today = date('Y-m-d');
        $todayStart = $today . ' 00:00:00';
        $todayEnd = $today . ' 23:59:59';

        return self::getEvents($todayStart, $todayEnd);
    }

    /**
     * 获取即将到来的事件
     *
     * @param int $days 未来几天
     * @param int $limit 数量限制
     * @return array
     */
    public static function getUpcomingEvents($days = 7, $limit = 10)
    {
        try {
            $now = date('Y-m-d H:i:s');
            $future = date('Y-m-d 23:59:59', strtotime("+{$days} days"));

            return Db::table('events')
                ->where('status', 1)
                ->where('start_time', '>=', $now)
                ->where('start_time', '<=', $future)
                ->order('start_time', 'asc')
                ->limit($limit)
                ->select()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
