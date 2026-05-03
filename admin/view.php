<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header('Location: index.php');
    exit;
}

$sql = "SELECT * FROM applications WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reject_reason = $_POST['reject_reason'];
    $sql = "UPDATE applications SET status = 'rejected', reject_reason = '$reject_reason' WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        header('Location: index.php?status=rejected');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>查看申请 - 后台管理</title>
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
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        nav {
            max-width: 800px;
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
        
        nav a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        nav a:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .detail-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .detail-card h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 22px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .info-label {
            width: 120px;
            font-weight: 500;
            color: #666;
            flex-shrink: 0;
        }
        
        .info-value {
            color: #333;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-badge.approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .btn {
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn.back {
            background: #f0f0f0;
            color: #666;
        }
        
        .btn.approve {
            background: #27ae60;
            color: white;
        }
        
        .btn.reject {
            background: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .reject-form {
            margin-top: 20px;
            padding: 20px;
            background: #fff5f5;
            border-radius: 8px;
        }
        
        .reject-form label {
            display: block;
            margin-bottom: 10px;
            color: #666;
            font-weight: 500;
        }
        
        .reject-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #f0c6c6;
            border-radius: 8px;
            font-family: inherit;
            resize: vertical;
            min-height: 80px;
            margin-bottom: 15px;
        }
        
        .reject-form button {
            padding: 10px 25px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .reject-form button:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>查看申请详情</h1>
            <a href="applications.php">返回列表</a>
        </nav>
    </header>

    <div class="container">
        <div class="detail-card">
            <h2>申请信息</h2>
            
            <div class="info-row">
                <span class="info-label">申请ID:</span>
                <span class="info-value">#<?php echo $row['id']; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">申请类型:</span>
                <span class="info-value"><?php echo $row['apply_type'] === 'personal' ? '个人' : '企业'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">名称:</span>
                <span class="info-value"><?php echo $row['company_name']; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">姓名/法定代表人:</span>
                <span class="info-value"><?php echo isset($row['legal_name']) && !empty($row['legal_name']) ? $row['legal_name'] : '未填写'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">网站:</span>
                <span class="info-value"><?php echo $row['website'] ? $row['website'] : '未填写'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">联系人:</span>
                <span class="info-value"><?php echo $row['contact_name']; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">联系电话:</span>
                <span class="info-value"><?php echo $row['contact_phone']; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">电子邮箱:</span>
                <span class="info-value"><?php echo $row['contact_email'] ? $row['contact_email'] : '未填写'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">ICP备案号:</span>
                <span class="info-value"><?php echo $row['icp_number'] ? $row['icp_number'] : '未填写'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">公网安备号:</span>
                <span class="info-value"><?php echo $row['beian_number'] ? $row['beian_number'] : '未填写'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">营业执照:</span>
                <span class="info-value"><?php echo $row['business_license'] ? $row['business_license'] : '未填写'; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">申请状态:</span>
                <span class="info-value"><span class="status-badge <?php echo $row['status']; ?>"><?php echo $row['status'] === 'pending' ? '待审核' : ($row['status'] === 'approved' ? '已通过' : '已拒绝'); ?></span></span>
            </div>
            
            <?php if ($row['status'] === 'rejected' && $row['reject_reason']): ?>
            <div class="info-row">
                <span class="info-label">拒绝原因:</span>
                <span class="info-value" style="color: #e74c3c;"><?php echo $row['reject_reason']; ?></span>
            </div>
            <?php endif; ?>
            
            <div class="info-row">
                <span class="info-label">提交时间:</span>
                <span class="info-value"><?php echo $row['created_at']; ?></span>
            </div>
            
            <div class="info-row">
                <span class="info-label">更新时间:</span>
                <span class="info-value"><?php echo $row['updated_at']; ?></span>
            </div>
            
            <div class="actions">
                <a href="applications.php" class="btn back">返回列表</a>
                <?php if ($row['status'] === 'pending'): ?>
                    <a href="approve.php?id=<?php echo $row['id']; ?>" class="btn approve">通过审核</a>
                    <button onclick="document.getElementById('reject-form').style.display='block'" class="btn reject">拒绝申请</button>
                <?php endif; ?>
            </div>
            
            <div id="reject-form" style="display: none;" class="reject-form">
                <form method="POST" action="view.php?id=<?php echo $row['id']; ?>">
                    <label>拒绝原因:</label>
                    <textarea name="reject_reason" placeholder="请填写拒绝原因" required></textarea>
                    <button type="submit">确认拒绝</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>