# 宝塔面板配置清单

## 1. 网站基本设置

### 网站目录配置
```
网站根目录: /www/wwwroot/your-domain.com
运行目录: /public
防跨站攻击: 开启
```

### 伪静态规则 (选择ThinkPHP)
```nginx
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
        break;
    }
}
```

## 2. PHP配置

### PHP版本要求
- 推荐使用 PHP 8.1 或 8.2

### 必需扩展
在 软件商店 → PHP管理 → 安装扩展：
```
✓ sqlite3      (数据库)
✓ pdo_sqlite   (数据库PDO)
✓ curl         (HTTP请求)
✓ mbstring     (多字节字符串)
✓ xml          (XML处理)
✓ fileinfo     (文件信息)
✓ openssl      (加密)
✓ json         (JSON处理)
✓ gd           (图像处理)
✓ zip          (压缩文件)
```

### PHP配置调整
在 软件商店 → PHP设置 → 配置修改：
```ini
# 上传限制
upload_max_filesize = 20M
post_max_size = 20M
max_file_uploads = 20

# 执行限制
max_execution_time = 300
max_input_time = 300
memory_limit = 256M

# 错误报告 (生产环境)
display_errors = Off
log_errors = On
error_log = /www/server/php/82/var/log/php_errors.log
```

## 3. 目录权限设置

### 可写目录 (权限755)
```
runtime/
public/storage/
public/uploads/
database/
```

### 安全目录 (禁止访问)
在 网站设置 → 目录保护 中添加：
```
/app/
/config/
/database/
/runtime/
/vendor/
/.git/
/.env
```

## 4. SSL证书配置

### Let's Encrypt免费证书
```
网站设置 → SSL → Let's Encrypt
域名: your-domain.com
自动续签: 开启
强制HTTPS: 开启
```

## 5. 安全设置

### 防火墙规则
```
端口: 80, 443 (开启)
端口: 22 (限制IP访问)
其他端口: 根据需要开启
```

### 文件上传安全
在 网站设置 → 配置文件 中添加：
```nginx
# 禁止执行PHP文件的目录
location ~* ^/(public/storage|public/uploads)/.*\.(php|php3|php4|php5|phtml|pht)$ {
    deny all;
}

# 限制文件类型
location ~* \.(php|php3|php4|php5|phtml|pht)$ {
    if ($request_uri ~* ^/(public/storage|public/uploads)) {
        return 403;
    }
    # 正常PHP处理
    include enable-php-82.conf;
}
```

## 6. 性能优化

### OPcache配置
在 软件商店 → PHP → 安装扩展 → OPcache：
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### Gzip压缩
```
网站设置 → 性能优化 → Gzip压缩: 开启
压缩级别: 6
```

### 静态文件缓存
在 网站设置 → 配置文件 中添加：
```nginx
# 静态文件缓存
location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
    expires 30d;
    add_header Cache-Control "public, immutable";
}
```

## 7. 计划任务

### 提醒任务
```
任务类型: Shell脚本
任务名称: 医疗系统提醒
执行周期: 每小时
脚本内容: cd /www/wwwroot/your-domain.com && php think reminder:send
```

### 日志清理
```
任务类型: Shell脚本
任务名称: 清理系统日志
执行周期: 每天 02:00
脚本内容: find /www/wwwroot/your-domain.com/runtime/log -name "*.log" -mtime +7 -delete
```

### 数据库备份
```
任务类型: 备份网站
任务名称: 医疗系统备份
执行周期: 每天 03:00
保留天数: 7天
备份到: 本地 + 云存储
```

## 8. 监控设置

### 网站监控
```
监控 → 网站监控
监控地址: https://your-domain.com
监控间隔: 5分钟
超时时间: 10秒
异常通知: 开启
```

### 服务器监控
```
监控 → 服务器监控
CPU使用率: >80% 报警
内存使用率: >85% 报警
磁盘使用率: >90% 报警
```

## 9. 数据库管理

### SQLite数据库位置
```
主数据库: /www/wwwroot/your-domain.com/database/medical.db
备份位置: /www/backup/database/
```

### 数据库维护
```bash
# 数据库优化 (每周执行)
cd /www/wwwroot/your-domain.com
sqlite3 database/medical.db "VACUUM;"

# 数据库备份
cp database/medical.db /www/backup/database/medical_$(date +%Y%m%d).db
```

## 10. 日志管理

### 日志文件位置
```
应用日志: runtime/log/
PHP错误日志: /www/server/php/82/var/log/php_errors.log
Nginx访问日志: /www/wwwroot/your-domain.com/log/
Nginx错误日志: /www/server/nginx/logs/error.log
```

### 日志轮转
```bash
# 添加到计划任务，每周执行
find /www/wwwroot/your-domain.com/runtime/log -name "*.log" -size +10M -exec gzip {} \;
find /www/wwwroot/your-domain.com/runtime/log -name "*.gz" -mtime +30 -delete
```

## 11. 故障排查

### 常见问题检查清单

#### 500错误
1. 检查PHP版本和扩展
2. 检查目录权限
3. 查看错误日志
4. 检查.htaccess语法

#### 数据库连接失败
1. 检查SQLite扩展
2. 检查数据库文件权限
3. 检查数据库路径

#### 文件上传失败
1. 检查upload目录权限
2. 检查PHP上传配置
3. 检查磁盘空间

#### 页面空白
1. 开启错误显示调试
2. 检查PHP内存限制
3. 查看PHP错误日志

### 调试模式开启
临时开启调试（仅用于故障排查）：
```php
// config/app.php
'debug' => true,
```

**注意：生产环境务必关闭调试模式！**

## 12. 安全检查清单

- [ ] 关闭调试模式
- [ ] 设置强密码
- [ ] 开启SSL证书
- [ ] 配置防火墙
- [ ] 禁止目录访问
- [ ] 定期更新系统
- [ ] 监控异常访问
- [ ] 备份重要数据

## 13. 维护建议

### 定期维护任务
- 每周检查系统日志
- 每月更新依赖包
- 每季度安全审计
- 每年系统升级

### 性能监控
- 监控响应时间
- 监控数据库大小
- 监控磁盘使用
- 监控内存使用

---

**技术支持**
如遇到问题，请按以下顺序排查：
1. 查看宝塔面板错误日志
2. 查看应用运行日志
3. 检查PHP配置
4. 验证目录权限
5. 测试数据库连接