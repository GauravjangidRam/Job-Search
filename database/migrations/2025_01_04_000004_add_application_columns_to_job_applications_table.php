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
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('applicant_name')->nullable();
            $table->string('applicant_email')->nullable();
            $table->string('applicant_phone')->nullable();
            $table->string('resume_path', 2048)->nullable();
            $table->text('cover_letter')->nullable();
            $table->text('additional_info')->nullable();
            $table->string('status')->default('applied');
            $table->timestamp('status_updated_at')->nullable();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn([
                'applicant_name',
                'applicant_email',
                'applicant_phone',
                'resume_path',
                'cover_letter',
                'additional_info',
                'status',
                'status_updated_at',
            ]);
        });
    }
};