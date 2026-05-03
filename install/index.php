<?php
session_start();

$install_dir = __DIR__;
$txt_files = glob($install_dir . '/*.txt');
if (!empty($txt_files)) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $action = $_POST['action'] ?? '';

    if ($action === 'check_environment') {
        $results = [];

        if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
            $results['php'] = ['status' => 'success', 'message' => 'PHP版本: ' . PHP_VERSION];
        } else {
            $results['php'] = ['status' => 'error', 'message' => 'PHP版本过低，需要7.4.0以上，当前: ' . PHP_VERSION];
        }

        if (extension_loaded('mysqli')) {
            $results['mysqli'] = ['status' => 'success', 'message' => 'mysqli扩展已启用'];
        } else {
            $results['mysqli'] = ['status' => 'error', 'message' => 'mysqli扩展未启用'];
        }

        if (extension_loaded('curl')) {
            $results['curl'] = ['status' => 'success', 'message' => 'curl扩展已启用'];
        } else {
            $results['curl'] = ['status' => 'error', 'message' => 'curl扩展未启用'];
        }

        if (extension_loaded('zip')) {
            $results['zip'] = ['status' => 'success', 'message' => 'zip扩展已启用'];
        } else {
            $results['zip'] = ['status' => 'error', 'message' => 'zip扩展未启用'];
        }

        $root_path = dirname(__DIR__);
        if (is_writable($root_path)) {
            $results['writable'] = ['status' => 'success', 'message' => '网站根目录可写入'];
        } else {
            $results['writable'] = ['status' => 'error', 'message' => '网站根目录不可写入'];
        }

        echo json_encode(['status' => 'success', 'results' => $results]);
        exit;
    }

    if ($action === 'install') {
        header('Content-Type: application/json; charset=utf-8');

        $db_host = trim($_POST['db_host'] ?? '');
        $db_username = trim($_POST['db_username'] ?? '');
        $db_password = trim($_POST['db_password'] ?? '');
        $db_name = trim($_POST['db_name'] ?? '');
        $site_title = trim($_POST['site_title'] ?? '');
        $admin_username = trim($_POST['admin_username'] ?? '');
        $admin_password = trim($_POST['admin_password'] ?? '');
        $admin_password_confirm = trim($_POST['admin_password_confirm'] ?? '');

        if (empty($db_host) || empty($db_username) || empty($db_name) || empty($site_title) || empty($admin_username) || empty($admin_password)) {
            echo json_encode(['status' => 'error', 'message' => '请填写所有必填项']);
            exit;
        }

        if ($admin_password !== $admin_password_confirm) {
            echo json_encode(['status' => 'error', 'message' => '两次输入的密码不一致']);
            exit;
        }

        if (strlen($admin_password) < 6) {
            echo json_encode(['status' => 'error', 'message' => '后台密码至少6位']);
            exit;
        }

        $conn = @new mysqli($db_host, $db_username, $db_password, $db_name);
        if ($conn->connect_error) {
            echo json_encode(['status' => 'error', 'message' => '数据库连接失败: ' . $conn->connect_error]);
            exit;
        }
        $conn->set_charset('utf8mb4');

        $sql_file = __DIR__ . '/localhost.sql';
        if (!file_exists($sql_file)) {
            echo json_encode(['status' => 'error', 'message' => '数据库文件不存在']);
            exit;
        }

        $sql_content = file_get_contents($sql_file);
        $sql_content = str_replace('`cs_ch1633_com`', "`" . $db_name . "`", $sql_content);

        $sql_content = preg_replace('/--.*$/m', '', $sql_content);
        $sql_content = preg_replace('/\/\*.*?\*\//s', '', $sql_content);

        $statements = array_filter(array_map('trim', explode(';', $sql_content)), function($stmt) {
            return !empty($stmt) && stripos($stmt, 'CREATE DATABASE') === false;
        });

        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && stripos($statement, 'CREATE DATABASE') === false && stripos($statement, 'USE `') === false) {
                if (stripos($statement, 'DROP TABLE') !== false || stripos($statement, 'CREATE TABLE') !== false ||
                    stripos($statement, 'INSERT INTO') !== false || stripos($statement, 'ALTER TABLE') !== false) {
                    $conn->query($statement);
                }
            }
        }

        $admin_password_hash = md5($admin_password);
        $check_user = $conn->query("SELECT id FROM users WHERE username = '" . $conn->real_escape_string($admin_username) . "'");
        if ($check_user->num_rows > 0) {
            $conn->query("UPDATE users SET password = '" . $conn->real_escape_string($admin_password_hash) . "' WHERE username = '" . $conn->real_escape_string($admin_username) . "'");
        } else {
            $conn->query("INSERT INTO users (username, password, role) VALUES ('" . $conn->real_escape_string($admin_username) . "', '" . $conn->real_escape_string($admin_password_hash) . "', 'admin')");
        }

        $db_config_content = '<?php
