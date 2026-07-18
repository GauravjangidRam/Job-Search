<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'job_listings_status_created_at_index');
            $table->index(['company_id', 'created_at'], 'job_listings_company_created_at_index');
        });

        Schema::table('job_applications', function (Blueprint $table) {
            $table->index(['job_listing_id', 'status', 'created_at'], 'job_applications_listing_status_created_at_index');
        });

        Schema::table('job_alerts', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'job_alerts_user_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('job_alerts', fn (Blueprint $table) => $table->dropIndex('job_alerts_user_created_at_index'));
        Schema::table('job_applications', fn (Blueprint $table) => $table->dropIndex('job_applications_listing_status_created_at_index'));
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropIndex('job_listings_status_created_at_index');
            $table->dropIndex('job_listings_company_created_at_index');
        });
    }
};
