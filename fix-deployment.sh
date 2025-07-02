#!/bin/bash

# 医疗系统部署问题修复脚本
# 针对 medical.genoimage.cn 的具体问题

echo "🔧 开始修复部署问题..."

SITE_PATH="/www/wwwroot/medical.genoimage.cn"

# 检查目录是否存在
if [ ! -d "$SITE_PATH" ]; then
    echo "❌ 网站目录不存在: $SITE_PATH"
    exit 1
fi

cd "$SITE_PATH"

echo "📍 当前目录: $(pwd)"

# 1. 修复PHP配置文件语法错误
echo "🔧 1. 修复PHP配置文件..."
PHP_INI="/www/server/php/80/etc/php-cli.ini"
if [ -f "$PHP_INI" ]; then
    # 备份原文件
    cp "$PHP_INI" "${PHP_INI}.backup.$(date +%Y%m%d_%H%M%S)"
    
    # 修复第1953行的语法错误（移除多余的=号）
    sed -i '1953s/==/=/g' "$PHP_INI"
    echo "✅ PHP配置文件已修复"
else
    echo "⚠️ PHP配置文件不存在，跳过修复"
fi

# 2. 修复内存限制问题
echo "🔧 2. 修复PHP内存限制..."
# 临时设置环境变量
export PHP_MEMORY_LIMIT=512M

# 修改php.ini文件
PHP_FPM_INI="/www/server/php/80/etc/php.ini"
if [ -f "$PHP_FPM_INI" ]; then
    sed -i 's/memory_limit = .*/memory_limit = 512M/' "$PHP_FPM_INI"
    echo "✅ PHP内存限制已调整为512M"
fi

# 3. 解决open_basedir限制问题
echo "🔧 3. 修复open_basedir限制..."
# 这个需要在宝塔面板中手动关闭防跨站攻击，这里先创建提示文件
cat > fix_open_basedir.txt << EOF
请在宝塔面板中执行以下操作：
1. 网站 → 设置 → 网站目录 → 防跨站攻击 → 关闭
2. 或者在防跨站攻击中添加允许路径：$SITE_PATH
EOF

# 4. 清理并重新安装Composer依赖
echo "🔧 4. 重新安装Composer依赖..."
rm -rf vendor/ composer.lock

# 使用更大的内存限制安装依赖
php -d memory_limit=512M /usr/local/bin/composer install --no-dev --optimize-autoloader --ignore-platform-reqs

if [ $? -eq 0 ]; then
    echo "✅ Composer依赖安装成功"
else
    echo "❌ Composer依赖安装失败，尝试备用方案..."
    # 备用方案：不检查平台要求
    php -d memory_limit=512M /usr/local/bin/composer install --no-dev --no-scripts --ignore-platform-reqs
fi

# 5. 设置正确的目录权限
echo "🔧 5. 设置目录权限..."
# 创建必要目录
mkdir -p runtime/log runtime/cache runtime/temp
mkdir -p public/storage/prescriptions public/storage/meals
mkdir -p public/uploads/prescriptions
mkdir -p database

# 设置权限
chmod -R 755 runtime/
chmod -R 755 public/storage/
chmod -R 755 public/uploads/
chmod -R 755 database/
chmod 644 database/*.db 2>/dev/null || true

# 设置所有者
chown -R www:www "$SITE_PATH"

echo "✅ 目录权限设置完成"

# 6. 复制数据库文件
echo "🔧 6. 初始化数据库..."
if [ -f "runtime/medical_system.db" ]; then
    cp runtime/medical_system.db database/medical.db
    chmod 644 database/medical.db
    chown www:www database/medical.db
    echo "✅ 数据库文件复制完成"
else
    echo "⚠️ 数据库文件不存在，将在首次访问时创建"
fi

# 7. 生成.htaccess文件
echo "🔧 7. 生成.htaccess文件..."
cat > public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    Options +FollowSymlinks -Multiviews
    RewriteEngine On
    
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>

# 安全设置
<Files ~ "\.(env|git|svn)">
    Order Allow,Deny
    Deny from all
</Files>
EOF

echo "✅ .htaccess文件生成完成"

# 8. 检查关键文件
echo "🔧 8. 检查关键文件..."
CRITICAL_FILES=(
    "public/index.php"
    "vendor/autoload.php"
    "config/app.php"
    "config/database.php"
)

for file in "${CRITICAL_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file 存在"
    else
        echo "❌ $file 不存在"
    fi
done

# 9. 重启PHP-FPM
echo "🔧 9. 重启PHP-FPM..."
systemctl restart php-fpm-80
echo "✅ PHP-FPM已重启"

# 10. 生成测试页面
echo "🔧 10. 生成测试页面..."
cat > public/test.php << 'EOF'
<?php
echo "PHP版本: " . PHP_VERSION . "<br>";
echo "当前时间: " . date('Y-m-d H:i:s') . "<br>";
echo "内存限制: " . ini_get('memory_limit') . "<br>";

if (file_exists('../vendor/autoload.php')) {
    echo "✅ Composer autoload 存在<br>";
} else {
    echo "❌ Composer autoload 不存在<br>";
}

if (extension_loaded('sqlite3')) {
    echo "✅ SQLite3 扩展已加载<br>";
} else {
    echo "❌ SQLite3 扩展未加载<br>";
}

echo "网站根目录: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "当前脚本: " . __FILE__ . "<br>";
?>
EOF

echo "✅ 测试页面已生成: http://medical.genoimage.cn/test.php"

echo ""
echo "🎉 修复完成！"
echo ""
echo "📋 接下来请在宝塔面板中完成以下配置："
echo ""
echo "1. 网站设置 → 网站目录 → 运行目录设置为: /public"
echo "2. 网站设置 → 网站目录 → 防跨站攻击 → 关闭"
echo "3. 网站设置 → 伪静态 → 选择 ThinkPHP"
echo "4. 软件商店 → PHP 8.0 → 配置修改 → memory_limit = 512M"
echo ""
echo "🌐 测试访问："
echo "- 测试页面: http://medical.genoimage.cn/test.php"
echo "- 系统首页: http://medical.genoimage.cn/"
echo "- 检查页面: http://medical.genoimage.cn/check.php"
echo ""
echo "⚠️ 重要提示："
echo "- 请务必在宝塔面板中关闭防跨站攻击"
echo "- 确保运行目录设置为 /public"
echo "- 如果仍有问题，请查看 fix_open_basedir.txt 文件"