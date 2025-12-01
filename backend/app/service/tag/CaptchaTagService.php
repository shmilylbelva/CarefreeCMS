<?php
namespace app\service\tag;

use think\facade\Session;

/**
 * 验证码标签服务类
 * 处理验证码生成标签的数据查询
 */
class CaptchaTagService
{
    /**
     * 生成验证码
     *
     * @param array $params 查询参数
     *   - type: 类型（image-图片验证码，sms-短信验证码，email-邮件验证码）
     *   - width: 宽度
     *   - height: 高度
     *   - length: 长度
     * @return array
     */
    public static function generate($params = [])
    {
        $type = $params['type'] ?? 'image';
        $width = $params['width'] ?? 120;
        $height = $params['height'] ?? 40;
        $length = $params['length'] ?? 4;

        try {
            switch ($type) {
                case 'image':
                    return self::generateImageCaptcha($width, $height, $length);

                case 'sms':
                    return self::generateSmsCaptcha($length);

                case 'email':
                    return self::generateEmailCaptcha($length);

                default:
                    return self::generateImageCaptcha($width, $height, $length);
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 生成图片验证码
     *
     * @param int $width 宽度
     * @param int $height 高度
     * @param int $length 长度
     * @return array
     */
    private static function generateImageCaptcha($width, $height, $length)
    {
        // 生成随机验证码
        $code = self::generateCode($length);

        // 保存到session
        $captchaKey = 'captcha_' . md5(uniqid());
        Session::set($captchaKey, [
            'code' => strtolower($code),
            'expire_time' => time() + 300 // 5分钟过期
        ]);

        // 返回验证码信息（实际图片生成应该通过API接口）
        return [
            'type' => 'image',
            'key' => $captchaKey,
            'url' => url('api/captcha/image', ['key' => $captchaKey])->build(),
            'expire_time' => 300
        ];
    }

    /**
     * 生成短信验证码
     *
     * @param int $length 长度
     * @return array
     */
    private static function generateSmsCaptcha($length = 6)
    {
        // 生成纯数字验证码
        $code = self::generateCode($length, 'number');

        // 生成唯一标识
        $captchaKey = 'sms_captcha_' . md5(uniqid());

        Session::set($captchaKey, [
            'code' => $code,
            'expire_time' => time() + 600 // 10分钟过期
        ]);

        return [
            'type' => 'sms',
            'key' => $captchaKey,
            'code' => $code, // 实际应用中不应该返回code，这里仅用于测试
            'expire_time' => 600
        ];
    }

    /**
     * 生成邮件验证码
     *
     * @param int $length 长度
     * @return array
     */
    private static function generateEmailCaptcha($length = 6)
    {
        // 生成混合验证码
        $code = self::generateCode($length, 'mixed');

        // 生成唯一标识
        $captchaKey = 'email_captcha_' . md5(uniqid());

        Session::set($captchaKey, [
            'code' => strtolower($code),
            'expire_time' => time() + 600 // 10分钟过期
        ]);

        return [
            'type' => 'email',
            'key' => $captchaKey,
            'code' => $code, // 实际应用中不应该返回code，这里仅用于测试
            'expire_time' => 600
        ];
    }

    /**
     * 生成验证码字符串
     *
     * @param int $length 长度
     * @param string $type 类型（number-数字，letter-字母，mixed-混合）
     * @return string
     */
    private static function generateCode($length = 4, $type = 'mixed')
    {
        $chars = '';

        switch ($type) {
            case 'number':
                $chars = '0123456789';
                break;

            case 'letter':
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;

            case 'mixed':
            default:
                // 排除容易混淆的字符：0, O, o, 1, l, I
                $chars = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ';
                break;
        }

        $code = '';
        $charsLen = strlen($chars);

        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[mt_rand(0, $charsLen - 1)];
        }

        return $code;
    }

    /**
     * 验证验证码
     *
     * @param string $key 验证码key
     * @param string $code 用户输入的验证码
     * @return bool
     */
    public static function verify($key, $code)
    {
        if (empty($key) || empty($code)) {
            return false;
        }

        // 从session获取验证码
        $captchaData = Session::get($key);

        if (empty($captchaData)) {
            return false;
        }

        // 检查是否过期
        if (time() > $captchaData['expire_time']) {
            Session::delete($key);
            return false;
        }

        // 验证码比对（不区分大小写）
        $result = strtolower($code) === strtolower($captchaData['code']);

        // 验证后删除（一次性使用）
        if ($result) {
            Session::delete($key);
        }

        return $result;
    }

    /**
     * 创建图片验证码图片
     *
     * @param string $code 验证码
     * @param int $width 宽度
     * @param int $height 高度
     * @return resource|false
     */
    public static function createImage($code, $width = 120, $height = 40)
    {
        // 创建画布
        $image = imagecreatetruecolor($width, $height);

        // 设置背景色
        $bgColor = imagecolorallocate($image, mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255));
        imagefill($image, 0, 0, $bgColor);

        // 添加干扰线
        for ($i = 0; $i < 5; $i++) {
            $lineColor = imagecolorallocate($image, mt_rand(100, 200), mt_rand(100, 200), mt_rand(100, 200));
            imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $lineColor);
        }

        // 添加干扰点
        for ($i = 0; $i < 100; $i++) {
            $pointColor = imagecolorallocate($image, mt_rand(100, 200), mt_rand(100, 200), mt_rand(100, 200));
            imagesetpixel($image, mt_rand(0, $width), mt_rand(0, $height), $pointColor);
        }

        // 写入验证码
        $codeLen = strlen($code);
        $fontSize = $height * 0.6;
        $x = $width / ($codeLen + 1);

        for ($i = 0; $i < $codeLen; $i++) {
            $textColor = imagecolorallocate($image, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
            $angle = mt_rand(-15, 15);

            // 使用内置字体（如果没有ttf字体文件）
            $charX = $x * ($i + 1);
            $charY = mt_rand($height * 0.4, $height * 0.8);

            imagestring($image, 5, $charX, $charY - 10, $code[$i], $textColor);
        }

        return $image;
    }

    /**
     * 输出图片验证码
     *
     * @param string $key 验证码key
     * @return void
     */
    public static function output($key)
    {
        $captchaData = Session::get($key);

        if (empty($captchaData)) {
            // 如果验证码不存在，生成一个错误提示图片
            $image = imagecreatetruecolor(120, 40);
            $bgColor = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $bgColor);
            $textColor = imagecolorallocate($image, 255, 0, 0);
            imagestring($image, 3, 20, 15, 'Invalid', $textColor);
        } else {
            $image = self::createImage($captchaData['code']);
        }

        header('Content-Type: image/png');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        imagepng($image);
        imagedestroy($image);
    }

