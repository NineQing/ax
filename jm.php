<?php
// 获取用户通过 GET 方法提交的 'url' 参数
// 如果存在 'url' 参数则赋值给 $url，否则设为 null
$url = isset($_GET['url'])? $_GET['url'] : null;

// 验证 $url 是否为有效的 URL
if ($url && filter_var($url, FILTER_VALIDATE_URL)) {
    // 如果是有效的 URL，则使用该 URL 进行后续处理
} else {
    // 如果不是有效的 URL，尝试从 $_GET 参数中提取 URL
    preg_match('/https?://[-A-Za-z0-9+&@#/%?=~_|!:,.;]+[-A-Za-z0-9+&@#/%=~_|]/', $_GET, $matches);
    // 获取提取到的 URL
    $extracted_url = $matches[0]?? null;
    if ($extracted_url) {
        // 如果成功提取到 URL，则使用提取的 URL
        $url = $extracted_url;
    } else {
        // 如果既没有有效的输入 URL，也没有提取到 URL，则终止程序并输出提示信息
        die('请提供有效的 URL 参数或确保 GET 参数中包含可提取的 URL。');
    }
}

// 检查 URL 是否包含.com，以进一步确认是一个可能有效的网址
if ($url && strstr($url, "http")) {
    // 定义一个函数，用于获取最终的重定向 URL
    function getRealUrl($url) {
        // 获取 URL 的所有响应头信息，第二个参数 1 表示以关联数组形式返回
        $headers = get_headers($url, 1);
        // 定义重定向状态码数组
        $redirectCodes = ['301', '302'];
        // 遍历重定向状态码数组
        foreach ($redirectCodes as $code) {
            // 检查响应头中是否包含重定向状态码
            if (strpos($headers[0], $code)!== false) {
                // 如果 'Location' 头信息是一个数组（可能存在多次重定向）
                if (is_array($headers['Location'])) {
                    // 返回最后一个重定向的 URL
                    return end($headers['Location']);
                } else {
                    // 如果 'Location' 头信息是一个字符串，直接返回该 URL
                    return $headers['Location'];
                }
            }
        }
        // 如果没有检测到重定向，则返回原始 URL
        return $url;
    }

    // 获取最终的重定向 URL
    $url = getRealUrl($url);

    // 设置模拟电脑的用户代理字符串，模拟 Chrome 浏览器
    $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';

    // 设置输出内容类型为 JSON
    header('Content-type:text/json');

    // 初始化 cURL 会话
    $ch = curl_init();
    // 设置要请求的 URL
    curl_setopt($ch, CURLOPT_URL, $url);
    // 不返回响应头信息
    curl_setopt($ch, CURLOPT_HEADER, false);
    // 将 curl_exec() 获取的信息以字符串形式返回，而不是直接输出
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 禁用 SSL 证书验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    // 不验证 SSL 主机名
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    // 自动处理所有编码类型
    curl_setopt($ch, CURLOPT_ENCODING, true);
    // 设置用户代理
    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
    // 启用跟踪重定向
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    // 执行 cURL 请求并获取响应数据，同时解码 URL 转义字符
    $data = curl_exec($ch);
    // 关闭 cURL 会话
    curl_close($ch);

    if ($data) {
        $keys = ['ahsp123456789012', 'asdfghjklmnbvcxz', 'qwertyuiopasdfgh'];
        foreach ($keys as $key) {
            $decrypted = openssl_decrypt($data, 'AES-128-ECB', $key, 0);
            if ($decrypted!== false) {
                try {
                    $decoded = json_decode($decrypted, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        // 使用JSON_UNESCAPED_UNICODE和JSON_UNESCAPED_SLASHES选项
                        echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    } else {
                        // 如果解密后的数据不是有效的JSON格式，直接输出解密后的数据
                        echo $decrypted;
                    }
                } catch (Exception $e) {
                    // 如果json_decode抛出异常，直接输出解密后的数据
                    echo $decrypted;
                }
                return;
            }
        }
        die('解密失败');
    } else {
        // 如果没有获取到数据，则输出提示信息
        echo '请输入网址';
    }
}
?>
