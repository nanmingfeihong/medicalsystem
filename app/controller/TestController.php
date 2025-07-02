<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\model\Prescription;
use think\Request;

/**
 * 测试控制器
 */
class TestController extends BaseController
{
    /**
     * 测试基本功能
     */
    public function index()
    {
        try {
            return json([
                'code' => 200,
                'message' => '测试成功',
                'data' => [
                    'time' => date('Y-m-d H:i:s'),
                    'php_version' => PHP_VERSION,
                    'thinkphp_version' => \think\App::VERSION
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'message' => '测试失败: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
    
    /**
     * 测试数据库连接
     */
    public function database()
    {
        try {
            $count = Prescription::count();
            return json([
                'code' => 200,
                'message' => '数据库连接成功',
                'data' => [
                    'prescription_count' => $count
                ]
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'message' => '数据库连接失败: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
    
    /**
     * 测试处方查询
     */
    public function prescription()
    {
        try {
            $prescriptions = Prescription::limit(5)->select();
            $data = [];
            foreach ($prescriptions as $prescription) {
                $data[] = [
                    'id' => $prescription->id,
                    'user_id' => $prescription->user_id,
                    'status' => $prescription->status,
                    'create_time' => $prescription->create_time
                ];
            }
            
            return json([
                'code' => 200,
                'message' => '查询成功',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'message' => '查询失败: ' . $e->getMessage(),
                'data' => null
            ]);
        }
    }
}