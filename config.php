<?php
require_once 'db_config.php';

$software_version = "v1.0.9";
$update_check_url = "";
$auth_code = "";

$site_title = "云天安全";
$site_subtitle = "云天安全提供专业的网站安全检测服务，通过对域名、网站、备案登记信息进行审核，验证网站真实身份。";
$footer_text = "© 2026 云天安全 版权所有";
$icp_number = "鄂ICP备2024041348号-2";
$service_phone = "130-4710-6211";
$contact_email = "i@ch1633.com";
$footer_html = "";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$mail_config = array(
    'smtp_host' => 'smtp.qiye.163.com',
    'smtp_port' => 465,
    'smtp_secure' => 'ssl',
    'smtp_username' => '123',
    'smtp_password' => '123',
    'smtp_from' => '123@qq.com',
    'smtp_from_name' => '123'
);

function send_email($to, $subject, $message) {
    global $mail_config;

    $smtp_host = $mail_config['smtp_host'];
    $smtp_port = $mail_config['smtp_port'];
    $smtp_secure = $mail_config['smtp_secure'] ?? 'ssl';
    $smtp_username = $mail_config['smtp_username'];
    $smtp_password = $mail_config['smtp_password'];
    $smtp_from = $mail_config['smtp_from'];
    $smtp_from_name = $mail_config['smtp_from_name'];

    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    if ($smtp_secure === 'ssl') {
        $smtp_conn = @stream_socket_client("ssl://$smtp_host:$smtp_port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
    } else {
        $smtp_conn = @stream_socket_client("tcp://$smtp_host:$smtp_port", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
    }

    if (!$smtp_conn) {
        error_log("SMTP连接失败: $errstr ($errno)");
        return false;
    }

    stream_set_timeout($smtp_conn, 10);

    $response = fgets($smtp_conn, 4096);
    error_log("SMTP连接响应: " . trim($response));

    fputs($smtp_conn, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
    usleep(50000);
    $response = '';
    while (!feof($smtp_conn)) {
        $line = fgets($smtp_conn, 4096);
        $response .= $line;
        if (substr($line, 3, 1) != '-') break;
    }
    error_log("EHLO响应: " . trim($response));

    if ($smtp_secure === 'tls') {
        fputs($smtp_conn, "STARTTLS\r\n");
        usleep(50000);
        $response = fgets($smtp_conn, 4096);
        error_log("STARTTLS响应: " . trim($response));

        if (substr(trim($response), 0, 3) == '220') {
            stream_socket_enable_crypto($smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            usleep(100000);

            fputs($smtp_conn, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
            usleep(50000);
            $response = '';
            while (!feof($smtp_conn)) {
                $line = fgets($smtp_conn, 4096);
                $response .= $line;
                if (substr($line, 3, 1) != '-') break;
            }
            error_log("TLS EHLO响应: " . trim($response));
        }
    }

    fputs($smtp_conn, "AUTH LOGIN\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);
    error_log("AUTH LOGIN响应: " . trim($response));

    fputs($smtp_conn, base64_encode($smtp_username) . "\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);
    error_log("用户名响应: " . trim($response));

    fputs($smtp_conn, base64_encode($smtp_password) . "\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);
    error_log("密码响应: " . trim($response));

    if (substr(trim($response), 0, 3) != '235') {
        error_log("认证失败，响应: " . trim($response));
        fclose($smtp_conn);
        return false;
    }

    fputs($smtp_conn, "MAIL FROM: <$smtp_from>\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);
    error_log("MAIL FROM响应: " . trim($response));

    fputs($smtp_conn, "RCPT TO: <$to>\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);
    error_log("RCPT TO响应: " . trim($response));

    fputs($smtp_conn, "DATA\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);
    error_log("DATA响应: " . trim($response));

    $headers = "From: " . mb_encode_mimeheader($smtp_from_name, 'UTF-8') . " <$smtp_from>\r\n";
    $headers .= "Reply-To: $smtp_from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $subject_encoded = mb_encode_mimeheader($subject, 'UTF-8');
    $email_data = "Subject: $subject_encoded\r\n$headers\r\n$message\r\n.\r\n";

    fputs($smtp_conn, $email_data);
    usleep(100000);
    $response = fgets($smtp_conn, 4096);
    error_log("发送响应: " . trim($response));

    fputs($smtp_conn, "QUIT\r\n");
    usleep(50000);
    fgets($smtp_conn, 4096);

    fclose($smtp_conn);

    error_log("邮件发送成功！");
    return true;
}
?>