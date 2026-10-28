<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class BulkEmailCampaign extends Model
{
    protected $fillable = [
        'name', 'subject', 'body_html', 'from_email', 'from_name',
        'total_recipients', 'sent_count', 'failed_count', 'status',
    ];
 
    public function recipients(): HasMany
    {
        return $this->hasMany(BulkEmailRecipient::class, 'campaign_id');
    }
}