<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM certificates WHERE id = $id";
    $conn->query($sql);
    header('Location: certificates.php?msg=deleted');
    exit;
}

$sql = "SELECT * FROM certificates ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>证书管理 - 后台管理</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔒</text></svg>" type="image/svg+xml">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Microsoft YaHei', sans-serif; 
            background: #f5f7fa;
            padding-bottom: 80px;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        header nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        header nav .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        nav h1 {
            font-size: 24px;
            font-weight: 600;
        }
        
        header a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            min-width: 90px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        header a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        header a.active {
            background: rgba(255,255,255,0.4);
        }
        
        header span {
            font-size: 16px;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            min-width: 100px;
            text-align: center;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .card h2 {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        
        .btn-success {
            background: #27ae60;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        
        .btn-code {
            background: #f39c12;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .msg {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            background: #d4edda;
            color: #155724;
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
                <a href="certificates.php" class="active">证书管理</a>
                <a href="settings.php">系统设置</a>
                <span>欢迎, <?php echo $_SESSION['username']; ?></span>
                <a href="logout.php">退出登录</a>
            </div>
        </nav>
    </header>
    
    <div class="container">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="msg">证书删除成功！</div>
        <?php endif; ?>
        
        <div class="card">
            <h2>证书列表</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>企业名称</th>
                        <th>网站</th>
                        <th>有效期</th>
                        <th>证书编号</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['company_name']; ?></td>
                            <td><?php echo $row['website']; ?></td>
                            <td><?php echo $row['valid_from']; ?> - <?php echo $row['valid_to']; ?></td>
                            <td><?php echo $row['certificate_code']; ?></td>
                            <td class="actions">
                                <a href="../footer_code.php?domain=<?php echo urlencode($row['website']); ?>" target="_blank" class="btn-code">页脚代码</a>
                                <a href="../certificate.php?domain=<?php echo urlencode($row['website']); ?>" target="_blank" class="btn-success">预览</a>
                                <a href="certificates.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('确定要删除吗？')" class="btn-danger">删除</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999;">暂无证书</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <footer>
        <p><?php include_once '../config.php'; echo isset($footer_text) ? htmlspecialchars($footer_text) : '© 2026 云天安全 版权所有'; ?></p>
    </footer>
</body>
</html>