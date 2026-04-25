<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(config('url-watcher.settings.table', 'url_watcher_settings'), function (Blueprint $table): void {
            $table->string('active_ruleset')->nullable()->after('notification_mode');
            $table->boolean('digest_enabled')->default(false)->after('show_widgets');
            $table->string('digest_mail')->nullable()->after('digest_enabled');
            $table->string('digest_title')->nullable()->after('digest_mail');
            $table->text('digest_intro')->nullable()->after('digest_title');
            $table->unsignedSmallInteger('digest_window_hours')->default(24)->after('digest_intro');
            $table->boolean('digest_notify_when_empty')->default(false)->after('digest_window_hours');
        });
    }

    public function down(): void
    {
        Schema::table(config('url-watcher.settings.table', 'url_watcher_settings'), function (Blueprint $table): void {
            $table->dropColumn([
                'active_ruleset',
                'digest_enabled',
                'digest_mail',
                'digest_title',
                'digest_intro',
                'digest_window_hours',
                'digest_notify_when_empty',
            ]);
        });
    }
};
