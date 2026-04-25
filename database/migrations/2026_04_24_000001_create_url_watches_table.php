<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('url-watcher.database.table', 'url_watches');

        // if (Schema::hasTable($tableName) || Schema::hasTable('bot_probes')) {
        //     return;
        // }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('fingerprint', 64)->unique();
            $table->string('exception_class')->nullable();
            $table->string('method', 16)->nullable();
            $table->string('host')->nullable();
            $table->string('path')->index();
            $table->string('normalized_path')->index();
            $table->string('route_name')->nullable()->index();
            $table->string('ip', 64)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('panel', 64)->nullable()->index();
            $table->string('status', 32)->default('pending')->index();
            $table->string('classification', 32)->default('unknown')->index();
            $table->string('suggested_classification', 32)->nullable()->index();
            $table->unsignedInteger('count')->default(1);
            $table->timestampTz('first_seen_at')->nullable()->index();
            $table->timestampTz('last_seen_at')->nullable()->index();
            $table->timestampTz('notified_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('url-watcher.database.table', 'url_watches'));
    }
};
