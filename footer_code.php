<?php
$default_domain = isset($_GET['domain']) ? htmlspecialchars($_GET['domain']) : 'www.ch1633.com';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>网站认证页脚代码 - 云天安全检测中心</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔒</text></svg>" type="image/svg+xml">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Microsoft YaHei', 'PingFang SC', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .preview-section {
            text-align: center;
            padding: 30px;
            background: #fafafa;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .badge-preview {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            padding: 10px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 18px rgba(255, 215, 0, 0.3);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .badge-preview:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .code-section {
            background: #2d2d2d;
            border-radius: 10px;
            padding: 20px;
            overflow-x: auto;
        }
        
        .code-section pre {
            color: #ccc;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 13px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-all;
        }
        
        .code-section code {
            color: #ccc;
        }
        
        .copy-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            box-shadow: 0 5px 18px rgba(102, 126, 234, 0.35);
        }
        
        .copy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.45);
        }
        
        .copy-btn.copied {
            background: linear-gradient(135deg, #27ae60 0%, #1e8449 100%);
            box-shadow: 0 5px 18px rgba(39, 174, 96, 0.35);
        }
        
        .instructions {
            background: #e8f5e9;
            border-left: 4px solid #27ae60;
            padding: 15px 20px;
            border-radius: 0 8px 8px 0;
            margin-top: 20px;
        }
        
        .instructions h4 {
            color: #155724;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .instructions ul {
            list-style: none;
            color: #2d5a27;
            font-size: 13px;
            line-height: 1.8;
        }
        
        .instructions li::before {
            content: '✓ ';
            color: #27ae60;
            font-weight: bold;
        }
        
        footer {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>网站认证页脚代码</h1>
            <p>生成认证图标代码，添加到您网站的页脚，提升网站可信度</p>
        </div>
        
        <div class="card">
            <div class="card-title">图标预览</div>
            
            <div class="preview-section">
                <a href="certificate.php?domain=ch1633.com" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); padding: 4px 10px; border-radius: 4px; text-decoration: none; box-shadow: 0 2px 6px rgba(255, 215, 0, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 3px 10px rgba(255, 215, 0, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(255, 215, 0, 0.3)';">
                    <span style="width: 22px; height: 22px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #FFA500; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">V</span>
                    <span style="text-align: left; display: inline-flex; flex-direction: column;">
                        <span style="font-size: 11px; font-weight: bold; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); display: block; line-height: 1.1;">安全认证</span>
                        <span style="font-size: 8px; color: rgba(255, 255, 255, 0.9); letter-spacing: 0.5px; display: block; line-height: 1.1;">★★★★★</span>
                    </span>
                </a>
                <p style="margin-top: 15px; color: #888; font-size: 13px;">点击图标将跳转到电子证书页面</p>
            </div>
            
            <div class="form-group">
                <label>请输入您的网站域名（用于生成跳转链接）</label>
                <input type="text" id="domain" placeholder="例如：www.ch1633.com" value="<?php echo $default_domain; ?>">
            </div>
            
            <div class="card-title">嵌入代码</div>
            
            <div class="code-section">
                <pre><code id="embedCode">&lt;a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/certificate.php?domain=<?php echo $default_domain; ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); padding: 4px 10px; border-radius: 4px; text-decoration: none; box-shadow: 0 2px 6px rgba(255, 215, 0, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 3px 10px rgba(255, 215, 0, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(255, 215, 0, 0.3)';"&gt;&lt;span style="width: 22px; height: 22px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #FFA500; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.08);"&gt;V&lt;/span&gt;&lt;span style="text-align: left; display: inline-flex; flex-direction: column;"&gt;&lt;span style="font-size: 11px; font-weight: bold; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); display: block; line-height: 1.1;"&gt;安全认证&lt;/span&gt;&lt;span style="font-size: 8px; color: rgba(255, 255, 255, 0.9); letter-spacing: 0.5px; display: block; line-height: 1.1;"&gt;★★★★★&lt;/span&gt;&lt;/span&gt;&lt;/a&gt;</code></pre>
            </div>
            
            <button class="copy-btn" id="copyBtn">复制代码</button>
            
            <div class="instructions">
                <h4>使用说明：</h4>
                <ul>
                    <li>复制上方代码</li>
                    <li>粘贴到您网站的页脚位置（footer）</li>
                    <li>将代码中的 www.ch1633.com 替换为您的实际域名</li>
                    <li>确保您的网站已经获得认证证书</li>
                </ul>
            </div>
        </div>
        
        <footer>
            <?php include_once 'config.php'; echo isset($footer_text) ? htmlspecialchars($footer_text) : '© 2026 云天安全检测中心 版权所有'; ?>
        </footer>
    </div>
    
    <script>
        const domainInput = document.getElementById('domain');
        const embedCode = document.getElementById('embedCode');
        const copyBtn = document.getElementById('copyBtn');
        
        domainInput.addEventListener('input', function() {
            const domain = this.value || 'www.ch1633.com';
            const host = window.location.host;
            embedCode.textContent = `<a href="http://${host}/certificate.php?domain=${domain}" target="_blank" style="display: inline-flex; align-items: center; gap: 4px; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); padding: 4px 10px; border-radius: 4px; text-decoration: none; box-shadow: 0 2px 6px rgba(255, 215, 0, 0.3); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 3px 10px rgba(255, 215, 0, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 6px rgba(255, 215, 0, 0.3)';"><span style="width: 22px; height: 22px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #FFA500; font-weight: bold; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">V</span><span style="text-align: left; display: inline-flex; flex-direction: column;"><span style="font-size: 11px; font-weight: bold; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); display: block; line-height: 1.1;">安全认证</span><span style="font-size: 8px; color: rgba(255, 255, 255, 0.9); letter-spacing: 0.5px; display: block; line-height: 1.1;">★★★★★</span></span></a>`;
        });
        
        copyBtn.addEventListener('click', async function() {
            try {
                await navigator.clipboard.writeText(embedCode.textContent);
                this.textContent = '已复制！';
                this.classList.add('copied');
                setTimeout(() => {
                    this.textContent = '复制代码';
                    this.classList.remove('copied');
                }, 2000);
            } catch (err) {
                console.error('复制失败:', err);
            }
        });
    </script>
</body>
</html>