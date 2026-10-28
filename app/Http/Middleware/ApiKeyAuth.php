<?php
// app/Http/Middleware/ApiKeyAuth.php  (Middleware ফোল্ডার না থাকলে তৈরি করুন)

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * .env: BULK_MAIL_API_KEYS="procurement:KEY1,siste:KEY2"
     * ক্লায়েন্ট প্রজেক্ট রিকোয়েস্ট হেডারে X-API-Key পাঠাবে।
     * মিল পেলে ক্লায়েন্টের নাম request-এ বসে যায় (কে কোন ক্যাম্পেইন পাঠাল তা ট্র্যাক করতে)।
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provided = (string) $request->header('X-API-Key', '');
        $client = null;

        foreach (explode(',', (string) config('bulkmail.api_keys')) as $pair) {
            $pair = trim($pair);

            if ($pair === '' || ! str_contains($pair, ':')) {
                continue;
            }

            [$name, $key] = explode(':', $pair, 2);

            if ($provided !== '' && hash_equals($key, $provided)) {
                $client = $name;
                break;
            }
        }

        if ($client === null) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $request->attributes->set('client', $client);

        return $next($request);
    }
}