# 宝塔面板PHP 404问题修复指南

## 🚨 问题分析

您的情况：
- ✅ 静态文件可访问 (1.txt)
- ❌ PHP文件返回404 (index.php, test.php)
- ✅ 运行目录已设置为 /public

**结论**: Nginx配置中PHP处理部分有问题

## 🔧 解决方案

### 方案一：使用自动修复脚本 (推荐)

```bash
# SSH连接服务器
cd /www/wwwroot/medical.genoimage.cn
wget https://raw.githubusercontent.com/nanmingfeihong/medicalsystem/feature/complete-medical-system/fix-php-404.sh
chmod +x fix-php-404.sh
./fix-php-404.sh
```

### 方案二：宝塔面板手动修复

#### 1. 检查PHP版本设置
```
宝塔面板 → 网站 → medical.genoimage.cn → 设置 → PHP版本
确保选择: PHP 8.0
```

#### 2. 重新设置网站配置
```
宝塔面板 → 网站 → medical.genoimage.cn → 设置 → 配置文件

将整个配置替换为以下内容：
```

```nginx
server
{
    listen 80;
    listen 443 ssl http2;
    server_name medical.genoimage.cn;
    index index.php index.html index.htm default.php default.htm default.html;
    root /www/wwwroot/medical.genoimage.cn/public;
    
    # 错误页面
    #error_page   404   /404.html;
    
    # 安全设置
    include /www/server/nginx/conf/rewrite/thinkphp.conf;
    
    # 禁止访问的文件或目录
    location ~ ^/(\.user.ini|\.htaccess|\.git|\.svn|\.project|LICENSE|README.md)
    {
        return 404;
    }
    
    # 一键申请SSL证书验证目录相关设置
    location ~ \.well-known{
        allow all;
    }
    
    # PHP处理 - 关键配置
    location ~ \.php(.*)$
    {
        fastcgi_pass unix:/tmp/php-cgi-80.sock;
        fastcgi_index index.php;
        fastcgi_split_path_info ^((?U).+\.php)(/?.+)$;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        include fastcgi_params;
    }
    
    # ThinkPHP URL重写
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # 静态文件缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    # 访问日志
    access_log  /www/wwwlogs/medical.genoimage.cn.log;
    error_log  /www/wwwlogs/medical.genoimage.cn.error.log;
}
```

#### 3. 创建ThinkPHP重写规则
```
宝塔面板 → 网站 → medical.genoimage.cn → 设置 → 伪静态

选择: ThinkPHP
或手动输入：

location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
        break;
    }
}
```

#### 4. 检查PHP-FPM状态
```
宝塔面板 → 软件商店 → PHP 8.0 → 设置 → 服务
确保状态为: 运行中
如果未运行，点击启动
```

#### 5. 重启服务
```
宝塔面板 → 软件商店 → Nginx → 设置 → 服务 → 重启
宝塔面板 → 软件商店 → PHP 8.0 → 设置 → 服务 → 重启
```

## 🧪 测试验证

### 1. 创建测试文件
在 `/www/wwwroot/medical.genoimage.cn/public/` 目录下创建 `phpinfo.php`：

```php
<?php
echo "PHP版本: " . PHP_VERSION . "<br>";
echo "当前时间: " . date('Y-m-d H:i:s') . "<br>";
echo "服务器信息: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "文档根目录: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "脚本名称: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "请求URI: " . $_SERVER['REQUEST_URI'] . "<br>";
?>
```

### 2. 测试访问
按顺序测试以下URL：

1. **静态文件测试**: `https://medical.genoimage.cn/1.txt`
   - 应该正常显示内容

2. **PHP信息测试**: `https://medical.genoimage.cn/phpinfo.php`
   - 应该显示PHP版本等信息

3. **原有测试文件**: `https://medical.genoimage.cn/test.php`
   - 应该正常显示

4. **系统入口**: `https://medical.genoimage.cn/index.php`
   - 应该显示系统页面

5. **根目录访问**: `https://medical.genoimage.cn/`
   - 应该自动跳转到index.php

## 🔍 故障排查

### 如果仍然404，检查以下项目：

#### 1. 检查PHP-FPM Socket
```bash
# SSH连接服务器
ls -la /tmp/php-cgi-80.sock
# 应该显示socket文件存在
```

#### 2. 检查Nginx错误日志
```bash
tail -f /www/wwwlogs/medical.genoimage.cn.error.log
```

#### 3. 检查PHP错误日志
```bash
tail -f /www/server/php/80/var/log/php_errors.log
```

#### 4. 测试Nginx配置
```bash
nginx -t
# 应该显示配置正确
```

#### 5. 检查文件权限
```bash
ls -la /www/wwwroot/medical.genoimage.cn/public/
# 确保文件所有者为www:www
```

## 🚨 常见错误及解决

### 错误1: 502 Bad Gateway
**原因**: PHP-FPM未运行
**解决**: 重启PHP-FPM服务

### 错误2: 403 Forbidden
**原因**: 文件权限问题
**解决**: 
```bash
chown -R www:www /www/wwwroot/medical.genoimage.cn/
chmod 644 /www/wwwroot/medical.genoimage.cn/public/*.php
```

### 错误3: 仍然404
**原因**: Nginx配置未生效
**解决**: 
1. 检查配置文件语法
2. 重启Nginx服务
3. 清除浏览器缓存

## 📞 紧急处理

如果上述方法都无效，请执行以下紧急重置：

```bash
# 1. 重置Nginx配置
cd /www/server/panel/vhost/nginx/
cp medical.genoimage.cn.conf medical.genoimage.cn.conf.backup
# 然后在宝塔面板中删除网站，重新添加

# 2. 重新添加网站
# 宝塔面板 → 网站 → 添加站点
# 域名: medical.genoimage.cn
# 根目录: /www/wwwroot/medical.genoimage.cn
# PHP版本: 8.0

# 3. 设置运行目录为 /public
# 4. 配置伪静态为 ThinkPHP
```

## ✅ 成功标志

修复成功后，您应该能看到：
- `https://medical.genoimage.cn/phpinfo.php` 显示PHP信息
- `https://medical.genoimage.cn/` 显示医疗系统首页
- Nginx日志显示200状态码而不是404