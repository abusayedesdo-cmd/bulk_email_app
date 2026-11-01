<?php
// config/bulkmail.php  (পুরো ফাইল রিপ্লেস করুন)

return [

    // "ক্লায়েন্টের_নাম:কী" জোড়াগুলো কমা দিয়ে আলাদা করে
    'api_keys' => env('BULK_MAIL_API_KEYS', ''),

    // প্রতি মিনিটে কয়টা বাল্ক মেইল (৫ = ঘণ্টায় ৩০০)
    'per_minute' => (int) env('BULK_MAIL_PER_MINUTE', 5),

    // পাসওয়ার্ড-রিকভারি রুটে API key লাগবে কিনা (true = লাগবে)।
    // পরে অথরাইজ করতে .env-এ true দিন।
    'otp_require_key' => (bool) env('BULK_MAIL_OTP_REQUIRE_KEY', true),

    // ওই রুট শুধু যে IP থেকে খুলবে, কমা দিয়ে লিখুন। ফাঁকা থাকলে সব IP।
    'otp_allowed_ips' => env('BULK_MAIL_OTP_ALLOWED_IPS', ''),
];