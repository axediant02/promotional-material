<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_chat_messages', function (Blueprint $table) {
            $table->uuid('message_id')->primary();
            $table->uuid('thread_id');
            $table->uuid('sender_user_id');
            $table->text('body');
            $table->timestamps();

            $table->index(['thread_id', 'created_at']);
            $table->foreign('thread_id')->references('thread_id')->on('assignment_chat_threads')->cascadeOnDelete();
            $table->foreign('sender_user_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_chat_messages');
    }
};
