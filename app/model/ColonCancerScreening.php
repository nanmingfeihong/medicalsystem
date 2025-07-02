<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 肠癌早筛模型
 */
class ColonCancerScreening extends Model
{
    protected $table = 'colon_cancer_screening';
    
    // 设置字段信息
    protected $schema = [
        'id'          => 'int',
        'user_id'     => 'int',
        'name'        => 'string',
        'contact'     => 'string',
        'age'         => 'string',
        'gender'      => 'string',
        'smoking'     => 'string',
        'height'      => 'float',
        'weight'      => 'float',
        'bmi'         => 'float',
        'bmi_category' => 'string',
        'first_degree_rel' => 'string',
        'colorectal_cancer' => 'string',
        'genetic_disease' => 'string',
        'risk_score'  => 'int',
        'risk_level'  => 'string',
        'ai_analysis' => 'text',
        'recommendations' => 'text',
        'create_time' => 'datetime',
        'update_time' => 'datetime',
    ];
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    
    // 关联用户模型
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * 计算风险评分
     */
    public function calculateRiskScore(): int
    {
        $score = 0;
        
        // 年龄评分
        switch ($this->age) {
            case '49岁及以下':
                $score += 1;
                break;
            case '50-59岁':
                $score += 2;
                break;
            case '60岁及以上':
                $score += 3;
                break;
        }
        
        // 性别评分
        if ($this->gender === '男性') {
            $score += 1;
        }
        
        // 吸烟史评分
        if ($this->smoking === '有') {
            $score += 1;
        }
        
        // BMI评分
        if ($this->bmi_category === '大于等于23') {
            $score += 1;
        }
        
        // 一级亲属结直肠癌史评分
        if ($this->first_degree_rel === '有') {
            $score += 2;
        }
        
        // 一级亲属确诊情况评分
        switch ($this->colorectal_cancer) {
            case '有1人在60岁以下确诊':
                $score += 2;
                break;
            case '有1人60岁及以上确诊':
                $score += 1;
                break;
            case '有2人或以上在60岁以下确诊':
                $score += 3;
                break;
            case '有2人或以上在60岁及以上确诊':
                $score += 2;
                break;
        }
        
        // 遗传性疾病评分
        if ($this->genetic_disease === '是') {
            $score += 3;
        }
        
        return $score;
    }
    
    /**
     * 获取风险等级
     */
    public function getRiskLevel(): string
    {
        $score = $this->risk_score;
        
        if ($score <= 3) {
            return '低风险';
        } elseif ($score <= 6) {
            return '中等风险';
        } else {
            return '高风险';
        }
    }
    
    /**
     * 获取基础建议
     */
    public function getBasicRecommendations(): array
    {
        $score = $this->risk_score;
        
        if ($score <= 3) {
            return [
                '您的肠癌风险较低',
                '建议保持健康的生活方式',
                '定期进行体检',
                '保持均衡饮食，多吃蔬菜水果',
                '适量运动，戒烟限酒'
            ];
        } elseif ($score <= 6) {
            return [
                '您的肠癌风险中等',
                '建议增加运动，保持健康饮食',
                '考虑每年进行一次肠癌筛查',
                '定期监测身体状况',
                '如有异常症状及时就医'
            ];
        } else {
            return [
                '您的肠癌风险较高',
                '强烈建议您尽快咨询医生',
                '进行全面的肠癌筛查',
                '定期复查，密切监测',
                '改善生活方式，戒烟限酒',
                '保持健康饮食和适量运动'
            ];
        }
    }
}