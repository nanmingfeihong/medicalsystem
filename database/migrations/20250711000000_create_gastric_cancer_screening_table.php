

<?php
declare(strict_types=1);

use think\migration\Migrator;
use think\migration\db\Column;

class CreateGastricCancerScreeningTable extends Migrator
{
    public function up()
    {
        $table = $this->table('gastric_cancer_screening');
        $table->addColumn('user_id', 'integer', ['comment' => '用户ID'])
            ->addColumn('name', 'string', ['limit' => 50, 'comment' => '姓名'])
            ->addColumn('contact', 'string', ['limit' => 50, 'comment' => '联系方式'])
            ->addColumn('age', 'integer', ['comment' => '年龄'])
            ->addColumn('gender', 'string', ['limit' => 10, 'comment' => '性别'])
            ->addColumn('height', 'float', ['comment' => '身高(cm)'])
            ->addColumn('weight', 'float', ['comment' => '体重(kg)'])
            ->addColumn('bmi', 'float', ['comment' => 'BMI指数'])
            ->addColumn('bmi_category', 'string', ['limit' => 20, 'comment' => 'BMI分类'])
            ->addColumn('hypertension', 'string', ['limit' => 10, 'comment' => '高血压'])
            ->addColumn('location', 'string', ['limit' => 50, 'comment' => '居住地'])
            ->addColumn('smoking', 'string', ['limit' => 10, 'comment' => '吸烟'])
            ->addColumn('alcohol', 'string', ['limit' => 20, 'comment' => '饮酒'])
            ->addColumn('pickled_food', 'string', ['limit' => 10, 'comment' => '腌制食品'])
            ->addColumn('fried_food', 'string', ['limit' => 10, 'comment' => '油炸食品'])
            ->addColumn('symptoms', 'string', ['limit' => 50, 'comment' => '胃部症状'])
            ->addColumn('digestive_issues', 'string', ['limit' => 50, 'comment' => '消化问题'])
            ->addColumn('stomach_disease', 'string', ['limit' => 50, 'comment' => '胃部疾病史'])
            ->addColumn('family_history', 'string', ['limit' => 50, 'comment' => '家族胃癌史'])
            ->addColumn('hp_test', 'string', ['limit' => 20, 'comment' => '幽门螺杆菌检测'])
            ->addColumn('endoscopy', 'string', ['limit' => 10, 'comment' => '胃镜检查史'])
            ->addColumn('risk_score', 'integer', ['comment' => '风险评分'])
            ->addColumn('risk_level', 'string', ['limit' => 20, 'comment' => '风险等级'])
            ->addColumn('ai_analysis', 'text', ['comment' => 'AI分析结果'])
            ->addColumn('recommendations', 'text', ['comment' => '建议'])
            ->addTimestamps()
            ->addIndex(['user_id'])
            ->create();
    }

    public function down()
    {
        $this->table('gastric_cancer_screening')->drop();
    }
}

