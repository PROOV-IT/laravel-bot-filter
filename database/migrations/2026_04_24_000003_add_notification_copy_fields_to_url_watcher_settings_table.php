<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('url-watcher.settings.table', 'url_watcher_settings'), function (Blueprint $table): void {
            $table->string('notification_title')->nullable()->after('notification_mode');
            $table->text('notification_intro')->nullable()->after('notification_title');
        });
    }

    public function down(): void
    {
        Schema::table(config('url-watcher.settings.table', 'url_watcher_settings'), function (Blueprint $table): void {
            $table->dropColumn(['notification_title', 'notification_intro']);
        });
    }
};
