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

// 处方详情路由（优先匹配）
Route::get('prescription/detail/:id', 'Prescription/detail');
Route::get('prescription/test/:id', 'Prescription/test');

// 处方相关路由
Route::group('prescription', function () {
    Route::get('upload', 'Prescription/upload');           // 显示上传页面
    Route::post('upload', 'Prescription/doUpload');       // 处理上传
    Route::get('list', 'Prescription/list');              // 处方列表
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
    Route::get('list', 'Reminder/list');                  // 提醒列表
    Route::post('mark_read/:id', 'Reminder/markRead');    // 标记已读
});

// 餐食相关路由
Route::group('meal', function () {
    Route::get('upload', 'Meal/upload');                  // 显示上传页面
    Route::post('upload', 'Meal/doUpload');               // 处理上传
    Route::get('list', 'Meal/list');                      // 餐食列表
    Route::get('summary', 'Meal/summary');                // 饮食总结
});

// API 路由
Route::group('api', function () {
    Route::post('prescription/extract', 'Api/extractPrescription');  // 提取处方信息
    Route::post('meal/analyze', 'Api/analyzeMeal');                 // 分析餐食
});