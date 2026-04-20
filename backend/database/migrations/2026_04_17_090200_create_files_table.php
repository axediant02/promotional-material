<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('file_id')->primary();
            $table->uuid('folder_id');
            $table->uuid('uploaded_by');
            $table->string('file_name');
            $table->string('storage_disk')->default('local');
            $table->string('storage_path');
            $table->enum('category', ['image', 'video', 'pdf']);
            $table->timestamp('last_deleted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('folder_id')->references('folder_id')->on('folders')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
