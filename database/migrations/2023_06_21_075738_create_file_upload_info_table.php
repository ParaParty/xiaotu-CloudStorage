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
        Schema::create('file_upload_info', function (Blueprint $table) {
            $table->id();
            // 添加其他列定义
            $table->bigInteger('file_id');
            $table->string('file_path');
            $table->string('file_hash');
            $table->integer('owner_id');
            $table->integer('total_chunks');
            $table->integer('uploaded_chunks')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_upload_info');
    }
};
