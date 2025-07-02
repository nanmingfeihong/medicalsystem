<?php
/**
 * 调试上传功能
 */

require __DIR__ . '/vendor/autoload.php';

use think\App;
use think\Request;

try {
    // 初始化应用
    $app = new App();
    $app->initialize();
    
    echo "=== 调试上传功能 ===\n\n";
    
    // 测试public_path函数
    if (function_exists('public_path')) {
        echo "✓ public_path() 函数存在\n";
        echo "  路径: " . public_path() . "\n";
    } else {
        echo "✗ public_path() 函数不存在\n";
        echo "  使用替代方案: " . __DIR__ . '/public/' . "\n";
    }
    
    // 测试上传目录
    $uploadPath = __DIR__ . '/public/uploads/prescriptions/';
    echo "\n上传目录: " . $uploadPath . "\n";
    echo "目录存在: " . (is_dir($uploadPath) ? '是' : '否') . "\n";
    echo "目录可写: " . (is_writable(dirname($uploadPath)) ? '是' : '否') . "\n";
    
    // 测试文件上传模拟
    $testFile = __DIR__ . '/test_prescription.png';
    if (file_exists($testFile)) {
        echo "\n测试文件存在: " . $testFile . "\n";
        echo "文件大小: " . filesize($testFile) . " bytes\n";
        
        // 模拟文件移动
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
            echo "✓ 创建上传目录成功\n";
        }
        
        $fileName = date('YmdHis') . '_test.png';
        $targetPath = $uploadPath . $fileName;
        
        if (copy($testFile, $targetPath)) {
            echo "✓ 文件复制成功: " . $fileName . "\n";
            echo "  目标路径: " . $targetPath . "\n";
        } else {
            echo "✗ 文件复制失败\n";
        }
    }
    
    echo "\n=== 调试完成 ===\n";
    
} catch (Exception $e) {
    echo "调试失败: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
}