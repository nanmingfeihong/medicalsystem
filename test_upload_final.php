<?php
/**
 * 最终上传测试
 */

require __DIR__ . '/vendor/autoload.php';

use app\model\Prescription;
use app\service\ThinkAIService;
use think\App;

try {
    // 初始化应用
    $app = new App();
    $app->initialize();
    
    echo "=== 最终上传测试 ===\n\n";
    
    // 模拟上传参数
    $userId = 1;
    $testFile = __DIR__ . '/test_prescription.png';
    
    echo "1. 验证文件...\n";
    if (!file_exists($testFile)) {
        throw new Exception("测试文件不存在: " . $testFile);
    }
    echo "   ✓ 文件存在\n";
    
    echo "\n2. 处理文件上传...\n";
    $uploadPath = __DIR__ . '/public/uploads/prescriptions/';
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    $fileName = date('YmdHis') . '_final_test.png';
    $targetPath = $uploadPath . $fileName;
    
    if (copy($testFile, $targetPath)) {
        echo "   ✓ 文件上传成功: " . $fileName . "\n";
    } else {
        throw new Exception("文件上传失败");
    }
    
    echo "\n3. 创建处方记录...\n";
    $now = date('Y-m-d H:i:s');
    $prescription = Prescription::create([
        'user_id' => $userId,
        'prescription_image' => $fileName,
        'original_filename' => 'test_prescription.png',
        'file_size' => filesize($testFile),
        'status' => Prescription::STATUS_PENDING,
        'create_time' => $now,
        'update_time' => $now
    ]);
    echo "   ✓ 处方记录创建成功，ID: " . $prescription->id . "\n";
    
    echo "\n4. 调用AI服务...\n";
    $aiService = new ThinkAIService();
    $extractedInfo = $aiService->extractPrescriptionInfo($targetPath);
    echo "   ✓ AI信息提取成功\n";
    
    echo "\n5. 更新处方记录...\n";
    $prescription->save([
        'extracted_content' => json_encode($extractedInfo, JSON_UNESCAPED_UNICODE),
        'follow_up_date' => $extractedInfo['follow_up_date'] ?? null,
        'medication_frequency' => $extractedInfo['medication_frequency'] ?? '',
        'medication_details' => json_encode($extractedInfo['medication_details'] ?? [], JSON_UNESCAPED_UNICODE),
        'status' => Prescription::STATUS_PROCESSED,
        'update_time' => date('Y-m-d H:i:s')
    ]);
    echo "   ✓ 处方记录更新成功\n";
    
    echo "\n6. 验证结果...\n";
    $result = Prescription::find($prescription->id);
    echo "   ✓ 处方ID: " . $result->id . "\n";
    echo "   ✓ 状态: " . $result->status_text . "\n";
    echo "   ✓ 创建时间: " . $result->create_time . "\n";
    echo "   ✓ 更新时间: " . $result->update_time . "\n";
    
    echo "\n=== 测试成功 ===\n";
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
    echo "测试失败: " . $e->getMessage() . "\n";
    echo "错误详情: " . $e->getTraceAsString() . "\n";
}