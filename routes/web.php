<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\MasterDataController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Schedule Management
    Route::get('/schedules/create', [ScheduleController::class, 'create'])->name('schedules.create');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::post('/schedules/check-conflict', [ScheduleController::class, 'checkConflict'])->name('schedules.check-conflict');

    // Master Data
    Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index');
    Route::post('/master-data/rooms', [MasterDataController::class, 'storeRoom'])->name('master-data.rooms.store');
    Route::put('/master-data/rooms/{room}', [MasterDataController::class, 'updateRoom'])->name('master-data.rooms.update');
    Route::delete('/master-data/rooms/{room}', [MasterDataController::class, 'destroyRoom'])->name('master-data.rooms.destroy');
    Route::post('/master-data/student-groups', [MasterDataController::class, 'storeStudentGroup'])->name('master-data.student-groups.store');
    Route::put('/master-data/student-groups/{studentGroup}', [MasterDataController::class, 'updateStudentGroup'])->name('master-data.student-groups.update');
    Route::delete('/master-data/student-groups/{studentGroup}', [MasterDataController::class, 'destroyStudentGroup'])->name('master-data.student-groups.destroy');
});

require __DIR__.'/auth.php';
