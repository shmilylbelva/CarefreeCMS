<?php
declare(strict_types=1);

namespace app\exception;

use app\common\ErrorCode;
use RuntimeException;
use Throwable;

/**
 * 业务异常类
 * 用于抛出业务逻辑错误，会被转换为友好的错误消息
 */
class BusinessException extends RuntimeException
{
    /**
     * 错误代码
     * @var int
     */
    protected $errorCode;

    /**
     * 额外的错误数据
     * @var array
     */
    protected $errorData = [];

    /**
     * 构造函数
     *
     * @param int $errorCode 错误代码（来自 ErrorCode 类）
     * @param string|null $message 自定义错误消息（可选，默认使用错误代码对应的消息）
     * @param array $data 额外的错误数据
     * @param Throwable|null $previous 上一个异常
     */
    public function __construct(
        int $errorCode = ErrorCode::UNKNOWN_ERROR,
        ?string $message = null,
        array $data = [],
        ?Throwable $previous = null
    ) {
        $this->errorCode = $errorCode;
        $this->errorData = $data;

        // 如果没有提供消息，使用错误代码对应的默认消息
        if ($message === null) {
            $message = ErrorCode::getMessage($errorCode);
        }

        parent::__construct($message, $errorCode, $previous);
    }

    /**
     * 获取错误代码
     *
     * @return int
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * 获取错误数据
     *
     * @return array
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * 设置错误数据
     *
     * @param array $data
     * @return self
     */
    public function setErrorData(array $data): self
    {
        $this->errorData = $data;
        return $this;
    }

    /**
     * 转换为数组
     *
     * @param bool $debug 是否包含调试信息
     * @return array
     */
    public function toArray(bool $debug = false): array
    {
        $result = [
            'code' => $this->errorCode,
            'message' => $this->getMessage(),
        ];

        if (!empty($this->errorData)) {
            $result['data'] = $this->errorData;
        }

        // 开发环境或调试模式下，返回详细的错误信息
        if ($debug) {
            $result['debug'] = [
                'file' => $this->getFile(),
                'line' => $this->getLine(),
                'trace' => $this->getTraceAsString(),
            ];
        }

        return $result;
    }

    // ==================== 快捷方法 ====================

    /**
     * 抛出认证失败异常
     *
     * @param string|null $message
     * @return never
     */
    public static function authFailed(?string $message = null): void
    {
        throw new self(ErrorCode::AUTH_FAILED, $message);
    }

    /**
     * 抛出权限拒绝异常
     *
     * @param string|null $message
     * @return never
     */
    public static function permissionDenied(?string $message = null): void
    {
        throw new self(ErrorCode::PERMISSION_DENIED, $message);
    }

    /**
     * 抛出资源不存在异常
     *
     * @param string|null $message
     * @return never
     */
    public static function notFound(?string $message = null): void
    {
        throw new self(ErrorCode::RESOURCE_NOT_FOUND, $message);
    }

    /**
     * 抛出资源已存在异常
     *
     * @param string|null $message
     * @return never
     */
    public static function alreadyExists(?string $message = null): void
    {
        throw new self(ErrorCode::RESOURCE_ALREADY_EXISTS, $message);
    }

    /**
     * 抛出参数无效异常
     *
     * @param string|null $message
     * @param array $data
     * @return never
     */
    public static function invalidParams(?string $message = null, array $data = []): void
    {
        throw new self(ErrorCode::INVALID_PARAMS, $message, $data);
    }

    /**
     * 抛出验证失败异常
     *
     * @param string|null $message
     * @param array $errors
     * @return never
     */
    public static function validationFailed(?string $message = null, array $errors = []): void
    {
        throw new self(ErrorCode::VALIDATION_FAILED, $message, $errors);
    }

    /**
     * 抛出操作失败异常
     *
     * @param string|null $message
     * @return never
     */
    public static function operationFailed(?string $message = null): void
    {
        throw new self(ErrorCode::OPERATION_FAILED, $message);
    }

    /**
     * 抛出系统错误异常
     *
     * @param string|null $message
     * @return never
     */
    public static function systemError(?string $message = null): void
    {
        throw new self(ErrorCode::SYSTEM_ERROR, $message);
    }
}
