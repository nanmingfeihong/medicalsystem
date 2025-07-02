<?php
use think\facade\Route;

// 处方相关路由
Route::group('prescription', function () {
    Route::get('/', 'PrescriptionController/index');
    Route::post('upload', 'PrescriptionController/upload');
    Route::get('detail', 'PrescriptionController/detail');
    Route::get('list', 'PrescriptionController/list');
});

// 测试路由
Route::group('test', function () {
    Route::get('/', 'TestController/index');
    Route::get('database', 'TestController/database');
    Route::get('prescription', 'TestController/prescription');
});

// 首页路由
Route::get('/', function () {
    return redirect('/prescription');
});

return [];