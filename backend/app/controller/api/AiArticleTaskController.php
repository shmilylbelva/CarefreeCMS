<?php

namespace app\controller\api;

use app\BaseController;
use app\common\Response;
use app\common\Logger;
use app\model\AiArticleTask;
use app\model\AiGeneratedArticle;
use app\model\AiConfig;
use app\model\OperationLog;
use app\service\AiArticleGeneratorService;
use think\Request;

/**
 * AI文章生成任务管理控制器
 */
class AiArticleTaskController extends BaseController
{
    /**
     * 任务列表
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $status = $request->get('status', '');
        $categoryId = $request->get('category_id', '');

        $query = AiArticleTask::with(['aiConfig', 'category'])
            ->order('id', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $list = $query->page($page, $pageSize)->select();
        $total = $query->count();

        // 添加进度信息
        $list = $list->map(function ($item) {
            $data = $item->toArray();
            $data['progress'] = $item->progress;
            $data['status_text'] = $item->status_text;
            return $data;
        });

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 任务详情
     */
    public function read($id)
    {
        $task = AiArticleTask::with(['aiConfig', 'category', 'generatedArticles'])
            ->find($id);

        if (!$task) {
            return Response::notFound('任务不存在');
        }

        $data = $task->toArray();
        $data['progress'] = $task->progress;
        $data['status_text'] = $task->status_text;

        return Response::success($data);
    }

