<?php
$db_config_file = __DIR__ . '/db_config.php';
$config_file = __DIR__ . '/config.php';

if (!file_exists($db_config_file) || !file_exists($config_file)) {
    header('Location: install/');
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
    header('Location: install/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include_once 'config.php'; ?>
    <title><?php echo isset($site_title) ? htmlspecialchars($site_title) : '云天安全'; ?> - <?php echo isset($site_subtitle) ? htmlspecialchars($site_subtitle) : '动态安全网站检测平台'; ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔒</text></svg>" type="image/svg+xml">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            min-height: 100vh;
        }
        
        header {
            background: #1a1a3e;
            color: white;
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        nav {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }
        
        nav .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        nav .logo-circle {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ff6b00 0%, #ff9500 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            animation: rotate 10s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        nav .logo-text {
            font-size: 18px;
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
            font-size: 15px;
            transition: color 0.3s ease;
        }
        
        nav a:hover {
            color: #ff9500;
        }
        
        .hero {
            background: linear-gradient(135deg, #1a1a3e 0%, #2d1b69 50%, #1a1a3e 100%);
            padding: 120px 0 100px;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            right: -10%;
            top: -10%;
            width: 600px;
            height: 600px;
            border: 1px solid rgba(100, 100, 255, 0.1);
            border-radius: 50%;
        }
        
        .hero::after {
            content: '';
            position: absolute;
            right: 5%;
            top: 10%;
            width: 400px;
            height: 400px;
            border: 1px solid rgba(100, 100, 255, 0.15);
            border-radius: 50%;
        }
        
        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .hero-content {
            flex: 1;
            padding-right: 50px;
        }
        
        .hero-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #ff6b00 0%, #ffc107 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-subtitle {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.8;
            margin-bottom: 30px;
        }
        
        .hero-stats {
            display: flex;
            gap: 40px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-item .number {
            font-size: 32px;
            font-weight: bold;
            color: #ff9500;
        }
        
        .stat-item .label {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 5px;
        }
        
        .hero-visual {
            flex: 1;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .main-logo {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #ff6b00 0%, #ff9500 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 80px;
            font-weight: bold;
            color: white;
            animation: rotate 20s linear infinite;
            box-shadow: 0 0 60px rgba(255, 107, 0, 0.4);
        }
        
        .main-logo::before {
            content: '';
            position: absolute;
            width: 220px;
            height: 220px;
            border: 2px solid rgba(255, 107, 0, 0.3);
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0; }
        }
        
        .intro-section {
            padding: 80px 0;
            background: #f5f7fa;
        }
        
        .intro-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }
        
        .intro-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
        }
        
        .intro-text {
            font-size: 16px;
            color: #666;
            line-height: 2;
            text-align: justify;
        }
        
        .features-section {
            padding: 80px 0;
            background: white;
        }
        
        .features-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }
        
        .feature-card {
            text-align: center;
            padding: 40px 20px;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .feature-icon svg {
            width: 60px;
            height: 60px;
            stroke: #666;
            stroke-width: 1.5;
            fill: none;
        }
        
        .feature-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        
        .feature-desc {
            font-size: 14px;
            color: #666;
            line-height: 1.8;
        }
        
        .certificate-section {
            padding: 80px 0;
            background: #f5f7fa;
        }
        
        .certificate-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .certificate {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .certificate-header {
            background: linear-gradient(90deg, #8B4513 0%, #CD853F 50%, #8B4513 100%);
            padding: 15px;
            text-align: center;
        }
        
        .certificate-header nav {
            display: inline-flex;
            gap: 40px;
            background: white;
            padding: 5px 60px;
            border-radius: 25px;
        }
        
        .certificate-header a {
            color: #8B4513;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        
        .certificate-header a.active {
            background: #DC143C;
            color: white;
        }
        
        .certificate-border {
            border: 8px solid #CD853F;
            margin: 20px;
            position: relative;
        }
        
        .certificate-border::before {
            content: '';
            position: absolute;
            top: 4px;
            left: 4px;
            right: 4px;
            bottom: 4px;
            border: 2px solid #D4AF37;
            pointer-events: none;
        }
        
        .certificate-content {
            padding: 40px;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 5px,
                rgba(212, 175, 55, 0.03) 5px,
                rgba(212, 175, 55, 0.03) 10px
            );
            text-align: center;
            position: relative;
        }
        
        .cert-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .cert-logo-circle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            font-weight: bold;
        }
        
        .cert-logo-text {
            background: #DC143C;
            color: white;
            padding: 8px 20px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .cert-company {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .cert-type {
            color: #666;
            margin-bottom: 10px;
        }
        
        .cert-website {
            font-size: 18px;
            color: #667eea;
            margin-bottom: 25px;
        }
        
        .cert-desc {
            color: #555;
            line-height: 1.8;
            margin-bottom: 25px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .cert-stars {
            color: #FFD700;
            font-size: 22px;
            margin-bottom: 25px;
        }
        
        .cert-info {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .cert-info-item {
            text-align: left;
            color: #666;
            font-size: 13px;
        }
        
        .cert-info-item strong {
            color: #333;
        }
        
        .cert-stamp {
            position: absolute;
            right: 40px;
            bottom: 40px;
            width: 100px;
            height: 100px;
            border: 3px solid #DC143C;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #DC143C;
            font-size: 11px;
            transform: rotate(-15deg);
        }
        
        .cert-stamp::before {
            content: '★';
            font-size: 25px;
            margin-bottom: 3px;
        }
        
        .cert-watermark {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(0,0,0,0.08);
            font-size: 16px;
            font-weight: bold;
        }
        
        footer {
            background: #1a1a3e;
            color: white;
            text-align: center;
            padding: 30px;
        }
        
        footer p {
            opacity: 0.7;
            font-size: 14px;
        }
        
        .search-section {
            padding: 80px 0;
            background: white;
        }
        
        .search-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }
        
        .search-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }
        
        .search-desc {
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
        }
        
        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-form input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .search-form input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .search-form button {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .search-result {
            margin-top: 30px;
            text-align: left;
            background: #f9f9f9;
            border-radius: 12px;
            padding: 25px;
            display: none;
        }
        
        .search-result.show {
            display: block;
        }
        
        .result-cert {
            background: white;
            border: 2px solid #FFD700;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .result-cert-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #ddd;
        }
        
        .result-cert-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            font-weight: bold;
        }
        
        .result-cert-info h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }
        
        .result-cert-info p {
            font-size: 13px;
            color: #666;
        }
        
        .result-cert-code {
            font-family: monospace;
            background: #f5f5f5;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 13px;
            color: #666;
        }
        
        .result-cert-link {
            display: inline-block;
            margin-top: 10px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .result-cert-link:hover {
            text-decoration: underline;
        }
        
        .result-footer-code {
            background: white;
            border-radius: 10px;
            padding: 20px;
        }
        
        .result-footer-code h4 {
            font-size: 14px;
            color: #333;
            margin-bottom: 15px;
        }
        
        .result-footer-code .code-box {
            background: #2d2d2d;
            color: #ccc;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 11px;
            line-height: 1.6;
            overflow-x: auto;
            word-break: break-all;
        }
        
        .result-footer-code .copy-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .result-footer-code .copy-btn:hover {
            background: #219a52;
        }
        
        .result-footer-code .copy-btn.copied {
            background: #27ae60;
        }
        
        .search-error {
            background: #fff3cd;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .search-error .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .hero-container {
                flex-direction: column;
                text-align: center;
            }
            
            .hero-content {
                padding-right: 0;
                margin-bottom: 40px;
            }
            
            .hero-title {
                font-size: 36px;
            }
            
            .hero-stats {
                justify-content: center;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .cert-info {
                flex-direction: column;
                align-items: center;
            }
            
            .cert-stamp {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <div class="logo-circle">安</div>
                <div class="logo-text"><?php echo isset($site_title) ? htmlspecialchars($site_title) : '云天安全'; ?></div>
            </div>
            <ul>
                <li><a href="index.php">首页</a></li>
                <li><a href="apply.php">用户申请</a></li>
                <li><a href="admin/login.php">后台管理</a></li>
            </ul>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">安全，你我</h1>
                <p class="hero-subtitle"><?php echo isset($site_subtitle) ? htmlspecialchars($site_subtitle) : '云天安全提供专业的网站安全检测服务，通过对域名、网站、备案登记信息进行审核，验证网站真实身份。'; ?></p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="number">100%</div>
                        <div class="label">网站域名信息验证</div>
                    </div>
                    
                </div>
            </div>
            <div class="hero-visual">
                <div class="main-logo">安</div>
            </div>
        </div>
    </section>

    <section class="intro-section">
        <div class="intro-container">
            <h2 class="intro-title">什么是云天安全</h2>
            <p class="intro-text">云天安全提供专业的网站安全检测服务，通过对域名、网站、备案登记信息进行审核，验证网站真实身份。。</p>
        </div>
    </section>

    <section class="features-section">
        <div class="features-container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">网站域名信息验证</h3>
                    <p class="feature-desc">验证网站域名的真实性和合法性，确保域名信息安全。</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">云天安全检测申请</h3>
                    <p class="feature-desc">个体，企业可申请云天安全检测认证，通过审核后获得认证标识。</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">网站运行监控</h3>
                    <p class="feature-desc">实时监控网站运行状态，保障网站稳定可靠运行。</p>
                </div>
            </div>
        </div>
    </section>

    <section class="search-section">
        <div class="search-container">
            <h2 class="search-title">证书查询</h2>
            <p class="search-desc">输入您的证书编号，查询认证信息及获取页脚代码</p>
            <form class="search-form" id="searchForm">
                <input type="text" id="certCode" placeholder="请输入证书编号，如：cert20260428123456" required>
                <button type="submit">查询</button>
            </form>
            <div class="search-result" id="searchResult"></div>
        </div>
    </section>

    <footer>
        <p><?php echo isset($footer_text) ? htmlspecialchars($footer_text) : '© 2026 云天安全 版权所有'; ?> <a href="https://w.ch1633.com/?page=public&site=7ffdf1c6534728b4" target="_blank" style="color: #666; text-decoration: none; font-size: 12px;">站点统计</a></p>
        <?php if (isset($icp_number) && !empty($icp_number)): ?>
        <a href="https://beian.miit.gov.cn" target="_blank" style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($icp_number); ?></a>
        <?php endif; ?>
        
        <?php if (isset($service_phone) && !empty($service_phone)): ?>
        <p style="font-size: 12px; opacity: 0.6; margin-top: 10px;">客服热线：<?php echo htmlspecialchars($service_phone); ?> | 邮箱：<?php echo isset($contact_email) ? htmlspecialchars($contact_email) : 'i@ch1633.com'; ?></p>
        <?php endif; ?>
        
        <?php if (isset($footer_html) && !empty($footer_html)): ?>
        <?php echo $footer_html; ?>
        <?php else: ?>
        <a href="http://kf.ch1633.com/certificate.php?domain=kf.ch1633.com" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); padding: 4px 10px; border-radius: 4px; text-decoration: none; box-shadow: 0 2px 6px rgba(255, 215, 0, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 3px 10px rgba(255, 215, 0, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(255, 215, 0, 0.3)';"><span style="width: 22px; height: 22px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #FFA500; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">V</span><span style="text-align: left; display: inline-flex; flex-direction: column;"><span style="font-size: 11px; font-weight: bold; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); display: block; line-height: 1.1;">安全认证</span><span style="font-size: 8px; color: rgba(255, 255, 255, 0.9); letter-spacing: 0.5px; display: block; line-height: 1.1;">★★★★★</span></span></a>
        <?php endif; ?>
    </footer>
    <!-- 云天系统 统计代码 -->
<script>
(function() {
    var script = document.createElement('script');
    script.src = 'https://w.ch1633.com/assets/js/analytics.js';
    script.defer = true;
    script.setAttribute('data-site-key', '7ffdf1c6534728b4');
    script.setAttribute('data-endpoint', 'https://w.ch1633.com/api/track.php');
    document.head.appendChild(script);
})();
</script>
<!-- 云天系统 统计代码结束 -->
    <script>
    document.getElementById('searchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var certCode = document.getElementById('certCode').value.trim();
        var resultDiv = document.getElementById('searchResult');
        
        resultDiv.innerHTML = '<p style="text-align: center; color: #666;">正在查询...</p>';
        resultDiv.classList.add('show');
        
        fetch('lookup_cert.php?code=' + encodeURIComponent(certCode))
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    var cert = data.cert;
                    var footerCode = '<a href="http://kf.ch1633.com/certificate.php?domain=' + cert.website + '" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); padding: 4px 10px; border-radius: 4px; text-decoration: none; box-shadow: 0 2px 6px rgba(255, 215, 0, 0.3); transition: all 0.3s ease;"><span style="width: 22px; height: 22px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #FFA500; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">V</span><span style="text-align: left; display: inline-flex; flex-direction: column;"><span style="font-size: 11px; font-weight: bold; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); display: block; line-height: 1.1;">安全认证</span><span style="font-size: 8px; color: rgba(255, 255, 255, 0.9); letter-spacing: 0.5px; display: block; line-height: 1.1;">★★★★★</span></span></a>';
                    
                    resultDiv.innerHTML = 
                        '<div class="result-cert">' +
                            '<div class="result-cert-header">' +
                                '<div class="result-cert-logo">V</div>' +
                                '<div class="result-cert-info">' +
                                    '<h3>' + cert.company_name + '</h3>' +
                                    '<p>' + (cert.apply_type === 'personal' ? '个人认证' : '企业认证') + '</p>' +
                                '</div>' +
                            '</div>' +
                            '<p><strong>认证网站：</strong><a href="http://' + cert.website + '" target="_blank" style="color: #667eea;">' + cert.website + '</a></p>' +
                            '<p><strong>有效期：</strong>' + cert.valid_from + ' 至 ' + cert.valid_to + '</p>' +
                            '<p><strong>证书编号：</strong><span class="result-cert-code">' + cert.certificate_code + '</span></p>' +
                            '<a href="http://kf.ch1633.com/certificate.php?domain=' + cert.website + '" target="_blank" class="result-cert-link">查看完整证书 →</a>' +
                        '</div>' +
                        '<div class="result-footer-code">' +
                            '<h4>📌 网站页脚认证图标代码（复制即可使用）</h4>' +
                            '<div class="code-box" id="footerCodeBox">' + escapeHtml(footerCode) + '</div>' +
                            '<textarea id="footerCodeRaw" style="display: none;">' + footerCode + '</textarea>' +
                            '<button class="copy-btn" onclick="copyFooterCode()">复制代码</button>' +
                        '</div>';
                } else {
                    resultDiv.innerHTML = 
                        '<div class="search-error">' +
                            '<div class="icon">⚠️</div>' +
                            '<p>' + data.message + '</p>' +
                        '</div>';
                }
            })
            .catch(function(err) {
                resultDiv.innerHTML = 
                    '<div class="search-error">' +
                        '<div class="icon">❌</div>' +
                        '<p>查询失败，请稍后重试</p>' +
                    '</div>';
            });
    });
    
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function copyFooterCode() {
        var code = document.getElementById('footerCodeRaw').value;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(code).then(function() {
                showCopySuccess();
            }).catch(function() {
                fallbackCopy(code);
            });
        } else {
            fallbackCopy(code);
        }
    }
    
    function showCopySuccess() {
        var btn = document.querySelector('.copy-btn');
        btn.textContent = '已复制！';
        btn.classList.add('copied');
        setTimeout(function() {
            btn.textContent = '复制代码';
            btn.classList.remove('copied');
        }, 2000);
    }
    
    function fallbackCopy(code) {
        var textarea = document.createElement('textarea');
        textarea.value = code;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showCopySuccess();
        } catch (err) {
            alert('复制失败，请手动复制代码');
        }
        document.body.removeChild(textarea);
    }
    </script>
</body>
</html>