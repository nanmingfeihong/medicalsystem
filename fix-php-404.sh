#!/bin/bash

# 修复PHP文件404问题的专用脚本
# 针对 medical.genoimage.cn 的PHP处理问题

echo "🔧 修复PHP文件404问题..."

SITE_PATH="/www/wwwroot/medical.genoimage.cn"
NGINX_CONF="/www/server/panel/vhost/nginx/medical.genoimage.cn.conf"

echo "📍 网站路径: $SITE_PATH"
echo "📍 Nginx配置: $NGINX_CONF"

# 1. 检查文件是否存在
echo "🔍 1. 检查关键文件..."
if [ -f "$SITE_PATH/public/index.php" ]; then
    echo "✅ index.php 存在"
else
    echo "❌ index.php 不存在"
fi

if [ -f "$SITE_PATH/public/test.php" ]; then
    echo "✅ test.php 存在"
else
    echo "❌ test.php 不存在"
fi

# 2. 检查当前Nginx配置
echo "🔍 2. 检查当前Nginx配置..."
if [ -f "$NGINX_CONF" ]; then
    echo "当前Nginx配置内容："
    cat "$NGINX_CONF"
    echo "================================"
else
    echo "❌ Nginx配置文件不存在"
fi

# 3. 备份原配置
echo "🔧 3. 备份原配置..."
if [ -f "$NGINX_CONF" ]; then
    cp "$NGINX_CONF" "${NGINX_CONF}.backup.$(date +%Y%m%d_%H%M%S)"
    echo "✅ 配置已备份"
fi

# 4. 生成正确的Nginx配置
echo "🔧 4. 生成正确的Nginx配置..."
cat > "$NGINX_CONF" << EOF
server
{
    listen 80;
    listen 443 ssl http2;
    server_name medical.genoimage.cn;
    index index.php index.html index.htm default.php default.htm default.html;
    root /www/wwwroot/medical.genoimage.cn/public;
    
    # SSL配置（如果有SSL证书）
    #ssl_certificate    /www/server/panel/vhost/cert/medical.genoimage.cn/fullchain.pem;
    #ssl_certificate_key    /www/server/panel/vhost/cert/medical.genoimage.cn/privkey.pem;
    #ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
    #ssl_ciphers EECDH+CHACHA20:EECDH+CHACHA20-draft:EECDH+AES128:RSA+AES128:EECDH+AES256:RSA+AES256:EECDH+3DES:RSA+3DES:!MD5;
    #ssl_prefer_server_ciphers on;
    #ssl_session_cache shared:SSL:10m;
    #ssl_session_timeout 10m;
    
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
    
    # PHP处理
    location ~ \.php(.*)$
    {
        fastcgi_pass unix:/tmp/php-cgi-80.sock;
        fastcgi_index index.php;
        fastcgi_split_path_info ^((?U).+\.php)(/?.+)$;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED \$document_root\$fastcgi_path_info;
        include fastcgi_params;
    }
    
    # ThinkPHP URL重写
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
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
EOF

echo "✅ Nginx配置已更新"

# 5. 检查ThinkPHP重写规则
echo "🔧 5. 检查ThinkPHP重写规则..."
REWRITE_FILE="/www/server/nginx/conf/rewrite/thinkphp.conf"
if [ ! -f "$REWRITE_FILE" ]; then
    echo "创建ThinkPHP重写规则..."
    mkdir -p /www/server/nginx/conf/rewrite/
    cat > "$REWRITE_FILE" << 'EOF'
location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
        break;
    }
}
EOF
    echo "✅ ThinkPHP重写规则已创建"
else
    echo "✅ ThinkPHP重写规则已存在"
fi

# 6. 检查PHP-FPM配置
echo "🔧 6. 检查PHP-FPM配置..."
PHP_FPM_SOCK="/tmp/php-cgi-80.sock"
if [ -S "$PHP_FPM_SOCK" ]; then
    echo "✅ PHP-FPM socket 存在"
else
    echo "❌ PHP-FPM socket 不存在，尝试重启PHP-FPM..."
    systemctl restart php-fpm-80
    sleep 2
    if [ -S "$PHP_FPM_SOCK" ]; then
        echo "✅ PHP-FPM 重启成功"
    else
        echo "❌ PHP-FPM 重启失败"
    fi
fi

# 7. 测试Nginx配置
echo "🔧 7. 测试Nginx配置..."
nginx -t
if [ $? -eq 0 ]; then
    echo "✅ Nginx配置语法正确"
else
    echo "❌ Nginx配置语法错误"
    exit 1
fi

# 8. 重启Nginx
echo "🔧 8. 重启Nginx..."
systemctl restart nginx
if [ $? -eq 0 ]; then
    echo "✅ Nginx重启成功"
else
    echo "❌ Nginx重启失败"
    exit 1
fi

# 9. 创建简单的测试文件
echo "🔧 9. 创建测试文件..."
cat > "$SITE_PATH/public/phpinfo.php" << 'EOF'
<?php
echo "PHP版本: " . PHP_VERSION . "<br>";
echo "当前时间: " . date('Y-m-d H:i:s') . "<br>";
echo "服务器信息: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "文档根目录: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "脚本名称: " . $_SERVER['SCRIPT_NAME'] . "<br>";
echo "请求URI: " . $_SERVER['REQUEST_URI'] . "<br>";

if (function_exists('phpinfo')) {
    echo "<hr>";
    phpinfo();
} else {
    echo "phpinfo() 函数被禁用";
}
?>
EOF

# 10. 设置文件权限
echo "🔧 10. 设置文件权限..."
chown -R www:www "$SITE_PATH"
chmod 644 "$SITE_PATH/public/"*.php

echo ""
echo "🎉 修复完成！"
echo ""
echo "🌐 请测试以下URL："
echo "- 静态文件: https://medical.genoimage.cn/1.txt"
echo "- PHP信息: https://medical.genoimage.cn/phpinfo.php"
echo "- 测试页面: https://medical.genoimage.cn/test.php"
echo "- 系统首页: https://medical.genoimage.cn/index.php"
echo "- 系统首页: https://medical.genoimage.cn/"
echo ""
echo "📋 如果仍有问题，请检查："
echo "1. PHP-FPM是否正常运行: systemctl status php-fpm-80"
echo "2. Nginx错误日志: tail -f /www/wwwlogs/medical.genoimage.cn.error.log"
echo "3. PHP错误日志: tail -f /www/server/php/80/var/log/php_errors.log"
EOF