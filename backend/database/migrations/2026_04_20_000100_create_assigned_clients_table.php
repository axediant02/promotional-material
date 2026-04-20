<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assigned_clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('production_id');
            $table->uuid('client_id');
            $table->enum('status', ['pending', 'in_progress', 'done'])->default('pending');
            $table->timestamps();

            $table->unique('client_id');
            $table->foreign('production_id')->references('user_id')->on('users')->cascadeOnDelete();
            $table->foreign('client_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assigned_clients');
    }
};
