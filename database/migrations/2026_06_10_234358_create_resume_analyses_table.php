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
        Schema::create('resume_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->nullable()->constrained('job_applications')->onDelete('cascade');
            $table->string('resume_path', 2048)->nullable();
            $table->json('analysis')->nullable();
            $table->string('provider')->nullable();
            $table->timestamps();

            $table->index('job_application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_analyses');
    }
};
