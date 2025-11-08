<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('location_settings_categories', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 254)->unique();
            $table->foreignId('location_id')->constrained('locations')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('location_settings_categories')->onDelete('cascade');
            $table->string('category_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['location_id', 'parent_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_settings_categories');
    }
};
