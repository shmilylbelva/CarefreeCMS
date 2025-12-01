<?php

namespace app\controller\api;

use app\model\SensitiveWord;
use app\service\SensitiveWordService;
use think\Request;
use think\Response;

/**
 * 敏感词管理控制器
 */
class SensitiveWordController extends BaseController
{
    /**
     * 获取敏感词列表
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $page = $request->param('page', 1);
        $pageSize = $request->param('page_size', 20);
        $category = $request->param('category', '');
        $level = $request->param('level', '');
        $keyword = $request->param('keyword', '');
        $isEnabled = $request->param('is_enabled', '');

        $query = SensitiveWord::order('id desc');

        // 筛选条件
        if ($category) {
            $query->where('category', $category);
        }
        if ($level !== '') {
            $query->where('level', $level);
        }
        if ($keyword) {
            $query->whereLike('word', "%{$keyword}%");
        }
        if ($isEnabled !== '') {
            $query->where('is_enabled', $isEnabled);
        }

        $total = $query->count();
        $list = $query->page($page, $pageSize)->select()->toArray();

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'total' => $total,
                'list' => $list,
                'page' => $page,
                'page_size' => $pageSize
            ]
        ]);
    }

    /**
     * 获取敏感词详情
     * @param int $id
     * @return Response
     */
    public function read(int $id): Response
    {
        $word = SensitiveWord::find($id);

        if (!$word) {
            return json(['code' => 404, 'message' => '敏感词不存在']);
        }

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => $word
        ]);
    }

    /**
     * 创建敏感词
     * @param Request $request
     * @return Response
     */
    public function save(Request $request): Response
    {
        $data = $request->only(['word', 'level', 'replacement', 'category', 'is_enabled', 'remark']);

        // 验证
        if (empty($data['word'])) {
            return json(['code' => 400, 'message' => '敏感词不能为空']);
        }

        // 检查是否已存在
        $exists = SensitiveWord::where('word', $data['word'])->find();
        if ($exists) {
            return json(['code' => 400, 'message' => '该敏感词已存在']);
        }

        $data['level'] = $data['level'] ?? SensitiveWord::LEVEL_REPLACE;
        $data['replacement'] = $data['replacement'] ?? '***';
        $data['category'] = $data['category'] ?? SensitiveWord::CATEGORY_GENERAL;
        $data['is_enabled'] = $data['is_enabled'] ?? 1;

        $word = SensitiveWord::create($data);

        // 清除缓存
        SensitiveWordService::clearCache();

        return json([
            'code' => 0,
            'message' => '创建成功',
            'data' => $word
        ]);
    }

    /**
     * 更新敏感词
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, int $id): Response
    {
        $word = SensitiveWord::find($id);
        if (!$word) {
            return json(['code' => 404, 'message' => '敏感词不存在']);
        }

        $data = $request->only(['word', 'level', 'replacement', 'category', 'is_enabled', 'remark']);

        // 如果修改了敏感词，检查是否重复
        if (isset($data['word']) && $data['word'] != $word->word) {
            $exists = SensitiveWord::where('word', $data['word'])
                ->where('id', '<>', $id)
                ->find();
            if ($exists) {
                return json(['code' => 400, 'message' => '该敏感词已存在']);
            }
        }

        $word->save($data);

        // 清除缓存
        SensitiveWordService::clearCache();

        return json([
            'code' => 0,
            'message' => '更新成功',
            'data' => $word
        ]);
    }

    /**
     * 删除敏感词
     * @param int $id
     * @return Response
     */
    public function delete(int $id): Response
    {
        $word = SensitiveWord::find($id);
        if (!$word) {
            return json(['code' => 404, 'message' => '敏感词不存在']);
        }

        $word->delete();

        // 清除缓存
        SensitiveWordService::clearCache();

        return json([
            'code' => 0,
            'message' => '删除成功'
        ]);
    }

    /**
     * 批量删除
     * @param Request $request
     * @return Response
     */
    public function batchDelete(Request $request): Response
    {
        $ids = $request->param('ids', []);

        if (empty($ids)) {
            return json(['code' => 400, 'message' => 'IDs不能为空']);
        }

        SensitiveWord::destroy($ids);

        // 清除缓存
        SensitiveWordService::clearCache();

        return json([
            'code' => 0,
            'message' => "已删除 " . count($ids) . " 个敏感词"
        ]);
    }

    /**
     * 批量导入
     * @param Request $request
     * @return Response
     */
    public function batchImport(Request $request): Response
    {
        $words = $request->param('words', '');
        $level = $request->param('level', SensitiveWord::LEVEL_REPLACE);
        $category = $request->param('category', SensitiveWord::CATEGORY_GENERAL);
        $replacement = $request->param('replacement', '***');

        if (empty($words)) {
            return json(['code' => 400, 'message' => '导入内容不能为空']);
        }

        // 按行分割
        $wordArray = array_filter(array_map('trim', explode("\n", $words)));

        $count = SensitiveWord::batchImport($wordArray, [
            'level' => $level,
            'category' => $category,
            'replacement' => $replacement
        ]);

        // 清除缓存
        SensitiveWordService::clearCache();

        return json([
            'code' => 0,
            'message' => "成功导入 {$count} 个敏感词"
        ]);
    }

    /**
     * 批量更新状态
     * @param Request $request
     * @return Response
     */
    public function batchUpdateStatus(Request $request): Response
    {
        $ids = $request->param('ids', []);
        $isEnabled = $request->param('is_enabled', 1);

        if (empty($ids)) {
            return json(['code' => 400, 'message' => 'IDs不能为空']);
        }

        SensitiveWord::whereIn('id', $ids)->update(['is_enabled' => $isEnabled]);

        // 清除缓存
        SensitiveWordService::clearCache();

        return json([
            'code' => 0,
            'message' => '更新成功'
        ]);
    }

    /**
     * 获取分类选项
     * @return Response
     */
    public function categories(): Response
    {
        return json([
            'code' => 0,
            'message' => 'success',
            'data' => SensitiveWord::getCategoryOptions()
        ]);
    }

    /**
     * 获取级别选项
     * @return Response
     */
    public function levels(): Response
    {
        return json([
            'code' => 0,
            'message' => 'success',
            'data' => SensitiveWord::getLevelOptions()
        ]);
    }

    /**
     * 获取统计信息
     * @return Response
     */
    public function statistics(): Response
    {
        return json([
            'code' => 0,
            'message' => 'success',
            'data' => SensitiveWord::getStatistics()
        ]);
    }

    /**
     * 测试检测
     * @param Request $request
     * @return Response
     */
    public function testCheck(Request $request): Response
    {
        $content = $request->param('content', '');

        if (empty($content)) {
            return json(['code' => 400, 'message' => '测试内容不能为空']);
        }

        $service = new SensitiveWordService();
        $checkResult = $service->check($content);
        $filterResult = $service->filter($content);

        return json([
            'code' => 0,
            'message' => 'success',
            'data' => [
                'has_sensitive' => $checkResult['has_sensitive'],
                'matched_words' => $checkResult['words'],
                'matched_count' => $checkResult['count'],
                'filtered_content' => $filterResult['filtered'],
                'matched_details' => $filterResult['matched']
            ]
        ]);
    }
}
