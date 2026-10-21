<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home route: redirect to dashboard if logged in, otherwise to login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Guest Routes (accessible only when not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/staff/login', [AuthController::class, 'showStaffLogin'])->name('staff.login');
    Route::post('/staff/login', [AuthController::class, 'staffLogin']);
});

// Authenticated Student Routes
Route::middleware(['auth', 'student'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Student complaint routes.
    Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy'])->name('complaints.destroy');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'studentShow'])->name('complaints.show');
    Route::post('/notifications/responses/{response}/read', [ComplaintController::class, 'markResponseAsRead'])->name('notifications.responses.read');
});

// Authenticated General Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Staff Routes
Route::middleware(['auth', 'staff'])->group(function () {
    Route::get('/staff', [ComplaintController::class, 'staffIndex'])->name('staff.dashboard');
    Route::get('/staff/complaints/{complaint}', [ComplaintController::class, 'staffShow'])->name('staff.complaints.show');
    Route::patch('/staff/complaints/{complaint}/verify', [ComplaintController::class, 'verify'])->name('staff.complaints.verify');
    Route::patch('/staff/complaints/{complaint}/status', [ComplaintController::class, 'updateStatus'])->name('staff.complaints.status');
    Route::post('/staff/complaints/{complaint}/responses', [ComplaintController::class, 'storeResponse'])->name('staff.complaints.responses.store');
});
