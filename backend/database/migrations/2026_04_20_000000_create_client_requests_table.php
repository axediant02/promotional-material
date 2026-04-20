<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_requests', function (Blueprint $table) {
            $table->uuid('request_id')->primary();
            $table->uuid('client_id');
            $table->uuid('folder_id');
            $table->string('title');
            $table->text('description');
            $table->enum('request_type', ['new_asset', 'update_asset']);
            $table->enum('status', ['pending', 'in_progress', 'done'])->default('pending');
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('client_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('folder_id')->references('folder_id')->on('folders')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_requests');
    }
};
