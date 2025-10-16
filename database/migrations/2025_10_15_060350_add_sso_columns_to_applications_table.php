<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (!Schema::hasColumn('applications', 'login_path')) {
                $table->string('login_path')->nullable()->after('url_staging');
            }
            if (!Schema::hasColumn('applications', 'target_param')) {
                $table->string('target_param')->nullable()->after('login_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'target_param')) {
                $table->dropColumn('target_param');
            }
            if (Schema::hasColumn('applications', 'login_path')) {
                $table->dropColumn('login_path');
            }
        });
    }
};
