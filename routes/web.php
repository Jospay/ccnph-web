<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Shop\ShopConversationController;
use App\Http\Controllers\Web\BusinessTrainingController;
use App\Http\Controllers\Web\Conversation\ConversationController;
use App\Http\Controllers\Web\Conversation\MessageController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\IntellectualPropertyController;
use App\Http\Controllers\Web\LoanAssistanceController;
use App\Http\Controllers\Web\LoanScheduleController;
use App\Http\Controllers\Web\Seller\DashboardController as SellerDashboardController;
use App\Http\Controllers\Web\Seller\ShopController as SellerShopController;
use App\Http\Controllers\Web\Seller\ProductController as SellerProductController;
use App\Http\Controllers\Web\Seller\OrderController as SellerOrderController;
use App\Http\Controllers\Web\Seller\SalesController as SellerSalesController;
use App\Http\Controllers\Web\Seller\SalesAnalyticsController as SellerSalesAnalyticsController;
use App\Http\Controllers\Web\SuperAdmin\AdminManagementController;
use App\Http\Controllers\Web\SuperAdmin\CoopMembershipController;
use App\Http\Controllers\Web\SupportChat\SupportChatController;
use App\Models\UserType;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'Home', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::get('/join-us', function () {
    return Inertia::render('landing/JoinUs');
})->name('join-us');

Route::middleware([
    'auth',
    'seller',
])
->prefix('seller')
->name('seller.')
->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])
        ->name('dashboard.index');

    Route::get('/shop/create', [SellerShopController::class, 'create'])
        ->name('shop.create');
    Route::post('/shop', [SellerShopController::class, 'store'])
        ->name('shop.store');
    Route::get('/shop/{shop:slug}/edit', [SellerShopController::class, 'edit'])
        ->name('shop.edit');
    Route::post('/shop/{shop:slug}', [SellerShopController::class, 'update'])
        ->name('shop.update');

    Route::get('/products', [SellerProductController::class, 'index'])
        ->name('products.index');
    Route::get('/products/create', [SellerProductController::class, 'create'])
        ->name('products.create');
    Route::get('/products/{product:slug}', [SellerProductController::class, 'show'])
        ->name('products.show');
    Route::post('/products', [SellerProductController::class, 'store'])
        ->name('products.store');
    Route::get('/products/{product:slug}/edit', [SellerProductController::class, 'edit'])
        ->name('products.edit');
    Route::post('/products/{product:slug}', [SellerProductController::class, 'update'])
        ->name('products.update');

    Route::get('/orders', [SellerOrderController::class, 'index'])
        ->name('orders.index');
    Route::get('/orders/{order:order_number}', [SellerOrderController::class, 'show'])
        ->name('orders.show');
    Route::patch('/orders/{order}/action', [SellerOrderController::class, 'action'])
        ->name('orders.action');
    Route::patch('/orders/{order}/cancel', [SellerOrderController::class, 'cancel'])
        ->name('orders.cancel');

    Route::get('/sales', [SellerSalesController::class, 'index'])
        ->name('sales.index');
    Route::patch('/sales/{order}/action', [SellerSalesController::class, 'action'])
        ->name('sales.action');
    Route::get('/sales/analytics', [SellerSalesAnalyticsController::class, 'analytics'])
        ->name('sales.analytics');

    // shop conversations
    Route::prefix('conversations')->name('conversations.')->group(function () {
        Route::get('/', [ShopConversationController::class, 'index'])->name('index');
        Route::get('{conversation}', [ShopConversationController::class, 'show'])->name('show');
        Route::post('{conversation}/messages', [ShopConversationController::class, 'storeMessage'])->name('messages.store');
    });
    
});

Route::middleware([
    'auth',
    'role:'.UserType::SUPER_ADMIN,
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
    'role:'.UserType::SUPER_ADMIN.','.UserType::ADMIN,
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

    Route::prefix('admin/support-chat')->name('support-chat.')->group(function () {
        Route::get('/', [SupportChatController::class, 'index'])->name('index');
    });

});

Route::middleware(['auth'])->group(function () {
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('{notification}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::post('read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
    });
});

require __DIR__.'/settings.php';
