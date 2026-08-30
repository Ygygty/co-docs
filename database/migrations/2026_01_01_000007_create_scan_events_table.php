<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scan_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('scan_run_id')->constrained('scan_runs')->cascadeOnDelete();
            $table->foreignId('file_id')->nullable()->constrained('files')->nullOnDelete();
            $table->foreignId('file_version_id')->nullable()->constrained('file_versions')->nullOnDelete();
            $table->string('event_type');
            $table->text('message')->nullable();
            $table->jsonb('meta')->default('{}');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_events');
    }
};
