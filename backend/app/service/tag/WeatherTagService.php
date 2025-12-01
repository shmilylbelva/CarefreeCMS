<?php
namespace app\service\tag;

use think\facade\Cache;

/**
 * 天气标签服务类
 * 处理天气标签的数据查询
 */
class WeatherTagService
{
    /**
     * 获取天气信息
     *
     * @param array $params 查询参数
     *   - city: 城市名称
     *   - days: 天数（1-7天）
     *   - unit: 温度单位（c-摄氏度，f-华氏度）
     * @return array|null
     */
    public static function getWeather($params = [])
    {
        $city = $params['city'] ?? '北京';
        $days = $params['days'] ?? 3;
        $unit = $params['unit'] ?? 'c';

        // 缓存键
        $cacheKey = "weather_{$city}_{$days}_{$unit}";

        // 先从缓存获取（缓存2小时）
        $weather = Cache::get($cacheKey);
        if (!empty($weather)) {
            return $weather;
        }

        try {
            // 方法1: 使用高德天气API（需要申请key）
            // $weather = self::fetchFromAmap($city, $days);

            // 方法2: 使用心知天气API（需要申请key）
            // $weather = self::fetchFromSeniverse($city, $days);

            // 方法3: 使用和风天气API（需要申请key）
            // $weather = self::fetchFromQweather($city, $days);

            // 方法4: 模拟数据（开发测试用）
            $weather = self::getMockWeather($city, $days, $unit);

            if (!empty($weather)) {
                // 缓存2小时
                Cache::set($cacheKey, $weather, 7200);
            }

            return $weather;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 使用高德天气API获取天气
     * 文档：https://lbs.amap.com/api/webservice/guide/api/weatherinfo
     *
     * @param string $city 城市
     * @param int $days 天数
     * @return array|null
     */
    private static function fetchFromAmap($city, $days)
    {
        $apiKey = config('weather.amap_key', ''); // 从配置文件读取

        if (empty($apiKey)) {
            return null;
        }

        $type = $days > 1 ? 'all' : 'base';
        $url = "https://restapi.amap.com/v3/weather/weatherInfo";
        $url .= "?city={$city}&key={$apiKey}&extensions={$type}";

        try {
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            if ($data['status'] == '1' && !empty($data['forecasts'])) {
                return self::formatAmapData($data['forecasts'][0]);
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 格式化高德天气数据
     *
     * @param array $data 原始数据
     * @return array
     */
    private static function formatAmapData($data)
    {
        $result = [
            'city' => $data['city'] ?? '',
            'update_time' => $data['reporttime'] ?? date('Y-m-d H:i:s'),
            'forecasts' => []
        ];

        if (!empty($data['casts'])) {
            foreach ($data['casts'] as $cast) {
                $result['forecasts'][] = [
                    'date' => $cast['date'] ?? '',
                    'week' => $cast['week'] ?? '',
                    'weather_day' => $cast['dayweather'] ?? '',
                    'weather_night' => $cast['nightweather'] ?? '',
                    'temp_day' => $cast['daytemp'] ?? '',
                    'temp_night' => $cast['nighttemp'] ?? '',
                    'wind_direction' => $cast['daywind'] ?? '',
                    'wind_power' => $cast['daypower'] ?? ''
                ];
            }
        }

        return $result;
    }

    /**
     * 获取模拟天气数据（用于开发测试）
     *
     * @param string $city 城市
     * @param int $days 天数
     * @param string $unit 温度单位
     * @return array
     */
    private static function getMockWeather($city, $days, $unit = 'c')
    {
        $weatherTypes = ['晴', '多云', '阴', '小雨', '中雨', '大雨', '雷阵雨', '雪'];
        $windDirections = ['东风', '南风', '西风', '北风', '东南风', '东北风', '西南风', '西北风'];

        $weather = [
            'city' => $city,
            'update_time' => date('Y-m-d H:i:s'),
            'forecasts' => []
        ];

        for ($i = 0; $i < $days; $i++) {
            $date = date('Y-m-d', strtotime("+{$i} days"));
            $week = self::getWeekDay(strtotime($date));

            // 模拟温度
            $tempDay = rand(15, 30);
            $tempNight = $tempDay - rand(5, 10);

            // 如果是华氏度，转换温度
            if ($unit == 'f') {
                $tempDay = round($tempDay * 9 / 5 + 32);
                $tempNight = round($tempNight * 9 / 5 + 32);
            }

            $weather['forecasts'][] = [
                'date' => $date,
                'week' => $week,
                'weather_day' => $weatherTypes[array_rand($weatherTypes)],
                'weather_night' => $weatherTypes[array_rand($weatherTypes)],
                'temp_day' => $tempDay,
                'temp_night' => $tempNight,
                'temp_unit' => $unit == 'c' ? '℃' : '℉',
                'wind_direction' => $windDirections[array_rand($windDirections)],
                'wind_power' => rand(1, 5) . '级',
                'humidity' => rand(40, 80) . '%',
                'air_quality' => self::getRandomAirQuality()
            ];
        }

        return $weather;
    }

    /**
     * 获取星期
     *
     * @param int $timestamp 时间戳
     * @return string
     */
    private static function getWeekDay($timestamp)
    {
        $weekDays = ['日', '一', '二', '三', '四', '五', '六'];
        $weekDay = date('w', $timestamp);

        return '周' . $weekDays[$weekDay];
    }

    /**
     * 获取随机空气质量
     *
     * @return array
     */
    private static function getRandomAirQuality()
    {
        $qualities = [
            ['level' => '优', 'aqi' => rand(0, 50), 'color' => '#00e400'],
            ['level' => '良', 'aqi' => rand(51, 100), 'color' => '#ffff00'],
            ['level' => '轻度污染', 'aqi' => rand(101, 150), 'color' => '#ff7e00'],
            ['level' => '中度污染', 'aqi' => rand(151, 200), 'color' => '#ff0000'],
        ];

        return $qualities[array_rand($qualities)];
    }

    /**
     * 获取实时天气
     *
     * @param string $city 城市
     * @return array|null
     */
    public static function getCurrentWeather($city = '北京')
    {
        $cacheKey = "weather_current_{$city}";

        // 先从缓存获取（缓存30分钟）
        $weather = Cache::get($cacheKey);
        if (!empty($weather)) {
            return $weather;
        }

        // 模拟实时天气数据
        $weather = [
            'city' => $city,
            'update_time' => date('Y-m-d H:i:s'),
            'temperature' => rand(15, 30),
            'temp_unit' => '℃',
            'weather' => ['晴', '多云', '阴'][array_rand(['晴', '多云', '阴'])],
            'wind_direction' => ['东风', '南风', '西风', '北风'][array_rand(['东风', '南风', '西风', '北风'])],
            'wind_power' => rand(1, 5) . '级',
            'humidity' => rand(40, 80) . '%',
            'air_quality' => self::getRandomAirQuality()
        ];

        Cache::set($cacheKey, $weather, 1800); // 缓存30分钟

        return $weather;
    }

    /**
     * 获取天气图标
     *
     * @param string $weather 天气类型
     * @return string
     */
    public static function getWeatherIcon($weather)
    {
        $iconMap = [
            '晴' => 'sunny',
            '多云' => 'cloudy',
            '阴' => 'overcast',
            '小雨' => 'light-rain',
            '中雨' => 'moderate-rain',
            '大雨' => 'heavy-rain',
            '暴雨' => 'storm',
            '雷阵雨' => 'thunderstorm',
            '雪' => 'snow',
            '雾' => 'fog',
            '霾' => 'haze'
        ];

        $iconType = $iconMap[$weather] ?? 'default';

        return "/static/weather-icons/{$iconType}.svg";
    }

    /**
     * 清除天气缓存
     *
     * @param string $city 城市（为空则清除所有）
     * @return bool
     */
    public static function clearCache($city = '')
    {
        if (empty($city)) {
            // 清除所有天气缓存
            return Cache::clear();
        } else {
            // 清除指定城市的缓存
            Cache::delete("weather_current_{$city}");
            Cache::delete("weather_{$city}_3_c");
            Cache::delete("weather_{$city}_3_f");
            Cache::delete("weather_{$city}_7_c");
            Cache::delete("weather_{$city}_7_f");
            return true;
        }
    }

    /**
     * 获取支持的城市列表
     *
     * @return array
     */
    public static function getSupportedCities()
    {
        return [
            ['name' => '北京', 'code' => '110000'],
            ['name' => '上海', 'code' => '310000'],
            ['name' => '广州', 'code' => '440100'],
            ['name' => '深圳', 'code' => '440300'],
            ['name' => '杭州', 'code' => '330100'],
            ['name' => '成都', 'code' => '510100'],
            ['name' => '重庆', 'code' => '500000'],
            ['name' => '武汉', 'code' => '420100'],
            ['name' => '西安', 'code' => '610100'],
            ['name' => '南京', 'code' => '320100'],
        ];
    }
}
