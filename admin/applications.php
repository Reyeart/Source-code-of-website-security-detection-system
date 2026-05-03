<?php
session_start();

$db_config_file = __DIR__ . '/../db_config.php';
$config_file = __DIR__ . '/../config.php';

if (!file_exists($db_config_file) || !file_exists($config_file)) {
    header('Location: ../install/');
    exit;
}

$installed = false;
if (file_exists($db_config_file)) {
    include_once $db_config_file;
    if (isset($servername) && isset($dbname)) {
        $conn = @new mysqli($servername, $username, $password, $dbname);
        if (!$conn->connect_error) {
            $result = $conn->query("SHOW TABLES LIKE 'users'");
            if ($result && $result->num_rows > 0) {
                $installed = true;
            }
            $conn->close();
        }
    }
}

if (!$installed) {
    header('Location: ../install/');
    exit;
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$status = isset($_GET['status']) ? $_GET['status'] : 'all';

$sql = "SELECT COUNT(*) as count FROM applications";
$total_result = $conn->query($sql);
$total = $total_result->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) as count FROM applications WHERE status = 'pending'";
$pending_result = $conn->query($sql);
$pending_count = $pending_result->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) as count FROM applications WHERE status = 'approved'";
$approved_result = $conn->query($sql);
$approved_count = $approved_result->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) as count FROM applications WHERE status = 'rejected'";
$rejected_result = $conn->query($sql);
$rejected_count = $rejected_result->fetch_assoc()['count'];

$where_clause = "";
if ($status === 'pending') {
    $where_clause = "WHERE status = 'pending'";
} elseif ($status === 'approved') {
    $where_clause = "WHERE status = 'approved'";
} elseif ($status === 'rejected') {
    $where_clause = "WHERE status = 'rejected'";
}

$sql = "SELECT * FROM applications $where_clause ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申请管理 - 云天后台管理系统</title>
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 14px;
        }
        
        .stat-card.pending .number { color: #f39c12; }
        .stat-card.approved .number { color: #27ae60; }
        .stat-card.rejected .number { color: #e74c3c; }
        
        .filter-bar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
        }
        
        .filter-bar a {
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            color: #666;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .filter-bar a.active {
            background: #667eea;
            color: white;
        }
        
        .filter-bar a:hover:not(.active) {
            background: #f0f0f0;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            overflow: hidden;
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
            color: #333;
        }
        
        tr:hover {
            background: #fafafa;
        }
        
        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status.approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status.rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 6px 15px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn.view {
            background: #667eea;
            color: white;
        }
        
        .btn.approve {
            background: #27ae60;
            color: white;
        }
        
        .btn.reject {
            background: #e74c3c;
            color: white;
        }
        
        .btn.edit {
            background: #f39c12;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .empty-message {
            padding: 60px;
            text-align: center;
            color: #999;
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
                <a href="index.php">系统信息</a>
                <a href="applications.php" class="active">申请管理</a>
                <a href="certificates.php">证书管理</a>
                <a href="settings.php">系统设置</a>
                <span>欢迎, <?php echo $_SESSION['username']; ?></span>
                <a href="logout.php">退出登录</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'approved'): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                审核通过！证书已自动生成。
                <?php 
                if (isset($_GET['email'])) {
                    if ($_GET['email'] === 'sent') {
                        echo '<span style="color: #27ae60; font-weight: bold;">邮件发送成功！</span>';
                    } elseif ($_GET['email'] === 'failed') {
                        echo '<span style="color: #e74c3c; font-weight: bold;">邮件发送失败，请检查邮箱配置！</span>';
                    } elseif ($_GET['email'] === 'noemail') {
                        echo '<span style="color: #f39c12; font-weight: bold;">无收件人邮箱，请联系用户获取邮箱地址！</span>';
                    }
                }
                ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card pending">
                <div class="number"><?php echo $pending_count; ?></div>
                <div class="label">待审核</div>
            </div>
            <div class="stat-card approved">
                <div class="number"><?php echo $approved_count; ?></div>
                <div class="label">已通过</div>
            </div>
            <div class="stat-card rejected">
                <div class="number"><?php echo $rejected_count; ?></div>
                <div class="label">已拒绝</div>
            </div>
        </div>
        
        <div class="filter-bar">
            <a href="applications.php?status=all" <?php echo $status === 'all' ? 'class="active"' : ''; ?>>全部 (<?php echo $total; ?>)</a>
            <a href="applications.php?status=pending" <?php echo $status === 'pending' ? 'class="active"' : ''; ?>>待审核 (<?php echo $pending_count; ?>)</a>
            <a href="applications.php?status=approved" <?php echo $status === 'approved' ? 'class="active"' : ''; ?>>已通过 (<?php echo $approved_count; ?>)</a>
            <a href="applications.php?status=rejected" <?php echo $status === 'rejected' ? 'class="active"' : ''; ?>>已拒绝 (<?php echo $rejected_count; ?>)</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>申请类型</th>
                        <th>名称</th>
                        <th>联系人</th>
                        <th>联系电话</th>
                        <th>状态</th>
                        <th>提交时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['apply_type'] === 'personal' ? '个人' : '企业'; ?></td>
                            <td><?php echo $row['company_name']; ?></td>
                            <td><?php echo $row['contact_name']; ?></td>
                            <td><?php echo $row['contact_phone']; ?></td>
                            <td><span class="status <?php echo $row['status']; ?>"><?php echo $row['status'] === 'pending' ? '待审核' : ($row['status'] === 'approved' ? '已通过' : '已拒绝'); ?></span></td>
                            <td><?php echo $row['created_at']; ?></td>
                            <td class="actions">
                                <a href="view.php?id=<?php echo $row['id']; ?>" class="btn view">查看</a>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn edit">编辑</a>
                                <?php if ($row['status'] === 'pending'): ?>
                                    <a href="approve.php?id=<?php echo $row['id']; ?>" class="btn approve">通过</a>
                                    <a href="reject.php?id=<?php echo $row['id']; ?>" class="btn reject">拒绝</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="empty-message">暂无申请记录</td>
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