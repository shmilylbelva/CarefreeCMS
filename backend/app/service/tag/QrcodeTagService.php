<?php
namespace app\service\tag;

/**
 * 二维码标签服务类
 * 处理二维码生成标签的数据查询
 */
class QrcodeTagService
{
    /**
     * 生成二维码
     *
     * @param array $params 查询参数
     *   - content: 二维码内容
     *   - size: 尺寸（默认200）
     *   - logo: Logo图片路径
     *   - level: 容错级别（L, M, Q, H）
     * @return string 二维码图片URL或Base64
     */
    public static function generate($params = [])
    {
        $content = $params['content'] ?? '';
        $size = $params['size'] ?? 200;
        $logo = $params['logo'] ?? '';
        $level = $params['level'] ?? 'M';

        if (empty($content)) {
            return '';
        }

        try {
            // 使用第三方API生成二维码（这里使用一个公共API作为示例）
            // 在实际项目中，建议使用 endroid/qr-code 等库进行本地生成

            // 方法1: 使用API生成
            $apiUrl = 'https://api.qrserver.com/v1/create-qr-code/';
            $params = [
                'data' => urlencode($content),
                'size' => $size . 'x' . $size,
                'ecc' => $level
            ];

            $qrUrl = $apiUrl . '?' . http_build_query($params);

            return $qrUrl;

            // 方法2: 如果项目中安装了 endroid/qr-code 库，可以使用以下代码
            /*
            if (class_exists('\Endroid\QrCode\QrCode')) {
                $qrCode = new \Endroid\QrCode\QrCode($content);
                $qrCode->setSize($size);
                $qrCode->setMargin(10);

                // 设置容错级别
                switch ($level) {
                    case 'L':
                        $qrCode->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::LOW);
                        break;
                    case 'M':
                        $qrCode->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::MEDIUM);
                        break;
                    case 'Q':
                        $qrCode->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::QUARTILE);
                        break;
                    case 'H':
                        $qrCode->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::HIGH);
                        break;
                }

                // 添加Logo
                if (!empty($logo) && file_exists($logo)) {
                    $qrCode->setLogoPath($logo);
                    $qrCode->setLogoSize($size / 5);
                }

                // 生成Base64图片
                $qrCode->setWriterByName('png');
                return 'data:image/png;base64,' . base64_encode($qrCode->writeString());
            }
            */
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * 批量生成二维码
     *
     * @param array $contents 内容数组
     * @param int $size 尺寸
     * @return array
     */
    public static function generateBatch($contents, $size = 200)
    {
        $result = [];

        foreach ($contents as $key => $content) {
            $result[$key] = self::generate([
                'content' => $content,
                'size' => $size
            ]);
        }

        return $result;
    }

    /**
     * 生成vCard二维码
     *
     * @param array $contact 联系人信息
     * @return string
     */
    public static function generateVCard($contact)
    {
        $vcard = "BEGIN:VCARD\n";
        $vcard .= "VERSION:3.0\n";

        if (!empty($contact['name'])) {
            $vcard .= "FN:" . $contact['name'] . "\n";
        }

        if (!empty($contact['org'])) {
            $vcard .= "ORG:" . $contact['org'] . "\n";
        }

        if (!empty($contact['title'])) {
            $vcard .= "TITLE:" . $contact['title'] . "\n";
        }

        if (!empty($contact['tel'])) {
            $vcard .= "TEL:" . $contact['tel'] . "\n";
        }

        if (!empty($contact['email'])) {
            $vcard .= "EMAIL:" . $contact['email'] . "\n";
        }

        if (!empty($contact['url'])) {
            $vcard .= "URL:" . $contact['url'] . "\n";
        }

        if (!empty($contact['address'])) {
            $vcard .= "ADR:" . $contact['address'] . "\n";
        }

        $vcard .= "END:VCARD";

        return self::generate(['content' => $vcard]);
    }

    /**
     * 生成WiFi二维码
     *
     * @param string $ssid WiFi名称
     * @param string $password 密码
     * @param string $security 加密类型（WPA, WEP, nopass）
     * @return string
     */
    public static function generateWiFi($ssid, $password, $security = 'WPA')
    {
        $wifiString = "WIFI:T:{$security};S:{$ssid};P:{$password};;";

        return self::generate(['content' => $wifiString]);
    }

    /**
     * 生成地理位置二维码
     *
     * @param float $latitude 纬度
     * @param float $longitude 经度
     * @param string $label 地点名称
     * @return string
     */
    public static function generateLocation($latitude, $longitude, $label = '')
    {
        $locationString = "geo:{$latitude},{$longitude}";

        if (!empty($label)) {
            $locationString .= "?q={$label}";
        }

        return self::generate(['content' => $locationString]);
    }

    /**
     * 生成电话二维码
     *
     * @param string $phone 电话号码
     * @return string
     */
    public static function generatePhone($phone)
    {
        return self::generate(['content' => "tel:{$phone}"]);
    }

    /**
     * 生成短信二维码
     *
     * @param string $phone 电话号码
     * @param string $message 短信内容
     * @return string
     */
    public static function generateSMS($phone, $message = '')
    {
        $smsString = "smsto:{$phone}";

        if (!empty($message)) {
            $smsString .= ":{$message}";
        }

        return self::generate(['content' => $smsString]);
    }

    /**
     * 生成邮件二维码
     *
     * @param string $email 邮箱地址
     * @param string $subject 主题
     * @param string $body 内容
     * @return string
     */
    public static function generateEmail($email, $subject = '', $body = '')
    {
        $emailString = "mailto:{$email}";

        $params = [];
        if (!empty($subject)) {
            $params['subject'] = $subject;
        }
        if (!empty($body)) {
            $params['body'] = $body;
        }

        if (!empty($params)) {
            $emailString .= '?' . http_build_query($params);
        }

        return self::generate(['content' => $emailString]);
    }

    /**
     * 保存二维码到文件
     *
     * @param string $content 内容
     * @param string $filepath 文件路径
     * @param int $size 尺寸
     * @return bool
     */
    public static function saveToFile($content, $filepath, $size = 200)
    {
        try {
            $qrUrl = self::generate([
                'content' => $content,
                'size' => $size
            ]);

            // 下载二维码图片
            $imageData = file_get_contents($qrUrl);

            if ($imageData !== false) {
                // 确保目录存在
                $dir = dirname($filepath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                return file_put_contents($filepath, $imageData) !== false;
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
