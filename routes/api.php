<?php
// routes/api.php  (পুরো ফাইল রিপ্লেস করুন)

use App\Http\Controllers\BulkEmailController;
use App\Http\Controllers\OtpMailController;
use App\Http\Middleware\ApiKeyAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// বাল্ক ক্যাম্পেইন: সবসময় API key লাগবে
Route::middleware([ApiKeyAuth::class, 'throttle:60,1'])
    ->prefix('bulk-email')
    ->group(function () {
        Route::post('/campaigns', [BulkEmailController::class, 'store']);
        Route::get('/campaigns/{campaignId}/status', [BulkEmailController::class, 'status']);
    });

// পাসওয়ার্ড-রিকভারি মেইল: .env-এ BULK_MAIL_OTP_REQUIRE_KEY=false থাকলে API key ছাড়াই চলবে
Route::middleware(
    config('bulkmail.otp_require_key')
        ? [ApiKeyAuth::class, 'throttle:60,1']
        : ['throttle:60,1']
)->get('/bulk-email/campaigns/career-portal-user-access.php', [OtpMailController::class, 'passwordRecover']);