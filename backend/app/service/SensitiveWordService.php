<?php

namespace app\service;

use app\model\SensitiveWord;
use app\model\ContentViolation;
use app\model\UserReputation;
use think\facade\Cache;

/**
 * 敏感词过滤服务
 * 使用DFA（Deterministic Finite Automaton）算法实现高效敏感词检测
 */
class SensitiveWordService
{
    /**
     * 敏感词树（字典树/Trie树）
     * @var array
     */
    private $wordTree = [];

    /**
     * 敏感词映射（word => 详情）
     * @var array
     */
    private $wordMap = [];

    /**
     * 缓存key
     */
    const CACHE_KEY = 'sensitive_words_tree';
    const CACHE_TTL = 3600; // 1小时

    /**
     * 构造函数 - 初始化敏感词树
     */
    public function __construct()
    {
        $this->loadWords();
    }

    /**
     * 加载敏感词
     */
    private function loadWords()
    {
        // 尝试从缓存读取
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached) {
            $this->wordTree = $cached['tree'];
            $this->wordMap = $cached['map'];
            return;
        }

        // 从数据库读取
        $words = SensitiveWord::getEnabledWords();
        $this->buildTree($words);

        // 存入缓存
        Cache::set(self::CACHE_KEY, [
            'tree' => $this->wordTree,
            'map' => $this->wordMap
        ], self::CACHE_TTL);
    }

    /**
     * 构建敏感词字典树（DFA）
     * @param array $words
     */
    private function buildTree(array $words)
    {
        $this->wordTree = [];
        $this->wordMap = [];

        foreach ($words as $id => $wordInfo) {
            $word = $wordInfo['word'];
            $this->wordMap[$word] = $wordInfo;

            // 将词拆分成字符数组
            $chars = $this->strToArray($word);
            $tree = &$this->wordTree;

            foreach ($chars as $char) {
                if (!isset($tree[$char])) {
                    $tree[$char] = [];
                }
                $tree = &$tree[$char];
            }

            // 标记结束节点
            $tree['end'] = true;
            $tree['word'] = $word;
        }
    }

    /**
     * 字符串转数组（支持中英文）
     * @param string $str
     * @return array
     */
    private function strToArray(string $str): array
    {
        return preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 检测内容中的敏感词
     * @param string $content
     * @return array ['has_sensitive' => bool, 'words' => array]
     */
    public function check(string $content): array
    {
        $matchedWords = [];
        $chars = $this->strToArray($content);
        $length = count($chars);

        for ($i = 0; $i < $length; $i++) {
            $tree = $this->wordTree;
            $word = '';
            $j = $i;

            while ($j < $length && isset($tree[$chars[$j]])) {
                $word .= $chars[$j];
                $tree = $tree[$chars[$j]];

                // 找到完整敏感词
                if (isset($tree['end']) && $tree['end']) {
                    $matchedWords[] = $tree['word'];
                    break;
                }

                $j++;
            }
        }

        // 去重
        $matchedWords = array_unique($matchedWords);

        return [
            'has_sensitive' => !empty($matchedWords),
            'words' => $matchedWords,
            'count' => count($matchedWords)
        ];
    }

    /**
     * 过滤内容中的敏感词
     * @param string $content
     * @param string $replacement 替换字符
     * @return array ['filtered' => string, 'matched' => array]
     */
    public function filter(string $content, string $replacement = '***'): array
    {
        $result = $this->check($content);

        if (!$result['has_sensitive']) {
            return [
                'filtered' => $content,
                'matched' => [],
                'replaced_count' => 0
            ];
        }

        $filtered = $content;
        $matchedDetails = [];

        foreach ($result['words'] as $word) {
            if (isset($this->wordMap[$word])) {
                $wordInfo = $this->wordMap[$word];
                $rep = $wordInfo['replacement'] ?? $replacement;

                // 替换敏感词
                $filtered = str_replace($word, $rep, $filtered);

                $matchedDetails[] = [
                    'word' => $word,
                    'level' => $wordInfo['level'],
                    'category' => $wordInfo['category'],
                    'replacement' => $rep
                ];
            }
        }

        return [
            'filtered' => $filtered,
            'matched' => $matchedDetails,
            'replaced_count' => count($matchedDetails)
        ];
    }

    /**
     * 检查并处理内容
     * @param string $contentType 内容类型（article/comment/page）
     * @param int $contentId 内容ID
     * @param int $userId 用户ID
     * @param string $content 内容
     * @param bool $autoReplace 是否自动替换
     * @return array ['allowed' => bool, 'content' => string, 'action' => string, 'message' => string]
     */
    public function checkAndHandle(
        string $contentType,
        int $contentId,
        int $userId,
        string $content,
        bool $autoReplace = true
    ): array {
        $checkResult = $this->check($content);

        // 没有敏感词，直接通过
        if (!$checkResult['has_sensitive']) {
            return [
                'allowed' => true,
                'content' => $content,
                'action' => 'pass',
                'message' => '内容检查通过'
            ];
        }

        // 有敏感词，判断处理级别
        $maxLevel = $this->getMaxLevel($checkResult['words']);
        $action = '';
        $allowed = true;
        $finalContent = $content;
        $message = '';

        switch ($maxLevel) {
            case SensitiveWord::LEVEL_REJECT:
                // 拒绝发布
                $allowed = false;
                $action = ContentViolation::ACTION_REJECT;
                $message = '内容包含严重违规词汇，无法发布';
                UserReputation::recordViolation($userId, 10);
                break;

            case SensitiveWord::LEVEL_REPLACE:
                // 自动替换
                if ($autoReplace) {
                    $filterResult = $this->filter($content);
                    $finalContent = $filterResult['filtered'];
                    $action = ContentViolation::ACTION_REPLACE;
                    $message = '内容已自动过滤敏感词';
                } else {
                    $allowed = false;
                    $action = ContentViolation::ACTION_REJECT;
                    $message = '内容包含敏感词汇，请修改后重新提交';
                }
                UserReputation::recordViolation($userId, 5);
                break;

            case SensitiveWord::LEVEL_WARN:
                // 仅警告
                $action = ContentViolation::ACTION_WARN;
                $message = '内容包含需要注意的词汇，已记录';
                UserReputation::recordViolation($userId, 2);
                break;
        }

        // 记录违规
        $this->recordViolation($contentType, $contentId, $userId, $content, $finalContent, $checkResult['words'], $action);

        return [
            'allowed' => $allowed,
            'content' => $finalContent,
            'action' => $action,
            'message' => $message,
            'matched_words' => $checkResult['words']
        ];
    }

    /**
     * 获取最高处理级别
     * @param array $words
     * @return int
     */
    private function getMaxLevel(array $words): int
    {
        $maxLevel = SensitiveWord::LEVEL_WARN;

        foreach ($words as $word) {
            if (isset($this->wordMap[$word])) {
                $level = $this->wordMap[$word]['level'];
                if ($level > $maxLevel) {
                    $maxLevel = $level;
                }
            }
        }

        return $maxLevel;
    }

    /**
     * 记录违规内容
     * @param string $contentType
     * @param int $contentId
     * @param int $userId
     * @param string $originalContent
     * @param string $filteredContent
     * @param array $matchedWords
     * @param string $action
     */
    private function recordViolation(
        string $contentType,
        int $contentId,
        int $userId,
        string $originalContent,
        string $filteredContent,
        array $matchedWords,
        string $action
    ) {
        ContentViolation::record([
            'content_type' => $contentType,
            'content_id' => $contentId,
            'user_id' => $userId,
            'matched_words' => $matchedWords,
            'original_content' => mb_substr($originalContent, 0, 500),
            'filtered_content' => $filteredContent !== $originalContent ? mb_substr($filteredContent, 0, 500) : null,
            'action' => $action
        ]);

        // 增加敏感词命中次数
        foreach ($matchedWords as $word) {
            if (isset($this->wordMap[$word])) {
                SensitiveWord::incrementHitCount(array_search($word, array_column($this->wordMap, 'word')));
            }
        }
    }

    /**
     * 清除缓存
     */
    public static function clearCache()
    {
        Cache::delete(self::CACHE_KEY);
    }

    /**
     * 重新加载敏感词
     */
    public function reload()
    {
        self::clearCache();
        $this->loadWords();
    }
}
