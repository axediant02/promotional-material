<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('folder_id');
            $table->uuid('uploaded_by');
            $table->string('original_name');
            $table->string('storage_disk')->default('local');
            $table->string('storage_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamp('last_deleted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
