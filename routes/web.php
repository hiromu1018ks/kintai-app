<?php

use App\Http\Controllers\Admin\AttendanceCorrectionController;
use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth', 'web'])->group(function () {
    // ダッシュボードの表示　
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/attendances/clock-in', [AttendanceController::class, 'clockIn'])->name('attendances.clock_in');
    Route::post('/attendances/clock-out', [AttendanceController::class, 'clockOut'])->name('attendances.clock_out');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'can:viewAdminFeatures'])->prefix('admin/')->name('admin.')->group(function () {
    Route::resource('holidays', HolidayController::class);

    // 打刻修正機能
    Route::get('attendance-corrections', [AttendanceCorrectionController::class, 'index'])->name('attendance_corrections.index'); // 一覧表示 (検索フォーム含む)
    Route::get('attendance-corrections/{attendance}/edit', [AttendanceCorrectionController::class, 'edit'])->name('attendance_corrections.edit'); // 修正フォーム表示
    Route::put('attendance-corrections/{attendance}', [AttendanceCorrectionController::class, 'update'])->name('attendance_corrections.update'); // 更新処理
});

require __DIR__ . '/auth.php';
