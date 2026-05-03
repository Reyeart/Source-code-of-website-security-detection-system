<?php
error_reporting(0);
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('status' => 'error', 'message' => '仅支持POST请求'));
    exit;
}

if (!function_exists('curl_init')) {
    echo json_encode(array('status' => 'error', 'message' => '服务器未安装CURL扩展，无法进行更新'));
    exit;
}

if (!class_exists('ZipArchive')) {
    echo json_encode(array('status' => 'error', 'message' => '服务器未安装Zip扩展，无法进行更新'));
    exit;
}

$action = $_POST['action'] ?? '';

if ($action == 'get_update_info') {
    // 使用正确的下载地址
    $zip_url = "https://kf.ch1633.com/gx/update.zip";
    
    echo json_encode(array('status' => 'success', 'zip_url' => $zip_url));
    exit;
}

if ($action == 'download_zip') {
    $zip_url = $_POST['zip_url'] ?? '';
    
    if (empty($zip_url)) {
        echo json_encode(array('status' => 'error', 'message' => 'zip_url不能为空'));
        exit;
    }
    
    $temp_file = tempnam(sys_get_temp_dir(), 'update_') . '.zip';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $zip_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $zip_content = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code != 200) {
        echo json_encode(array('status' => 'error', 'message' => '下载zip失败，HTTP状态：' . $http_code));
        exit;
    }
    
    if (file_put_contents($temp_file, $zip_content) === false) {
        echo json_encode(array('status' => 'error', 'message' => '保存zip文件失败'));
        exit;
    }
    
    $_SESSION['update_zip_file'] = $temp_file;
    echo json_encode(array('status' => 'success', 'temp_file' => $temp_file));
    exit;
}

if ($action == 'extract_zip') {
    $temp_file = $_SESSION['update_zip_file'] ?? '';
    
    if (empty($temp_file) || !file_exists($temp_file)) {
        echo json_encode(array('status' => 'error', 'message' => '未找到zip文件'));
        exit;
    }
    
    $zip = new ZipArchive();
    if ($zip->open($temp_file) !== true) {
        echo json_encode(array('status' => 'error', 'message' => '打开zip文件失败'));
        exit;
    }
    
    $extract_dir = '../';
    $files = [];
    $skip_files = array('db_config.php', 'admin/db_config.php', '../db_config.php');
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $stat = $zip->statIndex($i);
        if (!$stat['name'] || substr($stat['name'], -1) == '/') {
            continue;
        }
        
        $file_name = str_replace('\\', '/', $stat['name']);
        $file_name_lower = strtolower($file_name);
        
        if (in_array($file_name_lower, $skip_files) || substr($file_name_lower, -4) === '.sql') {
            continue;
        }
        
        $files[] = $stat['name'];
    }
    
    $_SESSION['update_files'] = $files;
    $_SESSION['update_index'] = 0;
    $_SESSION['update_total'] = count($files);
    
    $zip->close();
    
    // 在解压阶段就获取并更新版本号
    $latest_version = '';
    $api_url = 'https://kf.ch1633.com/api.php?action=get_version';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 && !empty($response)) {
        $data = json_decode($response, true);
        if (isset($data['status']) && $data['status'] == 'success' && isset($data['version'])) {
            $latest_version = $data['version'];
            
            $config_file = dirname(__DIR__) . '/config.php';
            if (file_exists($config_file)) {
                $config_content = file_get_contents($config_file);
                $new_content = preg_replace('/\$software_version\s*=\s*["\'].*?["\'];/', '$software_version = "' . $latest_version . '";', $config_content);
                if ($new_content !== $config_content) {
                    file_put_contents($config_file, $new_content);
                }
            }
        }
    }
    
    echo json_encode(array('status' => 'success', 'total' => count($files), 'files' => $files, 'version' => $latest_version));
    exit;
}

