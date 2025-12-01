<?php
namespace app\service\tag;

use think\facade\Db;

/**
 * 问答/测验标签服务类
 * 处理问答测验标签的数据查询
 */
class QuizTagService
{
    /**
     * 获取测验信息
     *
     * @param array $params 查询参数
     *   - quizid: 测验ID
     * @return array|null
     */
    public static function getInfo($params = [])
    {
        $quizid = $params['quizid'] ?? 0;

        if (empty($quizid)) {
            return null;
        }

        try {
            // 获取测验基本信息
            $quiz = Db::table('quizzes')
                ->where('id', $quizid)
                ->where('status', 1)
                ->find();

            if (!$quiz) {
                return null;
            }

            // 获取测验问题
            $questions = Db::table('quiz_questions')
                ->where('quiz_id', $quizid)
                ->where('status', 1)
                ->order('sort', 'asc')
                ->select()
                ->toArray();

            // 获取每个问题的选项
            foreach ($questions as &$question) {
                $options = Db::table('quiz_options')
                    ->where('question_id', $question['id'])
                    ->order('sort', 'asc')
                    ->select()
                    ->toArray();

                // 如果不显示答案，移除正确答案标记
                if ($quiz['show_answer'] != 1) {
                    foreach ($options as &$option) {
                        unset($option['is_correct']);
                    }
                }

                $question['options'] = $options;

                // 格式化问题类型
                $question['type_text'] = self::getQuestionTypeText($question['type'] ?? 'single');
            }

            $quiz['questions'] = $questions;
            $quiz['question_count'] = count($questions);

            // 获取统计信息
            $quiz['total_participants'] = Db::table('quiz_records')
                ->where('quiz_id', $quizid)
                ->count();

            // 格式化时间
            if (!empty($quiz['create_time'])) {
                $quiz['create_time_formatted'] = date('Y-m-d H:i', is_numeric($quiz['create_time']) ? $quiz['create_time'] : strtotime($quiz['create_time']));
            }

            // 检查是否有时间限制
            if (!empty($quiz['time_limit'])) {
                $quiz['time_limit_formatted'] = self::formatTimeLimit($quiz['time_limit']);
            }

            // 计算通过率（如果有通过分数）
            if (!empty($quiz['pass_score']) && $quiz['total_participants'] > 0) {
                $passedCount = Db::table('quiz_records')
                    ->where('quiz_id', $quizid)
                    ->where('score', '>=', $quiz['pass_score'])
                    ->count();

                $quiz['pass_rate'] = round(($passedCount / $quiz['total_participants']) * 100, 2);
            } else {
                $quiz['pass_rate'] = 0;
            }

            return $quiz;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * 获取问题类型文本
     *
     * @param string $type 类型
     * @return string
     */
    private static function getQuestionTypeText($type)
    {
        $typeMap = [
            'single' => '单选题',
            'multiple' => '多选题',
            'truefalse' => '判断题',
            'fill' => '填空题',
            'essay' => '问答题'
        ];

        return $typeMap[$type] ?? '未知';
    }

    /**
     * 格式化时间限制
     *
     * @param int $seconds 秒数
     * @return string
     */
    private static function formatTimeLimit($seconds)
    {
        if ($seconds >= 3600) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            return $hours . '小时' . ($minutes > 0 ? $minutes . '分钟' : '');
        } elseif ($seconds >= 60) {
            return floor($seconds / 60) . '分钟';
        } else {
            return $seconds . '秒';
        }
    }

    /**
     * 获取测验列表
     *
     * @param int $catid 分类ID
     * @param int $limit 数量限制
     * @return array
     */
    public static function getList($catid = 0, $limit = 10)
    {
        try {
            $query = Db::table('quizzes')
                ->where('status', 1);

            if ($catid > 0) {
                $query->where('category_id', $catid);
            }

            $quizzes = $query->order('create_time', 'desc')
                ->limit($limit)
                ->select()
                ->toArray();

            foreach ($quizzes as &$quiz) {
                // 获取问题数量
                $quiz['question_count'] = Db::table('quiz_questions')
                    ->where('quiz_id', $quiz['id'])
                    ->where('status', 1)
                    ->count();

                // 获取参与人数
                $quiz['total_participants'] = Db::table('quiz_records')
                    ->where('quiz_id', $quiz['id'])
                    ->count();

                // 格式化时间限制
                if (!empty($quiz['time_limit'])) {
                    $quiz['time_limit_formatted'] = self::formatTimeLimit($quiz['time_limit']);
                }
            }

            return $quizzes;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 提交测验答案
     *
     * @param int $quizId 测验ID
     * @param array $answers 答案数组 [question_id => answer]
     * @param string $userId 用户ID（可选）
     * @return array
     */
    public static function submitQuiz($quizId, $answers, $userId = '')
    {
        try {
            // 获取测验信息
            $quiz = Db::table('quizzes')
                ->where('id', $quizId)
                ->where('status', 1)
                ->find();

            if (!$quiz) {
                return ['success' => false, 'message' => '测验不存在'];
            }

            // 获取所有问题
            $questions = Db::table('quiz_questions')
                ->where('quiz_id', $quizId)
                ->where('status', 1)
                ->select()
                ->toArray();

            $totalScore = 0;
            $correctCount = 0;
            $wrongCount = 0;
            $resultDetails = [];

            // 评分
            foreach ($questions as $question) {
                $questionId = $question['id'];
                $userAnswer = $answers[$questionId] ?? '';

                // 获取正确答案
                $correctOptions = Db::table('quiz_options')
                    ->where('question_id', $questionId)
                    ->where('is_correct', 1)
                    ->column('id');

                $isCorrect = false;

                // 判断答案是否正确
                if ($question['type'] == 'multiple') {
                    // 多选题：需要完全匹配
                    $userAnswerArray = is_array($userAnswer) ? $userAnswer : explode(',', $userAnswer);
                    sort($userAnswerArray);
                    sort($correctOptions);
                    $isCorrect = $userAnswerArray == $correctOptions;
                } else {
                    // 单选题、判断题
                    $isCorrect = in_array($userAnswer, $correctOptions);
                }

                if ($isCorrect) {
                    $totalScore += $question['score'] ?? 0;
                    $correctCount++;
                } else {
                    $wrongCount++;
                }

                $resultDetails[] = [
                    'question_id' => $questionId,
                    'user_answer' => $userAnswer,
                    'correct_answer' => implode(',', $correctOptions),
                    'is_correct' => $isCorrect,
                    'score' => $isCorrect ? ($question['score'] ?? 0) : 0
                ];
            }

            // 保存记录
            Db::table('quiz_records')->insert([
                'quiz_id' => $quizId,
                'user_id' => $userId ?: null,
                'score' => $totalScore,
                'correct_count' => $correctCount,
                'wrong_count' => $wrongCount,
                'total_count' => count($questions),
                'answers' => json_encode($answers),
                'result_details' => json_encode($resultDetails),
                'create_time' => date('Y-m-d H:i:s')
            ]);

            // 判断是否通过
            $isPassed = false;
            if (!empty($quiz['pass_score'])) {
                $isPassed = $totalScore >= $quiz['pass_score'];
            }

            return [
                'success' => true,
                'message' => '提交成功',
                'score' => $totalScore,
                'correct_count' => $correctCount,
                'wrong_count' => $wrongCount,
                'total_count' => count($questions),
                'is_passed' => $isPassed,
                'pass_score' => $quiz['pass_score'] ?? 0,
                'result_details' => $quiz['show_answer'] == 1 ? $resultDetails : []
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => '提交失败：' . $e->getMessage()];
        }
    }
}
