<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // সব ক্লায়েন্ট প্রজেক্ট মিলে একটাই লিমিট — যাতে SMTP অ্যাকাউন্টের
        // ঘণ্টার লিমিট কখনো পার না হয়। সংখ্যা বদলাতে .env-এ BULK_MAIL_PER_MINUTE দিন।
        RateLimiter::for('bulk-mail', function ($job) {
            return Limit::perMinute(config('bulkmail.per_minute'));
        });
    }
}