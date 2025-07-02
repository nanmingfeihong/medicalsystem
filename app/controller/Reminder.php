<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\model\Reminder as ReminderModel;
use app\model\Prescription as PrescriptionModel;
use app\service\ReminderService;
use think\Request;
use think\facade\Log;

/**
 * 提醒控制器
 */
class Reminder extends BaseController
{
    /**
     * 显示提醒列表页面
     */
    public function index()
    {
        return view('reminder/list');
    }
    
    /**
     * 获取用户的提醒列表
     */
    public function list(Request $request)
    {
        $userId = $request->param('user_id', 1);
        $page = $request->param('page', 1);
        $limit = $request->param('limit', 10);
        $type = $request->param('type', ''); // 提醒类型筛选
        
        try {
            $query = ReminderModel::where('user_id', $userId);
            
            // 类型筛选
            if (!empty($type)) {
                $query->where('reminder_type', $type);
            }
            
            $reminders = $query->with(['prescription'])
                ->order('reminder_time', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page' => $page
                ]);
            
            return json([
                'code' => 1,
                'message' => '获取成功',
                'data' => $reminders->items(),
                'total' => $reminders->total(),
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($reminders->total() / $limit)
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取提醒列表失败: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取提醒列表失败', 'data' => null]);
        }
    }
    
    /**
     * 创建提醒
     */
    public function create(Request $request)
    {
        try {
            $data = $request->only([
                'user_id', 'prescription_id', 'reminder_type', 
                'reminder_time', 'title', 'content', 'send_method'
            ]);
            
            // 验证必填字段
            if (empty($data['user_id']) || empty($data['reminder_type']) || empty($data['reminder_time'])) {
                return json(['code' => 400, 'message' => '缺少必填参数']);
            }
            
            // 设置默认值
            $data['status'] = ReminderModel::STATUS_PENDING;
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = date('Y-m-d H:i:s');
            
            $reminder = ReminderModel::create($data);
            
            return json([
                'code' => 200,
                'message' => '提醒创建成功',
                'data' => $reminder
            ]);
            
        } catch (\Exception $e) {
            Log::error('创建提醒失败: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '创建提醒失败']);
        }
    }
    
    /**
     * 根据处方自动创建提醒
     */
    public function createFromPrescription(Request $request)
    {
        $prescriptionId = $request->param('prescription_id');
        
        try {
            $prescription = PrescriptionModel::find($prescriptionId);
            if (!$prescription) {
                return json(['code' => 404, 'message' => '处方不存在']);
            }
            
            $reminderService = new ReminderService();
            $result = $reminderService->createRemindersFromPrescription($prescription);
            
            return json([
                'code' => 200,
                'message' => '提醒创建成功',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('从处方创建提醒失败: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '创建提醒失败']);
        }
    }
    
    /**
     * 发送提醒
     */
    public function send(Request $request)
    {
        $reminderId = $request->param('id');
        
        try {
            $reminder = ReminderModel::find($reminderId);
            if (!$reminder) {
                return json(['code' => 404, 'message' => '提醒不存在']);
            }
            
            $reminderService = new ReminderService();
            $result = $reminderService->sendReminder($reminder);
            
            if ($result) {
                return json(['code' => 200, 'message' => '提醒发送成功']);
            } else {
                return json(['code' => 500, 'message' => '提醒发送失败']);
            }
            
        } catch (\Exception $e) {
            Log::error('发送提醒失败: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '发送提醒失败']);
        }
    }
    
    /**
     * 检查并发送到期提醒
     */
    public function checkAndSend()
    {
        try {
            $reminderService = new ReminderService();
            $result = $reminderService->checkAndSendDueReminders();
            
            return json([
                'code' => 200,
                'message' => '检查完成',
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            Log::error('检查提醒失败: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '检查提醒失败']);
        }
    }
    
    /**
     * 标记提醒为已读
     */
    public function markAsRead(Request $request)
    {
        $reminderId = $request->param('id');
        
        try {
            $reminder = ReminderModel::find($reminderId);
            if (!$reminder) {
                return json(['code' => 404, 'message' => '提醒不存在']);
            }
            
            $reminder->status = ReminderModel::STATUS_SENT;
            $reminder->sent_at = date('Y-m-d H:i:s');
            $reminder->update_time = date('Y-m-d H:i:s');
            $reminder->save();
            
            return json(['code' => 200, 'message' => '标记成功']);
            
        } catch (\Exception $e) {
            Log::error('标记提醒失败: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '标记失败']);
        }
    }
}