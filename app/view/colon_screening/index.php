<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>肠癌早筛系统 - 医疗系统</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
            padding: 30px;
            text-align: center;
            color: #333;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.8;
        }
        
        .content {
            padding: 40px;
        }
        
        .intro {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border-left: 4px solid #007bff;
        }
        
        .intro h3 {
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .bmi-display {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
            text-align: center;
            font-weight: 600;
            color: #1976d2;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 25px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
        }
        
        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            margin: 20px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .result {
            display: none;
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }
        
        .risk-level {
            text-align: center;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            font-size: 1.2em;
            font-weight: 600;
        }
        
        .risk-low {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .risk-medium {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .risk-high {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .recommendations {
            margin-top: 20px;
        }
        
        .recommendations ul {
            list-style: none;
            padding: 0;
        }
        
        .recommendations li {
            background: white;
            padding: 12px 15px;
            margin: 8px 0;
            border-radius: 8px;
            border-left: 4px solid #007bff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .ai-analysis {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin: 20px 0;
            border: 1px solid #dee2e6;
            line-height: 1.6;
        }
        
        .nav-buttons {
            text-align: center;
            margin-top: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: transform 0.3s ease;
            margin: 0 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            color: #8b4513;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .header {
                padding: 20px;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🩺 肠癌早筛系统</h1>
            <p>智能化健康辅助应用，帮助识别肠癌早期风险</p>
        </div>
        
        <div class="content">
            <div class="intro">
                <h3>📋 早筛说明</h3>
                <p>本系统通过一系列精心设计的问题对您进行肠癌风险的初步筛查。请如实填写以下信息，我们将利用AI技术为您提供个性化的风险评估和健康建议。</p>
            </div>
            
            <form id="screeningForm">
                <div class="form-group">
                    <label for="name">👤 姓名 *</label>
                    <input type="text" id="name" name="name" required placeholder="请输入您的姓名">
                </div>
                
                <div class="form-group">
                    <label for="contact">📞 联系方式 *</label>
                    <input type="text" id="contact" name="contact" required placeholder="请输入电话或邮箱">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="age">🎂 年龄 *</label>
                        <select id="age" name="age" required>
                            <option value="">请选择年龄段</option>
                            <option value="49岁及以下">49岁及以下</option>
                            <option value="50-59岁">50-59岁</option>
                            <option value="60岁及以上">60岁及以上</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">⚧ 性别 *</label>
                        <select id="gender" name="gender" required>
                            <option value="">请选择性别</option>
                            <option value="女性">女性</option>
                            <option value="男性">男性</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="smoking">🚬 吸烟史 *</label>
                    <select id="smoking" name="smoking" required>
                        <option value="">请选择</option>
                        <option value="无">无</option>
                        <option value="有">有</option>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="height">📏 身高 (cm) *</label>
                        <input type="number" id="height" name="height" required min="100" max="250" step="0.1" placeholder="170.0">
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">⚖️ 体重 (kg) *</label>
                        <input type="number" id="weight" name="weight" required min="30" max="200" step="0.1" placeholder="60.0">
                    </div>
                </div>
                
                <div id="bmiDisplay" class="bmi-display" style="display: none;">
                    BMI: <span id="bmiValue">0</span> | 分类: <span id="bmiCategory">-</span>
                </div>
                
                <div class="form-group">
                    <label for="first_degree_rel">👨‍👩‍👧‍👦 一级亲属结直肠癌史 *</label>
                    <select id="first_degree_rel" name="first_degree_rel" required>
                        <option value="">请选择</option>
                        <option value="没有">没有</option>
                        <option value="有">有</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="colorectal_cancer">🏥 一级亲属确诊情况 *</label>
                    <select id="colorectal_cancer" name="colorectal_cancer" required>
                        <option value="">请选择</option>
                        <option value="没有">没有</option>
                        <option value="有1人在60岁以下确诊">有1人在60岁以下确诊</option>
                        <option value="有1人60岁及以上确诊">有1人60岁及以上确诊</option>
                        <option value="有2人或以上在60岁以下确诊">有2人或以上在60岁以下确诊</option>
                        <option value="有2人或以上在60岁及以上确诊">有2人或以上在60岁及以上确诊</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="genetic_disease">🧬 肠道遗传性疾病 *</label>
                    <select id="genetic_disease" name="genetic_disease" required>
                        <option value="">请选择</option>
                        <option value="否">否</option>
                        <option value="是">是</option>
                    </select>
                </div>
                
                <button type="submit" class="submit-btn" id="submitBtn">
                    🔍 开始风险评估
                </button>
            </form>
            
            <div id="loading" class="loading">
                <h3>🤖 AI正在分析您的风险...</h3>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <p id="loadingText">正在处理您的信息...</p>
            </div>
            
            <div id="result" class="result">
                <h3>📊 评估结果</h3>
                
                <div id="riskLevel" class="risk-level">
                    风险等级: <span id="riskLevelText">-</span>
                </div>
                
                <div class="ai-analysis">
                    <h4>🤖 AI智能分析</h4>
                    <div id="aiAnalysis">分析中...</div>
                </div>
                
                <div class="recommendations">
                    <h4>💡 健康建议</h4>
                    <ul id="recommendationsList"></ul>
                </div>
            </div>
            
            <div class="nav-buttons">
                <a href="/prescription/" class="btn btn-secondary">📋 处方管理</a>
                <a href="/reminder/" class="btn btn-secondary">🔔 提醒中心</a>
                <a href="/" class="btn btn-primary">🏠 返回首页</a>
            </div>
        </div>
    </div>

    <script>
        // BMI计算
        function calculateBMI() {
            const height = parseFloat(document.getElementById('height').value);
            const weight = parseFloat(document.getElementById('weight').value);
            
            if (height && weight) {
                const bmi = weight / ((height / 100) ** 2);
                const category = bmi < 23 ? '小于23' : '大于等于23';
                
                document.getElementById('bmiValue').textContent = bmi.toFixed(2);
                document.getElementById('bmiCategory').textContent = category;
                document.getElementById('bmiDisplay').style.display = 'block';
            } else {
                document.getElementById('bmiDisplay').style.display = 'none';
            }
        }
        
        // 监听身高体重变化
        document.getElementById('height').addEventListener('input', calculateBMI);
        document.getElementById('weight').addEventListener('input', calculateBMI);
        
        // 表单提交
        document.getElementById('screeningForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // 显示加载状态
            document.getElementById('screeningForm').style.display = 'none';
            document.getElementById('loading').style.display = 'block';
            document.getElementById('result').style.display = 'none';
            
            // 模拟进度条
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                document.getElementById('progressFill').style.width = progress + '%';
                
                if (progress < 30) {
                    document.getElementById('loadingText').textContent = '正在分析基础信息...';
                } else if (progress < 60) {
                    document.getElementById('loadingText').textContent = '正在计算风险评分...';
                } else if (progress < 90) {
                    document.getElementById('loadingText').textContent = 'AI正在生成个性化建议...';
                }
            }, 200);
            
            try {
                const response = await fetch('/colon_screening/submit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(data)
                });
                
                const result = await response.json();
                
                clearInterval(progressInterval);
                document.getElementById('progressFill').style.width = '100%';
                document.getElementById('loadingText').textContent = '分析完成！';
                
                setTimeout(() => {
                    document.getElementById('loading').style.display = 'none';
                    
                    if (result.code === 1) {
                        showResult(result.data);
                    } else {
                        alert('评估失败: ' + result.message);
                        document.getElementById('screeningForm').style.display = 'block';
                    }
                }, 1000);
                
            } catch (error) {
                clearInterval(progressInterval);
                console.error('提交失败:', error);
                alert('网络错误，请稍后重试');
                document.getElementById('loading').style.display = 'none';
                document.getElementById('screeningForm').style.display = 'block';
            }
        });
        
        // 显示结果
        function showResult(data) {
            // 设置风险等级
            const riskLevelEl = document.getElementById('riskLevel');
            const riskLevelText = document.getElementById('riskLevelText');
            
            riskLevelText.textContent = data.risk_level + ' (评分: ' + data.risk_score + ')';
            
            // 设置风险等级样式
            riskLevelEl.className = 'risk-level';
            if (data.risk_level === '低风险') {
                riskLevelEl.classList.add('risk-low');
            } else if (data.risk_level === '中等风险') {
                riskLevelEl.classList.add('risk-medium');
            } else {
                riskLevelEl.classList.add('risk-high');
            }
            
            // 显示AI分析
            document.getElementById('aiAnalysis').innerHTML = data.ai_analysis.replace(/\n/g, '<br>');
            
            // 显示建议
            const recommendationsList = document.getElementById('recommendationsList');
            recommendationsList.innerHTML = '';
            data.recommendations.forEach(rec => {
                const li = document.createElement('li');
                li.textContent = rec;
                recommendationsList.appendChild(li);
            });
            
            // 显示结果区域
            document.getElementById('result').style.display = 'block';
        }
    </script>
</body>
</html>