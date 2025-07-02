<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// 首页
Route::get('/', 'Index/index');

// 处方详情路由（必须在路由组之前）
Route::get('prescription/show/:id', 'Prescription/show');        // 显示详情页面
Route::get('prescription/detail/:id', 'Prescription/detail');    // API接口
Route::get('prescription/test/:id', 'Prescription/test');

// 处方相关路由
Route::group('prescription', function () {
    Route::get('/', 'Prescription/index');                // 处方列表页面
    Route::get('upload', 'Prescription/upload');           // 显示上传页面
    Route::post('upload', 'Prescription/doUpload');       // 处理上传
    Route::get('list', 'Prescription/list');              // 处方列表API
});

// 测试路由
Route::get('test/:id', function($id) {
    return json(['test_id' => $id]);
});

// 用户相关路由
Route::group('user', function () {
    Route::get('profile', 'User/profile');                // 用户资料
    Route::post('profile', 'User/updateProfile');         // 更新用户资料
});

// 提醒相关路由
Route::group('reminder', function () {
    Route::get('/', 'Reminder/index');                    // 提醒列表页面
    Route::get('list', 'Reminder/list');                  // 提醒列表API
    Route::post('create', 'Reminder/create');             // 创建提醒
    Route::post('send/:id', 'Reminder/send');             // 发送提醒
    Route::post('mark-read/:id', 'Reminder/markAsRead');  // 标记已读
    Route::get('check', 'Reminder/checkAndSend');         // 检查并发送到期提醒
    Route::post('from-prescription', 'Reminder/createFromPrescription'); // 从处方创建提醒
});

// 餐食相关路由
Route::group('meal', function () {
    Route::get('upload', 'Meal/upload');                  // 显示上传页面
    Route::post('analyze', 'Meal/analyze');               // 分析餐食
    Route::get('list', 'Meal/list');                      // 餐食列表
    Route::get('summary', 'Meal/summary');                // 饮食总结
});

// 肠癌早筛路由
Route::group('colon_screening', function () {
    Route::get('/', 'ColonScreening/index');                // 早筛问卷页面
    Route::post('submit', 'ColonScreening/submit');         // 提交问卷
    Route::get('detail/:id', 'ColonScreening/detail');      // 获取详情API
    Route::get('list', 'ColonScreening/list');              // 筛查记录列表
    Route::get('result/:id', 'ColonScreening/result');      // 显示结果页面
});

// API 路由
Route::group('api', function () {
    Route::post('prescription/extract', 'Api/extractPrescription');  // 提取处方信息
    Route::post('meal/analyze', 'Api/analyzeMeal');                 // 分析餐食
});