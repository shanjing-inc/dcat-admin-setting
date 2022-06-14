<?php

use Shanjing\DcatAdminSetting\Http\Controllers;
use Shanjing\DcatAdminSetting\SettingServiceProvider;
use Illuminate\Support\Facades\Route;

$route = SettingServiceProvider::setting('page_route');
if ($route) {
    Route::resource($route, Controllers\DcatAdminSettingController::class);
}
