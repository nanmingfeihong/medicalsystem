<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>饮食分析 - 医疗系统</title>
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
            background: linear-gradient(135deg, #ff6b6b, #ffa500);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 40px;
        }
        
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 15px;
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: #ff6b6b;
            background-color: #fff5f5;
        }
        
        .upload-area.dragover {
            border-color: #ff6b6b;
            background-color: #fff5f5;
            transform: scale(1.02);
        }
        
        .upload-icon {
            font-size: 4em;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .upload-text {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 15px;
        }
        
        .upload-hint {
            color: #999;
            font-size: 0.9em;
        }
        
        #fileInput {
            display: none;
        }
        
        .btn {
            background: linear-gradient(135deg, #ff6b6b, #ffa500);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 10px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255,107,107,0.3);
        }
        
        .preview-area {
            display: none;
            margin-top: 30px;
            text-align: center;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 400px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 20px;
        }
        
        .analysis-result {
            display: none;
            margin-top: 30px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 15px;
        }
        
        .result-section {
            margin-bottom: 25px;
        }
        
        .result-title {
            font-size: 1.3em;
            color: #333;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .result-title::before {
            content: "🍎";
            margin-right: 10px;
            font-size: 1.2em;
        }
        
        .food-list {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        
        .calories-info {
            background: linear-gradient(135deg, #ff6b6b, #ffa500);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.2em;
            margin-bottom: 15px;
        }
        
        .suggestions {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #28a745;
        }
        
        .suggestions ul {
            list-style: none;
            padding: 0;
        }
        
        .suggestions li {
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .suggestions li:last-child {
            border-bottom: none;
        }
        
        .suggestions li::before {
            content: "💡";
            margin-right: 10px;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 30px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #ff6b6b;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .nav-buttons {
            text-align: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }
        
        .nav-buttons a {
            text-decoration: none;
            color: white;
            background: linear-gradient(135deg, #667eea, #764ba2);
            padding: 12px 25px;
            border-radius: 20px;
            margin: 0 10px;
            transition: all 0.3s ease;
        }
        
        .nav-buttons a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(102,126,234,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍎 智能饮食分析</h1>
            <p>拍照识别食物，获取营养分析和健康建议</p>
        </div>
        
        <div class="content">
            <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                <div class="upload-icon">📷</div>
                <div class="upload-text">点击或拖拽上传食物图片</div>
                <div class="upload-hint">支持 JPG、PNG 格式，文件大小不超过 10MB</div>
                <input type="file" id="fileInput" accept="image/*" />
            </div>
            
            <div class="preview-area" id="previewArea">
                <img id="previewImage" class="preview-image" />
                <div>
                    <button class="btn" onclick="analyzeFood()">🔍 开始分析</button>
                    <button class="btn" onclick="resetUpload()">🔄 重新上传</button>
                </div>
            </div>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>AI正在分析您的食物，请稍候...</p>
            </div>
            
            <div class="analysis-result" id="analysisResult">
                <div class="result-section">
                    <div class="result-title">识别的食物</div>
                    <div class="food-list" id="foodList"></div>
                </div>
                
                <div class="result-section">
                    <div class="result-title">卡路里信息</div>
                    <div class="calories-info" id="caloriesInfo"></div>
                </div>
                
                <div class="result-section">
                    <div class="result-title">营养建议</div>
                    <div class="suggestions" id="suggestions"></div>
                </div>
            </div>
            
            <div class="nav-buttons">
                <a href="/prescription/">📋 处方管理</a>
                <a href="/reminder/">🔔 提醒中心</a>
                <a href="/">🏠 返回首页</a>
            </div>
        </div>
    </div>

    <script>
        let selectedFile = null;
        
        // 文件选择处理
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                handleFileSelect(file);
            }
        });
        
        // 拖拽上传
        const uploadArea = document.querySelector('.upload-area');
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });
        
        function handleFileSelect(file) {
            if (!file.type.startsWith('image/')) {
                alert('请选择图片文件！');
                return;
            }
            
            if (file.size > 10 * 1024 * 1024) {
                alert('文件大小不能超过10MB！');
                return;
            }
            
            selectedFile = file;
            
            // 显示预览
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('previewArea').style.display = 'block';
                document.querySelector('.upload-area').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
        
        function analyzeFood() {
            if (!selectedFile) {
                alert('请先选择图片！');
                return;
            }
            
            // 显示加载状态
            document.getElementById('loading').style.display = 'block';
            document.getElementById('previewArea').style.display = 'none';
            document.getElementById('analysisResult').style.display = 'none';
            
            // 创建FormData
            const formData = new FormData();
            formData.append('meal_image', selectedFile);
            
            // 发送请求
            fetch('/meal/analyze', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading').style.display = 'none';
                
                if (data.code === 1) {
                    displayAnalysisResult(data.data.analysis);
                } else {
                    alert('分析失败: ' + data.message);
                    document.getElementById('previewArea').style.display = 'block';
                }
            })
            .catch(error => {
                document.getElementById('loading').style.display = 'none';
                document.getElementById('previewArea').style.display = 'block';
                alert('网络错误，请重试！');
                console.error('Error:', error);
            });
        }
        
        function displayAnalysisResult(analysis) {
            // 显示食物列表
            document.getElementById('foodList').innerHTML = analysis.food_items || '未识别到具体食物';
            
            // 显示卡路里信息
            document.getElementById('caloriesInfo').innerHTML = 
                `总热量: ${analysis.total_calories || '未知'} 千卡`;
            
            // 显示建议
            const suggestions = analysis.suggestions || ['暂无建议'];
            const suggestionsList = suggestions.map(item => `<li>${item}</li>`).join('');
            document.getElementById('suggestions').innerHTML = `<ul>${suggestionsList}</ul>`;
            
            // 显示结果区域
            document.getElementById('analysisResult').style.display = 'block';
        }
        
        function resetUpload() {
            selectedFile = null;
            document.getElementById('fileInput').value = '';
            document.getElementById('previewArea').style.display = 'none';
            document.getElementById('analysisResult').style.display = 'none';
            document.querySelector('.upload-area').style.display = 'block';
        }
    </script>
</body>
</html>