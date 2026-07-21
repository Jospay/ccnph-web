<?php

use App\Http\Controllers\API\Auth\AuthenticatedSessionController;
use App\Http\Controllers\API\Auth\PasswordResetController;
use App\Http\Controllers\API\Auth\RegisteredUserController;
use App\Http\Controllers\API\BusinessTraining\CategoryController;
use App\Http\Controllers\API\BusinessTraining\TrainingController;
use App\Http\Controllers\API\BusinessTraining\TypeController;
use App\Http\Controllers\API\Conversation\ConversationController;
use App\Http\Controllers\API\Conversation\MessageController;
use App\Http\Controllers\API\IntellectualProperty\IntellectualPropertyController;
use App\Http\Controllers\API\Loan\LoanController;
use App\Http\Controllers\API\Membership\MembershipController;
use App\Http\Controllers\API\News\NewsController;
use App\Http\Controllers\API\Payment\PaymentMethodController;
use App\Http\Controllers\API\Payment\PaymentWebhookController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\Settings\ProfileController;
use App\Http\Controllers\API\ShareCapital\ShareCapitalController;
use App\Http\Controllers\API\Shop\ShopConversationController;
use App\Http\Controllers\API\Store\CollectionProductController;
use App\Http\Controllers\API\Store\CustomerCartController;
use App\Http\Controllers\API\Store\CustomerCheckoutController;
use App\Http\Controllers\API\Store\CustomerOrderController;
use App\Http\Controllers\API\Store\ShopHomeController;
use App\Http\Controllers\API\Store\ShopProductController;
use App\Http\Controllers\API\Store\ShopStoreController;
use App\Http\Controllers\API\Verification\PhoneVerificationController;
use App\Http\Controllers\API\Wallet\WalletController;
use App\Models\UserType;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware('guest')->group(function () {
    // Auth
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1');

    // Verification
    Route::post('/verify-phone/acknowledge', [PhoneVerificationController::class, 'verify'])->middleware('throttle:5,1');
    Route::post('/verify-phone/resend', [PhoneVerificationController::class, 'resend'])->middleware('throttle:3,1');
    Route::post('/register/set-password', [RegisteredUserController::class, 'setPassword'])->middleware('throttle:5,1');

    Route::prefix('password')->group(function () {
        Route::post('/forgot', [PasswordResetController::class, 'sendOtp']);
        Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp']);
        Route::post('/reset', [PasswordResetController::class, 'resetPassword']);
        Route::post('/resend-otp', [PasswordResetController::class, 'resendOtp']);
    });
});

