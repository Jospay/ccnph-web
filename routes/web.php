<?php

use App\Http\Controllers\Web\Conversation\ConversationController;
use App\Http\Controllers\Web\Conversation\MessageController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\Auth\RegistrationController;
use Laravel\Fortify\Features;
use App\Models\UserType;
use Inertia\Inertia;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\BusinessTrainingController;
use App\Http\Controllers\Web\LoanAssistanceController;
use App\Http\Controllers\Web\LoanScheduleController;
use App\Http\Controllers\Web\IntellectualPropertyController;
use App\Http\Controllers\Web\SuperAdmin\CoopMembershipController;
use App\Http\Controllers\Web\SuperAdmin\AdminManagementController;
use App\Http\Controllers\Web\Seller\DashboardController as SellerDashboardController;

Route::inertia('/', 'Home', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::get('/join-us', function () {
    return Inertia::render('landing/JoinUs');
})->name('join-us');

Route::middleware([
    'auth',
    'role:' . UserType::MEMBER
])
    ->prefix('seller')
    ->name('seller.')
    ->group(function () {

        Route::get('/dashboard', [SellerDashboardController::class, 'index'])
            ->name('dashboard.index');

    });

Route::middleware([
    'auth',
    'role:' . UserType::SUPER_ADMIN
])->group(function () {

    Route::get('/admin-management', [AdminManagementController::class, 'index'])
        ->name('admin-management.index');

    Route::get('/admin-management/users/{user}', [AdminManagementController::class, 'show'])
        ->name('admin-management.users.show');

    Route::post('/admin-management/users', [AdminManagementController::class, 'store'])
        ->name('admin-management.users.store');

    Route::patch('/admin-management/{user}/services', [AdminManagementController::class, 'updateServices'])
        ->name('admin-management.users.update-services');

    // Cooperative Membership Domain
    Route::middleware(['service_access:coop-membership'])->group(function () {

        Route::get('/coop-membership', [CoopMembershipController::class, 'index'])
            ->name('coop-membership.index');

        Route::get('/coop-membership/users/{user}', [CoopMembershipController::class, 'show'])
            ->name('coop-membership.users.show');

        Route::patch('/coop-membership/users/{user}/status', [CoopMembershipController::class, 'updateStatus'])
            ->name('coop-membership.users.update-status');

    });

});

Route::middleware([
    'auth',
    'role:' . UserType::SUPER_ADMIN . ',' . UserType::ADMIN
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard.index');

    // Business Training Domain
    Route::middleware(['service_access:business-training'])->group(function () {

        Route::get('/business-training', [BusinessTrainingController::class, 'index'])
            ->name('business-training.index');

        Route::post('/business-training/types', [BusinessTrainingController::class, 'storeType'])
            ->name('business-training.types.store');

        Route::get('/business-training/types/{type:slug}', [BusinessTrainingController::class, 'showType'])
            ->name('business-training.types.show');

        Route::patch('/business-training/types/{type:slug}', [BusinessTrainingController::class, 'updateType'])
            ->name('business-training.types.update');

        Route::delete('/business-training/types/{type:slug}', [BusinessTrainingController::class, 'destroyType'])
            ->name('business-training.types.destroy');

        Route::post('/business-training/types/{type:slug}/categories', [BusinessTrainingController::class, 'storeCategory'])
            ->name('business-training.categories.store');

        Route::get('/business-training/categories/{category:slug}/modules', [BusinessTrainingController::class, 'getCategoryModules'])
            ->name('business-training.modules.show');

        Route::patch('/business-training/categories/{category:slug}', [BusinessTrainingController::class, 'updateCategory'])
            ->name('business-training.categories.update');

        Route::delete('/business-training/categories/{category:slug}', [BusinessTrainingController::class, 'destroyCategory'])
            ->name('business-training.categories.destroy');
    });

    // Loan Assistance Domain
    Route::middleware(['service_access:loan-assistance'])->group(function () {
        Route::get('/loan-assistance', [LoanAssistanceController::class, 'index'])
            ->name('loan-assistance.index');

        Route::patch('/loan-assistance/{loan}/status', [LoanAssistanceController::class, 'updateStatus'])
            ->name('loan-assistance.update-status');

        Route::get('/loan-assistance/{loan}/schedule', [LoanScheduleController::class, 'index'])
            ->name('loan-assistance.schedule.index');

        Route::patch('/loan-assistance/settings', [LoanAssistanceController::class, 'updateSettings'])
            ->name('loan-assistance.settings.update');
    });

    // Intellectual Property Assistance Domain
    Route::middleware(['service_access:intellectual-property-assistance'])->group(function () {
        Route::get('/intellectual-property-assistance', [IntellectualPropertyController::class, 'index'])
            ->name('intellectual-property-assistance.index');

        Route::get('/intellectual-property-assistance/{property}', [IntellectualPropertyController::class, 'show'])
            ->name('intellectual-property-assistance.show');

        Route::patch('/intellectual-property-assistance/{property}/status', [IntellectualPropertyController::class, 'updateStatus'])
            ->name('intellectual-property-assistance.update-status');
    });

    // Conversation routes
    Route::prefix('admin/conversations')->name('conversations.')->group(function () {
        Route::get('{conversation}', [ConversationController::class, 'show'])->name('show');
        Route::post('{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');
        Route::post('{conversation}/read', [ConversationController::class, 'markRead'])->name('read');
    });

    // NEW: notification bell routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    });
});

require __DIR__ . '/settings.php';
