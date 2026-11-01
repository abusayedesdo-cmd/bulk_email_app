<?php
// app/Http/Controllers/OtpMailController.php  (আগের ফাইল রিপ্লেস করুন)

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Throwable;

class OtpMailController extends Controller
{
    /**
     * GET /api/bulk-email/campaigns/password-recover.php
     *     ?PrimaryEmail=...&Username=...&OTP=...
     *
     * - Username না এলে পুরনো PrimaryPhone প্যারামিটারটাই Username হিসেবে ধরা হয়।
     * - OTP প্যারামিটারের মানটাই মেইলে "Password" হিসেবে যায়।
     * - বাল্ক কিউ/লিমিটারের বাইরে, সাথে সাথে পাঠায়।
     * - Username, Password বা ইমেইল কখনো লগে লেখা হয় না।
     */
    public function passwordRecover(Request $request)
    {
        // IP তালিকা দেওয়া থাকলে শুধু সেই IP থেকে চলবে (ফাঁকা থাকলে সবার জন্য খোলা)
        $allowedIps = array_filter(array_map('trim', explode(',', (string) config('bulkmail.otp_allowed_ips'))));

        if ($allowedIps && ! in_array($request->ip(), $allowedIps, true)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $data = $request->validate([
            'PrimaryEmail' => 'required|email|max:255',
            'OTP'          => 'required|string|min:4|max:50',
            'Username'     => 'required_without:PrimaryPhone|nullable|string|max:50',
            'PrimaryPhone' => 'required_without:Username|nullable|string|max:50',
        ]);

        $username = $data['Username'] ?? $data['PrimaryPhone'];

        // একই ইমেইলে মিনিটে ৩টার বেশি মেইল নয় (অপব্যবহার ঠেকাতে)
        $limitKey = 'otp-mail:' . strtolower($data['PrimaryEmail']);

        if (RateLimiter::tooManyAttempts($limitKey, 3)) {
            return response()->json(['message' => 'Too many requests for this email. Try again later.'], 429);
        }

        RateLimiter::hit($limitKey, 60);

        try {
            Mail::to($data['PrimaryEmail'])->send(new OtpMail($data['PrimaryEmail'], $username, $data['OTP']));
        } catch (Throwable $e) {
            Log::warning('User-access mail failed to send: ' . get_class($e));

            return response()->json(['message' => 'Mail could not be sent.'], 502);
        }

        return response()->json(['message' => 'Email sent.']);
    }
}