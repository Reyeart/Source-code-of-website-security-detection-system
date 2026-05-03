<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台登录 - 云天安全网站检测中心</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔒</text></svg>" type="image/svg+xml">
    <?php
    $db_config_file = __DIR__ . '/../db_config.php';
    $config_file = __DIR__ . '/../config.php';

    $needs_install = false;
    if (!file_exists($db_config_file) || !file_exists($config_file)) {
        $needs_install = true;
    } else {
        include_once $db_config_file;
        if (isset($servername) && isset($dbname)) {
            $conn = @new mysqli($servername, $username, $password, $dbname);
            if ($conn->connect_error) {
                $needs_install = true;
            } else {
                $result = $conn->query("SHOW TABLES LIKE 'users'");
                if (!$result || $result->num_rows == 0) {
                    $needs_install = true;
                }
                $conn->close();
            }
        } else {
            $needs_install = true;
        }
    }

    if ($needs_install) {
        header('Location: ../install/');
        exit;
    }
    ?>
    <body oncontextmenu="return false" onselectstart="return false" ondragstart="return false" oncopy="return false" oncut="return false">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            width: 400px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .login-card h2 {
            color: #333;
            margin-bottom: 30px;
            font-size: 24px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>后台管理登录</h2>
        
        <?php
        session_start();
        
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            header('Location: index.php');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            include '../config.php';
            
            $username = $_POST['username'];
            $password_input = $_POST['password'];
            $password_md5 = md5($password_input);
            
            $sql = "SELECT * FROM users WHERE username = '$username' AND role = 'admin'";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                if ($user['password'] === $password_md5 || $user['password'] === $password_input) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['username'] = $username;
                    header('Location: index.php');
                    exit;
                }
            }
            echo '<div class="error-message">用户名或密码错误</div>';
        }
        ?>
        
        <form method="POST" action="login.php">
            <div class="form-group">
                <label>用户名</label>
                <input type="text" name="username" placeholder="请输入用户名" required>
            </div>
            
            <div class="form-group">
                <label>密码</label>
                <input type="password" name="password" placeholder="请输入密码" required>
            </div>
            
            <button type="submit" class="login-btn">登录</button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">返回首页</a>
        </div>
    </div>
</body>
</html>