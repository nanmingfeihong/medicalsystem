<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>处方详情 - 医疗系统</title>
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
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .content {
            padding: 30px;
        }
        
        .prescription-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
            min-width: 120px;
        }
        
        .info-value {
            color: #212529;
            flex: 1;
            text-align: right;
        }
        
        .prescription-image {
            text-align: center;
            margin: 20px 0;
        }
        
        .prescription-image img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .medication-list {
            background: #fff3cd;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .medication-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #ffc107;
        }
        
        .medication-item:last-child {
            margin-bottom: 0;
        }
        
        .medication-name {
            font-weight: bold;
            color: #856404;
            font-size: 1.1em;
            margin-bottom: 5px;
        }
        
        .medication-details {
            color: #6c757d;
            font-size: 0.9em;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        
        .status-processed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .back-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            margin-top: 20px;
            transition: transform 0.3s ease;
        }
        
        .back-button:hover {
            transform: translateY(-2px);
        }
        
        .loading {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        .error {
            text-align: center;
            padding: 50px;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 处方详情</h1>
            <p>查看处方信息和用药指导</p>
        </div>
        
        <div class="content">
            <div id="loading" class="loading">
                <p>正在加载处方信息...</p>
            </div>
            
            <div id="error" class="error" style="display: none;">
                <p>加载失败，请稍后重试</p>
            </div>
            
            <div id="prescription-detail" style="display: none;">
                <!-- 处方信息将通过JavaScript动态加载 -->
            </div>
            
            <a href="/prescription/" class="back-button">← 返回处方列表</a>
        </div>
    </div>

    <script>
        // 获取URL中的处方ID
        function getPrescriptionId() {
            const path = window.location.pathname;
            const matches = path.match(/\/prescription\/show\/(\d+)/);
            return matches ? matches[1] : null;
        }
        
        // 加载处方详情
        async function loadPrescriptionDetail() {
            const prescriptionId = getPrescriptionId();
            
            if (!prescriptionId) {
                showError('无效的处方ID');
                return;
            }
            
            try {
                const response = await fetch(`/prescription/detail/${prescriptionId}`);
                const result = await response.json();
                
                if (result.code === 200) {
                    displayPrescriptionDetail(result.data);
                } else {
                    showError(result.message || '加载失败');
                }
            } catch (error) {
                console.error('加载处方详情失败:', error);
                showError('网络错误，请稍后重试');
            }
        }
        
        // 显示处方详情
        function displayPrescriptionDetail(prescription) {
            const container = document.getElementById('prescription-detail');
            
            // 解析提取的内容
            let extractedData = {};
            try {
                extractedData = JSON.parse(prescription.extracted_content || '{}');
            } catch (e) {
                console.warn('解析提取内容失败:', e);
            }
            
            // 解析用药详情
            let medicationDetails = [];
            try {
                medicationDetails = JSON.parse(prescription.medication_details || '[]');
            } catch (e) {
                console.warn('解析用药详情失败:', e);
            }
            
            container.innerHTML = `
                <div class="prescription-info">
                    <div class="info-row">
                        <span class="info-label">处方ID:</span>
                        <span class="info-value">${prescription.id}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">原始文件名:</span>
                        <span class="info-value">${prescription.original_filename}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">复诊时间:</span>
                        <span class="info-value">${prescription.follow_up_date || '未设置'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">用药频次:</span>
                        <span class="info-value">${prescription.medication_frequency || '未设置'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">处理状态:</span>
                        <span class="info-value">
                            <span class="status-badge ${prescription.status == 1 ? 'status-processed' : 'status-pending'}">
                                ${prescription.status_text}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">上传时间:</span>
                        <span class="info-value">${prescription.create_time}</span>
                    </div>
                </div>
                
                ${prescription.prescription_image ? `
                <div class="prescription-image">
                    <h3>处方图片</h3>
                    <img src="/uploads/${prescription.prescription_image}" alt="处方图片" />
                </div>
                ` : ''}
                
                ${medicationDetails.length > 0 ? `
                <div class="medication-list">
                    <h3>💊 用药详情</h3>
                    ${medicationDetails.map(med => `
                        <div class="medication-item">
                            <div class="medication-name">${med.name}</div>
                            <div class="medication-details">
                                剂量: ${med.dosage} | 
                                频次: ${med.frequency} | 
                                疗程: ${med.duration}
                                ${med.notes ? ` | 注意: ${med.notes}` : ''}
                            </div>
                        </div>
                    `).join('')}
                </div>
                ` : ''}
                
                ${extractedData.doctor_name || extractedData.hospital_name ? `
                <div class="prescription-info">
                    <h3>🏥 医疗信息</h3>
                    ${extractedData.doctor_name ? `
                    <div class="info-row">
                        <span class="info-label">医生:</span>
                        <span class="info-value">${extractedData.doctor_name}</span>
                    </div>
                    ` : ''}
                    ${extractedData.hospital_name ? `
                    <div class="info-row">
                        <span class="info-label">医院:</span>
                        <span class="info-value">${extractedData.hospital_name}</span>
                    </div>
                    ` : ''}
                    ${extractedData.diagnosis ? `
                    <div class="info-row">
                        <span class="info-label">诊断:</span>
                        <span class="info-value">${extractedData.diagnosis}</span>
                    </div>
                    ` : ''}
                </div>
                ` : ''}
            `;
            
            // 隐藏加载状态，显示内容
            document.getElementById('loading').style.display = 'none';
            container.style.display = 'block';
        }
        
        // 显示错误信息
        function showError(message) {
            document.getElementById('loading').style.display = 'none';
            const errorDiv = document.getElementById('error');
            errorDiv.innerHTML = `<p>${message}</p>`;
            errorDiv.style.display = 'block';
        }
        
        // 页面加载完成后执行
        document.addEventListener('DOMContentLoaded', loadPrescriptionDetail);
    </script>
</body>
</html>