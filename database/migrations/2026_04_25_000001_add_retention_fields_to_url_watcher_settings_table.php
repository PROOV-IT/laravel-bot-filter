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
            if (! Schema::hasColumn($tableName, 'digest_recent_events_limit')) {
                $table->unsignedSmallInteger('digest_recent_events_limit')->default(10)->after('digest_notify_when_empty');
            }

            if (! Schema::hasColumn($tableName, 'retention_enabled')) {
                $table->boolean('retention_enabled')->default(false)->after('digest_recent_events_limit');
            }

            if (! Schema::hasColumn($tableName, 'retention_days')) {
                $table->unsignedSmallInteger('retention_days')->default(30)->after('retention_enabled');
            }

            if (! Schema::hasColumn($tableName, 'retention_prune_aggregates')) {
                $table->boolean('retention_prune_aggregates')->default(false)->after('retention_days');
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
                Schema::hasColumn($tableName, 'digest_recent_events_limit') ? 'digest_recent_events_limit' : null,
                Schema::hasColumn($tableName, 'retention_enabled') ? 'retention_enabled' : null,
                Schema::hasColumn($tableName, 'retention_days') ? 'retention_days' : null,
                Schema::hasColumn($tableName, 'retention_prune_aggregates') ? 'retention_prune_aggregates' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
