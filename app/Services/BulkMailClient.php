<?php
// অন্য Laravel প্রজেক্টে: app/Services/BulkMailClient.php
//
// ওই প্রজেক্টের .env:
//   BULK_MAIL_URL=https://আপনার-মেইল-সার্ভিস-সাবডোমেইন
//   BULK_MAIL_KEY=এই প্রজেক্টের জন্য দেওয়া কী
//
// ওই প্রজেক্টের config/services.php-তে যোগ করুন:
//   'bulk_mail' => [
//       'url' => env('BULK_MAIL_URL'),
//       'key' => env('BULK_MAIL_KEY'),
//   ],
//
// ব্যবহার:
//   $id = app(\App\Services\BulkMailClient::class)->send(
//       'Meeting Notice', 'Meeting on Sunday', '<p>...</p>',
//       [['email' => 'a@x.com', 'name' => 'A'], ['email' => 'b@x.com']],
//   );
//   $status = app(\App\Services\BulkMailClient::class)->status($id);

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BulkMailClient
{
    /** ক্যাম্পেইন কিউতে দেয়; campaign_id ফেরত দেয় */
    public function send(
        string $name,
        string $subject,
        string $bodyHtml,
        array $recipients,
        ?string $fromEmail = null,
        ?string $fromName = null,
    ): int {
        $response = $this->http()->post('/api/bulk-email/campaigns', array_filter([
            'name'       => $name,
            'subject'    => $subject,
            'body_html'  => $bodyHtml,
            'from_email' => $fromEmail,
            'from_name'  => $fromName,
            'recipients' => $recipients,
        ], fn ($v) => $v !== null))->throw();

        return (int) $response->json('campaign_id');
    }

    public function status(int $campaignId): array
    {
        return $this->http()->get("/api/bulk-email/campaigns/{$campaignId}/status")->throw()->json();
    }

    private function http()
    {
        return Http::baseUrl(rtrim(config('services.bulk_mail.url'), '/'))
            ->withHeaders(['X-API-Key' => config('services.bulk_mail.key')])
            ->acceptJson()
            ->timeout(30);
    }
}