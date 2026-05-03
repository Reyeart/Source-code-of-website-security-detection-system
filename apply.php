<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户申请 - 云天安全检测中心</title>
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
        
        nav ul {
            list-style: none;
            display: flex;
            gap: 30px;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        nav a:hover {
            text-decoration: underline;
        }
        
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .apply-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .apply-card h2 {
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
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group .hint {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        
        .submit-btn {
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
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 30px;
            margin-top: 50px;
        }
        
        footer p {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <h1>云天安全检测中心</h1>
            <ul>
                <li><a href="index.php">首页</a></li>
                <li><a href="apply.php">用户申请</a></li>
                <li><a href="admin/login.php">后台管理</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <div class="apply-card">
            <h2>网站认证申请</h2>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                include_once 'config.php';
                
                $apply_type = $_POST['apply_type'];
                $company_name = $_POST['company_name'];
                $legal_name = $_POST['legal_name'];
                $website = preg_replace('/^https?:\/\//i', '', $_POST['website']);
                $contact_name = $_POST['contact_name'];
                $contact_phone = $_POST['contact_phone'];
                $contact_email = $_POST['contact_email'];
                $business_license = $_POST['business_license'];
                $icp_number = $_POST['icp_number'];
                $beian_number = $_POST['beian_number'];
                
                if (empty($company_name) || empty($legal_name) || empty($contact_name) || empty($contact_phone) || empty($contact_email)) {
                    echo '<div class="error-message">请填写必填项（名称、姓名/法定代表人、联系人、联系电话、电子邮箱）</div>';
                } elseif ($apply_type === 'enterprise' && empty($business_license)) {
                    echo '<div class="error-message">企业用户请填写营业执照编号</div>';
                } elseif (!empty($website)) {
                    $sql = "SELECT id FROM certificates WHERE website = '$website'";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        echo '<div class="success-message">该网站已获得认证证书！<a href="certificate.php?domain=' . urlencode($website) . '" target="_blank">查看证书</a></div>';
                    } else {
                        $sql = "SELECT id, status FROM applications WHERE website = '$website'";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            if ($row['status'] === 'pending') {
                                echo '<div class="error-message">该网站已提交过申请，当前状态：待审核，请耐心等待审核结果</div>';
                            } else {
                                $sql = "INSERT INTO applications (apply_type, company_name, legal_name, website, contact_name, contact_phone, contact_email, business_license, icp_number, beian_number) 
                                        VALUES ('$apply_type', '$company_name', '$legal_name', '$website', '$contact_name', '$contact_phone', '$contact_email', '$business_license', '$icp_number', '$beian_number')";
                                
                                if ($conn->query($sql) === TRUE) {
                                    echo '<div class="success-message">申请提交成功！我们将在3个工作日内进行审核，请保持电话畅通。</div>';
                                } else {
                                    echo '<div class="error-message">提交失败：' . $conn->error . '</div>';
                                }
                            }
                        } else {
                            $sql = "INSERT INTO applications (apply_type, company_name, legal_name, website, contact_name, contact_phone, contact_email, business_license, icp_number, beian_number) 
                                    VALUES ('$apply_type', '$company_name', '$legal_name', '$website', '$contact_name', '$contact_phone', '$contact_email', '$business_license', '$icp_number', '$beian_number')";
                            
                            if ($conn->query($sql) === TRUE) {
                                echo '<div class="success-message">申请提交成功！我们将在3个工作日内进行审核，请保持电话畅通。</div>';
                            } else {
                                echo '<div class="error-message">提交失败：' . $conn->error . '</div>';
                            }
                        }
                    }
                } else {
                    $sql = "INSERT INTO applications (apply_type, company_name, legal_name, website, contact_name, contact_phone, contact_email, business_license, icp_number, beian_number) 
                            VALUES ('$apply_type', '$company_name', '$legal_name', '$website', '$contact_name', '$contact_phone', '$contact_email', '$business_license', '$icp_number', '$beian_number')";
                    
                    if ($conn->query($sql) === TRUE) {
                        echo '<div class="success-message">申请提交成功！我们将在3个工作日内进行审核，请保持电话畅通。</div>';
                    } else {
                        echo '<div class="error-message">提交失败：' . $conn->error . '</div>';
                    }
                }
            }
            ?>
            
            <form method="POST" action="apply.php">
                <div class="form-group">
                    <label>申请类型 *</label>
                    <div style="display: flex; gap: 20px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="apply_type" value="personal" required checked onclick="toggleFormFields()">
                            <span>个人</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="radio" name="apply_type" value="enterprise" required onclick="toggleFormFields()">
                            <span>企业</span>
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>名称 *</label>
                    <input type="text" name="company_name" placeholder="个人请输入网站名称，企业请输入企业全称" required>
                </div>
                
                <div class="form-group">
                    <label>姓名/法定代表人 *</label>
                    <input type="text" name="legal_name" placeholder="个人请输入姓名，企业请输入法定代表人姓名" required>
                </div>
                
                <div class="form-group" id="website-group">
                    <label>网站域名</label>
                    <input type="text" name="website" placeholder="请输入网站域名（无需http://）">
                </div>
                
                <div class="form-group" id="icp-group">
                    <label>ICP备案号</label>
                    <input type="text" name="icp_number" placeholder="请输入网站ICP备案号，如：京ICP备12345678号">
                    <div class="hint">已备案网站请填写，未备案网站可留空</div>
                </div>
                
                <div class="form-group" id="beian-group">
                    <label>公网安备号</label>
                    <input type="text" name="beian_number" placeholder="请输入公网安备号，如：京公网安备11010502000000号">
                    <div class="hint">已申请公网安备的网站请填写，未申请可留空</div>
                </div>
                
                <div class="form-group">
                    <label>联系人 *</label>
                    <input type="text" name="contact_name" placeholder="请输入联系人姓名" required>
                </div>
                
                <div class="form-group">
                    <label>联系电话 *</label>
                    <input type="tel" name="contact_phone" placeholder="请输入联系电话" required>
                </div>
                
                <div class="form-group">
                    <label>电子邮箱 *</label>
                    <input type="email" name="contact_email" placeholder="请输入电子邮箱，用于通知审核结果" required>
                </div>
                
                <div class="form-group enterprise-only" id="license-group">
                    <label>营业执照编号 *</label>
                    <input type="text" name="business_license" placeholder="请输入营业执照编号">
                    <div class="hint">企业用户必填，请确保信息真实有效，我们将进行核验</div>
                </div>
                
                <button type="submit" class="submit-btn">提交申请</button>
            </form>
            
            <script>
                function toggleFormFields() {
                    var isEnterprise = document.querySelector('input[name="apply_type"]:checked').value === 'enterprise';
                    var licenseGroup = document.getElementById('license-group');
                    
                    if (isEnterprise) {
                        licenseGroup.style.display = 'block';
                        licenseGroup.querySelector('input').setAttribute('required', 'required');
                    } else {
                        licenseGroup.style.display = 'none';
                        licenseGroup.querySelector('input').removeAttribute('required');
                    }
                }
                
                toggleFormFields();
            </script>
        </div>
    </div>

    <footer>
        <p><?php include_once 'config.php'; echo isset($footer_text) ? htmlspecialchars($footer_text) : '© 2026 云天安全检测中心 版权所有'; ?></p>
    </footer>
</body>
</html>