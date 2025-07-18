<?php

use Shanjing\DcatAdminSetting\Http\Controllers;
use Shanjing\DcatAdminSetting\SettingServiceProvider;
use Illuminate\Support\Facades\Route;

$route = SettingServiceProvider::setting('page_route');
if ($route) {
    Route::resource($route, Controllers\DcatAdminSettingController::class);
    // 添加恢复版本的路由
    Route::post($route . '-restore', [Controllers\DcatAdminSettingController::class, 'restore']);
}
