<?php
include_once '../config.php';

function compareVersions($current, $latest) {
    $current = preg_replace('/^v/i', '', $current);
    $latest = preg_replace('/^v/i', '', $latest);
    
    $current_parts = explode('.', $current);
    $latest_parts = explode('.', $latest);
    
    $max_parts = max(count($current_parts), count($latest_parts));
    
    for ($i = 0; $i < $max_parts; $i++) {
        $current_part = isset($current_parts[$i]) ? (int)$current_parts[$i] : 0;
        $latest_part = isset($latest_parts[$i]) ? (int)$latest_parts[$i] : 0;
        
        if ($latest_part > $current_part) {
            return true;
        } elseif ($latest_part < $current_part) {
            return false;
        }
    }
    
    return false;
}

$current_version = $software_version;
$latest_version = $current_version;
$has_update = false;

// 先尝试 get_version API
$api_url = 'https://kf.ch1633.com/api.php?action=get_version';
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$found_version = false;

if ($http_code == 200 && !empty($response)) {
    $data = json_decode($response, true);
    if (isset($data['status']) && $data['status'] == 'success' && isset($data['version'])) {
        $latest_version = $data['version'];
        $has_update = compareVersions($current_version, $latest_version);
        $found_version = true;
    }
}

// 如果 get_version 不行，尝试用 auto_update 来检查
if (!$found_version) {
    $api_url = 'https://kf.ch1633.com/api.php?action=auto_update&version=' . urlencode($current_version) . '&domain=' . urlencode($_SERVER['HTTP_HOST']);
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 && !empty($response)) {
        $data = json_decode($response, true);
        if (isset($data['zip_url'])) {
            // 有 zip_url 说明有更新
            $has_update = true;
        }
    }
}

header('Content-Type: application/json');
echo json_encode(array(
    'current_version' => $current_version,
    'latest_version' => $latest_version,
    'has_update' => $has_update
));
?>