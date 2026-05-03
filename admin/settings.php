<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

function checkDomainAuthorization($domain) {
    global $software_version;
    
    $api_url = 'https://kf.ch1633.com/api.php?action=report_version&version=' . urlencode($software_version) . '&domain=' . urlencode($domain);
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 && !empty($response)) {
        $data = json_decode($response, true);
        if (is_array($data) && isset($data['status']) && $data['status'] == 'success') {
            return true;
        }
    }
    return false;
}

$current_domain = $_SERVER['HTTP_HOST'];
$is_authorized = checkDomainAuthorization($current_domain);

$message = '';
$message_type = '';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'title';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_authorized) {
        $message = '当前站点未授权，无法修改系统设置！';
        $message_type = 'error';
    } else {
        $action = $_POST['action'] ?? '';
    
    if ($action === 'password') {
        $old_username = $_SESSION['username'];
        $new_username = $_POST['username'];
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $sql = "SELECT password FROM users WHERE username = '$old_username'";
        $result = $conn->query($sql);
        $user = $result->fetch_assoc();

        if (md5($old_password) !== $user['password']) {
            $message = '原密码错误';
            $message_type = 'error';
        } elseif (!empty($new_password) && $new_password !== $confirm_password) {
            $message = '两次输入的新密码不一致';
            $message_type = 'error';
        } elseif (!empty($new_password) && strlen($new_password) < 6) {
            $message = '新密码长度至少为6位';
            $message_type = 'error';
        } else {
            $update_parts = array();
            if ($new_username !== $old_username) {
                $update_parts[] = "username = '" . $conn->real_escape_string($new_username) . "'";
            }
            if (!empty($new_password)) {
                $hashed_password = md5($new_password);
                $update_parts[] = "password = '$hashed_password'";
            }
            
            if (!empty($update_parts)) {
                $sql = "UPDATE users SET " . implode(', ', $update_parts) . " WHERE username = '$old_username'";
                if ($conn->query($sql) === TRUE) {
                    if ($new_username !== $old_username) {
                        $_SESSION['username'] = $new_username;
                    }
                    $message = '修改成功！';
                    $message_type = 'success';
                } else {
                    $message = '修改失败：' . $conn->error;
                    $message_type = 'error';
                }
            } else {
                $message = '没有进行任何修改';
                $message_type = 'error';
            }
        }
        $active_tab = 'password';
    }
    
    if ($action === 'title') {
        $site_title = $_POST['site_title'];
        $site_subtitle = $_POST['site_subtitle'];
        
        $config_file = '../config.php';
        $content = file_get_contents($config_file);
        
        if (preg_match('/\$site_title\s*=/', $content)) {
            $content = preg_replace('/\$site_title\s*=\s*["\']([^"\']*)["\'];/', '$site_title = "' . addslashes($site_title) . '";', $content);
        } else {
            $content = preg_replace('/<\?php/', '<?php' . "\n" . '$site_title = "' . addslashes($site_title) . '";', $content);
        }
        
        if (preg_match('/\$site_subtitle\s*=/', $content)) {
            $content = preg_replace('/\$site_subtitle\s*=\s*["\']([^"\']*)["\'];/', '$site_subtitle = "' . addslashes($site_subtitle) . '";', $content);
        } else {
            $content = preg_replace('/<\?php/', '<?php' . "\n" . '$site_subtitle = "' . addslashes($site_subtitle) . '";', $content);
        }
        
        file_put_contents($config_file, $content);
        
        $message = '网站标题修改成功！';
        $message_type = 'success';
        $active_tab = 'title';
    }
    
    if ($action === 'footer') {
        $footer_text = $_POST['footer_text'];
        $icp_number = $_POST['icp_number'];
        $service_phone = $_POST['service_phone'];
        
        $config_file = '../config.php';
        $content = file_get_contents($config_file);
        
        $content = preg_replace('/\$footer_text\s*=\s*["\']([^"\']*)["\'];/', '$footer_text = "' . addslashes($footer_text) . '";', $content);
        $content = preg_replace('/\$icp_number\s*=\s*["\']([^"\']*)["\'];/', '$icp_number = "' . addslashes($icp_number) . '";', $content);
        $content = preg_replace('/\$service_phone\s*=\s*["\']([^"\']*)["\'];/', '$service_phone = "' . addslashes($service_phone) . '";', $content);
        
        file_put_contents($config_file, $content);
        
        $message = '页脚设置修改成功！';
        $message_type = 'success';
        $active_tab = 'footer';
    }

    if ($action === 'test_email') {
        $test_email = $_POST['test_email'];
        $test_subject = '云天安全 - 邮箱配置测试';
        $test_message = '<p style="font-size: 15px; line-height: 1.8;">这是一封测试邮件，用于验证邮箱配置是否正确。</p><p style="font-size: 15px; line-height: 1.8;">如果您收到此邮件，说明邮箱配置成功！</p><p style="font-size: 15px; line-height: 1.8; margin-top: 20px;">发送时间：' . date('Y-m-d H:i:s') . '</p>';

        if (send_email($test_email, $test_subject, $test_message)) {
            $message = '测试邮件发送成功！请检查收件箱。';
            $message_type = 'success';
        } else {
            $message = '测试邮件发送失败，请检查邮箱配置。';
            $message_type = 'error';
        }
        $active_tab = 'email';
    }

    if ($action === 'email') {
        $smtp_host = $_POST['smtp_host'];
        $smtp_port = intval($_POST['smtp_port']);
        $smtp_secure = $_POST['smtp_secure'];
        $smtp_username = $_POST['smtp_username'];
        $smtp_password = $_POST['smtp_password'];
        $smtp_from = $_POST['smtp_from'];
        $smtp_from_name = $_POST['smtp_from_name'];
        
        $config_file = '../config.php';
        $content = file_get_contents($config_file);
        
        $start_pos = strpos($content, '$mail_config = array(');
        $end_pos = strpos($content, ');', $start_pos);
        
        if ($start_pos !== false && $end_pos !== false) {
            $old_mail_config = substr($content, $start_pos, $end_pos - $start_pos + 2);
            $new_mail_config = '$mail_config = array(
    \'smtp_host\' => \'' . addslashes($smtp_host) . '\',
    \'smtp_port\' => ' . $smtp_port . ',
    \'smtp_secure\' => \'' . addslashes($smtp_secure) . '\',
    \'smtp_username\' => \'' . addslashes($smtp_username) . '\',
    \'smtp_password\' => \'' . addslashes($smtp_password) . '\',
    \'smtp_from\' => \'' . addslashes($smtp_from) . '\',
    \'smtp_from_name\' => \'' . addslashes($smtp_from_name) . '\'
);';
            $content = str_replace($old_mail_config, $new_mail_config, $content);
            file_put_contents($config_file, $content);
            $message = '邮箱配置修改成功！';
            $message_type = 'success';
        } else {
            $message = '邮箱配置修改失败：未找到配置项';
            $message_type = 'error';
        }
        $active_tab = 'email';
    }
    }
}

