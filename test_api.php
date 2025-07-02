<?php
/**
 * API测试脚本
 */

require __DIR__ . '/vendor/autoload.php';

use app\controller\PrescriptionController;
use app\service\ThinkAIService;
use think\App;
use think\Request;

try {
    // 初始化应用
    $app = new App();
    $app->initialize();
    
    echo "=== 医疗系统API测试 ===\n\n";
    
    // 测试ThinkAI服务
    echo "1. 测试ThinkAI服务...\n";
    $aiService = new ThinkAIService();
    
    if (!$aiService->validateConfig()) {
        echo "   ⚠️  ThinkAI API密钥未配置，将使用模拟数据\n";
    } else {
        echo "   ✓ ThinkAI API配置正常\n";
    }
    
    // 测试模拟数据提取
    echo "\n2. 测试AI信息提取（模拟数据）...\n";
    $mockImagePath = __DIR__ . '/test_prescription.jpg';
    
    // 创建一个模拟图片文件
    if (!file_exists($mockImagePath)) {
        file_put_contents($mockImagePath, 'mock image data');
    }
    
    $extractedInfo = $aiService->extractPrescriptionInfo($mockImagePath);
    echo "   ✓ AI提取结果:\n";
    echo "     - 复诊时间: " . ($extractedInfo['follow_up_date'] ?? '未识别') . "\n";
    echo "     - 用药频次: " . ($extractedInfo['medication_frequency'] ?? '未识别') . "\n";
    echo "     - 药物数量: " . count($extractedInfo['medication_details'] ?? []) . " 种\n";
    echo "     - 置信度: " . ($extractedInfo['confidence'] ?? 0) . "\n";
    
    // 测试数据库连接
    echo "\n3. 测试数据库连接...\n";
    $dbFile = __DIR__ . '/runtime/medical_system.db';
    if (file_exists($dbFile)) {
        $pdo = new PDO('sqlite:' . $dbFile);
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM user");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "   ✓ 数据库连接正常，用户数量: " . $result['count'] . "\n";
    } else {
        echo "   ✗ 数据库文件不存在\n";
    }
    
    // 测试处方模型
    echo "\n4. 测试处方模型...\n";
    try {
        $prescription = new \app\model\Prescription();
        echo "   ✓ 处方模型加载成功\n";
    } catch (Exception $e) {
        echo "   ✗ 处方模型加载失败: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== 测试完成 ===\n";
    echo "\n使用说明:\n";
    echo "1. 启动服务器: php -S localhost:8080 -t public\n";
    echo "2. 访问上传页面: http://localhost:8080/prescription\n";
    echo "3. 或使用测试页面: 在浏览器中打开 test_upload.html\n";
    echo "\nAPI接口:\n";
    echo "- POST /prescription/upload - 上传处方\n";
    echo "- GET /prescription/detail?id=1 - 获取处方详情\n";
    echo "- GET /prescription/list?user_id=1 - 获取用户处方列表\n";
    
} catch (Exception $e) {
    echo "测试失败: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
}