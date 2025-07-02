<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;

/**
 * 用户模型
 */
class User extends Model
{
    protected $name = 'user';
    
    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'username'        => 'string',
        'phone'           => 'string',
        'email'           => 'string',
        'wechat_openid'   => 'string',
        'avatar'          => 'string',
        'status'          => 'int',
        'create_time'     => 'datetime',
        'update_time'     => 'datetime',
    ];
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    /**
     * 关联处方
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'user_id');
    }
}