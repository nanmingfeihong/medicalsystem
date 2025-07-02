<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 提醒模型
 */
class Reminder extends Model
{
    protected $name = 'reminder';
    
    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'user_id'         => 'int',
        'prescription_id' => 'int',
        'reminder_type'   => 'string',  // follow_up: 复诊提醒, medication: 用药提醒
        'reminder_time'   => 'datetime',
        'message'         => 'text',
        'status'          => 'int',     // 0: 待发送, 1: 已发送, 2: 发送失败
        'send_method'     => 'string',  // wechat: 微信, sms: 短信, app: 应用内
        'sent_at'         => 'datetime',
        'create_time'     => 'string',
        'update_time'     => 'string',
    ];
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    // 提醒类型常量
    const TYPE_FOLLOW_UP = 'follow_up';     // 复诊提醒
    const TYPE_MEDICATION = 'medication';   // 用药提醒
    
    // 状态常量
    const STATUS_PENDING = 0;    // 待发送
    const STATUS_SENT = 1;       // 已发送
    const STATUS_FAILED = 2;     // 发送失败
    
    // 发送方式常量
    const METHOD_WECHAT = 'wechat';  // 微信
    const METHOD_SMS = 'sms';        // 短信
    const METHOD_APP = 'app';        // 应用内
    
    /**
     * 获取提醒类型文本
     */
    public function getTypeTextAttr($value, $data)
    {
        $types = [
            self::TYPE_FOLLOW_UP => '复诊提醒',
            self::TYPE_MEDICATION => '用药提醒',
        ];
        return $types[$data['reminder_type']] ?? '未知';
    }
    
    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_PENDING => '待发送',
            self::STATUS_SENT => '已发送',
            self::STATUS_FAILED => '发送失败',
        ];
        return $status[$data['status']] ?? '未知';
    }
    
    /**
     * 获取发送方式文本
     */
    public function getMethodTextAttr($value, $data)
    {
        $methods = [
            self::METHOD_WECHAT => '微信',
            self::METHOD_SMS => '短信',
            self::METHOD_APP => '应用内',
        ];
        return $methods[$data['send_method']] ?? '未知';
    }
    
    /**
     * 关联用户模型
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * 关联处方模型
     */
    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }
}