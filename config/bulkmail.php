<?php
// config/bulkmail.php

return [

    // "ক্লায়েন্টের_নাম:কী" জোড়াগুলো কমা দিয়ে আলাদা করে
    'api_keys' => env('BULK_MAIL_API_KEYS', ''),

    // প্রতি মিনিটে কয়টা মেইল পাঠানো হবে (৫ = ঘণ্টায় ৩০০)।
    // cPanel-এর আসল hourly limit-এর নিচে রাখুন।
    'per_minute' => (int) env('BULK_MAIL_PER_MINUTE', 5),
];