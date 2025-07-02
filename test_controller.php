<?php
/**
 * 测试控制器功能
 */

require __DIR__ . '/vendor/autoload.php';

use app\controller\PrescriptionController;
use app\model\Prescription;
use think\App;
use think\Request;

try {
    // 初始化应用
    $app = new App();
    $app->initialize();
    
    echo "=== 测试控制器功能 ===\n\n";
    
    // 创建控制器实例
    $controller = new PrescriptionController();
    
    echo "1. 测试详情接口...\n";
    // 模拟请求
    $request = Request::create('http://localhost/prescription/detail?id=2', 'GET');
    $app->instance('request', $request);
    
    try {
        $response = $controller->detail($request);
        echo "   ✓ 详情接口调用成功\n";
        echo "   响应: " . $response->getContent() . "\n";
    } catch (Exception $e) {
        echo "   ✗ 详情接口调用失败: " . $e->getMessage() . "\n";
    }
    
    echo "\n2. 测试列表接口...\n";
    $request = Request::create('http://localhost/prescription/list?user_id=1', 'GET');
    $app->instance('request', $request);
    
    try {
        $response = $controller->list($request);
        echo "   ✓ 列表接口调用成功\n";
        echo "   响应: " . $response->getContent() . "\n";
    } catch (Exception $e) {
        echo "   ✗ 列表接口调用失败: " . $e->getMessage() . "\n";
    }
    
    echo "\n3. 直接查询数据库...\n";
    $prescriptions = Prescription::select();
    echo "   数据库中的处方数量: " . count($prescriptions) . "\n";
    foreach ($prescriptions as $prescription) {
        echo "   - ID: {$prescription->id}, 用户: {$prescription->user_id}, 状态: {$prescription->status_text}\n";
    }
    
    echo "\n=== 测试完成 ===\n";
    
} catch (Exception $e) {
    echo "测试失败: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
}