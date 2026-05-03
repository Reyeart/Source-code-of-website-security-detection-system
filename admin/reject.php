<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>拒绝申请 - 后台管理</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔒</text></svg>" type="image/svg+xml">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            width: 450px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .card h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 22px;
            text-align: center;
        }
        
        .card p {
            color: #666;
            margin-bottom: 20px;
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
        
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
            font-size: 14px;
        }
        
        .form-group textarea:focus {
            outline: none;
            border-color: #e74c3c;
        }
        
        .actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            text-align: center;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .btn.cancel {
            background: #f0f0f0;
            color: #666;
        }
        
        .btn.confirm {
            background: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <?php
    session_start();
    
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
    
    include '../config.php';
    
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id === 0) {
        header('Location: applications.php');
        exit;
    }
    
    $sql = "SELECT company_name, contact_name, contact_email FROM applications WHERE id = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reject_reason = $_POST['reject_reason'];
        $sql = "UPDATE applications SET status = 'rejected', reject_reason = '$reject_reason' WHERE id = $id";
        
        if ($conn->query($sql) === TRUE) {
            if (!empty($row['contact_email'])) {
                $subject = '【检测中心】网站认证申请未通过';
                $message = '<p>尊敬的' . htmlspecialchars($row['contact_name']) . '：</p>';
                $message .= '<p>您提交的网站认证申请未通过审核。</p>';
                $message .= '<p>企业名称：' . htmlspecialchars($row['company_name']) . '</p>';
                $message .= '<p>拒绝原因：' . htmlspecialchars($reject_reason) . '</p>';
                $message .= '<p>您可以根据以上原因完善材料后重新提交申请。</p>';
                $message .= '<p>感谢您使用本服务！</p>';
                
                send_email($row['contact_email'], $subject, $message);
            }
            
            header('Location: applications.php?status=rejected');
            exit;
        }
    }
    ?>
    
    <div class="card">
        <h2>拒绝申请</h2>
        <p>确定要拒绝企业 <strong><?php echo $row['company_name']; ?></strong> 的认证申请吗？</p>
        
        <form method="POST" action="reject.php?id=<?php echo $id; ?>">
            <div class="form-group">
                <label>拒绝原因 *</label>
                <textarea name="reject_reason" placeholder="请填写拒绝原因，该信息将告知申请人" required></textarea>
            </div>
            
            <div class="actions">
                <a href="applications.php" class="btn cancel">取消</a>
                <button type="submit" class="btn confirm">确认拒绝</button>
            </div>
        </form>
    </div>
</body>
</html>