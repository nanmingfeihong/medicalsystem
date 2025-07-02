<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 处方模型
 */
class Prescription extends Model
{
    protected $name = 'prescription';
    
    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'user_id'         => 'int',
        'prescription_image' => 'string',
        'original_filename' => 'string',
        'file_size'       => 'int',
        'extracted_content' => 'text',
        'follow_up_date'  => 'datetime',
        'medication_frequency' => 'string',
        'medication_details' => 'text',
        'status'          => 'int',
        'create_time'     => 'string',
        'update_time'     => 'string',
    ];
    
    // 自动时间戳
    protected $autoWriteTimestamp = false;
    
    // 状态常量
    const STATUS_PENDING = 0;    // 待处理
    const STATUS_PROCESSED = 1;  // 已处理
    const STATUS_FAILED = 2;     // 处理失败
    
    /**
     * 获取状态文本
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            self::STATUS_PENDING => '待处理',
            self::STATUS_PROCESSED => '已处理',
            self::STATUS_FAILED => '处理失败',
        ];
        return $status[$data['status']] ?? '未知';
    }
    
    /**
     * 关联用户模型
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * 获取处方图片完整路径
     */
    public function getImageUrlAttr($value, $data)
    {
        if (empty($data['prescription_image'])) {
            return '';
        }
        return request()->domain() . '/uploads/prescriptions/' . $data['prescription_image'];
    }
}