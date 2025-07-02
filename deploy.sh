#!/bin/bash

# 医疗系统宝塔部署脚本
# 使用方法: bash deploy.sh

echo "🏥 开始部署医疗系统..."

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 检查是否为root用户
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}请使用root用户运行此脚本${NC}"
    exit 1
fi

# 获取网站根目录
read -p "请输入网站根目录路径 (例如: /www/wwwroot/medical.example.com): " SITE_PATH

if [ ! -d "$SITE_PATH" ]; then
    echo -e "${RED}目录不存在: $SITE_PATH${NC}"
    exit 1
fi

cd "$SITE_PATH"

echo -e "${YELLOW}1. 检查PHP版本...${NC}"
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
if [ "$(echo "$PHP_VERSION >= 8.0" | bc)" -eq 1 ]; then
    echo -e "${GREEN}✓ PHP版本: $PHP_VERSION${NC}"
else
    echo -e "${RED}✗ PHP版本过低: $PHP_VERSION，需要8.0或更高版本${NC}"
    exit 1
fi

echo -e "${YELLOW}2. 检查必需的PHP扩展...${NC}"
REQUIRED_EXTENSIONS=("sqlite3" "curl" "mbstring" "xml" "fileinfo" "openssl")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -q "$ext"; then
        echo -e "${GREEN}✓ $ext 扩展已安装${NC}"
    else
        echo -e "${RED}✗ $ext 扩展未安装${NC}"
        echo "请在宝塔面板中安装 $ext 扩展"
        exit 1
    fi
done

echo -e "${YELLOW}3. 检查Composer...${NC}"
if command -v composer &> /dev/null; then
    echo -e "${GREEN}✓ Composer已安装${NC}"
else
    echo -e "${YELLOW}正在安装Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    echo -e "${GREEN}✓ Composer安装完成${NC}"
fi

echo -e "${YELLOW}4. 安装项目依赖...${NC}"
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader
    echo -e "${GREEN}✓ 依赖安装完成${NC}"
else
    echo -e "${RED}✗ composer.json文件不存在${NC}"
    exit 1
fi

echo -e "${YELLOW}5. 设置目录权限...${NC}"
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

echo -e "${GREEN}✓ 目录权限设置完成${NC}"

echo -e "${YELLOW}6. 初始化数据库...${NC}"
if [ -f "runtime/medical_system.db" ]; then
    cp runtime/medical_system.db database/medical.db
    chmod 644 database/medical.db
    echo -e "${GREEN}✓ 数据库文件复制完成${NC}"
else
    echo -e "${YELLOW}! 数据库文件不存在，将在首次访问时自动创建${NC}"
fi

echo -e "${YELLOW}7. 生成配置文件...${NC}"

# 生成.htaccess文件
cat > public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    Options +FollowSymlinks -Multiviews
    RewriteEngine On

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>

# 安全设置
<Files ~ "\.(php|php3|php4|php5|phtml|pht)$">
    <IfModule mod_dir.c>
        DirectoryIndex disabled
    </IfModule>
</Files>

# 禁止访问敏感文件
<FilesMatch "\.(env|git|svn|htaccess|htpasswd)">
    Order Allow,Deny
    Deny from all
</FilesMatch>
EOF

# 生成Nginx配置建议
cat > nginx.conf.example << 'EOF'
# Nginx配置示例 - 请在宝塔面板中手动配置

location / {
    if (!-e $request_filename) {
        rewrite ^(.*)$ /index.php?s=/$1 last;
        break;
    }
}

# 禁止访问敏感目录
location ~ ^/(app|config|database|runtime|vendor|\.git) {
    deny all;
}

# 文件上传安全
location ~* \.(php|php3|php4|php5|phtml|pht)$ {
    if ($request_uri ~* ^/(public/storage|public/uploads)) {
        return 403;
    }
    fastcgi_pass unix:/tmp/php-cgi-80.sock;
    fastcgi_index index.php;
    include fastcgi.conf;
}
EOF

echo -e "${GREEN}✓ 配置文件生成完成${NC}"

echo -e "${YELLOW}8. 清理缓存...${NC}"
php think clear 2>/dev/null || echo "缓存清理命令执行完成"

echo -e "${GREEN}🎉 部署完成！${NC}"
echo ""
echo -e "${YELLOW}接下来请在宝塔面板中完成以下配置：${NC}"
echo ""
echo "1. 网站设置 → 网站目录 → 运行目录设置为: /public"
echo "2. 网站设置 → 伪静态 → 选择ThinkPHP或使用生成的nginx.conf.example"
echo "3. 网站设置 → SSL → 申请并开启SSL证书"
echo "4. 软件商店 → PHP设置 → 调整上传限制和内存限制"
echo "5. 计划任务 → 添加提醒任务: cd $SITE_PATH && php think reminder:send"
echo ""
echo -e "${YELLOW}测试访问：${NC}"
echo "首页: http://your-domain.com/"
echo "处方上传: http://your-domain.com/prescription/upload"
echo "肠癌筛查: http://your-domain.com/colon_screening/"
echo "饮食分析: http://your-domain.com/meal/upload"
echo ""
echo -e "${YELLOW}配置文件位置：${NC}"
echo "数据库配置: config/database.php"
echo "应用配置: config/app.php"
echo "AI配置: config/qwen.php"
echo ""
echo -e "${GREEN}部署日志已保存到: $SITE_PATH/deploy.log${NC}"

# 保存部署日志
cat > deploy.log << EOF
医疗系统部署日志
部署时间: $(date)
部署路径: $SITE_PATH
PHP版本: $PHP_VERSION
Composer版本: $(composer --version 2>/dev/null || echo "未知")

部署状态: 成功
EOF

echo -e "${YELLOW}如有问题，请查看详细部署文档: DEPLOYMENT.md${NC}"