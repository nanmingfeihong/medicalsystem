<?php
declare(strict_types=1);

namespace app\controller;

use app\BaseController;
use app\service\QwenService;
use think\Request;
use think\facade\View;
use think\facade\Log;

/**
 * 饮食分析控制器
 */
class Meal extends BaseController
{
    protected $qwenService;
    
    public function __construct()
    {
        $this->qwenService = new QwenService();
    }
    
    /**
     * 显示饮食上传页面
     */
    public function upload()
    {
        return View::fetch('meal/upload');
    }
    
    /**
     * 处理饮食图片上传和分析
     */
    public function analyze(Request $request)
    {
        try {
            $file = $request->file('meal_image');
            
            if (!$file) {
                return json(['code' => 0, 'message' => '请选择要上传的图片']);
            }
            
            // 保存上传的文件
            $uploadPath = public_path() . 'storage/meals/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            $filename = date('YmdHis') . '_' . uniqid() . '.' . $file->getOriginalExtension();
            $filepath = $uploadPath . $filename;
            $file->move($uploadPath, $filename);
            
            // 使用AI分析食物
            $analysisResult = $this->qwenService->analyzeFoodImage($filepath);
            
            if ($analysisResult['success']) {
                $data = $analysisResult['data'];
                
                // 格式化分析结果
                $analysis = [
                    'food_items' => $this->formatFoodItems($data['foods'] ?? []),
                    'total_calories' => $data['total_nutrition']['calories'] ?? 0,
                    'nutrition' => $data['total_nutrition'] ?? [],
                    'health_score' => $data['health_score'] ?? 0,
                    'suggestions' => $data['suggestions'] ?? []
                ];
                
                return json([
                    'code' => 1,
                    'message' => '分析完成',
                    'data' => [
                        'image_path' => '/storage/meals/' . $filename,
                        'analysis' => $analysis
                    ]
                ]);
            } else {
                return json(['code' => 0, 'message' => '分析失败，请重试']);
            }
            
        } catch (\Exception $e) {
            Log::error('饮食分析失败: ' . $e->getMessage());
            return json(['code' => 0, 'message' => '分析失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 格式化食物列表
     */
    private function formatFoodItems($foods)
    {
        if (empty($foods)) {
            return '未识别到具体食物';
        }
        
        $items = [];
        foreach ($foods as $food) {
            $name = $food['name'] ?? '未知食物';
            $weight = $food['weight_grams'] ?? 0;
            $calories = $food['calories'] ?? 0;
            
            $items[] = "{$name}（约{$weight}g，{$calories}千卡）";
        }
        
        return implode('、', $items);
    }
}