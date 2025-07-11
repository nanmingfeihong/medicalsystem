<?php
declare (strict_types = 1);

namespace app\model;

use think\Model;
use think\model\concern\SoftDelete;

/**
 * 用户模型
 */
class User extends Model
{
    use SoftDelete;

    protected $name = 'user';
    
    // 设置字段信息
    protected $schema = [
        'id'              => 'int',
        'username'        => 'string',
        'password'        => 'string',
        'phone'           => 'string',
        'email'           => 'string',
        'wechat_openid'   => 'string',
        'avatar'          => 'string',
        'status'          => 'int',
        'role'            => 'int',
        'last_login_time' => 'datetime',
        'create_time'     => 'datetime',
        'update_time'     => 'datetime',
        'delete_time'     => 'datetime'
    ];
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    // 隐藏字段
    protected $hidden = ['password', 'delete_time'];

    // 用户角色
    const ROLE_ADMIN = 1;
    const ROLE_DOCTOR = 2;
    const ROLE_PATIENT = 3;

    /**
     * 关联处方
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'user_id');
    }

    /**
     * 关联胃癌筛查记录
     */
    public function gastricScreenings()
    {
        return $this->hasMany(GastricCancerScreening::class, 'user_id');
    }

    /**
     * 密码加密
     */
    public function setPasswordAttr($value)
    {
        return password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * 验证密码
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }
}