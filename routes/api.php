<?php
// routes/api.php  (পুরো ফাইল রিপ্লেস করুন)

use App\Http\Controllers\BulkEmailController;
use App\Http\Middleware\ApiKeyAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// এখন দুটো রুটই API key ছাড়া খুলবে না
Route::middleware([ApiKeyAuth::class, 'throttle:60,1'])
    ->prefix('bulk-email')
    ->group(function () {
        Route::post('/campaigns', [BulkEmailController::class, 'store']);
        Route::get('/campaigns/{campaignId}/status', [BulkEmailController::class, 'status']);
    });