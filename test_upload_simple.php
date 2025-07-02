<?php
/**
 * 简单的上传测试脚本
 */

require __DIR__ . '/vendor/autoload.php';

use app\model\Prescription;
use app\service\ThinkAIService;
use think\App;

try {
    // 初始化应用
    $app = new App();
    $app->initialize();
    
    echo "=== 处方上传测试 ===\n\n";
    
    // 模拟文件上传
    $uploadPath = __DIR__ . '/public/uploads/prescriptions/';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    // 创建测试处方记录
    $prescription = Prescription::create([
        'user_id' => 1,
        'prescription_image' => 'test_prescription.png',
        'original_filename' => 'test_prescription.png',
        'file_size' => 1024,
        'status' => 0
    ]);
    
    echo "✓ 处方记录创建成功，ID: " . $prescription->id . "\n";
    
    // 测试AI服务
    $aiService = new ThinkAIService();
    $imagePath = __DIR__ . '/test_prescription.png';
    
    $extractedInfo = $aiService->extractPrescriptionInfo($imagePath);
    
    // 更新处方记录
    $prescription->save([
        'extracted_content' => json_encode($extractedInfo, JSON_UNESCAPED_UNICODE),
        'follow_up_date' => $extractedInfo['follow_up_date'] ?? null,
        'medication_frequency' => $extractedInfo['medication_frequency'] ?? '',
        'medication_details' => json_encode($extractedInfo['medication_details'] ?? [], JSON_UNESCAPED_UNICODE),
        'status' => 1
    ]);
    
    echo "✓ AI信息提取成功\n";
    echo "  - 复诊时间: " . ($extractedInfo['follow_up_date'] ?? '未识别') . "\n";
    echo "  - 用药频次: " . ($extractedInfo['medication_frequency'] ?? '未识别') . "\n";
    echo "  - 药物数量: " . count($extractedInfo['medication_details'] ?? []) . " 种\n";
    
    // 查询处方详情
    $detail = Prescription::find($prescription->id);
    echo "\n✓ 处方详情查询成功\n";
    echo "  - 状态: " . $detail->status_text . "\n";
    echo "  - 创建时间: " . $detail->created_at . "\n";
    
    echo "\n=== 测试完成 ===\n";
    
} catch (Exception $e) {
    echo "测试失败: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
}