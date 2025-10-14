<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Recurring Types
        Schema::create('Reminder_RecurringTypes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type'); // Daily, Weekly, Monthly, Yearly, Custom
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('type');
        });

        // Stages
        Schema::create('Reminder_Stages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hash')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('name');
        });

        // Tags
        Schema::create('Reminder_Tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hash')->unique();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('name');
            $table->index('parent_id');
            $table->foreign('parent_id')->references('id')->on('Reminder_Tags')->nullOnDelete();
        });

        // Documents
        Schema::create('Reminder_Documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hash')->unique();
            $table->string('storage_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index('uploaded_by');
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });

        // Items
        Schema::create('Reminder_Items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hash')->unique();
            $table->unsignedBigInteger('location_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('due_at');
            $table->boolean('is_completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['location_id','due_at']);
            $table->index(['due_at','id']);
            $table->index(['is_completed','due_at']);
            $table->foreign('location_id')->references('id')->on('locations')->cascadeOnDelete();
        });

        // Subtasks
        Schema::create('Reminder_Subtasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hash')->unique();
            $table->unsignedBigInteger('item_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('item_id')->references('id')->on('Reminder_Items')->cascadeOnDelete();
        });

        // Item-Stage pivot
        Schema::create('Reminder_ItemStage', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('stage_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['item_id','stage_id']);
            $table->foreign('item_id')->references('id')->on('Reminder_Items')->cascadeOnDelete();
            $table->foreign('stage_id')->references('id')->on('Reminder_Stages')->cascadeOnDelete();
        });

        // Item-Tag pivot
        Schema::create('Reminder_ItemTag', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('tag_id');
            $table->timestamps();
            $table->unique(['item_id','tag_id']);
            $table->foreign('item_id')->references('id')->on('Reminder_Items')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('Reminder_Tags')->cascadeOnDelete();
        });

        // Item-Document pivot
        Schema::create('Reminder_ItemDocument', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('document_id');
            $table->timestamps();
            $table->unique(['item_id','document_id']);
            $table->foreign('item_id')->references('id')->on('Reminder_Items')->cascadeOnDelete();
            $table->foreign('document_id')->references('id')->on('Reminder_Documents')->cascadeOnDelete();
        });

        // Item Recurrences
        Schema::create('Reminder_ItemRecurrences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('recurring_type_id');
            $table->integer('interval_value')->default(1);
            $table->date('anchor_date');
            $table->date('end_date')->nullable();
            $table->string('timezone')->default('UTC');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('Reminder_Items')->cascadeOnDelete();
            $table->foreign('recurring_type_id')->references('id')->on('Reminder_RecurringTypes');
        });

        // Access Grants
        Schema::create('Reminder_AccessGrants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hash')->unique();
            $table->unsignedBigInteger('grantor_user_id');
            $table->unsignedBigInteger('grantee_user_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->enum('permission', ['view','comment','complete','manage']);
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['grantee_user_id','item_id']);
            $table->index(['grantee_user_id','location_id']);
            $table->index(['grantor_user_id']);
            $table->foreign('grantor_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('grantee_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('item_id')->references('id')->on('Reminder_Items')->nullOnDelete();
            $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();
        });

        // Activity Logs
        Schema::create('Reminder_ActivityLogs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hash')->unique();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('action');
            $table->json('meta')->nullable();
            $table->dateTime('created_at');
            $table->index(['location_id','created_at']);
            $table->index(['item_id','created_at']);
            $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();
            $table->foreign('item_id')->references('id')->on('Reminder_Items')->nullOnDelete();
            $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();
        });

        // Stage History
        Schema::create('Reminder_StageHistory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('stage_id');
            $table->unsignedBigInteger('changed_by');
            $table->dateTime('changed_at');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('Reminder_Items')->cascadeOnDelete();
            $table->foreign('stage_id')->references('id')->on('Reminder_Stages')->cascadeOnDelete();
            $table->foreign('changed_by')->references('id')->on('users')->cascadeOnDelete();
        });

        // Notifications
        Schema::create('Reminder_Notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id');
            $table->enum('channel', ['email','sms','webhook']);
            $table->dateTime('notify_at');
            $table->dateTime('sent_at')->nullable();
            $table->enum('status', ['pending','sent','failed'])->default('pending');
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['item_id','notify_at','status']);
            $table->foreign('item_id')->references('id')->on('Reminder_Items')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Reminder_Notifications');
        Schema::dropIfExists('Reminder_StageHistory');
        Schema::dropIfExists('Reminder_ActivityLogs');
        Schema::dropIfExists('Reminder_AccessGrants');
        Schema::dropIfExists('Reminder_ItemRecurrences');
        Schema::dropIfExists('Reminder_ItemDocument');
        Schema::dropIfExists('Reminder_ItemTag');
        Schema::dropIfExists('Reminder_ItemStage');
        Schema::dropIfExists('Reminder_Subtasks');
        Schema::dropIfExists('Reminder_Items');
        Schema::dropIfExists('Reminder_Documents');
        Schema::dropIfExists('Reminder_Tags');
        Schema::dropIfExists('Reminder_Stages');
        Schema::dropIfExists('Reminder_RecurringTypes');
    }
};
