<?php
/**
 * 数据库初始化脚本
 */

require __DIR__ . '/vendor/autoload.php';

use think\facade\Db;

try {
    // 确保runtime目录存在
    $runtimeDir = __DIR__ . '/runtime';
    if (!is_dir($runtimeDir)) {
        mkdir($runtimeDir, 0755, true);
    }
    
    // 读取SQL文件
    $sqlFile = __DIR__ . '/database/migrations/create_tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception('SQL文件不存在: ' . $sqlFile);
    }
    
    $sql = file_get_contents($sqlFile);
    
    // 分割SQL语句
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "开始初始化数据库...\n";
    
    // 执行SQL语句
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        try {
            Db::execute($statement);
            echo "✓ 执行成功: " . substr($statement, 0, 50) . "...\n";
        } catch (Exception $e) {
            echo "✗ 执行失败: " . $e->getMessage() . "\n";
            echo "SQL: " . $statement . "\n";
        }
    }
    
    echo "\n数据库初始化完成！\n";
    echo "数据库文件位置: " . realpath(__DIR__ . '/runtime/medical_system.db') . "\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    exit(1);
}