    /**
     * 创建任务
     */
    public function save(Request $request)
    {
        $data = $request->post();

        // 验证必填字段
        if (empty($data['title'])) {
            return Response::error('任务名称不能为空');
        }

        if (empty($data['topic'])) {
            return Response::error('生成主题不能为空');
        }

        if (empty($data['ai_config_id'])) {
            return Response::error('请选择AI配置');
        }

        // 验证AI配置是否存在
        $aiConfig = AiConfig::find($data['ai_config_id']);
        if (!$aiConfig) {
            return Response::error('AI配置不存在');
        }

        if ($aiConfig->status != 1) {
            return Response::error('AI配置已禁用');
        }

        try {
            // 解析主题（支持多行，每行一个主题）
            $topics = $this->parseTopics($data['topic']);

            if (empty($topics)) {
                return Response::error('请至少输入一个主题');
            }

            // 创建一个任务，包含所有主题
            $taskData = $data;
            $taskData['topic'] = implode("\n", $topics); // 保存所有主题（换行分隔）
            $taskData['total_count'] = count($topics); // 生成数量 = 主题数量

            $task = AiArticleTask::create($taskData);

            Logger::create(OperationLog::MODULE_ARTICLE, "AI生成任务[{$task->title}]，包含" . count($topics) . "个主题", $task->id);

            $message = count($topics) > 1
                ? "任务创建成功，将生成 " . count($topics) . " 篇文章"
                : "任务创建成功";

            return Response::success([
                'id' => $task->id,
                'topic_count' => count($topics)
            ], $message);
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_ARTICLE, OperationLog::ACTION_CREATE, '创建AI生成任务失败', false, $e->getMessage());
            return Response::error('任务创建失败：' . $e->getMessage());
        }
    }

    /**
     * 解析主题（支持多行）
     */
    private function parseTopics($topicString)
    {
        // 按换行符分隔
        $topics = preg_split('/\r\n|\r|\n/', trim($topicString));

        // 去除空行和首尾空格
        $topics = array_map('trim', $topics);
        $topics = array_filter($topics, function($topic) {
            return !empty($topic);
        });

        return array_values($topics);
    }

    /**
     * 更新任务
     */
    public function update(Request $request, $id)
    {
        $task = AiArticleTask::find($id);
        if (!$task) {
            return Response::notFound('任务不存在');
        }

        // 只有待处理和已停止的任务可以编辑
        if (!in_array($task->status, [AiArticleTask::STATUS_PENDING, AiArticleTask::STATUS_STOPPED])) {
            return Response::error('只有待处理或已停止的任务可以编辑');
        }

        $data = $request->post();

        try {
            $affected = \think\facade\Db::name('ai_article_tasks')
                ->where('id', '=', $id)
                ->limit(1)
                ->update($data);

            if ($affected === 0) {
                return Response::error('任务更新失败：未找到该任务或数据未改变');
            }

            Logger::update(OperationLog::MODULE_ARTICLE, 'AI生成任务', $id);
            return Response::success([], '任务更新成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_ARTICLE, OperationLog::ACTION_UPDATE, "更新AI生成任务失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('任务更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除任务
     */
    public function delete($id)
    {
        // 验证ID参数
        if (empty($id) || !is_numeric($id)) {
            return Response::error('无效的任务ID参数');
        }

        $id = (int)$id;

        // 使用find获取单个任务
        $task = AiArticleTask::find($id);
        if (!$task) {
            return Response::notFound('任务不存在');
        }

        // 处理中的任务不能删除
        if ($task->status === AiArticleTask::STATUS_PROCESSING) {
            return Response::error('处理中的任务不能删除，请先停止任务');
        }

        try {
            $taskId = $task->id;
            $taskTitle = $task->title;

            // 删除关联的生成记录（使用Db类明确指定条件）
            $deletedArticlesCount = \think\facade\Db::name('ai_generated_articles')
                ->where('task_id', '=', $taskId)
                ->delete();

            // 删除任务本身（使用Db类直接删除，确保WHERE条件精确）
            $deleteResult = \think\facade\Db::name('ai_article_tasks')
                ->where('id', '=', $taskId)
                ->limit(1)
                ->delete();

            if ($deleteResult === 0) {
                throw new \Exception('任务删除失败：未找到该任务');
            }

            Logger::delete(OperationLog::MODULE_ARTICLE, "AI生成任务[{$taskTitle}]，同时删除{$deletedArticlesCount}条生成记录", $taskId);
            return Response::success([], '任务删除成功');
        } catch (\Exception $e) {
            Logger::log(OperationLog::MODULE_ARTICLE, OperationLog::ACTION_DELETE, "删除AI生成任务失败 (ID: {$id})", false, $e->getMessage());
            return Response::error('任务删除失败：' . $e->getMessage());
        }
    }

    /**
     * 启动任务
     */
    public function start($id)
    {
        $task = AiArticleTask::with('aiConfig')->find($id);
        if (!$task) {
            return Response::notFound('任务不存在');
        }

        try {
            $task->start();

            // 异步执行生成任务
            $generator = new AiArticleGeneratorService();
            $generator->processTask($task);

            return Response::success([], '任务已启动，正在后台生成文章');
        } catch (\Exception $e) {
            $task->markAsFailed($e->getMessage());
            return Response::error('任务启动失败：' . $e->getMessage());
        }
    }

    /**
     * 停止任务
     */
    public function stop($id)
    {
        $task = AiArticleTask::find($id);
        if (!$task) {
            return Response::notFound('任务不存在');
        }

        try {
            $task->stop();
            return Response::success([], '任务已停止');
        } catch (\Exception $e) {
            return Response::error('停止任务失败：' . $e->getMessage());
        }
    }

    /**
     * 获取任务的生成记录
     */
    public function generatedArticles(Request $request, $id)
    {
        $task = AiArticleTask::find($id);
        if (!$task) {
            return Response::notFound('任务不存在');
        }

        $page = $request->get('page', 1);
        $pageSize = $request->get('page_size', 20);
        $status = $request->get('status', '');

        $query = AiGeneratedArticle::where('task_id', $id)
            ->with('article')
            ->order('id', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        $list = $query->page($page, $pageSize)->select();
        $total = $query->count();

        return Response::paginate($list->toArray(), $total, $page, $pageSize);
    }

    /**
     * 获取任务状态列表
     */
    public function statuses()
    {
        return Response::success(AiArticleTask::getStatuses());
    }

    /**
     * 统计信息
     */
    public function statistics()
    {
        $total = AiArticleTask::count();
        $pending = AiArticleTask::where('status', AiArticleTask::STATUS_PENDING)->count();
        $processing = AiArticleTask::where('status', AiArticleTask::STATUS_PROCESSING)->count();
        $completed = AiArticleTask::where('status', AiArticleTask::STATUS_COMPLETED)->count();
        $failed = AiArticleTask::where('status', AiArticleTask::STATUS_FAILED)->count();

        $totalGenerated = AiArticleTask::sum('generated_count');
        $totalSuccess = AiArticleTask::sum('success_count');

        return Response::success([
            'total' => $total,
            'pending' => $pending,
            'processing' => $processing,
            'completed' => $completed,
            'failed' => $failed,
            'total_generated' => $totalGenerated,
            'total_success' => $totalSuccess,
        ]);
    }
}
