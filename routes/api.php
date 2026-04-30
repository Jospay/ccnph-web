<?php

use App\Http\Controllers\API\Auth\AuthenticatedSessionController;
use App\Http\Controllers\API\Auth\RegisteredUserController;
use App\Http\Controllers\API\BusinessTraining\CategoryController;
use App\Http\Controllers\API\BusinessTraining\TrainingController;
use App\Http\Controllers\API\BusinessTraining\TypeController;
use App\Http\Controllers\API\IntellectualProperty\IntellectualPropertyController;
use App\Http\Controllers\API\Loan\LoanController;
use App\Http\Controllers\API\Membership\MembershipController;
use App\Http\Controllers\API\Payment\PaymentMethodController;
use App\Http\Controllers\API\Payment\PaymentWebhookController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\Settings\ProfileController;
use App\Http\Controllers\API\Verification\PhoneVerificationController;
use App\Http\Controllers\API\Wallet\WalletController;
use App\Models\UserType;
use Illuminate\Http\Request;
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
});

Route::get('/payment/status/{paymentIntentId}', [PaymentController::class, 'status']);
Route::get('/payment/success', [PaymentController::class, 'success']);
Route::post('/webhooks/{gateway}', PaymentWebhookController::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    // Business Training Routes
    Route::prefix('business-training')
        ->middleware('role.api:' . UserType::MEMBER)
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
        ->middleware('role.api:' . UserType::BASIC . ',' . UserType::MEMBER)
        ->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('update', [ProfileController::class, 'update']);
            Route::patch('change-password', [ProfileController::class, 'changePassword']);
            Route::post('avatar', [ProfileController::class, 'updateAvatar']);
        });

    // Wallet Routes
    Route::prefix('wallet')
        ->middleware('role.api:' . UserType::MEMBER)
        ->group(function () {
            Route::get('/', [WalletController::class, 'index']);
            Route::get('/update', [WalletController::class, 'update']);
            Route::get('transaction', [WalletController::class, 'transaction']);
        });

    // Loan Routes
    Route::prefix('loans')
        ->middleware('role.api:' . UserType::MEMBER)
        ->group(function () {
            Route::get('/', [LoanController::class, 'index']);
            Route::post('/', [LoanController::class, 'store']);

            Route::get('loanable-amount', [LoanController::class, 'getLoanableAmount']);
            Route::get('compute', [LoanController::class, 'compute']);

            Route::get('{loan}', [LoanController::class, 'show']);
            Route::post('{loan}/pay', [LoanController::class, 'pay']);
        });

    Route::get('/payment-methods', [PaymentMethodController::class, 'index'])->middleware('role.api:' . UserType::BASIC . ',' . UserType::MEMBER);

    Route::prefix('memberships')
        ->middleware('role.api:' . UserType::BASIC . ',' . UserType::MEMBER)
        ->group(function () {
            Route::get('/', [MembershipController::class, 'index']);
            Route::get('settings', [MembershipController::class, 'settings']);
            Route::post('apply', [MembershipController::class, 'apply']);
            Route::post('schedules/{schedule}/pay', [MembershipController::class, 'pay']);
            Route::delete('cancel', [MembershipController::class, 'cancel']);
        });

    // Intellectual Property Routes
    Route::prefix('intellectual-properties')
        ->middleware('role.api:' . UserType::MEMBER)
        ->group(function () {
            Route::get('/', [IntellectualPropertyController::class, 'index']);
            Route::post('/', [IntellectualPropertyController::class, 'store']);
            Route::get('{intellectualProperty}', [IntellectualPropertyController::class, 'show']);
            Route::put('{intellectualProperty}', [IntellectualPropertyController::class, 'update']);
            Route::get('{intellectualProperty}/settings', [IntellectualPropertyController::class, 'settings']);
            Route::post('{intellectualProperty}/apply/payment', [IntellectualPropertyController::class, 'applyPayment']);
            Route::post('schedules/{schedule}/pay', [IntellectualPropertyController::class, 'pay']);
        });
});
