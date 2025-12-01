<?php
namespace app\service\tag;

use think\facade\Db;
use think\facade\Cache;
use think\facade\Cookie;
use think\facade\Session;

/**
 * 多语言标签服务类
 * 处理多语言/国际化标签的数据查询
 */
class MultilangTagService
{
    /**
     * 默认语言
     */
    const DEFAULT_LANG = 'zh-cn';

    /**
     * 支持的语言列表
     */
    private static $supportedLangs = [
        'zh-cn' => '简体中文',
        'zh-tw' => '繁體中文',
        'en' => 'English',
        'ja' => '日本語',
        'ko' => '한국어',
        'es' => 'Español',
        'fr' => 'Français',
        'de' => 'Deutsch',
        'ru' => 'Русский',
        'ar' => 'العربية'
    ];

    /**
     * 获取翻译文本
     *
     * @param array $params 查询参数
     *   - key: 翻译键
     *   - lang: 语言代码
     *   - default: 默认值
     * @return string
     */
    public static function translate($params = [])
    {
        $key = $params['key'] ?? '';
        $lang = $params['lang'] ?? self::getCurrentLang();
        $default = $params['default'] ?? $key;

        if (empty($key)) {
            return $default;
        }

        // 从缓存获取翻译
        $cacheKey = "translation_{$lang}_{$key}";
        $translation = Cache::get($cacheKey);

        if ($translation !== false) {
            return $translation ?: $default;
        }

        try {
            // 从数据库获取翻译
            $translation = Db::table('translations')
                ->where('lang', $lang)
                ->where('key', $key)
                ->value('value');

            // 如果没有找到，尝试使用默认语言
            if (empty($translation) && $lang !== self::DEFAULT_LANG) {
                $translation = Db::table('translations')
                    ->where('lang', self::DEFAULT_LANG)
                    ->where('key', $key)
                    ->value('value');
            }

            // 如果还是没有，使用默认值
            if (empty($translation)) {
                $translation = $default;
            }

            // 缓存翻译（1小时）
            Cache::set($cacheKey, $translation, 3600);

            return $translation;
        } catch (\Exception $e) {
            return $default;
        }
    }

    /**
     * 获取当前语言
     *
     * @return string
     */
    public static function getCurrentLang()
    {
        // 优先从session获取
        $lang = Session::get('lang');

        if (!empty($lang) && isset(self::$supportedLangs[$lang])) {
            return $lang;
        }

        // 从cookie获取
        $lang = Cookie::get('lang');

        if (!empty($lang) && isset(self::$supportedLangs[$lang])) {
            return $lang;
        }

        // 从浏览器Accept-Language获取
        $acceptLang = request()->header('accept-language');
        if (!empty($acceptLang)) {
            $lang = self::parseBrowserLang($acceptLang);
            if (!empty($lang)) {
                return $lang;
            }
        }

        return self::DEFAULT_LANG;
    }

    /**
     * 解析浏览器语言
     *
     * @param string $acceptLang Accept-Language头
     * @return string|null
     */
    private static function parseBrowserLang($acceptLang)
    {
        // Accept-Language: zh-CN,zh;q=0.9,en;q=0.8
        $langs = explode(',', $acceptLang);

        foreach ($langs as $lang) {
            $lang = trim(explode(';', $lang)[0]);
            $lang = strtolower($lang);

            // 精确匹配
            if (isset(self::$supportedLangs[$lang])) {
                return $lang;
            }

            // 模糊匹配（例如：zh匹配zh-cn）
            foreach (self::$supportedLangs as $supportedLang => $name) {
                if (strpos($supportedLang, $lang) === 0 || strpos($lang, $supportedLang) === 0) {
                    return $supportedLang;
                }
            }
        }

        return null;
    }

    /**
     * 设置当前语言
     *
     * @param string $lang 语言代码
     * @return bool
     */
    public static function setLang($lang)
    {
        if (!isset(self::$supportedLangs[$lang])) {
            return false;
        }

        // 保存到session和cookie
        Session::set('lang', $lang);
        Cookie::set('lang', $lang, 86400 * 365); // 1年

        return true;
    }

    /**
     * 获取支持的语言列表
     *
     * @return array
     */
    public static function getSupportedLangs()
    {
        $langs = [];

        foreach (self::$supportedLangs as $code => $name) {
            $langs[] = [
                'code' => $code,
                'name' => $name,
                'is_current' => $code === self::getCurrentLang()
            ];
        }

        return $langs;
    }

    /**
     * 获取语言名称
     *
     * @param string $lang 语言代码
     * @return string
     */
    public static function getLangName($lang)
    {
        return self::$supportedLangs[$lang] ?? $lang;
    }

    /**
     * 批量获取翻译
     *
     * @param array $keys 翻译键数组
     * @param string $lang 语言代码
     * @return array
     */
    public static function batchTranslate($keys, $lang = '')
    {
        if (empty($lang)) {
            $lang = self::getCurrentLang();
        }

        $translations = [];

        try {
            $results = Db::table('translations')
                ->where('lang', $lang)
                ->whereIn('key', $keys)
                ->column('value', 'key');

            foreach ($keys as $key) {
                $translations[$key] = $results[$key] ?? $key;
            }

            return $translations;
        } catch (\Exception $e) {
            // 出错时返回键名作为默认值
            foreach ($keys as $key) {
                $translations[$key] = $key;
            }

            return $translations;
        }
    }

