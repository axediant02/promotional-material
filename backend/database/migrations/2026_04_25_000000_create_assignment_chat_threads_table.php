<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_chat_threads', function (Blueprint $table) {
            $table->uuid('thread_id')->primary();
            $table->uuid('assignment_id')->nullable();
            $table->uuid('client_id');
            $table->uuid('production_id');
            $table->enum('status', ['active', 'archived'])->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->uuid('last_message_by')->nullable();
            $table->timestamp('client_last_read_at')->nullable();
            $table->timestamp('production_last_read_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['production_id', 'status']);
            $table->index(['assignment_id', 'status']);
            $table->foreign('assignment_id')->references('id')->on('assigned_clients')->nullOnDelete();
            $table->foreign('client_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('production_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('last_message_by')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_chat_threads');
    }
};
