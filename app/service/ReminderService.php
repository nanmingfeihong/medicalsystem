<?php
declare (strict_types = 1);

namespace app\service;

use app\model\Reminder;
use app\model\Prescription;
use think\facade\Log;

/**
 * 提醒服务类
 */
class ReminderService
{
    /**
     * 根据处方创建提醒
     */
    public function createRemindersFromPrescription(Prescription $prescription)
    {
        $reminders = [];
        
        try {
            // 1. 创建复诊提醒
            if (!empty($prescription->follow_up_date)) {
                $followUpReminder = $this->createFollowUpReminder($prescription);
                if ($followUpReminder) {
                    $reminders[] = $followUpReminder;
                }
            }
            
            // 2. 创建用药提醒
            $medicationReminders = $this->createMedicationReminders($prescription);
            $reminders = array_merge($reminders, $medicationReminders);
            
            return $reminders;
            
        } catch (\Exception $e) {
            Log::error('从处方创建提醒失败: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 创建复诊提醒
     */
    private function createFollowUpReminder(Prescription $prescription)
    {
        try {
            // 在复诊日期前一天提醒
            $reminderTime = date('Y-m-d H:i:s', strtotime($prescription->follow_up_date . ' -1 day'));
            
            $data = [
                'user_id' => $prescription->user_id,
                'prescription_id' => $prescription->id,
                'reminder_type' => Reminder::TYPE_FOLLOW_UP,
                'reminder_time' => $reminderTime,
                'message' => "复诊提醒：您有一个复诊预约，时间：{$prescription->follow_up_date}，请及时前往医院复诊。",
                'status' => Reminder::STATUS_PENDING,
                'send_method' => Reminder::METHOD_APP,
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
            ];
            
            return Reminder::create($data);
            
        } catch (\Exception $e) {
            Log::error('创建复诊提醒失败: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 创建用药提醒
     */
    private function createMedicationReminders(Prescription $prescription)
    {
        $reminders = [];
        
        try {
            // 解析用药详情
            $medicationDetails = [];
            if (!empty($prescription->medication_details)) {
                $medicationDetails = json_decode($prescription->medication_details, true) ?: [];
            }
            
            foreach ($medicationDetails as $medication) {
                // 根据用药频次创建提醒
                $medicationReminders = $this->createMedicationRemindersByFrequency(
                    $prescription, 
                    $medication
                );
                $reminders = array_merge($reminders, $medicationReminders);
            }
            
            return $reminders;
            
        } catch (\Exception $e) {
            Log::error('创建用药提醒失败: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * 根据用药频次创建提醒
     */
    private function createMedicationRemindersByFrequency(Prescription $prescription, array $medication)
    {
        $reminders = [];
        $frequency = $medication['frequency'] ?? '';
        $duration = intval($medication['duration'] ?? 7); // 默认7天疗程
        
        // 解析频次
        $timesPerDay = $this->parseFrequency($frequency);
        
        // 设置提醒时间点（一日三次：8:00, 12:00, 18:00）
        $reminderTimes = $this->getReminderTimes($timesPerDay);
        
        // 创建疗程期间的提醒
        for ($day = 0; $day < $duration; $day++) {
            $date = date('Y-m-d', strtotime("+{$day} days"));
            
            foreach ($reminderTimes as $time) {
                $reminderDateTime = $date . ' ' . $time;
                
                $medicationName = $medication['name'] ?? '药物';
                $medicationDosage = $medication['dosage'] ?? '';
                $medicationNotes = $medication['notes'] ?? '';
                
                $message = "用药提醒：请按时服用 {$medicationName}";
                if ($medicationDosage) {
                    $message .= " {$medicationDosage}";
                }
                if ($medicationNotes) {
                    $message .= "，{$medicationNotes}";
                }
                
                $data = [
                    'user_id' => $prescription->user_id,
                    'prescription_id' => $prescription->id,
                    'reminder_type' => Reminder::TYPE_MEDICATION,
                    'reminder_time' => $reminderDateTime,
                    'message' => $message,
                    'status' => Reminder::STATUS_PENDING,
                    'send_method' => Reminder::METHOD_APP,
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s'),
                ];
                
                try {
                    $reminder = Reminder::create($data);
                    $reminders[] = $reminder;
                } catch (\Exception $e) {
                    Log::error('创建单个用药提醒失败: ' . $e->getMessage());
                }
            }
        }
        
        return $reminders;
    }
    
    /**
     * 解析用药频次
     */
    private function parseFrequency($frequency)
    {
        $frequency = strtolower($frequency);
        
        if (strpos($frequency, '一日三次') !== false || strpos($frequency, '每日3次') !== false) {
            return 3;
        } elseif (strpos($frequency, '一日两次') !== false || strpos($frequency, '每日2次') !== false) {
            return 2;
        } elseif (strpos($frequency, '一日一次') !== false || strpos($frequency, '每日1次') !== false) {
            return 1;
        } elseif (strpos($frequency, '一日四次') !== false || strpos($frequency, '每日4次') !== false) {
            return 4;
        }
        
        return 3; // 默认一日三次
    }
    
    /**
     * 获取提醒时间点
     */
    private function getReminderTimes($timesPerDay)
    {
        switch ($timesPerDay) {
            case 1:
                return ['08:00:00'];
            case 2:
                return ['08:00:00', '18:00:00'];
            case 3:
                return ['08:00:00', '12:00:00', '18:00:00'];
            case 4:
                return ['08:00:00', '12:00:00', '16:00:00', '20:00:00'];
            default:
                return ['08:00:00', '12:00:00', '18:00:00'];
        }
    }
    
    /**
     * 检查并发送到期提醒
     */
    public function checkAndSendDueReminders()
    {
        $now = date('Y-m-d H:i:s');
        $sentCount = 0;
        $failedCount = 0;
        
        try {
            // 查找到期的待发送提醒
            $dueReminders = Reminder::where('status', Reminder::STATUS_PENDING)
                ->where('reminder_time', '<=', $now)
                ->select();
            
            foreach ($dueReminders as $reminder) {
                if ($this->sendReminder($reminder)) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }
            }
            
            return [
                'sent' => $sentCount,
                'failed' => $failedCount,
                'total' => count($dueReminders)
            ];
            
        } catch (\Exception $e) {
            Log::error('检查到期提醒失败: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 发送提醒
     */
    public function sendReminder(Reminder $reminder)
    {
        try {
            $success = false;
            
            switch ($reminder->send_method) {
                case Reminder::METHOD_WECHAT:
                    $success = $this->sendWechatReminder($reminder);
                    break;
                case Reminder::METHOD_SMS:
                    $success = $this->sendSmsReminder($reminder);
                    break;
                case Reminder::METHOD_APP:
                    $success = $this->sendAppReminder($reminder);
                    break;
                default:
                    $success = $this->sendAppReminder($reminder);
            }
            
            // 更新提醒状态
            $reminder->status = $success ? Reminder::STATUS_SENT : Reminder::STATUS_FAILED;
            $reminder->sent_at = date('Y-m-d H:i:s');
            $reminder->update_time = date('Y-m-d H:i:s');
            $reminder->save();
            
            return $success;
            
        } catch (\Exception $e) {
            Log::error('发送提醒失败: ' . $e->getMessage());
            
            // 标记为发送失败
            $reminder->status = Reminder::STATUS_FAILED;
            $reminder->update_time = date('Y-m-d H:i:s');
            $reminder->save();
            
            return false;
        }
    }
    
    /**
     * 发送微信提醒（模拟）
     */
    private function sendWechatReminder(Reminder $reminder)
    {
        // TODO: 集成微信小程序推送API
        Log::info('发送微信提醒: ' . $reminder->title . ' - ' . $reminder->content);
        return true; // 模拟发送成功
    }
    
    /**
     * 发送短信提醒（模拟）
     */
    private function sendSmsReminder(Reminder $reminder)
    {
        // TODO: 集成短信服务API
        Log::info('发送短信提醒: ' . $reminder->title . ' - ' . $reminder->content);
        return true; // 模拟发送成功
    }
    
    /**
     * 发送应用内提醒
     */
    private function sendAppReminder(Reminder $reminder)
    {
        // 应用内提醒直接标记为已发送
        Log::info('应用内提醒: ' . $reminder->title . ' - ' . $reminder->content);
        return true;
    }
}