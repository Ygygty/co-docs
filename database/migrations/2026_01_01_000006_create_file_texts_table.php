<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('file_texts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->foreignId('file_version_id')->constrained('file_versions')->cascadeOnDelete();
            $table->text('content')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        // create tsvector column via raw SQL for PostgreSQL
        DB::statement('ALTER TABLE file_texts ADD COLUMN IF NOT EXISTS search_vector tsvector;');
        DB::statement("CREATE INDEX file_texts_search_vector_gin ON file_texts USING GIN (search_vector);");
    }

    public function down(): void
    {
        Schema::dropIfExists('file_texts');
    }
};
