<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scan_sources', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->text('root_path');
            $table->boolean('is_recursive')->default(true);
            $table->jsonb('include_patterns')->default('[]');
            $table->jsonb('exclude_patterns')->default('[]');
            $table->jsonb('allowed_extensions')->nullable();
            $table->bigInteger('max_file_size_bytes')->nullable();
            $table->integer('max_versions')->default(200);
            $table->boolean('is_active')->default(true);
            $table->boolean('follow_symlinks')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_sources');
    }
};
