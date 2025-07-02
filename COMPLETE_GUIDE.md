# 医疗系统完整指南

## 项目概述

这是一个基于 ThinkPHP 6.1 开发的医疗系统，主要实现处方图片上传、AI智能识别复诊时间和用药频次等功能。

## 🎯 已完成功能

### 1. 处方上传系统
- ✅ 支持多种图片格式（JPG、PNG、GIF）
- ✅ 文件大小限制（5MB）
- ✅ 拖拽上传界面
- ✅ 上传进度显示
- ✅ 文件安全验证

### 2. AI智能识别
- ✅ ThinkAI服务集成
- ✅ 复诊时间提取
- ✅ 用药频次识别
- ✅ 药物详情解析
- ✅ 医生和医院信息提取
- ✅ Mock数据支持（开发环境）

### 3. 数据管理
- ✅ SQLite数据库存储
- ✅ 处方记录管理
- ✅ 用户关联系统
- ✅ 状态跟踪（待处理/已处理/失败）

### 4. API接口
- ✅ RESTful API设计
- ✅ 处方上传接口
- ✅ 处方详情查询
- ✅ 处方列表分页查询
- ✅ 统一错误处理

### 5. Web界面
- ✅ 响应式设计
- ✅ 现代化UI界面
- ✅ 实时反馈
- ✅ 错误提示

## 🚀 快速开始

### 环境要求
- PHP >= 8.0
- SQLite扩展
- cURL扩展
- GD扩展

### 安装步骤

1. **获取项目**
```bash
cd /workspace/medicalsystem
```

2. **安装依赖**
```bash
composer install
```

3. **初始化数据库**
```bash
php init_db_fixed.php
```

4. **创建上传目录**
```bash
mkdir -p public/uploads/prescriptions
chmod 755 public/uploads/prescriptions
```

5. **启动服务**
```bash
php -S localhost:8080 -t public
```

6. **访问系统**
- Web界面: http://localhost:8080/prescription
- API测试: http://localhost:8080/test

## 📁 项目结构

```
medicalsystem/
├── app/
│   ├── controller/
│   │   ├── PrescriptionController.php  # 处方控制器
│   │   └── TestController.php          # 测试控制器
│   ├── model/
│   │   ├── Prescription.php            # 处方模型
│   │   └── User.php                    # 用户模型
│   ├── service/
│   │   └── ThinkAIService.php          # AI服务
│   ├── view/
│   │   └── prescription/
│   │       └── upload.php              # 上传页面
│   └── BaseController.php              # 基础控制器
├── config/
│   ├── database.php                    # 数据库配置
│   ├── route.php                       # 路由配置
│   └── log.php                         # 日志配置
├── public/
│   ├── uploads/prescriptions/          # 上传文件目录
│   └── index.php                       # 入口文件
├── runtime/
│   ├── medical_system.db               # SQLite数据库
│   └── log/                            # 日志目录
├── vendor/                             # Composer依赖
├── composer.json                       # 项目配置
├── .env                                # 环境配置
└── README.md                           # 项目说明
```

## 🔌 API接口文档

### 1. 处方上传
```http
POST /prescription/upload
Content-Type: multipart/form-data

参数:
- user_id: 用户ID (必填)
- prescription_image: 处方图片文件 (必填)

响应:
{
    "code": 200,
    "message": "处方上传成功",
    "data": {
        "prescription_id": 1,
        "status": 1,
        "image_url": "http://localhost:8080/uploads/prescriptions/xxx.png"
    }
}
```

### 2. 处方详情查询
```http
GET /prescription/detail?id={id}

参数:
- id: 处方ID (必填)

响应:
{
    "code": 200,
    "message": "获取成功",
    "data": {
        "id": 1,
        "user_id": 1,
        "prescription_image": "http://localhost:8080/uploads/prescriptions/xxx.png",
        "original_filename": "prescription.jpg",
        "extracted_content": "{...}",
        "follow_up_date": "2025-07-09 12:00:00",
        "medication_frequency": "每日3次，饭后服用",
        "medication_details": "[...]",
        "status": 1,
        "status_text": "已处理"
    }
}
```