$servername = "' . addslashes($db_host) . '";
$username = "' . addslashes($db_username) . '";
$password = "' . addslashes($db_password) . '";
$dbname = "' . addslashes($db_name) . '";
$charset = "utf8mb4";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("数据库连接失败: " . $conn->connect_error);
}

$conn->set_charset($charset);
?>';

        if (file_put_contents(dirname(__DIR__) . '/db_config.php', $db_config_content) === false) {
            echo json_encode(['status' => 'error', 'message' => '写入数据库配置文件失败']);
            exit;
        }

        $site_subtitle = "云天安全提供专业的网站安全检测服务，通过对域名、网站、备案登记信息进行审核，验证网站真实身份。";
        $footer_text = "© " . date('Y') . " " . addslashes($site_title) . " 版权所有";
        $icp_number = "鄂ICP备2024041348号-2";
        $service_phone = "130-4710-6211";
        $contact_email = "i@ch1633.com";

        $config_content = '<?php
require_once "db_config.php";

$software_version = "v1.0.9";
$update_check_url = "";
$auth_code = "";

$site_title = "' . addslashes($site_title) . '";
$site_subtitle = "' . addslashes($site_subtitle) . '";
$footer_text = "' . addslashes($footer_text) . '";
$icp_number = "' . addslashes($icp_number) . '";
$service_phone = "' . addslashes($service_phone) . '";
$contact_email = "' . addslashes($contact_email) . '";
$footer_html = "";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$mail_config = array(
    "smtp_host" => "smtp.qiye.163.com",
    "smtp_port" => 465,
    "smtp_secure" => "ssl",
    "smtp_username" => "",
    "smtp_password" => "",
    "smtp_from" => "",
    "smtp_from_name" => ""
);

