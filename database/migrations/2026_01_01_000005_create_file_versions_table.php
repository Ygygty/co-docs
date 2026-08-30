<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('file_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->integer('version_number');
            $table->boolean('is_current')->default(false);
            $table->foreignId('raw_blob_id')->nullable()->constrained('file_blobs')->nullOnDelete();
            $table->char('raw_sha256', 64);
            $table->bigInteger('raw_size_bytes');
            $table->string('original_file_name')->nullable();
            $table->string('original_extension')->nullable();
            $table->char('content_sha256', 64)->nullable();
            $table->bigInteger('extracted_text_size')->nullable();
            $table->string('detected_encoding')->nullable();
            $table->string('extraction_method')->nullable();
            $table->string('extractor_version')->nullable();
            $table->string('extraction_status')->default('pending');
            $table->text('extracted_text_storage_path')->nullable();
            $table->text('extracted_text')->nullable();
            $table->string('storage_status')->default('pending');
            $table->timestamp('purged_at')->nullable();
            $table->jsonb('meta')->default('{}');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['file_id', 'version_number']);
            // partial unique index: single current per file
        });

        // create partial unique index for is_current using raw SQL because Blueprint lacks partial indexes
        DB::statement('CREATE UNIQUE INDEX file_versions_file_current_unique ON file_versions (file_id) WHERE is_current = true;');

        Schema::table('file_versions', function (Blueprint $table) {
            $table->index('raw_blob_id');
            $table->index('raw_sha256');
            $table->index('created_at');
            $table->index('storage_status');
            $table->index('extraction_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_versions');
    }
};
