<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>处方上传 - 医疗系统</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 16px;
            opacity: 0.9;
        }
        
        .form-container {
            padding: 40px;
        }
        
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .upload-area:hover {
            border-color: #4facfe;
            background-color: #f8f9ff;
        }
        
        .upload-area.dragover {
            border-color: #4facfe;
            background-color: #f0f8ff;
        }
        
        .upload-icon {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .upload-text {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .upload-hint {
            font-size: 14px;
            color: #999;
        }
        
        .file-input {
            display: none;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #4facfe;
        }
        
        .btn {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .preview-area {
            margin-top: 20px;
            display: none;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .file-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .file-info p {
            margin: 5px 0;
            color: #666;
        }
        
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #f0f0f0;
            border-radius: 3px;
            margin: 20px 0;
            overflow: hidden;
            display: none;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4facfe, #00f2fe);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .result-area {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            display: none;
        }
        
        .result-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        
        .result-item {
            margin-bottom: 10px;
            padding: 10px;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #4facfe;
        }
        
        .result-label {
            font-weight: bold;
            color: #555;
        }
        
        .result-value {
            color: #333;
            margin-top: 5px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4facfe;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>处方上传</h1>
            <p>上传您的处方图片，系统将自动识别复诊时间和用药频次</p>
        </div>
        
        <div class="form-container">
            <form id="prescriptionForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">用户ID</label>
                    <input type="number" name="user_id" class="form-input" value="1" required>
                </div>
                
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">📷</div>
                    <div class="upload-text">点击或拖拽上传处方图片</div>
                    <div class="upload-hint">支持 JPG、PNG、GIF 格式，文件大小不超过 5MB</div>
                    <input type="file" name="prescription_image" class="file-input" id="fileInput" accept="image/*" required>
                </div>
                
                <div class="preview-area" id="previewArea">
                    <img id="previewImage" class="preview-image" alt="预览图片">
                    <div class="file-info" id="fileInfo"></div>
                </div>
                
                <div class="progress-bar" id="progressBar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">
                    <span id="btnText">上传并识别</span>
                </button>
            </form>
            
            <div class="result-area" id="resultArea">
                <div class="result-title">识别结果</div>
                <div id="resultContent"></div>
            </div>
        </div>
    </div>

    <script>
        // DOM 元素
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const previewArea = document.getElementById('previewArea');
        const previewImage = document.getElementById('previewImage');
        const fileInfo = document.getElementById('fileInfo');
        const form = document.getElementById('prescriptionForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');
        const resultArea = document.getElementById('resultArea');
        const resultContent = document.getElementById('resultContent');
        
        // 点击上传区域
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        // 文件选择
        fileInput.addEventListener('change', handleFileSelect);
        
        // 拖拽上传
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleFileSelect();
            }
        });
        
        // 处理文件选择
        function handleFileSelect() {
            const file = fileInput.files[0];
            if (!file) return;
            
            // 验证文件类型
            if (!file.type.startsWith('image/')) {
                showAlert('请选择图片文件', 'error');
                return;
            }
            
            // 验证文件大小
            if (file.size > 5 * 1024 * 1024) {
                showAlert('文件大小不能超过 5MB', 'error');
                return;
            }
            
            // 显示预览
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                previewArea.style.display = 'block';
                
                // 显示文件信息
                fileInfo.innerHTML = `
                    <p><strong>文件名:</strong> ${file.name}</p>
                    <p><strong>文件大小:</strong> ${formatFileSize(file.size)}</p>
                    <p><strong>文件类型:</strong> ${file.type}</p>
                `;
            };
            reader.readAsDataURL(file);
        }
        
        // 表单提交
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!fileInput.files[0]) {
                showAlert('请选择要上传的图片', 'error');
                return;
            }
            
            // 显示加载状态
            setLoading(true);
            showProgress(0);
            
            try {
                const formData = new FormData(form);
                
                const response = await fetch('/prescription/upload', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.code === 200) {
                    showAlert('处方上传成功！', 'success');
                    showProgress(100);
                    
                    // 获取详细信息
                    setTimeout(() => {
                        getPrescriptionDetail(result.data.prescription_id);
                    }, 1000);
                } else {
                    showAlert(result.message || '上传失败', 'error');
                }
            } catch (error) {
                console.error('上传错误:', error);
                showAlert('网络错误，请稍后重试', 'error');
            } finally {
                setLoading(false);
            }
        });
        
        // 获取处方详情
        async function getPrescriptionDetail(prescriptionId) {
            try {
                const response = await fetch(`/prescription/detail?id=${prescriptionId}`);
                const result = await response.json();
                
                if (result.code === 200) {
                    displayResult(result.data);
                }
            } catch (error) {
                console.error('获取详情错误:', error);
            }
        }
        
        // 显示结果
        function displayResult(data) {
            let html = '';
            
            if (data.follow_up_date) {
                html += `
                    <div class="result-item">
                        <div class="result-label">复诊时间</div>
                        <div class="result-value">${formatDate(data.follow_up_date)}</div>
                    </div>
                `;
            }
            
            if (data.medication_frequency) {
                html += `
                    <div class="result-item">
                        <div class="result-label">用药频次</div>
                        <div class="result-value">${data.medication_frequency}</div>
                    </div>
                `;
            }
            
            if (data.medication_details) {
                try {
                    const details = JSON.parse(data.medication_details);
                    if (details.length > 0) {
                        html += `
                            <div class="result-item">
                                <div class="result-label">药物详情</div>
                                <div class="result-value">
                        `;
                        details.forEach(med => {
                            html += `
                                <p><strong>${med.name}</strong> - ${med.dosage} - ${med.frequency}</p>
                            `;
                        });
                        html += `</div></div>`;
                    }
                } catch (e) {
                    // 忽略JSON解析错误
                }
            }
            
            html += `
                <div class="result-item">
                    <div class="result-label">处理状态</div>
                    <div class="result-value">${data.status_text}</div>
                </div>
            `;
            
            resultContent.innerHTML = html;
            resultArea.style.display = 'block';
        }
        
        // 设置加载状态
        function setLoading(loading) {
            submitBtn.disabled = loading;
            if (loading) {
                btnText.innerHTML = '<span class="loading"></span>处理中...';
            } else {
                btnText.textContent = '上传并识别';
            }
        }
        
        // 显示进度
        function showProgress(percent) {
            progressBar.style.display = 'block';
            progressFill.style.width = percent + '%';
            
            if (percent >= 100) {
                setTimeout(() => {
                    progressBar.style.display = 'none';
                }, 1000);
            }
        }
        
        // 显示提示
        function showAlert(message, type) {
            // 移除现有提示
            const existingAlert = document.querySelector('.alert');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            
            form.insertBefore(alert, form.firstChild);
            
            // 3秒后自动移除
            setTimeout(() => {
                alert.remove();
            }, 3000);
        }
        
        // 格式化文件大小
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // 格式化日期
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleString('zh-CN');
        }
    </script>
</body>
</html>