if ($action == 'update_file') {
    $temp_file = $_SESSION['update_zip_file'] ?? '';
    $files = $_SESSION['update_files'] ?? [];
    $index = $_SESSION['update_index'] ?? 0;
    $total = $_SESSION['update_total'] ?? 0;
    
    if (empty($temp_file) || !file_exists($temp_file)) {
        echo json_encode(array('status' => 'error', 'message' => '未找到zip文件'));
        exit;
    }
    
    if ($index >= $total) {
        unlink($temp_file);
        unset($_SESSION['update_zip_file'], $_SESSION['update_files'], $_SESSION['update_index'], $_SESSION['update_total']);
        
        $update_message = '更新完成';
        
        // 更新成功后获取最新版本号
        $api_url = 'https://kf.ch1633.com/api.php?action=get_version';
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code == 200 && !empty($response)) {
            $data = json_decode($response, true);
            if (isset($data['status']) && $data['status'] == 'success' && isset($data['version'])) {
                $latest_version = $data['version'];
                
                // 更新 config.php 文件中的版本号
                $config_file = dirname(__DIR__) . '/config.php';
                if (file_exists($config_file)) {
                    $config_content = file_get_contents($config_file);
                    $old_version = '';
                    if (preg_match('/\$software_version\s*=\s*["\'](.*?)["\'];/', $config_content, $matches)) {
                        $old_version = $matches[1];
                    }
                    
                    $new_content = preg_replace('/\$software_version\s*=\s*["\'].*?["\'];/', '$software_version = "' . $latest_version . '";', $config_content);
                    
                    if ($new_content !== $config_content) {
                        if (file_put_contents($config_file, $new_content)) {
                            $update_message = '更新完成！版本已从 ' . $old_version . ' 更新到 ' . $latest_version;
                        } else {
                            $update_message = '更新完成，但版本号更新失败（权限不足）';
                        }
                    } else {
                        $update_message = '更新完成，版本已是最新';
                    }
                } else {
                    $update_message = '更新完成，但未找到 config.php 文件';
                }
            }
        }
        
        echo json_encode(array('status' => 'complete', 'message' => $update_message));
        exit;
    }
    
    $zip = new ZipArchive();
    if ($zip->open($temp_file) !== true) {
        echo json_encode(array('status' => 'error', 'message' => '打开zip文件失败'));
        exit;
    }
    
    $file_name = $files[$index];
    $content = $zip->getFromName($file_name);
    
    if ($content === false) {
        $zip->close();
        $_SESSION['update_index']++;
        echo json_encode(array('status' => 'continue', 'index' => $index, 'total' => $total, 'file' => $file_name, 'error' => '读取文件失败'));
        exit;
    }
    
    $target_path = '../' . $file_name;
    $target_dir = dirname($target_path);

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    if ($file_name === 'config.php' || strpos($file_name, 'config.php') !== false) {
        $config_file = dirname(__DIR__) . '/config.php';
        if (file_exists($config_file)) {
            $current_config = file_get_contents($config_file);
            preg_match('/\$mail_config\s*=\s*array\([^)]*\);/s', $current_config, $current_mail_config);
            preg_match('/\$site_title\s*=\s*["\']([^"\']*)["\'];/', $current_config, $current_site_title);
            preg_match('/\$site_subtitle\s*=\s*["\']([^"\']*)["\'];/', $current_config, $current_site_subtitle);
            preg_match('/\$footer_text\s*=\s*["\']([^"\']*)["\'];/', $current_config, $current_footer_text);
            preg_match('/\$icp_number\s*=\s*["\']([^"\']*)["\'];/', $current_config, $current_icp_number);
            preg_match('/\$service_phone\s*=\s*["\']([^"\']*)["\'];/', $current_config, $current_service_phone);
            preg_match('/\$contact_email\s*=\s*["\']([^"\']*)["\'];/', $current_config, $current_contact_email);
            preg_match('/\$footer_html\s*=\s*["\']([^"\']*)["\'];/', $current_config, $current_footer_html);

            if (!empty($current_mail_config[0])) {
                $content = preg_replace('/\$mail_config\s*=\s*array\([^)]*\);/s', $current_mail_config[0], $content);
            }
            if (!empty($current_site_title[1])) {
                $content = preg_replace('/\$site_title\s*=\s*["\']([^"\']*)["\'];/', '$site_title = "' . addslashes($current_site_title[1]) . '";', $content);
            }
            if (!empty($current_site_subtitle[1])) {
                $content = preg_replace('/\$site_subtitle\s*=\s*["\']([^"\']*)["\'];/', '$site_subtitle = "' . addslashes($current_site_subtitle[1]) . '";', $content);
            }
            if (!empty($current_footer_text[1])) {
                $content = preg_replace('/\$footer_text\s*=\s*["\']([^"\']*)["\'];/', '$footer_text = "' . addslashes($current_footer_text[1]) . '";', $content);
            }
            if (!empty($current_icp_number[1])) {
                $content = preg_replace('/\$icp_number\s*=\s*["\']([^"\']*)["\'];/', '$icp_number = "' . addslashes($current_icp_number[1]) . '";', $content);
            }
            if (!empty($current_service_phone[1])) {
                $content = preg_replace('/\$service_phone\s*=\s*["\']([^"\']*)["\'];/', '$service_phone = "' . addslashes($current_service_phone[1]) . '";', $content);
            }
            if (!empty($current_contact_email[1])) {
                $content = preg_replace('/\$contact_email\s*=\s*["\']([^"\']*)["\'];/', '$contact_email = "' . addslashes($current_contact_email[1]) . '";', $content);
            }
            if (!empty($current_footer_html[1])) {
                $content = preg_replace('/\$footer_html\s*=\s*["\']([^"\']*)["\'];/', '$footer_html = "' . addslashes($current_footer_html[1]) . '";', $content);
            }

            $api_url = 'https://kf.ch1633.com/api.php?action=get_version';
            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code == 200 && !empty($response)) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] == 'success' && isset($data['version'])) {
                    $latest_version = $data['version'];
                    $content = preg_replace('/\$software_version\s*=\s*["\']([^"\']*)["\'];/', '$software_version = "' . $latest_version . '";', $content);
                }
            }
        }
    }

    $success = file_put_contents($target_path, $content);
    
    $zip->close();
    
    $_SESSION['update_index']++;
    
    if ($success === false) {
        echo json_encode(array('status' => 'continue', 'index' => $index, 'total' => $total, 'file' => $file_name, 'error' => '写入文件失败'));
    } else {
        chmod($target_path, 0644);
        echo json_encode(array('status' => 'continue', 'index' => $index, 'total' => $total, 'file' => $file_name, 'success' => true));
    }
    exit;
}

echo json_encode(array('status' => 'error', 'message' => '未知的action'));
?>
