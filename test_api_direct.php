<?php
/**
 * 直接测试API端点
 */

echo "=== 直接API测试 ===\n\n";

// 测试服务器是否运行
$serverUrl = 'http://localhost:8080';

echo "1. 测试服务器连接...\n";
$ch = curl_init($serverUrl . '/prescription');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "   ✓ 服务器连接正常\n";
} else {
    echo "   ✗ 服务器连接失败，HTTP状态码: " . $httpCode . "\n";
    exit(1);
}

echo "\n2. 测试处方详情API...\n";
$ch = curl_init($serverUrl . '/prescription/detail?id=2');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP状态码: " . $httpCode . "\n";
echo "   响应内容: " . $response . "\n";

echo "\n3. 测试处方列表API...\n";
$ch = curl_init($serverUrl . '/prescription/list?user_id=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP状态码: " . $httpCode . "\n";
echo "   响应内容: " . $response . "\n";

echo "\n4. 测试文件上传API...\n";
$testFile = __DIR__ . '/test_prescription.png';
if (file_exists($testFile)) {
    $ch = curl_init($serverUrl . '/prescription/upload');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'user_id' => '1',
        'prescription_image' => new CURLFile($testFile, 'image/png', 'test.png')
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   HTTP状态码: " . $httpCode . "\n";
    echo "   响应内容: " . $response . "\n";
} else {
    echo "   ✗ 测试文件不存在: " . $testFile . "\n";
}

echo "\n=== 测试完成 ===\n";