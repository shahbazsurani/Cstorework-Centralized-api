<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('applications')) {
            Schema::create('applications', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('url_local');
                $table->string('url_production');
                $table->string('url_staging')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'slug')) {
                $table->string('slug')->unique()->after('id');
            }
            if (!Schema::hasColumn('applications', 'name')) {
                $table->string('name')->after('slug');
            }
            if (!Schema::hasColumn('applications', 'url_local')) {
                $table->string('url_local')->after('name');
            }
            if (!Schema::hasColumn('applications', 'url_production')) {
                $table->string('url_production')->after('url_local');
            }
            if (!Schema::hasColumn('applications', 'url_staging')) {
                $table->string('url_staging')->nullable()->after('url_production');
            }
            if (!Schema::hasColumn('applications', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('url_staging');
            }
        });
    }

    public function down(): void
    {
        // Non-destructive: only drop if we created it here
        // To be safe, do not drop the table automatically in down()
        // Developers can craft a specific down migration if needed.
    }
};
