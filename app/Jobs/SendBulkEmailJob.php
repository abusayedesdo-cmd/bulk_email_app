<?php
// app/Jobs/SendBulkEmailJob.php  (পুরো ফাইল রিপ্লেস করুন)

namespace App\Jobs;

use App\Mail\BulkMail;
use App\Models\BulkEmailCampaign;
use App\Models\BulkEmailRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendBulkEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /*
     * গুরুত্বপূর্ণ: rate limiter জবকে "release" করলে সেটা এক attempt হিসেবে গণ্য হয়।
     * $tries = 3 থাকলে ৫০০ জবের মধ্যে অনেকগুলো শুধু অপেক্ষা করতে করতেই ফেইল হয়ে যেত।
     * তাই attempts সীমাহীন, আর শুধু আসল exception গুনি ($maxExceptions)।
     */
    public int $tries = 0;
    public int $maxExceptions = 3;
    public int $backoff = 30;

    public function __construct(public int $recipientId)
    {
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addHours(12);
    }

    public function middleware(): array
    {
        return [new RateLimited('bulk-mail')];
    }

    public function handle(): void
    {
        $recipient = BulkEmailRecipient::with('campaign')->find($this->recipientId);

        if (! $recipient || $recipient->status !== 'pending') {
            return; // ইতিমধ্যে পাঠানো/ফেইল হয়ে গেছে
        }

        $campaign = $recipient->campaign;

        if ($campaign->status === 'queued') {
            $campaign->update(['status' => 'processing']);
        }

        try {
            Mail::to($recipient->email)->send(new BulkMail(
                $campaign->subject,
                $campaign->body_html,
                $campaign->from_email,
                $campaign->from_name,
            ));
        } catch (Throwable $e) {
            Log::warning("Bulk mail attempt failed for recipient #{$recipient->id}: {$e->getMessage()}");
            throw $e; // রিট্রাই হবে; চূড়ান্ত ব্যর্থতা failed() এ ধরা হয়
        }

        $recipient->update(['status' => 'sent', 'sent_at' => now(), 'error_message' => null]);
        $campaign->increment('sent_count');
        $this->finalize($campaign);
    }

    /** সব রিট্রাইয়ের পরেও ব্যর্থ হলে শুধু তখনই failed গোনা হয় */
    public function failed(Throwable $e): void
    {
        $recipient = BulkEmailRecipient::with('campaign')->find($this->recipientId);

        if (! $recipient || $recipient->status !== 'pending') {
            return;
        }

        $recipient->update([
            'status'        => 'failed',
            'error_message' => Str::limit($e->getMessage(), 500),
        ]);

        $recipient->campaign->increment('failed_count');
        $this->finalize($recipient->campaign);
    }

    private function finalize(BulkEmailCampaign $campaign): void
    {
        $campaign->refresh();

        if ($campaign->sent_count + $campaign->failed_count >= $campaign->total_recipients) {
            $campaign->update([
                'status' => $campaign->failed_count > 0 ? 'completed_with_errors' : 'completed',
            ]);
        }
    }
}