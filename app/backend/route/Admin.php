<?php

use think\facade\Route;

Route::group('admins', function () {
    Route::get('', 'index');
})->prefix('admin/');