    /**
     * 发送短信验证码
     *
     * @param string $phone 手机号
     * @param string $code 验证码
     * @return bool
     */
    public static function sendSms($phone, $code)
    {
        // 这里应该调用短信服务商API
        // 示例：阿里云短信、腾讯云短信等

        try {
            // 模拟发送成功
            // 实际应用中应该调用真实的短信API
            // $result = SmsService::send($phone, $code);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 发送邮件验证码
     *
     * @param string $email 邮箱地址
     * @param string $code 验证码
     * @return bool
     */
    public static function sendEmail($email, $code)
    {
        // 这里应该调用邮件发送服务
        try {
            // 模拟发送成功
            // 实际应用中应该使用邮件发送库，如PHPMailer
            // $mail = new PHPMailer();
            // $mail->send();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 清理过期验证码
     *
     * @return int 清理数量
     */
    public static function clearExpired()
    {
        // 清理session中过期的验证码
        // ThinkPHP的session会自动清理过期数据
        // 这里只是提供一个接口

        return 0;
    }

    /**
     * 获取验证码剩余时间
     *
     * @param string $key 验证码key
     * @return int 剩余秒数，-1表示不存在或已过期
     */
    public static function getRemainTime($key)
    {
        $captchaData = Session::get($key);

        if (empty($captchaData)) {
            return -1;
        }

        $remainTime = $captchaData['expire_time'] - time();

        return $remainTime > 0 ? $remainTime : -1;
    }

    /**
     * 刷新验证码
     *
     * @param string $key 验证码key
     * @param int $width 宽度
     * @param int $height 高度
     * @param int $length 长度
     * @return array
     */
    public static function refresh($key, $width = 120, $height = 40, $length = 4)
    {
        // 删除旧验证码
        Session::delete($key);

        // 生成新验证码
        return self::generateImageCaptcha($width, $height, $length);
    }
}