### 3. 处方列表查询
```http
GET /prescription/list?user_id={user_id}&page={page}&limit={limit}

参数:
- user_id: 用户ID (必填)
- page: 页码 (可选，默认1)
- limit: 每页数量 (可选，默认10)

响应:
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

## 🗄️ 数据库设计

### 处方表 (prescription)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INTEGER | 主键，自增 |
| user_id | INTEGER | 用户ID |
| prescription_image | TEXT | 处方图片文件名 |
| original_filename | TEXT | 原始文件名 |
| file_size | INTEGER | 文件大小(字节) |
| extracted_content | TEXT | AI提取的完整内容(JSON格式) |
| follow_up_date | TEXT | 复诊时间 |
| medication_frequency | TEXT | 用药频次 |
| medication_details | TEXT | 药物详情(JSON格式) |
| status | INTEGER | 处理状态(0:待处理,1:已处理,2:失败) |
| create_time | TEXT | 创建时间 |
| update_time | TEXT | 更新时间 |

### 用户表 (user)
| 字段 | 类型 | 说明 |
|------|------|------|
| id | INTEGER | 主键，自增 |
| username | TEXT | 用户名 |
| phone | TEXT | 手机号 |
| email | TEXT | 邮箱 |
| wechat_openid | TEXT | 微信OpenID |
| avatar | TEXT | 头像URL |
| status | INTEGER | 用户状态 |
| create_time | TEXT | 创建时间 |
| update_time | TEXT | 更新时间 |

## 🧪 测试指南

### 1. 组件测试
```bash
php test_api_components.php
```

### 2. 上传功能测试
```bash
php test_upload_final.php
```

### 3. API接口测试
```bash
# 测试数据库连接
curl http://localhost:8080/test/database

# 测试处方查询
curl http://localhost:8080/test/prescription

# 测试处方上传
curl -X POST -F "user_id=1" -F "prescription_image=@test_prescription.png" http://localhost:8080/prescription/upload

# 测试处方列表
curl "http://localhost:8080/prescription/list?user_id=1"

# 测试处方详情
curl "http://localhost:8080/prescription/detail?id=1"
```

### 4. Web界面测试
访问 http://localhost:8080/prescription 进行手动测试

## ⚙️ 配置说明

### 数据库配置
文件: `config/database.php`
```php
'default' => 'sqlite',
'connections' => [
    'sqlite' => [
        'type' => 'sqlite',
        'database' => app()->getRuntimePath() . 'medical_system.db',
        'prefix' => '',
        'charset' => 'utf8',
    ]
]
```

### AI服务配置
文件: `.env`
```env
# ThinkAI API配置
THINKAI_API_KEY=your_api_key_here
THINKAI_API_URL=https://api.thinkai.com/v1/

# 如果不配置API密钥，系统将使用Mock数据
```

### 路由配置
文件: `config/route.php`
```php
// 处方相关路由
Route::group('prescription', function () {
    Route::get('/', 'PrescriptionController/index');
    Route::post('upload', 'PrescriptionController/upload');
    Route::get('detail', 'PrescriptionController/detail');
    Route::get('list', 'PrescriptionController/list');
});

// 测试路由
Route::group('test', function () {
    Route::get('/', 'TestController/index');
    Route::get('database', 'TestController/database');
    Route::get('prescription', 'TestController/prescription');
});
```

## 🔧 开发说明

### AI服务集成
ThinkAI服务支持两种运行模式：

1. **API模式**: 配置真实API密钥后调用ThinkAI服务
2. **Mock模式**: 未配置API密钥时返回模拟数据，便于开发测试

### 错误处理机制
- 统一的异常处理
- 详细的错误日志记录
- 用户友好的错误提示
- API错误码标准化

### 安全考虑
- 文件类型白名单验证
- 文件大小限制
- 上传路径安全检查
- SQL注入防护
- XSS攻击防护

### 性能优化
- 数据库查询优化
- 文件上传大小限制
- 分页查询支持
- 缓存机制预留

## 🔮 后续开发计划

### 1. 提醒系统
- [ ] 复诊时间提醒
- [ ] 用药时间提醒
- [ ] 微信小程序推送
- [ ] 短信提醒服务

### 2. 餐食识别系统
- [ ] 餐食图片上传
- [ ] 食物智能识别
- [ ] 卡路里自动计算
- [ ] 个性化饮食建议

### 3. 用户系统完善
- [ ] 用户注册登录
- [ ] 权限管理系统
- [ ] 个人中心功能
- [ ] 数据统计分析

### 4. 系统优化
- [ ] 缓存系统集成
- [ ] 队列任务处理
- [ ] 日志分析系统
- [ ] 监控告警机制

## 📞 技术支持

如遇到问题，请检查：
1. PHP版本是否符合要求
2. 必要的PHP扩展是否已安装
3. 数据库文件权限是否正确
4. 上传目录是否可写
5. 查看运行时日志文件

## 📄 许可证

MIT License - 详见 LICENSE 文件

---

**开发完成时间**: 2025年7月2日  
**版本**: v1.0.0  
**状态**: 生产就绪