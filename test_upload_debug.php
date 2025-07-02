<?php
/**
 * 详细的上传调试脚本
 */

require __DIR__ . '/vendor/autoload.php';

use app\model\Prescription;
use app\service\ThinkAIService;
use think\App;
use think\exception\ValidateException;

try {
    // 初始化应用
    $app = new App();
    $app->initialize();
    
    echo "=== 详细上传调试 ===\n\n";
    
    // 模拟上传参数
    $userId = 1;
    $testFile = __DIR__ . '/test_prescription.png';
    
    echo "1. 验证测试文件...\n";
    if (!file_exists($testFile)) {
        throw new Exception("测试文件不存在: " . $testFile);
    }
    echo "   ✓ 测试文件存在，大小: " . filesize($testFile) . " bytes\n";
    
    echo "\n2. 验证上传目录...\n";
    $uploadPath = __DIR__ . '/public/uploads/prescriptions/';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
        echo "   ✓ 创建上传目录: " . $uploadPath . "\n";
    } else {
        echo "   ✓ 上传目录已存在: " . $uploadPath . "\n";
    }
    
    echo "\n3. 模拟文件上传...\n";
    $fileName = date('YmdHis') . '_debug.png';
    $targetPath = $uploadPath . $fileName;
    
    if (copy($testFile, $targetPath)) {
        echo "   ✓ 文件复制成功: " . $fileName . "\n";
    } else {
        throw new Exception("文件复制失败");
    }
    
    echo "\n4. 创建处方记录...\n";
    $prescription = Prescription::create([
        'user_id' => $userId,
        'prescription_image' => $fileName,
        'original_filename' => 'test_prescription.png',
        'file_size' => filesize($testFile),
        'status' => Prescription::STATUS_PENDING
    ]);
    echo "   ✓ 处方记录创建成功，ID: " . $prescription->id . "\n";
    
    echo "\n5. 调用AI服务...\n";
    $aiService = new ThinkAIService();
    $extractedInfo = $aiService->extractPrescriptionInfo($targetPath);
    echo "   ✓ AI信息提取成功\n";
    echo "     - 复诊时间: " . ($extractedInfo['follow_up_date'] ?? '未识别') . "\n";
    echo "     - 用药频次: " . ($extractedInfo['medication_frequency'] ?? '未识别') . "\n";
    echo "     - 药物数量: " . count($extractedInfo['medication_details'] ?? []) . " 种\n";
    
    echo "\n6. 更新处方记录...\n";
    $prescription->save([
        'extracted_content' => json_encode($extractedInfo, JSON_UNESCAPED_UNICODE),
        'follow_up_date' => $extractedInfo['follow_up_date'] ?? null,
        'medication_frequency' => $extractedInfo['medication_frequency'] ?? '',
        'medication_details' => json_encode($extractedInfo['medication_details'] ?? [], JSON_UNESCAPED_UNICODE),
        'status' => Prescription::STATUS_PROCESSED
    ]);
    echo "   ✓ 处方记录更新成功\n";
    
    echo "\n7. 验证结果...\n";
    $result = Prescription::find($prescription->id);
    echo "   ✓ 处方ID: " . $result->id . "\n";
    echo "   ✓ 状态: " . $result->status_text . "\n";
    echo "   ✓ 图片URL: " . $result->image_url . "\n";
    echo "   ✓ 创建时间: " . $result->create_time . "\n";
    
    echo "\n=== 调试成功 ===\n";
    echo "\n模拟API响应:\n";
    echo json_encode([
        'code' => 200,
        'message' => '处方上传成功',
        'data' => [
            'prescription_id' => $result->id,
            'status' => $result->status,
            'image_url' => $result->image_url
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "调试失败: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
}