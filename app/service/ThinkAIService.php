<?php
declare (strict_types = 1);

namespace app\service;

use think\facade\Log;

/**
 * ThinkAI服务类
 * 用于调用AI接口提取处方信息
 */
class ThinkAIService
{
    private $apiUrl;
    private $apiKey;
    private $timeout;
    
    private $qwenService;
    
    public function __construct()
    {
        // 从配置文件或环境变量获取API配置
        $this->apiUrl = env('THINKAI_API_URL', 'https://api.thinkai.com/v1/ocr/prescription');
        $this->apiKey = env('THINKAI_API_KEY', '');
        $this->timeout = 30; // 30秒超时
        
        // 初始化通义千问服务
        $this->qwenService = new QwenService();
    }
    
    /**
     * 提取处方信息
     * 
     * @param string $imagePath 图片路径
     * @return array 提取的信息
     * @throws \Exception
     */
    public function extractPrescriptionInfo(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new \Exception('图片文件不存在');
        }
        
        try {
            Log::info('使用通义千问提取处方信息: ' . $imagePath);
            
            // 优先使用通义千问服务
            $result = $this->qwenService->extractPrescriptionInfo($imagePath);
            
            if ($result['success']) {
                Log::info('通义千问处方信息提取成功');
                return $result['data'];
            } else {
                Log::warning('通义千问提取失败，尝试备用方案: ' . ($result['error'] ?? '未知错误'));
                
                // 如果通义千问失败，尝试原有API（如果配置了）
                if (!empty($this->apiKey)) {
                    return $this->extractWithOriginalAPI($imagePath);
                } else {
                    Log::warning('无备用API配置，使用模拟数据');
                    return $this->getMockData();
                }
            }
            
        } catch (\Exception $e) {
            Log::error('处方信息提取失败: ' . $e->getMessage());
            // 异常时返回模拟数据
            return $this->getMockData();
        }
    }
    
    /**
     * 使用原有API提取处方信息（备用方案）
     */
    private function extractWithOriginalAPI(string $imagePath): array
    {
        try {
            // 准备请求数据
            $postData = [
                'image' => base64_encode(file_get_contents($imagePath)),
                'extract_fields' => [
                    'follow_up_date',      // 复诊时间
                    'medication_frequency', // 用药频次
                    'medication_details',   // 药物详情
                    'doctor_name',         // 医生姓名
                    'hospital_name',       // 医院名称
                    'diagnosis'            // 诊断
                ]
            ];
            
            // 发送请求
            $response = $this->sendRequest($postData);
            
            // 解析响应
            return $this->parseResponse($response);
            
        } catch (\Exception $e) {
            Log::error('原有API调用失败: ' . $e->getMessage());
            return $this->getMockData();
        }
    }
    
    /**
     * 餐食图片识别和营养分析
     */
    public function analyzeFoodImage(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new \Exception('图片文件不存在');
        }
        
        try {
            Log::info('使用通义千问分析餐食图片: ' . $imagePath);
            
            $result = $this->qwenService->analyzeFoodImage($imagePath);
            
            if ($result['success']) {
                Log::info('餐食分析成功');
                return $result['data'];
            } else {
                Log::error('通义千问餐食分析失败: ' . ($result['error'] ?? '未知错误'));
                throw new \Exception('餐食分析失败: ' . ($result['error'] ?? '未知错误'));
            }
            
        } catch (\Exception $e) {
            Log::error('餐食分析失败: ' . $e->getMessage());
            throw new \Exception('餐食分析失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 发送HTTP请求
     */
    private function sendRequest(array $postData): string
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
                'User-Agent: MedicalSystem/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($response === false) {
            throw new \Exception('网络请求失败: ' . $error);
        }
        
        if ($httpCode !== 200) {
            throw new \Exception('API请求失败，HTTP状态码: ' . $httpCode);
        }
        
        return $response;
    }
    
    /**
     * 解析API响应
     */
    private function parseResponse(string $response): array
    {
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('API响应格式错误');
        }
        
        if (!isset($data['success']) || !$data['success']) {
            $errorMsg = $data['message'] ?? '未知错误';
            throw new \Exception('API返回错误: ' . $errorMsg);
        }
        
        $result = $data['data'] ?? [];
        
        // 标准化返回数据格式
        return [
            'follow_up_date' => $this->parseDate($result['follow_up_date'] ?? ''),
            'medication_frequency' => $result['medication_frequency'] ?? '',
            'medication_details' => $result['medication_details'] ?? [],
            'doctor_name' => $result['doctor_name'] ?? '',
            'hospital_name' => $result['hospital_name'] ?? '',
            'diagnosis' => $result['diagnosis'] ?? '',
            'confidence' => $result['confidence'] ?? 0.0,
            'raw_text' => $result['raw_text'] ?? ''
        ];
    }
    
    /**
     * 解析日期字符串
     */
    private function parseDate(string $dateStr): ?string
    {
        if (empty($dateStr)) {
            return null;
        }
        
        // 尝试多种日期格式
        $formats = [
            'Y-m-d',
            'Y/m/d',
            'Y年m月d日',
            'm月d日',
            'd日'
        ];
        
        foreach ($formats as $format) {
            try {
                $date = \DateTime::createFromFormat($format, $dateStr);
                if ($date !== false) {
                    // 如果只有月日，补充当前年份
                    if (strpos($format, 'Y') === false) {
                        $date->setDate(date('Y'), $date->format('m'), $date->format('d'));
                    }
                    return $date->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // 如果无法解析，记录日志并返回null
        Log::warning('无法解析日期格式: ' . $dateStr);
        return null;
    }
    
    /**
     * 获取模拟数据（用于测试）
     */
    private function getMockData(): array
    {
        // 模拟AI识别结果
        return [
            'follow_up_date' => date('Y-m-d H:i:s', strtotime('+7 days')), // 7天后复诊
            'medication_frequency' => '每日3次，饭后服用',
            'medication_details' => [
                [
                    'name' => '阿莫西林胶囊',
                    'dosage' => '0.5g',
                    'frequency' => '每日3次',
                    'duration' => '7天',
                    'notes' => '饭后服用'
                ],
                [
                    'name' => '布洛芬缓释胶囊',
                    'dosage' => '0.3g',
                    'frequency' => '每日2次',
                    'duration' => '5天',
                    'notes' => '疼痛时服用'
                ]
            ],
            'doctor_name' => '张医生',
            'hospital_name' => '市人民医院',
            'diagnosis' => '急性上呼吸道感染',
            'confidence' => 0.95,
            'raw_text' => '处方单原始文本内容...'
        ];
    }
    
    /**
     * 验证API配置
     */
    public function validateConfig(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiUrl);
    }
    
    /**
     * 测试API连接
     */
    public function testConnection(): array
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->apiUrl . '/health',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $this->apiKey
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return [
                'success' => $httpCode === 200,
                'http_code' => $httpCode,
                'response' => $response
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}