# 医疗系统 - 处方上传与AI识别

这是一个基于 ThinkPHP 6 开发的医疗系统，主要功能是处方上传和AI智能识别。

## 功能特性

### 已实现功能
- ✅ 处方图片上传
- ✅ AI智能识别处方内容（复诊时间、用药频次）
- ✅ 处方信息存储和管理
- ✅ 用户处方列表查询
- ✅ 响应式前端界面

### 计划功能
- 🔄 微信小程序提醒功能
- 🔄 短信提醒功能
- 🔄 饮食记录和分析
- 🔄 营养建议推送

## 技术栈

- **后端**: ThinkPHP 6.x
- **数据库**: SQLite (可切换到MySQL)
- **AI服务**: ThinkAI API (支持模拟数据)
- **前端**: HTML5 + CSS3 + JavaScript

## 快速开始

### 1. 环境要求
- PHP >= 7.4
- Composer
- SQLite 扩展

### 2. 安装步骤

```bash
# 克隆项目
git clone <repository-url>
cd medicalsystem

# 安装依赖
composer install

# 初始化数据库
php init_db_simple.php

# 创建上传目录
mkdir -p public/uploads/prescriptions

# 启动服务器
php -S localhost:8080 -t public
```

### 3. 访问应用

- 主页面: http://localhost:8080/prescription
- 测试页面: 在浏览器中打开 `test_upload.html`

## API 接口

### 处方上传
```
POST /prescription/upload
Content-Type: multipart/form-data

参数:
- user_id: 用户ID (必填)
- prescription_image: 处方图片文件 (必填)

返回:
{
    "code": 200,
    "message": "处方上传成功",
    "data": {
        "prescription_id": 1,
        "status": 1,
        "image_url": "http://localhost:8080/uploads/prescriptions/xxx.jpg"
    }
}
```

### 获取处方详情
```
GET /prescription/detail?id=1

返回:
{
    "code": 200,
    "message": "获取成功",
    "data": {
        "id": 1,
        "user_id": 1,
        "prescription_image": "http://localhost:8080/uploads/prescriptions/xxx.jpg",
        "follow_up_date": "2025-07-09 11:55:26",
        "medication_frequency": "每日3次，饭后服用",
        "medication_details": "[{\"name\":\"阿莫西林胶囊\",\"dosage\":\"0.5g\"}]",
        "status": 1,
        "status_text": "已处理"
    }
}
```

### 获取用户处方列表
```
GET /prescription/list?user_id=1&page=1&limit=10

返回:
{
    "code": 200,
    "message": "获取成功",
    "data": {
        "list": [...],
        "total": 10,
        "page": 1,
        "limit": 10
    }
}
```

## 配置说明

### 环境配置 (.env)
```env
# 应用配置
APP_DEBUG = true

# 数据库配置
DATABASE_TYPE = sqlite
DATABASE_DATABASE = ./runtime/medical_system.db

# ThinkAI配置
THINKAI_API_URL = https://api.thinkai.com/v1/ocr/prescription
THINKAI_API_KEY = your_api_key_here

# 文件上传配置
UPLOAD_MAX_SIZE = 5242880
UPLOAD_ALLOWED_EXT = jpg,jpeg,png,gif,bmp
```

### AI服务配置

如果没有配置 `THINKAI_API_KEY`，系统会自动使用模拟数据进行测试。

要使用真实的AI服务，请：
1. 获取ThinkAI API密钥
2. 在 `.env` 文件中配置 `THINKAI_API_KEY`

## 数据库结构

### 用户表 (user)
- id: 主键
- username: 用户名
- phone: 手机号
- email: 邮箱
- wechat_openid: 微信OpenID
- status: 状态

### 处方表 (prescription)
- id: 主键
- user_id: 用户ID
- prescription_image: 处方图片文件名
- extracted_content: AI提取的完整内容
- follow_up_date: 复诊时间
- medication_frequency: 用药频次
- medication_details: 药物详情
- status: 处理状态

### 提醒记录表 (reminder)
- id: 主键
- user_id: 用户ID
- prescription_id: 处方ID
- reminder_type: 提醒类型
- reminder_time: 提醒时间
- message: 提醒消息
- send_method: 发送方式
- status: 发送状态

## 测试

运行测试脚本：
```bash
php test_api.php
```

## 项目结构

```
medicalsystem/
├── app/
│   ├── controller/          # 控制器
│   │   └── PrescriptionController.php
│   ├── model/              # 模型
│   │   ├── Prescription.php
│   │   └── User.php
│   ├── service/            # 服务类
│   │   └── ThinkAIService.php
│   └── view/               # 视图
│       └── prescription/
│           └── upload.html
├── config/                 # 配置文件
├── database/              # 数据库相关
├── public/                # 公共目录
│   ├── uploads/           # 上传文件
│   └── index.php         # 入口文件
├── runtime/               # 运行时目录
├── vendor/                # 依赖包
├── .env                   # 环境配置
├── composer.json          # Composer配置
├── init_db_simple.php     # 数据库初始化
├── test_api.php          # API测试
└── test_upload.html      # 前端测试页面
```

## 开发说明

### 添加新功能
1. 在 `app/controller/` 中创建控制器
2. 在 `app/model/` 中创建模型
3. 在 `config/route.php` 中添加路由
4. 在 `app/view/` 中创建视图

### 扩展AI服务
修改 `app/service/ThinkAIService.php` 来支持更多AI功能。

### 数据库迁移
修改 `init_db_simple.php` 或创建新的迁移脚本。

## 许可证

MIT License

## 联系方式

如有问题，请提交 Issue 或联系开发团队。
