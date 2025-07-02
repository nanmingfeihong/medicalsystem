<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>提醒列表 - 医疗系统</title>
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
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
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
        
        .filter-tabs {
            display: flex;
            gap: 10px;
        }
        
        .tab-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            background: #f8f9fa;
            color: #495057;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .tab-btn:hover {
            transform: translateY(-2px);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 提醒列表</h1>
            <p>管理您的复诊和用药提醒</p>
        </div>
        
        <div class="content">
            <div class="actions">
                <div class="filter-tabs">
                    <button class="filter-btn active" data-filter="all">全部</button>
                    <button class="filter-btn" data-filter="follow_up">复诊提醒</button>
                    <button class="filter-btn" data-filter="medication">用药提醒</button>
                </div>
                <div>
                    <a href="/prescription/" class="btn btn-secondary">📋 处方列表</a>
                    <a href="/" class="btn btn-primary">🏠 返回首页</a>
                </div>
            </div>
            
            <div id="loading" class="loading">
                <p>正在加载提醒列表...</p>
            </div>
            
            <div id="reminder-list" style="display: none;"></div>
            
            <div id="error" class="error" style="display: none;">
                <p>加载提醒列表失败，请稍后重试</p>
            </div>
            
            <div id="empty" class="empty" style="display: none;">
                <p>暂无提醒记录</p>
                <p>上传处方后系统会自动创建提醒</p>
            </div>
        </div>
    </div>

    <script>
        let currentFilter = 'all';
        let reminders = [];

        // 页面加载完成后获取提醒列表
        document.addEventListener('DOMContentLoaded', function() {
            loadReminders();
            
            // 绑定筛选按钮事件
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // 更新按钮状态
                    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    // 更新筛选条件
                    currentFilter = this.dataset.filter;
                    renderReminders();
                });
            });
        });

        // 加载提醒列表
        async function loadReminders() {
            try {
                const response = await fetch('/reminder/list?user_id=1');
                const data = await response.json();
                
                if (data.code === 1) {
                    reminders = data.data || [];
                    renderReminders();
                } else {
                    showError();
                }
            } catch (error) {
                console.error('加载提醒失败:', error);
                showError();
            }
        }

        // 渲染提醒列表
        function renderReminders() {
            const loadingEl = document.getElementById('loading');
            const listEl = document.getElementById('reminder-list');
            const errorEl = document.getElementById('error');
            const emptyEl = document.getElementById('empty');

            // 隐藏所有状态
            loadingEl.style.display = 'none';
            listEl.style.display = 'none';
            errorEl.style.display = 'none';
            emptyEl.style.display = 'none';

            // 筛选提醒
            let filteredReminders = reminders;
            if (currentFilter !== 'all') {
                filteredReminders = reminders.filter(reminder => reminder.reminder_type === currentFilter);
            }

            if (filteredReminders.length === 0) {
                emptyEl.style.display = 'block';
                return;
            }

            // 按时间排序
            filteredReminders.sort((a, b) => new Date(a.reminder_time) - new Date(b.reminder_time));

            // 生成HTML
            const html = filteredReminders.map(reminder => {
                const date = new Date(reminder.reminder_time);
                const now = new Date();
                const isOverdue = date < now;
                const isToday = date.toDateString() === now.toDateString();
                
                const typeText = reminder.reminder_type === 'follow_up' ? '复诊提醒' : '用药提醒';
                const typeIcon = reminder.reminder_type === 'follow_up' ? '🏥' : '💊';
                const statusText = reminder.status === 0 ? '待发送' : reminder.status === 1 ? '已发送' : '发送失败';
                const statusClass = reminder.status === 0 ? 'pending' : reminder.status === 1 ? 'sent' : 'failed';
                
                return `
                    <div class="reminder-card ${isOverdue ? 'overdue' : ''} ${isToday ? 'today' : ''}">
                        <div class="reminder-header">
                            <span class="reminder-type">${typeIcon} ${typeText}</span>
                            <span class="reminder-status ${statusClass}">${statusText}</span>
                        </div>
                        <div class="reminder-content">
                            <p class="reminder-message">${reminder.message || '暂无消息内容'}</p>
                            <div class="reminder-meta">
                                <span class="reminder-time">⏰ ${formatDateTime(reminder.reminder_time)}</span>
                                ${reminder.prescription ? `<span class="prescription-info">📋 处方 #${reminder.prescription.id}</span>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            listEl.innerHTML = html;
            listEl.style.display = 'block';
        }

        // 显示错误
        function showError() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('error').style.display = 'block';
        }

        // 格式化日期时间
        function formatDateTime(dateTimeStr) {
            const date = new Date(dateTimeStr);
            const now = new Date();
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
            const tomorrow = new Date(today.getTime() + 24 * 60 * 60 * 1000);
            const yesterday = new Date(today.getTime() - 24 * 60 * 60 * 1000);
            
            const dateOnly = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const timeStr = date.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' });
            
            if (dateOnly.getTime() === today.getTime()) {
                return `今天 ${timeStr}`;
            } else if (dateOnly.getTime() === tomorrow.getTime()) {
                return `明天 ${timeStr}`;
            } else if (dateOnly.getTime() === yesterday.getTime()) {
                return `昨天 ${timeStr}`;
            } else {
                return date.toLocaleString('zh-CN', { 
                    month: '2-digit', 
                    day: '2-digit', 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
            }
        }
    </script>

    <style>
        .filter-btn {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            color: #6c757d;
            padding: 8px 16px;
            margin: 0 5px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
        }
        
        .reminder-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #28a745;
            transition: transform 0.3s ease;
        }
        
        .reminder-card:hover {
            transform: translateY(-2px);
        }
        
        .reminder-card.overdue {
            border-left-color: #dc3545;
            background: #fff5f5;
        }
        
        .reminder-card.today {
            border-left-color: #ffc107;
            background: #fffbf0;
        }
        
        .reminder-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .reminder-type {
            font-weight: bold;
            color: #495057;
        }
        
        .reminder-status {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .reminder-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .reminder-status.sent {
            background: #d4edda;
            color: #155724;
        }
        
        .reminder-status.failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .reminder-message {
            color: #495057;
            margin: 10px 0;
            line-height: 1.5;
        }
        
        .reminder-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: #6c757d;
        }
        
        .reminder-time {
            font-weight: 500;
        }
        
        .prescription-info {
            color: #007bff;
        }
    </style>
</body>
</html>