<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('url-watcher.settings.table', 'url_watcher_settings');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (! Schema::hasColumn($tableName, 'notification_title')) {
                $table->string('notification_title')->nullable()->after('notification_mode');
            }

            if (! Schema::hasColumn($tableName, 'notification_intro')) {
                $table->text('notification_intro')->nullable()->after('notification_title');
            }
        });
    }

    public function down(): void
    {
        $tableName = config('url-watcher.settings.table', 'url_watcher_settings');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            $columns = array_values(array_filter([
                Schema::hasColumn($tableName, 'notification_title') ? 'notification_title' : null,
                Schema::hasColumn($tableName, 'notification_intro') ? 'notification_intro' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
