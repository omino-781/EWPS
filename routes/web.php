<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('events', EventController::class);
    Route::post('events/{event}/register', [EventController::class, 'register'])->name('events.register');
    Route::patch('events/{event}/attendance/confirm', [EventController::class, 'confirmAttendance'])->name('events.attendance.confirm')->middleware('role:participant');
    Route::get('events/{event}/ticket', [EventController::class, 'ticket'])->name('events.ticket');
    Route::post('events/{event}/vendors', [EventController::class, 'assignVendor'])->name('events.vendors.assign')->middleware('role:administrator,organizer');
    Route::patch('events/{event}/vendors/{vendor}', [EventController::class, 'updateVendorStatus'])->name('events.vendors.status');
    Route::delete('events/{event}/vendors/{vendor}', [EventController::class, 'removeVendor'])->name('events.vendors.remove')->middleware('role:administrator,organizer');

    Route::get('venues', [VenueController::class, 'index'])->name('venues.index')->middleware('role:administrator,organizer');
    Route::post('venues', [VenueController::class, 'store'])->name('venues.store')->middleware('role:administrator,organizer');
    Route::put('venues/{id}', [VenueController::class, 'update'])->name('venues.update')->middleware('role:administrator');

    Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index')->middleware('role:administrator,organizer,vendor');
    Route::post('vendors', [VendorController::class, 'store'])->name('vendors.store')->middleware('role:administrator,organizer');

    Route::get('budgets', [BudgetController::class, 'index'])->name('budgets.index')->middleware('role:administrator,organizer');
    Route::post('expenses', [BudgetController::class, 'storeExpense'])->name('expenses.store')->middleware('role:administrator,organizer');

    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index')->middleware('role:administrator,organizer');
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store')->middleware('role:administrator,organizer');
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');

    Route::get('payments', [PaymentController::class, 'index'])->name('payments.index')->middleware('role:administrator,organizer,vendor');
    Route::post('payments', [PaymentController::class, 'store'])->name('payments.store')->middleware('role:administrator,organizer');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index')->middleware('role:administrator,organizer');
    Route::post('reports/generate', [ReportController::class, 'generate'])->name('reports.generate')->middleware('role:administrator,organizer');
    Route::get('reports/{report}/download', [ReportController::class, 'download'])->name('reports.download')->middleware('role:administrator,organizer');

    Route::middleware('role:administrator')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::get('settings', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::patch('settings', [SystemSettingController::class, 'update'])->name('settings.update');
    });

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});