Route::get('/payment/status/{paymentIntentId}', [PaymentController::class, 'status']);
Route::get('/payment/success', [PaymentController::class, 'success']);
Route::post('/webhooks/{gateway}', PaymentWebhookController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    // Business Training Routes
    Route::prefix('business-training')
        ->middleware('role.api:'.UserType::MEMBER)
        ->group(function () {
            // Types
            Route::get('types', [TypeController::class, 'index']);
            Route::get('types/{type}', [TypeController::class, 'show']);

            // Categories under a Type
            Route::get('types/{type}/categories', [CategoryController::class, 'index']);
            Route::get('types/{type}/categories/{category}', [CategoryController::class, 'show']);

            // Training Modules under a Category
            Route::get('categories/{category}/trainings', [TrainingController::class, 'index']);
            Route::get('categories/{category}/trainings/{module}', [TrainingController::class, 'show'])
                ->whereNumber('module');
        });

    // Profile Routes
    Route::prefix('profile')
        ->middleware('role.api:'.UserType::BASIC.','.UserType::MEMBER)
        ->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('update', [ProfileController::class, 'update']);
            Route::patch('change-password', [ProfileController::class, 'changePassword']);
            Route::post('avatar', [ProfileController::class, 'updateAvatar']);

            Route::get('addresses', [ProfileController::class, 'getAddresses']);
            Route::post('address', [ProfileController::class, 'address']);
            Route::put('address/{userAddress}', [ProfileController::class, 'updateAddress']);
            Route::delete('address/{userAddress}', [ProfileController::class, 'deleteAddress']);
        });

    // Wallet Routes
    Route::prefix('wallet')
        ->middleware('role.api:'.UserType::MEMBER)
        ->group(function () {
            Route::get('/', [WalletController::class, 'index']);
            Route::get('/update', [WalletController::class, 'update']);
            Route::get('transaction', [WalletController::class, 'transaction']);
            Route::get('presets', [WalletController::class, 'presets']);
            Route::post('recharge', [WalletController::class, 'recharge']);
        });

    // Loan Routes
    Route::prefix('loans')
        ->middleware('role.api:'.UserType::MEMBER)
        ->group(function () {
            Route::get('/', [LoanController::class, 'index']);
            Route::post('/', [LoanController::class, 'store']);

            Route::get('loanable-amount', [LoanController::class, 'getLoanableAmount']);
            Route::get('compute', [LoanController::class, 'compute']);

            Route::get('{loan}', [LoanController::class, 'show']);
            Route::post('{loan}/pay', [LoanController::class, 'pay']);
        });

    // Share Capital Routes
    Route::prefix('share-capital')
        ->middleware('role.api:'.UserType::MEMBER)
        ->group(function () {
            Route::get('settings', [ShareCapitalController::class, 'settings']);
            Route::get('/', [ShareCapitalController::class, 'index']);
            Route::post('apply', [ShareCapitalController::class, 'apply']);
            Route::post('schedules/{schedule}/pay', [ShareCapitalController::class, 'pay']);
        });

    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->middleware('role.api:'.UserType::BASIC.','.UserType::MEMBER);

    Route::prefix('memberships')
        ->middleware('role.api:'.UserType::BASIC.','.UserType::MEMBER)
        ->group(function () {
            Route::get('/', [MembershipController::class, 'index']);
            Route::get('settings', [MembershipController::class, 'settings']);
            Route::post('apply', [MembershipController::class, 'apply']);
            Route::post('schedules/{schedule}/pay', [MembershipController::class, 'pay']);
            Route::delete('cancel', [MembershipController::class, 'cancel']);
        });

    // Intellectual Property Routes
    Route::prefix('intellectual-properties')
        ->middleware('role.api:'.UserType::MEMBER)
        ->group(function () {
            Route::get('/', [IntellectualPropertyController::class, 'index']);
            Route::post('/', [IntellectualPropertyController::class, 'store']);
            Route::get('{intellectualProperty}', [IntellectualPropertyController::class, 'show']);
            Route::put('{intellectualProperty}', [IntellectualPropertyController::class, 'update']);
            Route::get('{intellectualProperty}/settings', [IntellectualPropertyController::class, 'settings']);
            Route::post('{intellectualProperty}/apply/payment', [IntellectualPropertyController::class, 'applyPayment']);
            Route::post('schedules/{schedule}/pay', [IntellectualPropertyController::class, 'pay']);
        });

    // Conversation Routes
    Route::prefix('conversations')
        ->middleware('role.api:'.UserType::MEMBER)
        ->group(function () {
            Route::get('/', [ConversationController::class, 'index']);
            Route::get('{conversation}', [ConversationController::class, 'show']);
            Route::post('{conversation}/messages', [MessageController::class, 'store']);
            Route::post('{conversation}/read', [ConversationController::class, 'markRead']);
        });

    Route::get('/news', [NewsController::class, 'index'])
        ->middleware('role.api:'.UserType::BASIC.','.UserType::MEMBER);

    Route::get('/news/{id}', [NewsController::class, 'show'])
        ->middleware('role.api:'.UserType::BASIC.','.UserType::MEMBER);

    // FISMPC Online Store
    Route::prefix('store')
        ->middleware('role.api:'.UserType::BASIC.','.UserType::MEMBER)
        ->group(function () {
            Route::get('home', [ShopHomeController::class, 'index']);
            Route::get('/top-deals', [ShopHomeController::class, 'topDeals']);
            Route::get('products', [ShopProductController::class, 'index']);
            Route::get('products/{product}', [ShopProductController::class, 'show']);

            Route::get('collections', [CollectionProductController::class, 'index']);
            Route::post('collections/{product}/toggle', [CollectionProductController::class, 'toggle']);

            Route::get('cart', [CustomerCartController::class, 'index']);
            Route::post('cart/items', [CustomerCartController::class, 'store']);
            Route::patch('cart/items/{cartItem}', [CustomerCartController::class, 'update']);
            Route::delete('cart/items/{cartItem}', [CustomerCartController::class, 'destroy']);

            Route::post('/checkout/select', [CustomerCheckoutController::class, 'select']);
            Route::get('/checkout', [CustomerCheckoutController::class, 'index']);
            Route::post('/checkout', [CustomerCheckoutController::class, 'store']);

            Route::get('/orders', [CustomerOrderController::class, 'index']);
            Route::get('/orders/{order}', [CustomerOrderController::class, 'show']);
            Route::get('/orders/{order}/rate', [CustomerOrderController::class, 'rate']);
            Route::post('/orders/{order}/rate', [CustomerOrderController::class, 'storeRating']);
            Route::post('/orders/{order}/status', [CustomerOrderController::class, 'updateStatus']);

            Route::get('/{store}', [ShopStoreController::class, 'show']);
            Route::post('register-seller', [ShopStoreController::class, 'registerSeller']);
        });

    // Shop Conversations
    Route::prefix('shop-conversations')
        ->middleware('role.api:'.UserType::BASIC.','.UserType::MEMBER)
        ->group(function () {
            Route::get('/', [ShopConversationController::class, 'index']);
            Route::post('start', [ShopConversationController::class, 'start']);
            Route::get('{conversation}', [ShopConversationController::class, 'show']);
            Route::post('{conversation}/messages', [ShopConversationController::class, 'storeMessage']);
            Route::post('{conversation}/read', [ShopConversationController::class, 'markRead']);
        });

});
