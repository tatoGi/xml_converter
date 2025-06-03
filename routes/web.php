<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\Auth\AuthAdminController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\FileUploadController;
use Illuminate\Support\Facades\Artisan;


// Public routes with optional locale prefix
Route::prefix('{locale?}')
    ->middleware(['locale'])
    ->group(function () {
        // User authentication routes
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
        Route::post('/logout', [AuthController::class, 'logout'])->name('web.logout');

        // Admin login routes
        Route::get('/admin/login', [AuthAdminController::class, 'showLoginForm'])->name('admin.login');
        Route::post('/admin/login', [AuthAdminController::class, 'store'])->name('admin.login.submit');
        Route::post('/admin/logout', [AuthAdminController::class, 'logout'])->name('admin.logout');
        // Admin routes with 'auth_admin' middleware
        Route::prefix('admin')->middleware(['auth_admin'])->group(function () {
            Route::get('/', [DashboardController::class, 'index'])->name('admin');
            Route::get('/profile', [DashboardController::class, 'profile'])->name('admin.profile');

            Route::resource('/users', UserController::class);
        });

        Route::post('/switch-language', [FileUploadController::class, 'switchLanguage'])->name('witch.language');
        Route::post('/file-upload', [FileUploadController::class, 'upload'])->name('file.upload');
        // Welcome page, requires user authentication
        Route::get('/home', function () {
            return view('welcome');
        })->middleware('auth')->name('home');
    });
    Route::get('/', function () {
        return redirect(app()->getLocale() . '/home');
    });
Route::get('/clear-config', function() {
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    return "Configuration cache cleared!";
});
Route::get('/generate-key', function() {
    Artisan::call('key:generate');
    return 'Application key generated successfully!';
});
