<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scan_runs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('scan_source_id')->nullable()->constrained('scan_sources')->nullOnDelete();
            $table->string('status')->default('pending'); // use enum via app Enum mapping
            $table->string('triggered_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->boolean('listing_completed')->default(false);
            $table->jsonb('stats')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_runs');
    }
};
