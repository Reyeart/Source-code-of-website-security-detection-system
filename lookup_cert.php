<?php
header('Content-Type: application/json');
include_once 'config.php';

$code = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => '请输入证书编号']);
    exit;
}

$sql = "SELECT * FROM certificates WHERE certificate_code = '$code'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $cert = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'cert' => $cert
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => '未找到该证书编号对应的认证信息，请核对后重新查询'
    ]);
}
?>