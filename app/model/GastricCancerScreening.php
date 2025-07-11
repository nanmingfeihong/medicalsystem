
<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

class GastricCancerScreening extends Model
{
    protected $name = 'gastric_cancer_screening';
    
    // 自动时间戳
    protected $autoWriteTimestamp = true;
    
    // 字段类型
    protected $type = [
        'id' => 'integer',
        'user_id' => 'integer',
        'age' => 'integer',
        'height' => 'float',
        'weight' => 'float',
        'bmi' => 'float',
        'risk_score' => 'integer',
        'create_time' => 'datetime',
        'update_time' => 'datetime'
    ];
    
    // 关联用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * 计算胃癌风险评分
     */
    public function calculateRiskScore(): int
    {
        $score = 0;
        
        // 年龄评分
        if ($this->age >= 45) {
            $score += 2;
        }
        
        // 性别评分（男性风险更高）
        if ($this->gender === '男') {
            $score += 1;
        }
        
        // 居住地评分（高发地区）
        if (in_array($this->location, ['辽宁', '福建', '甘肃', '山东', '江苏'])) {
            $score += 2;
        }
        
        // 吸烟评分
        if ($this->smoking === '是') {
            $score += 1;
        }
        
        // 饮酒评分
        if ($this->alcohol !== '否') {
            $score += 1;
        }
        
        // 饮食习惯评分
        if ($this->pickled_food === '是') {
            $score += 2;
        }
        if ($this->fried_food === '是') {
            $score += 1;
        }
        
        // 症状评分
        if ($this->symptoms !== '无') {
            $score += 2;
        }
        
        // 消化问题评分
        if ($this->digestive_issues !== '无') {
            $score += 1;
        }
        
        // 胃部疾病史评分
        if ($this->stomach_disease !== '没有') {
            $score += 3;
        }
        
        // 家族史评分
        if ($this->family_history !== '无') {
            $score += 4;
        }
        
        // 幽门螺杆菌评分
        if ($this->hp_test === '有,阳性') {
            $score += 3;
        }
        
        return $score;
    }
    
    /**
     * 获取风险等级
     */
    public function getRiskLevel(): string
    {
        if ($this->risk_score <= 5) {
            return '低风险';
        } elseif ($this->risk_score <= 10) {
            return '中风险';
        } else {
            return '高风险';
        }
    }
    
    /**
     * 获取基础建议
     */
    public function getBasicRecommendations(): array
    {
        $recommendations = [];
        
        // 通用建议
        $recommendations[] = '保持健康饮食习惯，减少腌制、油炸食品摄入';
        $recommendations[] = '戒烟限酒';
        $recommendations[] = '定期体检，关注胃部健康';
        
        // 根据风险等级添加建议
        if ($this->risk_level === '中风险') {
            $recommendations[] = '建议1-2年进行一次胃镜检查';
            $recommendations[] = '如有胃部不适及时就医';
        } elseif ($this->risk_level === '高风险') {
            $recommendations[] = '建议每年进行一次胃镜检查';
            $recommendations[] = '建议到消化内科专科就诊';
            $recommendations[] = '如有幽门螺杆菌感染需规范治疗';
        }
        
        return $recommendations;
    }
}
