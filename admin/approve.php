<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id === 0) {
    header('Location: applications.php');
    exit;
}

$sql = "SELECT * FROM applications WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    header('Location: applications.php');
    exit;
}

$application = $result->fetch_assoc();

$conn->begin_transaction();

try {
    $sql = "UPDATE applications SET status = 'approved' WHERE id = $id";
    $conn->query($sql);

    $apply_type = $application['apply_type'];
    $company_name = $application['company_name'];
    $website = $application['website'];
    $certificate_code = 'cert' . date('YmdHis') . rand(1000, 9999);
    $valid_from = date('Y-m-d');
    $valid_to = date('Y-m-d', strtotime('+5 years'));
    $description = '已通过网站备案信息、域名注册信息、工商登记信息、网站身份信息等多项审核。';

    $icp_number = $application['icp_number'];
    $beian_number = $application['beian_number'];
    $sql = "INSERT INTO certificates (apply_type, company_name, website, icp_number, beian_number, description, valid_from, valid_to, certificate_code) 
            VALUES ('$apply_type', '$company_name', '$website', '$icp_number', '$beian_number', '$description', '$valid_from', '$valid_to', '$certificate_code')";
    $conn->query($sql);

    $certificate_id = $conn->insert_id;

    $conn->commit();

    $email_status = 'nosend';
    $email_to = $application['contact_email'];
    
    error_log("审核通过 - 尝试发送邮件到: " . ($email_to ? $email_to : '空'));
    
    if (!empty($email_to)) {
        $footer_code = '<a href="http://' . $_SERVER['HTTP_HOST'] . '/certificate.php?domain=' . urlencode($website) . '" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); padding: 4px 10px; border-radius: 4px; text-decoration: none; box-shadow: 0 2px 6px rgba(255, 215, 0, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform=\'translateY(-1px)\'; this.style.boxShadow=\'0 3px 10px rgba(255, 215, 0, 0.4)\';" onmouseout="this.style.transform=\'translateY(0)\'; this.style.boxShadow=\'0 2px 6px rgba(255, 215, 0, 0.3)\';"><span style="width: 22px; height: 22px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #FFA500; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">V</span><span style="text-align: left; display: inline-flex; flex-direction: column;"><span style="font-size: 11px; font-weight: bold; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); display: block; line-height: 1.1;">安全认证</span><span style="font-size: 8px; color: rgba(255, 255, 255, 0.9); letter-spacing: 0.5px; display: block; line-height: 1.1;">★★★★★</span></span></a>';

        $subject = '【检测中心】网站认证申请已通过';
        $message = '<p style="font-size: 15px; line-height: 1.8;">尊敬的' . htmlspecialchars($application['contact_name']) . '：</p>';
        $message .= '<p style="font-size: 15px; line-height: 1.8;">您提交的网站认证申请已通过审核！</p>';
        $message .= '<p style="font-size: 15px; line-height: 1.8;">企业名称：<strong>' . htmlspecialchars($company_name) . '</strong></p>';
        $message .= '<p style="font-size: 15px; line-height: 1.8;">认证网站：<strong>' . htmlspecialchars($website) . '</strong></p>';
        $message .= '<p style="font-size: 15px; line-height: 1.8;">您的电子证书已生成，请前往网站输入您的证书编号，查询认证信息及获取页脚代码</p>';
               $message .= '<p style="font-size: 15px; line-height: 1.8;">有效期：<strong>' . $valid_from . '</strong> 至 <strong>' . $valid_to . '</strong></p>';
        $message .= '<p style="font-size: 15px; line-height: 1.8;">证书编号：<strong style="font-family: monospace; color: #666;">' . $certificate_code . '</strong></p>';
        $message .= '<p style="font-size: 15px; line-height: 1.8; margin-top: 25px;">感谢您使用本服务！</p>';
        
        error_log("开始发送邮件...");
        if (send_email($email_to, $subject, $message)) {
            $email_status = 'sent';
            error_log("邮件发送成功！");
        } else {
            $email_status = 'failed';
            error_log("邮件发送失败！");
        }
    } else {
        $email_status = 'noemail';
        error_log("邮箱为空，跳过发送");
    }

    header('Location: applications.php?msg=approved&email=' . $email_status);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo "审核失败: " . $e->getMessage();
}
?>