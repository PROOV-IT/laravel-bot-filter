<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $watchTable = config('url-watcher.database.table', 'url_watches');
        $settingsTable = config('url-watcher.settings.table', 'url_watcher_settings');

        // if (Schema::hasTable('bot_probes') && ! Schema::hasTable($watchTable)) {
        //     Schema::rename('bot_probes', $watchTable);
        // }

        // if (Schema::hasTable('bot_filter_settings') && ! Schema::hasTable($settingsTable)) {
        //     Schema::rename('bot_filter_settings', $settingsTable);
        // }
    }

    public function down(): void
    {
        $watchTable = config('url-watcher.database.table', 'url_watches');
        $settingsTable = config('url-watcher.settings.table', 'url_watcher_settings');

        // if (Schema::hasTable($watchTable) && ! Schema::hasTable('bot_probes')) {
        //     Schema::rename($watchTable, 'bot_probes');
        // }

        // if (Schema::hasTable($settingsTable) && ! Schema::hasTable('bot_filter_settings')) {
        //     Schema::rename($settingsTable, 'bot_filter_settings');
        // }
    }
};
