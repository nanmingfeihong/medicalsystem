# 宝塔面板部署指南

## 系统要求

- PHP 8.0 或更高版本
- SQLite 扩展
- Composer
- 宝塔面板 7.0+

## 部署步骤

### 1. 环境准备

#### 1.1 安装PHP扩展
在宝塔面板 → 软件商店 → PHP管理 → 安装扩展：
```
- sqlite3 (必需)
- curl (必需)
- mbstring (必需)
- xml (必需)
- fileinfo (必需)
- openssl (必需)
```

#### 1.2 安装Composer
```bash
# SSH连接服务器后执行
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

### 2. 代码部署

#### 2.1 下载代码
```bash
# 进入网站根目录
cd /www/wwwroot/your-domain.com

# 克隆代码
git clone https://github.com/nanmingfeihong/medicalsystem.git .

# 或者直接下载ZIP包解压
```

#### 2.2 安装依赖
```bash
# 安装PHP依赖
composer install --no-dev --optimize-autoloader

# 如果composer install失败，可以尝试：
composer install --ignore-platform-reqs
```

### 3. 目录权限设置

在宝塔面板 → 文件 → 网站根目录，设置以下目录权限为755：
```
runtime/          (运行时缓存目录)
public/storage/   (文件上传目录)
public/uploads/   (上传文件目录)
database/         (数据库目录)
```

### 4. 网站配置

#### 4.1 设置网站运行目录
在宝塔面板 → 网站 → 设置 → 网站目录：
- 运行目录设置为：`/public`
- 开启防跨站攻击

#### 4.2 设置伪静态规则
在宝塔面板 → 网站 → 设置 → 伪静态，选择ThinkPHP规则：
```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
        break;
    }
}
```

#### 4.3 PHP配置调整
在宝塔面板 → 软件商店 → PHP设置 → 配置修改：
```ini
# 上传文件大小限制
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
memory_limit = 256M

# 开启必要扩展
extension=sqlite3
extension=pdo_sqlite
extension=curl
extension=mbstring
extension=xml
extension=fileinfo
extension=openssl
```

### 5. 数据库初始化

#### 5.1 创建数据库文件
```bash
# SSH连接服务器
cd /www/wwwroot/your-domain.com

# 创建数据库目录
mkdir -p database
chmod 755 database

# 复制数据库文件（如果存在）
cp runtime/medical_system.db database/medical.db
chmod 644 database/medical.db
```

#### 5.2 数据库配置
编辑 `config/database.php`：
```php
return [
    'default' => 'sqlite',
    'connections' => [
        'sqlite' => [
            'type' => 'sqlite',
            'database' => root_path() . 'database/medical.db',
            'prefix' => '',
            'charset' => 'utf8',
        ],
    ],
];
```

### 6. 应用配置

#### 6.1 环境配置
编辑 `config/app.php`：
```php
return [
    'debug' => false,  // 生产环境关闭调试
    'app_host' => 'your-domain.com',
    'app_domain' => 'your-domain.com',
    // 其他配置...
];
```

#### 6.2 通义千问API配置
编辑 `config/qwen.php`：
```php
return [
    'app_id' => 'your_app_id',
    'api_key' => 'your_api_key',
    'model' => 'qwen-turbo-0919',
    'base_url' => 'https://dashscope.aliyuncs.com/api/v1/services/aigc/text-generation/generation',
];
```

### 7. SSL证书配置（推荐）

在宝塔面板 → 网站 → 设置 → SSL：
- 申请Let's Encrypt免费证书
- 开启强制HTTPS

### 8. 安全设置

#### 8.1 目录访问限制
在宝塔面板 → 网站 → 设置 → 目录保护，禁止访问：
```
/app/
/config/
/database/
/runtime/
/vendor/
/.git/
```

#### 8.2 文件上传安全
编辑 `.htaccess` 或 Nginx配置：
```apache
# Apache (.htaccess)
<Files ~ "\.(php|php3|php4|php5|phtml|pht)$">
    Order Deny,Allow
    Deny from all
</Files>
```

### 9. 性能优化

#### 9.1 开启OPcache
在宝塔面板 → 软件商店 → PHP → 安装扩展 → OPcache

#### 9.2 开启Gzip压缩
在宝塔面板 → 网站 → 设置 → 性能优化 → 开启Gzip

#### 9.3 设置缓存
```bash
# 清理并预热缓存
php think clear
php think optimize:autoload
```

### 10. 定时任务设置

在宝塔面板 → 计划任务，添加以下任务：

#### 10.1 提醒任务（每小时执行）
```bash
# 任务名称：医疗提醒
# 执行周期：每小时
# 脚本内容：
cd /www/wwwroot/your-domain.com && php think reminder:send
```

#### 10.2 日志清理（每天执行）
```bash
# 任务名称：清理日志
# 执行周期：每天 02:00
# 脚本内容：
find /www/wwwroot/your-domain.com/runtime/log -name "*.log" -mtime +7 -delete
```

### 11. 监控和维护

#### 11.1 网站监控
在宝塔面板 → 监控 → 网站监控，设置：
- 监控URL：https://your-domain.com
- 监控频率：5分钟
- 异常通知：开启

#### 11.2 备份设置
在宝塔面板 → 计划任务 → 备份网站：
- 备份周期：每天
- 保留份数：7份
- 备份到云存储（推荐）

### 12. 测试验证

部署完成后，访问以下URL测试功能：

```
https://your-domain.com/                    # 首页
https://your-domain.com/prescription/upload # 处方上传
https://your-domain.com/colon_screening/    # 肠癌筛查
https://your-domain.com/meal/upload         # 饮食分析
https://your-domain.com/reminder/list       # 提醒列表
```

### 常见问题解决

#### Q1: 500错误
- 检查PHP版本是否≥8.0
- 检查目录权限是否正确
- 查看错误日志：`runtime/log/`

#### Q2: 数据库连接失败
- 检查SQLite扩展是否安装
- 检查数据库文件权限
- 检查数据库路径配置

#### Q3: 文件上传失败
- 检查upload目录权限
- 检查PHP上传配置
- 检查磁盘空间

#### Q4: Composer安装失败
```bash
# 使用国内镜像
composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
composer install
```

### 技术支持

如遇到部署问题，请检查：
1. PHP错误日志：`/www/server/php/版本号/var/log/php-fpm.log`
2. 网站错误日志：`runtime/log/`
3. 宝塔面板日志：面板 → 日志

---

**注意事项：**
- 生产环境务必关闭debug模式
- 定期备份数据库和上传文件
- 及时更新系统和依赖包
- 监控服务器资源使用情况