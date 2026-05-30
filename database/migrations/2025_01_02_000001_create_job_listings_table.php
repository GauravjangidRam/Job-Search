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
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('company_name', 255);
            $table->string('company_logo_url', 2048)->nullable();
            $table->string('location', 255);
            $table->unsignedInteger('salary_min');
            $table->unsignedInteger('salary_max');
            $table->string('job_type', 50);
            $table->string('location_type', 50);
            $table->text('description');
            $table->json('skills');
            $table->timestamps();

            $table->index('job_type');
            $table->index('location_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
