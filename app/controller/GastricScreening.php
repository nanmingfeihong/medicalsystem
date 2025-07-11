
<?php
declare(strict_types=1);

namespace app\controller;

use app\BaseController;
use app\model\GastricCancerScreening as ScreeningModel;
use app\service\QwenService;
use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Log;

/**
 * 胃癌早筛控制器
 */
class GastricScreening extends BaseController
{
    private $qwenService;
    
    public function __construct()
    {
        $this->qwenService = new QwenService();
    }
    
    /**
     * 显示早筛问卷页面
     */
    public function index()
    {
        return View::fetch('gastric_screening/index');
    }
    
    /**
     * 提交早筛问卷
     */
    public function submit(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证必填字段
            $required = ['name', 'contact', 'age', 'gender', 'height', 'weight', 
                        'hypertension', 'location', 'smoking', 'alcohol',
                        'pickled_food', 'fried_food', 'symptoms', 'digestive_issues',
                        'stomach_disease', 'family_history', 'hp_test', 'endoscopy'];
            
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return json(['code' => 0, 'message' => "请填写{$field}字段"]);
                }
            }
            
            // 计算BMI
            $height = floatval($data['height']);
            $weight = floatval($data['weight']);
            $bmi = $weight / (($height / 100) ** 2);
            $bmiCategory = $bmi < 23 ? '小于23' : '大于等于23';
            
            // 创建筛查记录
            $screening = new ScreeningModel();
            $screening->user_id = $request->param('user_id', 1);
            $screening->name = $data['name'];
            $screening->contact = $data['contact'];
            $screening->age = $data['age'];
            $screening->gender = $data['gender'];
            $screening->height = $height;
            $screening->weight = $weight;
            $screening->bmi = round($bmi, 2);
            $screening->bmi_category = $bmiCategory;
            $screening->hypertension = $data['hypertension'];
            $screening->location = $data['location'];
            $screening->smoking = $data['smoking'];
            $screening->alcohol = $data['alcohol'];
            $screening->pickled_food = $data['pickled_food'];
            $screening->fried_food = $data['fried_food'];
            $screening->symptoms = $data['symptoms'];
            $screening->digestive_issues = $data['digestive_issues'];
            $screening->stomach_disease = $data['stomach_disease'];
            $screening->family_history = $data['family_history'];
            $screening->hp_test = $data['hp_test'];
            $screening->endoscopy = $data['endoscopy'];
            
            // 计算风险评分
            $screening->risk_score = $screening->calculateRiskScore();
            $screening->risk_level = $screening->getRiskLevel();
            
            // 构建AI分析提示
            $prompt = $this->buildAnalysisPrompt($data, $bmi, $bmiCategory);
            
            // 调用通义千问进行风险评估
            $aiResult = $this->qwenService->assessGastricCancerRisk([
                '姓名' => $data['name'],
                '年龄' => $data['age'],
                '性别' => $data['gender'],
                'BMI' => "{$bmi}，分类为{$bmiCategory}",
                '高血压' => $data['hypertension'],
                '居住地' => $data['location'],
                '吸烟' => $data['smoking'],
                '饮酒' => $data['alcohol'],
                '腌制食品' => $data['pickled_food'],
                '油炸食品' => $data['fried_food'],
                '胃部症状' => $data['symptoms'],
                '消化问题' => $data['digestive_issues'],
                '胃部疾病史' => $data['stomach_disease'],
                '家族胃癌史' => $data['family_history'],
                '幽门螺杆菌检测' => $data['hp_test'],
                '胃镜检查史' => $data['endoscopy']
            ]);
            
            if ($aiResult['success']) {
                $screening->ai_analysis = $aiResult['content'];
            } else {
                Log::error('AI风险评估失败: ' . ($aiResult['error'] ?? '未知错误'));
                $screening->ai_analysis = 'AI分析暂时不可用，请参考基础评估结果';
            }
            
            // 获取基础建议
            $basicRecommendations = $screening->getBasicRecommendations();
            $screening->recommendations = json_encode($basicRecommendations, JSON_UNESCAPED_UNICODE);
            
            // 保存到数据库
            $screening->save();
            
            return json([
                'code' => 1,
                'message' => '评估完成',
                'data' => [
                    'id' => $screening->id,
                    'risk_score' => $screening->risk_score,
                    'risk_level' => $screening->risk_level,
                    'ai_analysis' => $screening->ai_analysis,
                    'recommendations' => $basicRecommendations,
                    'bmi' => $screening->bmi
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('胃癌早筛提交失败: ' . $e->getMessage());
            return json(['code' => 0, 'message' => '提交失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 构建AI分析提示
     */
    private function buildAnalysisPrompt(array $data, float $bmi, string $bmiCategory): string
    {
        return "请根据以下信息评估此人的胃癌风险，并给出详细的分析和建议：

问：您的年龄是?
答：{$data['age']}

问：您的性别是?
答：{$data['gender']}

问：您有高血压吗?
答：{$data['hypertension']}

问：您长期居住在哪里?
答：{$data['location']}

问：您吸烟吗?
答：{$data['smoking']}

问：您饮酒吗?
答：{$data['alcohol']}

问：您经常吃腌制食品吗?
答：{$data['pickled_food']}

问：您经常吃油炸食品吗?
答：{$data['fried_food']}

问：您有胃部不适症状吗?
答：{$data['symptoms']}

问：您有消化系统问题吗?
答：{$data['digestive_issues']}

问：您有胃部疾病史吗?
答：{$data['stomach_disease']}

问：您家族有胃癌史吗?
答：{$data['family_history']}

问：您做过幽门螺杆菌检测吗?
答：{$data['hp_test']}

问：您做过胃镜检查吗?
答：{$data['endoscopy']}

请提供以下信息：
1. 胃癌风险评估（低、中、高）
2. 详细的风险分析，解释每个因素如何影响风险  
3. 针对性的建议，包括生活方式调整和筛查建议
4. 任何其他相关的健康建议";
    }
    
    // 其他方法（detail/list/result）与ColonScreening类似
    // 可根据需要添加
}
