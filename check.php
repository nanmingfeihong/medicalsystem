<?php
/**
 * 医疗系统部署检查脚本
 * 访问: http://your-domain.com/check.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>医疗系统部署检查</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; color: #333; border-bottom: 2px solid #007cba; padding-bottom: 10px; margin-bottom: 20px; }
        .check-item { margin: 10px 0; padding: 10px; border-radius: 4px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .status { font-weight: bold; }
        .details { margin-top: 5px; font-size: 0.9em; }
        .section { margin: 20px 0; }
        .section h3 { color: #007cba; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 医疗系统部署检查</h1>
            <p>检查时间: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <?php
        $checks = [];
        $errors = 0;
        $warnings = 0;

        // 检查PHP版本
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.0.0', '>=')) {
            $checks[] = ['type' => 'success', 'title' => 'PHP版本检查', 'message' => "PHP版本: {$phpVersion} ✓", 'details' => '满足最低要求 PHP 8.0+'];
        } else {
            $checks[] = ['type' => 'error', 'title' => 'PHP版本检查', 'message' => "PHP版本过低: {$phpVersion} ✗", 'details' => '需要 PHP 8.0 或更高版本'];
            $errors++;
        }

        // 检查必需扩展
        $requiredExtensions = ['sqlite3', 'pdo_sqlite', 'curl', 'mbstring', 'xml', 'fileinfo', 'openssl', 'json'];
        $missingExtensions = [];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }

        if (empty($missingExtensions)) {
            $checks[] = ['type' => 'success', 'title' => 'PHP扩展检查', 'message' => '所有必需扩展已安装 ✓', 'details' => implode(', ', $requiredExtensions)];
        } else {
            $checks[] = ['type' => 'error', 'title' => 'PHP扩展检查', 'message' => '缺少扩展: ' . implode(', ', $missingExtensions) . ' ✗', 'details' => '请在宝塔面板中安装缺少的扩展'];
            $errors++;
        }

        // 检查目录权限
        $directories = [
            'runtime' => '运行时缓存目录',
            'public/storage' => '文件存储目录',
            'public/uploads' => '文件上传目录',
            'database' => '数据库目录'
        ];

        $permissionIssues = [];
        foreach ($directories as $dir => $desc) {
            if (!is_dir($dir)) {
                $permissionIssues[] = "{$desc}({$dir}) 不存在";
            } elseif (!is_writable($dir)) {
                $permissionIssues[] = "{$desc}({$dir}) 不可写";
            }
        }

        if (empty($permissionIssues)) {
            $checks[] = ['type' => 'success', 'title' => '目录权限检查', 'message' => '所有目录权限正常 ✓', 'details' => '已检查: ' . implode(', ', array_keys($directories))];
        } else {
            $checks[] = ['type' => 'error', 'title' => '目录权限检查', 'message' => '权限问题: ' . implode('; ', $permissionIssues) . ' ✗', 'details' => '请设置目录权限为755'];
            $errors++;
        }

        // 检查Composer依赖
        if (file_exists('vendor/autoload.php')) {
            $checks[] = ['type' => 'success', 'title' => 'Composer依赖', 'message' => 'Composer依赖已安装 ✓', 'details' => 'vendor/autoload.php 存在'];
        } else {
            $checks[] = ['type' => 'error', 'title' => 'Composer依赖', 'message' => 'Composer依赖未安装 ✗', 'details' => '请运行: composer install'];
            $errors++;
        }

        // 检查数据库
        $dbPath = 'database/medical.db';
        if (file_exists($dbPath)) {
            if (is_readable($dbPath) && is_writable($dbPath)) {
                $checks[] = ['type' => 'success', 'title' => '数据库文件', 'message' => '数据库文件正常 ✓', 'details' => "文件位置: {$dbPath}"];
            } else {
                $checks[] = ['type' => 'warning', 'title' => '数据库文件', 'message' => '数据库文件权限异常 ⚠', 'details' => '请检查文件读写权限'];
                $warnings++;
            }
        } else {
            $checks[] = ['type' => 'warning', 'title' => '数据库文件', 'message' => '数据库文件不存在 ⚠', 'details' => '将在首次访问时自动创建'];
            $warnings++;
        }

        // 检查配置文件
        $configFiles = [
            'config/app.php' => '应用配置',
            'config/database.php' => '数据库配置',
            'config/qwen.php' => 'AI配置'
        ];

        $configIssues = [];
        foreach ($configFiles as $file => $desc) {
            if (!file_exists($file)) {
                $configIssues[] = "{$desc}({$file})";
            }
        }

        if (empty($configIssues)) {
            $checks[] = ['type' => 'success', 'title' => '配置文件检查', 'message' => '所有配置文件存在 ✓', 'details' => implode(', ', array_keys($configFiles))];
        } else {
            $checks[] = ['type' => 'error', 'title' => '配置文件检查', 'message' => '缺少配置文件: ' . implode(', ', $configIssues) . ' ✗', 'details' => '请检查文件是否存在'];
            $errors++;
        }

        // 检查URL重写
        $rewriteTest = false;
        if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'index.php') === false) {
            $rewriteTest = true;
        }

        if ($rewriteTest || file_exists('public/.htaccess')) {
            $checks[] = ['type' => 'success', 'title' => 'URL重写', 'message' => 'URL重写配置正常 ✓', 'details' => '支持友好URL访问'];
        } else {
            $checks[] = ['type' => 'warning', 'title' => 'URL重写', 'message' => 'URL重写可能未配置 ⚠', 'details' => '请在宝塔面板中配置伪静态规则'];
            $warnings++;
        }

        // 检查上传配置
        $uploadMaxSize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $memoryLimit = ini_get('memory_limit');

        $uploadSizeBytes = return_bytes($uploadMaxSize);
        if ($uploadSizeBytes >= 20 * 1024 * 1024) { // 20MB
            $checks[] = ['type' => 'success', 'title' => '上传配置', 'message' => "上传限制: {$uploadMaxSize} ✓", 'details' => "POST限制: {$postMaxSize}, 内存限制: {$memoryLimit}"];
        } else {
            $checks[] = ['type' => 'warning', 'title' => '上传配置', 'message' => "上传限制较小: {$uploadMaxSize} ⚠", 'details' => '建议设置为20M或更大'];
            $warnings++;
        }

        // 功能测试
        $functionTests = [
            '首页' => '/',
            '处方上传' => '/prescription/upload',
            '肠癌筛查' => '/colon_screening/',
            '饮食分析' => '/meal/upload',
            '提醒列表' => '/reminder/list'
        ];

        function return_bytes($val) {
            $val = trim($val);
            $last = strtolower($val[strlen($val)-1]);
            $val = (int)$val;
            switch($last) {
                case 'g': $val *= 1024;
                case 'm': $val *= 1024;
                case 'k': $val *= 1024;
            }
            return $val;
        }
        ?>

        <div class="section">
            <h3>📋 检查结果</h3>
            <?php foreach ($checks as $check): ?>
                <div class="check-item <?php echo $check['type']; ?>">
                    <div class="status"><?php echo $check['title']; ?>: <?php echo $check['message']; ?></div>
                    <div class="details"><?php echo $check['details']; ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section">
            <h3>🔗 功能测试链接</h3>
            <div class="info check-item">
                <div class="status">请点击以下链接测试各功能模块:</div>
                <div class="details">
                    <?php foreach ($functionTests as $name => $url): ?>
                        <a href="<?php echo $url; ?>" target="_blank" style="margin-right: 15px;"><?php echo $name; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="section">
            <h3>📊 系统信息</h3>
            <div class="info check-item">
                <div class="status">服务器环境信息:</div>
                <div class="details">
                    <strong>PHP版本:</strong> <?php echo PHP_VERSION; ?><br>
                    <strong>服务器软件:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
                    <strong>操作系统:</strong> <?php echo PHP_OS; ?><br>
                    <strong>内存限制:</strong> <?php echo ini_get('memory_limit'); ?><br>
                    <strong>执行时间限制:</strong> <?php echo ini_get('max_execution_time'); ?>秒<br>
                    <strong>当前时间:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                </div>
            </div>
        </div>

        <div class="section">
            <h3>📈 总体状态</h3>
            <?php if ($errors == 0 && $warnings == 0): ?>
                <div class="success check-item">
                    <div class="status">🎉 系统部署完美！所有检查项目都通过了。</div>
                    <div class="details">您的医疗系统已经准备就绪，可以正常使用了。</div>
                </div>
            <?php elseif ($errors == 0): ?>
                <div class="warning check-item">
                    <div class="status">⚠️ 系统基本正常，但有 <?php echo $warnings; ?> 个警告项目。</div>
                    <div class="details">系统可以运行，但建议解决警告项目以获得更好的性能。</div>
                </div>
            <?php else: ?>
                <div class="error check-item">
                    <div class="status">❌ 发现 <?php echo $errors; ?> 个错误和 <?php echo $warnings; ?> 个警告。</div>
                    <div class="details">请先解决错误项目，然后重新检查。</div>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>🏥 医疗系统 v1.0 | 部署检查工具</p>
            <p><strong>注意:</strong> 检查完成后请删除此文件 (check.php) 以确保安全</p>
        </div>
    </div>
</body>
</html>