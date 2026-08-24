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
        Schema::create('pipeline_template_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_template_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('position');
            $table->enum('kind', ['interview', 'non_interview']);
            $table->foreignId('scorecard_template_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('default_duration_minutes')->nullable();
            $table->unsignedInteger('staleness_threshold_days');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pipeline_template_stages');
    }
};
