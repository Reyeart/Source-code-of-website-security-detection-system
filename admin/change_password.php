<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $sql = "SELECT password FROM users WHERE username = '$username'";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();

    if (md5($old_password) !== $user['password']) {
        $message = '原密码错误';
        $message_type = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = '两次输入的新密码不一致';
        $message_type = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = '新密码长度至少为6位';
        $message_type = 'error';
    } else {
        $hashed_password = md5($new_password);
        $sql = "UPDATE users SET password = '$hashed_password' WHERE username = '$username'";
        if ($conn->query($sql) === TRUE) {
            $message = '密码修改成功！';
            $message_type = 'success';
        } else {
            $message = '修改失败：' . $conn->error;
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改密码 - 后台管理</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔒</text></svg>" type="image/svg+xml">
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
            max-width: 500px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .form-card h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 22px;
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
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
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
                <a href="change_password.php" class="active">修改密码</a>
                <span>欢迎, <?php echo $_SESSION['username']; ?></span>
                <a href="logout.php">退出登录</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="form-card">
            <h2>修改密码</h2>
            
            <?php if ($message): ?>
                <div class="message <?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="old_password">原密码</label>
                    <input type="password" id="old_password" name="old_password" required placeholder="请输入原密码">
                </div>
                
                <div class="form-group">
                    <label for="new_password">新密码</label>
                    <input type="password" id="new_password" name="new_password" required placeholder="请输入新密码（至少6位）">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">确认新密码</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="请再次输入新密码">
                </div>
                
                <button type="submit" class="btn">修改密码</button>
            </form>
        </div>
    </div>

    <footer>
        <p><?php include_once '../config.php'; echo isset($footer_text) ? htmlspecialchars($footer_text) : '© 2026 云天安全 版权所有'; ?></p>
    </footer>
</body>
</html>