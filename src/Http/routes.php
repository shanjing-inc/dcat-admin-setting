<?php

use Shanjing\DcatAdminSetting\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::resource('system-setting', Controllers\DcatAdminSettingController::class);
