<?php
// app/Http/Controllers/BulkEmailController.php  (পুরো ফাইল রিপ্লেস করুন)

namespace App\Http\Controllers;

use App\Jobs\SendBulkEmailJob;
use App\Models\BulkEmailCampaign;
use App\Models\BulkEmailRecipient;
use Illuminate\Http\Request;

class BulkEmailController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'subject'             => 'required|string|max:255',
            'body_html'           => 'required|string',
            'from_email'          => 'nullable|email',
            'from_name'           => 'nullable|string|max:255',
            'recipients'          => 'required|array|min:1|max:500',
            'recipients.*.email'  => 'required|email',
            'recipients.*.name'   => 'nullable|string|max:255',
        ]);

        // আগে ডুপ্লিকেট বাদ দিই, তারপর গণনা — নাহলে total ভুল হয়
        $recipients = collect($data['recipients'])
            ->unique(fn ($r) => strtolower($r['email']))
            ->values();

        $campaign = new BulkEmailCampaign([
            'name'             => $data['name'],
            'subject'          => $data['subject'],
            'body_html'        => $data['body_html'],
            'from_email'       => $data['from_email'] ?? null,
            'from_name'        => $data['from_name'] ?? null,
            'total_recipients' => $recipients->count(),
            'status'           => 'queued',
        ]);
        // কোন প্রজেক্ট পাঠাল (middleware থেকে আসে)
        $campaign->client = $request->attributes->get('client');
        $campaign->save();

        $rows = $recipients->map(fn ($r) => [
            'campaign_id' => $campaign->id,
            'email'       => $r['email'],
            'name'        => $r['name'] ?? null,
            'status'      => 'pending',
            'created_at'  => now(),
            'updated_at'  => now(),
        ])->all();

        BulkEmailRecipient::insert($rows);

        BulkEmailRecipient::where('campaign_id', $campaign->id)
            ->select('id')
            ->chunkById(100, function ($chunk) {
                foreach ($chunk as $recipient) {
                    SendBulkEmailJob::dispatch($recipient->id);
                }
            });

        return response()->json([
            'message'     => 'Campaign queued successfully.',
            'campaign_id' => $campaign->id,
        ], 202);
    }

    public function status(Request $request, int $campaignId)
    {
        // এক প্রজেক্ট অন্য প্রজেক্টের ক্যাম্পেইন দেখতে পাবে না
        $campaign = BulkEmailCampaign::where('client', $request->attributes->get('client'))
            ->findOrFail($campaignId);

        $failed = BulkEmailRecipient::where('campaign_id', $campaign->id)
            ->where('status', 'failed')
            ->limit(50)
            ->get(['email', 'error_message']);

        return response()->json([
            'name'             => $campaign->name,
            'status'           => $campaign->status,
            'total_recipients' => $campaign->total_recipients,
            'sent_count'       => $campaign->sent_count,
            'failed_count'     => $campaign->failed_count,
            'pending_count'    => max(0, $campaign->total_recipients - $campaign->sent_count - $campaign->failed_count),
            'failed'           => $failed,
        ]);
    }
}