    /**
     * 添加翻译
     *
     * @param string $key 翻译键
     * @param array $translations 翻译内容 ['zh-cn' => '中文', 'en' => 'English']
     * @return bool
     */
    public static function addTranslation($key, $translations)
    {
        if (empty($key) || empty($translations)) {
            return false;
        }

        try {
            Db::startTrans();

            foreach ($translations as $lang => $value) {
                if (!isset(self::$supportedLangs[$lang])) {
                    continue;
                }

                // 检查是否已存在
                $exists = Db::table('translations')
                    ->where('key', $key)
                    ->where('lang', $lang)
                    ->find();

                if ($exists) {
                    // 更新
                    Db::table('translations')
                        ->where('key', $key)
                        ->where('lang', $lang)
                        ->update([
                            'value' => $value,
                            'update_time' => date('Y-m-d H:i:s')
                        ]);
                } else {
                    // 插入
                    Db::table('translations')->insert([
                        'key' => $key,
                        'lang' => $lang,
                        'value' => $value,
                        'create_time' => date('Y-m-d H:i:s'),
                        'update_time' => date('Y-m-d H:i:s')
                    ]);
                }

                // 清除缓存
                Cache::delete("translation_{$lang}_{$key}");
            }

            Db::commit();

            return true;
        } catch (\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    /**
     * 删除翻译
     *
     * @param string $key 翻译键
     * @return bool
     */
    public static function deleteTranslation($key)
    {
        if (empty($key)) {
            return false;
        }

        try {
            Db::table('translations')->where('key', $key)->delete();

            // 清除所有语言的缓存
            foreach (array_keys(self::$supportedLangs) as $lang) {
                Cache::delete("translation_{$lang}_{$key}");
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 导出翻译为JSON
     *
     * @param string $lang 语言代码
     * @return string JSON字符串
     */
    public static function exportToJson($lang = '')
    {
        if (empty($lang)) {
            $lang = self::getCurrentLang();
        }

        try {
            $translations = Db::table('translations')
                ->where('lang', $lang)
                ->column('value', 'key');

            return json_encode($translations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            return '{}';
        }
    }

    /**
     * 从JSON导入翻译
     *
     * @param string $json JSON字符串
     * @param string $lang 语言代码
     * @return bool
     */
    public static function importFromJson($json, $lang)
    {
        if (empty($json) || !isset(self::$supportedLangs[$lang])) {
            return false;
        }

        try {
            $translations = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return false;
            }

            foreach ($translations as $key => $value) {
                self::addTranslation($key, [$lang => $value]);
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取翻译统计
     *
     * @return array
     */
    public static function getStats()
    {
        try {
            $stats = [];

            foreach (self::$supportedLangs as $lang => $name) {
                $count = Db::table('translations')
                    ->where('lang', $lang)
                    ->count();

                $stats[] = [
                    'lang' => $lang,
                    'name' => $name,
                    'count' => $count
                ];
            }

            return $stats;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 查找缺失的翻译
     *
     * @param string $targetLang 目标语言
     * @param string $sourceLang 源语言（默认为默认语言）
     * @return array
     */
    public static function findMissingTranslations($targetLang, $sourceLang = '')
    {
        if (empty($sourceLang)) {
            $sourceLang = self::DEFAULT_LANG;
        }

        try {
            // 获取源语言的所有键
            $sourceKeys = Db::table('translations')
                ->where('lang', $sourceLang)
                ->column('key');

            // 获取目标语言的所有键
            $targetKeys = Db::table('translations')
                ->where('lang', $targetLang)
                ->column('key');

            // 找出差异
            $missingKeys = array_diff($sourceKeys, $targetKeys);

            return array_values($missingKeys);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 自动翻译（使用翻译API）
     *
     * @param string $text 文本
     * @param string $fromLang 源语言
     * @param string $toLang 目标语言
     * @return string|null
     */
    public static function autoTranslate($text, $fromLang, $toLang)
    {
        // 这里应该调用翻译API
        // 例如：百度翻译API、Google翻译API等

        try {
            // 模拟翻译结果
            // 实际应用中应该调用真实的翻译API
            // $result = TranslationAPI::translate($text, $fromLang, $toLang);

            return null; // 需要实现真实的翻译API调用
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 清除所有翻译缓存
     *
     * @return bool
     */
    public static function clearCache()
    {
        try {
            // 清除所有translation_开头的缓存
            return Cache::clear();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取语言切换URL
     *
     * @param string $lang 语言代码
     * @return string
     */
    public static function getSwitchUrl($lang)
    {
        $currentUrl = request()->url(true);

        // 添加lang参数
        $separator = strpos($currentUrl, '?') === false ? '?' : '&';

        return $currentUrl . $separator . 'lang=' . $lang;
    }
}
