<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>处方列表 - 医疗系统</title>
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
        
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: transform 0.3s ease;
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
        
        .prescription-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .prescription-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }
        
        .prescription-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .prescription-id {
            font-size: 1.2em;
            font-weight: bold;
            color: #495057;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8em;
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
        
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .card-info {
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9em;
        }
        
        .info-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .info-value {
            color: #212529;
            font-weight: 600;
        }
        
        .medication-preview {
            background: #fff3cd;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            font-size: 0.85em;
        }
        
        .medication-item {
            color: #856404;
            margin-bottom: 5px;
        }
        
        .medication-item:last-child {
            margin-bottom: 0;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-small {
            padding: 8px 15px;
            font-size: 0.85em;
            border-radius: 15px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-view {
            background: #007bff;
            color: white;
        }
        
        .btn-view:hover {
            background: #0056b3;
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
        
        .empty {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .page-btn {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            background: white;
            color: #495057;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .page-btn:hover {
            background: #e9ecef;
        }
        
        .page-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .page-info {
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 处方列表</h1>
            <p>管理您的处方信息</p>
        </div>
        
        <div class="content">
            <div class="actions">
                <a href="/prescription/upload" class="btn btn-primary">📤 上传新处方</a>
                <a href="/" class="btn btn-secondary">🏠 返回首页</a>
            </div>
            
            <div id="loading" class="loading">
                <p>正在加载处方列表...</p>
            </div>
            
            <div id="error" class="error" style="display: none;">
                <p>加载失败，请稍后重试</p>
            </div>
            
            <div id="empty" class="empty" style="display: none;">
                <p>暂无处方记录</p>
                <p><a href="/prescription/upload" class="btn btn-primary" style="margin-top: 20px;">上传第一个处方</a></p>
            </div>
            
            <div id="prescription-list" style="display: none;">
                <div class="prescription-grid" id="prescription-grid">
                    <!-- 处方卡片将通过JavaScript动态加载 -->
                </div>
                
                <div class="pagination" id="pagination">
                    <!-- 分页将通过JavaScript动态加载 -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        const pageSize = 6;
        
        // 加载处方列表
        async function loadPrescriptionList(page = 1) {
            try {
                showLoading();
                
                const response = await fetch(`/prescription/list?page=${page}&limit=${pageSize}&user_id=1`);
                const result = await response.json();
                
                if (result.code === 200) {
                    displayPrescriptionList(result.data);
                } else {
                    showError(result.message || '加载失败');
                }
            } catch (error) {
                console.error('加载处方列表失败:', error);
                showError('网络错误，请稍后重试');
            }
        }
        
        // 显示处方列表
        function displayPrescriptionList(data) {
            const { list, total, page, limit, pages } = data;
            
            if (list.length === 0) {
                showEmpty();
                return;
            }
            
            const grid = document.getElementById('prescription-grid');
            grid.innerHTML = list.map(prescription => createPrescriptionCard(prescription)).join('');
            
            // 显示分页
            displayPagination(page, pages, total);
            
            // 隐藏加载状态，显示列表
            hideLoading();
            document.getElementById('prescription-list').style.display = 'block';
        }
        
        // 创建处方卡片
        function createPrescriptionCard(prescription) {
            // 解析用药详情
            let medicationDetails = [];
            try {
                medicationDetails = JSON.parse(prescription.medication_details || '[]');
            } catch (e) {
                console.warn('解析用药详情失败:', e);
            }
            
            // 状态样式
            const statusClass = prescription.status == 1 ? 'status-processed' : 
                               prescription.status == 2 ? 'status-failed' : 'status-pending';
            const statusText = prescription.status == 1 ? '已处理' : 
                              prescription.status == 2 ? '处理失败' : '待处理';
            
            return `
                <div class="prescription-card" onclick="viewPrescription(${prescription.id})">
                    <div class="card-header">
                        <div class="prescription-id">处方 #${prescription.id}</div>
                        <div class="status-badge ${statusClass}">${statusText}</div>
                    </div>
                    
                    <div class="card-info">
                        <div class="info-item">
                            <span class="info-label">文件名:</span>
                            <span class="info-value">${prescription.original_filename}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">复诊时间:</span>
                            <span class="info-value">${prescription.follow_up_date || '未设置'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">用药频次:</span>
                            <span class="info-value">${prescription.medication_frequency || '未设置'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">上传时间:</span>
                            <span class="info-value">${formatDateTime(prescription.create_time)}</span>
                        </div>
                    </div>
                    
                    ${medicationDetails.length > 0 ? `
                    <div class="medication-preview">
                        <strong>💊 用药信息:</strong>
                        ${medicationDetails.slice(0, 2).map(med => `
                            <div class="medication-item">${med.name} - ${med.dosage} - ${med.frequency}</div>
                        `).join('')}
                        ${medicationDetails.length > 2 ? `<div class="medication-item">...还有${medicationDetails.length - 2}种药物</div>` : ''}
                    </div>
                    ` : ''}
                    
                    <div class="card-actions">
                        <a href="/prescription/show/${prescription.id}" class="btn-small btn-view" onclick="event.stopPropagation()">查看详情</a>
                    </div>
                </div>
            `;
        }
        
        // 显示分页
        function displayPagination(currentPage, totalPages, totalCount) {
            const pagination = document.getElementById('pagination');
            
            if (totalPages <= 1) {
                pagination.innerHTML = `<div class="page-info">共 ${totalCount} 条记录</div>`;
                return;
            }
            
            let paginationHTML = '';
            
            // 上一页
            if (currentPage > 1) {
                paginationHTML += `<a href="#" class="page-btn" onclick="loadPrescriptionList(${currentPage - 1})">上一页</a>`;
            }
            
            // 页码
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    paginationHTML += `<span class="page-btn active">${i}</span>`;
                } else {
                    paginationHTML += `<a href="#" class="page-btn" onclick="loadPrescriptionList(${i})">${i}</a>`;
                }
            }
            
            // 下一页
            if (currentPage < totalPages) {
                paginationHTML += `<a href="#" class="page-btn" onclick="loadPrescriptionList(${currentPage + 1})">下一页</a>`;
            }
            
            paginationHTML += `<div class="page-info">共 ${totalCount} 条记录，第 ${currentPage}/${totalPages} 页</div>`;
            
            pagination.innerHTML = paginationHTML;
        }
        
        // 查看处方详情
        function viewPrescription(id) {
            window.location.href = `/prescription/show/${id}`;
        }
        
        // 格式化日期时间
        function formatDateTime(dateTimeStr) {
            try {
                const date = new Date(dateTimeStr);
                return date.toLocaleString('zh-CN');
            } catch (e) {
                return dateTimeStr;
            }
        }
        
        // 显示加载状态
        function showLoading() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('error').style.display = 'none';
            document.getElementById('empty').style.display = 'none';
            document.getElementById('prescription-list').style.display = 'none';
        }
        
        // 隐藏加载状态
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }
        
        // 显示错误信息
        function showError(message) {
            hideLoading();
            const errorDiv = document.getElementById('error');
            errorDiv.innerHTML = `<p>${message}</p>`;
            errorDiv.style.display = 'block';
        }
        
        // 显示空状态
        function showEmpty() {
            hideLoading();
            document.getElementById('empty').style.display = 'block';
        }
        
        // 页面加载完成后执行
        document.addEventListener('DOMContentLoaded', () => {
            loadPrescriptionList(1);
        });
    </script>
</body>
</html>