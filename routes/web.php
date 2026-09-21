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
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\SanghFeeSettingController;
use App\Http\Controllers\ContactController;

// Redirect root → dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// ----------------------
// 🔑 Authentication
// ----------------------

Route::middleware('guest')->group(function () {
    // Register
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.submit');

    // Login
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.submit');

    // Forgot password
    Route::get('password/forgot', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    // Reset password
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Logout (must stay reachable while authenticated)
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// ----------------------
// 🔐 Protected Routes
// ----------------------
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.view');

    // Resources
    Route::resource('groups', GroupController::class)
        ->middlewareFor(['index', 'show'], 'permission:groups.view')
        ->middlewareFor(['create', 'store'], 'permission:groups.create')
        ->middlewareFor(['edit', 'update'], 'permission:groups.edit')
        ->middlewareFor('destroy', 'permission:groups.delete');
    Route::resource('files', FileController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:files.view')
        ->middlewareFor(['create', 'store'], 'permission:files.create')
        ->middlewareFor(['edit', 'update'], 'permission:files.edit')
        ->middlewareFor('destroy', 'permission:files.delete');
    Route::post('files/notes', [FileController::class, 'storeNote'])->name('files.notes.store')->middleware('permission:files.create');
    Route::resource('folders', FolderController::class)
        ->middlewareFor(['index', 'show'], 'permission:folders.view')
        ->middlewareFor(['create', 'store'], 'permission:folders.create')
        ->middlewareFor(['edit', 'update'], 'permission:folders.edit')
        ->middlewareFor('destroy', 'permission:folders.delete');
    Route::resource('receipts', ReceiptController::class)
        ->middlewareFor('index', 'permission:receipts.view')
        ->middlewareFor(['create', 'store'], 'permission:receipts.create');
    Route::resource('meetings', MeetingController::class)
        ->middlewareFor(['index', 'show'], 'permission:meetings.view')
        ->middlewareFor(['create', 'store'], 'permission:meetings.create')
        ->middlewareFor(['edit', 'update'], 'permission:meetings.edit')
        ->middlewareFor('destroy', 'permission:meetings.delete');
    Route::resource('sanghs', SanghController::class)
        ->middlewareFor(['index', 'show'], 'permission:sanghs.view')
        ->middlewareFor(['create', 'store'], 'permission:sanghs.create')
        ->middlewareFor(['edit', 'update'], 'permission:sanghs.edit')
        ->middlewareFor('destroy', 'permission:sanghs.delete');
    Route::resource('links', LinkController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:links.view')
        ->middlewareFor(['create', 'store'], 'permission:links.create')
        ->middlewareFor(['edit', 'update'], 'permission:links.edit')
        ->middlewareFor('destroy', 'permission:links.delete');

   

    // Settings (global)
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.view');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update')->middleware('permission:settings.edit');

    // Sangh fee slabs (linked to sangh registrations)
    Route::get('/settings/sangh-fees', [SanghFeeSettingController::class, 'edit'])->name('settings.sangh_fees.edit')->middleware('permission:sangh_fee.view');
    Route::put('/settings/sangh-fees', [SanghFeeSettingController::class, 'update'])->name('settings.sangh_fees.update')->middleware('permission:sangh_fee.edit');
    Route::post('/settings/sangh-fees/slabs', [SanghFeeSettingController::class, 'storeSlab'])->name('settings.sangh_fees.slabs.store')->middleware('permission:sangh_fee.edit');
    Route::delete('/settings/sangh-fees/slabs/{slab}', [SanghFeeSettingController::class, 'destroySlab'])->name('settings.sangh_fees.slabs.destroy')->middleware('permission:sangh_fee.edit');

    // Profile (user personal settings)
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Contacts directory
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index')->middleware('permission:contacts.view');

    Route::prefix('admin')->group(function () {
        Route::middleware('superadmin')->group(function () {
            Route::get('/user-roles', [UserRoleController::class, 'index'])->name('admin.user_roles.index');
            Route::put('/user-roles/{user}', [UserRoleController::class, 'update'])->name('admin.user_roles.update');
            Route::delete('/user-roles/{user}', [UserRoleController::class, 'destroy'])->name('admin.user_roles.destroy');
        });
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->name('admin.activity_logs.index')
            ->middleware('permission:audit.view');
    });


    Route::get('receipts-export', [ReceiptController::class, 'exportCsv'])->name('receipts.export')->middleware('permission:receipts.view');
    Route::get('sanghs-export', [SanghController::class, 'exportExcel'])->name('sanghs.export')->middleware('permission:sanghs.view');
    Route::post('sanghs-import', [SanghController::class, 'importExcel'])->name('sanghs.import')->middleware('permission:sanghs.create');
    Route::get('sanghs-template', [SanghController::class, 'downloadTemplate'])->name('sanghs.template')->middleware('permission:sanghs.create');
    Route::post('sanghs-seed-placeholders', [SanghController::class, 'seedPlaceholders'])->name('sanghs.seed_placeholders')->middleware('superadmin');
    Route::post('/sanghs/{sangh}/registration-receipt', [SanghController::class, 'updateRegistrationReceipt'])->name('sanghs.registration_receipt.update')->middleware('permission:sanghs.edit');
    Route::post('/sanghs/{sangh}/renewals/create-year', [SanghController::class, 'createRenewal'])->name('sanghs.renewals.create')->middleware('permission:sanghs.edit');
    Route::delete('/sanghs/{sangh}/renewals/{year}', [SanghController::class, 'destroyRenewal'])->name('sanghs.renewals.destroy')->middleware('permission:sanghs.delete');
    Route::post('/sanghs/{sangh}/renewals/{year}', [SanghController::class, 'updateRenewal'])->name('sanghs.renewals.update')->middleware('permission:sanghs.edit');
    Route::get('/sanghs/{sangh}/receipt/{year}/pdf', [SanghController::class, 'downloadReceiptPdf'])->name('sanghs.receipt.pdf')->middleware('permission:sanghs.view');
    Route::get('/sanghs/{sangh}/pdf', [SanghController::class, 'downloadPdf'])->name('sanghs.pdf')->middleware('permission:sanghs.view');         // generate & stream download
    Route::get('/sanghs/{sangh}/save-pdf', [SanghController::class, 'savePdfToStorage'])->name('sanghs.save_pdf')->middleware('permission:sanghs.view'); // save to storage & return link
    Route::get('/sanghs/{sangh}/download-stored', [SanghController::class, 'downloadStoredPdf'])->name('sanghs.download_stored')->middleware('permission:sanghs.view'); // download saved file



    Route::post('groups/{group}/users/{user}/admin', [GroupController::class, 'setAdmin'])->name('groups.users.admin')->middleware('permission:groups.edit');

    // Members
    Route::post('groups/{group}/members', [GroupController::class, 'addMember'])->name('groups.members.add')->middleware('permission:groups.edit');
    Route::delete('groups/{group}/members/{user}', [GroupController::class, 'removeMember'])->name('groups.members.remove')->middleware('permission:groups.edit');
    Route::post('/groups/reorder', [GroupController::class, 'reorder'])->name('groups.reorder')->middleware('permission:groups.edit');
    Route::post('/folders/reorder', [FolderController::class, 'reorder'])->name('folders.reorder')->middleware('permission:folders.edit');

    // CSV export
    Route::get('groups-export', [GroupController::class, 'exportCsv'])->name('groups.export')->middleware('permission:groups.view');

    // Tab management (dynamic)
    Route::post('groups/{group}/tabs', [ChatController::class, 'storeTab'])->name('groups.tabs.store')->middleware('permission:chats.create');
    Route::post('groups/{group}/tabs/reorder', [ChatController::class, 'reorderTabs'])->name('groups.tabs.reorder')->middleware('permission:chats.edit');
    Route::put('groups/{group}/tabs/{chat}', [ChatController::class, 'updateTab'])->name('groups.tabs.update')->middleware('permission:chats.edit');
    Route::delete('groups/{group}/tabs/{chat}', [ChatController::class, 'destroyTab'])->name('groups.tabs.destroy')->middleware('permission:chats.delete');

    // Chat (by chat id)
    Route::get('groups/{group}/chat/{chat}', [ChatController::class, 'show'])->name('groups.chat.show')->middleware('permission:chats.view');
    Route::post('groups/{group}/chat/{chat}/message', [ChatController::class, 'storeMessage'])->name('groups.chat.message')->middleware('permission:chats.create');
    Route::post('groups/{group}/chat/{chat}/pin/{message}', [ChatController::class, 'pin'])->name('groups.chat.pin')->middleware('permission:chats.edit');
    Route::post('groups/{group}/chat/{chat}/unpin', [ChatController::class, 'unpin'])->name('groups.chat.unpin')->middleware('permission:chats.edit');

    // Poll endpoint
    Route::get('groups/{group}/chat/{chat}/poll', [ChatController::class, 'poll'])->name('groups.chat.poll')->middleware('permission:chats.view');
    Route::get('/chats/{chat}/edit', [ChatController::class, 'edit'])->name('chats.edit');
    Route::get(
        '/groups/{group}/chats/{chat}/messages/{message}/edit',
        [ChatController::class, 'editMessage']
    )->name('chat.message.edit')->middleware('permission:chats.view');

    Route::put(
        '/groups/{group}/chats/{chat}/messages/{message}',
        [ChatController::class, 'updateMessage']
    )->name('chat.message.update')->middleware('permission:chats.view');

    Route::delete(
        '/groups/{group}/chats/{chat}/messages/{message}',
        [ChatController::class, 'destroyMessage']
    )->name('chat.message.destroy')->middleware('permission:chats.view');



    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index')->middleware('permission:notifications.view'); // JSON for header bell
    Route::get('/search', [SearchController::class, 'index'])->name('search.index')->middleware('permission:search.view');
});
