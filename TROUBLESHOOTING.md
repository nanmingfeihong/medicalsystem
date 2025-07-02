# 宝塔部署问题排查指南

## 🚨 常见问题及解决方案

### 问题1: 404 Not Found 错误

#### 原因分析
1. 网站运行目录未正确设置
2. 伪静态规则未配置
3. PHP配置问题
4. 文件权限问题

#### 解决步骤

##### 1. 检查网站运行目录
在宝塔面板中：
```
网站 → 设置 → 网站目录 → 运行目录
确保设置为: /public
```

##### 2. 配置伪静态规则
在宝塔面板中：
```
网站 → 设置 → 伪静态
选择: ThinkPHP
或手动添加以下规则：

location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
        break;
    }
}
```

##### 3. 检查入口文件
确保 `/www/wwwroot/medical.genoimage.cn/public/index.php` 文件存在且可访问

### 问题2: PHP配置错误

#### 错误信息
```
PHP: syntax error, unexpected '=' in /www/server/php/80/etc/php-cli.ini on line 1953
```

#### 解决方案
1. 在宝塔面板中：`软件商店 → PHP 8.0 → 配置修改`
2. 检查第1953行附近的配置，删除多余的 `=` 号
3. 或者重置PHP配置文件

### 问题3: 内存限制问题

#### 错误信息
```
Fatal error: Allowed memory size of 2097152 bytes exhausted
```

#### 解决方案
在宝塔面板中调整PHP配置：
```
软件商店 → PHP 8.0 → 配置修改
找到以下配置并修改：

memory_limit = 256M
max_execution_time = 300
```

### 问题4: open_basedir 限制

#### 错误信息
```
Warning: require(): open_basedir restriction in effect
```

#### 解决方案
在宝塔面板中：
```
网站 → 设置 → 网站目录 → 防跨站攻击
关闭防跨站攻击，或者添加允许的目录路径
```

### 问题5: Composer依赖安装失败

#### 解决方案
手动重新安装依赖：
```bash
cd /www/wwwroot/medical.genoimage.cn
rm -rf vendor/
composer install --no-dev --optimize-autoloader
```

## 🔧 完整修复步骤

### 步骤1: 修复PHP配置
```bash
# SSH连接服务器
# 备份原配置
cp /www/server/php/80/etc/php-cli.ini /www/server/php/80/etc/php-cli.ini.bak

# 检查并修复配置文件
nano /www/server/php/80/etc/php-cli.ini
# 找到第1953行，删除多余的 = 号

# 重启PHP
systemctl restart php-fpm-80
```

### 步骤2: 重新安装依赖
```bash
cd /www/wwwroot/medical.genoimage.cn

# 清理旧的依赖
rm -rf vendor/ composer.lock

# 重新安装
composer install --no-dev --optimize-autoloader --ignore-platform-reqs
```

### 步骤3: 设置正确的目录权限
```bash
cd /www/wwwroot/medical.genoimage.cn

# 设置目录权限
chmod -R 755 runtime/
chmod -R 755 public/storage/
chmod -R 755 public/uploads/
chmod -R 755 database/

# 设置文件所有者
chown -R www:www /www/wwwroot/medical.genoimage.cn/
```

### 步骤4: 宝塔面板配置

#### 4.1 网站目录设置
```
网站 → 设置 → 网站目录
运行目录: /public
防跨站攻击: 关闭（临时）
```

#### 4.2 PHP配置调整
```
软件商店 → PHP 8.0 → 配置修改

memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 20M
post_max_size = 20M
```

#### 4.3 伪静态配置
```
网站 → 设置 → 伪静态
选择: ThinkPHP
```

### 步骤5: 验证修复结果
```bash
# 检查入口文件
ls -la /www/wwwroot/medical.genoimage.cn/public/index.php

# 检查依赖
ls -la /www/wwwroot/medical.genoimage.cn/vendor/autoload.php

# 测试PHP
php -v

# 测试应用
cd /www/wwwroot/medical.genoimage.cn
php think version
```

## 🌐 访问测试

修复完成后，按以下顺序测试：

1. **基础访问测试**
   ```
   http://medical.genoimage.cn/
   ```

2. **检查页面测试**
   ```
   http://medical.genoimage.cn/check.php
   ```

3. **功能页面测试**
   ```
   http://medical.genoimage.cn/prescription/upload
   http://medical.genoimage.cn/colon_screening/
   ```

## 📞 紧急修复脚本

如果上述步骤仍有问题，使用以下紧急修复脚本：

```bash
#!/bin/bash
# 紧急修复脚本

SITE_PATH="/www/wwwroot/medical.genoimage.cn"
cd $SITE_PATH

echo "🔧 开始紧急修复..."

# 1. 修复权限
echo "修复权限..."
chown -R www:www $SITE_PATH
chmod -R 755 runtime/ public/storage/ public/uploads/ database/

# 2. 重新安装依赖
echo "重新安装依赖..."
rm -rf vendor/ composer.lock
composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# 3. 创建必要目录
echo "创建目录..."
mkdir -p runtime/log runtime/cache runtime/temp
mkdir -p public/storage/prescriptions public/storage/meals
mkdir -p database

# 4. 复制数据库
if [ -f "runtime/medical_system.db" ]; then
    cp runtime/medical_system.db database/medical.db
    chmod 644 database/medical.db
fi

# 5. 生成.htaccess
cat > public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    Options +FollowSymlinks -Multiviews
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>
EOF

echo "✅ 紧急修复完成！"
echo "请在宝塔面板中："
echo "1. 设置运行目录为 /public"
echo "2. 配置ThinkPHP伪静态"
echo "3. 调整PHP内存限制为256M"
```

## 📋 检查清单

修复完成后，请确认以下项目：

- [ ] 网站运行目录设置为 `/public`
- [ ] 伪静态规则配置为 ThinkPHP
- [ ] PHP内存限制 ≥ 256M
- [ ] 防跨站攻击已关闭或正确配置
- [ ] vendor/autoload.php 文件存在
- [ ] public/index.php 文件存在且可访问
- [ ] 目录权限正确 (755)
- [ ] 文件所有者为 www:www

## 🆘 如果仍有问题

如果按照上述步骤仍无法解决，请：

1. 查看宝塔面板错误日志：`网站 → 日志`
2. 查看PHP错误日志：`/www/server/php/80/var/log/php_errors.log`
3. 查看Nginx错误日志：`/www/server/nginx/logs/error.log`
4. 提供具体的错误信息以便进一步诊断