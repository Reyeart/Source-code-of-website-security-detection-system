<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';

$res = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='kf_ch1633_com' AND table_name='applications' AND column_name='icp_number'");
$row = $res->fetch_assoc();
if ($row['cnt'] == 0) {
    $conn->query("ALTER TABLE applications ADD COLUMN icp_number varchar(50) DEFAULT NULL");
}

$res = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='kf_ch1633_com' AND table_name='certificates' AND column_name='icp_number'");
$row = $res->fetch_assoc();
if ($row['cnt'] == 0) {
    $conn->query("ALTER TABLE certificates ADD COLUMN icp_number varchar(50) DEFAULT NULL");
}

$res = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='kf_ch1633_com' AND table_name='applications' AND column_name='beian_number'");
$row = $res->fetch_assoc();
if ($row['cnt'] == 0) {
    $conn->query("ALTER TABLE applications ADD COLUMN beian_number varchar(50) DEFAULT NULL");
}

$res = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='kf_ch1633_com' AND table_name='certificates' AND column_name='beian_number'");
$row = $res->fetch_assoc();
if ($row['cnt'] == 0) {
    $conn->query("ALTER TABLE certificates ADD COLUMN beian_number varchar(50) DEFAULT NULL");
}

$res = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='kf_ch1633_com' AND table_name='certificates' AND column_name='updated_at'");
$row = $res->fetch_assoc();
if ($row['cnt'] == 0) {
    $conn->query("ALTER TABLE certificates ADD COLUMN updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
}

$res = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='kf_ch1633_com' AND table_name='applications' AND column_name='created_at'");
$row = $res->fetch_assoc();
if ($row['cnt'] == 0) {
    $conn->query("ALTER TABLE applications ADD COLUMN created_at timestamp NULL DEFAULT CURRENT_TIMESTAMP");
}

$res = $conn->query("SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema='kf_ch1633_com' AND table_name='applications' AND column_name='updated_at'");
$row = $res->fetch_assoc();
if ($row['cnt'] == 0) {
    $conn->query("ALTER TABLE applications ADD COLUMN updated_at timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
}

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
    $apply_type = $conn->real_escape_string($_POST['apply_type']);
    $company_name = $conn->real_escape_string($_POST['company_name']);
    $legal_name = $conn->real_escape_string($_POST['legal_name']);
    $website = $conn->real_escape_string($_POST['website']);
    $icp_number = $conn->real_escape_string($_POST['icp_number']);
    $beian_number = $conn->real_escape_string($_POST['beian_number']);
    $business_license = $conn->real_escape_string($_POST['business_license']);
    $contact_name = $conn->real_escape_string($_POST['contact_name']);
    $contact_phone = $conn->real_escape_string($_POST['contact_phone']);
    $contact_email = $conn->real_escape_string($_POST['contact_email']);
    
    $sql = "UPDATE applications SET 
            apply_type = '$apply_type',
            company_name = '$company_name',
            legal_name = '$legal_name',
            website = '$website',
            icp_number = '$icp_number',
            beian_number = '$beian_number',
            business_license = '$business_license',
            contact_name = '$contact_name',
            contact_phone = '$contact_phone',
            contact_email = '$contact_email',
            updated_at = NOW()
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        $sql_cert = "UPDATE certificates SET 
                apply_type = '$apply_type',
                company_name = '$company_name',
                website = '$website',
                icp_number = '$icp_number',
                beian_number = '$beian_number',
                updated_at = NOW()
                WHERE website = '$website'";
        
        if ($conn->query($sql_cert) === TRUE) {
            header('Location: index.php?msg=updated');
            exit();
        } else {
            $error = '保存证书失败：' . $conn->error;
        }
    } else {
        $error = '保存申请失败：' . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编辑申请 - 后台管理</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔒</text></svg>" type="image/svg+xml">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif; background: #f5f7fa; }
        
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
        
        nav a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .form-card h2 {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-weight: 500;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group .hint {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd6;
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #666;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .radio-group {
            display: flex;
            gap: 30px;
        }
        
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .radio-group input[type="radio"] {
            width: auto;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>编辑申请信息</h1>
            <a href="applications.php">返回列表</a>
        </nav>
    </header>

    <div class="container">
        <div class="form-card">
            <h2>修改申请信息</h2>
            
            <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="edit.php?id=<?php echo $row['id']; ?>">
                <div class="form-group">
                    <label>申请类型</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="apply_type" value="personal" <?php echo $row['apply_type'] === 'personal' ? 'checked' : ''; ?>>
                            个人
                        </label>
                        <label>
                            <input type="radio" name="apply_type" value="enterprise" <?php echo $row['apply_type'] === 'enterprise' ? 'checked' : ''; ?>>
                            企业
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>名称 *</label>
                    <input type="text" name="company_name" value="<?php echo htmlspecialchars($row['company_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>姓名/法定代表人</label>
                    <input type="text" name="legal_name" value="<?php echo htmlspecialchars($row['legal_name']); ?>">
                </div>

                <div class="form-group">
                    <label>网站</label>
                    <input type="text" name="website" value="<?php echo htmlspecialchars($row['website']); ?>">
                    <div class="hint">请输入完整网址，如：https://www.example.com</div>
                </div>

                <div class="form-group">
                    <label>ICP备案号</label>
                    <input type="text" name="icp_number" value="<?php echo htmlspecialchars($row['icp_number']); ?>">
                    <div class="hint">已备案网站请填写，如：京ICP备12345678号</div>
                </div>

                <div class="form-group">
                    <label>公网安备号</label>
                    <input type="text" name="beian_number" value="<?php echo htmlspecialchars($row['beian_number']); ?>">
                    <div class="hint">已申请公网安备的网站请填写，如：京公网安备11010502000000号</div>
                </div>

                <div class="form-group">
                    <label>营业执照编号</label>
                    <input type="text" name="business_license" value="<?php echo htmlspecialchars($row['business_license']); ?>">
                    <div class="hint">企业申请必填</div>
                </div>

                <div class="form-group">
                    <label>联系人 *</label>
                    <input type="text" name="contact_name" value="<?php echo htmlspecialchars($row['contact_name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>联系电话 *</label>
                    <input type="text" name="contact_phone" value="<?php echo htmlspecialchars($row['contact_phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label>电子邮箱</label>
                    <input type="email" name="contact_email" value="<?php echo htmlspecialchars($row['contact_email']); ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">保存修改</button>
                    <a href="applications.php" class="btn btn-secondary">取消</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>