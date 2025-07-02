<?php
declare(strict_types=1);

namespace app\controller;

use app\BaseController;
use app\model\ColonCancerScreening as ScreeningModel;
use app\service\QwenService;
use think\Request;
use think\Response;
use think\facade\View;
use think\facade\Log;

/**
 * 肠癌早筛控制器
 */
class ColonScreening extends BaseController
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
        return View::fetch('colon_screening/index');
    }
    
    /**
     * 提交早筛问卷
     */
    public function submit(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证必填字段
            $required = ['name', 'contact', 'age', 'gender', 'smoking', 'height', 'weight', 
                        'first_degree_rel', 'colorectal_cancer', 'genetic_disease'];
            
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
            $screening->user_id = $request->param('user_id', 1); // 默认用户ID
            $screening->name = $data['name'];
            $screening->contact = $data['contact'];
            $screening->age = $data['age'];
            $screening->gender = $data['gender'];
            $screening->smoking = $data['smoking'];
            $screening->height = $height;
            $screening->weight = $weight;
            $screening->bmi = round($bmi, 2);
            $screening->bmi_category = $bmiCategory;
            $screening->first_degree_rel = $data['first_degree_rel'];
            $screening->colorectal_cancer = $data['colorectal_cancer'];
            $screening->genetic_disease = $data['genetic_disease'];
            
            // 计算风险评分
            $screening->risk_score = $screening->calculateRiskScore();
            $screening->risk_level = $screening->getRiskLevel();
            
            // 构建AI分析提示
            $prompt = $this->buildAnalysisPrompt($data, $bmi, $bmiCategory);
            
            // 调用通义千问进行风险评估
            $aiResult = $this->qwenService->assessColonCancerRisk([
                '姓名' => $data['name'],
                '年龄' => $data['age'],
                '性别' => $data['gender'],
                '吸烟史' => $data['smoking'],
                'BMI' => "{$bmi}，分类为{$bmiCategory}",
                '一级亲属结直肠癌史' => $data['first_degree_rel'],
                '一级亲属确诊情况' => $data['colorectal_cancer'],
                '遗传性疾病' => $data['genetic_disease']
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
            Log::error('肠癌早筛提交失败: ' . $e->getMessage());
            return json(['code' => 0, 'message' => '提交失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取筛查结果详情
     */
    public function detail(Request $request): Response
    {
        $id = $request->param('id');
        
        try {
            $screening = ScreeningModel::with(['user'])->find($id);
            
            if (!$screening) {
                return json(['code' => 0, 'message' => '记录不存在']);
            }
            
            $data = $screening->toArray();
            $data['recommendations'] = json_decode($data['recommendations'], true);
            
            return json(['code' => 1, 'message' => '获取成功', 'data' => $data]);
            
        } catch (\Exception $e) {
            Log::error('获取筛查详情失败: ' . $e->getMessage());
            return json(['code' => 0, 'message' => '获取失败']);
        }
    }
    
    /**
     * 获取筛查记录列表
     */
    public function list(Request $request): Response
    {
        $userId = $request->param('user_id', 1);
        $page = $request->param('page', 1);
        $limit = $request->param('limit', 10);
        
        try {
            $query = ScreeningModel::where('user_id', $userId);
            
            $screenings = $query->order('create_time', 'desc')
                ->paginate([
                    'list_rows' => $limit,
                    'page' => $page
                ]);
            
            $list = [];
            foreach ($screenings->items() as $screening) {
                $item = $screening->toArray();
                $item['recommendations'] = json_decode($item['recommendations'], true);
                $list[] = $item;
            }
            
            return json([
                'code' => 1,
                'message' => '获取成功',
                'data' => $list,
                'total' => $screenings->total(),
                'page' => $page,
                'limit' => $limit
            ]);
            
        } catch (\Exception $e) {
            Log::error('获取筛查列表失败: ' . $e->getMessage());
            return json(['code' => 0, 'message' => '获取失败']);
        }
    }
    
    /**
     * 显示筛查结果页面
     */
    public function result(Request $request): Response
    {
        $id = $request->param('id');
        
        try {
            $screening = ScreeningModel::find($id);
            
            if (!$screening) {
                return redirect('/colon_screening/')->with('error', '记录不存在');
            }
            
            $data = $screening->toArray();
            $data['recommendations'] = json_decode($data['recommendations'], true);
            
            return View::fetch('colon_screening/result', ['screening' => $data]);
            
        } catch (\Exception $e) {
            Log::error('显示筛查结果失败: ' . $e->getMessage());
            return redirect('/colon_screening/')->with('error', '获取结果失败');
        }
    }
    
    /**
     * 构建AI分析提示
     */
    private function buildAnalysisPrompt(array $data, float $bmi, string $bmiCategory): string
    {
        return "请根据以下信息评估此人的肠癌风险，并给出详细的分析和建议：

问：您的年龄是?
答：{$data['age']}

问：您的性别是?
答：{$data['gender']}

问：您有吸烟史吗?
答：{$data['smoking']}

问：您的BMI是多少?
答：{$bmi}，分类为{$bmiCategory}

问：您的一级亲属中是否有人患过结直肠癌?
答：{$data['first_degree_rel']}

问：您的一级亲属中确诊为结直肠癌的具体情况是?
答：{$data['colorectal_cancer']}

问：您和您的一级亲属中，是否有人曾经被诊断出有任何肠道方面的遗传性疾病?
答：{$data['genetic_disease']}

请提供以下信息：
1. 肠癌风险评估（低、中、高）
2. 详细的风险分析，解释每个因素如何影响风险
3. 针对性的建议，包括生活方式调整和筛查建议
4. 任何其他相关的健康建议";
    }
}