// 重新读取配置文件以获取最新值
include_once '../config.php';

$site_title = isset($GLOBALS['site_title']) ? $GLOBALS['site_title'] : '云天安全网站检测系统';
$site_subtitle = isset($GLOBALS['site_subtitle']) ? $GLOBALS['site_subtitle'] : '动态安全网站检测平台';
$footer_text = isset($GLOBALS['footer_text']) ? $GLOBALS['footer_text'] : '© 2026 云天安全 版权所有';
$icp_number = isset($GLOBALS['icp_number']) ? $GLOBALS['icp_number'] : '';
$service_phone = isset($GLOBALS['service_phone']) ? $GLOBALS['service_phone'] : '';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统设置 - 云天后台管理系统</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>" type="image/svg+xml">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            padding-bottom: 80px;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        nav h1 {
            font-size: 24px;
            font-weight: 600;
        }
        
        nav .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            transition: all 0.3s ease;
            min-width: 90px;
            text-align: center;
        }
        
        nav a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        nav a.active {
            background: rgba(255,255,255,0.4);
        }
        
        nav span {
            font-size: 16px;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            min-width: 100px;
            text-align: center;
        }
        
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }
        
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .settings-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .settings-tabs a {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            color: #666;
            background: #f0f0f0;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .settings-tabs a:hover {
            background: #e0e0e0;
        }
        
        .settings-tabs a.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .settings-section {
            display: <?php echo $active_tab === 'password' ? 'block' : 'none'; ?>;
        }
        
        .settings-section.password { display: <?php echo $active_tab === 'password' ? 'block' : 'none'; ?>; }
        .settings-section.title { display: <?php echo $active_tab === 'title' ? 'block' : 'none'; ?>; }
        .settings-section.footer { display: <?php echo $active_tab === 'footer' ? 'block' : 'none'; ?>; }
        .settings-section.email { display: <?php echo $active_tab === 'email' ? 'block' : 'none'; ?>; }
        
        .settings-section h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 14px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-test {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
        }

        .btn-test:hover {
            box-shadow: 0 5px 20px rgba(39, 174, 96, 0.4);
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 20px;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }
        
        footer p {
            opacity: 0.8;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>云天后台管理系统</h1>
            <div class="user-info">
                <a href="system.php">系统信息</a>
                <a href="applications.php">申请管理</a>
                <a href="certificates.php">证书管理</a>
                <a href="settings.php" class="active">系统设置</a>
                <span>欢迎, <?php echo $_SESSION['username']; ?></span>
                <a href="logout.php">退出登录</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="settings-card">
            <div class="settings-tabs">
                <a href="?tab=title" class="<?php echo $active_tab === 'title' ? 'active' : ''; ?>">标题设置</a>
                <a href="?tab=footer" class="<?php echo $active_tab === 'footer' ? 'active' : ''; ?>">页脚设置</a>
                <a href="?tab=email" class="<?php echo $active_tab === 'email' ? 'active' : ''; ?>">邮箱配置</a>
                <a href="?tab=password" class="<?php echo $active_tab === 'password' ? 'active' : ''; ?>">修改密码</a>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="settings-section password">
                <h2>修改密码与用户名</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="password">
                    <div class="form-group">
                        <label for="username">用户名</label>
                        <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_SESSION['username']); ?>" placeholder="请输入用户名">
                    </div>
                    <div class="form-group">
                        <label for="old_password">原密码</label>
                        <input type="password" id="old_password" name="old_password" required placeholder="请输入原密码">
                    </div>
                    <div class="form-group">
                        <label for="new_password">新密码（留空则不修改）</label>
                        <input type="password" id="new_password" name="new_password" placeholder="请输入新密码（至少6位）">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">确认新密码</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="请再次输入新密码">
                    </div>
                    <button type="submit" class="btn">保存修改</button>
                </form>
            </div>
            
            <div class="settings-section title">
                <h2>标题设置</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="title">
                    <div class="form-group">
                        <label for="site_title">网站标题</label>
                        <input type="text" id="site_title" name="site_title" required value="<?php echo htmlspecialchars($site_title); ?>" placeholder="请输入网站标题">
                    </div>
                    <div class="form-group">
                        <label for="site_subtitle">网站副标题</label>
                        <input type="text" id="site_subtitle" name="site_subtitle" required value="<?php echo htmlspecialchars($site_subtitle); ?>" placeholder="请输入网站副标题">
                    </div>
                    <button type="submit" class="btn">保存标题</button>
                </form>
            </div>
            
            <div class="settings-section footer">
                <h2>页脚设置</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="footer">
                    <div class="form-group">
                        <label for="footer_text">页脚内容</label>
                        <input type="text" id="footer_text" name="footer_text" required value="<?php echo htmlspecialchars($footer_text); ?>" placeholder="请输入页脚内容">
                    </div>
                    <div class="form-group">
                        <label for="icp_number">ICP备案信息</label>
                        <input type="text" id="icp_number" name="icp_number" value="<?php echo htmlspecialchars($icp_number); ?>" placeholder="如：鄂ICP备2024041348号-2">
                    </div>
                    <div class="form-group">
                        <label for="service_phone">客服热线</label>
                        <input type="text" id="service_phone" name="service_phone" value="<?php echo htmlspecialchars($service_phone); ?>" placeholder="如：130-4710-6211">
                    </div>
                    <button type="submit" class="btn">保存页脚设置</button>
                </form>
            </div>
            
            <div class="settings-section email">
                <h2>邮箱配置</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="email">
                    <div class="form-group">
                        <label for="smtp_host">SMTP 服务器</label>
                        <input type="text" id="smtp_host" name="smtp_host" required value="<?php echo htmlspecialchars($mail_config['smtp_host']); ?>" placeholder="如：smtp.163.com">
                    </div>
                    <div class="form-group">
                        <label for="smtp_port">SMTP 端口</label>
                        <input type="number" id="smtp_port" name="smtp_port" required value="<?php echo htmlspecialchars($mail_config['smtp_port']); ?>" placeholder="如：465">
                    </div>
                    <div class="form-group">
                        <label for="smtp_secure">安全类型</label>
                        <select id="smtp_secure" name="smtp_secure">
                            <option value="ssl" <?php echo $mail_config['smtp_secure'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                            <option value="tls" <?php echo $mail_config['smtp_secure'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="smtp_username">用户名</label>
                        <input type="text" id="smtp_username" name="smtp_username" required value="<?php echo htmlspecialchars($mail_config['smtp_username']); ?>" placeholder="SMTP用户名">
                    </div>
                    <div class="form-group">
                        <label for="smtp_password">密码</label>
                        <input type="password" id="smtp_password" name="smtp_password" required value="<?php echo htmlspecialchars($mail_config['smtp_password']); ?>" placeholder="SMTP密码或授权码">
                    </div>
                    <div class="form-group">
                        <label for="smtp_from">发件人邮箱</label>
                        <input type="email" id="smtp_from" name="smtp_from" required value="<?php echo htmlspecialchars($mail_config['smtp_from']); ?>" placeholder="发件人邮箱地址">
                    </div>
                    <div class="form-group">
                        <label for="smtp_from_name">发件人名称</label>
                        <input type="text" id="smtp_from_name" name="smtp_from_name" required value="<?php echo htmlspecialchars($mail_config['smtp_from_name']); ?>" placeholder="显示的发件人名称">
                    </div>
                    <button type="submit" class="btn">保存邮箱配置</button>
                </form>
                <hr style="margin: 30px 0; border: none; border-top: 1px dashed #ddd;">
                <h3>测试邮箱发送</h3>
                <form method="POST" style="margin-top: 15px;">
                    <input type="hidden" name="action" value="test_email">
                    <div class="form-group">
                        <label for="test_email">收件邮箱</label>
                        <input type="email" id="test_email" name="test_email" required placeholder="请输入测试收件邮箱地址">
                    </div>
                    <button type="submit" class="btn btn-test">发送测试邮件</button>
                </form>
            </div>
        </div>
    </div>

    <footer>
        <p><?php echo isset($footer_text) ? htmlspecialchars($footer_text) : '© 2026 云天安全 版权所有'; ?></p>
    </footer>
</body>
</html>