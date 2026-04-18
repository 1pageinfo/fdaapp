<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\SanghController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\Admin\UserRoleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LinkController;

// Redirect root → dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// ----------------------
// 🔑 Authentication
// ----------------------

// Register
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register'])->name('register.submit');

// Login
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->name('login.submit');

// Logout
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Forgot password
Route::get('password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

// Reset password
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// ----------------------
// 🔐 Protected Routes
// ----------------------
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Chat polling (no websockets)
    Route::get('/chat/poll', [DashboardController::class, 'pollChat'])->name('chat.poll');

    // Resources
    Route::resource('groups', GroupController::class);
    Route::resource('chats', ChatController::class);
    Route::resource('files', FileController::class);
    Route::resource('folders', FolderController::class);
    Route::resource('receipts', ReceiptController::class);
    Route::resource('meetings', MeetingController::class);
    Route::resource('sanghs', SanghController::class);
    Route::resource('links', LinkController::class);

   

    // Settings (global)
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // Profile (user personal settings)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin Role Management
    Route::prefix('admin')->group(function () {
        Route::get('/user-roles', [UserRoleController::class, 'index'])->name('admin.user_roles.index');
        Route::put('/user-roles/{user}', [UserRoleController::class, 'update'])->name('admin.user_roles.update');
    });


    Route::get('receipts-export', [ReceiptController::class, 'exportCsv'])->name('receipts.export');
    // Only the actions you have implemented
    Route::resource('receipts', ReceiptController::class)->only(['index', 'create', 'store']);
    Route::get('sanghs-export', [SanghController::class, 'exportExcel'])->name('sanghs.export');
    Route::post('sanghs-import', [SanghController::class, 'importExcel'])->name('sanghs.import');
    Route::get('sanghs-template', [SanghController::class, 'downloadTemplate'])->name('sanghs.template');
    Route::post('sanghs-seed-placeholders', [SanghController::class, 'seedPlaceholders'])->name('sanghs.seed_placeholders');
    Route::post('/sanghs/{sangh}/renewals/{year}', [SanghController::class, 'updateRenewal'])->name('sanghs.renewals.update');
    Route::get('/sanghs/{sangh}/receipt/{year}/pdf', [SanghController::class, 'downloadReceiptPdf'])->name('sanghs.receipt.pdf');
    Route::get('/sanghs/{sangh}/pdf', [SanghController::class, 'downloadPdf'])->name('sanghs.pdf');         // generate & stream download
    Route::get('/sanghs/{sangh}/save-pdf', [SanghController::class, 'savePdfToStorage'])->name('sanghs.save_pdf'); // save to storage & return link
    Route::get('/sanghs/{sangh}/download-stored', [SanghController::class, 'downloadStoredPdf'])->name('sanghs.download_stored'); // download saved file




    Route::get('groups/{group}/edit', [GroupController::class, 'edit'])->name('groups.edit');
    Route::put('groups/{group}', [GroupController::class, 'update'])->name('groups.update');
    Route::post('groups/{group}/users/{user}/admin', [GroupController::class, 'setAdmin'])->name('groups.users.admin');

    // Members
    Route::post('groups/{group}/members', [GroupController::class, 'addMember'])->name('groups.members.add');
    Route::delete('groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::post('/groups/reorder', [GroupController::class, 'reorder'])->name('groups.reorder');

    // CSV export
    Route::get('groups-export', [GroupController::class, 'exportCsv'])->name('groups.export');

    // Tab management (dynamic)
    Route::post('groups/{group}/tabs', [ChatController::class, 'storeTab'])->name('groups.tabs.store');
    Route::post('groups/{group}/tabs/reorder', [ChatController::class, 'reorderTabs'])->name('groups.tabs.reorder');
    Route::put('groups/{group}/tabs/{chat}', [ChatController::class, 'updateTab'])->name('groups.tabs.update');
    Route::delete('groups/{group}/tabs/{chat}', [ChatController::class, 'destroyTab'])->name('groups.tabs.destroy');

    // Chat (by chat id)
    Route::get('groups/{group}/chat/{chat}', [ChatController::class, 'show'])->name('groups.chat.show');
    Route::post('groups/{group}/chat/{chat}/message', [ChatController::class, 'storeMessage'])->name('groups.chat.message');
    Route::post('groups/{group}/chat/{chat}/pin/{message}', [ChatController::class, 'pin'])->name('groups.chat.pin');
    Route::post('groups/{group}/chat/{chat}/unpin', [ChatController::class, 'unpin'])->name('groups.chat.unpin');

    // Poll endpoint
    Route::get('groups/{group}/chat/{chat}/poll', [ChatController::class, 'poll'])->name('groups.chat.poll');
    Route::get('/chats/{chat}/edit', [ChatController::class, 'edit'])->name('chats.edit');
    Route::get(
        '/groups/{group}/chats/{chat}/messages/{message}/edit',
        [ChatController::class, 'editMessage']
    )->name('chat.message.edit');

    Route::put(
        '/groups/{group}/chats/{chat}/messages/{message}',
        [ChatController::class, 'updateMessage']
    )->name('chat.message.update');

    Route::delete(
        '/groups/{group}/chats/{chat}/messages/{message}',
        [ChatController::class, 'destroyMessage']
    )->name('chat.message.destroy');



    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index'); // JSON for header bell
    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
});
