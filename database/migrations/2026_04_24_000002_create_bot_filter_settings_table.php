<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('bot-filter.settings.table', 'bot_filter_settings'), function (Blueprint $table): void {
            $table->id();
            $table->boolean('capture_enabled')->default(true);
            $table->boolean('capture_exceptions')->default(true);
            $table->json('capture_statuses')->nullable();
            $table->json('ignore_paths')->nullable();
            $table->json('ignore_hosts')->nullable();
            $table->json('ignore_panels')->nullable();
            $table->json('ignore_methods')->nullable();
            $table->json('ignore_exception_classes')->nullable();
            $table->boolean('notifications_enabled')->default(true);
            $table->string('notification_mode', 32)->default('default')->index();
            $table->string('notification_mail')->nullable();
            $table->string('notification_route')->nullable();
            $table->string('custom_notification_class')->nullable();
            $table->boolean('show_widgets')->default(true);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('bot-filter.settings.table', 'bot_filter_settings'));
    }
};
