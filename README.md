# 🏥 智能医疗系统

这是一个基于 ThinkPHP 6 开发的综合医疗系统，集成了早期筛查、处方管理、智能提醒、饮食分析等功能，使用阿里巴巴通义千问大语言模型提供AI智能分析。

## 🌟 功能特性

### ✅ 已完成功能
- **处方管理系统**
  - 📷 处方图片上传
  - 🤖 AI智能识别处方内容（复诊时间、用药频次）
  - 📋 处方信息存储和管理
  - 📊 用户处方列表查询

- **智能提醒系统**
  - ⏰ 自动生成复诊提醒
  - 💊 智能用药提醒
  - 📱 提醒列表管理
  - 🔔 多种提醒方式支持

- **肠癌早期筛查**
  - 📝 智能问卷调查
  - 🧠 AI风险评估分析
  - 📈 个性化健康建议
  - 💾 筛查记录存储

- **饮食分析系统**
  - 📸 食物图片识别
  - 🍎 营养成分分析
  - 📊 卡路里计算
  - 💡 个性化饮食建议

- **系统特性**
  - 🎨 响应式前端界面
  - 🔒 安全的文件上传
  - 💾 SQLite数据库支持
  - 🚀 高性能架构设计

### 🔄 计划功能
- 微信小程序集成
- 短信提醒功能
- 更多疾病筛查模块
- 健康数据可视化

## 🛠 技术栈

- **后端框架**: ThinkPHP 6.1
- **数据库**: SQLite (支持MySQL)
- **AI服务**: 阿里巴巴通义千问 (Qwen)
- **前端技术**: HTML5 + CSS3 + JavaScript
- **开发语言**: PHP 8.0+
- **依赖管理**: Composer
- **部署环境**: 宝塔面板 (推荐)

## 🚀 快速开始

### 📋 环境要求
- PHP 8.0+
- Composer
- SQLite 扩展
- 宝塔面板 7.0+ (生产环境推荐)

### 💻 开发环境部署

```bash
# 1. 克隆项目
git clone https://github.com/nanmingfeihong/medicalsystem.git
cd medicalsystem

# 2. 安装依赖
composer install

# 3. 启动开发服务器
php -S localhost:8000 -t public

# 4. 访问系统
# 浏览器打开: http://localhost:8000
```

### 🏭 宝塔面板生产环境部署

#### 方式一：自动部署 (推荐)
```bash
# 1. 上传项目到网站根目录
# 2. SSH连接服务器，进入网站目录
cd /www/wwwroot/your-domain.com

# 3. 运行自动部署脚本
chmod +x deploy.sh
./deploy.sh

# 4. 按提示完成宝塔面板配置
```

#### 方式二：手动部署
详细步骤请参考：
- 📖 [宝塔面板部署指南](DEPLOYMENT.md)
- ⚙️ [宝塔配置清单](bt-config.md)

#### 部署验证
部署完成后访问：`http://your-domain.com/check.php` 检查系统状态

### 🌐 系统访问

部署成功后，您可以访问以下功能：

- **系统首页**: `http://your-domain.com/`
- **处方上传**: `http://your-domain.com/prescription/upload`
- **肠癌筛查**: `http://your-domain.com/colon_screening/`
- **饮食分析**: `http://your-domain.com/meal/upload`
- **提醒管理**: `http://your-domain.com/reminder/list`

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
