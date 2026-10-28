<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * প্রতিটা ক্যাম্পেইনের প্রতিটা রেসিপিয়েন্টের রেকর্ড এখানে থাকবে।
 * status ফিল্ড দিয়ে ট্র্যাক করা হবে কোনটা পাঠানো হয়েছে, কোনটা ব্যর্থ হয়েছে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->longText('body_html');
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->enum('status', ['draft', 'queued', 'processing', 'completed', 'completed_with_errors'])
                  ->default('draft');
            $table->timestamps();
        });

        Schema::create('bulk_email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('bulk_email_campaigns')->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_email_recipients');
        Schema::dropIfExists('bulk_email_campaigns');
    }
};