function send_email($to, $subject, $message) {
    global $mail_config;

    if (empty($mail_config["smtp_username"]) || empty($mail_config["smtp_password"])) {
        return false;
    }

    $smtp_host = $mail_config["smtp_host"];
    $smtp_port = $mail_config["smtp_port"];
    $smtp_username = $mail_config["smtp_username"];
    $smtp_password = $mail_config["smtp_password"];
    $smtp_from = $mail_config["smtp_from"];
    $smtp_from_name = $mail_config["smtp_from_name"];

    $context = stream_context_create([
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
            "allow_self_signed" => true
        ]
    ]);

    $smtp_conn = stream_socket_client("ssl://" . $smtp_host . ":" . $smtp_port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);

    if (!$smtp_conn) {
        error_log("SMTP连接失败: " . $errstr . " (" . $errno . ")");
        return false;
    }

    stream_set_timeout($smtp_conn, 10);

    $response = fgets($smtp_conn, 4096);

    fputs($smtp_conn, "EHLO " . $_SERVER["SERVER_NAME"] . "\r\n");
    usleep(50000);
    $response = "";
    while (!feof($smtp_conn)) {
        $line = fgets($smtp_conn, 4096);
        $response .= $line;
        if (substr($line, 3, 1) != "-") break;
    }

    fputs($smtp_conn, "AUTH LOGIN\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);

    fputs($smtp_conn, base64_encode($smtp_username) . "\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);

    fputs($smtp_conn, base64_encode($smtp_password) . "\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);

    if (substr(trim($response), 0, 3) != "235") {
        fclose($smtp_conn);
        return false;
    }

    fputs($smtp_conn, "MAIL FROM: <" . $smtp_from . ">\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);

    fputs($smtp_conn, "RCPT TO: <" . $to . ">\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);

    fputs($smtp_conn, "DATA\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);

    $headers = "From: " . $smtp_from_name . " <" . $smtp_from . ">\r\n";
    $headers .= "To: " . $to . "\r\n";
    $headers .= "Subject: " . $subject . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "\r\n";

    fputs($smtp_conn, "Date: " . date("r") . "\r\n");
    fputs($smtp_conn, $headers . "\r\n" . $message . "\r\n");
    fputs($smtp_conn, ".\r\n");
    usleep(50000);
    $response = fgets($smtp_conn, 4096);

    fclose($smtp_conn);
    return true;
}
?>';

        if (file_put_contents(dirname(__DIR__) . '/config.php', $config_content) === false) {
            echo json_encode(['status' => 'error', 'message' => '写入配置文件失败']);
            exit;
        }

        $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

        $install_dir = __DIR__;
        $txt_file = $install_dir . '/installed_' . date('YmdHis') . '.txt';
        $txt_content = "部署完成时间: " . date('Y-m-d H:i:s') . "\n网站域名: " . $protocol . '://' . $domain;
        file_put_contents($txt_file, $txt_content);

        echo json_encode([
            'status' => 'success',
            'message' => '安装成功',
            'data' => [
                'site_url' => $protocol . '://' . $domain,
                'admin_url' => $protocol . '://' . $domain . '/admin/',
                'admin_username' => $admin_username,
                'admin_password' => $admin_password
            ]
        ]);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => '未知的操作']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>云天安全 - 安装向导</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header p { opacity: 0.9; font-size: 14px; }
        .content { padding: 40px; }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        .step { display: flex; align-items: center; }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #ddd;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        .step.active .step-number { background: #667eea; color: white; }
        .step.completed .step-number { background: #4CAF50; color: white; }
        .step-line { width: 60px; height: 2px; background: #ddd; margin: 0 10px; }
        .step.active .step-line, .step.completed + .step .step-line { background: #667eea; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 500; }
        .form-group label span { color: #f44336; }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .form-group small { color: #999; font-size: 12px; margin-top: 5px; display: block; }
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-secondary { background: #f5f5f5; color: #666; }
        .btn-secondary:hover { background: #e0e0e0; box-shadow: none; }
        .message { padding: 15px; border-radius: 10px; margin-bottom: 20px; display: none; }
        .message.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .message.success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .check-list { margin-top: 20px; }
        .check-item { display: flex; align-items: center; padding: 10px; border-radius: 8px; margin-bottom: 8px; }
        .check-item.success { background: #e8f5e9; }
        .check-item.error { background: #ffebee; }
        .check-icon {
            width: 24px; height: 24px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin-right: 12px; font-size: 14px;
        }
        .check-item.success .check-icon { background: #4CAF50; color: white; }
        .check-item.error .check-icon { background: #f44336; color: white; }
        .success-box { text-align: center; padding: 20px; }
        .success-box h2 { color: #4CAF50; margin-bottom: 20px; }
        .success-box .info { background: #f5f5f5; padding: 20px; border-radius: 10px; text-align: left; margin-bottom: 20px; }
        .success-box .info p { margin-bottom: 10px; }
        .success-box .info strong { color: #667eea; }
        .hidden { display: none; }
        .loading { text-align: center; padding: 40px; }
        .spinner {
            width: 50px; height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        h3 { margin: 20px 0 15px; color: #667eea; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>云天安全 - 安装向导</h1>
            <p>快速部署您的网站安全检测系统</p>
        </div>
        <div class="content">
            <div class="step-indicator">
                <div class="step active" id="step1-indicator"><div class="step-number">1</div></div>
                <div class="step-line"></div>
                <div class="step" id="step2-indicator"><div class="step-number">2</div></div>
                <div class="step-line"></div>
                <div class="step" id="step3-indicator"><div class="step-number">3</div></div>
            </div>

            <div id="step1" class="step-content">
                <h2 style="margin-bottom: 20px; color: #333;">环境检测</h2>
                <div id="env-message" class="message"></div>
                <div id="check-list" class="check-list"></div>
                <button class="btn" id="check-env-btn" onclick="checkEnvironment()">开始检测</button>
            </div>

            <div id="step2" class="step-content hidden">
                <h2 style="margin-bottom: 20px; color: #333;">配置信息</h2>
                <div id="install-message" class="message"></div>
                <form id="install-form">
                    <h3>数据库设置</h3>
                    <div class="form-group">
                        <label>数据库主机 <span>*</span></label>
                        <input type="text" name="db_host" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label>数据库用户名 <span>*</span></label>
                        <input type="text" name="db_username" required>
                    </div>
                    <div class="form-group">
                        <label>数据库密码</label>
                        <input type="password" name="db_password">
                    </div>
                    <div class="form-group">
                        <label>数据库名称 <span>*</span></label>
                        <input type="text" name="db_name" required>
                        <small>如果数据库不存在，系统将尝试创建</small>
                    </div>
                    <h3>网站设置</h3>
                    <div class="form-group">
                        <label>网站标题 <span>*</span></label>
                        <input type="text" name="site_title" value="云天安全" required>
                    </div>
                    <h3>后台管理员</h3>
                    <div class="form-group">
                        <label>管理员账号 <span>*</span></label>
                        <input type="text" name="admin_username" value="admin" required>
                    </div>
                    <div class="form-group">
                        <label>管理员密码 <span>*</span></label>
                        <input type="password" name="admin_password" minlength="6" required>
                        <small>至少6位字符</small>
                    </div>
                    <div class="form-group">
                        <label>确认密码 <span>*</span></label>
                        <input type="password" name="admin_password_confirm" minlength="6" required>
                    </div>
                    <button type="submit" class="btn" id="install-btn">开始安装</button>
                </form>
            </div>

            <div id="step3" class="step-content hidden">
                <div class="success-box">
                    <h2>✓ 安装成功！</h2>
                    <div class="info" id="result-info"></div>
                    <p style="color: #666; margin-bottom: 20px;">请妥善保管上述账号信息</p>
                    <a href="../index.php" class="btn">访问前台</a>
                    <a href="../admin/" class="btn btn-secondary" style="margin-top: 10px;">访问后台</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function checkEnvironment() {
            const btn = document.getElementById('check-env-btn');
            const message = document.getElementById('env-message');
            const checkList = document.getElementById('check-list');

            btn.disabled = true;
            btn.textContent = '检测中...';
            message.style.display = 'none';
            checkList.innerHTML = '<div class="loading"><div class="spinner"></div><p>正在检测环境...</p></div>';

            fetch('index.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=check_environment'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    let allSuccess = true;
                    checkList.innerHTML = '';
                    for (const [key, item] of Object.entries(data.results)) {
                        if (item.status !== 'success') allSuccess = false;
                        const div = document.createElement('div');
                        div.className = 'check-item ' + item.status;
                        div.innerHTML = '<div class="check-icon">' + (item.status === 'success' ? '✓' : '✗') + '</div><div>' + item.message + '</div>';
                        checkList.appendChild(div);
                    }
                    if (allSuccess) {
                        message.className = 'message success';
                        message.textContent = '环境检测通过！点击下一步继续。';
                        message.style.display = 'block';
                        btn.textContent = '下一步';
                        btn.disabled = false;
                        btn.onclick = goToStep2;
                        document.getElementById('step1-indicator').classList.add('completed');
                    } else {
                        message.className = 'message error';
                        message.textContent = '环境检测未通过，请修复上述问题后重试。';
                        message.style.display = 'block';
                        btn.textContent = '重新检测';
                        btn.disabled = false;
                    }
                }
            })
            .catch(error => {
                message.className = 'message error';
                message.textContent = '检测失败: ' + error;
                message.style.display = 'block';
                btn.textContent = '重新检测';
                btn.disabled = false;
            });
        }

        function goToStep2() {
            document.getElementById('step1').classList.add('hidden');
            document.getElementById('step2').classList.remove('hidden');
            document.getElementById('step2-indicator').classList.add('active');
        }

        document.getElementById('install-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('install-btn');
            const message = document.getElementById('install-message');
            const formData = new FormData(this);
            formData.append('action', 'install');

            btn.disabled = true;
            btn.textContent = '安装中...';
            message.style.display = 'none';

            fetch('index.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('step2').classList.add('hidden');
                    document.getElementById('step3').classList.remove('hidden');
                    document.getElementById('step3-indicator').classList.add('completed', 'active');
                    document.getElementById('step2-indicator').classList.add('completed');
                    document.getElementById('result-info').innerHTML = '<p><strong>前台地址：</strong>' + data.data.site_url + '</p>' +
                        '<p><strong>后台地址：</strong>' + data.data.admin_url + '</p>' +
                        '<p><strong>管理员账号：</strong>' + data.data.admin_username + '</p>' +
                        '<p><strong>管理员密码：</strong>' + data.data.admin_password + '</p>';
                } else {
                    message.className = 'message error';
                    message.textContent = data.message;
                    message.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = '开始安装';
                }
            })
            .catch(error => {
                message.className = 'message error';
                message.textContent = '安装失败: ' + error;
                message.style.display = 'block';
                btn.disabled = false;
                btn.textContent = '开始安装';
            });
        });
    </script>
</body>
</html>
