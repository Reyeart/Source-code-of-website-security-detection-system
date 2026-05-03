<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include_once '../config.php';

function checkDomainAuthorization($domain) {
    global $software_version;
    
    $api_url = 'https://kf.ch1633.com/api.php?action=report_version&version=' . urlencode($software_version) . '&domain=' . urlencode($domain);
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
        if (is_array($data) && isset($data['status']) && $data['status'] == 'success') {
            return true;
        }
    }
    return false;
}

$current_domain = $_SERVER['HTTP_HOST'];
$is_authorized = checkDomainAuthorization($current_domain);

$announcement = '';
$announcement_title = '网站公告';
$announcement_time = '';
$announcement_author = '';
$ch = curl_init('https://kf.ch1633.com/announcement.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$announcement_response = curl_exec($ch);
$announcement_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($announcement_http_code == 200 && !empty($announcement_response)) {
    $announcement_data = json_decode($announcement_response, true);
    if (is_array($announcement_data) && isset($announcement_data['code']) && $announcement_data['code'] == 200) {
        $announcement = $announcement_data['data']['announcement'] ?? '';
        $announcement_title = '网站公告';
        $announcement_time = $announcement_data['data']['time'] ?? '';
        $announcement_author = $announcement_data['data']['author'] ?? '';
    }
}

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

$sql = "SELECT COUNT(*) as count FROM certificates";
$cert_result = $conn->query($sql);
$cert_count = $cert_result->fetch_assoc()['count'];

$sql = "SELECT VERSION() as version";
$mysql_version = $conn->query($sql)->fetch_assoc()['version'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系统信息 - 云天后台管理系统</title>
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
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .info-card h3 {
            color: #667eea;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            font-size: 18px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-item .label {
            color: #666;
            font-weight: 500;
        }
        
        .info-item .value {
            color: #333;
            font-weight: 600;
        }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-box {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stat-box .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-box .label {
            color: #666;
            font-size: 14px;
        }
        
        .stat-box.pending .number { color: #f39c12; }
        .stat-box.approved .number { color: #27ae60; }
        .stat-box.rejected .number { color: #e74c3c; }

        .announcement-card {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .announcement-card h3 {
            color: white;
            border-bottom: 2px solid rgba(255,255,255,0.3);
            margin-bottom: 15px;
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .announcement-title {
            font-size: 20px;
            font-weight: bold;
        }

        .announcement-meta {
            font-size: 13px;
            opacity: 0.85;
        }

        .announcement-content {
            font-size: 15px;
            line-height: 1.8;
            background: rgba(255,255,255,0.15);
            padding: 20px;
            border-radius: 10px;
            max-height: 200px;
            overflow-y: auto;
        }

        .announcement-content::-webkit-scrollbar {
            width: 6px;
        }

        .announcement-content::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.4);
            border-radius: 3px;
        }

        .no-announcement {
            text-align: center;
            padding: 40px;
            opacity: 0.8;
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
                <a href="system.php" class="active">系统信息</a>
                <a href="applications.php">申请管理</a>
                <a href="certificates.php">证书管理</a>
                <a href="settings.php">系统设置</a>
                <span>欢迎, <?php echo $_SESSION['username']; ?></span>
                <a href="logout.php">退出登录</a>
            </div>
        </nav>
    </header>

    <div class="container">
        <div class="stats-row">
            <div class="stat-box">
                <div class="number"><?php echo $total; ?></div>
                <div class="label">申请总数</div>
            </div>
            <div class="stat-box pending">
                <div class="number"><?php echo $pending_count; ?></div>
                <div class="label">待审核</div>
            </div>
            <div class="stat-box approved">
                <div class="number"><?php echo $approved_count; ?></div>
                <div class="label">已通过</div>
            </div>
            <div class="stat-box rejected">
                <div class="number"><?php echo $rejected_count; ?></div>
                <div class="label">已拒绝</div>
            </div>
        </div>

        <div class="info-grid">
            <div class="announcement-card">
                <h3>网站公告</h3>
                <?php if (!empty($announcement)): ?>
                <div class="announcement-header">
                    <span class="announcement-author">
                        <?php if (!empty($announcement_author)): ?>作者：<?php echo htmlspecialchars($announcement_author); ?><?php endif; ?>
                    </span>
                    <span class="announcement-time">
                        <?php if (!empty($announcement_time)): ?>发布时间：<?php echo htmlspecialchars($announcement_time); ?><?php endif; ?>
                    </span>
                </div>
                <div class="announcement-content"><?php echo $announcement; ?></div>
                <?php else: ?>
                <div class="no-announcement">
                    <p>暂无公告</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="info-card">
                <h3>服务器信息</h3>
                <div class="info-item">
                    <span class="label">服务器域名</span>
                    <span class="value"><?php echo $_SERVER['HTTP_HOST']; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">站点授权状态</span>
                    <span class="value" style="color: <?php echo $is_authorized ? '#27ae60' : '#e74c3c'; ?>; font-weight: bold;">
                        <?php echo $is_authorized ? '【正版授权】[' . $current_domain . ']' : '【未授权】[' . $current_domain . '] <a href=\'https://kf.ch1633.com/license_activate.php\' target=\'_blank\' style=\'color: #667eea; text-decoration: underline; margin-left: 10px;\'>去授权</a>'; ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="label">服务器IP</span>
                    <span class="value"><?php echo $_SERVER['SERVER_ADDR'] ?? '127.0.0.1'; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">服务器端口</span>
                    <span class="value"><?php echo $_SERVER['SERVER_PORT']; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">服务器软件</span>
                    <span class="value"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">站点物理路径</span>
                    <span class="value"><?php echo $_SERVER['DOCUMENT_ROOT']; ?></span>
                </div>
            </div>
            
            <div class="info-card">
                <h3>系统环境</h3>
                <div class="info-item">
                    <span class="label">PHP版本</span>
                    <span class="value"><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Zend版本</span>
                    <span class="value"><?php echo zend_version(); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">操作系统</span>
                    <span class="value"><?php echo PHP_OS; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">系统时间</span>
                    <span class="value"><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">系统时区</span>
                    <span class="value"><?php echo date_default_timezone_get(); ?></span>
                </div>
                <div class="info-item">
                    <span class="label">当前软件版本</span>
                    <span class="value"><?php echo $software_version; ?></span>
                </div>
            </div>
            
            <div class="info-card">
                <h3>数据库信息</h3>
                <div class="info-item">
                    <span class="label">数据库类型</span>
                    <span class="value">MySQL</span>
                </div>
                <div class="info-item">
                    <span class="label">数据库版本</span>
                    <span class="value"><?php echo $mysql_version; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">数据库名称</span>
                    <span class="value"><?php echo $dbname; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">数据库用户名</span>
                    <span class="value"><?php echo $username; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">证书总数</span>
                    <span class="value"><?php echo $cert_count; ?></span>
                </div>
            </div>
            
            <div class="info-card">
                <h3>邮件配置</h3>
                <div class="info-item">
                    <span class="label">SMTP服务器</span>
                    <span class="value"><?php echo $mail_config['smtp_host']; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">SMTP端口</span>
                    <span class="value"><?php echo $mail_config['smtp_port']; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">安全类型</span>
                    <span class="value"><?php echo $mail_config['smtp_secure']; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">发件人邮箱</span>
                    <span class="value"><?php echo $mail_config['smtp_from']; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">发件人名称</span>
                    <span class="value"><?php echo $mail_config['smtp_from_name']; ?></span>
                </div>
            </div>
            
            <div class="info-card">
                <h3>系统更新</h3>
                <div class="info-item">
                    <span class="label">当前版本</span>
                    <span class="value"><?php echo $software_version; ?></span>
                </div>
                <div class="info-item">
                    <span class="label">检查更新</span>
                    <span class="value">
                        <?php if ($is_authorized): ?>
                        <button onclick="checkUpdate()" style="padding: 8px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 20px; cursor: pointer; font-size: 14px;">检查更新</button>
                        <?php else: ?>
                        <button disabled style="padding: 8px 20px; background: #ccc; color: #666; border: none; border-radius: 20px; cursor: not-allowed; font-size: 14px;">未授权</button>
                        <?php endif; ?>
                    </span>
                </div>
                <div id="update-result" class="info-item" style="display: none;">
                <span class="label">更新状态</span>
                <span class="value" id="update-status"></span>
            </div>
            <div id="update-progress" class="info-item" style="display: none;">
                <span class="label">更新进度</span>
                <span class="value">
                    <div style="width: 200px; height: 20px; background: #f0f0f0; border-radius: 10px; overflow: hidden;">
                        <div id="progress-bar" style="height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 0%; transition: width 0.3s ease;"></div>
                    </div>
                    <div id="progress-text" style="font-size: 12px; color: #666; margin-top: 5px;">0%</div>
                </span>
            </div>
            <div id="update-button" class="info-item" style="display: none;">
                <span class="label"></span>
                <span class="value">
                    <button onclick="startUpdate()" style="padding: 8px 20px; background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; border: none; border-radius: 20px; cursor: pointer; font-size: 14px;">立即更新</button>
                </span>
            </div>
            </div>
        </div>
    </div>
    
    <script>
    function checkUpdate() {
        var btn = event.target;
        btn.disabled = true;
        btn.textContent = '检查中...';
        
        var resultDiv = document.getElementById('update-result');
        var statusSpan = document.getElementById('update-status');
        var progressDiv = document.getElementById('update-progress');
        var buttonDiv = document.getElementById('update-button');
        
        resultDiv.style.display = 'flex';
        progressDiv.style.display = 'none';
        buttonDiv.style.display = 'none';
        statusSpan.textContent = '正在连接更新服务器...';
        
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'check_update.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                btn.disabled = false;
                btn.textContent = '检查更新';
                
                if (xhr.status == 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.has_update) {
                            statusSpan.textContent = '发现新版本 ' + data.latest_version + '，当前版本 ' + data.current_version;
                            statusSpan.style.color = '#e74c3c';
                            buttonDiv.style.display = 'flex';
                        } else {
                            statusSpan.textContent = '已是最新版本';
                            statusSpan.style.color = '#27ae60';
                        }
                    } catch (e) {
                        statusSpan.textContent = '检查更新失败：解析数据错误';
                        statusSpan.style.color = '#e74c3c';
                    }
                } else {
                    statusSpan.textContent = '检查更新失败：网络错误';
                    statusSpan.style.color = '#e74c3c';
                }
            }
        };
        xhr.timeout = 10000;
        xhr.ontimeout = function() {
            btn.disabled = false;
            btn.textContent = '检查更新';
            statusSpan.textContent = '检查更新失败：连接超时';
            statusSpan.style.color = '#e74c3c';
        };
        xhr.send();
    }
    
    async function startUpdate() {
        var resultDiv = document.getElementById('update-result');
        var statusSpan = document.getElementById('update-status');
        var progressDiv = document.getElementById('update-progress');
        var buttonDiv = document.getElementById('update-button');
        var progressBar = document.getElementById('progress-bar');
        var progressText = document.getElementById('progress-text');
        
        buttonDiv.style.display = 'none';
        progressDiv.style.display = 'flex';
        progressBar.style.width = '0%';
        progressText.textContent = '0%';
        statusSpan.textContent = '正在获取更新信息...';
        statusSpan.style.color = '#667eea';
        
        try {
            var formData = new FormData();
            formData.append('action', 'get_update_info');
            
            var response = await fetch('do_update.php', {
                method: 'POST',
                body: formData
            });
            var data = await response.json();
            
            if (data.status == 'error') {
                throw new Error(data.message);
            }
            
            if (!data.zip_url) {
                throw new Error(data.message || '未找到更新文件');
            }
            
            statusSpan.textContent = '正在下载更新包...';
            progressBar.style.width = '10%';
            progressText.textContent = '10%';
            
            formData = new FormData();
            formData.append('action', 'download_zip');
            formData.append('zip_url', data.zip_url);
            
            response = await fetch('do_update.php', {
                method: 'POST',
                body: formData
            });
            data = await response.json();
            
            if (data.status == 'error') {
                throw new Error(data.message);
            }
            
            progressBar.style.width = '50%';
            progressText.textContent = '50%';
            statusSpan.textContent = '正在解压更新包...';
            
            formData = new FormData();
            formData.append('action', 'extract_zip');
            
            response = await fetch('do_update.php', {
                method: 'POST',
                body: formData
            });
            data = await response.json();
            
            if (data.status == 'error') {
                throw new Error(data.message);
            }
            
            var totalFiles = data.total;
            
            progressBar.style.width = '60%';
            progressText.textContent = '60%';
            statusSpan.textContent = '正在更新文件... (0/' + totalFiles + ')';
            
            var failedFiles = [];
            
            for (var i = 0; i < totalFiles; i++) {
                formData = new FormData();
                formData.append('action', 'update_file');
                
                response = await fetch('do_update.php', {
                    method: 'POST',
                    body: formData
                });
                data = await response.json();
                
                if (data.status == 'error') {
                    throw new Error(data.message);
                }
                
                if (data.status == 'continue') {
                    var percent = Math.round(60 + ((data.index + 1) / totalFiles) * 35);
                    progressBar.style.width = percent + '%';
                    progressText.textContent = percent + '%';
                    statusSpan.textContent = '正在更新文件... (' + (data.index + 1) + '/' + totalFiles + ') - ' + data.file;
                    
                    if (data.error) {
                        failedFiles.push(data.file);
                    }
                } else if (data.status == 'complete') {
                    break;
                }
            }
            
            progressBar.style.width = '100%';
            progressText.textContent = '100%';
            
            if (failedFiles.length > 0) {
                statusSpan.textContent = '更新完成！部分文件失败：' + failedFiles.join(', ');
                statusSpan.style.color = '#f39c12';
            } else {
                statusSpan.textContent = '更新完成！系统将自动刷新...';
                statusSpan.style.color = '#27ae60';
            }
            
            setTimeout(function() {
                location.reload();
            }, 3000);
            
        } catch (e) {
            statusSpan.textContent = '更新失败：' + e.message;
            statusSpan.style.color = '#e74c3c';
            progressDiv.style.display = 'none';
            buttonDiv.style.display = 'flex';
        }
    }
    </script>
    
    <footer>
        <p><?php include_once '../config.php'; echo isset($footer_text) ? htmlspecialchars($footer_text) : '© 2026 云天安全 版权所有'; ?></p>
    </footer>
</body>
</html>