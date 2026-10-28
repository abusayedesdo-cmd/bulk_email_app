<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkEmailRecipient extends Model
{
    protected $fillable = [
        'campaign_id', 'email', 'name', 'status', 'error_message', 'sent_at',
    ];
 
    protected $casts = [
        'sent_at' => 'datetime',
    ];
 
    public function campaign()
    {
        return $this->belongsTo(BulkEmailCampaign::class, 'campaign_id');
    }
}