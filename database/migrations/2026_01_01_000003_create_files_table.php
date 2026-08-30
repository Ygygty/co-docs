<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('scan_source_id')->constrained('scan_sources')->cascadeOnDelete();
            $table->text('relative_path');
            $table->text('absolute_path');
            $table->string('basename');
            $table->string('extension')->nullable();
            $table->string('mime_type')->nullable();
            $table->bigInteger('size_bytes')->nullable();
            $table->timestamp('file_mtime')->nullable();
            $table->unsignedBigInteger('inode')->nullable();
            $table->char('raw_sha256', 64)->nullable();
            $table->char('current_content_sha256', 64)->nullable();
            $table->string('status')->default('active');
            $table->integer('max_versions')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_indexed_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('error_message')->nullable();
            $table->jsonb('meta')->default('{}');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['scan_source_id', 'relative_path']);
            $table->index('status');
            $table->index('raw_sha256');
            $table->index('basename');
            $table->index('relative_path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
