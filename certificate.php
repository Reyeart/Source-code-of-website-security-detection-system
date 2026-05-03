<?php
include_once 'config.php';

$domain = isset($_GET['domain']) ? trim($_GET['domain']) : '';
$certificate = null;

if (!empty($domain)) {
    $domain = preg_replace('/^https?:\/\//i', '', $domain);
    $sql = "SELECT * FROM certificates WHERE website = '$domain'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $certificate = $result->fetch_assoc();
    }
}

function checkWebsiteSafety($url) {
    $isSafe = true;
    $riskInfo = '';
    
    if (!empty($url)) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $url = 'http://' . $url;
        }
        
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $apiUrl = 'https://api.360.cn/site_check/check';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['url' => $url]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && $response) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] != 'safe') {
                    $isSafe = false;
                    $riskInfo = isset($data['msg']) ? $data['msg'] : '检测到安全风险';
                }
            }
        }
    }
    
    return ['safe' => $isSafe, 'risk_info' => $riskInfo];
}

$safetyResult = ['safe' => true, 'risk_info' => ''];
if ($certificate && !empty($certificate['website'])) {
    $safetyResult = checkWebsiteSafety($certificate['website']);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>认证证书 - <?php echo $certificate ? htmlspecialchars($certificate['company_name']) : '云天安全检测中心'; ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔒</text></svg>" type="image/svg+xml">
    <body oncontextmenu="return false" onselectstart="return false" ondragstart="return false" oncopy="return false" oncut="return false">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', 'SimHei', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .cert-container {
            width: 95%;
            max-width: 1100px;
            min-height: 550px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 2px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .cert-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #FFD700 0%, #FFA500 50%, #FFD700 100%);
        }
        
        .cert-border {
            flex: 1;
            border: 3px double #D4AF37;
            margin: 15px;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .cert-border::before {
            content: '';
            position: absolute;
            top: 6px;
            left: 6px;
            right: 6px;
            bottom: 6px;
            border: 1px solid rgba(212, 175, 55, 0.2);
            pointer-events: none;
        }
        
        .corner {
            position: absolute;
            width: 35px;
            height: 35px;
            border-color: #D4AF37;
            border-style: solid;
            border-width: 0;
            opacity: 0.6;
        }
        
        .corner-top-left {
            top: 8px;
            left: 8px;
            border-top-width: 3px;
            border-left-width: 3px;
        }
        
        .corner-top-right {
            top: 8px;
            right: 8px;
            border-top-width: 3px;
            border-right-width: 3px;
        }
        
        .corner-bottom-left {
            bottom: 8px;
            left: 8px;
            border-bottom-width: 3px;
            border-left-width: 3px;
        }
        
        .corner-bottom-right {
            bottom: 8px;
            right: 8px;
            border-bottom-width: 3px;
            border-right-width: 3px;
        }
        
        .cert-content {
            flex: 1;
            padding: 25px 30px;
            background-image: 
                radial-gradient(circle at 2px 2px, rgba(212, 175, 55, 0.08) 1px, transparent 0),
                radial-gradient(circle at 16px 16px, rgba(212, 175, 55, 0.04) 1px, transparent 0);
            background-size: 18px 18px;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        .cert-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .cert-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            padding: 10px 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 18px rgba(255, 215, 0, 0.3);
            position: relative;
        }
        
        .cert-badge::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 11px;
            pointer-events: none;
        }
        
        .badge-icon {
            width: 42px;
            height: 42px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #FFA500;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .badge-text {
            text-align: left;
        }
        
        .badge-title {
            font-size: 16px;
            font-weight: bold;
            color: white;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        }
        
        .badge-stars {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 2px;
        }
        
        .tabs-container {
            display: flex;
            justify-content: center;
            gap: 40px;
            padding: 15px 30px;
            position: relative;
            z-index: 1;
            background: #fff;
            border-bottom: 2px solid rgba(212, 175, 55, 0.3);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            margin: 0 auto 10px;
            width: 95%;
            max-width: 1100px;
            border-radius: 10px 10px 0 0;
        }
        
        .tab {
            font-size: 14px;
            color: #666;
            cursor: pointer;
            padding-bottom: 8px;
            position: relative;
            transition: color 0.3s ease;
        }
        
        .tab:hover {
            color: #c00;
        }
        
        .tab.active {
            color: #c00;
            font-weight: bold;
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: #c00;
        }
        
        .golden-banner {
            background: linear-gradient(90deg, #FFA500 0%, #FFD700 30%, #FFD700 70%, #FFA500 100%);
            padding: 12px 35px;
            margin: 0 -30px;
            clip-path: polygon(1% 0%, 99% 0%, 100% 50%, 99% 100%, 1% 100%, 0% 50%);
            text-align: center;
            margin-bottom: 25px;
            position: relative;
        }
        
        .golden-banner::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(255,255,255,0.2) 0%, transparent 50%, rgba(0,0,0,0.1) 100%);
            pointer-events: none;
        }
        
        .golden-banner h2 {
            color: white;
            font-size: 18px;
            font-weight: bold;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        
        .cert-body {
            flex: 1;
            display: flex;
            gap: 30px;
        }
        
        .cert-left {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .cert-right {
            width: 280px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        
        .company-info-center {
            text-align: center;
            margin-top: -10px;
            margin-bottom: 22px;
            padding-bottom: 18px;
            border-bottom: 2px solid rgba(212, 175, 55, 0.3);
            width: 100%;
        }
        
        .company-name-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 18px;
            margin-bottom: 12px;
            flex-wrap: wrap;
            text-align: center;
        }
        
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #2c2c2c;
            letter-spacing: 1px;
        }
        
        .cert-badge-mini {
            font-size: 12px;
            font-weight: bold;
            padding: 4px 12px;
            border-radius: 4px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        
        .cert-badge-mini.safe {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: white;
        }
        
        .cert-badge-mini.danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        .company-website {
            font-size: 14px;
            color: #555;
            text-align: center;
        }
        
        .company-website span {
            color: #333;
            font-weight: 500;
        }
        
        .company-website a {
            color: #4a6cf7;
            text-decoration: none;
            word-break: break-all;
        }
        
        .verify-section {
            background: rgba(255,255,255,0.9);
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 18px;
            flex: 1;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        
        .verify-title {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #e0e0e0;
        }
        
        .verify-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .verify-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #555;
            padding: 6px;
            background: rgba(39, 174, 96, 0.06);
            border-radius: 5px;
        }
        
        .verify-item .check {
            color: #27ae60;
            font-size: 13px;
        }
        
        .verify-item .cross {
            color: #e74c3c;
            font-size: 13px;
        }
        
        .security-hint {
            font-size: 11px;
            color: #888;
            text-align: center;
            line-height: 1.5;
            padding-top: 10px;
            border-top: 1px dashed #e0e0e0;
            margin-top: 10px;
        }
        
        .result-box {
            background: rgba(255,255,255,0.9);
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 18px;
            width: 100%;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            margin-bottom: 20px;
            margin-top: 72px;
        }
        
        .result-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
        }
        
        .result-row:not(:last-child) {
            border-bottom: 1px dashed #f0f0f0;
        }
        
        .result-label {
            font-size: 12px;
            color: #666;
        }
        
        .result-value {
            font-size: 12px;
            font-weight: 500;
            color: #333;
        }
        
        .result-value.success {
            color: #27ae60;
        }
        
        .result-value.danger {
            color: #e74c3c;
        }
        
        .result-value .check {
            color: #27ae60;
            margin-right: 4px;
            font-size: 14px;
        }
        
        .result-value .cross {
            color: #e74c3c;
            margin-right: 4px;
            font-size: 14px;
        }
        
        .cert-status {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .cert-status span {
            font-size: 12px;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .cert-status .safe {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: white;
        }
        
        .cert-status .danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }
        
        .stamp-container {
            position: relative;
            width: 100%;
        }
        
        .stamp {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            bottom: -80px;
            right: -5px;
            transform: rotate(-3deg);
        }
        
        .stamp-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        
        .cert-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            margin-top: auto;
        }
        
        .footer-left {
            font-size: 11px;
            color: #666;
            line-height: 1.8;
            margin-left: 38px;
        }
        
        .footer-left strong {
            color: #333;
        }
        
        .footer-right {
            font-size: 11px;
            color: #666;
            text-align: right;
            line-height: 1.8;
        }
        
        .footer-right strong {
            color: #333;
        }
        
        .cert-not-found {
            text-align: center;
            padding: 60px 40px;
            width: 100%;
            max-width: 500px;
        }
        
        .not-found-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.18);
        }
        
        .not-found-icon span {
            font-size: 48px;
        }
        
        .not-found-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 12px;
        }
        
        .not-found-desc {
            font-size: 14px;
            color: #666;
            line-height: 1.7;
            margin-bottom: 30px;
        }
        
        .not-found-btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 18px rgba(102, 126, 234, 0.35);
        }
        
        .not-found-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.45);
        }
        
        .not-found-hint {
            font-size: 12px;
            color: #999;
            margin-top: 18px;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .cert-container {
                box-shadow: none;
                width: 100%;
                max-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .cert-body {
                flex-direction: column;
            }
            .cert-right {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                border-top: 2px solid rgba(212, 175, 55, 0.3);
                padding-top: 20px;
            }
            .result-box {
                margin-bottom: 0;
                width: auto;
            }
            .stamp {
                margin-left: 20px;
            }
        }
    </style>
</head>
<body>
    <?php if ($certificate): ?>
    <div class="cert-container">
        <div class="cert-border">
            <div class="corner corner-top-left"></div>
            <div class="corner corner-top-right"></div>
            <div class="corner corner-bottom-left"></div>
            <div class="corner corner-bottom-right"></div>
            
            <div class="cert-content">
                <div class="cert-header">
                    <div class="cert-badge">
                        <div class="badge-icon">V</div>
                        <div class="badge-text">
                            <div class="badge-title">安全认证</div>
                            <div class="badge-stars">★★★★★</div>
                        </div>
                    </div>
                    
                    <div class="golden-banner">
                        <h2><?php echo htmlspecialchars($certificate['company_name']); ?>-认证证书(<?php echo $certificate['apply_type'] === 'personal' ? '个人' : '企业'; ?>)</h2>
                    </div>
                </div>
                
                <div class="company-info-center">
                    <div class="company-name-row">
                        <span class="company-name"><?php echo htmlspecialchars($certificate['company_name']); ?></span>
                        <span class="cert-badge-mini <?php echo $safetyResult['safe'] ? 'safe' : 'danger'; ?>">
                            <?php echo $safetyResult['safe'] ? '安全认证' : '认证失效'; ?>
                        </span>
                    </div>
                    <div class="company-website">
                        <span>认证网站：</span>
                        <a href="http://<?php echo htmlspecialchars($certificate['website']); ?>" target="_blank">
                            <?php echo htmlspecialchars(preg_replace('/^https?:\/\//i', '', $certificate['website'])); ?>
                        </a>
                    </div>
                </div>
                
                <div class="cert-body">
                    <div class="cert-left">
                        <div class="verify-section">
                            <div class="verify-title">✓ 已完成以下信息核验</div>
                            <div class="verify-grid">
                                <div class="verify-item"><span class="<?php echo !empty($certificate['icp_number']) ? 'check' : 'cross'; ?>"><?php echo !empty($certificate['icp_number']) ? '✓' : '✗'; ?></span>ICP备案信息</div>
                                <div class="verify-item"><span class="check">✓</span>域名注册信息</div>
                                <div class="verify-item"><span class="<?php echo $certificate['apply_type'] === 'enterprise' ? 'check' : 'cross'; ?>"><?php echo $certificate['apply_type'] === 'enterprise' ? '✓' : '✗'; ?></span>工商登记信息</div>
                                <div class="verify-item"><span class="<?php echo $certificate['apply_type'] === 'enterprise' ? 'check' : 'cross'; ?>"><?php echo $certificate['apply_type'] === 'enterprise' ? '✓' : '✗'; ?></span>业务资质许可</div>
                                <div class="verify-item"><span class="check">✓</span>身份证明信息</div>
                                <div class="verify-item"><span class="check">✓</span>网站信誉信息</div>
                                <div class="verify-item"><span class="check">✓</span>网站安全监测</div>
                                <div class="verify-item"><span class="check">✓</span>网站漏洞检测</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="cert-right">
                        <div class="stamp-container">
                            <div class="result-box">
                                <div class="result-row">
                                    <span class="result-label">网站实时监测</span>
                                    <?php if ($safetyResult['safe']): ?>
                                    <span class="result-value success"><span class="check">✓</span>安全</span>
                                    <?php else: ?>
                                    <span class="result-value danger"><span class="cross">✗</span>有风险</span>
                                    <?php endif; ?>
                                </div>
                                <div class="result-row">
                                    <span class="result-label">监测来源</span>
                                    <span class="result-value">AI安全检测</span>
                                </div>
                                <div class="result-row">
                                    <span class="result-label">证书编号</span>
                                    <span class="result-value" style="font-family: monospace; font-size: 11px; color: #666;"><?php echo htmlspecialchars($certificate['certificate_code']); ?></span>
                                </div>
                                <div class="result-row">
                                    <span class="result-label">有效期</span>
                                    <span class="result-value"><?php echo $certificate['valid_from']; ?> - <?php echo $certificate['valid_to']; ?></span>
                                </div>
                                <div class="result-row">
                                    <span class="result-label">ICP备案号</span>
                                    <span class="result-value"><?php if (!empty($certificate['icp_number'])): ?><a href="https://beian.miit.gov.cn/" target="_blank" style="color:#4a6cf7;text-decoration:none;"><?php echo htmlspecialchars($certificate['icp_number']); ?></a><?php else: ?>未备案<?php endif; ?></span>
                                </div>
                                <div class="result-row">
                                    <span class="result-label">公网安备号</span>
                                    <span class="result-value"><?php if (!empty($certificate['beian_number'])): ?><a href="https://beian.mps.gov.cn/#/query/webSearch" target="_blank" style="color:#4a6cf7;text-decoration:none;"><?php echo htmlspecialchars($certificate['beian_number']); ?></a><?php else: ?>未申请<?php endif; ?></span>
                                </div>
                            </div>
                            
                            <div class="stamp">
                                <img src="zyz.png" alt="认证章印" class="stamp-image">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="cert-footer">
                    <div class="footer-left">
                        <div class="security-hint">安全防伪提示：请确认本页面是以https开头，即应用数据加密传输功能保证信息传输的安全性。</div>
                        <div class="security-hint">举报投诉信息：客户支持及投诉举报, 电话:130-4710-6211, 邮箱: i@ch1633.com</div>
                    </div>
                    <div class="footer-right">
                        <div><strong>审核方</strong>：云天安全检测中心</div>
                        <div><strong>签发时间</strong>：<?php echo $certificate['valid_from']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="cert-not-found">
        <div class="not-found-icon">
            <span>⚠️</span>
        </div>
        <h2 class="not-found-title">该网站不存在认证证书</h2>
        <p class="not-found-desc">
            您访问的网站尚未获得安全认证，或证书已被撤销。<br/>
            请申请认证以获得权威安全认证标识。
        </p>
        <a href="apply.php" class="not-found-btn">申请认证证书</a>
        <p class="not-found-hint">申请后我们将在3个工作日内进行审核</p>
    </div>
    <?php endif; ?>
</body>
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
</html>