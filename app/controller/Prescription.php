<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\model\Prescription as PrescriptionModel;
use app\service\ThinkAIService;
use app\service\ReminderService;
use think\exception\ValidateException;
use think\facade\Log;
use think\Request;

/**
 * 处方控制器
 */
class Prescription extends BaseController
{
    /**
     * 显示处方上传页面
     */
    public function upload()
    {
        return view('prescription/upload');
    }
    
    /**
     * 处理处方上传
     */
    public function doUpload(Request $request)
    {
        try {
            // 验证请求参数
            $this->validateUpload($request);
            
            // 获取用户ID（这里简化处理，实际应该从session或token中获取）
            $userId = $request->param('user_id', 1);
            
            // 处理文件上传
            $file = $request->file('prescription_image');
            $uploadResult = $this->handleFileUpload($file);
            
            // 创建处方记录
            $prescription = $this->createPrescriptionRecord($userId, $uploadResult, $file);
            
            // 调用AI服务提取处方信息
            $this->extractPrescriptionInfo($prescription);
            
            // 自动创建提醒
            $this->createRemindersForPrescription($prescription);
            
            return json([
                'code' => 200,
                'message' => '处方上传成功',
                'data' => [
                    'prescription_id' => $prescription->id,
                    'status' => $prescription->status,
                    'image_url' => $prescription->image_url
                ]
            ]);
            
        } catch (ValidateException $e) {
            return json(['code' => 400, 'message' => $e->getError(), 'data' => null]);
        } catch (\Exception $e) {
            Log::error('处方上传失败: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '处方上传失败，请稍后重试', 'data' => null]);
        }
    }
    
    /**
     * 测试方法
     */
    public function test($id = null)
    {
        return json(['code' => 200, 'message' => '测试成功', 'data' => ['id' => $id]]);
    }
    
    /**
     * 显示处方列表页面
     */
    public function index()
    {
        return view('prescription/list');
    }
    
    /**
     * 显示处方详情页面
     */
    public function show($id = null)
    {
        return view('prescription/detail');
    }
    
    /**
     * 获取处方详情（API接口）
     */
    public function detail($id = null)
    {
        // 调试信息
        Log::info('获取处方详情，ID: ' . $id);
        
        if (empty($id)) {
            return json(['code' => 400, 'message' => '处方ID不能为空', 'data' => null]);
        }
        
        try {
            $prescription = PrescriptionModel::with('user')->find($id);
            
            if (!$prescription) {
                return json(['code' => 404, 'message' => '处方不存在', 'data' => null]);
            }
            
            return json([
                'code' => 200,
                'message' => '获取成功',
                'data' => [
                    'id' => $prescription->id,
                    'user_id' => $prescription->user_id,
                    'prescription_image' => $prescription->prescription_image,
                    'original_filename' => $prescription->original_filename,
                    'extracted_content' => $prescription->extracted_content,
                    'follow_up_date' => $prescription->follow_up_date,
                    'medication_frequency' => $prescription->medication_frequency,
                    'medication_details' => $prescription->medication_details,
                    'status' => $prescription->status,
                    'status_text' => $prescription->status_text,
                    'create_time' => $prescription->create_time,
                    'update_time' => $prescription->update_time,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取处方详情失败: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取处方详情失败，请稍后重试', 'data' => null]);
        }
    }
    
    /**
     * 获取用户的处方列表
     */
    public function list(Request $request)
    {
        $userId = $request->param('user_id', 1);
        $page = $request->param('page', 1);
        $limit = $request->param('limit', 10);
        
        try {
            $prescriptions = PrescriptionModel::where('user_id', $userId)
                ->order('create_time', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page' => $page
                ]);
            
            return json([
                'code' => 200,
                'message' => '获取成功',
                'data' => [
                    'list' => $prescriptions->items(),
                    'total' => $prescriptions->total(),
                    'page' => $page,
                    'limit' => $limit,
                    'pages' => ceil($prescriptions->total() / $limit)
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取处方列表失败: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取处方列表失败', 'data' => null]);
        }
    }
    
    /**
     * 验证上传请求
     */
    private function validateUpload(Request $request)
    {
        $file = $request->file('prescription_image');
        
        if (!$file) {
            throw new ValidateException('请选择要上传的处方图片');
        }
        
        // 检查文件是否有效
        if (!$file->isValid()) {
            throw new ValidateException('文件上传失败，请重试');
        }
        
        // 验证文件类型
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];
        $fileExt = strtolower($file->getOriginalExtension());
        
        if (!in_array($fileExt, $allowedTypes)) {
            throw new ValidateException('只支持上传 jpg、jpeg、png、gif、bmp 格式的图片');
        }
        
        // 验证文件大小（5MB）
        $fileSize = $file->getSize();
        if ($fileSize === false || $fileSize > 5 * 1024 * 1024) {
            throw new ValidateException('图片大小不能超过5MB');
        }
    }
    
    /**
     * 处理文件上传
     */
    private function handleFileUpload($file)
    {
        // 创建上传目录
        $uploadPath = public_path() . 'uploads/prescriptions/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        // 生成文件名
        $fileName = date('YmdHis') . '_' . uniqid() . '.' . $file->getOriginalExtension();
        
        // 获取文件大小（在移动前）
        $fileSize = $file->getSize();
        $originalName = $file->getOriginalName();
        
        // 移动文件
        $savedFile = $file->move($uploadPath, $fileName);
        
        if (!$savedFile) {
            throw new \Exception('文件保存失败');
        }
        
        return [
            'filename' => $fileName,
            'original_name' => $originalName,
            'size' => $fileSize,
            'path' => $uploadPath . $fileName
        ];
    }
    
    /**
     * 创建处方记录
     */
    private function createPrescriptionRecord($userId, $uploadResult, $file)
    {
        return PrescriptionModel::create([
            'user_id' => $userId,
            'prescription_image' => $uploadResult['filename'],
            'original_filename' => $uploadResult['original_name'],
            'file_size' => $uploadResult['size'],
            'status' => PrescriptionModel::STATUS_PENDING
        ]);
    }
    
    /**
     * 提取处方信息
     */
    private function extractPrescriptionInfo(PrescriptionModel $prescription)
    {
        try {
            // 调用ThinkAI服务
            $aiService = new ThinkAIService();
            $imagePath = public_path() . 'uploads/prescriptions/' . $prescription->prescription_image;
            
            $extractedInfo = $aiService->extractPrescriptionInfo($imagePath);
            
            // 更新处方记录
            $prescription->save([
                'extracted_content' => json_encode($extractedInfo, JSON_UNESCAPED_UNICODE),
                'follow_up_date' => $extractedInfo['follow_up_date'] ?? null,
                'medication_frequency' => $extractedInfo['medication_frequency'] ?? '',
                'medication_details' => json_encode($extractedInfo['medication_details'] ?? [], JSON_UNESCAPED_UNICODE),
                'status' => PrescriptionModel::STATUS_PROCESSED
            ]);
            
            Log::info('处方信息提取成功', ['prescription_id' => $prescription->id]);
            
        } catch (\Exception $e) {
            // 更新状态为失败
            $prescription->save([
                'status' => PrescriptionModel::STATUS_FAILED,
                'extracted_content' => json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE)
            ]);
            
            Log::error('处方信息提取失败', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 为处方创建提醒
     */
    private function createRemindersForPrescription(PrescriptionModel $prescription)
    {
        try {
            $reminderService = new ReminderService();
            $reminders = $reminderService->createRemindersFromPrescription($prescription);
            
            Log::info('处方提醒创建成功', [
                'prescription_id' => $prescription->id,
                'reminder_count' => count($reminders)
            ]);
            
        } catch (\Exception $e) {
            // 提醒创建失败不影响处方上传
            Log::error('处方提醒创建失败', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}