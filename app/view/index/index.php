<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>医疗系统 - 首页</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Microsoft YaHei', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 36px;
            margin-bottom: 15px;
        }
        
        .header p {
            font-size: 18px;
            opacity: 0.9;
        }
        
        .content {
            padding: 50px;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .feature-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 2px solid transparent;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: #4facfe;
        }
        
        .feature-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #4facfe;
        }
        
        .feature-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-desc {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .feature-btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .feature-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }
        
        .system-info {
            background: #e3f2fd;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
        }
        
        .system-info h3 {
            color: #1976d2;
            margin-bottom: 20px;
            font-size: 24px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #4facfe;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
            margin-bottom: 8px;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #666;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 智能医疗系统</h1>
            <p>集成早期筛查、处方管理、用药提醒、饮食分析的综合医疗平台</p>
        </div>
        
        <div class="content">
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">🩺</div>
                    <div class="feature-title">肠癌早筛</div>
                    <div class="feature-desc">
                        基于问卷的肠癌早期筛查系统，通过AI智能分析为用户提供健康风险评估
                    </div>
                    <a href="/colon_screening/" class="feature-btn">开始筛查</a>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">💊</div>
                    <div class="feature-title">处方管理</div>
                    <div class="feature-desc">
                        上传处方图片，AI自动识别复诊时间和用药信息，智能提醒不错过
                    </div>
                    <a href="/prescription/upload" class="feature-btn">上传处方</a>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <div class="feature-title">智能提醒</div>
                    <div class="feature-desc">
                        微信小程序推送、短信提醒，确保按时复诊和用药，守护您的健康
                    </div>
                    <a href="/reminder/list" class="feature-btn">查看提醒</a>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">🍎</div>
                    <div class="feature-title">饮食分析</div>
                    <div class="feature-desc">
                        拍照识别食物和卡路里，提供个性化饮食建议和营养分析
                    </div>
                    <a href="/meal/upload" class="feature-btn">记录饮食</a>
                </div>
            </div>
            
            <div class="system-info">
                <h3>🔧 系统信息</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">框架版本</div>
                        <div class="info-value">ThinkPHP 6.1</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">PHP版本</div>
                        <div class="info-value"><?php echo PHP_VERSION; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">数据库</div>
                        <div class="info-value">SQLite</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">AI服务</div>
                        <div class="info-value">ThinkAI OCR</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; 2024 智能医疗系统. 专注于为用户提供便捷的医疗健康服务.</p>
        </div>
    </div>
</body>
</html>