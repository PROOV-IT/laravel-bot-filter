<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('url-watcher.database.events_table', 'url_watch_events');

        // if (Schema::hasTable($tableName)) {
        //     return;
        // }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('url_watch_id')->index();
            $table->string('method', 16)->nullable();
            $table->string('host')->nullable()->index();
            $table->string('path')->index();
            $table->string('normalized_path')->index();
            $table->text('full_url')->nullable();
            $table->text('query_string')->nullable();
            $table->string('route_name')->nullable()->index();
            $table->string('ip', 64)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->string('panel', 64)->nullable()->index();
            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->string('exception_class')->nullable()->index();
            $table->text('exception_message')->nullable();
            $table->string('request_id', 128)->nullable()->index();
            $table->timestampTz('occurred_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('url-watcher.database.events_table', 'url_watch_events'));
    }
};
