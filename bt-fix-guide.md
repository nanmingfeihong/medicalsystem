# 宝塔面板配置修复指南

## 🚨 紧急修复步骤

### 第一步：运行修复脚本
```bash
# SSH连接服务器
cd /www/wwwroot/medical.genoimage.cn
chmod +x fix-deployment.sh
./fix-deployment.sh
```

### 第二步：宝塔面板配置

#### 1. 网站目录设置 ⭐ 最重要
```
宝塔面板 → 网站 → medical.genoimage.cn → 设置 → 网站目录

✅ 运行目录: /public
✅ 防跨站攻击: 关闭 (这是关键！)
```

#### 2. 伪静态配置
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

#### 3. PHP配置调整
```
宝塔面板 → 软件商店 → PHP 8.0 → 设置 → 配置修改

找到并修改以下配置：
memory_limit = 512M
max_execution_time = 300
upload_max_filesize = 20M
post_max_size = 20M
```

#### 4. 修复PHP配置文件语法错误
```
宝塔面板 → 软件商店 → PHP 8.0 → 设置 → 配置修改

找到第1953行附近，如果有类似这样的错误：
some_setting == value

修改为：
some_setting = value

保存后重启PHP
```

### 第三步：验证修复

#### 1. 测试基础PHP功能
访问：`http://medical.genoimage.cn/test.php`

应该看到：
- PHP版本信息
- ✅ Composer autoload 存在
- ✅ SQLite3 扩展已加载

#### 2. 测试系统检查
访问：`http://medical.genoimage.cn/check.php`

#### 3. 测试系统首页
访问：`http://medical.genoimage.cn/`

## 🔧 详细问题解决

### 问题1: 404 Not Found

**原因**: 运行目录未设置为 `/public`

**解决**:
1. 宝塔面板 → 网站 → 设置 → 网站目录
2. 运行目录改为: `/public`
3. 点击保存

### 问题2: open_basedir 限制

**错误信息**:
```
Warning: require(): open_basedir restriction in effect
```

**解决**:
1. 宝塔面板 → 网站 → 设置 → 网站目录
2. 防跨站攻击 → 关闭
3. 或者添加允许路径: `/www/wwwroot/medical.genoimage.cn/`

### 问题3: Composer依赖问题

**解决**:
```bash
cd /www/wwwroot/medical.genoimage.cn
rm -rf vendor/ composer.lock
php -d memory_limit=512M /usr/local/bin/composer install --no-dev --ignore-platform-reqs
```

### 问题4: PHP内存不足

**解决**:
1. 宝塔面板 → 软件商店 → PHP 8.0 → 配置修改
2. 找到: `memory_limit = 128M`
3. 改为: `memory_limit = 512M`
4. 保存并重启PHP

## 📋 完整配置检查清单

### 宝塔面板设置
- [ ] 运行目录设置为 `/public`
- [ ] 防跨站攻击已关闭
- [ ] 伪静态规则设置为 ThinkPHP
- [ ] PHP内存限制 ≥ 512M
- [ ] PHP版本为 8.0

### 文件系统检查
- [ ] `/www/wwwroot/medical.genoimage.cn/public/index.php` 存在
- [ ] `/www/wwwroot/medical.genoimage.cn/vendor/autoload.php` 存在
- [ ] 目录权限正确 (755)
- [ ] 文件所有者为 www:www

### 功能测试
- [ ] `http://medical.genoimage.cn/test.php` 正常访问
- [ ] `http://medical.genoimage.cn/check.php` 正常访问
- [ ] `http://medical.genoimage.cn/` 显示系统首页

## 🆘 如果仍然404

### 方案1: 检查Nginx配置
```bash
# 查看Nginx配置
cat /www/server/panel/vhost/nginx/medical.genoimage.cn.conf

# 确保包含以下内容：
root /www/wwwroot/medical.genoimage.cn/public;
index index.php index.html;

location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
        break;
    }
}
```

### 方案2: 手动重启服务
```bash
# 重启Nginx
systemctl restart nginx

# 重启PHP-FPM
systemctl restart php-fpm-80
```

### 方案3: 检查域名解析
确保域名 `medical.genoimage.cn` 正确解析到服务器IP

## 📞 紧急联系

如果按照上述步骤仍无法解决，请提供：

1. 访问 `http://medical.genoimage.cn/test.php` 的结果
2. 宝塔面板错误日志截图
3. Nginx错误日志内容：
   ```bash
   tail -f /www/server/nginx/logs/error.log
   ```

## ⚡ 快速修复命令

如果您有SSH权限，可以直接运行：

```bash
# 一键修复脚本
cd /www/wwwroot/medical.genoimage.cn
wget https://raw.githubusercontent.com/nanmingfeihong/medicalsystem/feature/complete-medical-system/fix-deployment.sh
chmod +x fix-deployment.sh
./fix-deployment.sh
```

然后在宝塔面板中：
1. 设置运行目录为 `/public`
2. 关闭防跨站攻击
3. 配置ThinkPHP伪静态