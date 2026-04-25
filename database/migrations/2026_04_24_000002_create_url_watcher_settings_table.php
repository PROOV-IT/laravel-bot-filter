<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('url-watcher.settings.table', 'url_watcher_settings');

        // if (Schema::hasTable($tableName) || Schema::hasTable('bot_filter_settings')) {
        //     return;
        // }

        Schema::create($tableName, function (Blueprint $table): void {
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
        Schema::dropIfExists(config('url-watcher.settings.table', 'url_watcher_settings'));
    }
};
