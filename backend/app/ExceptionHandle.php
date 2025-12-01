<?php
namespace app;

use app\common\ErrorCode;
use app\exception\BusinessException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
        BusinessException::class, // 业务异常不记录到错误日志
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 判断是否为调试模式
        $debug = config('app.debug', false);

        // 处理业务异常
        if ($e instanceof BusinessException) {
            return $this->renderBusinessException($e, $debug);
        }

        // 处理模型不存在异常
        if ($e instanceof ModelNotFoundException || $e instanceof DataNotFoundException) {
            return $this->renderNotFoundResponse($debug);
        }

        // 处理验证异常
        if ($e instanceof ValidateException) {
            return $this->renderValidationException($e, $debug);
        }

        // 处理HTTP异常
        if ($e instanceof HttpException) {
            return $this->renderHttpException($request, $e);
        }

        // 处理其他异常
        return $this->renderGenericException($e, $debug);
    }

    /**
     * 渲染业务异常
     *
     * @param BusinessException $e
     * @param bool $debug
     * @return Response
     */
    protected function renderBusinessException(BusinessException $e, bool $debug): Response
    {
        $data = $e->toArray($debug);

        return json([
            'code' => $data['code'],
            'message' => $data['message'],
            'data' => $data['data'] ?? null,
            'debug' => $data['debug'] ?? null,
        ], 200);
    }

    /**
     * 渲染资源不存在响应
     *
     * @param bool $debug
     * @return Response
     */
    protected function renderNotFoundResponse(bool $debug): Response
    {
        return json([
            'code' => ErrorCode::RESOURCE_NOT_FOUND,
            'message' => ErrorCode::getMessage(ErrorCode::RESOURCE_NOT_FOUND),
            'data' => null,
        ], 404);
    }

    /**
     * 渲染验证异常
     *
     * @param ValidateException $e
     * @param bool $debug
     * @return Response
     */
    protected function renderValidationException(ValidateException $e, bool $debug): Response
    {
        $errors = $e->getError();

        return json([
            'code' => ErrorCode::VALIDATION_FAILED,
            'message' => is_array($errors) ? '数据验证失败' : $errors,
            'data' => is_array($errors) ? $errors : null,
            'debug' => $debug ? [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ] : null,
        ], 422);
    }

    /**
     * 渲染HTTP异常
     *
     * @param \think\Request $request
     * @param HttpException $e
     * @return Response
     */
    protected function renderHttpException($request, HttpException $e): Response
    {
        $debug = config('app.debug', false);
        $statusCode = $e->getStatusCode();
        $message = $e->getMessage();

        // 根据状态码提供友好的默认消息
        if (empty($message)) {
            $message = $this->getDefaultHttpMessage($statusCode);
        }

        return json([
            'code' => $statusCode,
            'message' => $message,
            'data' => null,
            'debug' => $debug ? [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ] : null,
        ], $statusCode);
    }

    /**
     * 渲染通用异常
     *
     * @param Throwable $e
     * @param bool $debug
     * @return Response
     */
    protected function renderGenericException(Throwable $e, bool $debug): Response
    {
        // 在生产环境隐藏技术细节
        if ($debug) {
            $message = $e->getMessage();
            $debugInfo = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];
        } else {
            $message = ErrorCode::getMessage(ErrorCode::SYSTEM_ERROR);
            $debugInfo = null;
        }

        return json([
            'code' => ErrorCode::SYSTEM_ERROR,
            'message' => $message,
            'data' => null,
            'debug' => $debugInfo,
        ], 500);
    }

    /**
     * 获取HTTP状态码的默认友好消息
     *
     * @param int $statusCode
     * @return string
     */
    protected function getDefaultHttpMessage(int $statusCode): string
    {
        $messages = [
            400 => '请求参数错误',
            401 => '未授权，请先登录',
            403 => '禁止访问，您没有权限',
            404 => '请求的资源不存在',
            405 => '请求方法不允许',
            408 => '请求超时',
            422 => '请求参数验证失败',
            429 => '请求过于频繁，请稍后再试',
            500 => '服务器内部错误',
            502 => '网关错误',
            503 => '服务暂时不可用',
            504 => '网关超时',
        ];

        return $messages[$statusCode] ?? '请求失败';
    }
}
