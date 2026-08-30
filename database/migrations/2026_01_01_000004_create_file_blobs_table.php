<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('file_blobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('sha256', 64)->unique();
            $table->bigInteger('size_bytes');
            $table->string('mime_type')->nullable();
            $table->text('storage_path');
            $table->timestamp('last_verified_at')->nullable();
            $table->jsonb('meta')->default('{}');
            $table->timestamps();
        });

        // index for size maybe
        Schema::table('file_blobs', function (Blueprint $table) {
            $table->index('size_bytes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_blobs');
    }
};
